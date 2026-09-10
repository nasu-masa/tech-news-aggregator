<?php

namespace Tests\Unit;

use App\Jobs\TranslateArticleTitleJob;
use App\Models\Article;
use App\Services\DeepLTranslator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class TranslateArticleTitleJobTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_ひらがなを含むタイトルは_deep_lを呼ばない(): void
    {
        $article = new Article(['title' => 'AIが人類を脅かす', 'translated_title' => null]);

        $translator = Mockery::mock(DeepLTranslator::class);
        $translator->shouldNotReceive('translate');

        (new TranslateArticleTitleJob($article))->handle($translator);
    }

    public function test_カタカナを含むタイトルは_deep_lを呼ばない(): void
    {
        $article = new Article(['title' => 'テクノロジーの最前線', 'translated_title' => null]);

        $translator = Mockery::mock(DeepLTranslator::class);
        $translator->shouldNotReceive('translate');

        (new TranslateArticleTitleJob($article))->handle($translator);
    }

    public function test_日本語タイトルは翻訳せず英語概要のみ翻訳する(): void
    {
        /** @var Article&MockInterface $article */
        $article = Mockery::mock(Article::class)->makePartial();
        $article->title = '最新AIの動向';
        $article->translated_title = null;
        $article->summary = 'The latest trends in AI';
        $article->translated_summary = null;
        $article->shouldReceive('update')
            ->once()
            ->with(['translated_summary' => 'AIの最新動向']);

        $translator = Mockery::mock(DeepLTranslator::class);
        $translator->shouldReceive('translate')
            ->once()
            ->with('The latest trends in AI')
            ->andReturn('AIの最新動向');

        (new TranslateArticleTitleJob($article))->handle($translator);
    }

    public function test_中国語タイトルは_deep_lを呼ぶ(): void
    {
        /** @var Article&MockInterface $article */
        $article = Mockery::mock(Article::class)->makePartial();
        $article->title = '人工智能威胁人类';
        $article->translated_title = null;
        $article->shouldReceive('update')
            ->once()
            ->with(['translated_title' => '人工知能が人類を脅かす']);

        $translator = Mockery::mock(DeepLTranslator::class);
        $translator->shouldReceive('translate')
            ->once()
            ->with('人工智能威胁人类')
            ->andReturn('人工知能が人類を脅かす');

        (new TranslateArticleTitleJob($article))->handle($translator);
    }

    public function test_英語タイトルは_deep_lを呼ぶ(): void
    {
        /** @var Article&MockInterface $article */
        $article = Mockery::mock(Article::class)->makePartial();
        $article->title = 'AI threatens humanity';
        $article->translated_title = null;
        $article->shouldReceive('update')
            ->once()
            ->with(['translated_title' => 'AIが人類を脅かす']);

        $translator = Mockery::mock(DeepLTranslator::class);
        $translator->shouldReceive('translate')
            ->once()
            ->with('AI threatens humanity')
            ->andReturn('AIが人類を脅かす');

        (new TranslateArticleTitleJob($article))->handle($translator);
    }

    public function test_translated_title設定済みは_deep_lを呼ばない(): void
    {
        $article = new Article([
            'title' => 'AI threatens humanity',
            'translated_title' => 'AIが人類を脅かす',
        ]);

        $translator = Mockery::mock(DeepLTranslator::class);
        $translator->shouldNotReceive('translate');

        (new TranslateArticleTitleJob($article))->handle($translator);
    }
}
