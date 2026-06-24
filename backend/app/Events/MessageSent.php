<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when a message is sent, so the other participant's chat updates in
 * real time. With BROADCAST_CONNECTION=null (default/tests) this is a no-op —
 * the frontend keeps polling. With Reverb running it delivers instantly.
 */
class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.'.$this->message->conversation_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * The payload sent to subscribers — shaped like the REST message resource so
     * the frontend can reuse its rendering path.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $m = $this->message->loadMissing('sender:id,name,avatar');

        return [
            'message' => [
                'id' => $m->id,
                'conversation_id' => $m->conversation_id,
                'sender_id' => $m->sender_id,
                'body' => $m->body,
                'attachment_url' => $m->attachment_url,
                'read_at' => $m->read_at,
                'created_at' => $m->created_at?->toIso8601String(),
                'sender' => $m->sender ? [
                    'id' => $m->sender->id,
                    'name' => $m->sender->name,
                    'avatar' => $m->sender->avatar,
                ] : null,
            ],
        ];
    }
}
