<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operation_managers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->string('account_type', 50)->default('operation_manager');
            $table->string('status', 50)->default('active');
            $table->string('phone', 20)->nullable();
            $table->rememberToken();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->unsignedTinyInteger('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->timestamps();

            $table->index('account_type');
            $table->index('status');
            $table->index('phone');
            $table->index('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operation_managers');
    }
};
