<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerBuyStatusSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'code' => 'mua_lai',
                'name' => 'Mua lại',
                'description' => 'Khách hàng đã mua và quay lại mua tiếp.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'da_mua',
                'name' => 'Đã mua',
                'description' => 'Khách hàng đã từng mua hàng.',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'code' => 'chua_mua',
                'name' => 'Chưa mua',
                'description' => 'Khách hàng chưa phát sinh đơn hàng.',
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            DB::table('customer_buy_statuses')->updateOrInsert(
                ['code' => $item['code']],
                array_merge($item, [
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ])
            );
        }
    }
}
