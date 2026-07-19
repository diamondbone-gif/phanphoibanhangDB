<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductPromotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductPromotionController extends Controller
{
    public function index(Request $request): View
    {
        $promotions = ProductPromotion::query()->with(['items.product'])
            ->when($request->filled('keyword'), fn ($q) => $q->where(fn ($sub) => $sub->where('code', 'like', '%'.trim($request->keyword).'%')->orWhere('name', 'like', '%'.trim($request->keyword).'%')))
            ->latest()->paginate(12)->withQueryString();
        $products = Product::active()->orderBy('product_name')->get(['id', 'product_code', 'product_name', 'price']);

        return view('admin.auth.product-promotions.index', compact('promotions', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->persist(new ProductPromotion, $this->validated($request));

        return back()->with('success', 'Đã tạo chương trình combo/khuyến mãi.');
    }

    public function update(Request $request, ProductPromotion $promotion): RedirectResponse
    {
        $this->persist($promotion, $this->validated($request, $promotion->id));

        return back()->with('success', 'Đã cập nhật chương trình.');
    }

    public function destroy(ProductPromotion $promotion): RedirectResponse
    {
        $promotion->delete();

        return back()->with('success', 'Đã xóa chương trình.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('product_promotions', 'code')->ignore($ignoreId)],
            'name' => ['required', 'string', 'max:180'],
            'promotion_type' => ['required', Rule::in(['combo', 'product_discount'])],
            'discount_type' => ['required', Rule::in(['percent', 'fixed_amount', 'fixed_price'])],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'minimum_order_amount' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'description' => ['nullable', 'string', 'max:3000'],
            'is_active' => ['nullable', 'boolean'],
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'quantities' => ['required', 'array'],
            'quantities.*' => ['required', 'integer', 'min:1'],
            'gift_product_ids' => ['nullable', 'array'],
        ]);

        if ($data['discount_type'] === 'percent' && (float) $data['discount_value'] > 100) {
            throw ValidationException::withMessages(['discount_value' => 'Giảm theo phần trăm không được vượt quá 100%.']);
        }
        if ($data['promotion_type'] === 'combo' && count($data['product_ids']) < 2) {
            throw ValidationException::withMessages(['product_ids' => 'Combo phải có ít nhất 2 sản phẩm.']);
        }

        return $data;
    }

    private function persist(ProductPromotion $promotion, array $data): void
    {
        DB::transaction(function () use ($promotion, $data) {
            $promotion->fill(collect($data)->except(['product_ids', 'quantities', 'gift_product_ids'])->all());
            $promotion->is_active = (bool) ($data['is_active'] ?? false);
            $promotion->minimum_order_amount = $data['minimum_order_amount'] ?? 0;
            $promotion->save();
            $promotion->items()->delete();
            $giftIds = array_map('intval', $data['gift_product_ids'] ?? []);
            foreach ($data['product_ids'] as $index => $productId) {
                $promotion->items()->create([
                    'product_id' => $productId,
                    'quantity' => $data['quantities'][$index] ?? 1,
                    'is_gift' => in_array((int) $productId, $giftIds, true),
                ]);
            }
        });
    }
}
