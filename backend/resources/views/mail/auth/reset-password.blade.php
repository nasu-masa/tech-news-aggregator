<x-mail::message>
# パスワードのリセット

{{ $user->name }} 様

パスワードリセットのリクエストを受け付けました。

以下のボタンをクリックして、パスワードをリセットしてください。

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:24px 0;">
<tr>
<td align="center">
<a href="{{ $resetUrl }}" style="display:inline-block;background-color:#15803d;color:#ffffff;font-family:system-ui,'Segoe UI',Roboto,sans-serif;font-size:15px;font-weight:500;text-decoration:none;border-radius:6px;padding:12px 28px;">パスワードをリセットする</a>
</td>
</tr>
</table>

このリンクの有効期限は {{ $expire }} 分です。

このリクエストに心当たりがない場合は、このメールを無視してください。

よろしくお願いいたします。
{{ config('app.name') }}
</x-mail::message>
