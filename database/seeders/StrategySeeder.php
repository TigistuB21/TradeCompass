<?php

namespace Database\Seeders;

use App\Enums\StrategyStatus;
use App\Models\Strategy;
use App\Models\User;
use Illuminate\Database\Seeder;

class StrategySeeder extends Seeder
{
    public function run(): void
    {
        $traders = User::role('trader')->get();

        foreach ($traders as $trader) {
            // 1. Trend Following (Active)
            Strategy::firstOrCreate(
                [
                    'user_id' => $trader->id,
                    'name' => 'Trend Pullback',
                ],
                [
                    'description' => 'Buying pullbacks in an established uptrend on the H1 timeframe.',
                    'status' => StrategyStatus::ACTIVE,
                    'tags' => ['trend', 'pullback', 'h1'],
                    'rules' => [
                        'Price must be above 50 EMA',
                        'RSI must not be overbought',
                        'Wait for bullish engulfing candle',
                        'Stop loss below swing low',
                        'Target 2R minimum'
                    ],
                ]
            );

            // 2. Breakout (Testing)
            Strategy::firstOrCreate(
                [
                    'user_id' => $trader->id,
                    'name' => 'London Breakout',
                ],
                [
                    'description' => 'Trading the breakout of the Asian range at London Open.',
                    'status' => StrategyStatus::TESTING,
                    'tags' => ['breakout', 'london-session', 'volatility'],
                    'rules' => [
                        'Identify Asian High and Low',
                        'Wait for 15m candle close outside range',
                        'Enter on retest of range edge',
                        'Stop loss inside range',
                    ],
                ]
            );

            // 3. Counter Trend (Retired)
            Strategy::firstOrCreate(
                [
                    'user_id' => $trader->id,
                    'name' => 'RSI Divergence',
                ],
                [
                    'description' => 'Fading moves at extreme RSI levels.',
                    'status' => StrategyStatus::ARCHIVED,
                    'tags' => ['reversal', 'rsi', 'risky'],
                    'rules' => [
                        'RSI > 70 or < 30',
                        'Wait for divergence on M15',
                        'Enter on candle close',
                    ],
                ]
            );
        }

        $this->command->info('Strategies seeded for ' . $traders->count() . ' traders.');
    }
}
