<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => $this->normalizePhone($this->phone),
            'referrer_phone' => $this->normalizePhone($this->referrer_phone),
        ]);
    }

    public function rules(): array
    {
        return [
            // Bảng customers
            'full_name' => [
                'required',
                'string',
                'max:255',
            ],

            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('customers', 'phone'),
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers', 'email'),
            ],

            'gender' => [
                'nullable',
                Rule::in(['male', 'female', 'other']),
            ],

            'birth_date' => [
                'nullable',
                'date',
            ],

            'customer_type_id' => [
                'nullable',
                'exists:customer_types,id',
            ],

            'customer_status_id' => [
                'nullable',
                'exists:customer_statuses,id',
            ],

            // Bảng customer_details
            'province' => [
                'nullable',
                'string',
                'max:100',
            ],

            'district' => [
                'nullable',
                'string',
                'max:100',
            ],

            'ward' => [
                'nullable',
                'string',
                'max:100',
            ],

            'address' => [
                'nullable',
                'string',
                'max:255',
            ],

            'medical_note' => [
                'nullable',
                'string',
            ],

            'buy_for_option_id' => [
                'nullable',
                'exists:buy_for_options,id',
            ],

            'interested_product_id' => [
                'nullable',
                'exists:products,id',
            ],

            'consultation_note' => [
                'nullable',
                'string',
            ],

            // Bảng customer_need_maps
            'customer_need_ids' => [
                'nullable',
                'array',
            ],

            'customer_need_ids.*' => [
                'integer',
                'exists:customer_needs,id',
            ],

            // Bảng customer_referrals
            'referrer_phone' => [
                'nullable',
                'string',
                'max:20',
                'exists:customers,phone',
            ],

            'referral_commission_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Vui lòng nhập họ tên khách hàng.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.unique' => 'Số điện thoại này đã tồn tại trong hệ thống.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã tồn tại trong hệ thống.',
            'gender.in' => 'Giới tính không hợp lệ.',
            'customer_type_id.exists' => 'Loại khách hàng không hợp lệ.',
            'customer_status_id.exists' => 'Trạng thái khách hàng không hợp lệ.',
            'buy_for_option_id.exists' => 'Lựa chọn khách mua cho ai không hợp lệ.',
            'interested_product_id.exists' => 'Sản phẩm quan tâm không hợp lệ.',
            'customer_need_ids.*.exists' => 'Nhu cầu khách hàng không hợp lệ.',
            'referrer_phone.exists' => 'Không tìm thấy khách hàng/CTV giới thiệu theo số điện thoại này.',
            'referral_commission_rate.max' => 'Tỷ lệ hoa hồng không được lớn hơn 100%.',
        ];
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        return preg_replace('/[\s\.\-\(\)]+/', '', trim($phone));
    }
}
