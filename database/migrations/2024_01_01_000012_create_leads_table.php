<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('search_result_id')->nullable()->constrained('search_results')->nullOnDelete();
            $table->string('place_reference')->nullable();
            $table->string('business_name');
            $table->string('category')->nullable();
            $table->string('location')->nullable();
            $table->text('formatted_address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('source')->default('business_finder');
            $table->string('status')->default('new');
            $table->string('priority')->default('warm');
            $table->integer('opportunity_score')->nullable();
            $table->decimal('expected_value', 12, 2)->nullable();
            $table->integer('probability')->nullable();
            $table->decimal('won_value', 12, 2)->nullable();
            $table->string('lost_reason')->nullable();
            $table->text('notes')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'assigned_to']);
            $table->index(['tenant_id', 'priority', 'created_at']);
            $table->index(['tenant_id', 'source']);
            $table->index(['tenant_id', 'phone']);
            $table->index(['tenant_id', 'website']);
            $table->index(['tenant_id', 'place_reference']);
        });
    }
    public function down(): void { Schema::dropIfExists('leads'); }
};
