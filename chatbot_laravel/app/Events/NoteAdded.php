<?php

namespace App\Events;

use App\Models\ConversationNote;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NoteAdded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $note;
    public $actorId;

    public function __construct(ConversationNote $note, int $actorId)
    {
        $this->note    = $note->load('author');
        $this->actorId = $actorId;
    }

    public function broadcastOn()
    {
        return new Channel('conversation.' . $this->note->conversation_id);
    }

    public function broadcastAs()
    {
        return 'note.added';
    }

    public function broadcastWith()
    {
        return [
            'id'         => $this->note->id,
            'body'       => $this->note->body,
            'author'     => $this->note->author?->name ?? 'Agent',
            'created_at' => $this->note->created_at->format('d M, h:i A'),
            'actor_id'   => $this->actorId,
        ];
    }
}
