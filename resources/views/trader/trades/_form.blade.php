@php
    $trade = $trade ?? null;
    $isEdit = !is_null($trade);
    
    // Categorize pairs for the grouped select
    $majors = ['EUR/USD', 'GBP/USD', 'USD/JPY', 'USD/CHF', 'AUD/USD', 'USD/CAD', 'NZD/USD'];
    $minors = [
        'EUR/GBP', 'EUR/JPY', 'GBP/JPY', 'EUR/CHF', 'EUR/AUD', 'EUR/CAD', 'EUR/NZD',
        'GBP/CHF', 'GBP/AUD', 'GBP/CAD', 'GBP/NZD', 'AUD/JPY', 'AUD/NZD', 'AUD/CAD',
        'CAD/JPY', 'CHF/JPY', 'NZD/JPY', 'GBP/ZAR', 'GBP/TRY'
    ];
    $commodities = ['XAU/USD', 'BTC/USD', 'ETH/USD', 'NAS100', 'US30', 'SPX500'];

    $currentPair = old('pair', $trade->pair ?? '');
    $currentDirection = old('direction', is_object($trade->direction ?? null) ? $trade->direction->value : ($trade->direction ?? 'buy'));
    $currentFollowedPlan = old('followed_plan', is_null($trade) ? '1' : ($trade->followed_plan ? '1' : '0'));
    
    // Safe default for entry date if creating
    $defaultEntryDate = old('entry_date', $trade?->entry_date ? $trade->entry_date->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i'));
    $defaultExitDate = old('exit_date', $trade?->exit_date ? $trade->exit_date->format('Y-m-d\TH:i') : '');

    // Safe fallbacks for form dependencies when included across arbitrary views/modals
    $emotions = $emotions ?? \App\Enums\TradeEmotion::cases();
    $postEmotions = $postEmotions ?? \App\Enums\PostTradeEmotion::cases();
    $accounts = $accounts ?? (auth()->check() ? auth()->user()->tradeAccounts : collect());
    $strategies = $strategies ?? (auth()->check() ? \App\Models\Strategy::where('user_id', auth()->id())->get() : collect());
    $focusArea = $focusArea ?? 'standard';
@endphp

<div x-data="{
    pair: '{{ $currentPair }}',
    direction: '{{ $currentDirection }}',
    entryPrice: '{{ old('entry_price', $trade->entry_price ?? '') }}',
    exitPrice: '{{ old('exit_price', $trade->exit_price ?? '') }}',
    stopLoss: '{{ old('stop_loss', $trade->stop_loss ?? '') }}',
    takeProfit: '{{ old('take_profit', $trade->take_profit ?? '') }}',
    lotSize: '{{ old('lot_size', $trade->lot_size ?? '1.0') }}',
    riskPercentage: '{{ old('risk_percentage', $trade->risk_percentage ?? '') }}',
    followedPlan: '{{ $currentFollowedPlan }}',

    get multiplier() {
        const p = (this.pair || '').toUpperCase();
        if (p.includes('JPY')) return 100;
        if (p.includes('XAU') || p.includes('GOLD')) return 10;
        if (p.includes('BTC') || p.includes('ETH') || p.includes('NAS') || p.includes('US30') || p.includes('SPX')) return 1;
        return 10000;
    },

    get pips() {
        const entry = parseFloat(this.entryPrice);
        const exit = parseFloat(this.exitPrice);
        if (isNaN(entry) || isNaN(exit)) return null;
        const diff = this.direction === 'buy' ? (exit - entry) : (entry - exit);
        return parseFloat((diff * this.multiplier).toFixed(2));
    },

    get estimatedPL() {
        if (this.pips === null) return null;
        const lot = parseFloat(this.lotSize) || 0;
        return parseFloat((this.pips * lot * 10).toFixed(2));
    },

    get riskRewardRatio() {
        const entry = parseFloat(this.entryPrice);
        const sl = parseFloat(this.stopLoss);
        const tp = parseFloat(this.takeProfit);
        if (isNaN(entry) || isNaN(sl) || isNaN(tp)) return null;
        const risk = Math.abs(entry - sl);
        const reward = Math.abs(tp - entry);
        if (risk <= 0) return null;
        return parseFloat((reward / risk).toFixed(2));
    },

    get projectedRiskAmount() {
        const entry = parseFloat(this.entryPrice);
        const sl = parseFloat(this.stopLoss);
        const lot = parseFloat(this.lotSize) || 0;
        if (isNaN(entry) || isNaN(sl) || lot <= 0) return null;
        const riskDistance = Math.abs(entry - sl);
        return parseFloat((riskDistance * this.multiplier * lot * 10).toFixed(2));
    }
}" class="space-y-6">

    <!-- ======================================================== -->
    <!-- REAL-TIME LIVE EXECUTION PREVIEW BAR                     -->
    <!-- ======================================================== -->
    <div class="rounded-xl bg-[#111620] border border-white/5 p-4 shadow-xl">
        <div class="flex items-center justify-between mb-3 pb-2.5 border-b border-slate-800">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                Order Summary & Execution Metrics
            </span>
            <span class="text-[11px] font-mono text-slate-500" x-text="'Multiplier: ' + multiplier"></span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
            <!-- Net Pips -->
            <div class="p-2.5 rounded-lg bg-[#161D27] border border-white/5">
                <p class="text-[10px] uppercase font-semibold text-slate-400 mb-0.5">Net Pips</p>
                <p class="text-base font-mono font-bold tabular-nums"
                   :class="{
                       'text-emerald-400': pips > 0,
                       'text-rose-400': pips < 0,
                       'text-slate-300': pips === 0 || pips === null
                   }"
                   x-text="pips !== null ? (pips > 0 ? '+' + pips : pips) + ' pips' : '--'">
                    --
                </p>
            </div>

            <!-- Estimated P&L -->
            <div class="p-2.5 rounded-lg bg-[#161D27] border border-white/5">
                <p class="text-[10px] uppercase font-semibold text-slate-400 mb-0.5">Estimated P&L</p>
                <p class="text-base font-mono font-bold tabular-nums"
                   :class="{
                       'text-emerald-400': estimatedPL > 0,
                       'text-rose-400': estimatedPL < 0,
                       'text-slate-300': estimatedPL === 0 || estimatedPL === null
                   }"
                   x-text="estimatedPL !== null ? (estimatedPL > 0 ? '+$' + estimatedPL.toLocaleString('en-US', {minimumFractionDigits: 2}) : '-$' + Math.abs(estimatedPL).toLocaleString('en-US', {minimumFractionDigits: 2})) : '--'">
                    --
                </p>
            </div>

            <!-- Risk:Reward Ratio -->
            <div class="p-2.5 rounded-lg bg-[#161D27] border border-white/5">
                <p class="text-[10px] uppercase font-semibold text-slate-400 mb-0.5">Risk : Reward</p>
                <p class="text-base font-mono font-bold tabular-nums text-slate-200"
                   x-text="riskRewardRatio !== null ? '1 : ' + riskRewardRatio : '--'">
                    --
                </p>
            </div>

            <!-- Risk Amount -->
            <div class="p-2.5 rounded-lg bg-[#161D27] border border-white/5">
                <p class="text-[10px] uppercase font-semibold text-slate-400 mb-0.5">Risked Capital</p>
                <p class="text-base font-mono font-bold tabular-nums text-rose-400"
                   x-text="projectedRiskAmount !== null ? '$' + projectedRiskAmount.toLocaleString('en-US', {minimumFractionDigits: 2}) : '--'">
                    --
                </p>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- DIRECTION SWITCHER (BUY vs SELL)                         -->
    <!-- ======================================================== -->
    <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Order Direction *</label>
        <div class="grid grid-cols-2 gap-3">
            <!-- BUY / LONG -->
            <label class="relative flex items-center justify-center p-3.5 rounded-xl border cursor-pointer transition-all duration-150"
                   :class="direction === 'buy' ? 'bg-emerald-500/15 border-emerald-500 text-emerald-300 ring-1 ring-emerald-500/40 font-semibold' : 'bg-[#111620] border-slate-800 text-slate-400 hover:bg-[#161D27] hover:text-slate-200 font-medium'">
                <input type="radio" name="direction" value="buy" x-model="direction" class="sr-only">
                <div class="flex items-center gap-2">
                    <svg class=" w-4 h-4 text-emerald-400\ fill=\none\ stroke=\currentColor\ viewBox=\0 0 24 24\><path stroke-linecap=\round\ stroke-linejoin=\round\ stroke-width=\2\ d=\M5 10l7-7m0 0l7 7m-7-7v18\/></svg>
 <span class=\text-xs font-bold tracking-wider uppercase\>Buy / Long</span>
                </div>
            </label>

            <!-- SELL / SHORT -->
            <label class="relative flex items-center justify-center p-3.5 rounded-xl border cursor-pointer transition-all duration-150"
                   :class="direction === 'sell' ? 'bg-rose-500/15 border-rose-500 text-rose-300 ring-1 ring-rose-500/40 font-semibold' : 'bg-[#111620] border-slate-800 text-slate-400 hover:bg-[#161D27] hover:text-slate-200 font-medium'">
                <input type="radio" name="direction" value="sell" x-model="direction" class="sr-only">
                <div class="flex items-center gap-2">
 <svg class=\w-4 h-4 text-rose-400\ fill=\none\ stroke=\currentColor\ viewBox=\0 0 24 24\><path stroke-linecap=\round\ stroke-linejoin=\round\ stroke-width=\2\ d=\M19 14l-7 7m0 0l-7-7m7 7V3\/></svg>
 <span class=\text-xs font-bold tracking-wider uppercase\>Sell / Short</span>
                </div>
            </label>
        </div>
        @error('direction') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
    </div>

    <!-- ======================================================== -->
    <!-- SECTION 1: INSTRUMENT, ACCOUNT & STRATEGY                -->
    <!-- ======================================================== -->
    <div class="rounded-xl bg-[#111620] border border-white/5 p-5 space-y-4">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Instrument & Account Setup
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Pair Selector -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Currency Pair / Instrument *</label>
                <select name="pair" x-model="pair"
                        class="w-full bg-[#161D27] border rounded-lg px-3.5 py-2.5 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors {{ $errors->has('pair') ? 'border-red-500' : 'border-slate-800 hover:border-slate-700' }}">
                    <option value="">Select Instrument...</option>
                    <optgroup label="Forex Majors">
                        @foreach($majors as $p)
                            <option value="{{ $p }}">{{ $p }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Forex Minors & Crosses">
                        @foreach($minors as $p)
                            <option value="{{ $p }}">{{ $p }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Metals, Indices & Crypto">
                        @foreach($commodities as $p)
                            <option value="{{ $p }}">{{ $p }}</option>
                        @endforeach
                    </optgroup>
                </select>
                @error('pair') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Trading Account -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Trading Account *</label>
                <select name="trade_account_id"
                        class="w-full bg-[#161D27] border rounded-lg px-3.5 py-2.5 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors {{ $errors->has('trade_account_id') ? 'border-red-500' : 'border-slate-800 hover:border-slate-700' }}">
                    <option value="">Select Trade Account...</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ (string)old('trade_account_id', $trade->trade_account_id ?? '') === (string)$acc->id ? 'selected' : '' }}>
                            {{ $acc->account_name }} ({{ $acc->currency }} {{ number_format($acc->current_balance ?? $acc->initial_balance, 2) }})
                        </option>
                    @endforeach
                </select>
                @error('trade_account_id') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Strategy -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Playbook Strategy</label>
                <select name="strategy_id"
                        class="w-full bg-[#161D27] border border-slate-800 hover:border-slate-700 rounded-lg px-3.5 py-2.5 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                    <option value="">No Strategy (Discretionary)</option>
                    @foreach($strategies as $st)
                        <option value="{{ $st->id }}" {{ (string)old('strategy_id', $trade->strategy_id ?? '') === (string)$st->id ? 'selected' : '' }}>
                            {{ $st->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Trade Type -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Trade Execution Type</label>
                <select name="trade_type"
                        class="w-full bg-[#161D27] border border-slate-800 hover:border-slate-700 rounded-lg px-3.5 py-2.5 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                    <option value="">Select Execution Type...</option>
                    @foreach(['Scalp', 'Day Trade', 'Swing', 'Position'] as $type)
                        <option value="{{ $type }}" {{ old('trade_type', $trade->trade_type ?? '') === $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- SECTION 2: EXECUTION PRICES & POSITION SIZING            -->
    <!-- ======================================================== -->
    <div class="rounded-xl bg-[#111620] border border-white/5 p-5 space-y-4">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Price Levels & Position Sizing
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Entry Price -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Entry Price *</label>
                <input type="number" step="0.00001" name="entry_price" x-model="entryPrice" placeholder="1.08500"
                       class="w-full bg-[#161D27] border rounded-lg px-3.5 py-2.5 text-xs font-mono tabular-nums text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors {{ $errors->has('entry_price') ? 'border-red-500' : 'border-slate-800' }}">
                @error('entry_price') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Exit Price -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Exit Price *</label>
                <input type="number" step="0.00001" name="exit_price" x-model="exitPrice" placeholder="1.09250"
                       class="w-full bg-[#161D27] border rounded-lg px-3.5 py-2.5 text-xs font-mono tabular-nums text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors {{ $errors->has('exit_price') ? 'border-red-500' : 'border-slate-800' }}">
                @error('exit_price') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Lot Size -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Position Size (Lots) *</label>
                <input type="number" step="0.01" name="lot_size" x-model="lotSize" placeholder="1.00"
                       class="w-full bg-[#161D27] border rounded-lg px-3.5 py-2.5 text-xs font-mono tabular-nums text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors {{ $errors->has('lot_size') ? 'border-red-500' : 'border-slate-800' }}">
                @error('lot_size') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Stop Loss -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Stop Loss Level</label>
                <input type="number" step="0.00001" name="stop_loss" x-model="stopLoss" placeholder="1.08200"
                       class="w-full bg-[#161D27] border border-slate-800 rounded-lg px-3.5 py-2.5 text-xs font-mono tabular-nums text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
            </div>

            <!-- Take Profit -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Take Profit Level</label>
                <input type="number" step="0.00001" name="take_profit" x-model="takeProfit" placeholder="1.09500"
                       class="w-full bg-[#161D27] border border-slate-800 rounded-lg px-3.5 py-2.5 text-xs font-mono tabular-nums text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
            </div>

            <!-- Risk Percentage -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Risk per Trade (%)</label>
                <div class="relative">
                    <input type="number" step="0.1" name="risk_percentage" x-model="riskPercentage" placeholder="1.0"
                           class="w-full bg-[#161D27] border border-slate-800 rounded-lg px-3.5 py-2.5 text-xs font-mono tabular-nums text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors pr-8">
                    <span class="absolute right-3 top-2.5 text-xs font-mono text-slate-500">%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- SECTION 3: TIMING & MARKET SESSION                       -->
    <!-- ======================================================== -->
    <div class="rounded-xl bg-[#111620] border border-white/5 p-5 space-y-4">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Execution Timing & Market Session
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Entry Date -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Entry Date & Time *</label>
                <input type="datetime-local" name="entry_date" value="{{ $defaultEntryDate }}"
                       class="w-full bg-[#161D27] border rounded-lg px-3.5 py-2.5 text-xs font-mono text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 [color-scheme:dark] transition-colors {{ $errors->has('entry_date') ? 'border-red-500' : 'border-slate-800' }}">
                @error('entry_date') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Exit Date -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Exit Date & Time</label>
                <input type="datetime-local" name="exit_date" value="{{ $defaultExitDate }}"
                       class="w-full bg-[#161D27] border border-slate-800 rounded-lg px-3.5 py-2.5 text-xs font-mono text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 [color-scheme:dark] transition-colors">
            </div>

            <!-- Session -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Market Session</label>
                <select name="session"
                        class="w-full bg-[#161D27] border border-slate-800 hover:border-slate-700 rounded-lg px-3.5 py-2.5 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                    <option value="">Auto-detect from entry time</option>
                    @foreach($sessions as $sess)
                        <option value="{{ $sess->value }}" {{ old('session', is_object($trade->session ?? null) ? $trade->session->value : ($trade->session ?? '')) === $sess->value ? 'selected' : '' }}>
                            {{ $sess->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- SECTION 4: GUIDED JOURNALING (Phase 6 Analyst Focus)     -->
    <!-- ======================================================== -->
    @if(isset($focusArea) && $focusArea !== 'standard')
        <div class="rounded-xl bg-indigo-500/10 border border-indigo-500/30 p-5 space-y-4">
            <div class="flex items-center gap-2">
 <span class=\text-xs font-bold uppercase tracking-wider text-indigo-300 flex items-center gap-1.5\><svg class=\w-4 h-4 text-indigo-400\ fill=\none\ stroke=\currentColor\ viewBox=\0 0 24 24\><path stroke-linecap=\round\ stroke-linejoin=\round\ stroke-width=\1.5\ d=\M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2\/></svg>Analyst Focus: {{ ucfirst() }}</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-500/20 text-indigo-300 uppercase">Assigned Priority</span>
            </div>

            <input type="hidden" name="focus_data[focus_type]" value="{{ $focusArea }}">

            @if($focusArea === 'psychology')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-indigo-200 mb-1.5">Stress Level (1 - 10)</label>
                        <input type="number" name="focus_data[stress_level]" min="1" max="10" value="{{ old('focus_data.stress_level', $trade->focus_data['stress_level'] ?? '') }}"
                               class="w-full bg-[#161D27] border border-indigo-500/30 rounded-lg px-3.5 py-2 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-indigo-200 mb-1.5">Primary Distraction</label>
                        <input type="text" name="focus_data[distraction]" placeholder="e.g. Social media, Tiredness" value="{{ old('focus_data.distraction', $trade->focus_data['distraction'] ?? '') }}"
                               class="w-full bg-[#161D27] border border-indigo-500/30 rounded-lg px-3.5 py-2 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-indigo-200 mb-1.5">Mental State Notes</label>
                        <textarea name="focus_data[mental_notes]" rows="2" placeholder="Describe your mindset before pulling the trigger..."
                                  class="w-full bg-[#161D27] border border-indigo-500/30 rounded-lg px-3.5 py-2 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('focus_data.mental_notes', $trade->focus_data['mental_notes'] ?? '') }}</textarea>
                    </div>
                </div>
            @elseif($focusArea === 'execution')
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-indigo-200 mb-1.5">Slippage (Pips)</label>
                        <input type="number" step="0.1" name="focus_data[slippage]" placeholder="0.0" value="{{ old('focus_data.slippage', $trade->focus_data['slippage'] ?? '') }}"
                               class="w-full bg-[#161D27] border border-indigo-500/30 rounded-lg px-3.5 py-2 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-indigo-200 mb-1.5">Time Difference (Signal vs Entry)</label>
                        <input type="text" name="focus_data[time_diff]" placeholder="e.g. Immediate, 3m late" value="{{ old('focus_data.time_diff', $trade->focus_data['time_diff'] ?? '') }}"
                               class="w-full bg-[#161D27] border border-indigo-500/30 rounded-lg px-3.5 py-2 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-indigo-200 mb-1.5">Execution Grade</label>
                        <select name="focus_data[execution_grade]" class="w-full bg-[#161D27] border border-indigo-500/30 rounded-lg px-3.5 py-2 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select Grade</option>
                            @foreach(['A' => 'A - Perfect Entry', 'B' => 'B - Good', 'C' => 'C - Hesitated/Chased', 'D' => 'D - Bad Entry', 'F' => 'F - Panic Trade'] as $val => $lbl)
                                <option value="{{ $val }}" {{ old('focus_data.execution_grade', $trade->focus_data['execution_grade'] ?? '') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @elseif($focusArea === 'risk')
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-indigo-200 mb-1.5">Confluence Checklist (3 Required Factors)</label>
                        <textarea name="focus_data[confluences]" rows="2" placeholder="1. Higher timeframe trend, 2. Key support level, 3. Bullish engulfing..."
                                  class="w-full bg-[#161D27] border border-indigo-500/30 rounded-lg px-3.5 py-2 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('focus_data.confluences', $trade->focus_data['confluences'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-indigo-200 mb-1.5">Were you completely willing to lose this amount?</label>
                        <div class="flex gap-3">
                            <label class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-indigo-500/30 bg-[#161D27] text-xs text-slate-300 cursor-pointer">
                                <input type="radio" name="focus_data[risk_acceptance]" value="yes" {{ old('focus_data.risk_acceptance', $trade->focus_data['risk_acceptance'] ?? '') === 'yes' ? 'checked' : '' }}>
                                <span>Yes, fully accepted</span>
                            </label>
                            <label class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-indigo-500/30 bg-[#161D27] text-xs text-slate-300 cursor-pointer">
                                <input type="radio" name="focus_data[risk_acceptance]" value="no" {{ old('focus_data.risk_acceptance', $trade->focus_data['risk_acceptance'] ?? '') === 'no' ? 'checked' : '' }}>
                                <span>No, felt uneasy</span>
                            </label>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- ======================================================== -->
    <!-- SECTION 5: PSYCHOLOGY & PLAN ADHERENCE                   -->
    <!-- ======================================================== -->
    <div class="rounded-xl bg-[#111620] border border-white/5 p-5 space-y-4">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Psychology & Rule Discipline
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Pre-Trade Emotion -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Pre-Trade Emotional State</label>
                <select name="pre_trade_emotion"
                        class="w-full bg-[#161D27] border border-slate-800 hover:border-slate-700 rounded-lg px-3.5 py-2.5 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                    <option value="">How did you feel before entry?</option>
                    @foreach($emotions as $em)
                        <option value="{{ $em->value }}" {{ old('pre_trade_emotion', $trade->pre_trade_emotion ?? '') === $em->value ? 'selected' : '' }}>
                            {{ $em->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Post-Trade Emotion -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Post-Trade Emotional State</label>
                <select name="post_trade_emotion"
                        class="w-full bg-[#161D27] border border-slate-800 hover:border-slate-700 rounded-lg px-3.5 py-2.5 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                    <option value="">How did you feel after exit?</option>
                    @foreach($postEmotions as $pem)
                        <option value="{{ $pem->value }}" {{ old('post_trade_emotion', $trade->post_trade_emotion ?? '') === $pem->value ? 'selected' : '' }}>
                            {{ $pem->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Followed Plan -->
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Did you strictly follow your trading plan?</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center justify-center gap-2 p-2.5 rounded-lg border cursor-pointer transition-all"
                           :class="followedPlan === '1' ? 'bg-emerald-500/15 border-emerald-500 text-emerald-300 font-semibold' : 'bg-[#161D27] border-slate-800 text-slate-400 hover:text-slate-200'">
                        <input type="radio" name="followed_plan" value="1" x-model="followedPlan" class="sr-only">
                        <svg class=\w-3.5 h-3.5 text-emerald-400\ fill=\none\ stroke=\currentColor\ viewBox=\0 0 24 24\><path stroke-linecap=\round\ stroke-linejoin=\round\ stroke-width=\2\ d=\M5 13l4 4L19 7\/></svg><span>Followed plan</span>
                    </label>
                    <label class="flex items-center justify-center gap-2 p-2.5 rounded-lg border cursor-pointer transition-all"
                           :class="followedPlan === '0' ? 'bg-rose-500/15 border-rose-500 text-rose-300 font-semibold' : 'bg-[#161D27] border-slate-800 text-slate-400 hover:text-slate-200'">
                        <input type="radio" name="followed_plan" value="0" x-model="followedPlan" class="sr-only">
                        <svg class=\w-3.5 h-3.5 text-rose-400\ fill=\none\ stroke=\currentColor\ viewBox=\0 0 24 24\><path stroke-linecap=\round\ stroke-linejoin=\round\ stroke-width=\2\ d=\M6 18L18 6M6 6l12 12\/></svg><span>Rule violation</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- SECTION 6: NOTES & MEDIA ATTACHMENTS                     -->
    <!-- ======================================================== -->
    <div class="rounded-xl bg-[#111620] border border-white/5 p-5 space-y-4">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Trade Thesis, Post-Mortem & Chart Link
        </h3>

        <div class="space-y-4">
            <!-- Setup Notes -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Trade Thesis & Setup Reasons</label>
                <textarea name="setup_notes" rows="2" placeholder="What key technical/fundamental factors triggered this entry? (e.g. 4H Order block retest, RSI divergence)"
                          class="w-full bg-[#161D27] border border-slate-800 rounded-lg px-3.5 py-2.5 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('setup_notes', $trade->setup_notes ?? '') }}</textarea>
            </div>

            <!-- Mistakes / Lessons -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Mistakes & Key Lessons</label>
                <textarea name="mistakes_lessons" rows="2" placeholder="What could have been executed better? Any early exit, hesitation, or sizing errors?"
                          class="w-full bg-[#161D27] border border-slate-800 rounded-lg px-3.5 py-2.5 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('mistakes_lessons', $trade->mistakes_lessons ?? '') }}</textarea>
            </div>

            <!-- Chart Link -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">TradingView / Broker Chart Snapshot URL</label>
                <div class="relative">
                    <input type="url" name="chart_link" value="{{ old('chart_link', $trade->chart_link ?? '') }}" placeholder="https://www.tradingview.com/x/..."
                           class="w-full bg-[#161D27] border border-slate-800 rounded-lg pl-9 pr-3.5 py-2.5 text-xs text-indigo-300 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 text-slate-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- SUBMIT ACTIONS                                           -->
    <!-- ======================================================== -->
    <div class="pt-2 flex items-center justify-end gap-3">
        <a href="{{ route('trader.trades.index') }}" 
           class="px-5 py-2.5 rounded-xl text-xs font-medium text-slate-400 hover:text-white hover:bg-[#161D27] border border-transparent transition-all">
            Cancel
        </a>
        <button type="submit" 
                class="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs shadow-lg shadow-emerald-950/40 hover:scale-[1.01] transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ $isEdit ? 'Update Execution' : 'Save Execution' }}</span>
        </button>
    </div>
</div>
