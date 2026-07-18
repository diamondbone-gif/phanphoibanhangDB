<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->unique();
            $table->string('type', 30)->index();
            $table->string('status', 30)->index();
            $table->foreignId('customer_order_id')->nullable()->constrained('customer_orders')->nullOnDelete();
            $table->foreignId('customer_order_return_id')->nullable()->constrained('customer_order_returns')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 100)->nullable();
            $table->string('bank_reference')->nullable()->index();
            $table->string('attachment_path')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('operation_managers')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('operation_managers')->nullOnDelete();
            $table->foreignId('executed_by')->nullable()->constrained('operation_managers')->nullOnDelete();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['customer_order_id', 'type', 'status'], 'idx_finance_order_type_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
