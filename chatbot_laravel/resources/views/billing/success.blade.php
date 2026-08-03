@extends('layouts.app')
@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50">
  <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center max-w-md">
    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
      <svg class="w-8 h-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    </div>
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Subscription Activated 🎉</h1>
    <p class="text-gray-500 text-sm mb-8">Thank you for your purchase. Your chatbot subscription is now active and your dashboard is ready to use.</p>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
      <a href="{{ route('billing.dashboard') }}" class="inline-block px-8 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition">View Billing</a>
      <a href="{{ route('agent.dashboard') }}" class="inline-block px-8 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition">Go to Dashboard</a>
    </div>
  </div>
</div>
@endsection