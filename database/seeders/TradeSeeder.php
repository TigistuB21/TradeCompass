<?php

namespace Database\Seeders;

use App\Enums\MarketSession;
use App\Enums\TradeDirection;
use App\Enums\TradeOutcome;
use App\Models\Strategy;
use App\Models\Trade;
use App\Models\TradeAccount;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class TradeSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $traders = User::role('trader')->get();

        foreach ($traders as $trader) {
            $accounts = TradeAccount::where('user_id', $trader->id)->get();
            $strategies = Strategy::where('user_id', $trader->id)->get();

            if ($accounts->isEmpty() || $strategies->isEmpty()) {
                continue;
            }

            // Generate 6-10 trades per trader for fast seeding
            $tradeCount = rand(6, 10);
            $startDate = Carbon::now()->subMonths(2);

            for ($i = 0; $i < $tradeCount; $i++) {
                // Pick account and strategy
                $account = $accounts->random();
                $strategy = $strategies->random();

                // Trade details
                $pair = $faker->randomElement(['EURUSD', 'GBPUSD', 'USDJPY', 'AUDUSD', 'XAUUSD', 'US30']);
                $direction = $faker->randomElement([TradeDirection::BUY, TradeDirection::SELL]);
                $session = $faker->randomElement([MarketSession::LONDON, MarketSession::NEWYORK, MarketSession::ASIA]);
                
                // Timeline
                $entryDate = (clone $startDate)->addDays(rand(1, 60))->addHours(rand(0, 23))->addMinutes(rand(0, 59));
                $duration = rand(1, 48); // 1 to 48 hours
                $exitDate = (clone $entryDate)->addHours($duration);

                // Outcome logic
                $outcome = $faker->randomElement([
                    TradeOutcome::WIN, 
                    TradeOutcome::WIN, 
                    TradeOutcome::LOSS, 
                    TradeOutcome::LOSS, 
                    TradeOutcome::BREAKEVEN
                ]); // Weighted slightly towards active trading

                $riskReward = $faker->randomFloat(2, 1, 4); // 1:1 to 1:4 normally
                $riskAmount = $account->initial_balance * 0.01; // 1% risk
                
                if ($outcome === TradeOutcome::WIN) {
                    $pips = rand(10, 100);
                    $pl = $riskAmount * $riskReward;
                } elseif ($outcome === TradeOutcome::LOSS) {
                    $pips = rand(10, 50) * -1;
                    $pl = -$riskAmount;
                } else {
                    $pips = 0;
                    $pl = 0;
                }

                // Create Trade
                $trade = Trade::create([
                    'user_id' => $trader->id,
                    'trade_account_id' => $account->id,
                    'strategy_id' => $strategy->id,
                    'pair' => $pair,
                    'direction' => $direction,
                    'trade_type' => 'MARKET', // Simplifying
                    'entry_date' => $entryDate,
                    'exit_date' => $exitDate,
                    'entry_price' => $faker->randomFloat(4, 1, 2000), // Approximate price
                    'exit_price' => $faker->randomFloat(4, 1, 2000), 
                    'risk_percentage' => 1.0,
                    'risk_reward_ratio' => $riskReward,
                    'pips' => $pips,
                    'profit_loss' => $pl,
                    'session' => $session,
                    'outcome' => $outcome,
                    'has_feedback' => false, // Will verify later in distinct seeder if desired
                    'notes' => $faker->sentence(),
                    'setup_notes' => $faker->sentence(),
                    'pre_trade_emotion' => $faker->randomElement(['Confident', 'Anxious', 'Neutral', 'Excited']),
                    'post_trade_emotion' => $outcome === TradeOutcome::WIN ? 'Satisfied' : 'Frustrated',
                    'followed_plan' => $faker->boolean(80),
                ]);
            }
        }

        $this->command->info('Trades seeded successfully.');
    }
}
