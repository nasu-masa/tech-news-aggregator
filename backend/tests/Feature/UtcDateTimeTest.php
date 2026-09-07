<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Source;
use App\Models\User;
use App\Models\UserArticle;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UtcDateTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_jst_dates_keep_the_same_instant_after_saving_and_serializing(): void
    {
        $jst = CarbonImmutable::parse('2026-08-21 09:00:00', 'Asia/Tokyo');
        $source = Source::factory()->create([
            'last_success_at' => $jst,
            'last_error_at' => $jst->toIso8601String(),
        ]);
        $article = Article::factory()->create([
            'source_id' => $source->id,
            'published_at' => $jst,
        ]);
        $status = UserArticle::create([
            'user_id' => User::factory()->create()->id,
            'article_id' => $article->id,
            'read_at' => $jst,
        ]);

        foreach ([[$source, 'last_success_at'], [$source, 'last_error_at'], [$article, 'published_at'], [$status, 'read_at']] as [$model, $field]) {
            $this->assertSame('2026-08-21 00:00:00', $model->getAttributes()[$field]);
            $model->refresh();
            $this->assertTrue($model->$field->equalTo($jst));
            $this->assertSame('UTC', $model->$field->timezoneName);
            $this->assertSame('2026-08-21T00:00:00.000000Z', $model->toArray()[$field]);
        }

        $this->assertSame('2026-08-21T09:00:00+09:00', $jst->toIso8601String());
    }

    public function test_utc_feed_strings_and_null_dates_are_preserved(): void
    {
        $article = Article::factory()->create(['published_at' => '2026-08-21 00:00:00']);
        $this->assertSame('2026-08-21T00:00:00.000000Z', $article->fresh()->toArray()['published_at']);

        $article->update(['published_at' => null]);
        $this->assertNull($article->fresh()->published_at);
    }
}
