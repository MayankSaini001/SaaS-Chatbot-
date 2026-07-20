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

		return view('agent.conversations.index', compact('conversations'));
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

        $conversation->messages()
            ->where('sender_type', 'visitor')
            ->where('is_read', false)
            ->update(['is_read' => true]);

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
            return response()->json(['success' => true]);
        }

        return back();
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

    public function embed()
    {
        $user = auth()->user();

        $tenantId = $user->tenant_id;

        if (!$tenantId) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Embed code tenant account se milta hai, admin se nahi.');
        }

        $widget = \App\Models\Widget::where('tenant_id', $tenantId)->first();

        if (!$widget) {
            $widget = \App\Models\Widget::create([
                'tenant_id'   => $tenantId,
                'embed_token' => \Illuminate\Support\Str::random(32),
                'color'       => '#6366f1',
                'position'    => 'bottom-right',
                'greeting'    => 'Hi! How can we help you?',
                'is_active'   => true,
            ]);
        }

        return view('agent.embed', compact('widget'));
    }

    public function updateWidget(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $widget = \App\Models\Widget::where('tenant_id', auth()->user()->tenant_id)->first();

        $data = [
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
            // hide_branding column shayad migration na chalne ki wajah se
            // maujood nahi — usi ke bina baaki settings phir bhi save karo.
            unset($data['hide_branding']);
            $widget->update($data);
            \Illuminate\Support\Facades\Log::warning('Widget update retried without hide_branding: ' . $e->getMessage());
        }

        if (!$canWhiteLabel && $request->boolean('hide_branding')) {
            return back()->with('error', 'Removing branding is a Pro/Enterprise feature. Please upgrade your plan.');
        }

        return back()->with('success', 'Widget updated!');
    }

    public function updateBusinessHours(Request $request)
    {
        $request->validate([
            'enabled'  => 'nullable|boolean',
            'timezone' => 'required|string|max:64',
            'days'     => 'nullable|array',
        ]);

        $widget = \App\Models\Widget::where('tenant_id', auth()->user()->tenant_id)->first();

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

        return back()->with('success', 'Business hours updated!');
    }

    public function getMessages(Conversation $conversation)
    {
        $messages = $conversation->messages()->oldest()->get();
        return response()->json($messages);
    }
}