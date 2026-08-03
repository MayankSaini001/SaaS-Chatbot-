@extends('layouts.app')



@section('content')

@php
    $isViewer = auth()->user()->role === 'viewer';
@endphp

<div class="flex flex-col lg:flex-row gap-4 h-auto lg:h-[calc(100vh-132px)]">



    <!-- Chat Window -->

    <div class="w-full flex-1 rounded-2xl flex flex-col overflow-hidden" style="background:linear-gradient(135deg,#f8f7ff 0%,#ffffff 100%);box-shadow:0 8px 32px rgba(99,82,199,0.10),0 1.5px 6px rgba(0,0,0,0.04);border:1px solid #ede9fe;">

        <div class="px-4 sm:px-6 py-4 flex flex-wrap gap-3 justify-between items-center" style="background:linear-gradient(90deg,#667eea 0%,#764ba2 100%);border-radius:16px 16px 0 0;">

            <div class="flex items-center gap-3">
                <div style="width:38px;height:38px;border-radius:50%;background:rgba(255,255,255,0.22);display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;color:#fff;border:2px solid rgba(255,255,255,0.4);">
                    {{ strtoupper(substr($conversation->visitor_name ?? 'V', 0, 1)) }}
                </div>
                <div>
                    <h2 class="font-bold text-white" style="font-size:15px;letter-spacing:0.01em;">{{ $conversation->visitor_name ?? 'Visitor' }}</h2>
                    <span class="text-xs" style="color:rgba(255,255,255,0.75);">{{ $conversation->visitor_page }}</span>
                </div>
            </div>

            <span class="text-xs px-3 py-1 font-semibold"
                style="{{ $conversation->status === 'open'
                    ? 'background:rgba(52,211,153,0.18);color:#d1fae5;border:1px solid rgba(52,211,153,0.35);border-radius:20px;'
                    : 'background:rgba(255,255,255,0.15);color:rgba(255,255,255,0.85);border:1px solid rgba(255,255,255,0.25);border-radius:20px;' }}">
                {{ ucfirst($conversation->status) }}
            </span>

        </div>



        <!-- Messages -->

        <div class="flex-1 overflow-y-auto p-3 sm:p-5 flex flex-col gap-4" id="message-box" style="background:linear-gradient(160deg,#f0eeff 0%,#faf8ff 50%,#eef4ff 100%);">
    @forelse($messages as $message)

        @if($message->sender_type === 'system')

            <div class="flex justify-center">
                <div class="text-xs px-4 py-1.5 text-center" style="background:rgba(139,92,246,0.07);color:#7c3aed;border:1px solid rgba(139,92,246,0.18);border-radius:20px;font-weight:500;letter-spacing:0.01em;">

                    @if(!empty($message->attachment))
                        <a href="{{ $message->attachment }}" target="_blank">
                            <img src="{{ $message->attachment }}"
                                 style="width:100%;max-width:250px;height:auto;border-radius:8px;">
                        </a>
                    @else
                        {{ $message->body }}
                    @endif

                </div>
            </div>

        @else

            {{-- Outer row --}}
            @php
                $isOwnAgentMsg = $message->sender_type === 'agent' && $message->sender_id === auth()->id();
            @endphp
            <div data-mid="{{ $message->id }}" class="{{ $isOwnAgentMsg ? 'own-msg' : '' }}" style="display:flex;{{ $message->sender_type === 'agent' ? 'justify-content:flex-end;' : 'justify-content:flex-start;' }};margin-bottom:6px;">

                {{-- Inner column: shrinks to content, max 60% --}}
                <div style="display:inline-flex;flex-direction:column;max-width:85%;align-items:{{ $message->sender_type === 'agent' ? 'flex-end;' : 'flex-start;' }}">

                    {{-- Sender Name --}}
                    <div style="font-size:11px;font-weight:600;margin-bottom:3px;{{ $message->sender_type === 'agent' ? 'color:#8b5cf6;' : 'color:#6366f1;' }}">
                        @if($message->sender_type === 'visitor')
                            {{ $conversation->visitor_name ?? 'Visitor' }}
                        @elseif($message->sender_type === 'agent')
                            {{ optional($message->sender)->name ?? 'Agent' }}
                        @endif
                    </div>

                    {{-- Bubble --}}
                    <div class="msg-bubble{{ $message->is_deleted ? ' msg-bubble-deleted' : '' }}" style="width:fit-content;max-width:100%;font-size:14px;line-height:1.6;word-break:break-word;overflow-wrap:anywhere;padding:10px 14px 8px 14px;{{ $message->is_deleted ? 'background:transparent;color:#94a3b8;border:1px dashed #cbd5e1;border-radius:16px;font-style:italic;' : ($message->sender_type === 'agent' ? 'background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;border-radius:18px 18px 4px 18px;box-shadow:0 6px 20px rgba(102,126,234,0.32);' : 'background:linear-gradient(135deg,#f0f4ff 0%,#faf5ff 100%);color:#2d2d4e;border-radius:18px 18px 18px 4px;box-shadow:0 2px 12px rgba(139,92,246,0.10);border:1px solid #e8e0ff;') }}">

                        <span class="msg-body">
                        @if($message->is_deleted)
                            Message deleted
                        @elseif(!empty($message->attachment))
                            <a href="{{ $message->attachment }}" target="_blank">
                                <img src="{{ $message->attachment }}" style="width:100%;max-width:200px;height:auto;border-radius:10px;display:block;">
                            </a>
                        @else
                            {{ $message->body }}
                        @endif
                        </span>
                        @if($message->is_edited && !$message->is_deleted)
                            <span class="msg-edited-tag" style="font-size:10px;opacity:0.6;font-style:italic;margin-left:4px;">(edited)</span>
                        @endif

                        <div class="msg-time" style="font-size:10px;margin-top:4px;opacity:0.55;text-align:{{ $message->sender_type === 'agent' ? 'right' : 'left' }};">
                            {{ $message->created_at->format('h:i A') }}
                        </div>

                    </div>

                    @if($isOwnAgentMsg && !$message->is_deleted && !$message->attachment)
                        <div class="msg-actions">
                            <button type="button" class="msg-action-btn msg-edit-btn" title="Edit">✎ Edit</button>
                            <button type="button" class="msg-action-btn msg-del-btn" title="Delete">🗑 Delete</button>
                        </div>
                    @endif

                </div>

            </div>

        @endif

    @empty

        <p class="text-center text-gray-400 text-sm mt-10" id="empty-msg">
            No messages yet.
        </p>

    @endforelse

