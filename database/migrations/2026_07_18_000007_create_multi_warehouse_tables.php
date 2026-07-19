<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('warehouse_code', 50)->unique();
            $table->string('warehouse_name');
            $table->string('address')->nullable();
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('average_cost', 15, 2)->default(0)->after('price');
        });
        Schema::table('product_batches', function (Blueprint $table) {
            $table->decimal('unit_cost', 15, 2)->default(0)->after('current_quantity');
        });

        Schema::create('warehouse_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_batch_id')->nullable()->constrained('product_batches')->cascadeOnDelete();
            $table->unsignedBigInteger('batch_key')->default(0);
            $table->unsignedInteger('on_hand_quantity')->default(0);
            $table->unsignedInteger('reserved_quantity')->default(0);
            $table->timestamps();
            $table->unique(['warehouse_id', 'product_id', 'batch_key'], 'uq_warehouse_product_batch');
            $table->index(['warehouse_id', 'on_hand_quantity', 'reserved_quantity'], 'idx_warehouse_availability');
        });

        Schema::create('stock_documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_code')->unique();
            $table->string('document_type', 30)->index();
            $table->string('status', 30)->default('draft')->index();
            $table->foreignId('source_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('destination_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamp('document_date');
            $table->text('reason')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('operation_managers')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('operation_managers')->nullOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('operation_managers')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
            $table->index(['reference_type', 'reference_id'], 'idx_stock_document_reference');
        });

        Schema::create('stock_document_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_batch_id')->nullable()->constrained('product_batches')->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::table('product_stock_movements', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('product_batch_id')->constrained()->nullOnDelete();
            $table->foreignId('stock_document_id')->nullable()->after('warehouse_id')->constrained()->nullOnDelete();
            $table->decimal('unit_cost', 15, 2)->default(0)->after('after_quantity');
            $table->decimal('total_cost', 15, 2)->default(0)->after('unit_cost');
        });

        $warehouseId = DB::table('warehouses')->insertGetId([
            'warehouse_code' => 'KHO-MAC-DINH',
            'warehouse_name' => 'Kho mặc định',
            'is_default' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_batches')->orderBy('id')->each(function ($batch) use ($warehouseId) {
            DB::table('warehouse_stocks')->insert([
                'warehouse_id' => $warehouseId,
                'product_id' => $batch->product_id,
                'product_batch_id' => $batch->id,
                'batch_key' => $batch->id,
                'on_hand_quantity' => $batch->current_quantity,
                'reserved_quantity' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        DB::table('products')->where('track_batch', false)->orderBy('id')->each(function ($product) use ($warehouseId) {
            DB::table('warehouse_stocks')->insert([
                'warehouse_id' => $warehouseId,
                'product_id' => $product->id,
                'batch_key' => 0,
                'on_hand_quantity' => $product->total_quantity,
                'reserved_quantity' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        DB::table('product_stock_movements')->whereNull('warehouse_id')->update(['warehouse_id' => $warehouseId]);
    }

    public function down(): void
    {
        Schema::table('product_stock_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('stock_document_id');
            $table->dropConstrainedForeignId('warehouse_id');
            $table->dropColumn(['unit_cost', 'total_cost']);
        });
        Schema::dropIfExists('stock_document_items');
        Schema::dropIfExists('stock_documents');
        Schema::dropIfExists('warehouse_stocks');
        Schema::table('product_batches', fn (Blueprint $table) => $table->dropColumn('unit_cost'));
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn('average_cost'));
        Schema::dropIfExists('warehouses');
    }
};
