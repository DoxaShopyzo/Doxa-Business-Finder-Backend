<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preview_send_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('preview_id');
            $table->unsignedBigInteger('campaign_lead_id')->nullable();
            $table->unsignedBigInteger('crm_lead_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('channel', ['whatsapp_deeplink', 'whatsapp_cloud_api', 'email', 'sms'])->default('whatsapp_deeplink');
            $table->string('recipient_phone', 50)->nullable();
            $table->text('message_body')->nullable();
            $table->enum('status', ['initiated', 'sent', 'delivered', 'failed'])->default('initiated');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('preview_id')->references('id')->on('generated_previews')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preview_send_logs');
    }
};
