<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChatBot SaaS</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body{
            background:#07090f;
            overflow-x:hidden;
        }

        .glow{
            position:absolute;
            border-radius:9999px;
            filter:blur(120px);
            opacity:.4;
        }

        .float{
            animation: float 5s ease-in-out infinite;
        }

        @keyframes float{
            0%{transform:translateY(0px);}
            50%{transform:translateY(-15px);}
            100%{transform:translateY(0px);}
        }

        .glass{
            background:rgba(255,255,255,.05);
            backdrop-filter:blur(20px);
            border:1px solid rgba(255,255,255,.1);
        }
    </style>
</head>

<body class="text-white">

<div class="glow bg-blue-500 w-96 h-96 top-0 left-0"></div>
<div class="glow bg-cyan-500 w-96 h-96 bottom-0 right-0"></div>

<!-- NAVBAR -->
<nav class="max-w-7xl mx-auto px-6 py-6 flex items-center justify-between relative z-10">

    <h2 class="text-lg sm:text-3xl font-black">
		ChatBot<span class="text-blue-500">SaaS</span>
	</h2>

    <div class="hidden md:flex gap-8 text-gray-300">
        <a href="#features" class="hover:text-white">Features</a>
        <a href="#pricing" class="hover:text-white">Pricing</a>
        <a href="#testimonials" class="hover:text-white">Testimonials</a>
    </div>

    <div class="flex items-center gap-2 sm:gap-4">

    <a href="{{ route('login') }}"
       class="px-3 py-2 sm:px-6 sm:py-3 text-xs sm:text-sm rounded-xl border border-white/10 hover:border-blue-500 transition">
        Login
    </a>

    <a href="{{ route('register') }}"
       class="px-3 py-2 sm:px-6 sm:py-3 text-xs sm:text-sm rounded-xl bg-blue-600 hover:bg-blue-700 transition">
        Get Started
    </a>

</div>

</nav>

<!-- HERO -->
<section class="min-h-screen flex items-center py-20 lg:py-0">

    <div class="max-w-7xl mx-auto px-5 sm:px-6 w-full">
        <div class="grid lg:grid-cols-2 gap-16 items-center">

        <!-- LEFT -->
        <div class="px-5 lg:px-0 text-center lg:text-left">

           <div class="inline-flex items-center px-4 py-2 rounded-full glass text-xs sm:text-sm text-blue-300 mb-8 mx-auto lg:mx-0">
                🚀 Real-Time Customer Support Platform
            </div>

            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black leading-tight max-w-xl mx-auto lg:mx-0">
                Add Live Chat To
                <span class="text-blue-500">Your Website</span>
                In Minutes
            </h1>

            <p class="text-gray-400 text-base sm:text-lg mt-8 px-0 lg:px-0">
                Convert visitors into customers with a modern live chat widget,
                multi-agent support and powerful analytics.
            </p>

            <div class="flex flex-wrap justify-center lg:justify-start gap-4 mt-10">

                <a href="{{ route('register') }}"
				   class="px-6 sm:px-8 py-3 sm:py-4 bg-blue-600 rounded-2xl text-base sm:text-lg font-semibold hover:bg-blue-700">
					Start Free
				</a>

                <a href="{{ route('billing.pricing') }}"
                   class="px-6 sm:px-8 py-3 sm:py-4 glass rounded-2xl text-base sm:text-lg font-semibold">
					View Pricing
				</a>

            </div>

        </div>

        <!-- RIGHT -->
        <div class="relative">

            <div class="float">

                <div class="glass rounded-3xl p-6 shadow-2xl max-w-lg mx-auto">

                    <div class="flex justify-between items-center mb-6">

                        <div>
                            <h3 class="font-bold text-lg">
                                Live Support
                            </h3>

                            <p class="text-green-400 text-sm">
                                ● Online
                            </p>
                        </div>

                        <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center">
                            💬
                        </div>

                    </div>

                    <div class="space-y-4">

                        <div class="bg-white/5 p-4 rounded-2xl">
                            <p class="text-sm text-gray-400 mb-1">
                                Visitor
                            </p>

                            Hi, do you offer custom plans?
                        </div>

                        <div class="bg-blue-600 p-4 rounded-2xl">
                            <p class="text-sm text-blue-100 mb-1">
                                Support Agent
                            </p>

                            Yes! We can create a custom package for your business.
                        </div>

                    </div>

                </div>

            </div>

        </div> 

    </div> 

</div>

</section>