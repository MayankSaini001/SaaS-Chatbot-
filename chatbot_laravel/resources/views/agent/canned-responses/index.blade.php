@extends('layouts.app')

@section('content')
@php
    $isViewer = auth()->user()->role === 'viewer';
@endphp
<div class="max-w-4xl mx-auto">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Canned Responses</h1>
        <span class="text-sm text-gray-500">{{ $responses->count() }} saved repl{{ $responses->count() == 1 ? 'y' : 'ies' }}</span>
    </div>

    @if(!$isViewer)
    {{-- Add Form --}}
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <h2 class="font-semibold text-gray-700 mb-4">Add New Canned Response</h2>

        <form method="POST" action="{{ route('agent.canned-responses.store') }}" class="flex flex-col gap-3">
            @csrf
            <input type="text" name="title" placeholder="Short title (e.g. Greeting, Payment Issue)" required
                class="border rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <textarea name="body" rows="3" placeholder="Full message text agents will send..." required
                class="border rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"></textarea>
            <button type="submit"
                class="self-start px-6 py-2 rounded-lg text-sm font-semibold bg-indigo-600 text-white hover:bg-indigo-700">
                + Add Response
            </button>
        </form>

        @if(session('success'))
        <div class="mt-3 text-sm text-green-600 font-medium">✓ {{ session('success') }}</div>
        @endif
    </div>
    @else
    <div class="mb-6 text-sm text-center py-3 rounded-xl" style="background:#f5f3ff;color:#7c3aed;border:1px solid #ede9fe;font-weight:500;">
        👁️ You have read-only (Viewer) access — only Agents and Owners can add or delete responses.
    </div>
    @endif

    {{-- List --}}
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        @forelse($responses as $response)
        <div class="flex items-start justify-between gap-4 px-6 py-4 border-b last:border-0">
            <div class="flex-1 min-w-0">
                <div class="font-medium text-gray-800 text-sm mb-1">{{ $response->title }}</div>
                <div class="text-xs text-gray-500 line-clamp-2">{{ $response->body }}</div>
                <div class="text-xs text-gray-400 mt-1.5">Added by {{ $response->author->name ?? 'Agent' }}</div>
            </div>
            @if(!$isViewer)
            <form method="POST" action="{{ route('agent.canned-responses.destroy', $response) }}"
                  onsubmit="return confirm('Delete this canned response?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium whitespace-nowrap">
                    Delete
                </button>
            </form>
            @endif
        </div>
        @empty
        <div class="text-center text-gray-400 text-sm py-10">
            No canned responses yet. Add your first quick reply above.
        </div>
        @endforelse
    </div>

    <p class="text-xs text-gray-400 mt-4">
        💡 These responses are shared across your whole team and appear as quick-insert options while replying to a conversation.
    </p>

</div>
@endsection
