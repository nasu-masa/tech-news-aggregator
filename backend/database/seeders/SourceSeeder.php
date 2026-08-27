<?php

namespace Database\Seeders;

use App\Models\Source;
use Illuminate\Database\Seeder;

class SourceSeeder extends Seeder
{
    public function run(): void
    {
        $sources = [
            [
                'name' => 'Claude Code Releases',
                'feed_url' => 'https://github.com/anthropics/claude-code/releases.atom',
                'site_url' => 'https://github.com/anthropics/claude-code',
            ],
            [
                'name' => 'OpenAI News',
                'feed_url' => 'https://openai.com/news/rss.xml',
                'site_url' => 'https://openai.com/news',
            ],
            [
                'name' => 'React Blog',
                'feed_url' => 'https://react.dev/rss.xml',
                'site_url' => 'https://react.dev',
            ],
            [
                'name' => 'Laravel News',
                'feed_url' => 'https://feed.laravel-news.com/',
                'site_url' => 'https://laravel-news.com',
            ],
            [
                'name' => 'Docker Blog',
                'feed_url' => 'https://www.docker.com/feed/',
                'site_url' => 'https://www.docker.com/blog',
            ],
            [
                'name' => 'GitHub Blog',
                'feed_url' => 'https://github.blog/feed/',
                'site_url' => 'https://github.blog',
            ],
            [
                'name' => 'Next.js Blog',
                'feed_url' => 'https://nextjs.org/feed.xml',
                'site_url' => 'https://nextjs.org/blog',
            ],
            [
                'name' => "Tom's Hardware",
                'feed_url' => 'https://www.tomshardware.com/feeds.xml',
                'site_url' => 'https://www.tomshardware.com',
            ],
            [
                'name' => 'Hacker News',
                'feed_url' => 'https://hnrss.org/frontpage',
                'site_url' => 'https://news.ycombinator.com',
            ],
            [
                'name' => 'Tailwind CSS Blog',
                'feed_url' => 'https://tailwindcss.com/feeds/feed.xml',
                'site_url' => 'https://tailwindcss.com/blog',
            ],
        ];

        foreach ($sources as $source) {
            Source::updateOrCreate(
                ['feed_url' => $source['feed_url']],
                [
                    'name' => $source['name'],
                    'site_url' => $source['site_url'],
                    'is_active' => true,
                ],
            );
        }
    }
}
