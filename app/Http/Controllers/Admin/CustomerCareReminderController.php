<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerCareReminderRequest;
use App\Services\CustomerCareReminderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerCareReminderController extends Controller
{
    public function __construct(private CustomerCareReminderService $reminders) {}

    public function store(StoreCustomerCareReminderRequest $request, int $customerId): RedirectResponse
    {
        $this->reminders->create($customerId, $request->validated(), auth('admin')->id());

        return redirect()->route('admin.customer-care.show', compact('customerId'))->with('success', 'Đã tạo lịch nhắc chăm sóc.');
    }

    public function complete(Request $request, int $reminderId): RedirectResponse
    {
        $data = $request->validate(['completion_note' => ['nullable', 'string', 'max:10000']]);
        $this->reminders->complete(
            $reminderId,
            isset($data['completion_note']) ? trim($data['completion_note']) : null,
            auth('admin')->id(),
        );

        return back()->with('success', 'Đã đánh dấu lịch chăm sóc hoàn thành.');
    }

    public function reopen(int $reminderId): RedirectResponse
    {
        $this->reminders->reopen($reminderId);

        return back()->with('success', 'Đã mở lại lịch chăm sóc.');
    }

    public function destroy(int $reminderId): RedirectResponse
    {
        $this->reminders->delete($reminderId);

        return back()->with('success', 'Đã xóa lịch nhắc chăm sóc.');
    }
}
