<?php

namespace Database\Seeders;

use App\Models\AnalystAssignment;
use App\Models\Feedback;
use App\Models\Notification;
use App\Models\Trade;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class FeedbackSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake();
        $assignments = AnalystAssignment::with(['analyst', 'trader'])->get();

        foreach ($assignments as $assignment) {
            $analyst = $assignment->analyst;
            $trader = $assignment->trader;

            // 1. General Feedback
            for ($i = 0; $i < 3; $i++) {
                $created = now()->subDays(rand(1, 30));
                
                $feedback = Feedback::create([
                    'trader_id' => $trader->id,
                    'analyst_id' => $analyst->id,
                    'type' => 'general', // assuming 'general' or 'trade' types
                    'content' => $faker->paragraph(),
                    'created_at' => $created,
                    'updated_at' => $created,
                ]);

                // Notify
                Notification::create([
                    'user_id' => $trader->id,
                    'type' => 'feedback_received',
                    'data' => [
                        'message' => 'New feedback from ' . $analyst->name,
                        'link' => route('trader.feedback.show', $feedback->id),
                    ],
                    'read_at' => rand(0, 1) ? now() : null,
                ]);
            }

            // 2. Trade-specific Feedback
            $trades = Trade::where('user_id', $trader->id)->inRandomOrder()->take(3)->get();
            
            foreach ($trades as $trade) {
                $created = $trade->exit_date->addHours(rand(2, 24));

                $feedback = Feedback::create([
                    'trader_id' => $trader->id,
                    'analyst_id' => $analyst->id,
                    'trade_id' => $trade->id,
                    'type' => 'trade',
                    'content' => "Good entry on this " . $trade->pair . " trade. " . $faker->sentence(),
                    'created_at' => $created,
                    'updated_at' => $created,
                ]);
                
                $trade->update(['has_feedback' => true]);

                Notification::create([
                    'user_id' => $trader->id,
                    'type' => 'feedback_received',
                    'data' => [
                        'message' => 'New feedback on trade #' . $trade->id,
                        'link' => route('trader.feedback.show', $feedback->id),
                    ],
                    'read_at' => rand(0, 1) ? now() : null,
                ]);
            }
        }

        $this->command->info('Feedback and Notifications seeded.');
    }
}
