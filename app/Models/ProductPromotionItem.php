<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPromotionItem extends Model
{
    protected $fillable = ['product_promotion_id', 'product_id', 'quantity', 'is_gift'];

    protected $casts = ['quantity' => 'integer', 'is_gift' => 'boolean'];

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(ProductPromotion::class, 'product_promotion_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
