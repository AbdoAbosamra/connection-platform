<?php

namespace Tests\Feature;

use App\Broadcasting\ConversationChannel;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\EmployerProfile;
use App\Models\JobSeekerProfile;
use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BroadcastingTest extends TestCase
{
    use RefreshDatabase;

    private function pair(): array
    {
        $employerUser = User::factory()->employer()->create();
        $employer = EmployerProfile::factory()->create(['user_id' => $employerUser->id]);
        $seekerUser = User::factory()->jobSeeker()->create();
        $seeker = JobSeekerProfile::factory()->create(['user_id' => $seekerUser->id]);
        $conversation = Conversation::factory()->create([
            'employer_profile_id' => $employer->id,
            'job_seeker_profile_id' => $seeker->id,
        ]);

        return compact('employerUser', 'seekerUser', 'conversation');
    }

    public function test_sending_a_message_broadcasts_on_the_conversation_channel(): void
    {
        Event::fake([MessageSent::class]);
        ['employerUser' => $eu, 'conversation' => $conversation] = $this->pair();

        Sanctum::actingAs($eu);
        $this->postJson("/api/employer/conversations/{$conversation->id}/messages", ['body' => 'Hi there'])
            ->assertCreated();

        Event::assertDispatched(MessageSent::class, function (MessageSent $event) use ($conversation) {
            $channels = $event->broadcastOn();

            return $event->message->conversation_id === $conversation->id
                && $channels[0] instanceof PrivateChannel
                && $channels[0]->name === 'private-conversation.'.$conversation->id;
        });
    }

    public function test_message_broadcast_payload_has_the_expected_shape(): void
    {
        ['employerUser' => $eu, 'conversation' => $conversation] = $this->pair();
        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $eu->id,
            'body' => 'Payload check',
        ]);

        $payload = (new MessageSent($message))->broadcastWith();

        $this->assertSame('Payload check', $payload['message']['body']);
        $this->assertSame($conversation->id, $payload['message']['conversation_id']);
        $this->assertArrayHasKey('sender', $payload['message']);
        $this->assertSame('message.sent', (new MessageSent($message))->broadcastAs());
    }

    // ── Channel authorization (tested at the rule level, independent of the
    //    configured broadcaster) ────────────────────────────────────────────

    public function test_conversation_channel_authorizes_only_participants_and_admins(): void
    {
        ['employerUser' => $eu, 'seekerUser' => $su, 'conversation' => $conversation] = $this->pair();
        $channel = new ConversationChannel;

        $this->assertTrue($channel->join($eu, $conversation), 'employer participant');
        $this->assertTrue($channel->join($su, $conversation), 'seeker participant');
        $this->assertTrue($channel->join(User::factory()->admin()->create(), $conversation), 'admin');

        $intruder = User::factory()->jobSeeker()->create();
        JobSeekerProfile::factory()->create(['user_id' => $intruder->id]);
        $this->assertFalse($channel->join($intruder, $conversation), 'outsider denied');
    }

    public function test_broadcasting_auth_endpoint_requires_authentication(): void
    {
        // The endpoint itself is guarded by auth:sanctum regardless of broadcaster.
        $this->postJson('/api/broadcasting/auth', [
            'socket_id' => '1.1',
            'channel_name' => 'private-App.Models.User.1',
        ])->assertUnauthorized();
    }

    public function test_authenticated_user_can_reach_the_auth_endpoint(): void
    {
        Sanctum::actingAs(User::factory()->create());

        // 200 (authorized) — proves the route + channel wiring resolve. Fine-grained
        // allow/deny is covered by the channel-class test above.
        $this->postJson('/api/broadcasting/auth', [
            'socket_id' => '1.1',
            'channel_name' => 'private-App.Models.User.1',
        ])->assertOk();
    }
}
