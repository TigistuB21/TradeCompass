@extends('layouts.app')

@section('title', 'Trader Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Top Bar: Header & Action Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-6 border-b border-slate-800/80">
        <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Active Journaling Session</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">
                Welcome back, {{ auth()->user()->name }}
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Real-time performance metrics, statistical edge, and trade analytics.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('trader.analytics.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-slate-700 bg-slate-800/60 hover:bg-slate-800 hover:border-slate-600 text-slate-200 text-sm font-medium transition-colors shadow-sm">
                <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
                <span>Analytics</span>
            </a>

            <button onclick="openTradeModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold shadow-sm transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-emerald-500/50">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Log Trade</span>
            </button>
        </div>
    </div>

    <!-- Active Mentorships (Subscriptions) Banner -->
    @if($activeSubscriptions->count() > 0)
    <div class="rounded-xl border border-indigo-500/30 bg-indigo-950/20 backdrop-blur-sm p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center border border-indigo-500/30">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-white">Active Mentorships</h2>
                    <p class="text-xs text-slate-400">Institutional analysts reviewing your journal</p>
                </div>
            </div>
            <span class="px-2 py-0.5 rounded-md text-xs font-medium bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 tabular-nums">
                {{ $activeSubscriptions->count() }} {{ Str::plural('Subscription', $activeSubscriptions->count()) }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($activeSubscriptions as $subscription)
            <div class="rounded-lg border border-slate-800 bg-slate-900/80 p-4 flex flex-col justify-between hover:border-indigo-500/40 transition-colors">
                <div>
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 flex items-center justify-center font-bold text-sm">
                                {{ substr($subscription->analyst->name, 0, 1) }}
                            </div>
                            <div>
                                <h3 class="font-semibold text-white text-sm">{{ $subscription->analyst->name }}</h3>
                                <p class="text-xs text-indigo-400 font-medium uppercase tracking-wider">{{ $subscription->plan }} Tier</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $subscription->status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' }}">
                            {{ ucfirst($subscription->status) }}
                        </span>
                    </div>

                    <div class="space-y-1.5 py-3 border-y border-slate-800 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Renewal Date</span>
                            <span class="text-slate-200 font-medium tabular-nums">{{ $subscription->current_period_end->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Billing Rate</span>
                            <span class="text-slate-200 font-medium tabular-nums">{{ number_format($subscription->price, 2) }} ETB/mo</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 mt-4 pt-1">
                    <a href="{{ route('analysts.show', $subscription->analyst) }}" class="flex-1 py-1.5 px-3 bg-indigo-600/20 hover:bg-indigo-600/30 border border-indigo-500/30 text-indigo-300 hover:text-white text-xs font-medium rounded-md text-center transition-colors">
                        View Analyst
                    </a>
                    <form action="{{ route('subscription.destroy', $subscription->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this subscription? You will lose access at the end of the billing period.');" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full py-1.5 px-3 bg-slate-800/80 hover:bg-rose-500/20 border border-slate-700 hover:border-rose-500/30 text-slate-400 hover:text-rose-400 text-xs font-medium rounded-md transition-colors">
                            Cancel
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Inverted Pyramid: Primary KPI Grid -->
    <div class="space-y-4">
        <!-- Tier 1: Core Performance Pillars (4 High-Emphasis Cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Net P&L -->
            <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5 backdrop-blur-sm hover:border-slate-700 transition-colors">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Net P&L</span>
                    <div class="w-8 h-8 rounded-lg {{ $stats['total_profit'] >= 0 ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400' }} flex items-center justify-center">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl sm:text-3xl font-bold tracking-tight {{ $stats['total_profit'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }} tabular-nums">
                        {{ $stats['total_profit'] >= 0 ? '+' : '' }}${{ number_format($stats['total_profit'], 2) }}
                    </p>
                </div>
                <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-800/60 text-xs">
                    <span class="text-slate-500">All-time balance delta</span>
                    <span class="font-medium {{ $stats['total_profit'] >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                        {{ $stats['total_profit'] >= 0 ? 'Profitable' : 'Drawdown' }}
                    </span>
                </div>
            </div>

            <!-- Win Rate -->
            <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5 backdrop-blur-sm hover:border-slate-700 transition-colors">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Win Rate</span>
                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <circle cx="12" cy="12" r="6"></circle>
                            <circle cx="12" cy="12" r="2"></circle>
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl sm:text-3xl font-bold tracking-tight text-white tabular-nums">
                        {{ number_format($stats['win_rate'], 1) }}%
                    </p>
                </div>
                <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-800/60 text-xs">
                    <span class="text-slate-500">Target baseline: 50.0%</span>
                    <span class="font-medium {{ $stats['win_rate'] >= 50 ? 'text-emerald-400' : 'text-amber-400' }}">
                        {{ $stats['win_rate'] >= 50 ? 'Above Benchmark' : 'Needs Work' }}
                    </span>
                </div>
            </div>

            <!-- Profit Factor -->
            <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5 backdrop-blur-sm hover:border-slate-700 transition-colors">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Profit Factor</span>
                    <div class="w-8 h-8 rounded-lg bg-teal-500/10 text-teal-400 flex items-center justify-center">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 16l3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1z"></path>
                            <path d="M2 16l3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1z"></path>
                            <path d="M7 21h10"></path>
                            <path d="M12 3v18"></path>
                            <path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl sm:text-3xl font-bold tracking-tight text-white tabular-nums">
                        {{ number_format($stats['profit_factor'], 2) }}
                    </p>
                </div>
                <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-800/60 text-xs">
                    <span class="text-slate-500">Gross win / loss</span>
                    <span class="font-medium {{ $stats['profit_factor'] >= 2.0 ? 'text-emerald-400' : ($stats['profit_factor'] >= 1.5 ? 'text-teal-400' : 'text-slate-400') }}">
                        {{ $stats['profit_factor'] >= 2.0 ? 'Exceptional' : ($stats['profit_factor'] >= 1.5 ? 'Healthy' : 'Sub-optimal') }}
                    </span>
                </div>
            </div>

            <!-- Average Risk : Reward -->
            <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5 backdrop-blur-sm hover:border-slate-700 transition-colors">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Avg Risk : Reward</span>
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                            <polyline points="16 7 22 7 22 13"></polyline>
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl sm:text-3xl font-bold tracking-tight text-white tabular-nums">
                        1 : {{ number_format($stats['avg_rr'], 2) }}
                    </p>
                </div>
                <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-800/60 text-xs">
                    <span class="text-slate-500">Average R:R realized</span>
                    <span class="font-medium {{ $stats['avg_rr'] >= 2.0 ? 'text-emerald-400' : 'text-slate-400' }}">
                        {{ $stats['avg_rr'] >= 2.0 ? 'High Edge' : 'Standard' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Tier 2: Supporting Analytical Indicators (4 Secondary Cards) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Trades -->
            <div class="rounded-xl border border-slate-800/80 bg-slate-900/40 p-4 hover:border-slate-700 transition-colors">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-slate-400">Total Trades</span>
                    <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                </div>
                <p class="text-xl font-bold text-white tabular-nums">
                    {{ number_format($stats['total_trades']) }}
                </p>
                <p class="text-xs text-slate-500 mt-1">All-time executed</p>
            </div>

            <!-- Expectancy -->
            <div class="rounded-xl border border-slate-800/80 bg-slate-900/40 p-4 hover:border-slate-700 transition-colors">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-slate-400">Trade Expectancy</span>
                    <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 14 14"></polyline>
                    </svg>
                </div>
                <p class="text-xl font-bold {{ $stats['expectancy'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }} tabular-nums">
                    {{ $stats['expectancy'] >= 0 ? '+' : '' }}${{ number_format($stats['expectancy'], 2) }}
                </p>
                <p class="text-xs text-slate-500 mt-1">Mathematical edge / trade</p>
            </div>

            <!-- Max Drawdown -->
            <div class="rounded-xl border border-slate-800/80 bg-slate-900/40 p-4 hover:border-slate-700 transition-colors">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-slate-400">Max Drawdown</span>
                    <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline>
                        <polyline points="17 18 23 18 23 12"></polyline>
                    </svg>
                </div>
                <p class="text-xl font-bold text-rose-400 tabular-nums">
                    -${{ number_format(abs($stats['max_drawdown']), 2) }}
                </p>
                <p class="text-xs text-slate-500 mt-1">Largest peak-to-valley dip</p>
            </div>

            <!-- This Month Trades -->
            <div class="rounded-xl border border-slate-800/80 bg-slate-900/40 p-4 hover:border-slate-700 transition-colors">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-slate-400">Volume This Month</span>
                    <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
                <p class="text-xl font-bold text-white tabular-nums">
                    {{ number_format($stats['this_month_trades']) }}
                </p>
                <p class="text-xs text-slate-500 mt-1">{{ date('F Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Streaks & Execution Momentum -->
    @if($stats['total_trades'] > 0)
    <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-800">
            <!-- Current Streak -->
            <div class="flex items-center gap-4 py-3 md:py-1 md:px-4 first:pl-0">
                <div class="w-10 h-10 rounded-lg {{ ($streaks['current_type'] ?? '') === 'win' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }} flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-bold text-white tabular-nums">{{ $streaks['current_streak'] }}</span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded {{ ($streaks['current_type'] ?? '') === 'win' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                            {{ ucfirst($streaks['current_type'] ?? 'No') }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">Current Execution Streak ({{ $streaks['current_streak'] === 1 ? 'trade' : 'trades' }})</p>
                </div>
            </div>

            <!-- Best Win Streak -->
            <div class="flex items-center gap-4 py-3 md:py-1 md:px-4">
                <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <div>
                    <span class="text-2xl font-bold text-white tabular-nums">{{ $streaks['max_win_streak'] }}</span>
                    <p class="text-xs text-slate-400 mt-0.5">Best Consecutive Win Streak</p>
                </div>
            </div>

            <!-- Max Loss Streak -->
            <div class="flex items-center gap-4 py-3 md:py-1 md:px-4 last:pr-0">
                <div class="w-10 h-10 rounded-lg bg-rose-500/10 text-rose-400 border border-rose-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                <div>
                    <span class="text-2xl font-bold text-white tabular-nums">{{ $streaks['max_loss_streak'] }}</span>
                    <p class="text-xs text-slate-400 mt-0.5">Max Consecutive Loss Streak</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Section: Equity Chart & Quick Toolkit -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Equity Curve Chart (2 cols) -->
        <div class="lg:col-span-2 rounded-xl border border-slate-800 bg-slate-900/60 p-5 backdrop-blur-sm flex flex-col justify-between">
            <div class="flex items-center justify-between pb-4 border-b border-slate-800/80 mb-4">
                <div>
                    <h2 class="text-base font-semibold text-white">Equity Growth Trajectory</h2>
                    <p class="text-xs text-slate-400">Cumulative account balance across the last 30 trades</p>
                </div>
                <a href="{{ route('trader.analytics.index') }}" class="text-xs font-medium text-emerald-400 hover:text-emerald-300 flex items-center gap-1 transition-colors">
                    <span>Deep Dive</span>
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
            </div>

            <div class="relative w-full h-64">
                @if($equityCurve->count() > 1)
                    <canvas id="equityCurveChart"></canvas>
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center text-center p-6">
                        <div class="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center text-slate-500 mb-3">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-slate-300">Insufficient trade history for curve projection</p>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm">Log at least 2 closed trades to visualize your balance growth trajectory.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions & Trader Workflow Toolkit (1 col) -->
        <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5 backdrop-blur-sm flex flex-col justify-between">
            <div class="pb-4 border-b border-slate-800/80 mb-4">
                <h2 class="text-base font-semibold text-white">Trading Toolkit</h2>
                <p class="text-xs text-slate-400">Essential navigation & logging actions</p>
            </div>

            <div class="space-y-3 flex-1 flex flex-col justify-center">
                <button onclick="openTradeModal()" class="w-full group flex items-center gap-3.5 p-3 rounded-lg border border-emerald-500/30 bg-emerald-500/10 hover:bg-emerald-500/20 transition-all text-left">
                    <div class="w-9 h-9 rounded-md bg-emerald-500/20 text-emerald-400 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white group-hover:text-emerald-300 transition-colors">Record New Position</p>
                        <p class="text-xs text-slate-400">Log entry price, SL, TP & metrics</p>
                    </div>
                </button>

                <a href="{{ route('trader.analytics.index') }}" class="w-full group flex items-center gap-3.5 p-3 rounded-lg border border-slate-800 bg-slate-800/40 hover:bg-slate-800/80 hover:border-slate-700 transition-all text-left">
                    <div class="w-9 h-9 rounded-md bg-blue-500/10 text-blue-400 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="20" x2="18" y2="10"></line>
                            <line x1="12" y1="20" x2="12" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="14"></line>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white group-hover:text-blue-300 transition-colors">Performance Analytics</p>
                        <p class="text-xs text-slate-400">Win distribution & session metrics</p>
                    </div>
                </a>

                <a href="{{ route('trader.trades.index') }}" class="w-full group flex items-center gap-3.5 p-3 rounded-lg border border-slate-800 bg-slate-800/40 hover:bg-slate-800/80 hover:border-slate-700 transition-all text-left">
                    <div class="w-9 h-9 rounded-md bg-purple-500/10 text-purple-400 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white group-hover:text-purple-300 transition-colors">Trade History Log</p>
                        <p class="text-xs text-slate-400">Detailed journal archive</p>
                    </div>
                </a>

                <a href="{{ route('trader.accounts.index') }}" class="w-full group flex items-center gap-3.5 p-3 rounded-lg border border-slate-800 bg-slate-800/40 hover:bg-slate-800/80 hover:border-slate-700 transition-all text-left">
                    <div class="w-9 h-9 rounded-md bg-amber-500/10 text-amber-400 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                            <line x1="2" y1="10" x2="22" y2="10"></line>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white group-hover:text-amber-300 transition-colors">Trading Accounts</p>
                        <p class="text-xs text-slate-400">Capital balance & prop firm accounts</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Trades Table -->
    <div class="rounded-xl border border-slate-800 bg-slate-900/60 backdrop-blur-sm overflow-hidden">
        <div class="p-5 border-b border-slate-800/80 flex items-center justify-between">
            <div>
                <h2 class="text-base font-semibold text-white">Recent Trade Executions</h2>
                <p class="text-xs text-slate-400">Last 5 positions logged to your TradeCompass journal</p>
            </div>
            <a href="{{ route('trader.trades.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-400 hover:text-emerald-300 transition-colors">
                <span>View All Trades</span>
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 bg-slate-900/80 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                        <th class="px-5 py-3.5">Instrument</th>
                        <th class="px-5 py-3.5">Direction</th>
                        <th class="px-5 py-3.5">Timestamp</th>
                        <th class="px-5 py-3.5">Outcome</th>
                        <th class="px-5 py-3.5 text-right">Realized P&L</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-sm">
                    @forelse($recentTrades as $trade)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <!-- Instrument -->
                            <td class="px-5 py-3.5 font-semibold text-white">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full {{ $trade->profit_loss >= 0 ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                                    <span>{{ $trade->pair }}</span>
                                </div>
                            </td>

                            <!-- Direction Badge -->
                            <td class="px-5 py-3.5">
                                @if(strtolower($trade->direction->value ?? (string)$trade->direction) === 'buy')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="12" y1="19" x2="12" y2="5"></line>
                                            <polyline points="5 12 12 5 19 12"></polyline>
                                        </svg>
                                        <span>BUY</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="12" y1="5" x2="12" y2="19"></line>
                                            <polyline points="19 12 12 19 5 12"></polyline>
                                        </svg>
                                        <span>SELL</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Timestamp -->
                            <td class="px-5 py-3.5 text-slate-400 text-xs tabular-nums">
                                {{ $trade->entry_date->format('M d, Y · H:i') }}
                            </td>

                            <!-- Outcome Badge -->
                            <td class="px-5 py-3.5">
                                @php
                                    $outcomeVal = strtolower($trade->outcome->value ?? (string)$trade->outcome);
                                @endphp
                                @if($outcomeVal === 'win')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                        <span>{{ $trade->outcome->label() }}</span>
                                    </span>
                                @elseif($outcomeVal === 'loss')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                        </svg>
                                        <span>{{ $trade->outcome->label() }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        <span>{{ $trade->outcome->label() }}</span>
                                    </span>
                                @endif
                            </td>

                            <!-- P&L -->
                            <td class="px-5 py-3.5 text-right font-semibold tabular-nums {{ $trade->profit_loss >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ $trade->profit_loss >= 0 ? '+' : '' }}${{ number_format($trade->profit_loss, 2) }}
                            </td>

                            <!-- Action -->
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('trader.trades.show', $trade) }}" class="inline-flex items-center gap-1 text-xs font-medium text-slate-400 hover:text-white transition-colors">
                                    <span>Details</span>
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="9 18 15 12 9 6"></polyline>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <div class="w-12 h-12 rounded-full bg-slate-800/80 text-slate-500 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="12" y1="18" x2="12" y2="12"></line>
                                        <line x1="9" y1="15" x2="15" y2="15"></line>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-semibold text-white">No trades logged yet</h3>
                                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                                    Begin tracking your trading journey to generate performance analytics, win rate stats, and equity trajectory.
                                </p>
                                <button onclick="openTradeModal()" class="mt-4 inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-sm transition-colors">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                    <span>Log Your First Trade</span>
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('trader.trades.create-modal')

@push('scripts')
@if($equityCurve->count() > 1)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('equityCurveChart');
        if (!ctx) return;

        const equityData = @json($equityCurve);
        const labels = equityData.map(d => d.date || '');
        const values = equityData.map(d => parseFloat(d.equity) || 0);

        const isPositiveOverall = values.length > 0 && values[values.length - 1] >= values[0];
        const primaryColor = isPositiveOverall ? '#10b981' : '#f43f5e';
        const primaryBg = isPositiveOverall ? 'rgba(16, 185, 129, 0.08)' : 'rgba(244, 63, 94, 0.08)';

        new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Account Equity',
                    data: values,
                    borderColor: primaryColor,
                    backgroundColor: primaryBg,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.35,
                    pointRadius: values.length > 15 ? 0 : 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: primaryColor,
                    pointBorderColor: '#0f172a',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#94a3b8',
                        bodyColor: '#ffffff',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 10,
                        boxPadding: 4,
                        callbacks: {
                            label: function (context) {
                                const val = context.parsed.y;
                                return ' Equity: $' + val.toLocaleString(undefined, {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#64748b',
                            font: {
                                size: 11
                            },
                            maxTicksLimit: 7
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(51, 65, 85, 0.35)',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#64748b',
                            font: {
                                size: 11
                            },
                            callback: function (value) {
                                return '$' + Number(value).toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endif
@endpush
@endsection
