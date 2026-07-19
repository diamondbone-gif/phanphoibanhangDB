<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = ProductCategory::query()->withCount('products')
            ->when($request->filled('keyword'), fn ($q) => $q->where(fn ($sub) => $sub->where('code', 'like', '%'.trim($request->keyword).'%')->orWhere('name', 'like', '%'.trim($request->keyword).'%')))
            ->orderBy('sort_order')->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.auth.product-categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        ProductCategory::create($this->validated($request));

        return back()->with('success', 'Đã tạo danh mục sản phẩm.');
    }

    public function update(Request $request, ProductCategory $category): RedirectResponse
    {
        $category->update($this->validated($request, $category->id));

        return back()->with('success', 'Đã cập nhật danh mục sản phẩm.');
    }

    public function destroy(ProductCategory $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'Danh mục đang có sản phẩm. Hãy chuyển sản phẩm sang danh mục khác trước.');
        }

        $category->delete();

        return back()->with('success', 'Đã xóa danh mục sản phẩm.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('product_categories', 'code')->ignore($ignoreId)],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => false, 'sort_order' => 0];
    }
}
