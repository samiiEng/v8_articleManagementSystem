<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;

class UpdateUserRequest extends FormRequest
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
    public function rules(Route $route)
    {
        $method = $route->getActionMethod();
        if ($method == "update") {
            return [

            ];
        } elseif ($method == "activateUsers") {
            return [
                "userID" => "required|bigInteger",
                "role" => "required|string",
            ];
        }
    }
}
