<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_orders', function (Blueprint $table) {
            $table->id();

            // Khách hàng đặt đơn
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            // Mã đơn hàng
            $table->string('order_code', 50)->unique();

            // Ngày tạo đơn / ngày mua hàng
            $table->date('order_date')->nullable();

            // Tổng tiền đơn hàng
            $table->decimal('total_amount', 15, 2)->default(0);

            // Số tiền giảm giá
            $table->decimal('discount_amount', 15, 2)->default(0);

            // Phí vận chuyển
            $table->decimal('shipping_fee', 15, 2)->default(0);

            // Số tiền dùng để tính hoa hồng
            $table->decimal('commission_base_amount', 15, 2)->default(0);

            // Trạng thái thanh toán
            $table->foreignId('payment_status_id')
                ->nullable()
                ->constrained('payment_statuses')
                ->nullOnDelete();

            // Trạng thái đơn hàng
            $table->foreignId('order_status_id')
                ->nullable()
                ->constrained('order_statuses')
                ->nullOnDelete();

            $table->timestamps();

            // Tối ưu tìm kiếm/lọc
            $table->index('order_date');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_orders');
    }
};
