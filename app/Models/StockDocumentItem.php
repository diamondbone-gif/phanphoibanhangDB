<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockDocumentItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['unit_cost' => 'decimal:2', 'total_cost' => 'decimal:2'];

    public function document(): BelongsTo
    {
        return $this->belongsTo(StockDocument::class, 'stock_document_id');
    }
}
