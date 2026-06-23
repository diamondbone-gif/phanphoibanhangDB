<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CustomerOptionController;
use App\Http\Controllers\Admin\CustomerRoleStatusController;
use App\Http\Controllers\Admin\CtvController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\CustomerCommissionController;

/*
|--------------------------------------------------------------------------
| KHU VỰC CTV / NGƯỜI DÙNG THƯỜNG
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('ctv.home');


/*
|--------------------------------------------------------------------------
| ROUTE LOGIN MẶC ĐỊNH CỦA LARAVEL
|--------------------------------------------------------------------------
*/

Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');


/*
|--------------------------------------------------------------------------
| KHU VỰC ADMIN
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | ADMIN ROOT
    |--------------------------------------------------------------------------
    */

    Route::get('/', function () {
        if (auth('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('admin.login');
    })->name('root');


    /*
    |--------------------------------------------------------------------------
    | ĐĂNG NHẬP ADMIN
    |--------------------------------------------------------------------------
    | Không đặt login trong auth:admin vì chưa đăng nhập vẫn phải vào được.
    |--------------------------------------------------------------------------
    */

    Route::get('login', [AdminLoginController::class, 'showLoginForm'])
        ->name('login');

    Route::post('login', [AdminLoginController::class, 'login'])
        ->name('login.submit')
        ->middleware('throttle:5,1');


    /*
    |--------------------------------------------------------------------------
    | CÁC ROUTE BẮT BUỘC ĐĂNG NHẬP ADMIN
    |--------------------------------------------------------------------------
    */

    Route::middleware(['auth:admin'])->group(function () {

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD
        |--------------------------------------------------------------------------
        */

        Route::get('dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | QUẢN LÝ KHÁCH HÀNG
        |--------------------------------------------------------------------------
        */

        Route::get('customers', [CustomerController::class, 'index'])
            ->name('customers.index');

        Route::get('customers/create', [CustomerController::class, 'create'])
            ->name('customers.create');

        Route::post('customers', [CustomerController::class, 'store'])
            ->name('customers.store');

        Route::get('customers/check-referrer', [CustomerController::class, 'checkReferrer'])
            ->name('customers.check-referrer');

        Route::get('customers/{customer}', [CustomerController::class, 'show'])
            ->name('customers.show')
            ->whereNumber('customer');

        Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])
            ->name('customers.edit')
            ->whereNumber('customer');

        Route::put('customers/{customer}', [CustomerController::class, 'update'])
            ->name('customers.update')
            ->whereNumber('customer');

        Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])
            ->name('customers.destroy')
            ->whereNumber('customer');

        Route::post('customers/{customer}/convert-to-ctv', [CustomerController::class, 'convertToCtv'])
            ->name('customers.convert-to-ctv')
            ->whereNumber('customer');

        Route::post('customers/{customer}/mark-stopped-buying', [CustomerController::class, 'markStoppedBuying'])
            ->name('customers.mark-stopped-buying')
            ->whereNumber('customer');


        /*
        |--------------------------------------------------------------------------
        | DANH MỤC TÙY CHỌN KHÁCH HÀNG
        |--------------------------------------------------------------------------
        */

        Route::get('customer-options', [CustomerOptionController::class, 'index'])
            ->name('customer-options.index');

        Route::post('customer-options/{type}', [CustomerOptionController::class, 'store'])
            ->name('customer-options.store')
            ->where('type', 'identity|buy_for|product|need|note')
            ->middleware('signed');

        Route::put('customer-options/{type}/{id}', [CustomerOptionController::class, 'update'])
            ->name('customer-options.update')
            ->where('type', 'identity|buy_for|product|need|note')
            ->whereNumber('id')
            ->middleware('signed');

        Route::delete('customer-options/{type}/{id}', [CustomerOptionController::class, 'destroy'])
            ->name('customer-options.destroy')
            ->where('type', 'identity|buy_for|product|need|note')
            ->whereNumber('id')
            ->middleware('signed');


        /*
        |--------------------------------------------------------------------------
        | VAI TRÒ / TRẠNG THÁI KHÁCH HÀNG
        |--------------------------------------------------------------------------
        */

        Route::get('role-status-options', [CustomerRoleStatusController::class, 'index'])
            ->name('role-status-options.index');

        Route::post('role-status-options/{type}', [CustomerRoleStatusController::class, 'store'])
            ->name('role-status-options.store')
            ->where('type', 'role|buy_status|customer_status|ctv_status')
            ->middleware('signed');

        Route::put('role-status-options/{type}/{id}', [CustomerRoleStatusController::class, 'update'])
            ->name('role-status-options.update')
            ->where('type', 'role|buy_status|customer_status|ctv_status')
            ->whereNumber('id')
            ->middleware('signed');

        Route::delete('role-status-options/{type}/{id}', [CustomerRoleStatusController::class, 'destroy'])
            ->name('role-status-options.destroy')
            ->where('type', 'role|buy_status|customer_status|ctv_status')
            ->whereNumber('id')
            ->middleware('signed');


        /*
        |--------------------------------------------------------------------------
        | QUẢN LÝ CỘNG TÁC VIÊN
        |--------------------------------------------------------------------------
        */

        Route::get('ctvs', [CtvController::class, 'index'])
            ->name('ctvs.index');

        Route::get('ctvs/{customer}/show', [CtvController::class, 'show'])
            ->name('ctvs.show')
            ->whereNumber('customer')
            ->middleware('signed');

        Route::get('ctvs/{ctv}/referred-customers/{referred}/show', [CtvController::class, 'referredShow'])
            ->name('ctvs.referred-customers.show')
            ->whereNumber('ctv')
            ->whereNumber('referred')
            ->middleware('signed');


        /*
        |--------------------------------------------------------------------------
        | HOA HỒNG CỘNG TÁC VIÊN
        |--------------------------------------------------------------------------
        */

        Route::get('customer-commissions', [CustomerCommissionController::class, 'index'])
            ->name('customer-commissions.index');

        Route::post('customer-commissions/{commission}/mark-paid', [CustomerCommissionController::class, 'markPaid'])
            ->name('customer-commissions.mark-paid')
            ->whereNumber('commission');

        Route::post('customer-commissions/{commission}/mark-unpaid', [CustomerCommissionController::class, 'markUnpaid'])
            ->name('customer-commissions.mark-unpaid')
            ->whereNumber('commission');


        /*
        |--------------------------------------------------------------------------
        | KHO SẢN PHẨM - DANH SÁCH SẢN PHẨM
        |--------------------------------------------------------------------------
        */

        Route::get('products', [ProductController::class, 'index'])
            ->name('products.index');

        Route::get('products/table', [ProductController::class, 'table'])
            ->name('products.table');

        Route::post('products', [ProductController::class, 'store'])
            ->name('products.store');

        Route::get('products/{product}/edit', [ProductController::class, 'edit'])
            ->name('products.edit')
            ->whereNumber('product');

        Route::put('products/{product}', [ProductController::class, 'update'])
            ->name('products.update')
            ->whereNumber('product');

        Route::delete('products/{product}', [ProductController::class, 'destroy'])
            ->name('products.destroy')
            ->whereNumber('product');

        Route::patch('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])
            ->name('products.toggle-status')
            ->whereNumber('product');


        /*
        |--------------------------------------------------------------------------
        | KHO SẢN PHẨM - QUẢN LÝ TỒN KHO
        |--------------------------------------------------------------------------
        */

        Route::get('inventory', [ProductController::class, 'inventory'])
            ->name('inventory.index');

        Route::get('inventory/table', [ProductController::class, 'inventoryTable'])
            ->name('inventory.table');

        Route::post('inventory/import-stock', [ProductController::class, 'importStock'])
            ->name('inventory.import-stock');

        Route::get('inventory/batches/{batch}/edit', [ProductController::class, 'editBatch'])
            ->name('inventory.batches.edit')
            ->whereNumber('batch');

        Route::put('inventory/batches/{batch}', [ProductController::class, 'updateBatch'])
            ->name('inventory.batches.update')
            ->whereNumber('batch');

        Route::patch('inventory/batches/{batch}/toggle-status', [ProductController::class, 'toggleBatchStatus'])
            ->name('inventory.batches.toggle-status')
            ->whereNumber('batch');

        Route::delete('inventory/batches/{batch}', [ProductController::class, 'destroyBatch'])
            ->name('inventory.batches.destroy')
            ->whereNumber('batch');

        Route::get('inventory/movement-history', [ProductController::class, 'movementHistory'])
            ->name('inventory.movement-history');


        /*
        |--------------------------------------------------------------------------
        | BÁN HÀNG - ĐƠN HÀNG
        |--------------------------------------------------------------------------
        */

        Route::get('sales/customers/search', [OrderController::class, 'searchCustomers'])
            ->name('orders.customers.search');

        Route::get('sales/orders', [OrderController::class, 'index'])
            ->name('orders.index');

        Route::get('sales/orders/create', [OrderController::class, 'create'])
            ->name('orders.create');

        Route::post('sales/orders', [OrderController::class, 'store'])
            ->name('orders.store');

        Route::get('sales/orders/{order:order_code}', [OrderController::class, 'show'])
            ->name('orders.show');

        Route::get('sales/orders/{order:order_code}/edit', [OrderController::class, 'edit'])
            ->name('orders.edit');

        Route::put('sales/orders/{order:order_code}', [OrderController::class, 'update'])
            ->name('orders.update');

        Route::patch('sales/orders/{order:order_code}/complete', [OrderController::class, 'complete'])
            ->name('orders.complete');

        Route::patch('sales/orders/{order:order_code}/cancel', [OrderController::class, 'cancel'])
            ->name('orders.cancel');

        Route::delete('sales/orders/{order:order_code}', [OrderController::class, 'destroy'])
            ->name('orders.destroy');


        /*
        |--------------------------------------------------------------------------
        | HÓA ĐƠN
        |--------------------------------------------------------------------------
        */

        Route::get('invoices/{invoice:invoice_code}/print', [InvoiceController::class, 'print'])
            ->name('invoices.print');


        /*
        |--------------------------------------------------------------------------
        | ĐĂNG XUẤT ADMIN
        |--------------------------------------------------------------------------
        */

        Route::post('logout', [AdminLoginController::class, 'logout'])
            ->name('logout');
    });
});