</div>



        <!-- Visitor Typing Indicator -->

        <div id="visitor-typing" style="display:none;" class="px-4 sm:px-6 pb-2">

            <div style="display:flex; align-items:center; gap:6px;">

                <div style="display:flex; gap:3px; align-items:center;">

                    <span style="width:6px;height:6px;border-radius:50%;background:#94a3b8;display:inline-block;animation:vt-bounce 1s infinite;"></span>

                    <span style="width:6px;height:6px;border-radius:50%;background:#94a3b8;display:inline-block;animation:vt-bounce 1s infinite 0.2s;"></span>

                    <span style="width:6px;height:6px;border-radius:50%;background:#94a3b8;display:inline-block;animation:vt-bounce 1s infinite 0.4s;"></span>

                </div>

                <span style="font-size:11px;color:#94a3b8;font-style:italic;">Visitor is typing...</span>

            </div>

        </div>



        <!-- Reply Box -->

        <div class="p-3 sm:p-4" id="reply-area" style="background:#fff;border-top:1px solid #ede9fe;position:relative;">

            @if($conversation->status === 'open' && !$isViewer)
            <div id="canned-popover" style="display:none;position:absolute;bottom:100%;left:12px;margin-bottom:8px;width:min(340px,90vw);max-height:280px;overflow-y:auto;background:#fff;border:1px solid #ede9fe;border-radius:14px;box-shadow:0 10px 30px rgba(99,82,199,0.18);z-index:20;">
                <div style="padding:10px 14px;border-bottom:1px solid #f1eefe;font-size:12px;font-weight:700;color:#6d28d9;letter-spacing:0.04em;text-transform:uppercase;">
                    ⚡ Canned Responses
                </div>
                <div id="canned-list" style="padding:6px;"></div>
            </div>

            <div class="flex flex-wrap sm:flex-nowrap gap-2 items-center">

				<button type="button"
					id="canned-btn"
					title="Insert a canned response"
					class="px-3 py-2 rounded-xl text-lg"
					style="background:#f5f3ff;border:1px solid #ede9fe;color:#7c3aed;transition:background .15s;">
					⚡
				</button>

				<button type="button"
					id="agent-upload-btn"
					class="px-3 py-2 rounded-xl text-lg"
					style="background:#f5f3ff;border:1px solid #ede9fe;color:#7c3aed;transition:background .15s;">
					📎
				</button>

				<input type="file"
					id="agent-file"
					accept="image/*"
					style="display:none;">

				<input type="text"
					id="agent-reply-input"
					placeholder="Type your reply..."
					autocomplete="off"
					class="flex-1 min-w-0 text-sm focus:outline-none"
					style="border:1px solid #ede9fe;border-radius:14px;padding:10px 16px;background:#f8f7ff;color:#1e1b4b;transition:box-shadow .15s,border-color .15s;"
					onfocus="this.style.boxShadow='0 0 0 3px rgba(139,92,246,0.15)';this.style.borderColor='#a78bfa';"
					onblur="this.style.boxShadow='none';this.style.borderColor='#ede9fe';">

				<button type="button"
						id="agent-send-btn"
						class="w-full sm:w-auto text-white text-sm font-semibold"
						style="min-width:90px;padding:10px 20px;border-radius:14px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);box-shadow:0 4px 12px rgba(102,126,234,0.35);border:none;cursor:pointer;transition:opacity .15s;"
						onmouseover="this.style.opacity='0.88'" onmouseout="this.style.opacity='1'">
						Send ➤
					</button>

			</div>

            @else

            <div class="text-center text-sm py-3 rounded-xl" style="background:#f5f3ff;color:#7c3aed;border:1px solid #ede9fe;font-weight:500;">

                @if($isViewer)
                    👁️ You have read-only (Viewer) access to this conversation.
                @else
                    🔒 This conversation has been resolved. No further replies allowed.
                @endif

            </div>

            @endif

        </div>

    </div>



    <!-- Visitor Info Panel -->

    <div class="w-full lg:w-72 flex flex-col gap-4 lg:h-full lg:overflow-y-auto lg:pr-1" style="scrollbar-width:thin;">

        <!-- Visitor Info -->

        <div class="text-sm rounded-2xl p-5" style="background:#fff;box-shadow:0 4px 20px rgba(99,82,199,0.08);border:1px solid #ede9fe;">

            <h3 class="font-bold mb-4" style="color:#4f46e5;font-size:13px;letter-spacing:0.04em;text-transform:uppercase;">Visitor Info</h3>

            <div class="flex flex-col gap-3">

                <div class="flex flex-col gap-0.5">
                    <span class="text-xs font-semibold" style="color:#a78bfa;letter-spacing:0.05em;text-transform:uppercase;">Name</span>
                    <span style="color:#1e1b4b;font-weight:500;">{{ $conversation->visitor_name ?? '-' }}</span>
                </div>

                <div class="flex flex-col gap-0.5">
                    <span class="text-xs font-semibold" style="color:#a78bfa;letter-spacing:0.05em;text-transform:uppercase;">Email</span>
                    <span style="color:#1e1b4b;font-weight:500;word-break:break-all;">{{ $conversation->visitor_email ?? '-' }}</span>
                </div>

                <div class="flex flex-col gap-0.5">
                    <span class="text-xs font-semibold" style="color:#a78bfa;letter-spacing:0.05em;text-transform:uppercase;">Page</span>
                    <span class="break-all" style="color:#6366f1;font-weight:500;font-size:12px;">{{ $conversation->visitor_page ?? '-' }}</span>
                </div>

                <div class="flex flex-col gap-0.5">
                    <span class="text-xs font-semibold" style="color:#a78bfa;letter-spacing:0.05em;text-transform:uppercase;">IP</span>
                    <span style="color:#1e1b4b;font-weight:500;">{{ $conversation->visitor_ip ?? '-' }}</span>
                </div>

                @if($conversation->visitor_ip)
                <div class="flex flex-col gap-0.5">
                    <span class="text-xs font-semibold" style="color:#a78bfa;letter-spacing:0.05em;text-transform:uppercase;">Access</span>
                    @if($isBlocked)
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold" style="color:#dc2626;">🚫 Blocked</span>
                            @if(!$isViewer)
                            <form method="POST" action="{{ route('agent.conversations.unblock', $conversation) }}" onsubmit="return confirm('Unblock this visitor?');">
                                @csrf
                                <button type="submit" class="text-xs font-semibold" style="color:#4f46e5;text-decoration:underline;background:none;border:none;cursor:pointer;padding:0;">Unblock</button>
                            </form>
                            @endif
                        </div>
                    @else
                        @if(!$isViewer)
                        <form method="POST" action="{{ route('agent.conversations.block', $conversation) }}" onsubmit="return confirm('Block this visitor\'s IP ({{ $conversation->visitor_ip }})? They won\'t be able to use this chat widget again.');">
                            @csrf
                            <button type="submit" class="text-xs font-semibold" style="color:#dc2626;text-decoration:underline;background:none;border:none;cursor:pointer;padding:0;">🚫 Block this visitor</button>
                        </form>
                        @else
                        <span class="text-xs" style="color:#9ca3af;">-</span>
                        @endif
                    @endif
                </div>
                @endif

                <div class="flex flex-col gap-0.5">
                    <span class="text-xs font-semibold" style="color:#a78bfa;letter-spacing:0.05em;text-transform:uppercase;">Status</span>
                    <span class="font-semibold" style="{{ $conversation->status === 'open' ? 'color:#10b981;' : 'color:#6b7280;' }}">
                        {{ ucfirst($conversation->status) }}
                    </span>
                </div>

            </div>

                <div class="flex flex-col gap-0.5 pt-1">
                    <span class="text-xs font-semibold" style="color:#a78bfa;letter-spacing:0.05em;text-transform:uppercase;">Assigned To</span>

                    @if($conversation->agent_id && $conversation->assignedAgent)

                        <span id="assigned-to-name" style="color:#6366f1;font-weight:600;">{{ $conversation->assignedAgent->name }}</span>

                        @if($conversation->agent_id === auth()->id())

                            <span class="text-xs" style="color:#a78bfa;">(You)</span>

                        @endif

                    @else

                        <span id="assigned-to-name" style="color:#d1d5db;font-weight:500;">Unassigned</span>

                    @endif

                </div>

                @if($conversation->status === 'resolved' && $conversation->agent_id && $conversation->assignedAgent)

                <div class="mt-3 p-3 rounded-xl" style="background:linear-gradient(135deg,#ecfdf5,#f0fdf4);border:1px solid #a7f3d0;">

                    <p class="text-xs font-semibold" style="color:#059669;">✓ Resolved by <strong>{{ $conversation->assignedAgent->name }}</strong></p>

                </div>

                @endif

                @if($conversation->rating)

                <div class="mt-3 p-3 rounded-xl" style="background:linear-gradient(135deg,#fffbeb,#fefce8);border:1px solid #fde68a;">

                    <span class="text-xs font-semibold" style="color:#b45309;letter-spacing:0.05em;text-transform:uppercase;">Visitor Rating</span>

                    <div style="font-size:16px;color:#f59e0b;letter-spacing:2px;margin-top:2px;">
                        {{ str_repeat('★', $conversation->rating) }}{{ str_repeat('☆', 5 - $conversation->rating) }}
                        <span style="font-size:12px;color:#92400e;font-weight:600;">({{ $conversation->rating }}/5)</span>
                    </div>

                    @if($conversation->rating_feedback)
                        <p class="text-xs mt-1" style="color:#78350f;">“{{ $conversation->rating_feedback }}”</p>
                    @endif

                </div>

                @endif

        </div>

        <!-- Tags -->
        <div class="rounded-2xl p-5 text-sm" style="background:#fff;box-shadow:0 4px 20px rgba(99,82,199,0.08);border:1px solid #ede9fe;">
            <h3 class="font-bold mb-3" style="color:#4f46e5;font-size:13px;letter-spacing:0.04em;text-transform:uppercase;">🏷️ Tags</h3>

            <div id="tag-pills" class="flex flex-wrap gap-1.5 mb-3">
                @foreach($conversation->tags as $tag)
                <span class="tag-pill" data-tag-id="{{ $tag->id }}"
                      style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:11.5px;font-weight:600;color:#fff;background:{{ $tag->color }};">
                    {{ $tag->name }}
                    @if(!$isViewer)
                    <button type="button" class="tag-remove-btn" data-tag-id="{{ $tag->id }}" style="background:none;border:none;color:#fff;opacity:0.75;cursor:pointer;font-size:11px;line-height:1;padding:0;">✕</button>
                    @endif
                </span>
                @endforeach
                <span id="tag-empty-msg" class="text-xs" style="color:#a78bfa;{{ $conversation->tags->count() ? 'display:none;' : '' }}">No tags yet.</span>
            </div>

            @if(!$isViewer)
            <div style="display:flex;gap:6px;">
                <select id="tag-select" class="flex-1 text-xs focus:outline-none"
                        style="border:1px solid #ede9fe;border-radius:10px;padding:7px 8px;background:#f8f7ff;color:#1e1b4b;">
                    <option value="">+ Add existing tag...</option>
                    @foreach($allTags as $t)
                        @if(!$conversation->tags->contains('id', $t->id))
                        <option value="{{ $t->id }}" data-color="{{ $t->color }}">{{ $t->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div style="display:flex;gap:6px;margin-top:6px;">
                <input type="text" id="tag-new-input" placeholder="Or type a new tag name..."
                       class="flex-1 text-xs focus:outline-none"
                       style="border:1px solid #ede9fe;border-radius:10px;padding:7px 10px;background:#f8f7ff;color:#1e1b4b;">
                <button type="button" id="tag-new-btn"
                        style="background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;border:none;border-radius:10px;padding:0 14px;font-size:12px;font-weight:600;cursor:pointer;">
                    Add
                </button>
            </div>
            @endif
        </div>

        <!-- Assign To -->

		@if(auth()->user()->role === 'owner' && $conversation->status === 'open')

		<div class="rounded-2xl p-5 text-sm" style="background:#fff;box-shadow:0 4px 20px rgba(99,82,199,0.08);border:1px solid #ede9fe;">

			<h3 class="font-bold mb-4" style="color:#4f46e5;font-size:13px;letter-spacing:0.04em;text-transform:uppercase;">Assign To</h3>

			<select id="assign-select"
				class="w-full text-sm mb-3 focus:outline-none"
				style="border:1px solid #ede9fe;border-radius:12px;padding:9px 14px;background:#f8f7ff;color:#1e1b4b;appearance:auto;">

				<option value="">-- Select Agent --</option>

				@foreach($agents as $agent)
				<option value="{{ $agent->id }}"
					{{ $conversation->agent_id == $agent->id ? 'selected' : '' }}>
					{{ $agent->name }}{{ $agent->id === auth()->id() ? ' (You)' : '' }}
				</option>
				@endforeach

			</select>

			<button type="button" id="assign-btn"
				class="w-full text-white text-sm font-semibold py-2 rounded-xl"
				style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);box-shadow:0 4px 12px rgba(102,126,234,0.30);border:none;cursor:pointer;">
				Assign
			</button>

			<div id="assign-msg" class="text-xs mt-2 hidden text-center font-semibold" style="color:#10b981;">
				✓ Assigned!
			</div>

		</div>

		@endif



        <!-- Resolve Button -->

        @if($conversation->status === 'open' && !$isViewer)

        <form method="POST" action="{{ route('agent.conversations.resolve', $conversation) }}">

            @csrf

            <button class="w-full text-white py-2 rounded-xl text-sm font-semibold"
                style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);box-shadow:0 4px 12px rgba(16,185,129,0.25);border:none;cursor:pointer;">

                ✓ Resolve Conversation

            </button>

        </form>

        @else

        <div class="text-center py-2 rounded-xl text-sm font-semibold" style="background:#f0fdf4;color:#059669;border:1px solid #a7f3d0;">

            @if($conversation->status === 'open' && $isViewer)
                👁️ Read-only access
            @else
                ✓ Conversation Resolved
            @endif

        </div>

        @endif



        <!-- Internal Notes (private, never visible to visitor) -->
        <div class="rounded-2xl p-5 text-sm" style="background:#fff;box-shadow:0 4px 20px rgba(99,82,199,0.08);border:1px solid #ede9fe;">
            <h3 class="font-bold mb-3 flex items-center gap-1.5" style="color:#b45309;font-size:13px;letter-spacing:0.04em;text-transform:uppercase;">
                🔒 Internal Notes
            </h3>

            <div id="notes-list" class="flex flex-col gap-2 mb-3" style="max-height:220px;overflow-y:auto;padding-right:6px;">
                @forelse($notes as $note)
                <div class="note-item p-2.5 rounded-xl" data-note-id="{{ $note->id }}" style="background:#fffbeb;border:1px solid #fde68a;">
                    <p class="text-xs" style="color:#78350f;white-space:pre-wrap;">{{ $note->body }}</p>
                    <div class="flex justify-between items-center mt-1.5">
                        <span class="text-xs" style="color:#b45309;">{{ $note->author->name ?? 'Agent' }} · {{ $note->created_at->format('d M, h:i A') }}</span>
                        @if(!$isViewer)
                        <button type="button" class="note-delete-btn" data-note-id="{{ $note->id }}" style="background:none;border:none;color:#b45309;cursor:pointer;font-size:11px;opacity:0.6;">✕</button>
                        @endif
                    </div>
                </div>
                @empty
                <p id="no-notes-msg" class="text-xs text-center py-2" style="color:#a78bfa;">No internal notes yet.</p>
                @endforelse
            </div>

            @if(!$isViewer)
            <textarea id="note-input" rows="2" placeholder="Add a private note for the team..."
                class="w-full text-sm focus:outline-none"
                style="border:1px solid #ede9fe;border-radius:12px;padding:8px 12px;background:#f8f7ff;color:#1e1b4b;resize:none;"></textarea>

            <button type="button" id="note-add-btn"
                class="w-full text-white text-sm font-semibold py-2 rounded-xl mt-2"
                style="background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);box-shadow:0 4px 12px rgba(245,158,11,0.25);border:none;cursor:pointer;">
                Add Note
            </button>
            @endif
        </div>



        <a href="{{ route('agent.conversations') }}"
            class="block text-center py-2 rounded-xl text-sm font-medium"
            style="background:#fff;border:1px solid #ede9fe;color:#6366f1;text-decoration:none;">

            ← Back to Inbox

        </a>

    </div>



