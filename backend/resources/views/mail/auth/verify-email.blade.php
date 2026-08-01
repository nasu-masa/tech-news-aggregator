<x-mail::message>
# メールアドレスの認証

{{ $user->name }} 様

ご登録ありがとうございます。

以下のボタンをクリックして、メールアドレスの認証を完了してください。

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:24px 0;">
<tr>
<td align="center">
<a href="{{ $verificationUrl }}" style="display:inline-block;background-color:#15803d;color:#ffffff;font-family:system-ui,'Segoe UI',Roboto,sans-serif;font-size:15px;font-weight:500;text-decoration:none;border-radius:6px;padding:12px 28px;">メールアドレスを認証する</a>
</td>
</tr>
</table>

この登録に心当たりがない場合は、このメールを無視してください。

よろしくお願いいたします。
{{ config('app.name') }}
</x-mail::message>