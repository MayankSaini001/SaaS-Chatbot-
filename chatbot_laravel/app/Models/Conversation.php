<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    const INACTIVITY_WARN_MINUTES  = 10;
    const INACTIVITY_CLOSE_MINUTES = 15;

    const SYS_WARN_BODY  = '⏳ No activity for 10 minutes. This conversation will be automatically resolved in 5 minutes unless someone replies.';
    const SYS_CLOSE_BODY = '🔒 This conversation was automatically resolved after 15 minutes of inactivity.';

    protected $fillable = [
        'widget_id', 'tenant_id', 'agent_id',
        'visitor_name', 'visitor_email',
        'visitor_ip', 'visitor_page', 'status',
        'session_token', 'visitor_info_collected',
        'rating', 'rating_feedback',
    ];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    // Alias — blade mein $conversation->assignedAgent se use hoga
    public function assignedAgent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function notes()
    {
        return $this->hasMany(ConversationNote::class)->latest();
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'conversation_tag');
    }

    public function minutesInactive(): int
    {
        $lastMessage = $this->messages()
            ->where('sender_type', '!=', 'system')
            ->latest()
            ->first();

        $lastActivity = $lastMessage ? $lastMessage->created_at : $this->created_at;

        return (int) floor($lastActivity->diffInMinutes(now()));
    }

    public function runInactivityCheck(): void
    {
        if ($this->status !== 'open') {
            return;
        }

        // An agent must have replied at least once before the inactivity
        // timer applies. A conversation still waiting on its first agent
        // response (e.g. left outside business hours) should stay open
        // indefinitely — auto-resolving it would silently drop the
        // visitor's message before anyone ever saw it.
        $agentHasReplied = $this->messages()
            ->where('sender_type', 'agent')
            ->exists();

        if (!$agentHasReplied) {
            return;
        }

        $lastMessage = $this->messages()
            ->where('sender_type', '!=', 'system')
            ->latest()
            ->first();

        $lastActivity    = $lastMessage ? $lastMessage->created_at : $this->created_at;
        $minutesInactive = (int) floor($lastActivity->diffInMinutes(now()));

        if ($minutesInactive >= self::INACTIVITY_CLOSE_MINUTES) {
		$alreadyClosed = $this->messages()
			->where('sender_type', 'system')
			->where('body', self::SYS_CLOSE_BODY)
			->exists();

            if (!$alreadyClosed) {
                $msg = $this->messages()->create([
                    'sender_type' => 'system',
                    'sender_id'   => null,
                    'body'        => self::SYS_CLOSE_BODY,
                    'is_read'     => false,
                ]);
                $this->broadcastSystem($msg);
            }

            $this->update(['status' => 'resolved']);

            try {
                event(new \App\Events\ConversationResolved($this->id));
            } catch (\Throwable $e) {}

            $this->sendTranscriptEmail();

            return;
        }

        if ($minutesInactive >= self::INACTIVITY_WARN_MINUTES) {
            $alreadyWarned = $this->messages()
			->where('sender_type', 'system')
			->where('body', self::SYS_WARN_BODY)
			->exists();

            if (!$alreadyWarned) {
                $msg = $this->messages()->create([
                    'sender_type' => 'system',
                    'sender_id'   => null,
                    'body'        => self::SYS_WARN_BODY,
                    'is_read'     => false,
                ]);
                $this->broadcastSystem($msg);
            }
        }
    }

    protected function broadcastSystem(Message $msg): void
    {
        try {
            broadcast(new \App\Events\MessageSent($msg));
        } catch (\Throwable $e) {}
    }

    /**
     * Feature: Email the full chat transcript to the visitor once resolved.
     * Silently does nothing if visitor never gave an email.
     */
    public function sendTranscriptEmail(): void
    {
        if (!$this->visitor_email) {
            return;
        }

        try {
            \Illuminate\Support\Facades\Mail::to($this->visitor_email)
                ->send(new \App\Mail\ConversationTranscriptMail($this));
        } catch (\Throwable $e) {}
    }
}