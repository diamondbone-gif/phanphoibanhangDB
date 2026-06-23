<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            // Mã khách hàng
            // Có thể tự sinh, hoặc lấy theo số điện thoại khi quản lý vận hành nhập
            $table->string('customer_code', 50)->unique();

            // Thông tin cá nhân khách hàng
            $table->string('full_name', 255);
            $table->string('phone', 20)->unique();
            $table->string('email', 255)->nullable()->unique();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('birth_date')->nullable();

            // Phân loại khách hàng
            $table->foreignId('customer_type_id')
                ->nullable()
                ->constrained('customer_types')
                ->nullOnDelete();

            $table->foreignId('customer_role_id')
                ->nullable()
                ->constrained('customer_roles')
                ->nullOnDelete();

            $table->foreignId('customer_status_id')
                ->nullable()
                ->constrained('customer_statuses')
                ->nullOnDelete();

            $table->foreignId('ctv_status_id')
                ->nullable()
                ->constrained('ctv_statuses')
                ->nullOnDelete();

            // Quản lý vận hành đã tạo khách hàng này
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('operation_managers')
                ->nullOnDelete();

            // Quản lý vận hành cập nhật khách hàng gần nhất
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('operation_managers')
                ->nullOnDelete();

            // Thông tin cộng tác viên nếu khách hàng được duyệt lên CTV
            $table->decimal('commission_rate', 5, 2)->nullable();

            $table->foreignId('ctv_approved_by')
                ->nullable()
                ->constrained('operation_managers')
                ->nullOnDelete();

            $table->timestamp('ctv_approved_at')->nullable();

            // Thông tin ngừng hoạt động / dừng hợp tác
            $table->text('stopped_reason')->nullable();
            $table->timestamp('stopped_at')->nullable();

            // Ghi chú nội bộ
            $table->text('note')->nullable();

            $table->timestamps();

            // Tối ưu lọc dữ liệu
            $table->index('full_name');
            $table->index('phone');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
