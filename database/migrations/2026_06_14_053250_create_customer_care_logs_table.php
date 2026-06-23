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
        Schema::create('customer_care_logs', function (Blueprint $table) {
            $table->id();

            // Khách hàng được chăm sóc
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            // Nhân viên phụ trách chăm sóc
            $table->foreignId('staff_id')
                ->nullable()
                ->constrained('operation_managers')
                ->nullOnDelete();

            // Kênh chăm sóc: gọi điện, Zalo, Facebook, trực tiếp...
            $table->foreignId('care_channel_id')
                ->nullable()
                ->constrained('care_channels')
                ->nullOnDelete();

            // Ngày chăm sóc
            $table->dateTime('care_date')->nullable();

            // Nội dung đã trao đổi với khách
            $table->text('content')->nullable();

            // Ghi chú nội bộ, chỉ nhân viên/quản lý xem
            $table->text('internal_note')->nullable();

            // Ngày giờ cần chăm sóc lại
            $table->dateTime('next_follow_up_at')->nullable();

            // Mức độ ưu tiên chăm sóc
            $table->foreignId('care_priority_id')
                ->nullable()
                ->constrained('care_priorities')
                ->nullOnDelete();

            // Trạng thái chăm sóc
            $table->foreignId('care_status_id')
                ->nullable()
                ->constrained('care_statuses')
                ->nullOnDelete();

            $table->timestamps();

            // Tối ưu tìm kiếm/lọc
            $table->index('care_date');
            $table->index('next_follow_up_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_care_logs');
    }
};
