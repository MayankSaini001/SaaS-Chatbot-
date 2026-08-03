(function () {
  var token = document.currentScript.getAttribute('data-token');
  var baseUrl = 'https://knights.topscripts.in/chatbot';
  var conversationId = (function () {
    var v = sessionStorage.getItem('cb_conversation_' + token);
    // Purani/poisoned sessions me kabhi kabhi literal string "undefined" ya
    // "null" save ho gayi thi (jab conversation create fail hui thi) — us
    // corrupt value ko yahin saaf kar do, warna har request usi bhi ID se
    // 404 khaati rahegi.
    if (!v || v === 'undefined' || v === 'null') {
      sessionStorage.removeItem('cb_conversation_' + token);
      return null;
    }
    return v;
  })();
  var isResolved = false;
  var pollInterval = null;
  var lastMessageId = 0;
  var sendingMessage = false;
  var pusherInstance = null;
  teardownPusher();

  // Properly tear down the Pusher connection (unsubscribe + disconnect)
  // instead of just discarding the reference — otherwise the old
  // connection stays alive in the background and keeps receiving
  // 'message.sent' events, causing every message to render twice once
  // initPusher() creates a second connection.
  function teardownPusher() {
    if (pusherInstance) {
      try { pusherInstance.disconnect(); } catch (e) {}
    }
    pusherInstance = null;
  }
  var visitorInfoCollected = false; // Feature 1: track if name/email collected

  // ── UNREAD BADGE STATE (agent message count on icon while closed) ──
  var unreadCount = 0;
  var lastSeenId = parseInt(sessionStorage.getItem('cb_lastseen_' + token) || '0', 10) || 0;
  var bgPollInterval = null;

  // ── PAGINATION ──────────────────────────────────────────────
  var currentPage = 1;
  var allPagesLoaded = false;
  var isLoadingHistory = false;
  var MESSAGES_PER_PAGE = 20;

  var PUSHER_KEY = 'a5278981e9260924a023';
  var PUSHER_CLUSTER = 'ap2';

  // ── NOTIFICATION SOUND ───────────────────────────────────────
  var audioCtx = null;
  var soundPending = false;

  function _doPlaySound() {
    try {
      var o = audioCtx.createOscillator();
      var g = audioCtx.createGain();
      o.connect(g); g.connect(audioCtx.destination);
      o.type = 'sine';
      o.frequency.setValueAtTime(880, audioCtx.currentTime);
      o.frequency.exponentialRampToValueAtTime(660, audioCtx.currentTime + 0.15);
      g.gain.setValueAtTime(0.3, audioCtx.currentTime);
      g.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.4);
      o.start(audioCtx.currentTime);
      o.stop(audioCtx.currentTime + 0.4);
    } catch(e) {}
  }

  function unlockAudio() {
    if (audioCtx) {
      if (audioCtx.state === 'suspended') {
        audioCtx.resume().then(function() {
          if (soundPending) { soundPending = false; _doPlaySound(); }
        });
      } else {
        if (soundPending) { soundPending = false; _doPlaySound(); }
      }
      return;
    }
    try {
      audioCtx = new (window.AudioContext || window.webkitAudioContext)();
      var buf = audioCtx.createBuffer(1, 1, 22050);
      var src = audioCtx.createBufferSource();
      src.buffer = buf; src.connect(audioCtx.destination); src.start(0);
      if (soundPending) { soundPending = false; _doPlaySound(); }
    } catch(e) {}
  }

  function playNotifSound() {
    if (!audioCtx || audioCtx.state === 'suspended') {
      soundPending = true;
      return;
    }
    _doPlaySound();
  }

  ['click', 'keydown', 'touchstart'].forEach(function(evt) {
    document.addEventListener(evt, unlockAudio, { once: false });
  });

  // ── STYLES ───────────────────────────────────────────────────
  var style = document.createElement('style');
  style.innerHTML = `
  
  .cb-name{
  font-size:11px;
  font-weight:600;
  color:#64748b;
  margin-bottom:3px;
	}

	.cb-name-r{
	  text-align:right;
	}
  
    #cb-bubble {
      position: fixed; bottom: 24px; right: 24px;
      width: 56px; height: 56px; border-radius: 50%;
      background: linear-gradient(135deg, #4f46e5, #7c3aed);
      cursor: pointer;
      box-shadow: 0 4px 20px rgba(79,70,229,0.5);
      display: flex; align-items: center; justify-content: center;
      z-index: 99999; transition: transform 0.2s ease;
    }
    #cb-bubble:hover { transform: scale(1.08); }
    #cb-badge {
      position: absolute; top: -4px; right: -4px;
      min-width: 20px; height: 20px; box-sizing: border-box;
      padding: 0 5px; border-radius: 11px;
      background: #ef4444; color: #fff;
      font-size: 11px; font-weight: 700; line-height: 20px;
      text-align: center; display: none; pointer-events: none;
      box-shadow: 0 0 0 2px #fff;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      animation: cb-badge-pop 0.3s ease;
    }
    @keyframes cb-badge-pop {
      0% { transform: scale(0); }
      70% { transform: scale(1.25); }
      100% { transform: scale(1); }
    }
    #cb-box {
      display: none;
      position: fixed; bottom: 90px; right: 24px;
      width: 340px; height: 500px;
      background: #f1f5f9;
      border-radius: 16px;
      box-shadow: 0 16px 48px rgba(0,0,0,0.18);
      z-index: 99999; flex-direction: column; overflow: hidden;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      min-width: 280px; min-height: 400px;
      max-width: 600px; max-height: 700px;
      transform-origin: bottom right;
      transform: scale(0.85); opacity: 0;
      transition: transform 0.22s cubic-bezier(0.34,1.56,0.64,1), opacity 0.18s ease;
    }
    #cb-box.open { display: flex; }
    #cb-box.cb-anim-in { transform: scale(1); opacity: 1; }
    #cb-box.cb-anim-out { transform: scale(0.85); opacity: 0; }
    #cb-resize-handle {
      position: absolute; bottom: 0; left: 0;
      width: 18px; height: 18px;
      cursor: sw-resize; z-index: 10;
      display: flex; align-items: flex-end; justify-content: flex-start;
      padding: 3px;
    }
    #cb-resize-handle svg { width: 10px; height: 10px; opacity: 0.3; }
    #cb-resize-handle:hover svg { opacity: 0.7; }
    #cb-header {
      cursor: move;
      background: linear-gradient(135deg, #4f46e5, #7c3aed);
      color: white; padding: 14px 16px;
      display: flex; align-items: center; gap: 12px;
      flex-shrink: 0;
    }
    #cb-avatar {
      width: 38px; height: 38px; border-radius: 50%;
      background: white;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px; flex-shrink: 0;
    }
    #cb-header-info { flex: 1; min-width: 0; }
    #cb-header-name { font-weight: 600; font-size: 14px; }
    #cb-header-status {
      font-size: 11px; opacity: 0.9;
      display: flex; align-items: center; gap: 4px; margin-top: 2px;
      white-space: nowrap;
    }
    #cb-status-dot {
      width: 7px; height: 7px; border-radius: 50%;
      background: #4ade80; display: inline-block; flex-shrink: 0;
    }
    #cb-status-dot.offline { background: #94a3b8; }
    @keyframes cb-bounce {
      0%, 60%, 100% { transform: translateY(0); }
      30% { transform: translateY(-4px); }
    }
    #cb-close-btn {
      background: rgba(255,255,255,0.2); border: none;
      color: white; width: 28px; height: 28px; flex-shrink: 0;
      border-radius: 50%; cursor: pointer; font-size: 14px;
      display: flex; align-items: center; justify-content: center;
    }
    #cb-close-btn:hover { background: rgba(255,255,255,0.35); }
    #cb-messages-wrap {
      flex: 1; position: relative; overflow: hidden;
      display: flex; flex-direction: column;
    }
    #cb-load-more {
      text-align: center; padding: 6px 0 2px;
      flex-shrink: 0;
    }
    #cb-load-more button {
      background: none; border: 1px solid #c7d2fe;
      color: #4f46e5; font-size: 11px; border-radius: 12px;
      padding: 4px 14px; cursor: pointer;
    }
    #cb-load-more button:hover { background: #eef2ff; }
    #cb-messages {
      flex: 1; overflow-y: auto; padding: 12px;
      display: flex; flex-direction: column; gap: 8px;
      font-size: 13px; scroll-behavior: smooth;
    }
    #cb-messages::-webkit-scrollbar { width: 4px; }
    #cb-messages::-webkit-scrollbar-thumb { background: #c7d2fe; border-radius: 10px; }
    .cb-row-v { display: flex; justify-content: flex-end; }
    .cb-row-a { display: flex; justify-content: flex-start; align-items: flex-end; gap: 6px; }
    .cb-icon {
      width: 26px; height: 26px; border-radius: 50%; flex-shrink: 0;
      background: linear-gradient(135deg, #4f46e5, #7c3aed);
      display: flex; align-items: center; justify-content: center;
      font-size: 12px; color: white; margin-bottom: 2px;
    }
    .cb-bubble-v {
      background: linear-gradient(135deg,#4f46e5,#7c3aed);
      color:white;
      padding:9px 13px;
      border-radius:16px 16px 4px 16px;
      max-width:220px;
      overflow:hidden;
    }
    .cb-bubble-v img, .cb-bubble-a img {
      max-width:220px; width:100%; height:auto;
      display:block; border-radius:8px;
    }
    .cb-bubble-a {
      background: white; color: #1e293b;
      padding: 9px 13px;
      border-radius: 16px 16px 16px 4px;
      font-size: 13px; line-height: 1.5;
      max-width: 220px; word-break: break-word;
      box-shadow: 0 2px 6px rgba(0,0,0,0.07);
    }
    .cb-time { font-size: 10px; color: #94a3b8; margin-top: 3px; }
    .cb-time-r { text-align: right; }
    .cb-time-l { text-align: left; padding-left: 32px; }
    .cb-system {
      text-align: center; font-size: 11px; color: #64748b;
      background: #e2e8f0; border-radius: 20px;
      padding: 4px 12px; align-self: center;
    }
    .cb-blocked-msg {
      text-align: center; font-size: 12px; color: #b91c1c;
      background: #fee2e2; border: 1px solid #fecaca; border-radius: 10px;
      padding: 10px 12px; font-weight: 500;
    }
    #cb-offline-banner {
      display: none;
      background: #fef9c3; border-top: 1px solid #fde68a;
      color: #92400e; font-size: 11px; text-align: center;
      padding: 6px 12px; flex-shrink: 0;
    }
    #cb-input-area {
      display: flex; align-items: center;
      border-top: 1px solid #e2e8f0;
      padding: 10px 12px; background: white; gap: 8px;
      flex-shrink: 0;
    }
    #cb-input {
      flex: 1; border: 1.5px solid #e2e8f0; border-radius: 20px;
      padding: 9px 14px; font-size: 13px; outline: none;
      background: #f8fafc; min-width: 0;
    }
    #cb-input:focus { border-color: #4f46e5; background: white; }
    #cb-send {
      width: 38px; height: 38px; flex-shrink: 0;
      background: linear-gradient(135deg, #4f46e5, #7c3aed);
      border: none; border-radius: 50%; cursor: pointer;
      display: flex; align-items: center; justify-content: center;
    }
    #cb-footer {
      text-align: center; font-size: 10px; color: #94a3b8;
      padding: 5px; background: white; flex-shrink: 0;
    }
    #cb-new-chat-btn {
      background: linear-gradient(135deg, #4f46e5, #7c3aed);
      color: white; border: none; padding: 9px 0;
      border-radius: 20px; font-size: 13px;
      cursor: pointer; width: 100%; margin-top: 6px;
    }
    /* ── FEATURE 1: Visitor Info Form Styles ── */
    #cb-visitor-form {
      flex: 1; display: flex; flex-direction: column;
      justify-content: center; padding: 20px 18px;
      background: #f8fafc;
    }
    #cb-visitor-form h3 {
      font-size: 15px; font-weight: 600; color: #1e293b;
      margin: 0 0 6px 0; text-align: center;
    }
    #cb-visitor-form p {
      font-size: 12px; color: #64748b;
      text-align: center; margin: 0 0 18px 0; line-height: 1.5;
    }
    #cb-visitor-form input {
      width: 100%; border: 1.5px solid #e2e8f0;
      border-radius: 10px; padding: 10px 12px;
      font-size: 13px; outline: none; background: white;
      margin-bottom: 10px; box-sizing: border-box;
      color: #1e293b;
    }
    #cb-visitor-form input:focus { border-color: #4f46e5; }
    #cb-visitor-form button {
      width: 100%;
      background: linear-gradient(135deg, #4f46e5, #7c3aed);
      color: white; border: none;
      padding: 11px 0; border-radius: 10px;
      font-size: 13px; font-weight: 600;
      cursor: pointer; margin-top: 4px;
    }
    /* ── CSAT Rating Styles ── */
    #cb-rating-box { width: 100%; padding: 10px 4px 4px; }
    #cb-rating-box .cb-rating-title {
      color: #1e293b; font-size: 13px; font-weight: 600;
      text-align: center; margin-bottom: 10px;
    }
    #cb-stars {
      display: flex; justify-content: center; gap: 6px; margin-bottom: 10px;
    }
    .cb-star {
      font-size: 28px; cursor: pointer; color: #d1d5db;
      line-height: 1; transition: color 0.12s, transform 0.12s;
    }
    .cb-star:hover { transform: scale(1.15); }
    #cb-rating-feedback {
      width: 100%; box-sizing: border-box; resize: none;
      border: 1.5px solid #e2e8f0; border-radius: 10px;
      padding: 8px 10px; font-size: 12px; font-family: inherit;
      margin-bottom: 10px; outline: none; color: #1e293b;
      background: #f8fafc;
    }
    #cb-rating-feedback:focus { border-color: #4f46e5; background: white; }
    #cb-rating-submit {
      width: 100%; border: none; border-radius: 20px;
      padding: 10px 0; font-size: 13px; font-weight: 600;
      cursor: pointer; transition: background 0.2s, opacity 0.2s;
      background: linear-gradient(135deg, #4f46e5, #7c3aed);
      color: white;
    }
    #cb-rating-submit:disabled {
      background: #e2e8f0; color: #94a3b8; cursor: not-allowed;
    }
    #cb-rating-skip {
      display: block; width: 100%; background: none; border: none;
      color: #94a3b8; font-size: 12px; margin-top: 8px;
      padding: 4px 0; cursor: pointer; text-align: center;
    }
    #cb-rating-skip:hover { color: #64748b; text-decoration: underline; }
    #cb-visitor-form button:hover { opacity: 0.92; }
    #cb-visitor-form .cb-form-err {
      color: #ef4444; font-size: 11px; margin-bottom: 8px;
      text-align: center; display: none;
    }
    /* ── Message Edit / Delete ── */
    .cb-msg-v-wrap { position: relative; }
    .cb-msg-actions {
      display: flex; justify-content: flex-end; gap: 6px;
      margin-top: 2px; opacity: 0; height: 0; overflow: hidden;
      transition: opacity 0.15s ease;
    }
    .cb-msg-v-wrap:hover .cb-msg-actions,
    .cb-msg-v-wrap.cb-editing .cb-msg-actions { opacity: 1; height: auto; overflow: visible; }
    .cb-msg-act-btn {
      background: none; border: none; cursor: pointer;
      font-size: 11px; color: #94a3b8; padding: 1px 3px;
      line-height: 1; display: inline-flex; align-items: center; gap: 2px;
    }
    .cb-msg-act-btn:hover { color: #4f46e5; }
    .cb-msg-act-btn.cb-msg-del:hover { color: #ef4444; }
    .cb-edited-tag { font-size: 10px; color: #c7d2fe; margin-left: 4px; font-style: italic; }
    .cb-bubble-a .cb-edited-tag { color: #94a3b8; }
    .cb-deleted-bubble {
      background: transparent !important; color: #94a3b8 !important;
      border: 1px dashed #cbd5e1; font-style: italic; font-size: 12px;
    }
    .cb-msg-edit-box { display: flex; flex-direction: column; gap: 6px; max-width: 220px; }
    .cb-msg-edit-input {
      width: 100%; box-sizing: border-box; border: 1.5px solid #c7d2fe;
      border-radius: 12px; padding: 7px 10px; font-size: 13px;
      outline: none; resize: none; font-family: inherit; color: #1e293b;
    }
    .cb-msg-edit-btns { display: flex; justify-content: flex-end; gap: 8px; }
    .cb-msg-edit-save, .cb-msg-edit-cancel {
      border: none; background: none; cursor: pointer;
      font-size: 11px; font-weight: 600; padding: 2px 4px;
    }
    .cb-msg-edit-save { color: #4f46e5; }
    .cb-msg-edit-cancel { color: #94a3b8; }
    /* ── Proactive Chat Trigger ── */
    #cb-proactive-popup {
      position: fixed; bottom: 92px; right: 24px;
      max-width: 240px; background: white; color: #1e293b;
      padding: 12px 32px 12px 14px; border-radius: 14px 14px 4px 14px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.16);
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      font-size: 13px; line-height: 1.4; z-index: 99998;
      cursor: pointer; opacity: 0; transform: translateY(8px) scale(0.96);
      transition: opacity 0.25s ease, transform 0.25s ease;
    }
    #cb-proactive-popup.cb-show { opacity: 1; transform: translateY(0) scale(1); }
    #cb-proactive-close {
      position: absolute; top: 6px; right: 8px;
      background: none; border: none; cursor: pointer;
      font-size: 13px; color: #94a3b8; line-height: 1; padding: 2px;
    }
    #cb-proactive-close:hover { color: #1e293b; }
  `;
  document.head.appendChild(style);

  // Load Pusher JS
  var pusherScript = document.createElement('script');
  pusherScript.src = 'https://js.pusher.com/8.2.0/pusher.min.js';
  document.head.appendChild(pusherScript);

  // Bubble
  var bubble = document.createElement('div');
  bubble.id = 'cb-bubble';
  bubble.innerHTML = `<svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:26px;height:26px;">
    <path d="M8 12C8 9.8 9.8 8 12 8H28C30.2 8 32 9.8 32 12V22C32 24.2 30.2 26 28 26H22L16 32V26H12C9.8 26 8 24.2 8 22V12Z" fill="white"/>
    <circle cx="14" cy="17" r="2" fill="#4f46e5"/>
    <circle cx="20" cy="17" r="2" fill="#4f46e5"/>
    <circle cx="26" cy="17" r="2" fill="#4f46e5"/>
  </svg>`;
  document.body.appendChild(bubble);

  // Unread message badge on the launcher icon
  var badge = document.createElement('span');
  badge.id = 'cb-badge';
  bubble.appendChild(badge);

  // Box
  var box = document.createElement('div');
  box.id = 'cb-box';
  box.innerHTML = `
    <div id="cb-header">
      <div id="cb-avatar">
        <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:26px;height:26px;">
          <rect x="6" y="8" width="26" height="20" rx="5" fill="#1e1b4b" opacity="0.9"/>
          <rect x="16" y="18" width="26" height="20" rx="5" fill="#1e1b4b" opacity="0.6"/>
          <circle cx="13" cy="18" r="2" fill="white"/>
          <circle cx="19" cy="18" r="2" fill="white"/>
          <circle cx="25" cy="18" r="2" fill="white"/>
        </svg>
      </div>
      <div id="cb-header-info">
        <div id="cb-header-name">Support Team</div>
        <div id="cb-header-status"><span id="cb-status-dot"></span> <span id="cb-status-text">Online — We reply instantly</span></div>
      </div>
      <button id="cb-close-btn">&#10005;</button>
    </div>
    <div id="cb-offline-banner">😴 No agents online right now. Leave a message and we'll reply soon!</div>
    <div id="cb-load-more" style="display:none;">
      <button id="cb-load-more-btn">Load earlier messages</button>
    </div>
    <div id="cb-messages-wrap">
      <div id="cb-messages"></div>
      <div id="cb-agent-typing" style="display:none; padding: 6px 12px 4px 12px;">
        <div style="display:flex; align-items:center; gap:6px;">
          <div style="display:flex; gap:3px; align-items:center;">
            <span style="width:6px;height:6px;border-radius:50%;background:#94a3b8;display:inline-block;animation:cb-bounce 1s infinite;"></span>
            <span style="width:6px;height:6px;border-radius:50%;background:#94a3b8;display:inline-block;animation:cb-bounce 1s infinite 0.2s;"></span>
            <span style="width:6px;height:6px;border-radius:50%;background:#94a3b8;display:inline-block;animation:cb-bounce 1s infinite 0.4s;"></span>
          </div>
          <span style="font-size:11px;color:#94a3b8;font-style:italic;">Agent is typing...</span>
        </div>
      </div>
    </div>
    <div id="cb-input-area">
      <button id="cb-upload-btn" style="border:none;background:none;font-size:18px;cursor:pointer;">📎</button>
      <input type="file" id="cb-file" accept="image/*" style="display:none;">
      <input id="cb-input" type="text" placeholder="Type your message..." />
      <button id="cb-send">
        <svg viewBox="0 0 24 24" style="width:16px;height:16px;fill:white;">
          <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
        </svg>
      </button>
    </div>
    <div id="cb-footer">Powered by ChatBot SaaS</div>
    <div id="cb-resize-handle">
      <svg viewBox="0 0 10 10" fill="none">
        <path d="M1 9L9 1M5 9L9 5M9 9" stroke="#64748b" stroke-width="1.5" stroke-linecap="round"/>
      </svg>
    </div>
  `;
  document.body.appendChild(box);

  // Page load pe settings fetch
  fetch(baseUrl + '/api/widget/settings?token=' + token)
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (!data.error) {
        applyWidgetSettings(data);
        if (data.greeting) proactiveGreetingText = data.greeting;
      }
    })
    .catch(function(){});

  var msgs = document.getElementById('cb-messages');

  // ── PROACTIVE CHAT TRIGGER ──────────────────────────────────
  // Visitor 30s tak page pe rehne ke baad, agar usne chat start nahi ki,
  // ek chhota greeting popup dikhta hai jo click karne par widget khol deta hai.
  var PROACTIVE_DELAY_MS = 30000;
  var proactiveTimer = null;
  var proactivePopupEl = null;
  var proactiveGreetingText = 'Need any help? 👋';

  function cancelProactiveTrigger() {
    if (proactiveTimer) { clearTimeout(proactiveTimer); proactiveTimer = null; }
    hideProactivePopup();
  }

  function scheduleProactiveTrigger() {
    if (sessionStorage.getItem('cb_proactive_shown_' + token)) return;
    if (conversationId && conversationId !== 'undefined' && conversationId !== 'null') return;
    if (proactiveTimer) clearTimeout(proactiveTimer);
    proactiveTimer = setTimeout(showProactivePopup, PROACTIVE_DELAY_MS);
  }

  function showProactivePopup() {
    proactiveTimer = null;
    if (box.classList.contains('open')) return;
    if (conversationId && conversationId !== 'undefined' && conversationId !== 'null') return;
    if (sessionStorage.getItem('cb_proactive_shown_' + token)) return;
    sessionStorage.setItem('cb_proactive_shown_' + token, '1');

    proactivePopupEl = document.createElement('div');
    proactivePopupEl.id = 'cb-proactive-popup';
    // Match the launcher's side (left/right) if it was repositioned.
    if (bubble.style.left && bubble.style.left !== 'auto') {
      proactivePopupEl.style.left = bubble.style.left;
      proactivePopupEl.style.right = 'auto';
    }
    proactivePopupEl.innerHTML = `
      <button type="button" id="cb-proactive-close" aria-label="Dismiss">&times;</button>
      <div id="cb-proactive-text">${escHtml(proactiveGreetingText)}</div>`;
    document.body.appendChild(proactivePopupEl);
    requestAnimationFrame(function() {
      if (proactivePopupEl) proactivePopupEl.classList.add('cb-show');
    });

    proactivePopupEl.addEventListener('click', function(e) {
      if (e.target && e.target.id === 'cb-proactive-close') {
        e.stopPropagation();
        hideProactivePopup();
        return;
      }
      hideProactivePopup();
      bubble.click();
    });

    setTimeout(hideProactivePopup, 12000);
  }

  function hideProactivePopup() {
    if (!proactivePopupEl) return;
    var el = proactivePopupEl;
    proactivePopupEl = null;
    el.classList.remove('cb-show');
    setTimeout(function() { if (el.parentNode) el.parentNode.removeChild(el); }, 250);
  }

  // ── UNREAD BADGE HELPERS ─────────────────────────────────────
  function updateBadge() {
    var b = document.getElementById('cb-badge');
    if (!b) return;
    if (unreadCount > 0 && !box.classList.contains('open')) {
      b.textContent = unreadCount > 9 ? '9+' : String(unreadCount);
      b.style.display = 'block';
    } else {
      b.style.display = 'none';
    }
  }

  // Mark everything currently loaded as seen and clear the badge
  function markAllSeen() {
    if (lastMessageId > lastSeenId) lastSeenId = lastMessageId;
    try { sessionStorage.setItem('cb_lastseen_' + token, String(lastSeenId)); } catch (e) {}
    unreadCount = 0;
    updateBadge();
  }

  // Recount unread agent messages from the server (authoritative)
  function refreshUnreadBadge() {
    if (!conversationId || conversationId === 'undefined' || conversationId === 'null') return;
    if (isResolved) return;
    fetch(baseUrl + '/api/widget/messages/' + conversationId)
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (!data || data.error) return;
        if (data.status === 'resolved') { isResolved = true; stopBgPoll(); return; }
        var list = data.messages || [];
        var count = 0;
        list.forEach(function(m) {
          if (m.sender_type === 'agent' && m.id > lastSeenId) count++;
        });
        unreadCount = count;
        updateBadge();
      })
      .catch(function() {});
  }

  // Background poll runs while the widget is CLOSED so the badge stays live
  function startBgPoll() {
    stopBgPoll();
    bgPollInterval = setInterval(function() {
      if (!conversationId || conversationId === 'undefined') return;
      if (box.classList.contains('open') || isResolved) return;
      refreshUnreadBadge();
    }, 5000);
  }
  function stopBgPoll() {
    if (bgPollInterval) { clearInterval(bgPollInterval); bgPollInterval = null; }
  }

  // On page load: if a conversation already exists, start listening in the
  // background so agent replies bump the icon badge even before the widget opens.
  (function initUnreadBackground() {
    if (!conversationId || conversationId === 'undefined' || conversationId === 'null') return;
    var hadStored = !!sessionStorage.getItem('cb_lastseen_' + token);
    fetch(baseUrl + '/api/widget/messages/' + conversationId)
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (!data || data.error) return;
        if (data.status === 'resolved') { isResolved = true; return; }
        var list = data.messages || [];
        var maxId = 0;
        list.forEach(function(m) { if (m.id > maxId) maxId = m.id; });
        // First time this session: treat existing history as already seen.
        if (!hadStored) {
          lastSeenId = maxId;
          try { sessionStorage.setItem('cb_lastseen_' + token, String(lastSeenId)); } catch (e) {}
        }
        refreshUnreadBadge();
        startBgPoll();
        initPusher();
      })
      .catch(function() {});
  })();

  // ── FEATURE 1: Show visitor info form ────────────────────────
  function showVisitorInfoForm() {
    var wrap = document.getElementById('cb-messages-wrap');
    var inputArea = document.getElementById('cb-input-area');
    var loadMore = document.getElementById('cb-load-more');

    if (loadMore) loadMore.style.display = 'none';
    if (inputArea) inputArea.style.display = 'none';
    msgs.style.display = 'none';

    // Remove existing form if any
    var existing = document.getElementById('cb-visitor-form');
    if (existing) existing.remove();

    var form = document.createElement('div');
    form.id = 'cb-visitor-form';
    form.innerHTML = `
      <h3>👋 Before we start...</h3>
      <p>Please share your details so our team can assist you better.</p>
      <div class="cb-form-err" id="cb-form-err">Please fill in all fields correctly.</div>
      <input type="text" id="cb-vname" placeholder="Your Name" autocomplete="name" />
      <input type="email" id="cb-vemail" placeholder="Your Email" autocomplete="email" />
      <button id="cb-vsubmit">Start Chat →</button>
    `;
    wrap.insertBefore(form, wrap.firstChild);

    document.getElementById('cb-vsubmit').addEventListener('click', submitVisitorInfo);
    document.getElementById('cb-vemail').addEventListener('keydown', function(e) {
      if (e.key === 'Enter') submitVisitorInfo();
    });
    document.getElementById('cb-vname').addEventListener('keydown', function(e) {
      if (e.key === 'Enter') document.getElementById('cb-vemail').focus();
    });
  }

  function submitVisitorInfo() {
    var name  = (document.getElementById('cb-vname').value || '').trim();
    var email = (document.getElementById('cb-vemail').value || '').trim();
    var errEl = document.getElementById('cb-form-err');

    if (!name || !email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      if (errEl) errEl.style.display = 'block';
      return;
    }
    if (errEl) errEl.style.display = 'none';

    var btn = document.getElementById('cb-vsubmit');
    if (btn) { btn.disabled = true; btn.textContent = 'Please wait...'; }

    // If conversation already exists, update info
    if (conversationId && conversationId !== 'undefined' && conversationId !== 'null') {
      fetch(baseUrl + '/api/widget/visitor-info', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ conversation_id: conversationId, name: name, email: email })
      })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        visitorInfoCollected = true;
        hideVisitorForm();
        loadAndShowChat();
      })
      .catch(function() {
        if (btn) { btn.disabled = false; btn.textContent = 'Start Chat →'; }
      });
    } else {
      // Start conversation with name/email directly
      startConversationWithInfo(name, email);
    }
  }

  function hideVisitorForm() {
    var form = document.getElementById('cb-visitor-form');
    if (form) form.remove();
    msgs.style.display = 'flex';
    var inputArea = document.getElementById('cb-input-area');
    if (inputArea) inputArea.style.display = 'flex';
  }

  function loadAndShowChat() {
    fetch(baseUrl + '/api/widget/messages/' + conversationId)
      .then(function(r) { return r.json(); })
      .then(function(data) {
        var list = data.messages || [];
        msgs.innerHTML = '';
        if (list.length === 0) addAgentBubble('Hi! How can we help you?', null);
        else { renderAll(list); updateLoadMoreBtn(list); }
        startPolling(); setupTypingIndicator(); initPusher();
      });
  }

  function startConversationWithInfo(name, email) {
    isResolved = false; lastMessageId = 0;
    currentPage = 1; allPagesLoaded = false;
    teardownPusher();

    var sessionToken = sessionStorage.getItem('cb_session_' + token);
    if (!sessionToken) {
      sessionToken = Math.random().toString(36).substr(2, 32);
      sessionStorage.setItem('cb_session_' + token, sessionToken);
    }

    fetch(baseUrl + '/api/widget/conversation/start', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ token: token, page: window.location.href, session_token: sessionToken, name: name, email: email })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.error) {
        hideVisitorForm();
        if (data.error === 'blocked') {
          showBlockedState();
        } else {
          addAgentBubble('Invalid token. Please check embed code.', null);
        }
        return;
      }
      if (!data.conversation_id) {
        // Malformed response — don't poison sessionStorage with "undefined".
        var btnRetry = document.getElementById('cb-vsubmit');
        if (btnRetry) { btnRetry.disabled = false; btnRetry.textContent = 'Start Chat →'; }
        return;
      }
      conversationId = data.conversation_id;
      sessionStorage.setItem('cb_conversation_' + token, conversationId);
      visitorInfoCollected = true;
      applyWidgetSettings(data);
      if (typeof data.agents_online !== 'undefined') updateAgentStatus(data.agents_online, data.within_business_hours, data.business_hours_summary);
      hideVisitorForm();
      loadAndShowChat();
    })
    .catch(function() {
      var btnRetry = document.getElementById('cb-vsubmit');
      if (btnRetry) { btnRetry.disabled = false; btnRetry.textContent = 'Start Chat →'; }
    });
  }

  // ── AGENTS ONLINE STATUS ─────────────────────────────────────
  function updateAgentStatus(agentsOnline, withinBusinessHours, businessHoursSummary) {
    var dot = document.getElementById('cb-status-dot');
    var txt = document.getElementById('cb-status-text');
    var banner = document.getElementById('cb-offline-banner');

    var isClosed = (withinBusinessHours === false);

    if (isClosed) {
      if (dot) { dot.classList.add('offline'); }
      if (txt) txt.textContent = 'Away — Leave a message';
      if (banner) {
        banner.innerHTML = '🌙 We\'re currently closed' +
          (businessHoursSummary ? ' (' + businessHoursSummary + ')' : '') +
          '. Leave a message and we\'ll get back to you!';
        banner.style.display = 'block';
      }
    } else if (agentsOnline) {
      if (dot) { dot.classList.remove('offline'); }
      if (txt) txt.textContent = 'Online — We reply instantly';
      if (banner) banner.style.display = 'none';
    } else {
      if (dot) { dot.classList.add('offline'); }
      if (txt) txt.textContent = 'Away — Leave a message';
      if (banner) {
        banner.innerHTML = '😴 No agents online right now. Leave a message and we\'ll reply soon!';
        banner.style.display = 'block';
      }
    }
  }

  // ── PUSHER ───────────────────────────────────────────────────
	function initPusher() {

		if (pusherInstance) {
			try {
				pusherInstance.disconnect();
			} catch (e) {}

			pusherInstance = null;
		}

		if (!conversationId || conversationId === 'undefined' || conversationId === 'null') return;

		if (typeof Pusher === 'undefined') {
			setTimeout(initPusher, 500);
			return;
		}

		pusherInstance = new Pusher(PUSHER_KEY, {
			cluster: PUSHER_CLUSTER
		});
    var channel = pusherInstance.subscribe('conversation.' + conversationId);
channel.bind('message.sent', function(data) {

    if (data.sender_type === 'visitor') {
        return;
    }

    if (!data) return;
      var body = data.body || (data.message && data.message.body);
      var senderType = data.sender_type || (data.message && data.message.sender_type);
      var msgId = data.id || (data.message && data.message.id);
      var msgTime = data.created_at || (data.message && data.message.created_at);
      // Widget closed: don't render, just bump the unread badge for agent messages.
      if (!box.classList.contains('open')) {
        if (senderType === 'agent') {
          refreshUnreadBadge();
          playNotifSound();
        }
        return;
      }
      if (senderType === 'system') {
        if (msgId && msgId <= lastMessageId) return;
        if (msgId) lastMessageId = msgId;
        addSystemNote(body); return;
      }
      if (senderType === 'agent') {
        if (msgId && msgId <= lastMessageId) return;
        if (msgId) lastMessageId = msgId;
        addAgentBubble(
			body,
			msgTime || new Date().toISOString(),
			data.agent_name,
			msgId
		);
        playNotifSound();
        markAllSeen();
      }
    });
    channel.bind('message.edited', function(data) {
      if (!data || !data.id) return;
      var wrap = msgs.querySelector('[data-mid="' + data.id + '"]');
      if (!wrap) return;
      var bubble = wrap.querySelector('.cb-bubble-v, .cb-bubble-a');
      if (bubble && !bubble.classList.contains('cb-deleted-bubble')) {
        bubble.innerHTML = escHtml(data.body) + '<span class="cb-edited-tag">(edited)</span>';
      }
    });
    channel.bind('message.deleted', function(data) {
      if (!data || !data.id) return;
      var wrap = msgs.querySelector('[data-mid="' + data.id + '"]');
      if (!wrap) return;
      var bubble = wrap.querySelector('.cb-bubble-v, .cb-bubble-a');
      if (bubble) {
        bubble.classList.add('cb-deleted-bubble');
        bubble.innerHTML = 'Message deleted';
      }
      var actionsEl = wrap.querySelector('.cb-msg-actions');
      if (actionsEl) actionsEl.parentNode.removeChild(actionsEl);
    });
    channel.bind('conversation.resolved', function() {
      if (isResolved) return;
      isResolved = true; stopPolling();
      fetch(baseUrl + '/api/widget/messages/' + conversationId)
        .then(function(r) { return r.json(); })
        .then(function(d) { showResolved((d && d.messages) || [], d && d.rating); })
        .catch(function() { showResolved([]); });
    });
  }

  // ── RESIZE ───────────────────────────────────────────────────
  var resizeHandle = document.getElementById('cb-resize-handle');
  var isResizing = false, resizeStartX, resizeStartY, resizeStartW, resizeStartH;
  resizeHandle.addEventListener('mousedown', function(e) {
    isResizing = true;
    resizeStartX = e.clientX; resizeStartY = e.clientY;
    resizeStartW = box.offsetWidth; resizeStartH = box.offsetHeight;
    e.preventDefault(); e.stopPropagation();
  });
  document.addEventListener('mousemove', function(e) {
    if (!isResizing) return;
    box.style.width  = Math.min(600, Math.max(280, resizeStartW + (resizeStartX - e.clientX))) + 'px';
    box.style.height = Math.min(700, Math.max(380, resizeStartH + (resizeStartY - e.clientY))) + 'px';
  });
  document.addEventListener('mouseup', function() { isResizing = false; });

  // ── DRAG ─────────────────────────────────────────────────────
  var isWidgetDragging = false, widgetDragStartX, widgetDragStartY, widgetStartRight, widgetStartBottom;
  var header = document.getElementById('cb-header');
  header.addEventListener('mousedown', function(e) {
    if (e.target.id === 'cb-close-btn') return;
    isWidgetDragging = true;
    widgetDragStartX = e.clientX; widgetDragStartY = e.clientY;
    var rect = box.getBoundingClientRect();
    widgetStartRight  = window.innerWidth  - rect.right;
    widgetStartBottom = window.innerHeight - rect.bottom;
    e.preventDefault();
  });
  document.addEventListener('mousemove', function(e) {
    if (!isWidgetDragging) return;
    box.style.right  = Math.max(8, Math.min(window.innerWidth  - 100, widgetStartRight  + (widgetDragStartX - e.clientX))) + 'px';
    box.style.bottom = Math.max(8, Math.min(window.innerHeight - 100, widgetStartBottom + (widgetDragStartY - e.clientY))) + 'px';
  });
  document.addEventListener('mouseup', function() { isWidgetDragging = false; });

  // ── OPEN/CLOSE with animation ─────────────────────────────────
  function openWidget() {
    box.style.display = 'flex';
    box.offsetHeight;
    box.classList.remove('cb-anim-out');
    box.classList.add('open', 'cb-anim-in');
    stopBgPoll();
    unreadCount = 0;
    updateBadge();
    cancelProactiveTrigger();
  }
  function closeWidget() {
    box.classList.remove('cb-anim-in');
    box.classList.add('cb-anim-out');
    setTimeout(function() {
      box.classList.remove('open', 'cb-anim-out');
      box.style.display = 'none';
    }, 200);
    stopPolling();
    startBgPoll();
  }

  document.getElementById('cb-close-btn').addEventListener('click', function(e) {
    e.stopPropagation();
    closeWidget();
  });

  // ── POLLING ───────────────────────────────────────────────────
  function startPolling() {
    stopPolling();
    pollInterval = setInterval(function() {
      if (!conversationId || conversationId === 'undefined' || !box.classList.contains('open') || isResolved) return;
      fetchNewMessages();
    }, 3000);
  }
  function stopPolling() {
    if (pollInterval) { clearInterval(pollInterval); pollInterval = null; }
  }

  function fetchNewMessages() {
    fetch(baseUrl + '/api/widget/messages/' + conversationId)
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (!data || data.error) { resetAndStart(); return; }
        if (data.status === 'resolved' && !isResolved) {
          isResolved = true; stopPolling(); showResolved(data.messages, data.rating); return;
        }
        var typingEl = document.getElementById('cb-agent-typing');
        if (typingEl) typingEl.style.display = data.agent_typing ? 'block' : 'none';
        if (typeof data.agents_online !== 'undefined') updateAgentStatus(data.agents_online, data.within_business_hours, data.business_hours_summary);
        appendNewOnly(data.messages || []);
      })
      .catch(function() {});
  }

