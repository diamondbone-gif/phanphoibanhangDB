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
<link rel="stylesheet" href="{{ asset('admin/css/pages/auth-ctvs-index.css') }}">
@endpush