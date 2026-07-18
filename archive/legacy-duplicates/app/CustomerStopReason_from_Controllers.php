<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerStopReason extends Model
{
    protected $table = 'customer_stop_reasons';

    protected $fillable = [
        'code',
        'name',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];
}
