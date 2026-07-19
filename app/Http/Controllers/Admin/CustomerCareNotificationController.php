<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CustomerCareNotificationService;
use Illuminate\Http\JsonResponse;

class CustomerCareNotificationController extends Controller
{
    public function __construct(private CustomerCareNotificationService $notifications) {}

    public function due(): JsonResponse
    {
        $items = $this->notifications->dueFor(auth('admin')->id());

        return response()->json([
            'success' => true,
            'server_time' => now()->format('d/m/Y H:i:s'),
            'count' => $items->count(),
            'items' => $items,
            'reminders' => $items,
        ]);
    }

    public function acknowledge(int $reminderId): JsonResponse
    {
        $this->notifications->acknowledge($reminderId);

        return response()->json(['success' => true, 'message' => 'Đã xác nhận thông báo.']);
    }

    public function snooze(int $reminderId): JsonResponse
    {
        $this->notifications->snooze($reminderId);

        return response()->json(['success' => true, 'message' => 'Lịch sẽ được nhắc lại sau 10 phút.']);
    }
}
