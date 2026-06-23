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
        Schema::create('customer_commissions', function (Blueprint $table) {
            $table->id();

            // Người giới thiệu / CTV
            $table->foreignId('referrer_customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();

            // Khách hàng được giới thiệu
            $table->foreignId('referred_customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();

            // Liên kết với bảng customer_referrals
            $table->foreignId('referral_id')
                ->nullable()
                ->constrained('customer_referrals')
                ->nullOnDelete();

            // Đơn hàng phát sinh hoa hồng
            $table->foreignId('customer_order_id')
                ->nullable()
                ->constrained('customer_orders')
                ->nullOnDelete();

            // Lưu lại mã đơn hàng tại thời điểm tạo hoa hồng
            $table->string('order_code', 50)->nullable();

            // Giá trị đơn hàng dùng để tham chiếu
            $table->decimal('order_amount', 15, 2)->default(0);

            // Tỷ lệ hoa hồng
            $table->decimal('commission_rate', 5, 2)->default(0);

            // Số tiền hoa hồng
            $table->decimal('commission_amount', 15, 2)->default(0);

            // Trạng thái hoa hồng
            $table->foreignId('commission_status_id')
                ->nullable()
                ->constrained('commission_statuses')
                ->nullOnDelete();

            // Người duyệt hoa hồng
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('operation_managers')
                ->nullOnDelete();

            // Thời gian duyệt hoa hồng
            $table->timestamp('approved_at')->nullable();

            // Thời gian chi trả hoa hồng
            $table->timestamp('paid_at')->nullable();

            // Lý do hủy hoa hồng
            $table->text('cancelled_reason')->nullable();

            $table->timestamps();

            // Tối ưu lọc dữ liệu
            $table->index('order_code');
            $table->index('approved_at');
            $table->index('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_commissions');
    }
};
