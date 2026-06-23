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
        Schema::create('customer_referrals', function (Blueprint $table) {
            $table->id();

            // Người giới thiệu
            $table->foreignId('referrer_customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();

            // Người được giới thiệu
            $table->foreignId('referred_customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            // Lưu lại số điện thoại người giới thiệu tại thời điểm tạo
            $table->string('referrer_phone', 20)->nullable();

            // Tỷ lệ hoa hồng riêng cho lượt giới thiệu này
            $table->decimal('commission_rate', 5, 2)->nullable();

            // Trạng thái giới thiệu
            $table->foreignId('referral_status_id')
                ->nullable()
                ->constrained('referral_statuses')
                ->nullOnDelete();

            // Thời gian bắt đầu / kết thúc tính giới thiệu
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            // Ghi chú
            $table->text('note')->nullable();

            $table->timestamps();

            // Tránh một khách được gán cùng một người giới thiệu nhiều lần
            $table->unique([
                'referrer_customer_id',
                'referred_customer_id'
            ], 'unique_customer_referral');

            // Tối ưu lọc dữ liệu
            $table->index('referrer_phone');
            $table->index('started_at');
            $table->index('ended_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_referrals');
    }
};
