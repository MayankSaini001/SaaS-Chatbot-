@extends('layouts.app')
@section('content')
<div class="min-h-screen bg-gray-50 py-16 px-4">
  <div class="max-w-5xl mx-auto">
    <h1 class="text-4xl font-bold text-center text-gray-900 mb-2">Simple, transparent pricing</h1>
    <p class="text-center text-gray-500 mb-12">No hidden fees. Cancel anytime.</p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      @foreach($plans as $key => $plan)
      <div class="bg-white rounded-2xl border {{ $key === 'pro' ? 'border-indigo-500 ring-2 ring-indigo-500' : 'border-gray-200' }} p-8 flex flex-col relative">
        @if($key === 'pro')
        <span class="absolute -top-4 left-1/2 -translate-x-1/2 bg-indigo-500 text-white text-xs font-semibold px-4 py-1 rounded-full">Most Popular</span>
        @endif
        <h2 class="text-xl font-bold text-gray-900">{{ $plan['name'] }}</h2>
        <p class="text-4xl font-bold text-gray-900 mt-4">{{ $plan['price'] }}</p>
        <ul class="mt-6 space-y-3 flex-1">
          @foreach($plan['features'] as $f)
          <li class="flex items-center gap-2 text-sm text-gray-600">
            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            {{ $f }}
          </li>
          @endforeach
        </ul>
        @auth
        <form action="{{ route('billing.checkout') }}" method="POST" class="mt-8">
          @csrf
          <input type="hidden" name="plan" value="{{ $key }}">
          <button type="submit" class="w-full py-3 rounded-xl font-semibold text-sm {{ $key === 'pro' ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }} transition">
            Get {{ $plan['name'] }}
          </button>
        </form>
        @else
        <a href="{{ route('register') }}" class="mt-8 block w-full py-3 rounded-xl font-semibold text-sm text-center {{ $key === 'pro' ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }} transition">
          Get Started
        </a>
        @endauth
      </div>
      @endforeach
    </div>
  </div>
</div>
@endsection