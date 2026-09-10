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
        // Keep this class name so already queued title jobs remain compatible.
        if ($this->article->exists) {
            $this->article->refresh();
        }

        $failure = null;

        foreach (['title', 'summary'] as $field) {
            $text = $this->article->{$field};
            $translatedField = 'translated_'.$field;

            if ($this->article->{$translatedField} !== null
                || $text === null || trim($text) === '' || $this->isJapanese($text)) {
                continue;
            }

            try {
                $translated = $translator->translate($text);
                $this->article->update([$translatedField => $translated]);
            } catch (\Throwable $exception) {
                // Save the other field even on failure; retries skip saved translations.
                $failure ??= $exception;
            }
        }

        if ($failure !== null) {
            throw $failure;
        }
    }

    private function isJapanese(string $text): bool
    {
        return (bool) preg_match('/[\p{Hiragana}\p{Katakana}]/u', $text);
    }
}
