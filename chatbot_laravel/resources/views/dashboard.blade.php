@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-bold mb-6">Admin Dashboard</h1>

<div class="grid grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded shadow text-center">
        <div class="text-3xl font-bold text-indigo-600">{{ $totalTenants }}</div>
        <div class="text-sm text-gray-500 mt-1">Total Tenants</div>
    </div>
    <div class="bg-white p-4 rounded shadow text-center">
        <div class="text-3xl font-bold text-green-600">{{ $totalAgents }}</div>
        <div class="text-sm text-gray-500 mt-1">Total Agents</div>
    </div>
    <div class="bg-white p-4 rounded shadow text-center">
        <div class="text-3xl font-bold text-blue-600">{{ $totalConversations }}</div>
        <div class="text-sm text-gray-500 mt-1">Total Conversations</div>
    </div>
    <div class="bg-white p-4 rounded shadow text-center">
        <div class="text-3xl font-bold text-orange-600">{{ $activeConversations }}</div>
        <div class="text-sm text-gray-500 mt-1">Active Chats</div>
    </div>
</div>

<div class="bg-white p-6 rounded shadow">
    <h2 class="text-lg font-semibold mb-4">Recent Tenants</h2>
    <p class="text-gray-500 text-sm">Tenant list will appear here...</p>
</div>
@endsection