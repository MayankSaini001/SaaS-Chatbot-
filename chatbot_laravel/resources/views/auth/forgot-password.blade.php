<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - ChatBot SaaS</title>

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
            box-shadow:none!important;
        }
    </style>
</head>

<body class="text-white min-h-screen flex items-center justify-center px-4">

<div class="glow glow-blue"></div>
<div class="glow glow-cyan"></div>

<div class="relative z-10 w-full max-w-md">

    <div class="glass rounded-3xl p-6 md:p-8">

        <div class="text-center mb-8">

            <a href="{{ url('/') }}"
               class="text-blue-400 text-sm hover:text-blue-300">
                ← Back to Home
            </a>

            <h2 class="text-3xl font-black mt-4">
                Forgot Password
            </h2>

            <p class="text-gray-400 mt-2 text-sm">
                Enter your email address and we'll send you a password reset link.
            </p>

        </div>

        @if (session('status'))
            <div class="mb-4 p-3 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 text-sm">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-5">
                <label class="block text-sm text-gray-300 mb-2">
                    Email Address
                </label>

                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    placeholder="you@example.com"
                    class="w-full rounded-xl px-4 py-3"
                >

                @error('email')
                    <p class="text-red-400 text-sm mt-2">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full py-4 rounded-xl bg-blue-600 hover:bg-blue-700 transition font-semibold">
                Send Reset Link →
            </button>

        </form>

        <div class="text-center mt-6">

            <a href="{{ route('login') }}"
               class="text-blue-400 hover:text-blue-300 text-sm">
                Back to Sign In
            </a>

        </div>

    </div>

</div>

</body>
</html>