function appendNewOnly(list) {
  var atBottom = (msgs.scrollHeight - msgs.scrollTop - msgs.clientHeight) < 60;
  var hadNew = false;

  list.forEach(function(m) {

 if (m.id <= lastMessageId) {
    return;
}

lastMessageId = Math.max(lastMessageId, m.id);
    hadNew = true;

    if (m.sender_type === 'system') {

      addSystemNote(m.body);

    } else if (m.sender_type === 'agent') {

      if (m.is_deleted) {

        addAgentBubble('', m.created_at, m.agent_name, m.id, false, true);

      } else if (m.attachment) {

        addAgentBubble(
          '<img src="' + m.attachment + '" style="max-width:220px;border-radius:8px;display:block;">',
          m.created_at,
          m.agent_name,
          m.id, m.is_edited
        );

      } else {

        addAgentBubble(
          m.body,
          m.created_at,
          m.agent_name,
          m.id, m.is_edited
        );

      }

	} else {

		return;

	}

  });

  if (atBottom && hadNew) {
    msgs.scrollTop = msgs.scrollHeight;
  }

  if (hadNew && box.classList.contains('open')) {
    markAllSeen();
  }
}

  // ── BUBBLE CLICK ─────────────────────────────────────────────
  bubble.addEventListener('click', function() {
    unlockAudio();
    if (box.classList.contains('open')) {
      closeWidget();
    } else {
      openWidget();
      if (conversationId && conversationId !== 'undefined') {
        fetch(baseUrl + '/api/widget/messages/' + conversationId)
          .then(function(r) { return r.json(); })
          .then(function(data) {
            if (!data || data.error) { resetAndStart(); return; }
            if (data.status === 'resolved') {
              isResolved = true; stopPolling(); showResolved(data.messages, data.rating); return;
            }
            if (typeof data.agents_online !== 'undefined') updateAgentStatus(data.agents_online, data.within_business_hours, data.business_hours_summary);

            // Feature 1: Check if visitor info collected
            if (!data.visitor_info_collected) {
              showVisitorInfoForm();
              return;
            }

            if (msgs.children.length === 0) renderAll(data.messages);
            else appendNewOnly(data.messages);
            updateLoadMoreBtn(data.messages || []);
            startPolling(); setupTypingIndicator(); initPusher();
            markAllSeen();
          }).catch(function() { resetAndStart(); });
      } else {
        // Feature 1 (fixed): Show form for new visitors WITHOUT creating a conversation yet
        showFormForNewVisitor();
      }
    }
  });

  // Feature 1 (fixed): Only fetch widget settings (greeting/color/title) and show the
  // form. No conversation is created in the database at this point.
  function showFormForNewVisitor() {
    isResolved = false; lastMessageId = 0;
    currentPage = 1; allPagesLoaded = false;
    teardownPusher();
    msgs.innerHTML = '';

    fetch(baseUrl + '/api/widget/settings?token=' + token)
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data.error) {
          addAgentBubble('Invalid token. Please check embed code.', null);
          return;
        }
        applyWidgetSettings(data);
        if (typeof data.agents_online !== 'undefined') updateAgentStatus(data.agents_online, data.within_business_hours, data.business_hours_summary);
        showVisitorInfoForm();
      })
      .catch(function() {
        // Even if settings fetch fails, still show the form so visitor can start chat
        showVisitorInfoForm();
      });
  }

  // NOTE: startConversationThenForm() is kept below but is no longer called on
  // widget-open. It used to create a conversation immediately (before the visitor
  // filled the form), which caused empty conversations to appear automatically.
  // The actual conversation is now created only in startConversationWithInfo()
  // after the visitor submits the form.
  function startConversationThenForm() {
    isResolved = false; lastMessageId = 0;
    currentPage = 1; allPagesLoaded = false;
    teardownPusher();
    msgs.innerHTML = '';

    var sessionToken = sessionStorage.getItem('cb_session_' + token);
    if (!sessionToken) {
      sessionToken = Math.random().toString(36).substr(2, 32);
      sessionStorage.setItem('cb_session_' + token, sessionToken);
    }

    fetch(baseUrl + '/api/widget/conversation/start', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ token: token, page: window.location.href, session_token: sessionToken })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.error) {
        if (data.error === 'blocked') {
          showBlockedState();
        } else {
          addAgentBubble('Invalid token. Please check embed code.', null);
        }
        return;
      }
      if (!data.conversation_id) {
        // Malformed response — don't poison sessionStorage with "undefined".
        return;
      }
      conversationId = data.conversation_id;
      sessionStorage.setItem('cb_conversation_' + token, conversationId);
      applyWidgetSettings(data);
      if (typeof data.agents_online !== 'undefined') updateAgentStatus(data.agents_online, data.within_business_hours, data.business_hours_summary);

      // Show form if info not collected
      if (!data.visitor_info_collected) {
        showVisitorInfoForm();
      } else {
        visitorInfoCollected = true;
        loadAndShowChat();
      }
    })
    .catch(function() {});
  }

  // ── LOAD MORE (PAGINATION) ────────────────────────────────────
  function updateLoadMoreBtn(messages) {
    var btn = document.getElementById('cb-load-more');
    if (btn) btn.style.display = (messages.length >= MESSAGES_PER_PAGE && !allPagesLoaded) ? 'block' : 'none';
  }

  document.getElementById('cb-load-more-btn').addEventListener('click', function() {
    if (isLoadingHistory || allPagesLoaded || !conversationId) return;
    isLoadingHistory = true;
    var btn = document.getElementById('cb-load-more-btn');
    if (btn) btn.textContent = 'Loading...';
    var oldScrollHeight = msgs.scrollHeight;
    fetch(baseUrl + '/api/widget/messages/' + conversationId + '?page=' + (currentPage + 1))
      .then(function(r) { return r.json(); })
      .then(function(data) {
        isLoadingHistory = false;
        var list = data.messages || [];
        if (list.length < MESSAGES_PER_PAGE) {
          allPagesLoaded = true;
          var loadMoreEl = document.getElementById('cb-load-more');
          if (loadMoreEl) loadMoreEl.style.display = 'none';
        } else {
          currentPage++;
          if (btn) btn.textContent = 'Load earlier messages';
        }
        var frag = document.createDocumentFragment();
        list.reverse().forEach(function(m) {
          var el = buildMessageEl(m);
          if (el) frag.insertBefore(el, frag.firstChild);
        });
        msgs.insertBefore(frag, msgs.firstChild);
        msgs.scrollTop = msgs.scrollHeight - oldScrollHeight;
      })
      .catch(function() {
        isLoadingHistory = false;
        if (btn) btn.textContent = 'Load earlier messages';
      });
  });

