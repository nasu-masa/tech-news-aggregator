<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'status' => ['nullable', Rule::in([
                'unread',
                'read',
                'favorite',
                'read_later',
            ])],
        ];
    }

    public function messages(): array
    {
        return [
            'keyword.string' => '検索キーワードの形式が正しくありません。',
            'source_id.integer' => 'ニュースソースの指定が正しくありません。',
            'subscribed_only.boolean' => 'ニュースソースの絞り込み条件が正しくありません。',
            'status.in' => '記事の状態には未読・既読・お気に入り・あとで見るのいずれかを指定してください。',
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
