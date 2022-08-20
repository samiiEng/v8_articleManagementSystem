<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Validation\Rule;

class FilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(Route $route, Request $request)
    {
        //Different roles for entries based on the method that's used.

        if ($route->getActionMethod() == "filterUsernamesByDepartments")
            return [
                "department_id" => "required|string",
            ];

        else if ($route->getActionMethod() == "filterCategoriesByDepartments")
            return [
                "department_id" => "required|string",
            ];

        else if ($route->getActionMethod() == "filterArticlesByCategoriesDepartments")
            return [
                "category_department_id" => "required|string",
            ];
    }
}
