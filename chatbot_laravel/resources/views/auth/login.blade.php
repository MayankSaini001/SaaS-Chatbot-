<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ChatBot SaaS</title>

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

<body class="text-white min-h-screen">

<div class="glow glow-blue"></div>
<div class="glow glow-cyan"></div>

<div class="relative z-10 min-h-screen grid lg:grid-cols-2">

<div class="hidden lg:flex flex-col justify-center px-20 left-panel">

    <div class="max-w-lg">

        <div class="inline-flex items-center gap-3 mb-8">
            <div class="logo-wrap">

    <div class="logo-glow"></div>

    <div class="logo-3d">

        <svg xmlns="http://www.w3.org/2000/svg"
             class="w-7 h-7 text-white"
             fill="none"
             viewBox="0 0 24 24"
             stroke="currentColor">

            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-4 4v-4z"/>

        </svg>

    </div>

</div>

            <h2 class="text-3xl font-black">
                ChatBot SaaS
            </h2>
        </div>

        <h1 class="text-6xl font-black leading-tight">
            Welcome Back
        </h1>

        <p class="text-gray-400 mt-6 text-lg">
            Manage conversations, agents and customers from one dashboard.
        </p>

        <div class="mt-10 space-y-5">

            <div class="flex items-center gap-3">
                <span class="text-green-400">✓</span>
                <span>Live Customer Chat</span>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-green-400">✓</span>
                <span>Unlimited Conversations</span>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-green-400">✓</span>
                <span>Analytics Dashboard</span>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-green-400">✓</span>
                <span>Multi-Agent Support</span>
            </div>

        </div>

    </div>

</div>

<div class="flex lg:items-center justify-center p-5 lg:p-10 pt-8 lg:pt-10">

    <div class="glass rounded-3xl p-5 md:p-8 w-full max-w-md mt-0 lg:mt-0">
	<div class="lg:hidden text-center mb-5">

		<h2 class="text-2xl font-black">
			ChatBot SaaS
		</h2>

	</div>
        <div class="mb-8">

		<a href="{{ url('/') }}"
		   class="text-blue-400 text-sm hover:text-blue-300">
			← Back to Home
		</a>

		<div class="text-center mt-4">
                Sign In
            </h2>

            <p class="text-gray-400 mt-2">
                Access your workspace
            </p>

        </div>

        @if(session('error'))
        <div class="bg-red-500/10 border border-red-500/20 text-red-400 rounded-xl px-4 py-3 mb-5">
            {{ session('error') }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-4">
                <label class="block text-sm text-gray-300 mb-2">
                    Email Address
                </label>

                <input type="email"
                       name="email"
                       value="{{ old('email') }}"
                       required
                       placeholder="you@example.com"
                       class="w-full rounded-xl px-4 py-3">
            </div>

            <div class="mb-4">

			<label class="block text-sm text-gray-300 mb-2">
				Password
			</label>

			<div class="relative">

				<input
					type="password"
					id="password"
					name="password"
					required
					placeholder="••••••••"
					class="w-full rounded-xl px-4 py-3 pr-12">

				<button
				type="button"
				onclick="togglePassword('password', this)"
				class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white">

				<!-- Eye Icon -->
				<svg xmlns="http://www.w3.org/2000/svg"
					 class="w-5 h-5"
					 fill="none"
					 viewBox="0 0 24 24"
					 stroke="currentColor">
					<path stroke-linecap="round"
						  stroke-linejoin="round"
						  stroke-width="2"
						  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
					<path stroke-linecap="round"
						  stroke-linejoin="round"
						  stroke-width="2"
						  d="M2.458 12C3.732 7.943 7.523 5 12 5
							 c4.478 0 8.268 2.943 9.542 7
							 -1.274 4.057-5.064 7-9.542 7
							 -4.477 0-8.268-2.943-9.542-7z"/>
				</svg>

			</button>

			</div>

		</div>

            <div class="flex justify-between items-center mb-6 text-sm">

                <label class="flex items-center gap-2 text-gray-400">
                    <input type="checkbox" name="remember">
                    Remember me
                </label>

                @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                   class="text-blue-400">
                    Forgot Password?
                </a>
                @endif

            </div>

            <button type="submit"
                    class="w-full py-4 rounded-xl bg-blue-600 hover:bg-blue-700 font-semibold transition">
                Sign In →
            </button>

        </form>

        <p class="text-center text-gray-400 text-sm mt-6">
            Don't have an account?

            <a href="{{ route('register') }}"
               class="text-blue-400 font-medium">
                Create Account
            </a>
        </p>

    </div>

</div>
</div>
<script>
function togglePassword(id, btn)
{
    const input = document.getElementById(id);

    if(input.type === 'password')
    {
        input.type = 'text';

        btn.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg"
             class="w-5 h-5"
             fill="none"
             viewBox="0 0 24 24"
             stroke="currentColor">

            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M3 3l18 18"/>

            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M10.58 10.58A3 3 0 0013.42 13.42"/>

            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9.88 5.09A9.77 9.77 0 0112 5
                     c4.48 0 8.27 2.94 9.54 7
                     a9.78 9.78 0 01-4.04 4.82"/>

            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6.23 6.23A9.77 9.77 0 002.46 12
                     c1.27 4.06 5.06 7 9.54 7
                     1.61 0 3.14-.38 4.5-1.05"/>
        </svg>`;
    }
    else
    {
        input.type = 'password';

        btn.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg"
             class="w-5 h-5"
             fill="none"
             viewBox="0 0 24 24"
             stroke="currentColor">

            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 12a3 3 0 11-6 0
                     3 3 0 016 0z"/>

            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M2.458 12C3.732 7.943
                     7.523 5 12 5
                     c4.478 0 8.268 2.943
                     9.542 7
                     -1.274 4.057
                     -5.064 7
                     -9.542 7
                     -4.477 0
                     -8.268-2.943
                     -9.542-7z"/>
        </svg>`;
    }
}
</script>
</body>
</html>


