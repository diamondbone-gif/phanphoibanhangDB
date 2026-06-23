<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CtvController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim((string) $request->query('keyword', ''));
        $keyword = mb_substr($keyword, 0, 100);

        $ctvStatus = (string) $request->query('ctv_status', '');

        $ctvStatuses = DB::table('ctv_statuses')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $validCtvStatusIds = $ctvStatuses->pluck('id')->map(fn($id) => (string) $id)->toArray();

        if (!in_array($ctvStatus, $validCtvStatusIds, true)) {
            $ctvStatus = '';
        }

        $ctvs = Customer::query()
            ->with([
                'role',
                'status',
                'ctvStatus',
            ])
            ->withCount([
                'givenReferrals as referred_customers_count',
            ])
            ->whereHas('role', function ($query) {
                $query->where('code', 'ctv');
            })
            ->when($keyword !== '', function ($query) use ($keyword) {
                $safeKeyword = $this->escapeLike($keyword);
                $phoneKeyword = preg_replace('/\D+/', '', $keyword);

                $query->where(function ($q) use ($safeKeyword, $phoneKeyword) {
                    $q->where('full_name', 'like', "%{$safeKeyword}%")
                        ->orWhere('customer_code', 'like', "%{$safeKeyword}%")
                        ->orWhere('phone', 'like', "%{$safeKeyword}%");

                    if ($phoneKeyword !== '') {
                        $q->orWhere('phone', 'like', "%{$phoneKeyword}%")
                            ->orWhere('customer_code', 'like', "%{$phoneKeyword}%");
                    }
                });
            })
            ->when($ctvStatus !== '', function ($query) use ($ctvStatus) {
                $query->where('ctv_status_id', (int) $ctvStatus);
            })
            ->latest('ctv_approved_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $ctvIds = $ctvs->getCollection()->pluck('id')->toArray();

        $commissionTotals = collect();

        if (!empty($ctvIds)) {
            $commissionTotals = DB::table('customer_commissions')
                ->whereIn('referrer_customer_id', $ctvIds)
                ->selectRaw('referrer_customer_id, SUM(commission_amount) as total_commission')
                ->groupBy('referrer_customer_id')
                ->pluck('total_commission', 'referrer_customer_id');
        }

        return view('admin.auth.ctvs.index', compact(
            'ctvs',
            'ctvStatuses',
            'commissionTotals'
        ));
    }

    public function show(Customer $customer)
    {
        abort_unless($customer->role?->code === 'ctv', 404);

        $customer->load([
            'type',
            'role',
            'status',
            'ctvStatus',
            'detail.sourceChannel',
            'givenReferrals.referred',
        ])->loadCount([
            'givenReferrals as referred_customers_count',
            'orders',
        ]);

        $referredCustomerIds = DB::table('customer_referrals')
            ->where('referrer_customer_id', $customer->id)
            ->whereNull('ended_at')
            ->pluck('referred_customer_id')
            ->filter()
            ->unique()
            ->values();

        $totalRevenueFromReferred = 0;
        $totalCommission = 0;
        $pendingCommission = 0;

        if ($referredCustomerIds->isNotEmpty()) {
            $totalRevenueFromReferred = DB::table('customer_orders')
                ->whereIn('customer_id', $referredCustomerIds)
                ->sum('total_amount');
        }

        $totalCommission = DB::table('customer_commissions')
            ->where('referrer_customer_id', $customer->id)
            ->sum('commission_amount');

        $pendingCommission = DB::table('customer_commissions')
            ->where('referrer_customer_id', $customer->id)
            ->whereNull('paid_at')
            ->sum('commission_amount');

        $paymentHistories = DB::table('customer_commissions as cc')
            ->leftJoin('operation_managers as om', 'cc.approved_by', '=', 'om.id')
            ->where('cc.referrer_customer_id', $customer->id)
            ->whereNotNull('cc.paid_at')
            ->select([
                'cc.id',
                'cc.commission_amount',
                'cc.paid_at',
                'cc.approved_at',
                'om.name as approved_by_name',
            ])
            ->orderByDesc('cc.paid_at')
            ->limit(10)
            ->get();

        $referredCustomers = DB::table('customer_referrals as cr')
            ->join('customers as c', 'cr.referred_customer_id', '=', 'c.id')
            ->leftJoin(DB::raw('
                (
                    SELECT customer_id, COUNT(*) as order_count, SUM(total_amount) as total_order_amount
                    FROM customer_orders
                    GROUP BY customer_id
                ) as order_summary
            '), 'c.id', '=', 'order_summary.customer_id')
            ->leftJoin(DB::raw('
                (
                    SELECT referred_customer_id, SUM(commission_amount) as total_commission
                    FROM customer_commissions
                    WHERE referrer_customer_id = ' . (int) $customer->id . '
                    GROUP BY referred_customer_id
                ) as commission_summary
            '), 'c.id', '=', 'commission_summary.referred_customer_id')
            ->where('cr.referrer_customer_id', $customer->id)
            ->whereNull('cr.ended_at')
            ->select([
                'cr.id as referral_id',
                'cr.started_at',
                'c.id as customer_id',
                'c.full_name',
                'c.phone',
                'c.customer_code',
                DB::raw('COALESCE(order_summary.order_count, 0) as order_count'),
                DB::raw('COALESCE(order_summary.total_order_amount, 0) as total_order_amount'),
                DB::raw('COALESCE(commission_summary.total_commission, 0) as total_commission'),
            ])
            ->orderByDesc('cr.started_at')
            ->orderByDesc('cr.id')
            ->get();

        return view('admin.auth.ctvs.show', compact(
            'customer',
            'totalRevenueFromReferred',
            'totalCommission',
            'pendingCommission',
            'paymentHistories',
            'referredCustomers'
        ));
    }

    public function referredShow(Customer $ctv, Customer $referred)
    {
        abort_unless($ctv->role?->code === 'ctv', 404);

        $referral = DB::table('customer_referrals')
            ->where('referrer_customer_id', $ctv->id)
            ->where('referred_customer_id', $referred->id)
            ->whereNull('ended_at')
            ->first();

        abort_unless($referral, 404);

        $referred->load([
            'type',
            'role',
            'status',
            'ctvStatus',
            'detail.sourceChannel',
        ])->loadCount('orders');

        $orders = DB::table('customer_orders')
            ->where('customer_id', $referred->id)
            ->orderByDesc('order_date')
            ->orderByDesc('id')
            ->get();

        $commissions = DB::table('customer_commissions')
            ->where('referrer_customer_id', $ctv->id)
            ->where('referred_customer_id', $referred->id)
            ->orderByDesc('id')
            ->get();

        $totalOrderAmount = $orders->sum('total_amount');
        $totalCommission = $commissions->sum('commission_amount');

        return view('admin.auth.ctvs.referred-show', compact(
            'ctv',
            'referred',
            'referral',
            'orders',
            'commissions',
            'totalOrderAmount',
            'totalCommission'
        ));
    }

    private function escapeLike(string $value): string
    {
        return str_replace(
            ['\\', '%', '_'],
            ['\\\\', '\%', '\_'],
            $value
        );
    }
}
