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
        Schema::create('customer_details', function (Blueprint $table) {
            $table->id();

            // Liên kết với bảng customers
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            // Địa chỉ
            $table->string('province', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('ward', 100)->nullable();
            $table->string('address', 255)->nullable();

            // Ghi chú y tế / tình trạng sức khỏe
            $table->text('medical_note')->nullable();

            // Khách mua cho ai
            $table->foreignId('buy_for_option_id')
                ->nullable()
                ->constrained('buy_for_options')
                ->nullOnDelete();

            // Sản phẩm/dịch vụ khách quan tâm
            $table->foreignId('interested_product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            // Ghi chú tư vấn
            $table->text('consultation_note')->nullable();

            $table->timestamps();

            // Mỗi khách chỉ có 1 dòng chi tiết
            $table->unique('customer_id', 'unique_customer_detail');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_details');
    }
};
