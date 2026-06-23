<?php

// namespace App\Services;
namespace App\Services\Admin;

use App\Models\Customer;
use App\Models\CustomerReferral;
use App\Models\CustomerRole;
use App\Models\CustomerStatus;
use App\Models\ReferralStatus;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    public function createCustomer(array $data, ?int $staffId = null): Customer
    {
        return DB::transaction(function () use ($data, $staffId) {
            $phone = $data['phone'];

            $customer = Customer::create([
                'customer_code' => $phone,
                'full_name' => $data['full_name'],
                'phone' => $phone,
                'email' => $data['email'] ?? null,
                'gender' => $data['gender'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,

                'customer_type_id' => $data['customer_type_id'] ?? null,
                'customer_role_id' => $this->getCustomerRoleId(),
                'customer_status_id' => $data['customer_status_id'] ?? $this->getCustomerStatusId(),

                'ctv_status_id' => null,
                'created_by' => $staffId,
                'updated_by' => $staffId,

                'commission_rate' => null,
                'ctv_approved_by' => null,
                'ctv_approved_at' => null,

                'stopped_reason' => null,
                'stopped_at' => null,
                'note' => null,
            ]);

            $customer->detail()->create([
                'province' => $data['province'] ?? null,
                'district' => $data['district'] ?? null,
                'ward' => $data['ward'] ?? null,
                'address' => $data['address'] ?? null,

                'medical_note' => $data['medical_note'] ?? null,

                'buy_for_option_id' => $data['buy_for_option_id'] ?? null,
                'interested_product_id' => $data['interested_product_id'] ?? null,

                'consultation_note' => $data['consultation_note'] ?? null,
            ]);

            if (!empty($data['customer_need_ids'])) {
                $customer->needs()->sync($data['customer_need_ids']);
            }

            if (!empty($data['referrer_phone'])) {
                $this->createReferral(
                    customer: $customer,
                    referrerPhone: $data['referrer_phone'],
                    commissionRate: $data['referral_commission_rate'] ?? null
                );
            }

            return $customer;
        });
    }

    private function createReferral(
        Customer $customer,
        string $referrerPhone,
        ?float $commissionRate = null
    ): void {
        $referrer = Customer::query()
            ->where('phone', $referrerPhone)
            ->first();

        if (!$referrer) {
            return;
        }

        CustomerReferral::create([
            'referrer_customer_id' => $referrer->id,
            'referred_customer_id' => $customer->id,
            'referrer_phone' => $referrerPhone,
            'commission_rate' => $commissionRate ?? $referrer->commission_rate,
            'referral_status_id' => $this->getReferralStatusId(),
            'started_at' => now(),
            'ended_at' => null,
            'note' => null,
        ]);
    }

    private function getCustomerRoleId(): ?int
    {
        return CustomerRole::query()
            ->where('code', 'customer')
            ->value('id');
    }

    private function getCustomerStatusId(): ?int
    {
        return CustomerStatus::query()
            ->where('code', 'active')
            ->value('id');
    }

    private function getReferralStatusId(): ?int
    {
        return ReferralStatus::query()
            ->where('code', 'pending')
            ->value('id');
    }
}
