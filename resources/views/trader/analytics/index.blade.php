@extends('layouts.app')

@section('title', isset($activePair) ? "Analytics: {$activePair}" : 'Performance Analytics')

@section('content')
<div class="space-y-6" x-data="{ 
    filterExpanded: false,
    calendarYear: {{ $calendarYear }},
    calendarMonth: {{ $calendarMonth }}
}">

    <!-- ======================================================== -->
    <!-- STICKY COMMAND BAR (TIMEFRAME CHIPS & ACCOUNT SELECTOR)  -->
    <!-- ======================================================== -->
    <div class="bg-[#111620] border border-white/5 rounded-2xl p-4 sm:p-5 shadow-lg shadow-black/20">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            
            <!-- Page Title & Subtitle -->
            <div>
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-white tracking-tight">
                                {{ isset($activePair) ? "Pair Analytics: {$activePair}" : "Performance Analytics" }}
                            </h1>
                            @if(isset($activePair))
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                    Symbol Isolated
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400">Time-weighted equity trajectory, drawdown analysis, and trading statistical edge</p>
                    </div>
                </div>
            </div>

            <!-- Timeframe Chips & Action Group -->
            <div class="flex flex-wrap items-center gap-2">
                @php
                    $currentPeriod = strtolower($period ?? request('period', 'all'));
                    if (empty($currentPeriod) && (request('date_from') || request('date_to'))) {
                        $currentPeriod = 'custom';
                    }
                    $timeframes = [
                        '7d'  => '7D',
                        '30d' => '30D',
                        '90d' => '90D',
                        'ytd' => 'YTD',
                        'all' => 'ALL',
                    ];
                @endphp

                <!-- Quick Period Chips -->
                <div class="inline-flex items-center bg-[#0B0E14] border border-white/10 rounded-xl p-1 gap-1">
                    @foreach($timeframes as $val => $lbl)
                        @php
                            $isActive = ($currentPeriod === $val) || ($val === 'all' && empty($currentPeriod));
                        @endphp
                        <a href="{{ route('trader.analytics.index', array_merge(request()->except(['date_from', 'date_to', 'period']), ['period' => $val])) }}" 
                           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $isActive ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                            {{ $lbl }}
                        </a>
                    @endforeach
                </div>

                <!-- Account Selector -->
                <form method="GET" action="{{ route('trader.analytics.index') }}" class="inline-flex">
                    @if(request('period'))
                        <input type="hidden" name="period" value="{{ request('period') }}">
                    @endif
                    @if(request('date_from'))
                        <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                    @endif
                    @if(request('date_to'))
                        <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                    @endif
                    @if(request('session'))
                        <input type="hidden" name="session" value="{{ request('session') }}">
                    @endif
                    @if(request('pair'))
                        <input type="hidden" name="pair" value="{{ request('pair') }}">
                    @endif

                    <select name="trade_account_id" 
                            onchange="this.form.submit()"
                            class="bg-[#0B0E14] border border-white/10 rounded-xl px-3 py-1.5 text-xs text-white focus:outline-none focus:border-emerald-500 transition-colors">
                        <option value="">All Accounts</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ (string)request('trade_account_id') === (string)$acc->id ? 'selected' : '' }}>
                                {{ $acc->account_name }} ({{ $acc->currency }})
                            </option>
                        @endforeach
                    </select>
                </form>

                <!-- Filter Drawer Toggle -->
                <button type="button" 
                        @click="filterExpanded = !filterExpanded" 
                        class="p-2 rounded-xl border border-white/10 bg-[#0B0E14] hover:bg-slate-800 text-slate-300 hover:text-white transition-colors"
                        title="Custom Date & Filters">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                </button>

                <!-- Behavioral Pattern Review Link -->
                <a href="{{ route('trader.analytics.review') }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-indigo-500/30 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-300 text-xs font-semibold transition-all">
                    <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Pattern Review</span>
                </a>
            </div>
        </div>

        <!-- Collapsible Custom Filter Bar -->
        <div x-show="filterExpanded" x-collapse class="mt-4 pt-4 border-t border-white/5">
            <form method="GET" action="{{ route('trader.analytics.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <input type="hidden" name="trade_account_id" value="{{ request('trade_account_id') }}">

                <!-- From Date -->
                <div>
                    <label class="block text-[11px] font-medium text-slate-400 mb-1">From Date</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                           class="w-full bg-[#0B0E14] border border-white/10 rounded-lg px-2.5 py-1.5 text-xs text-white focus:outline-none focus:border-emerald-500">
                </div>

                <!-- To Date -->
                <div>
                    <label class="block text-[11px] font-medium text-slate-400 mb-1">To Date</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                           class="w-full bg-[#0B0E14] border border-white/10 rounded-lg px-2.5 py-1.5 text-xs text-white focus:outline-none focus:border-emerald-500">
                </div>

                <!-- Pair Filter -->
                <div>
                    <label class="block text-[11px] font-medium text-slate-400 mb-1">Pair / Symbol</label>
                    <select name="pair" class="w-full bg-[#0B0E14] border border-white/10 rounded-lg px-2.5 py-1.5 text-xs text-white focus:outline-none focus:border-emerald-500">
                        <option value="">All Pairs</option>
                        @foreach($pairs as $p)
                            <option value="{{ $p }}" {{ ($filters['pair'] ?? '') === $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Session Filter -->
                <div>
                    <label class="block text-[11px] font-medium text-slate-400 mb-1">Trading Session</label>
                    <select name="session" class="w-full bg-[#0B0E14] border border-white/10 rounded-lg px-2.5 py-1.5 text-xs text-white focus:outline-none focus:border-emerald-500">
                        <option value="">All Sessions</option>
                        <option value="london" {{ ($filters['session'] ?? '') === 'london' ? 'selected' : '' }}>London</option>
                        <option value="newyork" {{ ($filters['session'] ?? '') === 'newyork' ? 'selected' : '' }}>New York</option>
                        <option value="asia" {{ ($filters['session'] ?? '') === 'asia' ? 'selected' : '' }}>Tokyo / Asia</option>
                        <option value="sydney" {{ ($filters['session'] ?? '') === 'sydney' ? 'selected' : '' }}>Sydney</option>
                    </select>
                </div>

                <!-- Submit / Reset Actions -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-1.5 px-3 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs rounded-lg transition-colors">
                        Filter
                    </button>
                    <a href="{{ route('trader.analytics.index') }}" class="py-1.5 px-3 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition-colors">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 8-CARD MODULAR KPI STAT MATRIX                           -->
    <!-- ======================================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
        
        <!-- 1. Net Profit / Loss -->
        <div class="bg-[#111620] border border-white/5 rounded-2xl p-4 shadow-sm relative overflow-hidden group hover:border-white/10 transition-colors">
            <div class="absolute -right-2 -top-2 w-16 h-16 {{ $metrics['net_profit_loss'] >= 0 ? 'bg-emerald-500/10' : 'bg-rose-500/10' }} rounded-full blur-xl transition-all"></div>
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Net P&L</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded font-bold uppercase {{ $metrics['net_profit_loss'] >= 0 ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                    {{ $metrics['net_profit_loss'] >= 0 ? 'Profitable' : 'Drawdown' }}
                </span>
            </div>
            <div class="flex items-baseline gap-1">
                <span class="text-2xl font-bold font-mono tabular-nums {{ $metrics['net_profit_loss'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                    {{ $metrics['net_profit_loss'] >= 0 ? '+' : '' }}${{ number_format($metrics['net_profit_loss'], 2) }}
                </span>
            </div>
            <div class="mt-2 text-[10px] text-slate-400 flex items-center justify-between">
                <span>Trades Executed</span>
                <span class="font-mono text-slate-300 font-semibold">{{ number_format($metrics['total_trades']) }}</span>
            </div>
        </div>

        <!-- 2. Win Rate % -->
        <div class="bg-[#111620] border border-white/5 rounded-2xl p-4 shadow-sm relative overflow-hidden group hover:border-white/10 transition-colors">
            <div class="absolute -right-2 -top-2 w-16 h-16 bg-blue-500/10 rounded-full blur-xl transition-all"></div>
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Win Rate</span>
                <span class="text-[10px] font-semibold text-slate-400">Target >= 50%</span>
            </div>
            <div class="flex items-baseline gap-1">
                <span class="text-2xl font-bold font-mono tabular-nums {{ $metrics['win_rate'] >= 50 ? 'text-emerald-400' : 'text-amber-400' }}">
                    {{ number_format($metrics['win_rate'], 1) }}%
                </span>
            </div>
            <div class="mt-2.5 w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                <div class="h-1.5 rounded-full {{ $metrics['win_rate'] >= 50 ? 'bg-emerald-500' : 'bg-amber-500' }}" 
                     style="width: {{ min(max($metrics['win_rate'], 0), 100) }}%"></div>
            </div>
        </div>

        <!-- 3. Profit Factor -->
        <div class="bg-[#111620] border border-white/5 rounded-2xl p-4 shadow-sm relative overflow-hidden group hover:border-white/10 transition-colors">
            <div class="absolute -right-2 -top-2 w-16 h-16 bg-purple-500/10 rounded-full blur-xl transition-all"></div>
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Profit Factor</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded font-mono {{ $metrics['profit_factor'] >= 1.5 ? 'bg-purple-500/10 text-purple-300 border border-purple-500/20' : 'bg-slate-800 text-slate-400' }}">
                    {{ $metrics['profit_factor'] >= 1.5 ? 'Strong Edge' : 'Normal' }}
                </span>
            </div>
            <div class="flex items-baseline gap-1">
                <span class="text-2xl font-bold font-mono tabular-nums text-white">
                    {{ number_format($metrics['profit_factor'], 2) }}
                </span>
            </div>
            <div class="mt-2 text-[10px] text-slate-400">
                <span>Gross Profit / Gross Loss</span>
            </div>
        </div>

        <!-- 4. Mathematical Expectancy -->
        <div class="bg-[#111620] border border-white/5 rounded-2xl p-4 shadow-sm relative overflow-hidden group hover:border-white/10 transition-colors">
            <div class="absolute -right-2 -top-2 w-16 h-16 bg-teal-500/10 rounded-full blur-xl transition-all"></div>
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Expectancy</span>
                <span class="text-[10px] text-slate-500">Per execution</span>
            </div>
            <div class="flex items-baseline gap-1">
                <span class="text-2xl font-bold font-mono tabular-nums {{ $metrics['expectancy'] >= 0 ? 'text-teal-400' : 'text-rose-400' }}">
                    {{ $metrics['expectancy'] >= 0 ? '+' : '' }}${{ number_format($metrics['expectancy'], 2) }}
                </span>
                <span class="text-[11px] text-slate-500">/ trade</span>
            </div>
            <div class="mt-2 text-[10px] text-slate-400">
                <span>Statistical return expectation</span>
            </div>
        </div>

        <!-- 5. Max Drawdown -->
        <div class="bg-[#111620] border border-white/5 rounded-2xl p-4 shadow-sm relative overflow-hidden group hover:border-white/10 transition-colors">
            <div class="absolute -right-2 -top-2 w-16 h-16 bg-rose-500/10 rounded-full blur-xl transition-all"></div>
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Max Drawdown</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-rose-500/10 text-rose-400 border border-rose-500/20 font-bold uppercase">
                    Risk
                </span>
            </div>
            <div class="flex items-baseline gap-1">
                <span class="text-2xl font-bold font-mono tabular-nums text-rose-400">
                    -${{ number_format($metrics['max_drawdown'], 2) }}
                </span>
            </div>
            <div class="mt-2 text-[10px] text-slate-400">
                <span>Peak-to-trough historical drop</span>
            </div>
        </div>

        <!-- 6. Average Risk : Reward -->
        <div class="bg-[#111620] border border-white/5 rounded-2xl p-4 shadow-sm relative overflow-hidden group hover:border-white/10 transition-colors">
            <div class="absolute -right-2 -top-2 w-16 h-16 bg-indigo-500/10 rounded-full blur-xl transition-all"></div>
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Avg R:R Ratio</span>
                <span class="text-[10px] text-indigo-400 font-semibold">Planned Edge</span>
            </div>
            <div class="flex items-baseline gap-1">
                <span class="text-2xl font-bold font-mono tabular-nums text-white">
                    1 : {{ number_format($metrics['avg_rr'], 2) }}
                </span>
            </div>
            <div class="mt-2 text-[10px] text-slate-400">
                <span>Target reward vs initial risk</span>
            </div>
        </div>

        <!-- 7. Recovery Factor -->
        <div class="bg-[#111620] border border-white/5 rounded-2xl p-4 shadow-sm relative overflow-hidden group hover:border-white/10 transition-colors">
            <div class="absolute -right-2 -top-2 w-16 h-16 bg-amber-500/10 rounded-full blur-xl transition-all"></div>
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Recovery Factor</span>
                <span class="text-[10px] text-slate-400 font-mono">Net / DD</span>
            </div>
            <div class="flex items-baseline gap-1">
                <span class="text-2xl font-bold font-mono tabular-nums text-amber-300">
                    {{ number_format($metrics['recovery_factor'], 2) }}
                </span>
            </div>
            <div class="mt-2 text-[10px] text-slate-400">
                <span>Return resilience score</span>
            </div>
        </div>

        <!-- 8. Average Hold Time -->
        <div class="bg-[#111620] border border-white/5 rounded-2xl p-4 shadow-sm relative overflow-hidden group hover:border-white/10 transition-colors">
            <div class="absolute -right-2 -top-2 w-16 h-16 bg-cyan-500/10 rounded-full blur-xl transition-all"></div>
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Avg Duration</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-cyan-500/10 text-cyan-300 border border-cyan-500/20 font-medium">
                    {{ $metrics['avg_hold_time'] < 4 ? 'Scalp' : ($metrics['avg_hold_time'] < 24 ? 'Intraday' : 'Swing') }}
                </span>
            </div>
            <div class="flex items-baseline gap-1">
                <span class="text-2xl font-bold font-mono tabular-nums text-white">
                    {{ number_format($metrics['avg_hold_time'], 1) }}
                </span>
                <span class="text-[11px] text-slate-500">hours</span>
            </div>
            <div class="mt-2 text-[10px] text-slate-400">
                <span>Mean trade exposure time</span>
            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- SYNCHRONIZED DUAL-CHART (EQUITY CURVE & UNDERWATER DD)   -->
    <!-- ======================================================== -->
    <div class="bg-[#111620] border border-white/5 rounded-2xl p-5 shadow-lg shadow-black/20 space-y-4">
        
        <!-- Equity Curve Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-white/5">
            <div>
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                    <span>Cumulative Equity Trajectory</span>
                </h3>
                <p class="text-[11px] text-slate-400">Realized trading equity trajectory across chronological trade executions</p>
            </div>
            <div class="text-xs font-mono text-slate-400 flex items-center gap-3">
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                    <span class="text-slate-300">Equity ($)</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                    <span class="text-slate-300">Underwater DD</span>
                </div>
            </div>
        </div>

        <!-- Upper Canvas: Equity Curve -->
        <div class="relative h-72 sm:h-80 w-full">
            <canvas id="equityCurveChart"></canvas>
        </div>

        <!-- Lower Canvas: Underwater Drawdown Ribbon -->
        <div class="pt-3 border-t border-white/5 space-y-1.5">
            <div class="flex items-center justify-between text-[11px]">
                <span class="font-semibold text-slate-400 uppercase tracking-wider">Underwater Drawdown Depth</span>
                <span class="font-mono text-rose-400 font-bold">Peak Risk: -${{ number_format($metrics['max_drawdown'], 2) }}</span>
            </div>
            <div class="relative h-28 sm:h-32 w-full">
                <canvas id="drawdownChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- FOREX TRADING CALENDAR HEATMAP (MONTHLY P&L MATRIX)      -->
    <!-- ======================================================== -->
    <div class="bg-[#111620] border border-white/5 rounded-2xl p-5 shadow-lg shadow-black/20 space-y-4">
        
        @php
            $currentMonthCarbon = \Carbon\Carbon::createFromDate($calendarYear, $calendarMonth, 1);
            $prevMonthCarbon = (clone $currentMonthCarbon)->subMonth();
            $nextMonthCarbon = (clone $currentMonthCarbon)->addMonth();

            $daysInMonth = $currentMonthCarbon->daysInMonth;
            $firstDayOfWeek = $currentMonthCarbon->copy()->startOfMonth()->dayOfWeekIso; // 1 = Mon, 7 = Sun
            
            // Calculate Month Stats
            $monthNetPnl = array_sum(array_column($calendarData, 'net_pnl'));
            $monthTradesCount = array_sum(array_column($calendarData, 'trade_count'));
            $profitDaysCount = count(array_filter($calendarData, fn($d) => $d['net_pnl'] > 0));
            $lossDaysCount = count(array_filter($calendarData, fn($d) => $d['net_pnl'] < 0));
        @endphp

        <!-- Calendar Header & Navigation -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-white/5">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white tracking-tight flex items-center gap-2">
                        <span>Trading Calendar Heatmap</span>
                        <span class="text-xs font-normal text-slate-400">({{ $currentMonthCarbon->format('F Y') }})</span>
                    </h3>
                    <p class="text-[11px] text-slate-400">Day-by-day P&L distribution and execution discipline</p>
                </div>
            </div>

            <!-- Month Quick Summary & Nav Buttons -->
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <span class="text-[11px] text-slate-400">Month P&L:</span>
                    <span class="font-mono text-xs font-bold {{ $monthNetPnl >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                        {{ $monthNetPnl >= 0 ? '+' : '' }}${{ number_format($monthNetPnl, 2) }}
                    </span>
                    <span class="text-[10px] text-slate-500">({{ $monthTradesCount }} trades)</span>
                </div>

                <div class="inline-flex items-center bg-[#0B0E14] border border-white/10 rounded-xl p-1 gap-1">
                    <!-- Previous Month -->
                    <a href="{{ route('trader.analytics.index', array_merge(request()->query(), ['calendar_year' => $prevMonthCarbon->year, 'calendar_month' => $prevMonthCarbon->month])) }}" 
                       class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors"
                       title="{{ $prevMonthCarbon->format('M Y') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <span class="text-xs font-bold text-slate-200 px-2">{{ $currentMonthCarbon->format('M Y') }}</span>
                    <!-- Next Month -->
                    <a href="{{ route('trader.analytics.index', array_merge(request()->query(), ['calendar_year' => $nextMonthCarbon->year, 'calendar_month' => $nextMonthCarbon->month])) }}" 
                       class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors"
                       title="{{ $nextMonthCarbon->format('M Y') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Month Performance Strip -->
        <div class="grid grid-cols-3 gap-3 p-3 bg-[#0B0E14] rounded-xl border border-white/5 text-center text-xs">
            <div>
                <span class="text-[10px] text-slate-400 uppercase">Month Realized</span>
                <p class="font-mono font-bold text-sm {{ $monthNetPnl >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                    {{ $monthNetPnl >= 0 ? '+' : '' }}${{ number_format($monthNetPnl, 2) }}
                </p>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 uppercase">Profitable Days</span>
                <p class="font-mono font-bold text-sm text-emerald-400">{{ $profitDaysCount }} days</p>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 uppercase">Drawdown Days</span>
                <p class="font-mono font-bold text-sm text-rose-400">{{ $lossDaysCount }} days</p>
            </div>
        </div>

        <!-- 5-Day Forex Trading Grid (Mon–Fri) -->
        <div class="overflow-x-auto">
            <div class="min-w-[600px]">
                <!-- Day-of-Week Headers -->
                <div class="grid grid-cols-5 gap-2 text-center text-[11px] font-semibold text-slate-400 uppercase pb-2 border-b border-white/5">
                    <div>Mon</div>
                    <div>Tue</div>
                    <div>Wed</div>
                    <div>Thu</div>
                    <div>Fri</div>
                </div>

                <!-- Calendar Days Grid -->
                <div class="grid grid-cols-5 gap-2 pt-2">
                    @php
                        // Offset for Monday-based index (1 = Mon ... 5 = Fri)
                        $startOffset = ($firstDayOfWeek <= 5) ? ($firstDayOfWeek - 1) : 0;
                    @endphp

                    {{-- Empty offset cells before 1st of month --}}
                    @for($i = 0; $i < $startOffset; $i++)
                        <div class="h-20 bg-[#0B0E14]/40 rounded-xl border border-dashed border-white/5 p-2 opacity-30"></div>
                    @endfor

                    {{-- Month Day Cells --}}
                    @for($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $loopDate = \Carbon\Carbon::createFromDate($calendarYear, $calendarMonth, $day);
                            $dow = $loopDate->dayOfWeekIso;
                            if ($dow > 5) continue; // Skip weekends for forex calendar view

                            $dateKey = $loopDate->format('Y-m-d');
                            $dayData = $calendarData[$dateKey] ?? null;
                            $hasTrades = !is_null($dayData) && $dayData['trade_count'] > 0;
                            $isProfit = $hasTrades && $dayData['net_pnl'] > 0;
                            $isLoss = $hasTrades && $dayData['net_pnl'] < 0;
                            $isToday = $loopDate->isToday();
                        @endphp

                        <div class="h-20 rounded-xl p-2.5 flex flex-col justify-between transition-all relative group {{ $hasTrades ? ($isProfit ? 'bg-emerald-500/10 border border-emerald-500/30 hover:border-emerald-500/60' : 'bg-rose-500/10 border border-rose-500/30 hover:border-rose-500/60') : 'bg-[#0B0E14] border border-white/5 hover:border-white/10' }} {{ $isToday ? 'ring-1 ring-indigo-500' : '' }}">
                            <!-- Top: Day Number & Status -->
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-mono font-bold {{ $isToday ? 'text-indigo-400' : ($hasTrades ? 'text-white' : 'text-slate-500') }}">
                                    {{ $day }}
                                </span>
                                @if($hasTrades)
                                    <span class="text-[9px] px-1 py-0.2 rounded font-mono font-semibold {{ $isProfit ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                                        {{ $dayData['trade_count'] }} {{ $dayData['trade_count'] === 1 ? 'trd' : 'trds' }}
                                    </span>
                                @endif
                            </div>

                            <!-- Bottom: Daily Net P&L -->
                            <div>
                                @if($hasTrades)
                                    <p class="font-mono text-xs font-bold tabular-nums {{ $isProfit ? 'text-emerald-400' : 'text-rose-400' }}">
                                        {{ $dayData['net_pnl'] >= 0 ? '+' : '' }}${{ number_format($dayData['net_pnl'], 2) }}
                                    </p>
                                    <p class="text-[9px] text-slate-400 font-mono">
                                        {{ $dayData['win_count'] }}W / {{ $dayData['loss_count'] }}L
                                    </p>
                                @else
                                    <span class="text-[10px] text-slate-600 font-mono">—</span>
                                @endif
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MARKET SESSION & INSTRUMENT BREAKDOWN                    -->
    <!-- ======================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Session Profitability Bar Chart -->
        <div class="bg-[#111620] border border-white/5 rounded-2xl p-5 shadow-lg shadow-black/20 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-white/5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg> Market Session Performance
                </h3>
            </div>
            <p class="text-[11px] text-slate-400">Net return grouped by London, New York, Asia, Sydney</p>
            <div class="relative h-60 w-full">
                <canvas id="sessionChart"></canvas>
            </div>
        </div>

        <!-- Monthly P&L Chart -->
        <div class="bg-[#111620] border border-white/5 rounded-2xl p-5 shadow-lg shadow-black/20 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-white/5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg> Monthly P&L ({{ now()->year }})
                </h3>
            </div>
            <p class="text-[11px] text-slate-400">Aggregate returns across calendar months</p>
            <div class="relative h-60 w-full">
                <canvas id="monthlyPLChart"></canvas>
            </div>
        </div>

        <!-- Outcome Distribution Donut -->
        <div class="bg-[#111620] border border-white/5 rounded-2xl p-5 shadow-lg shadow-black/20 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-white/5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg> Outcome Distribution
                </h3>
            </div>
            <p class="text-[11px] text-slate-400">Win / Loss / Breakeven ratio</p>
            <div class="relative h-60 w-full">
                <canvas id="distributionChart"></canvas>
            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- BEST VS WORST PERFORMING PAIRS                           -->
    <!-- ======================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Best Pairs -->
        <div class="bg-[#111620] border border-emerald-500/20 rounded-2xl p-5 shadow-lg shadow-black/20 space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-400 flex items-center gap-2 pb-2 border-b border-white/5">
                <svg class="w-4 h-4 text-emerald-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg> Top Profitable Instruments
            </h3>
            <div class="space-y-2">
                @forelse($bestWorstPairs['best'] as $pair)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-[#0B0E14] border border-white/5 hover:border-emerald-500/30 transition-colors">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            <div>
                                <a href="{{ route('trader.analytics.pair', str_replace('/', '-', $pair['pair'])) }}" class="font-bold text-white text-xs hover:text-emerald-400 transition-colors">
                                    {{ $pair['pair'] }}
                                </a>
                                <p class="text-[10px] text-slate-500">{{ $pair['trades'] }} trades executed</p>
                            </div>
                        </div>
                        <span class="font-mono text-xs font-bold text-emerald-400">
                            +${{ number_format($pair['profit'], 2) }}
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 py-4 text-center">No profitable pairs matching criteria yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Worst Pairs -->
        <div class="bg-[#111620] border border-rose-500/20 rounded-2xl p-5 shadow-lg shadow-black/20 space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-rose-400 flex items-center gap-2 pb-2 border-b border-white/5">
                <svg class="w-4 h-4 text-rose-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg> Lagging Instruments (Drawdown)
            </h3>
            <div class="space-y-2">
                @forelse($bestWorstPairs['worst'] as $pair)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-[#0B0E14] border border-white/5 hover:border-rose-500/30 transition-colors">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <div>
                                <a href="{{ route('trader.analytics.pair', str_replace('/', '-', $pair['pair'])) }}" class="font-bold text-white text-xs hover:text-rose-400 transition-colors">
                                    {{ $pair['pair'] }}
                                </a>
                                <p class="text-[10px] text-slate-500">{{ $pair['trades'] }} trades executed</p>
                            </div>
                        </div>
                        <span class="font-mono text-xs font-bold text-rose-400">
                            -${{ number_format(abs($pair['profit']), 2) }}
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 py-4 text-center">No loss-leading pairs detected.</p>
                @endforelse
            </div>
        </div>

    </div>

</div>

<!-- ======================================================== -->
<!-- CHART.JS SCRIPTS & CONFIGURATIONS                        -->
<!-- ======================================================== -->
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.Chart === 'undefined') {
            console.error('Chart.js is not loaded!');
            return;
        }

        // Shared Dark Theme Grid Styling
        const darkGridConfig = {
            color: 'rgba(255, 255, 255, 0.04)',
            drawBorder: false,
        };
        const darkTicksConfig = {
            color: '#64748B',
            font: { family: 'inherit', size: 10 }
        };

        // ========================================================
        // 1. HERO EQUITY CURVE CHART
        // ========================================================
        const equityCanvas = document.getElementById('equityCurveChart');
        if (equityCanvas) {
            const ctx = equityCanvas.getContext('2d');
            const equityData = {!! json_encode($equityCurve) !!};

            // Create linear gradient
            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(16, 185, 129, 0.28)');
            gradient.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: equityData.map(d => d.date),
                    datasets: [{
                        label: 'Cumulative Equity',
                        data: equityData.map(d => d.equity),
                        borderColor: '#10B981',
                        borderWidth: 2.2,
                        backgroundColor: gradient,
                        fill: true,
                        tension: 0.25,
                        pointRadius: equityData.length > 50 ? 0 : 3,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#10B981',
                        pointBorderColor: '#0F141C',
                        pointBorderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0F141C',
                            borderColor: 'rgba(255, 255, 255, 0.1)',
                            borderWidth: 1,
                            padding: 10,
                            titleColor: '#94A3B8',
                            bodyColor: '#FFFFFF',
                            callbacks: {
                                label: (ctx) => `Equity: $${ctx.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2})}`
                            }
                        }
                    },
                    scales: {
                        y: {
                            grid: darkGridConfig,
                            ticks: {
                                ...darkTicksConfig,
                                callback: (v) => '$' + v.toLocaleString()
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: darkTicksConfig
                        }
                    }
                }
            });
        }

        // ========================================================
        // 2. UNDERWATER DRAWDOWN RIBBON CHART
        // ========================================================
        const ddCanvas = document.getElementById('drawdownChart');
        if (ddCanvas) {
            const ctx = ddCanvas.getContext('2d');
            const ddData = {!! json_encode($drawdownCurve) !!};

            const ddGradient = ctx.createLinearGradient(0, 0, 0, 120);
            ddGradient.addColorStop(0, 'rgba(244, 63, 94, 0.0)');
            ddGradient.addColorStop(1, 'rgba(244, 63, 94, 0.35)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ddData.map(d => d.date),
                    datasets: [{
                        label: 'Drawdown ($)',
                        data: ddData.map(d => d.drawdown),
                        borderColor: '#F43F5E',
                        borderWidth: 1.5,
                        backgroundColor: ddGradient,
                        fill: 'origin',
                        tension: 0.2,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0F141C',
                            borderColor: 'rgba(244, 63, 94, 0.3)',
                            borderWidth: 1,
                            callbacks: {
                                label: (ctx) => `Drawdown: -$${Math.abs(ctx.parsed.y).toLocaleString('en-US', {minimumFractionDigits: 2})}`
                            }
                        }
                    },
                    scales: {
                        y: {
                            suggestedMax: 0,
                            grid: darkGridConfig,
                            ticks: {
                                ...darkTicksConfig,
                                callback: (v) => '-$' + Math.abs(v).toLocaleString()
                            }
                        },
                        x: {
                            display: false
                        }
                    }
                }
            });
        }

        // ========================================================
        // 3. MONTHLY P&L CHART
        // ========================================================
        const monthlyCanvas = document.getElementById('monthlyPLChart');
        if (monthlyCanvas) {
            const ctx = monthlyCanvas.getContext('2d');
            const monthlyData = {!! json_encode($monthlyPL) !!};

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        data: monthlyData,
                        backgroundColor: monthlyData.map(v => v >= 0 ? 'rgba(16, 185, 129, 0.85)' : 'rgba(244, 63, 94, 0.85)'),
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0F141C',
                            borderColor: 'rgba(255, 255, 255, 0.1)',
                            borderWidth: 1,
                            callbacks: {
                                label: (ctx) => `$${ctx.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2})}`
                            }
                        }
                    },
                    scales: {
                        y: {
                            grid: darkGridConfig,
                            ticks: {
                                ...darkTicksConfig,
                                callback: (v) => '$' + v.toLocaleString()
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: darkTicksConfig
                        }
                    }
                }
            });
        }

        // ========================================================
        // 4. SESSION PERFORMANCE CHART
        // ========================================================
        const sessionCanvas = document.getElementById('sessionChart');
        if (sessionCanvas) {
            const ctx = sessionCanvas.getContext('2d');
            const sessionData = {!! json_encode($sessionPerformance) !!};

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: sessionData.map(s => s.session),
                    datasets: [{
                        data: sessionData.map(s => s.profit),
                        backgroundColor: sessionData.map(s => s.profit >= 0 ? 'rgba(16, 185, 129, 0.85)' : 'rgba(244, 63, 94, 0.85)'),
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0F141C',
                            borderColor: 'rgba(255, 255, 255, 0.1)',
                            borderWidth: 1,
                            callbacks: {
                                label: (ctx) => `$${ctx.parsed.x.toLocaleString('en-US', {minimumFractionDigits: 2})} (${sessionData[ctx.dataIndex].trades} trades)`
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: darkGridConfig,
                            ticks: {
                                ...darkTicksConfig,
                                callback: (v) => '$' + v.toLocaleString()
                            }
                        },
                        y: {
                            grid: { display: false },
                            ticks: darkTicksConfig
                        }
                    }
                }
            });
        }

        // ========================================================
        // 5. OUTCOME DISTRIBUTION DONUT
        // ========================================================
        const distCanvas = document.getElementById('distributionChart');
        if (distCanvas) {
            const ctx = distCanvas.getContext('2d');
            const distribution = {!! json_encode($winLossDistribution) !!};

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(distribution),
                    datasets: [{
                        data: Object.values(distribution),
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.85)',
                            'rgba(244, 63, 94, 0.85)',
                            'rgba(251, 191, 36, 0.85)'
                        ],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#94A3B8',
                                font: { size: 11 },
                                boxWidth: 10,
                                padding: 15
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection
