<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MarkCustomerStoppedRequest;
use App\Models\Customer;
use App\Services\CustomerLifecycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerLifecycleController extends Controller
{
    public function __construct(private CustomerLifecycleService $customers) {}

    public function checkReferrer(Request $request): JsonResponse
    {
        $customer = $this->customers->findByPhone($request->input('phone', $request->input('referrer_phone')));
        if ($customer === null) {
            return response()->json(['found' => false, 'success' => false, 'message' => 'Không tìm thấy khách hàng/người giới thiệu với số điện thoại này.']);
        }

        $role = $customer->currentRole();
        $ctvStatus = $customer->currentCtvStatus();

        return response()->json([
            'found' => true, 'success' => true, 'id' => $customer->id,
            'full_name' => $customer->full_name, 'phone' => $customer->phone,
            'commission_rate' => $customer->commission_rate ?? 5,
            'message' => "Đã tìm thấy: {$customer->full_name} - {$customer->phone}",
            'data' => [
                'id' => $customer->id, 'customer_code' => $customer->customer_code ?? '',
                'full_name' => $customer->full_name, 'phone' => $customer->phone,
                'commission_rate' => $customer->commission_rate ?? 5,
                'role_code' => $role?->code, 'role_name' => $role?->name,
                'ctv_status_code' => $ctvStatus?->code, 'ctv_status_name' => $ctvStatus?->name,
            ],
        ]);
    }

    public function convertToCtv(Customer $customer): RedirectResponse
    {
        try {
            $changed = $this->customers->convertToCtv($customer, auth('admin')->id());

            return back()->with('success', $changed ? 'Đã chuyển khách hàng thành CTV thành công.' : 'Khách hàng này đã là CTV.');
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function markStopped(MarkCustomerStoppedRequest $request, Customer $customer): RedirectResponse
    {
        try {
            $this->customers->markStopped(
                $customer,
                (int) $request->validated('customer_stop_reason_id'),
                $request->validated('stopped_reason_note'),
                auth('admin')->id(),
            );

            return back()->with('success', 'Đã đánh dấu khách hàng ngưng mua.');
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }
}
