<?php

namespace Tests\Feature;

use App\Models\Source;
use App\Services\ArticleSaver;
use App\Services\FeedFetcher;
use App\Services\FeedImporter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Tests\TestCase;

class FeedImporterTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    use RefreshDatabase;

    public function test_取得した記事を保存処理へ渡す(): void
    {
        /** @var Source&MockInterface $source */
        $source = Mockery::mock(Source::class)->makePartial();
        $source->feed_url = 'https://example.com/feed.xml';

        $articles = [
            [
                'title' => 'テスト記事',
                'summary' => '概要',
                'url' => 'https://example.com/articles/1',
                'published_at' => null,
            ],
        ];

        $feedFetcher = Mockery::mock(FeedFetcher::class);
        $articleSaver = Mockery::mock(ArticleSaver::class);

        $source
            ->shouldReceive('update')
            ->once()
            ->with(Mockery::on(
                fn (array $data) => array_key_exists('last_success_at', $data)
            ))
            ->andReturn(true);

        $feedFetcher
            ->shouldReceive('fetch')
            ->once()
            ->with('https://example.com/feed.xml')
            ->andReturn($articles);

        $articleSaver
            ->shouldReceive('save')
            ->once()
            ->with(Mockery::type(Source::class), $articles)
            ->andReturn(new Collection);

        Queue::fake();

        $importer = new FeedImporter(
            $feedFetcher,
            $articleSaver,
        );

        $importer->import($source);
    }

    public function test_取得時にエラー情報を保存する(): void
    {
        /** @var Source&MockInterface $source */
        $source = Mockery::mock(Source::class)->makePartial();
        $source->feed_url = 'https://example.com/feed.xml';

        $feedFetcher = Mockery::mock(FeedFetcher::class);
        $articleSaver = Mockery::mock(ArticleSaver::class);

        $feedFetcher
            ->shouldReceive('fetch')
            ->once()
            ->with('https://example.com/feed.xml')
            ->andThrow(new \RuntimeException('フィード取得に失敗しました'));

        $source
            ->shouldReceive('update')
            ->once()
            ->with(Mockery::on(
                fn (array $data) => array_key_exists('last_error_at', $data)
                && $data['last_error_message'] === 'フィード取得に失敗しました'
            ))
            ->andReturn(true);

        $articleSaver
            ->shouldNotReceive('save');

        $importer = new FeedImporter(
            $feedFetcher,
            $articleSaver,
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('フィード取得に失敗しました');

        $importer->import($source);
    }

    public function test_取得成功時に過去のエラー情報をクリアする(): void
    {
        /** @var Source&MockInterface $source */
        $source = Mockery::mock(Source::class)->makePartial();

        $source->feed_url = 'https://example.com/feed.xml';
        $source->last_error_at = now();
        $source->last_error_message = '以前の取得エラー';

        $feedFetcher = Mockery::mock(FeedFetcher::class);
        $articleSaver = Mockery::mock(ArticleSaver::class);

        $articles = [
            [
                'title' => 'Test Article',
                'url' => 'https://example.com/article',
                'summary' => 'Test description',
                'published_at' => null,
            ],
        ];

        $feedFetcher
            ->shouldReceive('fetch')
            ->once()
            ->with($source->feed_url)
            ->andReturn($articles);

        $articleSaver
            ->shouldReceive('save')
            ->once()
            ->with($source, $articles)
            ->andReturn(new Collection);

        $source
            ->shouldReceive('update')
            ->once()
            ->with(Mockery::on(
                fn (array $data) => array_key_exists('last_success_at', $data)
                    && array_key_exists('last_error_at', $data)
                    && array_key_exists('last_error_message', $data)
                    && $data['last_error_at'] === null
                    && $data['last_error_message'] === null
            ))
            ->andReturn(true);

        Queue::fake();

        $feedImporter = new FeedImporter($feedFetcher, $articleSaver);

        $feedImporter->import($source);
    }

    public function test_同じsourceがロック中なら取り込み処理を実行しない(): void
    {
        $source = Source::create([
            'name' => 'テストフィード',
            'feed_url' => 'https://example.com/feed.xml',
            'site_url' => 'https://example.com',
            'is_active' => true,
        ]);

        $lock = Cache::lock("feed-import:{$source->id}", 60);
        $this->assertTrue($lock->get());

        $feedFetcher = Mockery::mock(FeedFetcher::class);
        $articleSaver = Mockery::mock(ArticleSaver::class);

        try {
            $feedFetcher
                ->shouldNotReceive('fetch');

            $articleSaver
                ->shouldNotReceive('save');

            $feedImporter = new FeedImporter($feedFetcher, $articleSaver);

            $feedImporter->import($source);

        } finally {
            $lock->release();
        }

    }

    public function test_別sourceがロック中でも取込処理を実行できる(): void
    {
        $lockedSource = Source::create([
            'name' => 'ロック中フィード',
            'feed_url' => 'https://example.com/feed.xml',
            'site_url' => 'https://example.com',
            'is_active' => true,
        ]);

        $targetSource = Source::create([
            'name' => '取得対象フィード',
            'feed_url' => 'https://example.org/feed.xml',
            'site_url' => 'https://example.org',
            'is_active' => true,
        ]);

        $lock = Cache::lock("feed-import:{$lockedSource->id}", 60);
        $this->assertTrue($lock->get());

        $feedFetcher = Mockery::mock(FeedFetcher::class);
        $articleSaver = Mockery::mock(ArticleSaver::class);

        $articles = [];

        try {
            $feedFetcher
                ->shouldReceive('fetch')
                ->once()
                ->with($targetSource->feed_url)
                ->andReturn($articles);

            $articleSaver
                ->shouldReceive('save')
                ->once()
                ->with($targetSource, $articles)
                ->andReturn(new Collection);

            Queue::fake();

            $feedImporter = new FeedImporter($feedFetcher, $articleSaver);

            $feedImporter->import($targetSource);
        } finally {
            $lock->release();
        }
    }

    public function test_取込完了後にロックが解放される(): void
    {
        $source = Source::create([
            'name' => 'テストフィード',
            'feed_url' => 'https://example.com/feed.xml',
            'site_url' => 'https://example.com',
            'is_active' => true,
        ]);

        $feedFetcher = Mockery::mock(FeedFetcher::class);
        $articleSaver = Mockery::mock(ArticleSaver::class);

        $feedFetcher
            ->shouldReceive('fetch')
            ->once()
            ->andReturn([]);

        $articleSaver
            ->shouldReceive('save')
            ->once()
            ->andReturn(new Collection);

        Queue::fake();

        $feedImporter = new FeedImporter($feedFetcher, $articleSaver);

        $feedImporter->import($source);

        $lock = Cache::lock("feed-import:{$source->id}", 60);

        $this->assertTrue($lock->get());

        $lock->release();
    }

    public function test_取込失敗後にロックが解放される(): void
    {
        $source = Source::create([
            'name' => 'テストフィード',
            'feed_url' => 'https://example.com/feed.xml',
            'site_url' => 'https://example.com',
            'is_active' => true,
        ]);

        $feedFetcher = Mockery::mock(FeedFetcher::class);
        $articleSaver = Mockery::mock(ArticleSaver::class);

        $feedFetcher
            ->shouldReceive('fetch')
            ->once()
            ->andThrow(new \RuntimeException('取得失敗'));

        $articleSaver
            ->shouldNotReceive('save');

        $feedImporter = new FeedImporter($feedFetcher, $articleSaver);

        try {
            $feedImporter->import($source);

            $this->fail('RuntimeExceptionが発生しませんでした');
        } catch (\RuntimeException $exception) {
            $this->assertSame('取得失敗', $exception->getMessage());
        }

        $lock = Cache::lock("feed-import:{$source->id}", 60);

        $this->assertTrue($lock->get());

        $lock->release();
    }
}
