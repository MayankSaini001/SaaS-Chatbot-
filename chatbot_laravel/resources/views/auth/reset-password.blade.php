<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - ChatBot SaaS</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body{
            background:#07090f;
            overflow-x:hidden;
        }

        .glow{
            position:fixed;
            border-radius:9999px;
            filter:blur(120px);
            opacity:.25;
            z-index:0;
        }

        .glow-blue{
            width:500px;
            height:500px;
            background:#2563eb;
            top:-150px;
            left:-150px;
        }

        .glow-cyan{
            width:500px;
            height:500px;
            background:#06b6d4;
            bottom:-150px;
            right:-150px;
        }

        .glass{
            background:rgba(255,255,255,.05);
            backdrop-filter:blur(20px);
            border:1px solid rgba(255,255,255,.08);
        }

        input{
            background:rgba(255,255,255,.04)!important;
            border:1px solid rgba(255,255,255,.08)!important;
            color:white!important;
        }

        input::placeholder{
            color:#6b7280;
        }

        input:focus{
            outline:none!important;
            border-color:#3b82f6!important;
        }
    </style>
</head>

<body class="text-white min-h-screen flex items-center justify-center px-5">

<div class="glow glow-blue"></div>
<div class="glow glow-cyan"></div>

<div class="relative z-10 w-full max-w-md">

    <div class="glass rounded-3xl p-8">

        <div class="text-center mb-8">

            <a href="{{ url('/') }}"
               class="text-blue-400 text-sm">
                ← Back to Home
            </a>

            <h1 class="text-3xl font-black mt-4">
                Reset Password
            </h1>

            <p class="text-gray-400 mt-2">
                Create a new secure password
            </p>

        </div>

        <form method="POST" action="{{ route('password.store') }}">
            @csrf

            <input type="hidden"
                   name="token"
                   value="{{ $request->route('token') }}">

            <div class="mb-4">
                <label class="block text-sm text-gray-300 mb-2">
                    Email Address
                </label>

                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $request->email) }}"
                    required
                    class="w-full rounded-xl px-4 py-3">

                @error('email')
                    <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm text-gray-300 mb-2">
                    New Password
                </label>

                <input
                    type="password"
                    name="password"
                    required
                    class="w-full rounded-xl px-4 py-3">

                @error('password')
                    <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm text-gray-300 mb-2">
                    Confirm Password
                </label>

                <input
                    type="password"
                    name="password_confirmation"
                    required
                    class="w-full rounded-xl px-4 py-3">

                @error('password_confirmation')
                    <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full py-4 rounded-xl bg-blue-600 hover:bg-blue-700 font-semibold transition">
                Reset Password →
            </button>

        </form>

    </div>

</div>

</body>
</html>