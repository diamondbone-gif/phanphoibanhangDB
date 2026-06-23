<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSourceChannelSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'code' => 'zalo',
                'name' => 'Zalo',
                'description' => 'Khách biết đến qua Zalo.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'tiktok',
                'name' => 'Tiktok',
                'description' => 'Khách biết đến qua Tiktok.',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'code' => 'nguoi_gioi_thieu',
                'name' => 'Người giới thiệu',
                'description' => 'Khách biết đến qua người quen giới thiệu.',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'facebook',
                'name' => 'Facebook',
                'description' => 'Khách biết đến qua Facebook.',
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            $exists = DB::table('customer_source_channels')
                ->where('code', $item['code'])
                ->exists();

            if ($exists) {
                DB::table('customer_source_channels')
                    ->where('code', $item['code'])
                    ->update(array_merge($item, [
                        'updated_at' => now(),
                    ]));
            } else {
                DB::table('customer_source_channels')
                    ->insert(array_merge($item, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]));
            }
        }
    }
}
