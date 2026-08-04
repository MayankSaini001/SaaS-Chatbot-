<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class NewConversation implements ShouldBroadcastNow
{
    use SerializesModels;

    public $conversationId;
    public $visitorName;
    public $tenantId;

    public function __construct($conversationId, $visitorName, $tenantId)
    {
        $this->conversationId = $conversationId;
        $this->visitorName    = $visitorName;
        $this->tenantId       = $tenantId;
    }

    public function broadcastOn()
    {
        return new Channel('tenant.' . $this->tenantId);
    }

    public function broadcastAs()
    {
        return 'new.conversation';
    }
}
