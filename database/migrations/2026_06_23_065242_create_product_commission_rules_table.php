<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | TẠO BẢNG PRODUCT_COMMISSION_RULES
    |--------------------------------------------------------------------------
    | Bảng này dùng để lưu quy tắc hoa hồng theo từng sản phẩm.
    |
    | Ví dụ:
    | - Sản phẩm A hoa hồng 5%.
    | - Sản phẩm B hoa hồng cố định 100.000đ.
    |
    | Khi đơn hàng hoàn thành, hệ thống sẽ dựa vào bảng này để tính hoa hồng
    | cho CTV/người giới thiệu.
    |--------------------------------------------------------------------------
    */
    public function up(): void
    {
        if (!Schema::hasTable('product_commission_rules')) {
            Schema::create('product_commission_rules', function (Blueprint $table) {
                $table->id();

                /*
                |--------------------------------------------------------------------------
                | Sản phẩm áp dụng hoa hồng
                |--------------------------------------------------------------------------
                | Cho phép nullable để tránh lỗi nếu sản phẩm bị xóa.
                |--------------------------------------------------------------------------
                */
                $table->unsignedBigInteger('product_id')->nullable()->index();

                /*
                |--------------------------------------------------------------------------
                | Kiểu tính hoa hồng
                |--------------------------------------------------------------------------
                | percent: tính theo phần trăm.
                | fixed: tính theo số tiền cố định.
                |--------------------------------------------------------------------------
                */
                $table->string('commission_type', 30)
                    ->default('percent')
                    ->comment('percent hoặc fixed');

                /*
                |--------------------------------------------------------------------------
                | Hoa hồng theo phần trăm
                |--------------------------------------------------------------------------
                | Ví dụ: 5 nghĩa là 5%.
                |--------------------------------------------------------------------------
                */
                $table->decimal('commission_percent', 8, 2)
                    ->default(0);

                /*
                |--------------------------------------------------------------------------
                | Hoa hồng cố định
                |--------------------------------------------------------------------------
                | Ví dụ: 100000 nghĩa là 100.000đ.
                |--------------------------------------------------------------------------
                */
                $table->decimal('commission_amount', 15, 2)
                    ->default(0);

                /*
                |--------------------------------------------------------------------------
                | Trạng thái quy tắc
                |--------------------------------------------------------------------------
                | true: đang áp dụng.
                | false: đã tắt.
                |--------------------------------------------------------------------------
                */
                $table->boolean('is_active')
                    ->default(true)
                    ->index();

                /*
                |--------------------------------------------------------------------------
                | Thời gian hiệu lực
                |--------------------------------------------------------------------------
                | Nếu để null:
                | - effective_from = null: có hiệu lực từ trước đến nay.
                | - effective_to = null: chưa hết hiệu lực.
                |--------------------------------------------------------------------------
                */
                $table->date('effective_from')->nullable()->index();
                $table->date('effective_to')->nullable()->index();

                /*
                |--------------------------------------------------------------------------
                | Ghi chú
                |--------------------------------------------------------------------------
                */
                $table->text('note')->nullable();

                /*
                |--------------------------------------------------------------------------
                | Người tạo / người cập nhật
                |--------------------------------------------------------------------------
                */
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();

                $table->timestamps();

                /*
                |--------------------------------------------------------------------------
                | Khóa ngoại sản phẩm
                |--------------------------------------------------------------------------
                | Nếu xóa sản phẩm thì product_id trong bảng này chuyển thành null.
                |--------------------------------------------------------------------------
                */
                $table->foreign('product_id')
                    ->references('id')
                    ->on('products')
                    ->nullOnDelete();
            });
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ROLLBACK
    |--------------------------------------------------------------------------
    */
    public function down(): void
    {
        Schema::dropIfExists('product_commission_rules');
    }
};
