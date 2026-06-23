<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->string('batch_number', 100);

            $table->date('manufacture_date')->nullable();

            $table->date('expiry_date')->nullable();

            $table->unsignedInteger('initial_quantity')->default(0);

            $table->unsignedInteger('current_quantity')->default(0);

            $table->string('status', 50)->default('active');

            $table->text('note')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('operation_managers')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('operation_managers')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['product_id', 'batch_number'], 'unique_product_batch');

            $table->index('batch_number');
            $table->index('manufacture_date');
            $table->index('expiry_date');
            $table->index('current_quantity');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};
