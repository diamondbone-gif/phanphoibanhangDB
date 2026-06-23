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
        Schema::create('customer_order_items', function (Blueprint $table) {
            $table->id();

            // Liên kết với đơn hàng chính
            $table->foreignId('customer_order_id')
                ->constrained('customer_orders')
                ->cascadeOnDelete();

            // Liên kết với sản phẩm/dịch vụ
            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            // Lưu lại tên sản phẩm tại thời điểm tạo đơn
            $table->string('product_name', 255);

            // Số lượng
            $table->unsignedInteger('quantity')->default(1);

            // Đơn giá
            $table->decimal('unit_price', 15, 2)->default(0);

            // Giảm giá trên dòng sản phẩm
            $table->decimal('discount_amount', 15, 2)->default(0);

            // Thành tiền của dòng sản phẩm
            $table->decimal('line_total', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_order_items');
    }
};
