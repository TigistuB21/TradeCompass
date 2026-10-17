<?php

namespace App\Http\Controllers\Trader;

use App\Enums\MarketSession;
use App\Enums\TradeDirection;
use App\Enums\TradeOutcome;
use App\Enums\TradeEmotion;
use App\Enums\PostTradeEmotion;
use App\Http\Controllers\Controller;
use App\Models\Trade;
use App\Models\Strategy;
use App\Services\AchievementService;
use App\Services\RiskComplianceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TradeController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource with scoping, filtering, sorting, summary metrics, and CSV export.
     */
    public function index(Request $request)
    {
        $query = Auth::user()->trades()->with(['tradeAccount', 'strategyModel']);

        // Conditional filters
        if ($request->filled('trade_account_id')) {
            $query->where('trade_account_id', $request->trade_account_id);
        }

        if ($request->filled('pair')) {
            $query->scopePair($request->pair);
        }

        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }

        if ($request->filled('outcome')) {
            $query->where('outcome', $request->outcome);
        }

        if ($request->filled('session')) {
            $query->scopeSession($request->session);
        }

        if ($request->filled('strategy_id')) {
            $query->where('strategy_id', $request->strategy_id);
        }

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $from = $request->filled('date_from') ? Carbon::parse($request->date_from)->startOfDay() : null;
            $to = $request->filled('date_to') ? Carbon::parse($request->date_to)->endOfDay() : null;
            $query->scopeDateRange($from, $to);
        }

        // Sorting
        $allowedSorts = ['entry_date', 'profit_loss', 'pips', 'lot_size'];
        $sortField = in_array($request->query('sort'), $allowedSorts, true) ? $request->query('sort') : 'entry_date';
        $sortOrder = strtolower($request->query('order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortField, $sortOrder);

        // Filtered Metrics Strip Calculation on pre-paginated clone
        $metricsQuery = clone $query;
        $totalTrades = (clone $metricsQuery)->count();
        $netProfitLoss = (float) ((clone $metricsQuery)->sum('profit_loss') ?? 0);
        $netPips = (float) ((clone $metricsQuery)->sum('pips') ?? 0);

        $winCount = (clone $metricsQuery)->where('outcome', 'win')->count();
        $closedCount = (clone $metricsQuery)->whereIn('outcome', ['win', 'loss', 'breakeven'])->count();
        $winRate = $closedCount > 0 ? round(($winCount / $closedCount) * 100, 1) : ($totalTrades > 0 ? round(($winCount / $totalTrades) * 100, 1) : 0.0);

        $grossProfit = (float) ((clone $metricsQuery)->where('profit_loss', '>', 0)->sum('profit_loss') ?? 0);
        $grossLoss = abs((float) ((clone $metricsQuery)->where('profit_loss', '<', 0)->sum('profit_loss') ?? 0));
        $profitFactor = $grossLoss > 0 ? round($grossProfit / $grossLoss, 2) : ($grossProfit > 0 ? 999.0 : 0.0);

        $summary = [
            'total_trades' => $totalTrades,
            'net_profit_loss' => $netProfitLoss,
            'net_pips' => $netPips,
            'win_rate' => $winRate,
            'profit_factor' => $profitFactor,
        ];

        // CSV Streamed Export
        if ($request->query('export') === 'csv') {
            $filename = 'tradecompass-trades-' . date('Y-m-d') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return response()->streamDownload(function () use ($query) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, [
                    'Date', 'Pair', 'Direction', 'Account', 'Session', 'Strategy',
                    'Lot Size', 'Entry Price', 'Exit Price', 'Stop Loss', 'Take Profit',
                    'Pips', 'P/L ($)', 'Outcome'
                ]);

                (clone $query)->chunk(250, function ($trades) use ($handle) {
                    foreach ($trades as $trade) {
                        fputcsv($handle, [
                            $trade->entry_date ? $trade->entry_date->format('Y-m-d H:i:s') : '',
                            $trade->pair,
                            is_object($trade->direction) ? $trade->direction->value : $trade->direction,
                            $trade->tradeAccount?->account_name ?? ($trade->account?->account_name ?? 'N/A'),
                            is_object($trade->session) ? $trade->session->value : $trade->session,
                            $trade->strategyModel?->name ?? ($trade->strategy ?? 'N/A'),
                            $trade->lot_size,
                            $trade->entry_price,
                            $trade->exit_price,
                            $trade->stop_loss,
                            $trade->take_profit,
                            $trade->pips,
                            $trade->profit_loss,
                            is_object($trade->outcome) ? $trade->outcome->value : $trade->outcome,
                        ]);
                    }
                });

                fclose($handle);
            }, $filename, $headers);
        }

        // Pagination (default 25 per page, clamped 10-100)
        $perPage = (int) $request->query('per_page', 25);
        if ($perPage < 10 || $perPage > 100) {
            $perPage = 25;
        }
        $trades = $query->paginate($perPage)->appends($request->query());

        // Dropdown & Filter options
        $userPairs = Auth::user()->trades()->whereNotNull('pair')->distinct()->pluck('pair')->toArray();
        $pairs = array_values(array_unique(array_merge($this->getCommonPairs(), $userPairs)));
        sort($pairs);

        $accounts = Auth::user()->tradeAccounts()->orderBy('account_name')->get();
        $strategies = Strategy::where('user_id', Auth::id())->orderBy('name')->get();
        $directions = TradeDirection::cases();
        $outcomes = TradeOutcome::cases();
        $sessions = MarketSession::cases();
        $emotions = TradeEmotion::cases();
        $postEmotions = PostTradeEmotion::cases();
        $focusArea = 'standard';
        $filters = $request->only([
            'trade_account_id', 'pair', 'direction', 'outcome', 'session',
            'strategy_id', 'date_from', 'date_to', 'sort', 'order', 'per_page'
        ]);

        return view('trader.trades.index', compact(
            'trades',
            'summary',
            'pairs',
            'accounts',
            'strategies',
            'directions',
            'outcomes',
            'sessions',
            'emotions',
            'postEmotions',
            'focusArea',
            'filters'
        ));
    }

    public function create()
    {
        $pairs = $this->getCommonPairs();
        $directions = TradeDirection::cases();
        $sessions = MarketSession::cases();
        $outcomes = TradeOutcome::cases();
        $emotions = TradeEmotion::cases();
        $postEmotions = PostTradeEmotion::cases();
        $accounts = Auth::user()->tradeAccounts()->get();
        $strategies = Strategy::where('user_id', Auth::id())->get();
        
        // Phase 6: Guided Journaling
        $focusArea = 'standard';
        $assignment = \App\Models\AnalystAssignment::where('trader_id', Auth::id())->first();
        if ($assignment) {
            $focusArea = $assignment->current_focus_area;
            
            // Feature Gating: Only allow guided journaling if user has the feature (Elite)
            // If they don't, fallback to standard even if analyst assigned a focus
            $subscription = Auth::user()->activeSubscription;
            if (!$subscription || !$subscription->hasFeature('guided_journaling')) {
                $focusArea = 'standard';
            }
        }

        return view('trader.trades.create', compact('pairs', 'directions', 'sessions', 'outcomes', 'emotions', 'postEmotions', 'accounts', 'strategies', 'focusArea'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'trade_account_id' => 'required|exists:trade_accounts,id',
            'pair' => 'required|string|max:255',
            'direction' => 'required|in:buy,sell',
            'entry_date' => 'required|date',
            'exit_date' => 'nullable|date|after:entry_date',
            'entry_price' => 'required|numeric|gt:0',
            'exit_price' => 'required|numeric|gt:0',
            'stop_loss' => 'nullable|numeric|gt:0',
            'take_profit' => 'nullable|numeric|gt:0',
            'lot_size' => 'required|numeric|gt:0',
            'risk_percentage' => 'nullable|numeric|min:0|max:100',
            'strategy_id' => 'nullable|exists:strategies,id',
            'trade_type' => 'nullable|string|max:50',
            'session' => 'nullable|string',
            'pre_trade_emotion' => 'nullable|string|max:255',
            'post_trade_emotion' => 'nullable|string|max:255',
            'followed_plan' => 'nullable|boolean',
            'setup_notes' => 'nullable|string',
            'mistakes_lessons' => 'nullable|string',
            'chart_link' => 'nullable|url',
            'notes' => 'nullable|string',
            'focus_data' => 'nullable|array',
        ]);

        // Auto-detect session if missing
        if (empty($validated['session'])) {
            $validated['session'] = $this->determineSession($validated['entry_date']);
        }

        // Calculate and Set Derived Metrics
        $calc = $this->calculateMetrics($validated);
        $validated['pips'] = $calc['pips'];
        $validated['profit_loss'] = $calc['profit_loss'];
        $validated['risk_reward_ratio'] = $calc['rr'];
        $validated['outcome'] = $calc['outcome'];

        // Compliance Check (Phase 6)
        $compliance = app(RiskComplianceService::class)->checkCompliance(Auth::user(), $validated);
        
        if (!$compliance['compliant']) {
            if ($compliance['is_hard_stop']) {
                return back()->withInput()->withErrors(['compliance' => 'Trade blocked by Risk Rule: ' . $compliance['reason']]);
            }
            
            // Soft Stop: Mark as non-compliant but allow save
            $validated['is_compliant'] = false;
            $validated['violation_reason'] = $compliance['reason'];
        }

        $trade = Auth::user()->trades()->create($validated);
        
        $message = 'Trade logged successfully! +10 XP';
        if (isset($validated['is_compliant']) && !$validated['is_compliant']) {
            $message .= ' (Warning: ' . $validated['violation_reason'] . ')';
        }

        // Award XP and check achievements
        app(AchievementService::class)->awardTradeXp(Auth::user());

        return redirect()->route('trader.trades.index')
            ->with('success', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(Trade $trade)
    {
        // Ensure user owns the trade
        $this->authorize('view', $trade);

        // Eager-load relations for technical scorecard, playbook strategy, and analyst/AI feedback
        $trade->loadMissing(['tradeAccount', 'strategyModel', 'feedback.analyst']);

        return view('trader.trades.show', compact('trade'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Trade $trade)
    {
        // Ensure user owns the trade
        $this->authorize('update', $trade);

        $pairs = $this->getCommonPairs();
        $directions = TradeDirection::cases();
        $sessions = MarketSession::cases();
        $outcomes = TradeOutcome::cases();
        $emotions = TradeEmotion::cases();
        $postEmotions = PostTradeEmotion::cases();
        $accounts = Auth::user()->tradeAccounts()->get();
        $strategies = Strategy::where('user_id', Auth::id())->get();

        return view('trader.trades.edit', compact('trade', 'pairs', 'directions', 'sessions', 'outcomes', 'emotions', 'postEmotions', 'accounts', 'strategies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Trade $trade)
    {
        // Ensure user owns the trade
        $this->authorize('update', $trade);

        $validated = $request->validate([
            'pair' => 'required|string|max:255',
            'direction' => 'required|in:buy,sell',
            'entry_date' => 'required|date',
            'exit_date' => 'nullable|date|after:entry_date',
            'entry_price' => 'required|numeric|gt:0',
            'exit_price' => 'required|numeric|gt:0',
            'stop_loss' => 'nullable|numeric|gt:0',
            'take_profit' => 'nullable|numeric|gt:0',
            'lot_size' => 'required|numeric|gt:0',
            'risk_percentage' => 'nullable|numeric|min:0|max:100',
            'strategy_id' => 'nullable|exists:strategies,id',
            'trade_type' => 'nullable|string|max:50',
            'session' => 'nullable|string',
            'pre_trade_emotion' => 'nullable|string|max:255',
            'post_trade_emotion' => 'nullable|string|max:255',
            'followed_plan' => 'nullable|boolean',
            'setup_notes' => 'nullable|string',
            'mistakes_lessons' => 'nullable|string',
            'chart_link' => 'nullable|url',
            'notes' => 'nullable|string',
            'focus_data' => 'nullable|array',
        ]);

        // Auto-detect session if missing
        if (empty($validated['session'])) {
            $validated['session'] = $this->determineSession($validated['entry_date']);
        }

        // Calculate Metrics
        $calc = $this->calculateMetrics($validated);
        $validated['pips'] = $calc['pips'];
        $validated['profit_loss'] = $calc['profit_loss'];
        $validated['risk_reward_ratio'] = $calc['rr'];
        $validated['outcome'] = $calc['outcome'];

        $trade->update($validated);

        return redirect()->route('trader.trades.show', $trade)
            ->with('success', 'Trade updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Trade $trade)
    {
        // Ensure user owns the trade
        $this->authorize('delete', $trade);

        $trade->delete();

        return redirect()->route('trader.trades.index')
            ->with('success', 'Trade deleted successfully!');
    }

    /**
     * Get common forex pairs
     */
    private function getCommonPairs(): array
    {
        return [
            'EUR/USD', 'GBP/USD', 'USD/JPY', 'USD/CHF', 'AUD/USD', 'USD/CAD', 'NZD/USD',
            'EUR/GBP', 'EUR/JPY', 'GBP/JPY', 'EUR/CHF', 'EUR/AUD', 'EUR/CAD', 'EUR/NZD',
            'GBP/CHF', 'GBP/AUD', 'GBP/CAD', 'GBP/NZD', 'AUD/JPY', 'AUD/NZD', 'AUD/CAD',
            'CAD/JPY', 'CHF/JPY', 'NZD/JPY', 'GBP/ZAR', 'GBP/TRY', 'XAU/USD', 'BTC/USD',
        ];
    }

    /**
     * Determine trading session from time (UTC)
     */
    private function determineSession($date): string
    {
        $hour = Carbon::parse($date)->hour;
        
        if ($hour >= 8 && $hour < 16) return MarketSession::LONDON->value;
        if ($hour >= 13 && $hour < 21) return MarketSession::NEWYORK->value;
        if ($hour >= 21 || $hour < 5) return MarketSession::SYDNEY->value;
        return MarketSession::ASIA->value;
    }

    /**
     * Calculate derived metrics
     */
    private function calculateMetrics(array $data): array
    {
        $entry = $data['entry_price'];
        $exit = $data['exit_price'];
        $direction = $data['direction']; 
        $lot = $data['lot_size'];

        // Pip Multiplier
        $pair = strtoupper($data['pair'] ?? '');
        $isJpy = str_contains($pair, 'JPY');
        $multiplier = $isJpy ? 100 : 10000;
        
        // Pips
        if ($direction === 'buy') {
            $diff = $exit - $entry;
        } else {
            $diff = $entry - $exit;
        }
        $pips = $diff * $multiplier;
        
        // Approx Profit ($10 per pip per lot standard)
        $pl = $pips * $lot * 10; 
        
        // Outcome
        if ($pips > 0) $outcome = TradeOutcome::WIN->value;
        elseif ($pips < 0) $outcome = TradeOutcome::LOSS->value;
        else $outcome = TradeOutcome::BREAKEVEN->value;

        // RR
        $rr = 0;
        if (!empty($data['stop_loss']) && !empty($data['take_profit'])) {
             $risk = abs($entry - $data['stop_loss']);
             $reward = abs($entry - $data['take_profit']);
             if ($risk > 0) {
                 $rr = $reward / $risk;
             }
        }

        return [
            'pips' => round($pips, 2),
            'profit_loss' => round($pl, 2),
            'rr' => round($rr, 2),
            'outcome' => $outcome
        ];
    }
}
