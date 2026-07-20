<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NoteDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $noteId;
    public $conversationId;
    public $actorId;

    public function __construct(int $noteId, int $conversationId, int $actorId)
    {
        $this->noteId         = $noteId;
        $this->conversationId = $conversationId;
        $this->actorId        = $actorId;
    }

    public function broadcastOn()
    {
        return new Channel('conversation.' . $this->conversationId);
    }

    public function broadcastAs()
    {
        return 'note.deleted';
    }

    public function broadcastWith()
    {
        return [
            'id'       => $this->noteId,
            'actor_id' => $this->actorId,
        ];
    }
}
