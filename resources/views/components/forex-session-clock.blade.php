<div x-data="{
    utcTime: '',
    utcDate: '',
    sessions: {
        sydney: false,
        tokyo: false,
        london: false,
        newyork: false,
        overlap: false
    },
    init() {
        this.updateTime();
        setInterval(() => this.updateTime(), 1000);
    },
    updateTime() {
        const now = new Date();
        const hours = now.getUTCHours();
        const minutes = now.getUTCMinutes();
        const seconds = now.getUTCSeconds();
        const timeDec = hours + (minutes / 60);

        this.utcTime = String(hours).padStart(2, '0') + ':' + 
                       String(minutes).padStart(2, '0') + ':' + 
                       String(seconds).padStart(2, '0');

        const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        this.utcDate = days[now.getUTCDay()] + ', ' + now.getUTCDate() + ' ' + months[now.getUTCMonth()];

        // Sydney: 22:00 - 07:00 UTC
        this.sessions.sydney = (timeDec >= 22 || timeDec < 7);
        
        // Tokyo: 00:00 - 09:00 UTC
        this.sessions.tokyo = (timeDec >= 0 && timeDec < 9);

        // London: 08:00 - 16:30 UTC
        this.sessions.london = (timeDec >= 8 && timeDec < 16.5);

        // New York: 13:00 - 21:00 UTC
        this.sessions.newyork = (timeDec >= 13 && timeDec < 21);

        // London / NY Overlap: 13:00 - 16:30 UTC
        this.sessions.overlap = (this.sessions.london && this.sessions.newyork);
    }
}" class="relative flex items-center gap-2 sm:gap-3 px-3 py-1.5 rounded-lg bg-[#111620] border border-white/5 text-xs text-slate-300">

    <!-- UTC Clock -->
    <div class="flex items-center gap-2 pr-2.5 border-r border-slate-800">
        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        <div class="flex flex-col">
            <span class="font-mono font-semibold tracking-wider text-slate-100 tabular-nums" x-text="utcTime">--:--:--</span>
            <span class="text-[9px] uppercase font-medium tracking-wider text-slate-500">UTC</span>
        </div>
    </div>

    <!-- Live Sessions Strip -->
    <div class="hidden sm:flex items-center gap-1.5">
        <!-- Sydney -->
        <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-md transition-colors"
             :class="sessions.sydney ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 font-medium' : 'text-slate-500 border border-transparent'">
            <span class="w-1.5 h-1.5 rounded-full" :class="sessions.sydney ? 'bg-emerald-400 ring-1 ring-emerald-500/40' : 'bg-slate-700'"></span>
            <span class="text-[11px] font-mono">SYD</span>
        </div>

        <!-- Tokyo -->
        <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-md transition-colors"
             :class="sessions.tokyo ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 font-medium' : 'text-slate-500 border border-transparent'">
            <span class="w-1.5 h-1.5 rounded-full" :class="sessions.tokyo ? 'bg-emerald-400 ring-1 ring-emerald-500/40' : 'bg-slate-700'"></span>
            <span class="text-[11px] font-mono">TKO</span>
        </div>

        <!-- London -->
        <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-md transition-colors"
             :class="sessions.london ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 font-medium' : 'text-slate-500 border border-transparent'">
            <span class="w-1.5 h-1.5 rounded-full" :class="sessions.london ? 'bg-emerald-400 ring-1 ring-emerald-500/40' : 'bg-slate-700'"></span>
            <span class="text-[11px] font-mono">LDN</span>
        </div>

        <!-- New York -->
        <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-md transition-colors"
             :class="sessions.newyork ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 font-medium' : 'text-slate-500 border border-transparent'">
            <span class="w-1.5 h-1.5 rounded-full" :class="sessions.newyork ? 'bg-emerald-400 ring-1 ring-emerald-500/40' : 'bg-slate-700'"></span>
            <span class="text-[11px] font-mono">NYC</span>
        </div>

        <!-- High Liquidity Overlap Badge -->
        <template x-if="sessions.overlap">
            <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-amber-500/10 border border-amber-500/30 text-amber-300 font-semibold tracking-wider uppercase text-[10px]">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                <span>OVERLAP</span>
            </div>
        </template>
    </div>
</div>
