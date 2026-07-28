<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Models\TradeAccount;
use App\Models\User;
use Illuminate\Database\Seeder;

class TradeAccountSeeder extends Seeder
{
    public function run(): void
    {
        $traders = User::role('trader')->get();

        foreach ($traders as $trader) {
            // 1. Live Account
            TradeAccount::firstOrCreate(
                [
                    'user_id' => $trader->id,
                    'account_name' => 'Main Live Account',
                ],
                [
                    'account_type' => AccountType::REAL,
                    'broker' => 'IC Markets',
                    'initial_balance' => 10000,
                    'currency' => 'USD',
                    'is_system_default' => true,
                ]
            );

            // 2. Demo Account
            TradeAccount::firstOrCreate(
                [
                    'user_id' => $trader->id,
                    'account_name' => 'Challenge Demo',
                ],
                [
                    'account_type' => AccountType::DEMO,
                    'broker' => 'FTMO',
                    'initial_balance' => 100000,
                    'currency' => 'USD',
                    'is_system_default' => false,
                ]
            );

             // 3. Small Account
             TradeAccount::firstOrCreate(
                [
                    'user_id' => $trader->id,
                    'account_name' => 'Small Flip',
                ],
                [
                    'account_type' => AccountType::REAL,
                    'broker' => 'Oanda',
                    'initial_balance' => 500,
                    'currency' => 'USD',
                    'is_system_default' => false,
                ]
            );
        }

        $this->command->info('Trade Accounts seeded for ' . $traders->count() . ' traders.');
    }
}
