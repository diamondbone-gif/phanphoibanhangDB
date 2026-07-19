<?php

namespace App\Services;

use App\Models\CustomerCareReminder;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CustomerCareNotificationService
{
    public function dueFor(?int $staffId, int $limit = 20): Collection
    {
        $momentSql = $this->momentSql('reminder');
        $query = DB::table('customer_care_reminders as reminder')
            ->join('customers as customer', 'customer.id', '=', 'reminder.customer_id')
            ->leftJoin('customer_details as detail', 'detail.customer_id', '=', 'customer.id')
            ->leftJoin('care_statuses as status', 'status.id', '=', 'reminder.care_status_id')
            ->leftJoin('care_priorities as priority', 'priority.id', '=', 'reminder.care_priority_id')
            ->leftJoin('operation_managers as staff', 'staff.id', '=', 'reminder.assigned_staff_id')
            ->whereNull('reminder.completed_at')
            ->where(fn ($q) => $q->whereNull('status.code')->orWhereNotIn('status.code', ['completed', 'cancelled']))
            ->where(fn ($q) => $q->whereNull('reminder.notified_at')->orWhere('reminder.snoozed_until', '<=', now()))
            ->whereRaw($momentSql.' <= ?', [now()->format('Y-m-d H:i:s')])
            ->select([
                'reminder.id', 'reminder.customer_id', 'reminder.content',
                'reminder.reminder_date', 'reminder.reminder_time', 'reminder.notified_at', 'reminder.snoozed_until',
                'customer.full_name', 'customer.phone', 'customer.note as customer_note',
                'detail.address', 'detail.ward', 'detail.district', 'detail.province', 'detail.consultation_note',
                'staff.name as staff_name', 'priority.name as priority_name', 'status.code as status_code',
            ])
            ->selectRaw($momentSql.' as reminder_at');

        if ($staffId !== null) {
            $query->where(fn ($q) => $q->whereNull('reminder.assigned_staff_id')->orWhere('reminder.assigned_staff_id', $staffId));
        }

        return $query->orderByRaw($momentSql.' ASC')->limit($limit)->get()->map(function (object $row): array {
            $address = implode(', ', array_filter([
                $row->address, $row->ward, $row->district, $row->province,
            ], fn ($value) => $value !== null && trim((string) $value) !== ''));
            $display = Carbon::parse($row->reminder_at)->format('d/m/Y H:i');

            return [
                'id' => $row->id,
                'customer_id' => $row->customer_id,
                'customer_name' => $row->full_name,
                'full_name' => $row->full_name,
                'phone' => $row->phone,
                'address' => $address ?: 'Chưa cập nhật địa chỉ',
                'content' => $row->content ?: 'Không có nội dung ghi chú',
                'customer_note' => $row->customer_note ?: 'Không có ghi chú khách hàng',
                'consultation_note' => $row->consultation_note ?: 'Không có ghi chú tư vấn',
                'priority_name' => $row->priority_name ?: 'Bình thường',
                'staff_name' => $row->staff_name ?: 'Chưa phân công',
                'reminder_date' => $row->reminder_date,
                'reminder_time' => $row->reminder_time,
                'reminder_at' => $display,
                'reminder_at_display' => $display,
                'notified_at' => $row->notified_at,
                'snoozed_until' => $row->snoozed_until,
                'status_code' => $row->status_code,
                'customer_url' => route('admin.customer-care.show', ['customerId' => $row->customer_id]),
                'complete_url' => route('admin.customer-care.reminders.complete', ['reminderId' => $row->id]),
            ];
        })->values();
    }

    public function acknowledge(int $reminderId): CustomerCareReminder
    {
        $reminder = CustomerCareReminder::query()->findOrFail($reminderId);
        $reminder->update(['notified_at' => now(), 'snoozed_until' => null]);

        return $reminder;
    }

    public function snooze(int $reminderId, int $minutes = 10): CustomerCareReminder
    {
        $reminder = CustomerCareReminder::query()->findOrFail($reminderId);
        $reminder->update(['snoozed_until' => now()->addMinutes($minutes), 'notified_at' => now()]);

        return $reminder;
    }

    private function momentSql(string $alias): string
    {
        return "COALESCE({$alias}.snoozed_until, CONCAT({$alias}.reminder_date, ' ', COALESCE({$alias}.reminder_time, '00:00:00')))";
    }
}
