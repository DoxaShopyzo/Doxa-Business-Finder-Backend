<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('customer_address')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('gst_percent', 5, 2)->default(18.00);
            $table->decimal('gst_amount', 10, 2);
            $table->decimal('total', 10, 2);
            $table->string('currency')->default('INR');
            $table->enum('status', ['draft', 'paid', 'cancelled'])->default('draft');
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('invoices'); }
};