</div>



<style>

#notes-list {
    scrollbar-width: thin;
    scrollbar-color: #fbbf24 #fef3c7;
}
#notes-list::-webkit-scrollbar {
    width: 6px;
}
#notes-list::-webkit-scrollbar-track {
    background: #fef3c7;
    border-radius: 10px;
}
#notes-list::-webkit-scrollbar-thumb {
    background: #fbbf24;
    border-radius: 10px;
}
#notes-list::-webkit-scrollbar-thumb:hover {
    background: #f59e0b;
}

@media (max-width:768px){

    #message-box{
        padding:12px !important;
    }

    #reply-area{
        padding:12px !important;
    }

    #agent-send-btn{
        width:100%;
    }

    #agent-upload-btn{
        flex-shrink:0;
        width:48px;
    }

    #agent-reply-input{
        width:100%;
    }

}

@keyframes vt-bounce {

    0%, 60%, 100% { transform: translateY(0); }

    30% { transform: translateY(-4px); }

}

/* ── Agent-side message edit/delete ── */
[data-mid].own-msg { position: relative; }
.msg-actions {
    display: flex; gap: 8px; margin-top: 3px; opacity: 0; height: 0;
    overflow: hidden; transition: opacity 0.15s ease;
}
[data-mid].own-msg:hover .msg-actions,
[data-mid].own-msg.editing .msg-actions { opacity: 1; height: auto; overflow: visible; }
.msg-action-btn {
    background: none; border: none; cursor: pointer; font-size: 11px;
    color: #a78bfa; padding: 1px 2px; line-height: 1;
}
.msg-action-btn:hover { color: #4f46e5; }
.msg-action-btn.msg-del-btn:hover { color: #dc2626; }
.msg-edit-box { display: flex; flex-direction: column; gap: 6px; max-width: 260px; margin-left: auto; }
.msg-edit-textarea {
    width: 100%; box-sizing: border-box; border: 1.5px solid #c7d2fe;
    border-radius: 12px; padding: 7px 10px; font-size: 13px;
    outline: none; resize: none; font-family: inherit; color: #1e293b;
}
.msg-edit-actions { display: flex; justify-content: flex-end; gap: 8px; }
.msg-edit-save-btn, .msg-edit-cancel-btn {
    border: none; background: none; cursor: pointer;
    font-size: 11px; font-weight: 600; padding: 2px 4px;
}
.msg-edit-save-btn { color: #4f46e5; }
.msg-edit-cancel-btn { color: #94a3b8; }

</style>



<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

<script>

var PUSHER_KEY     = '{{ config("broadcasting.connections.pusher.key") ?: "a5278981e9260924a023" }}';
var PUSHER_CLUSTER = '{{ config("broadcasting.connections.pusher.options.cluster") ?: "ap2" }}';
var pusher  = null;
var channel = null;

if (typeof Pusher !== 'undefined' && PUSHER_KEY) {
    try {
        pusher  = new Pusher(PUSHER_KEY, { cluster: PUSHER_CLUSTER });
        channel = pusher.subscribe('conversation.{{ $conversation->id }}');
    } catch(e) {
        console.warn('Pusher init failed:', e);
        pusher = null; channel = null;
    }
}

if (channel) {

channel.bind('message.sent', function(data) {

    if (data.sender_type !== 'visitor') return;

    var typingEl = document.getElementById('visitor-typing');
    if (typingEl) typingEl.style.display = 'none';

    // Use the same renderIncoming() function the 3-second poll uses, so
    // both paths share one dedup check (m.id <= lastMsgId) and both keep
    // lastMsgId in sync. Previously this handler had its own separate
    // rendering code that never updated lastMsgId — so whichever arrived
    // second (Pusher or the poll) would render the same message again,
    // causing every visitor message to appear twice.
    renderIncoming(data);

});

// Visitor edited one of their own messages — update the bubble live,
// wherever it currently sits in the DOM (initial Blade render or one
// appended later by renderIncoming()).
channel.bind('message.edited', function(data) {
    if (!data || !data.id) return;
    var row = document.querySelector('[data-mid="' + data.id + '"]');
    if (!row) return;
    var bodyEl = row.querySelector('.msg-body');
    if (bodyEl) bodyEl.textContent = data.body;
    var bubbleEl = row.querySelector('.msg-bubble');
    if (bubbleEl && !bubbleEl.querySelector('.msg-edited-tag')) {
        var tag = document.createElement('span');
        tag.className = 'msg-edited-tag';
        tag.style.cssText = 'font-size:10px;opacity:0.6;font-style:italic;margin-left:4px;';
        tag.textContent = '(edited)';
        var timeEl = bubbleEl.querySelector('.msg-time');
        if (timeEl) bubbleEl.insertBefore(tag, timeEl);
        else bubbleEl.appendChild(tag);
    }
});

// Visitor deleted one of their own messages — swap the bubble for the
// "Message deleted" placeholder, same as the widget shows.
channel.bind('message.deleted', function(data) {
    if (!data || !data.id) return;
    var row = document.querySelector('[data-mid="' + data.id + '"]');
    if (!row) return;
    var bubbleEl = row.querySelector('.msg-bubble');
    if (!bubbleEl) return;
    bubbleEl.classList.add('msg-bubble-deleted');
    bubbleEl.style.background = 'transparent';
    bubbleEl.style.color = '#94a3b8';
    bubbleEl.style.border = '1px dashed #cbd5e1';
    bubbleEl.style.boxShadow = 'none';
    bubbleEl.style.fontStyle = 'italic';
    var bodyEl = bubbleEl.querySelector('.msg-body');
    if (bodyEl) bodyEl.textContent = 'Message deleted';
    var tagEl = bubbleEl.querySelector('.msg-edited-tag');
    if (tagEl) tagEl.remove();
});



// Visitor typing indicator — Pusher se

channel.bind('visitor.typing', function(data) {

    var el = document.getElementById('visitor-typing');

    if (!el) return;

    el.style.display = data.typing ? 'block' : 'none';

});



// Agent typing indicator — widget mein dikhega

channel.bind('agent.typing', function(data) {

    // Agent typing broadcast ho raha hai

});



// Conversation auto-resolved (inactivity) — lock the panel live

channel.bind('conversation.resolved', function() {

    if (window.__convResolved) return;

    window.__convResolved = true;

    // Turant reply box lock karo — reload ki zarurat nahi
    lockReplyBox();

    // System message dikhao
    var box = document.getElementById('message-box');
    if (box) {
        var note = document.createElement('div');
        note.style.cssText = 'display:flex;justify-content:center;margin:4px 0;';
        var pill = document.createElement('div');
        pill.style.cssText = 'font-size:11px;padding:4px 14px;background:rgba(139,92,246,0.07);color:#7c3aed;border:1px solid rgba(139,92,246,0.18);border-radius:20px;font-weight:500;';
        pill.textContent = '🔒 This conversation was automatically resolved';
        note.appendChild(pill);
        box.appendChild(note);
        box.scrollTop = box.scrollHeight;
    }

});

// Internal Notes: live sync across agents viewing the same conversation
channel.bind('note.added', function(data) {
    if (data.actor_id === currentUserId) return; // already rendered locally by the adder
    if (typeof notesList === 'undefined' || !notesList) return;
    if (notesList.querySelector('.note-item[data-note-id="' + data.id + '"]')) return;
    if (typeof noNotesMsg !== 'undefined' && noNotesMsg) { noNotesMsg.remove(); noNotesMsg = null; }
    notesList.insertBefore(renderNote(data), notesList.firstChild);
});

channel.bind('note.deleted', function(data) {
    if (data.actor_id === currentUserId) return; // already removed locally by the deleter
    if (typeof notesList === 'undefined' || !notesList) return;
    var item = notesList.querySelector('.note-item[data-note-id="' + data.id + '"]');
    if (item) item.remove();
});

} // end if (channel)



var box = document.getElementById('message-box');

if (box) box.scrollTop = box.scrollHeight;

// Agar conversation already resolved hai toh flag set karo
@if($conversation->status === 'resolved')
window.__convResolved = true;
@endif

// Reply box ko live lock karne ka function
function lockReplyBox() {
    var replyArea = document.getElementById('reply-area');
    if (replyArea) {
        replyArea.innerHTML = '<div style="text-align:center;font-size:13px;padding:12px;background:#f5f3ff;color:#7c3aed;border-radius:12px;border:1px solid #ede9fe;font-weight:500;">🔒 This conversation has been resolved. No further replies allowed.</div>';
    }
    // Input aur button bhi disable karo
    var inp = document.getElementById('agent-reply-input');
    var btn = document.getElementById('agent-send-btn');
    var upBtn = document.getElementById('agent-upload-btn');
    if (inp) { inp.disabled = true; inp.placeholder = 'Conversation resolved...'; inp.style.opacity = '0.5'; }
    if (btn) { btn.disabled = true; btn.style.opacity = '0.5'; btn.style.cursor = 'not-allowed'; }
    if (upBtn) { upBtn.disabled = true; upBtn.style.opacity = '0.5'; }
}



var lastMsgId = {{ $messages->last()->id ?? 0 }};

var conversationId = {{ $conversation->id }};

var csrfToken = '{{ csrf_token() }}';

var isSending = false;



// Visitor messages auto-refresh

setInterval(function() {

    fetch(window.location.origin + '/chatbot/api/widget/messages/' + conversationId, {

        headers: { 'Accept': 'application/json' }

    })

    .then(r => r.json())

    .then(function(data) {

        if (!data || data.error) return;



        // Auto-resolve happened (inactivity) -> lock the panel live

        if (data.status === 'resolved' && !window.__convResolved) {

            window.__convResolved = true;

            // Nayi system messages dikhao
            (data.messages || []).forEach(renderIncoming);

            // Reply box lock karo bina reload ke
            lockReplyBox();

            return;

        }

        // Agar already resolved hai toh polling band karo
        if (data.status === 'resolved') return;



        var messages = data.messages || [];



        // Visitor typing update — polling se

        var vTyping = document.getElementById('visitor-typing');

        if (vTyping && data.visitor_typing !== undefined) {

            vTyping.style.display = data.visitor_typing ? 'block' : 'none';

        }



        if (!messages.length) return;



        messages.forEach(renderIncoming);

    });

}, 3000);



// Render a single incoming message (visitor bubble or centered system notice).

// Agent's own messages are skipped here (already shown locally / via Pusher).

function renderIncoming(m) {

    if (m.id <= lastMsgId) return;

    lastMsgId = m.id;

    var empty = document.getElementById('empty-msg');
    if (empty) empty.remove();

    if (m.sender_type === 'agent') return;

    var wrapper = document.createElement('div');

    if (m.sender_type === 'system') {

        wrapper.className = 'flex justify-center';

        var note = document.createElement('div');
        note.style.cssText = 'font-size:11px;padding:4px 14px;background:rgba(139,92,246,0.07);color:#7c3aed;border:1px solid rgba(139,92,246,0.18);border-radius:20px;font-weight:500;';
        note.textContent = m.body;

        wrapper.appendChild(note);

        box.appendChild(wrapper);
        box.scrollTop = box.scrollHeight;

        return;
    }

    wrapper.style.cssText = 'display:flex;justify-content:flex-start;margin-bottom:2px;';
    wrapper.setAttribute('data-mid', m.id);

    var outer = document.createElement('div');
    outer.style.cssText = 'max-width:85%;';

    var sender = document.createElement('div');
    sender.style.cssText = 'font-size:11px;font-weight:600;color:#6366f1;margin-bottom:4px;';
    sender.textContent = m.visitor_name || '{{ $conversation->visitor_name }}';

    var bubble = document.createElement('div');
    bubble.className = 'msg-bubble' + (m.is_deleted ? ' msg-bubble-deleted' : '');
    bubble.style.cssText = m.is_deleted
        ? 'display:table;font-size:14px;line-height:1.55;padding:10px 14px 8px 14px;max-width:100%;background:transparent;color:#94a3b8;border:1px dashed #cbd5e1;border-radius:16px;font-style:italic;'
        : 'display:table;font-size:14px;line-height:1.55;word-break:break-word;overflow-wrap:anywhere;padding:10px 14px 8px 14px;max-width:100%;background:linear-gradient(135deg,#f0f4ff 0%,#faf5ff 100%);color:#2d2d4e;border-radius:18px 18px 18px 4px;box-shadow:0 2px 12px rgba(139,92,246,0.10);border:1px solid #e8e0ff;';

    var body = document.createElement('span');
    body.className = 'msg-body';
    body.style.cssText = 'word-break:break-word;overflow-wrap:anywhere;';

    if (m.is_deleted) {

        body.textContent = 'Message deleted';

    } else if (m.attachment) {

        body.innerHTML =
            '<a href="' + m.attachment + '" target="_blank">' +
            '<img src="' + m.attachment + '" style="width:100%;max-width:200px;height:auto;border-radius:10px;display:block;">' +
            '</a>';

    } else {

        body.textContent = m.body;

    }

    if (m.is_edited && !m.is_deleted) {
        var editedTag = document.createElement('span');
        editedTag.className = 'msg-edited-tag';
        editedTag.style.cssText = 'font-size:10px;opacity:0.6;font-style:italic;margin-left:4px;';
        editedTag.textContent = '(edited)';
        bubble.appendChild(body);
        bubble.appendChild(editedTag);
    } else {
        bubble.appendChild(body);
    }

    var time = document.createElement('div');
    time.className = 'msg-time';
    time.style.cssText = 'font-size:10px;margin-top:5px;opacity:0.55;text-align:left;';
    time.textContent = m.created_at
        ? new Date(m.created_at).toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        })
        : 'Just now';

    bubble.appendChild(time);

    outer.appendChild(sender);
    outer.appendChild(bubble);

    wrapper.appendChild(outer);

    box.appendChild(wrapper);
    box.scrollTop = box.scrollHeight;
}



// Agent reply

function sendAgentReply() {

    if (isSending) return;

    var input = document.getElementById('agent-reply-input');

    var btn = document.getElementById('agent-send-btn');

    if (!input || !btn) return;

    var text = input.value.trim();

    if (!text) return;



    isSending = true;

	btn.disabled = true;

	btn.innerHTML = 'Sending...';

    input.value = '';



    var wrapper = document.createElement('div');
wrapper.className = 'own-msg';
wrapper.style.cssText = 'display:flex;justify-content:flex-end;margin-bottom:2px;';

var outer = document.createElement('div');
outer.style.cssText = 'display:inline-flex;flex-direction:column;max-width:85%;align-items:flex-end;';

var sender = document.createElement('div');
sender.style.cssText = 'font-size:11px;font-weight:600;color:#8b5cf6;margin-bottom:4px;text-align:right;';
sender.textContent = '{{ auth()->user()->name }}';

var bubble = document.createElement('div');
bubble.className = 'msg-bubble';
bubble.style.cssText = 'display:table;font-size:14px;line-height:1.55;word-break:break-word;overflow-wrap:anywhere;padding:10px 14px 8px 14px;max-width:100%;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;border-radius:18px 18px 4px 18px;box-shadow:0 6px 20px rgba(102,126,234,0.35);margin-left:auto;';

var body = document.createElement('span');
body.className = 'msg-body';
body.style.cssText = 'word-break:break-word;overflow-wrap:anywhere;';
body.textContent = text;

    var time = document.createElement('div');
    time.className = 'msg-time';
    time.style.cssText = 'font-size:10px;margin-top:5px;opacity:0.55;text-align:right;';
    time.textContent = 'Just now';

    bubble.appendChild(body);
	bubble.appendChild(time);

	outer.appendChild(sender);
	outer.appendChild(bubble);

	wrapper.appendChild(outer);

	box.appendChild(wrapper);

    box.scrollTop = box.scrollHeight;



    fetch(window.location.origin + '/chatbot/agent/conversations/{{ $conversation->id }}/reply', {

        method: 'POST',

        headers: {

            'Content-Type': 'application/x-www-form-urlencoded',

            'X-CSRF-TOKEN': csrfToken,

            'Accept': 'application/json'

        },

        body: '_token=' + csrfToken + '&message=' + encodeURIComponent(text)

    })

    .then(function(r) { return r.json().catch(function(){ return null; }); })

    .then(function(data) {

        isSending = false;

        btn.disabled = false;

        btn.textContent = 'Send';

        if (data && data.message_id) {
            wrapper.setAttribute('data-mid', data.message_id);
            var actions = document.createElement('div');
            actions.className = 'msg-actions';
            actions.innerHTML = '<button type="button" class="msg-action-btn msg-edit-btn" title="Edit">✎ Edit</button>' +
                '<button type="button" class="msg-action-btn msg-del-btn" title="Delete">🗑 Delete</button>';
            outer.appendChild(actions);
        }

    })

    .catch(function() {

        isSending = false;

        btn.disabled = false;

        btn.textContent = 'Send';

    });

}



var sendBtn = document.getElementById('agent-send-btn');

var replyInput = document.getElementById('agent-reply-input');

if (sendBtn) sendBtn.addEventListener('click', sendAgentReply);

if (replyInput) replyInput.addEventListener('keydown', function(e) {

    if (e.key === 'Enter') sendAgentReply();

});

// ── AGENT-SIDE MESSAGE EDIT / DELETE (own messages only) ──────
box.addEventListener('click', function(e) {
    var editBtn = e.target.closest && e.target.closest('.msg-edit-btn');
    var delBtn  = e.target.closest && e.target.closest('.msg-del-btn');
    if (!editBtn && !delBtn) return;

    var wrap = e.target.closest('[data-mid]');
    if (!wrap) return;
    var mid = wrap.getAttribute('data-mid');
    if (!mid) return;

    if (editBtn) startEditAgentMessage(wrap, mid);
    else if (delBtn) deleteAgentMessage(wrap, mid);
});

function startEditAgentMessage(wrap, mid) {
    if (wrap.classList.contains('editing')) return;
    var bubble = wrap.querySelector('.msg-bubble');
    var bodyEl = wrap.querySelector('.msg-body');
    if (!bubble || !bodyEl) return;
    var currentText = bodyEl.textContent.trim();
    wrap.classList.add('editing');

    var actions = wrap.querySelector('.msg-actions');
    bubble.style.display = 'none';
    if (actions) actions.style.display = 'none';

    var editBox = document.createElement('div');
    editBox.className = 'msg-edit-box';
    editBox.innerHTML = '<textarea class="msg-edit-textarea" rows="2"></textarea>' +
        '<div class="msg-edit-actions">' +
        '<button type="button" class="msg-edit-cancel-btn">Cancel</button>' +
        '<button type="button" class="msg-edit-save-btn">Save</button>' +
        '</div>';
    bubble.parentNode.insertBefore(editBox, bubble);
    var textarea = editBox.querySelector('.msg-edit-textarea');
    textarea.value = currentText;
    textarea.focus();
    textarea.setSelectionRange(textarea.value.length, textarea.value.length);

    function cleanup() {
        editBox.parentNode.removeChild(editBox);
        bubble.style.display = '';
        if (actions) actions.style.display = '';
        wrap.classList.remove('editing');
    }

    editBox.querySelector('.msg-edit-cancel-btn').addEventListener('click', cleanup);
    editBox.querySelector('.msg-edit-save-btn').addEventListener('click', function() {
        var newText = textarea.value.trim();
        if (!newText || newText === currentText) { cleanup(); return; }

        fetch(window.location.origin + '/chatbot/agent/conversations/{{ $conversation->id }}/messages/' + mid + '/edit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ message: newText })
        })
        .then(function(r) { return r.json().catch(function(){ return null; }).then(function(d){ return { ok: r.ok, data: d }; }); })
        .then(function(res) {
            var data = res.data;
            if (!res.ok || !data || data.error || data.success !== true || !data.message || typeof data.message.body !== 'string') {
                cleanup();
                return;
            }
            bodyEl.textContent = data.message.body;
            if (!bubble.querySelector('.msg-edited-tag')) {
                var tag = document.createElement('span');
                tag.className = 'msg-edited-tag';
                tag.style.cssText = 'font-size:10px;opacity:0.6;font-style:italic;margin-left:4px;';
                tag.textContent = '(edited)';
                var timeEl = bubble.querySelector('.msg-time');
                if (timeEl) bubble.insertBefore(tag, timeEl);
                else bubble.appendChild(tag);
            }
            cleanup();
        })
        .catch(function() { cleanup(); });
    });

    textarea.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); editBox.querySelector('.msg-edit-save-btn').click(); }
        if (e.key === 'Escape') cleanup();
    });
}

