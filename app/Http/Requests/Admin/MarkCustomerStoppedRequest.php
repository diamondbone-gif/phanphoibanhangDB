<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MarkCustomerStoppedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'customer_stop_reason_id' => ['required', 'integer', 'exists:customer_stop_reasons,id'],
            'stopped_reason_note' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
