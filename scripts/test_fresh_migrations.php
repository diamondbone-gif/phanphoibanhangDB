<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$testDatabase = $argv[1] ?? 'htpp_roadmap_test_20260718';
if (!preg_match('/^htpp_roadmap_test_[A-Za-z0-9_]+$/', $testDatabase)) {
    throw new InvalidArgumentException('Refusing unsafe test database name.');
}

$connection = Config::get('database.default');
$originalDatabase = Config::get("database.connections.{$connection}.database");
$quotedTestDatabase = '`' . str_replace('`', '``', $testDatabase) . '`';

DB::statement("DROP DATABASE IF EXISTS {$quotedTestDatabase}");
DB::statement("CREATE DATABASE {$quotedTestDatabase} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

try {
    Config::set("database.connections.{$connection}.database", $testDatabase);
    DB::purge($connection);
    DB::reconnect($connection);

    $exitCode = Artisan::call('migrate', ['--force' => true, '--no-interaction' => true]);
    echo Artisan::output();

    if ($exitCode !== 0) {
        throw new RuntimeException("Fresh migration failed with exit code {$exitCode}.");
    }

    foreach (['customers', 'customer_orders', 'customer_order_returns', 'customer_commissions'] as $table) {
        if (!Schema::hasTable($table)) {
            throw new RuntimeException("Fresh migration did not create {$table}.");
        }
    }

    echo "PASSED: all migrations ran on a clean XAMPP MariaDB database.\n";
} finally {
    Config::set("database.connections.{$connection}.database", $originalDatabase);
    DB::purge($connection);
    DB::reconnect($connection);
    DB::statement("DROP DATABASE IF EXISTS {$quotedTestDatabase}");
    echo "Cleaned test database: {$testDatabase}\n";
}