function deleteAgentMessage(wrap, mid) {
    if (!window.confirm('Delete this message?')) return;
    fetch(window.location.origin + '/chatbot/agent/conversations/{{ $conversation->id }}/messages/' + mid + '/delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(function(r) { return r.json().catch(function(){ return null; }).then(function(d){ return { ok: r.ok, data: d }; }); })
    .then(function(res) {
        var data = res.data;
        if (!res.ok || !data || data.error || data.success !== true) return;
        var bubble = wrap.querySelector('.msg-bubble');
        if (bubble) {
            bubble.classList.add('msg-bubble-deleted');
            bubble.style.background = 'transparent';
            bubble.style.color = '#94a3b8';
            bubble.style.border = '1px dashed #cbd5e1';
            bubble.style.boxShadow = 'none';
            bubble.style.fontStyle = 'italic';
            var bodyEl = bubble.querySelector('.msg-body');
            if (bodyEl) bodyEl.textContent = 'Message deleted';
            var tagEl = bubble.querySelector('.msg-edited-tag');
            if (tagEl) tagEl.remove();
        }
        var actionsEl = wrap.querySelector('.msg-actions');
        if (actionsEl) actionsEl.parentNode.removeChild(actionsEl);
    })
    .catch(function() {});
}



// Agent typing indicator — cache se widget ko batao

var agentTypingTimer;

var agentIsTyping = false;



if (replyInput) {

    replyInput.addEventListener('input', function() {

        if (!agentIsTyping) {

            agentIsTyping = true;

            fetch(window.location.origin + '/chatbot/api/agent/typing', {

                method: 'POST',

                headers: {

                    'Content-Type': 'application/x-www-form-urlencoded',

                    'X-CSRF-TOKEN': csrfToken

                },

                body: '_token=' + csrfToken +

                      '&conversation_id=' + conversationId +

                      '&typing=1'

            });

        }

        clearTimeout(agentTypingTimer);

        agentTypingTimer = setTimeout(function() {

            agentIsTyping = false;

            fetch(window.location.origin + '/chatbot/api/agent/typing', {

                method: 'POST',

                headers: {

                    'Content-Type': 'application/x-www-form-urlencoded',

                    'X-CSRF-TOKEN': csrfToken

                },

                body: '_token=' + csrfToken +

                      '&conversation_id=' + conversationId +

                      '&typing=0'

            });

        }, 1500);

    });

}

var uploadBtn  = document.getElementById('agent-upload-btn');
var uploadFile = document.getElementById('agent-file');

if (uploadBtn) {
    uploadBtn.addEventListener('click', function(){
        if (uploadFile) uploadFile.click();
    });
}

if (uploadFile) {
    uploadFile.addEventListener('change', function(){

        if(!this.files.length) return;

        const file = this.files[0];

        // Reset input
        this.value = '';

        uploadAgentImage(file);

    });
}

// ── Canned Responses ─────────────────────────────────────────
(function () {
    var cannedBtn      = document.getElementById('canned-btn');
    var cannedPopover  = document.getElementById('canned-popover');
    var cannedList     = document.getElementById('canned-list');
    var replyInputEl   = document.getElementById('agent-reply-input');
    var loaded         = false;

    if (!cannedBtn) return;

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function renderList(items) {
        cannedList.innerHTML = '';
        if (!items.length) {
            var empty = document.createElement('div');
            empty.style.cssText = 'padding:14px;text-align:center;font-size:12px;color:#a78bfa;';
            empty.textContent = 'No canned responses yet. Add some from the sidebar.';
            cannedList.appendChild(empty);
            return;
        }
        items.forEach(function (item) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'canned-item';
            btn.dataset.body = item.body;
            btn.style.cssText = 'display:block;width:100%;text-align:left;padding:8px 10px;border:none;background:none;border-radius:10px;cursor:pointer;';

            var title = document.createElement('div');
            title.style.cssText = 'font-size:12.5px;font-weight:600;color:#4c1d95;';
            title.textContent = item.title;

            var body = document.createElement('div');
            body.style.cssText = 'font-size:11.5px;color:#94a3b8;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;';
            body.textContent = item.body;

            btn.appendChild(title);
            btn.appendChild(body);
            cannedList.appendChild(btn);
        });
    }

    function loadCannedResponses() {
        cannedList.innerHTML = '<div style="padding:14px;text-align:center;font-size:12px;color:#a78bfa;">Loading...</div>';
        fetch(window.location.origin + '/chatbot/agent/canned-responses-data', {
            headers: { 'Accept': 'application/json' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            loaded = true;
            renderList(Array.isArray(data) ? data : []);
        })
        .catch(function () {
            cannedList.innerHTML = '<div style="padding:14px;text-align:center;font-size:12px;color:#dc2626;">Could not load responses.</div>';
        });
    }

    cannedBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = cannedPopover.style.display === 'block';
        cannedPopover.style.display = isOpen ? 'none' : 'block';
        if (!isOpen && !loaded) loadCannedResponses();
    });

    cannedList.addEventListener('click', function (e) {
        var btn = e.target.closest('.canned-item');
        if (!btn || !replyInputEl) return;
        var text = btn.getAttribute('data-body');
        replyInputEl.value = replyInputEl.value ? (replyInputEl.value + ' ' + text) : text;
        replyInputEl.focus();
        cannedPopover.style.display = 'none';
    });

    document.addEventListener('click', function (e) {
        if (cannedPopover.style.display === 'block' &&
            !cannedPopover.contains(e.target) &&
            e.target !== cannedBtn) {
            cannedPopover.style.display = 'none';
        }
    });
})();

