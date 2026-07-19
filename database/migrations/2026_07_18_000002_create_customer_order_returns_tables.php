<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_order_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_code', 50)->unique();
            $table->foreignId('customer_order_id')->constrained('customer_orders')->restrictOnDelete();
            $table->decimal('refund_amount', 15, 2)->default(0);
            $table->string('refund_method', 50)->nullable();
            $table->string('status', 30)->default('completed');
            $table->text('reason');
            $table->text('note')->nullable();
            $table->timestamp('returned_at');
            $table->foreignId('created_by')->nullable()->constrained('operation_managers')->nullOnDelete();
            $table->timestamps();
            $table->index(['customer_order_id', 'status']);
            $table->index('returned_at');
        });

        Schema::create('customer_order_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_order_return_id')->constrained('customer_order_returns')->cascadeOnDelete();
            $table->foreignId('customer_order_item_id')->constrained('customer_order_items')->restrictOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('product_batch_id')->nullable()->constrained('product_batches')->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_refund_amount', 15, 2)->default(0);
            $table->decimal('refund_amount', 15, 2)->default(0);
            $table->timestamps();
            $table->index('customer_order_item_id');
        });

        Schema::table('customer_orders', function (Blueprint $table) {
            $table->decimal('returned_amount', 15, 2)->default(0)->after('final_amount');
            $table->decimal('net_amount', 15, 2)->default(0)->after('returned_amount');
            $table->string('return_status', 30)->default('none')->after('net_amount');
            $table->index('return_status');
        });

        Schema::table('customer_commissions', function (Blueprint $table) {
            $table->decimal('clawback_amount', 15, 2)->default(0)->after('paid_amount');
        });

        DB::table('customer_orders')->update([
            'net_amount' => DB::raw('final_amount'),
        ]);
    }

    public function down(): void
    {
        Schema::table('customer_commissions', function (Blueprint $table) {
            $table->dropColumn('clawback_amount');
        });
        Schema::table('customer_orders', function (Blueprint $table) {
            $table->dropIndex(['return_status']);
            $table->dropColumn(['returned_amount', 'net_amount', 'return_status']);
        });
        Schema::dropIfExists('customer_order_return_items');
        Schema::dropIfExists('customer_order_returns');
    }
};
