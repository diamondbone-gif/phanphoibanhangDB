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
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();

            // Mã loại sản phẩm/dịch vụ
            $table->string('code', 50)->unique();

            // Tên loại sản phẩm/dịch vụ
            $table->string('name', 150);

            // Mô tả thêm
            $table->text('description')->nullable();

            // Thứ tự hiển thị
            $table->unsignedInteger('sort_order')->default(0);

            // Bật / tắt
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};