document.addEventListener('paste', function(e){

    var items = e.clipboardData.items;

    for(var i=0;i<items.length;i++){

        if(items[i].type.indexOf('image') !== -1){

            uploadAgentImage(
                items[i].getAsFile()
            );

            break;
        }
    }

});

// Assign

var assignBtn = document.getElementById('assign-btn');

if (assignBtn) {

    assignBtn.addEventListener('click', function() {

        var agentId = document.getElementById('assign-select').value;

        if (!agentId) return;

        assignBtn.disabled = true;

        assignBtn.textContent = 'Assigning...';



        fetch(window.location.origin + '/chatbot/agent/conversations/{{ $conversation->id }}/assign', {

            method: 'POST',

            headers: {

                'Content-Type': 'application/x-www-form-urlencoded',

                'X-CSRF-TOKEN': csrfToken,

                'Accept': 'application/json'

            },

            body: '_token=' + csrfToken + '&agent_id=' + agentId

        })

        .then(r => r.json())

        .then(function(data) {

            assignBtn.disabled = false;

            assignBtn.textContent = 'Assign';

            var msg = document.getElementById('assign-msg');

            msg.classList.remove('hidden');

            setTimeout(function() { msg.classList.add('hidden'); }, 2000);

            // Real-time: "ASSIGNED TO" section update karo bina page reload ke
            if (data.agent_name) {
                var assignedSpan = document.getElementById('assigned-to-name');
                if (assignedSpan) {
                    assignedSpan.textContent = data.agent_name;
                    assignedSpan.style.color = '#6366f1';
                    assignedSpan.style.fontWeight = '600';
                }
            }

        })

        .catch(function() {

            assignBtn.disabled = false;

            assignBtn.textContent = 'Assign';

        });

    });

}