function buildMessageEl(m) {
  if (m.sender_type === 'system') {
    var note = document.createElement('div');
    note.className = 'cb-system';
    note.textContent = m.body;
    return note;
  }

  var wrap = document.createElement('div');

  if (m.sender_type === 'agent') {

    if (m.id) wrap.setAttribute('data-mid', m.id);

    wrap.innerHTML = `
      <div class="cb-row-a">
        <div class="cb-icon">
          <svg viewBox="0 0 48 48" fill="none" style="width:13px;height:13px;">
            <rect x="6" y="8" width="26" height="20" rx="5" fill="white" opacity="0.9"/>
            <rect x="16" y="18" width="26" height="20" rx="5" fill="white" opacity="0.6"/>
          </svg>
        </div>

        <div>
          ${m.agent_name ? `<div class="cb-name">${m.agent_name}</div>` : ''}

          <div class="cb-bubble-a${m.is_deleted ? ' cb-deleted-bubble' : ''}">
            ${
              m.is_deleted
                ? 'Message deleted'
                : (m.attachment
                    ? '<img src="' + m.attachment + '" style="max-width:220px;border-radius:8px;display:block;">'
                    : escHtml(m.body))
            }${(m.is_edited && !m.is_deleted) ? '<span class="cb-edited-tag">(edited)</span>' : ''}
          </div>
        </div>

      </div>

      ${m.created_at
        ? '<div class="cb-time cb-time-l">' + fmt(m.created_at) + '</div>'
        : ''
      }`;

  } else {

    var vIsImg = !!m.attachment;
    var vCanEditDelete = !!m.id && !vIsImg && !m.is_deleted;
    wrap.className = 'cb-msg-v-wrap';
    if (m.id) wrap.setAttribute('data-mid', m.id);

    renderVisitorBubbleContent(
      wrap,
      m.is_deleted ? '' : (m.attachment ? '<img src="' + m.attachment + '" style="max-width:220px;border-radius:8px;display:block;">' : m.body),
      m.created_at,
      m.visitor_name,
      m.is_edited,
      m.is_deleted,
      vIsImg,
      vCanEditDelete
    );

  }

  return wrap;
}

  function resetAndStart() {
    sessionStorage.removeItem('cb_conversation_' + token);
    conversationId = null; isResolved = false; lastMessageId = 0;
    currentPage = 1; allPagesLoaded = false;
    teardownPusher(); visitorInfoCollected = false;
    lastSeenId = 0; unreadCount = 0;
    try { sessionStorage.removeItem('cb_lastseen_' + token); } catch (e) {}
    stopBgPoll(); updateBadge();
    restoreInput(); showFormForNewVisitor();
  }

  function restoreInput() {
    document.getElementById('cb-input-area').innerHTML = `
      <button id="cb-upload-btn" style="border:none;background:none;font-size:18px;cursor:pointer;">📎</button>
      <input type="file" id="cb-file" accept="image/*" style="display:none;">
      <input id="cb-input" type="text" placeholder="Type your message..." />
      <button id="cb-send">
        <svg viewBox="0 0 24 24" style="width:16px;height:16px;fill:white;">
          <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
        </svg>
      </button>`;
    setTimeout(function(){
      var btn = document.getElementById('cb-upload-btn');
      if(btn){ btn.onclick = function(){ document.getElementById('cb-file').click(); }; }
    },100);
  }

  function setupTypingIndicator() {
    var typingTimer;
    var isTyping = false;
    document.addEventListener('input', function(e) {
      if (e.target.id !== 'cb-input') return;
      if (!conversationId) return;
      if (!isTyping) {
        isTyping = true;
        fetch(baseUrl + '/api/widget/typing', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({ conversation_id: conversationId, typing: true })
        });
      }
      clearTimeout(typingTimer);
      typingTimer = setTimeout(function() {
        isTyping = false;
        fetch(baseUrl + '/api/widget/typing', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({ conversation_id: conversationId, typing: false })
        });
      }, 1500);
    });
  }

  function startConversation() {
    showFormForNewVisitor();
  }

  function applyWidgetSettings(data) {
    if (data.color) {
      var color = data.color;
      var b = document.getElementById('cb-bubble');
      if (b) b.style.background = 'linear-gradient(135deg,' + color + ',' + color + 'cc)';
      var h = document.getElementById('cb-header');
      if (h) h.style.background = 'linear-gradient(135deg,' + color + ',' + color + 'cc)';
      var s = document.getElementById('cb-send');
      if (s) s.style.background = 'linear-gradient(135deg,' + color + ',' + color + 'cc)';
    }
    if (data.title) {
      var t = document.getElementById('cb-header-name');
      if (t) t.textContent = data.title;
    }
    if (data.position) {
      var bx = document.getElementById('cb-box');
      var bb = document.getElementById('cb-bubble');
      if (data.position === 'bottom-left') {
        if (bx) { bx.style.right = 'auto'; bx.style.left = '24px'; }
        if (bb) { bb.style.right = 'auto'; bb.style.left = '24px'; }
      } else {
        if (bx) { bx.style.left = 'auto'; bx.style.right = '24px'; }
        if (bb) { bb.style.left = 'auto'; bb.style.right = '24px'; }
      }
    }
    if (typeof data.hide_branding !== 'undefined') {
      var footer = document.getElementById('cb-footer');
      if (footer) footer.style.display = data.hide_branding ? 'none' : '';
    }
  }

  function showBlockedState() {
    isResolved = true;
    try { stopPolling(); } catch (e) {}
    try { stopBgPoll(); } catch (e) {}
    var area = document.getElementById('cb-input-area');
    if (area) {
      area.innerHTML = '<div class="cb-blocked-msg">🚫 You have been blocked from this chat.</div>';
    }
    if (msgs) {
      var s = document.createElement('div');
      s.className = 'cb-system';
      s.textContent = '🚫 You have been blocked from this chat.';
      msgs.appendChild(s);
      msgs.scrollTop = msgs.scrollHeight;
    }
  }

  function fmt(d) {
    if (!d) return '';
    return new Date(d).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  }

