<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class OrderCalculatorService
{
    public function calculate(array $data): array
    {
        $items = [];
        $subtotal = 0;
        $productDiscountAmount = 0;

        foreach ($data['items'] as $row) {
            $product = Product::query()
                ->where('is_active', true)
                ->findOrFail($row['product_id']);

            $quantity = max(1, (int) $row['quantity']);
            $unitPrice = (float) $product->price;

            $discountPercent = (float) Arr::get($row, 'discount_percent', 0);
            $discountPercent = max(0, min(100, $discountPercent));

            if (!$product->is_discountable) {
                $discountPercent = 0;
            }

            $originalTotal = $unitPrice * $quantity;
            $discountAmount = round($originalTotal * $discountPercent / 100);
            $finalTotal = max(0, $originalTotal - $discountAmount);

            $subtotal += $originalTotal;
            $productDiscountAmount += $discountAmount;

            $items[] = [
                'product' => $product,
                'product_id' => $product->id,
                'product_code' => $product->product_code,
                'product_name' => $product->product_name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'original_total' => $originalTotal,
                'discount_type' => $discountPercent > 0 ? 'product' : 'none',
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                'final_total' => $finalTotal,
            ];
        }

        if (count($items) === 0) {
            throw new InvalidArgumentException('Đơn hàng phải có ít nhất 1 sản phẩm.');
        }

        $orderDiscountPercent = (float) Arr::get($data, 'order_discount_percent', 0);
        $orderDiscountPercent = max(0, min(100, $orderDiscountPercent));

        $afterProductDiscount = max(0, $subtotal - $productDiscountAmount);
        $orderDiscountAmount = round($afterProductDiscount * $orderDiscountPercent / 100);

        $finalAmount = max(0, $afterProductDiscount - $orderDiscountAmount);

        $paidAmount = (float) Arr::get($data, 'paid_amount', 0);
        $paidAmount = max(0, min($finalAmount, $paidAmount));

        $debtAmount = max(0, $finalAmount - $paidAmount);

        return [
            'items' => $items,
            'subtotal_amount' => $subtotal,
            'product_discount_amount' => $productDiscountAmount,
            'combo_discount_amount' => 0,
            'order_discount_percent' => $orderDiscountPercent,
            'order_discount_amount' => $orderDiscountAmount,
            'final_amount' => $finalAmount,
            'paid_amount' => $paidAmount,
            'debt_amount' => $debtAmount,
        ];
    }
}
