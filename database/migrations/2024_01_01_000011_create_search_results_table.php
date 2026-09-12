<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('search_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('search_id')->constrained('searches')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('place_reference')->nullable()->index();
            $table->string('business_name');
            $table->string('category')->nullable();
            $table->text('formatted_address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->decimal('rating', 2, 1)->nullable();
            $table->integer('review_count')->nullable();
            $table->integer('opportunity_score')->nullable();
            $table->string('source')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index('search_id');
            $table->index(['tenant_id', 'place_reference']);
            $table->index('opportunity_score');
        });
    }
    public function down(): void { Schema::dropIfExists('search_results'); }
};
