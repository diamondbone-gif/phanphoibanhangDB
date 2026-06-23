<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommissionStatus extends Model
{
    protected $table = 'commission_statuses';

    protected $guarded = [];

    public function commissions(): HasMany
    {
        return $this->hasMany(CustomerCommission::class, 'commission_status_id');
    }
}
