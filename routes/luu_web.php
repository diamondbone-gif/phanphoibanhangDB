<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CustomerOptionController;
use App\Http\Controllers\Admin\CustomerRoleStatusController;
use App\Http\Controllers\Admin\CtvController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\CustomerCommissionController;
use App\Http\Controllers\Admin\CommissionController;

/*
|--------------------------------------------------------------------------
| KHU VỰC CTV / NGƯỜI DÙNG THƯỜNG
|--------------------------------------------------------------------------
*/

/*
| Trang chủ ngoài hệ thống.
| Khi truy cập domain chính "/", hệ thống sẽ hiển thị trang welcome.
*/

Route::get('/', function () {
    return view('welcome');
})->name('ctv.home');


/*
|--------------------------------------------------------------------------
| ROUTE LOGIN MẶC ĐỊNH CỦA LARAVEL
|--------------------------------------------------------------------------
*/

/*
| Route login mặc định của Laravel.
| Nếu hệ thống hoặc middleware tự gọi route "login", sẽ chuyển về trang đăng nhập admin.
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

    /*
    | Đường dẫn gốc của admin: /admin.
    | Nếu admin đã đăng nhập thì chuyển vào dashboard.
    | Nếu chưa đăng nhập thì chuyển về trang login admin.
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

    /*
    | Hiển thị form đăng nhập admin.
    | Đường dẫn: /admin/login
    | Tên route: admin.login
    */
    Route::get('login', [AdminLoginController::class, 'showLoginForm'])
        ->name('login');

    /*
    | Xử lý đăng nhập admin.
    | Có giới hạn 5 lần đăng nhập trong 1 phút để tăng bảo mật.
    | Đường dẫn: POST /admin/login
    | Tên route: admin.login.submit
    */
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

        /*
        | Trang dashboard quản trị.
        | Đây là route bắt buộc để dùng được route('admin.dashboard').
        | Đường dẫn: /admin/dashboard
        | Tên route: admin.dashboard
        */
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | QUẢN LÝ KHÁCH HÀNG
        |--------------------------------------------------------------------------
        */

        /*
        | Danh sách khách hàng.
        | Hiển thị toàn bộ khách hàng, tìm kiếm, lọc nếu controller có xử lý.
        | Đường dẫn: /admin/customers
        | Tên route: admin.customers.index
        */
        Route::get('customers', [CustomerController::class, 'index'])
            ->name('customers.index');

        /*
        | Form thêm khách hàng mới.
        | Đường dẫn: /admin/customers/create
        | Tên route: admin.customers.create
        */
        Route::get('customers/create', [CustomerController::class, 'create'])
            ->name('customers.create');

        /*
        | Lưu khách hàng mới vào database.
        | Đường dẫn: POST /admin/customers
        | Tên route: admin.customers.store
        */
        Route::post('customers', [CustomerController::class, 'store'])
            ->name('customers.store');

        /*
        | Kiểm tra số điện thoại người giới thiệu / CTV.
        | Dùng cho AJAX ở form thêm/sửa khách hàng.
        | Đường dẫn: /admin/customers/check-referrer
        | Tên route: admin.customers.check-referrer
        */
        Route::get('customers/check-referrer', [CustomerController::class, 'checkReferrer'])
            ->name('customers.check-referrer');

        /*
        | Xem chi tiết một khách hàng.
        | Chỉ nhận customer là số ID.
        | Đường dẫn: /admin/customers/{customer}
        | Tên route: admin.customers.show
        */
        Route::get('customers/{customer}', [CustomerController::class, 'show'])
            ->name('customers.show')
            ->whereNumber('customer');

        /*
        | Form sửa thông tin khách hàng.
        | Chỉ nhận customer là số ID.
        | Đường dẫn: /admin/customers/{customer}/edit
        | Tên route: admin.customers.edit
        */
        Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])
            ->name('customers.edit')
            ->whereNumber('customer');

        /*
        | Cập nhật thông tin khách hàng.
        | Chỉ nhận customer là số ID.
        | Đường dẫn: PUT /admin/customers/{customer}
        | Tên route: admin.customers.update
        */
        Route::put('customers/{customer}', [CustomerController::class, 'update'])
            ->name('customers.update')
            ->whereNumber('customer');

        /*
        | Xóa khách hàng.
        | Chỉ nhận customer là số ID.
        | Đường dẫn: DELETE /admin/customers/{customer}
        | Tên route: admin.customers.destroy
        */
        Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])
            ->name('customers.destroy')
            ->whereNumber('customer');

        /*
        | Chuyển khách hàng thường thành CTV.
        | Chỉ nhận customer là số ID.
        | Đường dẫn: POST /admin/customers/{customer}/convert-to-ctv
        | Tên route: admin.customers.convert-to-ctv
        */
        Route::post('customers/{customer}/convert-to-ctv', [CustomerController::class, 'convertToCtv'])
            ->name('customers.convert-to-ctv')
            ->whereNumber('customer');

        /*
        | Đánh dấu khách hàng đã ngưng mua hàng.
        | Chỉ nhận customer là số ID.
        | Đường dẫn: POST /admin/customers/{customer}/mark-stopped-buying
        | Tên route: admin.customers.mark-stopped-buying
        */
        Route::post('customers/{customer}/mark-stopped-buying', [CustomerController::class, 'markStoppedBuying'])
            ->name('customers.mark-stopped-buying')
            ->whereNumber('customer');


        /*
        |--------------------------------------------------------------------------
        | DANH MỤC TÙY CHỌN KHÁCH HÀNG
        |--------------------------------------------------------------------------
        */

        /*
        | Trang quản lý danh mục tùy chọn trong form khách hàng.
        | Ví dụ: thông tin nhận diện, mua cho ai, sản phẩm quan tâm, nhu cầu, ghi chú.
        | Đường dẫn: /admin/customer-options
        | Tên route: admin.customer-options.index
        */
        Route::get('customer-options', [CustomerOptionController::class, 'index'])
            ->name('customer-options.index');

        /*
        | Thêm mới một tùy chọn khách hàng theo từng loại.
        | Có middleware signed vì form dùng URL::signedRoute.
        | Đường dẫn: POST /admin/customer-options/{type}
        | Tên route: admin.customer-options.store
        */
        Route::post('customer-options/{type}', [CustomerOptionController::class, 'store'])
            ->name('customer-options.store')
            ->where('type', 'identity|buy_for|product|need|note')
            ->middleware('signed');

        /*
        | Cập nhật một tùy chọn khách hàng theo từng loại.
        | Có middleware signed vì form dùng URL::signedRoute.
        | Đường dẫn: PUT /admin/customer-options/{type}/{id}
        | Tên route: admin.customer-options.update
        */
        Route::put('customer-options/{type}/{id}', [CustomerOptionController::class, 'update'])
            ->name('customer-options.update')
            ->where('type', 'identity|buy_for|product|need|note')
            ->whereNumber('id')
            ->middleware('signed');

        /*
        | Xóa một tùy chọn khách hàng theo từng loại.
        | Có middleware signed vì form dùng URL::signedRoute.
        | Đường dẫn: DELETE /admin/customer-options/{type}/{id}
        | Tên route: admin.customer-options.destroy
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
        | Trang quản lý vai trò và trạng thái khách hàng / CTV.
        | Ví dụ: vai trò, trạng thái mua, trạng thái khách hàng, trạng thái CTV.
        | Đường dẫn: /admin/role-status-options
        | Tên route: admin.role-status-options.index
        */
        Route::get('role-status-options', [CustomerRoleStatusController::class, 'index'])
            ->name('role-status-options.index');

        /*
        | Thêm mới vai trò hoặc trạng thái.
        | Có middleware signed vì form dùng URL::signedRoute.
        | Đường dẫn: POST /admin/role-status-options/{type}
        | Tên route: admin.role-status-options.store
        */
        Route::post('role-status-options/{type}', [CustomerRoleStatusController::class, 'store'])
            ->name('role-status-options.store')
            ->where('type', 'role|buy_status|customer_status|ctv_status')
            ->middleware('signed');

        /*
        | Cập nhật vai trò hoặc trạng thái.
        | Có middleware signed vì form dùng URL::signedRoute.
        | Đường dẫn: PUT /admin/role-status-options/{type}/{id}
        | Tên route: admin.role-status-options.update
        */
        Route::put('role-status-options/{type}/{id}', [CustomerRoleStatusController::class, 'update'])
            ->name('role-status-options.update')
            ->where('type', 'role|buy_status|customer_status|ctv_status')
            ->whereNumber('id')
            ->middleware('signed');

        /*
        | Xóa vai trò hoặc trạng thái.
        | Có middleware signed vì form dùng URL::signedRoute.
        | Đường dẫn: DELETE /admin/role-status-options/{type}/{id}
        | Tên route: admin.role-status-options.destroy
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
        | Danh sách CTV.
        | Hiển thị CTV, tìm kiếm, lọc trạng thái, thống kê khách giới thiệu.
        | Đường dẫn: /admin/ctvs
        | Tên route: admin.ctvs.index
        */
        Route::get('ctvs', [CtvController::class, 'index'])
            ->name('ctvs.index');

        /*
        | Xem chi tiết một CTV.
        | Dùng signed route để hạn chế truy cập sai đường dẫn.
        | Đường dẫn: /admin/ctvs/{customer}/show
        | Tên route: admin.ctvs.show
        */
        Route::get('ctvs/{customer}/show', [CtvController::class, 'show'])
            ->name('ctvs.show')
            ->whereNumber('customer')
            ->middleware('signed');

        /*
        | Xem chi tiết khách hàng được một CTV giới thiệu.
        | Dùng signed route để bảo vệ đường dẫn chi tiết.
        | Đường dẫn: /admin/ctvs/{ctv}/referred-customers/{referred}/show
        | Tên route: admin.ctvs.referred-customers.show
        */
        Route::get('ctvs/{ctv}/referred-customers/{referred}/show', [CtvController::class, 'referredShow'])
            ->name('ctvs.referred-customers.show')
            ->whereNumber('ctv')
            ->whereNumber('referred')
            ->middleware('signed');


        /*
        |--------------------------------------------------------------------------
        | HOA HỒNG CỘNG TÁC VIÊN - GIAO DIỆN MỚI
        |--------------------------------------------------------------------------
        */

        /*
        | Danh sách tổng hợp hoa hồng CTV.
        | Hiển thị tổng hoa hồng, đã chi, còn nợ theo từng CTV.
        | Đường dẫn: /admin/commissions
        | Tên route: admin.commissions.index
        */
        Route::get('commissions', [CommissionController::class, 'index'])
            ->name('commissions.index');

        /*
        | Xem chi tiết hoa hồng của một CTV.
        | Chỉ nhận ctv là số ID.
        | Đường dẫn: /admin/commissions/{ctv}/detail
        | Tên route: admin.commissions.detail
        */
        Route::get('commissions/{ctv}/detail', [CommissionController::class, 'detail'])
            ->whereNumber('ctv')
            ->name('commissions.detail');

        /*
        | Thanh toán hoa hồng cho một CTV.
        | Chỉ nhận ctv là số ID.
        | Đường dẫn: POST /admin/commissions/{ctv}/pay
        | Tên route: admin.commissions.pay
        */
        Route::post('commissions/{ctv}/pay', [CommissionController::class, 'pay'])
            ->whereNumber('ctv')
            ->name('commissions.pay');

        /*
        | Xem lịch sử thanh toán hoa hồng của một CTV.
        | Chỉ nhận ctv là số ID.
        | Đường dẫn: /admin/commissions/{ctv}/history
        | Tên route: admin.commissions.history
        */
        Route::get('commissions/{ctv}/history', [CommissionController::class, 'history'])
            ->whereNumber('ctv')
            ->name('commissions.history');

        /*
        | Form sửa lịch sử thanh toán hoa hồng.
        | Chỉ nhận ctv và payout là số ID.
        | Đường dẫn: /admin/commissions/{ctv}/history/{payout}/edit
        | Tên route: admin.commissions.history.edit
        */
        Route::get('commissions/{ctv}/history/{payout}/edit', [CommissionController::class, 'editHistory'])
            ->whereNumber('ctv')
            ->whereNumber('payout')
            ->name('commissions.history.edit');

        /*
        | Cập nhật lịch sử thanh toán hoa hồng.
        | Chỉ nhận ctv và payout là số ID.
        | Đường dẫn: PUT /admin/commissions/{ctv}/history/{payout}
        | Tên route: admin.commissions.history.update
        */
        Route::put('commissions/{ctv}/history/{payout}', [CommissionController::class, 'updateHistory'])
            ->whereNumber('ctv')
            ->whereNumber('payout')
            ->name('commissions.history.update');


        /*
        |--------------------------------------------------------------------------
        | HOA HỒNG CỘNG TÁC VIÊN - ROUTE CŨ
        |--------------------------------------------------------------------------
        */

        /*
        | Danh sách hoa hồng theo logic cũ.
        | Giữ lại để không lỗi các màn hình hoặc chức năng cũ đang dùng.
        | Đường dẫn: /admin/customer-commissions
        | Tên route: admin.customer-commissions.index
        */
        Route::get('customer-commissions', [CustomerCommissionController::class, 'index'])
            ->name('customer-commissions.index');

        /*
        | Đánh dấu một khoản hoa hồng là đã thanh toán.
        | Chỉ nhận commission là số ID.
        | Đường dẫn: POST /admin/customer-commissions/{commission}/mark-paid
        | Tên route: admin.customer-commissions.mark-paid
        */
        Route::post('customer-commissions/{commission}/mark-paid', [CustomerCommissionController::class, 'markPaid'])
            ->name('customer-commissions.mark-paid')
            ->whereNumber('commission');

        /*
        | Đánh dấu một khoản hoa hồng là chưa thanh toán.
        | Chỉ nhận commission là số ID.
        | Đường dẫn: POST /admin/customer-commissions/{commission}/mark-unpaid
        | Tên route: admin.customer-commissions.mark-unpaid
        */
        Route::post('customer-commissions/{commission}/mark-unpaid', [CustomerCommissionController::class, 'markUnpaid'])
            ->name('customer-commissions.mark-unpaid')
            ->whereNumber('commission');


        /*
        |--------------------------------------------------------------------------
        | KHO SẢN PHẨM - DANH SÁCH SẢN PHẨM
        |--------------------------------------------------------------------------
        */

        /*
        | Danh sách sản phẩm.
        | Hiển thị sản phẩm, tìm kiếm, lọc, thêm/sửa/xóa qua controller.
        | Đường dẫn: /admin/products
        | Tên route: admin.products.index
        */
        Route::get('products', [ProductController::class, 'index'])
            ->name('products.index');

        /*
        | Lấy bảng sản phẩm bằng AJAX.
        | Dùng khi giao diện cần reload riêng phần table.
        | Đường dẫn: /admin/products/table
        | Tên route: admin.products.table
        */
        Route::get('products/table', [ProductController::class, 'table'])
            ->name('products.table');

        /*
        | Lưu sản phẩm mới.
        | Đường dẫn: POST /admin/products
        | Tên route: admin.products.store
        */
        Route::post('products', [ProductController::class, 'store'])
            ->name('products.store');

        /*
        | Lấy dữ liệu sản phẩm để sửa.
        | Chỉ nhận product là số ID.
        | Đường dẫn: /admin/products/{product}/edit
        | Tên route: admin.products.edit
        */
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])
            ->name('products.edit')
            ->whereNumber('product');

        /*
        | Cập nhật sản phẩm.
        | Chỉ nhận product là số ID.
        | Đường dẫn: PUT /admin/products/{product}
        | Tên route: admin.products.update
        */
        Route::put('products/{product}', [ProductController::class, 'update'])
            ->name('products.update')
            ->whereNumber('product');

        /*
        | Xóa sản phẩm.
        | Chỉ nhận product là số ID.
        | Đường dẫn: DELETE /admin/products/{product}
        | Tên route: admin.products.destroy
        */
        Route::delete('products/{product}', [ProductController::class, 'destroy'])
            ->name('products.destroy')
            ->whereNumber('product');

        /*
        | Bật / tắt trạng thái sản phẩm.
        | Chỉ nhận product là số ID.
        | Đường dẫn: PATCH /admin/products/{product}/toggle-status
        | Tên route: admin.products.toggle-status
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
        | Trang quản lý tồn kho.
        | Hiển thị sản phẩm, lô hàng, số lượng tồn, cận date, hết hàng.
        | Đường dẫn: /admin/inventory
        | Tên route: admin.inventory.index
        */
        Route::get('inventory', [ProductController::class, 'inventory'])
            ->name('inventory.index');

        /*
        | Lấy bảng tồn kho bằng AJAX.
        | Dùng khi giao diện cần reload riêng phần table tồn kho.
        | Đường dẫn: /admin/inventory/table
        | Tên route: admin.inventory.table
        */
        Route::get('inventory/table', [ProductController::class, 'inventoryTable'])
            ->name('inventory.table');

        /*
        | Nhập kho / tạo lô hàng mới.
        | Đường dẫn: POST /admin/inventory/import-stock
        | Tên route: admin.inventory.import-stock
        */
        Route::post('inventory/import-stock', [ProductController::class, 'importStock'])
            ->name('inventory.import-stock');

        /*
        | Lấy dữ liệu lô hàng để sửa.
        | Chỉ nhận batch là số ID.
        | Đường dẫn: /admin/inventory/batches/{batch}/edit
        | Tên route: admin.inventory.batches.edit
        */
        Route::get('inventory/batches/{batch}/edit', [ProductController::class, 'editBatch'])
            ->name('inventory.batches.edit')
            ->whereNumber('batch');

        /*
        | Cập nhật thông tin lô hàng.
        | Chỉ nhận batch là số ID.
        | Đường dẫn: PUT /admin/inventory/batches/{batch}
        | Tên route: admin.inventory.batches.update
        */
        Route::put('inventory/batches/{batch}', [ProductController::class, 'updateBatch'])
            ->name('inventory.batches.update')
            ->whereNumber('batch');

        /*
        | Bật / tắt trạng thái lô hàng.
        | Chỉ nhận batch là số ID.
        | Đường dẫn: PATCH /admin/inventory/batches/{batch}/toggle-status
        | Tên route: admin.inventory.batches.toggle-status
        */
        Route::patch('inventory/batches/{batch}/toggle-status', [ProductController::class, 'toggleBatchStatus'])
            ->name('inventory.batches.toggle-status')
            ->whereNumber('batch');

        /*
        | Xóa lô hàng.
        | Chỉ nhận batch là số ID.
        | Đường dẫn: DELETE /admin/inventory/batches/{batch}
        | Tên route: admin.inventory.batches.destroy
        */
        Route::delete('inventory/batches/{batch}', [ProductController::class, 'destroyBatch'])
            ->name('inventory.batches.destroy')
            ->whereNumber('batch');

        /*
        | Lịch sử nhập / xuất / thay đổi tồn kho.
        | Đường dẫn: /admin/inventory/movement-history
        | Tên route: admin.inventory.movement-history
        */
        Route::get('inventory/movement-history', [ProductController::class, 'movementHistory'])
            ->name('inventory.movement-history');


        /*
        |--------------------------------------------------------------------------
        | BÁN HÀNG - ĐƠN HÀNG
        |--------------------------------------------------------------------------
        */

        /*
        | Tìm kiếm khách hàng khi tạo đơn hàng.
        | Dùng cho AJAX search theo tên hoặc số điện thoại.
        | Đường dẫn: /admin/sales/customers/search
        | Tên route: admin.orders.customers.search
        */
        Route::get('sales/customers/search', [OrderController::class, 'searchCustomers'])
            ->name('orders.customers.search');

        /*
        | Danh sách đơn hàng.
        | Hiển thị đơn hàng, tìm kiếm mã đơn, khách hàng, số điện thoại.
        | Đường dẫn: /admin/sales/orders
        | Tên route: admin.orders.index
        */
        Route::get('sales/orders', [OrderController::class, 'index'])
            ->name('orders.index');

        /*
        | Form tạo đơn hàng mới.
        | Đường dẫn: /admin/sales/orders/create
        | Tên route: admin.orders.create
        */
        Route::get('sales/orders/create', [OrderController::class, 'create'])
            ->name('orders.create');

        /*
        | Lưu đơn hàng mới vào database.
        | Sau khi lưu có thể trừ kho, tính tiền, tạo công nợ, tạo hoa hồng tùy logic controller/service.
        | Đường dẫn: POST /admin/sales/orders
        | Tên route: admin.orders.store
        */
        Route::post('sales/orders', [OrderController::class, 'store'])
            ->name('orders.store');

        /*
        | Xem chi tiết đơn hàng theo mã đơn.
        | Dùng route model binding theo order_code.
        | Đường dẫn: /admin/sales/orders/{order_code}
        | Tên route: admin.orders.show
        */
        Route::get('sales/orders/{order:order_code}', [OrderController::class, 'show'])
            ->name('orders.show');

        /*
        | Form sửa đơn hàng theo mã đơn.
        | Dùng route model binding theo order_code.
        | Đường dẫn: /admin/sales/orders/{order_code}/edit
        | Tên route: admin.orders.edit
        */
        Route::get('sales/orders/{order:order_code}/edit', [OrderController::class, 'edit'])
            ->name('orders.edit');

        /*
        | Cập nhật đơn hàng theo mã đơn.
        | Dùng route model binding theo order_code.
        | Đường dẫn: PUT /admin/sales/orders/{order_code}
        | Tên route: admin.orders.update
        */
        Route::put('sales/orders/{order:order_code}', [OrderController::class, 'update'])
            ->name('orders.update');

        /*
        | Hoàn tất đơn hàng.
        | Có thể dùng để cập nhật trạng thái completed, ghi nhận ngày hoàn tất, tạo hoa hồng nếu đủ điều kiện.
        | Đường dẫn: PATCH /admin/sales/orders/{order_code}/complete
        | Tên route: admin.orders.complete
        */
        Route::patch('sales/orders/{order:order_code}/complete', [OrderController::class, 'complete'])
            ->name('orders.complete');

        /*
        | Hủy đơn hàng.
        | Có thể dùng để hoàn kho, hủy công nợ, hủy hoa hồng tùy logic controller/service.
        | Đường dẫn: PATCH /admin/sales/orders/{order_code}/cancel
        | Tên route: admin.orders.cancel
        */
        Route::patch('sales/orders/{order:order_code}/cancel', [OrderController::class, 'cancel'])
            ->name('orders.cancel');

        /*
        | Xóa đơn hàng.
        | Dùng khi muốn xóa hoặc soft delete đơn hàng tùy model/controller.
        | Đường dẫn: DELETE /admin/sales/orders/{order_code}
        | Tên route: admin.orders.destroy
        */
        Route::delete('sales/orders/{order:order_code}', [OrderController::class, 'destroy'])
            ->name('orders.destroy');


        /*
        |--------------------------------------------------------------------------
        | HÓA ĐƠN
        |--------------------------------------------------------------------------
        */

        /*
        | In hóa đơn theo mã hóa đơn.
        | Dùng route model binding theo invoice_code.
        | Đường dẫn: /admin/invoices/{invoice_code}/print
        | Tên route: admin.invoices.print
        */
        Route::get('invoices/{invoice:invoice_code}/print', [InvoiceController::class, 'print'])
            ->name('invoices.print');


        /*
        |--------------------------------------------------------------------------
        | ĐĂNG XUẤT ADMIN
        |--------------------------------------------------------------------------
        */

        /*
        | Đăng xuất admin.
        | Xóa session guard admin và chuyển về màn hình đăng nhập.
        | Đường dẫn: POST /admin/logout
        | Tên route: admin.logout
        */
        Route::post('logout', [AdminLoginController::class, 'logout'])
            ->name('logout');
    });
});