<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
    public function rules()
    {
        return [
            "firstName" => "required|string|max:50",
            "lastName" => "required|string|max:50",
            "nationalCode" => "required|integer",
            "username" => "required|string|max:50",
            "password" => "required|string",
            "email" => "required|email",
            "phoneNumber" => "required|integer",
            "avatar_image_path" => "required|string",
            "departmentID" => "required|integer",
            "extra" => "required|json",
            "isNormal" => "required|boolean"
        ];
    }
}

