<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'unit_name')) {
                $table->string('unit_name', 100)->nullable()->after('product_category_id');
            }

            if (!Schema::hasColumn('products', 'main_image')) {
                $table->string('main_image', 500)->nullable()->after('price');
            }

            if (!Schema::hasColumn('products', 'short_description')) {
                $table->string('short_description', 500)->nullable()->after('main_image');
            }

            if (!Schema::hasColumn('products', 'description')) {
                $table->longText('description')->nullable()->after('short_description');
            }

            if (!Schema::hasColumn('products', 'total_quantity')) {
                $table->unsignedInteger('total_quantity')->default(0)->after('description');
            }

            if (!Schema::hasColumn('products', 'track_batch')) {
                $table->boolean('track_batch')->default(true)->after('total_quantity');
            }

            if (!Schema::hasColumn('products', 'track_expiry')) {
                $table->boolean('track_expiry')->default(true)->after('track_batch');
            }

            if (!Schema::hasColumn('products', 'min_quantity_alert')) {
                $table->unsignedInteger('min_quantity_alert')->default(0)->after('track_expiry');
            }

            if (!Schema::hasColumn('products', 'is_commissionable')) {
                $table->boolean('is_commissionable')->default(true)->after('min_quantity_alert');
            }

            if (!Schema::hasColumn('products', 'default_commission_rate')) {
                $table->decimal('default_commission_rate', 5, 2)->default(0)->after('is_commissionable');
            }

            if (!Schema::hasColumn('products', 'allow_sell_without_stock')) {
                $table->boolean('allow_sell_without_stock')->default(false)->after('default_commission_rate');
            }

            if (!Schema::hasColumn('products', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('allow_sell_without_stock');
            }

            if (!Schema::hasColumn('products', 'created_by')) {
                $table->foreignId('created_by')
                    ->nullable()
                    ->after('sort_order')
                    ->constrained('operation_managers')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('products', 'updated_by')) {
                $table->foreignId('updated_by')
                    ->nullable()
                    ->after('created_by')
                    ->constrained('operation_managers')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'updated_by')) {
                $table->dropConstrainedForeignId('updated_by');
            }

            if (Schema::hasColumn('products', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }

            $columns = [
                'unit_name',
                'main_image',
                'short_description',
                'description',
                'total_quantity',
                'track_batch',
                'track_expiry',
                'min_quantity_alert',
                'is_commissionable',
                'default_commission_rate',
                'allow_sell_without_stock',
                'sort_order',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
