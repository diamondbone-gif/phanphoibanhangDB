<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Thêm dữ liệu danh mục chăm sóc khách hàng.
     *
     * Migration này KHÔNG tạo bảng mới.
     * Nó chỉ thêm dữ liệu vào các bảng đã có:
     * - care_channels
     * - care_priorities
     * - care_statuses
     */
    public function up(): void
    {
        $now = now();

        /*
        |--------------------------------------------------------------------------
        | 1. Kênh chăm sóc khách hàng
        |--------------------------------------------------------------------------
        */
        if (Schema::hasTable('care_channels')) {
            $channels = [
                [
                    'code' => 'phone',
                    'name' => 'Gọi điện',
                    'description' => 'Chăm sóc khách hàng qua điện thoại.',
                    'sort_order' => 1,
                    'is_active' => 1,
                ],
                [
                    'code' => 'zalo',
                    'name' => 'Zalo',
                    'description' => 'Chăm sóc khách hàng qua Zalo.',
                    'sort_order' => 2,
                    'is_active' => 1,
                ],
                [
                    'code' => 'sms',
                    'name' => 'Tin nhắn SMS',
                    'description' => 'Chăm sóc khách hàng qua tin nhắn SMS.',
                    'sort_order' => 3,
                    'is_active' => 1,
                ],
                [
                    'code' => 'email',
                    'name' => 'Email',
                    'description' => 'Chăm sóc khách hàng qua email.',
                    'sort_order' => 4,
                    'is_active' => 1,
                ],
                [
                    'code' => 'direct',
                    'name' => 'Gặp trực tiếp',
                    'description' => 'Trao đổi trực tiếp với khách hàng.',
                    'sort_order' => 5,
                    'is_active' => 1,
                ],
                [
                    'code' => 'other',
                    'name' => 'Kênh khác',
                    'description' => 'Các hình thức chăm sóc khác.',
                    'sort_order' => 6,
                    'is_active' => 1,
                ],
            ];

            foreach ($channels as $channel) {
                DB::table('care_channels')->updateOrInsert(
                    [
                        'code' => $channel['code'],
                    ],
                    [
                        'name' => $channel['name'],
                        'description' => $channel['description'],
                        'sort_order' => $channel['sort_order'],
                        'is_active' => $channel['is_active'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Mức độ ưu tiên chăm sóc
        |--------------------------------------------------------------------------
        */
        if (Schema::hasTable('care_priorities')) {
            $priorities = [
                [
                    'code' => 'low',
                    'name' => 'Thấp',
                    'description' => 'Khách hàng chưa cần chăm sóc ngay.',
                    'sort_order' => 1,
                    'is_active' => 1,
                ],
                [
                    'code' => 'normal',
                    'name' => 'Bình thường',
                    'description' => 'Chăm sóc theo lịch thông thường.',
                    'sort_order' => 2,
                    'is_active' => 1,
                ],
                [
                    'code' => 'high',
                    'name' => 'Cao',
                    'description' => 'Khách hàng cần được ưu tiên chăm sóc.',
                    'sort_order' => 3,
                    'is_active' => 1,
                ],
                [
                    'code' => 'urgent',
                    'name' => 'Khẩn',
                    'description' => 'Khách hàng cần được liên hệ sớm.',
                    'sort_order' => 4,
                    'is_active' => 1,
                ],
            ];

            foreach ($priorities as $priority) {
                DB::table('care_priorities')->updateOrInsert(
                    [
                        'code' => $priority['code'],
                    ],
                    [
                        'name' => $priority['name'],
                        'description' => $priority['description'],
                        'sort_order' => $priority['sort_order'],
                        'is_active' => $priority['is_active'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Trạng thái chăm sóc
        |--------------------------------------------------------------------------
        */
        if (Schema::hasTable('care_statuses')) {
            $statuses = [
                [
                    'code' => 'pending',
                    'name' => 'Chờ chăm sóc',
                    'description' => 'Lịch chăm sóc đang chờ thực hiện.',
                    'sort_order' => 1,
                    'is_active' => 1,
                ],
                [
                    'code' => 'processing',
                    'name' => 'Đang xử lý',
                    'description' => 'Nhân viên đang chăm sóc khách hàng.',
                    'sort_order' => 2,
                    'is_active' => 1,
                ],
                [
                    'code' => 'completed',
                    'name' => 'Đã hoàn thành',
                    'description' => 'Đã hoàn thành chăm sóc khách hàng.',
                    'sort_order' => 3,
                    'is_active' => 1,
                ],
                [
                    'code' => 'no_answer',
                    'name' => 'Không liên hệ được',
                    'description' => 'Chưa thể liên hệ được với khách hàng.',
                    'sort_order' => 4,
                    'is_active' => 1,
                ],
                [
                    'code' => 'cancelled',
                    'name' => 'Đã hủy',
                    'description' => 'Lịch chăm sóc đã được hủy.',
                    'sort_order' => 5,
                    'is_active' => 1,
                ],
            ];

            foreach ($statuses as $status) {
                DB::table('care_statuses')->updateOrInsert(
                    [
                        'code' => $status['code'],
                    ],
                    [
                        'name' => $status['name'],
                        'description' => $status['description'],
                        'sort_order' => $status['sort_order'],
                        'is_active' => $status['is_active'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }

    /**
     * Hoàn tác dữ liệu do migration này thêm.
     */
    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Xóa các kênh chăm sóc đã thêm
        |--------------------------------------------------------------------------
        */
        if (Schema::hasTable('care_channels')) {
            DB::table('care_channels')
                ->whereIn('code', [
                    'phone',
                    'zalo',
                    'sms',
                    'email',
                    'direct',
                    'other',
                ])
                ->delete();
        }

        /*
        |--------------------------------------------------------------------------
        | Xóa các mức ưu tiên đã thêm
        |--------------------------------------------------------------------------
        */
        if (Schema::hasTable('care_priorities')) {
            DB::table('care_priorities')
                ->whereIn('code', [
                    'low',
                    'normal',
                    'high',
                    'urgent',
                ])
                ->delete();
        }

        /*
        |--------------------------------------------------------------------------
        | Xóa các trạng thái chăm sóc đã thêm
        |--------------------------------------------------------------------------
        */
        if (Schema::hasTable('care_statuses')) {
            DB::table('care_statuses')
                ->whereIn('code', [
                    'pending',
                    'processing',
                    'completed',
                    'no_answer',
                    'cancelled',
                ])
                ->delete();
        }
    }
};