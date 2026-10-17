<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TradeCompass') }} - @yield('title', 'Forex Trading Journal')</title>

    <!-- Google / Bunny Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        .tabular-nums { font-variant-numeric: tabular-nums; }
    </style>
</head>
<body class="min-h-screen bg-[#080B10] text-slate-100 antialiased font-sans selection:bg-indigo-500 selection:text-white"
      x-data="{ 
          sidebarCollapsed: localStorage.getItem('tc_sidebar_collapsed') === 'true',
          mobileMenuOpen: false,
          toggleSidebar() {
              this.sidebarCollapsed = !this.sidebarCollapsed;
              localStorage.setItem('tc_sidebar_collapsed', this.sidebarCollapsed);
          }
      }">

    <div class="flex min-h-screen">
        <!-- ======================================================== -->
        <!-- DESKTOP COLLAPSIBLE SIDEBAR                              -->
        <!-- ======================================================== -->
        <aside class="hidden lg:flex lg:flex-col h-screen sticky top-0 bg-[#0F141C] border-r border-white/5 flex-shrink-0 transition-all duration-300 z-30"
               :class="sidebarCollapsed ? 'w-20' : 'w-64'">
            
            <!-- Brand / Logo -->
            <div class="p-4 border-b border-white/5 flex items-center justify-between"
                 :class="{ 'justify-center p-3': sidebarCollapsed }">
                <a href="{{ route('trader.dashboard') }}" class="flex items-center gap-3 group">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center border border-indigo-500/30 flex-shrink-0">
                        <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon>
                        </svg>
                    </div>
                    <div x-show="!sidebarCollapsed" class="flex flex-col">
                        <span class="text-sm font-bold tracking-tight text-white flex items-center gap-1.5">
                            TradeCompass
                            <span class="px-1.5 py-0.2 text-[9px] font-mono font-semibold bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 rounded">FX</span>
                        </span>
                        <span class="text-[10px] text-slate-500 font-medium">Trading Journal</span>
                    </div>
                </a>
            </div>

            <!-- Role-based Navigation Links -->
            @include('components.navigation')

            <!-- Desktop Bottom: User Profile & Collapse Toggle -->
            @auth
            <div class="p-3 border-t border-white/5 bg-[#0B0F17]/80">
                <!-- User Profile Summary -->
                <a href="{{ route('profile.show', auth()->user()->username ?? auth()->user()->id) }}" 
                   class="flex items-center gap-3 p-2 rounded-lg bg-white/[0.02] hover:bg-white/[0.06] border border-white/5 transition-all mb-2 group"
                   :class="{ 'justify-center p-1.5': sidebarCollapsed }"
                   title="{{ auth()->user()->name }}">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-[#18202F] border border-white/10 flex items-center justify-center text-slate-200 text-xs font-semibold font-mono">
                        @if(auth()->user()->profile_photo_path)
                            <img src="{{ auth()->user()->getProfilePhotoUrl() }}" class="w-full h-full rounded-full object-cover">
                        @else
                            {{ substr(auth()->user()->name, 0, 1) }}
                        @endif
                    </div>
                    <div x-show="!sidebarCollapsed" class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-white truncate group-hover:text-indigo-400 transition-colors">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-slate-400 font-mono capitalize truncate">{{ auth()->user()->roles->first()?->name ?? 'Trader' }}</p>
                    </div>
                </a>

                <!-- Actions Grid: Logout & Sidebar Toggle -->
                <div class="flex items-center gap-1.5" :class="{ 'flex-col': sidebarCollapsed }">
                    <form method="POST" action="{{ route('logout') }}" class="flex-1 w-full">
                        @csrf
                        <button type="submit" 
                                class="w-full flex items-center justify-center gap-2 px-3 py-1.5 rounded-lg text-xs text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 border border-transparent hover:border-rose-500/20 transition-all font-medium"
                                title="Sign Out">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span x-show="!sidebarCollapsed" class="truncate">Logout</span>
                        </button>
                    </form>

                    <!-- Collapse / Expand Sidebar Toggle -->
                    <button @click="toggleSidebar()" 
                            class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 border border-white/5 transition-colors"
                            :title="sidebarCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'">
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': sidebarCollapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                        </svg>
                    </button>
                </div>
            </div>
            @else
            <div class="p-4 border-t border-white/5">
                <a href="{{ route('login') }}" class="block w-full px-3 py-2 text-xs text-center font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg transition-colors mb-2">
                    Log In
                </a>
                <a href="{{ route('register') }}" class="block w-full px-3 py-2 text-xs text-center font-medium text-slate-300 hover:text-white hover:bg-white/5 border border-white/5 rounded-lg transition-colors">
                    Register
                </a>
            </div>
            @endauth
        </aside>

        <!-- ======================================================== -->
        <!-- MAIN SHELL (TOP BAR + CONTENT CONTAINER)                 -->
        <!-- ======================================================== -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Universal Top Bar -->
            <header class="sticky top-0 z-20 h-14 bg-[#0F141C]/90 backdrop-blur-xl border-b border-white/5 flex items-center justify-between px-4 sm:px-6">
                <!-- Left: Mobile Menu Trigger + Brand for Mobile -->
                <div class="flex items-center gap-3">
                    <button @click="mobileMenuOpen = true" 
                            class="lg:hidden p-2 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <a href="{{ route('trader.dashboard') }}" class="lg:hidden flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-xs">TC</div>
                        <span class="text-sm font-bold text-white tracking-tight">TradeCompass</span>
                    </a>
                </div>

                <!-- Center: Live Forex Market Session Clock -->
                <div class="hidden md:flex items-center justify-center">
                    @include('components.forex-session-clock')
                </div>

                <!-- Right: Active Account Selector + Notifications + User Avatar -->
                <div class="flex items-center gap-2.5 sm:gap-3.5">
                    <!-- Account & Equity Switcher Widget -->
                    @include('components.account-switcher')

                    <!-- Notifications Dropdown -->
                    @include('components.notifications-dropdown')

                    <!-- User Quick Avatar -->
                    @auth
                    <a href="{{ route('profile.show', auth()->user()->username ?? auth()->user()->id) }}" 
                       class="flex items-center gap-2 pl-2 border-l border-slate-800 hover:opacity-90 transition-opacity">
                        <div class="w-8 h-8 rounded-full bg-[#18202F] flex items-center justify-center text-slate-200 text-xs font-semibold font-mono border border-white/10">
                            @if(auth()->user()->profile_photo_path)
                                <img src="{{ auth()->user()->getProfilePhotoUrl() }}" class="w-full h-full rounded-full object-cover">
                            @else
                                {{ substr(auth()->user()->name, 0, 1) }}
                            @endif
                        </div>
                    </a>
                    @endauth
                </div>
            </header>

            <!-- Mobile Drawer Menu -->
            <div x-show="mobileMenuOpen" 
                 x-cloak
                 class="fixed inset-0 z-50 lg:hidden flex"
                 x-transition:enter="transition-opacity ease-linear duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/80 backdrop-blur-sm" @click="mobileMenuOpen = false"></div>

                <!-- Drawer Content -->
                <div class="relative flex-1 flex flex-col max-w-xs w-full bg-[#0F141C] border-r border-white/10 p-4"
                     x-show="mobileMenuOpen"
                     x-transition:enter="transition ease-in-out duration-250 transform"
                     x-transition:enter-start="-translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transition ease-in-out duration-250 transform"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="-translate-x-full">
                    
                    <div class="flex items-center justify-between pb-4 border-b border-white/5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-sm">TC</div>
                            <span class="text-sm font-bold text-white tracking-tight">TradeCompass</span>
                        </div>
                        <button @click="mobileMenuOpen = false" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Mobile Live Session Strip -->
                    <div class="my-3">
                        @include('components.forex-session-clock')
                    </div>

                    <!-- Navigation Links in Drawer -->
                    <div class="flex-1 overflow-y-auto">
                        @include('components.navigation')
                    </div>

                    <!-- Mobile Logout -->
                    @auth
                    <div class="pt-3 border-t border-white/5 mt-auto">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full py-2 px-3 text-xs text-center font-medium text-rose-400 hover:text-white bg-rose-500/10 hover:bg-rose-500/20 rounded-lg border border-rose-500/20 transition-colors">
                                Logout
                            </button>
                        </form>
                    </div>
                    @endauth
                </div>
            </div>

            <!-- Page Workspace Container -->
            <main class="flex-1">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6 pb-28 max-w-7xl">
                    <!-- Success Flash Alert -->
                    @if(session('success'))
                        <div class="bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
                            <div class="w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <p class="text-xs sm:text-sm font-semibold tracking-wide">{{ session('success') }}</p>
                        </div>
                    @endif

                    <!-- Error Flash Alert (Supporting both strings and nested arrays) -->
                    @if(session('error'))
                        <div class="bg-rose-500/10 border border-rose-500/25 text-rose-400 px-4 py-3 rounded-xl mb-6 flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-rose-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                            <div class="text-xs sm:text-sm font-medium">
                                @if(is_array(session('error')))
                                    <ul class="list-disc list-inside space-y-1">
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
                                    <p>{{ session('error') }}</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- View Injection Point -->
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Script Stacks -->
    @stack('scripts')
</body>
</html>
