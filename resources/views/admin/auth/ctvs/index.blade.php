@extends('admin.auth.dashboardAmin')

@section('title', 'Danh sách Cộng tác viên')

@section('admin_content')
<div class="container-fluid ctv-page">

    <div class="ctv-breadcrumb mb-2">
        <a href="{{ route('admin.dashboard') }}">Quản lý</a>
        <span>/</span>
        <span>Danh sách Cộng tác viên</span>
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h3 class="mb-0 ctv-page-title">Danh sách Cộng tác viên</h3>
        </div>

        <div class="text-muted">
            Chỉ xem danh sách và thống kê hoạt động của CTV.
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    <form method="GET" action="{{ route('admin.ctvs.index') }}" class="ctv-filter-card mb-3">
        <div class="row g-3 align-items-center">
            <div class="col-lg-4">
                <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control ctv-control"
                    placeholder="Tìm tên, SĐT CTV...">
            </div>

            <div class="col-lg-3">
                <select name="ctv_status" class="form-select ctv-control">
                    <option value="">Trạng thái</option>

                    @foreach($ctvStatuses as $status)
                    <option value="{{ $status->id }}" @selected((string) request('ctv_status')===(string) $status->id)>
                        {{ $status->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2">
                <button class="btn btn-secondary ctv-filter-btn w-100">
                    <i class="fa-solid fa-filter me-1"></i>
                    Lọc
                </button>
            </div>

            <div class="col-lg-2">
                <a href="{{ route('admin.ctvs.index') }}" class="btn btn-light border ctv-reset-btn w-100">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </div>
    </form>

    <div class="ctv-table-card">
        <div class="table-responsive">
            <table class="table ctv-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Mã CTV / SĐT</th>
                        <th>Họ tên</th>
                        <th>Tỷ lệ HH</th>
                        <th>Khách giới thiệu</th>
                        <th>Tổng HH phát sinh</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($ctvs as $index => $ctv)
                    @php
                    $totalCommission = (float) ($commissionTotals[$ctv->id] ?? 0);
                    $showUrl = \Illuminate\Support\Facades\URL::signedRoute('admin.ctvs.show', [
                    'customer' => $ctv->id,
                    ]);

                    $statusName = $ctv->ctvStatus?->name ?? 'Chưa có trạng thái';
                    $statusCode = $ctv->ctvStatus?->code ?? '';
                    $isActive = in_array($statusCode, ['active', 'dang_hoat_dong', 'hoat_dong'], true);
                    @endphp

                    <tr>
                        <td>{{ $ctvs->firstItem() + $index }}</td>

                        <td class="fw-bold">
                            {{ $ctv->phone ?: $ctv->customer_code }}
                        </td>

                        <td>{{ $ctv->full_name }}</td>

                        <td>
                            {{ number_format((float) ($ctv->commission_rate ?? 0), 0) }}%
                        </td>

                        <td>
                            {{ $ctv->referred_customers_count ?? 0 }} khách
                        </td>

                        <td class="ctv-money text-success">
                            {{ number_format($totalCommission, 0, ',', '.') }}đ
                        </td>

                        <td>
                            @if($isActive)
                            <span class="ctv-status ctv-status-active">
                                {{ $statusName }}
                            </span>
                            @else
                            <span class="ctv-status ctv-status-warning">
                                {{ $statusName }}
                            </span>
                            @endif
                        </td>

                        <td class="text-end">
                            <a href="{{ $showUrl }}" class="btn ctv-eye-btn" title="Xem chi tiết">
                                <i class="fa-regular fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            Chưa có CTV nào.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ctvs->hasPages())
        <div class="ctv-pagination">
            {{ $ctvs->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

@push('styles')
<style>
    .ctv-page {
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

    .ctv-page-title {
        font-size: 30px;
        font-weight: 800;
        color: #111827;
    }

    .ctv-filter-card {
        background: #fff;
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
    }

    .ctv-control {
        height: 44px;
        border-radius: 10px;
        border-color: #d5deea;
        font-size: 16px;
    }

    .ctv-filter-btn {
        height: 44px;
        border-radius: 10px;
        font-weight: 700;
    }

    .ctv-reset-btn {
        height: 44px;
        border-radius: 10px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .ctv-table-card {
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
    }

    .ctv-table thead th {
        background: #f8fafc;
        color: #46536a;
        font-weight: 800;
        border-bottom: 1px solid #cbd5e1;
        padding: 12px 16px;
        white-space: nowrap;
    }

    .ctv-table tbody td {
        padding: 13px 16px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: middle;
    }

    .ctv-money {
        font-weight: 900;
        font-size: 17px;
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

    .ctv-pagination {
        padding: 12px 16px;
        display: flex;
        justify-content: flex-end;
    }
</style>
@endpush