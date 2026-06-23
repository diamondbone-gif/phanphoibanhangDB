<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tạo hoặc sửa bảng payments.
     * Bảng này lưu các lần thanh toán của đơn hàng.
     */
    public function up(): void
    {
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();

                $table->string('payment_code', 100)->unique();

                $table->unsignedBigInteger('customer_order_id');
                $table->unsignedBigInteger('payment_status_id')->nullable();

                $table->decimal('amount', 15, 2)->default(0);

                /**
                 * cash: tiền mặt
                 * bank_transfer: chuyển khoản
                 * card: thẻ
                 */
                $table->string('payment_method', 100)->nullable();

                $table->timestamp('payment_date')->nullable();

                $table->text('note')->nullable();

                $table->unsignedBigInteger('created_by')->nullable();

                $table->timestamps();

                $table->index('payment_code');
                $table->index('customer_order_id');
                $table->index('payment_status_id');
                $table->index('payment_method');
                $table->index('payment_date');

                $table->foreign('customer_order_id')
                    ->references('id')
                    ->on('customer_orders')
                    ->restrictOnDelete();

                if (Schema::hasTable('payment_statuses')) {
                    $table->foreign('payment_status_id')
                        ->references('id')
                        ->on('payment_statuses')
                        ->nullOnDelete();
                }

                if (Schema::hasTable('operation_managers')) {
                    $table->foreign('created_by')
                        ->references('id')
                        ->on('operation_managers')
                        ->nullOnDelete();
                }
            });

            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'payment_code')) {
                $table->string('payment_code', 100)->nullable()->after('id');
            }

            if (!Schema::hasColumn('payments', 'customer_order_id')) {
                $table->unsignedBigInteger('customer_order_id')->nullable()->after('payment_code');
            }

            if (!Schema::hasColumn('payments', 'payment_status_id')) {
                $table->unsignedBigInteger('payment_status_id')->nullable()->after('customer_order_id');
            }

            if (!Schema::hasColumn('payments', 'amount')) {
                $table->decimal('amount', 15, 2)->default(0)->after('payment_status_id');
            }

            if (!Schema::hasColumn('payments', 'payment_method')) {
                $table->string('payment_method', 100)->nullable()->after('amount');
            }

            if (!Schema::hasColumn('payments', 'payment_date')) {
                $table->timestamp('payment_date')->nullable()->after('payment_method');
            }

            if (!Schema::hasColumn('payments', 'note')) {
                $table->text('note')->nullable()->after('payment_date');
            }

            if (!Schema::hasColumn('payments', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('note');
            }

            if (!Schema::hasColumn('payments', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
