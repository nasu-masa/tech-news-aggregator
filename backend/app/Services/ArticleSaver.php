<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Source;

class ArticleSaver
{
    public function save(Source $source, array $articles): void
    {
        if (empty($articles)) {
            return;
        }

        $rows = array_map(fn (array $article): array => [
            'source_id' => $source->id,
            'url' => $article['url'],
            'title' => $article['title'],
            'summary' => $article['summary'],
            'published_at' => $article['published_at'],
        ], $articles);

        Article::upsert(
            $rows,
            ['url'],
            ['title', 'summary', 'published_at'],
        );
    }
}
