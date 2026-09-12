<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generated_previews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('campaign_lead_id')->nullable();
            $table->unsignedBigInteger('crm_lead_id')->nullable();
            $table->unsignedBigInteger('template_id');
            $table->enum('preview_type', ['website', 'app']);
            $table->string('token', 64)->unique();
            $table->string('business_name');
            $table->string('category', 100)->nullable();
            $table->text('logo_url')->nullable();
            $table->json('theme_json'); // CSS tokens: primary, secondary, accent, bg, font, radius
            $table->json('custom_content')->nullable(); // phone, address, rating, tagline, service_items
            $table->unsignedInteger('view_count')->default(0);
            $table->timestamp('last_viewed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('campaign_lead_id')->references('id')->on('campaign_leads')->onDelete('cascade');
            $table->foreign('crm_lead_id')->references('id')->on('leads')->onDelete('cascade');
            $table->foreign('template_id')->references('id')->on('preview_templates')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['tenant_id', 'created_at']);
            $table->index(['campaign_lead_id', 'preview_type']);
            $table->index(['crm_lead_id', 'preview_type']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_previews');
    }
};
