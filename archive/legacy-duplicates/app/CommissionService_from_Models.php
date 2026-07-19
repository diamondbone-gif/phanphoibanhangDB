<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerCommission;
use App\Models\CustomerOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CommissionService
{
    /**
     * Tạo hoa hồng cho đơn hàng đã hoàn thành.
     *
     * Công thức:
     * Hoa hồng = final_amount của đơn hàng × % hoa hồng của CTV
     */
    public function createForOrder(CustomerOrder $order): ?CustomerCommission
    {
        return DB::transaction(function () use ($order) {
            $order = CustomerOrder::query()
                ->where('id', $order->id)
                ->lockForUpdate()
                ->first();

            if (!$order) {
                return null;
            }

            /**
             * Chỉ tính hoa hồng cho đơn đã hoàn thành.
             * Database của bạn hiện order_status_id = 2 là completed.
             */
            if ((int) $order->order_status_id !== 2) {
                return null;
            }

            /**
             * Nếu đơn này đã có hoa hồng rồi thì không tạo trùng.
             */
            $existingCommission = CustomerCommission::query()
                ->where('customer_order_id', $order->id)
                ->whereNull('deleted_at')
                ->first();

            if ($existingCommission) {
                if ((int) $order->commission_created !== 1) {
                    $order->commission_created = 1;
                    $order->save();
                }

                return $existingCommission;
            }

            /**
             * Tìm CTV giới thiệu khách hàng của đơn này.
             */
            $referral = $this->findReferralForCustomer((int) $order->customer_id);

            if (!$referral || !$referral->referrer_customer_id) {
                return null;
            }

            $ctv = Customer::query()->find($referral->referrer_customer_id);

            if (!$ctv) {
                return null;
            }

            /**
             * Lấy % hoa hồng đã cài cho CTV.
             * Ưu tiên customers.commission_rate của CTV.
             * Nếu CTV chưa có commission_rate thì lấy customer_referrals.commission_rate.
             */
            $commissionRate = $this->getCommissionRate($ctv, $referral);

            if ($commissionRate <= 0) {
                return null;
            }

            /**
             * Lấy tổng tiền cuối cùng của đơn hàng.
             * Đây là số tiền sau khi đã giảm giá sản phẩm, combo, toàn đơn.
             */
            $orderFinalAmount = (float) $order->final_amount;

            if ($orderFinalAmount <= 0) {
                return null;
            }

            $commissionAmount = round($orderFinalAmount * $commissionRate / 100, 2);

            $pendingStatusId = $this->getCommissionStatusId('pending');

            $commission = CustomerCommission::query()->create([
                'commission_code' => $this->generateCommissionCode(),

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

                'commission_status_id' => $pendingStatusId,
                'status' => 'unpaid',

                'paid_amount' => 0,
                'commission_date' => now(),

                'note' => 'Hoa hồng CTV tính theo tổng tiền cuối cùng của đơn hàng.',
                'created_by' => Auth::guard('admin')->id() ?? Auth::id(),
            ]);

            /**
             * Đánh dấu đơn hàng đã tạo hoa hồng.
             */
            $order->commission_created = 1;
            $order->commission_base_amount = $orderFinalAmount;
            $order->save();

            return $commission;
        });
    }

    /**
     * Hủy hoa hồng khi đơn hàng bị hủy.
     */
    public function cancelForOrder(CustomerOrder $order, ?string $reason = null): void
    {
        DB::transaction(function () use ($order, $reason) {
            $cancelledStatusId = $this->getCommissionStatusId('cancelled');

            CustomerCommission::query()
                ->where('customer_order_id', $order->id)
                ->whereNull('deleted_at')
                ->update([
                    'status' => 'cancelled',
                    'commission_status_id' => $cancelledStatusId,
                    'cancelled_at' => now(),
                    'cancelled_by' => Auth::guard('admin')->id() ?? Auth::id(),
                    'cancel_reason' => $reason,
                    'cancelled_reason' => $reason,
                    'updated_at' => now(),
                ]);

            $order->commission_created = 0;
            $order->save();
        });
    }

    /**
     * Tìm dòng giới thiệu của khách hàng.
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

    /**
     * Lấy % hoa hồng.
     * Ưu tiên % được cài trong hồ sơ CTV.
     */
    private function getCommissionRate(Customer $ctv, object $referral): float
    {
        $ctvRate = (float) ($ctv->commission_rate ?? 0);

        if ($ctvRate > 0) {
            return $ctvRate;
        }

        $referralRate = (float) ($referral->commission_rate ?? 0);

        if ($referralRate > 0) {
            return $referralRate;
        }

        return 0;
    }

    private function getCommissionStatusId(string $code): ?int
    {
        if (!Schema::hasTable('commission_statuses')) {
            return null;
        }

        $id = DB::table('commission_statuses')
            ->where('code', $code)
            ->value('id');

        return $id ? (int) $id : null;
    }

    private function generateCommissionCode(): string
    {
        do {
            $code = 'HH' . now()->format('ymdHis') . strtoupper(Str::random(4));
        } while (
            CustomerCommission::query()
            ->where('commission_code', $code)
            ->exists()
        );

        return $code;
    }
}
