<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analyst_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analyst_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('trader_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('status')->default('active');
            $table->timestamp('start_date')->nullable();
            $table->timestamps();
            
            $table->unique(['analyst_id', 'trader_id']);
            $table->index('analyst_id');
            $table->index('trader_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analyst_assignments');
    }
};
