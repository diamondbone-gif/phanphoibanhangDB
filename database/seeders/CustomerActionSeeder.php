<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerActionSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCustomerRoles();
        $this->seedCustomerStatuses();
        $this->seedCtvStatuses();
        $this->seedStopReasons();
    }

    private function seedCustomerRoles(): void
    {
        $items = [
            [
                'code' => 'customer',
                'name' => 'Khách',
                'description' => 'Khách hàng thông thường.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'ctv',
                'name' => 'CTV',
                'description' => 'Cộng tác viên.',
                'sort_order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            $this->upsertLookup('customer_roles', $item);
        }
    }

    private function seedCustomerStatuses(): void
    {
        $items = [
            [
                'code' => 'active',
                'name' => 'Đang hoạt động',
                'description' => 'Khách hàng đang hoạt động.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'stopped_buying',
                'name' => 'Ngưng mua',
                'description' => 'Khách hàng đã ngưng mua.',
                'sort_order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            $this->upsertLookup('customer_statuses', $item);
        }
    }

    private function seedCtvStatuses(): void
    {
        $items = [
            [
                'code' => 'active',
                'name' => 'Đang hoạt động',
                'description' => 'CTV đang hoạt động.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'inactive',
                'name' => 'Tạm ngưng',
                'description' => 'CTV tạm ngưng hoạt động.',
                'sort_order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            $this->upsertLookup('ctv_statuses', $item);
        }
    }

    private function seedStopReasons(): void
    {
        $items = [
            [
                'code' => 'dung_khong_hop',
                'name' => 'Dùng không hợp',
                'description' => 'Khách ngưng mua vì dùng sản phẩm không hợp.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'khong_co_nhu_cau',
                'name' => 'Không còn nhu cầu',
                'description' => 'Khách không còn nhu cầu mua hàng.',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'code' => 'gia_chua_phu_hop',
                'name' => 'Giá chưa phù hợp',
                'description' => 'Khách thấy giá chưa phù hợp.',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'da_mua_ben_khac',
                'name' => 'Đã mua bên khác',
                'description' => 'Khách đã mua ở nơi khác.',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'code' => 'khong_lien_lac_duoc',
                'name' => 'Không liên lạc được',
                'description' => 'Không liên hệ được với khách.',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'code' => 'khac',
                'name' => 'Lý do khác',
                'description' => 'Lý do khác.',
                'sort_order' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            $this->upsertLookup('customer_stop_reasons', $item);
        }
    }

    private function upsertLookup(string $table, array $item): void
    {
        $exists = DB::table($table)
            ->where('code', $item['code'])
            ->exists();

        if ($exists) {
            DB::table($table)
                ->where('code', $item['code'])
                ->update(array_merge($item, [
                    'updated_at' => now(),
                ]));

            return;
        }

        DB::table($table)->insert(array_merge($item, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }
}
