<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductCategory;
use App\Models\ProductStockMovement;
use App\Services\StockDocumentService;
use App\Services\WarehouseInventoryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function __construct(
        private WarehouseInventoryService $warehouseInventoryService,
        private StockDocumentService $stockDocumentService,
    ) {}

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
        $this->normalizeProductInput($request);
        $data = $this->validateProduct($request);
        $data['product_code'] = filled($data['product_code'] ?? null)
            ? $data['product_code']
            : $this->generateProductCode();
        $data['unit_name'] = filled($data['unit_name'] ?? null) ? $data['unit_name'] : 'Sản phẩm';

        $product = DB::transaction(function () use ($request, $data) {
            $adminId = Auth::guard('admin')->id();
            $openingStock = [
                'quantity' => (int) ($data['initial_quantity'] ?? 0),
                'batch_number' => $data['batch_number'] ?? null,
                'manufacture_date' => $data['manufacture_date'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'note' => $data['stock_note'] ?? null,
            ];

            unset(
                $data['initial_quantity'],
                $data['batch_number'],
                $data['manufacture_date'],
                $data['expiry_date'],
                $data['stock_note'],
            );

            $data['main_image'] = $this->storeProductImage($request);
            $data['is_active'] = $request->boolean('is_active');
            $data['created_by'] = $adminId;
            $data['updated_by'] = $adminId;

            $product = Product::query()->create($data);

            if ($openingStock['quantity'] > 0) {
                $this->createOpeningStock($product, $openingStock, $adminId);
            }

            return $product;
        });

        return response()->json([
            'success' => true,
            'product_id' => $product->id,
            'message' => (int) ($data['initial_quantity'] ?? 0) > 0
                ? 'Đã tạo sản phẩm và ghi nhận phiếu nhập tồn ban đầu.'
                : 'Thêm sản phẩm thành công.',
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
        $this->normalizeProductInput($request);
        $data = $this->validateProduct($request, $product->id);
        $data['product_code'] = filled($data['product_code'] ?? null)
            ? $data['product_code']
            : $product->product_code;
        $data['unit_name'] = filled($data['unit_name'] ?? null)
            ? $data['unit_name']
            : ($product->unit_name ?: 'Sản phẩm');

        DB::transaction(function () use ($request, $product, $data) {
            $adminId = Auth::guard('admin')->id();

            if ($request->hasFile('main_image')) {
                $this->deleteProductImage($product->main_image);
                $data['main_image'] = $this->storeProductImage($request);
            }

            $data['is_active'] = $request->boolean('is_active');
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
            $product = Product::query()->lockForUpdate()->findOrFail($data['product_id']);
            $warehouse = $this->warehouseInventoryService->defaultWarehouse();
            $batch = ProductBatch::query()->firstOrCreate(
                ['product_id' => $product->id, 'batch_number' => $data['batch_number']],
                [
                    'manufacture_date' => $data['manufacture_date'] ?? null,
                    'expiry_date' => $data['expiry_date'],
                    'supplier_name' => $data['supplier_name'] ?? null,
                    'initial_quantity' => 0,
                    'current_quantity' => 0,
                    'status' => 'active',
                    'is_active' => true,
                    'created_by' => Auth::guard('admin')->id(),
                ],
            );

            $this->stockDocumentService->createAndPost([
                'document_type' => 'receipt',
                'destination_warehouse_id' => $warehouse->id,
                'document_date' => now(),
                'reason' => $data['note'] ?? 'Nhập kho từ màn hình quản lý lô.',
            ], [[
                'product_id' => $product->id,
                'product_batch_id' => $batch->id,
                'quantity' => (int) $data['quantity'],
                'note' => $data['supplier_name'] ?? null,
            ]], Auth::guard('admin')->id());

            $batch->increment('initial_quantity', (int) $data['quantity']);
            $batch->update([
                'manufacture_date' => $data['manufacture_date'] ?? $batch->manufacture_date,
                'expiry_date' => $data['expiry_date'],
                'supplier_name' => $data['supplier_name'] ?? $batch->supplier_name,
                'note' => $data['note'] ?? $batch->note,
                'updated_by' => Auth::guard('admin')->id(),
            ]);
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
            'note' => ['nullable', 'string'],
        ], [
            'batch_number.required' => 'Vui lòng nhập số lô.',
            'batch_number.unique' => 'Số lô này đã tồn tại với sản phẩm này.',
            'expiry_date.required' => 'Vui lòng nhập hạn sử dụng.',
            'expiry_date.after_or_equal' => 'Hạn sử dụng phải lớn hơn hoặc bằng ngày sản xuất.',
        ]);

        DB::transaction(function () use ($data, $batch) {
            $batch->update([
                'batch_number' => $data['batch_number'],
                'manufacture_date' => $data['manufacture_date'] ?? null,
                'expiry_date' => $data['expiry_date'],
                'supplier_name' => $data['supplier_name'] ?? null,
                'status' => $this->resolveBatchStatus((int) $batch->current_quantity, $data['expiry_date']),
                'note' => $data['note'] ?? null,
                'updated_by' => Auth::guard('admin')->id(),
            ]);

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
        $result = DB::transaction(function () use ($batch) {
            $lockedBatch = ProductBatch::query()->lockForUpdate()->findOrFail($batch->id);

            if ((int) $lockedBatch->current_quantity > 0) {
                return 'has_stock';
            }

            if ($lockedBatch->movements()->exists()) {
                $lockedBatch->update([
                    'is_active' => false,
                    'status' => 'out_of_stock',
                    'updated_by' => Auth::guard('admin')->id(),
                ]);

                return 'archived';
            }

            $lockedBatch->delete();

            return 'deleted';
        });

        if ($result === 'has_stock') {
            return response()->json([
                'success' => false,
                'message' => 'Lô vẫn còn tồn kho. Hãy lập phiếu điều chỉnh giảm về 0 trước khi ngừng sử dụng lô.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result === 'archived'
                ? 'Lô đã có lịch sử giao dịch nên được lưu trữ và ngừng sử dụng, không xóa dữ liệu đối soát.'
                : 'Xóa lô chưa phát sinh giao dịch thành công.',
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
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'product_code')->ignore($ignoreId),
            ],
            'product_name' => ['required', 'string', 'max:255'],
            'unit_name' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'decimal:0,2', 'min:0', 'max:9999999999999.99'],
            'main_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'min_quantity_alert' => ['nullable', 'integer', 'min:0'],
            'default_commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'initial_quantity' => [$ignoreId === null ? 'nullable' : 'exclude', 'integer', 'min:0'],
            'batch_number' => [
                $ignoreId === null
                    ? Rule::requiredIf(fn (): bool => (int) $request->input('initial_quantity', 0) > 0)
                    : 'exclude',
                'nullable',
                'string',
                'max:100',
            ],
            'manufacture_date' => [$ignoreId === null ? 'nullable' : 'exclude', 'date'],
            'expiry_date' => [
                $ignoreId === null
                    ? Rule::requiredIf(fn (): bool => (int) $request->input('initial_quantity', 0) > 0)
                    : 'exclude',
                'nullable',
                'date',
                'after_or_equal:manufacture_date',
            ],
            'stock_note' => [$ignoreId === null ? 'nullable' : 'exclude', 'string', 'max:1000'],
        ], [
            'product_code.unique' => 'Mã SKU này đã tồn tại.',
            'product_name.required' => 'Vui lòng nhập tên sản phẩm.',
            'price.required' => 'Vui lòng nhập giá bán.',
            'batch_number.required' => 'Vui lòng nhập số lô khi có tồn ban đầu.',
            'expiry_date.required' => 'Vui lòng nhập hạn sử dụng khi có tồn ban đầu.',
            'main_image.image' => 'File tải lên phải là hình ảnh.',
            'main_image.max' => 'Hình ảnh không được vượt quá 4MB.',
        ]);
    }

    private function normalizeProductInput(Request $request): void
    {
        $request->merge([
            'product_name' => trim((string) $request->input('product_name')),
            'product_code' => Str::upper(trim((string) $request->input('product_code'))),
            'unit_name' => trim((string) $request->input('unit_name')),
            'batch_number' => Str::upper(trim((string) $request->input('batch_number'))),
        ]);
    }

    private function createOpeningStock(Product $product, array $stock, ?int $adminId): void
    {
        $warehouse = $this->warehouseInventoryService->defaultWarehouse();
        $batch = ProductBatch::query()->create([
            'product_id' => $product->id,
            'batch_number' => $stock['batch_number'],
            'manufacture_date' => $stock['manufacture_date'],
            'expiry_date' => $stock['expiry_date'],
            'initial_quantity' => 0,
            'current_quantity' => 0,
            'status' => 'active',
            'is_active' => true,
            'note' => $stock['note'],
            'created_by' => $adminId,
            'updated_by' => $adminId,
        ]);

        $this->stockDocumentService->createAndPost([
            'document_type' => 'receipt',
            'destination_warehouse_id' => $warehouse->id,
            'document_date' => now(),
            'reason' => 'Nhập tồn ban đầu khi tạo sản phẩm.',
            'note' => $stock['note'],
        ], [[
            'product_id' => $product->id,
            'product_batch_id' => $batch->id,
            'quantity' => $stock['quantity'],
            'note' => $stock['note'],
        ]], $adminId);

        $batch->update([
            'initial_quantity' => $stock['quantity'],
            'status' => $this->resolveBatchStatus($stock['quantity'], $stock['expiry_date']),
        ]);
    }

    private function generateProductCode(): string
    {
        do {
            $code = 'SP-'.Str::upper(Str::random(8));
        } while (Product::query()->where('product_code', $code)->exists());

        return $code;
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