function renderAll(list) {
  msgs.innerHTML = '';
  lastMessageId = 0;

  if (!list || list.length === 0) {
    addAgentBubble('Hi! How can we help you?', null);
    return;
  }

  list.forEach(function(m) {

    lastMessageId = Math.max(lastMessageId, m.id);

    if (m.sender_type === 'system') {

      addSystemNote(m.body);

    } else if (m.sender_type === 'agent') {

      if (m.is_deleted) {

        addAgentBubble('', m.created_at, m.agent_name, m.id, false, true);

      } else if (m.attachment) {

        addAgentBubble(
          '<img src="' + m.attachment + '" style="max-width:220px;border-radius:8px;display:block;">',
          m.created_at,
          m.agent_name,
          m.id, m.is_edited
        );

      } else if (m.body && m.body !== '[IMAGE]') {

        addAgentBubble(
          m.body,
          m.created_at,
          m.agent_name,
          m.id, m.is_edited
        );

      }

    } else {

      if (m.is_deleted) {

        addVisitorBubble('', m.created_at, m.visitor_name, m.id, false, true);

      } else if (m.attachment) {

        addVisitorBubble(
          '<img src="' + m.attachment + '" style="max-width:220px;border-radius:8px;display:block;">',
          m.created_at,
          m.visitor_name,
          m.id, m.is_edited, false, true
        );

      } else if (m.body && m.body !== '[IMAGE]') {

        addVisitorBubble(
          m.body,
          m.created_at,
          m.visitor_name,
          m.id, m.is_edited, false
        );

      }

    }

  });

  msgs.scrollTop = msgs.scrollHeight;
}
function addAgentBubble(text, time, name, id, isEdited, isDeleted) {
  var isImg = typeof text === 'string' && /^\s*<img\s/i.test(text);
  var deleted = !!isDeleted;
  var safeText = deleted ? 'Message deleted' : (isImg ? text : escHtml(text));
  var wrap = document.createElement('div');
  if (id) wrap.setAttribute('data-mid', id);
  wrap.innerHTML = `
    <div class="cb-row-a">
      <div class="cb-icon">
        <svg viewBox="0 0 48 48" fill="none" style="width:13px;height:13px;">
          <rect x="6" y="8" width="26" height="20" rx="5" fill="white" opacity="0.9"/>
          <rect x="16" y="18" width="26" height="20" rx="5" fill="white" opacity="0.6"/>
        </svg>
      </div>

      <div>
       ${name ? `<div class="cb-name">${escHtml(name)}</div>` : ''}
        <div class="cb-bubble-a${deleted ? ' cb-deleted-bubble' : ''}">
          ${safeText}${(isEdited && !deleted) ? '<span class="cb-edited-tag">(edited)</span>' : ''}
        </div>
      </div>
    </div>

    ${time ? '<div class="cb-time cb-time-l">' + fmt(time) + '</div>' : ''}
  `;

  msgs.appendChild(wrap);
  msgs.scrollTop = msgs.scrollHeight;
}


