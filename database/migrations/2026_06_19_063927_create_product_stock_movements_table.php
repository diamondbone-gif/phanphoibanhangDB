<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_stock_movements')) {
            return;
        }

        Schema::create('product_stock_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('product_batch_id')
                ->nullable()
                ->constrained('product_batches')
                ->nullOnDelete();

            $table->string('movement_type', 50);
            $table->integer('quantity');

            $table->integer('before_quantity')->default(0);
            $table->integer('after_quantity')->default(0);

            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->timestamp('movement_date')->nullable();
            $table->text('note')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('operation_managers')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('movement_type');
            $table->index('reference_type');
            $table->index('reference_id');
            $table->index('movement_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_stock_movements');
    }
};
