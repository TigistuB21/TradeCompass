@auth
    @if(auth()->user()->hasRole('trader'))
        @php
            $tradeAccounts = auth()->user()->tradeAccounts()->withCount('trades')->get();
            $selectedAccountId = request('trade_account_id') ?? session('active_trade_account_id') ?? $tradeAccounts->first()?->id;
            $currentAccount = $tradeAccounts->firstWhere('id', $selectedAccountId) ?? $tradeAccounts->first();
        @endphp

        <div class="relative" x-data="{ open: false }" @click.away="open = false">
            @if($currentAccount)
                <!-- Trigger Button -->
                <button @click="open = !open" 
                        class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg bg-[#111620] hover:bg-[#161D27] border border-white/5 hover:border-slate-700 text-left transition-all duration-150 group">
                    <div class="w-7 h-7 rounded-md bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 group-hover:bg-indigo-500/20 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-semibold text-white tracking-tight truncate max-w-[110px] sm:max-w-[140px]">
                                {{ $currentAccount->account_name }}
                            </span>
                            <span class="text-[9px] uppercase px-1.5 py-0.2 rounded font-semibold {{ $currentAccount->account_type === 'live' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400 border border-slate-700' }}">
                                {{ $currentAccount->account_type }}
                            </span>
                        </div>
                        <span class="text-[11px] font-mono font-medium text-emerald-400 tabular-nums">
                            {{ $currentAccount->currency }} {{ number_format($currentAccount->current_balance ?? $currentAccount->initial_balance, 2) }}
                        </span>
                    </div>
                    <svg class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-300 ml-1 transition-transform duration-200" 
                         :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <!-- Dropdown Popover -->
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                     class="absolute right-0 mt-2 w-72 rounded-xl bg-[#161D27] border border-white/10 shadow-2xl shadow-black/80 z-50 overflow-hidden"
                     style="display: none;">
                    
                    <!-- Header -->
                    <div class="px-4 py-2.5 border-b border-slate-800/80 flex items-center justify-between bg-[#111620]">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Trading Accounts</span>
                        <span class="text-[10px] text-slate-500 font-mono">{{ $tradeAccounts->count() }} registered</span>
                    </div>

                    <!-- Accounts List -->
                    <div class="max-h-60 overflow-y-auto divide-y divide-slate-800/50">
                        @foreach($tradeAccounts as $acc)
                            <a href="{{ route('trader.accounts.show', $acc) }}" 
                               class="flex items-center justify-between px-4 py-2.5 hover:bg-[#1E2533] transition-colors {{ $acc->id === $currentAccount->id ? 'bg-indigo-600/10' : '' }}">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-2 h-2 rounded-full {{ $acc->id === $currentAccount->id ? 'bg-emerald-400 shadow-[0_0_6px_#10B981]' : 'bg-slate-600' }}"></div>
                                    <div class="truncate">
                                        <p class="text-xs font-medium text-slate-100 truncate {{ $acc->id === $currentAccount->id ? 'text-indigo-400' : '' }}">
                                            {{ $acc->account_name }}
                                        </p>
                                        <p class="text-[10px] text-slate-400 truncate">
                                            {{ $acc->broker ?? 'Direct' }} • {{ $acc->trades_count }} trades
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0 ml-2">
                                    <span class="text-xs font-mono font-semibold text-slate-200 tabular-nums">
                                        {{ $acc->currency }} {{ number_format($acc->current_balance ?? $acc->initial_balance, 2) }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <!-- Actions Footer -->
                    <div class="p-2 border-t border-slate-800/80 bg-[#111620]/80 flex items-center justify-between gap-2">
                        <a href="{{ route('trader.accounts.create') }}" 
                           class="flex-1 flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600/20 hover:bg-indigo-600/30 border border-indigo-500/30 text-indigo-300 text-xs font-medium transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Add Account</span>
                        </a>
                        <a href="{{ route('trader.accounts.index') }}" 
                           class="flex-1 flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800/60 hover:bg-slate-800 border border-slate-700/60 text-slate-300 text-xs font-medium transition-colors">
                            <span>Manage All</span>
                        </a>
                    </div>
                </div>
            @else
                <!-- No accounts created yet prompt -->
                <a href="{{ route('trader.accounts.create') }}" 
                   class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-indigo-600/20 hover:bg-indigo-600/30 border border-indigo-500/30 text-indigo-300 text-xs font-medium transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Create Trade Account</span>
                </a>
            @endif
        </div>
    @endif
@endauth
