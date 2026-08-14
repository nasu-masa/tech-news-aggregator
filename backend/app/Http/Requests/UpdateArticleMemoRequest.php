<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleMemoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'memo' => ['present', 'nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'memo.present' => 'メモの内容を入力してください。',
            'memo.string' => 'メモは文字で入力してください。',
            'memo.max' => 'メモは5000文字以内で入力してください。',
        ];
    }
}
