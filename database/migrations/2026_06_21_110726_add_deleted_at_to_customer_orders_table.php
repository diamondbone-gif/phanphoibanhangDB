<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Thêm deleted_at cho bảng customer_orders.
     * Dùng để xóa mềm đơn hàng, vẫn giữ lịch sử hóa đơn/kho/hoa hồng.
     */
    public function up(): void
    {
        Schema::table('customer_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_orders', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    /**
     * Rollback khi cần.
     */
    public function down(): void
    {
        Schema::table('customer_orders', function (Blueprint $table) {
            if (Schema::hasColumn('customer_orders', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
