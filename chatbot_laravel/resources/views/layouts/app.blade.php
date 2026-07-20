<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="data:,">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Outfit', sans-serif;
            margin: 0;
            overflow: hidden;
            height: 100vh;
            background: #f0f2f5;
        }

        /* ── Navbar ── */
        .app-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 64px;
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 1px solid rgba(0,0,0,0.06);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 100;
            gap: 12px;
        }
        .app-navbar .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        .app-navbar .nav-brand-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(99,102,241,0.3);
            flex-shrink: 0;
        }
        .app-navbar .nav-brand-icon svg {
            width: 18px;
            height: 18px;
            fill: #fff;
        }
        .app-navbar .nav-brand-text {
            font-weight: 700;
            font-size: 18px;
            color: #1e293b;
            letter-spacing: -0.3px;
            white-space: nowrap;
        }
        .app-navbar .nav-brand-text span {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .app-navbar .nav-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        .app-navbar .nav-user-name {
            font-size: 14px;
            font-weight: 500;
            color: #475569;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 140px;
        }
        .app-navbar .nav-logout-btn {
            font-family: 'Outfit', sans-serif;
            font-size: 13px;
            font-weight: 600;
            color: #ef4444;
            background: #fef2f2;
            border: 1px solid #fecaca;
            padding: 6px 14px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .app-navbar .nav-logout-btn:hover {
            background: #fee2e2;
            transform: translateY(-1px);
        }
        .menu-toggle-btn {
            display: none;
            background: none;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 6px 8px;
            cursor: pointer;
            color: #64748b;
            flex-shrink: 0;
        }

        /* ── Layout Container ── */
        .app-layout {
            display: flex;
            height: 100vh;
            padding-top: 64px;
        }

        /* ── Sidebar ── */
        .app-sidebar {
            position: fixed;
            top: 64px;
            left: 0;
            bottom: 0;
            width: 260px;
            background: linear-gradient(180deg, #0f172a 0%, #1e1b4b 100%);
            display: flex;
            flex-direction: column;
            z-index: 50;
            overflow-y: auto;
            overflow-x: hidden;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .app-sidebar::-webkit-scrollbar { width: 4px; }
        .app-sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }

        .sb-section-label {
            padding: 20px 20px 8px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(148,163,184,0.5);
        }
        .sb-section-label:first-child { padding-top: 16px; }

        .sb-link {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 2px 12px;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            color: #94a3b8;
            text-decoration: none;
            transition: all 0.2s;
            position: relative;
        }
        .sb-link:hover {
            color: #e2e8f0;
            background: rgba(255,255,255,0.06);
        }
        .sb-link.active {
            color: #fff;
            background: linear-gradient(135deg, rgba(99,102,241,0.9), rgba(139,92,246,0.9));
            box-shadow: 0 4px 16px rgba(99,102,241,0.3);
        }
        .sb-link.active::before {
            content: '';
            position: absolute;
            left: -12px;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 20px;
            background: #818cf8;
            border-radius: 0 4px 4px 0;
        }
        .sb-link svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            opacity: 0.7;
        }
        .sb-link.active svg { opacity: 1; }

        .sb-user {
            margin-top: auto;
            padding: 16px 20px;
            border-top: 1px solid rgba(255,255,255,0.06);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sb-user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            flex-shrink: 0;
        }
        .sb-user-name {
            font-size: 13px;
            font-weight: 600;
            color: #e2e8f0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sb-user-role {
            font-size: 11px;
            color: #64748b;
            text-transform: capitalize;
        }

        /* ── Main Content ── */
        .app-main {
            margin-left: 260px;
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            height: calc(100vh - 64px);
            padding: 28px 32px 40px;
            scroll-behavior: smooth;
        }
        .app-main::-webkit-scrollbar { width: 6px; }
        .app-main::-webkit-scrollbar-track { background: transparent; }
        .app-main::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .app-main::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* ── Overlay (Mobile) ── */
        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15,23,42,0.6);
            backdrop-filter: blur(4px);
            z-index: 40;
            display: none;
        }
        .sidebar-overlay.show { display: block; }

        /* ── Mobile Responsive ── */
        @media (max-width: 768px) {
            .app-navbar {
                padding: 0 14px;
            }
            .app-navbar .nav-brand-text {
                font-size: 16px;
            }
            .app-navbar .nav-user-name {
                font-size: 13px;
                max-width: 80px;
            }
            .app-navbar .nav-logout-btn {
                font-size: 12px;
                padding: 5px 10px;
                border-radius: 8px;
            }
            .menu-toggle-btn { display: flex; }
            .nav-user-desktop { display: none; }
            @media (max-width:768px) {
				.app-sidebar {
					transform: translateX(-100%);
					width: 280px;
					top: 0;
					padding-top: 52px;
					z-index: 60;
				}
			}
            .app-sidebar.open { transform: translateX(0); }
            .app-main {
                margin-left: 0;
                padding: 20px 16px 32px;
            }
        }
        @media (min-width: 769px) {
            .nav-user-mobile { display: none; }
        }
    </style>

    @stack('head')
