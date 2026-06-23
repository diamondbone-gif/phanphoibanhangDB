<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductStockController extends Controller
{
    public function storeImport(Request $request)
    {
        $request->merge([
            'import_quantity' => $request->input('import_quantity', $request->input('quantity')),
            'batch_no' => $request->input('batch_no', $request->input('batch_code')),
            'mfg_date' => $request->input('mfg_date', $request->input('manufacturing_date')),
            'expiry_date' => $request->input('expiry_date', $request->input('expired_at')),
        ]);

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'import_quantity' => ['required', 'integer', 'min:1'],
            'batch_no' => ['nullable', 'string', 'max:100'],
            'mfg_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:mfg_date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'product_id.required' => 'Vui lòng chọn sản phẩm.',
            'product_id.exists' => 'Sản phẩm không tồn tại.',
            'import_quantity.required' => 'Vui lòng nhập số lượng.',
            'import_quantity.integer' => 'Số lượng phải là số nguyên.',
            'import_quantity.min' => 'Số lượng nhập phải lớn hơn 0.',
            'expiry_date.after_or_equal' => 'Hạn sử dụng phải sau hoặc bằng ngày sản xuất.',
        ]);

        try {
            DB::beginTransaction();

            $product = Product::lockForUpdate()->findOrFail($validated['product_id']);

            $batchId = null;

            if (Schema::hasTable('product_batches')) {
                $batchData = $this->filterExistingColumns('product_batches', [
                    'product_id' => $product->id,
                    'batch_no' => $validated['batch_no'] ?? null,
                    'batch_code' => $validated['batch_no'] ?? null,
                    'import_quantity' => $validated['import_quantity'],
                    'initial_quantity' => $validated['import_quantity'],
                    'current_quantity' => $validated['import_quantity'],
                    'quantity' => $validated['import_quantity'],
                    'mfg_date' => $validated['mfg_date'] ?? null,
                    'manufacturing_date' => $validated['mfg_date'] ?? null,
                    'expiry_date' => $validated['expiry_date'] ?? null,
                    'expired_at' => $validated['expiry_date'] ?? null,
                    'note' => $validated['note'] ?? null,
                    'created_by' => auth('admin')->id(),
                    'updated_by' => auth('admin')->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $batchId = DB::table('product_batches')->insertGetId($batchData);
            }

            $product->update([
                'total_quantity' => ((int) $product->total_quantity) + (int) $validated['import_quantity'],
                'updated_by' => auth('admin')->id(),
            ]);

            if (Schema::hasTable('stock_movements')) {
                $movementData = $this->filterExistingColumns('stock_movements', [
                    'product_id' => $product->id,
                    'product_batch_id' => $batchId,
                    'batch_id' => $batchId,
                    'type' => 'import',
                    'movement_type' => 'import',
                    'quantity' => $validated['import_quantity'],
                    'before_quantity' => max(0, ((int) $product->total_quantity) - (int) $validated['import_quantity']),
                    'after_quantity' => (int) $product->total_quantity,
                    'note' => $validated['note'] ?? 'Nhập kho sản phẩm',
                    'created_by' => auth('admin')->id(),
                    'updated_by' => auth('admin')->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('stock_movements')->insert($movementData);
            }

            DB::commit();

            return response()->json([
                'message' => 'Nhập kho thành công.',
                'product' => $product->fresh(),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Không thể nhập kho. Vui lòng kiểm tra lại dữ liệu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    private function filterExistingColumns(string $table, array $data): array
    {
        $columns = Schema::getColumnListing($table);

        return collect($data)
            ->only($columns)
            ->toArray();
    }
}
