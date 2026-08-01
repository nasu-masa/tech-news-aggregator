<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute を承認してください。',
    'accepted_if' => ':other が :value の場合、:attribute を承認してください。',
    'active_url' => ':attribute に有効なURLを入力してください。',
    'after' => ':attribute には :date より後の日付を入力してください。',
    'after_or_equal' => ':attribute には :date 以降の日付を入力してください。',
    'alpha' => ':attribute には英字のみ使用できます。',
    'alpha_dash' => ':attribute には英字、数字、ハイフン、アンダースコアのみ使用できます。',
    'alpha_num' => ':attribute には英字と数字のみ使用できます。',
    'any_of' => ':attribute の値が無効です。',
    'array' => ':attribute には配列を指定してください。',
    'ascii' => ':attribute には半角英数字と記号のみ使用できます。',
    'before' => ':attribute には :date より前の日付を入力してください。',
    'before_or_equal' => ':attribute には :date 以前の日付を入力してください。',
    'between' => [
        'array' => ':attribute の項目数は :min 〜 :max 件にしてください。',
        'file' => ':attribute のファイルサイズは :min 〜 :max キロバイトにしてください。',
        'numeric' => ':attribute の値は :min 〜 :max の範囲で入力してください。',
        'string' => ':attribute は :min 〜 :max 文字で入力してください。',
    ],
    'boolean' => ':attribute には true か false を指定してください。',
    'can' => ':attribute に許可されていない値が含まれています。',
    'confirmed' => ':attribute と確認用の値が一致しません。',
    'contains' => ':attribute に必要な値が含まれていません。',
    'current_password' => 'パスワードが正しくありません。',
    'date' => ':attribute に有効な日付を入力してください。',
    'date_equals' => ':attribute には :date と同じ日付を入力してください。',
    'date_format' => ':attribute は :format 形式で入力してください。',
    'decimal' => ':attribute には小数点以下 :decimal 桁の値を入力してください。',
    'declined' => ':attribute を拒否してください。',
    'declined_if' => ':other が :value の場合、:attribute を拒否してください。',
    'different' => ':attribute と :other には異なる値を入力してください。',
    'digits' => ':attribute は :digits 桁で入力してください。',
    'digits_between' => ':attribute は :min 〜 :max 桁で入力してください。',
    'dimensions' => ':attribute の画像サイズが正しくありません。',
    'distinct' => ':attribute に重複した値があります。',
    'doesnt_contain' => ':attribute には次の値を含めることができません：:values。',
    'doesnt_end_with' => ':attribute の末尾を次のいずれかにすることはできません：:values。',
    'doesnt_start_with' => ':attribute の先頭を次のいずれかにすることはできません：:values。',
    'email' => ':attribute に有効なメールアドレスを入力してください。',
    'encoding' => ':attribute は :encoding エンコーディングで入力してください。',
    'ends_with' => ':attribute の末尾は次のいずれかにしてください：:values。',
    'enum' => '選択された :attribute の値が無効です。',
    'exists' => '選択された :attribute の値が無効です。',
    'extensions' => ':attribute には次のいずれかの拡張子のファイルを指定してください：:values。',
    'file' => ':attribute にはファイルを指定してください。',
    'filled' => ':attribute に値を入力してください。',
    'gt' => [
        'array' => ':attribute の項目数は :value 件より多くしてください。',
        'file' => ':attribute のファイルサイズは :value キロバイトより大きくしてください。',
        'numeric' => ':attribute の値は :value より大きい値を入力してください。',
        'string' => ':attribute は :value 文字より長く入力してください。',
    ],
    'gte' => [
        'array' => ':attribute の項目数は :value 件以上にしてください。',
        'file' => ':attribute のファイルサイズは :value キロバイト以上にしてください。',
        'numeric' => ':attribute の値は :value 以上を入力してください。',
        'string' => ':attribute は :value 文字以上で入力してください。',
    ],
    'hex_color' => ':attribute に有効な16進数カラーコードを入力してください。',
    'image' => ':attribute には画像ファイルを指定してください。',
    'in' => '選択された :attribute の値が無効です。',
    'in_array' => ':attribute の値は :other に含まれている必要があります。',
    'in_array_keys' => ':attribute には次のキーの少なくとも1つを含める必要があります：:values。',
    'integer' => ':attribute には整数を入力してください。',
    'ip' => ':attribute に有効なIPアドレスを入力してください。',
    'ipv4' => ':attribute に有効なIPv4アドレスを入力してください。',
    'ipv6' => ':attribute に有効なIPv6アドレスを入力してください。',
    'json' => ':attribute に有効なJSON文字列を入力してください。',
    'list' => ':attribute にはリストを指定してください。',
    'lowercase' => ':attribute は小文字で入力してください。',
    'lt' => [
        'array' => ':attribute の項目数は :value 件より少なくしてください。',
        'file' => ':attribute のファイルサイズは :value キロバイト未満にしてください。',
        'numeric' => ':attribute の値は :value より小さい値を入力してください。',
        'string' => ':attribute は :value 文字より短く入力してください。',
    ],
    'lte' => [
        'array' => ':attribute の項目数は :value 件以下にしてください。',
        'file' => ':attribute のファイルサイズは :value キロバイト以下にしてください。',
        'numeric' => ':attribute の値は :value 以下を入力してください。',
        'string' => ':attribute は :value 文字以下で入力してください。',
    ],
    'mac_address' => ':attribute に有効なMACアドレスを入力してください。',
    'max' => [
        'array' => ':attribute の項目数は :max 件以下にしてください。',
        'file' => ':attribute のファイルサイズは :max キロバイト以下にしてください。',
        'numeric' => ':attribute の値は :max 以下を入力してください。',
        'string' => ':attribute は :max 文字以下で入力してください。',
    ],
    'max_digits' => ':attribute は :max 桁以下で入力してください。',
    'mimes' => ':attribute には次のファイル形式を指定してください：:values。',
    'mimetypes' => ':attribute には次のファイル形式を指定してください：:values。',
    'min' => [
        'array' => ':attribute の項目数は :min 件以上にしてください。',
        'file' => ':attribute のファイルサイズは :min キロバイト以上にしてください。',
        'numeric' => ':attribute の値は :min 以上を入力してください。',
        'string' => ':attribute は :min 文字以上で入力してください。',
    ],
    'min_digits' => ':attribute は :min 桁以上で入力してください。',
    'missing' => ':attribute は送信しないでください。',
    'missing_if' => ':other が :value の場合、:attribute は送信しないでください。',
    'missing_unless' => ':other が :value でない場合、:attribute は送信しないでください。',
    'missing_with' => ':values が存在する場合、:attribute は送信しないでください。',
    'missing_with_all' => ':values がすべて存在する場合、:attribute は送信しないでください。',
    'multiple_of' => ':attribute には :value の倍数を入力してください。',
    'not_in' => '選択された :attribute の値が無効です。',
    'not_regex' => ':attribute の形式が正しくありません。',
    'numeric' => ':attribute には数値を入力してください。',
    'password' => [
        'letters' => ':attribute には英字を1文字以上含めてください。',
        'mixed' => ':attribute には大文字と小文字をそれぞれ1文字以上含めてください。',
        'numbers' => ':attribute には数字を1文字以上含めてください。',
        'symbols' => ':attribute には記号を1文字以上含めてください。',
        'uncompromised' => '入力された :attribute はデータ漏洩で確認されています。別の :attribute を設定してください。',
    ],
    'present' => ':attribute を含めてください。',
    'present_if' => ':other が :value の場合、:attribute を含めてください。',
    'present_unless' => ':other が :value でない場合、:attribute を含めてください。',
    'present_with' => ':values が存在する場合、:attribute を含めてください。',
    'present_with_all' => ':values がすべて存在する場合、:attribute を含めてください。',
    'prohibited' => ':attribute は使用できません。',
    'prohibited_if' => ':other が :value の場合、:attribute は使用できません。',
    'prohibited_if_accepted' => ':other が承認されている場合、:attribute は使用できません。',
    'prohibited_if_declined' => ':other が拒否されている場合、:attribute は使用できません。',
    'prohibited_unless' => ':other が :values に含まれていない場合、:attribute は使用できません。',
    'prohibits' => ':attribute が存在する場合、:other は使用できません。',
    'regex' => ':attribute の形式が正しくありません。',
    'required' => ':attribute を入力してください。',
    'required_array_keys' => ':attribute には次の項目を含めてください：:values。',
    'required_if' => ':other が :value の場合、:attribute を入力してください。',
    'required_if_accepted' => ':other が承認されている場合、:attribute を入力してください。',
    'required_if_declined' => ':other が拒否されている場合、:attribute を入力してください。',
    'required_unless' => ':other が :values に含まれていない場合、:attribute を入力してください。',
    'required_with' => ':values が存在する場合、:attribute を入力してください。',
    'required_with_all' => ':values がすべて存在する場合、:attribute を入力してください。',
    'required_without' => ':values が存在しない場合、:attribute を入力してください。',
    'required_without_all' => ':values のいずれも存在しない場合、:attribute を入力してください。',
    'same' => ':attribute と :other を一致させてください。',
    'size' => [
        'array' => ':attribute の項目数は :size 件にしてください。',
        'file' => ':attribute のファイルサイズは :size キロバイトにしてください。',
        'numeric' => ':attribute の値は :size にしてください。',
        'string' => ':attribute は :size 文字で入力してください。',
    ],
    'starts_with' => ':attribute の先頭は次のいずれかにしてください：:values。',
    'string' => ':attribute には文字列を入力してください。',
    'timezone' => ':attribute に有効なタイムゾーンを指定してください。',
    'unique' => 'この :attribute はすでに使用されています。',
    'uploaded' => ':attribute のアップロードに失敗しました。',
    'uppercase' => ':attribute は大文字で入力してください。',
    'url' => ':attribute に有効なURLを入力してください。',
    'ulid' => ':attribute に有効なULIDを入力してください。',
    'uuid' => ':attribute に有効なUUIDを入力してください。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'name' => '名前',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'password_confirmation' => '確認用パスワード',
    ],

];
