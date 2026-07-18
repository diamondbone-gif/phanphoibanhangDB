<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'items' => collect($this->input('items', []))
                ->filter(fn ($row) => (int) ($row['quantity'] ?? 0) > 0)
                ->values()
                ->all(),
        ]);
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:1000'],
            'refund_method' => ['nullable', 'in:cash,bank_transfer,credit,other'],
            'note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'integer', 'exists:customer_order_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
