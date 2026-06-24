<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    /** A minimal database notification for exercising the feed endpoints. */
    private function notify(User $user): void
    {
        $user->notify(new class extends Notification
        {
            public function via($n): array
            {
                return ['database'];
            }

            public function toArray($n): array
            {
                return ['type' => 'test', 'message' => 'hello'];
            }
        });
    }

    public function test_user_can_list_notifications_and_see_unread_count(): void
    {
        $user = User::factory()->create();
        $this->notify($user);
        $this->notify($user);

        Sanctum::actingAs($user);
        $this->getJson('/api/notifications')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/notifications/unread-count')->assertOk()->assertJsonPath('unread', 2);
    }

    public function test_user_can_mark_a_single_notification_read(): void
    {
        $user = User::factory()->create();
        $this->notify($user);
        $id = $user->notifications()->first()->id;

        Sanctum::actingAs($user);
        $this->postJson("/api/notifications/{$id}/read")->assertOk();
        $this->assertEquals(0, $user->unreadNotifications()->count());
    }

    public function test_user_can_mark_all_read(): void
    {
        $user = User::factory()->create();
        $this->notify($user);
        $this->notify($user);

        Sanctum::actingAs($user);
        $this->postJson('/api/notifications/read-all')->assertOk();
        $this->assertEquals(0, $user->unreadNotifications()->count());
    }

    public function test_user_cannot_read_another_users_notification(): void
    {
        $owner = User::factory()->create();
        $this->notify($owner);
        $id = $owner->notifications()->first()->id;

        Sanctum::actingAs(User::factory()->create());
        $this->postJson("/api/notifications/{$id}/read")->assertNotFound();
    }
}
