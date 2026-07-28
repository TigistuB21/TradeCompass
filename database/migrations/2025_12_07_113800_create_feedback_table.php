<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trader_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('analyst_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('trade_id')->nullable()->constrained('trades')->onDelete('cascade');
            $table->string('type')->default('general');
            $table->text('content');
            $table->json('ai_suggestions')->nullable();
            $table->enum('status', ['draft', 'submitted', 'locked'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['trader_id', 'analyst_id']);
            $table->index('trade_id');
            $table->index(['status', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
