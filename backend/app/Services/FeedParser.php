<?php

namespace App\Services;

use Laminas\Feed\Reader\Reader;
use DateTimeZone;

class FeedParser
{
    public function parse(string $xml): array
    {
        $feed = Reader::importString($xml);
        $articles = [];

        foreach ($feed as $entry) {
            $title = trim((string) $entry->getTitle());
            $url = trim((string) $entry->getLink());

            if (
                $title === ''
                || filter_var($url, FILTER_VALIDATE_URL) === false
                || ! in_array(parse_url($url, PHP_URL_SCHEME), ['https'], true)
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
