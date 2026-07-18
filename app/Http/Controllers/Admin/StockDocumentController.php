<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StockDocumentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStockDocumentRequest;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockDocument;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\StockDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockDocumentController extends Controller
{
    public function __construct(private StockDocumentService $documents) {}

    public function index(Request $request): View
    {
        $query = StockDocument::query()->with(['sourceWarehouse', 'destinationWarehouse', 'items'])->latest('id');
        if ($request->filled('type')) {
            $query->where('document_type', $request->string('type')->toString());
        }

        return view('admin.auth.stock-documents.index', [
            'documents' => $query->paginate(15)->withQueryString(),
            'warehouses' => Warehouse::query()->where('is_active', true)->orderByDesc('is_default')->orderBy('warehouse_name')->get(),
            'stocks' => WarehouseStock::query()->with(['warehouse', 'product', 'batch'])->orderBy('warehouse_id')->orderBy('product_id')->get(),
            'products' => Product::query()->where('is_active', true)->orderBy('product_name')->get(),
            'batches' => ProductBatch::query()->where('is_active', true)->orderBy('product_id')->orderBy('expiry_date')->get(),
            'types' => StockDocumentType::cases(),
        ]);
    }

    public function store(StoreStockDocumentRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $document = $this->documents->createAndPost($data, $data['items'], auth('admin')->id());

            return redirect()->route('admin.stock-documents.index')->with('success', "Đã ghi sổ chứng từ {$document->document_code}.");
        } catch (\RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function storeWarehouse(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'warehouse_code' => ['required', 'string', 'max:50', 'unique:warehouses,warehouse_code'],
            'warehouse_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);
        Warehouse::query()->create($data + ['is_default' => false, 'is_active' => true]);

        return back()->with('success', 'Đã tạo kho mới.');
    }
}