function addVisitorBubble(text, time, name, id, isEdited, isDeleted, isImgMsg) {
  var isImg = isImgMsg || (typeof text === 'string' && /^\s*<img\s/i.test(text));
  var canEditDelete = !!id && !isImg && !isDeleted;
  var wrap = document.createElement('div');
  wrap.className = 'cb-msg-v-wrap';
  if (id) wrap.setAttribute('data-mid', id);

  renderVisitorBubbleContent(wrap, text, time, name, isEdited, isDeleted, isImg, canEditDelete);

  msgs.appendChild(wrap);
  msgs.scrollTop = msgs.scrollHeight;
  return wrap;
}

function renderVisitorBubbleContent(wrap, text, time, name, isEdited, isDeleted, isImg, canEditDelete) {
  var safeText = isDeleted ? 'Message deleted' : (isImg ? text : escHtml(text));
  var bubbleClass = isDeleted ? 'cb-bubble-v cb-deleted-bubble' : 'cb-bubble-v';
  wrap.innerHTML = `
    <div class="cb-name cb-name-r">${escHtml(name || 'You')}</div>
    <div class="cb-row-v">
      <div class="${bubbleClass}">${safeText}${(isEdited && !isDeleted) ? '<span class="cb-edited-tag">(edited)</span>' : ''}</div>
    </div>
    ${!isDeleted && canEditDelete ? `
    <div class="cb-msg-actions">
      <button type="button" class="cb-msg-act-btn cb-msg-edit" title="Edit">✎ Edit</button>
      <button type="button" class="cb-msg-act-btn cb-msg-del" title="Delete">🗑 Delete</button>
    </div>` : ''}
		${time ? '<div class="cb-time cb-time-r">' + fmt(time) + '</div>' : ''}`;
}

  function escHtml(t) {
    return String(t).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function addSystemNote(text) {
    var note = document.createElement('div');
    note.className = 'cb-system';
    note.textContent = text;
    msgs.appendChild(note);
    msgs.scrollTop = msgs.scrollHeight;
  }

  function showResolved(messages, existingRating) {
    // Remember which conversation this was, since resetAndStart()/the CSAT
    // submit both need the id after we clear it from sessionStorage below.
    var ratedConversationId = conversationId;
    sessionStorage.removeItem('cb_conversation_' + token);
    try { sessionStorage.removeItem('cb_lastseen_' + token); } catch (e) {}
    stopBgPoll(); unreadCount = 0; updateBadge();
    renderAll(messages);
    var s = document.createElement('div');
    s.className = 'cb-system'; s.textContent = '✓ This chat has ended';
    msgs.appendChild(s); msgs.scrollTop = msgs.scrollHeight;

    if (existingRating) {
      showEndedFooter();
    } else {
      showRatingPrompt(ratedConversationId);
    }
  }

  // CSAT: 1-5 star rating + optional feedback, shown once per resolved chat
  function showRatingPrompt(ratedConversationId) {
    var area = document.getElementById('cb-input-area');
    area.innerHTML = `
      <div id="cb-rating-box">
        <div class="cb-rating-title">How was your chat experience?</div>
        <div id="cb-stars">
          ${[1,2,3,4,5].map(function(n) {
            return '<span class="cb-star" data-val="' + n + '">★</span>';
          }).join('')}
        </div>
        <textarea id="cb-rating-feedback" placeholder="Any comments? (optional)" style="display:none;"></textarea>
        <button id="cb-rating-submit" disabled>Submit Rating</button>
        <button id="cb-rating-skip">Skip</button>
      </div>`;

    var selected = 0;
    var stars = area.querySelectorAll('.cb-star');
    var submitBtn = document.getElementById('cb-rating-submit');
    var feedbackEl = document.getElementById('cb-rating-feedback');

    function paintStars(upTo) {
      stars.forEach(function(star) {
        var v = parseInt(star.getAttribute('data-val'), 10);
        star.style.color = v <= upTo ? '#f59e0b' : '#cbd5e1';
      });
    }

    stars.forEach(function(star) {
      star.addEventListener('mouseenter', function() {
        paintStars(parseInt(star.getAttribute('data-val'), 10));
      });
      star.addEventListener('click', function() {
        selected = parseInt(star.getAttribute('data-val'), 10);
        paintStars(selected);
        feedbackEl.style.display = 'block';
        submitBtn.disabled = false;
      });
    });
    area.querySelector('#cb-stars').addEventListener('mouseleave', function() {
      paintStars(selected);
    });

    submitBtn.addEventListener('click', function() {
      if (!selected || !ratedConversationId) return;
      submitBtn.disabled = true; submitBtn.textContent = 'Submitting...';
      fetch(baseUrl + '/api/widget/rating', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({
          conversation_id: ratedConversationId,
          rating: selected,
          feedback: (feedbackEl.value || '').trim()
        })
      })
      .then(function(r) {
        return r.json().catch(function() { return {}; }).then(function(data) {
          return { ok: r.ok, status: r.status, data: data };
        });
      })
      .then(function(res) {
        if (!res.ok || !res.data || res.data.success !== true) {
          console.error('CB: rating submit failed', res.status, res.data);
          submitBtn.disabled = false; submitBtn.textContent = 'Submit Rating';
          var box = document.getElementById('cb-rating-box');
          var err = document.getElementById('cb-rating-err');
          if (!err && box) {
            err = document.createElement('div');
            err.id = 'cb-rating-err';
            err.style.cssText = 'color:#dc2626;font-size:11px;text-align:center;margin-top:6px;';
            box.appendChild(err);
          }
          if (err) err.textContent = 'Could not submit rating (error ' + res.status + '). Please try again.';
          return;
        }
        showEndedFooter('Thanks for your feedback! 🙌');
      })
      .catch(function(e) {
        console.error('CB: rating network error', e);
        submitBtn.disabled = false; submitBtn.textContent = 'Submit Rating';
      });
    });

    document.getElementById('cb-rating-skip').addEventListener('click', function() {
      showEndedFooter();
    });
  }

  function showEndedFooter(thanksMsg) {
    var area = document.getElementById('cb-input-area');
    area.innerHTML = `
      <div style="width:100%;padding:4px 0;">
        ${thanksMsg ? '<div style="color:#16a34a;font-size:12px;text-align:center;margin-bottom:6px;">' + thanksMsg + '</div>' : ''}
        <div style="color:#64748b;font-size:12px;text-align:center;margin-bottom:6px;">Need more help?</div>
        <button id="cb-new-chat-btn">Start New Conversation</button>
      </div>`;
    document.getElementById('cb-new-chat-btn').addEventListener('click', resetAndStart);
  }

  // Send message
  document.addEventListener('click', function(e) {
    if (e.target.id === 'cb-send' || (e.target.closest && e.target.closest('#cb-send'))) sendMessage();
  });
  document.addEventListener('keydown', function(e) {
    var inp = document.getElementById('cb-input');
    if (e.key === 'Enter' && inp && inp === document.activeElement) sendMessage();
  });

 function sendMessage() {

    if (sendingMessage) return;

    if (isResolved) return;
    var input = document.getElementById('cb-input');
    if (!input) return;
    var text = input.value.trim();
    if (!text || !conversationId || conversationId === 'undefined') return;
    input.value = '';
    var newBubbleWrap = addVisitorBubble(text, new Date().toISOString());
    playNotifSound();
sendingMessage = true;

fetch(baseUrl + '/api/widget/message/send', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    body: JSON.stringify({
        conversation_id: conversationId,
        message: text
    })
})
.then(function(r){
    return r.json();
})
.then(function(data){

    sendingMessage = false;

    if (data.error === 'resolved') {
        isResolved = true;
        stopPolling();
        showResolved([]);
    }

    if (data.error === 'blocked') {
        showBlockedState();
    }

    if (data.message_id) {
        lastMessageId = Math.max(lastMessageId, data.message_id);
        if (newBubbleWrap && newBubbleWrap.parentNode) {
            newBubbleWrap.setAttribute('data-mid', data.message_id);
            var actionsWrap = newBubbleWrap.querySelector('.cb-msg-actions');
            if (!actionsWrap) {
                var rowV = newBubbleWrap.querySelector('.cb-row-v');
                if (rowV) {
                    var actDiv = document.createElement('div');
                    actDiv.className = 'cb-msg-actions';
                    actDiv.innerHTML = '<button type="button" class="cb-msg-act-btn cb-msg-edit" title="Edit">✎ Edit</button>' +
                        '<button type="button" class="cb-msg-act-btn cb-msg-del" title="Delete">🗑 Delete</button>';
                    rowV.parentNode.insertBefore(actDiv, rowV.nextSibling);
                }
            }
        }
    }

})
.catch(function(){

    sendingMessage = false;

});
}
  // ── WIDGET MESSAGE EDIT / DELETE ────────────────────────────
  function showMsgActionError(wrap, text) {
    var old = wrap.querySelector('.cb-msg-error-toast');
    if (old) old.parentNode.removeChild(old);
    var toast = document.createElement('div');
    toast.className = 'cb-msg-error-toast';
    toast.textContent = text;
    wrap.appendChild(toast);
    setTimeout(function() {
      if (toast.parentNode) toast.parentNode.removeChild(toast);
    }, 3500);
  }

  msgs.addEventListener('click', function(e) {
    var editBtn = e.target.closest && e.target.closest('.cb-msg-edit');
    var delBtn  = e.target.closest && e.target.closest('.cb-msg-del');
    if (!editBtn && !delBtn) return;

    var wrap = e.target.closest('.cb-msg-v-wrap');
    if (!wrap) return;
    var mid = wrap.getAttribute('data-mid');
    if (!mid) return;

    if (editBtn) startEditMessage(wrap, mid);
    else if (delBtn) confirmDeleteMessage(wrap, mid);
  });

  function startEditMessage(wrap, mid) {
    if (isResolved || wrap.classList.contains('cb-editing')) return;
    var bubble = wrap.querySelector('.cb-bubble-v');
    if (!bubble) return;
    var currentText = bubble.textContent.replace(/\(edited\)\s*$/, '').trim();
    wrap.classList.add('cb-editing');
    var rowV = wrap.querySelector('.cb-row-v');
    var actions = wrap.querySelector('.cb-msg-actions');
    var timeEl = wrap.querySelector('.cb-time');
    rowV.style.display = 'none';
    if (actions) actions.style.display = 'none';

    var editBox = document.createElement('div');
    editBox.className = 'cb-msg-edit-box';
    editBox.innerHTML = `
      <textarea class="cb-msg-edit-input" rows="2">${currentText}</textarea>
      <div class="cb-msg-edit-btns">
        <button type="button" class="cb-msg-edit-cancel">Cancel</button>
        <button type="button" class="cb-msg-edit-save">Save</button>
      </div>`;
    rowV.parentNode.insertBefore(editBox, rowV);

    var textarea = editBox.querySelector('.cb-msg-edit-input');
    textarea.focus();
    textarea.setSelectionRange(textarea.value.length, textarea.value.length);

    function cleanup() {
      editBox.parentNode.removeChild(editBox);
      rowV.style.display = '';
      if (actions) actions.style.display = '';
      wrap.classList.remove('cb-editing');
    }

    editBox.querySelector('.cb-msg-edit-cancel').addEventListener('click', cleanup);
    editBox.querySelector('.cb-msg-edit-save').addEventListener('click', function() {
      var newText = textarea.value.trim();
      if (!newText || newText === currentText) { cleanup(); return; }
      fetch(baseUrl + '/api/widget/message/edit', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ conversation_id: conversationId, message_id: mid, message: newText })
      })
      .then(function(r) {
        return r.json().catch(function(){ return null; }).then(function(d) { return { ok: r.ok, data: d }; });
      })
      .then(function(res) {
        var data = res.data;
        // Only trust a well-formed success payload — anything else (route
        // not found, validation error, expired edit window, network hiccup)
        // must fall back to reverting the edit box instead of rendering
        // whatever partial shape Laravel/PHP happened to return.
        if (!res.ok || !data || data.error || data.success !== true ||
            !data.message || typeof data.message.body !== 'string') {
          cleanup();
          return;
        }
        bubble.innerHTML = escHtml(data.message.body) + '<span class="cb-edited-tag">(edited)</span>';
        cleanup();
      })
      .catch(function() { cleanup(); });
    });

    textarea.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); editBox.querySelector('.cb-msg-edit-save').click(); }
      if (e.key === 'Escape') cleanup();
    });
  }

  function confirmDeleteMessage(wrap, mid) {
    if (isResolved) return;
    if (!window.confirm('Delete this message?')) return;
    fetch(baseUrl + '/api/widget/message/delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ conversation_id: conversationId, message_id: mid })
    })
    .then(function(r) {
      return r.json().catch(function(){ return null; }).then(function(d) { return { ok: r.ok, data: d }; });
    })
    .then(function(res) {
      var data = res.data;
      if (!res.ok || !data || data.error || data.success !== true) return;
      var rowV = wrap.querySelector('.cb-row-v');
      if (rowV) rowV.innerHTML = '<div class="cb-bubble-v cb-deleted-bubble">Message deleted</div>';
      var actionsEl = wrap.querySelector('.cb-msg-actions');
      if (actionsEl) actionsEl.parentNode.removeChild(actionsEl);
    })
    .catch(function() {});
  }

