<?php

namespace Tests\Feature;

use App\Models\UserArticle;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PostgresTimezoneTest extends TestCase
{
    // Read-only: safe to run against PostgreSQL without migrating any tables.
    public function test_jst_input_is_bound_as_the_correct_postgres_instant(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Requires a PostgreSQL connection.');
        }

        $this->assertSame('UTC', DB::selectOne('SHOW timezone')->TimeZone);
        $input = CarbonImmutable::parse('2026-08-21 09:00:00', 'Asia/Tokyo');
        $model = new UserArticle(['read_at' => $input]);
        $row = DB::selectOne(
            'SELECT CAST(? AS timestamptz) AS read_at',
            [$model->getAttributes()['read_at']],
        );
        $reloaded = (new UserArticle)->newFromBuilder((array) $row);

        $this->assertTrue($reloaded->read_at->equalTo($input));
        $this->assertSame('2026-08-21T00:00:00.000000Z', $reloaded->toArray()['read_at']);
    }
}
