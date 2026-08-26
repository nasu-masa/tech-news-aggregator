<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Source;
use App\Models\User;
use App\Models\UserArticle;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException(
                'DevelopmentSeeder can only be run in the local environment.'
            );
        }

        $user = User::updateOrCreate(
            ['email' => 'dev@example.test'],
            [
                'name' => 'Development User',
                'password' => Hash::make('password'),
                'email_verified_at' => CarbonImmutable::parse(
                    '2026-08-01 00:00:00+09:00'
                ),
            ],
        );

        $sourceDefinitions = [
            'backend' => [
                'name' => 'Development Backend News',
                'feed_url' => 'https://backend.example.test/feed.xml',
                'site_url' => 'https://backend.example.test',
            ],
            'frontend' => [
                'name' => 'Development Frontend News',
                'feed_url' => 'https://frontend.example.test/feed.xml',
                'site_url' => 'https://frontend.example.test',
            ],
            'ai' => [
                'name' => 'Development AI News',
                'feed_url' => 'https://ai.example.test/feed.xml',
                'site_url' => 'https://ai.example.test',
            ],
            'infrastructure' => [
                'name' => 'Development Infrastructure News',
                'feed_url' => 'https://infrastructure.example.test/feed.xml',
                'site_url' => 'https://infrastructure.example.test',
            ],
        ];

        $sources = collect($sourceDefinitions)->mapWithKeys(
            function (array $definition, string $key): array {
                $source = Source::updateOrCreate(
                    ['feed_url' => $definition['feed_url']],
                    [
                        'name' => $definition['name'],
                        'site_url' => $definition['site_url'],
                        'is_active' => true,
                    ],
                );

                return [$key => $source];
            }
        );

        $user->sources()->sync(
            $sources->only(['backend', 'frontend', 'ai'])->pluck('id')->all()
        );

        $publishedAt = CarbonImmutable::parse('2026-08-20 12:00:00+09:00');
        $articleSequence = 0;

        foreach ($sources as $sourceKey => $source) {
            for ($number = 1; $number <= 6; $number++) {
                $articleSequence++;
                $numberLabel = str_pad((string) $number, 2, '0', STR_PAD_LEFT);

                $article = Article::updateOrCreate(
                    [
                        'url' => "https://articles.example.test/{$sourceKey}/{$numberLabel}",
                    ],
                    [
                        'source_id' => $source->id,
                        'title' => "{$source->name} Article {$numberLabel}",
                        'translated_title' => $this->articleTranslatedTitle($sourceKey, $number),
                        'summary' => $number % 2 === 0
                            ? "Fixed development summary for {$sourceKey} article {$numberLabel}."
                            : null,
                        'published_at' => $sourceKey === 'infrastructure' && $number === 6
                            ? null
                            : $publishedAt->subHours($articleSequence - 1),
                    ],
                );

                $state = $this->articleState($sourceKey, $number);

                if ($state !== null) {
                    UserArticle::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'article_id' => $article->id,
                        ],
                        $state,
                    );
                } else {
                    UserArticle::query()
                        ->where('user_id', $user->id)
                        ->where('article_id', $article->id)
                        ->delete();
                }
            }
        }
    }

    /**
     * @return array<string, bool|string|CarbonImmutable|null>|null
     */
    private function articleState(string $sourceKey, int $number): ?array
    {
        $baseState = [
            'is_read' => false,
            'is_favorite' => false,
            'is_read_later' => false,
            'memo' => null,
            'read_at' => null,
        ];

        return match ($number) {
            1 => null,
            2 => $baseState,
            3 => [
                ...$baseState,
                'is_read' => true,
                'read_at' => CarbonImmutable::parse('2026-08-21 09:00:00+09:00'),
            ],
            4 => [
                ...$baseState,
                'is_favorite' => true,
            ],
            5 => [
                ...$baseState,
                'is_read_later' => true,
            ],
            6 => $this->combinedArticleState($sourceKey, $baseState),
        };
    }

    private function articleTranslatedTitle(string $sourceKey, int $number): ?string
    {
        return match (true) {
            $sourceKey === 'backend' && $number === 1 => 'RustによるWebサーバーのパフォーマンス最適化',
            $sourceKey === 'backend' && $number === 3 => 'マイクロサービスアーキテクチャの最新トレンド',
            $sourceKey === 'backend' && $number === 5 => 'GraphQL vs REST：2026年のAPI設計指南',
            $sourceKey === 'frontend' && $number === 1 => 'React 20の新機能と移行ガイド',
            $sourceKey === 'frontend' && $number === 3 => 'TypeScriptの型安全なフォームバリデーション',
            $sourceKey === 'frontend' && $number === 5 => 'Webパフォーマンス計測の実践テクニック',
            $sourceKey === 'ai' && $number === 1 => 'LLMを活用したコードレビューの自動化',
            $sourceKey === 'ai' && $number === 3 => 'RAGアーキテクチャの設計パターン比較',
            $sourceKey === 'ai' && $number === 5 => 'AIエージェントの信頼性を高める手法',
            $sourceKey === 'infrastructure' && $number === 1 => 'Kubernetesクラスタのコスト最適化とOPA Gatekeeperの活用',
            $sourceKey === 'infrastructure' && $number === 3 => 'GitOpsを用いたゼロダウンタイムデプロイ戦略',
            $sourceKey === 'infrastructure' && $number === 5 => '可観測性プラットフォームの統合運用ガイド',
            default => null,
        };
    }

    /**
     * @param  array<string, bool|string|CarbonImmutable|null>  $baseState
     * @return array<string, bool|string|CarbonImmutable|null>
     */
    private function combinedArticleState(string $sourceKey, array $baseState): array
    {
        return match ($sourceKey) {
            'backend' => [
                ...$baseState,
                'is_read' => true,
                'is_favorite' => true,
                'memo' => 'Review this backend article.',
                'read_at' => CarbonImmutable::parse('2026-08-21 10:00:00+09:00'),
            ],
            'frontend' => [
                ...$baseState,
                'is_favorite' => true,
                'is_read_later' => true,
                'memo' => 'Compare this frontend approach.',
            ],
            'ai' => [
                ...$baseState,
                'is_read' => true,
                'is_read_later' => true,
                'memo' => 'Follow up on this AI article.',
                'read_at' => CarbonImmutable::parse('2026-08-21 11:00:00+09:00'),
            ],
            'infrastructure' => [
                ...$baseState,
                'is_read' => true,
                'is_favorite' => true,
                'is_read_later' => true,
                'memo' => 'Discuss this infrastructure article.',
                'read_at' => CarbonImmutable::parse('2026-08-21 12:00:00+09:00'),
            ],
        };
    }
}
