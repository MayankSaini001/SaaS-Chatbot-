<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Workspace - ChatBot SaaS</title>

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

        @media(max-width:1024px){
			.left-panel{
				display:none !important;
			}
		}
    </style>
</head>

<body class="text-white min-h-screen">

<div class="glow glow-blue"></div>
<div class="glow glow-cyan"></div>

<div class="relative z-10 min-h-screen grid lg:grid-cols-2">

    <!-- LEFT SIDE -->

    <div class="flex flex-col justify-center px-5 lg:px-20 left-panel">

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

                <div>
                    <h2 class="text-3xl font-black">
                        ChatBot SaaS
                    </h2>
                </div>

            </div>

            <h1 class="text-6xl font-black leading-tight">
                Turn Visitors Into Customers
            </h1>

            <p class="text-gray-400 mt-6 text-lg">
                Powerful live chat platform designed for agencies,
                startups and growing businesses.
            </p>

            <div class="mt-10 space-y-5">

                <div class="flex items-center gap-3">
                    <span class="text-green-400">✓</span>
                    <span>Real-Time Live Chat</span>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-green-400">✓</span>
                    <span>Multi Agent Support</span>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-green-400">✓</span>
                    <span>Analytics Dashboard</span>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-green-400">✓</span>
                    <span>Easy Website Integration</span>
                </div>

            </div>

        </div>

    </div>

    <!-- RIGHT SIDE -->

    <div class="flex items-center justify-center p-5 lg:p-10">

        <div class="glass rounded-3xl p-5 sm:p-6 md:p-8 w-full max-w-md mx-auto">

            <div class="text-center mb-8">

			<a href="{{ url('/') }}"
			   class="text-blue-400 text-sm">
				← Back to Home
			</a>

			<h2 class="text-3xl font-black mt-4">
				Create Workspace
			</h2>

			<p class="text-gray-400 mt-2">
				Launch your live chat platform in minutes
			</p>

		</div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm text-gray-300 mb-2">
                        Full Name
                    </label>

                    <input type="text"
                           name="name"
                           value="{{ old('name') }}"
                           required
                           placeholder="John Doe"
                           class="w-full rounded-xl px-4 py-3">

                    @error('name')
                        <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm text-gray-300 mb-2">
                        Company Name
                    </label>

                    <input type="text"
                           name="company_name"
                           value="{{ old('company_name') }}"
                           required
                           placeholder="Acme Inc."
                           class="w-full rounded-xl px-4 py-3">

                    @error('company_name')
                        <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm text-gray-300 mb-2">
                        Email Address
                    </label>

                    <input type="email"
                           name="email"
                           value="{{ old('email') }}"
                           required
                           placeholder="you@company.com"
                           class="w-full rounded-xl px-4 py-3">

                    @error('email')
                        <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                    @enderror
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
						placeholder="Minimum 8 characters"
						class="w-full rounded-xl px-4 py-3 pr-12">

			        <button
					type="button"
					onclick="togglePassword('password', this)"
					class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white">

					<svg class="w-5 h-5 eye-open"
						 fill="none"
						 stroke="currentColor"
						 viewBox="0 0 24 24">
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

                    @error('password')
                        <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                    @enderror
					 </div>
                </div>

                <div class="mb-6">
				<label class="block text-sm text-gray-300 mb-2">
					Confirm Password
				</label>

				<div class="relative">

					<input
						type="password"
						id="confirm_password"
						name="password_confirmation"
						required
						placeholder="••••••••"
						class="w-full rounded-xl px-4 py-3 pr-12">

					<button
						type="button"
						onclick="togglePassword('confirm_password', this)"
						class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white">

						<svg class="w-5 h-5"
							 fill="none"
							 stroke="currentColor"
							 viewBox="0 0 24 24">
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

                <button type="submit"
                        class="w-full py-3 sm:py-4 rounded-xl bg-blue-600 hover:bg-blue-700 font-semibold transition">
                    Create Account →
                </button>

            </form>

            <p class="text-center text-gray-400 text-sm mt-6">
                Already have an account?

                <a href="{{ route('login') }}"
                   class="text-blue-400 font-medium">
                    Sign In
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
        <svg class="w-5 h-5"
             fill="none"
             stroke="currentColor"
             viewBox="0 0 24 24">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13.875 18.825A10.05 10.05 0 0112 19
                     c-4.478 0-8.268-2.943-9.542-7
                     a9.956 9.956 0 012.293-3.95M6.223 6.223
                     A9.953 9.953 0 0112 5c4.478 0
                     8.268 2.943 9.542 7a9.97 9.97 0
                     01-4.132 5.411M15 12a3 3 0
                     11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M3 3l18 18"/>
        </svg>`;
    }
    else
    {
        input.type = 'password';

        btn.innerHTML = `
        <svg class="w-5 h-5"
             fill="none"
             stroke="currentColor"
             viewBox="0 0 24 24">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 12a3 3 0 11-6 0
                     3 3 0 016 0z"/>
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M2.458 12C3.732 7.943
                     7.523 5 12 5c4.478 0
                     8.268 2.943 9.542 7
                     -1.274 4.057-5.064 7
                     -9.542 7-4.477 0
                     -8.268-2.943-9.542-7z"/>
        </svg>`;
    }
}
</script>
</body>
</html>