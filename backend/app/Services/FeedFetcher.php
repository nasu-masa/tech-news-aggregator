<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FeedFetcher
{
    public function __construct(
        private readonly FeedParser $feedParser
    ) {}

    public function fetch(string $url): array
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (
            filter_var($url, FILTER_VALIDATE_URL) === false
            || ! is_string($scheme)
            || strtolower($scheme) !== 'https'
        ) {
            throw new \InvalidArgumentException(
                'フィードURLは有効なHTTPS URLを指定してください。'
            );
        }

        $xml = Http::timeout(10)
            ->retry(2, 500)
            ->get($url)
            ->throw()
            ->body();

        return $this->feedParser->parse($xml);
    }
}
