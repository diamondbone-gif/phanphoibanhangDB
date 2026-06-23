<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuyForOption extends Model
{
    protected $table = 'buy_for_options';

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
