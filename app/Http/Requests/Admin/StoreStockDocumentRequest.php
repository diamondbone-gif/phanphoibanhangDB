<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        $type = (string) $this->input('document_type');

        return [
            'document_type' => ['required', Rule::in(['receipt', 'issue', 'transfer', 'adjustment_increase', 'adjustment_decrease', 'return'])],
            'source_warehouse_id' => [Rule::requiredIf(in_array($type, ['issue', 'transfer', 'adjustment_decrease'], true)), 'nullable', 'integer', 'exists:warehouses,id'],
            'destination_warehouse_id' => [Rule::requiredIf(in_array($type, ['receipt', 'transfer', 'adjustment_increase', 'return'], true)), 'nullable', 'integer', 'exists:warehouses,id', 'different:source_warehouse_id'],
            'document_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:2000'],
            'note' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.product_batch_id' => ['nullable', 'integer', 'exists:product_batches,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
