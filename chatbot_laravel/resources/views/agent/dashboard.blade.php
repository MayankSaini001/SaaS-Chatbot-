@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Feature 2: Trial Period Notice --}}
    @if(auth()->user()->role === 'owner' && auth()->user()->tenant && auth()->user()->tenant->plan === 'trial' && auth()->user()->tenant->trial_ends_at)
        @php
			$daysLeft = max(0, floor(now()->diffInDays(auth()->user()->tenant->trial_ends_at, false)));
		@endphp
        @if($daysLeft >= 0)
        <div class="bg-amber-50 border-b border-amber-200 px-4 md:px-8 py-2 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-amber-600 font-semibold text-sm">🎉 Free Trial</span>
                <span class="text-amber-700 text-sm">
                    @if($daysLeft > 0)
                        {{ $daysLeft }} day{{ $daysLeft == 1 ? '' : 's' }} remaining in your trial.
                    @else
                        Your trial expires today!
                    @endif
                </span>
            </div>
            <a href="{{ route('billing.pricing') }}" class="text-xs font-semibold bg-amber-500 hover:bg-amber-600 text-white px-3 py-1 rounded-full transition">
                Upgrade Now
            </a>
        </div>
        @endif
    @endif



    {{-- Top Welcome Bar --}}
    <div class="bg-white border-b px-4 md:px-8 py-4 md:py-5 flex items-center justify-between">
        <div>
            <h1 class="text-lg md:text-xl font-bold text-gray-800">Agent Dashboard</h1>
            <p class="text-xs md:text-sm text-gray-400 mt-0.5">Welcome back, <span class="text-indigo-600 font-medium">{{ auth()->user()->name }}</span></p>
        </div>
        <div class="flex items-center gap-2 md:gap-3">
            <span class="hidden md:block text-xs text-gray-400">{{ now()->format('d M Y, h:i A') }}</span>
            <span class="inline-flex items-center gap-1.5 bg-green-50 text-green-700 text-xs font-medium px-2 md:px-3 py-1 md:py-1.5 rounded-full border border-green-200">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse inline-block"></span>
                Online
            </span>
        </div>
    </div>

    <div class="px-4 md:px-8 py-4 md:py-6 space-y-4 md:space-y-6">

        {{-- Stats Row --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 md:gap-4">

            {{-- Open Conversations --}}
            <div class="bg-white rounded-xl md:rounded-2xl border border-gray-100 shadow-sm p-4 md:p-5 flex items-center gap-3 md:gap-4 hover:shadow-md transition">
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <div>
                    <div class="text-xl md:text-2xl font-bold text-gray-800">{{ $openConversations }}</div>
                    <div class="text-xs text-gray-400 mt-0.5">Open Chats</div>
                </div>
            </div>

            {{-- Unread Messages --}}
            <div class="bg-white rounded-xl md:rounded-2xl border border-gray-100 shadow-sm p-4 md:p-5 flex items-center gap-3 md:gap-4 hover:shadow-md transition">
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <div>
                    <div class="text-xl md:text-2xl font-bold text-gray-800 flex items-center gap-1">
                        <span id="unread-count">{{ $unreadMessages }}</span>
                        <span id="unread-dot" class="inline-block w-2 h-2 bg-red-500 rounded-full animate-pulse"
                            style="{{ $unreadMessages > 0 ? '' : 'display:none' }}"></span>
                    </div>
                    <div class="text-xs text-gray-400 mt-0.5">Unread</div>
                </div>
            </div>

            {{-- Total Conversations --}}
            <div class="bg-white rounded-xl md:rounded-2xl border border-gray-100 shadow-sm p-4 md:p-5 flex items-center gap-3 md:gap-4 hover:shadow-md transition">
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div>
                    <div class="text-xl md:text-2xl font-bold text-gray-800">{{ $totalConversations }}</div>
                    <div class="text-xs text-gray-400 mt-0.5">Total</div>
                </div>
            </div>

            {{-- Resolved --}}
            <div class="bg-white rounded-xl md:rounded-2xl border border-gray-100 shadow-sm p-4 md:p-5 flex items-center gap-3 md:gap-4 hover:shadow-md transition">
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <div class="text-xl md:text-2xl font-bold text-gray-800">{{ $resolvedConversations }}</div>
                    <div class="text-xs text-gray-400 mt-0.5">Resolved</div>
                </div>
            </div>

            {{-- Resolved Today --}}
            <div class="bg-white rounded-xl md:rounded-2xl border border-gray-100 shadow-sm p-4 md:p-5 flex items-center gap-3 md:gap-4 hover:shadow-md transition col-span-2 md:col-span-1">
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-purple-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-purple-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <div class="text-xl md:text-2xl font-bold text-gray-800">{{ $resolvedToday }}</div>
                    <div class="text-xs text-gray-400 mt-0.5">Today</div>
                </div>
            </div>

        </div>

        {{-- Main Content --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">

            {{-- Recent Conversations --}}
            <div class="lg:col-span-2 bg-white rounded-xl md:rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-4 md:px-6 py-3 md:py-4 border-b border-gray-50 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                        </svg>
                        <h2 class="font-semibold text-gray-800 text-sm">Recent Conversations</h2>
                    </div>
                    <a href="{{ route('agent.conversations') }}" class="text-xs text-indigo-600 hover:underline font-medium">View all →</a>
                </div>

                <div id="recent-conv-list">
                    @forelse($recentConversations as $conv)
                    @php
                        $unread = $conv->messages()->where('sender_type','visitor')->where('is_read', false)->count();
                    @endphp
                    <a href="{{ route('agent.conversations.show', $conv) }}"
                        class="flex items-center gap-3 md:gap-4 px-4 md:px-6 py-3 md:py-4 border-b border-gray-50 hover:bg-indigo-50/40 transition last:border-0">
                        <div class="w-9 h-9 md:w-10 md:h-10 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                            {{ strtoupper(substr($conv->visitor_name ?? 'V', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap mb-0.5">
                                <span class="font-medium text-sm text-gray-800">{{ $conv->visitor_name ?? 'Visitor' }}</span>
                                @if(!$conv->agent_id)
                                <span class="text-xs bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded-full font-medium">Unassigned</span>
                                @endif
                                @if($unread > 0)
                                <span class="text-xs bg-red-100 text-red-600 px-1.5 py-0.5 rounded-full font-medium animate-pulse">{{ $unread }} new</span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-400 truncate">{{ $conv->visitor_page ?? 'No page info' }}</div>
                        </div>
                        <div class="flex flex-col items-end gap-1 flex-shrink-0">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                {{ $conv->status === 'open' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ ucfirst($conv->status) }}
                            </span>
                            <span class="text-xs text-gray-400">{{ $conv->created_at->diffForHumans() }}</span>
                        </div>
                    </a>
                    @empty
                    <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                        <svg class="w-10 h-10 mb-3 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <div class="text-sm font-medium text-gray-400">No conversations yet.</div>
                        <div class="text-xs text-gray-300 mt-1">Share your embed code to get started!</div>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Right Sidebar --}}
            <div class="flex flex-col gap-4">

                {{-- Embed Code Card — sirf Owner ko dikhao --}}
                @if(auth()->user()->role === 'owner')
                <div class="bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl md:rounded-2xl p-4 md:p-5 text-white shadow-lg">
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        <span class="font-semibold text-sm">Embed Code</span>
                    </div>
                    <p class="text-indigo-200 text-xs mb-4">Add the chat widget to your website in seconds.</p>
                    <a href="{{ route('agent.embed') }}"
                        class="block text-center bg-white text-indigo-600 font-semibold text-sm py-2.5 rounded-xl hover:bg-indigo-50 transition">
                        Get Embed Code
                    </a>
                </div>

                {{-- Widget Token — sirf Owner ko dikhao --}}
                <div class="bg-white rounded-xl md:rounded-2xl border border-gray-100 shadow-sm p-4 md:p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                        <h3 class="font-semibold text-sm text-gray-700">Widget Token</h3>
                    </div>
                    @if($widget)
                    <div class="bg-gray-50 rounded-xl px-3 py-2 text-xs font-mono text-gray-500 break-all border border-gray-100">
                        {{ substr($widget->embed_token, 0, 20) }}...
                    </div>
                    <div class="mt-3 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full {{ $widget->is_active ? 'bg-green-500' : 'bg-red-400' }} inline-block"></span>
                        <span class="text-xs text-gray-500 font-medium">Widget {{ $widget->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                    @else
                    <div class="text-xs text-gray-400">No widget found.</div>
                    @endif
                </div>
                @endif
                {{-- /Owner only --}}

                {{-- All Time Stats — sabko dikhao --}}
                <div class="bg-white rounded-xl md:rounded-2xl border border-gray-100 shadow-sm p-4 md:p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <h3 class="font-semibold text-sm text-gray-700">All Time Stats</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-500">Total</span>
                            <span class="text-sm font-bold text-gray-800">{{ $totalConversations }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="bg-indigo-500 h-1.5 rounded-full" style="width:100%"></div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-500">Resolved</span>
                            <span class="text-sm font-bold text-green-600">{{ $resolvedConversations }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            @if($totalConversations > 0)
                            <div class="bg-green-500 h-1.5 rounded-full"
                                style="width:{{ ($resolvedConversations / $totalConversations) * 100 }}%"></div>
                            @endif
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-500">Open</span>
                            <span class="text-sm font-bold text-orange-500">{{ $openConversations }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            @if($totalConversations > 0)
                            <div class="bg-orange-400 h-1.5 rounded-full"
                                style="width:{{ ($openConversations / $totalConversations) * 100 }}%"></div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function refreshDashboard() {
    fetch('/chatbot/agent/conversations/data?filter=all', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(function(conversations) {
        var totalUnread = conversations.reduce(function(sum, conv) {
            return sum + (conv.unread_count || 0);
        }, 0);
        var unreadEl = document.getElementById('unread-count');
        var unreadDot = document.getElementById('unread-dot');
        if (unreadEl) unreadEl.textContent = totalUnread;
        if (unreadDot) unreadDot.style.display = totalUnread > 0 ? 'inline-block' : 'none';

        var convList = document.getElementById('recent-conv-list');
        if (!convList) return;

        if (conversations.length === 0) {
            convList.innerHTML = '<div class="flex flex-col items-center justify-center py-12 text-gray-400"><div class="text-sm">No conversations yet.</div></div>';
            return;
        }

        var html = '';
        conversations.slice(0, 5).forEach(function(conv) {
            var name = conv.visitor_name || 'Visitor';
            var initial = name.charAt(0).toUpperCase();
            var unread = conv.unread_count || 0;
            var isOpen = conv.status === 'open';

            html += '<a href="/chatbot/agent/conversations/' + conv.id + '" class="flex items-center gap-3 px-4 md:px-6 py-3 border-b border-gray-50 hover:bg-indigo-50/40 transition last:border-0">';
            html += '<div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">' + initial + '</div>';
            html += '<div class="flex-1 min-w-0"><div class="flex items-center gap-1.5 flex-wrap mb-0.5">';
            html += '<span class="font-medium text-sm text-gray-800">' + name + '</span>';
            if (!conv.agent_id) html += '<span class="text-xs bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded-full">Unassigned</span>';
            if (unread > 0) html += '<span class="text-xs bg-red-100 text-red-600 px-1.5 py-0.5 rounded-full animate-pulse">' + unread + ' new</span>';
            html += '</div><div class="text-xs text-gray-400 truncate">' + (conv.visitor_page || 'No page info') + '</div></div>';
            html += '<div class="flex flex-col items-end gap-1 flex-shrink-0">';
            html += '<span class="text-xs px-2 py-0.5 rounded-full ' + (isOpen ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500') + '">' + (isOpen ? 'Open' : 'Resolved') + '</span>';
            html += '<span class="text-xs text-gray-400">' + conv.time_ago + '</span>';
            html += '</div></a>';
        });
        convList.innerHTML = html;
    });
}

setInterval(refreshDashboard, 5000);

setInterval(function () {
    fetch('/chatbot/agent/ping', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    });
}, 30000);
</script>

@endsection