function uploadAgentImage(file){

    var fd = new FormData();

    fd.append('conversation_id', conversationId);
    fd.append('file', file);

    fetch(window.location.origin + '/chatbot/agent/upload', {
        method:'POST',
        headers:{
            'X-CSRF-TOKEN': csrfToken
        },
        body: fd
    })
    .then(function(r){
        if (!r.ok) { console.error('Upload failed: ' + r.status); return null; }
        return r.json();
    })
    .then(function(data){
        if (!data || !data.url) return;

        var wrapper = document.createElement('div');
        wrapper.style.cssText = 'display:flex;justify-content:flex-end;margin-bottom:2px;';

        var outer = document.createElement('div');
        outer.style.cssText = 'max-width:85%;';

		var sender = document.createElement('div');
		sender.style.cssText = 'font-size:11px;font-weight:600;color:#8b5cf6;margin-bottom:4px;text-align:right;';
		sender.textContent = '{{ auth()->user()->name }}';

        var bubble = document.createElement('div');
        bubble.style.cssText = 'display:table;word-break:break-word;overflow-wrap:anywhere;padding:10px 14px 8px 14px;max-width:100%;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;border-radius:18px 18px 4px 18px;box-shadow:0 6px 20px rgba(102,126,234,0.35);margin-left:auto;';

        bubble.innerHTML =
            '<a href="' + data.url + '" target="_blank">' +
            '<img src="' + data.url + '" style="width:100%;max-width:200px;height:auto;border-radius:10px;display:block;">' +
            '</a>' +
            '<div style="font-size:10px;margin-top:5px;opacity:0.55;text-align:right;">Just now</div>';

		outer.appendChild(sender);
        outer.appendChild(bubble);
        wrapper.appendChild(outer);

        box.appendChild(wrapper);

        box.scrollTop = box.scrollHeight;

    })
    .catch(function(e){ console.error('Upload error:', e); });

}

