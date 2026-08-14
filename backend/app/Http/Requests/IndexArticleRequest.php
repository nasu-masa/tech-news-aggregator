<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword' => ['sometimes', 'string'],
            'source_id' => ['sometimes', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'keyword.string' => '検索キーワードの形式が正しくありません。',
            'source_id.integer' => 'ニュースソースの指定が正しくありません。',
        ];
    }
}
