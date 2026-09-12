<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('wallet_id')->constrained('credit_wallets')->cascadeOnDelete();
            $table->string('type');
            $table->integer('credits');
            $table->integer('balance_before');
            $table->integer('balance_after');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['tenant_id', 'created_at']);
            $table->index('wallet_id');
            $table->index(['reference_type', 'reference_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('credit_transactions'); }
};
