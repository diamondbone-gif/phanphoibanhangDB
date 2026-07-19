<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup-xampp
        {--path= : Thư mục lưu file SQL}
        {--keep=30 : Số ngày giữ bản sao lưu}
        {--no-compress : Không nén file SQL thành gzip}';

    protected $description = 'Sao lưu database MariaDB/MySQL bằng mysqldump của XAMPP';

    public function handle(): int
    {
        $connectionName = (string) config('database.default');
        $connection = config("database.connections.{$connectionName}");

        if (! is_array($connection) || ! in_array($connection['driver'] ?? null, ['mysql', 'mariadb'], true)) {
            $this->error('Kết nối mặc định phải dùng driver mysql hoặc mariadb.');

            return self::FAILURE;
        }

        $binary = (string) config('database.xampp.mysqldump_path');
        if (! is_file($binary)) {
            $this->error("Không tìm thấy mysqldump tại: {$binary}");
            $this->line('Khai báo MYSQLDUMP_PATH trong .env nếu XAMPP được cài ở vị trí khác.');

            return self::FAILURE;
        }

        $database = (string) ($connection['database'] ?? '');
        if ($database === '' || ! preg_match('/^[A-Za-z0-9_-]+$/', $database)) {
            $this->error('Tên database trống hoặc chứa ký tự không an toàn.');

            return self::FAILURE;
        }

        $directory = $this->option('path')
            ? base_path((string) $this->option('path'))
            : base_path('backups');
        File::ensureDirectoryExists($directory);

        $filename = sprintf(
            '%s_%s.sql',
            $database,
            now()->format('Ymd_His')
        );
        $output = $directory.DIRECTORY_SEPARATOR.$filename;

        $process = new Process([
            $binary,
            '--host='.(string) ($connection['host'] ?? '127.0.0.1'),
            '--port='.(string) ($connection['port'] ?? 3306),
            '--user='.(string) ($connection['username'] ?? 'root'),
            '--default-character-set=utf8mb4',
            '--single-transaction',
            '--routines',
            '--triggers',
            '--result-file='.$filename,
            $database,
        ], $directory, [
            'MYSQL_PWD' => (string) ($connection['password'] ?? ''),
        ]);
        $process->setTimeout(600);
        $process->run();

        if (! $process->isSuccessful()) {
            File::delete($output);
            $error = trim($process->getErrorOutput()) ?: 'mysqldump thực thi thất bại.';
            Log::channel('backup')->error('Sao lưu database thất bại', [
                'database' => $database,
                'error' => $error,
            ]);
            $this->error($error);

            return self::FAILURE;
        }

        if (! is_file($output) || filesize($output) === 0) {
            File::delete($output);
            Log::channel('backup')->error('File sao lưu rỗng', ['database' => $database]);
            $this->error('mysqldump không tạo được file dữ liệu hợp lệ.');

            return self::FAILURE;
        }

        if (! $this->option('no-compress')) {
            try {
                $output = $this->compress($output);
            } catch (\Throwable $exception) {
                File::delete([$output, $output.'.gz']);
                Log::channel('backup')->error('Nén backup thất bại', [
                    'database' => $database,
                    'error' => $exception->getMessage(),
                ]);
                $this->error($exception->getMessage());

                return self::FAILURE;
            }
        }

        $this->removeExpiredBackups($directory, $database, max((int) $this->option('keep'), 1));
        Log::channel('backup')->info('Sao lưu database thành công', [
            'database' => $database,
            'file' => $output,
            'bytes' => filesize($output),
        ]);
        $this->info('Đã sao lưu: '.$output);

        return self::SUCCESS;
    }

    private function compress(string $source): string
    {
        $target = $source.'.gz';
        $input = fopen($source, 'rb');
        $output = gzopen($target, 'wb9');

        if ($input === false || $output === false) {
            is_resource($input) && fclose($input);
            $output !== false && gzclose($output);
            File::delete($target);
            throw new \RuntimeException('Không thể mở file để nén backup.');
        }

        while (! feof($input)) {
            $chunk = fread($input, 1024 * 1024);
            if ($chunk === false || gzwrite($output, $chunk) === false) {
                fclose($input);
                gzclose($output);
                File::delete($target);
                throw new \RuntimeException('Nén backup thất bại.');
            }
        }

        fclose($input);
        gzclose($output);
        File::delete($source);

        return $target;
    }

    private function removeExpiredBackups(string $directory, string $database, int $keepDays): void
    {
        $expiredBefore = now()->subDays($keepDays)->getTimestamp();

        foreach (File::glob($directory.DIRECTORY_SEPARATOR.$database.'_*.sql*') as $file) {
            if (is_file($file) && filemtime($file) < $expiredBefore) {
                File::delete($file);
            }
        }
    }
}
