<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCommissionRecoveryRequest;
use App\Models\CustomerCommissionAdjustment;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CommissionClawbackController extends Controller
{
    public function index()
    {
        $adjustments = DB::table('customer_commission_adjustments as adjustment')
            ->join('customer_commissions as commission', 'commission.id', '=', 'adjustment.customer_commission_id')
            ->join('customers as ctv', 'ctv.id', '=', DB::raw('COALESCE(commission.ctv_customer_id, commission.referrer_customer_id)'))
            ->join('customer_orders as orders', 'orders.id', '=', 'commission.customer_order_id')
            ->join('customers as buyer', 'buyer.id', '=', 'orders.customer_id')
            ->leftJoin('customer_order_returns as returns', 'returns.id', '=', 'adjustment.customer_order_return_id')
            ->select([
                'adjustment.*', 'commission.commission_code', 'commission.commission_amount',
                'commission.paid_amount', 'commission.commission_rate_percent', 'orders.order_code',
                'orders.final_amount', 'orders.net_amount', 'ctv.full_name as ctv_name',
                'ctv.phone as ctv_phone', 'buyer.full_name as buyer_name', 'buyer.phone as buyer_phone',
                'returns.return_code', 'returns.refund_amount', 'returns.returned_at',
            ])
            ->orderByRaw("adjustment.status = 'pending' DESC")
            ->orderByDesc('adjustment.id')
            ->paginate(30);

        $recoveries = DB::table('customer_commission_recoveries')
            ->whereIn('customer_commission_adjustment_id', $adjustments->pluck('id'))
            ->latest('id')->get()->groupBy('customer_commission_adjustment_id');

        $summary = DB::table('customer_commission_adjustments')->selectRaw(
            'COALESCE(SUM(amount),0) total, COALESCE(SUM(recovered_amount),0) recovered, COALESCE(SUM(GREATEST(amount-recovered_amount,0)),0) outstanding'
        )->first();

        return view('admin.auth.commissions.clawbacks', compact('adjustments', 'recoveries', 'summary'));
    }

    public function recover(StoreCommissionRecoveryRequest $request, CustomerCommissionAdjustment $adjustment)
    {
        DB::transaction(function () use ($request, $adjustment): void {
            $adjustment = CustomerCommissionAdjustment::query()->lockForUpdate()->findOrFail($adjustment->id);
            $amountCents = Money::cents($request->validated('amount'));
            $outstandingCents = max(0, Money::cents($adjustment->amount) - Money::cents($adjustment->recovered_amount));

            if ($amountCents > $outstandingCents) {
                throw ValidationException::withMessages(['amount' => 'Số thu hồi không được vượt quá '.Money::decimal($outstandingCents).'đ còn lại.']);
            }

            DB::table('customer_commission_recoveries')->insert([
                'customer_commission_adjustment_id' => $adjustment->id,
                'recovery_code' => 'THHH'.now()->format('ymdHis').random_int(100, 999),
                'amount' => Money::decimal($amountCents),
                'recovery_method' => $request->validated('recovery_method'),
                'recovered_date' => $request->validated('recovered_date'),
                'note' => $request->validated('note'),
                'created_by' => auth('admin')->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $newRecoveredCents = Money::cents($adjustment->recovered_amount) + $amountCents;
            $adjustment->update([
                'recovered_amount' => Money::decimal($newRecoveredCents),
                'status' => $newRecoveredCents >= Money::cents($adjustment->amount) ? 'recovered' : 'partial',
                'recovered_at' => $newRecoveredCents >= Money::cents($adjustment->amount) ? now() : null,
            ]);
        });

        return back()->with('success', 'Đã ghi nhận thu hồi/khấu trừ hoa hồng.');
    }
}
