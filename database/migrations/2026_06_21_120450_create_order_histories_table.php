<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng lưu lịch sử thao tác đơn hàng:
     * tạo đơn, sửa đơn, hoàn thành, hủy đơn, xóa mềm...
     */
    public function up(): void
    {
        if (!Schema::hasTable('order_histories')) {
            Schema::create('order_histories', function (Blueprint $table) {
                $table->id();

                $table->foreignId('customer_order_id')
                    ->constrained('customer_orders')
                    ->cascadeOnDelete();

                $table->string('action', 100);

                $table->unsignedBigInteger('old_status_id')->nullable();
                $table->unsignedBigInteger('new_status_id')->nullable();

                $table->json('old_data')->nullable();
                $table->json('new_data')->nullable();

                $table->text('note')->nullable();

                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('operation_managers')
                    ->nullOnDelete();

                $table->timestamps();

                $table->index('customer_order_id');
                $table->index('action');
                $table->index('old_status_id');
                $table->index('new_status_id');
                $table->index('created_by');
                $table->index('created_at');
            });

            return;
        }

        Schema::table('order_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('order_histories', 'customer_order_id')) {
                $table->unsignedBigInteger('customer_order_id')->nullable()->after('id');
            }

            if (!Schema::hasColumn('order_histories', 'action')) {
                $table->string('action', 100)->nullable()->after('customer_order_id');
            }

            if (!Schema::hasColumn('order_histories', 'old_status_id')) {
                $table->unsignedBigInteger('old_status_id')->nullable()->after('action');
            }

            if (!Schema::hasColumn('order_histories', 'new_status_id')) {
                $table->unsignedBigInteger('new_status_id')->nullable()->after('old_status_id');
            }

            if (!Schema::hasColumn('order_histories', 'old_data')) {
                $table->json('old_data')->nullable()->after('new_status_id');
            }

            if (!Schema::hasColumn('order_histories', 'new_data')) {
                $table->json('new_data')->nullable()->after('old_data');
            }

            if (!Schema::hasColumn('order_histories', 'note')) {
                $table->text('note')->nullable()->after('new_data');
            }

            if (!Schema::hasColumn('order_histories', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('note');
            }

            if (!Schema::hasColumn('order_histories', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_histories');
    }
};
