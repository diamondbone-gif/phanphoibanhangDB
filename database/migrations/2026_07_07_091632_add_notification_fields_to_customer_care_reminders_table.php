<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bổ sung các cột phục vụ popup nhắc chăm sóc khách hàng.
     */
    public function up(): void
    {
        Schema::table('customer_care_reminders', function (Blueprint $table) {
            /*
            |--------------------------------------------------------------------------
            | Thời điểm quản lý xác nhận đã xem thông báo
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn(
                'customer_care_reminders',
                'notified_at'
            )) {
                $table->timestamp('notified_at')
                    ->nullable()
                    ->after('completed_at');
            }

            /*
            |--------------------------------------------------------------------------
            | Thời gian hoãn để hệ thống nhắc lại
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn(
                'customer_care_reminders',
                'snoozed_until'
            )) {
                $table->timestamp('snoozed_until')
                    ->nullable()
                    ->after('notified_at');
            }
        });
    }

    /**
     * Hoàn tác migration.
     */
    public function down(): void
    {
        Schema::table('customer_care_reminders', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn(
                'customer_care_reminders',
                'notified_at'
            )) {
                $columns[] = 'notified_at';
            }

            if (Schema::hasColumn(
                'customer_care_reminders',
                'snoozed_until'
            )) {
                $columns[] = 'snoozed_until';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
