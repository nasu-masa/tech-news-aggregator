<?php

namespace Tests\Unit;

use App\Models\Source;
use App\Services\ArticleSaver;
use App\Services\FeedFetcher;
use App\Services\FeedImporter;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class FeedImporterTest extends TestCase
{
    use MockeryPHPUnitIntegration;

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
            ->with(Mockery::type(Source::class), $articles);

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
            ->with($source, $articles);

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

        $feedImporter = new FeedImporter($feedFetcher, $articleSaver);

        $feedImporter->import($source);
    }
}
