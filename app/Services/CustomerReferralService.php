<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\ReferralStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class CustomerReferralService
{
    public function sync(Customer $customer, Request|array $input): void
    {
        $isRequest = $input instanceof Request;
        $kind = $isRequest
            ? $input->input('customer_kind')
            : (($input['customer_source'] ?? '') === 'ctv_referral' ? 'ctv' : 'self');

        if ($kind === 'self') {
            DB::transaction(function () use ($customer): void {
                if (Schema::hasColumn('customers', 'referrer_id')) {
                    $customer->update(['referrer_id' => null]);
                }

                if (Schema::hasTable('customer_referrals') && Schema::hasColumn('customer_referrals', 'referred_customer_id')) {
                    DB::table('customer_referrals')->where('referred_customer_id', $customer->id)->delete();
                }
            });

            return;
        }

        $phone = $this->normalizePhone($isRequest ? $input->input('referrer_phone') : ($input['referrer_phone'] ?? null));
        if (! $phone) {
            throw ValidationException::withMessages(['referrer_phone' => 'Vui lòng nhập số điện thoại người giới thiệu.']);
        }

        $referrer = $this->findByPhone($phone);
        if (! $referrer) {
            throw ValidationException::withMessages(['referrer_phone' => 'Không tìm thấy khách hàng/người giới thiệu theo số điện thoại đã nhập.']);
        }
        if ((int) $referrer->id === (int) $customer->id) {
            throw ValidationException::withMessages(['referrer_phone' => 'Khách hàng không thể tự giới thiệu chính mình.']);
        }

        $rate = $isRequest ? ($input->input('commission_rate') ?: 5) : ($input['commission_rate'] ?? 5);
        DB::transaction(function () use ($customer, $referrer, $rate): void {
            if (Schema::hasColumn('customers', 'referrer_id')) {
                $customer->update(['referrer_id' => $referrer->id]);
            }
            if (! Schema::hasTable('customer_referrals')) {
                return;
            }

            $data = $this->existingColumns('customer_referrals', [
                'referrer_customer_id' => $referrer->id,
                'referred_customer_id' => $customer->id,
                'referrer_phone' => $referrer->phone,
                'commission_rate' => $rate,
                'referral_status_id' => $this->statusId(),
                'status' => 'active',
                'started_at' => now(),
                'ended_at' => null,
                'note' => 'Cập nhật thông tin người giới thiệu.',
                'updated_at' => now(),
            ]);
            $existingId = DB::table('customer_referrals')->where('referred_customer_id', $customer->id)->value('id');
            if ($existingId) {
                unset($data['started_at']);
                DB::table('customer_referrals')->where('id', $existingId)->update($data);
            } else {
                if (Schema::hasColumn('customer_referrals', 'created_at')) {
                    $data['created_at'] = now();
                }
                DB::table('customer_referrals')->insert($data);
            }
        });
    }

    private function findByPhone(string $phone): ?Customer
    {
        $candidates = [$phone];
        if (str_starts_with($phone, '84') && strlen($phone) >= 11) {
            $candidates[] = '0'.substr($phone, 2);
        }
        if (str_starts_with($phone, '0') && strlen($phone) >= 10) {
            $candidates[] = '84'.substr($phone, 1);
        }
        if (! str_starts_with($phone, '0') && ! str_starts_with($phone, '84') && strlen($phone) === 9) {
            $candidates[] = '0'.$phone;
            $candidates[] = '84'.$phone;
        }

        $column = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '.', ''), '-', ''), '(', ''), ')', ''), '+', '')";

        return Customer::query()->where(function ($query) use ($column, $candidates): void {
            foreach (array_unique($candidates) as $candidate) {
                $query->orWhereRaw("{$column} = ?", [$candidate]);
            }
        })->first();
    }

    private function normalizePhone(mixed $phone): ?string
    {
        $phone = preg_replace('/\D+/', '', trim((string) $phone));
        if (str_starts_with($phone, '0084')) {
            $phone = '84'.substr($phone, 4);
        }

        return $phone !== '' ? $phone : null;
    }

    private function statusId(): ?int
    {
        return ReferralStatus::query()->where('is_active', true)
            ->whereIn('code', ['active', 'approved', 'pending', 'dang_hoat_dong', 'cho_duyet'])
            ->orderBy('sort_order')->orderBy('id')->value('id');
    }

    private function existingColumns(string $table, array $data): array
    {
        return array_filter($data, fn (string $column): bool => Schema::hasColumn($table, $column), ARRAY_FILTER_USE_KEY);
    }
}
