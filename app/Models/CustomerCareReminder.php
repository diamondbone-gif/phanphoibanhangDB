<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerCareReminder extends Model
{
    protected $table = 'customer_care_reminders';

    protected $fillable = [
        'customer_id',
        'assigned_staff_id',
        'reminder_date',
        'reminder_time',
        'content',
        'care_priority_id',
        'care_status_id',
        'completed_at',
    ];

    protected $casts = [
        'reminder_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(OperationManager::class, 'assigned_staff_id');
    }
}
