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
        Schema::create('customer_need_maps', function (Blueprint $table) {
            $table->id();

            // Khách hàng
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            // Nhu cầu của khách hàng
            $table->foreignId('customer_need_id')
                ->constrained('customer_needs')
                ->cascadeOnDelete();

            // Ghi chú thêm cho nhu cầu này
            $table->text('note')->nullable();

            $table->timestamps();

            // Tránh gán trùng 1 nhu cầu cho cùng 1 khách nhiều lần
            $table->unique([
                'customer_id',
                'customer_need_id'
            ], 'unique_customer_need_map');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_need_maps');
    }
};
