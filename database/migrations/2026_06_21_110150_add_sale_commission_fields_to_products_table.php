<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Thêm các cột phục vụ giảm giá và hoa hồng sản phẩm.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'is_discountable')) {
                $table->boolean('is_discountable')
                    ->default(true)
                    ->after('allow_sell_without_stock');
            }

            if (!Schema::hasColumn('products', 'is_commissionable')) {
                $table->boolean('is_commissionable')
                    ->default(true)
                    ->after('is_discountable');
            }

            if (!Schema::hasColumn('products', 'default_commission_rate')) {
                $table->decimal('default_commission_rate', 5, 2)
                    ->default(0)
                    ->after('is_commissionable');
            }
        });
    }

    /**
     * Rollback khi cần.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'default_commission_rate')) {
                $table->dropColumn('default_commission_rate');
            }

            if (Schema::hasColumn('products', 'is_commissionable')) {
                $table->dropColumn('is_commissionable');
            }

            if (Schema::hasColumn('products', 'is_discountable')) {
                $table->dropColumn('is_discountable');
            }
        });
    }
};
