<?php

namespace App\Jobs;

use App\Models\Source;
use App\Services\FeedImporter;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ImportFeedJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(
        public Source $source,
    ) {}

    public function handle(FeedImporter $feedImporter): void
    {
        $feedImporter->import($this->source);
    }

    public function uniqueId(): string
    {
        return (string) $this->source->getKey();
    }
}
