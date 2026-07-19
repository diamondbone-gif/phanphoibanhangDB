<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('customer_order_items')
            && !Schema::hasColumn('customer_order_items', 'stock_quantity')
        ) {
            Schema::table('customer_order_items', function (Blueprint $table) {
                $table->unsignedInteger('stock_quantity')
                    ->nullable()
                    ->after('quantity')
                    ->comment('Actual inventory quantity deducted for this line');
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('customer_order_items')
            && Schema::hasColumn('customer_order_items', 'stock_quantity')
        ) {
            Schema::table('customer_order_items', function (Blueprint $table) {
                $table->dropColumn('stock_quantity');
            });
        }
    }
};
