@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto py-10 px-4">
  <h1 class="text-2xl font-bold text-gray-900 mb-8">Billing & Subscription</h1>

  @if(session('success'))
  <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm">{{ session('success') }}</div>
  @endif
  @if(session('error'))
  <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm">{{ session('error') }}</div>
  @endif

  {{-- Current Plan --}}
  <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-6">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm text-gray-500">Current plan</p>
        <p class="text-2xl font-bold text-gray-900 capitalize mt-1">{{ $tenant->plan ?? 'None' }}</p>
        <p class="text-sm mt-1">
          Status:
          <span class="font-semibold {{ $tenant->is_active ? 'text-green-600' : 'text-red-500' }}">
            {{ $tenant->is_active ? 'Active' : 'Inactive' }}
          </span>
        </p>
        @if($tenant->subscription_ends_at)
        <p class="text-xs text-gray-400 mt-1">Cancels on {{ \Carbon\Carbon::parse($tenant->subscription_ends_at)->format('M d, Y') }}</p>
        @endif
      </div>
      <div class="flex flex-col gap-2">
        <a href="{{ route('billing.pricing') }}" class="px-5 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition text-center">
          {{ $tenant->is_active ? 'Change Plan' : 'Subscribe' }}
        </a>
        @if($tenant->is_active && !$tenant->subscription_ends_at)
        <form action="{{ route('billing.cancel') }}" method="POST" onsubmit="return confirm('Cancel subscription?')">
          @csrf
          <button class="w-full px-5 py-2 border border-red-300 text-red-600 text-sm font-semibold rounded-xl hover:bg-red-50 transition">Cancel</button>
        </form>
        @endif
      </div>
    </div>
  </div>

  {{-- Plan limits --}}
  @if(isset($plans[$tenant->plan ?? '']))
  @php $currentPlan = $plans[$tenant->plan]; @endphp
  <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-6">
    <h2 class="font-semibold text-gray-800 mb-4">Plan limits</h2>
    <div class="grid grid-cols-2 gap-4">
      <div class="bg-gray-50 rounded-xl p-4">
        <p class="text-xs text-gray-500">Max agents</p>
        <p class="text-2xl font-bold text-gray-900">{{ $currentPlan['agents'] == 999 ? '∞' : $currentPlan['agents'] }}</p>
      </div>
      <div class="bg-gray-50 rounded-xl p-4">
        <p class="text-xs text-gray-500">Chats / month</p>
        <p class="text-2xl font-bold text-gray-900">{{ $currentPlan['chats'] == 999999 ? '∞' : number_format($currentPlan['chats']) }}</p>
      </div>
    </div>
  </div>
  @endif

  {{-- Invoice history --}}
  @if(count($invoices))
  <div class="bg-white border border-gray-200 rounded-2xl p-6">
    <h2 class="font-semibold text-gray-800 mb-4">Invoice history</h2>
    <table class="w-full text-sm">
      <thead><tr class="text-left text-gray-400 border-b border-gray-100">
        <th class="pb-3 font-medium">Date</th>
        <th class="pb-3 font-medium">Amount</th>
        <th class="pb-3 font-medium">Status</th>
        <th class="pb-3 font-medium"></th>
      </tr></thead>
      <tbody>
        @foreach($invoices as $invoice)
        <tr class="border-b border-gray-50">
          <td class="py-3">{{ \Carbon\Carbon::createFromTimestamp($invoice->created)->format('M d, Y') }}</td>
          <td class="py-3">${{ number_format($invoice->amount_paid / 100, 2) }}</td>
          <td class="py-3">
            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
              {{ ucfirst($invoice->status) }}
            </span>
          </td>
          <td class="py-3 text-right">
            @if($invoice->invoice_pdf)
            <a href="{{ $invoice->invoice_pdf }}" target="_blank" class="text-indigo-600 text-xs hover:underline">Download PDF</a>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
@endsection