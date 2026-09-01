<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_正しいパスワードでアカウントを削除できる(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($user);

        $response = $this->deleteJson('/api/user', [
            'password' => 'password123',
        ]);

        $response->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_誤ったパスワードではアカウントを削除できない(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($user);

        $response = $this->deleteJson('/api/user', [
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_パスワードなしではアカウントを削除できない(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->deleteJson('/api/user', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_未認証ユーザーはアカウントを削除できない(): void
    {
        $response = $this->deleteJson('/api/user', [
            'password' => 'password123',
        ]);

        $response->assertUnauthorized();
    }

    public function test_退会後も他ユーザー購読中の_sourceと_articleが残りcreated_by_user_idが_nul_lになる(): void
    {
        $creator = User::factory()->create(['password' => bcrypt('password123')]);
        $subscriber = User::factory()->create();

        $source = Source::factory()->create(['created_by_user_id' => $creator->id]);
        $article = Article::factory()->create(['source_id' => $source->id]);
        $subscriber->sources()->attach($source->id);

        $this->actingAs($creator)->deleteJson('/api/user', ['password' => 'password123']);

        $this->assertDatabaseHas('sources', ['id' => $source->id, 'created_by_user_id' => null]);
        $this->assertDatabaseHas('articles', ['id' => $article->id]);
        $this->assertDatabaseHas('user_sources', ['user_id' => $subscriber->id, 'source_id' => $source->id]);
    }

    public function test_退会後に購読者がいない_sourceも_nul_lになり残る(): void
    {
        $creator = User::factory()->create(['password' => bcrypt('password123')]);
        $source = Source::factory()->create(['created_by_user_id' => $creator->id]);

        $this->actingAs($creator)->deleteJson('/api/user', ['password' => 'password123']);

        $this->assertDatabaseHas('sources', ['id' => $source->id, 'created_by_user_id' => null]);
    }
}
