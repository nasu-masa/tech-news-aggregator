<?php

namespace Tests\Unit;

use App\Services\FeedParser;
use Laminas\Feed\Reader\Exception\RuntimeException;
use Tests\TestCase;

class FeedParserTest extends TestCase
{
    public function test_rssを解析できる(): void
    {
        $xml = <<<'XML'
        <?xml version="1.0" encoding="UTF-8" ?>
        <rss version="2.0">
            <channel>
                <title>テストフィード</title>

                <item>
                    <title>テスト記事</title>
                    <link>https://example.com/articles/1</link>
                    <description>記事の概要です。</description>
                    <pubDate>Tue, 04 Aug 2026 12:00:00 +0900</pubDate>
                </item>
            </channel>
        </rss>
        XML;

        $parser = new FeedParser;

        $articles = $parser->parse($xml);

        $this->assertCount(1, $articles);
        $this->assertSame('テスト記事', $articles[0]['title']);
        $this->assertSame(
            'https://example.com/articles/1',
            $articles[0]['url']
        );
        $this->assertSame(
            '記事の概要です。',
            $articles[0]['description']
        );
        $this->assertSame(
            '2026-08-04 03:00:00',
            $articles[0]['published_at']
        );
    }

    public function test_atomを解析できる(): void
    {
        $xml = <<<'XML'
        <?xml version="1.0" encoding="UTF-8"?>
        <feed xmlns="http://www.w3.org/2005/Atom">
            <title>テストAtomフィード</title>

            <entry>
                <title>Atomテスト記事</title>
                <link href="https://example.com/articles/2" />
                <summary>Atom記事の概要です。</summary>
                <published>2026-08-04T12:00:00+09:00</published>
            </entry>
        </feed>
        XML;

        $parser = new FeedParser;

        $articles = $parser->parse($xml);

        $this->assertCount(1, $articles);

        $this->assertSame(
            'Atomテスト記事',
            $articles[0]['title']
        );

        $this->assertSame(
            'https://example.com/articles/2',
            $articles[0]['url']
        );

        $this->assertSame(
            'Atom記事の概要です。',
            $articles[0]['description']
        );

        $this->assertSame(
            '2026-08-04 03:00:00',
            $articles[0]['published_at']
        );
    }

    public function test_タイトルがない記事をスキップする(): void
    {
        $xml = <<<'XML'
        <rss version="2.0">
            <channel>
                <title>テストフィード</title>

                <item>
                    <title>正常な記事</title>
                    <link>https://example.com/articles/1</link>
                </item>

                <item>
                    <title></title>
                    <link>https://example.com/articles/2</link>
                </item>

            </channel>
        </rss>
        XML;

        $parser = new FeedParser;

        $articles = $parser->parse($xml);

        $this->assertCount(1, $articles);
        $this->assertSame('正常な記事', $articles[0]['title']);
    }

    public function test_httpの記事をスキップする(): void
    {
        $xml = <<<'XML'
        <?xml version="1.0" encoding="UTF-8" ?>
        <rss version="2.0">
            <channel>
                <title>テストフィード</title>

                <item>
                    <title>HTTPの記事</title>
                    <link>http://example.com/articles/1</link>
                </item>

                <item>
                    <title>正常な記事</title>
                    <link>https://example.com/articles/2</link>
                </item>
            </channel>
        </rss>
        XML;

        $articles = (new FeedParser)->parse($xml);

        $this->assertCount(1, $articles);
        $this->assertSame('正常な記事', $articles[0]['title']);
    }

    public function test_urlが不正な記事をスキップする(): void
    {
        $xml = <<<'XML'
        <?xml version="1.0" encoding="UTF-8" ?>
        <rss version="2.0">
            <channel>
                <title>テストフィード</title>

                <item>
                    <title>URLが不正な記事</title>
                    <link>not-url</link>
                </item>

                <item>
                    <title>正常な記事</title>
                    <link>https://example.com/articles/2</link>
                </item>
            </channel>
        </rss>
        XML;

        $articles = (new FeedParser)->parse($xml);

        $this->assertCount(1, $articles);
        $this->assertSame('正常な記事', $articles[0]['title']);
    }

    public function test_不正なxmlでは例外が発生する(): void
    {
        $xml = '<rss><channel><item></rss>';

        $this->expectException(RuntimeException::class);

        (new FeedParser)->parse($xml);
    }

    public function test_descriptionがない記事はnullになる(): void
    {
        $xml = <<<'XML'
        <?xml version="1.0" encoding="UTF-8" ?>
        <rss version="2.0">
            <channel>
                <title>テストフィード</title>

                <item>
                    <title>概要なしの記事</title>
                    <link>https://example.com/articles/1</link>
                </item>
            </channel>
        </rss>
        XML;

        $articles = (new FeedParser)->parse($xml);

        $this->assertCount(1, $articles);
        $this->assertNull($articles[0]['description']);
    }

    public function test_published_atがない記事はnullになる(): void
    {
        $xml = <<<'XML'
        <?xml version="1.0" encoding="UTF-8" ?>
        <rss version="2.0">
            <channel>
                <title>テストフィード</title>

                <item>
                    <title>公開日時なしの記事</title>
                    <link>https://example.com/articles/1</link>
                    <description>記事の概要です。</description>
                </item>
            </channel>
        </rss>
        XML;

        $articles = (new FeedParser)->parse($xml);

        $this->assertCount(1, $articles);
        $this->assertNull($articles[0]['published_at']);
    }

    public function test_publishedがなくupdatedだけのatomを解析できる(): void
    {
        $xml = <<<'XML'
        <?xml version="1.0" encoding="UTF-8"?>
        <feed xmlns="http://www.w3.org/2005/Atom">
            <title>テストAtomフィード</title>

            <entry>
                <title>更新日時だけの記事</title>
                <link href="https://example.com/articles/3" />
                <summary>記事の概要です。</summary>
                <updated>2026-08-04T12:00:00+09:00</updated>
            </entry>
        </feed>
        XML;

        $articles = (new FeedParser)->parse($xml);

        $this->assertCount(1, $articles);
        $this->assertSame(
            '2026-08-04 03:00:00',
            $articles[0]['published_at']
        );
    }

    public function test_publishedとupdatedがある場合はpublishedを優先する(): void
    {
        $xml = <<<'XML'
        <?xml version="1.0" encoding="UTF-8"?>
        <feed xmlns="http://www.w3.org/2005/Atom">
            <title>テストAtomフィード</title>

            <entry>
                <title>公開日時と更新日時がある記事</title>
                <link href="https://example.com/articles/4" />
                <summary>記事の概要です。</summary>
                <published>2026-08-04T12:00:00+09:00</published>
                <updated>2026-08-05T12:00:00+09:00</updated>
            </entry>
        </feed>
        XML;

        $articles = (new FeedParser)->parse($xml);

        $this->assertCount(1, $articles);
        $this->assertSame(
            '2026-08-04 03:00:00',
            $articles[0]['published_at']
        );
    }

    public function test_httpsスキームの大文字小文字を区別せず解析する(): void
    {
        $xml = <<<'XML'
        <rss version="2.0">
            <channel>
                <title>テストフィード</title>
                <item>
                    <title>大文字HTTPSの記事</title>
                    <link>HTTPS://example.com/articles/1</link>
                </item>
            </channel>
        </rss>
        XML;

        $articles = (new FeedParser)->parse($xml);

        $this->assertCount(1, $articles);
        $this->assertSame(
            'HTTPS://example.com/articles/1',
            $articles[0]['url']
        );
    }
}
