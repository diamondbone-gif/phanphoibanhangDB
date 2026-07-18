<?php

namespace App\Services;

use App\Enums\CommissionState;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderReturn;
use App\Support\Money;
use App\Support\StatusHelper;
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

            if (! $order) {
                return null;
            }

            /*
            |--------------------------------------------------------------------------
            | 1. Chỉ tính hoa hồng khi đơn đã hoàn thành
            |--------------------------------------------------------------------------
            */
            $completedStatusId = StatusHelper::id('order_statuses', 'completed');

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
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first();

            /*
            |--------------------------------------------------------------------------
            | 3. Tìm CTV giới thiệu khách hàng của đơn này
            |--------------------------------------------------------------------------
            */
            $referral = $this->findReferralForCustomer((int) $order->customer_id);

            if (! $referral || empty($referral->referrer_customer_id)) {
                return null;
            }

            $ctv = DB::table('customers')
                ->where('id', $referral->referrer_customer_id)
                ->first();

            if (! $ctv) {
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

            $commissionBasisPoints = Money::percentBasisPoints($commissionRate);

            if ($commissionBasisPoints <= 0) {
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
            $orderFinalAmount = ($order->return_status ?? 'none') === 'none'
                ? $order->final_amount
                : $order->net_amount;

            $orderFinalCents = Money::cents($orderFinalAmount);

            if ($orderFinalCents <= 0) {
                return null;
            }

            /*
            |--------------------------------------------------------------------------
            | 6. Tính hoa hồng
            |--------------------------------------------------------------------------
            */
            $commissionAmount = Money::decimal(
                Money::percentage($orderFinalCents, $commissionBasisPoints)
            );

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

                'commission_status_id' => StatusHelper::id('commission_statuses', 'pending'),
                'status' => CommissionState::Unpaid->value,

                'paid_amount' => 0,
                'commission_date' => now(),

                'note' => 'Hoa hồng CTV tính theo final_amount của đơn hàng.',
                'created_by' => $adminId,

                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($existingCommission) {
                $paidAmountCents = Money::cents($existingCommission->paid_amount ?? 0);
                $commissionAmountCents = Money::cents($commissionAmount);
                $clawbackCents = max(0, $paidAmountCents - $commissionAmountCents);
                $status = match (true) {
                    $clawbackCents > 0 => CommissionState::Clawback->value,
                    $paidAmountCents >= $commissionAmountCents => CommissionState::Paid->value,
                    default => CommissionState::Unpaid->value,
                };

                $commissionData['status'] = $status;
                $commissionData['commission_status_id'] = StatusHelper::id(
                    'commission_statuses',
                    $status === CommissionState::Paid->value ? 'paid' : 'pending'
                );
                $commissionData['clawback_amount'] = Money::decimal($clawbackCents);
                $commissionData['cancelled_at'] = null;
                $commissionData['cancelled_by'] = null;
                $commissionData['cancel_reason'] = null;
                $commissionData['cancelled_reason'] = null;
                $commissionData['updated_at'] = now();
                unset($commissionData['commission_code'], $commissionData['paid_amount'], $commissionData['created_at']);

                DB::table('customer_commissions')
                    ->where('id', $existingCommission->id)
                    ->update($commissionData);
                $commissionId = $existingCommission->id;
            } else {
                $commissionId = DB::table('customer_commissions')->insertGetId($commissionData);
            }

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

    public function recalculateForOrder(
        CustomerOrder $order,
        ?int $adminId = null,
        ?CustomerOrderReturn $orderReturn = null
    ): ?object {
        return DB::transaction(function () use ($order, $adminId, $orderReturn) {
            $order = CustomerOrder::query()->lockForUpdate()->findOrFail($order->id);
            $commission = DB::table('customer_commissions')
                ->where('customer_order_id', $order->id)
                ->where(function ($query) {
                    $query->whereNull('status')->orWhere('status', '!=', CommissionState::Cancelled->value);
                })
                ->lockForUpdate()
                ->latest('id')
                ->first();

            $commissionBaseCents = max(0, Money::cents($order->net_amount ?? $order->final_amount));

            if (! $commission) {
                return $commissionBaseCents > 0 ? $this->createForOrder($order, $adminId) : null;
            }

            $rate = $commission->commission_rate_percent ?? $commission->commission_rate ?? 0;
            $newAmountCents = Money::percentage(
                $commissionBaseCents,
                Money::percentBasisPoints($rate)
            );
            $paidAmountCents = Money::cents($commission->paid_amount ?? 0);
            $clawbackCents = max(0, $paidAmountCents - $newAmountCents);

            $status = match (true) {
                $clawbackCents > 0 => CommissionState::Clawback->value,
                $newAmountCents <= 0 => CommissionState::Cancelled->value,
                $paidAmountCents >= $newAmountCents => CommissionState::Paid->value,
                default => CommissionState::Unpaid->value,
            };

            $update = [
                'order_amount' => Money::decimal($commissionBaseCents),
                'order_final_amount' => Money::decimal($commissionBaseCents),
                'commission_amount' => Money::decimal($newAmountCents),
                'clawback_amount' => Money::decimal($clawbackCents),
                'status' => $status,
                'commission_status_id' => StatusHelper::id(
                    'commission_statuses',
                    $status === CommissionState::Cancelled->value
                        ? 'cancelled'
                        : ($status === CommissionState::Paid->value ? 'paid' : 'pending')
                ),
                'cancelled_at' => $status === CommissionState::Cancelled->value ? now() : null,
                'cancelled_by' => $status === CommissionState::Cancelled->value ? $adminId : null,
                'cancel_reason' => $status === CommissionState::Cancelled->value ? 'Đơn hàng đã hoàn trả toàn bộ' : null,
                'updated_at' => now(),
            ];

            DB::table('customer_commissions')
                ->where('id', $commission->id)
                ->update($update);

            if ($clawbackCents > 0 && $orderReturn) {
                $recordedClawbackCents = Money::cents(
                    DB::table('customer_commission_adjustments')
                        ->where('customer_commission_id', $commission->id)
                        ->where('adjustment_type', 'clawback')
                        ->sum('amount')
                );
                $newAdjustmentCents = max(0, $clawbackCents - $recordedClawbackCents);

                if ($newAdjustmentCents > 0) {
                    DB::table('customer_commission_adjustments')->updateOrInsert(
                        ['customer_order_return_id' => $orderReturn->id],
                        [
                            'customer_commission_id' => $commission->id,
                            'adjustment_code' => 'THH'.$orderReturn->id,
                            'adjustment_type' => 'clawback',
                            'amount' => Money::decimal($newAdjustmentCents),
                            'recovered_amount' => 0,
                            'status' => 'pending',
                            'reason' => 'Thu hồi hoa hồng do hoàn đơn '.$order->order_code.' ('.$orderReturn->return_code.').',
                            'created_by' => $adminId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }

            DB::table('customer_orders')->where('id', $order->id)->update([
                'commission_created' => $newAmountCents > 0,
                'commission_base_amount' => Money::decimal($commissionBaseCents),
                'updated_by' => $adminId,
                'updated_at' => now(),
            ]);

            return DB::table('customer_commissions')->where('id', $commission->id)->first();
        });
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
            $cancelStatusId = StatusHelper::id('commission_statuses', 'cancelled');

            $updateData = [
                'commission_status_id' => $cancelStatusId,
                'status' => CommissionState::Cancelled->value,
                'cancelled_at' => now(),
                'cancelled_by' => $adminId,
                'cancel_reason' => $reason,
                'cancelled_reason' => $reason,
                'updated_at' => now(),
            ];

            DB::table('customer_commissions')
                ->where('customer_order_id', $order->id)
                ->whereNull('deleted_at')
                ->where(fn ($query) => $query->whereNull('status')->orWhere('status', '!=', CommissionState::Cancelled->value))
                ->update($updateData);

            $orderUpdateData = [
                'commission_created' => false,
                'commission_base_amount' => 0,
                'updated_by' => $adminId,
                'updated_at' => now(),
            ];

            DB::table('customer_orders')
                ->where('id', $order->id)
                ->update($orderUpdateData);
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
        $query = DB::table('customer_referrals')
            ->where('referred_customer_id', $customerId)
            ->whereNotNull('referrer_customer_id');

        if (Schema::hasColumn('customer_referrals', 'status')) {
            $query->where(function ($query): void {
                $query->whereNull('status')->orWhere('status', 'active');
            });
        }
        if (Schema::hasColumn('customer_referrals', 'started_at')) {
            $query->where(function ($query): void {
                $query->whereNull('started_at')->orWhere('started_at', '<=', now());
            });
        }
        if (Schema::hasColumn('customer_referrals', 'ended_at')) {
            $query->whereNull('ended_at');
        }
        if (Schema::hasColumn('customer_referrals', 'effective_from')) {
            $query->where(function ($query): void {
                $query->whereNull('effective_from')->orWhere('effective_from', '<=', now());
            });
        }
        if (Schema::hasColumn('customer_referrals', 'effective_to')) {
            $query->where(function ($query): void {
                $query->whereNull('effective_to')->orWhere('effective_to', '>=', now());
            });
        }

        return $query->orderByDesc('id')->first();
    }

    /*
    |--------------------------------------------------------------------------
    | LẤY % HOA HỒNG CỦA CTV
    |--------------------------------------------------------------------------
    */
    private function getCommissionRate(object $ctv, object $referral): string
    {
        $referralRate = isset($referral->commission_rate)
            ? (string) $referral->commission_rate
            : '0';

        if (Money::percentBasisPoints($referralRate) > 0) {
            return $referralRate;
        }

        $ctvRate = isset($ctv->commission_rate)
            ? (string) $ctv->commission_rate
            : '0';

        if (Money::percentBasisPoints($ctvRate) > 0) {
            return $ctvRate;
        }

        return '0';
    }

    /*
    |--------------------------------------------------------------------------
    | ĐÁNH DẤU ĐƠN HÀNG ĐÃ TẠO HOA HỒNG
    |--------------------------------------------------------------------------
    */
    private function markOrderCommissionCreated(
        CustomerOrder $order,
        int|float|string $orderFinalAmount,
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
            ->update($updateData);
    }

    /*
    |--------------------------------------------------------------------------
    | TẠO MÃ HOA HỒNG
    |--------------------------------------------------------------------------
    */
    private function makeCommissionCode(): string
    {
        do {
            $code = 'HH'.now()->format('ymdHis').strtoupper(Str::random(4));
        } while (
            DB::table('customer_commissions')
                ->where('commission_code', $code)
                ->exists()
        );

        return $code;
    }
}
