@extends('admin.auth.dashboardAmin')

@section('admin_content')

@php
$customer = $order->customer;

$customerName = data_get($customer, 'full_name') ?: '---';
$customerPhone = data_get($customer, 'phone') ?: '---';

$customerAddress = $customer?->display_address ?: '---';

/*
|--------------------------------------------------------------------------
| LẤY HÌNH SẢN PHẨM
|--------------------------------------------------------------------------
*/
$getProductImage = function ($item) {
$product = $item->product ?? null;

$image = data_get($product, 'main_image')
?: data_get($product, 'image')
?: data_get($product, 'thumbnail')
?: data_get($product, 'images.0.image_path')
?: data_get($product, 'images.0.path')
?: data_get($product, 'images.0.url')
?: data_get($product, 'images.0.image')
?: data_get($item, 'product_image')
?: null;

if (!$image) {
return null;
}

if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
return $image;
}

return asset('storage/' . ltrim($image, '/'));
};
@endphp

<style>
    .order-product-img {
        width: 58px;
        height: 58px;
        border-radius: 12px;
        object-fit: cover;
        border: 1px solid #e5e7eb;
        background: #f8fafc;
    }

    .order-product-placeholder {
        width: 58px;
        height: 58px;
        border-radius: 12px;
        border: 1px dashed #cbd5e1;
        background: #f8fafc;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 20px;
    }

    .customer-info-row {
        display: grid;
        grid-template-columns: 180px 1fr;
        gap: 12px;
        padding: 14px 0;
        border-bottom: 1px solid #edf2f7;
        align-items: start;
    }

    .customer-info-row:last-child {
        border-bottom: 0;
    }

    .customer-info-label {
        color: #64748b;
        font-weight: 700;
    }

    .customer-info-value {
        color: #0f172a;
        font-weight: 700;
        word-break: break-word;
    }

    @media (max-width: 768px) {
        .customer-info-row {
            grid-template-columns: 1fr;
            gap: 4px;
        }
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h3 class="mb-1">Đơn hàng {{ $order->order_code }}</h3>
        <div class="text-muted">
            Khách hàng: {{ $customerName }}
        </div>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-light border">
            <i class="fa-solid fa-arrow-left"></i> Quay lại
        </a>

        <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-primary">
            <i class="fa-solid fa-pen"></i> Sửa
        </a>

        @if($order->invoice)
        <a href="{{ route('admin.invoices.print', $order->invoice) }}" target="_blank" class="btn btn-success">
            <i class="fa-solid fa-print"></i> In hóa đơn
        </a>
        @endif
    </div>
</div>

@include('admin.auth.partials.alerts')

<div class="row g-3">
    <div class="col-lg-8">
        {{-- SẢN PHẨM TRONG ĐƠN --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0">Sản phẩm trong đơn</h5>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 config-table">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 text-center" style="width: 70px;">STT</th>
                                <th style="width: 90px;">Hình</th>
                                <th>Sản phẩm</th>
                                <th class="text-center" style="width: 90px;">SL</th>
                                <th class="text-end" style="width: 140px;">Đơn giá</th>
                                <th class="text-end" style="width: 110px;">Giảm</th>
                                <th class="text-end pe-3" style="width: 150px;">Thành tiền</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($order->items as $index => $item)
                            @php
                            $imageUrl = $getProductImage($item);
                            @endphp

                            <tr>
                                <td class="ps-3 text-center fw-bold" data-label="STT">
                                    {{ $index + 1 }}
                                </td>

                                <td data-label="Hình">
                                    @if($imageUrl)
                                    <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}"
                                        class="order-product-img">
                                    @else
                                    <span class="order-product-placeholder">
                                        <i class="fa-solid fa-image"></i>
                                    </span>
                                    @endif
                                </td>

                                <td data-label="Sản phẩm">
                                    <div class="fw-bold">{{ $item->product_name }}</div>
                                    <small class="text-muted">{{ $item->product_code }}</small>
                                </td>

                                <td data-label="SL" class="text-center fw-bold">
                                    {{ $item->quantity }}
                                </td>

                                <td data-label="Đơn giá" class="text-end">
                                    {{ number_format($item->unit_price, 0, ',', '.') }}đ
                                </td>

                                <td data-label="Giảm" class="text-end text-danger">
                                    -{{ number_format($item->discount_amount, 0, ',', '.') }}đ
                                </td>

                                <td data-label="Thành tiền" class="text-end pe-3 fw-bold">
                                    {{ number_format($item->final_total, 0, ',', '.') }}đ
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Chưa có sản phẩm trong đơn.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- THÔNG TIN KHÁCH HÀNG --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thông tin khách hàng</h5>
            </div>

            <div class="card-body">
                <div class="customer-info-row">
                    <div class="customer-info-label">Tên khách hàng</div>
                    <div class="customer-info-value">{{ $customerName }}</div>
                </div>

                <div class="customer-info-row">
                    <div class="customer-info-label">Số điện thoại</div>
                    <div class="customer-info-value">{{ $customerPhone }}</div>
                </div>

                <div class="customer-info-row">
                    <div class="customer-info-label">Địa chỉ</div>
                    <div class="customer-info-value">{{ $customerAddress }}</div>
                </div>

                <div class="alert alert-info mb-0 mt-3">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Người bán kiểm tra lại tên, số điện thoại và địa chỉ trước khi in hóa đơn hoặc bàn giao sản phẩm.
                </div>
            </div>
        </div>
    </div>

    {{-- CỘT PHẢI: GIỮ NGUYÊN TỔNG KẾT + THAO TÁC --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0">Tổng kết</h5>
            </div>

            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Tổng gốc</span>
                    <strong>{{ number_format($order->subtotal_amount, 0, ',', '.') }}đ</strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span>Giảm sản phẩm</span>
                    <strong class="text-danger">
                        -{{ number_format($order->product_discount_amount, 0, ',', '.') }}đ
                    </strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span>Giảm đơn</span>
                    <strong class="text-danger">
                        -{{ number_format($order->order_discount_amount, 0, ',', '.') }}đ
                    </strong>
                </div>

                <hr>

                <div class="d-flex justify-content-between fs-5">
                    <span>Tổng cuối</span>
                    <strong class="text-danger">{{ number_format($order->final_amount, 0, ',', '.') }}đ</strong>
                </div>

                <div class="d-flex justify-content-between mt-2">
                    <span>Đã thanh toán</span>
                    <strong>{{ number_format($order->paid_amount, 0, ',', '.') }}đ</strong>
                </div>

                <div class="d-flex justify-content-between">
                    <span>Còn nợ</span>
                    <strong>{{ number_format($order->debt_amount, 0, ',', '.') }}đ</strong>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thao tác đơn hàng</h5>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('admin.orders.complete', $order) }}" class="mb-2">
                    @csrf
                    @method('PATCH')

                    <button class="btn btn-success w-100" onclick="return confirm('Xác nhận hoàn thành đơn hàng?')">
                        <i class="fa-solid fa-check"></i> Hoàn thành đơn
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" class="mb-2">
                    @csrf
                    @method('PATCH')

                    <textarea name="cancel_reason" class="form-control mb-2" rows="2" required
                        placeholder="Lý do hủy đơn..."></textarea>

                    <button class="btn btn-warning w-100" onclick="return confirm('Xác nhận hủy đơn và hoàn kho?')">
                        <i class="fa-solid fa-ban"></i> Hủy đơn
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.orders.destroy', $order) }}">
                    @csrf
                    @method('DELETE')

                    <textarea name="delete_reason" class="form-control mb-2" rows="2" required
                        placeholder="Lý do xóa mềm..."></textarea>

                    <button class="btn btn-danger w-100"
                        onclick="return confirm('Xóa mềm đơn hàng? Dữ liệu lịch sử vẫn được giữ.')">
                        <i class="fa-solid fa-trash"></i> Xóa mềm
                    </button>
                </form>
            </div>
        </div>

        @if($order->commission)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Hoa hồng</h5>
            </div>

            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <span>Tiền hoa hồng</span>
                    <strong class="text-success">
                        {{ number_format($order->commission->commission_amount, 0, ',', '.') }}đ
                    </strong>
                </div>

                <div class="small text-muted mt-2">
                    CTV ID: {{ $order->commission->referrer_customer_id }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection