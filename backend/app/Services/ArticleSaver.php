<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Source;
use Illuminate\Database\Eloquent\Collection;

class ArticleSaver
{
    public function save(Source $source, array $articles): Collection
    {
        if (empty($articles)) {
            return new Collection;
        }

        $rows = array_map(fn (array $article): array => [
            'source_id' => $source->id,
            'url' => $article['url'],
            'title' => $article['title'],
            'summary' => $article['summary'],
            'published_at' => $article['published_at'],
        ], $articles);

        $urls = array_column($rows, 'url');

        $existingUrls = Article::whereIn('url', $urls)
            ->pluck('url')
            ->flip()
            ->all();

        Article::upsert(
            $rows,
            ['url'],
            ['title', 'summary', 'published_at'],
        );

        $newUrls = array_values(array_filter(
            $urls,
            fn (string $url) => ! array_key_exists($url, $existingUrls),
        ));

        if (empty($newUrls)) {
            return new Collection;
        }

        return Article::whereIn('url', $newUrls)->get();
    }
}
