<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerLookupSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCustomerTypes();
        $this->seedCustomerRoles();
        $this->seedCustomerStatuses();
        $this->fixExistingCustomers();
    }

    private function seedCustomerTypes(): void
    {
        $items = [
            [
                'code' => 'direct',
                'name' => 'Tự tìm đến',
                'description' => 'Khách tự tìm đến, không qua CTV giới thiệu.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'ctv_referral',
                'name' => 'CTV giới thiệu',
                'description' => 'Khách do CTV/người giới thiệu đưa về.',
                'sort_order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            $this->upsertLookup('customer_types', $item);
        }
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
                'description' => 'Khách hàng đang hoạt động bình thường.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'stopped_buying',
                'name' => 'Ngừng mua',
                'description' => 'Khách hàng tạm ngừng hoặc không còn mua hàng.',
                'sort_order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            $this->upsertLookup('customer_statuses', $item);
        }
    }

    private function fixExistingCustomers(): void
    {
        $directTypeId = DB::table('customer_types')
            ->where('code', 'direct')
            ->value('id');

        $ctvReferralTypeId = DB::table('customer_types')
            ->where('code', 'ctv_referral')
            ->value('id');

        $customerRoleId = DB::table('customer_roles')
            ->where('code', 'customer')
            ->value('id');

        $activeStatusId = DB::table('customer_statuses')
            ->where('code', 'active')
            ->value('id');

        if ($customerRoleId) {
            DB::table('customers')
                ->whereNull('customer_role_id')
                ->update([
                    'customer_role_id' => $customerRoleId,
                    'updated_at' => now(),
                ]);
        }

        if ($activeStatusId) {
            DB::table('customers')
                ->whereNull('customer_status_id')
                ->update([
                    'customer_status_id' => $activeStatusId,
                    'updated_at' => now(),
                ]);
        }

        if ($ctvReferralTypeId) {
            $referredCustomerIds = DB::table('customer_referrals')
                ->whereNull('ended_at')
                ->whereNotNull('referred_customer_id')
                ->pluck('referred_customer_id')
                ->unique()
                ->values();

            if ($referredCustomerIds->isNotEmpty()) {
                DB::table('customers')
                    ->whereIn('id', $referredCustomerIds)
                    ->update([
                        'customer_type_id' => $ctvReferralTypeId,
                        'updated_at' => now(),
                    ]);
            }
        }

        if ($directTypeId) {
            DB::table('customers')
                ->whereNull('customer_type_id')
                ->update([
                    'customer_type_id' => $directTypeId,
                    'updated_at' => now(),
                ]);
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
