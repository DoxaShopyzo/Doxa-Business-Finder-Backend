<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'active_session_token')) {
                $table->string('active_session_token', 100)->nullable()->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'active_device_label')) {
                $table->string('active_device_label', 191)->nullable()->after('active_session_token');
            }
            if (!Schema::hasColumn('users', 'session_started_at')) {
                $table->timestamp('session_started_at')->nullable()->after('active_device_label');
            }
            if (!Schema::hasColumn('users', 'membership_expires_at')) {
                $table->timestamp('membership_expires_at')->nullable()->after('session_started_at')->index();
            }
            if (!Schema::hasColumn('users', 'force_logout')) {
                $table->boolean('force_logout')->default(false)->after('membership_expires_at');
            }
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'active_session_token',
                'active_device_label',
                'session_started_at',
                'membership_expires_at',
                'force_logout'
            ]);
        });
    }
};