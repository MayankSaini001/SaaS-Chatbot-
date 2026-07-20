@extends('layouts.app')

@section('content')

@push('head')
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
/* ── Embed Page — Two-Column Premium ── */
.ep * { box-sizing: border-box; }

/* Hero */
.ep-hero {
    text-align: center;
    padding: 1.8rem 1rem 1.2rem;
    animation: epUp 0.5s ease both;
}
.ep-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #eef2ff, #f5f3ff);
    color: #6366f1;
    font-size: 11px;
    font-weight: 700;
    padding: 5px 14px;
    border-radius: 20px;
    margin-bottom: 10px;
    letter-spacing: 0.6px;
    border: 1px solid rgba(99,102,241,0.12);
    text-transform: uppercase;
}
.ep-badge-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #22c55e;
    box-shadow: 0 0 6px rgba(34,197,94,0.5);
    animation: epPulse 1.8s ease infinite;
}
.ep-hero h1 {
    font-family: 'Outfit', sans-serif;
    font-size: clamp(22px, 5vw, 34px);
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -1px;
    margin: 0 0 6px;
}
.ep-hero p {
    font-family: 'Outfit', sans-serif;
    font-size: 13px;
    color: #64748b;
    margin: 0 auto;
    max-width: 320px;
}

/* ── Two-Column Grid ── */
.ep-grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 24px;
    align-items: start;
    animation: epUp 0.55s 0.1s ease both;
    padding: 0 4px;
}

/* ── Left Column ── */
.ep-left { display: flex; flex-direction: column; gap: 18px; }

/* ── Right Column — Sticky Preview ── */
.ep-right {
    position: sticky;
    top: 20px;
}

