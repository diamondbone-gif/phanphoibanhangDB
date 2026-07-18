<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class VerifyDatabaseBackup extends Command
{
    protected $signature = 'db:verify-backup-xampp {file? : File .sql hoặc .sql.gz; mặc định dùng file mới nhất}';

    protected $description = 'Phục hồi backup vào database tạm, kiểm tra và tự xóa database thử nghiệm';

    public function handle(): int
    {
        $connection = config('database.connections.'.config('database.default'));
        if (! is_array($connection) || ! in_array($connection['driver'] ?? null, ['mysql', 'mariadb'], true)) {
            $this->error('Kết nối mặc định phải dùng mysql hoặc mariadb.');

            return self::FAILURE;
        }

        $mysql = (string) config('database.xampp.mysql_path');
        if (! is_file($mysql)) {
            $this->error("Không tìm thấy mysql.exe tại: {$mysql}");

            return self::FAILURE;
        }

        $file = $this->resolveBackupFile();
        if ($file === null) {
            $this->error('Không tìm thấy file backup để kiểm tra.');

            return self::FAILURE;
        }

        $sql = str_ends_with(strtolower($file), '.gz')
            ? gzdecode((string) File::get($file))
            : File::get($file);
        if (! is_string($sql) || trim($sql) === '') {
            $this->error('File backup rỗng hoặc gzip không hợp lệ.');

            return self::FAILURE;
        }

        $database = 'htpp_restore_verify_'.now()->format('Ymd_His');

        try {
            $this->runMysql($mysql, $connection, null, "CREATE DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            $this->runMysql($mysql, $connection, $database, $sql, 600);

            $tableCount = trim($this->runMysql(
                $mysql,
                $connection,
                $database,
                "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '{$database}';",
            ));
            $migrationCount = trim($this->runMysql(
                $mysql,
                $connection,
                $database,
                'SELECT COUNT(*) FROM migrations;',
            ));

            if ((int) $tableCount === 0 || (int) $migrationCount === 0) {
                throw new \RuntimeException('Database phục hồi thiếu bảng hoặc lịch sử migration.');
            }

            $this->info("Restore đạt: {$file}");
            $this->line("Database tạm có {$tableCount} bảng và {$migrationCount} migration.");

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error('Restore thất bại: '.$exception->getMessage());

            return self::FAILURE;
        } finally {
            try {
                $this->runMysql($mysql, $connection, null, "DROP DATABASE IF EXISTS `{$database}`;");
            } catch (\Throwable $exception) {
                $this->warn("Không thể xóa database thử nghiệm {$database}: {$exception->getMessage()}");
            }
        }
    }

    private function resolveBackupFile(): ?string
    {
        $argument = $this->argument('file');
        if (is_string($argument) && $argument !== '') {
            $isAbsolute = preg_match('/^(?:[A-Za-z]:[\\\\\/]|[\\\\\/]{2})/', $argument) === 1;
            $path = $isAbsolute ? $argument : base_path($argument);

            return is_file($path) ? $path : null;
        }

        $files = File::glob(base_path('backups/*.sql*'));
        usort($files, fn (string $left, string $right): int => filemtime($right) <=> filemtime($left));

        return $files[0] ?? null;
    }

    private function runMysql(
        string $binary,
        array $connection,
        ?string $database,
        string $input,
        int $timeout = 60,
    ): string {
        $command = [
            $binary,
            '--batch',
            '--skip-column-names',
            '--host='.(string) ($connection['host'] ?? '127.0.0.1'),
            '--port='.(string) ($connection['port'] ?? 3306),
            '--user='.(string) ($connection['username'] ?? 'root'),
            '--default-character-set=utf8mb4',
        ];
        if ($database !== null) {
            $command[] = $database;
        }

        $process = new Process($command, base_path(), [
            'MYSQL_PWD' => (string) ($connection['password'] ?? ''),
        ]);
        $process->setInput($input);
        $process->setTimeout($timeout);
        $process->mustRun();

        return $process->getOutput();
    }
}
