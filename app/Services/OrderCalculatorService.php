<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Arr;
use App\Support\Money;
use InvalidArgumentException;

class OrderCalculatorService
{
    public function calculate(array $data): array
    {
        $items = [];
        $subtotalCents = 0;
        $productDiscountCents = 0;

        foreach ($data['items'] as $row) {
            $product = Product::query()
                ->where('is_active', true)
                ->findOrFail($row['product_id']);

            $quantity = max(1, (int) $row['quantity']);
            $unitPriceCents = Money::cents($product->price);

            $discountBasisPoints = Money::percentBasisPoints(Arr::get($row, 'discount_percent', 0));

            if (!$product->is_discountable) {
                $discountBasisPoints = 0;
            }

            $originalTotalCents = $unitPriceCents * $quantity;
            $discountCents = Money::percentage($originalTotalCents, $discountBasisPoints);
            $finalTotalCents = max(0, $originalTotalCents - $discountCents);

            $subtotalCents += $originalTotalCents;
            $productDiscountCents += $discountCents;

            $items[] = [
                'product' => $product,
                'product_id' => $product->id,
                'product_code' => $product->product_code,
                'product_name' => $product->product_name,
                'quantity' => $quantity,
                'unit_price' => Money::decimal($unitPriceCents),
                'original_total' => Money::decimal($originalTotalCents),
                'discount_type' => $discountBasisPoints > 0 ? 'product' : 'none',
                'discount_percent' => Money::decimal($discountBasisPoints),
                'discount_amount' => Money::decimal($discountCents),
                'final_total' => Money::decimal($finalTotalCents),
            ];
        }

        if (count($items) === 0) {
            throw new InvalidArgumentException('Đơn hàng phải có ít nhất 1 sản phẩm.');
        }

        $orderDiscountBasisPoints = Money::percentBasisPoints(Arr::get($data, 'order_discount_percent', 0));

        $afterProductDiscountCents = max(0, $subtotalCents - $productDiscountCents);
        $orderDiscountCents = Money::percentage($afterProductDiscountCents, $orderDiscountBasisPoints);

        $finalAmountCents = max(0, $afterProductDiscountCents - $orderDiscountCents);

        $paidAmountCents = max(0, min($finalAmountCents, Money::cents(Arr::get($data, 'paid_amount', 0))));

        $debtAmountCents = max(0, $finalAmountCents - $paidAmountCents);

        return [
            'items' => $items,
            'subtotal_amount' => Money::decimal($subtotalCents),
            'product_discount_amount' => Money::decimal($productDiscountCents),
            'combo_discount_amount' => '0.00',
            'order_discount_percent' => Money::decimal($orderDiscountBasisPoints),
            'order_discount_amount' => Money::decimal($orderDiscountCents),
            'final_amount' => Money::decimal($finalAmountCents),
            'paid_amount' => Money::decimal($paidAmountCents),
            'debt_amount' => Money::decimal($debtAmountCents),
        ];
    }
}
