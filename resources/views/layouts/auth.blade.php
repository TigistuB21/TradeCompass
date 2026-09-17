<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TradeCompass') }} - @yield('title', 'Forex Trading Journal')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-950 text-slate-100 antialiased font-sans selection:bg-indigo-500 selection:text-white">
    <div class="flex min-h-screen">
        <!-- Left Side - Enterprise Trading Desk Showcase (Desktop only) -->
        <div class="hidden lg:flex lg:w-1/2 relative flex-col justify-between p-12 bg-slate-900 border-r border-slate-800 overflow-hidden">
            <!-- Subtle background grid pattern -->
            <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 24px 24px;"></div>
            
            <!-- Top Branding Header -->
            <div class="relative z-10 flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-600 flex items-center justify-center shadow-md shadow-indigo-600/30">
                    <!-- Compass / Navigation Icon -->
                    <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon>
                    </svg>
                </div>
                <div>
                    <span class="text-xl font-bold tracking-tight text-white">TradeCompass</span>
                    <span class="ml-2 text-xs font-medium px-2 py-0.5 rounded border border-slate-700 bg-slate-800/80 text-slate-300">PRO</span>
                </div>
            </div>

            <!-- Middle Feature Callouts -->
            <div class="relative z-10 my-auto py-12 max-w-lg">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-indigo-500/20 bg-indigo-500/10 text-indigo-400 text-xs font-medium mb-6">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                    Institutional Trading Intelligence
                </div>
                
                <h1 class="text-4xl font-bold tracking-tight text-white leading-tight mb-4">
                    Precision trade logging for disciplined forex traders.
                </h1>
                
                <p class="text-slate-400 text-base leading-relaxed mb-10">
                    Eliminate emotional bias, track comprehensive execution statistics, and identify your true statistical edge with automated risk analytics.
                </p>

                <div class="space-y-4">
                    <div class="flex items-start gap-4 p-4 rounded-xl border border-slate-800 bg-slate-900/60 backdrop-blur-sm">
                        <div class="w-9 h-9 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <!-- Candlestick / Analytics Icon -->
                            <svg class="w-4 h-4 text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 3v18h18"></path>
                                <path d="m19 9-5 5-4-4-3 3"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-white">Execution Metrics & R:R Breakdown</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Automated calculation of profit factor, Sharpe ratio, and expectancy per setup.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-4 rounded-xl border border-slate-800 bg-slate-900/60 backdrop-blur-sm">
                        <div class="w-9 h-9 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <!-- Shield / Risk Management Icon -->
                            <svg class="w-4 h-4 text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"></path>
                                <path d="m9 12 2 2 4-4"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-white">Capital Protection & Drawdown Controls</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Real-time exposure auditing to guard against overtrading and revenge trades.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Compliance & Trust Footer -->
            <div class="relative z-10 flex items-center justify-between pt-6 border-t border-slate-800 text-xs text-slate-500">
                <span>&copy; {{ date('Y') }} TradeCompass Systems. All rights reserved.</span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Systems Operational
                </span>
            </div>
        </div>

        <!-- Right Side - Authentication Card -->
        <div class="flex-1 flex flex-col justify-center px-4 sm:px-6 lg:px-12 py-12 bg-slate-950">
            <div class="mx-auto w-full max-w-sm sm:max-w-md">
                <!-- Mobile Branding (Hidden on lg+) -->
                <div class="lg:hidden flex items-center justify-center space-x-2.5 mb-8">
                    <div class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center shadow-md shadow-indigo-600/30">
                        <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon>
                        </svg>
                    </div>
                    <span class="text-xl font-bold tracking-tight text-white">TradeCompass</span>
                </div>

                <!-- Main Form Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl shadow-black/40 p-6 sm:p-8">
                    @yield('content')
                </div>

                <!-- Footer Links -->
                <div class="mt-6 text-center text-xs sm:text-sm text-slate-400">
                    @yield('footer')
                </div>
            </div>
        </div>
    </div>
</body>
</html>
