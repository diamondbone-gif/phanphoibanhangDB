<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customer_commission_payouts')) {
            Schema::table('customer_commission_payouts', function (Blueprint $table) {
                if (!Schema::hasColumn('customer_commission_payouts', 'payment_method')) {
                    $table->string('payment_method', 100)->nullable()->after('paid_by');
                }

                if (!Schema::hasColumn('customer_commission_payouts', 'payout_type')) {
                    $table->string('payout_type', 50)->nullable()->after('payment_method');
                }
            });
        }

        if (Schema::hasTable('payout_statuses')) {
            $now = now();

            $statuses = [
                [
                    'code' => 'pending',
                    'name' => 'Chờ chi',
                    'description' => null,
                    'sort_order' => 1,
                    'is_active' => 1,
                ],
                [
                    'code' => 'paid',
                    'name' => 'Đã chi',
                    'description' => null,
                    'sort_order' => 2,
                    'is_active' => 1,
                ],
                [
                    'code' => 'cancelled',
                    'name' => 'Đã hủy',
                    'description' => null,
                    'sort_order' => 3,
                    'is_active' => 1,
                ],
            ];

            foreach ($statuses as $status) {
                DB::table('payout_statuses')->updateOrInsert(
                    ['code' => $status['code']],
                    array_merge($status, [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])
                );
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('customer_commission_payouts')) {
            Schema::table('customer_commission_payouts', function (Blueprint $table) {
                if (Schema::hasColumn('customer_commission_payouts', 'payout_type')) {
                    $table->dropColumn('payout_type');
                }

                if (Schema::hasColumn('customer_commission_payouts', 'payment_method')) {
                    $table->dropColumn('payment_method');
                }
            });
        }
    }
};
