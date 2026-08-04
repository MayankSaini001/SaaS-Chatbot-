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
<<<<<<< HEAD
        'visitor_ip', 'visitor_page', 'user_agent', 'status',
=======
        'visitor_ip', 'visitor_page', 'status',
>>>>>>> origin/main
        'session_token', 'visitor_info_collected',
        'rating', 'rating_feedback',
    ];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }

<<<<<<< HEAD
    /**
     * Visitor Info & Insights — raw `user_agent` string se readable
     * Browser / OS / Device nikalta hai. Koi naya column/migration nahi
     * chahiye — ye purani conversations ke liye bhi kaam karega.
     */
    public function parsedUserAgent(): array
    {
        $ua = $this->user_agent ?? '';

        if ($ua === '') {
            return ['browser' => null, 'os' => null, 'device' => null];
        }

        // Browser — order matters (Edge/Opera match Chrome's UA too).
        $browser = 'Unknown';
        if (preg_match('/Edg\//i', $ua)) {
            $browser = 'Edge';
        } elseif (preg_match('/OPR\/|Opera/i', $ua)) {
            $browser = 'Opera';
        } elseif (preg_match('/Chrome\//i', $ua)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Firefox\//i', $ua)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Safari\//i', $ua)) {
            $browser = 'Safari';
        }

        // OS
        $os = 'Unknown';
        if (preg_match('/Windows NT/i', $ua)) {
            $os = 'Windows';
        } elseif (preg_match('/iPhone|iPad|iPod/i', $ua)) {
            $os = 'iOS';
        } elseif (preg_match('/Android/i', $ua)) {
            $os = 'Android';
        } elseif (preg_match('/Mac OS X/i', $ua)) {
            $os = 'macOS';
        } elseif (preg_match('/Linux/i', $ua)) {
            $os = 'Linux';
        }

        // Device type
        $device = 'Desktop';
        if (preg_match('/iPad|Tablet/i', $ua)) {
            $device = 'Tablet';
        } elseif (preg_match('/Mobi|iPhone|Android.*Mobile/i', $ua)) {
            $device = 'Mobile';
        }

        return ['browser' => $browser, 'os' => $os, 'device' => $device];
    }

=======
>>>>>>> origin/main
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
<<<<<<< HEAD
     * Visitor Info & Insights — user_agent ko simple readable form me
     * todo ("Chrome on Windows · Desktop"), koi external package use
     * nahi kiya, bas common patterns check kiye hain.
     */
    public function deviceInfo(): ?array
    {
        $ua = $this->user_agent ?? null;
        if (!$ua) {
            return null;
        }

        $browser = 'Unknown Browser';
        if (stripos($ua, 'Edg/') !== false) {
            $browser = 'Edge';
        } elseif (stripos($ua, 'OPR/') !== false || stripos($ua, 'Opera') !== false) {
            $browser = 'Opera';
        } elseif (stripos($ua, 'Chrome/') !== false) {
            $browser = 'Chrome';
        } elseif (stripos($ua, 'CriOS') !== false) {
            $browser = 'Chrome';
        } elseif (stripos($ua, 'Firefox/') !== false) {
            $browser = 'Firefox';
        } elseif (stripos($ua, 'Safari/') !== false) {
            $browser = 'Safari';
        }

        $os = 'Unknown OS';
        if (stripos($ua, 'Windows') !== false) {
            $os = 'Windows';
        } elseif (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false || stripos($ua, 'iOS') !== false) {
            $os = 'iOS';
        } elseif (stripos($ua, 'Mac OS') !== false) {
            $os = 'macOS';
        } elseif (stripos($ua, 'Android') !== false) {
            $os = 'Android';
        } elseif (stripos($ua, 'Linux') !== false) {
            $os = 'Linux';
        }

        $device = 'Desktop';
        if (stripos($ua, 'iPad') !== false || stripos($ua, 'Tablet') !== false) {
            $device = 'Tablet';
        } elseif (stripos($ua, 'Mobile') !== false || stripos($ua, 'iPhone') !== false || stripos($ua, 'Android') !== false) {
            $device = 'Mobile';
        }

        return ['browser' => $browser, 'os' => $os, 'device' => $device];
    }

    public function deviceInfoLabel(): ?string
    {
        $info = $this->deviceInfo();
        if (!$info) {
            return null;
        }
        return $info['browser'] . ' on ' . $info['os'] . ' · ' . $info['device'];
    }

    /**
=======
>>>>>>> origin/main
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