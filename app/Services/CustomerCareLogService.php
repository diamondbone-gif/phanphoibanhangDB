<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerCareLog;
use App\Models\CustomerCareReminder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CustomerCareLogService
{
    public function create(int $customerId, array $data, ?int $adminId): CustomerCareLog
    {
        Customer::query()->findOrFail($customerId);

        return DB::transaction(function () use ($customerId, $data, $adminId) {
            $log = CustomerCareLog::query()->create($this->payload($data, $adminId) + [
                'log_type' => 'consultation',
                'customer_id' => $customerId,
            ]);
            $this->syncReminder($log, $adminId);

            return $log;
        });
    }

    public function update(int $logId, array $data, ?int $adminId): CustomerCareLog
    {
        return DB::transaction(function () use ($logId, $data, $adminId) {
            $log = CustomerCareLog::query()->lockForUpdate()->findOrFail($logId);
            if (! in_array($log->getRawOriginal('log_type'), [null, 'consultation'], true)) {
                throw new RuntimeException('Nhật ký hệ thống không được phép sửa như nội dung tư vấn.');
            }
            $log->update($this->payload($data, $adminId) + ['log_type' => 'consultation']);
            $this->syncReminder($log->fresh(), $adminId);

            return $log->fresh();
        });
    }

    public function delete(int $logId): int
    {
        return DB::transaction(function () use ($logId) {
            $log = CustomerCareLog::query()->lockForUpdate()->findOrFail($logId);
            if (! in_array($log->getRawOriginal('log_type'), [null, 'consultation'], true)) {
                throw new RuntimeException('Không thể xóa nhật ký hệ thống tại chức năng này.');
            }
            $customerId = (int) $log->customer_id;
            CustomerCareReminder::query()->where('care_log_id', $log->id)->whereNull('completed_at')->delete();
            CustomerCareReminder::query()->where('care_log_id', $log->id)->whereNotNull('completed_at')->update(['care_log_id' => null]);
            $log->delete();

            return $customerId;
        });
    }

    private function payload(array $data, ?int $adminId): array
    {
        return [
            'staff_id' => ($data['staff_id'] ?? null) ?: $adminId,
            'care_channel_id' => $data['care_channel_id'] ?? null,
            'care_date' => $data['care_date'],
            'content' => trim((string) $data['content']),
            'internal_note' => $this->nullableTrim($data['internal_note'] ?? null),
            'next_follow_up_at' => $data['next_follow_up_at'] ?? null,
            'care_priority_id' => $data['care_priority_id'] ?? null,
            'care_status_id' => ($data['care_status_id'] ?? null) ?: $this->lookupId('care_statuses', 'completed'),
        ];
    }

    private function syncReminder(CustomerCareLog $log, ?int $adminId): void
    {
        $reminder = CustomerCareReminder::query()->where('care_log_id', $log->id)->whereNull('completed_at')->first();
        if ($log->next_follow_up_at === null) {
            $reminder?->delete();

            return;
        }
        $moment = Carbon::parse($log->next_follow_up_at);
        $payload = [
            'customer_id' => $log->customer_id,
            'care_log_id' => $log->id,
            'assigned_staff_id' => $log->staff_id ?: $adminId,
            'reminder_date' => $moment->format('Y-m-d'),
            'reminder_time' => $moment->format('H:i:s'),
            'content' => 'Liên hệ lại sau tư vấn: '.mb_substr((string) $log->content, 0, 900),
            'care_priority_id' => $log->care_priority_id ?: $this->lookupId('care_priorities', 'normal'),
            'care_status_id' => $this->lookupId('care_statuses', 'pending'),
            'completed_at' => null,
            'notified_at' => null,
            'snoozed_until' => null,
        ];
        $reminder ? $reminder->update($payload) : CustomerCareReminder::query()->create($payload);
    }

    private function lookupId(string $table, string $code): ?int
    {
        $id = DB::table($table)->where('code', $code)->value('id');

        return $id === null ? null : (int) $id;
    }

    private function nullableTrim(?string $value): ?string
    {
        $value = $value === null ? null : trim($value);

        return $value === '' ? null : $value;
    }
}
