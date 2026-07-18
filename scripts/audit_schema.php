<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$report = [
    'generated_at' => date(DATE_ATOM),
    'database' => DB::connection()->getDatabaseName(),
    'migrations' => Schema::hasTable('migrations')
        ? DB::table('migrations')->orderBy('id')->get()->all()
        : [],
    'tables' => [],
];

foreach (Schema::getTables() as $tableInfo) {
    $table = $tableInfo['name'];
    $report['tables'][$table] = [
        'columns' => Schema::getColumns($table),
        'indexes' => Schema::getIndexes($table),
        'foreign_keys' => Schema::getForeignKeys($table),
    ];
}

$json = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
$output = $argv[1] ?? null;

if ($output) {
    file_put_contents($output, $json . PHP_EOL);
    echo "Schema report created: {$output}\n";
} else {
    echo $json . PHP_EOL;
}
