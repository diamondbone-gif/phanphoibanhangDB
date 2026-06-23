<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Thêm cột cho phép sản phẩm được giảm giá hay không.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'is_discountable')) {
                $table->boolean('is_discountable')
                    ->default(true)
                    ->after('allow_sell_without_stock');
            }
        });
    }

    /**
     * Rollback khi cần.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'is_discountable')) {
                $table->dropColumn('is_discountable');
            }
        });
    }
};
