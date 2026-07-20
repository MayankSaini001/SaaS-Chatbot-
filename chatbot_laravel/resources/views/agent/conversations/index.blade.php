@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Conversations</h1>
    <div class="flex items-center gap-3">
        <!-- Filter Tabs -->
        <div class="flex bg-gray-100 rounded-lg p-1 text-sm">
            <button onclick="filterConvs('all')" id="tab-all"
                class="tab-btn px-4 py-1.5 rounded-md font-medium transition bg-white shadow text-indigo-600">All</button>
            <button onclick="filterConvs('open')" id="tab-open"
                class="tab-btn px-4 py-1.5 rounded-md font-medium transition text-gray-500">Open</button>
            <button onclick="filterConvs('resolved')" id="tab-resolved"
                class="tab-btn px-4 py-1.5 rounded-md font-medium transition text-gray-500">Resolved</button>
            <button onclick="filterConvs('mine')" id="tab-mine"
                class="tab-btn px-4 py-1.5 rounded-md font-medium transition text-gray-500">Mine</button>
            @if(auth()->user()->role === 'owner')
            <button onclick="filterConvs('unassigned')" id="tab-unassigned"
                class="tab-btn px-4 py-1.5 rounded-md font-medium transition text-gray-500">Unassigned</button>
            @endif
        </div>
        <!-- Live indicator -->
        <div class="flex items-center gap-1.5 text-xs text-green-600">
            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span> Live
        </div>
    </div>
</div>

<div class="mb-4 flex gap-3">
    <input
        type="text"
        id="conversationSearch"
        placeholder="Search visitor, agent, or anything said in the chat..."
        class="flex-1 border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
    >
    <select id="tagFilter" class="border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" style="max-width:180px;">
        <option value="">All Tags</option>
    </select>
</div>

<!-- Conversations List -->
<div class="bg-white rounded-xl shadow-sm border overflow-hidden" id="conv-list">
    <div id="conv-items">
        <div class="px-6 py-8 text-center text-gray-400 text-sm" id="loading">Loading conversations...</div>
    </div>
</div>

<!-- Toast -->
<div id="cb-toast" style="display:none;position:fixed;bottom:24px;right:24px;z-index:9999;
     background:#1e293b;color:#fff;padding:12px 18px;border-radius:10px;font-size:13px;
     box-shadow:0 8px 24px rgba(0,0,0,0.25);max-width:300px;"></div>

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
var currentFilter       = 'all';
var previousUnreadTotal = 0;
var currentUserId       = {{ auth()->id() }};
var currentUserRole     = @json(auth()->user()->role);
var tenantId            = {{ auth()->user()->tenant_id ?? 'null' }};
var PUSHER_KEY          = '{{ config("broadcasting.connections.pusher.key") ?: "a5278981e9260924a023" }}';
var PUSHER_CLUSTER      = '{{ config("broadcasting.connections.pusher.options.cluster") ?: "ap2" }}';
var searchTerm = '';

function toast(text) {
    var el = document.getElementById('cb-toast');
    if (!el) return;
    el.textContent = text;
    el.style.display = 'block';
    clearTimeout(el._t);
    el._t = setTimeout(function(){ el.style.display = 'none'; }, 4000);
}

function filterConvs(filter) {
    currentFilter = filter;
    document.querySelectorAll('.tab-btn').forEach(function(btn) {
        btn.className = 'tab-btn px-4 py-1.5 rounded-md font-medium transition text-gray-500';
    });
    var activeTab = document.getElementById('tab-' + filter);
    if (activeTab) activeTab.className = 'tab-btn px-4 py-1.5 rounded-md font-medium transition bg-white shadow text-indigo-600';
    loadConversations();
}

function playBeep() {
    try {
        var ctx = new (window.AudioContext || window.webkitAudioContext)();
        var o = ctx.createOscillator(), g = ctx.createGain();
        o.connect(g); g.connect(ctx.destination);
        o.frequency.value = 800; o.type = 'sine';
        g.gain.setValueAtTime(0.3, ctx.currentTime);
        g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
        o.start(ctx.currentTime); o.stop(ctx.currentTime + 0.3);
    } catch(e) {}
}

function checkNewMessages(conversations) {
    var total = conversations.reduce(function(s, c){ return s + (c.unread_count || 0); }, 0);
    if (previousUnreadTotal !== 0 && total > previousUnreadTotal) playBeep();
    previousUnreadTotal = total;
}

