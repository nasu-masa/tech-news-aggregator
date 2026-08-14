<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Source;
use App\Models\User;
use App\Models\UserArticle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証済みユーザーは記事一覧を取得できる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $source = Source::create([
            'name' => 'テストフィード',
            'feed_url' => 'https://example.com/feed.xml',
            'site_url' => 'https://example.com',
            'is_active' => true,
        ]);

        Article::create([
            'source_id' => $source->id,
            'title' => 'テスト記事',
            'summary' => 'テスト概要',
            'url' => 'https://example.com/article',
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/articles');

        $response
            ->assertOk()
            ->assertJsonFragment([
                'title' => 'テスト記事',
            ]);
    }

    public function test_未認証ユーザーは記事一覧を取得できない(): void
    {
        $response = $this->getJson('/api/articles');

        $response->assertUnauthorized();
    }

    public function test_メール未認証ユーザーは記事一覧を取得できない(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->getJson('/api/articles');

        $response->assertForbidden();
    }

    public function test_記事一覧は新しい順で返る(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $source = Source::create([
            'name' => 'テストフィード',
            'feed_url' => 'https://example.com/feed.xml',
            'site_url' => 'https://example.com',
            'is_active' => true,
        ]);

        Article::create([
            'source_id' => $source->id,
            'title' => '古い記事',
            'summary' => null,
            'url' => 'https://example.com/old',
            'published_at' => '2026-08-01 00:00:00',
        ]);

        Article::create([
            'source_id' => $source->id,
            'title' => '新しい記事',
            'summary' => null,
            'url' => 'https://example.com/new',
            'published_at' => '2026-08-10 00:00:00',
        ]);

        $response = $this->getJson('/api/articles');

        $response
            ->assertOk()
            ->assertJsonpath('data.0.title', '新しい記事')
            ->assertJsonpath('data.1.title', '古い記事');
    }

    public function test_公開日時がない記事は公開日時がある記事より後に返る(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        Article::factory()->create([
            'title' => '公開日時あり',
            'published_at' => now(),
        ]);

        Article::factory()->create([
            'title' => '公開日時なし',
            'published_at' => null,
        ]);

        $response = $this->getJson('/api/articles');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.title', '公開日時あり')
            ->assertJsonPath('data.1.title', '公開日時なし');
    }

    public function test_記事一覧に取得元sourceが含まれる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $source = Source::create([
            'name' => 'Laravel Blog',
            'feed_url' => 'https://example.com/feed.xml',
            'site_url' => 'https://example.com',
            'is_active' => true,
        ]);

        Article::create([
            'source_id' => $source->id,
            'title' => 'テスト記事',
            'summary' => null,
            'url' => 'https://example.com/article',
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/articles');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.source.name', 'Laravel Blog');
    }

    public function test_記事一覧は20件ずつページネーションされる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        Article::factory()
            ->count(21)
            ->create();

        $response = $this->getJson('/api/articles');

        $response
            ->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('per_page', 20);
    }

    public function test_記事一覧の2ページ目に残りの記事が返る(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        Article::factory()
            ->count(21)
            ->create();

        $response = $this->getJson('/api/articles?page=2');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('current_page', 2);
    }

    public function test_認証済みユーザーは記事詳細を取得できる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $article = Article::factory()->create([
            'title' => '詳細テスト記事',
        ]);

        $response = $this->getJson("/api/articles/{$article->id}");

        $response
            ->assertOk()
            ->assertJsonPath('title', '詳細テスト記事');
    }

    public function test_存在しない記事詳細は404になる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->getJson('/api/articles/999999');

        $response->assertNotFound();
    }

    public function test_記事詳細に取得元sourceが含まれる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $source = Source::factory()->create([
            'name' => 'Laravel Blog',
        ]);

        $article = Article::factory()->create([
            'source_id' => $source->id,
        ]);

        $response = $this->getJson("/api/articles/{$article->id}");

        $response->assertOk()
            ->assertJsonPath('source.name', 'Laravel Blog');
    }

    public function test_記事詳細にログインユーザー自身の記事状態が含まれる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $article = Article::factory()->create();

        UserArticle::create([
            'user_id' => $user->id,
            'article_id' => $article->id,
            'is_favorite' => true,
            'is_read' => true,
            'is_read_later' => false,
        ]);

        $response = $this->getJson("/api/articles/{$article->id}");

        $response
            ->assertOk()
            ->assertJsonPath('user_articles.0.is_favorite', true)
            ->assertJsonPath('user_articles.0.is_read', true)
            ->assertJsonPath('user_articles.0.is_read_later', false);
    }

    public function test_記事詳細に他ユーザーの記事状態は含まれない(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $otherUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $article = Article::factory()->create();

        UserArticle::create([
            'user_id' => $otherUser->id,
            'article_id' => $article->id,
            'is_favorite' => true,
            'is_read' => true,
            'is_read_later' => true,
        ]);

        $response = $this->getJson(
            "/api/articles/{$article->id}"
        );

        $response
            ->assertOk()
            ->assertJsonCount(0, 'user_articles');
    }

    public function test_記事詳細のsourceに購読状態が含まれる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $source = Source::factory()->create();

        $user->sources()->attach($source->id);

        $article = Article::factory()->create([
            'source_id' => $source->id,
        ]);

        $response = $this->getJson(
            "/api/articles/{$article->id}"
        );

        $response
            ->assertOk()
            ->assertJsonPath('source.is_subscribed', true);
    }

    public function test_未購読sourceの記事詳細では購読状態がfalseになる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $source = Source::factory()->create();

        $article = Article::factory()->create([
            'source_id' => $source->id,
        ]);

        $response = $this->getJson(
            "/api/articles/{$article->id}"
        );

        $response
            ->assertOk()
            ->assertJsonPath('source.is_subscribed', false);
    }

    public function test_未認証ユーザーは記事詳細を取得できない(): void
    {
        $article = Article::factory()->create();

        $response = $this->getJson("/api/articles/{$article->id}");

        $response->assertUnauthorized();
    }

    public function test_メール未認証ユーザーは記事詳細を取得できない(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $article = Article::factory()->create();

        $response = $this->getJson("/api/articles/{$article->id}");

        $response->assertForbidden();
    }

    public function test_タイトルのキーワードで記事を検索できる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        Article::factory()->create([
            'title' => 'Laravelの新機能',
            'summary' => 'PHPフレームワークの記事です',
        ]);

        Article::factory()->create([
            'title' => 'Reactの新機能',
            'summary' => 'フロントエンドの記事です',
        ]);

        $response = $this->getJson('/api/articles?keyword=Laravel');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Laravelの新機能');
    }

    public function test_概要のキーワードで記事を検索できる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        Article::factory()->create([
            'title' => 'PHPの記事',
            'summary' => 'Laravelの新機能について紹介します',
        ]);

        Article::factory()->create([
            'title' => 'Reactの記事',
            'summary' => 'フロントエンドの記事です',
        ]);

        $response = $this->getJson('/api/articles?keyword=Laravel');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'PHPの記事');
    }

    public function test_キーワードに一致する記事がない場合は空のdataが返る(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        Article::factory()->create([
            'title' => 'Reactの記事',
            'summary' => 'フロントエンドの記事です',
        ]);

        $response = $this->getJson('/api/articles?keyword=Laravel');

        $response
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_キーワード未指定なら通常の記事一覧を返す(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        Article::factory()->count(2)->create();

        $response = $this->getJson('/api/articles');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_sourceで記事一覧を絞り込める(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $sourceA = Source::factory()->create();
        $sourceB = Source::factory()->create();

        Article::factory()->create([
            'source_id' => $sourceA->id,
            'title' => 'SourceAの記事',
        ]);

        Article::factory()->create([
            'source_id' => $sourceB->id,
            'title' => 'SourceBの記事',
        ]);

        $response = $this->getJson("/api/articles?source_id={$sourceA->id}");

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'SourceAの記事');
    }

    public function test_キーワードとsourceを同時に指定して記事一覧を絞り込める(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $sourceA = Source::factory()->create();
        $sourceB = Source::factory()->create();

        Article::factory()->create([
            'source_id' => $sourceA->id,
            'title' => 'Laravelの記事',
        ]);

        Article::factory()->create([
            'source_id' => $sourceA->id,
            'title' => 'Reactの記事',
        ]);

        Article::factory()->create([
            'source_id' => $sourceB->id,
            'title' => 'Laravelの記事',
        ]);

        $response = $this->getJson(
            "/api/articles?keyword=Laravel&source_id={$sourceA->id}"
        );

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Laravelの記事');
    }

    public function test_source_idとsubscribed_onlyを同時に指定して絞り込める(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $subscribedSource = Source::factory()->create();
        $otherSource = Source::factory()->create();

        $user->sources()->attach($subscribedSource->id);

        Article::factory()->create([
            'source_id' => $subscribedSource->id,
            'title' => '購読中Sourceの記事',
        ]);

        Article::factory()->create([
            'source_id' => $otherSource->id,
            'title' => '未購読Sourceの記事',
        ]);

        $response = $this->getJson(
            "/api/articles?source_id={$subscribedSource->id}&subscribed_only=true"
        );

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', '購読中Sourceの記事');
    }

    public function test_source_idが不正な場合は422になる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->getJson(
            '/api/articles?source_id=abc'
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'source_id',
            ]);
    }

    public function test_subscribed_onlyが不正な場合は422になる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->getJson(
            '/api/articles?subscribed_only=invalid'
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'subscribed_only',
            ]);
    }

    public function test_存在しないsourceを指定した場合は空の記事一覧が返る(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        Article::factory()->create();

        $response = $this->getJson('/api/articles?source_id=999999');

        $response
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_subscribed_onlyで購読中sourceの記事だけ取得できる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $subscribedSource = Source::factory()->create();
        $otherSource = Source::factory()->create();

        $user->sources()->attach($subscribedSource->id);

        Article::factory()->create([
            'source_id' => $subscribedSource->id,
            'title' => '購読中の記事',
        ]);

        Article::factory()->create([
            'source_id' => $otherSource->id,
            'title' => '未購読の記事',
        ]);

        $response = $this->getJson(
            '/api/articles?subscribed_only=true'
        );

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', '購読中の記事');
    }

    public function test_subscribed_onlyがfalseなら全記事を取得できる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $subscribedSource = Source::factory()->create();
        $otherSource = Source::factory()->create();

        $user->sources()->attach($subscribedSource->id);

        Article::factory()->create([
            'source_id' => $subscribedSource->id,
            'title' => '購読中の記事',
        ]);

        Article::factory()->create([
            'source_id' => $otherSource->id,
            'title' => '未購読の記事',
        ]);

        $response = $this->getJson(
            '/api/articles?subscribed_only=false'
        );

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_記事一覧のsourceに購読状態が含まれる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $source = Source::factory()->create();

        $user->sources()->attach($source->id);

        Article::factory()->create([
            'source_id' => $source->id,
        ]);

        $response = $this->getJson('/api/articles');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.source.is_subscribed', true);
    }

    public function test_未購読sourceなら購読状態はfalseになる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $source = Source::factory()->create();

        Article::factory()->create([
            'source_id' => $source->id,
        ]);

        $response = $this->getJson('/api/articles');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.source.is_subscribed', false);
    }

    public function test_お気に入り状態を更新できる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $article = Article::factory()->create();

        $response = $this->patchJson(
            "/api/articles/{$article->id}/status",
            [
                'is_favorite' => true,
            ],
        );

        $response
            ->assertOk()
            ->assertJsonPath('is_favorite', true);

        $this->assertDatabaseHas('user_articles', [
            'user_id' => $user->id,
            'article_id' => $article->id,
            'is_favorite' => true,
        ]);
    }

    public function test_既存のお気に入り状態を更新できる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $article = Article::factory()->create();

        UserArticle::create([
            'user_id' => $user->id,
            'article_id' => $article->id,
            'is_favorite' => false,
        ]);

        $response = $this->patchJson(
            "/api/articles/{$article->id}/status",
            [
                'is_favorite' => true,
            ],
        );

        $response
            ->assertOk()
            ->assertJsonPath('is_favorite', true);

        $this->assertDatabaseHas('user_articles', [
            'user_id' => $user->id,
            'article_id' => $article->id,
            'is_favorite' => true,
        ]);
    }

    public function test_既読状態を更新できる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $article = Article::factory()->create();

        $response = $this->patchJson(
            "/api/articles/{$article->id}/status",
            [
                'is_read' => true,
            ],
        );

        $response
            ->assertOk()
            ->assertJsonPath('is_read', true);

        $this->assertDatabaseHas('user_articles', [
            'user_id' => $user->id,
            'article_id' => $article->id,
            'is_read' => true,
        ]);
    }

    public function test_あとで読む状態を更新できる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $article = Article::factory()->create();

        $response = $this->patchJson(
            "/api/articles/{$article->id}/status",
            [
                'is_read_later' => true,
            ],
        );

        $response
            ->assertOk()
            ->assertJsonPath('is_read_later', true);

        $this->assertDatabaseHas('user_articles', [
            'user_id' => $user->id,
            'article_id' => $article->id,
            'is_read_later' => true,
        ]);
    }

    public function test_複数の記事状態を同時に更新できる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $article = Article::factory()->create();

        $response = $this->patchJson(
            "/api/articles/{$article->id}/status",
            [
                'is_favorite' => true,
                'is_read' => true,
                'is_read_later' => true,
            ],
        );

        $response
            ->assertOk()
            ->assertJsonPath('is_favorite', true)
            ->assertJsonPath('is_read', true)
            ->assertJsonPath('is_read_later', true);

        $this->assertDatabaseHas('user_articles', [
            'user_id' => $user->id,
            'article_id' => $article->id,
            'is_favorite' => true,
            'is_read' => true,
            'is_read_later' => true,
        ]);
    }

    public function test_他ユーザーの記事状態は更新されない(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $otherUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $article = Article::factory()->create();

        UserArticle::create([
            'user_id' => $otherUser->id,
            'article_id' => $article->id,
            'is_favorite' => false,
        ]);

        $this->actingAs($user);

        $response = $this->patchJson(
            "/api/articles/{$article->id}/status",
            [
                'is_favorite' => true,
            ],
        );

        $response->assertOk();

        $this->assertDatabaseHas('user_articles', [
            'user_id' => $otherUser->id,
            'article_id' => $article->id,
            'is_favorite' => false,
        ]);

        $this->assertDatabaseHas('user_articles', [
            'user_id' => $user->id,
            'article_id' => $article->id,
            'is_favorite' => true,
        ]);
    }

    public function test_記事状態に不正なboolean値を送ると422になる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $article = Article::factory()->create();

        $response = $this->patchJson(
            "/api/articles/{$article->id}/status",
            [
                'is_favorite' => 'invalid',
            ],
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'is_favorite',
            ]);
    }

    public function test_未認証ユーザーは記事状態を更新できない(): void
    {
        $article = Article::factory()->create();

        $response = $this->patchJson(
            "/api/articles/{$article->id}/status",
            [
                'is_favorite' => true,
            ],
        );

        $response->assertUnauthorized();
    }

    public function test_メール未認証ユーザーは記事状態を更新できない(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $article = Article::factory()->create();

        $response = $this->patchJson(
            "/api/articles/{$article->id}/status",
            [
                'is_favorite' => true,
            ],
        );

        $response->assertForbidden();
    }

    public function test_存在しない記事の状態は更新できない(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->patchJson(
            '/api/articles/999999/status',
            [
                'is_favorite' => true,
            ],
        );

        $response->assertNotFound();
    }

    public function test_記事状態を何も送らない場合は422になる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $article = Article::factory()->create();

        $response = $this->patchJson(
            "/api/articles/{$article->id}/status",
            [],
        );

        $response->assertUnprocessable();
    }

    public function test_記事一覧にログインユーザー自身の記事状態が含まれる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $article = Article::factory()->create();

        UserArticle::create([
            'user_id' => $user->id,
            'article_id' => $article->id,
            'is_favorite' => true,
            'is_read' => true,
            'is_read_later' => false,
        ]);

        $response = $this->getJson('/api/articles');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.user_articles.0.is_favorite', true)
            ->assertJsonPath('data.0.user_articles.0.is_read', true)
            ->assertJsonPath('data.0.user_articles.0.is_read_later', false);
    }

    public function test_記事一覧に他ユーザーの記事状態は含まれない(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $otherUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $article = Article::factory()->create();

        UserArticle::create([
            'user_id' => $otherUser->id,
            'article_id' => $article->id,
            'is_favorite' => true,
            'is_read' => true,
            'is_read_later' => true,
        ]);

        $response = $this->getJson('/api/articles');

        $response
            ->assertOk()
            ->assertJsonCount(0, 'data.0.user_articles');
    }

}
