<?php

namespace Tests\Unit;

use App\Jobs\ImportFeedJob;
use App\Models\Source;
use App\Services\FeedImporter;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class ImportFeedJobTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_指定したsourceをimporterへ渡す(): void
    {
        $source = new Source([
            'feed_url' => 'https://example.com/feed.xml',
        ]);

        $feedImporter = Mockery::mock(FeedImporter::class);

        $feedImporter
            ->shouldReceive('import')
            ->once()
            ->with($source);

        $job = new ImportFeedJob($source);

        $job->handle($feedImporter);
    }

    public function test_source_idをunique_idとして返す(): void
    {
        $source = new Source;
        $source->id = 123;

        $job = new ImportFeedJob($source);

        $this->assertSame('123', $job->uniqueId());
    }
}
