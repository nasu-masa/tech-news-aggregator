<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'feed_url' => ['required', 'string', 'url', 'regex:/^https:\/\//i', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'feed_url.required' => 'URLを入力してください。',
            'feed_url.url' => '有効なURLを指定してください。',
            'feed_url.regex' => 'HTTPSのURLを指定してください。',
            'feed_url.max' => 'URLが長すぎます。',
        ];
    }
}
