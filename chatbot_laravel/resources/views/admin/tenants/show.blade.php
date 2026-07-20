@extends('layouts.app')

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.tenants') }}"
           class="text-gray-400 hover:text-gray-600 text-sm flex items-center gap-1 flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back
        </a>
        <h1 class="text-xl md:text-2xl font-bold text-gray-800">{{ $tenant->name }}</h1>
        <span class="px-3 py-1 rounded-full text-xs font-semibold flex-shrink-0
            {{ $tenant->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
            {{ $tenant->is_active ? 'Active' : 'Inactive' }}
        </span>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

        <div class="bg-white rounded-xl border shadow-sm p-4 text-center hover:shadow-md transition">
            <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div class="text-2xl font-bold text-indigo-600">{{ $agents->count() }}</div>
            <div class="text-xs text-gray-400 mt-0.5">Agents</div>
        </div>

        <div class="bg-white rounded-xl border shadow-sm p-4 text-center hover:shadow-md transition">
            <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <div class="text-2xl font-bold text-blue-600">{{ $conversations->count() }}</div>
            <div class="text-xs text-gray-400 mt-0.5">Conversations</div>
        </div>

        <div class="bg-white rounded-xl border shadow-sm p-4 text-center hover:shadow-md transition">
            <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                </svg>
            </div>
            <div class="text-2xl font-bold text-green-600">{{ $totalMessages }}</div>
            <div class="text-xs text-gray-400 mt-0.5">Total Messages</div>
        </div>

        <div class="bg-white rounded-xl border shadow-sm p-4 text-center hover:shadow-md transition">
            <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div class="text-xl font-bold text-orange-600">{{ ucfirst($tenant->plan) }}</div>
            <div class="text-xs text-gray-400 mt-0.5">Plan</div>
        </div>

    </div>

    {{-- Agents + Conversations --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- Agents --}}
        <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b bg-gray-50 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <h2 class="font-semibold text-gray-700 text-sm">Agents</h2>
                <span class="ml-auto text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full font-medium">
                    {{ $agents->count() }}
                </span>
            </div>
            <div class="divide-y">
                @forelse($agents as $agent)
                <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-gray-50 transition">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500
                                flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                        {{ strtoupper(substr($agent->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="text-sm font-medium text-gray-800">{{ $agent->name }}</div>
                        <div class="text-xs text-gray-400 truncate">{{ $agent->email }}</div>
                    </div>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">No agents yet.</div>
                @endforelse
            </div>
        </div>

        {{-- Recent Conversations --}}
        <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b bg-gray-50 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <h2 class="font-semibold text-gray-700 text-sm">Recent Conversations</h2>
                <span class="ml-auto text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">
                    {{ $conversations->count() }}
                </span>
            </div>
            <div class="divide-y">
                @forelse($conversations as $conv)
                <div class="flex items-center justify-between px-5 py-3.5 hover:bg-gray-50 transition">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center
                                    text-gray-600 font-bold text-xs flex-shrink-0">
                            {{ strtoupper(substr($conv->visitor_name ?? 'V', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-medium text-gray-700 truncate">
                                {{ $conv->visitor_name ?? 'Visitor' }}
                            </div>
                            <div class="text-xs text-gray-400">{{ $conv->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium flex-shrink-0 ml-2
                        {{ $conv->status === 'open' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ ucfirst($conv->status) }}
                    </span>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">No conversations yet.</div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Toggle Status --}}
    <div class="pt-2">
        <form method="POST" action="{{ route('admin.tenants.toggle', $tenant) }}">
            @csrf
            <button class="px-6 py-2.5 rounded-xl text-sm font-semibold transition shadow-sm
                {{ $tenant->is_active
                    ? 'bg-red-500 text-white hover:bg-red-600'
                    : 'bg-green-500 text-white hover:bg-green-600' }}">
                {{ $tenant->is_active ? '⛔ Deactivate Tenant' : '✅ Activate Tenant' }}
            </button>
        </form>
    </div>

</div>
@endsection