<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

/**
 * Read Receipts — agent ne conversation kholi aur visitor ke unread
 * messages "read" mark ho gaye. Widget isko sun ke last visitor
 * message ke neeche "Seen" dikha deta hai, bina refresh kiye.
 */
class MessagesRead implements ShouldBroadcastNow
{
    use SerializesModels;

    public $conversationId;
    public $readAt;

    public function __construct($conversationId, $readAt = null)
    {
        $this->conversationId = $conversationId;
        $this->readAt = $readAt ?: now()->toIso8601String();
    }

    public function broadcastOn()
    {
        return new Channel('conversation.' . $this->conversationId);
    }

    public function broadcastAs()
    {
        return 'messages.read';
    }

    public function broadcastWith()
    {
        return [
            'conversation_id' => $this->conversationId,
            'read_at'         => $this->readAt,
        ];
    }
}
