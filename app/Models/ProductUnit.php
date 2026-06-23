<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductUnit extends Model
{
    use SoftDeletes;

    protected $table = 'product_units';

    protected $fillable = [
        'unit_code',
        'unit_name',
        'description',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'product_unit_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function createdBy()
    {
        return $this->belongsTo(OperationManager::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(OperationManager::class, 'updated_by');
    }
}
