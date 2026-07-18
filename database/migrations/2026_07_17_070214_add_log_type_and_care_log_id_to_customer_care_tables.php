<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bổ sung dữ liệu phục vụ chức năng chăm sóc khách hàng.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Phân biệt tư vấn thật và nhật ký hệ thống
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasColumn('customer_care_logs', 'log_type')) {
            Schema::table('customer_care_logs', function (Blueprint $table) {
                $table->string('log_type', 30)
                    ->default('consultation')
                    ->after('id')
                    ->index();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Đánh dấu các bản ghi hoàn thành lịch cũ là nhật ký hệ thống
        |--------------------------------------------------------------------------
        | Những bản ghi này không được tính là một lần tư vấn thực tế.
        */
        DB::table('customer_care_logs')
            ->where('content', 'like', 'Đã hoàn thành lịch chăm sóc:%')
            ->update([
                'log_type' => 'system',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Liên kết lịch nhắc với nội dung tư vấn
        |--------------------------------------------------------------------------
        | Khi sửa ngày liên hệ lại của một nội dung tư vấn, hệ thống biết chính
        | xác lịch nhắc nào cần được cập nhật.
        */
        if (!Schema::hasColumn('customer_care_reminders', 'care_log_id')) {
            Schema::table('customer_care_reminders', function (Blueprint $table) {
                $table->foreignId('care_log_id')
                    ->nullable()
                    ->after('customer_id')
                    ->constrained('customer_care_logs')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Hoàn tác migration.
     */
    public function down(): void
    {
        if (Schema::hasColumn('customer_care_reminders', 'care_log_id')) {
            Schema::table('customer_care_reminders', function (Blueprint $table) {
                $table->dropConstrainedForeignId('care_log_id');
            });
        }

        if (Schema::hasColumn('customer_care_logs', 'log_type')) {
            Schema::table('customer_care_logs', function (Blueprint $table) {
                $table->dropIndex('customer_care_logs_log_type_index');
                $table->dropColumn('log_type');
            });
        }
    }
};
