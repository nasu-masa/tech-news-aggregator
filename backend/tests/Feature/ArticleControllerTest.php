<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Source;
use App\Models\User;
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
}
