<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;

class DashboardController extends Controller
{
    public function index()
{
    $tenantId = auth()->user()->tenant_id;
    $userId = auth()->id();
    if (in_array(auth()->user()->role, ['owner', 'viewer'])) {

        $openConversations = Conversation::where('tenant_id', $tenantId)
            ->where('status', 'open')
            ->count();

        $totalConversations = Conversation::where('tenant_id', $tenantId)
            ->count();

        $resolvedConversations = Conversation::where('tenant_id', $tenantId)
            ->where('status', 'resolved')
            ->count();

        $resolvedToday = Conversation::where('tenant_id', $tenantId)
            ->where('status', 'resolved')
            ->whereDate('updated_at', today())
            ->count();

        $unreadMessages = Message::whereHas('conversation', function ($q) use ($tenantId, $userId) {
        $q->where('tenant_id', $tenantId)
          ->where('agent_id', $userId);
			})
			->where('sender_type', 'visitor')
			->where('is_read', false)
			->count();

        $recentConversations = Conversation::where('tenant_id', $tenantId)
            ->latest()
            ->take(5)
            ->get();

    } else {

        $openConversations = Conversation::where('tenant_id', $tenantId)
            ->where('agent_id', auth()->id())
            ->where('status', 'open')
            ->count();

        $totalConversations = Conversation::where('tenant_id', $tenantId)
            ->where('agent_id', auth()->id())
            ->count();

        $resolvedConversations = Conversation::where('tenant_id', $tenantId)
            ->where('agent_id', auth()->id())
            ->where('status', 'resolved')
            ->count();

        $resolvedToday = Conversation::where('tenant_id', $tenantId)
            ->where('agent_id', auth()->id())
            ->where('status', 'resolved')
            ->whereDate('updated_at', today())
            ->count();

        $unreadMessages = Message::whereHas('conversation', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)
                  ->where('agent_id', auth()->id());
            })
            ->where('sender_type', 'visitor')
            ->where('is_read', false)
            ->count();

        $recentConversations = Conversation::where('tenant_id', $tenantId)
            ->where('agent_id', auth()->id())
            ->latest()
            ->take(5)
            ->get();
    }

    $widget = \App\Models\Widget::where('tenant_id', $tenantId)->first();

    return view('agent.dashboard', compact(
        'openConversations',
        'totalConversations',
        'resolvedConversations',
        'resolvedToday',
        'unreadMessages',
        'recentConversations',
        'widget'
    ));
}
}