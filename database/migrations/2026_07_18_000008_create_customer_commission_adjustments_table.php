<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_commission_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_commission_id')->constrained('customer_commissions')->restrictOnDelete();
            $table->foreignId('customer_order_return_id')->nullable()->constrained('customer_order_returns')->restrictOnDelete();
            $table->string('adjustment_code', 50)->unique();
            $table->string('adjustment_type', 30)->default('clawback');
            $table->decimal('amount', 15, 2);
            $table->decimal('recovered_amount', 15, 2)->default(0);
            $table->string('status', 30)->default('pending');
            $table->text('reason');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('recovered_at')->nullable();
            $table->timestamps();

            $table->unique('customer_order_return_id', 'uq_commission_adjustment_return');
            $table->index(['customer_commission_id', 'status'], 'idx_commission_adjustment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_commission_adjustments');
    }
};
