<?php

namespace App\Listeners;

use App\Events\StoreArticleEvent;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class SendContributorInvitationMessage
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle(StoreArticleEvent $event)
    {
        $messages = $event->messages;
        $article = $event->article[0];
        $from = $event->article[0]->user_ref_id;
        $now = Carbon::now();
        $invitationMessagesIDs = [];

        $defaultMessage = DB::select("SELECT * FROM default_messages WHERE type = 'invitation_message'");

        foreach ($messages as $message) {
            $to = $message['contributorID'];

            /*
             * Only the new contributors(in ArticleController@update) should be sent if we have any
             * new contributors!
             */
            if (!empty($event->newContributors))
                if (!array_search($to, $event->newContributors))
                    continue;

            $sentContributors[] = $to;

            $acceptLink = URL::signedRoute('invitationResponse', ['articleID' => $event->article[0]->article_id, 'userID' => $to, 'parameter' => 'accept']);
            $rejectLink = URL::signedRoute('invitationResponse', ['articleID' => $event->article[0]->article_id, 'userID' => $to, 'parameter' => 'reject']);

            //Returns undefined array key 0 if the default_messages table's correspond record is empty
            $title = !empty($message['title']) ? $message['title'] : $defaultMessage[0]->title;
            $body = !empty($message['body']) ? $message['body'] : $defaultMessage[0]->body;
            $body .= "\r\n Accept : " . $acceptLink . "\r\n Reject: " . $rejectLink;

            DB::insert("INSERT INTO messages (title, body, from_ref_id, to_ref_id, status, created_at)
                 VALUES(?,?,?,?,?,?)", [$title, $body, $from, $to, 'received', $now]);

            $invitationMessagesIDs[$to] = DB::getPdo()->lastInsertID();

        }

        //1- Send message to the contributors that didn't have any custom message in the input request
        /*
         * Only the new contributors(in ArticleController@update) should be sent if we have any
         * new contributors!
         */
        $to = !empty($event->newContributors) ? $event->newContributors : explode(',', $event->article[0]->waiting_contributors_ref_id);
        $to = array_diff($to, $sentContributors);
        foreach ($to as $value) {
            $acceptLink = URL::signedRoute('invitationResponse', ['articleID' => $event->article[0]->article_id, 'userID' => $value, 'parameter' => 'accept']);
            $rejectLink = URL::signedRoute('invitationResponse', ['articleID' => $event->article[0]->article_id, 'userID' => $value, 'parameter' => 'reject']);

            $title = $defaultMessage[0]->title;
            $body = $defaultMessage[0]->body . "\r\n Accept : " . $acceptLink . "\r\n Reject: " . $rejectLink;

            DB::insert("INSERT INTO messages (title, body, from_ref_id, to_ref_id, status, created_at)
                 VALUES(?,?,?,?,?,?)", [$title, $body, $from, $value, 'received', $now]);

            $invitationMessagesIDs[$value] = DB::getPdo()->lastInsertID();
        }

        $invitationMessagesIDs = json_encode($invitationMessagesIDs);
        DB::update("UPDATE articles SET invitation_messages_ref_id = ? WHERE article_id = ?", [$invitationMessagesIDs, $article->article_id]);


        if ($event->ifStopTheNextListener)
            return false;

    }
}
