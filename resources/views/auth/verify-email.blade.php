@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
<div class="space-y-6">
    <!-- Header Block -->
    <div class="text-left space-y-2">
        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 mb-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>

        <h2 class="text-2xl font-bold tracking-tight text-white">Verify Your Email</h2>
        <p class="text-xs text-slate-400 leading-relaxed">
            We've dispatched a secure verification link to activate your trading desk. Please verify your address to access journal logging, real-time analytics, and execution tools.
        </p>

        @auth
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-[#161D27] border border-white/10 text-xs font-mono text-slate-200 mt-2">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                </svg>
                <span>{{ auth()->user()->email }}</span>
            </div>
        @endauth
    </div>

    <!-- Status Alerts -->
    @if (session('status') === 'verification-link-sent')
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/25 text-emerald-300 text-xs flex items-start gap-3">
            <svg class="w-4 h-4 text-emerald-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <div>
                <span class="font-semibold block">Verification Link Dispatched</span>
                <span class="text-emerald-400/90 mt-0.5 block">A fresh verification link has been sent to your email address. Please check your inbox and spam folder.</span>
            </div>
        </div>
    @endif

    @if (session('info'))
        <div class="p-3.5 rounded-xl bg-indigo-500/10 border border-indigo-500/25 text-indigo-300 text-xs flex items-start gap-2.5">
            <svg class="w-4 h-4 text-indigo-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p>{{ session('info') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/25 text-rose-300 text-xs flex items-start gap-2.5">
            <svg class="w-4 h-4 text-rose-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <!-- Action Buttons -->
    <div class="space-y-3 pt-2">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" 
                    class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs border border-emerald-500/30 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span>Resend Verification Email</span>
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" 
                    class="w-full flex items-center justify-center gap-2 py-2 px-4 rounded-xl bg-[#161D27] hover:bg-[#1C2533] text-slate-400 hover:text-slate-200 border border-white/5 transition-all text-xs font-medium">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span>Sign Out</span>
            </button>
        </form>
    </div>

    <!-- Security Micro-Footer -->
    <div class="pt-4 border-t border-white/5 text-center">
        <p class="text-[11px] text-slate-500">
            Link didn't arrive? Check your spam/junk folder or ensure your mail client accepts automated messages from TradeCompass.
        </p>
    </div>
</div>
@endsection
