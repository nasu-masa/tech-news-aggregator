<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        return [
            'source_id' => Source::factory(),
            'title' => fake()->sentence(),
            'summary' => fake()->paragraph(),
            'url' => fake()->unique()->url(),
            'published_at' => now(),
        ];
    }
}
