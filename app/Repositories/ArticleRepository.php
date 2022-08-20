<?php

namespace App\Repositories;

use App\Events\StoreArticleEvent;
use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use App\Events\DeleteWaitingContributorEvent;

class ArticleRepository
{

    /*
     * Gives the last revision of the articles and if the $isPublished is set, it shows only the published ones,
     * and if the $showDeleted is set, it shows the soft deleted ones.
     */
    public function index($user, $isPublished, $showDeleted)
    {
        $isPublished = $isPublished ? " AND publish_date IS NOT NULL" : "";
        $showDeleted = $showDeleted ? " AND deleted_at IS NOT NULL" : "";

        if ($user->role == 'normal') {
            //Gets his/her articles

            return DB::select("SELECT * FROM articles WHERE user_ref_id = ? AND is_last_revision = 1 $isPublished $showDeleted", [$user->id]);
        } elseif ($user->role == 'department_manager') {
            //Only gets the articles belong to the authors of this department

            $users = DB::select("SELECT user_id FROM users WHERE department_ref_id = ? AND is_last_revision = 1 $isPublished $showDeleted", [$user->department_ref_id]);
            $length = count($users);
            $i = 0;
            $conditions = "";
            $values = [];
            foreach ($users as $value) {
                $i++;
                if ($length == $i)
                    $conditions .= "user_ref_id = ? OR ";
                else
                    $conditions .= "user_ref_id = ?";
                $values[] = $value->id;
            }
            $conditions .= $isPublished . $showDeleted;

            return DB::select("SELECT * FROM articles WHERE is_last_revision = 1 AND ($conditions))", $values);
        } else {
            //Gets all articles
            //The user is admin or the university manager
            return DB::select("SELECT * FROM articles");
        }
    }

    /*
     *CONVENTION ==> {
     * "author", "title", "contributors", "publishedArticles", "categories", "body",
     *  "tags", "messages" => [
     *      {"contributorID",
     *       "title",
     *       "body"}
     *    ]
     * }
     */

    public function create($validated)
    {

        //Definded now() out side of the loop for making the time in all of these columns (publish_date, created-at, ...) the same!
        $now = Carbon::now();
        //19 character unique article_code
        $articleCode = random_int(100000000000000000, 9111111111111111111);

        $bindings = [$articleCode, $validated['title'], $validated['body'] ?? "", $validated['categories'] ?? "",
            $validated['tags'] ?? "", $validated['author'], $validated['contributors'], $validated['publishedArticles'], 1, 'pending', $now];


        try {
            $model = DB::insert("INSERT INTO articles (article_code, title, body, category_department_ref_id,
                      tag_ref_id, user_ref_id, waiting_contributors_ref_id, published_articles_ref_id, is_last_revision,
                      status, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?)", $bindings);
        } catch (QueryException $e) {
            return $e->getMessage();
        }

        //dispatch an event
        $messages = $validated['messages'];
        $article = DB::select("SELECT * FROM articles WHERE article_code = $articleCode");


        event(new StoreArticleEvent($article, $messages));
        return "The Article is created successfully and the invitation messages are sent!";
    }

    /*
     * CONVENTION ==> [[ "field to set" => "?,", ... ],["field for where clause" => "? AND OR AND( OR( AND) OR) )AND( )OR(...", ...], [value1, value2, ...]]
     */
    public function update($items)
    {
        $set = "";
        $where = "";
        $values = [];

        foreach ($items[0] as $key => $value) {

            $set .= $key . " " . $value;
        }

        foreach ($items[1] as $key => $value) {
            $where .= $key . " " . $value;
        }

        foreach ($items[2] as $item) {
            $values[] = $item;
        }


        DB::update("UPDATE articles SET $set WHERE $where", $values);

        return "The article is updated!";
    }

