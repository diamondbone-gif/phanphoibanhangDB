<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // For batch-tracked products, batches are the source of truth.
        DB::statement(<<<'SQL'
            UPDATE products AS product
            INNER JOIN (
                SELECT product_id, SUM(current_quantity) AS batch_quantity
                FROM product_batches
                GROUP BY product_id
            ) AS stock ON stock.product_id = product.id
            SET product.total_quantity = stock.batch_quantity
            WHERE product.track_batch = 1
              AND product.total_quantity <> stock.batch_quantity
        SQL);

        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->foreign('customer_order_id', 'fk_invoices_order')
                ->references('id')->on('customer_orders')->restrictOnDelete();
            $table->foreign('customer_id', 'fk_invoices_customer')
                ->references('id')->on('customers')->restrictOnDelete();
            $table->foreign('created_by', 'fk_invoices_creator')
                ->references('id')->on('operation_managers')->nullOnDelete();
            $table->index(['customer_order_id'], 'idx_invoices_order');
            $table->index(['customer_id', 'invoice_date'], 'idx_invoices_customer_date');
            $table->index(['status', 'invoice_date'], 'idx_invoices_status_date');
        });

        Schema::table('customer_commissions', function (Blueprint $table) {
            $table->foreign('ctv_customer_id', 'fk_commissions_ctv')
                ->references('id')->on('customers')->nullOnDelete();
            $table->unique('customer_order_id', 'uq_commissions_order');
            $table->index(['ctv_customer_id', 'status', 'commission_date'], 'idx_commissions_ctv_status_date');
        });

        Schema::table('customer_orders', function (Blueprint $table) {
            $table->index(['order_status_id', 'order_date'], 'idx_orders_status_date');
            $table->index(['payment_status_id', 'order_date'], 'idx_orders_payment_date');
            $table->index(['customer_id', 'order_date'], 'idx_orders_customer_date');
        });

        Schema::table('customer_care_reminders', function (Blueprint $table) {
            $table->index(
                ['care_status_id', 'reminder_date', 'reminder_time', 'completed_at'],
                'idx_care_due_lookup'
            );
        });

        Schema::table('product_batches', function (Blueprint $table) {
            $table->index(['status', 'expiry_date', 'current_quantity'], 'idx_batches_status_expiry_qty');
        });

        Schema::table('product_stock_movements', function (Blueprint $table) {
            $table->index(['reference_type', 'reference_id'], 'idx_movements_reference');
            $table->index(['product_id', 'movement_date'], 'idx_movements_product_date');
        });
    }

    public function down(): void
    {
        Schema::table('product_stock_movements', function (Blueprint $table) {
            $table->dropIndex('idx_movements_reference');
            $table->dropIndex('idx_movements_product_date');
        });
        Schema::table('product_batches', function (Blueprint $table) {
            $table->dropIndex('idx_batches_status_expiry_qty');
        });
        Schema::table('customer_care_reminders', function (Blueprint $table) {
            $table->dropIndex('idx_care_due_lookup');
        });
        Schema::table('customer_orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_status_date');
            $table->dropIndex('idx_orders_payment_date');
            $table->dropIndex('idx_orders_customer_date');
        });
        Schema::table('customer_commissions', function (Blueprint $table) {
            $table->dropIndex('idx_commissions_ctv_status_date');
            $table->dropUnique('uq_commissions_order');
            $table->dropForeign('fk_commissions_ctv');
        });
        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->dropIndex('idx_invoices_order');
            $table->dropIndex('idx_invoices_customer_date');
            $table->dropIndex('idx_invoices_status_date');
            $table->dropForeign('fk_invoices_order');
            $table->dropForeign('fk_invoices_customer');
            $table->dropForeign('fk_invoices_creator');
        });
    }
};
