<?php

namespace App\Console\Commands;

use App\Jobs\ImportFeedJob;
use App\Models\Source;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('feeds:import')]
#[Description('Dispatch import jobs for all active feeds')]
class ImportFeedsCommand extends Command
{
    public function handle()
    {
        $sources = Source::where('is_active', true)->get();

        foreach ($sources as $source) {
            ImportFeedJob::dispatch($source);
        }
    }
}
