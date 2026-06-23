<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bổ sung liên kết đơn hàng và dòng đơn hàng vào lịch sử kho.
     */
    public function up(): void
    {
        if (!Schema::hasTable('product_stock_movements')) {
            return;
        }

        Schema::table('product_stock_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('product_stock_movements', 'customer_order_id')) {
                $table->unsignedBigInteger('customer_order_id')
                    ->nullable()
                    ->after('product_batch_id');

                $table->index('customer_order_id');
            }

            if (!Schema::hasColumn('product_stock_movements', 'customer_order_item_id')) {
                $table->unsignedBigInteger('customer_order_item_id')
                    ->nullable()
                    ->after('customer_order_id');

                $table->index('customer_order_item_id');
            }
        });
    }

    /**
     * Rollback migration.
     */
    public function down(): void
    {
        if (!Schema::hasTable('product_stock_movements')) {
            return;
        }

        Schema::table('product_stock_movements', function (Blueprint $table) {
            if (Schema::hasColumn('product_stock_movements', 'customer_order_item_id')) {
                $table->dropIndex(['customer_order_item_id']);
                $table->dropColumn('customer_order_item_id');
            }

            if (Schema::hasColumn('product_stock_movements', 'customer_order_id')) {
                $table->dropIndex(['customer_order_id']);
                $table->dropColumn('customer_order_id');
            }
        });
    }
};
