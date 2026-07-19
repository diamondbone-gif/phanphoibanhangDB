<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'reason' => trim((string) $this->input('reason')),
            'note' => trim((string) $this->input('note')) ?: null,
            'exchange_note' => trim((string) $this->input('exchange_note')) ?: null,
            'items' => collect($this->input('items', []))
                ->filter(fn ($row) => (int) ($row['quantity'] ?? 0) > 0)
                ->values()
                ->all(),
        ]);
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
            'resolution_type' => ['required', Rule::in(['refund', 'exchange', 'mixed'])],
            'refund_method' => [Rule::requiredIf(in_array($this->input('resolution_type'), ['refund', 'mixed'], true)), 'nullable', 'in:cash,bank_transfer,credit,other'],
            'cash_refund_amount' => [Rule::requiredIf($this->input('resolution_type') === 'mixed'), 'nullable', 'numeric', 'min:0.01'],
            'exchange_note' => [Rule::requiredIf(in_array($this->input('resolution_type'), ['exchange', 'mixed'], true)), 'nullable', 'string', 'max:1000'],
            'note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'integer', 'exists:customer_order_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
