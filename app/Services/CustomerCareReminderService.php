<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerCareLog;
use App\Models\CustomerCareReminder;
use Illuminate\Support\Facades\DB;

class CustomerCareReminderService
{
    public function create(int $customerId, array $data, ?int $adminId): CustomerCareReminder
    {
        Customer::query()->findOrFail($customerId);

        return CustomerCareReminder::query()->create([
            'customer_id' => $customerId,
            'care_log_id' => null,
            'assigned_staff_id' => ($data['assigned_staff_id'] ?? null) ?: $adminId,
            'reminder_date' => $data['reminder_date'],
            'reminder_time' => $data['reminder_time'],
            'content' => trim((string) $data['content']),
            'care_priority_id' => ($data['care_priority_id'] ?? null) ?: $this->lookupId('care_priorities', 'normal'),
            'care_status_id' => ($data['care_status_id'] ?? null) ?: $this->statusId('pending'),
            'completed_at' => null,
            'notified_at' => null,
            'snoozed_until' => null,
        ]);
    }

    public function complete(int $reminderId, ?string $note, ?int $adminId): CustomerCareReminder
    {
        return DB::transaction(function () use ($reminderId, $note, $adminId) {
            $reminder = CustomerCareReminder::query()->lockForUpdate()->findOrFail($reminderId);
            if ($reminder->completed_at !== null) {
                return $reminder;
            }

            $completedStatusId = $this->statusId('completed');
            $reminder->update([
                'care_status_id' => $completedStatusId ?? $reminder->care_status_id,
                'completed_at' => now(),
                'notified_at' => now(),
                'snoozed_until' => null,
            ]);

            CustomerCareLog::query()->create([
                'log_type' => 'system',
                'customer_id' => $reminder->customer_id,
                'staff_id' => $adminId ?? $reminder->assigned_staff_id,
                'care_channel_id' => null,
                'care_date' => now(),
                'content' => $note ?: 'Đã hoàn thành lịch chăm sóc: '.$reminder->content,
                'internal_note' => "Hệ thống ghi nhận từ lịch nhắc #{$reminder->id}. Nội dung lịch nhắc: {$reminder->content}",
                'next_follow_up_at' => null,
                'care_priority_id' => $reminder->care_priority_id,
                'care_status_id' => $completedStatusId,
            ]);

            return $reminder->fresh();
        });
    }

    public function reopen(int $reminderId): CustomerCareReminder
    {
        $reminder = CustomerCareReminder::query()->findOrFail($reminderId);
        $reminder->update([
            'care_status_id' => $this->statusId('pending'),
            'completed_at' => null,
            'notified_at' => null,
            'snoozed_until' => null,
        ]);

        return $reminder;
    }

    public function delete(int $reminderId): void
    {
        CustomerCareReminder::query()->findOrFail($reminderId)->delete();
    }

    private function statusId(string $code): ?int
    {
        return $this->lookupId('care_statuses', $code);
    }

    private function lookupId(string $table, string $code): ?int
    {
        $id = DB::table($table)->where('code', $code)->value('id');

        return $id === null ? null : (int) $id;
    }
}
