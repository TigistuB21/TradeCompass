@extends('layouts.app')

@section('title', 'Edit Trade #' . $trade->id)

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Breadcrumb & Header -->
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-xs text-slate-400 mb-2">
            <a href="{{ route('trader.dashboard') }}" class="hover:text-white transition-colors">Dashboard</a>
            <span>/</span>
            <a href="{{ route('trader.trades.index') }}" class="hover:text-white transition-colors">Trade History</a>
            <span>/</span>
            <a href="{{ route('trader.trades.show', $trade) }}" class="hover:text-white transition-colors">Trade #{{ $trade->id }} ({{ $trade->pair }})</a>
            <span>/</span>
            <span class="text-slate-200 font-medium">Edit</span>
        </nav>
        
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white">
                    Edit Trade Record #{{ $trade->id }}
                </h1>
                <p class="text-xs text-slate-400 mt-1">Update execution prices, stop loss levels, and post-trade emotional assessments.</p>
            </div>
            
            <a href="{{ route('trader.trades.show', $trade) }}" 
               class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white bg-[#111620] hover:bg-[#161D27] border border-white/5 transition-all">
                Cancel
            </a>
        </div>
    </div>

    <!-- Main Edit Form -->
    <form action="{{ route('trader.trades.update', $trade) }}" method="POST">
        @csrf
        @method('PUT')
        @include('trader.trades._form', ['trade' => $trade])
    </form>
</div>
@endsection
