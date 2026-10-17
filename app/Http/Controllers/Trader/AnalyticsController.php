<?php

namespace App\Http\Controllers\Trader;

use App\Http\Controllers\Controller;
use App\Services\TradeAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    protected $analytics;

    public function __construct(TradeAnalyticsService $analytics)
    {
        $this->analytics = $analytics;
    }

    /**
     * Display main analytics page with interactive command bar, KPI matrix, dual equity/drawdown charts, and monthly heatmap.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Handle Quick Timeframe Chips & Legacy Presets
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $period = strtolower($request->period ?? '');

        if ($period) {
            switch ($period) {
                case '7d':
                case '7_days':
                    $dateFrom = now()->subDays(7)->format('Y-m-d');
                    $dateTo = now()->format('Y-m-d');
                    break;
                case '30d':
                case '30_days':
                    $dateFrom = now()->subDays(30)->format('Y-m-d');
                    $dateTo = now()->format('Y-m-d');
                    break;
                case '90d':
                case '90_days':
                    $dateFrom = now()->subDays(90)->format('Y-m-d');
                    $dateTo = now()->format('Y-m-d');
                    break;
                case 'ytd':
                case 'this_year':
                    $dateFrom = now()->startOfYear()->format('Y-m-d');
                    $dateTo = now()->format('Y-m-d');
                    break;
                case 'all':
                case 'all_time':
                    $dateFrom = null;
                    $dateTo = null;
                    break;
                case 'this_week':
                    $dateFrom = now()->startOfWeek()->format('Y-m-d');
                    $dateTo = now()->endOfWeek()->format('Y-m-d');
                    break;
                case 'last_week':
                    $dateFrom = now()->subWeek()->startOfWeek()->format('Y-m-d');
                    $dateTo = now()->subWeek()->endOfWeek()->format('Y-m-d');
                    break;
                case 'this_month':
                    $dateFrom = now()->startOfMonth()->format('Y-m-d');
                    $dateTo = now()->endOfMonth()->format('Y-m-d');
                    break;
                case 'last_month':
                    $dateFrom = now()->subMonth()->startOfMonth()->format('Y-m-d');
                    $dateTo = now()->subMonth()->endOfMonth()->format('Y-m-d');
                    break;
            }
        }

        // Build filters from request
        $filters = array_filter([
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'pair' => $request->pair,
            'session' => $request->session,
            'outcome' => $request->outcome,
            'trade_account_id' => $request->trade_account_id,
        ]);

        // Get 8 KPI quantitative metrics
        $metrics = [
            'total_trades' => $this->analytics->getTotalTrades($user, $filters),
            'net_profit_loss' => $this->analytics->getNetProfitLoss($user, $filters),
            'win_rate' => $this->analytics->getWinRate($user, $filters),
            'avg_rr' => $this->analytics->getAverageRiskReward($user, $filters),
            'profit_factor' => $this->analytics->getProfitFactor($user, $filters),
            'expectancy' => $this->analytics->getExpectancy($user, $filters),
            'max_drawdown' => $this->analytics->getMaxDrawdown($user, $filters),
            'recovery_factor' => $this->analytics->getRecoveryFactor($user, $filters),
            'avg_hold_time' => $this->analytics->getAverageHoldTime($user, $filters),
        ];

        // Dual Synchronized Charts Data
        $equityCurve = $this->analytics->getEquityCurveData($user, $filters);
        $drawdownCurve = $this->analytics->getDrawdownCurveData($user, $filters);

        // Monthly P&L Data
        $monthlyPL = $this->analytics->getMonthlyPLData($user, $request->year ?? now()->year, $request->trade_account_id);

        // Forex Trading Calendar Heatmap
        $calendarYear = (int) ($request->calendar_year ?? now()->year);
        $calendarMonth = (int) ($request->calendar_month ?? now()->month);
        if ($calendarMonth < 1) {
            $calendarMonth = 12;
            $calendarYear--;
        } elseif ($calendarMonth > 12) {
            $calendarMonth = 1;
            $calendarYear++;
        }
        $calendarData = $this->analytics->getDailyCalendarData($user, $calendarYear, $calendarMonth, $request->trade_account_id);

        // Sessions, Pairs & Distributions
        $sessionPerformance = $this->analytics->getSessionPerformance($user, $filters);
        $bestWorstPairs = $this->analytics->getBestWorstPairs($user, 5, $request->trade_account_id);
        $winLossDistribution = $this->analytics->getWinLossDistribution($user, $filters);

        // Filter Dropdowns
        $pairs = $user->trades()->distinct()->pluck('pair')->sort()->values();
        $accounts = $user->tradeAccounts()->get();

        return view('trader.analytics.index', compact(
            'metrics',
            'equityCurve',
            'drawdownCurve',
            'monthlyPL',
            'calendarData',
            'calendarYear',
            'calendarMonth',
            'sessionPerformance',
            'bestWorstPairs',
            'winLossDistribution',
            'pairs',
            'accounts',
            'filters',
            'period'
        ));
    }

    /**
     * Display granular analysis for a single currency pair / symbol
     */
    public function pairAnalysis(Request $request, string $pair)
    {
        $user = Auth::user();
        $pairSymbol = strtoupper(str_replace('-', '/', $pair));

        $filters = array_filter([
            'pair' => $pairSymbol,
            'trade_account_id' => $request->trade_account_id,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
        ]);

        $metrics = [
            'total_trades' => $this->analytics->getTotalTrades($user, $filters),
            'net_profit_loss' => $this->analytics->getNetProfitLoss($user, $filters),
            'win_rate' => $this->analytics->getWinRate($user, $filters),
            'avg_rr' => $this->analytics->getAverageRiskReward($user, $filters),
            'profit_factor' => $this->analytics->getProfitFactor($user, $filters),
            'expectancy' => $this->analytics->getExpectancy($user, $filters),
            'max_drawdown' => $this->analytics->getMaxDrawdown($user, $filters),
            'recovery_factor' => $this->analytics->getRecoveryFactor($user, $filters),
            'avg_hold_time' => $this->analytics->getAverageHoldTime($user, $filters),
        ];

        $equityCurve = $this->analytics->getEquityCurveData($user, $filters);
        $drawdownCurve = $this->analytics->getDrawdownCurveData($user, $filters);
        $monthlyPL = $this->analytics->getMonthlyPLData($user, $request->year ?? now()->year, $request->trade_account_id);

        $calendarYear = (int) ($request->calendar_year ?? now()->year);
        $calendarMonth = (int) ($request->calendar_month ?? now()->month);
        $calendarData = $this->analytics->getDailyCalendarData($user, $calendarYear, $calendarMonth, $request->trade_account_id);

        $sessionPerformance = $this->analytics->getSessionPerformance($user, $filters);
        $bestWorstPairs = $this->analytics->getBestWorstPairs($user, 5, $request->trade_account_id);
        $winLossDistribution = $this->analytics->getWinLossDistribution($user, $filters);

        $pairs = $user->trades()->distinct()->pluck('pair')->sort()->values();
        $accounts = $user->tradeAccounts()->get();
        $period = $request->period ?? 'all';

        return view('trader.analytics.index', compact(
            'metrics',
            'equityCurve',
            'drawdownCurve',
            'monthlyPL',
            'calendarData',
            'calendarYear',
            'calendarMonth',
            'sessionPerformance',
            'bestWorstPairs',
            'winLossDistribution',
            'pairs',
            'accounts',
            'filters',
            'period'
        ))->with('activePair', $pairSymbol);
    }

    /**
     * Display trade review/pattern recognition page
     */
    public function review()
    {
        $user = Auth::user();

        $patterns = [
            'streaks' => $this->analytics->getStreaks($user),
            'time_of_day' => $this->analytics->getTimeOfDayPerformance($user),
            'day_of_week' => $this->analytics->getDayOfWeekPerformance($user),
        ];

        return view('trader.analytics.review', compact('patterns'));
    }
}
