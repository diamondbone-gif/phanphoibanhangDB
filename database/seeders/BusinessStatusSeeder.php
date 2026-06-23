<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessStatusSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSimple('order_statuses', [
            ['code' => 'pending', 'name' => 'Chờ xử lý', 'sort_order' => 1],
            ['code' => 'completed', 'name' => 'Hoàn thành', 'sort_order' => 2],
            ['code' => 'cancelled', 'name' => 'Đã hủy', 'sort_order' => 3],
        ]);

        $this->seedSimple('payment_statuses', [
            ['code' => 'unpaid', 'name' => 'Chưa thanh toán', 'sort_order' => 1],
            ['code' => 'partial', 'name' => 'Thanh toán một phần', 'sort_order' => 2],
            ['code' => 'paid', 'name' => 'Đã thanh toán', 'sort_order' => 3],
        ]);

        $this->seedSimple('commission_statuses', [
            ['code' => 'pending', 'name' => 'Chờ duyệt', 'sort_order' => 1],
            ['code' => 'approved', 'name' => 'Đã duyệt', 'sort_order' => 2],
            ['code' => 'paid', 'name' => 'Đã chi', 'sort_order' => 3],
            ['code' => 'cancelled', 'name' => 'Đã hủy', 'sort_order' => 4],
        ]);

        $this->seedSimple('referral_statuses', [
            ['code' => 'active', 'name' => 'Đang hiệu lực', 'sort_order' => 1],
            ['code' => 'inactive', 'name' => 'Ngưng hiệu lực', 'sort_order' => 2],
        ]);
    }

    private function seedSimple(string $table, array $rows): void
    {
        foreach ($rows as $row) {
            DB::table($table)->updateOrInsert(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'sort_order' => $row['sort_order'] ?? 0,
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
