<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('api_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('search_id')->nullable()->constrained('searches')->nullOnDelete();
            $table->string('provider');
            $table->string('endpoint')->nullable();
            $table->json('request_data')->nullable();
            $table->integer('response_status')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->decimal('estimated_cost', 8, 4)->default(0);
            $table->integer('credits_charged')->default(0);
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['tenant_id', 'created_at']);
            $table->index(['provider', 'created_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('api_usage_logs'); }
};
