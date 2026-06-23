<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_details', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_details', 'source_channel_id')) {
                $table->foreignId('source_channel_id')
                    ->nullable()
                    ->after('address')
                    ->constrained('customer_source_channels')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer_details', function (Blueprint $table) {
            if (Schema::hasColumn('customer_details', 'source_channel_id')) {
                $table->dropConstrainedForeignId('source_channel_id');
            }
        });
    }
};
