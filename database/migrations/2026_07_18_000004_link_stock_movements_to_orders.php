<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('product_stock_movements', 'customer_order_id')) {
            Schema::table('product_stock_movements', function (Blueprint $table) {
                $table->unsignedBigInteger('customer_order_id')->nullable()->after('product_batch_id');
            });
        }

        if (!Schema::hasColumn('product_stock_movements', 'customer_order_item_id')) {
            Schema::table('product_stock_movements', function (Blueprint $table) {
                $table->unsignedBigInteger('customer_order_item_id')->nullable()->after('customer_order_id');
            });
        }

        Schema::table('product_stock_movements', function (Blueprint $table) {
            $table->foreign('customer_order_id', 'product_stock_movements_customer_order_id_foreign')
                ->references('id')->on('customer_orders')->nullOnDelete();
            $table->foreign('customer_order_item_id', 'product_stock_movements_customer_order_item_id_foreign')
                ->references('id')->on('customer_order_items')->nullOnDelete();
            $table->index(['customer_order_id', 'movement_type'], 'idx_movements_order_type');
        });
    }

    public function down(): void
    {
        Schema::table('product_stock_movements', function (Blueprint $table) {
            $table->dropIndex('idx_movements_order_type');
            $table->dropForeign('product_stock_movements_customer_order_item_id_foreign');
            $table->dropForeign('product_stock_movements_customer_order_id_foreign');
        });
    }
};
