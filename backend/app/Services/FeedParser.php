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
                'summary' => $this->normalizeSummary($entry->getDescription()),
                'published_at' => $publishedAt
                    ? $publishedAt
                        ->setTimezone(new DateTimeZone('UTC'))
                        ->format('Y-m-d H:i:s')
                    : null,
            ];
        }

        return $articles;
    }

    private function normalizeSummary(?string $description): ?string
    {
        if ($description === null) {
            return null;
        }

        $summary = trim(strip_tags($description));

        if ($summary === '') {
            return null;
        }

        // hnrss.org uses the description for Hacker News metadata rather than
        // an article summary. Do not expose that boilerplate as the summary.
        if (
            str_contains($summary, 'Article URL:')
            && str_contains($summary, 'Comments URL:')
            && preg_match('/\bPoints:\s*\d+\b/i', $summary) === 1
            && preg_match('/#\s*Comments:\s*\d+\b/i', $summary) === 1
        ) {
            return null;
        }

        return $summary;
    }
}
