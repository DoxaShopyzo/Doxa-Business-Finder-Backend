<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('created_by');
            $table->string('name');
            $table->string('client_name');
            $table->string('product_service');
            $table->json('target_categories');
            $table->string('target_location');
            $table->string('target_customer_type')->nullable();
            $table->string('minimum_business_size', 100)->nullable();
            $table->text('preferred_characteristics')->nullable();
            $table->unsignedInteger('required_lead_count')->default(50);
            $table->text('additional_requirements')->nullable();
            $table->json('scoring_weights')->nullable();
            $table->enum('status', ['draft', 'processing', 'in_verification', 'completed', 'failed'])->default('draft');
            $table->unsignedInteger('total_discovered')->default(0);
            $table->unsignedInteger('total_prefiltered')->default(0);
            $table->unsignedInteger('total_ai_analyzed')->default(0);
            $table->unsignedInteger('total_approved')->default(0);
            $table->unsignedInteger('total_rejected')->default(0);
            $table->unsignedInteger('credits_spent')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_campaigns');
    }
};
