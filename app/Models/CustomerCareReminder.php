<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerCareReminder extends Model
{
    /**
     * Tên bảng dữ liệu mà Model sử dụng.
     *
     * @var string
     */
    protected $table = 'customer_care_reminders';

    /**
     * Các trường được phép thêm mới hoặc cập nhật hàng loạt.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'care_log_id',
        'assigned_staff_id',
        'reminder_date',
        'reminder_time',
        'content',
        'care_priority_id',
        'care_status_id',
        'completed_at',
        'notified_at',
        'snoozed_until',
    ];

    /**
     * Chuyển đổi kiểu dữ liệu tự động.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'reminder_date' => 'date',
        'completed_at' => 'datetime',
        'notified_at' => 'datetime',
        'snoozed_until' => 'datetime',
    ];

    /**
     * Khách hàng cần được chăm sóc.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            Customer::class,
            'customer_id'
        );
    }

    /**
     * Nội dung chăm sóc đã tạo ra lịch nhắc này.
     */
    public function careLog(): BelongsTo
    {
        return $this->belongsTo(
            CustomerCareLog::class,
            'care_log_id'
        );
    }

    /**
     * Nhân viên được giao phụ trách lịch nhắc.
     */
    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(
            OperationManager::class,
            'assigned_staff_id'
        );
    }
}
