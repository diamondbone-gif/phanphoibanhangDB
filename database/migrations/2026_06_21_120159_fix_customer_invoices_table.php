<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sửa bảng customer_invoices cho đúng logic hóa đơn bán hàng.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Nếu bảng chưa tồn tại thì tạo mới đầy đủ
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('customer_invoices')) {
            Schema::create('customer_invoices', function (Blueprint $table) {
                $table->id();

                $table->string('invoice_code', 100)->unique();

                $table->unsignedBigInteger('customer_order_id');
                $table->unsignedBigInteger('customer_id');

                $table->date('invoice_date')->nullable();

                $table->decimal('total_amount', 15, 2)->default(0);
                $table->decimal('tax_amount', 15, 2)->default(0);
                $table->decimal('final_amount', 15, 2)->default(0);

                $table->string('status', 50)->default('issued');
                $table->text('note')->nullable();

                $table->unsignedBigInteger('created_by')->nullable();

                $table->timestamps();

                $table->index('customer_order_id');
                $table->index('customer_id');
                $table->index('invoice_date');
                $table->index('status');
            });

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Nếu bảng đã tồn tại thì bổ sung cột còn thiếu
        |--------------------------------------------------------------------------
        */
        Schema::table('customer_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_invoices', 'invoice_code')) {
                $table->string('invoice_code', 100)->nullable()->after('id');
            }

            if (!Schema::hasColumn('customer_invoices', 'customer_order_id')) {
                $table->unsignedBigInteger('customer_order_id')->nullable()->after('invoice_code');
            }

            if (!Schema::hasColumn('customer_invoices', 'customer_id')) {
                $table->unsignedBigInteger('customer_id')->nullable()->after('customer_order_id');
            }

            if (!Schema::hasColumn('customer_invoices', 'invoice_date')) {
                $table->date('invoice_date')->nullable()->after('customer_id');
            }

            if (!Schema::hasColumn('customer_invoices', 'total_amount')) {
                $table->decimal('total_amount', 15, 2)->default(0)->after('invoice_date');
            }

            if (!Schema::hasColumn('customer_invoices', 'tax_amount')) {
                $table->decimal('tax_amount', 15, 2)->default(0)->after('total_amount');
            }

            if (!Schema::hasColumn('customer_invoices', 'final_amount')) {
                $table->decimal('final_amount', 15, 2)->default(0)->after('tax_amount');
            }

            if (!Schema::hasColumn('customer_invoices', 'status')) {
                $table->string('status', 50)->default('issued')->after('final_amount');
            }

            if (!Schema::hasColumn('customer_invoices', 'note')) {
                $table->text('note')->nullable()->after('status');
            }

            if (!Schema::hasColumn('customer_invoices', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('note');
            }

            if (!Schema::hasColumn('customer_invoices', 'created_at')) {
                $table->timestamps();
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Gán mã hóa đơn cho các dòng cũ nếu invoice_code đang null
        |--------------------------------------------------------------------------
        */
        if (Schema::hasColumn('customer_invoices', 'invoice_code')) {
            $invoices = DB::table('customer_invoices')
                ->whereNull('invoice_code')
                ->orWhere('invoice_code', '')
                ->get();

            foreach ($invoices as $invoice) {
                DB::table('customer_invoices')
                    ->where('id', $invoice->id)
                    ->update([
                        'invoice_code' => 'HD' . now()->format('ymdHis') . $invoice->id,
                    ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Thêm unique index cho invoice_code nếu chưa có
        |--------------------------------------------------------------------------
        | Nếu đoạn này báo trùng index thì comment 5 dòng bên dưới lại.
        |--------------------------------------------------------------------------
        */
        try {
            Schema::table('customer_invoices', function (Blueprint $table) {
                $table->unique('invoice_code', 'customer_invoices_invoice_code_unique');
            });
        } catch (Throwable $e) {
            // Bỏ qua nếu index đã tồn tại.
        }
    }

    /**
     * Rollback an toàn.
     */
    public function down(): void
    {
        // Không drop bảng để tránh mất hóa đơn đã tạo.
        // Nếu thật sự muốn rollback, chỉ nên rollback trên database test.
    }
};
