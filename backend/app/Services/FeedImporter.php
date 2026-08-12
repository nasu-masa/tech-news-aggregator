<?php

namespace App\Services;

use App\Models\Source;
use Illuminate\Support\Facades\Cache;

class FeedImporter
{
    public function __construct(
        private readonly FeedFetcher $feedFetcher,
        private readonly ArticleSaver $articleSaver,
    ) {}

    public function import(Source $source): void
    {
        Cache::lock("feed-import:{$source->id}", 60)->get(function () use ($source) {
            try {
                $articles = $this->feedFetcher->fetch(
                    $source->feed_url
                );

                $this->articleSaver->save(
                    $source,
                    $articles,
                );

                $source->update([
                    'last_success_at' => now(),
                    'last_error_at' => null,
                    'last_error_message' => null,
                ]);
            } catch (\Throwable $exception) {
                $source->update([
                    'last_error_at' => now(),
                    'last_error_message' => $exception->getMessage(),
                ]);

                throw $exception;
            }
        });
    }
}
