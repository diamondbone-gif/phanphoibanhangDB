<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerCareLog extends Model
{
    /**
     * Tên bảng dữ liệu mà Model sử dụng.
     *
     * @var string
     */
    protected $table = 'customer_care_logs';

    /**
     * Các trường được phép thêm mới hoặc cập nhật hàng loạt.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'log_type',
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

    /**
     * Chuyển đổi kiểu dữ liệu tự động.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'care_date' => 'datetime',
        'next_follow_up_at' => 'datetime',
    ];

    /**
     * Khách hàng thuộc nội dung chăm sóc, tư vấn này.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            Customer::class,
            'customer_id'
        );
    }

    /**
     * Nhân viên thực hiện chăm sóc, tư vấn khách hàng.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(
            OperationManager::class,
            'staff_id'
        );
    }

    /**
     * Các lịch nhắc chăm sóc được tạo từ nội dung tư vấn này.
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(
            CustomerCareReminder::class,
            'care_log_id'
        );
    }
}
