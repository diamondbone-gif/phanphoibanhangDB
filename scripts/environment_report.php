<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$version = DB::selectOne('SELECT VERSION() AS version, @@version_comment AS version_comment');
$sqlMode = DB::selectOne('SELECT @@sql_mode AS sql_mode');

echo json_encode([
    'php_binary' => PHP_BINARY,
    'php_version' => PHP_VERSION,
    'laravel_version' => app()->version(),
    'database_driver' => DB::connection()->getDriverName(),
    'database_name' => DB::connection()->getDatabaseName(),
    'database_version' => $version->version,
    'database_distribution' => $version->version_comment,
    'sql_mode' => $sqlMode->sql_mode,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . PHP_EOL;
