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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Mã sản phẩm/dịch vụ
            $table->string('product_code', 50)->unique();

            // Tên sản phẩm/dịch vụ
            $table->string('product_name', 255);

            // Loại sản phẩm/dịch vụ
            $table->foreignId('product_category_id')
                ->nullable()
                ->constrained('product_categories')
                ->nullOnDelete();

            // Giá bán
            $table->decimal('price', 15, 2)->default(0);

            // Trạng thái bật/tắt
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Tối ưu tìm kiếm/lọc
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
