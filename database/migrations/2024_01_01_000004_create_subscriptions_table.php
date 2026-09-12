<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->enum('status', ['active', 'expired', 'cancelled', 'trial'])->default('trial');
            $table->timestamp('starts_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index('expires_at');
        });
    }
    public function down(): void { Schema::dropIfExists('subscriptions'); }
};
