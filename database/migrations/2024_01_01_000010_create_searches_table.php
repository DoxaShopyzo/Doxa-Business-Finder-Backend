<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('searches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('keyword')->nullable();
            $table->string('category')->nullable();
            $table->string('location');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('radius')->nullable();
            $table->json('filters')->nullable();
            $table->integer('result_count')->default(0);
            $table->integer('credits_used')->default(0);
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->string('api_provider')->nullable();
            $table->decimal('api_cost', 8, 4)->default(0);
            $table->integer('response_time_ms')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'created_at']);
            $table->index('status');
        });
    }
    public function down(): void { Schema::dropIfExists('searches'); }
};
