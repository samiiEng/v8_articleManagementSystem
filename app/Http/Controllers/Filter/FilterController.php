<?php

namespace App\Http\Controllers\Filter;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterRequest;
use App\Models\Department;
use App\Repositories\Filter\FilterRepository;
use Illuminate\Http\Request;


class FilterController extends Controller
{

    /******** CONVENTION *********
     * $filters ==> @array ==> ['outputTable', 'outputFields' = [*], hasDistinct, [field, operator, value, next], [REPEAT PREVIOUS ARRAY], ....]
     */

    /*
     * This function has no prior filter and just retrieves all departments
     */
    public
    function retrieveDepartments()
    {
        //I suppose that if I type hint this then it makes me to enter a parameter for this argument.
        $filterRepository = new FilterRepository();
        $departments = $filterRepository->retrieveDepartments();
        return $departments;
    }


    public function filterUsernamesByDepartments(FilterRequest $filterRequest, FilterRepository $filterRepository)
    {
        $validated = $filterRequest->safe()->only('department_id');
        $results = $filterRepository->filterUsernamesByDepartments($validated);
        $results = structuredJson($results);
        return response()->json($results[0], $results[1], $results[2], $results[3]);
    }

    public function filterCategoriesByDepartments(FilterRequest $filterRequest, FilterRepository $filterRepository)
    {

        $validated = $filterRequest->safe()->only('department_id');
        $results = structuredJson($filterRepository->filterCategoriesByDepartments($validated));
        return response()->json($results[0], $results[1], $results[2], $results[3]);

    }

    public function filterArticlesByCategoriesDepartments(FilterRequest $filterRequest, FilterRepository $filterRepository)
    {
        $validated = $filterRequest->safe()->only('category_department_id');
        $results = structuredJson($filterRepository->filterArticlesByCategoriesDepartments($validated));
        return response()->json($results[0], $results[1], $results[2], $results[3]);
    }
}
