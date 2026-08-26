<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DeepLTranslator
{
    public function translate(string $text, string $targetLang = 'JA'): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'DeepL-Auth-Key '.config('services.deepl.api_key'),
        ])->post(config('services.deepl.api_url'), [
            'text' => [$text],
            'target_lang' => $targetLang,
        ]);

        $response->throw();

        return $response->json('translations.0.text');
    }
}
