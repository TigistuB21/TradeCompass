<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@forexjournal.com'],
            [
                'name' => 'System Admin',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'bio' => 'System Administrator for Forex Journal.',
                'country' => 'US',
                'timezone' => 'America/New_York',
            ]
        );
        $admin->assignRole('admin');
        
        $this->command->info('created Admin user: admin@forexjournal.com');

        // 2. Create Analysts
        $analysts = [
            [
                'name' => 'Sarah Market',
                'email' => 'sarah.analyst@forexjournal.com',
                'username' => 'sarah_charts',
                'bio' => 'Senior Technical Analyst with 10 years experience in FX majors.',
                'specialization' => 'Technical Analysis',
                'years_of_experience' => 10,
                'max_traders' => 10,
            ],
            [
                'name' => 'Mike Macro',
                'email' => 'mike.analyst@forexjournal.com',
                'username' => 'macro_mike',
                'bio' => 'Global Macro Strategist focused on central bank policies.',
                'specialization' => 'Fundamental Analysis',
                'years_of_experience' => 8,
                'max_traders' => 15,
            ],
            [
                'name' => 'Dr. Psychology',
                'email' => 'doc.psych@forexjournal.com',
                'username' => 'trading_mind',
                'bio' => 'Trading Psychology expert helping traders master their mindset.',
                'specialization' => 'Psychology',
                'years_of_experience' => 12,
                'max_traders' => 5,
            ],
        ];

        foreach ($analysts as $data) {
            $analyst = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'username' => $data['username'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'bio' => $data['bio'],
                    // Analyst specific fields (assuming these exist on User or related profile)
                    // If they are on user table directly based on task description "Analyst Profiles"
                    'specialization' => $data['specialization'], 
                    'years_of_experience' => $data['years_of_experience'],
                    'verification_status' => 'verified',
                ]
            );
            $analyst->assignRole('analyst');
        }
        $this->command->info('Created ' . count($analysts) . ' Analyst users.');

        // 3. Create Traders
        $traderProfiles = [
            [
                'name' => 'John Scalper',
                'email' => 'john.trader@example.com',
                'username' => 'j_scalps',
                'style' => 'Scalper',
                'experience' => 'beginner',
                'bio' => 'New to trading, trying to learn price action on M1/M5.',
            ],
            [
                'name' => 'Emma Swing',
                'email' => 'emma.trader@example.com',
                'username' => 'emma_swings',
                'style' => 'Swing',
                'experience' => 'intermediate',
                'bio' => 'Swing trader holding positions for days. Focus on H4/D1.',
            ],
            [
                'name' => 'Alex Day',
                'email' => 'alex.trader@example.com',
                'username' => 'alex_daytrade',
                'style' => 'Day Trader',
                'experience' => 'advanced',
                'bio' => 'Full-time day trader. Indices and Majors.',
            ],
            [
                'name' => 'Crypto King',
                'email' => 'crypto.trader@example.com',
                'username' => 'btc_king',
                'style' => 'Swing',
                'experience' => 'intermediate',
                'bio' => 'Crypto enthusiast diversifying into FX.',
            ],
            [
                'name' => 'Lisa Levels',
                'email' => 'lisa.trader@example.com',
                'username' => 'lisa_levels',
                'style' => 'Price Action',
                'experience' => 'advanced',
                'bio' => 'Supply and Demand trader.',
            ],
             [
                'name' => 'Tom Trend',
                'email' => 'tom.trader@example.com',
                'username' => 'tom_trends',
                'style' => 'Trend Following',
                'experience' => 'beginner',
                'bio' => 'Following the trend until it bends.',
            ],
        ];

        foreach ($traderProfiles as $data) {
            $trader = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'username' => $data['username'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'bio' => $data['bio'],
                    'trading_style' => $data['style'],
                    'experience_level' => $data['experience'],
                    'country' => 'US',
                    'timezone' => 'America/New_York',
                    'profile_visibility' => 'public',
                ]
            );
            $trader->assignRole('trader');
        }
        $this->command->info('Created ' . count($traderProfiles) . ' Trader users.');
    }
}
