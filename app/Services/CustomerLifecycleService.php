<?php

namespace App\Services;

use App\Models\CtvStatus;
use App\Models\Customer;
use App\Models\CustomerRole;
use App\Models\CustomerStatus;
use App\Models\CustomerStopReason;
use RuntimeException;

class CustomerLifecycleService
{
    public function findByPhone(?string $input): ?Customer
    {
        $phone = $this->normalizePhone($input);
        if ($phone === null) {
            return null;
        }

        return Customer::query()->with(['role', 'ctvStatus'])->where('phone', $phone)->first();
    }

    public function convertToCtv(Customer $customer, ?int $adminId): bool
    {
        if ($customer->currentRole()?->code === 'ctv') {
            return false;
        }

        $roleId = $this->lookupId(CustomerRole::class, ['ctv', 'cong_tac_vien', 'collaborator']);
        if ($roleId === null) {
            throw new RuntimeException('Chưa có vai trò CTV trong hệ thống.');
        }

        $ctvStatusId = $this->lookupId(CtvStatus::class, ['active', 'dang_hoat_dong', 'hoat_dong']);
        $customerStatusId = $this->lookupId(CustomerStatus::class, ['active', 'dang_hoat_dong', 'hoat_dong', 'new', 'moi']);
        $customer->update([
            'customer_role_id' => $roleId,
            'ctv_status_id' => $ctvStatusId,
            'customer_status_id' => $customerStatusId ?? $customer->customer_status_id,
            'commission_rate' => $customer->commission_rate ?? 5,
            'ctv_approved_by' => $adminId,
            'ctv_approved_at' => now(),
            'stopped_reason' => null,
            'stopped_at' => null,
            'updated_by' => $adminId,
        ]);

        return true;
    }

    public function markStopped(Customer $customer, int $reasonId, ?string $note, ?int $adminId): void
    {
        $reason = CustomerStopReason::query()->where('is_active', true)->find($reasonId);
        if ($reason === null) {
            throw new RuntimeException('Lý do ngưng mua không hợp lệ hoặc đã bị tắt.');
        }
        $statusId = $this->lookupId(CustomerStatus::class, ['stopped_buying', 'ngung_mua', 'stop_buying', 'inactive']);
        $note = trim((string) $note);
        $customer->update([
            'customer_status_id' => $statusId ?? $customer->customer_status_id,
            'stopped_reason' => $note === '' ? "Lý do: {$reason->name}" : "Lý do: {$reason->name}\nGhi chú: {$note}",
            'stopped_at' => now(),
            'updated_by' => $adminId,
        ]);
    }

    private function normalizePhone(?string $input): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $input);
        if ($digits === null || $digits === '') {
            return null;
        }
        if (str_starts_with($digits, '84')) {
            $digits = '0'.substr($digits, 2);
        }

        return preg_match('/^0\d{9,10}$/', $digits) === 1 ? $digits : null;
    }

    private function lookupId(string $model, array $codes): ?int
    {
        $id = $model::query()->whereIn('code', $codes)->orderByRaw('FIELD(code, '.implode(',', array_fill(0, count($codes), '?')).')', $codes)->value('id');

        return $id === null ? null : (int) $id;
    }
}
