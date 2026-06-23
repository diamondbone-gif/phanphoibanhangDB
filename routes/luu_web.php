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

/*
|--------------------------------------------------------------------------
| KHU VỰC CTV / NGƯỜI DÙNG THƯỜNG
|--------------------------------------------------------------------------
| Đây là trang ngoài public, không cần đăng nhập admin.
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| GET /
|--------------------------------------------------------------------------
| Đường dẫn trang chủ dành cho CTV hoặc người dùng thường.
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('ctv.home');


/*
|--------------------------------------------------------------------------
| ROUTE LOGIN MẶC ĐỊNH CỦA LARAVEL
|--------------------------------------------------------------------------
| Khi Laravel tự redirect về route "login" thì chuyển sang trang login admin.
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| GET /login
|--------------------------------------------------------------------------
| Route mặc định của Laravel.
| Nếu hệ thống gọi route login thì tự chuyển về /admin/login.
|--------------------------------------------------------------------------
*/
Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');


/*
|--------------------------------------------------------------------------
| KHU VỰC ADMIN
|--------------------------------------------------------------------------
| Tất cả đường dẫn trong group này đều bắt đầu bằng /admin.
| Tên route đều bắt đầu bằng admin.
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | ADMIN ROOT
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | GET /admin
    |--------------------------------------------------------------------------
    | Nếu admin đã đăng nhập thì chuyển vào dashboard.
    | Nếu chưa đăng nhập thì chuyển về trang login admin.
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
    | Các route login không đặt trong middleware auth:admin.
    | Vì chưa đăng nhập vẫn phải vào được trang login.
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | GET /admin/login
    |--------------------------------------------------------------------------
    | Hiển thị form đăng nhập admin.
    |--------------------------------------------------------------------------
    */
    Route::get('login', [AdminLoginController::class, 'showLoginForm'])
        ->name('login');

    /*
    |--------------------------------------------------------------------------
    | POST /admin/login
    |--------------------------------------------------------------------------
    | Xử lý đăng nhập admin.
    | Có throttle:5,1 để giới hạn 5 lần đăng nhập trong 1 phút.
    |--------------------------------------------------------------------------
    */
    Route::post('login', [AdminLoginController::class, 'login'])
        ->name('login.submit')
        ->middleware('throttle:5,1');


    /*
    |--------------------------------------------------------------------------
    | CÁC ROUTE BẮT BUỘC ĐĂNG NHẬP ADMIN
    |--------------------------------------------------------------------------
    | Tất cả route trong group này đều có middleware auth:admin.
    | Admin chưa đăng nhập sẽ không truy cập được.
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:admin')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD
        |--------------------------------------------------------------------------
        */

        /*
        |--------------------------------------------------------------------------
        | GET /admin/dashboard
        |--------------------------------------------------------------------------
        | Hiển thị trang dashboard quản trị.
        |--------------------------------------------------------------------------
        */
        Route::get('dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | QUẢN LÝ KHÁCH HÀNG
        |--------------------------------------------------------------------------
        */

        /*
        |--------------------------------------------------------------------------
        | GET /admin/customers
        |--------------------------------------------------------------------------
        | Hiển thị danh sách khách hàng.
        |--------------------------------------------------------------------------
        */
        Route::get('customers', [CustomerController::class, 'index'])
            ->name('customers.index');

        /*
        |--------------------------------------------------------------------------
        | GET /admin/customers/create
        |--------------------------------------------------------------------------
        | Hiển thị form thêm khách hàng mới.
        |--------------------------------------------------------------------------
        */
        Route::get('customers/create', [CustomerController::class, 'create'])
            ->name('customers.create');

        /*
        |--------------------------------------------------------------------------
        | POST /admin/customers
        |--------------------------------------------------------------------------
        | Lưu khách hàng mới vào database.
        |--------------------------------------------------------------------------
        */
        Route::post('customers', [CustomerController::class, 'store'])
            ->name('customers.store');

        /*
        |--------------------------------------------------------------------------
        | POST /admin/customers/check-referrer
        |--------------------------------------------------------------------------
        | Kiểm tra số điện thoại người giới thiệu / CTV.
        |--------------------------------------------------------------------------
        */
        Route::post('customers/check-referrer', [CustomerController::class, 'checkReferrer'])
            ->name('customers.check-referrer');

        /*
        |--------------------------------------------------------------------------
        | POST /admin/customers/{customer}/convert-to-ctv
        |--------------------------------------------------------------------------
        | Chuyển khách hàng thành cộng tác viên.
        | {customer} là id khách hàng.
        | Route có signed nên nên tạo link bằng URL::signedRoute().
        |--------------------------------------------------------------------------
        */
        Route::post('customers/{customer}/convert-to-ctv', [CustomerController::class, 'convertToCtv'])
            ->name('customers.convert-to-ctv')
            ->whereNumber('customer')
            ->middleware('signed');

        /*
        |--------------------------------------------------------------------------
        | POST /admin/customers/{customer}/mark-stopped-buying
        |--------------------------------------------------------------------------
        | Đánh dấu khách hàng ngưng mua hàng.
        | {customer} là id khách hàng.
        | Route có signed nên nên tạo link bằng URL::signedRoute().
        |--------------------------------------------------------------------------
        */
        Route::post('customers/{customer}/mark-stopped-buying', [CustomerController::class, 'markStoppedBuying'])
            ->name('customers.mark-stopped-buying')
            ->whereNumber('customer')
            ->middleware('signed');

        /*
        |--------------------------------------------------------------------------
        | GET /admin/customers/{customer}/show
        |--------------------------------------------------------------------------
        | Xem chi tiết một khách hàng.
        | {customer} là id khách hàng.
        |--------------------------------------------------------------------------
        */
        Route::get('customers/{customer}/show', [CustomerController::class, 'show'])
            ->name('customers.show')
            ->whereNumber('customer')
            ->middleware('signed');

        /*
        |--------------------------------------------------------------------------
        | GET /admin/customers/{customer}/edit
        |--------------------------------------------------------------------------
        | Hiển thị form sửa thông tin khách hàng.
        | {customer} là id khách hàng.
        |--------------------------------------------------------------------------
        */
        Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])
            ->name('customers.edit')
            ->whereNumber('customer')
            ->middleware('signed');

        /*
        |--------------------------------------------------------------------------
        | PUT /admin/customers/{customer}
        |--------------------------------------------------------------------------
        | Cập nhật thông tin khách hàng.
        | {customer} là id khách hàng.
        |--------------------------------------------------------------------------
        */
        Route::put('customers/{customer}', [CustomerController::class, 'update'])
            ->name('customers.update')
            ->whereNumber('customer')
            ->middleware('signed');


        /*
        |--------------------------------------------------------------------------
        | DANH MỤC TÙY CHỌN KHÁCH HÀNG
        |--------------------------------------------------------------------------
        */

        /*
        |--------------------------------------------------------------------------
        | GET /admin/customer-options
        |--------------------------------------------------------------------------
        | Hiển thị trang quản lý các tùy chọn khách hàng.
        |--------------------------------------------------------------------------
        */
        Route::get('customer-options', [CustomerOptionController::class, 'index'])
            ->name('customer-options.index');

        /*
        |--------------------------------------------------------------------------
        | POST /admin/customer-options/{type}
        |--------------------------------------------------------------------------
        | Thêm mới một tùy chọn khách hàng theo type.
        | Type hợp lệ: identity, buy_for, product, need, note.
        |--------------------------------------------------------------------------
        */
        Route::post('customer-options/{type}', [CustomerOptionController::class, 'store'])
            ->name('customer-options.store')
            ->where('type', 'identity|buy_for|product|need|note')
            ->middleware('signed');

        /*
        |--------------------------------------------------------------------------
        | PUT /admin/customer-options/{type}/{id}
        |--------------------------------------------------------------------------
        | Cập nhật một tùy chọn khách hàng theo type và id.
        | Type hợp lệ: identity, buy_for, product, need, note.
        |--------------------------------------------------------------------------
        */
        Route::put('customer-options/{type}/{id}', [CustomerOptionController::class, 'update'])
            ->name('customer-options.update')
            ->where('type', 'identity|buy_for|product|need|note')
            ->whereNumber('id')
            ->middleware('signed');

        /*
        |--------------------------------------------------------------------------
        | DELETE /admin/customer-options/{type}/{id}
        |--------------------------------------------------------------------------
        | Xóa một tùy chọn khách hàng theo type và id.
        | Type hợp lệ: identity, buy_for, product, need, note.
        |--------------------------------------------------------------------------
        */
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

        /*
        |--------------------------------------------------------------------------
        | GET /admin/role-status-options
        |--------------------------------------------------------------------------
        | Hiển thị trang quản lý vai trò và trạng thái khách hàng.
        |--------------------------------------------------------------------------
        */
        Route::get('role-status-options', [CustomerRoleStatusController::class, 'index'])
            ->name('role-status-options.index');

        /*
        |--------------------------------------------------------------------------
        | POST /admin/role-status-options/{type}
        |--------------------------------------------------------------------------
        | Thêm mới vai trò hoặc trạng thái theo type.
        | Type hợp lệ: role, buy_status, customer_status, ctv_status.
        |--------------------------------------------------------------------------
        */
        Route::post('role-status-options/{type}', [CustomerRoleStatusController::class, 'store'])
            ->name('role-status-options.store')
            ->where('type', 'role|buy_status|customer_status|ctv_status')
            ->middleware('signed');

        /*
        |--------------------------------------------------------------------------
        | PUT /admin/role-status-options/{type}/{id}
        |--------------------------------------------------------------------------
        | Cập nhật vai trò hoặc trạng thái theo type và id.
        | Type hợp lệ: role, buy_status, customer_status, ctv_status.
        |--------------------------------------------------------------------------
        */
        Route::put('role-status-options/{type}/{id}', [CustomerRoleStatusController::class, 'update'])
            ->name('role-status-options.update')
            ->where('type', 'role|buy_status|customer_status|ctv_status')
            ->whereNumber('id')
            ->middleware('signed');

        /*
        |--------------------------------------------------------------------------
        | DELETE /admin/role-status-options/{type}/{id}
        |--------------------------------------------------------------------------
        | Xóa vai trò hoặc trạng thái theo type và id.
        | Type hợp lệ: role, buy_status, customer_status, ctv_status.
        |--------------------------------------------------------------------------
        */
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

        /*
        |--------------------------------------------------------------------------
        | GET /admin/ctvs
        |--------------------------------------------------------------------------
        | Hiển thị danh sách cộng tác viên.
        |--------------------------------------------------------------------------
        */
        Route::get('ctvs', [CtvController::class, 'index'])
            ->name('ctvs.index');

        /*
        |--------------------------------------------------------------------------
        | GET /admin/ctvs/{customer}/show
        |--------------------------------------------------------------------------
        | Xem chi tiết cộng tác viên.
        | {customer} là id khách hàng đang là CTV.
        |--------------------------------------------------------------------------
        */
        Route::get('ctvs/{customer}/show', [CtvController::class, 'show'])
            ->name('ctvs.show')
            ->whereNumber('customer')
            ->middleware('signed');

        /*
        |--------------------------------------------------------------------------
        | GET /admin/ctvs/{ctv}/referred-customers/{referred}/show
        |--------------------------------------------------------------------------
        | Xem chi tiết khách hàng được CTV giới thiệu.
        | {ctv} là id CTV.
        | {referred} là id khách hàng được giới thiệu.
        |--------------------------------------------------------------------------
        */
        Route::get('ctvs/{ctv}/referred-customers/{referred}/show', [CtvController::class, 'referredShow'])
            ->name('ctvs.referred-customers.show')
            ->whereNumber('ctv')
            ->whereNumber('referred')
            ->middleware('signed');


        /*
        |--------------------------------------------------------------------------
        | KHO SẢN PHẨM - DANH SÁCH SẢN PHẨM
        |--------------------------------------------------------------------------
        */

        /*
        |--------------------------------------------------------------------------
        | GET /admin/products
        |--------------------------------------------------------------------------
        | Hiển thị danh sách sản phẩm.
        |--------------------------------------------------------------------------
        */
        Route::get('products', [ProductController::class, 'index'])
            ->name('products.index');

        /*
        |--------------------------------------------------------------------------
        | GET /admin/products/table
        |--------------------------------------------------------------------------
        | Trả về bảng sản phẩm.
        | Thường dùng cho AJAX reload danh sách sản phẩm.
        |--------------------------------------------------------------------------
        */
        Route::get('products/table', [ProductController::class, 'table'])
            ->name('products.table');

        /*
        |--------------------------------------------------------------------------
        | POST /admin/products
        |--------------------------------------------------------------------------
        | Lưu sản phẩm mới vào database.
        |--------------------------------------------------------------------------
        */
        Route::post('products', [ProductController::class, 'store'])
            ->name('products.store');

        /*
        |--------------------------------------------------------------------------
        | GET /admin/products/{product}/edit
        |--------------------------------------------------------------------------
        | Lấy thông tin sản phẩm để sửa.
        | {product} là id sản phẩm.
        |--------------------------------------------------------------------------
        */
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])
            ->name('products.edit')
            ->whereNumber('product');

        /*
        |--------------------------------------------------------------------------
        | PUT /admin/products/{product}
        |--------------------------------------------------------------------------
        | Cập nhật thông tin sản phẩm.
        | {product} là id sản phẩm.
        |--------------------------------------------------------------------------
        */
        Route::put('products/{product}', [ProductController::class, 'update'])
            ->name('products.update')
            ->whereNumber('product');

        /*
        |--------------------------------------------------------------------------
        | DELETE /admin/products/{product}
        |--------------------------------------------------------------------------
        | Xóa sản phẩm.
        | {product} là id sản phẩm.
        |--------------------------------------------------------------------------
        */
        Route::delete('products/{product}', [ProductController::class, 'destroy'])
            ->name('products.destroy')
            ->whereNumber('product');

        /*
        |--------------------------------------------------------------------------
        | PATCH /admin/products/{product}/toggle-status
        |--------------------------------------------------------------------------
        | Bật hoặc tắt trạng thái hoạt động của sản phẩm.
        | {product} là id sản phẩm.
        |--------------------------------------------------------------------------
        */
        Route::patch('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])
            ->name('products.toggle-status')
            ->whereNumber('product');


        /*
        |--------------------------------------------------------------------------
        | KHO SẢN PHẨM - QUẢN LÝ TỒN KHO
        |--------------------------------------------------------------------------
        */

        /*
        |--------------------------------------------------------------------------
        | GET /admin/inventory
        |--------------------------------------------------------------------------
        | Hiển thị trang quản lý tồn kho.
        |--------------------------------------------------------------------------
        */
        Route::get('inventory', [ProductController::class, 'inventory'])
            ->name('inventory.index');

        /*
        |--------------------------------------------------------------------------
        | GET /admin/inventory/table
        |--------------------------------------------------------------------------
        | Trả về bảng tồn kho.
        | Thường dùng cho AJAX reload danh sách tồn kho.
        |--------------------------------------------------------------------------
        */
        Route::get('inventory/table', [ProductController::class, 'inventoryTable'])
            ->name('inventory.table');

        /*
        |--------------------------------------------------------------------------
        | POST /admin/inventory/import-stock
        |--------------------------------------------------------------------------
        | Nhập kho / tạo lô hàng mới cho sản phẩm.
        |--------------------------------------------------------------------------
        */
        Route::post('inventory/import-stock', [ProductController::class, 'importStock'])
            ->name('inventory.import-stock');

        /*
        |--------------------------------------------------------------------------
        | GET /admin/inventory/batches/{batch}/edit
        |--------------------------------------------------------------------------
        | Lấy thông tin lô hàng để sửa.
        | {batch} là id lô hàng.
        |--------------------------------------------------------------------------
        */
        Route::get('inventory/batches/{batch}/edit', [ProductController::class, 'editBatch'])
            ->name('inventory.batches.edit')
            ->whereNumber('batch');

        /*
        |--------------------------------------------------------------------------
        | PUT /admin/inventory/batches/{batch}
        |--------------------------------------------------------------------------
        | Cập nhật thông tin lô hàng.
        | {batch} là id lô hàng.
        |--------------------------------------------------------------------------
        */
        Route::put('inventory/batches/{batch}', [ProductController::class, 'updateBatch'])
            ->name('inventory.batches.update')
            ->whereNumber('batch');

        /*
        |--------------------------------------------------------------------------
        | PATCH /admin/inventory/batches/{batch}/toggle-status
        |--------------------------------------------------------------------------
        | Bật hoặc tắt trạng thái của lô hàng.
        | {batch} là id lô hàng.
        |--------------------------------------------------------------------------
        */
        Route::patch('inventory/batches/{batch}/toggle-status', [ProductController::class, 'toggleBatchStatus'])
            ->name('inventory.batches.toggle-status')
            ->whereNumber('batch');

        /*
        |--------------------------------------------------------------------------
        | DELETE /admin/inventory/batches/{batch}
        |--------------------------------------------------------------------------
        | Xóa lô hàng.
        | {batch} là id lô hàng.
        |--------------------------------------------------------------------------
        */
        Route::delete('inventory/batches/{batch}', [ProductController::class, 'destroyBatch'])
            ->name('inventory.batches.destroy')
            ->whereNumber('batch');

        /*
        |--------------------------------------------------------------------------
        | GET /admin/inventory/movement-history
        |--------------------------------------------------------------------------
        | Hiển thị lịch sử nhập/xuất/tồn kho.
        |--------------------------------------------------------------------------
        */
        Route::get('inventory/movement-history', [ProductController::class, 'movementHistory'])
            ->name('inventory.movement-history');


        /*
        |--------------------------------------------------------------------------
        | BÁN HÀNG - ĐƠN HÀNG
        |--------------------------------------------------------------------------
        */

        /*
        |--------------------------------------------------------------------------
        | GET /admin/sales/customers/search
        |--------------------------------------------------------------------------
        | Tìm khách hàng khi tạo đơn hàng.
        | Có thể tìm theo tên hoặc số điện thoại.
        |--------------------------------------------------------------------------
        */
        Route::get('sales/customers/search', [OrderController::class, 'searchCustomers'])
            ->name('orders.customers.search');

        /*
        |--------------------------------------------------------------------------
        | GET /admin/sales/orders
        |--------------------------------------------------------------------------
        | Hiển thị danh sách đơn hàng.
        |--------------------------------------------------------------------------
        */
        Route::get('sales/orders', [OrderController::class, 'index'])
            ->name('orders.index');

        /*
        |--------------------------------------------------------------------------
        | GET /admin/sales/orders/create
        |--------------------------------------------------------------------------
        | Hiển thị form tạo đơn hàng mới.
        |--------------------------------------------------------------------------
        */
        Route::get('sales/orders/create', [OrderController::class, 'create'])
            ->name('orders.create');

        /*
        |--------------------------------------------------------------------------
        | POST /admin/sales/orders
        |--------------------------------------------------------------------------
        | Lưu đơn hàng mới vào database.
        | Sau khi tạo đơn có thể trừ kho và tạo hóa đơn.
        |--------------------------------------------------------------------------
        */
        Route::post('sales/orders', [OrderController::class, 'store'])
            ->name('orders.store');

        /*
        |--------------------------------------------------------------------------
        | GET /admin/sales/orders/{order:order_code}
        |--------------------------------------------------------------------------
        | Xem chi tiết đơn hàng theo mã đơn hàng.
        | {order:order_code} nghĩa là tìm order bằng cột order_code.
        |--------------------------------------------------------------------------
        */
        Route::get('sales/orders/{order:order_code}', [OrderController::class, 'show'])
            ->name('orders.show');

        /*
        |--------------------------------------------------------------------------
        | GET /admin/sales/orders/{order:order_code}/edit
        |--------------------------------------------------------------------------
        | Hiển thị form sửa đơn hàng theo mã đơn hàng.
        |--------------------------------------------------------------------------
        */
        Route::get('sales/orders/{order:order_code}/edit', [OrderController::class, 'edit'])
            ->name('orders.edit');

        /*
        |--------------------------------------------------------------------------
        | PUT /admin/sales/orders/{order:order_code}
        |--------------------------------------------------------------------------
        | Cập nhật đơn hàng theo mã đơn hàng.
        |--------------------------------------------------------------------------
        */
        Route::put('sales/orders/{order:order_code}', [OrderController::class, 'update'])
            ->name('orders.update');

        /*
        |--------------------------------------------------------------------------
        | PATCH /admin/sales/orders/{order:order_code}/complete
        |--------------------------------------------------------------------------
        | Hoàn tất đơn hàng.
        | Có thể dùng để tính hoa hồng CTV nếu đơn đủ điều kiện.
        |--------------------------------------------------------------------------
        */
        Route::patch('sales/orders/{order:order_code}/complete', [OrderController::class, 'complete'])
            ->name('orders.complete');

        /*
        |--------------------------------------------------------------------------
        | PATCH /admin/sales/orders/{order:order_code}/cancel
        |--------------------------------------------------------------------------
        | Hủy đơn hàng.
        | Có thể hoàn kho và trừ lại hoa hồng nếu đã tạo.
        |--------------------------------------------------------------------------
        */
        Route::patch('sales/orders/{order:order_code}/cancel', [OrderController::class, 'cancel'])
            ->name('orders.cancel');

        /*
        |--------------------------------------------------------------------------
        | DELETE /admin/sales/orders/{order:order_code}
        |--------------------------------------------------------------------------
        | Xóa mềm đơn hàng theo mã đơn hàng.
        |--------------------------------------------------------------------------
        */
        Route::delete('sales/orders/{order:order_code}', [OrderController::class, 'destroy'])
            ->name('orders.destroy');


        /*
        |--------------------------------------------------------------------------
        | HÓA ĐƠN
        |--------------------------------------------------------------------------
        */

        /*
        |--------------------------------------------------------------------------
        | GET /admin/invoices/{invoice:invoice_code}/print
        |--------------------------------------------------------------------------
        | In hóa đơn theo mã hóa đơn.
        | {invoice:invoice_code} nghĩa là tìm hóa đơn bằng cột invoice_code.
        |
        | Tên route đầy đủ:
        | admin.invoices.print
        |
        | Cách gọi trong blade/controller:
        | route('admin.invoices.print', $invoice->invoice_code)
        |--------------------------------------------------------------------------
        */
        Route::get('invoices/{invoice:invoice_code}/print', [InvoiceController::class, 'print'])
            ->name('invoices.print');


        /*
        |--------------------------------------------------------------------------
        | ĐĂNG XUẤT ADMIN
        |--------------------------------------------------------------------------
        */

        /*
        |--------------------------------------------------------------------------
        | POST /admin/logout
        |--------------------------------------------------------------------------
        | Đăng xuất tài khoản admin hiện tại.
        |--------------------------------------------------------------------------
        */
        Route::post('logout', [AdminLoginController::class, 'logout'])
            ->name('logout');
    });
});
