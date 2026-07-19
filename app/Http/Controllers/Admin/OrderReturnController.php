<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrderReturn;
use Illuminate\Http\Request;

class OrderReturnController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->string('type')->toString();
        $status = $request->string('status')->toString();
        $keyword = trim($request->string('keyword')->toString());

        $returns = CustomerOrderReturn::query()
            ->with(['order.customer', 'items.orderItem', 'creator', 'refundTransaction'])
            ->when(in_array($type, ['refund', 'exchange', 'mixed'], true), fn ($query) => $query->where('resolution_type', $type))
            ->when(in_array($status, ['completed', 'pending_exchange'], true), fn ($query) => $query->where('resolution_status', $status))
            ->when($keyword !== '', function ($query) use ($keyword): void {
                $query->where(function ($query) use ($keyword): void {
                    $query->where('return_code', 'like', "%{$keyword}%")
                        ->orWhereHas('order', fn ($order) => $order->where('order_code', 'like', "%{$keyword}%")
                            ->orWhereHas('customer', fn ($customer) => $customer->where('full_name', 'like', "%{$keyword}%")->orWhere('phone', 'like', "%{$keyword}%")));
                });
            })
            ->latest('id')->paginate(20)->withQueryString();

        $summary = CustomerOrderReturn::query()->selectRaw(
            'COUNT(*) total_count, COALESCE(SUM(refund_amount),0) returned_value, COALESCE(SUM(cash_refund_amount),0) cash_refund, COALESCE(SUM(exchange_credit_amount),0) exchange_credit'
        )->first();

        return view('admin.auth.orders.returns', compact('returns', 'summary', 'type', 'status', 'keyword'));
    }

    public function completeExchange(Request $request, CustomerOrderReturn $orderReturn)
    {
        abort_unless($orderReturn->resolution_status === 'pending_exchange', 422, 'Phiếu này không còn chờ đổi hàng.');
        $data = $request->validate(['completion_note' => ['nullable', 'string', 'max:1000']]);
        $completionNote = trim((string) ($data['completion_note'] ?? ''));

        $orderReturn->update([
            'resolution_status' => 'completed',
            'resolution_completed_at' => now(),
            'resolution_completed_by' => auth('admin')->id(),
            'exchange_note' => trim(($orderReturn->exchange_note ?? '').($completionNote !== '' ? "\nĐã giao: {$completionNote}" : '')),
        ]);

        return back()->with('success', 'Đã xác nhận giao sản phẩm đổi cho khách.');
    }
}
