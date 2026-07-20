<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class ConversationAssigned implements ShouldBroadcastNow
{
    use SerializesModels;

    public $conversationId;
    public $agentId;
    public $visitorName;
    public $agentName;

    public function __construct($conversationId, $agentId, $visitorName, $agentName = null)
    {
        $this->conversationId = $conversationId;
        $this->agentId        = $agentId;
        $this->visitorName    = $visitorName;
        $this->agentName      = $agentName;
    }

    public function broadcastOn()
    {
        return new Channel('agent.' . $this->agentId);
    }

    public function broadcastAs()
    {
        return 'conversation.assigned';
    }

    public function broadcastWith()
    {
        return [
            'conversationId' => $this->conversationId,
            'agentId'        => $this->agentId,
            'visitorName'    => $this->visitorName,
            'agentName'      => $this->agentName,
        ];
    }
}
