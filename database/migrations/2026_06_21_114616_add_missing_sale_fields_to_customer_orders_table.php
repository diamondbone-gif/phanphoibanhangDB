<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bổ sung các cột cần thiết cho chức năng bán hàng, hóa đơn, công nợ, kho và hoa hồng.
     */
    public function up(): void
    {
        Schema::table('customer_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_orders', 'subtotal_amount')) {
                $table->decimal('subtotal_amount', 15, 2)->default(0);
            }

            if (!Schema::hasColumn('customer_orders', 'product_discount_amount')) {
                $table->decimal('product_discount_amount', 15, 2)->default(0);
            }

            if (!Schema::hasColumn('customer_orders', 'combo_discount_amount')) {
                $table->decimal('combo_discount_amount', 15, 2)->default(0);
            }

            if (!Schema::hasColumn('customer_orders', 'order_discount_percent')) {
                $table->decimal('order_discount_percent', 5, 2)->default(0);
            }

            if (!Schema::hasColumn('customer_orders', 'order_discount_amount')) {
                $table->decimal('order_discount_amount', 15, 2)->default(0);
            }

            if (!Schema::hasColumn('customer_orders', 'final_amount')) {
                $table->decimal('final_amount', 15, 2)->default(0);
            }

            if (!Schema::hasColumn('customer_orders', 'paid_amount')) {
                $table->decimal('paid_amount', 15, 2)->default(0);
            }

            if (!Schema::hasColumn('customer_orders', 'debt_amount')) {
                $table->decimal('debt_amount', 15, 2)->default(0);
            }

            if (!Schema::hasColumn('customer_orders', 'stock_reverted')) {
                $table->boolean('stock_reverted')->default(false);
            }

            if (!Schema::hasColumn('customer_orders', 'commission_created')) {
                $table->boolean('commission_created')->default(false);
            }

            if (!Schema::hasColumn('customer_orders', 'order_date')) {
                $table->timestamp('order_date')->nullable();
            }

            if (!Schema::hasColumn('customer_orders', 'confirmed_by')) {
                $table->unsignedBigInteger('confirmed_by')->nullable();
            }

            if (!Schema::hasColumn('customer_orders', 'completed_at')) {
                $table->timestamp('completed_at')->nullable();
            }

            if (!Schema::hasColumn('customer_orders', 'cancelled_by')) {
                $table->unsignedBigInteger('cancelled_by')->nullable();
            }

            if (!Schema::hasColumn('customer_orders', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable();
            }

            if (!Schema::hasColumn('customer_orders', 'cancel_reason')) {
                $table->text('cancel_reason')->nullable();
            }

            if (!Schema::hasColumn('customer_orders', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable();
            }

            if (!Schema::hasColumn('customer_orders', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable();
            }

            if (!Schema::hasColumn('customer_orders', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Rollback khi cần.
     */
    public function down(): void
    {
        Schema::table('customer_orders', function (Blueprint $table) {
            $columns = [
                'subtotal_amount',
                'product_discount_amount',
                'combo_discount_amount',
                'order_discount_percent',
                'order_discount_amount',
                'final_amount',
                'paid_amount',
                'debt_amount',
                'stock_reverted',
                'commission_created',
                'order_date',
                'confirmed_by',
                'completed_at',
                'cancelled_by',
                'cancelled_at',
                'cancel_reason',
                'created_by',
                'updated_by',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('customer_orders', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('customer_orders', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
