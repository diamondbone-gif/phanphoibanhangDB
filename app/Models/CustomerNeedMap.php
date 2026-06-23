<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerNeedMap extends Model
{
    protected $table = 'customer_need_maps';

    protected $fillable = [
        'customer_id',
        'customer_need_id',
        'note',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function need(): BelongsTo
    {
        return $this->belongsTo(CustomerNeed::class, 'customer_need_id');
    }
}
