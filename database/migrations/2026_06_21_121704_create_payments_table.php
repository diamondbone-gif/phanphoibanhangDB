<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng lưu các lần thanh toán của đơn hàng.
     * Một đơn hàng có thể thanh toán 1 lần hoặc nhiều lần.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->string('payment_code', 100)->unique();

            $table->foreignId('customer_order_id')
                ->constrained('customer_orders')
                ->restrictOnDelete();

            $table->foreignId('payment_status_id')
                ->nullable()
                ->constrained('payment_statuses')
                ->nullOnDelete();

            $table->decimal('amount', 15, 2)->default(0);

            /**
             * cash: tiền mặt
             * bank_transfer: chuyển khoản
             * card: thẻ
             */
            $table->string('payment_method', 100)->nullable();

            $table->timestamp('payment_date')->nullable();

            $table->text('note')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('operation_managers')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('payment_code');
            $table->index('customer_order_id');
            $table->index('payment_status_id');
            $table->index('payment_method');
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
