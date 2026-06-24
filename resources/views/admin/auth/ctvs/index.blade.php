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
    :root {

        /* ===== Màu chữ ===== */
        --commission-text: #111827;
        --commission-title: #0f172a;
        --commission-muted: #64748b;
        --commission-white: #ffffff;

        /* ===== Màu nền tổng thể ===== */
        --commission-bg-main: #eef5ff;
        --commission-bg-light: #f8fbff;
        --commission-bg-white: #ffffff;
        --commission-bg-table-head: #e6f0fe;
        --commission-bg-soft-blue: #eff6ff;
        --commission-bg-soft-card: #f2f9ff;

        /* ===== Viền ===== */
        --commission-border: #dbeafe;
        --commission-border-soft: #edf4ff;
        --commission-border-blue: #cfe0ff;

        /* ===== Màu xanh dương ===== */
        --commission-blue: #2563eb;
        --commission-blue-dark: #1e3a8a;
        --commission-blue-1: #236ae9;
        --commission-blue-2: #1984e2;
        --commission-blue-3: #42b8e1;
        --commission-cyan: #06b6d4;

        /* ===== Màu xanh lá ===== */
        --commission-green: #16a34a;
        --commission-green-1: #17a64c;
        --commission-green-2: #1baf51;
        --commission-green-3: #51cc7e;
        --commission-green-light: #22c55e;

        /* ===== Màu đỏ / cam ===== */
        --commission-red: #ef4444;
        --commission-red-1: #f04840;
        --commission-orange: #f97316;
        --commission-orange-1: #f35831;
        --commission-orange-2: #f98a51;

        /* ===== Màu phụ ===== */
        --commission-purple: #7c3aed;
        --commission-teal: #0f766e;
        --commission-warning: #facc15;
        --commission-danger-bg: #fff7ed;

        /* ===== Shadow ===== */
        --commission-shadow-sm: 0 6px 16px rgba(15, 23, 42, 0.045);
        --commission-shadow-md: 0 10px 28px rgba(37, 99, 235, 0.10);
        --commission-shadow-lg: 0 18px 45px rgba(15, 23, 42, 0.10);
        --commission-shadow-modal: 0 30px 90px rgba(15, 23, 42, 0.26);

        /* ===== Gradient chính ===== */
        --commission-gradient-page:
            radial-gradient(circle at top left, rgba(37, 99, 235, 0.18), transparent 30%),
            radial-gradient(circle at top right, rgba(14, 165, 233, 0.16), transparent 34%),
            linear-gradient(135deg, #eef5ff 0%, #f8fbff 55%, #ffffff 100%);

        --commission-gradient-total: linear-gradient(135deg, #236ae9 0%, #1984e2 45%, #42b8e1 100%);
        --commission-gradient-paid: linear-gradient(135deg, #17a64c 0%, #1baf51 45%, #51cc7e 100%);
        --commission-gradient-debt: linear-gradient(135deg, #f04840 0%, #f35831 55%, #f98a51 100%);

        --commission-gradient-icon: linear-gradient(135deg, #2563eb 0%, #06b6d4 100%);
        --commission-gradient-modal-header: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
        --commission-gradient-box: linear-gradient(135deg, #eff6ff 0%, #f8fbff 100%);
        --commission-gradient-table-head: linear-gradient(180deg, #eff6ff 0%, #dbeafe 100%);
    }

    .ctv-page {
        min-height: calc(100vh - 80px);
        padding: 24px 24px 40px;
        color: var(--commission-text);
        background: var(--commission-gradient-page);
        border-radius: 24px;
    }

    .ctv-breadcrumb {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 15px;
        margin-bottom: 14px !important;
        color: var(--commission-muted);
        font-size: 13px;
        font-weight: 700;
        background: rgba(255, 255, 255, 0.78);
        border: 1px solid var(--commission-border);
        border-radius: 999px;
        box-shadow: var(--commission-shadow-sm);
    }

    .ctv-breadcrumb a {
        color: var(--commission-blue);
        font-weight: 800;
        text-decoration: none;
    }

    .ctv-breadcrumb a:hover {
        color: var(--commission-blue-dark);
    }

    .ctv-breadcrumb span:last-child {
        color: var(--commission-blue-dark);
    }

    .ctv-page-title {
        position: relative;
        display: flex;
        align-items: center;
        gap: 13px;
        font-size: 30px;
        font-weight: 900;
        color: var(--commission-title);
        letter-spacing: -0.04em;
    }

    .ctv-page-title::before {
        content: "";
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        background: var(--commission-gradient-icon);
        border-radius: 16px;
        box-shadow: var(--commission-shadow-md);
    }

    .ctv-page .text-muted {
        color: var(--commission-muted) !important;
        font-weight: 600;
    }

    .ctv-page .alert {
        border-radius: 18px;
        border: 1px solid transparent;
        box-shadow: var(--commission-shadow-sm);
    }

    .ctv-page .alert-success {
        color: var(--commission-green);
        background: rgba(34, 197, 94, 0.10);
        border-color: rgba(34, 197, 94, 0.22);
        font-weight: 700;
    }

    .ctv-page .alert-danger {
        color: var(--commission-red-1);
        background: var(--commission-danger-bg);
        border-color: rgba(239, 68, 68, 0.22);
        font-weight: 700;
    }

    .ctv-filter-card {
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid var(--commission-border-soft);
        border-radius: 22px;
        padding: 18px;
        box-shadow: var(--commission-shadow-md);
        backdrop-filter: blur(10px);
    }

    .ctv-control {
        height: 46px;
        color: var(--commission-text);
        font-size: 15px;
        font-weight: 600;
        background-color: var(--commission-bg-light);
        border: 1px solid var(--commission-border-blue);
        border-radius: 15px;
        box-shadow: none;
        transition: all 0.18s ease;
    }

    .ctv-control::placeholder {
        color: #94a3b8;
        font-weight: 500;
    }

    .ctv-control:focus {
        color: var(--commission-text);
        background-color: var(--commission-bg-white);
        border-color: var(--commission-blue);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.13);
    }

    .ctv-filter-btn,
    .ctv-reset-btn {
        height: 46px;
        border-radius: 15px;
        font-size: 14px;
        font-weight: 800;
        transition: all 0.18s ease;
    }

    .ctv-filter-btn {
        color: var(--commission-white);
        background: var(--commission-gradient-total);
        border: 0;
        box-shadow: var(--commission-shadow-md);
    }

    .ctv-filter-btn:hover,
    .ctv-filter-btn:focus {
        color: var(--commission-white);
        background: var(--commission-gradient-total);
        transform: translateY(-1px);
        box-shadow: var(--commission-shadow-lg);
    }

    .ctv-reset-btn {
        color: var(--commission-blue);
        background: var(--commission-bg-white);
        border: 1px solid var(--commission-border-blue) !important;
        box-shadow: var(--commission-shadow-sm);
    }

    .ctv-reset-btn:hover,
    .ctv-reset-btn:focus {
        color: var(--commission-white);
        background: var(--commission-gradient-total);
        border-color: transparent !important;
        transform: translateY(-1px);
        box-shadow: var(--commission-shadow-md);
    }

    .ctv-table-card {
        background: rgba(255, 255, 255, 0.94);
        border: 1px solid var(--commission-border-soft);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: var(--commission-shadow-md);
        backdrop-filter: blur(10px);
    }

    .ctv-table {
        --bs-table-bg: transparent;
        margin-bottom: 0;
    }

    .ctv-table thead th {
        background: var(--commission-gradient-table-head);
        color: var(--commission-blue-dark);
        font-weight: 900;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        border-bottom: 1px solid var(--commission-border-blue);
        padding: 16px;
        white-space: nowrap;
        vertical-align: middle;
    }

    .ctv-table tbody td {
        padding: 16px;
        color: var(--commission-text);
        font-size: 14px;
        font-weight: 600;
        border-bottom: 1px solid var(--commission-border-soft);
        background: var(--commission-bg-white);
        vertical-align: middle;
        white-space: nowrap;
    }

    .ctv-table tbody tr:hover td {
        background: var(--commission-bg-soft-blue);
    }

    .ctv-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .ctv-table tbody td.fw-bold {
        color: var(--commission-blue-dark);
        font-weight: 900 !important;
    }

    .ctv-money {
        font-weight: 900;
        font-size: 17px;
    }

    .ctv-money.text-success {
        color: var(--commission-green) !important;
    }

    .ctv-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 28px;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        line-height: 1;
        white-space: nowrap;
    }

    .ctv-status-active {
        color: var(--commission-white);
        background: var(--commission-gradient-paid);
        box-shadow: 0 8px 18px rgba(22, 163, 74, 0.18);
    }

    .ctv-status-warning {
        color: var(--commission-title);
        background: linear-gradient(135deg, rgba(250, 204, 21, 0.95), rgba(249, 115, 22, 0.45));
        border: 1px solid rgba(249, 115, 22, 0.25);
        box-shadow: 0 8px 18px rgba(249, 115, 22, 0.12);
    }

    .ctv-eye-btn {
        width: 40px;
        height: 38px;
        min-height: 38px;
        padding: 0;
        border: 1px solid var(--commission-border-blue);
        border-radius: 14px;
        color: var(--commission-blue);
        background: var(--commission-bg-white);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: var(--commission-shadow-sm);
        transition: all 0.18s ease;
    }

    .ctv-eye-btn:hover,
    .ctv-eye-btn:focus {
        color: var(--commission-white);
        background: var(--commission-gradient-total);
        border-color: transparent;
        transform: translateY(-1px);
        box-shadow: var(--commission-shadow-md);
    }

    .ctv-pagination {
        padding: 14px 18px;
        display: flex;
        justify-content: flex-end;
        background: var(--commission-bg-light);
        border-top: 1px solid var(--commission-border);
    }

    .ctv-pagination .pagination {
        gap: 6px;
        flex-wrap: wrap;
        margin-bottom: 0;
    }

    .ctv-pagination .page-link {
        min-width: 38px;
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--commission-blue);
        font-weight: 800;
        background: var(--commission-bg-white);
        border: 1px solid var(--commission-border-blue);
        border-radius: 12px;
        box-shadow: var(--commission-shadow-sm);
    }

    .ctv-pagination .page-link:hover {
        color: var(--commission-white);
        background: var(--commission-gradient-total);
        border-color: transparent;
    }

    .ctv-pagination .page-item.active .page-link {
        color: var(--commission-white);
        background: var(--commission-gradient-total);
        border-color: transparent;
        box-shadow: var(--commission-shadow-md);
    }

    .ctv-pagination .page-item.disabled .page-link {
        color: var(--commission-muted);
        background: var(--commission-bg-light);
        border-color: var(--commission-border-soft);
        box-shadow: none;
    }

    @media (max-width: 991.98px) {
        .ctv-page {
            padding: 18px 16px 34px;
        }

        .ctv-page-title {
            font-size: 24px;
        }

        .ctv-page-title::before {
            width: 40px;
            height: 40px;
            flex-basis: 40px;
            border-radius: 14px;
        }

        .ctv-filter-card {
            padding: 16px;
            border-radius: 20px;
        }

        .ctv-table-card {
            border-radius: 20px;
        }

        .ctv-table thead th,
        .ctv-table tbody td {
            padding: 14px;
        }
    }

    @media (max-width: 767.98px) {
        .ctv-page {
            padding: 14px 12px 30px;
            border-radius: 18px;
        }

        .ctv-breadcrumb {
            max-width: 100%;
            overflow-x: auto;
            white-space: nowrap;
        }

        .ctv-page-title {
            font-size: 21px;
            line-height: 1.3;
        }

        .ctv-page-title::before {
            width: 36px;
            height: 36px;
            flex-basis: 36px;
            border-radius: 13px;
        }

        .ctv-filter-card {
            border-radius: 18px;
            padding: 14px;
        }

        .ctv-control,
        .ctv-filter-btn,
        .ctv-reset-btn {
            height: 44px;
            border-radius: 14px;
        }

        .ctv-table-card {
            border-radius: 18px;
        }

        .ctv-table {
            min-width: 900px;
        }

        .ctv-table thead th {
            font-size: 12px;
        }

        .ctv-table tbody td {
            font-size: 13px;
        }

        .ctv-pagination {
            justify-content: center;
            padding: 12px;
        }
    }
</style>
@endpush