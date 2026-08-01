<x-mail::message>
# メールアドレスの認証

{{ $user->name }} 様

ご登録ありがとうございます。

以下のボタンをクリックして、メールアドレスの認証を完了してください。

<x-mail::button :url="$verificationUrl">
メールアドレスを認証する
</x-mail::button>

この登録に心当たりがない場合は、このメールを無視してください。

よろしくお願いいたします。
{{ config('app.name') }}
</x-mail::message>