@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">

    {{-- Top Bar --}}
    <div class="bg-white border-b px-4 md:px-8 py-4 md:py-5 flex items-center justify-between">
        <div>
            <h1 class="text-lg md:text-xl font-bold text-gray-800">Analytics</h1>
            <p class="text-xs md:text-sm text-gray-400 mt-0.5">
                {{ $isOwner ? 'Team performance overview' : 'Your performance overview' }}
            </p>
        </div>
    </div>

    <div class="px-4 md:px-8 py-4 md:py-6 space-y-4 md:space-y-6">

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">

            <div class="bg-white rounded-xl md:rounded-2xl border border-gray-100 shadow-sm p-4 md:p-5">
                <div class="text-xs text-gray-400 mb-1">Total Conversations</div>
                <div class="text-xl md:text-2xl font-bold text-gray-800">{{ $totalConversations }}</div>
                <div class="text-xs text-gray-400 mt-1">{{ $resolvedConversations }} resolved · {{ $openConversations }} open</div>
            </div>

            <div class="bg-white rounded-xl md:rounded-2xl border border-gray-100 shadow-sm p-4 md:p-5">
                <div class="text-xs text-gray-400 mb-1">Avg. First Response</div>
                <div class="text-xl md:text-2xl font-bold text-gray-800">
                    @if($avgResponseMinutes !== null)
                        {{ $avgResponseMinutes < 60 ? round($avgResponseMinutes) . ' min' : round($avgResponseMinutes / 60, 1) . ' hr' }}
                    @else
                        —
                    @endif
                </div>
                <div class="text-xs text-gray-400 mt-1">Time to first agent reply</div>
            </div>

            <div class="bg-white rounded-xl md:rounded-2xl border border-gray-100 shadow-sm p-4 md:p-5">
                <div class="text-xs text-gray-400 mb-1">Avg. Resolution Time</div>
                <div class="text-xl md:text-2xl font-bold text-gray-800">
                    @if($avgResolutionMinutes !== null)
                        {{ $avgResolutionMinutes < 60 ? round($avgResolutionMinutes) . ' min' : round($avgResolutionMinutes / 60, 1) . ' hr' }}
                    @else
                        —
                    @endif
                </div>
                <div class="text-xs text-gray-400 mt-1">Open to resolved</div>
            </div>

            <div class="bg-white rounded-xl md:rounded-2xl border border-gray-100 shadow-sm p-4 md:p-5">
                <div class="text-xs text-gray-400 mb-1">Avg. Rating</div>
                <div class="text-xl md:text-2xl font-bold text-gray-800 flex items-center gap-1">
                    @if($avgRating)
                        {{ number_format($avgRating, 1) }} <span class="text-amber-400 text-base">★</span>
                    @else
                        —
                    @endif
                </div>
                <div class="text-xs text-gray-400 mt-1">{{ $ratingCount }} rating{{ $ratingCount == 1 ? '' : 's' }}</div>
            </div>

        </div>

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">

            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-4 md:p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Conversations — Last 14 Days</h3>
                <canvas id="chatsPerDayChart" height="90"></canvas>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 md:p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Rating Distribution</h3>
                @if($ratingCount > 0)
                    <canvas id="ratingChart" height="140"></canvas>
                @else
                    <div class="text-center text-gray-400 text-sm py-10">No ratings yet</div>
                @endif
            </div>

        </div>

        {{-- CSAT Trend --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 md:p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">CSAT Rating Trend — Last 14 Days</h3>
            @if($ratingCount > 0)
                <canvas id="ratingTrendChart" height="70"></canvas>
            @else
                <div class="text-center text-gray-400 text-sm py-10">No ratings yet</div>
            @endif
        </div>

        {{-- Per-Agent Table (Owner only) --}}
        @if($isOwner)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <h3 class="text-sm font-semibold text-gray-700 px-5 py-4 border-b">Performance by Agent</h3>
            @if($chatsPerAgent->count() > 0)
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase text-left">
                        <th class="px-5 py-2 font-medium">Agent</th>
                        <th class="px-5 py-2 font-medium">Total Chats</th>
                        <th class="px-5 py-2 font-medium">Resolved</th>
                        <th class="px-5 py-2 font-medium">Avg. Rating</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($chatsPerAgent as $agent)
                    <tr class="border-t">
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $agent['name'] }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $agent['total'] }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $agent['resolved'] }}</td>
                        <td class="px-5 py-3 text-gray-600">
                            @if($agent['avg_rating'])
                                {{ number_format($agent['avg_rating'], 1) }} <span class="text-amber-400">★</span>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="text-center text-gray-400 text-sm py-10">No agent activity yet</div>
            @endif
        </div>
        @endif

    </div>
</div>

<script>
const chatsPerDayData = @json($chatsPerDay);

new Chart(document.getElementById('chatsPerDayChart'), {
    type: 'bar',
    data: {
        labels: chatsPerDayData.map(d => d.label),
        datasets: [{
            label: 'Conversations',
            data: chatsPerDayData.map(d => d.count),
            backgroundColor: '#6366f1',
            borderRadius: 6,
            maxBarThickness: 28
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } },
            x: { grid: { display: false } }
        }
    }
});

@if($ratingCount > 0)
const ratingData = @json($ratingDistribution);
new Chart(document.getElementById('ratingChart'), {
    type: 'bar',
    data: {
        labels: Object.keys(ratingData).map(k => k + ' ★'),
        datasets: [{
            data: Object.values(ratingData),
            backgroundColor: '#f59e0b',
            borderRadius: 6,
            maxBarThickness: 22
        }]
    },
    options: {
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: {
            x: { beginAtZero: true, ticks: { precision: 0 } }
        }
    }
});
@endif

@if($ratingCount > 0)
const trendData = @json($ratingTrend);
new Chart(document.getElementById('ratingTrendChart'), {
    type: 'line',
    data: {
        labels: trendData.map(d => d.label),
        datasets: [{
            label: 'Avg Rating',
            data: trendData.map(d => d.avg),
            borderColor: '#f59e0b',
            backgroundColor: 'rgba(245, 158, 11, 0.1)',
            fill: true,
            tension: 0.35,
            spanGaps: true,
            pointRadius: trendData.map(d => d.avg === null ? 0 : 3),
            pointBackgroundColor: '#f59e0b'
        }]
    },
    options: {
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        var d = trendData[ctx.dataIndex];
                        return d.avg === null ? 'No ratings' : d.avg + ' ★ (' + d.count + ' rating' + (d.count === 1 ? '' : 's') + ')';
                    }
                }
            }
        },
        scales: {
            y: { min: 0, max: 5, ticks: { stepSize: 1 } },
            x: { grid: { display: false } }
        }
    }
});
@endif
</script>
@endsection
