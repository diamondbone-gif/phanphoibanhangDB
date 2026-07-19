<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerInvoice extends Model
{
    protected $table = 'customer_invoices';

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
    | Khi dùng route /invoices/{invoice:invoice_code}, Laravel sẽ tìm theo
    | invoice_code thay vì ID.
    |--------------------------------------------------------------------------
    */

    public function getRouteKeyName(): string
    {
        return 'invoice_code';
    }

    /*
    |--------------------------------------------------------------------------
    | ĐƠN HÀNG
    |--------------------------------------------------------------------------
    */

    public function order()
    {
        return $this->belongsTo(\App\Models\CustomerOrder::class, 'customer_order_id');
    }

    /*
    |--------------------------------------------------------------------------
    | KHÁCH HÀNG
    |--------------------------------------------------------------------------
    */

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
    }

    /*
    |--------------------------------------------------------------------------
    | NGƯỜI TẠO HÓA ĐƠN
    |--------------------------------------------------------------------------
    */

    public function creator()
    {
        return $this->belongsTo(\App\Models\OperationManager::class, 'created_by');
    }
}
