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
        Schema::create('customer_needs', function (Blueprint $table) {
            $table->id();

            // Mã nhu cầu, dùng trong code
            $table->string('code', 50)->unique();

            // Tên nhu cầu hiển thị trên giao diện
            $table->string('name', 150);

            // Mô tả thêm
            $table->text('description')->nullable();

            // Thứ tự hiển thị
            $table->unsignedInteger('sort_order')->default(0);

            // Bật / tắt nhu cầu
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_needs');
    }
};
