@extends('layouts.app')

@section('content')
@php
    $isViewer = auth()->user()->role === 'viewer';
@endphp
<div class="max-w-4xl mx-auto">

    <div class="flex justify-between items-center mb-2">
        <h1 class="text-2xl font-bold text-gray-800">Auto-Replies (FAQ Bot)</h1>
        <span class="text-sm text-gray-500">{{ $autoReplies->count() }} rule{{ $autoReplies->count() == 1 ? '' : 's' }}</span>
    </div>
    <p class="text-sm text-gray-500 mb-6">
        Jab visitor apne message me koi keyword type kare, bot turant matching reply bhej deta hai — agent ke available hone se pehle hi.
    </p>

    @if(!$isViewer)
    {{-- Add Form --}}
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <h2 class="font-semibold text-gray-700 mb-4">Add New Auto-Reply</h2>

        <form method="POST" action="{{ route('agent.auto-replies.store') }}" class="flex flex-col gap-3">
            @csrf
            <input type="text" name="keyword" placeholder="Keyword visitor might type (e.g. pricing, refund, hours)" required maxlength="100"
                class="border rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <textarea name="reply" rows="3" placeholder="Instant reply the bot should send when this keyword is found..." required
                class="border rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"></textarea>
            <button type="submit"
                class="self-start px-6 py-2 rounded-lg text-sm font-semibold bg-indigo-600 text-white hover:bg-indigo-700">
                + Add Auto-Reply
            </button>
        </form>

        @if(session('success'))
        <div class="mt-3 text-sm text-green-600 font-medium">✓ {{ session('success') }}</div>
        @endif
    </div>
    @else
    <div class="mb-6 text-sm text-center py-3 rounded-xl" style="background:#f5f3ff;color:#7c3aed;border:1px solid #ede9fe;font-weight:500;">
        👁️ You have read-only (Viewer) access — only Agents and Owners can add, pause, or delete auto-replies.
    </div>
    @endif

    {{-- List --}}
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        @forelse($autoReplies as $rule)
        <div class="flex items-start justify-between gap-4 px-6 py-4 border-b last:border-0" style="{{ $rule->is_active ? '' : 'opacity:0.55;' }}">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold" style="background:#ede9fe;color:#6d28d9;">
                        🔑 {{ $rule->keyword }}
                    </span>
                    @if(!$rule->is_active)
                        <span class="text-xs font-semibold text-gray-400">Paused</span>
                    @endif
                </div>
                <div class="text-xs text-gray-500 line-clamp-2">{{ $rule->reply }}</div>
            </div>
            @if(!$isViewer)
            <div class="flex items-center gap-3 flex-shrink-0">
                <form method="POST" action="{{ route('agent.auto-replies.toggle', $rule) }}">
                    @csrf
                    <button type="submit" class="text-xs font-medium whitespace-nowrap" style="color:{{ $rule->is_active ? '#b45309' : '#059669' }};">
                        {{ $rule->is_active ? 'Pause' : 'Enable' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('agent.auto-replies.destroy', $rule) }}"
                      onsubmit="return confirm('Delete this auto-reply rule?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium whitespace-nowrap">
                        Delete
                    </button>
                </form>
            </div>
            @endif
        </div>
        @empty
        <div class="text-center text-gray-400 text-sm py-10">
            No auto-reply rules yet. Add your first keyword above — try "pricing" or "refund".
        </div>
        @endforelse
    </div>

    <p class="text-xs text-gray-400 mt-4">
        💡 Matching is case-insensitive and checks if the keyword appears anywhere in the visitor's message. The bot's reply shows up instantly as "Quick Reply" in the chat — agents can still jump in normally afterwards.
    </p>

</div>
@endsection
