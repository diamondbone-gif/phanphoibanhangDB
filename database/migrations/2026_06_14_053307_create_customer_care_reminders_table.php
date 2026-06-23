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
        Schema::create('customer_care_reminders', function (Blueprint $table) {
            $table->id();

            // Khách hàng cần chăm sóc
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            // Nhân viên được giao chăm sóc
            $table->foreignId('assigned_staff_id')
                ->nullable()
                ->constrained('operation_managers')
                ->nullOnDelete();

            // Ngày nhắc chăm sóc
            $table->date('reminder_date')->nullable();

            // Giờ nhắc chăm sóc
            $table->time('reminder_time')->nullable();

            // Nội dung cần nhắc
            $table->text('content')->nullable();

            // Mức độ ưu tiên
            $table->foreignId('care_priority_id')
                ->nullable()
                ->constrained('care_priorities')
                ->nullOnDelete();

            // Trạng thái nhắc/chăm sóc
            $table->foreignId('care_status_id')
                ->nullable()
                ->constrained('care_statuses')
                ->nullOnDelete();

            // Thời gian hoàn thành lịch nhắc
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            // Tối ưu tìm kiếm/lọc lịch nhắc
            $table->index('reminder_date');
            $table->index('reminder_time');
            $table->index('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_care_reminders');
    }
};
