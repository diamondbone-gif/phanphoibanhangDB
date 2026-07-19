@extends('admin.auth.dashboardAmin')

@section('admin_content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">Bán hàng</li>
        <li class="breadcrumb-item active">Danh sách đơn hàng</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Danh sách đơn hàng</h3>
    <a href="{{ route('admin.orders.returns.index') }}" class="btn btn-warning ms-auto">
        <i class="fa-solid fa-rotate-left me-1"></i> Hoàn và đổi hàng
    </a>
    <a href="{{ route('admin.orders.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i> Lên đơn mới
    </a>
</div>

@include('admin.auth.partials.alerts')

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-10">
                <input type="text" name="keyword" value="{{ $keyword }}" class="form-control"
                    placeholder="Tìm mã đơn, tên khách, số điện thoại...">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">
                    <i class="fa-solid fa-magnifying-glass"></i> Tìm
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 config-table">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Mã ĐH</th>
                        <th>Khách hàng</th>
                        <th>Tổng tiền</th>
                        <th>Đã thanh toán</th>
                        <th>Còn nợ</th>
                        <th>Ngày tạo</th>
                        <th class="text-end pe-3">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td class="ps-3 fw-bold" data-label="Mã ĐH">
                            <a href="{{ route('admin.orders.show', $order) }}">
                                {{ $order->order_code }}
                            </a>
                        </td>
                        <td data-label="Khách hàng">
                            <div class="fw-semibold">{{ $order->customer->full_name ?? '---' }}</div>
                            <small class="text-muted">{{ $order->customer->phone ?? '' }}</small>
                        </td>
                        <td data-label="Tổng tiền" class="fw-bold text-danger">
                            {{ number_format($order->final_amount, 0, ',', '.') }}đ
                        </td>
                        <td data-label="Đã thanh toán">
                            {{ number_format($order->paid_amount, 0, ',', '.') }}đ
                        </td>
                        <td data-label="Còn nợ">
                            {{ number_format($order->debt_amount, 0, ',', '.') }}đ
                        </td>
                        <td data-label="Ngày tạo">
                            {{ optional($order->order_date)->format('d/m/Y H:i') }}
                        </td>
                        <td class="text-end pe-3" data-label="Thao tác">
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-light border">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.orders.edit', $order) }}"
                                class="btn btn-sm btn-light border text-primary">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            Chưa có đơn hàng.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $orders->links() }}
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/pages/auth-orders-index.css') }}">
@endpush
