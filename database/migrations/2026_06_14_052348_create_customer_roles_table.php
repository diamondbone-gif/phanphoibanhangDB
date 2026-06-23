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
        Schema::create('customer_roles', function (Blueprint $table) {
            $table->id();

            // Mã vai trò, dùng trong code để xử lý logic
            $table->string('code', 50)->unique();

            // Tên vai trò hiển thị trên giao diện
            $table->string('name', 100);

            // Mô tả thêm
            $table->text('description')->nullable();

            // Thứ tự hiển thị
            $table->unsignedInteger('sort_order')->default(0);

            // Bật / tắt vai trò
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_roles');
    }
};
