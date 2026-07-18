<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class StatusHelper
{
    public static function id(string $table, string $code): int
    {
        $id = DB::table($table)->where('code', $code)->value('id');

        if (! $id) {
            throw new RuntimeException("Không tìm thấy trạng thái {$code} trong bảng {$table}");
        }

        return (int) $id;
    }

    public static function code(string $table, ?int $id): ?string
    {
        if (! $id) {
            return null;
        }

        return DB::table($table)->where('id', $id)->value('code');
    }
}
