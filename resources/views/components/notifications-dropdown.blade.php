{{-- Modernized Notification Dropdown Component --}}
@php
    $unreadCount = $unreadCount ?? (auth()->check() ? \App\Models\Notification::where('user_id', auth()->id())->whereNull('read_at')->count() : 0);
    $notifications = $notifications ?? (auth()->check() ? \App\Models\Notification::where('user_id', auth()->id())->latest()->take(6)->get() : collect());
@endphp

<div class="relative" x-data="{ open: false }" @click.away="open = false">
    <!-- Notification Bell Button -->
    <button @click="open = !open" 
            class="relative p-2 rounded-lg text-slate-400 hover:text-white hover:bg-[#161D27] border border-transparent hover:border-slate-800 transition-all duration-150 group"
            title="Notifications">
        <svg class="w-5 h-5 text-slate-400 group-hover:text-slate-200 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        
        <!-- Unread Badge -->
        @if($unreadCount > 0)
            <span class="absolute top-1 right-1 flex h-4 min-w-[16px] px-1 items-center justify-center rounded-full bg-rose-500 text-[10px] font-semibold text-white border border-rose-400/30">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown Panel -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
         class="absolute right-0 mt-2 w-80 sm:w-88 rounded-xl bg-[#161D27] border border-white/10 shadow-2xl shadow-black/80 z-50 overflow-hidden"
         style="display: none;">
        
        <!-- Header -->
        <div class="px-4 py-3 border-b border-slate-800 bg-[#111620] flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-300">Notifications</span>
                @if($unreadCount > 0)
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-medium bg-rose-500/20 text-rose-400 border border-rose-500/30">
                        {{ $unreadCount }} new
                    </span>
                @endif
            </div>
            @if($unreadCount > 0)
                <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-xs text-sky-400 hover:text-sky-300 font-medium transition-colors">
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>

        <!-- Notifications List -->
        <div class="max-h-96 overflow-y-auto divide-y divide-slate-800/60">
            @forelse($notifications as $notification)
                <a href="{{ $notification->data['url'] ?? '#' }}" 
                   class="block px-4 py-3 hover:bg-[#1E2533] transition-colors {{ $notification->isUnread() ? 'bg-sky-500/5' : '' }}"
                   onclick="markAsRead({{ $notification->id }})">
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 mt-1.5 rounded-full flex-shrink-0 {{ $notification->isUnread() ? 'bg-sky-400' : 'bg-transparent' }}"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-slate-200 line-clamp-2">
                                {{ $notification->data['message'] ?? $notification->data['title'] ?? 'New notification' }}
                            </p>
                            <span class="text-[10px] font-mono text-slate-500 mt-1 block">
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="px-4 py-8 text-center">
                    <svg class="w-8 h-8 text-slate-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <p class="text-xs text-slate-400 font-medium">No notifications yet</p>
                    <p class="text-[10px] text-slate-600 mt-0.5">We'll alert you when analysts review your trades</p>
                </div>
            @endforelse
        </div>

        <!-- Footer -->
        <div class="px-4 py-2 border-t border-slate-800 bg-[#111620] text-center">
            <a href="{{ route('notifications.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium transition-colors">
                View all notifications &rarr;
            </a>
        </div>
    </div>
</div>

<script>
    function markAsRead(id) {
        fetch(`/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        }).catch(err => console.error('Failed to mark read', err));
    }
</script>
