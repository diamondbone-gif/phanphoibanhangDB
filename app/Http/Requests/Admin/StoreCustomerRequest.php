<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    protected function prepareForValidation(): void
    {
        $needIds = $this->input('customer_need_ids', []);

        if (!is_array($needIds)) {
            $needIds = [];
        }

        $needIds = array_values(array_filter($needIds, function ($id) {
            return $id !== null && $id !== '';
        }));

        $this->merge([
            'full_name' => $this->filled('full_name')
                ? trim((string) $this->input('full_name'))
                : null,

            'phone' => $this->normalizePhone($this->input('phone')),

            'email' => $this->filled('email')
                ? strtolower(trim((string) $this->input('email')))
                : null,

            'referrer_phone' => $this->normalizePhone($this->input('referrer_phone')),

            'source_channel_id' => $this->filled('source_channel_id')
                ? (int) $this->input('source_channel_id')
                : null,

            'province' => $this->filled('province')
                ? trim((string) $this->input('province'))
                : null,

            'district' => $this->filled('district')
                ? trim((string) $this->input('district'))
                : null,

            'ward' => $this->filled('ward')
                ? trim((string) $this->input('ward'))
                : null,

            'address' => $this->filled('address')
                ? trim((string) $this->input('address'))
                : null,

            'medical_note' => $this->filled('medical_note')
                ? trim((string) $this->input('medical_note'))
                : null,

            'consultation_note' => $this->filled('consultation_note')
                ? trim((string) $this->input('consultation_note'))
                : null,

            'customer_need_ids' => $needIds,
        ]);
    }

    public function rules(): array
    {
        return [
            'full_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'phone' => [
                'required',
                'digits_between:9,15',
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
                'before_or_equal:today',
            ],

            'customer_source' => [
                'required',
                Rule::in(['direct', 'ctv_referral']),
            ],

            /*
            |--------------------------------------------------------------------------
            | Thông tin nhận diện
            |--------------------------------------------------------------------------
            | Chỉ bắt buộc khi chọn "Khách tự tìm đến".
            | Dữ liệu lấy từ bảng customer_source_channels.
            */

            'source_channel_id' => [
                'nullable',
                'required_if:customer_source,direct',
                'integer',
                Rule::exists('customer_source_channels', 'id')->where(function ($query) {
                    $query->where('is_active', true);
                }),
            ],

            'referrer_phone' => [
                'nullable',
                'required_if:customer_source,ctv_referral',
                'digits_between:9,15',
                'different:phone',
                Rule::exists('customers', 'phone'),
            ],

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
                'max:5000',
            ],

            'buy_for_option_id' => [
                'nullable',
                'integer',
                Rule::exists('buy_for_options', 'id')->where(function ($query) {
                    $query->where('is_active', true);
                }),
            ],

            'interested_product_id' => [
                'nullable',
                'integer',
                Rule::exists('products', 'id')->where(function ($query) {
                    $query->where('is_active', true);
                }),
            ],

            'customer_need_ids' => [
                'nullable',
                'array',
            ],

            'customer_need_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('customer_needs', 'id')->where(function ($query) {
                    $query->where('is_active', true);
                }),
            ],

            'consultation_note' => [
                'nullable',
                'string',
                'max:5000',
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
            'full_name.min' => 'Họ tên khách hàng quá ngắn.',

            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.digits_between' => 'Số điện thoại chỉ được gồm số và dài từ 9 đến 15 chữ số.',
            'phone.unique' => 'Số điện thoại này đã tồn tại trong hệ thống.',

            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã tồn tại trong hệ thống.',

            'gender.in' => 'Giới tính không hợp lệ.',
            'birth_date.before_or_equal' => 'Ngày sinh không được lớn hơn ngày hiện tại.',

            'customer_source.required' => 'Vui lòng chọn loại khách hàng.',
            'customer_source.in' => 'Loại khách hàng không hợp lệ.',

            'source_channel_id.required_if' => 'Vui lòng chọn thông tin nhận diện của khách hàng.',
            'source_channel_id.integer' => 'Thông tin nhận diện không hợp lệ.',
            'source_channel_id.exists' => 'Thông tin nhận diện không hợp lệ hoặc đã bị tắt.',

            'referrer_phone.required_if' => 'Vui lòng nhập số điện thoại CTV/người giới thiệu.',
            'referrer_phone.digits_between' => 'Số điện thoại CTV/người giới thiệu phải dài từ 9 đến 15 chữ số.',
            'referrer_phone.different' => 'Số điện thoại người giới thiệu không được trùng với số điện thoại khách hàng mới.',
            'referrer_phone.exists' => 'Không tìm thấy CTV/khách hàng giới thiệu theo số điện thoại này.',

            'buy_for_option_id.exists' => 'Lựa chọn khách mua cho ai không hợp lệ.',
            'interested_product_id.exists' => 'Sản phẩm quan tâm không hợp lệ.',

            'customer_need_ids.array' => 'Danh sách nhu cầu không hợp lệ.',
            'customer_need_ids.*.exists' => 'Nhu cầu khách hàng không hợp lệ.',
            'customer_need_ids.*.distinct' => 'Nhu cầu khách hàng bị chọn trùng.',

            'referral_commission_rate.numeric' => 'Tỷ lệ hoa hồng phải là số.',
            'referral_commission_rate.min' => 'Tỷ lệ hoa hồng không được nhỏ hơn 0%.',
            'referral_commission_rate.max' => 'Tỷ lệ hoa hồng không được lớn hơn 100%.',
        ];
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $phone = preg_replace('/\D+/', '', trim($phone));

        return $phone !== '' ? $phone : null;
    }
}
