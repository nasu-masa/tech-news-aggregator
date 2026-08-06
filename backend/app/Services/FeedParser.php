<?php

namespace App\Services;

use DateTimeZone;
use Laminas\Feed\Reader\Reader;

class FeedParser
{
    public function parse(string $xml): array
    {
        $feed = Reader::importString($xml);
        $articles = [];

        foreach ($feed as $entry) {
            $title = trim((string) $entry->getTitle());
            $url = trim((string) $entry->getLink());
            $scheme = parse_url($url, PHP_URL_SCHEME);

            if (
                $title === ''
                || filter_var($url, FILTER_VALIDATE_URL) === false
                || ! is_string($scheme)
                || strtolower($scheme) !== 'https'
            ) {
                continue;
            }

            $publishedAt = $entry->getDateCreated()
                ?? $entry->getDateModified();

            $articles[] = [
                'title' => $title,
                'url' => $url,
                'description' => $entry->getDescription(),
                'published_at' => $publishedAt
                    ? $publishedAt
                        ->setTimezone(new DateTimeZone('UTC'))
                        ->format('Y-m-d H:i:s')
                    : null,
            ];
        }

        return $articles;
    }
}
