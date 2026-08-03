<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class VisitorTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function broadcastOn()
    {
        return new Channel('conversation.' . $this->data['conversation_id']);
    }

    public function broadcastAs()
    {
        return 'visitor.typing';
    }

    public function broadcastWith()
    {
        return $this->data;
    }
}