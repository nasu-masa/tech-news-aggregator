<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_favorite' => ['boolean', 'required_without_all:is_read,is_read_later'],
            'is_read' => ['boolean', 'required_without_all:is_favorite,is_read_later'],
            'is_read_later' => ['boolean', 'required_without_all:is_favorite,is_read'],
        ];
    }

    public function messages(): array
    {
        return [
            'is_favorite.boolean' => 'お気に入りの状態が正しくありません。',
            'is_read.boolean' => '既読状態が正しくありません。',
            'is_read_later.boolean' => '「あとで読む」の状態が正しくありません。',
        ];
    }
}
