@extends('layouts.app')

@section('title', 'Trade #' . $trade->id . ' - ' . $trade->pair)

@section('content')
<div class="space-y-6" x-data="{ 
    lightboxOpen: false,
    deleteModalOpen: false
}">

    <!-- ======================================================== -->
    <!-- BREADCRUMB & BACK ACTION                                 -->
    <!-- ======================================================== -->
    <div class="flex items-center justify-between">
        <nav class="flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('trader.dashboard') }}" class="hover:text-white transition-colors">Dashboard</a>
            <span>/</span>
            <a href="{{ route('trader.trades.index') }}" class="hover:text-white transition-colors">Trade Journal</a>
            <span>/</span>
            <span class="text-white font-mono font-medium">Trade #{{ $trade->id }}</span>
        </nav>

        <a href="{{ route('trader.trades.index') }}" 
           class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Back to Journal</span>
        </a>
    </div>

    <!-- ======================================================== -->
    <!-- HERO EXECUTION RIBBON                                    -->
    <!-- ======================================================== -->
    @php
        $isBuy = strtolower(is_object($trade->direction) ? $trade->direction->value : $trade->direction) === 'buy';
        $outVal = strtolower(is_object($trade->outcome) ? $trade->outcome->value : (string)$trade->outcome);
        $sessVal = strtolower(is_object($trade->session) ? $trade->session->value : (string)$trade->session);
        
        $chartUrl = $trade->chart_link ?: $trade->tradingview_link;
        $isTvSnapshot = $chartUrl && str_contains($chartUrl, 'tradingview.com/x/');
        $previewUrl = $chartUrl;
        if ($isTvSnapshot && !str_ends_with($chartUrl, '.png') && !str_ends_with($chartUrl, '.jpg')) {
            $previewUrl = rtrim($chartUrl, '/') . '.png';
        }
    @endphp

    <div class="bg-[#111620] border border-white/5 rounded-2xl p-5 shadow-xl relative overflow-hidden">
        <!-- Ambient Background Glow -->
        

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
            <!-- Left: Instrument & Metadata Badges -->
            <div class="space-y-2.5">
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight font-mono">
                        {{ $trade->pair }}
                    </h1>

                    <!-- Direction Pill -->
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider {{ $isBuy ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($isBuy)
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            @endif
                        </svg>
                        <span>{{ $isBuy ? 'BUY' : 'SELL' }}</span>
                    </span>

                    <!-- Outcome Badge -->
                    @if($outVal)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider border {{ $outVal === 'win' ? 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30' : ($outVal === 'loss' ? 'bg-rose-500/15 text-rose-400 border-rose-500/30' : 'bg-amber-500/15 text-amber-300 border-amber-500/30') }}">
                            {{ $outVal }}
                        </span>
                    @endif

                    <!-- Compliance Flag -->
                    @if($trade->is_compliant === false)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-300 border border-amber-500/20 flex items-center gap-1" title="{{ $trade->violation_reason }}">
                            <svg class="w-3 h-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Rule Violation</span>
                        </span>
                    @endif
                </div>

                <!-- Secondary Metadata -->
                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400">
                    <span class="flex items-center gap-1 text-slate-300">
                        <span class="text-slate-500">Account:</span>
                        <strong class="font-medium text-white">{{ $trade->tradeAccount?->account_name ?? ($trade->account?->account_name ?? 'Standard') }}</strong>
                        <span class="text-[10px] text-slate-500">({{ $trade->tradeAccount?->currency ?? 'USD' }})</span>
                    </span>
                    <span>&bull;</span>
                    <span class="flex items-center gap-1">
                        <span class="text-slate-500">Session:</span>
                        <span class="capitalize text-slate-200 font-medium">{{ $sessVal ?: 'Unknown' }}</span>
                    </span>
                    @if($trade->strategyModel || $trade->strategy)
                        <span>&bull;</span>
                        <span class="flex items-center gap-1">
                            <span class="text-slate-500">Strategy:</span>
                            <span class="bg-indigo-500/10 text-indigo-300 border border-indigo-500/20 px-2 py-0.5 rounded text-[11px] font-medium">
                                {{ $trade->strategyModel?->name ?? $trade->strategy }}
                            </span>
                        </span>
                    @endif
                    <span>&bull;</span>
                    <span class="text-slate-400 font-mono text-[11px]">
                        {{ $trade->entry_date ? $trade->entry_date->format('M d, Y H:i') . ' UTC' : '' }}
                    </span>
                </div>
            </div>

            <!-- Right: P&L Summary & Actions -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                <!-- Metrics Box -->
                <div class="flex items-baseline sm:flex-col sm:items-end gap-3 sm:gap-0.5">
                    <div class="text-2xl sm:text-3xl font-bold font-mono tabular-nums {{ $trade->profit_loss >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                        {{ $trade->profit_loss >= 0 ? '+' : '' }}${{ number_format($trade->profit_loss, 2) }}
                    </div>
                    <div class="flex items-center gap-2 text-xs font-mono">
                        @if(!is_null($trade->pips))
                            <span class="{{ $trade->pips >= 0 ? 'text-teal-400' : 'text-rose-400' }}">
                                {{ $trade->pips >= 0 ? '+' : '' }}{{ number_format($trade->pips, 1) }} pips
                            </span>
                        @endif
                        @if($trade->risk_reward_ratio)
                            <span class="text-slate-500">&bull;</span>
                            <span class="text-indigo-300 font-medium">
                                1 : {{ number_format($trade->risk_reward_ratio, 2) }} R:R
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Actions Button Group -->
                <div class="flex items-center gap-2 pt-2 sm:pt-0 border-t sm:border-t-0 sm:border-l border-white/5 sm:pl-5">
                    <!-- Edit Button -->
                    <a href="{{ route('trader.trades.edit', $trade) }}" 
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#161D27] hover:bg-slate-800 text-slate-200 hover:text-white border border-white/10 text-xs font-semibold transition-all">
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Edit</span>
                    </a>

                    <!-- Delete Trigger -->
                    <button type="button" 
                            @click="deleteModalOpen = true"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 text-xs font-semibold transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>Delete</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- SPLIT-PANE TECHNICAL INSPECTION                         -->
    <!-- ======================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left Pane: Chart Snapshot Viewer (7 cols) -->
        <div class="lg:col-span-7 bg-[#111620] border border-white/5 rounded-2xl p-5 shadow-sm space-y-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-white/5">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                        <span>Technical Execution Chart</span>
                    </h3>
                    @if($chartUrl)
                        <a href="{{ $chartUrl }}" target="_blank" rel="noopener noreferrer" 
                           class="inline-flex items-center gap-1 text-[11px] text-indigo-400 hover:text-indigo-300 transition-colors">
                            <span>Open in TradingView</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    @endif
                </div>

                <!-- Chart Container -->
                <div class="mt-4">
                    @if($chartUrl)
                        <div class="relative group cursor-pointer overflow-hidden rounded-xl border border-white/10 bg-[#0B0E14]"
                             @click="lightboxOpen = true">
                            <img src="{{ $previewUrl }}" 
                                 alt="{{ $trade->pair }} Execution Chart" 
                                 class="w-full h-auto max-h-[380px] object-cover object-top transition-transform duration-300 group-hover:scale-[1.01]"
                                 onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'p-8 text-center text-xs text-slate-400 space-y-2\'><div>Chart preview unavailable</div><a href=\'{{ $chartUrl }}\' target=\'_blank\' class=\'text-indigo-400 underline font-medium\'>View Chart on TradingView &rarr;</a></div>';">
                            
                            <!-- Hover Zoom Overlay -->
                            <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 text-white text-xs font-semibold">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                </svg>
                                <span>Click to Enlarge / Fullscreen</span>
                            </div>
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="py-16 px-4 rounded-xl border border-dashed border-white/10 bg-[#0B0E14] text-center space-y-3">
                            <div class="w-12 h-12 mx-auto rounded-full bg-slate-800/80 flex items-center justify-center text-slate-500">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-white">No Chart Snapshot Attached</h4>
                                <p class="text-[11px] text-slate-400 mt-0.5">Attach a TradingView or broker chart link to visually review setup triggers</p>
                            </div>
                            <a href="{{ route('trader.trades.edit', $trade) }}" 
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#161D27] hover:bg-slate-800 text-slate-300 text-xs font-medium rounded-lg border border-white/10 transition-colors">
                                <span>Attach Chart Link</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Chart Footer Note -->
            @if($chartUrl)
                <div class="pt-2 text-[11px] text-slate-500 flex items-center justify-between">
                    <span>Source: TradingView / Broker Snapshot</span>
                    <span class="font-mono text-slate-400">Click preview for high-res inspection</span>
                </div>
            @endif
        </div>

        <!-- Right Pane: Tabular Execution Scorecard (5 cols) -->
        <div class="lg:col-span-5 bg-[#111620] border border-white/5 rounded-2xl p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-white/5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span>Execution Scorecard</span>
                </h3>
                <span class="text-[11px] font-mono text-slate-500">Filled Order Data</span>
            </div>

            <!-- Scorecard Key-Value List -->
            <div class="divide-y divide-white/[0.04] text-xs">
                
                <!-- Entry Price & Date -->
                <div class="py-2.5 flex items-center justify-between">
                    <span class="text-slate-400">Entry Price</span>
                    <div class="text-right">
                        <span class="font-mono font-bold text-white tabular-nums">{{ number_format($trade->entry_price, 5) }}</span>
                        <div class="text-[10px] text-slate-500 font-mono">{{ $trade->entry_date ? $trade->entry_date->format('M d, Y H:i') : '—' }}</div>
                    </div>
                </div>

                <!-- Exit Price & Date -->
                <div class="py-2.5 flex items-center justify-between">
                    <span class="text-slate-400">Exit Price</span>
                    <div class="text-right">
                        <span class="font-mono font-bold text-white tabular-nums">{{ number_format($trade->exit_price, 5) }}</span>
                        <div class="text-[10px] text-slate-500 font-mono">{{ $trade->exit_date ? $trade->exit_date->format('M d, Y H:i') : 'Open Position' }}</div>
                    </div>
                </div>

                <!-- Trade Duration -->
                <div class="py-2.5 flex items-center justify-between">
                    <span class="text-slate-400">Trade Duration</span>
                    <span class="font-mono text-slate-200 font-semibold">
                        @if($trade->duration)
                            {{ round($trade->duration, 1) }} hours
                        @elseif($trade->exit_date && $trade->entry_date)
                            {{ $trade->entry_date->diffForHumans($trade->exit_date, true) }}
                        @else
                            Active Position
                        @endif
                    </span>
                </div>

                <!-- Stop Loss -->
                <div class="py-2.5 flex items-center justify-between">
                    <span class="text-slate-400">Stop Loss</span>
                    <div class="text-right">
                        @if($trade->stop_loss)
                            <span class="font-mono font-semibold text-rose-400 tabular-nums">{{ number_format($trade->stop_loss, 5) }}</span>
                            @php
                                $slDistance = abs($trade->entry_price - $trade->stop_loss);
                            @endphp
                            <div class="text-[10px] text-slate-500 font-mono">Distance: {{ number_format($slDistance, 4) }}</div>
                        @else
                            <span class="text-slate-500 font-mono">No SL Defined</span>
                        @endif
                    </div>
                </div>

                <!-- Take Profit -->
                <div class="py-2.5 flex items-center justify-between">
                    <span class="text-slate-400">Take Profit</span>
                    <div class="text-right">
                        @if($trade->take_profit)
                            <span class="font-mono font-semibold text-emerald-400 tabular-nums">{{ number_format($trade->take_profit, 5) }}</span>
                            @php
                                $tpDistance = abs($trade->take_profit - $trade->entry_price);
                            @endphp
                            <div class="text-[10px] text-slate-500 font-mono">Target: {{ number_format($tpDistance, 4) }}</div>
                        @else
                            <span class="text-slate-500 font-mono">Discretionary Exit</span>
                        @endif
                    </div>
                </div>

                <!-- Lot Size & Capital Risk -->
                <div class="py-2.5 flex items-center justify-between">
                    <span class="text-slate-400">Position Size</span>
                    <div class="text-right">
                        <span class="font-mono font-bold text-white tabular-nums">{{ number_format($trade->lot_size, 2) }} lots</span>
                        @if($trade->risk_percentage)
                            <div class="text-[10px] text-slate-400 font-mono">Risk: {{ $trade->risk_percentage }}% capital</div>
                        @endif
                    </div>
                </div>

                <!-- Realized Edge (Pips & P/L) -->
                <div class="py-2.5 flex items-center justify-between bg-white/[0.02] px-2 rounded-lg mt-1">
                    <span class="font-semibold text-slate-300">Realized Return</span>
                    <div class="text-right">
                        <span class="font-mono font-bold text-sm {{ $trade->profit_loss >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                            {{ $trade->profit_loss >= 0 ? '+' : '' }}${{ number_format($trade->profit_loss, 2) }}
                        </span>
                        @if(!is_null($trade->pips))
                            <div class="text-[10px] font-mono {{ $trade->pips >= 0 ? 'text-teal-400' : 'text-rose-400' }}">
                                {{ $trade->pips >= 0 ? '+' : '' }}{{ number_format($trade->pips, 1) }} pips
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- TRADING PSYCHOLOGY & EXECUTION DISCIPLINE MATRIX         -->
    <!-- ======================================================== -->
    <div class="bg-[#111620] border border-white/5 rounded-2xl p-5 shadow-sm space-y-5">
        <div class="flex items-center justify-between pb-3 border-b border-white/5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Trading Psychology & Discipline</span>
            </h3>
            <span class="text-[11px] text-slate-500 font-medium">Self-Regulation Audit</span>
        </div>

        <!-- Plan Adherence Banner -->
        @if(!is_null($trade->followed_plan))
            <div class="p-3.5 rounded-xl border flex items-center justify-between gap-3 {{ $trade->followed_plan ? 'bg-emerald-500/10 border-emerald-500/25 text-emerald-300' : 'bg-rose-500/10 border-rose-500/25 text-rose-300' }}">
                <div class="flex items-center gap-2.5">
                    @if($trade->followed_plan)
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    @else
                        <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    @endif
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider">
                            {{ $trade->followed_plan ? 'Strictly Followed Trading Plan' : 'Plan Disregarded / Execution Mistake' }}
                        </h4>
                        <p class="text-[11px] opacity-80 mt-0.5">
                            {{ $trade->followed_plan ? 'Execution matched defined playbook criteria without emotional intervention.' : 'Trade was entered or managed outside predefined playbook parameters.' }}
                        </p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $trade->followed_plan ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                    {{ $trade->followed_plan ? 'Disciplined' : 'Rule Violation' }}
                </span>
            </div>
        @endif

        <!-- Risk Compliance Alert (if flagged) -->
        @if($trade->is_compliant === false && $trade->violation_reason)
            <div class="p-3.5 rounded-xl border border-amber-500/30 bg-amber-500/10 text-amber-300 flex items-start gap-2.5 text-xs">
                <svg class="w-4 h-4 text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div>
                    <h5 class="font-bold">Risk Management Warning</h5>
                    <p class="text-[11px] opacity-90 mt-0.5">{{ $trade->violation_reason }}</p>
                </div>
            </div>
        @endif

        <!-- Pre vs Post Trade Emotional States -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Pre-Trade Emotion -->
            <div class="p-4 rounded-xl bg-[#0B0E14] border border-white/5 space-y-1.5">
                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Pre-Trade Mindset</span>
                <div class="flex items-center gap-2 pt-1">
                    <div class="w-7 h-7 rounded-lg bg-[#161D27] border border-white/10 flex items-center justify-center text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <h5 class="text-xs font-bold text-white">
                            {{ is_object($trade->pre_trade_emotion) ? $trade->pre_trade_emotion->label() : ($trade->pre_trade_emotion ?: ($trade->emotion ?: 'Not recorded')) }}
                        </h5>
                        <p class="text-[10px] text-slate-400">Emotional state before order placement</p>
                    </div>
                </div>
            </div>

            <!-- Post-Trade Emotion -->
            <div class="p-4 rounded-xl bg-[#0B0E14] border border-white/5 space-y-1.5">
                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Post-Trade Mindset</span>
                <div class="flex items-center gap-2 pt-1">
                    <div class="w-7 h-7 rounded-lg bg-[#161D27] border border-white/10 flex items-center justify-center text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h5 class="text-xs font-bold text-white">
                            {{ is_object($trade->post_trade_emotion) ? $trade->post_trade_emotion->label() : ($trade->post_trade_emotion ?: 'Not recorded') }}
                        </h5>
                        <p class="text-[10px] text-slate-400">Emotional state after position liquidation</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Split Notes: Setup Thesis vs Post-Mortem Lessons -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Setup Reasons / Thesis -->
            <div class="p-4 rounded-xl bg-[#0B0E14] border border-white/5 space-y-2">
                <div class="flex items-center gap-2 text-xs font-bold text-indigo-300 uppercase tracking-wider">
                    <svg class="w-3.5 h-3.5 text-indigo-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Setup Thesis & Entry Triggers
                </div>
                <p class="text-xs text-slate-300 whitespace-pre-wrap leading-relaxed">
                    {{ $trade->setup_notes ?: ($trade->notes ?: 'No technical setup notes provided for this execution.') }}
                </p>
            </div>

            <!-- Mistakes & Lessons Learned -->
            <div class="p-4 rounded-xl bg-[#0B0E14] border border-white/5 space-y-2">
                <div class="flex items-center gap-2 text-xs font-bold text-amber-300 uppercase tracking-wider">
                    <svg class="w-3.5 h-3.5 text-amber-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg> Execution Post-Mortem & Lessons
                </div>
                <p class="text-xs text-slate-300 whitespace-pre-wrap leading-relaxed">
                    {{ $trade->mistakes_lessons ?: 'No execution errors or retrospective notes logged.' }}
                </p>
            </div>
        </div>

        <!-- Guided Journaling Focus Area Data (if populated) -->
        @if(!empty($trade->focus_data) && is_array($trade->focus_data))
            <div class="p-4 rounded-xl bg-[#0B0E14] border border-indigo-500/20 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-white/5">
                    <h4 class="text-xs font-bold text-indigo-300 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-indigo-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg> Guided Journaling Audit Data
                    </h4>
                    <span class="text-[10px] text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded font-mono">
                        Institutional Track
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    @if(isset($trade->focus_data['tilt_level']))
                        <div class="p-2.5 rounded-lg bg-white/[0.02] border border-white/5">
                            <span class="text-[10px] text-slate-500 uppercase">Tilt Level</span>
                            <p class="font-mono font-bold text-white">{{ $trade->focus_data['tilt_level'] }} / 10</p>
                        </div>
                    @endif
                    @if(isset($trade->focus_data['execution_grade']))
                        <div class="p-2.5 rounded-lg bg-white/[0.02] border border-white/5">
                            <span class="text-[10px] text-slate-500 uppercase">Execution Grade</span>
                            <p class="font-mono font-bold text-emerald-400">{{ $trade->focus_data['execution_grade'] }}</p>
                        </div>
                    @endif
                    @if(isset($trade->focus_data['slippage']))
                        <div class="p-2.5 rounded-lg bg-white/[0.02] border border-white/5">
                            <span class="text-[10px] text-slate-500 uppercase">Slippage</span>
                            <p class="font-mono font-bold text-white">{{ $trade->focus_data['slippage'] }} pips</p>
                        </div>
                    @endif
                    @if(isset($trade->focus_data['risk_acceptance']))
                        <div class="p-2.5 rounded-lg bg-white/[0.02] border border-white/5">
                            <span class="text-[10px] text-slate-500 uppercase">Risk Acceptance</span>
                            <p class="font-mono font-bold text-white capitalize">{{ $trade->focus_data['risk_acceptance'] }}</p>
                        </div>
                    @endif
                    @if(!empty($trade->focus_data['confluences']))
                        <div class="sm:col-span-3 p-2.5 rounded-lg bg-white/[0.02] border border-white/5">
                            <span class="text-[10px] text-slate-500 uppercase">Confluence Factors</span>
                            <p class="text-xs text-slate-300 mt-1 whitespace-pre-wrap">{{ $trade->focus_data['confluences'] }}</p>
                        </div>
                    @endif
                    @if(!empty($trade->focus_data['mental_notes']))
                        <div class="sm:col-span-3 p-2.5 rounded-lg bg-white/[0.02] border border-white/5">
                            <span class="text-[10px] text-slate-500 uppercase">Mental State Dialogue</span>
                            <p class="text-xs text-slate-300 mt-1 whitespace-pre-wrap">{{ $trade->focus_data['mental_notes'] }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

    </div>

    <!-- ======================================================== -->
    <!-- MENTOR & AI FEEDBACK PANEL                               -->
    <!-- ======================================================== -->
    <div class="bg-[#111620] border border-white/5 rounded-2xl p-5 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-white/5">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </div>
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-white">Mentor & Institutional Feedback</h3>
                    <p class="text-[11px] text-slate-400">Coaching critique, execution strengths, and recommendations</p>
                </div>
            </div>

            @if($trade->feedback && $trade->feedback->count() > 0)
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    {{ $trade->feedback->count() }} {{ Str::plural('Review', $trade->feedback->count()) }}
                </span>
            @endif
        </div>

        @if($trade->feedback && $trade->feedback->count() > 0)
            <div class="space-y-4">
                @foreach($trade->feedback as $item)
                    <div class="p-4 rounded-xl bg-[#0B0E14] border border-white/5 space-y-4">
                        <!-- Analyst / Reviewer Header -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-indigo-600 to-indigo-500 flex items-center justify-center font-bold text-white text-xs">
                                    {{ substr($item->analyst?->name ?? 'AI Coach', 0, 1) }}
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-white">{{ $item->analyst?->name ?? 'Institutional AI Coach' }}</h4>
                                    <p class="text-[10px] text-slate-400">
                                        Reviewed {{ $item->submitted_at ? $item->submitted_at->format('M d, Y H:i') : $item->created_at->format('M d, Y') }}
                                    </p>
                                </div>
                            </div>

                            @if($item->confidence_rating)
                                <div class="flex items-center gap-0.5 text-amber-400" title="Confidence: {{ $item->confidence_rating }}/5">
                                    @for($s = 1; $s <= 5; $s++)
                                        <svg class="w-3.5 h-3.5 {{ $s <= $item->confidence_rating ? 'text-amber-400 fill-amber-400' : 'text-slate-600' }}" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                            @endif
                        </div>

                        <!-- Strengths / Weaknesses / Recommendations Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <!-- Strengths -->
                            @if(!empty($item->strengths) && is_array($item->strengths))
                                <div class="p-3 rounded-lg bg-emerald-500/5 border border-emerald-500/20 space-y-1.5">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400 flex items-center gap-1">
                                        <svg class="w-3 h-3 text-emerald-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Key Strengths
                                    </span>
                                    <ul class="text-xs text-slate-300 space-y-1 list-disc list-inside">
                                        @foreach($item->strengths as $st)
                                            <li>{{ $st }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Weaknesses -->
                            @if(!empty($item->weaknesses) && is_array($item->weaknesses))
                                <div class="p-3 rounded-lg bg-rose-500/5 border border-rose-500/20 space-y-1.5">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-rose-400 flex items-center gap-1">
                                        <svg class="w-3 h-3 text-rose-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Areas for Improvement
                                    </span>
                                    <ul class="text-xs text-slate-300 space-y-1 list-disc list-inside">
                                        @foreach($item->weaknesses as $wk)
                                            <li>{{ $wk }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Recommendations -->
                            @if(!empty($item->recommendations) && is_array($item->recommendations))
                                <div class="p-3 rounded-lg bg-indigo-500/5 border border-indigo-500/20 space-y-1.5">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-400 flex items-center gap-1">
                                        <svg class="w-3 h-3 text-indigo-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg> Actionable Recommendations
                                    </span>
                                    <ul class="text-xs text-slate-300 space-y-1 list-disc list-inside">
                                        @foreach($item->recommendations as $rec)
                                            <li>{{ $rec }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>

                        <!-- Written Feedback Content -->
                        @if($item->content)
                            <div class="pt-2 border-t border-white/5">
                                <p class="text-xs text-slate-300 whitespace-pre-wrap leading-relaxed">{{ $item->content }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <!-- No Feedback State -->
            <div class="py-10 px-4 rounded-xl border border-dashed border-white/10 bg-[#0B0E14] text-center space-y-2">
                <div class="w-10 h-10 mx-auto rounded-full bg-slate-800/80 flex items-center justify-center text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h4 class="text-xs font-semibold text-white">No Analyst Feedback Yet</h4>
                <p class="text-[11px] text-slate-400 max-w-md mx-auto">
                    This execution has not yet received written feedback from an institutional analyst. Active mentors review flagged journal trades regularly.
                </p>
                <a href="{{ route('trader.feedback.index') }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#161D27] hover:bg-slate-800 text-slate-300 text-xs font-medium rounded-lg border border-white/10 transition-colors">
                    <span>View All Feedback</span>
                </a>
            </div>
        @endif
    </div>

    <!-- ======================================================== -->
    <!-- LIGHTBOX MODAL FOR CHART SNAPSHOT                        -->
    <!-- ======================================================== -->
    @if($chartUrl)
        <div x-show="lightboxOpen" 
             x-cloak 
             @keydown.escape.window="lightboxOpen = false"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/90 backdrop-blur-md"
             role="dialog" 
             aria-modal="true">
            
            <!-- Close Button -->
            <button type="button" 
                    @click="lightboxOpen = false" 
                    class="absolute top-4 right-4 p-2.5 rounded-full bg-white/10 hover:bg-white/20 text-white transition-colors"
                    title="Close (Esc)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <!-- Lightbox Image -->
            <div class="max-w-6xl max-h-[90vh] overflow-auto rounded-xl border border-white/10 shadow-2xl"
                 @click.outside="lightboxOpen = false">
                <img src="{{ $previewUrl }}" 
                     alt="{{ $trade->pair }} Full Chart Snapshot" 
                     class="w-full h-auto object-contain">
            </div>
        </div>
    @endif

    <!-- ======================================================== -->
    <!-- DELETE CONFIRMATION MODAL                                -->
    <!-- ======================================================== -->
    <div x-show="deleteModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" 
             @click="deleteModalOpen = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-xl bg-[#111620] border border-white/10 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white" id="modal-title">Delete Trade #{{ $trade->id }}</h3>
                        <p class="text-xs text-slate-400">Permanently soft-delete this trade record from your journal?</p>
                    </div>
                </div>

                <p class="text-xs text-slate-400 mb-6 bg-slate-900/50 p-3 rounded-lg border border-white/5">
                    This execution will be removed from your trade history and journal analytics will be automatically recalculated.
                </p>

                <form action="{{ route('trader.trades.destroy', $trade) }}" method="POST" class="flex items-center justify-end gap-3">
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
@endsection
