<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index()
	{
		if (in_array(auth()->user()->role, ['owner', 'viewer'])) {

			$conversations = Conversation::where('tenant_id', auth()->user()->tenant_id)
				->latest()
				->paginate(20);

		} else {

			$conversations = Conversation::where('tenant_id', auth()->user()->tenant_id)
				->where('agent_id', auth()->id())
				->latest()
				->paginate(20);

		}

		$widgets = \App\Models\Widget::where('tenant_id', auth()->user()->tenant_id)->orderBy('id')->get();

		return view('agent.conversations.index', compact('conversations', 'widgets'));
	}

    /**
     * Read Receipts — agent conversation already khola hua hai aur usi
     * waqt naya visitor message aata hai; use turant "read" mark karo
     * (page reload ka wait mat karo).
     */
    public function markRead(Conversation $conversation)
    {
        if ($conversation->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $unreadExisted = $conversation->messages()
            ->where('sender_type', 'visitor')
            ->where('is_read', false)
            ->exists();

        if ($unreadExisted) {
            $conversation->messages()
                ->where('sender_type', 'visitor')
                ->where('is_read', false)
                ->update(['is_read' => true]);

            try {
                broadcast(new \App\Events\MessagesRead($conversation->id));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('MessagesRead broadcast failed: ' . $e->getMessage());
            }
        }

        return response()->json(['success' => true]);
    }

    public function show(Conversation $conversation)
    {
        // Feature 4: Agent sirf apni assigned conversation dekh sakta hai
        // Owner/admin sab dekh sakte hain
        $user = auth()->user();
        if ($user->role === 'agent') {
            if ($conversation->agent_id !== $user->id) {
                abort(403, 'You can only view conversations assigned to you.');
            }
        }

        // Tenant check
        if ($conversation->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $unreadExisted = $conversation->messages()
            ->where('sender_type', 'visitor')
            ->where('is_read', false)
            ->exists();

        $conversation->messages()
            ->where('sender_type', 'visitor')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        if ($unreadExisted) {
            try {
                broadcast(new \App\Events\MessagesRead($conversation->id));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('MessagesRead broadcast failed: ' . $e->getMessage());
            }
        }

        $messages = $conversation->messages()->oldest()->get();

        $agents = \App\Models\User::where('tenant_id', auth()->user()->tenant_id)
            ->where('role', 'agent')
            ->get();

        $notes = $conversation->notes()->with('author')->get();

        $conversation->load('tags');
        $allTags = \App\Models\Tag::where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get();

        $isBlocked = \App\Models\BlockedIp::isBlocked($conversation->tenant_id, $conversation->visitor_ip);

        return view('agent.conversations.show', compact('conversation', 'messages', 'agents', 'notes', 'allTags', 'isBlocked'));
    }

    /**
     * Feature: Block/Ban Visitor — tenant ke andar is visitor ki IP se
     * aane wali koi bhi future chat/message reject ho jayegi.
     */
    public function blockVisitor(Request $request, Conversation $conversation)
    {
        $user = auth()->user();
        if ($user->role === 'agent' && $conversation->agent_id !== $user->id) {
            abort(403);
        }
        if ($conversation->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        if (!$conversation->visitor_ip) {
            return back()->with('error', 'Visitor IP not available, cannot block.');
        }

        \App\Models\BlockedIp::firstOrCreate(
            [
                'tenant_id'  => $conversation->tenant_id,
                'ip_address' => $conversation->visitor_ip,
            ],
            [
                'reason'     => $request->input('reason'),
                'blocked_by' => $user->id,
            ]
        );

        $msg = $conversation->messages()->create([
            'sender_type' => 'system',
            'sender_id'   => null,
            'body'        => '🚫 This visitor has been blocked by ' . $user->name . '.',
            'is_read'     => true,
        ]);

        try {
            broadcast(new \App\Events\MessageSent($msg));
        } catch (\Throwable $e) {}

        if ($conversation->status !== 'resolved') {
            $conversation->update(['status' => 'resolved']);
            try {
                event(new \App\Events\ConversationResolved($conversation->id));
            } catch (\Throwable $e) {}
        }

        return back()->with('success', 'Visitor blocked. They can no longer use this chat widget.');
    }

    public function unblockVisitor(Conversation $conversation)
    {
        $user = auth()->user();
        if ($user->role === 'agent' && $conversation->agent_id !== $user->id) {
            abort(403);
        }
        if ($conversation->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        \App\Models\BlockedIp::where('tenant_id', $conversation->tenant_id)
            ->where('ip_address', $conversation->visitor_ip)
            ->delete();

        // Blocking marks the conversation as 'resolved', which also stops the
        // visitor from sending messages (WidgetController@sendMessage rejects
        // resolved conversations). Unblocking must undo that too, otherwise
        // the visitor stays locked out even after the IP is unblocked.
        if ($conversation->status === 'resolved') {
            $conversation->update(['status' => 'open']);
            try {
                event(new \App\Events\ConversationReopened($conversation->id));
            } catch (\Throwable $e) {}
        }

        $msg = $conversation->messages()->create([
            'sender_type' => 'system',
            'sender_id'   => null,
            'body'        => '✅ This visitor has been unblocked by ' . $user->name . '. Conversation reopened.',
            'is_read'     => true,
        ]);

        try {
            broadcast(new \App\Events\MessageSent($msg));
        } catch (\Throwable $e) {}

        return back()->with('success', 'Visitor unblocked.');
    }

    // Agents list
    public function agents()
    {
        $agents = User::where('tenant_id', auth()->user()->tenant_id)
            ->whereIn('role', ['agent', 'viewer'])
            ->get();

        return view('agent.agents.index', compact('agents'));
    }

    public function addAgent(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role'     => 'nullable|in:agent,viewer',
        ]);

        $role = $request->role === 'viewer' ? 'viewer' : 'agent';

        $tenantId = auth()->user()->tenant_id;
        $tenant   = \App\Models\Tenant::find($tenantId);

        // ── Plan limit check (agents + viewers both count as team seats) ──
        if ($tenant) {
            $planLimits = [
                'trial'      => 2,
                'basic'      => 2,
                'pro'        => 10,
                'enterprise' => 999,
            ];

            $maxAgents    = $planLimits[$tenant->plan ?? 'basic'] ?? 2;
            $currentCount = User::where('tenant_id', $tenantId)
                                ->whereIn('role', ['agent', 'viewer'])
                                ->count();

            if ($currentCount >= $maxAgents) {
                return back()->with('error',
                    'Team member limit reached! Your ' . ucfirst($tenant->plan) . ' plan allows max ' . $maxAgents . ' team members. Please upgrade your plan.'
                );
            }
        }

        $plainPassword = $request->password;

        $agent = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => \Illuminate\Support\Facades\Hash::make($plainPassword),
            'role'      => $role,
            'tenant_id' => auth()->user()->tenant_id,
        ]);

	try {

		\Illuminate\Support\Facades\Mail::to($agent->email)
			->send(new \App\Mail\AgentWelcomeMail(
				$agent->name,
				$agent->email,
				$plainPassword
			));

	} catch (\Throwable $e) {

		dd($e->getMessage());

	}

	return back()->with('success', 'Agent added! Login details sent to ' . $agent->email);
		}

    public function deleteAgent(User $user)
    {
        if ($user->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $user->delete();
        return back()->with('success', 'Agent removed.');
    }

    /**
     * Agent Presence & Status — Online/Away/Busy manual toggle.
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|in:online,away,busy',
        ]);

        auth()->user()->update([
            'status'    => $request->status,
            'last_seen' => now(),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'status' => $request->status]);
        }

        return back();
    }

    public function updateAgentRole(Request $request, User $user)
    {
        if ($user->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own role.');
        }

        if (!in_array($user->role, ['agent', 'viewer'])) {
            return back()->with('error', 'This member\'s role cannot be changed.');
        }

        $request->validate([
            'role' => 'required|in:agent,viewer',
        ]);

        $user->update(['role' => $request->role]);

        return back()->with('success', $user->name . '\'s role updated to ' . ucfirst($request->role) . '.');
    }

    public function reply(Request $request, Conversation $conversation)
    {
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_type'     => 'agent',
            'sender_id'       => auth()->id(),
            'body'            => $request->message,
            'is_read'         => false,
        ]);
		
        $message->load(['sender', 'conversation']);

        try {
            broadcast(new \App\Events\MessageSent($message))->toOthers();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('MessageSent (agent reply) broadcast failed: ' . $e->getMessage());
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message_id' => $message->id]);
        }

        return back();
    }

    /**
     * Agent-side message edit — agent apna khud ka bheja hua message edit kar sake.
     */
    public function editMessage(Request $request, Conversation $conversation, Message $message)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        if ($message->conversation_id !== $conversation->id || $message->sender_type !== 'agent') {
            abort(404);
        }
        if ($message->sender_id !== auth()->id()) {
            abort(403, 'You can only edit your own messages.');
        }
        if ($message->is_deleted) {
            return response()->json(['error' => 'Message deleted'], 422);
        }
        if ($message->attachment) {
            return response()->json(['error' => 'Cannot edit an attachment'], 422);
        }
        if ($message->created_at->diffInMinutes(now()) > 15) {
            return response()->json(['error' => 'Edit window expired'], 422);
        }

        $message->body      = trim($request->message);
        $message->is_edited = true;
        $message->edited_at = now();
        $message->save();

        try {
            broadcast(new \App\Events\MessageEdited($message))->toOthers();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('MessageEdited (agent) broadcast failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => [
                'id'        => $message->id,
                'body'      => $message->body,
                'is_edited' => true,
            ],
        ]);
    }

    /**
     * Agent-side message delete — agent apna khud ka bheja hua message delete kar sake.
     */
    public function deleteMessage(Conversation $conversation, Message $message)
    {
        if ($message->conversation_id !== $conversation->id || $message->sender_type !== 'agent') {
            abort(404);
        }
        if ($message->sender_id !== auth()->id()) {
            abort(403, 'You can only delete your own messages.');
        }

        if (!$message->is_deleted) {
            $message->is_deleted = true;
            $message->body       = '';
            $message->attachment = null;
            $message->save();

            try {
                broadcast(new \App\Events\MessageDeleted($message->id, $conversation->id, 'agent'))->toOthers();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('MessageDeleted (agent) broadcast failed: ' . $e->getMessage());
            }
        }

        return response()->json(['success' => true]);
    }

    public function assign(Request $request, Conversation $conversation)
	{
		if (auth()->user()->role !== 'owner') {
			abort(403);
		}

		$request->validate([
			'agent_id' => 'required|exists:users,id',
		]);

		$agentId = $request->agent_id;
		$agent   = User::find($agentId);

		$conversation->update(['agent_id' => $agentId]);

		$agentName = $agent ? $agent->name : 'An agent';

		$notificationMsg = Message::create([
			'conversation_id' => $conversation->id,
			'sender_type'     => 'system',
			'sender_id'       => null,
			'body'            => "🤝 {$agentName} will now continue this chat.",
			'is_read'         => false,
		]);

		try {
			broadcast(new \App\Events\MessageSent($notificationMsg));
		} catch (\Throwable $e) {}

		try {
			event(new \App\Events\ConversationAssigned(
				$conversation->id,
				$agentId,
				$conversation->visitor_name,
				$agentName
			));
		} catch (\Throwable $e) {}

		try {
			\App\Mail\AgentMailHelper::sendAssigned(
				$agent->email,
				$agent->name,
				$conversation->visitor_name ?? 'Visitor',
				$conversation->id
			);
		} catch (\Throwable $e) {
			\Log::error('AgentAssignedMail failed: ' . $e->getMessage());
		}

		if ($request->expectsJson()) {
			return response()->json(['success' => true, 'agent_name' => $agentName]);
		}

		return redirect()->route('agent.conversations.show', $conversation)
			->with('success', "Assigned to {$agentName}!");
	}

    public function data(Request $request)
    {
        $this->closeInactiveConversations();

        $user  = auth()->user();
		\Log::info([
			'user_id' => $user->id,
			'role' => $user->role,
			'tenant_id' => $user->tenant_id
		]);
        $query = Conversation::where('tenant_id', $user->tenant_id)->latest();

        if ($request->filter === 'open') {
            $query->where('status', 'open');
        } elseif ($request->filter === 'resolved') {
            $query->where('status', 'resolved');
        } elseif ($request->filter === 'mine') {
            $query->where('agent_id', $user->id);
        }

        if ($request->tag_id) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('tags.id', $request->tag_id);
            });
        }

        // Multi-Widget Support — dashboard me widget ke hisaab se filter.
        if ($request->filled('widget_id')) {
            $query->where('widget_id', $request->widget_id);
        }

        // Feature 4: Regular agents sirf apni conversations dekhein
        if ($user->role === 'agent') {
            $query->where('agent_id', $user->id);
        }

        // Conversation Search — searches visitor name/email AND the full
        // message history, not just the last message.
        $searchTerm = trim((string) $request->q);
        if ($searchTerm !== '') {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('visitor_name', 'like', "%{$searchTerm}%")
                  ->orWhere('visitor_email', 'like', "%{$searchTerm}%")
                  ->orWhereHas('messages', function ($mq) use ($searchTerm) {
                      $mq->where('body', 'like', "%{$searchTerm}%");
                  });
            });
        }

		\Log::info([
			'sql' => $query->toSql(),
			'bindings' => $query->getBindings()
		]);
        $conversations = $query->get()->map(function($conv) use ($searchTerm) {
            $lastMessage = $conv->messages()->latest()->first();

            // When searching, show the actual matching message as the
            // preview snippet (not just whatever the last message happens
            // to be), so the agent can see why this conversation matched.
            $matchedMessage = null;
            if ($searchTerm !== '') {
                $matchedMessage = $conv->messages()
                    ->where('body', 'like', "%{$searchTerm}%")
                    ->latest()
                    ->first();
            }
            $previewMessage = $matchedMessage ?: $lastMessage;

            $unreadCount = 0;
            if ($conv->status === 'open') {
                $unreadCount = $conv->messages()
                    ->where('sender_type', 'visitor')
                    ->where('is_read', false)
                    ->count();
            }

            return [
                'id'            => $conv->id,
                'visitor_name'  => $conv->visitor_name ?? 'Visitor',
                'visitor_email' => $conv->visitor_email,
                'status'        => $conv->status,
                'last_message'  => $previewMessage ? substr($previewMessage->body, 0, 80) : null,
                'matched'       => (bool) $matchedMessage,
                'unread_count'  => $unreadCount,
                'time_ago'      => $conv->created_at->diffForHumans(),
                'agent_id'      => $conv->agent_id,
                'agent_name'    => $conv->agent ? $conv->agent->name : null,
                'rating'        => $conv->rating,
                'widget_id'     => $conv->widget_id,
                'widget_name'   => $conv->widget ? $conv->widget->displayName() : null,
                'tags'          => $conv->tags->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'color' => $t->color]),
            ];
        });

        return response()->json($conversations);
    }

    private function closeInactiveConversations()
    {
        Conversation::where('tenant_id', auth()->user()->tenant_id)
            ->where('status', 'open')
            ->get()
            ->each(function (Conversation $conv) {
                $conv->runInactivityCheck();
            });
    }

    public function resolve(Conversation $conversation)
    {
        $conversation->update(['status' => 'resolved']);

        try {
            event(new \App\Events\ConversationResolved($conversation->id));
        } catch (\Throwable $e) {}

        $conversation->sendTranscriptEmail();

        return redirect()->route('agent.conversations')
            ->with('success', 'Conversation resolved!');
    }

    // Internal Notes: private notes between agents, never visible to the visitor
    public function addNote(Request $request, Conversation $conversation)
    {
        if ($conversation->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $note = $conversation->notes()->create([
            'user_id' => auth()->id(),
            'body'    => $request->body,
        ]);

        try {
            broadcast(new \App\Events\NoteAdded($note, auth()->id()));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('NoteAdded broadcast failed: ' . $e->getMessage());
        }

        // @Mention — note body me "@Full Name" milte hi us teammate ko
        // notify karo (desktop notification, agent already-existing
        // 'agent.{id}' channel infra reuse karke).
        try {
            $teammates = \App\Models\User::where('tenant_id', auth()->user()->tenant_id)
                ->where('id', '!=', auth()->id())
                ->get(['id', 'name']);

            foreach ($teammates as $teammate) {
                if (stripos($request->body, '@' . $teammate->name) !== false) {
                    broadcast(new \App\Events\NoteMentioned(
                        $teammate->id,
                        $conversation->id,
                        $conversation->visitor_name ?? 'Visitor',
                        auth()->user()->name,
                        \Illuminate\Support\Str::limit($request->body, 80)
                    ));
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('NoteMentioned broadcast failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'note' => [
                'id'         => $note->id,
                'body'       => $note->body,
                'author'     => auth()->user()->name,
                'created_at' => $note->created_at->format('d M, h:i A'),
            ],
        ]);
    }

    public function deleteNote(Conversation $conversation, \App\Models\ConversationNote $note)
    {
        if ($note->conversation_id !== $conversation->id || $conversation->tenant_id !== auth()->user()->tenant_id) {
            abort(404);
        }

        $noteId = $note->id;
        $note->delete();

        try {
            broadcast(new \App\Events\NoteDeleted($noteId, $conversation->id, auth()->id()));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('NoteDeleted broadcast failed: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }

    public function embed(?\App\Models\Widget $widget = null)
    {
        $user = auth()->user();

        $tenantId = $user->tenant_id;

        if (!$tenantId) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Embed code tenant account se milta hai, admin se nahi.');
        }

        $widgets = \App\Models\Widget::where('tenant_id', $tenantId)->orderBy('id')->get();

        if ($widgets->isEmpty()) {
            $created = \App\Models\Widget::create([
                'tenant_id'   => $tenantId,
                'name'        => 'Main Widget',
                'embed_token' => \Illuminate\Support\Str::random(32),
                'color'       => '#6366f1',
                'position'    => 'bottom-right',
                'greeting'    => 'Hi! How can we help you?',
                'is_active'   => true,
            ]);
            $widgets = collect([$created]);
        }

        // Route se widget diya gaya ho (aur usi tenant ka ho) to wahi
        // select karo, warna list ka pehla widget default.
        $selected = ($widget && $widget->tenant_id === $tenantId)
            ? $widget
            : $widgets->first();

        return view('agent.embed', ['widget' => $selected, 'widgets' => $widgets]);
    }

    /**
     * Multi-Widget Support — naya widget banao (dusri website/product ke liye).
     */
    public function createWidget(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'name' => 'nullable|string|max:100',
        ]);

        $widget = \App\Models\Widget::create([
            'tenant_id'   => $tenantId,
            'name'        => $request->name ?: 'New Widget',
            'embed_token' => \Illuminate\Support\Str::random(32),
            'color'       => '#6366f1',
            'position'    => 'bottom-right',
            'greeting'    => 'Hi! How can we help you?',
            'title'       => 'Support Team',
            'is_active'   => true,
        ]);

        return redirect()->route('agent.embed', $widget)->with('success', 'New widget created!');
    }

    public function deleteWidget(\App\Models\Widget $widget)
    {
        $tenantId = auth()->user()->tenant_id;

        if ($widget->tenant_id !== $tenantId) {
            abort(403);
        }

        $remaining = \App\Models\Widget::where('tenant_id', $tenantId)->count();

        if ($remaining <= 1) {
            return back()->with('error', 'You must keep at least one widget.');
        }

        $widget->delete();

        return redirect()->route('agent.embed')->with('success', 'Widget deleted.');
    }

    public function updateWidget(Request $request)
    {
        $tenant = auth()->user()->tenant;

        // widget_id form se aaya to usi widget ko target karo (multi-widget),
        // warna purana behaviour: tenant ka pehla widget (backward compat).
        $widget = $request->filled('widget_id')
            ? \App\Models\Widget::where('id', $request->widget_id)
                ->where('tenant_id', auth()->user()->tenant_id)
                ->firstOrFail()
            : \App\Models\Widget::where('tenant_id', auth()->user()->tenant_id)->first();

        $data = [
            'name'     => $request->name ?: $widget->name,
            'title'    => $request->title,
            'greeting' => $request->greeting,
            'color'    => $request->color,
            'position' => $request->position,
        ];

        // White-label / Remove Branding — sirf Pro & Enterprise plan par allowed.
        $canWhiteLabel = in_array($tenant->plan ?? 'basic', ['pro', 'enterprise']);
        if ($canWhiteLabel) {
            $data['hide_branding'] = $request->boolean('hide_branding');
        }

        try {
            $widget->update($data);
        } catch (\Throwable $e) {
            // hide_branding/name column shayad migration na chalne ki wajah
            // se maujood nahi — usi ke bina baaki settings phir bhi save karo.
            unset($data['hide_branding']);
            try {
                $widget->update($data);
            } catch (\Throwable $e2) {
                unset($data['name']);
                $widget->update($data);
            }
            \Illuminate\Support\Facades\Log::warning('Widget update retried without hide_branding/name: ' . $e->getMessage());
        }

        if (!$canWhiteLabel && $request->boolean('hide_branding')) {
            return redirect()->route('agent.embed', $widget)->with('error', 'Removing branding is a Pro/Enterprise feature. Please upgrade your plan.');
        }

        return redirect()->route('agent.embed', $widget)->with('success', 'Widget updated!');
    }

    public function updateBusinessHours(Request $request)
    {
        $request->validate([
            'enabled'  => 'nullable|boolean',
            'timezone' => 'required|string|max:64',
            'days'     => 'nullable|array',
        ]);

        $widget = $request->filled('widget_id')
            ? \App\Models\Widget::where('id', $request->widget_id)
                ->where('tenant_id', auth()->user()->tenant_id)
                ->firstOrFail()
            : \App\Models\Widget::where('tenant_id', auth()->user()->tenant_id)->first();

        $schedule = [];
        foreach (\App\Models\Widget::DAYS as $day) {
            $dayInput = $request->input("days.$day", []);
            $schedule[$day] = [
                'enabled' => !empty($dayInput['enabled']),
                'start'   => $dayInput['start'] ?? '09:00',
                'end'     => $dayInput['end'] ?? '18:00',
            ];
        }

        $widget->update([
            'business_hours_enabled'  => $request->boolean('enabled'),
            'business_hours_timezone' => $request->timezone,
            'business_hours'          => $schedule,
        ]);

        return redirect()->route('agent.embed', $widget)->with('success', 'Business hours updated!');
    }

    public function getMessages(Conversation $conversation)
    {
        $messages = $conversation->messages()->oldest()->get();
        return response()->json($messages);
    }
}