<?php

namespace App\Repositories\Filter;


use App\Exceptions\FilterFormatException;
use App\Models\Department;
use http\Exception;
use Illuminate\Support\Facades\DB;
use function PHPUnit\Framework\throwException;

class FilterRepository
{

    /******** CONVENTION ********
     * $filters ==> @array ==> ['outputTable', 'outputFields' = [*], hasDistinct, [field, operator, value, next], [REPEAT PREVIOUS ARRAY], ....]
     */


    /*
     * This function has no prior filter and just retrieves all departments
     */
    public
    function retrieveDepartments()
    {
//        $departmentsParents = Department::where('department_ref_id', null)->get();
        $departmentsParents = DB::select('SELECT * FROM departments WHERE department_ref_id IS NULL');
        $departments = [];
        $i = 0;
        foreach ($departmentsParents as $departmentsParent) {
            $parentID = $departmentsParent->department_id;
            $departmentsChildren = DB::select('SELECT * FROM departments WHERE department_ref_id = ' . $parentID);
            $i++;
            $departments[$i] = [$departmentsParent, $departmentsChildren];
        }
        return $departments;

    }


    /*
     * CONVENTION ==> $filters ==> {"department_id" : [1,2,3,...]}
     * @return ==>{
     *  "departmet_ref_id" => [
     *          {department info},
     *          {users info},
     *              .
     *              .
     *              .
     *       ]
     *  }
     */
    public function filterUsernamesByDepartments($filters)
    {
        $departmentsIDs = explode(",", $filters['department_id']);

        //defining the binding
        $length = count($departmentsIDs);
        $binding = [];

        //defining the conditions and bindings
        $conditions = "";
        $i = 0;
        foreach ($departmentsIDs as $departmentsID) {
            ++$i;
            if ($i != $length) {
                $conditions .= "department_ref_id = ? OR ";
                $j = $i - 1;
                $binding[] = $departmentsIDs[$j];
            } else {
                $conditions .= "department_ref_id = ?";
                $j = $i - 1;
                $binding[] = $departmentsIDs[$j];
            }
        }


        $users = DB::select("SELECT * FROM users WHERE $conditions", $binding);

        foreach ($departmentsIDs as $departmentsID) {
            $flag = true;
            foreach ($users as $user) {
                if ($departmentsID == $user->department_ref_id) {
                    //Get departments' information just once
                    if ($flag) {
                        $department = DB::select('SELECT * FROM departments WHERE department_id = ?', [$departmentsID]);
                        $departmentID = $department[0]->department_id;
                        $departmentName = $department[0]->name;
                        $departmentEnglishName = $department[0]->english_name;
                        $departmentParentID = $department[0]->department_ref_id;
                        if (!empty($departmentParentID)) {
                            $departmentParent = DB::select('SELECT * FROM departments WHERE department_id = ?', [$departmentParentID]);
                            $departmentParentName = $departmentParent[0]->name;
                            $departmentParentEnglishName = $departmentParent[0]->english_name;
                        }

                        $usersByDepartments[$departmentsID][] = array(
                            "departmentID" => $departmentID,
                            "departmentName" => $departmentName,
                            "departmentEnglishName" => $departmentEnglishName,
                            "departmentParentID" => $departmentParentID,
                            "departmentParentName" => $departmentParentName ?? null,
                            "departmentParentEnglishName" => $departmentParentEnglishName ?? null
                        );
                    }

                    //Get users' information
                    $username = $user->username;
                    $avatarImagePath = $user->avatar_image_path;
                    $firstName = $user->first_name;
                    $lastName = $user->last_name;

                    $usersByDepartments[$departmentsID][] = array(
                        "username" => $username,
                        "avatarImagePath" => $avatarImagePath,
                        "firstName" => $firstName,
                        "lastName" => $lastName
                    );
                }
            }
        }
        return $usersByDepartments;

    }


