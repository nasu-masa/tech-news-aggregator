<?php

namespace Tests\Unit;

use App\Services\DnsResolver;
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

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** すべてのホストに対して同一のパブリック IP を返すスタブ */
    private function publicIpResolver(string $ipv4 = '93.184.216.34'): DnsResolver
    {
        return new class($ipv4) extends DnsResolver
        {
            public function __construct(private string $ipv4) {}

            public function resolveIpv4(string $host): array
            {
                return [$this->ipv4];
            }

            public function resolveIpv6(string $host): array
            {
                return [];
            }
        };
    }

    /** ホスト名ごとに異なる IP を返すスタブ（未登録ホストはデフォルト IP） */
    private function ipMappingResolver(array $hostIpMap, string $defaultIp = '93.184.216.34'): DnsResolver
    {
        return new class($hostIpMap, $defaultIp) extends DnsResolver
        {
            public function __construct(
                private readonly array $hostIpMap,
                private readonly string $defaultIp,
            ) {}

            public function resolveIpv4(string $host): array
            {
                return [$this->hostIpMap[$host] ?? $this->defaultIp];
            }

            public function resolveIpv6(string $host): array
            {
                return [];
            }
        };
    }

    // -------------------------------------------------------------------------
    // 基本動作
    // -------------------------------------------------------------------------

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

        $fetcher = new FeedFetcher(new FeedParser, $this->publicIpResolver());

        $articles = $fetcher->fetch('https://example.com/feed.xml');

        $this->assertCount(1, $articles);
        $this->assertSame('テスト記事', $articles[0]['title']);
        $this->assertSame('https://example.com/articles/1', $articles[0]['url']);

        Http::assertSent(
            fn ($request) => $request->url() === 'https://example.com/feed.xml'
        );
    }

    public function test_不正な_ur_lでは例外が発生し_htt_p通信しない(): void
    {
        Http::fake();

        $fetcher = new FeedFetcher(new FeedParser, new DnsResolver);

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

        $fetcher = new FeedFetcher(new FeedParser, new DnsResolver);

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

        $fetcher = new FeedFetcher(new FeedParser, $this->publicIpResolver());

        $this->expectException(RequestException::class);

        try {
            $fetcher->fetch('https://example.com/feed.xml');
        } finally {
            Http::assertSentCount(2);
        }
    }

    public function test_取得した本文が不正_xm_lなら例外が発生する(): void
    {
        Http::fake([
            'https://example.com/feed.xml' => Http::response(
                '<rss><channel><title>壊れたXML',
                200
            ),
        ]);

        $fetcher = new FeedFetcher(new FeedParser, $this->publicIpResolver());

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

        $fetcher = new FeedFetcher(new FeedParser, $this->publicIpResolver());

        $articles = $fetcher->fetch('HTTPS://example.com/feed.xml');

        $this->assertCount(1, $articles);

        Http::assertSent(
            fn ($request) => strtolower($request->url())
                === 'https://example.com/feed.xml'
        );
    }

    public function test_ループバックipへの接続を拒否する(): void
    {
        Http::fake();

        $fetcher = new FeedFetcher(new FeedParser, new DnsResolver);

        try {
            $fetcher->fetch('https://127.0.0.1/feed.xml');

            $this->fail('InvalidArgumentExceptionが発生しませんでした。');
        } catch (InvalidArgumentException) {
            Http::assertNothingSent();
        }
    }

    public function test_プライベートipへの接続を拒否する(): void
    {
        Http::fake();

        $fetcher = new FeedFetcher(new FeedParser, new DnsResolver);

        try {
            $fetcher->fetch('https://10.0.0.1/feed.xml');

            $this->fail('InvalidArgumentExceptionが発生しませんでした。');
        } catch (InvalidArgumentException) {
            Http::assertNothingSent();
        }
    }

    public function test_リンクローカルipへの接続を拒否する(): void
    {
        Http::fake();

        $fetcher = new FeedFetcher(new FeedParser, new DnsResolver);

        try {
            $fetcher->fetch('https://169.254.169.254/feed.xml');

            $this->fail('InvalidArgumentExceptionが発生しませんでした。');
        } catch (InvalidArgumentException) {
            Http::assertNothingSent();
        }
    }

    public function test_ipv6ループバックへの接続を拒否する(): void
    {
        Http::fake();

        $fetcher = new FeedFetcher(new FeedParser, new DnsResolver);

        try {
            $fetcher->fetch('https://[::1]/feed.xml');

            $this->fail('InvalidArgumentExceptionが発生しませんでした。');
        } catch (InvalidArgumentException) {
            Http::assertNothingSent();
        }
    }

    // -------------------------------------------------------------------------
    // fetchXml()
    // -------------------------------------------------------------------------

    public function test_fetch_xmlはraw_xmlを返す(): void
    {
        $xml = <<<'XML'
        <rss version="2.0"><channel><title>テスト</title></channel></rss>
        XML;

        Http::fake([
            'https://example.com/feed.xml' => Http::response($xml, 200),
        ]);

        $fetcher = new FeedFetcher(new FeedParser, $this->publicIpResolver());

        $result = $fetcher->fetchXml('https://example.com/feed.xml');

        $this->assertIsString($result);
        $this->assertStringContainsString('<rss', $result);
    }

    public function test_fetchを経由してもfetch_xmlと同じ結果になる(): void
    {
        $xml = <<<'XML'
        <rss version="2.0">
            <channel>
                <title>テストフィード</title>
                <item>
                    <title>記事</title>
                    <link>https://example.com/articles/1</link>
                </item>
            </channel>
        </rss>
        XML;

        Http::fake([
            'https://example.com/feed.xml' => Http::response($xml, 200),
        ]);

        $fetcher = new FeedFetcher(new FeedParser, $this->publicIpResolver());

        $articles = $fetcher->fetch('https://example.com/feed.xml');

        $this->assertCount(1, $articles);
        $this->assertSame('記事', $articles[0]['title']);
    }

    // -------------------------------------------------------------------------
    // リダイレクト追跡
    // -------------------------------------------------------------------------

    public function test_public_http_sへのリダイレクトを追跡できる(): void
    {
        $xml = '<rss version="2.0"><channel><title>テスト</title></channel></rss>';

        Http::fake([
            'https://origin.example.com/feed.xml' => Http::response('', 301, [
                'Location' => 'https://destination.example.com/feed.xml',
            ]),
            'https://destination.example.com/feed.xml' => Http::response($xml, 200),
        ]);

        $fetcher = new FeedFetcher(new FeedParser, $this->publicIpResolver());

        $result = $fetcher->fetchXml('https://origin.example.com/feed.xml');

        $this->assertSame($xml, $result);
        Http::assertSentCount(2);
        Http::assertSent(
            fn ($req) => $req->url() === 'https://destination.example.com/feed.xml'
        );
    }

    public function test_301の正常_rs_sを追跡して_xm_lを取得できる(): void
    {
        $xml = <<<'XML'
        <rss version="2.0">
            <channel>
                <title>リダイレクト先フィード</title>
                <item>
                    <title>記事タイトル</title>
                    <link>https://example.com/articles/1</link>
                </item>
            </channel>
        </rss>
        XML;

        Http::fake([
            'https://example.com/old-feed.xml' => Http::response('', 301, [
                'Location' => 'https://example.com/new-feed.xml',
            ]),
            'https://example.com/new-feed.xml' => Http::response($xml, 200),
        ]);

        $fetcher = new FeedFetcher(new FeedParser, $this->publicIpResolver());

        $articles = $fetcher->fetch('https://example.com/old-feed.xml');

        $this->assertCount(1, $articles);
        $this->assertSame('記事タイトル', $articles[0]['title']);
    }

    public function test_302も追跡できる(): void
    {
        $xml = '<rss version="2.0"><channel><title>テスト</title></channel></rss>';

        Http::fake([
            'https://example.com/feed.xml' => Http::response('', 302, [
                'Location' => 'https://example.com/new-feed.xml',
            ]),
            'https://example.com/new-feed.xml' => Http::response($xml, 200),
        ]);

        $fetcher = new FeedFetcher(new FeedParser, $this->publicIpResolver());

        $this->assertSame($xml, $fetcher->fetchXml('https://example.com/feed.xml'));
        Http::assertSentCount(2);
    }

    public function test_307も追跡できる(): void
    {
        $xml = '<rss version="2.0"><channel><title>テスト</title></channel></rss>';

        Http::fake([
            'https://example.com/feed.xml' => Http::response('', 307, [
                'Location' => 'https://example.com/new-feed.xml',
            ]),
            'https://example.com/new-feed.xml' => Http::response($xml, 200),
        ]);

        $fetcher = new FeedFetcher(new FeedParser, $this->publicIpResolver());

        $this->assertSame($xml, $fetcher->fetchXml('https://example.com/feed.xml'));
        Http::assertSentCount(2);
    }

    public function test_308も追跡できる(): void
    {
        $xml = '<rss version="2.0"><channel><title>テスト</title></channel></rss>';

        Http::fake([
            'https://example.com/feed.xml' => Http::response('', 308, [
                'Location' => 'https://example.com/new-feed.xml',
            ]),
            'https://example.com/new-feed.xml' => Http::response($xml, 200),
        ]);

        $fetcher = new FeedFetcher(new FeedParser, $this->publicIpResolver());

        $this->assertSame($xml, $fetcher->fetchXml('https://example.com/feed.xml'));
        Http::assertSentCount(2);
    }

    // -------------------------------------------------------------------------
    // リダイレクト先の SSRF 検証
    // -------------------------------------------------------------------------

    public function test_private_i_pへのリダイレクトを拒否する(): void
    {
        Http::fake([
            'https://example.com/feed.xml' => Http::response('', 301, [
                'Location' => 'https://redirect.example.com/feed.xml',
            ]),
        ]);

        $fetcher = new FeedFetcher(
            new FeedParser,
            $this->ipMappingResolver(['redirect.example.com' => '192.168.1.1'])
        );

        $this->expectException(InvalidArgumentException::class);

        $fetcher->fetchXml('https://example.com/feed.xml');
    }

    public function test_loopback_i_pへのリダイレクトを拒否する(): void
    {
        Http::fake([
            'https://example.com/feed.xml' => Http::response('', 301, [
                'Location' => 'https://redirect.example.com/feed.xml',
            ]),
        ]);

        $fetcher = new FeedFetcher(
            new FeedParser,
            $this->ipMappingResolver(['redirect.example.com' => '127.0.0.1'])
        );

        $this->expectException(InvalidArgumentException::class);

        $fetcher->fetchXml('https://example.com/feed.xml');
    }

    public function test_link_local_i_pへのリダイレクトを拒否する(): void
    {
        Http::fake([
            'https://example.com/feed.xml' => Http::response('', 301, [
                'Location' => 'https://redirect.example.com/feed.xml',
            ]),
        ]);

        $fetcher = new FeedFetcher(
            new FeedParser,
            $this->ipMappingResolver(['redirect.example.com' => '169.254.169.254'])
        );

        $this->expectException(InvalidArgumentException::class);

        $fetcher->fetchXml('https://example.com/feed.xml');
    }

    public function test_htt_pへのダウングレードリダイレクトを拒否する(): void
    {
        Http::fake([
            'https://example.com/feed.xml' => Http::response('', 301, [
                'Location' => 'http://example.com/feed.xml',
            ]),
        ]);

        $fetcher = new FeedFetcher(new FeedParser, $this->publicIpResolver());

        $this->expectException(InvalidArgumentException::class);

        $fetcher->fetchXml('https://example.com/feed.xml');
    }

    // -------------------------------------------------------------------------
    // リダイレクトループ・上限
    // -------------------------------------------------------------------------

    public function test_リダイレクトループを検出する(): void
    {
        Http::fake([
            'https://a.example.com/feed.xml' => Http::response('', 302, [
                'Location' => 'https://b.example.com/feed.xml',
            ]),
            'https://b.example.com/feed.xml' => Http::response('', 302, [
                'Location' => 'https://a.example.com/feed.xml',
            ]),
        ]);

        $fetcher = new FeedFetcher(new FeedParser, $this->publicIpResolver());

        $this->expectException(InvalidArgumentException::class);

        $fetcher->fetchXml('https://a.example.com/feed.xml');
    }

    public function test_最大リダイレクト回数を超えると拒否する(): void
    {
        // 6回リダイレクト（上限5回を超える）
        Http::fake([
            'https://a.example.com/feed.xml' => Http::response('', 301, ['Location' => 'https://b.example.com/feed.xml']),
            'https://b.example.com/feed.xml' => Http::response('', 301, ['Location' => 'https://c.example.com/feed.xml']),
            'https://c.example.com/feed.xml' => Http::response('', 301, ['Location' => 'https://d.example.com/feed.xml']),
            'https://d.example.com/feed.xml' => Http::response('', 301, ['Location' => 'https://e.example.com/feed.xml']),
            'https://e.example.com/feed.xml' => Http::response('', 301, ['Location' => 'https://f.example.com/feed.xml']),
            'https://f.example.com/feed.xml' => Http::response('', 301, ['Location' => 'https://g.example.com/feed.xml']),
            'https://g.example.com/feed.xml' => Http::response('<rss/>', 200),
        ]);

        $fetcher = new FeedFetcher(new FeedParser, $this->publicIpResolver());

        $this->expectException(InvalidArgumentException::class);

        try {
            $fetcher->fetchXml('https://a.example.com/feed.xml');
        } finally {
            // a〜f の 6 リクエストで止まり g には到達しない
            Http::assertSentCount(6);
            Http::assertNotSent(
                fn ($req) => $req->url() === 'https://g.example.com/feed.xml'
            );
        }
    }

    public function test_locationなしの3xxを拒否する(): void
    {
        Http::fake([
            'https://example.com/feed.xml' => Http::response('', 301),
        ]);

        $fetcher = new FeedFetcher(new FeedParser, $this->publicIpResolver());

        $this->expectException(InvalidArgumentException::class);

        $fetcher->fetchXml('https://example.com/feed.xml');
    }

    // -------------------------------------------------------------------------
    // 相対 Location の解決（RFC 3986 準拠）
    // -------------------------------------------------------------------------

    public function test_絶対パス相対_locationを正しく解決する(): void
    {
        $xml = '<rss version="2.0"><channel><title>テスト</title></channel></rss>';

        Http::fake([
            'https://example.com/dir/feed.xml' => Http::response('', 301, [
                'Location' => '/feeds.xml',
            ]),
            'https://example.com/feeds.xml' => Http::response($xml, 200),
        ]);

        $fetcher = new FeedFetcher(new FeedParser, $this->publicIpResolver());

        $result = $fetcher->fetchXml('https://example.com/dir/feed.xml');

        $this->assertSame($xml, $result);
        Http::assertSent(fn ($req) => $req->url() === 'https://example.com/feeds.xml');
    }

    public function test_相対パス_locationを正しく解決する(): void
    {
        $xml = '<rss version="2.0"><channel><title>テスト</title></channel></rss>';

        Http::fake([
            'https://example.com/dir/feed.xml' => Http::response('', 301, [
                'Location' => 'feeds.xml',
            ]),
            'https://example.com/dir/feeds.xml' => Http::response($xml, 200),
        ]);

        $fetcher = new FeedFetcher(new FeedParser, $this->publicIpResolver());

        $result = $fetcher->fetchXml('https://example.com/dir/feed.xml');

        $this->assertSame($xml, $result);
        Http::assertSent(fn ($req) => $req->url() === 'https://example.com/dir/feeds.xml');
    }

    public function test_親ディレクトリ相対_locationを正しく解決する(): void
    {
        $xml = '<rss version="2.0"><channel><title>テスト</title></channel></rss>';

        Http::fake([
            'https://example.com/a/b/feed.xml' => Http::response('', 301, [
                'Location' => '../feeds.xml',
            ]),
            'https://example.com/a/feeds.xml' => Http::response($xml, 200),
        ]);

        $fetcher = new FeedFetcher(new FeedParser, $this->publicIpResolver());

        $result = $fetcher->fetchXml('https://example.com/a/b/feed.xml');

        $this->assertSame($xml, $result);
        Http::assertSent(fn ($req) => $req->url() === 'https://example.com/a/feeds.xml');
    }

    public function test_スキーム相対_locationを正しく解決する(): void
    {
        $xml = '<rss version="2.0"><channel><title>テスト</title></channel></rss>';

        Http::fake([
            'https://origin.example.com/feed.xml' => Http::response('', 301, [
                'Location' => '//destination.example.com/feeds.xml',
            ]),
            'https://destination.example.com/feeds.xml' => Http::response($xml, 200),
        ]);

        $fetcher = new FeedFetcher(new FeedParser, $this->publicIpResolver());

        $result = $fetcher->fetchXml('https://origin.example.com/feed.xml');

        $this->assertSame($xml, $result);
        Http::assertSent(fn ($req) => $req->url() === 'https://destination.example.com/feeds.xml');
    }

    // -------------------------------------------------------------------------
    // リダイレクト先のDNS解決とIP固定
    // -------------------------------------------------------------------------

    public function test_リダイレクト先も_dn_s解決と_ssr_f検証が実行される(): void
    {
        $xml = '<rss version="2.0"><channel><title>テスト</title></channel></rss>';

        Http::fake([
            'https://origin.example.com/feed.xml' => Http::response('', 301, [
                'Location' => 'https://destination.example.com/feed.xml',
            ]),
            'https://destination.example.com/feed.xml' => Http::response($xml, 200),
        ]);

        $resolvedHosts = [];
        $resolver = new class($resolvedHosts) extends DnsResolver
        {
            public function __construct(private array &$resolvedHosts) {}

            public function resolveIpv4(string $host): array
            {
                $this->resolvedHosts[] = $host;

                return ['93.184.216.34'];
            }

            public function resolveIpv6(string $host): array
            {
                return [];
            }
        };

        $fetcher = new FeedFetcher(new FeedParser, $resolver);
        $fetcher->fetchXml('https://origin.example.com/feed.xml');

        $this->assertContains('origin.example.com', $resolvedHosts, 'origin の DNS 解決が実行されていません');
        $this->assertContains('destination.example.com', $resolvedHosts, 'リダイレクト先の DNS 解決が実行されていません');
    }

    // -------------------------------------------------------------------------
    // IPv6 link-local
    // -------------------------------------------------------------------------

    public function test_ipv6_link_localへの接続を拒否する(): void
    {
        Http::fake();

        $fetcher = new FeedFetcher(new FeedParser, new DnsResolver);

        try {
            $fetcher->fetch('https://[fe80::1]/feed.xml');

            $this->fail('InvalidArgumentExceptionが発生しませんでした。');
        } catch (InvalidArgumentException) {
            Http::assertNothingSent();
        }
    }

    // -------------------------------------------------------------------------
    // レスポンスサイズ制限
    // -------------------------------------------------------------------------

    public function test_巨大なレスポンスは拒否する(): void
    {
        // MAX_BODY_BYTES (10 MB) を 1 バイト超過
        $oversized = str_repeat('x', 10 * 1024 * 1024 + 1);

        Http::fake([
            'https://example.com/feed.xml' => Http::response($oversized, 200),
        ]);

        $fetcher = new FeedFetcher(new FeedParser, $this->publicIpResolver());

        $this->expectException(InvalidArgumentException::class);

        $fetcher->fetchXml('https://example.com/feed.xml');
    }

}
