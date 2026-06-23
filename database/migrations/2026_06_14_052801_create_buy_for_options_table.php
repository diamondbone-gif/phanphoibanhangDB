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
        Schema::create('buy_for_options', function (Blueprint $table) {
            $table->id();

            // Mã lựa chọn, dùng trong code
            $table->string('code', 50)->unique();

            // Tên hiển thị trên giao diện
            $table->string('name', 100);

            // Mô tả thêm
            $table->text('description')->nullable();

            // Thứ tự hiển thị
            $table->unsignedInteger('sort_order')->default(0);

            // Bật / tắt lựa chọn
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buy_for_options');
    }
};
