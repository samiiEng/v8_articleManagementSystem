<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;
use phpDocumentor\Reflection\Types\Nullable;

class StoreArticleRequest extends FormRequest
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
            "author" => "required|integer",
            "title" => "required|string|max:100",
            "body" => "nullable|string",
            "contributors" => "nullable|string",
            "publishedArticles" => "nullable|string",
            "categories" => "required|string",
            "tags" => "nullable|string",
            "messages.*.contributorID" => "required|integer",
            "messages.*.title" => "nullable|string|max:100",
            "messages.*.body" => "nullable|string"
        ];
    }
}
