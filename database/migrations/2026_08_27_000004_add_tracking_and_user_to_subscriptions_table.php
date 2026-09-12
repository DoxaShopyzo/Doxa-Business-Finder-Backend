<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('subscriptions', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('tenant_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('subscriptions', 'duration_days')) {
                $table->integer('duration_days')->nullable()->after('plan_id');
            }
            if (!Schema::hasColumn('subscriptions', 'data_limit_total')) {
                $table->integer('data_limit_total')->nullable()->after('duration_days');
            }
            if (!Schema::hasColumn('subscriptions', 'data_used_total')) {
                $table->integer('data_used_total')->default(0)->after('data_limit_total');
            }
            if (!Schema::hasColumn('subscriptions', 'last_data_pull_at')) {
                $table->timestamp('last_data_pull_at')->nullable()->after('data_used_total');
            }
        });
    }

    public function down(): void {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id',
                'duration_days',
                'data_limit_total',
                'data_used_total',
                'last_data_pull_at'
            ]);
        });
    }
};