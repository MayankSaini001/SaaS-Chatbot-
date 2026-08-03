@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto">

    {{-- Profile Photo --}}
    <div class="bg-white rounded-xl shadow-sm border p-8 mb-6">
        <div class="text-center">
            <div class="w-20 h-20 rounded-full mx-auto mb-4 flex items-center justify-center overflow-hidden"
                 style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                @if(auth()->user()->avatar)
                    <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                @else
                    <span class="text-white text-2xl font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                @endif
            </div>
            <h2 class="font-semibold text-gray-800">{{ auth()->user()->name }}</h2>
            <p class="text-xs text-gray-400 mt-0.5">{{ ucfirst(auth()->user()->role) }}</p>

            @if(session('success'))
            <div class="mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-2.5 rounded-lg text-xs font-medium">
                ✓ {{ session('success') }}
            </div>
            @endif

            <form method="POST" action="{{ route('agent.profile.avatar') }}" enctype="multipart/form-data" class="mt-5">
                @csrf
                <label class="block cursor-pointer">
                    <span class="inline-block px-4 py-2 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-600 hover:bg-indigo-100">
                        📷 Choose Photo
                    </span>
                    <input type="file" name="avatar" accept="image/png,image/jpeg,image/webp" class="hidden"
                           onchange="this.form.querySelector('button').disabled=false; this.form.querySelector('#avatar-filename').textContent=this.files[0]?.name || '';">
                </label>
                <div id="avatar-filename" class="text-xs text-gray-400 mt-2"></div>
                <button type="submit" disabled
                    class="mt-3 w-full bg-indigo-600 text-white py-2 rounded-lg text-xs font-semibold hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed transition">
                    Upload Photo
                </button>
            </form>

            @if(auth()->user()->avatar)
            <form method="POST" action="{{ route('agent.profile.avatar.remove') }}" class="mt-2"
                  onsubmit="return confirm('Remove your profile photo?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs text-red-500 hover:text-red-700">Remove photo</button>
            </form>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-8">
        <div class="text-center mb-6">
            <div class="w-14 h-14 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-800">Change Password</h1>
            <p class="text-sm text-gray-500 mt-1">Update your account password</p>
        </div>

        @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm font-medium">
            ✗ {{ session('error') }}
        </div>
        @endif

        <form method="POST" action="{{ route('agent.password.update') }}">
            @csrf

            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Current Password</label>
                <input type="password" name="current_password" required
                    class="w-full border rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 @error('current_password') border-red-400 @enderror">
                @error('current_password')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">New Password</label>
                <input type="password" name="password" required minlength="6"
                    class="w-full border rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 @error('password') border-red-400 @enderror">
                @error('password')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Confirm New Password</label>
                <input type="password" name="password_confirmation" required minlength="6"
                    class="w-full border rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>

            <button type="submit"
                class="w-full bg-indigo-600 text-white py-2.5 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                Update Password
            </button>
        </form>

        <div class="mt-4 text-center">
            <a href="{{ route('agent.dashboard') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back to Dashboard</a>
        </div>
    </div>
</div>
@endsection