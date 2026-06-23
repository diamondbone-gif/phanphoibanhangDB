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
        Schema::create('customer_commission_payouts', function (Blueprint $table) {
            $table->id();

            // Mã phiếu chi hoa hồng
            $table->string('payout_code', 50)->unique();

            // Người nhận hoa hồng / CTV
            $table->foreignId('referrer_customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();

            // Tổng tiền chi hoa hồng
            $table->decimal('total_amount', 15, 2)->default(0);

            // Trạng thái phiếu chi
            $table->foreignId('payout_status_id')
                ->nullable()
                ->constrained('payout_statuses')
                ->nullOnDelete();

            // Thời gian đã chi
            $table->timestamp('paid_at')->nullable();

            // Người thực hiện chi
            $table->foreignId('paid_by')
                ->nullable()
                ->constrained('operation_managers')
                ->nullOnDelete();

            // Ghi chú
            $table->text('note')->nullable();

            $table->timestamps();

            // Tối ưu tìm kiếm/lọc dữ liệu
            $table->index('payout_code');
            $table->index('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_commission_payouts');
    }
};
