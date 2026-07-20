<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id;
        $isOwner  = in_array($user->role, ['owner', 'viewer']);

        $base = Conversation::where('tenant_id', $tenantId);
        if (!$isOwner) {
            $base->where('agent_id', $user->id);
        }

        // ── Summary counts ──
        $totalConversations    = (clone $base)->count();
        $resolvedConversations = (clone $base)->where('status', 'resolved')->count();
        $openConversations     = (clone $base)->where('status', 'open')->count();

        // ── Avg resolution time (minutes): created_at -> updated_at for resolved chats ──
        $avgResolutionMinutes = (clone $base)
            ->where('status', 'resolved')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg_min')
            ->value('avg_min');

        // ── Avg first-response time (minutes): conversation created -> first agent message ──
        $responseRows = DB::table('conversations')
            ->join('messages', function ($join) {
                $join->on('messages.conversation_id', '=', 'conversations.id')
                     ->where('messages.sender_type', '=', 'agent')
                     ->whereNotNull('messages.sender_id');
            })
            ->where('conversations.tenant_id', $tenantId)
            ->when(!$isOwner, function ($q) use ($user) {
                $q->where('conversations.agent_id', $user->id);
            })
            ->groupBy('conversations.id', 'conversations.created_at')
            ->select('conversations.id', 'conversations.created_at as conv_created')
            ->selectRaw('MIN(messages.created_at) as first_reply')
            ->get();

        $avgResponseMinutes = $responseRows->isEmpty() ? null : $responseRows->avg(function ($row) {
            return Carbon::parse($row->conv_created)->diffInMinutes(Carbon::parse($row->first_reply));
        });

        // ── CSAT rating ──
        $avgRating   = (clone $base)->whereNotNull('rating')->avg('rating');
        $ratingCount = (clone $base)->whereNotNull('rating')->count();

        $ratingDistribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $ratingDistribution[$i] = (clone $base)->where('rating', $i)->count();
        }

        // ── Conversations per day (last 14 days) ──
        $since = Carbon::today()->subDays(13);
        $rawCounts = (clone $base)
            ->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd');

        $chatsPerDay = collect(range(13, 0))->map(function ($i) use ($rawCounts) {
            $day = Carbon::today()->subDays($i);
            $key = $day->format('Y-m-d');
            return [
                'label' => $day->format('M d'),
                'count' => (int) ($rawCounts[$key] ?? 0),
            ];
        })->values();

        // ── Rating trend (last 14 days) — average rating per day ──
        $ratingRows = (clone $base)
            ->whereNotNull('rating')
            ->where('updated_at', '>=', $since)
            ->selectRaw('DATE(updated_at) as d, AVG(rating) as avg_r, COUNT(*) as c')
            ->groupBy('d')
            ->get()
            ->keyBy('d');

        $ratingTrend = collect(range(13, 0))->map(function ($i) use ($ratingRows) {
            $day = Carbon::today()->subDays($i);
            $key = $day->format('Y-m-d');
            $row = $ratingRows[$key] ?? null;
            return [
                'label' => $day->format('M d'),
                'avg'   => $row ? round((float) $row->avg_r, 2) : null,
                'count' => $row ? (int) $row->c : 0,
            ];
        })->values();

        // ── Chats per agent (owner only) ──
        $chatsPerAgent = collect();
        if ($isOwner) {
            $chatsPerAgent = User::where('tenant_id', $tenantId)
                ->whereNotIn('role', ['admin', 'viewer'])
                ->get()
                ->map(function ($agent) use ($tenantId) {
                    $agentConvos = Conversation::where('tenant_id', $tenantId)
                        ->where('agent_id', $agent->id);

                    $total = (clone $agentConvos)->count();

                    return [
                        'name'       => $agent->name,
                        'total'      => $total,
                        'resolved'   => (clone $agentConvos)->where('status', 'resolved')->count(),
                        'avg_rating' => (clone $agentConvos)->whereNotNull('rating')->avg('rating'),
                    ];
                })
                ->filter(function ($a) { return $a['total'] > 0; })
                ->sortByDesc('total')
                ->values();
        }

        return view('agent.analytics', compact(
            'totalConversations',
            'resolvedConversations',
            'openConversations',
            'avgResolutionMinutes',
            'avgResponseMinutes',
            'avgRating',
            'ratingCount',
            'ratingDistribution',
            'ratingTrend',
            'chatsPerDay',
            'chatsPerAgent',
            'isOwner'
        ));
    }
}
