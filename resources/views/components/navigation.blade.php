<nav class="flex-1 px-2.5 py-4 space-y-6 overflow-y-auto overflow-x-hidden scrollbar-thin scrollbar-thumb-slate-800">
    @php
        $user = auth()->user();
        $isAdmin = $user && $user->hasRole('admin');
        $isTrader = $user && $user->hasRole('trader');
        $isAnalyst = $user && $user->hasRole('analyst');
    @endphp

    {{-- ========================================== --}}
    {{-- TRADER NAVIGATION                          --}}
    {{-- ========================================== --}}
    @if($isTrader)
        {{-- Section 1: Core Journaling & Logging --}}
        <div class="space-y-1">
            <div x-show="!sidebarCollapsed" class="px-3 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                Core Trading
            </div>

            <!-- New Trade CTA Button -->
            <a href="{{ route('trader.trades.create') }}" 
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-2 font-medium transition-all duration-150 {{ request()->routeIs('trader.trades.create') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'bg-indigo-600/15 hover:bg-indigo-600/25 border border-indigo-500/30 text-indigo-300 hover:text-white' }}"
               :class="{ 'justify-center px-2': sidebarCollapsed }"
               title="Log New Trade">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="text-xs font-semibold tracking-wide truncate">Log New Trade</span>
            </a>

            <!-- Dashboard -->
            <a href="{{ route('trader.dashboard') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('trader.dashboard') ? 'bg-[#1E2533] text-white border-l-2 border-indigo-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }}"
               :class="{ 'justify-center px-2': sidebarCollapsed }"
               title="Dashboard">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Dashboard</span>
            </a>

            <!-- Trade History / Journal -->
            <a href="{{ route('trader.trades.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('trader.trades.index') || request()->routeIs('trader.trades.show') || request()->routeIs('trader.trades.edit') ? 'bg-[#1E2533] text-white border-l-2 border-indigo-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }}"
               :class="{ 'justify-center px-2': sidebarCollapsed }"
               title="Trade History">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Trade History</span>
            </a>

            <!-- Analytics -->
            <a href="{{ route('trader.analytics.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('trader.analytics.*') ? 'bg-[#1E2533] text-white border-l-2 border-indigo-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }}"
               :class="{ 'justify-center px-2': sidebarCollapsed }"
               title="Analytics">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Analytics</span>
            </a>
        </div>

        {{-- Section 2: Strategy & Tools --}}
        <div class="space-y-1">
            <div x-show="!sidebarCollapsed" class="px-3 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                Strategy & Execution
            </div>

            <!-- Accounts -->
            <a href="{{ route('trader.accounts.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('trader.accounts.*') ? 'bg-[#1E2533] text-white border-l-2 border-indigo-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }}"
               :class="{ 'justify-center px-2': sidebarCollapsed }"
               title="Trade Accounts">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Accounts</span>
            </a>

            <!-- Playbook (Strategies) -->
            <a href="{{ route('trader.strategies.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('trader.strategies.*') ? 'bg-[#1E2533] text-white border-l-2 border-indigo-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }}"
               :class="{ 'justify-center px-2': sidebarCollapsed }"
               title="Playbook & Strategies">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Playbook</span>
            </a>

            <!-- Tools -->
            <a href="{{ route('trader.tools.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('trader.tools.*') ? 'bg-[#1E2533] text-white border-l-2 border-indigo-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }}"
               :class="{ 'justify-center px-2': sidebarCollapsed }"
               title="Tools & Calculators">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Tools</span>
            </a>
        </div>

        {{-- Section 3: Mentorship & Network --}}
        <div class="space-y-1">
            <div x-show="!sidebarCollapsed" class="px-3 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                Community & Network
            </div>

            <!-- Messages -->
            <a href="{{ route('conversations.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('conversations.*') ? 'bg-[#1E2533] text-white border-l-2 border-indigo-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }} relative"
               :class="{ 'justify-center px-2': sidebarCollapsed }"
               title="Messages">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Messages</span>
                @php
                    $messageUnreadCount = \App\Models\Conversation::where('analyst_id', auth()->id())
                        ->orWhere('trader_id', auth()->id())
                        ->get()
                        ->sum(function($conversation) {
                            return $conversation->messages()->where('sender_id', '!=', auth()->id())->where('is_read', false)->count();
                        });
                @endphp
                @if($messageUnreadCount > 0)
                    <span x-show="!sidebarCollapsed" class="ml-auto min-w-[18px] h-4 px-1 bg-sky-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                        {{ $messageUnreadCount > 9 ? '9+' : $messageUnreadCount }}
                    </span>
                    <span x-show="sidebarCollapsed" class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-sky-500 ring-2 ring-slate-900"></span>
                @endif
            </a>

            <!-- Find Analyst -->
            <a href="{{ route('analysts.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('analysts.*') || request()->routeIs('subscription.*') ? 'bg-[#1E2533] text-white border-l-2 border-indigo-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }}"
               :class="{ 'justify-center px-2': sidebarCollapsed }"
               title="Find Analyst">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Find Analyst</span>
            </a>

            <!-- Feedback -->
            <a href="{{ route('trader.feedback.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('trader.feedback.*') ? 'bg-[#1E2533] text-white border-l-2 border-indigo-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }}"
               :class="{ 'justify-center px-2': sidebarCollapsed }"
               title="Feedback">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Feedback</span>
            </a>

            <!-- Leaderboard -->
            <a href="{{ route('trader.leaderboard.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('trader.leaderboard.*') ? 'bg-[#1E2533] text-white border-l-2 border-indigo-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }}"
               :class="{ 'justify-center px-2': sidebarCollapsed }"
               title="Leaderboard">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Leaderboard</span>
            </a>

            <!-- Achievements -->
            <a href="{{ route('trader.achievements.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('trader.achievements.*') ? 'bg-[#1E2533] text-white border-l-2 border-indigo-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }}"
               :class="{ 'justify-center px-2': sidebarCollapsed }"
               title="Achievements">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Achievements</span>
            </a>

            <!-- My Subscriptions -->
            <a href="{{ route('trader.subscriptions.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('trader.subscriptions.*') ? 'bg-[#1E2533] text-white border-l-2 border-indigo-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }}"
               :class="{ 'justify-center px-2': sidebarCollapsed }"
               title="Subscriptions">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Subscriptions</span>
            </a>

            <!-- Disputes -->
            <a href="{{ route('trader.disputes.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('trader.disputes.*') ? 'bg-[#1E2533] text-white border-l-2 border-indigo-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }}"
               :class="{ 'justify-center px-2': sidebarCollapsed }"
               title="Disputes">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Disputes</span>
            </a>
        </div>
    @endif

    {{-- ========================================== --}}
    {{-- ADMIN NAVIGATION                           --}}
    {{-- ========================================== --}}
    @if($isAdmin)
        <div class="space-y-1">
            <div x-show="!sidebarCollapsed" class="px-3 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                Administration
            </div>

            <a href="{{ route('admin.dashboard') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('admin.dashboard') ? 'bg-[#1E2533] text-white border-l-2 border-emerald-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }}"
               :class="{ 'justify-center px-2': sidebarCollapsed }" title="Admin Dashboard">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span x-show="!sidebarCollapsed" class="truncate">Dashboard</span>
            </a>

            <a href="{{ route('admin.users.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('admin.users.*') ? 'bg-[#1E2533] text-white border-l-2 border-emerald-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }}"
               :class="{ 'justify-center px-2': sidebarCollapsed }" title="User Management">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span x-show="!sidebarCollapsed" class="truncate">User Management</span>
            </a>

            <a href="{{ route('admin.analyst-applications.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('admin.analyst-applications.*') ? 'bg-[#1E2533] text-white border-l-2 border-emerald-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }}"
               :class="{ 'justify-center px-2': sidebarCollapsed }" title="Analyst Applications">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span x-show="!sidebarCollapsed" class="truncate">Applications</span>
            </a>

            <a href="{{ route('admin.subscriptions.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('admin.subscriptions.*') ? 'bg-[#1E2533] text-white border-l-2 border-emerald-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }}"
               :class="{ 'justify-center px-2': sidebarCollapsed }" title="Subscriptions">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span x-show="!sidebarCollapsed" class="truncate">Subscriptions</span>
            </a>

            <a href="{{ route('admin.disputes.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('admin.disputes.*') ? 'bg-[#1E2533] text-white border-l-2 border-emerald-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }}"
               :class="{ 'justify-center px-2': sidebarCollapsed }" title="Disputes">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span x-show="!sidebarCollapsed" class="truncate">Disputes</span>
            </a>

            <a href="{{ route('admin.analytics.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('admin.analytics.*') ? 'bg-[#1E2533] text-white border-l-2 border-emerald-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }}"
               :class="{ 'justify-center px-2': sidebarCollapsed }" title="Analytics">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span x-show="!sidebarCollapsed" class="truncate">Analytics</span>
            </a>

            <a href="{{ route('admin.activity-logs.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('admin.activity-logs.*') ? 'bg-[#1E2533] text-white border-l-2 border-emerald-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }}"
               :class="{ 'justify-center px-2': sidebarCollapsed }" title="Activity Logs">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span x-show="!sidebarCollapsed" class="truncate">Activity Logs</span>
            </a>

            <a href="{{ route('admin.backups.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('admin.backups.*') ? 'bg-[#1E2533] text-white border-l-2 border-emerald-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }}"
               :class="{ 'justify-center px-2': sidebarCollapsed }" title="Backups">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                <span x-show="!sidebarCollapsed" class="truncate">Backups</span>
            </a>

            <a href="{{ route('admin.settings.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('admin.settings.*') ? 'bg-[#1E2533] text-white border-l-2 border-emerald-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }}"
               :class="{ 'justify-center px-2': sidebarCollapsed }" title="Settings">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span x-show="!sidebarCollapsed" class="truncate">Settings</span>
            </a>
        </div>
    @endif

    {{-- ========================================== --}}
    {{-- ANALYST NAVIGATION                         --}}
    {{-- ========================================== --}}
    @if($isAnalyst)
        <div class="space-y-1">
            <div x-show="!sidebarCollapsed" class="px-3 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                Analyst Portal
            </div>

            <a href="{{ route('analyst.dashboard') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('analyst.dashboard') ? 'bg-[#1E2533] text-white border-l-2 border-emerald-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }}"
               :class="{ 'justify-center px-2': sidebarCollapsed }" title="Analyst Dashboard">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span x-show="!sidebarCollapsed" class="truncate">Dashboard</span>
            </a>

            <a href="{{ route('analyst.revenue') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('analyst.revenue') ? 'bg-[#1E2533] text-white border-l-2 border-emerald-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }}"
               :class="{ 'justify-center px-2': sidebarCollapsed }" title="Revenue">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span x-show="!sidebarCollapsed" class="truncate">Revenue</span>
            </a>

            <a href="{{ route('conversations.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('conversations.*') ? 'bg-[#1E2533] text-white border-l-2 border-emerald-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }} relative"
               :class="{ 'justify-center px-2': sidebarCollapsed }" title="Messages">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                <span x-show="!sidebarCollapsed" class="truncate">Messages</span>
                @php
                    $analystMessageUnreadCount = \App\Models\Conversation::where('analyst_id', auth()->id())
                        ->orWhere('trader_id', auth()->id())
                        ->get()
                        ->sum(function($conversation) {
                            return $conversation->messages()->where('sender_id', '!=', auth()->id())->where('is_read', false)->count();
                        });
                @endphp
                @if($analystMessageUnreadCount > 0)
                    <span x-show="!sidebarCollapsed" class="ml-auto min-w-[18px] h-4 px-1 bg-sky-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                        {{ $analystMessageUnreadCount > 9 ? '9+' : $analystMessageUnreadCount }}
                    </span>
                    <span x-show="sidebarCollapsed" class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-sky-500 ring-2 ring-slate-900"></span>
                @endif
            </a>
        </div>
    @endif

    {{-- Section: Profile & Notifications Links (Universal) --}}
    @auth
        <div class="space-y-1 pt-2 border-t border-slate-800/60">
            <a href="{{ route('notifications.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('notifications.*') ? 'bg-[#1E2533] text-white border-l-2 border-indigo-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }} relative"
               :class="{ 'justify-center px-2': sidebarCollapsed }"
               title="Notifications">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Notifications</span>
                @php
                    $sidebarUnreadCount = \App\Models\Notification::where('user_id', auth()->id())->whereNull('read_at')->count();
                @endphp
                @if($sidebarUnreadCount > 0)
                    <span x-show="!sidebarCollapsed" class="ml-auto min-w-[18px] h-4 px-1 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                        {{ $sidebarUnreadCount > 9 ? '9+' : $sidebarUnreadCount }}
                    </span>
                    <span x-show="sidebarCollapsed" class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-red-500 ring-2 ring-slate-900"></span>
                @endif
            </a>

            <a href="{{ route('profile.show', $user->username ?? $user->id) }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('profile.show') ? 'bg-[#1E2533] text-white border-l-2 border-indigo-500 shadow-sm font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#161D27] font-medium' }}"
               :class="{ 'justify-center px-2': sidebarCollapsed }"
               title="My Profile">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">My Profile</span>
            </a>
        </div>
    @endauth
</nav>