// ── Heartbeat: agent online status fresh rakhne ke liye ──────
// Jab agent conversation page pe ho, har 60 sec mein ping karo
// Taaki widget mein "No agents online" na aaye
(function heartbeat() {
    fetch(window.location.origin + '/chatbot/agent/ping', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    }).catch(function(){});
    setTimeout(heartbeat, 60000);
})();

// ── Internal Notes ──────────────────────────────────────────
var notesList  = document.getElementById('notes-list');
var noteInput  = document.getElementById('note-input');
var addBtn     = document.getElementById('note-add-btn');
var noNotesMsg = document.getElementById('no-notes-msg');
var currentUserId = {{ auth()->id() }};

function escapeHtml(str) {
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function renderNote(note) {
    var div = document.createElement('div');
    div.className = 'note-item p-2.5 rounded-xl';
    div.setAttribute('data-note-id', note.id);
    div.style.cssText = 'background:#fffbeb;border:1px solid #fde68a;';
    div.innerHTML =
        '<p class="text-xs" style="color:#78350f;white-space:pre-wrap;">' + escapeHtml(note.body) + '</p>' +
        '<div class="flex justify-between items-center mt-1.5">' +
            '<span class="text-xs" style="color:#b45309;">' + escapeHtml(note.author) + ' · ' + escapeHtml(note.created_at) + '</span>' +
            '<button type="button" class="note-delete-btn" data-note-id="' + note.id + '" style="background:none;border:none;color:#b45309;cursor:pointer;font-size:11px;opacity:0.6;">✕</button>' +
        '</div>';
    return div;
}

function showNoteError(msg) {
    var box = document.getElementById('note-error');
    if (!box) {
        box = document.createElement('div');
        box.id = 'note-error';
        box.style.cssText = 'color:#dc2626;font-size:11px;text-align:center;margin-top:6px;';
        addBtn.insertAdjacentElement('afterend', box);
    }
    box.textContent = msg;
}

if (addBtn) {
    addBtn.addEventListener('click', function () {
        var body = (noteInput.value || '').trim();
        if (!body) return;
        addBtn.disabled = true; addBtn.textContent = 'Adding...';

        fetch(window.location.origin + '/chatbot/agent/conversations/{{ $conversation->id }}/notes', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ body: body })
        })
        .then(function (r) {
            return r.text().then(function (text) {
                var data = null;
                try { data = JSON.parse(text); } catch (e) {}
                return { ok: r.ok, status: r.status, data: data, raw: text };
            });
        })
        .then(function (res) {
            addBtn.disabled = false; addBtn.textContent = 'Add Note';
            if (!res.ok || !res.data || res.data.success !== true) {
                console.error('CB: add note failed', res.status, res.data || res.raw);
                showNoteError('Could not add note (error ' + res.status + '). Check console for details.');
                return;
            }
            if (noNotesMsg) { noNotesMsg.remove(); noNotesMsg = null; }
            if (notesList && !notesList.querySelector('.note-item[data-note-id="' + res.data.note.id + '"]')) {
                notesList.insertBefore(renderNote(res.data.note), notesList.firstChild);
            }
            noteInput.value = '';
            var err = document.getElementById('note-error');
            if (err) err.remove();
        })
        .catch(function (e) {
            console.error('CB: add note network error', e);
            addBtn.disabled = false; addBtn.textContent = 'Add Note';
            showNoteError('Network error — please try again.');
        });
    });
}