    /*
     * CONVENTION ==> $filters ==> {"department_id" : [1,2,3,...]}
     * @returns ==>
     * {
     *  "departmet_ref_id" => [
     *          {department info},
     *          {category info},
     *              .
     *              .
     *              .
     *       ]
     *  }
     */
    public function filterCategoriesByDepartments($filters)
    {
        $departmentsIDs = explode(',', $filters['department_id']);

        //Make conditions and bindings
        $bindings = [];
        $conditions = "";
        $length = count($departmentsIDs);
        $i = 0;
        foreach ($departmentsIDs as $departmentsID) {
            ++$i;
            if ($i != $length) {
                $conditions .= "department_ref_id = ? OR ";
                $j = $i - 1;
                $bindings[] = $departmentsIDs[$j];
            } else {
                $conditions .= "department_ref_id = ?";
                $j = $i - 1;
                $bindings[] = $departmentsIDs[$j];
            }
        }

        $categoriesDepartments = DB::select("SELECT * FROM category_department WHERE $conditions", $bindings);
        $categoriesBasedDepartments = [];
        foreach ($departmentsIDs as $departmentsID) {
            $flag = true;
            foreach ($categoriesDepartments as $categoriesDepartment) {
                /*
                 * Goal:  getting all records corresponding to a department_id into one index
                 * 1- getting the department info and category info like english name and name as well
                 * and create an associative array out of them.
                 */
                if ($departmentsID == $categoriesDepartment->department_ref_id) {
                    $categoryDepartmentID = $categoriesDepartment->category_department_id;

                    //Getting departments full info but just once
                    if ($flag) {
                        $flag = false;
                        $department = DB::select("SELECT * FROM departments WHERE department_id = ?", [$departmentsID]);

                        $departmentID = $department[0]->department_id;
                        $departmentName = $department[0]->name;
                        $departmentEnglishName = $department[0]->english_name;
                        $departmentParentID = $department[0]->department_ref_id;
                        if (!empty($departmentParent)) {
                            $departmentParent = DB::select("SELECT * FROM departments WHERE department_id = ?", [$departmentParentID]);
                            $departmentParentName = $departmentParent[0]->name;
                            $departmentParentEnglishName = $departmentParent[0]->english_name;
                        }

                        $categoriesBasedDepartments["$departmentsID"][] = array(
                            "departmentID" => $departmentID,
                            "departmentName" => $departmentName,
                            "departmentEnglishName" => $departmentEnglishName,
                            "departmentParentID" => $departmentParentID ?? null,
                            "departmentParentName" => $departmentParentName ?? null,
                            "departmentParentEnglishName" => $departmentParentEnglishName ?? null,
                        );
                    }

                    //Getting categories full info

                    $category = DB::select("SELECT * FROM categories WHERE category_id = ?", [$categoriesDepartment->category_ref_id]);
                    $categoryID = $category[0]->category_id;
                    $categoryName = $category[0]->name;
                    $categoryEnglishName = $category[0]->english_name;
                    $categoryParentID = $category[0]->category_ref_id;

                    if (!empty($categoryParentID)) {
                        $categoryParent = DB::select("SELECT * FROM categories WHERE category_id = ?", [$categoryParentID]);;
                        $categoryParentName = $categoryParent[0]->name;
                        $categoryParentEnglishName = $categoryParent[0]->english_name;
                    }


                    $categoriesBasedDepartments["$departmentsID"][] = array(
                        "categoryDepartmentID" => $categoryDepartmentID,
                        "categoryID" => $categoryID,
                        "categoryName" => $categoryName,
                        "categoryEnglishName" => $categoryEnglishName,
                        "categoryParentID" => $categoryParentID ?? null,
                        "categoryParentName" => $categoryParentName ?? null,
                        "categoryParentEnglishName" => $categoryParentEnglishName ?? null
                    );
                }
            }
        }

        return $categoriesBasedDepartments;

    }


    /*
     * CONVENTION ==> $filters ==> {"category_department_id" : [1,2,3,...]}
     * @return ==> {
     *  "departmet_ref_id" => [
     *          {article info},
     *              .
     *              .
     *              .
     *       ]
     *  }
     */
    public function filterArticlesByCategoriesDepartments($filters)
    {
        $filters = $this->bubble_Sort(explode(",", $filters['category_department_id']));
        $finalArticles = [];

        $articles = DB::select("SELECT article_id, article_code, title,  user_ref_id, tag_ref_id, category_department_ref_id FROM articles WHERE is_last_revision = 1 AND publish_date IS NOT NULL");
        foreach ($articles as $article) {
            $categoriesDepartmentsIDs = $this->bubble_Sort(explode(',', $article->category_department_ref_id));
            if ($filters == $categoriesDepartmentsIDs) {
                $tags = "";
                $categories = "";
                $tagsList = explode(",", $article->tag_ref_id);
                $i = 0;
                $length = count($tagsList);
                foreach ($tagsList as $tag) {
                    ++$i;
                    $name = DB::select("SELECT name FROM tags WHERE tag_id = $tag")[0]->name ?? null;
                    if (!empty($name)) {
                        $j = $i - 1;
                        if ($j != $length)
                            $tags .= $name . ", ";
                        else
                            $tags .= $name;
                    } else
                        continue;

                }

                $categoriesList = explode(',', $article->category_department_ref_id);
                $i = 0;
                $length = count($tagsList);
                foreach ($categoriesList as $category) {
                    ++$i;
                    $categoryID = DB::select("SELECT category_ref_id FROM category_department WHERE category_department_id = $category")[0]->category_ref_id ?? null;
                    if (!empty($categoryID)) {
                        $name = DB::select("SELECT name FROM categories WHERE category_id = $categoryID")[0]->name ?? null;
                        if (!empty($name)) {
                            $j = $i - 1;
                            if ($j != $length)
                                $categories .= $name . ", ";
                            else
                                $categories .= $name;
                        } else {
                            continue;
                        }
                    } else {
                        continue;
                    }

                }


                $users = DB::select("SELECT username, first_name, last_name FROM users WHERE user_id = $article->user_ref_id");

                $finalArticles[] = array(
                    "articleID" => $article->article_id,
                    "articleCode" => $article->article_code,
                    "title" => $article->title,
                    "userID" => $article->user_ref_id,
                    "username" => $users[0]->username,
                    "fistName" => $users[0]->first_name,
                    "lastName" => $users[0]->last_name,
                    "categories" => $categories,
                    "tags" => $tags,
                );
            }
        }
        return $finalArticles;

    }

    public function bubble_Sort($array)
    {
        do {
            $swapped = false;
            for ($i = 0, $c = count($array) - 1; $i < $c; $i++) {
                if ($array[$i] > $array[$i + 1]) {
                    list($array[$i + 1], $array[$i]) =
                        array($array[$i], $array[$i + 1]);
                    $swapped = true;
                }
            }
        } while ($swapped);
        return $array;
    }

}