function escHtml(t) {
    return String(t == null ? '' : t)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function loadConversations() {
    var url = '/chatbot/agent/conversations/data?filter=' + currentFilter;
    if (currentTagId) url += '&tag_id=' + currentTagId;
    if (searchTerm) url += '&q=' + encodeURIComponent(searchTerm);
    fetch(url, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(function(conversations) {
        var container = document.getElementById('conv-items');

        // client-side unassigned filter (owner tab)
        if (currentFilter === 'unassigned') {
            conversations = conversations.filter(function(c){ return !c.agent_id; });
        }

        checkNewMessages(conversations);

        if (conversations.length === 0) {
            container.innerHTML =
                '<div class="px-6 py-12 text-center text-gray-400">' +
                '<div class="text-4xl mb-3">💬</div>' +
                '<div class="text-sm">No conversations found.</div></div>';
            return;
        }

        var html = '';
        conversations.forEach(function(conv) {
            var isOpen   = conv.status === 'open';
            var name     = conv.visitor_name || 'Visitor';
            var initials = name.charAt(0).toUpperCase();
            var lastMsg  = conv.last_message || 'No messages yet';
            var unread   = conv.unread_count || 0;

            // Assignment badge
            var assignBadge;
            if (conv.agent_id) {
                var who = (conv.agent_id === currentUserId) ? 'You' : (conv.agent_name || 'Agent');
                assignBadge = '<span class="text-xs px-2 py-0.5 rounded-full font-medium bg-indigo-50 text-indigo-600">👤 ' + escHtml(who) + '</span>';
            } else {
                assignBadge = '<span class="text-xs px-2 py-0.5 rounded-full font-medium bg-amber-50 text-amber-700">Unassigned</span>';
            }

            // Rating badge (only if visitor has rated)
            var ratingBadge = '';
            if (conv.rating) {
                var starsStr = '★'.repeat(conv.rating) + '☆'.repeat(5 - conv.rating);
                ratingBadge = '<span class="text-xs px-2 py-0.5 rounded-full font-medium bg-amber-50 text-amber-600" style="letter-spacing:1px;" title="' + conv.rating + '/5 rating">' + starsStr + '</span>';
            }

            // Tag pills
            var tagBadges = '';
            (conv.tags || []).forEach(function(t) {
                tagBadges += '<span class="text-xs px-2 py-0.5 rounded-full font-medium" style="color:#fff;background:' + (t.color || '#6366f1') + ';">' + escHtml(t.name) + '</span>';
            });

            html +=
            '<a href="/chatbot/agent/conversations/' + conv.id + '"' +
            ' class="flex items-center gap-4 px-6 py-4 border-b last:border-0 hover:bg-indigo-50 transition group">' +
                '<div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm flex-shrink-0">' + escHtml(initials) + '</div>' +
                '<div class="flex-1 min-w-0">' +
                    '<div class="flex justify-between items-center mb-0.5">' +
                        '<span class="font-medium text-gray-800 text-sm ' + (unread > 0 ? 'font-bold' : '') + '">' + escHtml(name) + '</span>' +
                        '<span class="text-xs text-gray-400">' + escHtml(conv.time_ago) + '</span>' +
                    '</div>' +
                    '<div class="text-xs ' + (unread > 0 ? 'text-gray-800 font-medium' : 'text-gray-500') + ' truncate">' + (conv.matched ? '🔍 ' : '') + escHtml(lastMsg) + '</div>' +
                    '<div class="mt-1 flex items-center gap-1.5 flex-wrap">' + assignBadge + ratingBadge + tagBadges + '</div>' +
                '</div>' +
                '<div class="flex flex-col items-end gap-1 flex-shrink-0">' +
                    '<span class="text-xs px-2 py-0.5 rounded-full font-medium ' + (isOpen ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500') + '">' + (isOpen ? 'Open' : 'Resolved') + '</span>' +
                    (unread > 0 ? '<span class="w-5 h-5 bg-indigo-600 text-white text-xs rounded-full flex items-center justify-center font-medium animate-pulse">' + unread + '</span>' : '') +
                '</div>' +
            '</a>';
        });

        container.innerHTML = html;
    })
    .catch(function(err){
        console.error('CB: loadConversations failed', err);
        var container = document.getElementById('conv-items');
        if (container) {
            container.innerHTML =
                '<div class="px-6 py-12 text-center text-gray-400">' +
                '<div class="text-4xl mb-3">⚠️</div>' +
                '<div class="text-sm">Could not load conversations. Please refresh the page.</div></div>';
        }
    });
}

var currentTagId = '';

var searchDebounceTimer = null;
document.getElementById('conversationSearch').addEventListener('input', function () {

    searchTerm = this.value.trim();

    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(loadConversations, 350);

});

var tagFilterEl = document.getElementById('tagFilter');
if (tagFilterEl) {
    fetch(window.location.origin + '/chatbot/agent/tags', { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (tags) {
            (tags || []).forEach(function (t) {
                var opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.name;
                tagFilterEl.appendChild(opt);
            });
        })
        .catch(function () {});

    tagFilterEl.addEventListener('change', function () {
        currentTagId = this.value;
        loadConversations();
    });
}

loadConversations();
setInterval(loadConversations, 4000);

// ── Real-time: light up the inbox the moment something changes ──
if (typeof Pusher !== 'undefined' && PUSHER_KEY) {
    var pusher = new Pusher(PUSHER_KEY, { cluster: PUSHER_CLUSTER });

    // A chat got assigned to ME -> notify + refresh
    var myChannel = pusher.subscribe('agent.' + currentUserId);
    myChannel.bind('conversation.assigned', function(data) {
        playBeep();
        toast('🤝 New chat assigned to you: ' + (data && data.visitor_name ? data.visitor_name : 'Visitor'));
        loadConversations();
    });

    // Owner sees every brand-new conversation for the tenant
    if (currentUserRole === 'owner' && tenantId) {
        var tenantChannel = pusher.subscribe('tenant.' + tenantId);
        tenantChannel.bind('new.conversation', function(data) {
            playBeep();
            toast('💬 New conversation: ' + (data && data.visitorName ? data.visitorName : 'Visitor'));
            loadConversations();
        });
    }
}
</script>
@endsection
