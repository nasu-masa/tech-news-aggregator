<?php

namespace Tests\Feature;

use App\Models\Source;
use App\Models\User;
use App\Services\FeedFetcher;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response as LaravelResponse;
use Tests\TestCase;

class SourceControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // GET /api/sources
    // -------------------------------------------------------------------------

    public function test_共通sourceは取得できる(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);

        Source::factory()->create([
            'name' => '共通Source',
            'created_by_user_id' => null,
            'is_active' => true,
        ]);

        $this->getJson('/api/sources')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', '共通Source');
    }

    public function test_購読済みカスタムsourceは取得できる(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);

        $source = Source::factory()->create([
            'name' => '自分のカスタムSource',
            'created_by_user_id' => $user->id,
            'is_active' => true,
        ]);
        $user->sources()->attach($source->id);

        $this->getJson('/api/sources')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', '自分のカスタムSource');
    }

    public function test_自分が作成しただけで未購読のカスタムsourceは取得できない(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);

        Source::factory()->create([
            'name' => '未購読の自分のSource',
            'created_by_user_id' => $user->id,
            'is_active' => true,
        ]);

        $this->getJson('/api/sources')
            ->assertOk()
            ->assertJsonCount(0);
    }

    public function test_他ユーザーの未購読カスタムsourceは取得できない(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $otherUser = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);

        Source::factory()->create([
            'name' => '他ユーザーのSource',
            'created_by_user_id' => $otherUser->id,
            'is_active' => true,
        ]);

        $this->getJson('/api/sources')
            ->assertOk()
            ->assertJsonCount(0);
    }

    public function test_is_subscribedが正しく返る(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);

        $subscribedSource = Source::factory()->create([
            'name' => '購読中Source',
            'created_by_user_id' => null,
            'is_active' => true,
        ]);
        $unsubscribedSource = Source::factory()->create([
            'name' => '未購読Source',
            'created_by_user_id' => null,
            'is_active' => true,
        ]);

        $user->sources()->attach($subscribedSource->id);

        $this->getJson('/api/sources')
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonFragment(['id' => $subscribedSource->id, 'is_subscribed' => true])
            ->assertJsonFragment(['id' => $unsubscribedSource->id, 'is_subscribed' => false]);
    }

    public function test_source一覧は名前順で返る(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);

        Source::factory()->create(['name' => 'React Blog', 'is_active' => true]);
        Source::factory()->create(['name' => 'Laravel Blog', 'is_active' => true]);

        $this->getJson('/api/sources')
            ->assertOk()
            ->assertJsonPath('0.name', 'Laravel Blog')
            ->assertJsonPath('1.name', 'React Blog');
    }

    public function test_無効なsourceはsource一覧に含まれない(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);

        Source::factory()->create(['name' => '有効Source', 'is_active' => true]);
        Source::factory()->create(['name' => '無効Source', 'is_active' => false]);

        $this->getJson('/api/sources')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', '有効Source');
    }

    public function test_未認証ユーザーはsource一覧を取得できない(): void
    {
        $this->getJson('/api/sources')->assertUnauthorized();
    }

    public function test_メール未認証ユーザーはsource一覧を取得できない(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->actingAs($user);

        $this->getJson('/api/sources')->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // POST /api/sources
    // -------------------------------------------------------------------------

    private function validFeedXml(string $title = 'テストフィード'): string
    {
        return <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <rss version="2.0">
            <channel>
                <title>{$title}</title>
                <link>https://example.com</link>
                <item>
                    <title>記事1</title>
                    <link>https://example.com/articles/1</link>
                </item>
            </channel>
        </rss>
        XML;
    }

    private function mockFetchXml(string $xml): void
    {
        $mock = $this->createMock(FeedFetcher::class);
        $mock->method('fetchXml')->willReturn($xml);
        $this->app->instance(FeedFetcher::class, $mock);
    }

    private function mockFetchXmlThrows(\Throwable $e): void
    {
        $mock = $this->createMock(FeedFetcher::class);
        $mock->method('fetchXml')->willThrowException($e);
        $this->app->instance(FeedFetcher::class, $mock);
    }

    public function test_新規rss_urlでsourceを作成できる(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);
        $this->mockFetchXml($this->validFeedXml('My Blog'));

        $response = $this->postJson('/api/sources', [
            'feed_url' => 'https://example.com/feed.xml',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('sources', [
            'feed_url' => 'https://example.com/feed.xml',
        ]);
    }

    public function test_新規sourceのcreated_by_user_idが現在ユーザーになる(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);
        $this->mockFetchXml($this->validFeedXml());

        $this->postJson('/api/sources', [
            'feed_url' => 'https://example.com/feed.xml',
        ]);

        $this->assertDatabaseHas('sources', [
            'feed_url' => 'https://example.com/feed.xml',
            'created_by_user_id' => $user->id,
        ]);
    }

    public function test_新規source作成時に自動購読される(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);
        $this->mockFetchXml($this->validFeedXml());

        $this->postJson('/api/sources', [
            'feed_url' => 'https://example.com/feed.xml',
        ]);

        $source = Source::where('feed_url', 'https://example.com/feed.xml')->firstOrFail();

        $this->assertDatabaseHas('user_sources', [
            'user_id' => $user->id,
            'source_id' => $source->id,
        ]);
    }

    public function test_新規source作成のレスポンスにis_subscribed_trueが含まれる(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);
        $this->mockFetchXml($this->validFeedXml());

        $this->postJson('/api/sources', [
            'feed_url' => 'https://example.com/feed.xml',
        ])->assertJsonPath('is_subscribed', true);
    }

    public function test_フィードtitleがsource_nameに保存される(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);
        $this->mockFetchXml($this->validFeedXml('My Awesome Blog'));

        $this->postJson('/api/sources', [
            'feed_url' => 'https://example.com/feed.xml',
        ]);

        $this->assertDatabaseHas('sources', [
            'feed_url' => 'https://example.com/feed.xml',
            'name' => 'My Awesome Blog',
        ]);
    }

    public function test_titleなしならhostnameをnameに使用する(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);

        $xml = <<<'XML'
        <?xml version="1.0" encoding="UTF-8"?>
        <rss version="2.0">
            <channel>
                <title></title>
                <link>https://myblog.example.com</link>
            </channel>
        </rss>
        XML;

        $this->mockFetchXml($xml);

        $this->postJson('/api/sources', [
            'feed_url' => 'https://myblog.example.com/feed.xml',
        ]);

        $this->assertDatabaseHas('sources', [
            'feed_url' => 'https://myblog.example.com/feed.xml',
            'name' => 'myblog.example.com',
        ]);
    }

    public function test_site_urlがscheme_host形式で設定される(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);
        $this->mockFetchXml($this->validFeedXml());

        $this->postJson('/api/sources', [
            'feed_url' => 'https://myblog.example.com/path/feed.xml',
        ]);

        $this->assertDatabaseHas('sources', [
            'feed_url' => 'https://myblog.example.com/path/feed.xml',
            'site_url' => 'https://myblog.example.com',
        ]);
    }

    public function test_自分の既存sourceは重複作成しない(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);

        $existing = Source::factory()->create([
            'feed_url' => 'https://example.com/feed.xml',
            'created_by_user_id' => $user->id,
            'is_active' => true,
        ]);
        $user->sources()->attach($existing->id);

        // FeedFetcher のモックは不要（既存URLなので fetchXml は呼ばれない）
        $mock = $this->createMock(FeedFetcher::class);
        $mock->expects($this->never())->method('fetchXml');
        $this->app->instance(FeedFetcher::class, $mock);

        $this->postJson('/api/sources', [
            'feed_url' => 'https://example.com/feed.xml',
        ])->assertOk();

        $this->assertDatabaseCount('sources', 1);
    }

    public function test_共通sourceも重複作成せず購読する(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);

        $common = Source::factory()->create([
            'feed_url' => 'https://example.com/feed.xml',
            'created_by_user_id' => null,
            'is_active' => true,
        ]);

        $mock = $this->createMock(FeedFetcher::class);
        $mock->expects($this->never())->method('fetchXml');
        $this->app->instance(FeedFetcher::class, $mock);

        $this->postJson('/api/sources', [
            'feed_url' => 'https://example.com/feed.xml',
        ])->assertOk()->assertJsonPath('is_subscribed', true);

        $this->assertDatabaseCount('sources', 1);
        $this->assertDatabaseHas('user_sources', [
            'user_id' => $user->id,
            'source_id' => $common->id,
        ]);
    }

    public function test_他ユーザー作成sourceと同じurlでも重複作成せず現在ユーザーを購読する(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $otherUser = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);

        $otherSource = Source::factory()->create([
            'feed_url' => 'https://example.com/feed.xml',
            'created_by_user_id' => $otherUser->id,
            'is_active' => true,
        ]);
        $otherUser->sources()->attach($otherSource->id);

        $mock = $this->createMock(FeedFetcher::class);
        $mock->expects($this->never())->method('fetchXml');
        $this->app->instance(FeedFetcher::class, $mock);

        $this->postJson('/api/sources', [
            'feed_url' => 'https://example.com/feed.xml',
        ])->assertOk()->assertJsonPath('is_subscribed', true);

        $this->assertDatabaseCount('sources', 1);
        $this->assertDatabaseHas('user_sources', [
            'user_id' => $user->id,
            'source_id' => $otherSource->id,
        ]);
        // 他ユーザーの購読は残っている
        $this->assertDatabaseHas('user_sources', [
            'user_id' => $otherUser->id,
            'source_id' => $otherSource->id,
        ]);
    }

    public function test_既存sourceへの再登録でuser_sourcesに重複しない(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);

        $source = Source::factory()->create([
            'feed_url' => 'https://example.com/feed.xml',
            'created_by_user_id' => null,
            'is_active' => true,
        ]);
        $user->sources()->attach($source->id);

        $mock = $this->createMock(FeedFetcher::class);
        $mock->expects($this->never())->method('fetchXml');
        $this->app->instance(FeedFetcher::class, $mock);

        $this->postJson('/api/sources', [
            'feed_url' => 'https://example.com/feed.xml',
        ])->assertOk();

        $this->assertDatabaseCount('user_sources', 1);
    }

    public function test_http_urlは422(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);

        $this->postJson('/api/sources', [
            'feed_url' => 'http://example.com/feed.xml',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.feed_url.0', 'HTTPSのURLを指定してください。');
    }

    public function test_ssrf拒否urlは422(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);
        $this->mockFetchXmlThrows(new \InvalidArgumentException('このURLには接続できません。'));

        $this->postJson('/api/sources', [
            'feed_url' => 'https://192.168.1.1/feed.xml',
        ])->assertUnprocessable()
            ->assertJsonStructure(['errors' => ['feed_url']]);
    }

    public function test_dns解決失敗は422(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);
        $this->mockFetchXmlThrows(new \InvalidArgumentException('フィードURLのホスト名を解決できませんでした。'));

        $this->postJson('/api/sources', [
            'feed_url' => 'https://nonexistent.invalid/feed.xml',
        ])->assertUnprocessable()
            ->assertJsonStructure(['errors' => ['feed_url']]);
    }

    public function test_httpエラーは422(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);

        $guzzleResponse = new GuzzleResponse(500);
        $laravelResponse = new LaravelResponse($guzzleResponse);
        $this->mockFetchXmlThrows(new RequestException($laravelResponse));

        $this->postJson('/api/sources', [
            'feed_url' => 'https://example.com/feed.xml',
        ])->assertUnprocessable()
            ->assertJsonStructure(['errors' => ['feed_url']]);
    }

    public function test_不正xmlは422(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);
        $this->mockFetchXml('<not-rss>invalid xml content</not-rss>');

        $this->postJson('/api/sources', [
            'feed_url' => 'https://example.com/feed.xml',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.feed_url.0', '有効なRSS/AtomフィードのURLを指定してください。');
    }

    public function test_取得失敗時にsourceとuser_sourcesに中途半端なデータが残らない(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);
        $this->mockFetchXmlThrows(new \InvalidArgumentException('エラー'));

        $this->postJson('/api/sources', [
            'feed_url' => 'https://example.com/feed.xml',
        ])->assertUnprocessable();

        $this->assertDatabaseMissing('sources', ['feed_url' => 'https://example.com/feed.xml']);
        $this->assertDatabaseCount('user_sources', 0);
    }

    public function test_不正xml時にsourceとuser_sourcesにデータが残らない(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);
        $this->mockFetchXml('<garbage>not a feed</garbage>');

        $this->postJson('/api/sources', [
            'feed_url' => 'https://example.com/feed.xml',
        ])->assertUnprocessable();

        $this->assertDatabaseMissing('sources', ['feed_url' => 'https://example.com/feed.xml']);
        $this->assertDatabaseCount('user_sources', 0);
    }

    public function test_未認証ユーザーはpost_sourcesを使えない(): void
    {
        $this->postJson('/api/sources', [
            'feed_url' => 'https://example.com/feed.xml',
        ])->assertUnauthorized();
    }

    public function test_メール未認証ユーザーはpost_sourcesを使えない(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->actingAs($user);

        $this->postJson('/api/sources', [
            'feed_url' => 'https://example.com/feed.xml',
        ])->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // POST /api/sources/{id}/subscribe
    // -------------------------------------------------------------------------

    public function test_共通sourceは購読できる(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);

        $source = Source::factory()->create([
            'created_by_user_id' => null,
            'is_active' => true,
        ]);

        $this->postJson("/api/sources/{$source->id}/subscribe")
            ->assertOk()
            ->assertJsonPath('message', 'ニュースソースを追加しました。');

        $this->assertDatabaseHas('user_sources', [
            'user_id' => $user->id,
            'source_id' => $source->id,
        ]);
    }

    public function test_自分が作成したカスタムsourceは購読できる(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);

        $source = Source::factory()->create([
            'created_by_user_id' => $user->id,
            'is_active' => true,
        ]);

        $this->postJson("/api/sources/{$source->id}/subscribe")
            ->assertOk()
            ->assertJsonPath('message', 'ニュースソースを追加しました。');

        $this->assertDatabaseHas('user_sources', [
            'user_id' => $user->id,
            'source_id' => $source->id,
        ]);
    }

    public function test_他ユーザー作成カスタムsourceをid直接指定すると404(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $otherUser = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);

        $source = Source::factory()->create([
            'created_by_user_id' => $otherUser->id,
            'is_active' => true,
        ]);

        $this->postJson("/api/sources/{$source->id}/subscribe")
            ->assertNotFound();

        $this->assertDatabaseMissing('user_sources', [
            'user_id' => $user->id,
            'source_id' => $source->id,
        ]);
    }

    public function test_無効なsourceは購読できない(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);

        $source = Source::factory()->create(['is_active' => false]);

        $this->postJson("/api/sources/{$source->id}/subscribe")
            ->assertNotFound();

        $this->assertDatabaseMissing('user_sources', [
            'user_id' => $user->id,
            'source_id' => $source->id,
        ]);
    }

    public function test_同じsourceを再追加しても重複しない(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);

        $source = Source::factory()->create(['created_by_user_id' => null]);

        $this->postJson("/api/sources/{$source->id}/subscribe")->assertOk();
        $this->postJson("/api/sources/{$source->id}/subscribe")->assertOk();

        $this->assertDatabaseCount('user_sources', 1);
    }

    public function test_未認証ユーザーはsourceを追加できない(): void
    {
        $source = Source::factory()->create(['is_active' => true]);

        $this->postJson("/api/sources/{$source->id}/subscribe")
            ->assertUnauthorized();

        $this->assertDatabaseMissing('user_sources', ['source_id' => $source->id]);
    }

    public function test_メール未認証ユーザーはsourceを追加できない(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->actingAs($user);

        $source = Source::factory()->create(['is_active' => true]);

        $this->postJson("/api/sources/{$source->id}/subscribe")
            ->assertForbidden();

        $this->assertDatabaseMissing('user_sources', [
            'user_id' => $user->id,
            'source_id' => $source->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // DELETE /api/sources/{id}/subscribe
    // -------------------------------------------------------------------------

    public function test_自分が購読済みなら解除できる(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);

        $source = Source::factory()->create(['created_by_user_id' => null]);
        $user->sources()->attach($source->id);

        $this->deleteJson("/api/sources/{$source->id}/subscribe")
            ->assertOk()
            ->assertJsonPath('message', 'ニュースソースの購読を解除しました。');

        $this->assertDatabaseMissing('user_sources', [
            'user_id' => $user->id,
            'source_id' => $source->id,
        ]);
    }

    public function test_他ユーザーが最初に作成したsourceでも正当に購読済みなら解除できる(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $otherUser = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);

        $source = Source::factory()->create([
            'created_by_user_id' => $otherUser->id,
            'is_active' => true,
        ]);
        // ユーザーは正当に購読済み（POST /api/sources 経由を想定）
        $user->sources()->attach($source->id);

        $this->deleteJson("/api/sources/{$source->id}/subscribe")
            ->assertOk()
            ->assertJsonPath('message', 'ニュースソースの購読を解除しました。');

        $this->assertDatabaseMissing('user_sources', [
            'user_id' => $user->id,
            'source_id' => $source->id,
        ]);
    }

    public function test_自分の購読解除は他ユーザーの購読状態に影響しない(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $otherUser = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);

        $source = Source::factory()->create(['created_by_user_id' => null]);
        $user->sources()->attach($source->id);
        $otherUser->sources()->attach($source->id);

        $this->deleteJson("/api/sources/{$source->id}/subscribe")->assertOk();

        // 自分の購読は解除済み
        $this->assertDatabaseMissing('user_sources', [
            'user_id' => $user->id,
            'source_id' => $source->id,
        ]);
        // 他ユーザーの購読は残っている
        $this->assertDatabaseHas('user_sources', [
            'user_id' => $otherUser->id,
            'source_id' => $source->id,
        ]);
    }

    public function test_未購読のsourceを解除しようとすると404(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);

        $source = Source::factory()->create();

        $this->deleteJson("/api/sources/{$source->id}/subscribe")
            ->assertNotFound();
    }

    public function test_sourceの購読解除をしてもsource本体は削除されない(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user);

        $source = Source::factory()->create();
        $user->sources()->attach($source->id);

        $this->deleteJson("/api/sources/{$source->id}/subscribe")->assertOk();

        $this->assertDatabaseHas('sources', ['id' => $source->id]);
    }

    public function test_未認証ユーザーはsourceの追加を解除できない(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $source = Source::factory()->create();
        $user->sources()->attach($source->id);

        $this->deleteJson("/api/sources/{$source->id}/subscribe")
            ->assertUnauthorized();

        $this->assertDatabaseHas('user_sources', [
            'user_id' => $user->id,
            'source_id' => $source->id,
        ]);
    }

    public function test_メール未認証ユーザーはsourceの追加を解除できない(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $source = Source::factory()->create();
        $user->sources()->attach($source->id);

        $this->actingAs($user);

        $this->deleteJson("/api/sources/{$source->id}/subscribe")
            ->assertForbidden();

        $this->assertDatabaseHas('user_sources', [
            'user_id' => $user->id,
            'source_id' => $source->id,
        ]);
    }
}
