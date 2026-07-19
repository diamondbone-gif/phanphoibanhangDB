<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));

        $summary = DB::table('customer_commissions')
            ->whereNull('deleted_at')
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '<>', 'cancelled');
            })
            ->selectRaw('
                COALESCE(SUM(commission_amount), 0) AS total_commission,
                COALESCE(SUM(paid_amount), 0) AS total_paid,
                COALESCE(SUM(GREATEST(commission_amount - paid_amount, 0)), 0) AS total_debt
            ')
            ->first();

        $lastPaidSub = DB::table('customer_commission_payouts')
            ->selectRaw('referrer_customer_id, MAX(paid_at) AS last_paid_at')
            ->groupBy('referrer_customer_id');

        $rowsQuery = DB::table('customer_commissions as cc')
            ->join('customers as ctv', function ($join) {
                $join->on('ctv.id', '=', DB::raw('COALESCE(cc.ctv_customer_id, cc.referrer_customer_id)'));
            })
            ->leftJoin('customer_details as cd', 'cd.customer_id', '=', 'ctv.id')
            ->leftJoinSub($lastPaidSub, 'lp', function ($join) {
                $join->on('lp.referrer_customer_id', '=', 'ctv.id');
            })
            ->whereNull('cc.deleted_at')
            ->where(function ($query) {
                $query->whereNull('cc.status')
                    ->orWhere('cc.status', '<>', 'cancelled');
            });

        if ($search !== '') {
            $rowsQuery->where(function ($query) use ($search) {
                $query->where('ctv.full_name', 'like', "%{$search}%")
                    ->orWhere('ctv.phone', 'like', "%{$search}%");
            });
        }

        $rows = $rowsQuery
            ->selectRaw("
                ctv.id AS ctv_id,
                ctv.full_name,
                ctv.phone,
                COALESCE(
                    NULLIF(
                        TRIM(BOTH ', ' FROM CONCAT_WS(', ',
                            NULLIF(cd.address, ''),
                            NULLIF(cd.ward, ''),
                            NULLIF(cd.district, ''),
                            NULLIF(cd.province, '')
                        )),
                        ''
                    ),
                    'Chưa cập nhật'
                ) AS full_address,
                COALESCE(ctv.commission_rate, MAX(NULLIF(cc.commission_rate_percent, 0)), MAX(cc.commission_rate), 0) AS commission_rate,
                COALESCE(SUM(cc.commission_amount), 0) AS total_commission,
                COALESCE(SUM(cc.paid_amount), 0) AS total_paid,
                COALESCE(SUM(GREATEST(cc.commission_amount - cc.paid_amount, 0)), 0) AS total_debt,
                lp.last_paid_at
            ")
            ->groupBy(
                'ctv.id',
                'ctv.full_name',
                'ctv.phone',
                'ctv.commission_rate',
                'cd.address',
                'cd.ward',
                'cd.district',
                'cd.province',
                'lp.last_paid_at'
            )
            ->orderByRaw('total_debt DESC')
            ->paginate(10)
            ->withQueryString();

        return view('admin.auth.commissions.index', compact('summary', 'rows', 'search'));
    }

    public function detail(int $ctv)
    {
        $ctvData = $this->getCtvData($ctv);

        $orders = DB::table('customer_commissions as cc')
            ->leftJoin('customer_orders as co', 'co.id', '=', 'cc.customer_order_id')
            ->leftJoin('customer_order_items as oi', 'oi.customer_order_id', '=', 'co.id')
            ->whereNull('cc.deleted_at')
            ->whereRaw('COALESCE(cc.ctv_customer_id, cc.referrer_customer_id) = ?', [$ctv])
            ->where(function ($query) {
                $query->whereNull('cc.status')
                    ->orWhere('cc.status', '<>', 'cancelled');
            })
            ->selectRaw('
                cc.id AS commission_id,
                cc.order_code,
                COALESCE(co.subtotal_amount, cc.order_amount, 0) AS subtotal_amount,
                COALESCE(co.product_discount_amount, 0) AS product_discount_amount,
                COALESCE(co.combo_discount_amount, 0) AS combo_discount_amount,
                COALESCE(co.order_discount_amount, co.discount_amount, 0) AS order_discount_amount,
                COALESCE(co.final_amount, cc.order_final_amount, cc.order_amount, 0) AS final_amount,
                GROUP_CONCAT(CONCAT(oi.product_name, " x", oi.quantity) SEPARATOR ", ") AS product_text
            ')
            ->groupBy(
                'cc.id',
                'cc.order_code',
                'co.subtotal_amount',
                'cc.order_amount',
                'co.product_discount_amount',
                'co.combo_discount_amount',
                'co.order_discount_amount',
                'co.discount_amount',
                'co.final_amount',
                'cc.order_final_amount'
            )
            ->orderByDesc('cc.id')
            ->get()
            ->map(function ($item) {
                $discount = (float) $item->product_discount_amount
                    + (float) $item->combo_discount_amount
                    + (float) $item->order_discount_amount;

                return [
                    'commission_id' => $item->commission_id,
                    'order_code' => $item->order_code,
                    'product_text' => $item->product_text ?: 'Chưa có sản phẩm',
                    'subtotal_amount' => (float) $item->subtotal_amount,
                    'discount_amount' => $discount,
                    'final_amount' => (float) $item->final_amount,
                ];
            });

        return response()->json([
            'ctv' => $ctvData,
            'orders' => $orders,
        ]);
    }

    public function pay(Request $request, int $ctv)
    {
        $validator = Validator::make($request->all(), [
            'payout_type' => ['required', 'in:all,installment'],
            'amount' => ['required', 'numeric', 'min:1000', 'regex:/^\d+(?:\.\d{1,2})?$/'],
            'paid_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'payout_type.required' => 'Vui lòng chọn hình thức thanh toán.',
            'amount.required' => 'Vui lòng nhập số tiền chi hoa hồng.',
            'amount.numeric' => 'Số tiền chi không hợp lệ.',
            'amount.min' => 'Số tiền chi tối thiểu là 1.000đ.',
            'paid_date.required' => 'Vui lòng chọn ngày chi.',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $this->getCtvData($ctv);

        $payoutType = $request->input('payout_type');
        $amountCents = Money::cents($request->input('amount'));
        $paidDate = Carbon::parse($request->input('paid_date'))->setTimeFrom(now());
        $paymentMethod = $request->input('payment_method');
        $note = $request->input('note');
        $paidBy = Auth::guard('admin')->id();

        try {
            DB::transaction(function () use ($ctv, $payoutType, &$amountCents, $paidDate, $paymentMethod, $note, $paidBy) {
                $totalDebtCents = $this->getCtvTotalDebtCents($ctv);

                if ($totalDebtCents <= 0) {
                    throw new \Exception('CTV này không còn hoa hồng cần chi.');
                }

                if ($payoutType === 'all') {
                    $amountCents = $totalDebtCents;
                }

                if ($amountCents > $totalDebtCents) {
                    throw new \Exception('Số tiền chi không được lớn hơn hoa hồng còn nợ.');
                }

                $payoutStatusPaidId = $this->getPayoutStatusId('paid');

                $insertData = [
                    'payout_code' => $this->makePayoutCode(),
                    'referrer_customer_id' => $ctv,
                    'total_amount' => Money::decimal($amountCents),
                    'payout_status_id' => $payoutStatusPaidId,
                    'paid_at' => $paidDate,
                    'paid_by' => $paidBy,
                    'note' => $note,
                    'payment_method' => $paymentMethod,
                    'payout_type' => $payoutType,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $payoutId = DB::table('customer_commission_payouts')->insertGetId($insertData);

                $this->allocatePayoutToCommissions($ctv, $payoutId, $amountCents, $paidDate, $paidBy);
            });

            return response()->json([
                'message' => 'Đã lưu thanh toán hoa hồng thành công.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage() ?: 'Không thể lưu thanh toán hoa hồng.',
            ], 422);
        }
    }

    public function history(int $ctv)
    {
        $ctvData = $this->getCtvData($ctv);
        $summary = $this->getCtvCommissionSummary($ctv);

        $selects = [
            'p.id',
            'p.payout_code',
            'p.total_amount',
            'p.paid_at',
            'p.note',
            'ps.name as status_name',
            'p.payment_method',
            'p.payout_type',
        ];

        $histories = DB::table('customer_commission_payouts as p')
            ->leftJoin('payout_statuses as ps', 'ps.id', '=', 'p.payout_status_id')
            ->where('p.referrer_customer_id', $ctv)
            ->select($selects)
            ->orderByDesc('p.paid_at')
            ->orderByDesc('p.id')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'payout_code' => $item->payout_code,
                    'total_amount' => (float) $item->total_amount,
                    'paid_at' => $item->paid_at,
                    'payment_method' => $item->payment_method ?: 'Chưa cập nhật',
                    'payout_type' => $item->payout_type ?: 'installment',
                    'payout_type_label' => $item->payout_type === 'all'
                        ? 'Thanh toán toàn bộ'
                        : 'Thanh toán chia theo đợt',
                    'note' => $item->note ?: '',
                    'status_name' => $item->status_name ?: '',
                ];
            });

        return response()->json([
            'ctv' => $ctvData,
            'summary' => $summary,
            'histories' => $histories,
        ]);
    }

    public function editHistory(int $ctv, int $payout)
    {
        $ctvData = $this->getCtvData($ctv);
        $summary = $this->getCtvCommissionSummary($ctv);

        $payoutData = DB::table('customer_commission_payouts')
            ->where('id', $payout)
            ->where('referrer_customer_id', $ctv)
            ->first();

        abort_if(!$payoutData, 404, 'Không tìm thấy lịch sử thanh toán.');

        return response()->json([
            'ctv' => $ctvData,
            'summary' => $summary,
            'max_edit_amount' => (float) Money::decimal(
                Money::cents($summary['total_debt']) + Money::cents($payoutData->total_amount)
            ),
            'payout' => [
                'id' => $payoutData->id,
                'total_amount' => (float) $payoutData->total_amount,
                'paid_at' => $payoutData->paid_at,
                'payment_method' => $payoutData->payment_method ?? 'Chuyển khoản',
                'payout_type' => $payoutData->payout_type ?? 'installment',
                'note' => $payoutData->note ?: '',
            ],
        ]);
    }

    public function updateHistory(Request $request, int $ctv, int $payout)
    {
        $validator = Validator::make($request->all(), [
            'payout_type' => ['required', 'in:all,installment'],
            'amount' => ['required', 'numeric', 'min:1000', 'regex:/^\d+(?:\.\d{1,2})?$/'],
            'paid_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'payout_type.required' => 'Vui lòng chọn hình thức thanh toán.',
            'amount.required' => 'Vui lòng nhập số tiền chi hoa hồng.',
            'amount.numeric' => 'Số tiền chi không hợp lệ.',
            'amount.min' => 'Số tiền chi tối thiểu là 1.000đ.',
            'paid_date.required' => 'Vui lòng chọn ngày chi.',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $this->getCtvData($ctv);

        $payoutType = $request->input('payout_type');
        $amountCents = Money::cents($request->input('amount'));
        $paidDate = Carbon::parse($request->input('paid_date'))->setTimeFrom(now());
        $paymentMethod = $request->input('payment_method');
        $note = $request->input('note');
        $paidBy = Auth::guard('admin')->id();

        try {
            DB::transaction(function () use ($ctv, $payout, $payoutType, &$amountCents, $paidDate, $paymentMethod, $note, $paidBy) {
                $payoutData = DB::table('customer_commission_payouts')
                    ->where('id', $payout)
                    ->where('referrer_customer_id', $ctv)
                    ->lockForUpdate()
                    ->first();

                if (!$payoutData) {
                    throw new \Exception('Không tìm thấy lịch sử thanh toán.');
                }

                $this->reversePayoutItems($payout);

                $totalDebtAfterReverseCents = $this->getCtvTotalDebtCents($ctv);

                if ($totalDebtAfterReverseCents <= 0) {
                    throw new \Exception('CTV này không còn hoa hồng cần chi.');
                }

                if ($payoutType === 'all') {
                    $amountCents = $totalDebtAfterReverseCents;
                }

                if ($amountCents > $totalDebtAfterReverseCents) {
                    throw new \Exception('Số tiền sửa không được lớn hơn hoa hồng còn nợ.');
                }

                $updateData = [
                    'total_amount' => Money::decimal($amountCents),
                    'paid_at' => $paidDate,
                    'paid_by' => $paidBy,
                    'note' => $note,
                    'payment_method' => $paymentMethod,
                    'payout_type' => $payoutType,
                    'updated_at' => now(),
                ];

                DB::table('customer_commission_payouts')
                    ->where('id', $payout)
                    ->update($updateData);

                $this->allocatePayoutToCommissions($ctv, $payout, $amountCents, $paidDate, $paidBy);
            });

            return response()->json([
                'message' => 'Đã cập nhật lịch sử thanh toán hoa hồng.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage() ?: 'Không thể cập nhật lịch sử thanh toán.',
            ], 422);
        }
    }

    private function allocatePayoutToCommissions(int $ctv, int $payoutId, int $amountCents, Carbon $paidDate, ?int $paidBy): void
    {
        $commissionStatusPaidId = $this->getCommissionStatusId('paid');
        $commissionStatusApprovedId = $this->getCommissionStatusId('approved');

        $commissions = DB::table('customer_commissions')
            ->whereNull('deleted_at')
            ->whereRaw('COALESCE(ctv_customer_id, referrer_customer_id) = ?', [$ctv])
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhereNotIn('status', ['paid', 'cancelled']);
            })
            ->whereRaw('GREATEST(commission_amount - paid_amount, 0) > 0')
            ->orderBy('commission_date')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $remainingCents = $amountCents;

        foreach ($commissions as $commission) {
            if ($remainingCents <= 0) {
                break;
            }

            $commissionCents = Money::cents($commission->commission_amount);
            $paidCents = Money::cents($commission->paid_amount);
            $debtCents = max($commissionCents - $paidCents, 0);

            if ($debtCents <= 0) {
                continue;
            }

            $payCents = min($debtCents, $remainingCents);
            $newPaidCents = $paidCents + $payCents;
            $isFullyPaid = $newPaidCents >= $commissionCents;

            DB::table('customer_commission_payout_items')->insert([
                'payout_id' => $payoutId,
                'customer_commission_id' => $commission->id,
                'amount' => Money::decimal($payCents),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('customer_commissions')
                ->where('id', $commission->id)
                ->update([
                    'paid_amount' => Money::decimal($newPaidCents),
                    'status' => $isFullyPaid ? 'paid' : 'partial',
                    'commission_status_id' => $isFullyPaid
                        ? $commissionStatusPaidId
                        : ($commissionStatusApprovedId ?: $commission->commission_status_id),
                    'paid_at' => $isFullyPaid ? $paidDate : null,
                    'paid_by' => $isFullyPaid ? $paidBy : null,
                    'updated_at' => now(),
                ]);

            $remainingCents -= $payCents;
        }

        if ($remainingCents !== 0) {
            throw new \Exception('Không đủ hoa hồng còn nợ để phân bổ thanh toán.');
        }
    }

    private function reversePayoutItems(int $payoutId): void
    {
        $items = DB::table('customer_commission_payout_items')
            ->where('payout_id', $payoutId)
            ->lockForUpdate()
            ->get();

        DB::table('customer_commission_payout_items')
            ->where('payout_id', $payoutId)
            ->delete();

        $commissionStatusPaidId = $this->getCommissionStatusId('paid');
        $commissionStatusApprovedId = $this->getCommissionStatusId('approved');

        foreach ($items as $item) {
            $commission = DB::table('customer_commissions')
                ->where('id', $item->customer_commission_id)
                ->lockForUpdate()
                ->first();

            if (!$commission) {
                continue;
            }

            $commissionCents = Money::cents($commission->commission_amount);
            $newPaidCents = max(
                Money::cents($commission->paid_amount) - Money::cents($item->amount),
                0
            );
            $isFullyPaid = $newPaidCents >= $commissionCents;
            $isPartial = $newPaidCents > 0 && !$isFullyPaid;

            DB::table('customer_commissions')
                ->where('id', $commission->id)
                ->update([
                    'paid_amount' => Money::decimal($newPaidCents),
                    'status' => $isFullyPaid ? 'paid' : ($isPartial ? 'partial' : 'unpaid'),
                    'commission_status_id' => $isFullyPaid
                        ? $commissionStatusPaidId
                        : ($commissionStatusApprovedId ?: $commission->commission_status_id),
                    'paid_at' => $isFullyPaid ? $this->getLatestPaidAtForCommission($commission->id) : null,
                    'paid_by' => $isFullyPaid ? $this->getLatestPaidByForCommission($commission->id) : null,
                    'updated_at' => now(),
                ]);
        }
    }

    private function getCtvData(int $ctvId): array
    {
        $ctv = DB::table('customers as ctv')
            ->leftJoin('customer_details as cd', 'cd.customer_id', '=', 'ctv.id')
            ->where('ctv.id', $ctvId)
            ->selectRaw("
                ctv.id,
                ctv.full_name,
                ctv.phone,
                COALESCE(ctv.commission_rate, 0) AS commission_rate,
                COALESCE(
                    NULLIF(
                        TRIM(BOTH ', ' FROM CONCAT_WS(', ',
                            NULLIF(cd.address, ''),
                            NULLIF(cd.ward, ''),
                            NULLIF(cd.district, ''),
                            NULLIF(cd.province, '')
                        )),
                        ''
                    ),
                    'Chưa cập nhật'
                ) AS full_address
            ")
            ->first();

        abort_if(!$ctv, 404, 'Không tìm thấy khách hàng/CTV.');

        return [
            'id' => $ctv->id,
            'full_name' => $ctv->full_name,
            'phone' => $ctv->phone,
            'commission_rate' => (float) $ctv->commission_rate,
            'full_address' => $ctv->full_address,
        ];
    }

    private function getCtvCommissionSummary(int $ctvId): array
    {
        $summary = DB::table('customer_commissions')
            ->whereNull('deleted_at')
            ->whereRaw('COALESCE(ctv_customer_id, referrer_customer_id) = ?', [$ctvId])
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '<>', 'cancelled');
            })
            ->selectRaw('
                COALESCE(SUM(commission_amount), 0) AS total_commission,
                COALESCE(SUM(paid_amount), 0) AS total_paid,
                COALESCE(SUM(GREATEST(commission_amount - paid_amount, 0)), 0) AS total_debt
            ')
            ->first();

        return [
            'total_commission' => (float) ($summary->total_commission ?? 0),
            'total_paid' => (float) ($summary->total_paid ?? 0),
            'total_debt' => (float) ($summary->total_debt ?? 0),
        ];
    }

    private function getCtvTotalDebtCents(int $ctvId): int
    {
        $summary = $this->getCtvCommissionSummary($ctvId);

        return Money::cents($summary['total_debt']);
    }

    private function getCommissionStatusId(string $code): ?int
    {
        return DB::table('commission_statuses')
            ->where('code', $code)
            ->value('id');
    }

    private function getPayoutStatusId(string $code): ?int
    {
        return DB::table('payout_statuses')
            ->where('code', $code)
            ->value('id');
    }

    private function getLatestPaidAtForCommission(int $commissionId)
    {
        $latest = DB::table('customer_commission_payout_items as pi')
            ->join('customer_commission_payouts as p', 'p.id', '=', 'pi.payout_id')
            ->where('pi.customer_commission_id', $commissionId)
            ->orderByDesc('p.paid_at')
            ->orderByDesc('p.id')
            ->select('p.paid_at')
            ->first();

        return $latest->paid_at ?? null;
    }

    private function getLatestPaidByForCommission(int $commissionId)
    {
        $latest = DB::table('customer_commission_payout_items as pi')
            ->join('customer_commission_payouts as p', 'p.id', '=', 'pi.payout_id')
            ->where('pi.customer_commission_id', $commissionId)
            ->orderByDesc('p.paid_at')
            ->orderByDesc('p.id')
            ->select('p.paid_by')
            ->first();

        return $latest->paid_by ?? null;
    }

    private function makePayoutCode(): string
    {
        do {
            $code = 'CHH' . now()->format('ymdHis') . Str::upper(Str::random(4));
        } while (
            DB::table('customer_commission_payouts')
            ->where('payout_code', $code)
            ->exists()
        );

        return $code;
    }
}