</head>
<body>

<!-- ── Navbar ── -->
<nav class="app-navbar">
    <div class="nav-brand">
        <button class="menu-toggle-btn" id="menu-toggle">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <div class="nav-brand-icon">
            <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
        </div>
        <div class="nav-brand-text">Chatbot <span>SaaS</span></div>
    </div>
    <div class="nav-right">
        <button type="button" id="notif-bell-btn" title="Notifications on — click to mute"
            style="background:none;border:none;font-size:18px;cursor:pointer;padding:6px 8px;border-radius:8px;line-height:1;">
            🔔
        </button>
        <span class="nav-user-name nav-user-desktop">{{ auth()->user()->name }}</span>
        <span class="nav-user-name nav-user-mobile">{{ explode(' ', auth()->user()->name)[0] }}</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="nav-logout-btn" type="submit">Logout</button>
        </form>
    </div>
</nav>

<!-- ── Overlay ── -->
<div class="sidebar-overlay" id="sidebar-overlay" onclick="closeSidebar()"></div>

<!-- ── Layout ── -->
<div class="app-layout">

    <!-- Sidebar -->
    <aside class="app-sidebar" id="sidebar">

        @if(auth()->user()->role === 'admin')

        {{-- ── ADMIN SIDEBAR ── --}}
        <div class="sb-section-label">Main</div>

        <a href="{{ route('admin.dashboard') }}"
           class="sb-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>

        <div class="sb-section-label">Management</div>

        <a href="{{ route('admin.tenants') }}"
           class="sb-link {{ request()->routeIs('admin.tenants*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            Tenants
        </a>

        <a href="{{ route('admin.plans') }}"
           class="sb-link {{ request()->routeIs('admin.plans*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Plans
        </a>

        <a href="{{ route('admin.revenue') }}"
           class="sb-link {{ request()->routeIs('admin.revenue*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Revenue
        </a>

        @else

        {{-- ── OWNER / AGENT SIDEBAR ── --}}
        <div class="sb-section-label">Main</div>

        <a href="{{ route('agent.dashboard') }}"
           class="sb-link {{ request()->routeIs('agent.dashboard') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>

        <div class="sb-section-label">Support</div>

        <a href="{{ route('agent.conversations') }}"
           class="sb-link {{ request()->routeIs('agent.conversations*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            Conversations
        </a>

        <a href="{{ route('agent.analytics') }}"
           class="sb-link {{ request()->routeIs('agent.analytics*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Analytics
        </a>

        <a href="{{ route('agent.canned-responses') }}"
           class="sb-link {{ request()->routeIs('agent.canned-responses*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Canned Responses
        </a>

        {{-- Sirf Owner ko Settings dikhao --}}
        @if(auth()->user()->role === 'owner')

        <div class="sb-section-label">Settings</div>

        <a href="{{ route('agent.embed') }}"
           class="sb-link {{ request()->routeIs('agent.embed') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
            </svg>
            Embed Code
        </a>

		<a href="{{ route('billing.dashboard') }}"
		   class="sb-link {{ request()->routeIs('billing.*') ? 'active' : '' }}">
			<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
			</svg>
			Billing
		</a>

        <a href="{{ route('agent.agents') }}"
           class="sb-link {{ request()->routeIs('agent.agents*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Team
        </a>

        @endif
        {{-- /Owner only --}}

        @endif

        <!-- User Info -->
        <a href="{{ route('agent.password.change') }}" class="sb-user" style="text-decoration:none;">
            <div class="sb-user-avatar" style="overflow:hidden;">
                @if(auth()->user()->avatar)
                    <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" style="width:100%;height:100%;object-fit:cover;">
                @else
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                @endif
            </div>
            <div style="min-width:0;">
                <div class="sb-user-name">{{ auth()->user()->name }}</div>
                <div class="sb-user-role">{{ auth()->user()->role }}</div>
            </div>
        </a>

    </aside>

    <!-- Main Content (Scrollable) -->
    <main class="app-main" id="app-main">
        @yield('content')
    </main>