document.addEventListener('click', function(e){

    if(e.target.id !== 'cb-upload-btn') return;

    e.preventDefault();
    e.stopPropagation();

    var input = document.getElementById('cb-file');

    input.value = '';

    input.click();

});

  document.addEventListener('change', function(e){

    if(e.target.id !== 'cb-file') return;

    var input = e.target;
    var file = input.files[0];

    if(!file || !conversationId) return;

    var fd = new FormData();
    fd.append('conversation_id', conversationId);
    fd.append('file', file);

    fetch(baseUrl + '/api/widget/upload', {
        method: 'POST',
        body: fd
    })
    .then(function(r){
        return r.json();
    })
    .then(function(data){

        // Reset file input
        input.value = '';

        if(data.url){
            fetchNewMessages();
        }

    })
    .catch(function(){

        // Reset even if upload fails
        input.value = '';

    });

});

  document.addEventListener('paste', function(e){
    if(!conversationId) return;
    var items = e.clipboardData.items;
    for(var i=0;i<items.length;i++){
      if(items[i].type.indexOf('image') !== -1){
        var file = items[i].getAsFile();
        var fd = new FormData();
        fd.append('conversation_id', conversationId);
        fd.append('file', file);
        fetch(baseUrl + '/api/widget/upload', { method:'POST', body:fd })
        .then(function(r){ return r.json(); })
        .then(function(data){ if(data.url){ fetchNewMessages(); } });
        break;
      }
    }
  });

  scheduleProactiveTrigger();

})();