<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->foreignId('strategy_id')->nullable()->constrained('strategies')->nullOnDelete();
            $table->string('trade_type')->nullable(); // Scalp, Day, Swing, etc.
            $table->decimal('risk_percentage', 5, 2)->nullable();
            
            // Detailed Prices & Sizing
            $table->decimal('entry_price', 15, 5)->nullable();
            $table->decimal('exit_price', 15, 5)->nullable();
            $table->decimal('stop_loss', 15, 5)->nullable();
            $table->decimal('take_profit', 15, 5)->nullable();
            $table->decimal('lot_size', 8, 2)->nullable();

            // Psychology
            $table->string('pre_trade_emotion')->nullable();
            $table->string('post_trade_emotion')->nullable();
            $table->boolean('followed_plan')->nullable();
            
            // Notes & Analysis
            $table->text('mistakes_lessons')->nullable();
            $table->text('setup_notes')->nullable();
            $table->string('chart_link')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropForeign(['strategy_id']);
            $table->dropColumn([
                'strategy_id',
                'trade_type',
                'risk_percentage',
                'entry_price',
                'exit_price',
                'stop_loss',
                'take_profit',
                'lot_size',
                'pre_trade_emotion',
                'post_trade_emotion',
                'followed_plan',
                'mistakes_lessons',
                'setup_notes',
                'chart_link'
            ]);
        });
    }
};
