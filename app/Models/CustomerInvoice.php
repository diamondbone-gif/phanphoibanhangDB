<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerInvoice extends Model
{
    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    | Khai báo model này dùng bảng customer_invoices.
    |--------------------------------------------------------------------------
    */
    protected $table = 'customer_invoices';

    /*
    |--------------------------------------------------------------------------
    | FILLABLE
    |--------------------------------------------------------------------------
    | Các cột được phép thêm/sửa bằng create() hoặc update().
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'invoice_code',
        'customer_order_id',
        'customer_id',
        'invoice_date',
        'total_amount',
        'tax_amount',
        'final_amount',
        'status',
        'note',
        'created_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    | Ép kiểu dữ liệu khi lấy ra từ database.
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'invoice_date' => 'date',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | ROUTE MODEL BINDING
    |--------------------------------------------------------------------------
    | Khi route dùng {invoice}, Laravel sẽ tìm hóa đơn bằng invoice_code
    | thay vì tìm bằng id.
    |
    | Ví dụ route:
    | /admin/invoices/{invoice:invoice_code}/print
    |--------------------------------------------------------------------------
    */
    public function getRouteKeyName(): string
    {
        return 'invoice_code';
    }

    /*
    |--------------------------------------------------------------------------
    | QUAN HỆ: HÓA ĐƠN THUỘC VỀ ĐƠN HÀNG
    |--------------------------------------------------------------------------
    | Một hóa đơn thuộc về một đơn hàng.
    |--------------------------------------------------------------------------
    */
    public function order(): BelongsTo
    {
        return $this->belongsTo(CustomerOrder::class, 'customer_order_id');
    }

    /*
    |--------------------------------------------------------------------------
    | QUAN HỆ: HÓA ĐƠN THUỘC VỀ KHÁCH HÀNG
    |--------------------------------------------------------------------------
    | Một hóa đơn thuộc về một khách hàng.
    |--------------------------------------------------------------------------
    */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /*
    |--------------------------------------------------------------------------
    | QUAN HỆ: NGƯỜI TẠO HÓA ĐƠN
    |--------------------------------------------------------------------------
    | created_by lưu id của admin/người vận hành tạo hóa đơn.
    |--------------------------------------------------------------------------
    */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(OperationManager::class, 'created_by');
    }
}
