<?php

namespace Tests\Feature;

use App\Jobs\TranslateArticleTitleJob;
use App\Models\Article;
use App\Models\Source;
use App\Services\ArticleSaver;
use App\Services\DeepLTranslator;
use App\Services\FeedFetcher;
use App\Services\FeedImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TranslateArticleSummaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.deepl.api_url' => 'https://deepl.example.test/translate', 'services.deepl.api_key' => 'test-only']);
        Http::preventStrayRequests();
    }

    public function test_タイトルと概要を保存し同じ記事を再翻訳しない(): void
    {
        Http::fake(fn (Request $request) => Http::response([
            'translations' => [['text' => $request['text'][0] === 'English title' ? '翻訳タイトル' : '翻訳概要']],
        ]));
        $article = Article::factory()->create(['title' => 'English title', 'summary' => 'English summary']);
        $job = new TranslateArticleTitleJob($article);
        $job->handle(new DeepLTranslator);
        $job->handle(new DeepLTranslator);

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'title' => 'English title',
            'summary' => 'English summary',
            'translated_title' => '翻訳タイトル',
            'translated_summary' => '翻訳概要',
        ]);
        Http::assertSentCount(2);
    }

    public static function skippedSummaries(): array
    {
        return [[null], [''], [" \n\t"], ['日本語の概要']];
    }

    #[DataProvider('skippedSummaries')]
    public function test_空または日本語概要は_apiを呼ばずタイトルのみ翻訳する(?string $summary): void
    {
        Http::fake(['*' => Http::response(['translations' => [['text' => '翻訳タイトル']]])]);
        $article = Article::factory()->create(['title' => 'English title', 'summary' => $summary]);
        (new TranslateArticleTitleJob($article))->handle(new DeepLTranslator);

        Http::assertSentCount(1);
        Http::assertSent(fn (Request $request) => $request['text'] === ['English title'] && $request['target_lang'] === 'JA');
        $this->assertNull($article->fresh()->translated_summary);
    }

    public function test_日本語タイトルでも概要を翻訳する(): void
    {
        Http::fake(['*' => Http::response(['translations' => [['text' => '翻訳概要']]])]);
        $article = Article::factory()->create(['title' => '日本語のタイトル', 'summary' => 'English summary']);
        (new TranslateArticleTitleJob($article))->handle(new DeepLTranslator);

        Http::assertSentCount(1);
        $this->assertSame('翻訳概要', $article->fresh()->translated_summary);
        $this->assertNull($article->translated_title);
    }

    public function test_概要の失敗でも保存済み記事とタイトル訳が残りリトライは概要のみ翻訳する(): void
    {
        $article = (new ArticleSaver)->save(Source::factory()->create(), [[
            'title' => 'English title', 'summary' => 'English summary',
            'url' => 'https://example.test/article', 'published_at' => null,
        ]])->sole();
        Http::fakeSequence()
            ->push(['translations' => [['text' => '翻訳タイトル']]])
            ->push([], 503)
            ->push(['translations' => [['text' => '翻訳概要']]]);
        $job = new TranslateArticleTitleJob($article);
        try {
            $job->handle(new DeepLTranslator);
            $this->fail('Translation should fail for queue retry.');
        } catch (RequestException $exception) {
            $this->assertSame(503, $exception->response->status());
        }
        $this->assertDatabaseHas('articles', [
            'id' => $article->id, 'summary' => 'English summary',
            'translated_title' => '翻訳タイトル', 'translated_summary' => null,
        ]);
        $job->handle(new DeepLTranslator);
        $this->assertSame('翻訳概要', $article->fresh()->translated_summary);
        Http::assertSentCount(3);
        $this->assertCount(1, Http::recorded(fn (Request $request) => $request['text'] === ['English title']));
    }

    public function test_タイトル翻訳失敗でも概要は保存されリトライで再翻訳されない(): void
    {
        $article = Article::factory()->create(['title' => 'English title', 'summary' => 'English summary']);
        Http::fakeSequence()->push([], 503)
            ->push(['translations' => [['text' => '翻訳概要']]])
            ->push(['translations' => [['text' => '翻訳タイトル']]]);
        $job = new TranslateArticleTitleJob($article);
        try {
            $job->handle(new DeepLTranslator);
            $this->fail('Translation should fail for queue retry.');
        } catch (RequestException) {
            $this->assertSame('翻訳概要', $article->fresh()->translated_summary);
        }
        $job->handle(new DeepLTranslator);
        $this->assertSame('翻訳タイトル', $article->fresh()->translated_title);
        Http::assertSentCount(3);
        $this->assertCount(1, Http::recorded(fn (Request $request) => $request['text'] === ['English summary']));
    }

    public function test_新規記事のみキューへ投入し再取得では翻訳を追加しない(): void
    {
        Queue::fake();
        Http::fake();
        $source = Source::factory()->create();
        $fetcher = $this->mock(FeedFetcher::class);
        $fetcher->shouldReceive('fetch')->twice()->andReturn([[
            'title' => 'English title', 'summary' => 'English summary',
            'url' => 'https://example.test/article', 'published_at' => null,
        ]]);
        $importer = new FeedImporter($fetcher, new ArticleSaver);
        $importer->import($source);
        $importer->import($source);

        Queue::assertPushed(TranslateArticleTitleJob::class, 1);
        Http::assertNothingSent();
        $this->assertDatabaseHas('articles', ['summary' => 'English summary', 'translated_summary' => null]);
    }
}
