<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            if (!Schema::hasColumn('product_batches', 'supplier_name')) {
                $table->string('supplier_name', 255)->nullable()->after('expiry_date');
            }

            if (!Schema::hasColumn('product_batches', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            if (Schema::hasColumn('product_batches', 'supplier_name')) {
                $table->dropColumn('supplier_name');
            }

            if (Schema::hasColumn('product_batches', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
