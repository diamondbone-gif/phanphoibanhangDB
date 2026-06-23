@extends('admin.auth.dashboardAmin')

@section('title', 'Chi tiết Cộng tác viên')

@section('admin_content')
@php
$backUrl = route('admin.ctvs.index');

$originProfileUrl = \Illuminate\Support\Facades\URL::signedRoute('admin.customers.show', [
'customer' => $customer->id,
]);

$statusName = $customer->ctvStatus?->name ?? 'Chưa có trạng thái';
$statusCode = $customer->ctvStatus?->code ?? '';
$isActive = in_array($statusCode, ['active', 'dang_hoat_dong', 'hoat_dong'], true);

$firstLetter = mb_substr($customer->full_name ?? 'C', 0, 1);
@endphp

<div class="container-fluid ctv-detail-page">

    <div class="ctv-breadcrumb mb-3">
        <a href="{{ route('admin.dashboard') }}">Quản lý</a>
        <span>/</span>
        <a href="{{ route('admin.ctvs.index') }}">Danh sách Cộng tác viên</a>
        <span>/</span>
        <span>Chi tiết Cộng tác viên</span>
    </div>

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
        <div>
            <h3 class="ctv-detail-title mb-1">Chi tiết Cộng tác viên</h3>
            <p class="text-muted mb-0">
                Xem thông tin và danh sách khách hàng do CTV này giới thiệu.
            </p>
        </div>

        <a href="{{ $backUrl }}" class="btn btn-light border ctv-back-btn">
            <i class="fa-solid fa-arrow-left me-1"></i>
            Quay lại danh sách
        </a>
    </div>

    <div class="ctv-profile-card mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="ctv-avatar">
                {{ $firstLetter }}
            </div>

            <div>
                <h4 class="mb-1 fw-bold">{{ $customer->full_name }}</h4>

                <div class="ctv-meta">
                    <span>
                        <i class="fa-solid fa-fingerprint me-1"></i>
                        Mã CTV: {{ $customer->customer_code }}
                    </span>

                    <span class="meta-divider">|</span>

                    <span>
                        <i class="fa-solid fa-phone me-1"></i>
                        SĐT: {{ $customer->phone }}
                    </span>
                </div>

                <div class="d-flex align-items-center gap-2 mt-2">
                    @if($isActive)
                    <span class="ctv-status ctv-status-active">{{ $statusName }}</span>
                    @else
                    <span class="ctv-status ctv-status-warning">{{ $statusName }}</span>
                    @endif

                    <span class="ctv-rate-badge">
                        Tỷ lệ HH: {{ number_format((float) ($customer->commission_rate ?? 0), 0) }}%
                    </span>
                </div>
            </div>
        </div>

        <div>
            <a href="{{ $originProfileUrl }}" class="btn btn-outline-primary ctv-origin-btn">
                <i class="fa-regular fa-user me-1"></i>
                Xem hồ sơ gốc
            </a>
        </div>
    </div>

    <div class="ctv-detail-card">
        <ul class="nav ctv-tabs" id="ctvDetailTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overviewPane"
                    type="button" role="tab">
                    Tổng quan hiệu quả
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link" id="referred-tab" data-bs-toggle="tab" data-bs-target="#referredPane"
                    type="button" role="tab">
                    Khách đã giới thiệu
                </button>
            </li>
        </ul>

        <div class="tab-content pt-3">
            <div class="tab-pane fade show active" id="overviewPane" role="tabpanel">
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="ctv-stat-card">
                            <div class="ctv-stat-label">Tổng khách giới thiệu</div>
                            <div class="ctv-stat-number">
                                {{ $customer->referred_customers_count ?? 0 }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="ctv-stat-card">
                            <div class="ctv-stat-label">Tổng doanh thu từ KH</div>
                            <div class="ctv-stat-number text-primary">
                                {{ number_format((float) $totalRevenueFromReferred, 0, ',', '.') }}đ
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="ctv-stat-card">
                            <div class="ctv-stat-label">Tổng HH phát sinh</div>
                            <div class="ctv-stat-number text-success">
                                {{ number_format((float) $totalCommission, 0, ',', '.') }}đ
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="ctv-stat-card">
                            <div class="ctv-stat-label">HH chờ thanh toán</div>
                            <div class="ctv-stat-number text-warning">
                                {{ number_format((float) $pendingCommission, 0, ',', '.') }}đ
                            </div>
                        </div>
                    </div>
                </div>

                <h6 class="ctv-section-heading">
                    Lịch sử thanh toán hoa hồng gần đây
                </h6>

                <div class="table-responsive">
                    <table class="table ctv-detail-table align-middle">
                        <thead>
                            <tr>
                                <th>Ngày thanh toán</th>
                                <th>Số tiền</th>
                                <th>Người duyệt</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($paymentHistories as $payment)
                            <tr>
                                <td>
                                    {{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('d/m/Y') : '—' }}
                                </td>

                                <td class="text-success fw-bold">
                                    {{ number_format((float) $payment->commission_amount, 0, ',', '.') }}đ
                                </td>

                                <td>
                                    {{ $payment->approved_by_name ?? 'Admin' }}
                                </td>

                                <td>
                                    <span class="ctv-status ctv-status-active">
                                        Đã chuyển khoản
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    Chưa có lịch sử thanh toán hoa hồng.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="referredPane" role="tabpanel">
                <div class="table-responsive">
                    <table class="table ctv-detail-table align-middle">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Tên khách hàng</th>
                                <th>Số điện thoại</th>
                                <th>Số đơn hàng</th>
                                <th>Tổng tiền mua</th>
                                <th>Hoa hồng phát sinh</th>
                                <th>Ngày giới thiệu</th>
                                <th class="text-end">Xem chi tiết</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($referredCustomers as $index => $referred)
                            @php
                            $referredShowUrl =
                            \Illuminate\Support\Facades\URL::signedRoute('admin.ctvs.referred-customers.show', [
                            'ctv' => $customer->id,
                            'referred' => $referred->customer_id,
                            ]);
                            @endphp

                            <tr>
                                <td>{{ $index + 1 }}</td>

                                <td>
                                    <a href="{{ $referredShowUrl }}" class="ctv-link">
                                        {{ $referred->full_name }}
                                    </a>
                                </td>

                                <td>{{ $referred->phone }}</td>

                                <td>{{ $referred->order_count }}</td>

                                <td>
                                    {{ number_format((float) $referred->total_order_amount, 0, ',', '.') }}đ
                                </td>

                                <td class="text-success fw-bold">
                                    {{ number_format((float) $referred->total_commission, 0, ',', '.') }}đ
                                </td>

                                <td>
                                    {{ $referred->started_at ? \Carbon\Carbon::parse($referred->started_at)->format('d/m/Y') : '—' }}
                                </td>

                                <td class="text-end">
                                    <a href="{{ $referredShowUrl }}" class="btn ctv-eye-btn" title="Xem chi tiết">
                                        <i class="fa-regular fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    CTV này chưa giới thiệu khách nào.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .ctv-detail-page {
        padding: 20px 24px 40px;
    }

    .ctv-breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #637083;
        font-size: 14px;
    }

    .ctv-breadcrumb a {
        color: #0d6efd;
        font-weight: 700;
        text-decoration: none;
    }

    .ctv-detail-title {
        font-size: 30px;
        font-weight: 800;
        color: #111827;
    }

    .ctv-back-btn,
    .ctv-origin-btn {
        border-radius: 12px;
        font-weight: 700;
    }

    .ctv-profile-card {
        background: #fff;
        border-radius: 18px;
        padding: 22px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .ctv-avatar {
        width: 58px;
        height: 58px;
        border-radius: 999px;
        background: #0d6efd;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        font-weight: 800;
        flex-shrink: 0;
    }

    .ctv-meta {
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .ctv-status {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        color: #fff;
    }

    .ctv-status-active {
        background: #108a55;
    }

    .ctv-status-warning {
        background: #f4b000;
        color: #111827;
    }

    .ctv-rate-badge {
        background: #eaf2ff;
        color: #0d6efd;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 800;
    }

    .ctv-detail-card {
        background: #fff;
        border-radius: 18px;
        padding: 16px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
    }

    .ctv-tabs {
        border-bottom: 1px solid #d6e1ef;
    }

    .ctv-tabs .nav-link {
        color: #46536a;
        font-weight: 800;
        border: 1px solid transparent;
        border-radius: 12px 12px 0 0;
        padding: 12px 18px;
    }

    .ctv-tabs .nav-link.active {
        color: #0d6efd;
        border-color: #bcd3ff #bcd3ff #fff;
        box-shadow: 0 0 0 0.18rem rgba(37, 99, 235, 0.12);
        background: #fff;
    }

    .ctv-stat-card {
        background: #f8fafc;
        border-radius: 16px;
        padding: 16px;
        text-align: center;
    }

    .ctv-stat-label {
        color: #3f4a5f;
        font-size: 16px;
        margin-bottom: 6px;
    }

    .ctv-stat-number {
        font-size: 26px;
        font-weight: 500;
        color: #111827;
    }

    .ctv-section-heading {
        color: #0d6efd;
        font-weight: 800;
        margin-bottom: 14px;
    }

    .ctv-detail-table thead th {
        background: #f8fafc;
        color: #46536a;
        font-weight: 800;
        border-bottom: 1px solid #cbd5e1;
        white-space: nowrap;
    }

    .ctv-detail-table td,
    .ctv-detail-table th {
        padding: 10px 14px;
    }

    .ctv-link {
        color: #0d6efd;
        text-decoration: none;
        font-weight: 800;
    }

    .ctv-link:hover {
        text-decoration: underline;
    }

    .ctv-eye-btn {
        width: 36px;
        height: 32px;
        border: 1px solid #d6e1ef;
        border-radius: 12px;
        color: #0d6efd;
        background: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .ctv-eye-btn:hover {
        background: #eef5ff;
        color: #0b5ed7;
    }

    @media (max-width: 767.98px) {
        .ctv-profile-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .ctv-origin-btn {
            width: 100%;
        }
    }
</style>
@endpush