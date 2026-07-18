<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveCustomerCareLogRequest;
use App\Services\CustomerCareLogService;
use Illuminate\Http\RedirectResponse;

class CustomerCareLogController extends Controller
{
    public function __construct(private CustomerCareLogService $logs) {}

    public function store(SaveCustomerCareLogRequest $request, int $customerId): RedirectResponse
    {
        $this->logs->create($customerId, $request->validated(), auth('admin')->id());

        return redirect()->route('admin.customer-care.show', compact('customerId'))->with('success', 'Đã lưu nội dung tư vấn cho khách hàng.');
    }

    public function update(SaveCustomerCareLogRequest $request, int $logId): RedirectResponse
    {
        try {
            $log = $this->logs->update($logId, $request->validated(), auth('admin')->id());

            return redirect()->route('admin.customer-care.show', ['customerId' => $log->customer_id])->with('success', 'Đã cập nhật nội dung tư vấn.');
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function destroy(int $logId): RedirectResponse
    {
        try {
            $customerId = $this->logs->delete($logId);

            return redirect()->route('admin.customer-care.show', compact('customerId'))->with('success', 'Đã xóa nội dung tư vấn.');
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }
}
