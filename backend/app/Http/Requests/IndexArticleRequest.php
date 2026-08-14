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
            'subscribed_only' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'keyword.string' => '検索キーワードの形式が正しくありません。',
            'source_id.integer' => 'ニュースソースの指定が正しくありません。',
            'subscribed_only.boolean' => 'ニュースソースの絞り込み条件が正しくありません。',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('subscribed_only')) {
            $this->merge([
                'subscribed_only' => filter_var(
                    $this->input('subscribed_only'),
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE
                ),
            ]);
        }
    }
}
