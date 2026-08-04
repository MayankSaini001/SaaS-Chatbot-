<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
<<<<<<< HEAD
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationResolved implements ShouldBroadcastNow
=======
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationResolved implements ShouldBroadcast
>>>>>>> origin/main
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversationId;

    public function __construct($conversationId)
    {
        $this->conversationId = $conversationId;
    }

    public function broadcastOn()
    {
        return new Channel('conversation.' . $this->conversationId);
    }

    public function broadcastAs()
    {
        return 'conversation.resolved';
    }

    public function broadcastWith()
    {
        return [
            'conversation_id' => $this->conversationId,
            'status'          => 'resolved',
        ];
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> origin/main
