<!-- Modern Slide-Over Trade Creation Drawer / Modal -->
<div id="tradeModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity opacity-0 duration-300" 
         id="tradeModalBackdrop" 
         onclick="closeTradeModal()"></div>

    <div class="fixed inset-0 z-50 overflow-hidden pointer-events-none">
        <div class="absolute inset-y-0 right-0 max-w-full flex pl-10 pointer-events-auto">
            <!-- Slide-over Drawer Panel -->
            <div class="w-screen max-w-2xl transform transition-transform ease-out duration-300 translate-x-full bg-[#0F141C] border-l border-white/10 shadow-2xl shadow-black flex flex-col"
                 id="tradeModalPanel">
                
                <!-- Drawer Header -->
                <div class="px-6 py-4 border-b border-white/5 bg-[#111620] flex items-center justify-between flex-shrink-0 sticky top-0 z-20">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white tracking-tight" id="modal-title">
                                Quick Trade Logging
                            </h3>
                            <p class="text-[11px] text-slate-400">Record execution, position size, and discipline notes</p>
                        </div>
                    </div>
                    
                    <button type="button" 
                            onclick="closeTradeModal()" 
                            class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors"
                            title="Close Drawer (Esc)">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Form Body -->
                <form action="{{ route('trader.trades.store') }}" method="POST" class="flex-1 overflow-y-auto p-6 space-y-6">
                    @csrf
                    @include('trader.trades._form', ['trade' => null])
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const tradeModal = document.getElementById('tradeModal');
    const tradeBackdrop = document.getElementById('tradeModalBackdrop');
    const tradePanel = document.getElementById('tradeModalPanel');

    function openTradeModal() {
        if (!tradeModal) return;
        tradeModal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        // Smooth transition
        requestAnimationFrame(() => {
            tradeBackdrop.classList.remove('opacity-0');
            tradeBackdrop.classList.add('opacity-100');
            tradePanel.classList.remove('translate-x-full');
            tradePanel.classList.add('translate-x-0');
        });
    }

    function closeTradeModal() {
        if (!tradeModal) return;
        tradeBackdrop.classList.remove('opacity-100');
        tradeBackdrop.classList.add('opacity-0');
        tradePanel.classList.remove('translate-x-0');
        tradePanel.classList.add('translate-x-full');

        setTimeout(() => {
            tradeModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }, 300);
    }

    // Close on Escape key press
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && tradeModal && !tradeModal.classList.contains('hidden')) {
            closeTradeModal();
        }
    });

    // Auto-open if validation errors exist
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            openTradeModal();
        });
    @endif
</script>
