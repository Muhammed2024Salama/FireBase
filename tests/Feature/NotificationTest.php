<?php

namespace Tests\Feature;

use App\Jobs\SendNotificationJob;
use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Queue::fake();
    }

    public function test_authenticated_user_can_send_notification_to_users(): void
    {
        Sanctum::actingAs($this->user);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        DeviceToken::factory()->create([
            'user_id' => $user1->id,
            'token' => 'token_user_1',
        ]);

        DeviceToken::factory()->create([
            'user_id' => $user2->id,
            'token' => 'token_user_2',
        ]);

        $notificationData = [
            'title' => 'Test Notification',
            'body' => 'This is a test notification',
            'user_ids' => [$user1->id, $user2->id],
            'data' => ['key' => 'value'],
        ];

        $response = $this->postJson('/api/notifications/send', $notificationData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'count',
                    'message',
                    'sent_at',
                ],
            ]);

        Queue::assertPushed(SendNotificationJob::class, function ($job) use ($notificationData) {
            return $job->title === $notificationData['title']
                && $job->body === $notificationData['body']
                && $job->userIds === $notificationData['user_ids'];
        });
    }

    public function test_authenticated_user_can_send_notification_to_tokens(): void
    {
        Sanctum::actingAs($this->user);

        $notificationData = [
            'title' => 'Test Notification',
            'body' => 'This is a test notification',
            'tokens' => ['token1', 'token2', 'token3'],
            'data' => ['key' => 'value'],
        ];

        $response = $this->postJson('/api/notifications/send', $notificationData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'count',
                    'message',
                    'sent_at',
                ],
            ]);

        Queue::assertPushed(SendNotificationJob::class, function ($job) use ($notificationData) {
            return $job->title === $notificationData['title']
                && $job->body === $notificationData['body']
                && $job->tokens === $notificationData['tokens'];
        });
    }

    public function test_notification_sending_requires_authentication(): void
    {
        $notificationData = [
            'title' => 'Test Notification',
            'body' => 'This is a test notification',
            'user_ids' => [1],
        ];

        $response = $this->postJson('/api/notifications/send', $notificationData);

        $response->assertStatus(401);
    }

    public function test_notification_sending_requires_title(): void
    {
        Sanctum::actingAs($this->user);

        $notificationData = [
            'body' => 'This is a test notification',
            'user_ids' => [1],
        ];

        $response = $this->postJson('/api/notifications/send', $notificationData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_notification_sending_requires_body(): void
    {
        Sanctum::actingAs($this->user);

        $notificationData = [
            'title' => 'Test Notification',
            'user_ids' => [1],
        ];

        $response = $this->postJson('/api/notifications/send', $notificationData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['body']);
    }

    public function test_notification_sending_requires_either_user_ids_or_tokens(): void
    {
        Sanctum::actingAs($this->user);

        $notificationData = [
            'title' => 'Test Notification',
            'body' => 'This is a test notification',
        ];

        $response = $this->postJson('/api/notifications/send', $notificationData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_ids']);
    }

    public function test_notification_sending_with_empty_user_ids_returns_error(): void
    {
        Sanctum::actingAs($this->user);

        $notificationData = [
            'title' => 'Test Notification',
            'body' => 'This is a test notification',
            'user_ids' => [],
            'tokens' => [],
        ];

        $response = $this->postJson('/api/notifications/send', $notificationData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_ids']);
    }

    public function test_notification_sending_with_nonexistent_user_ids_returns_validation_error(): void
    {
        Sanctum::actingAs($this->user);

        $notificationData = [
            'title' => 'Test Notification',
            'body' => 'This is a test notification',
            'user_ids' => [99999, 99998],
        ];

        $response = $this->postJson('/api/notifications/send', $notificationData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_ids.0', 'user_ids.1']);
    }

    public function test_notification_sending_with_valid_user_ids_but_no_tokens_returns_success(): void
    {
        Sanctum::actingAs($this->user);

        $user = User::factory()->create();

        $notificationData = [
            'title' => 'Test Notification',
            'body' => 'This is a test notification',
            'user_ids' => [$user->id],
        ];

        $response = $this->postJson('/api/notifications/send', $notificationData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'count' => 0,
                ],
            ]);
    }

    public function test_notification_sending_validates_user_ids_are_integers(): void
    {
        Sanctum::actingAs($this->user);

        $notificationData = [
            'title' => 'Test Notification',
            'body' => 'This is a test notification',
            'user_ids' => ['invalid', 'ids'],
        ];

        $response = $this->postJson('/api/notifications/send', $notificationData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_ids.0', 'user_ids.1']);
    }

    public function test_notification_sending_validates_tokens_are_strings(): void
    {
        Sanctum::actingAs($this->user);

        $notificationData = [
            'title' => 'Test Notification',
            'body' => 'This is a test notification',
            'tokens' => [123, 456],
        ];

        $response = $this->postJson('/api/notifications/send', $notificationData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tokens.0', 'tokens.1']);
    }

    public function test_notification_sending_with_valid_data_structure(): void
    {
        Sanctum::actingAs($this->user);

        $user = User::factory()->create();
        DeviceToken::factory()->create([
            'user_id' => $user->id,
            'token' => 'test_token',
        ]);

        $notificationData = [
            'title' => 'Test Notification',
            'body' => 'This is a test notification',
            'user_ids' => [$user->id],
            'data' => [
                'type' => 'test',
                'action' => 'open',
            ],
        ];

        $response = $this->postJson('/api/notifications/send', $notificationData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonStructure([
                'data' => [
                    'count',
                    'message',
                    'sent_at',
                ],
            ]);
    }

    public function test_notification_sending_handles_arabic_content(): void
    {
        Sanctum::actingAs($this->user);

        $user = User::factory()->create();
        DeviceToken::factory()->create([
            'user_id' => $user->id,
            'token' => 'test_token',
        ]);

        $notificationData = [
            'title' => 'إشعار تجريبي',
            'body' => 'هذه رسالة إشعار تجريبية',
            'user_ids' => [$user->id],
        ];

        $response = $this->postJson('/api/notifications/send', $notificationData);

        $response->assertStatus(200);

        Queue::assertPushed(SendNotificationJob::class, function ($job) use ($notificationData) {
            return $job->title === $notificationData['title']
                && $job->body === $notificationData['body'];
        });
    }
}

