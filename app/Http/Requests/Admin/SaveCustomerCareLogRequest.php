<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SaveCustomerCareLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'staff_id' => ['nullable', 'integer', 'exists:operation_managers,id'],
            'care_channel_id' => ['nullable', 'integer', 'exists:care_channels,id'],
            'care_date' => ['required', 'date'],
            'content' => ['required', 'string', 'max:10000'],
            'internal_note' => ['nullable', 'string', 'max:10000'],
            'next_follow_up_at' => ['nullable', 'date', 'after_or_equal:care_date'],
            'care_priority_id' => ['nullable', 'integer', 'exists:care_priorities,id'],
            'care_status_id' => ['nullable', 'integer', 'exists:care_statuses,id'],
        ];
    }
}
