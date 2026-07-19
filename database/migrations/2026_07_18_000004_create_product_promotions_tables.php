<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_promotions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 180);
            $table->string('promotion_type', 30)->default('product_discount');
            $table->string('discount_type', 30)->default('percent');
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('minimum_order_amount', 15, 2)->default(0);
            $table->dateTime('starts_at')->nullable()->index();
            $table->dateTime('ends_at')->nullable()->index();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('product_promotion_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_promotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->boolean('is_gift')->default(false);
            $table->timestamps();
            $table->unique(['product_promotion_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_promotion_items');
        Schema::dropIfExists('product_promotions');
    }
};
