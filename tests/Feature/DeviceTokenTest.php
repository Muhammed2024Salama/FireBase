<?php

namespace Tests\Feature;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeviceTokenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_authenticated_user_can_store_device_token(): void
    {
        Sanctum::actingAs($this->user);

        $tokenData = [
            'token' => 'test_device_token_12345',
            'platform' => 'ios',
        ];

        $response = $this->postJson('/api/device-tokens', $tokenData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'token',
                    'platform',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('device_tokens', [
            'user_id' => $this->user->id,
            'token' => 'test_device_token_12345',
            'platform' => 'ios',
        ]);
    }

    public function test_device_token_storage_requires_authentication(): void
    {
        $tokenData = [
            'token' => 'test_device_token_12345',
            'platform' => 'ios',
        ];

        $response = $this->postJson('/api/device-tokens', $tokenData);

        $response->assertStatus(401);
    }

    public function test_device_token_storage_requires_token(): void
    {
        Sanctum::actingAs($this->user);

        $tokenData = [
            'platform' => 'ios',
        ];

        $response = $this->postJson('/api/device-tokens', $tokenData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    }

    public function test_device_token_storage_accepts_valid_platform(): void
    {
        Sanctum::actingAs($this->user);

        $tokenData = [
            'token' => 'test_device_token_12345',
            'platform' => 'android',
        ];

        $response = $this->postJson('/api/device-tokens', $tokenData);

        $response->assertStatus(201);
    }

    public function test_device_token_storage_accepts_web_platform(): void
    {
        Sanctum::actingAs($this->user);

        $tokenData = [
            'token' => 'test_device_token_12345',
            'platform' => 'web',
        ];

        $response = $this->postJson('/api/device-tokens', $tokenData);

        $response->assertStatus(201);
    }

    public function test_device_token_storage_updates_existing_token(): void
    {
        Sanctum::actingAs($this->user);

        $existingToken = DeviceToken::factory()->create([
            'user_id' => $this->user->id,
            'token' => 'existing_token',
            'platform' => 'ios',
        ]);

        $tokenData = [
            'token' => 'existing_token',
            'platform' => 'android',
        ];

        $response = $this->postJson('/api/device-tokens', $tokenData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('device_tokens', [
            'token' => 'existing_token',
            'platform' => 'android',
        ]);

        $this->assertDatabaseCount('device_tokens', 1);
    }

    public function test_authenticated_user_can_delete_device_token(): void
    {
        Sanctum::actingAs($this->user);

        $deviceToken = DeviceToken::factory()->create([
            'user_id' => $this->user->id,
            'token' => 'test_token_to_delete',
        ]);

        $response = $this->deleteJson('/api/device-tokens', [
            'token' => 'test_token_to_delete',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Device token deleted successfully',
            ]);

        $this->assertDatabaseMissing('device_tokens', [
            'id' => $deviceToken->id,
        ]);
    }

    public function test_device_token_deletion_requires_authentication(): void
    {
        $response = $this->deleteJson('/api/device-tokens', [
            'token' => 'test_token',
        ]);

        $response->assertStatus(401);
    }

    public function test_device_token_deletion_requires_token(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson('/api/device-tokens', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    }

    public function test_device_token_deletion_returns_404_for_nonexistent_token(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson('/api/device-tokens', [
            'token' => 'nonexistent_token',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Device token not found',
            ]);
    }
}

