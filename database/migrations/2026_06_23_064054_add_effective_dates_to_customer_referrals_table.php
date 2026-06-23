<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | THÊM THỜI GIAN HIỆU LỰC CHO BẢNG CUSTOMER_REFERRALS
    |--------------------------------------------------------------------------
    | Lý do thêm:
    | - Khi hoàn thành đơn hàng, hệ thống cần kiểm tra khách hàng này đang thuộc CTV nào.
    | - effective_from: ngày bắt đầu hiệu lực giới thiệu.
    | - effective_to: ngày kết thúc hiệu lực giới thiệu.
    |
    | Nếu để null:
    | - effective_from = null nghĩa là có hiệu lực từ trước đến nay.
    | - effective_to = null nghĩa là chưa hết hiệu lực.
    |--------------------------------------------------------------------------
    */
    public function up(): void
    {
        if (Schema::hasTable('customer_referrals')) {
            Schema::table('customer_referrals', function (Blueprint $table) {
                /*
                |--------------------------------------------------------------------------
                | Ngày bắt đầu hiệu lực
                |--------------------------------------------------------------------------
                */
                if (!Schema::hasColumn('customer_referrals', 'effective_from')) {
                    $table->date('effective_from')
                        ->nullable()
                        ->after('referred_customer_id');
                }

                /*
                |--------------------------------------------------------------------------
                | Ngày kết thúc hiệu lực
                |--------------------------------------------------------------------------
                */
                if (!Schema::hasColumn('customer_referrals', 'effective_to')) {
                    $table->date('effective_to')
                        ->nullable()
                        ->after('effective_from');
                }
            });
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ROLLBACK
    |--------------------------------------------------------------------------
    | Nếu cần rollback migration thì xóa lại 2 cột đã thêm.
    |--------------------------------------------------------------------------
    */
    public function down(): void
    {
        if (Schema::hasTable('customer_referrals')) {
            Schema::table('customer_referrals', function (Blueprint $table) {
                if (Schema::hasColumn('customer_referrals', 'effective_to')) {
                    $table->dropColumn('effective_to');
                }

                if (Schema::hasColumn('customer_referrals', 'effective_from')) {
                    $table->dropColumn('effective_from');
                }
            });
        }
    }
};