    public function editArticle($article, $revisionNumber)
    {
        if ($revisionNumber)
            $article = DB::select("SELECT * FROM articles WHERE revision_number = ? AND revision_ref_id = ?", [$revisionNumber, $article->article_id]);

        //author's article
        $result[] = $article;

        //list of contributors with their status and some info about them
        $lists = [$article->waiting_contributors_ref_id, $article->rejected_contributors_ref_id, $article->contributors_ref_id];

        //$lists array has three indexes (waiting_contributors_ref_id, rejected_contributors_ref_id, contributors_ref_id)
        $length = 3;
        $i = 0;
        foreach ($lists as $list) {
            ++$i;
            //All the results of a query builder or eloquent model are json objects comprised of pairs of key/value(table field name/value)
            foreach ($list as $key => $value) {
                //only the third index of $lists which is contributor_ref_id is json and needs to be decoded
                if ($i == 3)
                    $values = json_decode($value, true);
                else
                    $values = explode(',', $value);


                //Instead of having some userIDs we're replacing them with their whole model instance
                foreach ($values as $item) {
                    $userItem = DB::select("SELECT * FROM users WHERE user_id = ?", [$item])[0];
                    if ($i == 1)
                        $result['waiting'][] = $userItem;
                    else if ($i == 2)
                        $result['rejected'][] = $userItem;
                    else if ($i == 3)
                        $result['accepted'][] = $userItem;
                }
            }
        }


        //accepted contributors' articles

        $acceptedContributors = json_decode($article->contributors_ref_id, true);
        foreach ($acceptedContributors as $key => $value) {
            $user = DB::select("SELECT username, first_name, last_name FROM users WHERE user_id = ?", [$key])[0];

            //putting the whole article of a user with the user info and even the name of his/her department
            $result['contributorsArticles'][] = DB::select("SELECT title, body, article_code, article_id FROM articles WHERE article_id = ?", [$value])[0]
                . $user . DB::select("SELECT name, english_name FROM departments WHERE department_id = ?", [$user->department_ref_id])[0];
        }


        return $result;

    }

    /*
     * convention ==> {"title" : "", "body" : "", "deletedWaitingContributors" : "", "deletedRejectedContributors" : "", "newWaitingContributors" : ""}
     */
    public function updateArticle($request, $article)
    {
        $now = $request->isPublished ? Carbon::now() : null;


        $deletedWaitingContributors = explode(',', $request->deletedWaitingContributors);
        $newWaitingContributors = explode(',', $request->newWaitingContributors);
        $deletedRejectedContributors = explode(',', $request->deletedRejectedContributors);

        $contributors['waiting'] = $article->waiting_contributors_ref_id;
        $contributors['rejected'] = $article->rejected_contributors_ref_id;
        $newWaitings = [];

        foreach ($deletedWaitingContributors as $value) {
            if ($key = array_search($value, $contributors['waiting'])) {
                unset($contributors['waiting'][$key]);
            }
        }

        /*
         * Put new after to make sure that even if someone is deleted if the user decided to add
         * that contributor again, maybe he/she had subconsciously a reason for that!
         */
        foreach ($newWaitingContributors as $value) {
            if (!array_search($value, $contributors['waiting'])) {
                $contributors['waiting'][] = $value;
                /*
                 * we're now certain that these are the real new contributors that are not
                 *  duplicated, so we can send the invitation links for them free of mind!
                 */
                $newWaitings[] = $value;
            }
        }
        foreach ($deletedRejectedContributors as $value) {
            if ($key = array_search($value, $contributors['rejected'])) {
                unset($contributors['rejected'][$key]);
            }
        }

        $waiting = implode(',', $contributors['waiting']);
        $rejected = implode(',', $contributors['rejected']);


        DB::update("UPDATE articles SET title = ?, body = ?, publish_date = ?,
                    waiting_contributors_ref_id = ?, rejected_contributors_ref_id = ?
                    WHERE article_id = ?", [$request->title, $request->body, $now, $waiting
            , $rejected, $article->article_id]);

        event(new StoreArticleEvent($article, $request->messages, $newWaitings));

