<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->enum('status', ['active', 'suspended', 'invited'])->default('active');
            $table->timestamp('last_login_at')->nullable();
            $table->integer('login_count')->default(0);
            $table->boolean('onboarding_completed')->default(false);
            $table->boolean('is_super_admin')->default(false);
            $table->json('settings')->nullable();
            $table->softDeletes();
            
            $table->index('tenant_id');
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn(['tenant_id', 'phone', 'avatar', 'status', 'last_login_at', 'login_count', 'onboarding_completed', 'is_super_admin', 'settings', 'deleted_at']);
        });
    }
};
