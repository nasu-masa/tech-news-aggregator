<?php

namespace Tests\Feature;

use App\Jobs\ImportFeedJob;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ImportFeedsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_デフォルトsourceは購読者なしでもjobが投入される(): void
    {
        Queue::fake();
        $source = Source::factory()->create(['is_default' => true, 'is_active' => true]);

        $this->artisan('feeds:import');

        Queue::assertPushed(ImportFeedJob::class, fn ($job) => $job->source->is($source));
    }

    public function test_購読者ありカスタムsourceのjobが投入される(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $source = Source::factory()->create(['is_default' => false, 'is_active' => true]);
        $user->sources()->attach($source->id);

        $this->artisan('feeds:import');

        Queue::assertPushed(ImportFeedJob::class, fn ($job) => $job->source->is($source));
    }

    public function test_購読者なしカスタムsourceのjobは投入されない(): void
    {
        Queue::fake();
        Source::factory()->create(['is_default' => false, 'is_active' => true]);

        $this->artisan('feeds:import');

        Queue::assertNothingPushed();
    }

    public function test_無効なsourceのjobは投入されない(): void
    {
        Queue::fake();
        Source::factory()->create(['is_default' => true, 'is_active' => false]);

        $this->artisan('feeds:import');

        Queue::assertNothingPushed();
    }
}
