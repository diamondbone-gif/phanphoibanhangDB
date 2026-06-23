<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerSourceChannel extends Model
{
    protected $table = 'customer_source_channels';

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
