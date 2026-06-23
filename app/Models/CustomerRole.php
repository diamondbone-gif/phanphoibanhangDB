<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerRole extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'sort_order',
        'is_active',
    ];
}
