<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::table('plans', function (Blueprint $table) {
            if (!Schema::hasColumn('plans', 'duration_days')) {
                $table->integer('duration_days')->nullable()->after('billing_cycle');
            }
            if (!Schema::hasColumn('plans', 'max_data_units_total')) {
                $table->integer('max_data_units_total')->nullable()->after('max_searches_per_day');
            }
            if (!Schema::hasColumn('plans', 'reset_daily_limit_on_period')) {
                $table->boolean('reset_daily_limit_on_period')->default(false)->after('max_data_units_total');
            }
        });

        try {
            DB::statement("ALTER TABLE plans MODIFY billing_cycle ENUM('monthly','yearly','one_time','custom') NOT NULL DEFAULT 'monthly'");
        } catch (\Throwable $e) {}
    }

    public function down(): void {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['duration_days', 'max_data_units_total', 'reset_daily_limit_on_period']);
        });
    }
};