<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Source;

class ArticleSaver
{
    public function save(Source $source, array $articles): void
    {
        foreach ($articles as $article) {
            $storedArticle = Article::firstOrNew([
                'url' => $article['url'],
            ]);

            if (! $storedArticle->exists) {
                $storedArticle->source_id = $source->id;
            }

            $storedArticle->title = $article['title'];
            $storedArticle->summary = $article['summary'];
            $storedArticle->published_at = $article['published_at'];

            $storedArticle->save();
        }
    }
}
