<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductCategory;
use App\Models\ProductStockMovement;
use App\Services\WarehouseInventoryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function __construct(private WarehouseInventoryService $warehouseInventoryService) {}

    public function index(Request $request)
    {
        $categories = ProductCategory::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $products = $this->productQuery($request)
            ->paginate(10)
            ->withQueryString();

        return view('admin.auth.products.index', compact('categories', 'products'));
    }

    public function table(Request $request)
    {
        $products = $this->productQuery($request)
            ->paginate(10)
            ->withQueryString();

        return view('admin.auth.products._product_table', compact('products'))->render();
    }

    public function store(Request $request)
    {
        $data = $this->validateProduct($request);

        if ((int) $request->input('initial_quantity', 0) > 0 && ! $request->filled('batch_number')) {
            throw ValidationException::withMessages([
                'batch_number' => 'Vui lòng nhập số lô khi có số lượng nhập ban đầu.',
            ]);
        }

        DB::transaction(function () use ($request, $data) {
            $adminId = Auth::guard('admin')->id();

            $data['main_image'] = $this->storeProductImage($request);
            $data['is_active'] = $request->boolean('is_active');
            $data['track_batch'] = $request->boolean('track_batch');
            $data['track_expiry'] = $request->boolean('track_expiry');
            $data['is_commissionable'] = $request->boolean('is_commissionable');
            $data['allow_sell_without_stock'] = $request->boolean('allow_sell_without_stock');
            $data['created_by'] = $adminId;
            $data['updated_by'] = $adminId;

            $product = Product::create($data);

            $initialQuantity = (int) $request->input('initial_quantity', 0);

            if ($initialQuantity > 0) {
                $this->importStockForProduct(
                    product: $product,
                    batchNumber: $request->input('batch_number'),
                    quantity: $initialQuantity,
                    manufactureDate: $request->input('manufacture_date'),
                    expiryDate: $request->input('expiry_date'),
                    note: $request->input('stock_note') ?: 'Nhập kho ban đầu khi tạo sản phẩm.'
                );
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Thêm sản phẩm thành công.',
        ]);
    }

    public function edit(Product $product)
    {
        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'product_category_id' => $product->product_category_id,
                'product_code' => $product->product_code,
                'product_name' => $product->product_name,
                'unit_name' => $product->unit_name,
                'price' => $product->price,
                'main_image' => $product->main_image,
                'image_url' => $product->image_url,
                'short_description' => $product->short_description,
                'description' => $product->description,
                'total_quantity' => $product->total_quantity,
                'track_batch' => $product->track_batch,
                'track_expiry' => $product->track_expiry,
                'min_quantity_alert' => $product->min_quantity_alert,
                'is_commissionable' => $product->is_commissionable,
                'default_commission_rate' => $product->default_commission_rate,
                'allow_sell_without_stock' => $product->allow_sell_without_stock,
                'sort_order' => $product->sort_order,
                'is_active' => $product->is_active,
            ],
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateProduct($request, $product->id);

        DB::transaction(function () use ($request, $product, $data) {
            $adminId = Auth::guard('admin')->id();

            if ($request->hasFile('main_image')) {
                $this->deleteProductImage($product->main_image);
                $data['main_image'] = $this->storeProductImage($request);
            }

            $data['is_active'] = $request->boolean('is_active');
            $data['track_batch'] = $request->boolean('track_batch');
            $data['track_expiry'] = $request->boolean('track_expiry');
            $data['is_commissionable'] = $request->boolean('is_commissionable');
            $data['allow_sell_without_stock'] = $request->boolean('allow_sell_without_stock');
            $data['updated_by'] = $adminId;

            $product->update($data);
        });

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật sản phẩm thành công.',
        ]);
    }

    public function destroy(Product $product)
    {
        $hasStockHistory = $product->stockMovements()->exists() || $product->batches()->exists();

        $hasOrderHistory = DB::table('customer_order_items')
            ->where('product_id', $product->id)
            ->exists();

        if ($hasStockHistory || $hasOrderHistory) {
            $product->update([
                'is_active' => false,
                'updated_by' => Auth::guard('admin')->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sản phẩm đã có lịch sử kho/đơn hàng nên hệ thống đã chuyển sang trạng thái ẩn để không mất dữ liệu.',
            ]);
        }

        $this->deleteProductImage($product->main_image);
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa sản phẩm thành công.',
        ]);
    }

    public function toggleStatus(Product $product)
    {
        $product->update([
            'is_active' => ! $product->is_active,
            'updated_by' => Auth::guard('admin')->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công.',
            'is_active' => $product->is_active,
        ]);
    }

    public function inventory(Request $request)
    {
        $products = Product::active()
            ->orderBy('product_name')
            ->get();

        $batches = $this->batchQuery($request)
            ->paginate(10)
            ->withQueryString();

        $stats = $this->inventoryStats();

        return view('admin.auth.inventory.index', compact('products', 'batches', 'stats'));
    }

    public function inventoryTable(Request $request)
    {
        $batches = $this->batchQuery($request)
            ->paginate(10)
            ->withQueryString();

        return view('admin.auth.inventory._inventory_table', compact('batches'))->render();
    }

    public function importStock(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'batch_number' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:1'],
            'manufacture_date' => ['nullable', 'date'],
            'expiry_date' => ['required', 'date', 'after_or_equal:manufacture_date'],
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
        ], [
            'product_id.required' => 'Vui lòng chọn sản phẩm. Bạn cần thêm sản phẩm trước khi lập phiếu nhập kho.',
            'product_id.exists' => 'Sản phẩm không tồn tại.',
            'batch_number.required' => 'Vui lòng nhập số lô hàng.',
            'quantity.required' => 'Vui lòng nhập số lượng nhập.',
            'quantity.min' => 'Số lượng nhập phải lớn hơn 0.',
            'expiry_date.required' => 'Vui lòng nhập hạn sử dụng.',
            'expiry_date.after_or_equal' => 'Hạn sử dụng phải lớn hơn hoặc bằng ngày sản xuất.',
        ]);

        DB::transaction(function () use ($data) {
            $product = Product::findOrFail($data['product_id']);

            $this->importStockForProduct(
                product: $product,
                batchNumber: $data['batch_number'],
                quantity: (int) $data['quantity'],
                manufactureDate: $data['manufacture_date'] ?? null,
                expiryDate: $data['expiry_date'] ?? null,
                supplierName: $data['supplier_name'] ?? null,
                note: $data['note'] ?? 'Lập phiếu nhập kho.'
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Lưu lô hàng thành công.',
        ]);
    }

    public function editBatch(ProductBatch $batch)
    {
        $batch->load('product');

        return response()->json([
            'success' => true,
            'batch' => [
                'id' => $batch->id,
                'product_id' => $batch->product_id,
                'product_name' => optional($batch->product)->product_name,
                'batch_number' => $batch->batch_number,
                'manufacture_date' => optional($batch->manufacture_date)->format('Y-m-d'),
                'expiry_date' => optional($batch->expiry_date)->format('Y-m-d'),
                'supplier_name' => $batch->supplier_name,
                'initial_quantity' => $batch->initial_quantity,
                'current_quantity' => $batch->current_quantity,
                'note' => $batch->note,
                'is_active' => $batch->is_active,
            ],
        ]);
    }

    public function updateBatch(Request $request, ProductBatch $batch)
    {
        $data = $request->validate([
            'batch_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('product_batches', 'batch_number')
                    ->where(fn ($query) => $query->where('product_id', $batch->product_id))
                    ->ignore($batch->id),
            ],
            'manufacture_date' => ['nullable', 'date'],
            'expiry_date' => ['required', 'date', 'after_or_equal:manufacture_date'],
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'initial_quantity' => ['required', 'integer', 'min:0'],
            'current_quantity' => ['required', 'integer', 'min:0'],
            'note' => ['nullable', 'string'],
        ], [
            'batch_number.required' => 'Vui lòng nhập số lô.',
            'batch_number.unique' => 'Số lô này đã tồn tại với sản phẩm này.',
            'expiry_date.required' => 'Vui lòng nhập hạn sử dụng.',
            'expiry_date.after_or_equal' => 'Hạn sử dụng phải lớn hơn hoặc bằng ngày sản xuất.',
            'initial_quantity.required' => 'Vui lòng nhập số lượng ban đầu.',
            'current_quantity.required' => 'Vui lòng nhập số lượng còn lại.',
        ]);

        DB::transaction(function () use ($data, $batch) {
            $before = (int) $batch->current_quantity;
            $after = (int) $data['current_quantity'];
            $delta = $after - $before;

            $batch->update([
                'batch_number' => $data['batch_number'],
                'manufacture_date' => $data['manufacture_date'] ?? null,
                'expiry_date' => $data['expiry_date'],
                'supplier_name' => $data['supplier_name'] ?? null,
                'initial_quantity' => (int) $data['initial_quantity'],
                'current_quantity' => $after,
                'status' => $this->resolveBatchStatus($after, $data['expiry_date']),
                'note' => $data['note'] ?? null,
                'updated_by' => Auth::guard('admin')->id(),
            ]);

            if ($delta !== 0) {
                $warehouse = $this->warehouseInventoryService->defaultWarehouse();
                ProductStockMovement::create([
                    'product_id' => $batch->product_id,
                    'product_batch_id' => $batch->id,
                    'warehouse_id' => $warehouse->id,
                    'movement_type' => 'adjustment',
                    'quantity' => $delta,
                    'before_quantity' => $before,
                    'after_quantity' => $after,
                    'reference_type' => 'manual',
                    'movement_date' => now(),
                    'note' => 'Điều chỉnh số lượng lô.',
                    'created_by' => Auth::guard('admin')->id(),
                ]);
                $this->warehouseInventoryService->syncActualStock($batch->product, $after, $batch, $warehouse);
            }

            if ($batch->product) {
                $this->refreshProductTotal($batch->product);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật lô hàng thành công.',
        ]);
    }

    public function toggleBatchStatus(ProductBatch $batch)
    {
        $batch->update([
            'is_active' => ! $batch->is_active,
            'updated_by' => Auth::guard('admin')->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái lô hàng thành công.',
        ]);
    }

    public function destroyBatch(ProductBatch $batch)
    {
        DB::transaction(function () use ($batch) {
            $product = $batch->product;
            $before = (int) $batch->current_quantity;

            $warehouse = $this->warehouseInventoryService->defaultWarehouse();
            ProductStockMovement::create([
                'product_id' => $batch->product_id,
                'product_batch_id' => $batch->id,
                'warehouse_id' => $warehouse->id,
                'movement_type' => 'delete_batch',
                'quantity' => -$before,
                'before_quantity' => $before,
                'after_quantity' => 0,
                'reference_type' => 'manual',
                'movement_date' => now(),
                'note' => 'Xóa lô hàng khỏi kho.',
                'created_by' => Auth::guard('admin')->id(),
            ]);

            $batch->delete();

            if ($product) {
                $this->refreshProductTotal($product);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Xóa lô hàng thành công.',
        ]);
    }

    public function movementHistory(Request $request)
    {
        $movements = ProductStockMovement::with(['product', 'batch'])
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $keyword = trim($request->keyword);

                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->whereHas('product', function ($productQuery) use ($keyword) {
                        $productQuery->where('product_name', 'like', "%{$keyword}%")
                            ->orWhere('product_code', 'like', "%{$keyword}%");
                    })->orWhereHas('batch', function ($batchQuery) use ($keyword) {
                        $batchQuery->where('batch_number', 'like', "%{$keyword}%");
                    });
                });
            })
            ->latest('movement_date')
            ->latest('id')
            ->limit(100)
            ->get();

        return view('admin.auth.inventory._movement_history_table', compact('movements'))->render();
    }

    private function batchQuery(Request $request)
    {
        return ProductBatch::query()
            ->with(['product.category'])
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $keyword = trim($request->keyword);

                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('batch_number', 'like', "%{$keyword}%")
                        ->orWhereHas('product', function ($productQuery) use ($keyword) {
                            $productQuery->where('product_code', 'like', "%{$keyword}%")
                                ->orWhere('product_name', 'like', "%{$keyword}%")
                                ->orWhere('unit_name', 'like', "%{$keyword}%");
                        });
                });
            })
            ->when($request->filled('expiry_filter'), function ($query) use ($request) {
                if ($request->expiry_filter === 'near_expired') {
                    $query->where('current_quantity', '>', 0)
                        ->whereDate('expiry_date', '>=', now())
                        ->whereDate('expiry_date', '<=', now()->addMonths(6));
                }

                if ($request->expiry_filter === 'expired') {
                    $query->whereDate('expiry_date', '<', now());
                }
            })
            ->when($request->filled('stock_filter'), function ($query) use ($request) {
                if ($request->stock_filter === 'in_stock') {
                    $query->where('current_quantity', '>', 0);
                }

                if ($request->stock_filter === 'low_stock') {
                    $query->where('current_quantity', '>', 0)
                        ->where('current_quantity', '<=', 20);
                }

                if ($request->stock_filter === 'out_stock') {
                    $query->where('current_quantity', '<=', 0);
                }
            })
            ->latest('id');
    }

    private function inventoryStats(): array
    {
        return [
            'total_batches' => ProductBatch::count(),

            'low_stock' => ProductBatch::where('current_quantity', '>', 0)
                ->where('current_quantity', '<=', 20)
                ->count(),

            'out_stock' => ProductBatch::where('current_quantity', '<=', 0)
                ->count(),

            'near_expired' => ProductBatch::where('current_quantity', '>', 0)
                ->whereDate('expiry_date', '>=', now())
                ->whereDate('expiry_date', '<=', now()->addMonths(6))
                ->count(),
        ];
    }

    private function importStockForProduct(
        Product $product,
        string $batchNumber,
        int $quantity,
        ?string $manufactureDate = null,
        ?string $expiryDate = null,
        ?string $supplierName = null,
        ?string $note = null
    ): void {
        $batch = ProductBatch::firstOrNew([
            'product_id' => $product->id,
            'batch_number' => $batchNumber,
        ]);

        $before = (int) ($batch->current_quantity ?? 0);
        $after = $before + $quantity;

        if (! $batch->exists) {
            $batch->initial_quantity = 0;
            $batch->created_by = Auth::guard('admin')->id();
        }

        $batch->manufacture_date = $manufactureDate;
        $batch->expiry_date = $expiryDate;
        $batch->supplier_name = $supplierName;
        $batch->initial_quantity = (int) $batch->initial_quantity + $quantity;
        $batch->current_quantity = $after;
        $batch->status = $this->resolveBatchStatus($after, $expiryDate);
        $batch->is_active = true;
        $batch->note = $note;
        $batch->updated_by = Auth::guard('admin')->id();
        $batch->save();

        ProductStockMovement::create([
            'product_id' => $product->id,
            'product_batch_id' => $batch->id,
            'warehouse_id' => $this->warehouseInventoryService->defaultWarehouse()->id,
            'movement_type' => 'import',
            'quantity' => $quantity,
            'before_quantity' => $before,
            'after_quantity' => $after,
            'reference_type' => 'manual',
            'movement_date' => now(),
            'note' => $note,
            'created_by' => Auth::guard('admin')->id(),
        ]);

        $this->warehouseInventoryService->syncActualStock($product, $after, $batch);

        $this->refreshProductTotal($product);
    }

    private function refreshProductTotal(Product $product): void
    {
        $totalQuantity = $product->batches()->sum('current_quantity');

        $product->update([
            'total_quantity' => $totalQuantity,
            'updated_by' => Auth::guard('admin')->id(),
        ]);
    }

    private function resolveBatchStatus(int $quantity, ?string $expiryDate = null): string
    {
        if ($quantity <= 0) {
            return 'sold_out';
        }

        if ($expiryDate && Carbon::parse($expiryDate)->isPast()) {
            return 'expired';
        }

        if ($expiryDate && Carbon::parse($expiryDate)->lte(now()->addMonths(6))) {
            return 'near_expired';
        }

        return 'active';
    }

    private function productQuery(Request $request)
    {
        return Product::query()
            ->with(['category', 'latestBatch'])
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $keyword = trim($request->keyword);

                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('product_code', 'like', "%{$keyword}%")
                        ->orWhere('product_name', 'like', "%{$keyword}%")
                        ->orWhere('unit_name', 'like', "%{$keyword}%");
                });
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('product_category_id', $request->category_id);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                }

                if ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->orderBy('sort_order')
            ->latest('id');
    }

    private function validateProduct(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'product_category_id' => ['nullable', 'exists:product_categories,id'],
            'product_code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'product_code')->ignore($ignoreId),
            ],
            'product_name' => ['required', 'string', 'max:255'],
            'unit_name' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'main_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'min_quantity_alert' => ['nullable', 'integer', 'min:0'],
            'default_commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],

            'initial_quantity' => ['nullable', 'integer', 'min:0'],
            'batch_number' => ['nullable', 'string', 'max:100'],
            'manufacture_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:manufacture_date'],
            'stock_note' => ['nullable', 'string'],
        ], [
            'product_code.required' => 'Vui lòng nhập mã SKU.',
            'product_code.unique' => 'Mã SKU này đã tồn tại.',
            'product_name.required' => 'Vui lòng nhập tên sản phẩm.',
            'price.required' => 'Vui lòng nhập giá bán.',
            'main_image.image' => 'File tải lên phải là hình ảnh.',
            'main_image.max' => 'Hình ảnh không được vượt quá 4MB.',
        ]);
    }

    private function storeProductImage(Request $request): ?string
    {
        if (! $request->hasFile('main_image')) {
            return null;
        }

        return $request->file('main_image')->store('image_product', 'public');
    }

    private function deleteProductImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
