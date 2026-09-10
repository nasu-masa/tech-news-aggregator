<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const DEFAULT_FEED_URLS = [
        'https://github.com/anthropics/claude-code/releases.atom',
        'https://openai.com/news/rss.xml',
        'https://react.dev/rss.xml',
        'https://feed.laravel-news.com/',
        'https://www.docker.com/feed/',
        'https://github.blog/feed/',
        'https://nextjs.org/feed.xml',
        'https://www.tomshardware.com/feeds.xml',
        'https://hnrss.org/frontpage',
        'https://tailwindcss.com/feeds/feed.xml',
        'https://gihyo.jp/feed/rss2',
        'https://dev.classmethod.jp/feed/',
        'https://codezine.jp/rss/new/20/index.xml',
    ];

    public function up(): void
    {
        Schema::table('sources', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('created_by_user_id');
        });

        DB::table('sources')
            ->whereIn('feed_url', self::DEFAULT_FEED_URLS)
            ->update(['is_default' => true]);
    }

    public function down(): void
    {
        Schema::table('sources', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
};
