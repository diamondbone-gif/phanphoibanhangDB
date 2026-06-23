<?php

namespace App\Services;

use App\Models\CustomerCommission;
use App\Models\CustomerCommissionAdjustment;
use App\Models\CustomerCommissionItem;
use App\Models\CustomerOrder;
use App\Models\CustomerReferral;
use App\Models\ProductCommissionRule;
use App\Support\StatusHelper;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    public function createForCompletedOrder(CustomerOrder $order, ?int $adminId = null): void
    {
        $order->load(['items.product']);

        if ($order->commission_created) {
            return;
        }

        $completedId = StatusHelper::id('order_statuses', 'completed');

        if ((int) $order->order_status_id !== $completedId) {
            return;
        }

        $activeReferral = CustomerReferral::query()
            ->where('referred_customer_id', $order->customer_id)
            ->where(function ($q) {
                $q->whereNull('effective_from')->orWhere('effective_from', '<=', now()->toDateString());
            })
            ->where(function ($q) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', now()->toDateString());
            })
            ->latest('id')
            ->first();

        if (!$activeReferral) {
            return;
        }

        $lineNetTotal = (float) $order->items->sum('final_total');
        $commissionItems = [];
        $totalBase = 0;
        $totalCommission = 0;

        foreach ($order->items as $item) {
            $product = $item->product;

            if (!$product || !$product->is_commissionable) {
                continue;
            }

            $eligibleAmount = (float) $item->final_total;

            if ($lineNetTotal > 0 && $order->order_discount_amount > 0) {
                $shareDiscount = round($item->final_total * $order->order_discount_amount / $lineNetTotal);
                $eligibleAmount = max(0, $eligibleAmount - $shareDiscount);
            }

            $rate = $this->getCommissionRate($product);
            $commissionAmount = round($eligibleAmount * $rate / 100);

            if ($eligibleAmount <= 0 || $rate <= 0 || $commissionAmount <= 0) {
                continue;
            }

            $commissionItems[] = [
                'customer_order_item_id' => $item->id,
                'product_id' => $product->id,
                'product_name' => $item->product_name,
                'eligible_amount' => $eligibleAmount,
                'commission_rate' => $rate,
                'commission_amount' => $commissionAmount,
            ];

            $totalBase += $eligibleAmount;
            $totalCommission += $commissionAmount;
        }

        if ($totalCommission <= 0) {
            return;
        }

        $averageRate = $totalBase > 0 ? round($totalCommission / $totalBase * 100, 2) : 0;

        $commission = CustomerCommission::create([
            'referrer_customer_id' => $activeReferral->referrer_customer_id,
            'referred_customer_id' => $order->customer_id,
            'referral_id' => $activeReferral->id,
            'customer_order_id' => $order->id,
            'order_code' => $order->order_code,
            'order_amount' => $order->final_amount,
            'commission_base_amount' => $totalBase,
            'commission_rate' => $averageRate,
            'commission_amount' => $totalCommission,
            'commission_status_id' => StatusHelper::id('commission_statuses', 'pending'),
        ]);

        foreach ($commissionItems as $row) {
            $row['customer_commission_id'] = $commission->id;
            CustomerCommissionItem::create($row);
        }

        $order->update([
            'commission_created' => true,
        ]);
    }

    public function reverseForCancelledOrder(CustomerOrder $order, string $reason, ?int $adminId = null): void
    {
        $commission = CustomerCommission::query()
            ->where('customer_order_id', $order->id)
            ->first();

        if (!$commission) {
            return;
        }

        if ($commission->paid_at) {
            CustomerCommissionAdjustment::create([
                'customer_commission_id' => $commission->id,
                'adjustment_code' => $this->makeCode('ADJ', 'customer_commission_adjustments', 'adjustment_code'),
                'adjustment_type' => 'reverse',
                'amount' => -abs((float) $commission->commission_amount),
                'reason' => $reason,
                'created_by' => $adminId,
            ]);
        }

        $commission->update([
            'commission_status_id' => StatusHelper::id('commission_statuses', 'cancelled'),
            'cancelled_reason' => $reason,
        ]);

        $order->update([
            'commission_created' => false,
        ]);
    }

    private function getCommissionRate($product): float
    {
        $today = now()->toDateString();

        $rule = ProductCommissionRule::query()
            ->where('is_active', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
            })
            ->where(function ($q) use ($product) {
                $q->where('product_id', $product->id)
                    ->orWhere('product_category_id', $product->product_category_id);
            })
            ->orderByRaw('product_id IS NULL')
            ->latest('id')
            ->first();

        if ($rule) {
            return (float) $rule->commission_rate;
        }

        return (float) $product->default_commission_rate;
    }

    private function makeCode(string $prefix, string $table, string $column): string
    {
        do {
            $code = $prefix . now()->format('ymdHis') . random_int(100, 999);
        } while (DB::table($table)->where($column, $code)->exists());

        return $code;
    }
}
