<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerCommission;
use App\Services\CommissionService;
use Illuminate\Http\Request;

class CustomerCommissionController extends Controller
{
    public function __construct(
        private CommissionService $commissionService
    ) {}

    /*
    |--------------------------------------------------------------------------
    | DANH SÁCH HOA HỒNG CTV
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $status = $request->get('status');
        $keyword = trim((string) $request->get('keyword'));

        $query = CustomerCommission::query()
            ->with(['order', 'ctvCustomer', 'referredCustomer'])
            ->latest('id');

        if (in_array($status, ['unpaid', 'paid', 'cancelled'], true)) {
            $query->where('status', $status);
        }

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('commission_code', 'like', "%{$keyword}%")
                    ->orWhere('order_code', 'like', "%{$keyword}%");
            });
        }

        $commissions = $query->paginate(20)->withQueryString();

        $summary = [
            'unpaid_total' => (float) CustomerCommission::query()
                ->where('status', 'unpaid')
                ->sum('commission_amount'),

            'paid_total' => (float) CustomerCommission::query()
                ->where('status', 'paid')
                ->sum('paid_amount'),

            'cancelled_total' => (float) CustomerCommission::query()
                ->where('status', 'cancelled')
                ->sum('commission_amount'),
        ];

        return view('admin.commissions.index', compact(
            'commissions',
            'summary',
            'status',
            'keyword'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | ĐÁNH DẤU ĐÃ THANH TOÁN
    |--------------------------------------------------------------------------
    */
    public function markPaid(Request $request, CustomerCommission $commission)
    {
        try {
            $adminId = auth('admin')->id();

            $this->commissionService->markPaid(
                commissionId: $commission->id,
                adminId: $adminId,
                note: $request->input('note')
            );

            return back()->with('success', 'Đã cập nhật hoa hồng sang trạng thái đã thanh toán.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CHUYỂN VỀ CHƯA THANH TOÁN
    |--------------------------------------------------------------------------
    */
    public function markUnpaid(CustomerCommission $commission)
    {
        try {
            $this->commissionService->markUnpaid($commission->id);

            return back()->with('success', 'Đã chuyển hoa hồng về trạng thái chưa thanh toán.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
