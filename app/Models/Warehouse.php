<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['is_default' => 'boolean', 'is_active' => 'boolean'];

    public function stocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }
}
