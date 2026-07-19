<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_order_returns', function (Blueprint $table) {
            $table->string('resolution_type', 20)->default('refund')->after('refund_method');
            $table->decimal('cash_refund_amount', 15, 2)->default(0)->after('resolution_type');
            $table->decimal('exchange_credit_amount', 15, 2)->default(0)->after('cash_refund_amount');
            $table->string('resolution_status', 30)->default('completed')->after('exchange_credit_amount');
            $table->text('exchange_note')->nullable()->after('resolution_status');
            $table->index(['resolution_type', 'resolution_status'], 'idx_order_returns_resolution');
        });

        DB::table('customer_order_returns')->update([
            'resolution_type' => 'refund',
            'cash_refund_amount' => DB::raw('refund_amount'),
            'resolution_status' => 'completed',
        ]);
    }

    public function down(): void
    {
        Schema::table('customer_order_returns', function (Blueprint $table) {
            $table->dropIndex('idx_order_returns_resolution');
            $table->dropColumn(['resolution_type', 'cash_refund_amount', 'exchange_credit_amount', 'resolution_status', 'exchange_note']);
        });
    }
};
