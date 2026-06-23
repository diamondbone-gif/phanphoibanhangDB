@extends('admin.auth.dashboardAmin')

@section('title', 'Danh sách khách hàng')

@section('admin_content')
<div class="container-fluid customer-index-page">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h3 class="mb-1">Danh sách khách hàng</h3>
            <p class="text-muted mb-0">
                Quản lý khách hàng, CTV, người giới thiệu và tình trạng mua hàng.
            </p>
        </div>

        <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i>
            Thêm khách hàng
        </a>
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

    @if($errors->any())
    <div class="alert alert-danger">
        <strong>Vui lòng kiểm tra lại:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="GET" action="{{ route('admin.customers.index') }}" class="customer-filter-card mb-3">
        <div class="row g-3 align-items-center">
            <div class="col-lg-3">
                <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control customer-control"
                    placeholder="Tìm tên, SĐT, Mã KH...">
            </div>

            <div class="col-lg-2">
                <select name="customer_type" class="form-select customer-control">
                    <option value="">Tất cả loại khách</option>

                    @foreach($customerTypes as $type)
                    <option value="{{ $type->code }}" @selected(request('customer_type')===$type->code)>
                        {{ $type->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2">
                <select name="buy_status" class="form-select customer-control">
                    <option value="">Tình trạng mua</option>
                    <option value="chua_mua" @selected(request('buy_status')==='chua_mua' )>Chưa mua</option>
                    <option value="da_mua" @selected(request('buy_status')==='da_mua' )>Đã mua</option>
                    <option value="mua_lai" @selected(request('buy_status')==='mua_lai' )>Mua lại</option>
                </select>
            </div>

            <div class="col-lg-2">
                <select name="customer_status" class="form-select customer-control">
                    <option value="">Trạng thái KH</option>

                    @foreach($customerStatuses as $status)
                    <option value="{{ $status->code }}" @selected(request('customer_status')===$status->code)>
                        {{ $status->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-1">
                <button class="btn btn-secondary customer-filter-btn w-100">
                    <i class="fa-solid fa-filter me-1"></i>
                    Lọc
                </button>
            </div>

            <div class="col-lg-1">
                <a href="{{ route('admin.customers.index') }}" class="btn btn-light border customer-reset-btn w-100">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </div>
    </form>

    <div class="customer-table-card">
        <div class="table-responsive">
            <table class="table customer-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Mã KH</th>
                        <th>Họ tên</th>
                        <th>Số điện thoại</th>
                        <th>Loại khách</th>
                        <th>Người giới thiệu</th>
                        <th>Số đơn</th>
                        <th>Tình trạng</th>
                        <th>Vai trò</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($customers as $index => $customer)
                    @php
                    $orderCount = (int) ($customer->orders_count ?? 0);

                    if ($orderCount === 0) {
                    $buyStatusText = 'Chưa mua';
                    } elseif ($orderCount === 1) {
                    $buyStatusText = 'Đã mua';
                    } else {
                    $buyStatusText = 'Mua lại';
                    }

                    $isCtv = $customer->role?->code === 'ctv';

                    $showUrl = \Illuminate\Support\Facades\URL::signedRoute('admin.customers.show', [
                    'customer' => $customer->id,
                    ]);

                    $editUrl = \Illuminate\Support\Facades\URL::signedRoute('admin.customers.edit', [
                    'customer' => $customer->id,
                    ]);

                    $convertToCtvUrl = \Illuminate\Support\Facades\URL::signedRoute('admin.customers.convert-to-ctv', [
                    'customer' => $customer->id,
                    ]);

                    $markStoppedUrl =
                    \Illuminate\Support\Facades\URL::signedRoute('admin.customers.mark-stopped-buying', [
                    'customer' => $customer->id,
                    ]);
                    @endphp

                    <tr>
                        <td>{{ $customers->firstItem() + $index }}</td>

                        <td>{{ $customer->customer_code }}</td>

                        <td class="fw-bold">{{ $customer->full_name }}</td>

                        <td>{{ $customer->phone }}</td>

                        <td>
                            @if($customer->type)
                            @if(str_contains($customer->type->code, 'ctv'))
                            <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis customer-badge">
                                {{ $customer->type->name }}
                            </span>
                            @else
                            <span class="badge rounded-pill bg-primary-subtle text-primary customer-badge">
                                {{ $customer->type->name }}
                            </span>
                            @endif
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td>
                            @if($customer->receivedReferral?->referrer)
                            <div>{{ $customer->receivedReferral->referrer->full_name }}</div>
                            <div class="text-muted small">{{ $customer->receivedReferral->referrer->phone }}</div>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td>{{ $orderCount }}</td>

                        <td>
                            <span class="badge rounded-pill bg-light text-dark customer-badge">
                                {{ $buyStatusText }}
                            </span>
                        </td>

                        <td>
                            {{ $customer->role?->name ?? 'Khách' }}
                        </td>

                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-light border dropdown-toggle customer-action-btn" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    Thao tác
                                </button>

                                <ul class="dropdown-menu dropdown-menu-end customer-action-menu">
                                    <li>
                                        <a class="dropdown-item" href="{{ $showUrl }}">
                                            <i class="fa-regular fa-eye me-2"></i>
                                            Xem chi tiết
                                        </a>
                                    </li>

                                    <li>
                                        <a class="dropdown-item" href="{{ $editUrl }}">
                                            <i class="fa-regular fa-pen-to-square me-2"></i>
                                            Sửa thông tin
                                        </a>
                                    </li>

                                    @if(!$isCtv)
                                    <li>
                                        <form method="POST" action="{{ $convertToCtvUrl }}"
                                            onsubmit="return confirm('Bạn có chắc muốn chuyển khách hàng này thành CTV?')">
                                            @csrf

                                            <button type="submit" class="dropdown-item text-success">
                                                <i class="fa-solid fa-people-arrows me-2"></i>
                                                Chuyển thành CTV
                                            </button>
                                        </form>
                                    </li>
                                    @endif

                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>

                                    <li>
                                        <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal"
                                            data-bs-target="#stopBuyingModal{{ $customer->id }}">
                                            <i class="fa-regular fa-circle-xmark me-2"></i>
                                            Đánh dấu ngưng mua
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>

                    <div class="modal fade stop-buying-modal" id="stopBuyingModal{{ $customer->id }}" tabindex="-1"
                        aria-labelledby="stopBuyingModalLabel{{ $customer->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <form method="POST" action="{{ $markStoppedUrl }}"
                                class="modal-content stop-buying-content">
                                @csrf

                                <div class="modal-header stop-buying-header">
                                    <h5 class="modal-title" id="stopBuyingModalLabel{{ $customer->id }}">
                                        Đánh dấu khách ngừng mua
                                    </h5>

                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Đóng"></button>
                                </div>

                                <div class="modal-body stop-buying-body">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            Lý do ngừng mua
                                        </label>

                                        <select name="customer_stop_reason_id" class="form-select" required>
                                            <option value="">-- Chọn lý do --</option>

                                            @foreach(($stopReasons ?? collect()) as $reason)
                                            <option value="{{ $reason->id }}">
                                                {{ $reason->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-0">
                                        <label class="form-label">
                                            Ghi chú thêm
                                        </label>

                                        <textarea name="stopped_reason_note" class="form-control" rows="4"
                                            placeholder="Nhập chi tiết lý do..."></textarea>
                                    </div>
                                </div>

                                <div class="modal-footer stop-buying-footer">
                                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
                                        Hủy
                                    </button>

                                    <button type="submit" class="btn btn-danger">
                                        Xác nhận
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            Chưa có khách hàng nào.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="customer-table-footer">
            <div>
                Hiển thị
                <strong>{{ $customers->firstItem() ?? 0 }}</strong>
                -
                <strong>{{ $customers->lastItem() ?? 0 }}</strong>
                trên
                <strong>{{ $customers->total() }}</strong>
                khách hàng
            </div>

            <div>
                {{ $customers->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    :root {
        --db-page-bg: #f4f8fc;
        --db-card-bg: #ffffff;
        --db-border: #dbe4ef;
        --db-border-strong: #cbd5e1;
        --db-text: #0f172a;
        --db-text-soft: #475569;
        --db-muted: #64748b;
        --db-blue: #1667f2;
        --db-blue-soft: #e7f0ff;
        --db-gray-btn: #737d86;
        --db-green-soft: #d8f7e5;
        --db-green: #008b3a;
        --db-red-soft: #fee2e2;
        --db-red: #d92d20;
        --db-yellow-soft: #fff1cc;
        --db-yellow: #9a6700;
        --db-pill-gray: #edf3f9;
    }

    body {
        background: var(--db-page-bg) !important;
    }

    .customer-index-page {
        background: var(--db-page-bg);
        min-height: calc(100vh - 70px);
        padding: 18px 24px 40px;
    }

    .customer-index-page h3 {
        display: block;
        color: #0f172a;
        font-size: 28px;
        font-weight: 800;
        line-height: 1.2;
        margin: 0;
    }

    .customer-index-page p {
        display: none;
    }

    .customer-index-page .btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 40px;
        border-radius: 0 0 10px 10px;
        background: var(--db-blue) !important;
        border-color: var(--db-blue) !important;
        color: #fff !important;
        font-weight: 800;
        padding: 9px 16px;
        box-shadow: none;
    }

    .customer-index-page .btn-primary:hover {
        background: #0f5ddd !important;
        border-color: #0f5ddd !important;
    }

    .customer-filter-card {
        background: var(--db-card-bg);
        border: 1px solid #edf2f7;
        border-radius: 18px;
        padding: 16px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
        margin-bottom: 16px;
    }

    .customer-filter-card .row {
        align-items: center;
    }

    .customer-filter-card .col-lg-3 {
        flex: 0 0 25%;
        max-width: 25%;
    }

    .customer-filter-card .col-lg-2 {
        flex: 0 0 17%;
        max-width: 17%;
    }

    .customer-filter-card .col-lg-1 {
        flex: 0 0 12%;
        max-width: 12%;
    }

    .customer-control {
        height: 42px;
        border-radius: 12px;
        border: 1px solid #cfdbea;
        background-color: #fff;
        color: var(--db-text);
        font-size: 16px;
        font-weight: 500;
        box-shadow: none;
    }

    .customer-control::placeholder {
        color: #475569;
        opacity: 0.95;
    }

    .customer-control:focus {
        border-color: var(--db-blue);
        box-shadow: 0 0 0 3px rgba(22, 103, 242, 0.12);
    }

    .customer-filter-btn {
        height: 42px;
        border-radius: 10px;
        border: 1px solid var(--db-gray-btn) !important;
        background: var(--db-gray-btn) !important;
        color: #fff !important;
        font-size: 16px;
        font-weight: 800;
        box-shadow: none !important;
    }

    .customer-filter-btn:hover,
    .customer-filter-btn:focus {
        background: #65707a !important;
        border-color: #65707a !important;
        color: #fff !important;
    }

    .customer-reset-btn {
        height: 42px;
        border-radius: 10px;
        background: #fff !important;
        border: 1px solid #6b7280 !important;
        color: #64748b !important;
        font-size: 18px;
        font-weight: 800;
        box-shadow: none !important;
    }

    .customer-reset-btn:hover,
    .customer-reset-btn:focus {
        background: #f8fafc !important;
        color: var(--db-blue) !important;
        border-color: var(--db-blue) !important;
    }

    .customer-table-card {
        background: var(--db-card-bg);
        border: 1px solid #edf2f7;
        border-radius: 6px;
        overflow: visible;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
    }

    .table-responsive {
        overflow: visible;
    }

    .customer-table {
        margin-bottom: 0;
        color: #000;
        overflow: visible;
    }

    .customer-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 16px;
        font-weight: 800;
        white-space: nowrap;
        padding: 12px 16px;
        border-bottom: 1px solid var(--db-border-strong);
    }

    .customer-table tbody td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--db-border);
        vertical-align: middle;
        font-size: 16px;
        color: #000;
        background: #fff;
    }

    .customer-table tbody tr:hover td {
        background: #f8fbff;
    }

    .customer-table tbody td:nth-child(3) {
        font-weight: 800;
    }

    .customer-table tbody td:nth-child(3).text-danger,
    .customer-table tbody td:nth-child(3) .text-danger {
        color: #e11d48 !important;
    }

    .customer-table tbody td:nth-child(6) .small {
        color: #64748b !important;
        font-size: 14px;
        margin-top: 2px;
    }

    .customer-badge,
    .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px !important;
        border: 0 !important;
        min-height: 22px;
        padding: 5px 10px;
        font-size: 13px;
        font-weight: 800;
        line-height: 1;
        white-space: nowrap;
    }

    .bg-primary-subtle,
    .text-primary {
        background-color: var(--db-blue-soft) !important;
        color: var(--db-blue) !important;
    }

    .bg-warning-subtle,
    .text-warning-emphasis {
        background-color: var(--db-yellow-soft) !important;
        color: var(--db-yellow) !important;
    }

    .bg-light.text-dark {
        background-color: var(--db-pill-gray) !important;
        color: #475569 !important;
    }

    .js-role-ctv-badge,
    .role-ctv-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 24px;
        padding: 5px 10px;
        border-radius: 999px;
        background: var(--db-blue);
        color: #fff;
        font-size: 13px;
        font-weight: 800;
        line-height: 1;
        white-space: nowrap;
    }

    .js-stopped-buying-badge,
    .stopped-buying-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 22px;
        margin-left: 8px;
        padding: 5px 10px;
        border-radius: 999px;
        background: var(--db-red-soft);
        color: var(--db-red);
        font-size: 13px;
        font-weight: 800;
        line-height: 1;
        white-space: nowrap;
        vertical-align: middle;
    }

    .js-buy-again-badge,
    .buy-again-badge {
        background: var(--db-green-soft) !important;
        color: var(--db-green) !important;
    }

    .js-bought-badge,
    .bought-badge {
        background: var(--db-blue-soft) !important;
        color: var(--db-blue) !important;
    }

    .js-not-bought-badge,
    .not-bought-badge {
        background: var(--db-pill-gray) !important;
        color: #475569 !important;
    }

    .customer-action-btn {
        background: #fff !important;
        color: #000 !important;
        border: 1px solid #d7e0ea !important;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 800;
        padding: 6px 12px;
        box-shadow: none !important;
    }

    .customer-action-btn:hover,
    .customer-action-btn:focus {
        background: #f8fafc !important;
        color: #000 !important;
        border-color: #cbd5e1 !important;
    }

    .customer-action-menu {
        border: 0;
        border-radius: 12px;
        padding: 10px;
        min-width: 230px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
        z-index: 9999;
    }

    .customer-action-menu .dropdown-item {
        border-radius: 8px;
        padding: 10px 12px;
        font-weight: 600;
        white-space: normal;
        color: #0f172a;
    }

    .customer-action-menu .dropdown-item:hover {
        background: var(--db-blue-soft);
        color: var(--db-blue);
    }

    .customer-action-menu .dropdown-item.text-success {
        color: #16a34a !important;
    }

    .customer-action-menu .dropdown-item.text-danger {
        color: var(--db-red) !important;
    }

    .customer-action-menu form {
        margin: 0;
    }

    .stop-buying-content {
        border: 0;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(15, 23, 42, 0.3);
    }

    .stop-buying-header {
        background: #e9344f;
        color: #fff;
        border-bottom: 0;
        padding: 18px 20px;
    }

    .stop-buying-header .modal-title {
        font-weight: 800;
    }

    .stop-buying-body {
        padding: 20px;
    }

    .stop-buying-body .form-select,
    .stop-buying-body .form-control {
        border-radius: 10px;
        border-color: #d6e1ef;
    }

    .stop-buying-footer {
        border-top: 1px solid #e5e7eb;
        padding: 16px 20px;
    }

    .customer-table-footer {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        padding: 8px 16px;
        color: #475569;
        border-top: 1px solid #e2e8f0;
    }

    .customer-table-footer>div:first-child {
        display: none;
    }

    .customer-table-footer .pagination {
        margin-bottom: 0;
    }

    .customer-table-footer .page-link {
        color: var(--db-blue);
        border-color: #d7e0ea;
        font-size: 14px;
        padding: 6px 10px;
    }

    .customer-table-footer .page-item.active .page-link {
        background: var(--db-blue);
        border-color: var(--db-blue);
        color: #fff;
    }

    .customer-table-footer .page-item.disabled .page-link {
        background: #edf2f7;
        color: #64748b;
    }

    .text-muted {
        color: #64748b !important;
    }

    .small {
        font-size: 14px;
    }

    .sidebar,
    .admin-sidebar,
    .main-sidebar,
    aside {
        background: #f8fbff !important;
        border-right: 1px solid #e2e8f0;
    }

    .sidebar .nav-link,
    .admin-sidebar .nav-link,
    .main-sidebar .nav-link,
    aside .nav-link,
    .sidebar a,
    .admin-sidebar a,
    .main-sidebar a,
    aside a {
        color: #475569;
        font-weight: 700;
        border-radius: 12px;
    }

    .sidebar .nav-link.active,
    .admin-sidebar .nav-link.active,
    .main-sidebar .nav-link.active,
    aside .nav-link.active,
    .sidebar a.active,
    .admin-sidebar a.active,
    .main-sidebar a.active,
    aside a.active {
        background: var(--db-blue-soft) !important;
        color: var(--db-blue) !important;
        border-left: 4px solid var(--db-blue);
    }

    .sidebar .nav-link:hover,
    .admin-sidebar .nav-link:hover,
    .main-sidebar .nav-link:hover,
    aside .nav-link:hover,
    .sidebar a:hover,
    .admin-sidebar a:hover,
    .main-sidebar a:hover,
    aside a:hover {
        background: var(--db-blue-soft);
        color: var(--db-blue);
    }

    /* FIX dropdown và modal bị vỡ giao diện khi nằm trong table */
    .customer-table-card,
    .table-responsive,
    .customer-table,
    .customer-table tbody,
    .customer-table tr,
    .customer-table td {
        overflow: visible;
    }

    .dropdown-menu.show,
    .customer-action-menu.show {
        z-index: 2050 !important;
    }

    .modal-backdrop {
        z-index: 3000 !important;
    }

    .stop-buying-modal {
        z-index: 3010 !important;
    }

    .stop-buying-modal.show {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 16px !important;
    }

    .stop-buying-modal .modal-dialog {
        width: min(520px, calc(100vw - 32px)) !important;
        max-width: 520px !important;
        margin: 0 auto !important;
        transform: none !important;
        display: block !important;
        position: relative !important;
    }

    .stop-buying-modal .modal-content,
    .stop-buying-modal .stop-buying-content {
        display: flex !important;
        flex-direction: column !important;
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
        position: relative !important;
        background: #fff !important;
        border: 0 !important;
        border-radius: 16px !important;
        overflow: hidden !important;
        box-shadow: 0 24px 80px rgba(15, 23, 42, 0.35) !important;
        pointer-events: auto !important;
    }

    .stop-buying-modal .modal-header,
    .stop-buying-modal .stop-buying-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
        min-height: 64px !important;
        padding: 18px 22px !important;
        background: #ef3154 !important;
        color: #fff !important;
        border: 0 !important;
    }

    .stop-buying-modal .modal-title {
        color: #fff !important;
        font-size: 20px !important;
        font-weight: 800 !important;
        line-height: 1.25 !important;
        margin: 0 !important;
        max-width: calc(100% - 36px) !important;
    }

    .stop-buying-modal .btn-close {
        flex: 0 0 auto !important;
        opacity: 1 !important;
        box-shadow: none !important;
    }

    .stop-buying-modal .modal-body,
    .stop-buying-modal .stop-buying-body {
        display: block !important;
        width: 100% !important;
        padding: 22px !important;
        background: #fff !important;
    }

    .stop-buying-modal .modal-body .mb-3,
    .stop-buying-modal .modal-body .mb-0,
    .stop-buying-modal .stop-buying-body .mb-3,
    .stop-buying-modal .stop-buying-body .mb-0 {
        display: block !important;
        width: 100% !important;
    }

    .stop-buying-modal .form-label {
        display: block !important;
        width: 100% !important;
        margin-bottom: 8px !important;
        color: #0f172a !important;
        font-size: 16px !important;
        font-weight: 700 !important;
        line-height: 1.3 !important;
    }

    .stop-buying-modal .form-select,
    .stop-buying-modal .form-control,
    .stop-buying-modal textarea {
        display: block !important;
        width: 100% !important;
        min-width: 100% !important;
        max-width: 100% !important;
        border-radius: 10px !important;
        border: 1px solid #d6e1ef !important;
        background-color: #fff !important;
        color: #0f172a !important;
        font-size: 16px !important;
        line-height: 1.5 !important;
        box-shadow: none !important;
    }

    .stop-buying-modal .form-select {
        height: 44px !important;
        min-height: 44px !important;
        padding: 8px 40px 8px 12px !important;
    }

    .stop-buying-modal textarea.form-control,
    .stop-buying-modal textarea {
        min-height: 110px !important;
        resize: vertical !important;
        padding: 10px 12px !important;
    }

    .stop-buying-modal .form-select:focus,
    .stop-buying-modal .form-control:focus,
    .stop-buying-modal textarea:focus {
        border-color: var(--db-blue) !important;
        box-shadow: 0 0 0 3px rgba(22, 103, 242, 0.12) !important;
    }

    .stop-buying-modal .modal-footer,
    .stop-buying-modal .stop-buying-footer {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 10px !important;
        width: 100% !important;
        padding: 16px 22px !important;
        background: #fff !important;
        border-top: 1px solid #e5e7eb !important;
    }

    .stop-buying-modal .modal-footer .btn,
    .stop-buying-modal .stop-buying-footer .btn {
        min-width: 92px !important;
        min-height: 42px !important;
        border-radius: 9px !important;
        font-size: 16px !important;
        font-weight: 700 !important;
        padding: 8px 14px !important;
    }

    .stop-buying-modal .btn-danger {
        background: #ef3154 !important;
        border-color: #ef3154 !important;
        color: #fff !important;
    }

    .stop-buying-modal .btn-danger:hover {
        background: #dc2448 !important;
        border-color: #dc2448 !important;
    }

    @media (max-width: 576px) {
        .stop-buying-modal.show {
            align-items: flex-start !important;
            padding-top: 72px !important;
        }

        .stop-buying-modal .modal-dialog {
            width: calc(100vw - 24px) !important;
            max-width: calc(100vw - 24px) !important;
        }

        .stop-buying-modal .modal-title {
            font-size: 18px !important;
        }

        .stop-buying-modal .modal-header,
        .stop-buying-modal .stop-buying-header,
        .stop-buying-modal .modal-body,
        .stop-buying-modal .stop-buying-body,
        .stop-buying-modal .modal-footer,
        .stop-buying-modal .stop-buying-footer {
            padding-left: 16px !important;
            padding-right: 16px !important;
        }
    }

    @media (max-width: 1200px) {
        .customer-filter-card .col-lg-3 {
            flex: 0 0 50%;
            max-width: 50%;
        }

        .customer-filter-card .col-lg-2 {
            flex: 0 0 25%;
            max-width: 25%;
        }

        .customer-filter-card .col-lg-1 {
            flex: 0 0 25%;
            max-width: 25%;
        }

        .table-responsive {
            overflow-x: auto;
            overflow-y: visible;
        }

        .customer-table {
            min-width: 1180px;
        }
    }

    @media (max-width: 992px) {
        .customer-index-page {
            padding: 14px;
        }

        .customer-index-page h3 {
            font-size: 24px;
        }

        .customer-filter-card .col-lg-3,
        .customer-filter-card .col-lg-2,
        .customer-filter-card .col-lg-1 {
            flex: 0 0 100%;
            max-width: 100%;
        }

        .customer-control,
        .customer-filter-btn,
        .customer-reset-btn {
            height: 44px;
        }

        .customer-table thead th,
        .customer-table tbody td {
            font-size: 14px;
            padding: 12px;
        }
    }

    @media (max-width: 576px) {
        .customer-index-page {
            padding: 12px;
        }

        .customer-index-page .d-flex {
            align-items: flex-start !important;
        }

        .customer-index-page .btn-primary {
            width: 100%;
            border-radius: 10px;
        }

        .customer-filter-card {
            border-radius: 14px;
            padding: 12px;
        }

        .customer-table-card {
            border-radius: 10px;
        }

        .customer-table {
            min-width: 1080px;
        }

        .customer-table-footer {
            justify-content: center;
        }
    }

    /* FIX dropdown Thao tác nổi hẳn lên trên, không bị cắt trong bảng */
    .customer-table-card,
    .customer-table-card .table-responsive,
    .customer-table,
    .customer-table tbody,
    .customer-table tr,
    .customer-table td {
        overflow: visible !important;
    }

    .customer-action-menu {
        z-index: 5000 !important;
    }

    .customer-action-menu.dropdown-menu-fixed {
        position: fixed !important;
        inset: auto auto auto auto !important;
        transform: none !important;
        margin: 0 !important;
        z-index: 5000 !important;
        display: block !important;
        max-width: min(260px, calc(100vw - 24px)) !important;
        background: #fff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        box-shadow: 0 24px 70px rgba(15, 23, 42, 0.28) !important;
    }

    .customer-action-menu.dropdown-menu-fixed::before {
        content: "";
        position: absolute;
        top: -7px;
        right: 22px;
        width: 14px;
        height: 14px;
        background: #fff;
        border-left: 1px solid #e2e8f0;
        border-top: 1px solid #e2e8f0;
        transform: rotate(45deg);
    }

    .customer-action-menu.dropdown-menu-fixed.dropup-fixed::before {
        top: auto;
        bottom: -7px;
        border-left: 0;
        border-top: 0;
        border-right: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
    }

    .customer-action-menu.dropdown-menu-fixed .dropdown-item {
        position: relative;
        z-index: 1;
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        min-height: 44px !important;
    }

    .customer-action-menu.dropdown-menu-fixed .dropdown-divider {
        margin: 8px 0 !important;
    }

    /* FIX modal không tự tắt khi bấm trong form */
    .stop-buying-modal {
        z-index: 6000 !important;
    }

    .modal-backdrop {
        z-index: 5990 !important;
    }

    .stop-buying-modal .modal-dialog,
    .stop-buying-modal .modal-content,
    .stop-buying-modal .stop-buying-content,
    .stop-buying-modal .modal-header,
    .stop-buying-modal .modal-body,
    .stop-buying-modal .modal-footer {
        pointer-events: auto !important;
    }

    .stop-buying-modal .modal-dialog {
        position: relative !important;
        z-index: 6001 !important;
    }

    @media (max-width: 768px) {
        .customer-action-menu.dropdown-menu-fixed {
            min-width: 220px !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function moveStopBuyingModalsToBody() {
            document.querySelectorAll('.stop-buying-modal').forEach(function(modal) {
                modal.setAttribute('data-bs-backdrop', 'static');
                modal.setAttribute('data-bs-keyboard', 'false');

                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }
            });
        }

        function formatCustomerTableBadges() {
            document.querySelectorAll('.customer-table tbody tr').forEach(function(row) {
                const cells = row.querySelectorAll('td');

                if (cells.length < 10) {
                    return;
                }

                const buyStatusCell = cells[7];
                const roleCell = cells[8];

                const buyBadge = buyStatusCell.querySelector('.badge');
                if (buyBadge) {
                    const buyText = buyBadge.textContent.trim();

                    buyBadge.classList.remove('bg-light', 'text-dark');

                    if (buyText === 'Mua lại') {
                        buyBadge.classList.add('js-buy-again-badge');
                    } else if (buyText === 'Đã mua') {
                        buyBadge.classList.add('js-bought-badge');
                    } else if (buyText === 'Chưa mua') {
                        buyBadge.classList.add('js-not-bought-badge');
                    }
                }

                if (roleCell.dataset.formattedRole === '1') {
                    return;
                }

                const rawText = roleCell.textContent.replace(/\s+/g, ' ').trim();

                if (rawText === 'CTV') {
                    roleCell.innerHTML = '<span class="js-role-ctv-badge">CTV</span>';
                    roleCell.dataset.formattedRole = '1';
                    return;
                }

                if (rawText.includes('Ngừng mua')) {
                    const cleanRole = rawText.replace('Ngừng mua', '').trim() || 'Khách';
                    roleCell.innerHTML = '';

                    const roleText = document.createTextNode(cleanRole + ' ');
                    const stoppedBadge = document.createElement('span');

                    stoppedBadge.className = 'js-stopped-buying-badge';
                    stoppedBadge.textContent = 'Ngừng mua';

                    roleCell.appendChild(roleText);
                    roleCell.appendChild(stoppedBadge);
                    roleCell.dataset.formattedRole = '1';
                }
            });
        }

        function clearFixedDropdown(menu) {
            if (!menu) {
                return;
            }

            menu.classList.remove('dropdown-menu-fixed', 'dropup-fixed');
            menu.style.left = '';
            menu.style.top = '';
            menu.style.right = '';
            menu.style.bottom = '';
            menu.style.position = '';
            menu.style.transform = '';
        }

        function positionActionDropdown(dropdown) {
            const button = dropdown.querySelector('.customer-action-btn');
            const menu = dropdown.querySelector('.customer-action-menu');

            if (!button || !menu || !menu.classList.contains('show')) {
                return;
            }

            menu.classList.add('dropdown-menu-fixed');
            menu.classList.remove('dropup-fixed');

            const buttonRect = button.getBoundingClientRect();

            menu.style.position = 'fixed';
            menu.style.transform = 'none';
            menu.style.left = '0px';
            menu.style.top = '0px';

            const menuWidth = menu.offsetWidth || 240;
            const menuHeight = menu.offsetHeight || 180;
            const gap = 8;
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;

            let left = buttonRect.right - menuWidth;
            left = Math.max(8, Math.min(left, viewportWidth - menuWidth - 8));

            let top = buttonRect.bottom + gap;

            if (top + menuHeight > viewportHeight - 8) {
                top = buttonRect.top - menuHeight - gap;
                menu.classList.add('dropup-fixed');
            }

            top = Math.max(8, Math.min(top, viewportHeight - menuHeight - 8));

            menu.style.left = left + 'px';
            menu.style.top = top + 'px';
        }

        function bindActionDropdowns() {
            document.querySelectorAll('.customer-table .dropdown').forEach(function(dropdown) {
                if (dropdown.dataset.dropdownFixedBound === '1') {
                    return;
                }

                dropdown.dataset.dropdownFixedBound = '1';

                dropdown.addEventListener('shown.bs.dropdown', function() {
                    positionActionDropdown(dropdown);
                });

                dropdown.addEventListener('hide.bs.dropdown', function() {
                    const menu = dropdown.querySelector('.customer-action-menu');
                    clearFixedDropdown(menu);
                });

                const menu = dropdown.querySelector('.customer-action-menu');
                if (!menu) {
                    return;
                }

                menu.addEventListener('click', function(event) {
                    const modalBtn = event.target.closest('[data-bs-toggle="modal"]');

                    if (!modalBtn) {
                        return;
                    }

                    const targetSelector = modalBtn.getAttribute('data-bs-target');
                    const modal = document.querySelector(targetSelector);

                    if (!modal) {
                        return;
                    }

                    moveStopBuyingModalsToBody();

                    event.preventDefault();
                    event.stopPropagation();

                    const dropdownButton = dropdown.querySelector('.customer-action-btn');

                    if (dropdownButton && window.bootstrap) {
                        bootstrap.Dropdown.getOrCreateInstance(dropdownButton).hide();
                    }

                    if (window.bootstrap) {
                        const modalInstance = bootstrap.Modal.getOrCreateInstance(modal, {
                            backdrop: 'static',
                            keyboard: false
                        });

                        setTimeout(function() {
                            modalInstance.show();
                        }, 80);
                    }
                });
            });
        }

        moveStopBuyingModalsToBody();
        formatCustomerTableBadges();
        bindActionDropdowns();

        window.addEventListener('scroll', function() {
            document.querySelectorAll('.customer-table .dropdown').forEach(function(dropdown) {
                positionActionDropdown(dropdown);
            });
        }, true);

        window.addEventListener('resize', function() {
            document.querySelectorAll('.customer-table .dropdown').forEach(function(dropdown) {
                positionActionDropdown(dropdown);
            });
        });

        document.addEventListener('shown.bs.modal', function(event) {
            if (event.target && event.target.classList.contains('stop-buying-modal')) {
                event.target.style.display = 'flex';

                const firstSelect = event.target.querySelector('select[name="customer_stop_reason_id"]');
                if (firstSelect) {
                    firstSelect.style.width = '100%';
                    firstSelect.style.minWidth = '100%';
                }
            }
        });

        document.addEventListener('hidden.bs.modal', function(event) {
            if (event.target && event.target.classList.contains('stop-buying-modal')) {
                event.target.style.display = '';
            }
        });

        const tableCard = document.querySelector('.customer-table-card');
        if (tableCard) {
            const observer = new MutationObserver(function() {
                moveStopBuyingModalsToBody();
                formatCustomerTableBadges();
                bindActionDropdowns();
            });

            observer.observe(tableCard, {
                childList: true,
                subtree: true
            });
        }
    });
</script>
@endpush