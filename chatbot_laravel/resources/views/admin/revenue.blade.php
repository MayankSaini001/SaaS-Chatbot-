@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-800">Revenue Overview</h1>
        <p class="text-sm text-gray-400 mt-0.5">Subscriptions & earnings</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">

        <div class="bg-white rounded-xl shadow-sm border p-4 md:p-5">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs text-gray-400 font-medium">MRR</span>
            </div>
            <div class="text-xl md:text-2xl font-bold text-green-600 truncate">₹{{ number_format($mrr) }}</div>
            <div class="text-xs text-gray-400 mt-0.5">Monthly Revenue</div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-4 md:p-5">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span class="text-xs text-gray-400 font-medium">Subscribers</span>
            </div>
            <div class="text-xl md:text-2xl font-bold text-indigo-600">{{ $activeTenants }}</div>
            <div class="text-xs text-gray-400 mt-0.5">Active</div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-4 md:p-5">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <span class="text-xs text-gray-400 font-medium">Chats</span>
            </div>
            <div class="text-xl md:text-2xl font-bold text-blue-600">{{ $totalConversations }}</div>
            <div class="text-xs text-gray-400 mt-0.5">Total Conversations</div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-4 md:p-5">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 bg-orange-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <span class="text-xs text-gray-400 font-medium">ARR</span>
            </div>
            <div class="text-xl md:text-2xl font-bold text-orange-600 truncate">₹{{ number_format($mrr * 12) }}</div>
            <div class="text-xs text-gray-400 mt-0.5">Annual Revenue</div>
        </div>

    </div>

    {{-- Recent Subscriptions --}}
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="px-4 md:px-6 py-4 border-b flex justify-between items-center bg-gray-50">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h2 class="font-semibold text-gray-700 text-sm">Recent Subscriptions</h2>
            </div>
            <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-full">
                {{ count($recentTenants) }} total
            </span>
        </div>

        {{-- Mobile: Card view --}}
        <div class="md:hidden divide-y">
            @foreach($recentTenants as $tenant)
            <div class="p-4 space-y-2">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="font-semibold text-gray-800 text-sm">{{ $tenant['name'] }}</div>
                        <div class="text-xs text-gray-400 truncate mt-0.5">{{ $tenant['email'] }}</div>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium ml-2 flex-shrink-0
                        {{ $tenant['status'] === 'Active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $tenant['status'] }}
                    </span>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full text-xs font-medium">
                        {{ ucfirst($tenant['plan']) }}
                    </span>
                    <span class="text-green-600 font-semibold text-xs">
                        ₹{{ number_format($tenant['amount']) }}/mo
                    </span>
                    <span class="text-gray-400 text-xs">{{ $tenant['date'] }}</span>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Desktop: Table view --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-6 py-3 text-gray-500 font-medium text-xs uppercase tracking-wider">Company</th>
                        <th class="text-left px-6 py-3 text-gray-500 font-medium text-xs uppercase tracking-wider">Email</th>
                        <th class="text-left px-6 py-3 text-gray-500 font-medium text-xs uppercase tracking-wider">Plan</th>
                        <th class="text-left px-6 py-3 text-gray-500 font-medium text-xs uppercase tracking-wider">Amount</th>
                        <th class="text-left px-6 py-3 text-gray-500 font-medium text-xs uppercase tracking-wider">Status</th>
                        <th class="text-left px-6 py-3 text-gray-500 font-medium text-xs uppercase tracking-wider">Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentTenants as $tenant)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-medium text-gray-800">{{ $tenant['name'] }}</td>
                        <td class="px-6 py-4 text-gray-500 text-xs">{{ $tenant['email'] }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-medium">
                                {{ ucfirst($tenant['plan']) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-green-600 font-semibold text-xs">
                            ₹{{ number_format($tenant['amount']) }}/mo
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium
                                {{ $tenant['status'] === 'Active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $tenant['status'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-400 text-xs">{{ $tenant['date'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection