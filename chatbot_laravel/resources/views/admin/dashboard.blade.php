@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-800">Admin Dashboard</h1>
            <p class="text-sm text-gray-400 mt-0.5">{{ now()->format('l, d M Y') }}</p>
        </div>
        <span class="inline-flex items-center gap-1.5 bg-indigo-50 text-indigo-700 text-xs font-medium px-3 py-1.5 rounded-full border border-indigo-200">
            <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse inline-block"></span>
            Live
        </span>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">

        <div class="bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-xl p-4 md:p-5 text-white shadow-lg relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-white bg-opacity-10 rounded-full"></div>
            <div class="absolute -right-2 -bottom-6 w-16 h-16 bg-white bg-opacity-10 rounded-full"></div>
            <div class="relative">
                <div class="text-indigo-200 text-xs font-medium mb-3">Total Tenants</div>
                <div class="text-3xl font-bold mb-1">{{ $totalTenants }}</div>
                <div class="text-indigo-200 text-xs">Companies registered</div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-xl p-4 md:p-5 text-white shadow-lg relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-white bg-opacity-10 rounded-full"></div>
            <div class="absolute -right-2 -bottom-6 w-16 h-16 bg-white bg-opacity-10 rounded-full"></div>
            <div class="relative">
                <div class="text-emerald-200 text-xs font-medium mb-3">Total Agents</div>
                <div class="text-3xl font-bold mb-1">{{ $totalAgents }}</div>
                <div class="text-emerald-200 text-xs">Support members</div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl p-4 md:p-5 text-white shadow-lg relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-white bg-opacity-10 rounded-full"></div>
            <div class="absolute -right-2 -bottom-6 w-16 h-16 bg-white bg-opacity-10 rounded-full"></div>
            <div class="relative">
                <div class="text-blue-200 text-xs font-medium mb-3">Conversations</div>
                <div class="text-3xl font-bold mb-1">{{ $totalConversations }}</div>
                <div class="text-blue-200 text-xs">Total chats</div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-orange-500 to-orange-700 rounded-xl p-4 md:p-5 text-white shadow-lg relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-white bg-opacity-10 rounded-full"></div>
            <div class="absolute -right-2 -bottom-6 w-16 h-16 bg-white bg-opacity-10 rounded-full"></div>
            <div class="relative">
                <div class="text-orange-200 text-xs font-medium mb-3">Active Chats</div>
                <div class="text-3xl font-bold mb-1">{{ $activeConversations }}</div>
                <div class="text-orange-200 text-xs">Currently open</div>
            </div>
        </div>

    </div>

    {{-- Chart + Quick Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- Chart --}}
        <div class="md:col-span-2 bg-white rounded-xl shadow-sm border p-5">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-semibold text-gray-800 text-sm">Conversations Overview</h2>
                <span class="text-xs text-gray-400">Last 7 days</span>
            </div>
            <canvas id="convChart" height="120"></canvas>
        </div>

        {{-- Quick Stats --}}
        <div class="bg-white rounded-xl shadow-sm border p-5 flex flex-col justify-between">
            <h2 class="font-semibold text-gray-800 text-sm mb-4">Quick Stats</h2>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-gray-500">Active Tenants</span>
                        <span class="font-medium text-gray-700">{{ $totalTenants }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-indigo-500 h-2 rounded-full" style="width:100%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-gray-500">Active Chats</span>
                        <span class="font-medium text-gray-700">{{ $activeConversations }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        @if($totalConversations > 0)
                        <div class="bg-orange-400 h-2 rounded-full"
                             style="width:{{ min(($activeConversations/$totalConversations)*100, 100) }}%"></div>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-gray-500">Total Agents</span>
                        <span class="font-medium text-gray-700">{{ $totalAgents }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-emerald-500 h-2 rounded-full" style="width:100%"></div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t grid grid-cols-2 gap-3">
                <a href="{{ route('admin.tenants') }}"
                   class="text-center bg-indigo-50 text-indigo-700 text-xs font-medium py-2.5 rounded-lg hover:bg-indigo-100 transition">
                    View Tenants
                </a>
                <a href="{{ route('admin.revenue') }}"
                   class="text-center bg-emerald-50 text-emerald-700 text-xs font-medium py-2.5 rounded-lg hover:bg-emerald-100 transition">
                    Revenue
                </a>
            </div>
        </div>

    </div>

    {{-- Recent Tenants --}}
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="flex justify-between items-center px-4 md:px-6 py-4 border-b">
            <h2 class="font-semibold text-gray-800 text-sm">Recent Tenants</h2>
            <a href="{{ route('admin.tenants') }}" class="text-xs text-indigo-600 hover:underline font-medium">View all →</a>
        </div>

        {{-- Mobile Cards --}}
        <div class="md:hidden divide-y">
            @foreach($recentTenants as $tenant)
            <div class="p-4">
                <div class="flex justify-between items-start mb-2">
                    <a href="{{ route('admin.tenants.show', $tenant) }}"
                       class="font-medium text-indigo-600 text-sm hover:underline">
                        {{ $tenant->name }}
                    </a>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium ml-2 flex-shrink-0
                        {{ $tenant->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $tenant->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="text-xs text-gray-400 truncate mb-2">{{ $tenant->email }}</div>
                <div class="flex items-center gap-2">
                    <span class="bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full text-xs font-medium">
                        {{ ucfirst($tenant->plan) }}
                    </span>
                    <span class="text-xs text-gray-400">{{ $tenant->created_at->format('d M Y') }}</span>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Desktop Table --}}
        <div class="hidden md:block">
            <table class="w-full text-sm">
                <thead style="background: #f8fafc;" class="border-b">
                    <tr>
                        <th class="text-left px-6 py-3 text-gray-500 font-medium text-xs uppercase tracking-wider">Company</th>
                        <th class="text-left px-6 py-3 text-gray-500 font-medium text-xs uppercase tracking-wider">Email</th>
                        <th class="text-left px-6 py-3 text-gray-500 font-medium text-xs uppercase tracking-wider">Plan</th>
                        <th class="text-left px-6 py-3 text-gray-500 font-medium text-xs uppercase tracking-wider">Status</th>
                        <th class="text-left px-6 py-3 text-gray-500 font-medium text-xs uppercase tracking-wider">Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentTenants as $tenant)
                    <tr class="border-t hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.tenants.show', $tenant) }}"
                               class="font-medium text-indigo-600 hover:underline">
                                {{ $tenant->name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-gray-500 text-xs">{{ $tenant->email }}</td>
                        <td class="px-6 py-4">
                            <span class="bg-indigo-100 text-indigo-700 px-2.5 py-1 rounded-full text-xs font-medium">
                                {{ ucfirst($tenant->plan) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium
                                {{ $tenant->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $tenant->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-400 text-xs">{{ $tenant->created_at->format('d M Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
// Simple conversations chart
var ctx = document.getElementById('convChart');
if (ctx) {
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['6d ago', '5d ago', '4d ago', '3d ago', '2d ago', 'Yesterday', 'Today'],
            datasets: [{
                label: 'Conversations',
                data: [
                    {{ \App\Models\Conversation::whereDate('created_at', now()->subDays(6))->count() }},
                    {{ \App\Models\Conversation::whereDate('created_at', now()->subDays(5))->count() }},
                    {{ \App\Models\Conversation::whereDate('created_at', now()->subDays(4))->count() }},
                    {{ \App\Models\Conversation::whereDate('created_at', now()->subDays(3))->count() }},
                    {{ \App\Models\Conversation::whereDate('created_at', now()->subDays(2))->count() }},
                    {{ \App\Models\Conversation::whereDate('created_at', now()->subDays(1))->count() }},
                    {{ \App\Models\Conversation::whereDate('created_at', now())->count() }},
                ],
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99,102,241,0.08)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#6366f1',
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: '#f1f5f9' } },
                x: { ticks: { font: { size: 11 } }, grid: { display: false } }
            }
        }
    });
}
</script>

@endsection