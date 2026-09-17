@extends('layouts.auth')

@section('title', 'Create Account')

@section('content')
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold tracking-tight text-white">Create your account</h2>
        <p class="mt-1 text-sm text-slate-400">Start logging executions and analyzing your trading edge</p>
    </div>

    <!-- General Error Banner -->
    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-500/30 bg-red-500/10 p-3.5 text-xs text-red-400">
            <div class="flex items-center gap-2 font-medium">
                <svg class="h-4 w-4 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                </svg>
                <span>Please correct the errors highlighted below.</span>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4" enctype="multipart/form-data" id="registerForm" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
        @csrf

        <!-- Full Name Field -->
        <div>
            <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                Full Name
            </label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                    <!-- User Icon -->
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <input 
                    id="name" 
                    name="name" 
                    type="text" 
                    required 
                    autofocus
                    autocomplete="name"
                    value="{{ old('name') }}"
                    placeholder="Alex Morgan"
                    class="block w-full rounded-lg border bg-slate-950/80 pl-10 pr-3.5 py-2.5 text-sm text-slate-100 placeholder-slate-500 transition-colors focus:outline-none focus:ring-2 @error('name') border-red-500/80 focus:border-red-500 focus:ring-red-500/20 @else border-slate-700/80 focus:border-indigo-500 focus:ring-indigo-500/30 @enderror"
                >
            </div>
            @error('name')
                <div class="mt-1.5 flex items-center gap-1.5 text-xs text-red-400">
                    <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ $message }}</span>
                </div>
            @enderror
        </div>

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
                    autocomplete="email"
                    value="{{ old('email') }}"
                    placeholder="alex@tradecompass.io"
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

        <!-- Role Selection -->
        <div>
            <label for="role" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                Account Purpose
            </label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                    <!-- Briefcase / Role Icon -->
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="20" height="14" x="2" y="7" rx="2" ry="2"></rect>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                </div>
                <select 
                    id="role" 
                    name="role" 
                    required
                    class="block w-full appearance-none rounded-lg border bg-slate-950/80 pl-10 pr-10 py-2.5 text-sm text-slate-100 transition-colors focus:outline-none focus:ring-2 @error('role') border-red-500/80 focus:border-red-500 focus:ring-red-500/20 @else border-slate-700/80 focus:border-indigo-500 focus:ring-indigo-500/30 @enderror cursor-pointer"
                >
                    <option value="" class="bg-slate-900 text-slate-400">Select account purpose...</option>
                    <option value="trader" class="bg-slate-900 text-slate-100" {{ old('role', 'trader') == 'trader' ? 'selected' : '' }}>Trader — Track executions & journaling</option>
                    <option value="analyst" class="bg-slate-900 text-slate-100" {{ old('role') == 'analyst' ? 'selected' : '' }}>Performance Analyst — Review and evaluate traders</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-500">
                    <!-- Chevron Down Icon -->
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m6 9 6 6 6-6"></path>
                    </svg>
                </div>
            </div>
            @error('role')
                <div class="mt-1.5 flex items-center gap-1.5 text-xs text-red-400">
                    <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ $message }}</span>
                </div>
            @enderror
        </div>

        <!-- Standard Fields Container -->
        <div id="standardFields" class="space-y-4 pt-1">
            <!-- Password Field -->
            <div x-data="{ showPassword: false }">
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    Password
                </label>
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
                        autocomplete="new-password"
                        placeholder="••••••••••••"
                        class="block w-full rounded-lg border bg-slate-950/80 pl-10 pr-10 py-2.5 text-sm text-slate-100 placeholder-slate-500 transition-colors focus:outline-none focus:ring-2 @error('password') border-red-500/80 focus:border-red-500 focus:ring-red-500/20 @else border-slate-700/80 focus:border-indigo-500 focus:ring-indigo-500/30 @enderror"
                    >
                    <button 
                        type="button" 
                        @click="showPassword = !showPassword" 
                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-500 hover:text-slate-300 focus:outline-none transition-colors"
                        aria-label="Toggle password visibility"
                    >
                        <!-- Eye Icon -->
                        <svg x-show="!showPassword" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <!-- Eye Off Icon -->
                        <svg x-show="showPassword" x-cloak class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                            <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                            <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                            <line x1="2" x2="22" y1="2" y2="22"></line>
                        </svg>
                    </button>
                </div>
                <p class="mt-1 text-xs text-slate-500">Must be at least 8 characters</p>
                @error('password')
                    <div class="mt-1.5 flex items-center gap-1.5 text-xs text-red-400">
                        <svg class="h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <!-- Confirm Password Field -->
            <div x-data="{ showConfirmPassword: false }">
                <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    Confirm Password
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                        <!-- Shield Check Icon -->
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"></path>
                            <path d="m9 12 2 2 4-4"></path>
                        </svg>
                    </div>
                    <input 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        :type="showConfirmPassword ? 'text' : 'password'"
                        type="password" 
                        required
                        autocomplete="new-password"
                        placeholder="••••••••••••"
                        class="block w-full rounded-lg border border-slate-700/80 bg-slate-950/80 pl-10 pr-10 py-2.5 text-sm text-slate-100 placeholder-slate-500 transition-colors focus:outline-none focus:ring-2 focus:border-indigo-500 focus:ring-indigo-500/30"
                    >
                    <button 
                        type="button" 
                        @click="showConfirmPassword = !showConfirmPassword" 
                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-500 hover:text-slate-300 focus:outline-none transition-colors"
                        aria-label="Toggle confirm password visibility"
                    >
                        <!-- Eye Icon -->
                        <svg x-show="!showConfirmPassword" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <!-- Eye Off Icon -->
                        <svg x-show="showConfirmPassword" x-cloak class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                            <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                            <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                            <line x1="2" x2="22" y1="2" y2="22"></line>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            <button 
                id="submitButton"
                type="submit" 
                :disabled="isSubmitting"
                :class="isSubmitting ? 'opacity-75 cursor-not-allowed' : ''"
                class="w-full mt-2 flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-indigo-600/30 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900 active:bg-indigo-700 transition-all duration-150"
            >
                <span x-show="!isSubmitting" class="flex items-center gap-2">
                    <span>Create Account</span>
                    <!-- Arrow Right Icon -->
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"></path>
                        <path d="m12 5 7 7-7 7"></path>
                    </svg>
                </span>
                <span x-show="isSubmitting" x-cloak class="flex items-center gap-2">
                    <!-- Loading Spinner -->
                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>Creating account...</span>
                </span>
            </button>
        </div>
    </form>

    <!-- Auto-fill Script from Query Parameters -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            if(params.has('email')) {
                const emailInput = document.getElementById('email');
                if(emailInput) emailInput.value = params.get('email');
            }
            if(params.has('name')) {
                const nameInput = document.getElementById('name');
                if(nameInput) nameInput.value = params.get('name');
            }
        });
    </script>
@endsection

@section('footer')
    <p class="text-slate-400">
        Already have an account? 
        <a href="{{ route('login') }}" class="font-medium text-indigo-400 hover:text-indigo-300 transition-colors">
            Sign in
        </a>
    </p>
@endsection
