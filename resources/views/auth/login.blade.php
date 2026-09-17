@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold tracking-tight text-white">Sign in to your account</h2>
        <p class="mt-1 text-sm text-slate-400">Enter your credentials to access your trading journal</p>
    </div>

    <!-- Session Status Alert -->
    @if (session('status'))
        <div class="mb-5 flex items-start gap-3 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400">
            <svg class="h-5 w-5 flex-shrink-0 text-emerald-400 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
            </svg>
            <div>{{ session('status') }}</div>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Field -->
        <div>
            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                Email Address
            </label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                    <!-- Envelope Icon -->
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                    </svg>
                </div>
                <input 
                    id="email" 
                    name="email" 
                    type="email" 
                    required 
                    autofocus
                    autocomplete="email"
                    value="{{ old('email') }}"
                    placeholder="trader@tradecompass.io"
                    class="block w-full rounded-lg border bg-slate-950/80 pl-10 pr-3.5 py-2.5 text-sm text-slate-100 placeholder-slate-500 transition-colors focus:outline-none focus:ring-2 @error('email') border-red-500/80 focus:border-red-500 focus:ring-red-500/20 @else border-slate-700/80 focus:border-indigo-500 focus:ring-indigo-500/30 @enderror"
                >
            </div>
            @error('email')
                <div class="mt-1.5 flex items-center gap-1.5 text-xs text-red-400">
                    <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ $message }}</span>
                </div>
            @enderror
        </div>

        <!-- Password Field -->
        <div x-data="{ showPassword: false }">
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                    Password
                </label>
                <a href="{{ route('password.request') }}" class="text-xs font-medium text-indigo-400 hover:text-indigo-300 transition-colors">
                    Forgot password?
                </a>
            </div>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                    <!-- Lock Icon -->
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
                <input 
                    id="password" 
                    name="password" 
                    :type="showPassword ? 'text' : 'password'"
                    type="password" 
                    required
                    autocomplete="current-password"
                    placeholder="••••••••••••"
                    class="block w-full rounded-lg border bg-slate-950/80 pl-10 pr-10 py-2.5 text-sm text-slate-100 placeholder-slate-500 transition-colors focus:outline-none focus:ring-2 @error('password') border-red-500/80 focus:border-red-500 focus:ring-red-500/20 @else border-slate-700/80 focus:border-indigo-500 focus:ring-indigo-500/30 @enderror"
                >
                <button 
                    type="button" 
                    @click="showPassword = !showPassword" 
                    class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-500 hover:text-slate-300 focus:outline-none transition-colors"
                    aria-label="Toggle password visibility"
                >
                    <!-- Eye Icon (Hidden when showPassword is true) -->
                    <svg x-show="!showPassword" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <!-- Eye Off Icon (Shown when showPassword is true) -->
                    <svg x-show="showPassword" x-cloak class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                        <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                        <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                        <line x1="2" x2="22" y1="2" y2="22"></line>
                    </svg>
                </button>
            </div>
            @error('password')
                <div class="mt-1.5 flex items-center gap-1.5 text-xs text-red-400">
                    <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ $message }}</span>
                </div>
            @enderror
        </div>

        <!-- Remember Me Checkbox -->
        <div class="flex items-center">
            <input 
                id="remember" 
                name="remember" 
                type="checkbox"
                class="h-4 w-4 rounded border-slate-700 bg-slate-950 text-indigo-600 focus:ring-2 focus:ring-indigo-500/40 focus:ring-offset-0 transition"
            >
            <label for="remember" class="ml-2.5 text-xs font-medium text-slate-300 select-none cursor-pointer">
                Keep me signed in for 30 days
            </label>
        </div>

        <!-- Submit Button -->
        <button 
            type="submit" 
            class="w-full flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-indigo-600/30 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900 active:bg-indigo-700 transition-all duration-150"
        >
            <span>Sign In</span>
            <!-- Arrow Right Icon -->
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14"></path>
                <path d="m12 5 7 7-7 7"></path>
            </svg>
        </button>
    </form>
@endsection

@section('footer')
    <p class="text-slate-400">
        Don't have a trading journal yet? 
        <a href="{{ route('register') }}" class="font-medium text-indigo-400 hover:text-indigo-300 transition-colors">
            Create an account
        </a>
    </p>
@endsection
