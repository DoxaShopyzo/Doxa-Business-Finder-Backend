<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('followups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('followup_date');
            $table->time('followup_time')->nullable();
            $table->string('type')->default('call');
            $table->string('status')->default('scheduled');
            $table->text('notes')->nullable();
            $table->text('outcome')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'user_id', 'status']);
            $table->index(['tenant_id', 'followup_date']);
        });
    }
    public function down(): void { Schema::dropIfExists('followups'); }
};
