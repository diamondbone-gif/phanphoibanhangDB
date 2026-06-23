<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | CẬP NHẬT BẢNG CUSTOMER_COMMISSIONS
    |--------------------------------------------------------------------------
    | Database của bạn đã có bảng customer_commissions.
    |
    | Migration này chỉ bổ sung các cột còn thiếu để tính hoa hồng CTV
    | theo tổng tiền cuối của đơn hàng.
    |
    | Không xóa dữ liệu cũ.
    |--------------------------------------------------------------------------
    */
    public function up(): void
    {
        if (!Schema::hasTable('customer_commissions')) {
            Schema::create('customer_commissions', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }

        Schema::table('customer_commissions', function (Blueprint $table) {
            /*
            |--------------------------------------------------------------------------
            | Mã hoa hồng
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('customer_commissions', 'commission_code')) {
                $table->string('commission_code', 50)->nullable()->unique();
            }

            /*
            |--------------------------------------------------------------------------
            | Liên kết đơn hàng
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('customer_commissions', 'customer_order_id')) {
                $table->unsignedBigInteger('customer_order_id')->nullable()->index();
            }

            if (!Schema::hasColumn('customer_commissions', 'order_code')) {
                $table->string('order_code', 100)->nullable()->index();
            }

            /*
            |--------------------------------------------------------------------------
            | CTV và khách được giới thiệu
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('customer_commissions', 'ctv_customer_id')) {
                $table->unsignedBigInteger('ctv_customer_id')->nullable()->index();
            }

            if (!Schema::hasColumn('customer_commissions', 'referred_customer_id')) {
                $table->unsignedBigInteger('referred_customer_id')->nullable()->index();
            }

            /*
            |--------------------------------------------------------------------------
            | Số tiền đơn hàng dùng để tính hoa hồng
            |--------------------------------------------------------------------------
            | Lấy theo customer_orders.final_amount.
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('customer_commissions', 'order_final_amount')) {
                $table->decimal('order_final_amount', 15, 2)->default(0);
            }

            /*
            |--------------------------------------------------------------------------
            | Tỷ lệ và số tiền hoa hồng
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('customer_commissions', 'commission_rate_percent')) {
                $table->decimal('commission_rate_percent', 8, 2)->default(0);
            }

            if (!Schema::hasColumn('customer_commissions', 'commission_amount')) {
                $table->decimal('commission_amount', 15, 2)->default(0);
            }

            /*
            |--------------------------------------------------------------------------
            | Số tiền đã thanh toán cho CTV
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('customer_commissions', 'paid_amount')) {
                $table->decimal('paid_amount', 15, 2)->default(0);
            }

            /*
            |--------------------------------------------------------------------------
            | Trạng thái hoa hồng
            |--------------------------------------------------------------------------
            | unpaid: chưa thanh toán
            | paid: đã thanh toán
            | cancelled: đã hủy
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('customer_commissions', 'status')) {
                $table->string('status', 30)->default('unpaid')->index();
            }

            /*
            |--------------------------------------------------------------------------
            | Ngày phát sinh hoa hồng
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('customer_commissions', 'commission_date')) {
                $table->timestamp('commission_date')->nullable()->index();
            }

            /*
            |--------------------------------------------------------------------------
            | Thông tin thanh toán
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('customer_commissions', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->index();
            }

            if (!Schema::hasColumn('customer_commissions', 'paid_by')) {
                $table->unsignedBigInteger('paid_by')->nullable();
            }

            /*
            |--------------------------------------------------------------------------
            | Thông tin hủy hoa hồng
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('customer_commissions', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable();
            }

            if (!Schema::hasColumn('customer_commissions', 'cancelled_by')) {
                $table->unsignedBigInteger('cancelled_by')->nullable();
            }

            if (!Schema::hasColumn('customer_commissions', 'cancel_reason')) {
                $table->text('cancel_reason')->nullable();
            }

            /*
            |--------------------------------------------------------------------------
            | Ghi chú và người tạo
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('customer_commissions', 'note')) {
                $table->text('note')->nullable();
            }

            if (!Schema::hasColumn('customer_commissions', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable();
            }

            /*
            |--------------------------------------------------------------------------
            | Xóa mềm
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('customer_commissions', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        /*
        |--------------------------------------------------------------------------
        | BỔ SUNG CỘT ĐÁNH DẤU CHO CUSTOMER_ORDERS
        |--------------------------------------------------------------------------
        | Tránh tạo hoa hồng trùng cho cùng 1 đơn hàng.
        |--------------------------------------------------------------------------
        */
        if (Schema::hasTable('customer_orders')) {
            Schema::table('customer_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('customer_orders', 'commission_created')) {
                    $table->boolean('commission_created')->default(false)->index();
                }
            });
        }
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Không drop cột để tránh mất dữ liệu hoa hồng.
        | Nếu muốn rollback thật sự thì xử lý thủ công trong phpMyAdmin.
        |--------------------------------------------------------------------------
        */
    }
};