        return "The article is successfully updated!";
    }

    public function find($id)
    {
        return DB::select("SELECT * FROM articles WHERE article_id = ?", [$id]);
    }

    public function softDelete($article)
    {
        //*************Before anyone click on the accept invitation link we need to quickly delete the article.
        //Notice that the $article variable has a copy of the record and the deletion of the record in the database has obviously no effect on it.
        DB::delete("UPDATE articles SET deleted_at = ? WHERE article_id = ?", [Carbon::now(), $article->article_id]);

        $waitingContributors = explode(',', $article->waiting_contributors_ref_id);
        $acceptedContributors = json_decode($article->contributors_ref_id, true);
        $invitationMessages = json_decode($article->invitation_messages_ref_id, true);

        //*************Deleting the article_id from the parent_ref_id field of the acceptedContributors' articles table record
        foreach ($acceptedContributors as $articleID) {
            $parentsIDs = explode(',', DB::select("SELECT parent_ref_id FROM articles WHERE article_id = ?", [$articleID])[0]->parent_ref_id);
            if ($key = array_search($article->article_id, $parentsIDs)) {
                unset($parentsIDs[$key]);
            }
            $parentsIDs = implode(',', $parentsIDs);
            DB::update("UPDATE articles SET parent_ref_id = ? WHERE article_id = ?", [$parentsIDs, $articleID]);
        }


        //*************Deleting the invitation message from the waiting contributors mailbox
        foreach ($waitingContributors as $contributor) {
            foreach ($invitationMessages as $key => $value) {
                if ($key == $contributor) {
                    DB::delete("DELETE FROM messages WHERE message_id = ?", [$value]);
                }
            }
        }


        //*************Send a message to the accepted contributors to notify them that the project is closed.
        foreach ($acceptedContributors as $key => $value) {
            DB::insert("INSERT INTO messages (title, body, from_ref_id, to_ref_id, created_at)
                                VALUES(?,?,?,?,?)", ["The project is closed", "Hi dear colleague. Unfortunately
                                this project is closed.", $article->user_id, $key, Carbon::now()]);
        }

        return "The article is temporarily deleted, you can decide to restore it later.";

    }

    public function forceDelete($article)
    {
        DB::delete("DELETE FROM articles WHERE article_id = ?", [$article->article_id]);

        return "The article is completely deleted from the database";

    }

    public function restoreDeleted($article)
    {
        DB::delete("UPDATE articles SET deleted_at = ? WHERE article_id = ?", [null, $article->article_id]);

        $waitingContributors = explode(',', $article->waiting_contributors_ref_id);
        $acceptedContributors = json_decode($article->contributors_ref_id, true);
        $invitationMessages = json_decode($article->invitation_messages_ref_id, true);

        //*************Adding the article_id from the parent_ref_id field of the acceptedContributors' articles table record
        foreach ($acceptedContributors as $articleID) {
            $parentsIDs = explode(',', DB::select("SELECT parent_ref_id FROM articles WHERE article_id = ?", [$articleID])[0]->parent_ref_id);
            if (!array_search($article->article_id, $parentsIDs)) {
                $parentsIDs[] = $articleID;
            }
            $parentsIDs = implode(',', $parentsIDs);
            DB::update("UPDATE articles SET parent_ref_id = ? WHERE article_id = ?", [$parentsIDs, $articleID]);
        }


        //*************Sending invitation messages for the waiting contributors

        event(new StoreArticleEvent($article, [], [], true));


        //*************Send a message to the accepted contributors to notify them that the project is closed.
        foreach ($acceptedContributors as $key => $value) {
            DB::insert("INSERT INTO messages (title, body, from_ref_id, to_ref_id, created_at)
                                VALUES(?,?,?,?,?)", ["The project is reopened", "Hi dear colleague. Unfortunately
                                this project is reopened.", $article->user_id, $key, Carbon::now()]);
        }

        return "The article is restored";

    }

    public function invitationResponse($articleID, $userID, $parameter)
    {
        $article = DB::select("SELECT contributors_ref_id, waiting_contributors_ref_id, rejected_contributors_ref_id FROM articles WHERE article_id = ?", [$articleID]);
        $contributors = !empty($article[0]->contributors_ref_id) ? json_decode($article[0]->contributors_ref_id, true) : [];
        $waitingContributors = !empty($article[0]->waiting_contributors_ref_id) ? explode(',', $article[0]->waiting_contributors_ref_id) : [];
        $rejectedContributors = !empty($article[0]->rejected_contributors_ref_id) ? explode(',', $article[0]->rejected_contributors_ref_id) : [];
        /*$hasNotAlreadyDeletedFromWaitingListByAuthor or maybe the invitation list had been processed before and
        therefore deleted the contributore from the waiting list*/
        $hasNotAlreadyDeletedFromWaitingListByAuthor = false;;
        $i = 0;
        foreach ($waitingContributors as $waitingContributor) {
            if ($waitingContributor == $userID) {
                unset($waitingContributors[$i]);
                $hasNotAlreadyDeletedFromWaitingListByAuthor = true;
                break;
            }
            ++$i;
        }

        if ($hasNotAlreadyDeletedFromWaitingListByAuthor) {

            //Updating the waiting_contributors_id to the new list without that id
            $waitingContributors = implode(',', $waitingContributors);
            DB::update("UPDATE articles SET waiting_contributors_ref_id = $waitingContributors WHERE article_id = ?", [$articleID]);

            //Updating the accept/rejected_contributors_id to the new list with that id
            if ($parameter == 'accept') {
                //Determining if the user already exists in the contributors_ref_id field
                $ifUserAlreadyExists = false;
                foreach ($contributors as $key => $value)
                    if ($key == $userID)
                        $ifUserAlreadyExists = true;

                //**************Define an article for the user that has accepted the invitaion
                $now = Carbon::now();
                //19 character unique article_code
                $articleCode = random_int(100000000000000000, 9111111111111111111);

                DB::insert("INSERT INTO articles (article_code, user_ref_id, parent_ref_id, is_last_revision, status, created_at) VALUES(?,?,?,?,?,?)", [$articleCode, $userID, $articleID, 1, 'pending', $now]);
                $definedArticleID = DB::getPdo()->lastInsertId();

                //**************Insert userID/articleID in the contributors_ref_id field
                if (!$ifUserAlreadyExists)
                    $contributors[$userID] = $definedArticleID;
                $contributors = json_encode($contributors);
                DB::update("UPDATE articles SET contributors_ref_id = $contributors WHERE article_id = ?", [$articleID]);

            } else {
                if (array_search($userID, $rejectedContributors))
                    $rejectedContributors[] = $userID;
                $rejectedContributors = implode(',', $rejectedContributors);
                DB::update("UPDATE articles SET rejected_contributors_ref_id = $rejectedContributors  WHERE article_id = ?", [$articleID]);
            }

        }
        if ($hasNotAlreadyDeletedFromWaitingListByAuthor)
            return "The clicked invitation link is successfully processed!";
        else
            return "This link is no longer working, maybe you have used it before or the author has deleted you from the contributors' list!";

    }

    public function deleteContributor($request)
    {
        //We can only delete the rejected and waiting contributors.
        $article = DB::select("SELECT * FROM articles WHERE article_id = ?", [$request['articleID']]);

        $waitingContributors = explode(',', $article[0]->waiting_contributors_ref_id);
        $rejectedContributors = explode(',', $article[0]->rejected_contributors_ref_id);

        foreach ($request['contributors'] as $contributor) {
            if ($contributor['isWaiting']) {
                $contributorID = array($contributor['contributorID']);
                $waitingContributors = implode(',', array_diff($waitingContributors, $contributorID));

                DB::update("UPDATE articles SET waiting_contributors_ref_id = ? WHERE article_id = ?", [$waitingContributors, $request['articleID']]);

                //Event for deleting the invitation link that has been sent
                event(new DeleteWaitingContributorEvent($article[0]->article_id, $contributor['contributorID'], json_decode($article[0]->invitation_messages_ref_id, true)));

            } else {
                $contributorID = array($contributor['contributorID']);
                $rejectedContributors = implode(',', array_diff($rejectedContributors, $contributorID));

                DB::update("UPDATE articles SET rejected_contributors_ref_id = ? WHERE article_id = ?", [$rejectedContributors, $request['articleID']]);

                //The invitation is seen and responded by the user so deleting the invitation is pointless.
            }

        }

        return "The operation was successful!";
    }

}
