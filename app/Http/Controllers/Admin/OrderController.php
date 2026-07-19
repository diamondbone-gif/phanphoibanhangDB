<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrderRequest;
use App\Http\Requests\Admin\StoreOrderReturnRequest;
use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\Product;
use App\Services\OrderService;
use App\Services\ReturnOrderService;
use Illuminate\Http\Request;
use Throwable;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private ReturnOrderService $returnOrderService,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | DANH SÁCH ĐƠN HÀNG
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $keyword = trim((string) $request->get('keyword'));

        $orders = CustomerOrder::query()
            ->with([
                'customer',
                'invoice',
                'commission.ctvCustomer',
                'commission.referredCustomer',
            ])
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where('order_code', 'like', "%{$keyword}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($keyword) {
                        $customerQuery->where('full_name', 'like', "%{$keyword}%")
                            ->orWhere('phone', 'like', "%{$keyword}%");

                        $customerQuery->orWhere('customer_code', 'like', "%{$keyword}%");
                    });
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.auth.orders.index', compact('orders', 'keyword'));
    }

    /*
    |--------------------------------------------------------------------------
    | FORM LÊN ĐƠN HÀNG
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $products = $this->getProductsForOrderForm();

        return view('admin.auth.orders.create', compact('products'));
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX TÌM KHÁCH HÀNG
    |--------------------------------------------------------------------------
    */
    public function searchCustomers(Request $request)
    {
        $keyword = trim((string) $request->get('q'));

        if ($keyword === '' || mb_strlen($keyword) < 2) {
            return response()->json([]);
        }

        $customers = Customer::query()
            ->select($this->customerSelectColumns())
            ->where(function ($query) use ($keyword) {
                $query->where('full_name', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%");

                $query->orWhere('customer_code', 'like', "%{$keyword}%");
            })
            ->orderBy('full_name')
            ->limit(10)
            ->get()
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'customer_code' => $customer->customer_code ?? '',
                    'full_name' => $customer->full_name ?? '',
                    'phone' => $customer->phone ?? '',
                    'label' => trim(($customer->full_name ?? '') . ' - ' . ($customer->phone ?? '')),
                ];
            });

        return response()->json($customers);
    }

    /*
    |--------------------------------------------------------------------------
    | LƯU ĐƠN HÀNG
    |--------------------------------------------------------------------------
    */
    public function store(StoreOrderRequest $request)
    {
        try {
            $order = $this->orderService->create(
                $request->validated(),
                auth('admin')->id()
            );

            $order->load('invoice');

            if (!$order->invoice) {
                return redirect()
                    ->route('admin.orders.show', $order)
                    ->with('error', 'Đơn hàng đã tạo nhưng chưa tìm thấy hóa đơn.');
            }

            return redirect()
                ->route('admin.invoices.print', $order->invoice)
                ->with('success', 'Tạo đơn hàng và hóa đơn thành công.');
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Không thể lưu đơn hàng: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CHI TIẾT ĐƠN HÀNG
    |--------------------------------------------------------------------------
    */
    public function show(CustomerOrder $order)
    {
        $order->load([
            'customer.detail',
            'items.product.mainImage',
            'items.product.images',
            'invoice',
            'payments',
            'histories',
            'returns.items.orderItem',
            'returns.creator',
            'commission.ctvCustomer',
            'commission.referredCustomer',
        ]);

        return view('admin.auth.orders.show', compact('order'));
    }

    public function storeReturn(StoreOrderReturnRequest $request, CustomerOrder $order)
    {
        try {
            $return = $this->returnOrderService->create(
                $order,
                $request->validated(),
                auth('admin')->id()
            );

            return back()->with(
                'success',
                "Đã tạo phiếu hoàn {$return->return_code}, nhập lại kho và tính lại hoa hồng."
            );
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'Không thể hoàn trả đơn hàng: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FORM SỬA ĐƠN HÀNG
    |--------------------------------------------------------------------------
    */
    public function edit(CustomerOrder $order)
    {
        $order->load([
            'customer',
            'customer.detail',
            'items.product',
            'invoice',
            'payments',
            'commission.ctvCustomer',
        ]);

        $customers = Customer::query()
            ->select($this->customerSelectColumns())
            ->orderBy('full_name')
            ->get();

        $products = $this->getProductsForOrderForm();

        return view('admin.auth.orders.edit', compact(
            'order',
            'customers',
            'products'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | CẬP NHẬT ĐƠN HÀNG
    |--------------------------------------------------------------------------
    */
    public function update(StoreOrderRequest $request, CustomerOrder $order)
    {
        try {
            $updatedOrder = $this->orderService->update(
                $order,
                $request->validated(),
                auth('admin')->id()
            );

            return redirect()
                ->route('admin.orders.show', $updatedOrder)
                ->with('success', 'Cập nhật đơn hàng thành công.');
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Không thể cập nhật đơn hàng: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HOÀN THÀNH ĐƠN HÀNG
    |--------------------------------------------------------------------------
    */
    public function complete(CustomerOrder $order)
    {
        try {
            $this->orderService->complete(
                $order,
                auth('admin')->id()
            );

            return back()->with(
                'success',
                'Đơn hàng đã hoàn thành. Hệ thống đã tạo hoa hồng nếu khách hàng có CTV giới thiệu.'
            );
        } catch (Throwable $e) {
            return back()->with('error', 'Không thể hoàn thành đơn hàng: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HỦY ĐƠN HÀNG
    |--------------------------------------------------------------------------
    */
    public function cancel(Request $request, CustomerOrder $order)
    {
        $request->validate([
            'cancel_reason' => ['required', 'string', 'max:1000'],
        ], [
            'cancel_reason.required' => 'Vui lòng nhập lý do hủy đơn.',
        ]);

        try {
            $this->orderService->cancel(
                $order,
                $request->cancel_reason,
                auth('admin')->id()
            );

            return back()->with(
                'success',
                'Đơn hàng đã được hủy. Kho và hoa hồng đã được xử lý tự động.'
            );
        } catch (Throwable $e) {
            return back()->with('error', 'Không thể hủy đơn hàng: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | XÓA MỀM ĐƠN HÀNG
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request, CustomerOrder $order)
    {
        $request->validate([
            'delete_reason' => ['required', 'string', 'max:1000'],
        ], [
            'delete_reason.required' => 'Vui lòng nhập lý do xóa đơn hàng.',
        ]);

        try {
            $this->orderService->delete(
                $order,
                $request->delete_reason,
                auth('admin')->id()
            );

            return redirect()
                ->route('admin.orders.index')
                ->with('success', 'Đã xóa mềm đơn hàng và lưu lại lịch sử.');
        } catch (Throwable $e) {
            return back()->with('error', 'Không thể xóa đơn hàng: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | LẤY SẢN PHẨM CHO FORM LÊN ĐƠN
    |--------------------------------------------------------------------------
    */
    private function getProductsForOrderForm()
    {
        $query = Product::query();

        $query->where('is_active', true);

        return $query
            ->select($this->productSelectColumns())
            ->orderBy('product_name')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | CHỈ SELECT CỘT SẢN PHẨM CÓ TỒN TẠI TRONG DATABASE
    |--------------------------------------------------------------------------
    */
    private function productSelectColumns(): array
    {
        $columns = [
            'id',
            'product_code',
            'product_name',
            'price',
        ];

        $optionalColumns = [
            'total_quantity',
            'product_type',
            'track_batch',
            'allow_sell_without_stock',
            'is_discountable',
            'is_commissionable',
            'default_commission_rate',
        ];

        $columns = array_merge($columns, $optionalColumns);

        return $columns;
    }

    /*
    |--------------------------------------------------------------------------
    | CHỈ SELECT CỘT KHÁCH HÀNG CÓ TỒN TẠI
    |--------------------------------------------------------------------------
    */
    private function customerSelectColumns(): array
    {
        $columns = [
            'id',
            'full_name',
            'phone',
        ];

        $columns[] = 'customer_code';

        return $columns;
    }
}
