<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bổ sung các cột cần cho chi tiết đơn hàng.
     */
    public function up(): void
    {
        Schema::table('customer_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_order_items', 'product_batch_id')) {
                $table->unsignedBigInteger('product_batch_id')->nullable();
            }

            if (!Schema::hasColumn('customer_order_items', 'product_combo_id')) {
                $table->unsignedBigInteger('product_combo_id')->nullable();
            }

            if (!Schema::hasColumn('customer_order_items', 'product_event_id')) {
                $table->unsignedBigInteger('product_event_id')->nullable();
            }

            if (!Schema::hasColumn('customer_order_items', 'product_code')) {
                $table->string('product_code', 100)->nullable();
            }

            if (!Schema::hasColumn('customer_order_items', 'product_name')) {
                $table->string('product_name', 255)->nullable();
            }

            if (!Schema::hasColumn('customer_order_items', 'quantity')) {
                $table->unsignedInteger('quantity')->default(1);
            }

            if (!Schema::hasColumn('customer_order_items', 'unit_price')) {
                $table->decimal('unit_price', 15, 2)->default(0);
            }

            if (!Schema::hasColumn('customer_order_items', 'original_total')) {
                $table->decimal('original_total', 15, 2)->default(0);
            }

            if (!Schema::hasColumn('customer_order_items', 'discount_type')) {
                $table->string('discount_type', 50)->default('none');
            }

            if (!Schema::hasColumn('customer_order_items', 'discount_percent')) {
                $table->decimal('discount_percent', 5, 2)->default(0);
            }

            if (!Schema::hasColumn('customer_order_items', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 2)->default(0);
            }

            if (!Schema::hasColumn('customer_order_items', 'final_total')) {
                $table->decimal('final_total', 15, 2)->default(0);
            }

            if (!Schema::hasColumn('customer_order_items', 'note')) {
                $table->text('note')->nullable();
            }
        });
    }

    /**
     * Rollback khi cần.
     */
    public function down(): void
    {
        Schema::table('customer_order_items', function (Blueprint $table) {
            $columns = [
                'product_batch_id',
                'product_combo_id',
                'product_event_id',
                'product_code',
                'product_name',
                'quantity',
                'unit_price',
                'original_total',
                'discount_type',
                'discount_percent',
                'discount_amount',
                'final_total',
                'note',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('customer_order_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
