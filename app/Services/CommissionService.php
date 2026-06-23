<?php

namespace App\Services;

use App\Models\CustomerOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CommissionService
{
    /*
    |--------------------------------------------------------------------------
    | TẠO HOA HỒNG CHO ĐƠN HÀNG
    |--------------------------------------------------------------------------
    | Công thức:
    | Hoa hồng = final_amount của đơn hàng × % hoa hồng của CTV
    |
    | Ví dụ:
    | final_amount = 7.315.000
    | CTV commission_rate = 5%
    | Hoa hồng = 7.315.000 × 5% = 365.750
    |--------------------------------------------------------------------------
    */
    public function createForOrder(CustomerOrder $order, ?int $adminId = null): ?object
    {
        return DB::transaction(function () use ($order, $adminId) {
            $order = CustomerOrder::query()
                ->lockForUpdate()
                ->find($order->id);

            if (!$order) {
                return null;
            }

            /*
            |--------------------------------------------------------------------------
            | 1. Chỉ tính hoa hồng khi đơn đã hoàn thành
            |--------------------------------------------------------------------------
            */
            $completedStatusId = $this->getStatusId('order_statuses', 'completed', 2);

            if ((int) $order->order_status_id !== (int) $completedStatusId) {
                return null;
            }

            /*
            |--------------------------------------------------------------------------
            | 2. Nếu đơn này đã có hoa hồng chưa bị hủy thì không tạo trùng
            |--------------------------------------------------------------------------
            */
            $existingCommission = DB::table('customer_commissions')
                ->where('customer_order_id', $order->id)
                ->when(
                    Schema::hasColumn('customer_commissions', 'deleted_at'),
                    fn($query) => $query->whereNull('deleted_at')
                )
                ->where(function ($query) {
                    if (Schema::hasColumn('customer_commissions', 'status')) {
                        $query->whereNull('status')
                            ->orWhere('status', '!=', 'cancelled');
                    }
                })
                ->first();

            if ($existingCommission) {
                $this->markOrderCommissionCreated($order, (float) $order->final_amount, $adminId);

                return $existingCommission;
            }

            /*
            |--------------------------------------------------------------------------
            | 3. Tìm CTV giới thiệu khách hàng của đơn này
            |--------------------------------------------------------------------------
            */
            $referral = $this->findReferralForCustomer((int) $order->customer_id);

            if (!$referral || empty($referral->referrer_customer_id)) {
                return null;
            }

            $ctv = DB::table('customers')
                ->where('id', $referral->referrer_customer_id)
                ->first();

            if (!$ctv) {
                return null;
            }

            /*
            |--------------------------------------------------------------------------
            | 4. Lấy % hoa hồng đã cài cho CTV
            |--------------------------------------------------------------------------
            | Ưu tiên lấy customers.commission_rate của CTV.
            | Nếu CTV chưa có thì lấy customer_referrals.commission_rate.
            |--------------------------------------------------------------------------
            */
            $commissionRate = $this->getCommissionRate($ctv, $referral);

            if ($commissionRate <= 0) {
                return null;
            }

            /*
            |--------------------------------------------------------------------------
            | 5. Lấy tổng tiền cuối cùng của đơn hàng
            |--------------------------------------------------------------------------
            | Đây là số sau giảm giá:
            | - giảm theo sản phẩm
            | - giảm combo
            | - giảm toàn đơn
            |
            | Tuyệt đối không tính theo subtotal_amount.
            |--------------------------------------------------------------------------
            */
            $orderFinalAmount = (float) $order->final_amount;

            if ($orderFinalAmount <= 0) {
                return null;
            }

            /*
            |--------------------------------------------------------------------------
            | 6. Tính hoa hồng
            |--------------------------------------------------------------------------
            */
            $commissionAmount = round($orderFinalAmount * $commissionRate / 100, 2);

            /*
            |--------------------------------------------------------------------------
            | 7. Ghi xuống bảng customer_commissions
            |--------------------------------------------------------------------------
            */
            $commissionData = [
                'commission_code' => $this->makeCommissionCode(),

                'referrer_customer_id' => $ctv->id,
                'ctv_customer_id' => $ctv->id,
                'referred_customer_id' => $order->customer_id,
                'referral_id' => $referral->id,

                'customer_order_id' => $order->id,
                'order_code' => $order->order_code,

                'order_amount' => $orderFinalAmount,
                'order_final_amount' => $orderFinalAmount,

                'commission_rate' => $commissionRate,
                'commission_rate_percent' => $commissionRate,
                'commission_amount' => $commissionAmount,

                'commission_status_id' => $this->getStatusId('commission_statuses', 'pending', 1),
                'status' => 'unpaid',

                'paid_amount' => 0,
                'commission_date' => now(),

                'note' => 'Hoa hồng CTV tính theo final_amount của đơn hàng.',
                'created_by' => $adminId,

                'created_at' => now(),
                'updated_at' => now(),
            ];

            $commissionId = DB::table('customer_commissions')->insertGetId(
                $this->onlyExistingColumns('customer_commissions', $commissionData)
            );

            /*
            |--------------------------------------------------------------------------
            | 8. Đánh dấu đơn hàng đã tạo hoa hồng
            |--------------------------------------------------------------------------
            */
            $this->markOrderCommissionCreated($order, $orderFinalAmount, $adminId);

            return DB::table('customer_commissions')->where('id', $commissionId)->first();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | HÀM NÀY ĐỂ TƯƠNG THÍCH VỚI CODE CŨ
    |--------------------------------------------------------------------------
    | Nếu chỗ nào trong project còn gọi createForCompletedOrder()
    | thì vẫn chạy được, không bị lỗi.
    |--------------------------------------------------------------------------
    */
    public function createForCompletedOrder(CustomerOrder $order, ?int $adminId = null): ?object
    {
        return $this->createForOrder($order, $adminId);
    }

    /*
    |--------------------------------------------------------------------------
    | HỦY HOA HỒNG KHI HỦY ĐƠN
    |--------------------------------------------------------------------------
    */
    public function cancelForOrder(
        CustomerOrder $order,
        ?string $reason = null,
        ?int $adminId = null
    ): void {
        DB::transaction(function () use ($order, $reason, $adminId) {
            $cancelStatusId = $this->getStatusId('commission_statuses', 'cancelled', 4);

            $updateData = [
                'commission_status_id' => $cancelStatusId,
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => $adminId,
                'cancel_reason' => $reason,
                'cancelled_reason' => $reason,
                'updated_at' => now(),
            ];

            DB::table('customer_commissions')
                ->where('customer_order_id', $order->id)
                ->when(
                    Schema::hasColumn('customer_commissions', 'deleted_at'),
                    fn($query) => $query->whereNull('deleted_at')
                )
                ->where(function ($query) {
                    if (Schema::hasColumn('customer_commissions', 'status')) {
                        $query->whereNull('status')
                            ->orWhere('status', '!=', 'cancelled');
                    }
                })
                ->update($this->onlyExistingColumns('customer_commissions', $updateData));

            $orderUpdateData = [
                'commission_created' => false,
                'commission_base_amount' => 0,
                'updated_by' => $adminId,
                'updated_at' => now(),
            ];

            DB::table('customer_orders')
                ->where('id', $order->id)
                ->update($this->onlyExistingColumns('customer_orders', $orderUpdateData));
        });
    }

    /*
    |--------------------------------------------------------------------------
    | TÌM CTV GIỚI THIỆU CỦA KHÁCH HÀNG
    |--------------------------------------------------------------------------
    | Không lọc referral_status_id vì dữ liệu cũ của bạn có dòng bị NULL.
    |--------------------------------------------------------------------------
    */
    private function findReferralForCustomer(int $customerId): ?object
    {
        if (!Schema::hasTable('customer_referrals')) {
            return null;
        }

        return DB::table('customer_referrals')
            ->where('referred_customer_id', $customerId)
            ->whereNotNull('referrer_customer_id')
            ->orderByDesc('id')
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | LẤY % HOA HỒNG CỦA CTV
    |--------------------------------------------------------------------------
    */
    private function getCommissionRate(object $ctv, object $referral): float
    {
        $ctvRate = isset($ctv->commission_rate)
            ? (float) $ctv->commission_rate
            : 0;

        if ($ctvRate > 0) {
            return $ctvRate;
        }

        $referralRate = isset($referral->commission_rate)
            ? (float) $referral->commission_rate
            : 0;

        if ($referralRate > 0) {
            return $referralRate;
        }

        return 0;
    }

    /*
    |--------------------------------------------------------------------------
    | ĐÁNH DẤU ĐƠN HÀNG ĐÃ TẠO HOA HỒNG
    |--------------------------------------------------------------------------
    */
    private function markOrderCommissionCreated(
        CustomerOrder $order,
        float $orderFinalAmount,
        ?int $adminId = null
    ): void {
        $updateData = [
            'commission_created' => true,
            'commission_base_amount' => $orderFinalAmount,
            'updated_by' => $adminId,
            'updated_at' => now(),
        ];

        DB::table('customer_orders')
            ->where('id', $order->id)
            ->update($this->onlyExistingColumns('customer_orders', $updateData));
    }

    /*
    |--------------------------------------------------------------------------
    | LẤY ID TRẠNG THÁI THEO CODE
    |--------------------------------------------------------------------------
    */
    private function getStatusId(string $table, string $code, ?int $default = null): ?int
    {
        if (!Schema::hasTable($table)) {
            return $default;
        }

        if (!Schema::hasColumn($table, 'code')) {
            return $default;
        }

        $id = DB::table($table)
            ->where('code', $code)
            ->value('id');

        return $id ? (int) $id : $default;
    }

    /*
    |--------------------------------------------------------------------------
    | TẠO MÃ HOA HỒNG
    |--------------------------------------------------------------------------
    */
    private function makeCommissionCode(): string
    {
        do {
            $code = 'HH' . now()->format('ymdHis') . strtoupper(Str::random(4));
        } while (
            DB::table('customer_commissions')
            ->where('commission_code', $code)
            ->exists()
        );

        return $code;
    }

    /*
    |--------------------------------------------------------------------------
    | CHỈ GHI NHỮNG CỘT CÓ TỒN TẠI TRONG DATABASE
    |--------------------------------------------------------------------------
    | Giúp tránh lỗi nếu database thiếu một cột nào đó.
    |--------------------------------------------------------------------------
    */
    private function onlyExistingColumns(string $table, array $data): array
    {
        $result = [];

        foreach ($data as $column => $value) {
            if (Schema::hasColumn($table, $column)) {
                $result[$column] = $value;
            }
        }

        return $result;
    }
}
