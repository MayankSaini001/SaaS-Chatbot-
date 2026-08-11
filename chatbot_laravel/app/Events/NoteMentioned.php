<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

/**
 * Internal Collaboration — @Mention: jab koi agent internal note me
 * "@Full Name" likhta hai, us teammate ko unke private 'agent.{id}'
 * channel par notify karo. ConversationController@addNote isse
 * broadcast karta hai aur layouts/app.blade.php 'note.mentioned'
 * event sunta hai — yehi missing class thi jiski wajah se mention
 * chup-chaap fail ho raha tha.
 */
class NoteMentioned implements ShouldBroadcastNow
{
    use SerializesModels;

    public $agentId;
    public $conversationId;
    public $visitorName;
    public $authorName;
    public $snippet;

    public function __construct($agentId, $conversationId, $visitorName, $authorName, $snippet)
    {
        $this->agentId        = $agentId;
        $this->conversationId = $conversationId;
        $this->visitorName    = $visitorName;
        $this->authorName     = $authorName;
        $this->snippet        = $snippet;
    }

    public function broadcastOn()
    {
        return new Channel('agent.' . $this->agentId);
    }

    public function broadcastAs()
    {
        return 'note.mentioned';
    }

    public function broadcastWith()
    {
        return [
            'conversationId' => $this->conversationId,
            'visitorName'    => $this->visitorName,
            'authorName'     => $this->authorName,
            'snippet'        => $this->snippet,
        ];
    }
}