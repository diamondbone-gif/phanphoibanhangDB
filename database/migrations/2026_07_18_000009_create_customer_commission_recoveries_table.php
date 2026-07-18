<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_commission_recoveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_commission_adjustment_id');
            $table->foreign('customer_commission_adjustment_id', 'fk_comm_recovery_adjustment')
                ->references('id')->on('customer_commission_adjustments')->restrictOnDelete();
            $table->string('recovery_code', 50)->unique();
            $table->decimal('amount', 15, 2);
            $table->string('recovery_method', 30);
            $table->date('recovered_date');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['customer_commission_adjustment_id', 'recovered_date'], 'idx_commission_recovery_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_commission_recoveries');
    }
};
