<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('campaign_id');
            $table->string('place_reference'); // Google Place ID (retained indefinitely)

            // Google Sourced Attributes (Subject to 30-day purge)
            $table->string('business_name')->nullable();
            $table->string('category', 100)->nullable();
            $table->text('location_address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('website')->nullable();
            $table->decimal('google_rating', 2, 1)->nullable();
            $table->unsignedInteger('google_review_count')->nullable();
            $table->string('business_status', 50)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamp('google_purged_at')->nullable();

            // Stage A Pre-filter (Rule-based, zero LLM cost)
            $table->boolean('prefilter_passed')->default(true);
            $table->unsignedTinyInteger('prefilter_score')->default(0);
            $table->json('prefilter_reasons')->nullable();

            // Stage B AI Scoring & Intelligence (Batched LLM output)
            $table->unsignedTinyInteger('ai_score')->default(0);
            $table->enum('ai_classification', ['VERY_HIGH', 'HIGH', 'MEDIUM', 'LOW', 'VERY_LOW'])->default('MEDIUM');
            $table->enum('potential_requirement', ['HIGH', 'MEDIUM', 'LOW'])->default('MEDIUM');
            $table->enum('confidence_level', ['HIGH', 'MEDIUM', 'LOW'])->default('MEDIUM');
            $table->text('qualification_reason')->nullable();
            $table->enum('sales_priority', ['HIGH', 'MEDIUM', 'LOW'])->default('MEDIUM');
            $table->text('suggested_sales_question')->nullable();
            $table->json('score_breakdown')->nullable();

            // Human Verification Workflow
            $table->enum('verification_status', ['pending', 'needs_manual_review', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedTinyInteger('human_score')->nullable();
            $table->string('human_classification', 50)->nullable();
            $table->text('human_remarks')->nullable();

            // TRAI / DND & Consent Compliance (India telecalling)
            $table->enum('dnd_status', ['not_checked', 'dnd_active', 'dnd_inactive', 'exempt'])->default('not_checked');
            $table->timestamp('dnd_checked_at')->nullable();
            $table->string('dnd_check_reference')->nullable();
            $table->enum('consent_status', ['none', 'explicit_consent', 'opt_in', 'revoked'])->default('none');
            $table->timestamp('consent_timestamp')->nullable();
            $table->string('consent_channel', 50)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['campaign_id', 'verification_status'], 'idx_camp_verif');
            $table->index(['campaign_id', 'confidence_level'], 'idx_camp_conf');
            $table->index(['campaign_id', 'place_reference'], 'idx_camp_place');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('campaign_id')->references('id')->on('lead_campaigns')->onDelete('cascade');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_leads');
    }
};
