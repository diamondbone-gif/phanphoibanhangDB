<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerCareReminderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'assigned_staff_id' => ['nullable', 'integer', 'exists:operation_managers,id'],
            'reminder_date' => ['required', 'date', 'after_or_equal:today'],
            'reminder_time' => ['required', 'date_format:H:i'],
            'content' => ['required', 'string', 'max:10000'],
            'care_priority_id' => ['nullable', 'integer', 'exists:care_priorities,id'],
            'care_status_id' => ['nullable', 'integer', 'exists:care_statuses,id'],
        ];
    }
}
