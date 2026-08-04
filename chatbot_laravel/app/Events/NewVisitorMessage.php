<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class NewVisitorMessage implements ShouldBroadcastNow
{
    use SerializesModels;

    public $conversationId;
    public $visitorName;
    public $body;
    public $targetChannel;

    /**
     * @param string $targetChannel e.g. "agent.5" (assigned) or "tenant.3" (unassigned — notify everyone)
     */
    public function __construct($conversationId, $visitorName, $body, $targetChannel)
    {
        $this->conversationId = $conversationId;
        $this->visitorName    = $visitorName;
        $this->body           = \Illuminate\Support\Str::limit($body, 80);
        $this->targetChannel  = $targetChannel;
    }

    public function broadcastOn()
    {
        return new Channel($this->targetChannel);
    }

    public function broadcastAs()
    {
        return 'new.visitor.message';
    }
}
