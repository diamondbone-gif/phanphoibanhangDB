<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | BỔ SUNG CỘT THIẾU CHO PRODUCT_COMMISSION_RULES
    |--------------------------------------------------------------------------
    | Lý do:
    | - CommissionService hiện tại đang query các cột:
    |   start_date, end_date, product_category_id.
    |
    | - Nhưng bảng product_commission_rules hiện tại của bạn chưa có start_date,
    |   nên khi bấm "Hoàn thành đơn" Laravel báo:
    |   Unknown column 'start_date' in 'where clause'
    |
    | File này sẽ bổ sung các cột còn thiếu mà không xóa dữ liệu cũ.
    |--------------------------------------------------------------------------
    */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Nếu bảng chưa tồn tại thì dừng lại
        |--------------------------------------------------------------------------
        | Tránh lỗi khi migrate trên database chưa có bảng product_commission_rules.
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('product_commission_rules')) {
            return;
        }

        Schema::table('product_commission_rules', function (Blueprint $table) {
            /*
            |--------------------------------------------------------------------------
            | product_category_id
            |--------------------------------------------------------------------------
            | Code hiện tại có đoạn:
            | product_id = 6 OR product_category_id IS NULL
            |
            | Vì vậy bảng cần có cột product_category_id.
            | Nếu chưa phân loại theo danh mục thì để NULL.
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('product_commission_rules', 'product_category_id')) {
                $table->unsignedBigInteger('product_category_id')
                    ->nullable()
                    ->after('product_id')
                    ->index();
            }

            /*
            |--------------------------------------------------------------------------
            | start_date
            |--------------------------------------------------------------------------
            | Ngày bắt đầu áp dụng quy tắc hoa hồng.
            | Nếu NULL nghĩa là áp dụng từ trước đến nay.
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('product_commission_rules', 'start_date')) {
                $table->date('start_date')
                    ->nullable()
                    ->after('is_active')
                    ->index();
            }

            /*
            |--------------------------------------------------------------------------
            | end_date
            |--------------------------------------------------------------------------
            | Ngày kết thúc áp dụng quy tắc hoa hồng.
            | Nếu NULL nghĩa là chưa hết hiệu lực.
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('product_commission_rules', 'end_date')) {
                $table->date('end_date')
                    ->nullable()
                    ->after('start_date')
                    ->index();
            }
        });

        /*
        |--------------------------------------------------------------------------
        | ĐỒNG BỘ DỮ LIỆU CŨ NẾU CÓ
        |--------------------------------------------------------------------------
        | Trước đó nếu bạn đã tạo effective_from/effective_to,
        | thì copy qua start_date/end_date để CommissionService dùng được.
        |--------------------------------------------------------------------------
        */
        if (
            Schema::hasColumn('product_commission_rules', 'effective_from') &&
            Schema::hasColumn('product_commission_rules', 'start_date')
        ) {
            DB::table('product_commission_rules')
                ->whereNull('start_date')
                ->update([
                    'start_date' => DB::raw('effective_from'),
                ]);
        }

        if (
            Schema::hasColumn('product_commission_rules', 'effective_to') &&
            Schema::hasColumn('product_commission_rules', 'end_date')
        ) {
            DB::table('product_commission_rules')
                ->whereNull('end_date')
                ->update([
                    'end_date' => DB::raw('effective_to'),
                ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ROLLBACK
    |--------------------------------------------------------------------------
    | Nếu rollback migration thì xóa lại các cột đã thêm.
    |--------------------------------------------------------------------------
    */
    public function down(): void
    {
        if (!Schema::hasTable('product_commission_rules')) {
            return;
        }

        Schema::table('product_commission_rules', function (Blueprint $table) {
            if (Schema::hasColumn('product_commission_rules', 'end_date')) {
                $table->dropColumn('end_date');
            }

            if (Schema::hasColumn('product_commission_rules', 'start_date')) {
                $table->dropColumn('start_date');
            }

            if (Schema::hasColumn('product_commission_rules', 'product_category_id')) {
                $table->dropColumn('product_category_id');
            }
        });
    }
};
