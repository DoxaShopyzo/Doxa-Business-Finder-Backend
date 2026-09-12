<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('search_results', function (Blueprint $table) {
            if (!Schema::hasColumn('search_results', 'subscription_id')) {
                $table->foreignId('subscription_id')->nullable()->after('search_id')->constrained('subscriptions')->nullOnDelete();
            }
        });
    }

    public function down(): void {
        Schema::table('search_results', function (Blueprint $table) {
            $table->dropForeign(['subscription_id']);
            $table->dropColumn('subscription_id');
        });
    }
};