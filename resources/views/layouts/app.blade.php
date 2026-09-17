<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TradeCompass') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Vite -->
    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-slate-100 antialiased font-sans">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="hidden lg:flex lg:flex-col w-64 h-screen sticky top-0 bg-slate-900/50 backdrop-blur-xl border-r border-slate-800/50 flex-shrink-0">
            <!-- Logo -->
            <div class="p-6 border-b border-slate-800/50">
                <a href="{{ route('trader.dashboard') }}" class="flex items-center gap-3 group">
                    <div class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center shadow-md shadow-indigo-600/30 group-hover:bg-indigo-500 transition-colors">
                        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon>
                        </svg>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-lg font-bold tracking-tight text-white">TradeCompass</span>
                    </div>
                </a>
            </div>

            <!-- Navigation -->
            @include('components.navigation')

            <!-- User Profile -->
            @auth
            <div class="mt-auto p-4 border-t border-slate-800/50">
                <a href="{{ route('profile.show', auth()->user()->username ?? auth()->user()->id) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg bg-slate-800/30 hover:bg-slate-800/80 transition-colors mb-2 group">
                    <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-full flex items-center justify-center text-white font-semibold border-2 border-transparent group-hover:border-slate-600 transition-colors">
                        @if(auth()->user()->profile_photo_path)
                             <img src="{{ auth()->user()->getProfilePhotoUrl() }}" class="w-full h-full rounded-full object-cover">
                        @else
                             {{ substr(auth()->user()->name, 0, 1) }}
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate group-hover:text-blue-400 transition-colors">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ auth()->user()->roles->first()?->name ?? 'User' }}</p>
                    </div>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 text-sm text-left text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-lg transition-colors">
                        Logout
                    </button>
                </form>
            </div>
            @else
            <div class="mt-auto p-4 border-t border-slate-800/50">
                <a href="{{ route('login') }}" class="block w-full px-4 py-2 text-sm text-center text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors mb-2">
                    Login
                </a>
                <a href="{{ route('register') }}" class="block w-full px-4 py-2 text-sm text-center text-slate-400 hover:text-white hover:bg-slate-800/50 rounded-lg transition-colors">
                    Register
                </a>
            </div>
            @endauth
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Mobile Header -->
            <header class="lg:hidden sticky top-0 z-10 bg-slate-900/95 backdrop-blur-xl border-b border-slate-800/50 flex-shrink-0">
                <div class="flex items-center justify-between px-4 py-3">
                    <a href="{{ route('trader.dashboard') }}" class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center shadow-sm">
                            <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon>
                            </svg>
                        </div>
                        <span class="text-base font-bold tracking-tight text-white">TradeCompass</span>
                    </a>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1">
                <div class="container mx-auto px-4 py-8 pb-32 max-w-7xl">
                    @if(session('success'))
                        <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-lg mb-6 flex items-center gap-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <p class="font-medium">{{ session('success') }}</p>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-lg mb-6 flex items-center gap-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            @if(is_array(session('error')))
                                <ul class="list-disc list-inside">
                                    @foreach(session('error') as $err)
                                        @if(is_array($err))
                                            @foreach($err as $subErr)
                                                <li>{{ $subErr }}</li>
                                            @endforeach
                                        @else
                                            <li>{{ $err }}</li>
                                        @endif
                                    @endforeach
                                </ul>
                            @else
                                <p class="font-medium">{{ session('error') }}</p>
                            @endif
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