</div>

<script>
    var PUSHER_KEY     = '{{ config("broadcasting.connections.pusher.key") ?: "a5278981e9260924a023" }}';
    var PUSHER_CLUSTER = '{{ config("broadcasting.connections.pusher.options.cluster") ?: "ap2" }}';

    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebar-overlay');
    var menuBtn = document.getElementById('menu-toggle');

    if (menuBtn) {
        menuBtn.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
        });
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
    }

    sidebar.querySelectorAll('a').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth < 769) closeSidebar();
        });
    });
	
setInterval(() => {
    fetch('/chatbot/agent/ping', { method: 'POST', 
    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
    });
}, 60000);

// ── Sound + Desktop Notifications ────────────────────────────
(function () {
    var NOTIFY_SOUND = 'data:audio/wav;base64,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';

    function isMuted() {
        return localStorage.getItem('cb_notify_muted') === '1';
    }

    function playSound() {
        if (isMuted() || !NOTIFY_SOUND) return;
        try {
            var audio = new Audio(NOTIFY_SOUND);
            audio.play().catch(function () {});
        } catch (e) {}
    }

    function showDesktopNotification(title, body) {
        if (isMuted()) return;
        if (typeof Notification === 'undefined' || Notification.permission !== 'granted') return;
        try {
            new Notification(title, { body: body, icon: '/favicon.ico' });
        } catch (e) {}
    }

    function isViewingConversation(conversationId) {
        return window.location.pathname.indexOf('/conversations/' + conversationId) !== -1;
    }

    if (typeof Pusher === 'undefined' || !PUSHER_KEY) return;

    var pusher = new Pusher(PUSHER_KEY, { cluster: PUSHER_CLUSTER });

    var tenantId = {{ auth()->user()->tenant_id ?? 'null' }};
    var agentId  = {{ auth()->id() }};

    if (tenantId) {
        var tenantChannel = pusher.subscribe('tenant.' + tenantId);

        tenantChannel.bind('new.conversation', function (data) {
            playSound();
            showDesktopNotification('New Conversation!', 'Visitor: ' + (data.visitorName || 'Someone'));
        });

        tenantChannel.bind('new.visitor.message', function (data) {
            if (isViewingConversation(data.conversationId)) return;
            playSound();
            showDesktopNotification('New message from ' + (data.visitorName || 'Visitor'), data.body || '');
        });
    }

    var agentChannel = pusher.subscribe('agent.' + agentId);

    agentChannel.bind('conversation.assigned', function (data) {
        playSound();
        showDesktopNotification('Conversation Assigned!', 'New chat from: ' + (data.visitorName || 'Someone'));
    });

    agentChannel.bind('new.visitor.message', function (data) {
        if (isViewingConversation(data.conversationId)) return;
        playSound();
        showDesktopNotification('New message from ' + (data.visitorName || 'Visitor'), data.body || '');
    });

    // ── Bell toggle (mute/unmute + permission request) ──
    var bellBtn = document.getElementById('notif-bell-btn');
    function paintBell() {
        if (!bellBtn) return;
        bellBtn.textContent = isMuted() ? '🔕' : '🔔';
        bellBtn.title = isMuted() ? 'Notifications muted — click to enable' : 'Notifications on — click to mute';
    }
    if (bellBtn) {
        paintBell();
        bellBtn.addEventListener('click', function () {
            if (isMuted()) {
                localStorage.setItem('cb_notify_muted', '0');
                if (typeof Notification !== 'undefined' && Notification.permission === 'default') {
                    Notification.requestPermission().then(paintBell);
                }
            } else {
                localStorage.setItem('cb_notify_muted', '1');
            }
            paintBell();
        });
    }

    if (typeof Notification !== 'undefined' && Notification.permission === 'default' && !isMuted()) {
        Notification.requestPermission();
    }
})();
</script>

</body>
</html>