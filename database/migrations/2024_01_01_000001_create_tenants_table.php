<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('company_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('country')->default('IN');
            $table->string('logo')->nullable();
            $table->string('business_category')->nullable();
            $table->json('target_locations')->nullable();
            $table->json('services_offered')->nullable();
            $table->enum('status', ['active', 'suspended', 'trial'])->default('trial');
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('tenants'); }
};
