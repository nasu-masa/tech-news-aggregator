<?php

namespace App\Jobs;

use App\Models\Article;
use App\Services\DeepLTranslator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class TranslateArticleTitleJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public readonly Article $article,
    ) {}

    public function handle(DeepLTranslator $translator): void
    {
        if ($this->article->translated_title !== null) {
            return;
        }

        if ($this->isJapanese($this->article->title)) {
            return;
        }

        $translated = $translator->translate($this->article->title);

        $this->article->update(['translated_title' => $translated]);
    }

    private function isJapanese(string $text): bool
    {
        return (bool) preg_match('/[\p{Hiragana}\p{Katakana}]/u', $text);
    }
}
