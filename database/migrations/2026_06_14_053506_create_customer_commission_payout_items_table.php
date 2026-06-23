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
        Schema::create('customer_commission_payout_items', function (Blueprint $table) {
            $table->id();

            // Liên kết với phiếu chi hoa hồng
            $table->foreignId('payout_id')
                ->constrained('customer_commission_payouts')
                ->cascadeOnDelete();

            // Liên kết với khoản hoa hồng cụ thể
            $table->foreignId('customer_commission_id')
                ->constrained('customer_commissions')
                ->cascadeOnDelete();

            // Số tiền được chi cho khoản hoa hồng này
            $table->decimal('amount', 15, 2)->default(0);

            $table->timestamps();

            // Tránh đưa cùng một khoản hoa hồng vào 1 phiếu chi nhiều lần
            $table->unique([
                'payout_id',
                'customer_commission_id'
            ], 'unique_payout_commission_item');

            // Nếu mỗi khoản hoa hồng chỉ được chi 1 lần duy nhất, nên bật dòng này
            // $table->unique('customer_commission_id', 'unique_commission_paid_once');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_commission_payout_items');
    }
};
