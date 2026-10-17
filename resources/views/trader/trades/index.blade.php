@extends('layouts.app')

@section('title', 'Trade Log & Journal History')

@section('content')
<div class="space-y-6" x-data="{ 
    filterOpen: true,
    deleteModalOpen: false,
    deleteActionUrl: '',
    confirmDelete(url) {
        this.deleteActionUrl = url;
        this.deleteModalOpen = true;
    }
}">

    <!-- Page Header & Action Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2 border-b border-white/5">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white tracking-tight">Trade Journal & History</h1>
                    <p class="text-xs text-slate-400">Review executions, analyze statistical edge, and filter trade journal records</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5 self-start md:self-auto">
            <!-- CSV Export CTA preserving active filter parameters -->
            <a href="{{ route('trader.trades.index', array_merge(request()->query(), ['export' => 'csv'])) }}" 
               class="inline-flex items-center gap-2 px-3 py-2 bg-[#161D27] hover:bg-slate-800 text-slate-300 hover:text-white border border-white/10 rounded-lg text-xs font-medium transition-all shadow-sm group"
               title="Export filtered trades as CSV">
                <svg class="w-4 h-4 text-slate-400 group-hover:text-emerald-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Export CSV</span>
            </a>

            <!-- Quick Log Trade Drawer Trigger -->
            <button type="button" 
                    onclick="openTradeModal()" 
                    class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-lg border border-emerald-500/30 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Log New Trade</span>
            </button>
        </div>
    </div>

    <!-- Flash Notifications -->
    @if(session('success'))
        <div class="px-4 py-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white">&times;</button>
        </div>
    @endif

    <!-- Subset Summary Strip (4 KPI Blocks) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
        <!-- Filtered Trades Count -->
        <div class="bg-[#111620] border border-white/5 rounded-xl p-4 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-2 -top-2 w-16 h-16 bg-slate-800/20 rounded-full blur-xl group-hover:bg-slate-700/30 transition-all"></div>
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Filtered Trades</span>
                <span class="text-xs text-slate-500">Total</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold font-mono tabular-nums text-white">
                    {{ number_format($summary['total_trades']) }}
                </span>
                <span class="text-[11px] text-slate-500">records</span>
            </div>
            <div class="mt-2 text-[10px] text-slate-400 flex items-center gap-1.5">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                <span>Active Scope</span>
            </div>
        </div>

        <!-- Net Profit / Loss -->
        <div class="bg-[#111620] border border-white/5 rounded-xl p-4 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-2 -top-2 w-16 h-16 {{ $summary['net_profit_loss'] >= 0 ? 'bg-emerald-500/10' : 'bg-rose-500/10' }} rounded-full blur-xl transition-all"></div>
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Net P&L</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded {{ $summary['net_profit_loss'] >= 0 ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                    {{ $summary['net_profit_loss'] >= 0 ? 'PROFIT' : 'DRAWDOWN' }}
                </span>
            </div>
            <div class="flex items-baseline gap-1">
                <span class="text-2xl font-bold font-mono tabular-nums {{ $summary['net_profit_loss'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                    {{ $summary['net_profit_loss'] >= 0 ? '+' : '' }}${{ number_format($summary['net_profit_loss'], 2) }}
                </span>
            </div>
            <div class="mt-2 text-[10px] text-slate-400 flex items-center justify-between">
                <span>Profit Factor</span>
                <span class="font-mono font-semibold text-slate-300">{{ number_format($summary['profit_factor'], 2) }}</span>
            </div>
        </div>

        <!-- Win Rate -->
        <div class="bg-[#111620] border border-white/5 rounded-xl p-4 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-2 -top-2 w-16 h-16 bg-blue-500/10 rounded-full blur-xl group-hover:bg-blue-500/20 transition-all"></div>
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Win Rate</span>
                <span class="text-[10px] text-slate-500">Closed</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold font-mono tabular-nums {{ $summary['win_rate'] >= 50 ? 'text-emerald-400' : 'text-amber-400' }}">
                    {{ number_format($summary['win_rate'], 1) }}%
                </span>
            </div>
            <!-- Win Rate Progress Bar -->
            <div class="mt-2.5 w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                <div class="h-1.5 rounded-full {{ $summary['win_rate'] >= 50 ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ min(max($summary['win_rate'], 0), 100) }}%"></div>
            </div>
        </div>

        <!-- Net Pips -->
        <div class="bg-[#111620] border border-white/5 rounded-xl p-4 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-2 -top-2 w-16 h-16 {{ $summary['net_pips'] >= 0 ? 'bg-teal-500/10' : 'bg-rose-500/10' }} rounded-full blur-xl transition-all"></div>
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Net Pips</span>
                <span class="text-xs text-slate-500">Forex/Metals</span>
            </div>
            <div class="flex items-baseline gap-1">
                <span class="text-2xl font-bold font-mono tabular-nums {{ $summary['net_pips'] >= 0 ? 'text-teal-400' : 'text-rose-400' }}">
                    {{ $summary['net_pips'] >= 0 ? '+' : '' }}{{ number_format($summary['net_pips'], 1) }}
                </span>
                <span class="text-[11px] text-slate-500">pips</span>
            </div>
            <div class="mt-2 text-[10px] text-slate-400">
                <span>Weighted market displacement</span>
            </div>
        </div>
    </div>

    <!-- High-Density Collapsible Filter Bar -->
    <div class="bg-[#111620] border border-white/5 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 bg-[#141B26] border-b border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <button type="button" 
                        @click="filterOpen = !filterOpen" 
                        class="flex items-center gap-2 text-xs font-semibold text-slate-200 hover:text-white transition-colors">
                    <svg class="w-3.5 h-3.5 text-emerald-400 transition-transform duration-200" :class="{ 'rotate-180': !filterOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    <span>Filter & Query Parameters</span>
                </button>

                @php
                    $activeFiltersCount = count(array_filter(request()->only([
                        'trade_account_id', 'pair', 'direction', 'outcome', 'session', 'strategy_id', 'date_from', 'date_to'
                    ])));
                @endphp

                @if($activeFiltersCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                        {{ $activeFiltersCount }} active
                    </span>
                @endif
            </div>

            @if($activeFiltersCount > 0)
                <a href="{{ route('trader.trades.index') }}" 
                   class="inline-flex items-center gap-1 text-[11px] text-slate-400 hover:text-rose-400 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span>Reset All Filters</span>
                </a>
            @endif
        </div>

        <div x-show="filterOpen" x-collapse>
            <form method="GET" action="{{ route('trader.trades.index') }}" class="p-4 space-y-3.5">
                <!-- Keep sorting parameters intact -->
                @if(request('sort'))
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                @endif
                @if(request('order'))
                    <input type="hidden" name="order" value="{{ request('order') }}">
                @endif
                @if(request('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    <!-- Account Filter -->
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 mb-1">Trading Account</label>
                        <select name="trade_account_id" class="w-full bg-[#0B0E14] border border-white/10 rounded-lg px-2.5 py-1.5 text-xs text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                            <option value="">All Accounts</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ request('trade_account_id') == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->account_name }} ({{ $acc->currency }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pair Filter -->
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 mb-1">Currency Pair</label>
                        <select name="pair" class="w-full bg-[#0B0E14] border border-white/10 rounded-lg px-2.5 py-1.5 text-xs text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                            <option value="">All Pairs</option>
                            @foreach($pairs as $p)
                                <option value="{{ $p }}" {{ request('pair') == $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Direction Filter -->
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 mb-1">Direction</label>
                        <select name="direction" class="w-full bg-[#0B0E14] border border-white/10 rounded-lg px-2.5 py-1.5 text-xs text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                            <option value="">All Directions</option>
                            @foreach($directions as $dir)
                                <option value="{{ $dir->value }}" {{ request('direction') == $dir->value ? 'selected' : '' }}>
                                    {{ $dir->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Outcome Filter -->
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 mb-1">Outcome</label>
                        <select name="outcome" class="w-full bg-[#0B0E14] border border-white/10 rounded-lg px-2.5 py-1.5 text-xs text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                            <option value="">All Outcomes</option>
                            @foreach($outcomes as $out)
                                <option value="{{ $out->value }}" {{ request('outcome') == $out->value ? 'selected' : '' }}>
                                    {{ $out->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Session Filter -->
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 mb-1">Market Session</label>
                        <select name="session" class="w-full bg-[#0B0E14] border border-white/10 rounded-lg px-2.5 py-1.5 text-xs text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                            <option value="">All Sessions</option>
                            @foreach($sessions as $sess)
                                <option value="{{ $sess->value }}" {{ request('session') == $sess->value ? 'selected' : '' }}>
                                    {{ $sess->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Strategy Filter -->
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 mb-1">Strategy</label>
                        <select name="strategy_id" class="w-full bg-[#0B0E14] border border-white/10 rounded-lg px-2.5 py-1.5 text-xs text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                            <option value="">All Strategies</option>
                            @foreach($strategies as $st)
                                <option value="{{ $st->id }}" {{ request('strategy_id') == $st->id ? 'selected' : '' }}>
                                    {{ $st->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Date Range & Action Buttons -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2 border-t border-white/5">
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] text-slate-400">From:</span>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" 
                                   class="bg-[#0B0E14] border border-white/10 rounded-lg px-2 py-1 text-xs text-white focus:border-emerald-500 outline-none">
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] text-slate-400">To:</span>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" 
                                   class="bg-[#0B0E14] border border-white/10 rounded-lg px-2 py-1 text-xs text-white focus:border-emerald-500 outline-none">
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('trader.trades.index') }}" 
                           class="px-3 py-1.5 bg-[#161D27] hover:bg-slate-800 text-slate-300 text-xs font-medium rounded-lg border border-white/10 transition-colors">
                            Reset
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            <span>Apply Filters</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table & Mobile Cards Container -->
    <div class="bg-[#111620] border border-white/5 rounded-xl shadow-sm overflow-hidden">
        
        <!-- Helper Macro for Sortable Table Headers -->
        @php
            $currentSort = request('sort', 'entry_date');
            $currentOrder = request('order', 'desc');

            $getSortUrl = function($column) use ($currentSort, $currentOrder) {
                $nextOrder = ($currentSort === $column && $currentOrder === 'desc') ? 'asc' : 'desc';
                return route('trader.trades.index', array_merge(request()->query(), [
                    'sort' => $column,
                    'order' => $nextOrder,
                    'page' => 1
                ]));
            };
        @endphp

        <!-- Desktop High-Density Table View (Hidden on small mobile) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 bg-[#0E131C]/95 backdrop-blur-md z-10 border-b border-white/5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="py-2.5 px-3.5">
                            <a href="{{ $getSortUrl('entry_date') }}" class="inline-flex items-center gap-1 hover:text-white transition-colors">
                                <span>Entry Date</span>
                                @if($currentSort === 'entry_date')
                                    @if($currentOrder === 'asc')
                                        <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                    @else
                                        <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    @endif
                                @else
                                    <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                                @endif
                            </a>
                        </th>
                        <th class="py-2.5 px-3.5">Pair & Account</th>
                        <th class="py-2.5 px-3.5">Direction</th>
                        <th class="py-2.5 px-3.5">Session</th>
                        <th class="py-2.5 px-3.5">Strategy</th>
                        <th class="py-2.5 px-3.5">
                            <a href="{{ $getSortUrl('lot_size') }}" class="inline-flex items-center gap-1 hover:text-white transition-colors">
                                <span>Lots</span>
                                @if($currentSort === 'lot_size')
                                    @if($currentOrder === 'asc')
                                        <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                    @else
                                        <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    @endif
                                @else
                                    <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                                @endif
                            </a>
                        </th>
                        <th class="py-2.5 px-3.5 text-right">Price (In / Out)</th>
                        <th class="py-2.5 px-3.5 text-right">
                            <a href="{{ $getSortUrl('pips') }}" class="inline-flex items-center gap-1 justify-end hover:text-white transition-colors">
                                <span>Pips</span>
                                @if($currentSort === 'pips')
                                    @if($currentOrder === 'asc')
                                        <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                    @else
                                        <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    @endif
                                @else
                                    <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                                @endif
                            </a>
                        </th>
                        <th class="py-2.5 px-3.5 text-right">
                            <a href="{{ $getSortUrl('profit_loss') }}" class="inline-flex items-center gap-1 justify-end hover:text-white transition-colors">
                                <span>Net P&L</span>
                                @if($currentSort === 'profit_loss')
                                    @if($currentOrder === 'asc')
                                        <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                    @else
                                        <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    @endif
                                @else
                                    <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                                @endif
                            </a>
                        </th>
                        <th class="py-2.5 px-3.5 text-center">Outcome</th>
                        <th class="py-2.5 px-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04] text-xs text-slate-300">
                    @forelse($trades as $trade)
                        <tr class="hover:bg-slate-800/40 transition-colors group">
                            <!-- Entry Date -->
                            <td class="py-2.5 px-3.5 whitespace-nowrap font-mono text-[11px] text-slate-300">
                                <div>{{ $trade->entry_date ? $trade->entry_date->format('M d, Y') : '—' }}</div>
                                <div class="text-[10px] text-slate-500">{{ $trade->entry_date ? $trade->entry_date->format('H:i') . ' UTC' : '' }}</div>
                            </td>

                            <!-- Pair & Account -->
                            <td class="py-2.5 px-3.5 whitespace-nowrap">
                                <div class="font-bold text-white tracking-tight flex items-center gap-1.5">
                                    <span>{{ $trade->pair }}</span>
                                    @if($trade->is_compliant === false)
                                        <span class="text-amber-400 text-[10px] inline-flex items-center" title="Risk non-compliant: {{ $trade->violation_reason }}"><svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-slate-400 truncate max-w-[130px]" title="{{ $trade->tradeAccount?->account_name ?? ($trade->account?->account_name ?? 'Standard') }}">
                                    {{ $trade->tradeAccount?->account_name ?? ($trade->account?->account_name ?? 'Standard') }}
                                </div>
                            </td>

                            <!-- Direction Micro-Badge -->
                            <td class="py-2.5 px-3.5 whitespace-nowrap">
                                @php
                                    $isBuy = strtolower(is_object($trade->direction) ? $trade->direction->value : $trade->direction) === 'buy';
                                @endphp
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $isBuy ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                                    {{ $isBuy ? 'BUY' : 'SELL' }}
                                </span>
                            </td>

                            <!-- Session -->
                            <td class="py-2.5 px-3.5 whitespace-nowrap">
                                @php
                                    $sessVal = strtolower(is_object($trade->session) ? $trade->session->value : (string)$trade->session);
                                    $sessColors = [
                                        'london' => 'bg-amber-500/10 text-amber-300 border-amber-500/20',
                                        'newyork' => 'bg-blue-500/10 text-blue-300 border-blue-500/20',
                                        'asia' => 'bg-purple-500/10 text-purple-300 border-purple-500/20',
                                        'tokyo' => 'bg-purple-500/10 text-purple-300 border-purple-500/20',
                                        'sydney' => 'bg-emerald-500/10 text-emerald-300 border-emerald-500/20',
                                    ];
                                    $sessClass = $sessColors[$sessVal] ?? 'bg-slate-700/20 text-slate-400 border-slate-700/30';
                                @endphp
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-medium border {{ $sessClass }}">
                                    {{ ucfirst($sessVal ?: 'Unknown') }}
                                </span>
                            </td>

                            <!-- Strategy -->
                            <td class="py-2.5 px-3.5 whitespace-nowrap text-[11px]">
                                @if($trade->strategyModel)
                                    <span class="text-indigo-300 bg-indigo-500/10 border border-indigo-500/20 px-2 py-0.5 rounded text-[10px] font-medium">
                                        {{ $trade->strategyModel->name }}
                                    </span>
                                @elseif(!empty($trade->strategy))
                                    <span class="text-slate-300 bg-slate-800 px-2 py-0.5 rounded text-[10px]">
                                        {{ $trade->strategy }}
                                    </span>
                                @else
                                    <span class="text-slate-600">—</span>
                                @endif
                            </td>

                            <!-- Lot Size -->
                            <td class="py-2.5 px-3.5 whitespace-nowrap font-mono tabular-nums text-slate-300">
                                {{ number_format($trade->lot_size, 2) }}
                            </td>

                            <!-- Entry & Exit Prices -->
                            <td class="py-2.5 px-3.5 whitespace-nowrap text-right font-mono tabular-nums text-[11px]">
                                <div class="text-slate-200">{{ number_format($trade->entry_price, 5) }}</div>
                                <div class="text-[10px] text-slate-500">{{ number_format($trade->exit_price, 5) }}</div>
                            </td>

                            <!-- Pips -->
                            <td class="py-2.5 px-3.5 whitespace-nowrap text-right font-mono tabular-nums">
                                @if(!is_null($trade->pips))
                                    <span class="{{ $trade->pips >= 0 ? 'text-teal-400' : 'text-rose-400' }} font-medium">
                                        {{ $trade->pips >= 0 ? '+' : '' }}{{ number_format($trade->pips, 1) }}
                                    </span>
                                @else
                                    <span class="text-slate-600">—</span>
                                @endif
                            </td>

                            <!-- Net P/L ($) -->
                            <td class="py-2.5 px-3.5 whitespace-nowrap text-right font-mono tabular-nums font-bold">
                                @if(!is_null($trade->profit_loss))
                                    <span class="{{ $trade->profit_loss >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                        {{ $trade->profit_loss >= 0 ? '+' : '' }}${{ number_format($trade->profit_loss, 2) }}
                                    </span>
                                @else
                                    <span class="text-slate-600">—</span>
                                @endif
                            </td>

                            <!-- Outcome Badge -->
                            <td class="py-2.5 px-3.5 whitespace-nowrap text-center">
                                @php
                                    $outVal = strtolower(is_object($trade->outcome) ? $trade->outcome->value : (string)$trade->outcome);
                                    $outColors = [
                                        'win' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                        'loss' => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
                                        'breakeven' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                    ];
                                    $outClass = $outColors[$outVal] ?? 'bg-slate-700/20 text-slate-400 border-slate-700/30';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider border {{ $outClass }}">
                                    {{ $outVal ?: 'OPEN' }}
                                </span>
                            </td>

                            <!-- Row Actions -->
                            <td class="py-2.5 px-3.5 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <!-- View Details (Show) -->
                                    <a href="{{ route('trader.trades.show', $trade) }}" 
                                       class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700/50 transition-colors" 
                                       title="View Trade Analysis">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    <!-- Edit Trade -->
                                    <a href="{{ route('trader.trades.edit', $trade) }}" 
                                       class="p-1.5 rounded-lg text-slate-400 hover:text-amber-400 hover:bg-slate-700/50 transition-colors" 
                                       title="Edit Execution Parameters">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>

                                    <!-- Delete Trade Trigger -->
                                    <button type="button" 
                                            @click="confirmDelete('{{ route('trader.trades.destroy', $trade) }}')"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-700/50 transition-colors" 
                                            title="Delete Record">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="py-12 px-4 text-center">
                                <div class="max-w-sm mx-auto">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-800/80 border border-white/5 flex items-center justify-center text-slate-500 mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <h4 class="text-sm font-semibold text-white mb-1">No Matching Trades Found</h4>
                                    <p class="text-xs text-slate-400 mb-4">No trading executions match your selected filters or journal criteria.</p>
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('trader.trades.index') }}" class="px-3 py-1.5 bg-[#161D27] hover:bg-slate-800 text-slate-300 text-xs font-medium rounded-lg border border-white/10 transition-colors">
                                            Clear Filters
                                        </a>
                                        <button type="button" onclick="openTradeModal()" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                                            Log Trade Now
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View (Shown on Viewports < 768px) -->
        <div class="block md:hidden divide-y divide-white/[0.04]">
            @forelse($trades as $trade)
                @php
                    $isBuy = strtolower(is_object($trade->direction) ? $trade->direction->value : $trade->direction) === 'buy';
                    $outVal = strtolower(is_object($trade->outcome) ? $trade->outcome->value : (string)$trade->outcome);
                @endphp
                <div class="p-4 space-y-3 hover:bg-slate-800/20 transition-colors">
                    <!-- Top Row: Pair, Badges, and Date -->
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-white text-sm tracking-tight">{{ $trade->pair }}</span>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $isBuy ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                                    {{ $isBuy ? 'BUY' : 'SELL' }}
                                </span>
                                @if($outVal)
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase {{ $outVal === 'win' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : ($outVal === 'loss' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20') }}">
                                        {{ $outVal }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-[10px] text-slate-500 mt-0.5">
                                {{ $trade->entry_date ? $trade->entry_date->format('M d, Y H:i') . ' UTC' : '—' }}
                                &bull; {{ $trade->tradeAccount?->account_name ?? 'Standard' }}
                            </div>
                        </div>

                        <!-- Right: P/L and Pips -->
                        <div class="text-right">
                            <div class="font-mono text-sm font-bold {{ $trade->profit_loss >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ $trade->profit_loss >= 0 ? '+' : '' }}${{ number_format($trade->profit_loss, 2) }}
                            </div>
                            <div class="font-mono text-[11px] {{ $trade->pips >= 0 ? 'text-teal-400' : 'text-rose-400' }}">
                                {{ $trade->pips >= 0 ? '+' : '' }}{{ number_format($trade->pips, 1) }} pips
                            </div>
                        </div>
                    </div>

                    <!-- Middle Row: Strategy, Session, Lots -->
                    <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1 border-t border-white/5">
                        <div class="flex items-center gap-2">
                            <span>Lots: <strong class="font-mono text-slate-200">{{ number_format($trade->lot_size, 2) }}</strong></span>
                            @if($trade->strategyModel)
                                <span class="bg-indigo-500/10 text-indigo-300 border border-indigo-500/20 px-1.5 py-0.5 rounded text-[10px]">
                                    {{ $trade->strategyModel->name }}
                                </span>
                            @endif
                        </div>
                        <span class="text-[10px] text-slate-500 capitalize">
                            {{ is_object($trade->session) ? $trade->session->value : ($trade->session ?: 'Unknown') }} Session
                        </span>
                    </div>

                    <!-- Actions Bar -->
                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-white/5">
                        <a href="{{ route('trader.trades.show', $trade) }}" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition-colors">
                            View
                        </a>
                        <a href="{{ route('trader.trades.edit', $trade) }}" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-amber-400 text-xs rounded-lg transition-colors">
                            Edit
                        </a>
                        <button type="button" @click="confirmDelete('{{ route('trader.trades.destroy', $trade) }}')" class="px-2.5 py-1 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 text-xs rounded-lg transition-colors">
                            Delete
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400 text-xs">
                    No trades match your criteria.
                </div>
            @endforelse
        </div>

        <!-- Table Pagination Footer -->
        @if($trades->hasPages() || $trades->total() > 0)
            <div class="px-4 py-3 bg-[#0E131C] border-t border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-400">
                <div>
                    Showing <span class="font-mono font-medium text-white">{{ $trades->firstItem() ?? 0 }}</span> to <span class="font-mono font-medium text-white">{{ $trades->lastItem() ?? 0 }}</span> of <span class="font-mono font-medium text-white">{{ $trades->total() }}</span> recorded executions
                </div>
                <div class="trade-pagination">
                    {{ $trades->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- Delete Confirmation Modal (Preserves CSRF and DELETE method) -->
    <div x-show="deleteModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" 
             @click="deleteModalOpen = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-xl bg-[#111620] border border-white/10 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white" id="modal-title">Delete Trade Record</h3>
                        <p class="text-xs text-slate-400">Are you sure you want to delete this trade from your journal?</p>
                    </div>
                </div>

                <p class="text-xs text-slate-400 mb-6 bg-slate-900/50 p-3 rounded-lg border border-white/5">
                    This action will soft-delete the trade record and recalculate your journal statistics and analytics accordingly.
                </p>

                <form :action="deleteActionUrl" method="POST" class="flex items-center justify-end gap-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" 
                            @click="deleteModalOpen = false" 
                            class="px-4 py-2 bg-[#161D27] hover:bg-slate-800 text-slate-300 text-xs font-semibold rounded-lg border border-white/10 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold rounded-lg shadow-lg shadow-rose-500/20 transition-all">
                        Confirm Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

<!-- Slide-Over Drawer for Quick Trade Creation -->
@include('trader.trades.create-modal')
@endsection
