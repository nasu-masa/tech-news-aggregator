<?php

namespace Tests\Feature;

use App\Jobs\ImportFeedJob;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ImportFeedJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_同じsourceのjobは重複してqueueへ投入されない(): void
    {
        Queue::fake();

        $source = Source::create([
            'name' => 'テストフィード',
            'feed_url' => 'https://example.com/feed.xml',
            'site_url' => 'https://example.com',
            'is_active' => true,
        ]);

        ImportFeedJob::dispatch($source);
        ImportFeedJob::dispatch($source);

        Queue::assertPushed(ImportFeedJob::class, 1);
    }

    public function test_別sourceのjobはそれぞれqueueへ投入される(): void
    {
        Queue::fake();

        $source1 = Source::create([
            'name' => 'フィード1',
            'feed_url' => 'https://example.com/feed-1.xml',
            'site_url' => 'https://example.com',
            'is_active' => true,
        ]);

        $source2 = Source::create([
            'name' => 'フィード2',
            'feed_url' => 'https://example.org/feed-2.xml',
            'site_url' => 'https://example.org',
            'is_active' => true,
        ]);

        ImportFeedJob::dispatch($source1);
        ImportFeedJob::dispatch($source2);

        Queue::assertPushed(ImportFeedJob::class, 2);
    }
}
