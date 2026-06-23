<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CustomerNeed extends Model
{
    protected $table = 'customer_needs';

    protected $fillable = [
        'code',
        'name',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function customerNeedMaps(): HasMany
    {
        return $this->hasMany(CustomerNeedMap::class, 'customer_need_id');
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(
            Customer::class,
            'customer_need_maps',
            'customer_need_id',
            'customer_id'
        );
    }
}
