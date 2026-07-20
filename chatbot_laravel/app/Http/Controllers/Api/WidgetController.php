<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Widget;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class WidgetController extends Controller
{
    public function startConversation(Request $request)
    {
        $widget = Widget::where('embed_token', $request->token)
            ->where('is_active', true)
            ->first();

        if (!$widget) {
            return response()->json(['error' => 'Invalid token'], 403);
        }

        if (\App\Models\BlockedIp::isBlocked($widget->tenant_id, $request->ip())) {
            return response()->json([
                'error'   => 'blocked',
                'message' => 'You have been blocked from this chat.',
            ], 403);
        }

        $agentsOnline = User::where('tenant_id', $widget->tenant_id)
            ->whereNotIn('role', ['admin', 'viewer'])
            ->where('last_seen', '>=', now()->subMinutes(3))
            ->exists();

        $sessionToken = $request->session_token;

        if (!$sessionToken) {
            $sessionToken = md5($request->ip() . $request->userAgent());
        }

        // Find existing open conversation
        $existing = Conversation::where('widget_id', $widget->id)
            ->where('status', 'open')
            ->where(function ($q) use ($sessionToken, $request) {
                $q->where('session_token', $sessionToken)
                  ->orWhere('visitor_ip', $request->ip());
            })
            ->latest('id')
            ->first();

        if ($existing) {
            if ($request->page) {
                $existing->visitor_page = $request->page;
                $existing->save();
            }

            return response()->json([
                'conversation_id'        => $existing->id,
                'greeting'               => $widget->greeting,
                'color'                  => $widget->color,
                'position'               => $widget->position,
                'title'                  => $widget->title ?? 'Support Team',
                'agents_online'          => $agentsOnline,
                'within_business_hours'  => $widget->isWithinBusinessHours(),
                'business_hours_summary' => $widget->businessHoursSummary(),
                'visitor_info_collected' => (bool) $existing->visitor_info_collected,
            ]);
        }

        // New conversation — visitor_info_collected false (form dikhana hoga)
        $visitorName  = $request->name  ? trim($request->name)  : null;
        $visitorEmail = $request->email ? trim($request->email) : null;

        // Agar name+email already POST mein aa rahe hain (submitVisitorInfo se)
        $infoCollected = ($visitorName && $visitorEmail) ? true : false;

        $conversation = Conversation::create([
            'widget_id'              => $widget->id,
            'tenant_id'              => $widget->tenant_id,
            'visitor_name'           => $visitorName ?? 'Visitor',
            'visitor_email'          => $visitorEmail,
            'visitor_ip'             => $request->ip(),
            'visitor_page'           => $request->page,
            'session_token'          => $sessionToken,
            'status'                 => 'open',
            'visitor_info_collected' => $infoCollected,
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_type'     => 'agent',
            'sender_id'       => null,
            'body'            => $widget->greeting,
            'is_read'         => true,
        ]);

        try {
            event(new \App\Events\NewConversation(
                $conversation->id,
                $conversation->visitor_name,
                $widget->tenant_id
            ));
        } catch (\Throwable $e) {
        }

        return response()->json([
            'conversation_id'        => $conversation->id,
            'greeting'               => $widget->greeting,
            'color'                  => $widget->color,
            'position'               => $widget->position,
            'title'                  => $widget->title ?? 'Support Team',
            'agents_online'          => $agentsOnline,
            'within_business_hours'  => $widget->isWithinBusinessHours(),
            'business_hours_summary' => $widget->businessHoursSummary(),
            'visitor_info_collected' => $infoCollected,
        ]);
    }

    /**
     * Feature 1: Visitor name + email submit karne ke baad call hoga
     * POST /api/widget/visitor-info
     */
    public function submitVisitorInfo(Request $request)
    {
        $conversationId = $request->conversation_id;
        $name           = trim($request->name ?? '');
        $email          = trim($request->email ?? '');

        if (!$conversationId || !$name || !$email) {
            return response()->json(['error' => 'Name, email and conversation_id required'], 422);
        }

        $conversation = Conversation::find($conversationId);

        if (!$conversation) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $conversation->update([
            'visitor_name'           => $name,
            'visitor_email'          => $email,
            'visitor_info_collected' => true,
        ]);

        return response()->json(['success' => true]);
    }

    public function visitorTyping(Request $request)
    {
        $conversationId = $request->conversation_id;
        $typing         = $request->typing;

        if (!$conversationId) return response()->json(['error' => 'Missing conversation_id'], 422);

        if ($typing) {
            cache()->put('visitor_typing_' . $conversationId, true, 5);
        } else {
            cache()->forget('visitor_typing_' . $conversationId);
        }

        return response()->json(['success' => true]);
    }

    public function agentTyping(Request $request)
    {
        $conversationId = $request->conversation_id;
        $typing         = $request->typing;

        if (!$conversationId) return response()->json(['error' => 'Missing conversation_id'], 422);

        if ($typing) {
            cache()->put('agent_typing_' . $conversationId, true, 5);
        } else {
            cache()->forget('agent_typing_' . $conversationId);
        }

        return response()->json(['success' => true]);
    }

    public function getSettings(Request $request)
    {
        $widget = Widget::where('embed_token', $request->token)
            ->where('is_active', true)
            ->first();

        if (!$widget) {
            return response()->json(['error' => 'Invalid token'], 403);
        }

        $agentsOnline = User::where('tenant_id', $widget->tenant_id)
            ->whereNotIn('role', ['admin', 'viewer'])
            ->where('last_seen', '>=', now()->subMinutes(3))
            ->exists();

        return response()->json([
            'color'                  => $widget->color,
            'position'               => $widget->position,
            'title'                  => $widget->title ?? 'Support Team',
            'greeting'               => $widget->greeting,
            'agents_online'          => $agentsOnline,
            'within_business_hours'  => $widget->isWithinBusinessHours(),
            'business_hours_summary' => $widget->businessHoursSummary(),
            'hide_branding'          => (bool) $widget->hide_branding,
        ]);
    }

    public function agentReply(Request $request)
    {
        $conversation = Conversation::find($request->conversation_id);

        if (!$conversation) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_type'     => 'agent',
            'sender_id'       => auth()->id(),
            'body'            => $request->message,
            'is_read'         => false,
        ]);

        event(new \App\Events\MessageSent($message));

        return response()->json([
            'success' => true
        ]);
    }

    public function sendMessage(Request $request)
    {
        $conversation = Conversation::find($request->conversation_id);

        if (!$conversation) {
            return response()->json(['error' => 'Not found'], 404);
        }

        if ($conversation->status === 'resolved') {
            return response()->json(['error' => 'resolved'], 403);
        }

        if (\App\Models\BlockedIp::isBlocked($conversation->tenant_id, $request->ip())) {
            return response()->json([
                'error'   => 'blocked',
                'message' => 'You have been blocked from this chat.',
            ], 403);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_type'     => 'visitor',
            'sender_id'       => null,
            'body'            => $request->message,
            'is_read'         => false,
        ]);

        try {
            broadcast(new \App\Events\MessageSent($message->load('conversation')));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('MessageSent (visitor) broadcast failed: ' . $e->getMessage());
        }

        try {
            $targetChannel = $conversation->agent_id
                ? 'agent.' . $conversation->agent_id
                : 'tenant.' . $conversation->tenant_id;

            broadcast(new \App\Events\NewVisitorMessage(
                $conversation->id,
                $conversation->visitor_name ?? 'Visitor',
                $request->message,
                $targetChannel
            ));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('NewVisitorMessage broadcast failed: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message_id' => $message->id]);
    }

    /**
     * Widget Message Edit: visitor apna khud ka bheja hua message edit kar sakta hai.
     * POST /api/widget/message/edit
     */
    public function editMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|integer',
            'message_id'      => 'required|integer',
            'message'         => 'required|string|max:5000',
        ]);

        $conversation = Conversation::find($request->conversation_id);

        if (!$conversation) {
            return response()->json(['error' => 'Not found'], 404);
        }

        if ($conversation->status === 'resolved') {
            return response()->json(['error' => 'resolved'], 403);
        }

        if (\App\Models\BlockedIp::isBlocked($conversation->tenant_id, $request->ip())) {
            return response()->json(['error' => 'blocked'], 403);
        }

        $message = Message::where('id', $request->message_id)
            ->where('conversation_id', $conversation->id)
            ->where('sender_type', 'visitor')
            ->first();

        if (!$message) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        if ($message->is_deleted) {
            return response()->json(['error' => 'Message deleted'], 422);
        }

        if ($message->attachment) {
            return response()->json(['error' => 'Cannot edit an attachment'], 422);
        }

        // Visitor sirf apna recent message edit kar sake (15 minute window)
        if ($message->created_at->diffInMinutes(now()) > 15) {
            return response()->json(['error' => 'Edit window expired'], 422);
        }

        $message->body      = trim($request->message);
        $message->is_edited = true;
        $message->edited_at = now();
        $message->save();

        try {
            broadcast(new \App\Events\MessageEdited($message));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('MessageEdited broadcast failed: ' . $e->getMessage());
        }

        return response()->json([
            'success'   => true,
            'message'   => [
                'id'        => $message->id,
                'body'      => $message->body,
                'is_edited' => true,
            ],
        ]);
    }

    /**
     * Widget Message Delete: visitor apna khud ka bheja hua message delete kar sakta hai
     * (soft delete — body clear ho jata hai, "message deleted" dikhega).
     * POST /api/widget/message/delete
     */
    public function deleteMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|integer',
            'message_id'      => 'required|integer',
        ]);

        $conversation = Conversation::find($request->conversation_id);

        if (!$conversation) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $message = Message::where('id', $request->message_id)
            ->where('conversation_id', $conversation->id)
            ->where('sender_type', 'visitor')
            ->first();

        if (!$message) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        if ($message->is_deleted) {
            return response()->json(['success' => true]);
        }

        $message->is_deleted = true;
        $message->body       = '';
        $message->attachment = null;
        $message->save();

        try {
            broadcast(new \App\Events\MessageDeleted($message->id, $conversation->id, 'visitor'));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('MessageDeleted broadcast failed: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }

    /**
     * GET /api/widget/messages/{conversationId}?page=1
     */
    public function getMessages(Request $request, $conversationId)
    {
        $conversation = Conversation::find($conversationId);

        if (!$conversation) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $this->checkInactiveConversation($conversation);

        $page    = max(1, intval($request->query('page', 1)));
        $perPage = 20;

        $total  = Message::where('conversation_id', $conversationId)->count();
        $offset = max(0, $total - ($page * $perPage));
        $limit  = min($perPage, $total - (($page - 1) * $perPage));

        $messages = Message::where('conversation_id', $conversationId)
    ->with('sender')
    ->oldest()
    ->skip($offset)
    ->take($limit)
    ->get()
    ->map(function ($message) use ($conversation) {

        return [
			'id'           => $message->id,
			'sender_type'  => $message->sender_type,
			'body'         => $message->is_deleted ? '' : $message->body,
			'attachment'   => $message->is_deleted ? null : $message->attachment,
			'created_at'   => $message->created_at,
			'is_edited'    => (bool) $message->is_edited,
			'is_deleted'   => (bool) $message->is_deleted,

			'agent_name' => (
				$message->sender_type === 'agent' && $message->sender_id && $message->sender
			)
				? $message->sender->name
				: null,

			'visitor_name' => $conversation->visitor_name ?? 'Visitor',
		];
    });

        $agentsOnline = User::where('tenant_id', $conversation->tenant_id)
            ->whereNotIn('role', ['admin', 'viewer'])
            ->where('last_seen', '>=', now()->subMinutes(3))
            ->exists();

        $widget = $conversation->widget;

        return response()->json([
            'status'                 => $conversation->fresh()->status,
            'messages'               => $messages,
            'agent_typing'           => cache()->get('agent_typing_' . $conversationId, false),
            'visitor_typing'         => cache()->get('visitor_typing_' . $conversationId, false),
            'agents_online'          => $agentsOnline,
            'within_business_hours'  => $widget ? $widget->isWithinBusinessHours() : true,
            'business_hours_summary' => $widget ? $widget->businessHoursSummary() : null,
            'visitor_info_collected' => (bool) $conversation->visitor_info_collected,
            'rating'                 => $conversation->rating,
        ]);
    }

    /**
     * CSAT: Visitor submits a rating (1-5) + optional feedback after a
     * conversation is resolved.
     * POST /api/widget/rating
     */
    public function submitRating(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|integer',
            'rating'          => 'required|integer|min:1|max:5',
            'feedback'        => 'nullable|string|max:1000',
        ]);

        $conversation = Conversation::find($request->conversation_id);

        if (!$conversation) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $conversation->update([
            'rating'          => $request->rating,
            'rating_feedback' => $request->feedback,
        ]);

        return response()->json(['success' => true]);
    }

    public function uploadFile(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'No file'], 400);
        }

        $conversation = Conversation::find($request->conversation_id);

        if (!$conversation) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        $file = $request->file('file');

        $filename = time() . '_' . preg_replace(
            '/[^a-zA-Z0-9._-]/',
            '',
            $file->getClientOriginalName()
        );

        $destination = dirname(public_path()) . '/../chatbot/uploads/chat';

        if (!file_exists($destination)) {
            mkdir($destination, 0775, true);
        }

        $file->move($destination, $filename);

        if (!file_exists($destination . '/' . $filename)) {
            return response()->json([
                'error'       => 'File not saved',
                'destination' => $destination,
                'file'        => $filename
            ]);
        }

        $url = url('/uploads/chat/' . $filename);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_type'     => 'visitor',
            'sender_id'       => null,
            'body'            => '',
            'attachment'      => $url,
            'is_read'         => false,
        ]);

        return response()->json([
            'success'    => true,
            'url'        => $url,
            'message_id' => $message->id
        ]);
    }

    public function uploadAgentFile(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'No file'], 400);
        }

        $conversation = Conversation::find($request->conversation_id);

        if (!$conversation) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        $file = $request->file('file');

        $filename = time() . '_' . preg_replace(
            '/[^a-zA-Z0-9._-]/',
            '',
            $file->getClientOriginalName()
        );

        $destination = dirname(public_path()) . '/../chatbot/uploads/chat';

        if (!file_exists($destination)) {
            mkdir($destination, 0775, true);
        }

        $file->move($destination, $filename);

        $url = url('/uploads/chat/' . $filename);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_type'     => 'agent',
            'sender_id'       => auth()->id(),
            'body'            => '',
            'attachment'      => $url,
            'is_read'         => false,
        ]);

        try {
            event(new \App\Events\MessageSent($message));
        } catch (\Throwable $e) {
        }

        return response()->json([
            'success'    => true,
            'url'        => $url,
            'message_id' => $message->id
        ]);
    }

    private function checkInactiveConversation($conversation)
    {
        $conversation->runInactivityCheck();
    }
}
