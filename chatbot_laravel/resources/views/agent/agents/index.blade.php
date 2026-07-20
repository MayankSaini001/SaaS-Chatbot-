@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Team Members</h1>
        <span class="text-sm text-gray-500">{{ $agents->count() }} agent(s)</span>
    </div>

    {{-- Plan limit check --}}
    @php
        $planLimits = ['basic' => 2, 'pro' => 10, 'enterprise' => 999];
        $tenant = auth()->user()->tenant ?? \App\Models\Tenant::find(auth()->user()->tenant_id);
        $maxAgents = $planLimits[$tenant->plan ?? 'basic'] ?? 2;
        $limitReached = $agents->count() >= $maxAgents;
    @endphp

    {{-- Add Agent Form --}}
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-700">Add New Agent</h2>
            <span class="text-xs px-3 py-1 rounded-full font-medium {{ $limitReached ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-700' }}">
                {{ $agents->count() }} / {{ $maxAgents == 999 ? '∞' : $maxAgents }} agents used
            </span>
        </div>

        <form method="POST" action="{{ route('agent.agents.add') }}" class="flex flex-col md:flex-row gap-3">
            @csrf
            <input type="text" name="name" placeholder="Full Name" required
                {{ $limitReached ? 'disabled' : '' }}
                class="flex-1 border rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 {{ $limitReached ? 'bg-gray-100 cursor-not-allowed opacity-60' : '' }}">
            <input type="email" name="email" placeholder="Email" required
                {{ $limitReached ? 'disabled' : '' }}
                class="flex-1 border rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 {{ $limitReached ? 'bg-gray-100 cursor-not-allowed opacity-60' : '' }}">
            <input type="password" name="password" placeholder="Password" required
                {{ $limitReached ? 'disabled' : '' }}
                class="flex-1 border rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 {{ $limitReached ? 'bg-gray-100 cursor-not-allowed opacity-60' : '' }}">
            <select name="role"
                {{ $limitReached ? 'disabled' : '' }}
                title="Agent: full access to reply/resolve chats. Viewer: read-only access."
                class="border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 {{ $limitReached ? 'bg-gray-100 cursor-not-allowed opacity-60' : '' }}">
                <option value="agent">Agent (full access)</option>
                <option value="viewer">Viewer (read-only)</option>
            </select>
            <button type="submit"
                {{ $limitReached ? 'disabled' : '' }}
                class="px-6 py-2 rounded-lg text-sm whitespace-nowrap font-semibold {{ $limitReached ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-indigo-600 text-white hover:bg-indigo-700' }}">
                + Add Agent
            </button>
        </form>

        @if($limitReached)
        <div class="mt-3 flex items-center gap-2 p-3 bg-amber-50 border border-amber-200 rounded-lg">
            <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <span class="text-sm text-amber-700">
                Agent limit reached for your <strong class="capitalize">{{ $tenant->plan }}</strong> plan (max {{ $maxAgents }}).
                <a href="{{ route('billing.pricing') }}" class="underline font-semibold text-indigo-600 ml-1">Upgrade plan →</a>
            </span>
        </div>
        @endif

        @if(session('success'))
        <div class="mt-3 text-sm text-green-600 font-medium">✓ {{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="mt-3 text-sm text-red-600 font-medium">✗ {{ session('error') }}</div>
        @endif
    </div>

    {{-- Agents List --}}
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Agent</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Role</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Last Seen</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Conversations</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($agents as $agent)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm flex-shrink-0" style="overflow:hidden;">
                                @if($agent->avatar)
                                    <img src="{{ $agent->avatar }}" alt="{{ $agent->name }}" style="width:100%;height:100%;object-fit:cover;">
                                @else
                                    {{ strtoupper(substr($agent->name, 0, 1)) }}
                                @endif
                            </div>
                            <div>
                                <div class="font-medium text-gray-800">
                                    {{ $agent->name }}
                                    @if($agent->id === auth()->id())
                                    <span class="text-xs text-indigo-500 font-normal">(You)</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-400">{{ $agent->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($agent->id === auth()->id())
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $agent->role === 'viewer' ? 'bg-amber-100 text-amber-700' : 'bg-indigo-100 text-indigo-700' }}">
                            {{ ucfirst($agent->role) }}
                        </span>
                        @else
                        <form method="POST" action="{{ route('agent.agents.role', $agent) }}"
                              onchange="this.submit()">
                            @csrf @method('PATCH')
                            <select name="role"
                                class="text-xs font-semibold rounded-full px-2.5 py-1 border-0 focus:outline-none focus:ring-2 focus:ring-indigo-400 cursor-pointer {{ $agent->role === 'viewer' ? 'bg-amber-100 text-amber-700' : 'bg-indigo-100 text-indigo-700' }}">
                                <option value="agent" {{ $agent->role === 'agent' ? 'selected' : '' }}>Agent</option>
                                <option value="viewer" {{ $agent->role === 'viewer' ? 'selected' : '' }}>Viewer</option>
                            </select>
                        </form>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $isOnline = $agent->is_online && $agent->last_seen && $agent->last_seen->gt(now()->subMinutes(2));
                        @endphp
                        @if($isOnline)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                            Online
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                            Offline
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-500 text-xs">
                        {{ $agent->last_seen ? $agent->last_seen->diffForHumans() : 'Never' }}
                    </td>
                    <td class="px-6 py-4 text-gray-600">
                        {{ $agent->conversations()->count() }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        @if($agent->id !== auth()->id())
                        <form method="POST" action="{{ route('agent.agents.delete', $agent) }}"
                              onsubmit="return confirm('Remove this agent?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-500 hover:text-red-700 font-medium">Remove</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-10 text-center text-gray-400">No agents yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
