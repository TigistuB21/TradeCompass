<?php

namespace App\Services;

use App\Models\Trade;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TradeAnalyticsService
{
    /**
     * Get total number of trades
     */
    public function getTotalTrades(User $user, ?array $filters = []): int
    {
        return $this->applyFilters($user->trades(), $filters)->count();
    }

    /**
     * Get net profit / loss ($)
     */
    public function getNetProfitLoss(User $user, ?array $filters = []): float
    {
        return round((float) ($this->applyFilters($user->trades(), $filters)->sum('profit_loss') ?? 0), 2);
    }

    /**
     * Get win rate percentage
     */
    public function getWinRate(User $user, ?array $filters = []): float
    {
        $query = $this->applyFilters($user->trades(), $filters);
        $total = $query->count();
        
        if ($total === 0) {
            return 0;
        }

        $wins = (clone $query)->where('outcome', 'win')->count();
        
        return round(($wins / $total) * 100, 2);
    }

    /**
     * Get average risk:reward ratio
     */
    public function getAverageRiskReward(User $user, ?array $filters = []): float
    {
        $query = $this->applyFilters($user->trades(), $filters);
        
        return round($query->whereNotNull('risk_reward_ratio')->avg('risk_reward_ratio') ?? 0, 2);
    }

    /**
     * Get profit factor (Gross Profit / Gross Loss)
     */
    public function getProfitFactor(User $user, ?array $filters = []): float
    {
        $query = $this->applyFilters($user->trades(), $filters);
        
        $grossProfit = (clone $query)->where('profit_loss', '>', 0)->sum('profit_loss');
        $grossLoss = abs((clone $query)->where('profit_loss', '<', 0)->sum('profit_loss'));
        
        if ($grossLoss == 0) {
            return $grossProfit > 0 ? 999 : 0;
        }

        return round($grossProfit / $grossLoss, 2);
    }

    /**
     * Get expectancy: (Win% × Avg Win) - (Loss% × Avg Loss)
     */
    public function getExpectancy(User $user, ?array $filters = []): float
    {
        $query = $this->applyFilters($user->trades(), $filters);
        $total = $query->count();
        
        if ($total === 0) {
            return 0;
        }

        $wins = (clone $query)->where('outcome', 'win');
        $losses = (clone $query)->where('outcome', 'loss');
        
        $avgWin = $wins->avg('profit_loss') ?? 0;
        $avgLoss = abs($losses->avg('profit_loss') ?? 0);
        $winRate = $this->getWinRate($user, $filters) / 100;
        
        return round(($winRate * $avgWin) - ((1 - $winRate) * $avgLoss), 2);
    }

    /**
     * Get average hold time in hours
     */
    public function getAverageHoldTime(User $user, ?array $filters = []): float
    {
        $query = $this->applyFilters($user->trades(), $filters);
        
        $trades = $query->whereNotNull('entry_date')->whereNotNull('exit_date')->get();
        
        if ($trades->isEmpty()) {
            return 0;
        }

        $totalHours = $trades->sum(function ($trade) {
            if (!$trade->entry_date || !$trade->exit_date) {
                return 0;
            }
            return $trade->entry_date->diffInHours($trade->exit_date);
        });

        return round($totalHours / $trades->count(), 1);
    }

    /**
     * Get maximum drawdown in dollars ($)
     */
    public function getMaxDrawdown(User $user, ?array $filters = []): float
    {
        $query = $this->applyFilters($user->trades(), $filters);
        $trades = $query->orderBy('entry_date')->get();
        
        if ($trades->isEmpty()) {
            return 0;
        }

        $peak = 0;
        $maxDrawdown = 0;
        $runningTotal = 0;

        foreach ($trades as $trade) {
            $runningTotal += $trade->profit_loss;
            
            if ($runningTotal > $peak) {
                $peak = $runningTotal;
            }
            
            $drawdown = $peak - $runningTotal;
            
            if ($drawdown > $maxDrawdown) {
                $maxDrawdown = $drawdown;
            }
        }

        return round($maxDrawdown, 2);
    }

    /**
     * Get recovery factor (Net Profit / Max Drawdown)
     */
    public function getRecoveryFactor(User $user, ?array $filters = []): float
    {
        $query = $this->applyFilters($user->trades(), $filters);
        $netProfit = $query->sum('profit_loss');
        $maxDrawdown = $this->getMaxDrawdown($user, $filters);
        
        if ($maxDrawdown == 0) {
            return $netProfit > 0 ? 999 : 0;
        }

        return round($netProfit / $maxDrawdown, 2);
    }

    /**
     * Get equity curve data for chart
     */
    public function getEquityCurveData(User $user, ?array $filters = []): array
    {
        $query = $this->applyFilters($user->trades(), $filters);
        $trades = $query->orderBy('entry_date')->get();
        
        $data = [];
        $runningTotal = 0;

        foreach ($trades as $trade) {
            $runningTotal += (float) ($trade->profit_loss ?? 0);
            $data[] = [
                'date' => $trade->entry_date ? $trade->entry_date->format('Y-m-d') : now()->format('Y-m-d'),
                'equity' => round($runningTotal, 2),
                'pnl' => round((float) ($trade->profit_loss ?? 0), 2),
                'pair' => $trade->pair,
            ];
        }

        return $data;
    }

    /**
     * Get drawdown curve data for underwater chart
     * Values <= 0 indicating drop from running historical peak
     */
    public function getDrawdownCurveData(User $user, ?array $filters = []): array
    {
        $query = $this->applyFilters($user->trades(), $filters);
        $trades = $query->orderBy('entry_date')->get();

        $data = [];
        $runningTotal = 0;
        $peak = 0;

        foreach ($trades as $trade) {
            $runningTotal += (float) ($trade->profit_loss ?? 0);
            if ($runningTotal > $peak) {
                $peak = $runningTotal;
            }
            
            $drawdown = $runningTotal - $peak; // Continuous drawdown <= 0
            $drawdownPct = $peak > 0 ? round(($drawdown / $peak) * 100, 2) : 0.0;

            $data[] = [
                'date' => $trade->entry_date ? $trade->entry_date->format('Y-m-d') : now()->format('Y-m-d'),
                'drawdown' => round($drawdown, 2),
                'drawdown_pct' => $drawdownPct,
            ];
        }

        return $data;
    }

    /**
     * Get monthly P&L data for chart
     */
    public function getMonthlyPLData(User $user, int $year, ?int $tradeAccountId = null): array
    {
        $query = $user->trades()->whereYear('entry_date', $year);

        if ($tradeAccountId) {
            $query->where('trade_account_id', $tradeAccountId);
        }

        $trades = $query->get(['entry_date', 'profit_loss']);
        $monthlyTotals = array_fill(1, 12, 0.0);

        foreach ($trades as $trade) {
            if (!$trade->entry_date) {
                continue;
            }
            $month = (int) $trade->entry_date->format('n');
            $monthlyTotals[$month] += (float) ($trade->profit_loss ?? 0);
        }

        $result = [];
        for ($i = 1; $i <= 12; $i++) {
            $result[] = round($monthlyTotals[$i], 2);
        }

        return $result;
    }

    /**
     * Get daily calendar performance data for monthly heatmap
     */
    public function getDailyCalendarData(User $user, int $year, int $month, ?int $tradeAccountId = null): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $query = $user->trades()
            ->where('entry_date', '>=', $startDate)
            ->where('entry_date', '<=', $endDate);

        if ($tradeAccountId) {
            $query->where('trade_account_id', $tradeAccountId);
        }

        $trades = $query->orderBy('entry_date')->get();

        $daily = [];
        foreach ($trades as $trade) {
            $dateKey = $trade->entry_date ? $trade->entry_date->format('Y-m-d') : '';
            if (!$dateKey) continue;

            if (!isset($daily[$dateKey])) {
                $daily[$dateKey] = [
                    'date' => $dateKey,
                    'net_pnl' => 0.0,
                    'trade_count' => 0,
                    'win_count' => 0,
                    'loss_count' => 0,
                    'pips' => 0.0,
                ];
            }

            $daily[$dateKey]['net_pnl'] += (float) ($trade->profit_loss ?? 0);
            $daily[$dateKey]['trade_count']++;
            $daily[$dateKey]['pips'] += (float) ($trade->pips ?? 0);

            $outVal = strtolower(is_object($trade->outcome) ? $trade->outcome->value : (string) $trade->outcome);
            if ($outVal === 'win') {
                $daily[$dateKey]['win_count']++;
            } elseif ($outVal === 'loss') {
                $daily[$dateKey]['loss_count']++;
            }
        }

        foreach ($daily as &$day) {
            $day['net_pnl'] = round($day['net_pnl'], 2);
            $day['pips'] = round($day['pips'], 1);
        }

        return $daily;
    }

    /**
     * Get session performance data
     */
    public function getSessionPerformance(User $user, ?array $filters = []): array
    {
        $query = $this->applyFilters($user->trades(), $filters);
        
        return $query->selectRaw('session, COUNT(*) as trades, SUM(profit_loss) as profit, AVG(profit_loss) as avg_profit')
            ->groupBy('session')
            ->get()
            ->map(function ($item) {
                return [
                    'session' => is_object($item->session) ? $item->session->label() : ucfirst($item->session ?? 'Other'),
                    'trades' => (int) $item->trades,
                    'profit' => round((float) $item->profit, 2),
                    'avg_profit' => round((float) $item->avg_profit, 2),
                ];
            })
            ->toArray();
    }

    /**
     * Get strategy performance data
     */
    public function getStrategyPerformance(User $user, ?array $filters = []): array
    {
        $query = $this->applyFilters($user->trades(), $filters);
        
        return $query->whereNotNull('strategy_id')
            ->join('strategies', 'trades.strategy_id', '=', 'strategies.id')
            ->selectRaw('strategies.name as strategy_name, strategies.id as strategy_id, COUNT(*) as trades, SUM(profit_loss) as profit, AVG(profit_loss) as avg_profit')
            ->groupBy('strategies.id', 'strategies.name')
            ->get()
            ->map(function ($item) use ($user, $filters) {
                $total = $item->trades;
                $wins = $user->trades()
                    ->where('strategy_id', $item->strategy_id)
                    ->where('outcome', 'win')
                    ->when(isset($filters['date_from']), fn($q) => $q->where('entry_date', '>=', $filters['date_from']))
                    ->when(isset($filters['date_to']), fn($q) => $q->where('entry_date', '<=', $filters['date_to']))
                    ->when(isset($filters['trade_account_id']), fn($q) => $q->where('trade_account_id', $filters['trade_account_id']))
                    ->count();

                $winRate = $total > 0 ? round(($wins / $total) * 100, 1) : 0;

                return [
                    'id' => $item->strategy_id,
                    'name' => $item->strategy_name,
                    'trades' => (int) $item->trades,
                    'profit' => round((float) $item->profit, 2),
                    'win_rate' => $winRate,
                    'avg_profit' => round((float) $item->avg_profit, 2),
                ];
            })
            ->sortByDesc('profit')
            ->values()
            ->toArray();
    }

    /**
     * Get best and worst performing pairs
     */
    public function getBestWorstPairs(User $user, int $limit = 5, ?int $tradeAccountId = null): array
    {
        $query = $user->trades();

        if ($tradeAccountId) {
            $query->where('trade_account_id', $tradeAccountId);
        }

        $pairStats = $query->selectRaw('pair, COUNT(*) as trades, SUM(profit_loss) as profit')
            ->groupBy('pair')
            ->havingRaw('COUNT(*) >= 2')
            ->orderByRaw('SUM(profit_loss) DESC')
            ->get();

        return [
            'best' => $pairStats->where('profit', '>', 0)->take($limit)->map(fn($item) => [
                'pair' => $item->pair,
                'trades' => (int) $item->trades,
                'profit' => round((float) $item->profit, 2),
            ])->values()->toArray(),
            'worst' => $pairStats->where('profit', '<', 0)->sortBy('profit')->take($limit)->map(fn($item) => [
                'pair' => $item->pair,
                'trades' => (int) $item->trades,
                'profit' => round((float) $item->profit, 2),
            ])->values()->toArray(),
        ];
    }

    /**
     * Get win/loss distribution
     */
    public function getWinLossDistribution(User $user, ?array $filters = []): array
    {
        $query = $this->applyFilters($user->trades(), $filters);
        
        return $query->selectRaw('outcome, COUNT(*) as count')
            ->groupBy('outcome')
            ->get()
            ->mapWithKeys(function ($item) {
                $label = is_object($item->outcome) ? $item->outcome->label() : ucfirst($item->outcome ?? 'Other');
                return [$label => (int) $item->count];
            })
            ->toArray();
    }

    /**
     * Detect win/loss streaks
     */
    public function getStreaks(User $user): array
    {
        $trades = $user->trades()->orderBy('entry_date')->get();
        
        $currentStreak = 0;
        $currentType = null;
        $maxWinStreak = 0;
        $maxLossStreak = 0;

        foreach ($trades as $trade) {
            $outcomeValue = is_object($trade->outcome) ? $trade->outcome->value : $trade->outcome;
            
            if ($outcomeValue === $currentType) {
                $currentStreak++;
            } else {
                if ($currentType === 'win' && $currentStreak > $maxWinStreak) {
                    $maxWinStreak = $currentStreak;
                }
                if ($currentType === 'loss' && $currentStreak > $maxLossStreak) {
                    $maxLossStreak = $currentStreak;
                }
                
                $currentType = $outcomeValue;
                $currentStreak = 1;
            }
        }

        if ($currentType === 'win' && $currentStreak > $maxWinStreak) {
            $maxWinStreak = $currentStreak;
        }
        if ($currentType === 'loss' && $currentStreak > $maxLossStreak) {
            $maxLossStreak = $currentStreak;
        }

        return [
            'max_win_streak' => $maxWinStreak,
            'max_loss_streak' => $maxLossStreak,
            'current_streak' => $currentStreak,
            'current_type' => $currentType,
        ];
    }

    /**
     * Get time of day performance
     */
    public function getTimeOfDayPerformance(User $user): array
    {
        $trades = $user->trades()->whereNotNull('entry_date')->get(['entry_date', 'profit_loss']);
        $hourly = [];
        for ($h = 0; $h < 24; $h++) {
            $hourly[$h] = ['hour' => $h, 'trades' => 0, 'profit' => 0.0];
        }

        foreach ($trades as $trade) {
            if (!$trade->entry_date) continue;
            $hour = (int) $trade->entry_date->format('G');
            if (isset($hourly[$hour])) {
                $hourly[$hour]['trades']++;
                $hourly[$hour]['profit'] += (float) ($trade->profit_loss ?? 0);
            }
        }

        foreach ($hourly as &$item) {
            $item['profit'] = round($item['profit'], 2);
        }

        return array_values($hourly);
    }

    /**
     * Get day of week performance
     */
    public function getDayOfWeekPerformance(User $user): array
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $trades = $user->trades()->whereNotNull('entry_date')->get(['entry_date', 'profit_loss']);
        $dayStats = [];
        for ($i = 0; $i < 7; $i++) {
            $dayStats[$i] = ['day' => $days[$i], 'trades' => 0, 'profit' => 0.0];
        }

        foreach ($trades as $trade) {
            if (!$trade->entry_date) continue;
            $dayIdx = (int) $trade->entry_date->format('w');
            if (isset($dayStats[$dayIdx])) {
                $dayStats[$dayIdx]['trades']++;
                $dayStats[$dayIdx]['profit'] += (float) ($trade->profit_loss ?? 0);
            }
        }

        foreach ($dayStats as &$item) {
            $item['profit'] = round($item['profit'], 2);
        }

        return array_values($dayStats);
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, ?array $filters = [])
    {
        if (empty($filters)) {
            return $query;
        }

        if (isset($filters['date_from']) && !empty($filters['date_from'])) {
            $query->where('entry_date', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }

        if (isset($filters['date_to']) && !empty($filters['date_to'])) {
            $query->where('entry_date', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        if (isset($filters['pair']) && !empty($filters['pair'])) {
            $query->where('pair', $filters['pair']);
        }

        if (isset($filters['session']) && !empty($filters['session'])) {
            $query->where('session', $filters['session']);
        }

        if (isset($filters['outcome']) && !empty($filters['outcome'])) {
            $query->where('outcome', $filters['outcome']);
        }

        if (isset($filters['trade_account_id']) && !empty($filters['trade_account_id'])) {
            $query->where('trade_account_id', $filters['trade_account_id']);
        }

        if (isset($filters['strategy_id']) && !empty($filters['strategy_id'])) {
            $query->where('strategy_id', $filters['strategy_id']);
        }

        return $query;
    }
}
