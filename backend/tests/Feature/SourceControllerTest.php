<?php

namespace Tests\Feature;

use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SourceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_共通sourceは全ユーザーに返る(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        Source::factory()->create([
            'name' => '共通Source',
            'created_by_user_id' => null,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/sources');

        $response
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', '共通Source');
    }

    public function test_自分が作成したsourceは取得できる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        Source::factory()->create([
            'name' => '自分のSource',
            'created_by_user_id' => $user->id,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/sources');

        $response
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', '自分のSource');
    }

    public function test_他ユーザーが作成したsourceは取得できない(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $otherUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        Source::factory()->create([
            'name' => '他ユーザーのSource',
            'created_by_user_id' => $otherUser->id,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/sources');

        $response
            ->assertOk()
            ->assertJsonCount(0);
    }

    public function test_共通sourceと自分のsourceだけ返り他ユーザーのsourceは除外される(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $otherUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        Source::factory()->create([
            'name' => '共通Source',
            'created_by_user_id' => null,
            'is_active' => true,
        ]);

        Source::factory()->create([
            'name' => '自分のSource',
            'created_by_user_id' => $user->id,
            'is_active' => true,
        ]);

        Source::factory()->create([
            'name' => '他ユーザーのSource',
            'created_by_user_id' => $otherUser->id,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/sources');

        $response
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonFragment(['name' => '共通Source'])
            ->assertJsonFragment(['name' => '自分のSource'])
            ->assertJsonMissing(['name' => '他ユーザーのSource']);
    }

    public function test_自分のsourceにもis_subscribedが正しく返る(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $subscribedSource = Source::factory()->create([
            'created_by_user_id' => $user->id,
            'is_active' => true,
        ]);

        Source::factory()->create([
            'created_by_user_id' => $user->id,
            'is_active' => true,
        ]);

        $user->sources()->attach($subscribedSource->id);

        $response = $this->getJson('/api/sources');

        $response
            ->assertOk()
            ->assertJsonFragment([
                'id' => $subscribedSource->id,
                'is_subscribed' => true,
            ]);
    }

    public function test_source一覧で購読状態を確認できる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $subscribedSource = Source::factory()->create([
            'name' => '購読中Source',
            'is_active' => true,
        ]);

        Source::factory()->create([
            'name' => '未購読Source',
            'is_active' => true,
        ]);

        $user->sources()->attach($subscribedSource->id);

        $response = $this->getJson('/api/sources');

        $response
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonFragment([
                'name' => '購読中Source',
                'is_subscribed' => true,
            ])
            ->assertJsonFragment([
                'name' => '未購読Source',
                'is_subscribed' => false,
            ]);
    }

    public function test_source一覧は名前順で返る(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        Source::factory()->create([
            'name' => 'React Blog',
            'is_active' => true,
        ]);

        Source::factory()->create([
            'name' => 'Laravel Blog',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/sources');

        $response
            ->assertOk()
            ->assertJsonPath('0.name', 'Laravel Blog')
            ->assertJsonPath('1.name', 'React Blog');
    }

    public function test_無効なsourceはsource一覧に含まれない(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        Source::factory()->create([
            'name' => '有効Source',
            'is_active' => true,
        ]);

        Source::factory()->create([
            'name' => '無効Source',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/sources');

        $response
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', '有効Source');
    }

    public function test_無効なsourceは購読できない(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $source = Source::factory()->create([
            'is_active' => false,
        ]);

        $response = $this->postJson(
            "/api/sources/{$source->id}/subscribe"
        );

        $response->assertNotFound();

        $this->assertDatabaseMissing('user_sources', [
            'user_id' => $user->id,
            'source_id' => $source->id,
        ]);
    }

    public function test_未認証ユーザーはsource一覧を取得できない(): void
    {
        $response = $this->getJson('/api/sources');

        $response->assertUnauthorized();
    }

    public function test_メール未認証ユーザはsource一覧を取得できない(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->getJson('/api/sources');

        $response->assertForbidden();
    }

    public function test_ニュースソースを追加できる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $source = Source::factory()->create();

        $response = $this->postJson(
            "/api/sources/{$source->id}/subscribe"
        );

        $response
            ->assertOk()
            ->assertJsonPath('message', 'ニュースソースを追加しました。');

        $this->assertDatabaseHas('user_sources', [
            'user_id' => $user->id,
            'source_id' => $source->id,
        ]);
    }

    public function test_メール未認証ユーザーはsourceを追加できない(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $source = Source::factory()->create([
            'is_active' => true,
        ]);

        $response = $this->postJson(
            "/api/sources/{$source->id}/subscribe"
        );

        $response->assertForbidden();

        $this->assertDatabaseMissing('user_sources', [
            'user_id' => $user->id,
            'source_id' => $source->id,
        ]);
    }

    public function test_未認証ユーザーはsourceを追加できない(): void
    {
        $source = Source::factory()->create([
            'is_active' => true,
        ]);

        $response = $this->postJson(
            "/api/sources/{$source->id}/subscribe"
        );

        $response->assertUnauthorized();

        $this->assertDatabaseMissing('user_sources', [
            'source_id' => $source->id,
        ]);
    }

    public function test_同じsourceを再追加しても重複しない(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $source = Source::factory()->create();

        $this->postJson("/api/sources/{$source->id}/subscribe")
            ->assertOk();

        $this->postJson("/api/sources/{$source->id}/subscribe")
            ->assertOk();

        $this->assertDatabaseCount('user_sources', 1);
    }

    public function test_ニュースソースの追加を解除できる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $source = Source::factory()->create();

        $user->sources()->attach($source->id);

        $response = $this->deleteJson(
            "/api/sources/{$source->id}/subscribe"
        );

        $response
            ->assertOk()
            ->assertJsonPath('message', 'ニュースソースの購読を解除しました。');

        $this->assertDatabaseMissing('user_sources', [
            'user_id' => $user->id,
            'source_id' => $source->id,
        ]);
    }

    public function test_未認証ユーザーはsourceの追加を解除できない(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $source = Source::factory()->create();

        $user->sources()->attach($source->id);

        $response = $this->deleteJson(
            "/api/sources/{$source->id}/subscribe"
        );

        $response->assertUnauthorized();

        $this->assertDatabaseHas('user_sources', [
            'user_id' => $user->id,
            'source_id' => $source->id,
        ]);
    }

    public function test_メール未認証ユーザーはsourceの追加を解除できない(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $source = Source::factory()->create();

        $user->sources()->attach($source->id);

        $this->actingAs($user);

        $response = $this->deleteJson(
            "/api/sources/{$source->id}/subscribe"
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('user_sources', [
            'user_id' => $user->id,
            'source_id' => $source->id,
        ]);
    }

    public function test_sourceの購読解除をしてもsource本体は削除されない(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $source = Source::factory()->create();

        $user->sources()->attach($source->id);

        $this->deleteJson(
            "/api/sources/{$source->id}/subscribe"
        )->assertOk();

        $this->assertDatabaseHas('sources', [
            'id' => $source->id,
        ]);
    }

    public function test_未購読のsourceを解除しても正常終了する(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $source = Source::factory()->create();

        $response = $this->deleteJson(
            "/api/sources/{$source->id}/subscribe"
        );

        $response
            ->assertOk()
            ->assertJsonPath('message', 'ニュースソースの購読を解除しました。');
    }
}
