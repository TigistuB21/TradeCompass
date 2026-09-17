<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users ALTER COLUMN username TYPE VARCHAR(100)');
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username', 100)->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users ALTER COLUMN username TYPE VARCHAR(20)');
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username', 20)->nullable()->change();
            });
        }
    }
};
