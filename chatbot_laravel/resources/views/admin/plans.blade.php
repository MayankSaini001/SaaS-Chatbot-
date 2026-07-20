@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-800">Plans Management</h1>
        <p class="text-sm text-gray-400 mt-0.5">Subscription plans & revenue</p>
    </div>

    {{-- Plans Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
        @foreach($plans as $plan)
        <div class="bg-white rounded-xl shadow-sm border p-5 md:p-6 hover:shadow-md transition">

            {{-- Plan Header --}}
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">{{ $plan['name'] }}</h2>
                    <span class="text-xs text-gray-400 font-medium">Monthly Plan</span>
                </div>
                <div class="text-right flex-shrink-0 ml-2">
                    <div class="text-xl md:text-2xl font-bold text-indigo-600">
                        ₹{{ number_format($plan['price']) }}
                    </div>
                    <div class="text-xs text-gray-400">/month</div>
                </div>
            </div>

            {{-- Plan Details --}}
            <div class="flex flex-col gap-2 text-sm mb-5">
                <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-gray-500">Max Agents</span>
                    <span class="font-semibold text-gray-800">
                        {{ $plan['agents'] == 999 ? 'Unlimited' : $plan['agents'] }}
                    </span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-gray-500">Monthly Chats</span>
                    <span class="font-semibold text-gray-800">
                        {{ $plan['chats'] == 99999 ? 'Unlimited' : number_format($plan['chats']) }}
                    </span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-gray-500">Active Tenants</span>
                    <span class="font-semibold text-indigo-600">{{ $plan['tenants'] }}</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-gray-500">Monthly Revenue</span>
                    <span class="font-semibold text-green-600">
                        ₹{{ number_format($plan['price'] * $plan['tenants']) }}
                    </span>
                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-gray-50 rounded-lg px-3 py-2 text-center">
                <span class="text-xs text-gray-400">
                    {{ $plan['tenants'] }} {{ $plan['tenants'] == 1 ? 'tenant' : 'tenants' }} on this plan
                </span>
            </div>

        </div>
        @endforeach
    </div>

    {{-- Revenue Summary --}}
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl p-5 md:p-6 shadow-lg">
        <h3 class="text-xs font-semibold text-indigo-200 uppercase tracking-wider mb-4">Revenue Summary</h3>
        <div class="grid grid-cols-3 gap-3 md:gap-6 text-center">
            <div class="bg-white bg-opacity-10 rounded-xl p-3 md:p-4">
                <div class="text-lg md:text-3xl font-bold truncate">
                    ₹{{ number_format(collect($plans)->sum(fn($p) => $p['price'] * $p['tenants'])) }}
                </div>
                <div class="text-indigo-200 text-xs mt-1">Total MRR</div>
            </div>
            <div class="bg-white bg-opacity-10 rounded-xl p-3 md:p-4">
                <div class="text-lg md:text-3xl font-bold">
                    {{ collect($plans)->sum('tenants') }}
                </div>
                <div class="text-indigo-200 text-xs mt-1">Total Tenants</div>
            </div>
            <div class="bg-white bg-opacity-10 rounded-xl p-3 md:p-4">
                <div class="text-lg md:text-3xl font-bold truncate">
                    ₹{{ number_format(collect($plans)->sum(fn($p) => $p['price'] * $p['tenants']) * 12) }}
                </div>
                <div class="text-indigo-200 text-xs mt-1">Annual (ARR)</div>
            </div>
        </div>
    </div>

</div>
@endsection