/* Cards */
.ep-card {
    background: #fff;
    border-radius: 18px;
    border: 1px solid rgba(0,0,0,0.05);
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03), 0 6px 20px rgba(0,0,0,0.025);
    transition: box-shadow 0.3s, transform 0.3s;
}
.ep-card:hover {
    box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 10px 28px rgba(0,0,0,0.055);
    transform: translateY(-1px);
}
.ep-card-hdr {
    display: flex;
    align-items: center;
    gap: 11px;
    margin-bottom: 4px;
}
.ep-num {
    width: 30px; height: 30px;
    border-radius: 9px;
    background: linear-gradient(135deg, #eef2ff, #f5f3ff);
    border: 1px solid rgba(99,102,241,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Outfit', sans-serif;
    font-size: 13px;
    font-weight: 800;
    color: #6366f1;
    flex-shrink: 0;
}
.ep-card-title {
    font-family: 'Outfit', sans-serif;
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.2px;
}
.ep-card-sub {
    font-family: 'Outfit', sans-serif;
    font-size: 12px;
    color: #94a3b8;
    margin-left: 41px;
    margin-bottom: 14px;
}

/* Code block */
.ep-code {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
    border-radius: 14px;
    padding: 16px 18px;
    padding-right: 110px;
    position: relative;
    font-family: 'JetBrains Mono', monospace;
    font-size: 11.5px;
    line-height: 1.9;
    color: #94a3b8;
    word-break: break-all;
    border: 1px solid rgba(99,102,241,0.12);
}
.ep-code .tb { color: #7dd3fc; }
.ep-code .tg { color: #6ee7b7; }.ep-copybtn {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border: none;
    border-radius: 9px;
    padding: 6px 14px;
    font-size: 11px;
    font-weight: 700;
    color: #fff;
    cursor: pointer;
    font-family: 'Outfit', sans-serif;
    transition: all 0.25s;
    box-shadow: 0 2px 8px rgba(99,102,241,0.3);
    white-space: nowrap;
}
.ep-copybtn:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(99,102,241,0.4); }

/* Desktop: inside code block, top-right */
.ep-copybtn-desktop {
    position: absolute;
    top: 12px;
    right: 12px;
}

/* Mobile button: hidden by default */
.ep-copybtn-mobile {
    display: none;
    width: 100%;
    margin-top: 10px;
    padding: 11px 14px;
    font-size: 13px;
    border-radius: 11px;
    text-align: center;
}
.ep-copybtn:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(99,102,241,0.4); }

/* HTML block */
.ep-html {
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    padding: 14px 16px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    line-height: 2;
    color: #94a3b8;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.ep-html .ht { color: #6366f1; }
.ep-html .hh { color: #22c55e; font-weight: 500; }

/* Chips */
.ep-chips {
    display: flex;
    gap: 7px;
    flex-wrap: wrap;
    margin-top: 14px;
}
.ep-chip {
    display: flex;
    align-items: center;
    gap: 5px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 5px 12px;
    font-family: 'Outfit', sans-serif;
    font-size: 11px;
    color: #475569;
    font-weight: 600;
}

/* ── Preview Card (Right) ── */
.ep-preview-card {
    background: #fff;
    border-radius: 18px;
    border: 1px solid rgba(0,0,0,0.05);
    padding: 18px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03), 0 8px 24px rgba(0,0,0,0.04);
}
.ep-pvlabel {
    font-family: 'Outfit', sans-serif;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #94a3b8;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.ep-pvhint {
    font-size: 10px;
    color: #cbd5e1;
    font-weight: 500;
    letter-spacing: 0;
    text-transform: none;
}

/* Browser Frame */
.ep-frame {
    border-radius: 14px;
    position: relative;
    overflow: hidden;
    border: 1.5px solid #e2e8f0;
    background: linear-gradient(160deg, #f0f4ff 0%, #faf5ff 40%, #f0fdf4 100%);
    aspect-ratio: 4/5;
    min-height: 320px;
}
.ep-fbar {
    background: #f1f5f9;
    border-bottom: 1px solid #e2e8f0;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.ep-fdots { display: flex; gap: 5px; }
.ep-fdot { width: 8px; height: 8px; border-radius: 50%; }
.ep-furl {
    flex: 1;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 4px 10px;
    font-size: 10px;
    color: #94a3b8;
    font-family: 'JetBrains Mono', monospace;
    margin-left: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ep-fbody {
    position: relative;
    height: calc(100% - 40px);
}

/* Fake page content lines */
.ep-fakelines {
    padding: 20px 18px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.ep-fl {
    height: 8px;
    border-radius: 4px;
    background: rgba(0,0,0,0.04);
}

/* Chat widget preview */
.ep-mbubble {
    position: absolute;
    bottom: 12px;
    right: 12px;
    width: 44px; height: 44px;
    border-radius: 50%;
    display: none;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
}
.ep-mbubble:hover { transform: scale(1.08); }
.ep-mbubble svg { width: 20px; height: 20px; fill: #fff; }

.ep-mchat {
    position: absolute;
    bottom: 12px;
    right: 12px;
    width: 210px;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.14);
    transition: all 0.35s cubic-bezier(0.34,1.56,0.64,1);
    transform-origin: bottom right;
}
.ep-mchat.hidden {
    opacity: 0;
    transform: scale(0.85) translateY(14px);
    pointer-events: none;
}
.ep-mhdr {
    padding: 10px 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.ep-mav {
    width: 28px; height: 28px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 9px;
    font-weight: 800;
    color: #fff;
    font-family: 'Outfit', sans-serif;
    flex-shrink: 0;
}
.ep-mname {
    font-size: 11px;
    font-weight: 700;
    color: #fff;
    font-family: 'Outfit', sans-serif;
}
.ep-monline {
    font-size: 9px;
    color: rgba(255,255,255,0.8);
    font-family: 'Outfit', sans-serif;
}
.ep-mbody {
    background: #fff;
    padding: 10px 12px;
}
.ep-mmsg {
    background: #f1f5f9;
    border-radius: 12px;
    padding: 7px 10px;
    font-size: 10px;
    color: #374151;
    margin-bottom: 7px;
    display: inline-block;
    max-width: 100%;
    font-family: 'Outfit', sans-serif;
    line-height: 1.4;
}
.ep-minp {
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 20px;
    padding: 5px 10px;
    font-size: 9px;
    color: #94a3b8;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-family: 'Outfit', sans-serif;
}

/* Status */
.ep-status {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 14px;
    padding: 8px 14px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 10px;
    font-family: 'Outfit', sans-serif;
    font-size: 12px;
    font-weight: 600;
    color: #15803d;
}
.ep-status-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #22c55e;
    animation: epPulse 1.8s ease infinite;
    flex-shrink: 0;
}

.ep-pos-label {
    text-align: center;
    margin-top: 10px;
    font-family: 'Outfit', sans-serif;
    font-size: 11px;
    color: #94a3b8;
    font-weight: 500;
}

/* ── Form ── */
.ep-field { margin-bottom: 16px; }
.ep-field label {
    display: block;
    font-family: 'Outfit', sans-serif;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 7px;
}
.ep-input {
    width: 100%;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 11px;
    padding: 12px 14px;
    font-size: 14px;
    font-family: 'Outfit', sans-serif;
    color: #0f172a;
    outline: none;
    transition: all 0.25s;
    -webkit-appearance: none;
    appearance: none;
}
.ep-input:focus {
    border-color: #6366f1;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
}
select.ep-input { cursor: pointer; }
.ep-frow {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 16px;
}
.ep-crow { display: flex; align-items: center; gap: 10px; }
.ep-cswatch {
    width: 44px; height: 44px;
    border-radius: 11px;
    border: 2px solid #e2e8f0;
    cursor: pointer;
    flex-shrink: 0;
    padding: 2px;
    background: transparent;
    min-width: 44px;
}
.ep-cswatch:focus { border-color: #6366f1; }

.ep-savebtn {
    padding: 10px 28px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #fff;
    border: none;
    border-radius: 13px;
    font-family: 'Outfit', sans-serif;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.25s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 6px;
    box-shadow: 0 4px 16px rgba(99,102,241,0.3);
    position: relative;
    overflow: hidden;
    -webkit-tap-highlight-color: transparent;
}
.ep-savebtn::before {
    content: '';
    position: absolute;
    top: 0; left: -100%;
    width: 100%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
    transition: left 0.5s;
}
.ep-savebtn:hover::before { left: 100%; }
.ep-savebtn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(99,102,241,0.4);
}
.ep-savebtn:active { transform: scale(0.98); }

/* Toast */
.ep-toast {
    position: fixed;
    bottom: 28px;
    left: 50%;
    transform: translateX(-50%) translateY(16px);
    background: #fff;
    border: 1.5px solid #bbf7d0;
    color: #065f46;
    border-radius: 20px;
    padding: 10px 22px;
    font-size: 13px;
    font-weight: 600;
    font-family: 'Outfit', sans-serif;
    opacity: 0;
    transition: all 0.35s;
    pointer-events: none;
    z-index: 9999;
    box-shadow: 0 8px 28px rgba(0,0,0,0.1);
    white-space: nowrap;
    max-width: calc(100vw - 32px);
}
.ep-toast.show {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}

/* ── Responsive: Tablet ── */
@media (max-width: 900px) {
    .ep-grid {
        grid-template-columns: 1fr;
    }
    .ep-right {
        position: static;
    }
    .ep-frame {
        aspect-ratio: 16/9;
        min-height: 240px;
    }
}

/* ── Responsive: Mobile ── */
@media (max-width: 640px) {
    .ep-hero {
        padding: 1.2rem 0.75rem 1rem;
    }
    .ep-hero h1 {
        font-size: 24px;
        letter-spacing: -0.5px;
    }
    .ep-hero p {
        font-size: 12px;
    }

    .ep-grid {
        gap: 14px;
        padding: 0 2px;
    }

    .ep-left {
        gap: 14px;
    }

    .ep-card {
        padding: 16px 14px;
        border-radius: 14px;
    }

    .ep-card-title { font-size: 14px; }
    .ep-card-sub { font-size: 11px; margin-left: 38px; }

    .ep-frow {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .ep-frow .ep-field {
        margin-bottom: 14px;
    }

    .ep-code {
        font-size: 10.5px;
        padding: 14px 14px;
        padding-right: 14px;
        line-height: 1.8;
    }

    .ep-copybtn-desktop { display: none; }
    .ep-copybtn-mobile  { display: block; }

    .ep-html {
        font-size: 10px;
        padding: 12px 14px;
        line-height: 1.85;
    }

    .ep-chips {
        gap: 5px;
    }
    .ep-chip {
        font-size: 10px;
        padding: 4px 10px;
    }

    .ep-input {
        font-size: 16px; /* prevents iOS zoom */
        padding: 12px 13px;
    }

    select.ep-input {
        font-size: 16px;
    }

    .ep-cswatch {
        width: 48px;
        height: 48px;
    }

    .ep-savebtn {
        padding: 10px 24px;
        font-size: 13px;
        border-radius: 12px;
        width: auto;
    }

    /* Preview on mobile — landscape ratio */
    .ep-frame {
        aspect-ratio: 16/10;
        min-height: 200px;
    }

    .ep-mchat {
        width: 180px;
        bottom: 10px;
        right: 10px;
    }
    .ep-mchat.left-pos {
        right: auto;
        left: 10px;
    }

    .ep-mbubble {
        bottom: 10px;
        right: 10px;
        width: 40px;
        height: 40px;
    }
    .ep-mbubble.left-pos {
        right: auto;
        left: 10px;
    }

    .ep-preview-card {
        padding: 14px;
        border-radius: 14px;
    }

    .ep-status {
        font-size: 11px;
        padding: 7px 12px;
    }

    .ep-pos-label {
        font-size: 10px;
    }

    .ep-toast {
        bottom: 16px;
        font-size: 12px;
        padding: 9px 18px;
    }
}

/* ── Extra small (360px phones) ── */
@media (max-width: 380px) {
    .ep-hero h1 { font-size: 20px; }
    .ep-card { padding: 14px 12px; }
    .ep-code { font-size: 10px; }
    .ep-html { font-size: 9.5px; }
    .ep-mchat { width: 160px; }
    .ep-chips { gap: 4px; }
    .ep-chip { font-size: 9.5px; padding: 4px 9px; }
}

@keyframes epUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes epPulse {
    0%,100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(0.7); }
}
</style>
@endpush

{{-- Hero --}}
<div class="ep-hero">
    <div class="ep-badge"><span class="ep-badge-dot"></span> Widget Active</div>
    <h1>Your Embed Code</h1>
    <p>Add the chat widget to any website in under 60 seconds</p>
</div>

{{-- Two-Column Layout --}}
<div class="ep-grid">

    {{-- ══ LEFT COLUMN ══ --}}
    <div class="ep-left">

        {{-- Card 1: Copy Code --}}
        <div class="ep-card">
            <div class="ep-card-hdr">
                <div class="ep-num">1</div>
                <div class="ep-card-title">Copy Embed Code</div>
            </div>
            <div class="ep-card-sub">Your unique script — don't change the URL</div>
            <div class="ep-code" id="ep-codebox">
                &lt;script src="<span class="tb">{{ config('app.url') }}/js/widget.js</span>"<br>
                &nbsp;&nbsp;data-token="<span class="tg">{{ $widget->embed_token }}</span>"&gt;&lt;/script&gt;
                <button class="ep-copybtn ep-copybtn-desktop" onclick="epCopy()">📋 Copy</button>
            </div>
            <button class="ep-copybtn ep-copybtn-mobile" onclick="epCopy()">📋 Copy Code</button>
            <div class="ep-chips">
                <div class="ep-chip">🔒 Unique token</div>
                <div class="ep-chip">🌐 Any domain</div>
                <div class="ep-chip">⚡ Zero config</div>
            </div>
        </div>

        {{-- Card 2: Paste --}}
        <div class="ep-card">
            <div class="ep-card-hdr">
                <div class="ep-num">2</div>
                <div class="ep-card-title">Paste Before &lt;/body&gt;</div>
            </div>
            <div class="ep-card-sub">Works on WordPress, Shopify, HTML — any platform</div>
            <div class="ep-html">
                <span class="ht">&lt;html&gt;</span><br>
                &nbsp;&nbsp;<span class="ht">&lt;body&gt;</span><br>
                &nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#cbd5e1">... your website content ...</span><br><br>
                &nbsp;&nbsp;&nbsp;&nbsp;<span class="hh">&lt;!-- ✦ Paste script here --&gt;</span><br>
                &nbsp;&nbsp;&nbsp;&nbsp;<span class="hh">&lt;script src="..."&gt;&lt;/script&gt;</span><br><br>
                &nbsp;&nbsp;<span class="ht">&lt;/body&gt;</span><br>
                <span class="ht">&lt;/html&gt;</span>
            </div>
        </div>

        {{-- Card 3: Settings --}}
        <div class="ep-card">
            <div class="ep-card-hdr">
                <div class="ep-num">3</div>
                <div class="ep-card-title">Widget Settings</div>
            </div>
            <div class="ep-card-sub">Customize how your widget looks</div>

            <form method="POST" action="{{ route('agent.widget.update') }}">
                @csrf

                <div class="ep-field">
                    <label>Greeting Message</label>
                    <input type="text" name="greeting" class="ep-input" id="ep-greeting"
                           value="{{ $widget->greeting }}" oninput="epLive()">
                </div>

                <div class="ep-frow">
                    <div class="ep-field" style="margin-bottom:0">
                        <label>Widget Color</label>
                        <div class="ep-crow">
                            <input type="color" name="color" class="ep-cswatch" id="ep-color"
                                   value="{{ $widget->color }}" oninput="epLive()">
                            <input type="text" class="ep-input" id="ep-colortext"
                                   value="{{ $widget->color }}" oninput="epSyncColor()" style="flex:1">
                        </div>
                    </div>
                    <div class="ep-field" style="margin-bottom:0">
                        <label>Position</label>
                        <select name="position" class="ep-input" id="ep-pos" onchange="epLive()">
                            <option value="bottom-right" {{ $widget->position === 'bottom-right' ? 'selected' : '' }}>Bottom Right</option>
                            <option value="bottom-left" {{ $widget->position === 'bottom-left' ? 'selected' : '' }}>Bottom Left</option>
                        </select>
                    </div>
                </div>

                <div class="ep-field">
                    <label>Widget Title</label>
                    <input type="text" name="title" class="ep-input" id="ep-title"
                           value="{{ $widget->title ?? 'Support Team' }}"
                           placeholder="Support Team" oninput="epLive()">
                </div>

                @php
                    $epCanWhiteLabel = in_array(auth()->user()->tenant->plan ?? 'basic', ['pro', 'enterprise']);
                @endphp

                <div class="ep-field" style="{{ $epCanWhiteLabel ? '' : 'opacity:0.6;' }}">
                    <label style="display:flex;align-items:center;gap:8px;cursor:{{ $epCanWhiteLabel ? 'pointer' : 'not-allowed' }};">
                        <input type="checkbox" name="hide_branding" value="1"
                               {{ $widget->hide_branding ? 'checked' : '' }}
                               {{ $epCanWhiteLabel ? '' : 'disabled onclick="return false;"' }}>
                        <span>Remove "Powered by ChatBot SaaS" branding</span>
                        @unless($epCanWhiteLabel)
                            <span style="font-size:11px;font-weight:700;color:#b45309;background:#fef3c7;padding:2px 8px;border-radius:999px;">PRO/ENTERPRISE</span>
                        @endunless
                    </label>
                    @unless($epCanWhiteLabel)
                        <p style="font-size:12px;color:#9ca3af;margin-top:4px;">Upgrade to Pro or Enterprise to white-label your widget.</p>
                    @endunless
                </div>

                <button type="submit" class="ep-savebtn">💾 Save Settings</button>

                @if(session('success'))
                <script>
                    document.addEventListener('DOMContentLoaded', function(){
                        var t = document.getElementById('ep-toast');
                        t.textContent = '✓ {{ session("success") }}';
                        t.classList.add('show');
                        setTimeout(function(){ t.classList.remove('show'); }, 3000);
                    });
                </script>
                @endif

                @if(session('error'))
                <script>
                    document.addEventListener('DOMContentLoaded', function(){
                        var t = document.getElementById('ep-toast');
                        t.textContent = '⚠ {{ session("error") }}';
                        t.style.background = '#dc2626';
                        t.classList.add('show');
                        setTimeout(function(){ t.classList.remove('show'); }, 4000);
                    });
                </script>
                @endif
            </form>
        </div>

        {{-- Card 4: Business Hours --}}
        @php
            $bhSchedule = $widget->business_hours ?: \App\Models\Widget::defaultBusinessHours();
            $bhDayLabels = ['mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday', 'thu' => 'Thursday', 'fri' => 'Friday', 'sat' => 'Saturday', 'sun' => 'Sunday'];
        @endphp
        <div class="ep-card">
            <div class="ep-card-hdr">
                <div class="ep-num">4</div>
                <div class="ep-card-title">Business Hours</div>
            </div>
            <div class="ep-card-sub">Show visitors when you're closed, outside these hours</div>

            <form method="POST" action="{{ route('agent.widget.business-hours') }}">
                @csrf

                <div class="ep-field" style="display:flex;align-items:center;gap:10px;">
                    <input type="checkbox" name="enabled" id="bh-enabled" value="1"
                           {{ $widget->business_hours_enabled ? 'checked' : '' }}
                           style="width:18px;height:18px;accent-color:#6366f1;cursor:pointer;">
                    <label for="bh-enabled" style="margin:0;cursor:pointer;">Enable business hours</label>
                </div>

                <div class="ep-field">
                    <label>Timezone</label>
                    <select name="timezone" class="ep-input">
                        @php
                            $tzOptions = ['Asia/Kolkata' => 'India (IST)', 'Asia/Dubai' => 'Dubai (GST)', 'Europe/London' => 'London (GMT/BST)', 'America/New_York' => 'New York (ET)', 'America/Los_Angeles' => 'Los Angeles (PT)', 'Asia/Singapore' => 'Singapore (SGT)', 'Australia/Sydney' => 'Sydney (AEST)'];
                            $currentTz = $widget->business_hours_timezone ?: 'Asia/Kolkata';
                        @endphp
                        @foreach($tzOptions as $tzVal => $tzLabel)
                        <option value="{{ $tzVal }}" {{ $currentTz === $tzVal ? 'selected' : '' }}>{{ $tzLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="ep-field">
                    <label style="display:block;margin-bottom:8px;">Weekly Schedule</label>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        @foreach($bhDayLabels as $dayKey => $dayLabel)
                        @php $day = $bhSchedule[$dayKey] ?? ['enabled' => false, 'start' => '09:00', 'end' => '18:00']; @endphp
                        <div style="display:flex;align-items:center;gap:10px;padding:8px 10px;background:#f8fafc;border-radius:10px;">
                            <input type="checkbox" name="days[{{ $dayKey }}][enabled]" value="1"
                                   {{ !empty($day['enabled']) ? 'checked' : '' }}
                                   style="width:16px;height:16px;accent-color:#6366f1;cursor:pointer;flex-shrink:0;">
                            <span style="width:80px;font-size:12.5px;font-weight:600;color:#334155;flex-shrink:0;">{{ $dayLabel }}</span>
                            <input type="time" name="days[{{ $dayKey }}][start]" value="{{ $day['start'] ?? '09:00' }}"
                                   class="ep-input" style="padding:6px 8px;font-size:12px;">
                            <span style="color:#94a3b8;font-size:12px;">to</span>
                            <input type="time" name="days[{{ $dayKey }}][end]" value="{{ $day['end'] ?? '18:00' }}"
                                   class="ep-input" style="padding:6px 8px;font-size:12px;">
                        </div>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="ep-savebtn">💾 Save Business Hours</button>
            </form>
        </div>

    </div>

    {{-- ══ RIGHT COLUMN — Sticky Preview ══ --}}
    <div class="ep-right">
        <div class="ep-preview-card">
            <div class="ep-pvlabel">
                <span>👁 Live Preview</span>
                <span class="ep-pvhint">tap bubble to toggle</span>
            </div>

            <div class="ep-frame">
                {{-- Browser bar --}}
                <div class="ep-fbar">
                    <div class="ep-fdots">
                        <div class="ep-fdot" style="background:#ff5f57"></div>
                        <div class="ep-fdot" style="background:#febc2e"></div>
                        <div class="ep-fdot" style="background:#28c840"></div>
                    </div>
                    <div class="ep-furl">yourwebsite.com</div>
                </div>

                {{-- Fake page --}}
                <div class="ep-fbody">
                    <div class="ep-fakelines">
                        <div class="ep-fl" style="width:55%"></div>
                        <div class="ep-fl" style="width:80%"></div>
                        <div class="ep-fl" style="width:40%"></div>
                        <div class="ep-fl" style="width:70%;margin-top:8px"></div>
                        <div class="ep-fl" style="width:90%"></div>
                        <div class="ep-fl" style="width:60%"></div>
                        <div class="ep-fl" style="width:45%;margin-top:8px"></div>
                        <div class="ep-fl" style="width:75%"></div>
                    </div>

                    {{-- Chat --}}
                    <div class="ep-mchat" id="ep-mchat">
                        <div class="ep-mhdr" id="ep-mhdr" style="background:{{ $widget->color }}">
                            <div class="ep-mav" id="ep-mav">
                                {{ strtoupper(substr(preg_replace('/\s+/', '', $widget->title ?? 'ST'), 0, 2)) }}
                            </div>
                            <div>
                                <div class="ep-mname" id="ep-mname">{{ $widget->title ?? 'Support Team' }}</div>
                                <div class="ep-monline">● Online</div>
                            </div>
                        </div>
                        <div class="ep-mbody">
                            <div class="ep-mmsg" id="ep-mmsg">{{ $widget->greeting }}</div>
                            <div class="ep-minp">Type a message... <span>➤</span></div>
                        </div>
                    </div>

                    {{-- Bubble --}}
                    <div class="ep-mbubble" id="ep-mbubble" onclick="epToggle()" style="background:{{ $widget->color }}">
                        <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
                    </div>
                </div>
            </div>

            <div class="ep-status">
                <span class="ep-status-dot"></span>
                Widget is live and ready
            </div>

            <div class="ep-pos-label" id="ep-pos-label">
                Position: <strong>{{ $widget->position === 'bottom-left' ? 'Bottom Left' : 'Bottom Right' }}</strong>
            </div>
        </div>
    </div>

</div>

<div class="ep-toast" id="ep-toast">✓ Settings saved!</div>

<textarea id="ep-raw" style="position:fixed;left:-9999px;opacity:0;font-size:16px;">&lt;script src="{{ config('app.url') }}/js/widget.js" data-token="{{ $widget->embed_token }}"&gt;&lt;/script&gt;</textarea>

<script>
var epOpen = true;

function epToggle(){
    epOpen = !epOpen;
    var c = document.getElementById('ep-mchat');
    var b = document.getElementById('ep-mbubble');
    if(epOpen){
        c.classList.remove('hidden');
        b.style.display = 'none';
    } else {
        c.classList.add('hidden');
        b.style.display = 'flex';
    }
}

function epLive(){
    var color = document.getElementById('ep-color').value;
    document.getElementById('ep-colortext').value = color;
    document.getElementById('ep-mhdr').style.background = color;
    document.getElementById('ep-mbubble').style.background = color;

    var title = document.getElementById('ep-title').value || 'Support Team';
    document.getElementById('ep-mname').textContent = title;
    var words = title.trim().split(/\s+/);
    var init = words.map(function(w){ return w[0]||''; }).join('').substring(0,2).toUpperCase();
    document.getElementById('ep-mav').textContent = init;

    document.getElementById('ep-mmsg').textContent =
        document.getElementById('ep-greeting').value || 'Hi! How can we help?';

    var pos = document.getElementById('ep-pos').value;
    var chat = document.getElementById('ep-mchat');
    var bub  = document.getElementById('ep-mbubble');
    var lbl  = document.getElementById('ep-pos-label');
    if(pos === 'bottom-left'){
        chat.style.right='auto'; chat.style.left='10px';
        bub.style.right='auto';  bub.style.left='10px';
        lbl.innerHTML = 'Position: <strong>Bottom Left</strong>';
    } else {
        chat.style.left='auto'; chat.style.right='10px';
        bub.style.left='auto';  bub.style.right='10px';
        lbl.innerHTML = 'Position: <strong>Bottom Right</strong>';
    }
}

function epSyncColor(){
    var v = document.getElementById('ep-colortext').value;
    if(/^#[0-9A-Fa-f]{6}$/.test(v)){
        document.getElementById('ep-color').value = v;
        epLive();
    }
}

function epCopy(){
    var ta = document.getElementById('ep-raw');
    var btns = document.querySelectorAll('.ep-copybtn');
    function setLabel(txt){
        btns.forEach(function(b){ b.textContent = txt; });
    }
    if(navigator.clipboard && window.isSecureContext){
        navigator.clipboard.writeText(ta.value).then(function(){
            setLabel('✓ Copied!');
            setTimeout(function(){ 
                document.querySelector('.ep-copybtn-desktop').textContent = '📋 Copy';
                document.querySelector('.ep-copybtn-mobile').textContent = '📋 Copy Code';
            }, 2000);
        });
    } else {
        ta.style.left='0'; ta.style.opacity='1';
        ta.select();
        document.execCommand('copy');
        ta.style.left='-9999px'; ta.style.opacity='0';
        setLabel('✓ Copied!');
        setTimeout(function(){ 
            document.querySelector('.ep-copybtn-desktop').textContent = '📋 Copy';
            document.querySelector('.ep-copybtn-mobile').textContent = '📋 Copy Code';
        }, 2000);
    }
}
</script>

@endsection