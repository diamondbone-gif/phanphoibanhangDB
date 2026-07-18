<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\CustomerCareLog;
use App\Models\CustomerCareReminder;
use App\Models\CustomerCommission;
use App\Models\CustomerCommissionAdjustment;
use App\Models\CustomerInvoice;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderReturn;
use App\Models\FinancialTransaction;
use App\Models\Payment;
use App\Models\ProductBatch;
use App\Models\ProductStockMovement;
use App\Models\StockDocument;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Observers\AuditObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([
            Customer::class,
            CustomerCareLog::class,
            CustomerCareReminder::class,
            CustomerOrder::class,
            CustomerOrderReturn::class,
            FinancialTransaction::class,
            CustomerCommission::class,
            CustomerCommissionAdjustment::class,
            CustomerInvoice::class,
            Payment::class,
            ProductBatch::class,
            ProductStockMovement::class,
            WarehouseStock::class,
            Warehouse::class,
            StockDocument::class,
        ] as $model) {
            $model::observe(AuditObserver::class);
        }
    }
}
