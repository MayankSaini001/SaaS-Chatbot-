<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message->load(['sender', 'conversation']);
    }

    public function broadcastOn()
    {
        return new Channel('conversation.' . $this->message->conversation_id);
    }

    public function broadcastAs()
    {
        return 'message.sent';
    }

    public function broadcastWith()
    {
        return [
            'id'           => $this->message->id,
            'body'         => $this->message->body,
            'attachment'   => $this->message->attachment,
            'sender_type'  => $this->message->sender_type,
            'created_at'   => $this->message->created_at,
            'agent_name'   => $this->message->sender?->name,
            'visitor_name' => $this->message->conversation?->visitor_name,
        ];
    }
}
