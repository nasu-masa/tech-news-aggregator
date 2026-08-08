<?php

namespace Tests\Unit;

use App\Models\Source;
use App\Services\ArticleSaver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleSaverTest extends TestCase
{
    use RefreshDatabase;

    public function test_新しい記事を保存できる(): void
    {
        $source = Source::create([
            'name' => 'テストフィード',
            'feed_url' => 'https://example.com/feed.xml',
            'site_url' => 'https://example.com',
            'is_active' => true,
        ]);

        $articles = [
            [
                'title' => 'テスト記事',
                'summary' => '記事の概要',
                'url' => 'https://example.com/articles/1',
                'published_at' => '2026-08-07 03:00:00',
            ],
        ];

        (new ArticleSaver)->save($source, $articles);

        $this->assertDatabaseHas('articles', [
            'source_id' => $source->id,
            'title' => 'テスト記事',
            'summary' => '記事の概要',
            'url' => 'https://example.com/articles/1',
        ]);
    }

    public function test_同じurlの記事は新規追加せず更新する(): void
    {
        $source = Source::create([
            'name' => 'テストフィード',
            'feed_url' => 'https://example.com/feed.xml',
            'site_url' => 'https://example.come',
            'is_active' => true,
        ]);

        $saver = new ArticleSaver;

        $saver->save($source, [
            [
                'title' => '変更前タイトル',
                'summary' => '変更前概要',
                'url' => 'https://example.com/articles/1',
                'published_at' => '2026-08-07 03:00:00',
            ],
        ]);

        $saver->save($source, [
            [
                'title' => '変更後タイトル',
                'summary' => '変更後概要',
                'url' => 'https://example.com/articles/1',
                'published_at' => '2026-08-07 03:00:00',
            ],
        ]);

        $this->assertDatabaseHas('articles', [
            'url' => 'https://example.com/articles/1',
            'title' => '変更後タイトル',
            'summary' => '変更後概要',
        ]);
    }

    public function test_複数の記事を保存できる(): void
    {
        $source = Source::create([
            'name' => 'テストフィード',
            'feed_url' => 'https://example.com/feed.xml',
            'site_url' => 'https://example.com',
            'is_active' => true,
        ]);

        $articles = [
            [
                'title' => '記事1',
                'summary' => '概要1',
                'url' => 'https://example.com/articles/1',
                'published_at' => '2026-08-07 03:00:00',
            ],
            [
                'title' => '記事2',
                'summary' => '概要2',
                'url' => 'https://example.com/articles/2',
                'published_at' => '2026-08-07 04:00:00',
            ],
        ];

        (new ArticleSaver)->save($source, $articles);

        $this->assertDatabaseCount('articles', 2);

        $this->assertDatabaseHas('articles', [
            'url' => 'https://example.com/articles/1',
            'title' => '記事1',
        ]);

        $this->assertDatabaseHas('articles', [
            'url' => 'https://example.com/articles/2',
            'title' => '記事2',
        ]);
    }

    public function test_同じurlを別sourceから取得しても最初のsourceを維持する(): void
    {
        $firstSource = Source::create([
            'name' => '最初のフィード',
            'feed_url' => 'https://example.com/feed-1.xml',
            'site_url' => 'https://example.com',
            'is_active' => true,
        ]);

        $secondSource = Source::create([
            'name' => '別のフィード',
            'feed_url' => 'https://example.org/feed-2.xml',
            'site_url' => 'https://example.org',
            'is_active' => true,
        ]);

        $saver = new ArticleSaver;

        $saver->save($firstSource, [
            [
                'title' => '変更前タイトル',
                'summary' => '変更前概要',
                'url' => 'https://example.com/articles/1',
                'published_at' => '2026-08-07 03:00:00',
            ],
        ]);

        $saver->save($secondSource, [
            [
                'title' => '変更後タイトル',
                'summary' => '変更後概要',
                'url' => 'https://example.com/articles/1',
                'published_at' => '2026-08-08 03:00:00',
            ],
        ]);

        $this->assertDatabaseCount('articles', 1);

        $this->assertDatabaseHas('articles', [
            'url' => 'https://example.com/articles/1',
            'source_id' => $firstSource->id,
            'title' => '変更後タイトル',
            'summary' => '変更後概要',
        ]);
    }
}
