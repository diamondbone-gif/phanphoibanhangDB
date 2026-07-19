<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerCareLog extends Model
{
    protected $table = 'customer_care_logs';

    protected $fillable = [
        'customer_id',
        'staff_id',
        'care_channel_id',
        'care_date',
        'content',
        'internal_note',
        'next_follow_up_at',
        'care_priority_id',
        'care_status_id',
    ];

    protected $casts = [
        'care_date' => 'datetime',
        'next_follow_up_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(OperationManager::class, 'staff_id');
    }
}