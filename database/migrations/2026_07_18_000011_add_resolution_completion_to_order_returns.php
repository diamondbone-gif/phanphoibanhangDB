<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_order_returns', function (Blueprint $table) {
            $table->timestamp('resolution_completed_at')->nullable()->after('resolution_status');
            $table->unsignedBigInteger('resolution_completed_by')->nullable()->after('resolution_completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('customer_order_returns', function (Blueprint $table) {
            $table->dropColumn(['resolution_completed_at', 'resolution_completed_by']);
        });
    }
};
