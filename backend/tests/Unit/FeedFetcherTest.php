<?php

namespace Tests\Unit;

use App\Services\FeedFetcher;
use App\Services\FeedParser;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Laminas\Feed\Reader\Exception\RuntimeException;
use Tests\TestCase;

class FeedFetcherTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    public function test_httpsフィードを取得して解析結果を返す(): void
    {
        $xml = <<<'XML'
        <rss version="2.0">
            <channel>
                <title>テストフィード</title>
                <item>
                    <title>テスト記事</title>
                    <link>https://example.com/articles/1</link>
                    <description>記事の説明</description>
                    <pubDate>Tue, 04 Aug 2026 12:00:00 +0900</pubDate>
                </item>
            </channel>
        </rss>
        XML;

        Http::fake([
            'https://example.com/feed.xml' => Http::response($xml, 200),
        ]);

        $fetcher = new FeedFetcher(new FeedParser);

        $articles = $fetcher->fetch(
            'https://example.com/feed.xml'
        );

        $this->assertCount(1, $articles);
        $this->assertSame(
            'テスト記事',
            $articles[0]['title']
        );
        $this->assertSame(
            'https://example.com/articles/1',
            $articles[0]['url']
        );

        Http::assertSent(
            fn ($request) => $request->url() === 'https://example.com/feed.xml'
        );
    }

    public function test_不正な_ur_lでは例外が発生し_htt_p通信しない(): void
    {
        Http::fake();

        $fetcher = new FeedFetcher(new FeedParser);

        try {
            $fetcher->fetch('invalid-url');

            $this->fail('InvalidArgumentExceptionが発生しませんでした。');
        } catch (InvalidArgumentException) {
            Http::assertNothingSent();
        }
    }

    public function test_http_ur_lでは例外が発生し_htt_p通信しない(): void
    {
        Http::fake();

        $fetcher = new FeedFetcher(new FeedParser);

        try {
            $fetcher->fetch('http://example.com/feed.xml');

            $this->fail('InvalidArgumentExceptionが発生しませんでした。');
        } catch (InvalidArgumentException) {
            Http::assertNothingSent();
        }
    }

    public function test_httpエラーでは_request_exceptionが発生する(): void
    {
        Http::fake([
            'https://example.com/feed.xml' => Http::response('', 500),
        ]);

        $fetcher = new FeedFetcher(new FeedParser);

        $this->expectException(RequestException::class);

        $fetcher->fetch('https://example.com/feed.xml');
    }

    public function test_取得した本文が不正_xm_lなら例外が発生する(): void
    {
        Http::fake([
            'https://example.com/feed.xml' => Http::response(
                '<rss><channel><title>壊れたXML',
                200
            ),
        ]);

        $fetcher = new FeedFetcher(new FeedParser);

        $this->expectException(RuntimeException::class);

        $fetcher->fetch('https://example.com/feed.xml');
    }

    public function test_httpsスキームの大文字小文字を区別せず取得する(): void
    {
        $xml = <<<'XML'
        <rss version="2.0">
            <channel>
                <title>テストフィード</title>
                <item>
                    <title>テスト記事</title>
                    <link>https://example.com/articles/1</link>
                </item>
            </channel>
        </rss>
        XML;

        Http::fake([
            'https://example.com/feed.xml' => Http::response($xml, 200),
        ]);

        $fetcher = new FeedFetcher(new FeedParser);

        $articles = $fetcher->fetch(
            'HTTPS://example.com/feed.xml'
        );

        $this->assertCount(1, $articles);

        Http::assertSent(
            fn ($request) => strtolower($request->url())
                === 'https://example.com/feed.xml'
        );
    }
}