if (notesList) {
    notesList.addEventListener('click', function (e) {
        var btn = e.target.closest('.note-delete-btn');
        if (!btn) return;
        var noteId = btn.getAttribute('data-note-id');
        if (!confirm('Delete this note?')) return;

        fetch(window.location.origin + '/chatbot/agent/conversations/{{ $conversation->id }}/notes/' + noteId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                var item = notesList.querySelector('.note-item[data-note-id="' + noteId + '"]');
                if (item) item.remove();
            }
        });
    });
}

// ── Tags ─────────────────────────────────────────────────────
(function () {
    var pillsBox   = document.getElementById('tag-pills');
    var emptyMsg   = document.getElementById('tag-empty-msg');
    var selectEl   = document.getElementById('tag-select');
    var newInput   = document.getElementById('tag-new-input');
    var newBtn     = document.getElementById('tag-new-btn');

    if (!pillsBox) return;

    var TAG_BASE = window.location.origin + '/chatbot/agent/conversations/{{ $conversation->id }}/tags';

    function renderPill(tag) {
        var span = document.createElement('span');
        span.className = 'tag-pill';
        span.setAttribute('data-tag-id', tag.id);
        span.style.cssText = 'display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:11.5px;font-weight:600;color:#fff;background:' + (tag.color || '#6366f1') + ';';
        span.appendChild(document.createTextNode(tag.name));

        @if(!$isViewer)
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'tag-remove-btn';
        btn.setAttribute('data-tag-id', tag.id);
        btn.style.cssText = 'background:none;border:none;color:#fff;opacity:0.75;cursor:pointer;font-size:11px;line-height:1;padding:0;';
        btn.textContent = '✕';
        span.appendChild(btn);
        @endif

        return span;
    }

    function addTagToConversation(payload) {
        return fetch(TAG_BASE, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data || !data.success) return;
            if (emptyMsg) { emptyMsg.style.display = 'none'; }
            if (!pillsBox.querySelector('.tag-pill[data-tag-id="' + data.tag.id + '"]')) {
                pillsBox.insertBefore(renderPill(data.tag), emptyMsg || null);
            }
            // Remove it from the "add existing" dropdown now that it's attached
            if (selectEl) {
                var opt = selectEl.querySelector('option[value="' + data.tag.id + '"]');
                if (opt) opt.remove();
            }
        });
    }

    if (selectEl) {
        selectEl.addEventListener('change', function () {
            if (!selectEl.value) return;
            addTagToConversation({ tag_id: selectEl.value });
            selectEl.value = '';
        });
    }

    if (newBtn) {
        newBtn.addEventListener('click', function () {
            var name = (newInput.value || '').trim();
            if (!name) return;
            addTagToConversation({ name: name }).then(function () {
                newInput.value = '';
            });
        });
        newInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); newBtn.click(); }
        });
    }

    pillsBox.addEventListener('click', function (e) {
        var btn = e.target.closest('.tag-remove-btn');
        if (!btn) return;
        var tagId = btn.getAttribute('data-tag-id');

        fetch(TAG_BASE + '/' + tagId, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data || !data.success) return;
            var pill = pillsBox.querySelector('.tag-pill[data-tag-id="' + tagId + '"]');
            if (pill) {
                var name = pill.textContent.replace('✕', '').trim();
                pill.remove();
                if (selectEl) {
                    var opt = document.createElement('option');
                    opt.value = tagId;
                    opt.textContent = name;
                    selectEl.appendChild(opt);
                }
            }
            if (pillsBox.querySelectorAll('.tag-pill').length === 0 && emptyMsg) {
                emptyMsg.style.display = 'inline';
            }
        });
    });
})();

</script>

@endsection