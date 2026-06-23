@extends('admin.auth.dashboardAmin')

@section('title', 'Chi tiết khách được giới thiệu')

@section('admin_content')
@php
$backUrl = \Illuminate\Support\Facades\URL::signedRoute('admin.ctvs.show', [
'customer' => $ctv->id,
]);

$originCustomerUrl = \Illuminate\Support\Facades\URL::signedRoute('admin.customers.show', [
'customer' => $referred->id,
]);
@endphp

<div class="container-fluid referred-detail-page">

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
        <div>
            <h3 class="mb-1 fw-bold">Chi tiết khách được giới thiệu</h3>
            <p class="text-muted mb-0">
                Trang này chỉ xem thông tin, không chỉnh sửa dữ liệu.
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ $backUrl }}" class="btn btn-light border">
                <i class="fa-solid fa-arrow-left me-1"></i>
                Quay lại CTV
            </a>

            <a href="{{ $originCustomerUrl }}" class="btn btn-outline-primary">
                Xem hồ sơ gốc
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm mb-4 referred-card">
                <div class="card-body">
                    <h5 class="text-primary fw-bold mb-3">
                        Thông tin khách hàng
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Mã khách hàng</div>
                            <div class="fw-bold">{{ $referred->customer_code }}</div>
                        </div>

                        <div class="col-md-6">
                            <div class="text-muted small">Họ tên</div>
                            <div class="fw-bold">{{ $referred->full_name }}</div>
                        </div>

                        <div class="col-md-6">
                            <div class="text-muted small">Số điện thoại</div>
                            <div>{{ $referred->phone }}</div>
                        </div>

                        <div class="col-md-6">
                            <div class="text-muted small">Email</div>
                            <div>{{ $referred->email ?? '—' }}</div>
                        </div>

                        <div class="col-md-6">
                            <div class="text-muted small">Loại khách</div>
                            <div>{{ $referred->type?->name ?? '—' }}</div>
                        </div>

                        <div class="col-md-6">
                            <div class="text-muted small">Trạng thái</div>
                            <div>{{ $referred->status?->name ?? '—' }}</div>
                        </div>

                        <div class="col-md-12">
                            <div class="text-muted small">Ghi chú tư vấn</div>
                            <div>{{ $referred->detail?->consultation_note ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm referred-card">
                <div class="card-body">
                    <h5 class="text-primary fw-bold mb-3">
                        Đơn hàng của khách
                    </h5>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Ngày mua</th>
                                    <th>Tổng tiền</th>
                                    <th>Phí vận chuyển</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($orders as $order)
                                <tr>
                                    <td>{{ $order->order_code }}</td>
                                    <td>
                                        {{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('d/m/Y') : '—' }}
                                    </td>
                                    <td class="fw-bold">
                                        {{ number_format((float) $order->total_amount, 0, ',', '.') }}đ
                                    </td>
                                    <td>
                                        {{ number_format((float) $order->shipping_fee, 0, ',', '.') }}đ
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        Khách này chưa có đơn hàng.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm referred-card">
                <div class="card-body">
                    <h5 class="text-primary fw-bold mb-3">
                        Thông tin giới thiệu
                    </h5>

                    <div class="mb-3">
                        <div class="text-muted small">CTV giới thiệu</div>
                        <div class="fw-bold">{{ $ctv->full_name }}</div>
                        <div class="text-muted">{{ $ctv->phone }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">Ngày giới thiệu</div>
                        <div>
                            {{ $referral->started_at ? \Carbon\Carbon::parse($referral->started_at)->format('d/m/Y') : '—' }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">Số đơn hàng</div>
                        <div class="fs-4 fw-bold">{{ $referred->orders_count ?? 0 }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">Tổng tiền mua</div>
                        <div class="fs-4 fw-bold text-primary">
                            {{ number_format((float) $totalOrderAmount, 0, ',', '.') }}đ
                        </div>
                    </div>

                    <div class="mb-0">
                        <div class="text-muted small">Hoa hồng phát sinh</div>
                        <div class="fs-4 fw-bold text-success">
                            {{ number_format((float) $totalCommission, 0, ',', '.') }}đ
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    .referred-detail-page {
        padding: 20px 24px 40px;
    }

    .referred-card {
        border-radius: 18px;
    }

    .table thead th {
        background: #f8fafc;
        color: #46536a;
        font-weight: 800;
        border-bottom: 1px solid #cbd5e1;
    }
</style>
@endpush