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

    /* ===== Liquid Glass ===== */
    --commission-glass-bg: rgba(255, 255, 255, 0.62);
    --commission-glass-bg-strong: rgba(255, 255, 255, 0.78);
    --commission-glass-bg-soft: rgba(248, 251, 255, 0.56);
    --commission-glass-border: rgba(255, 255, 255, 0.72);
    --commission-glass-border-blue: rgba(207, 224, 255, 0.76);
    --commission-glass-highlight: rgba(255, 255, 255, 0.88);
    --commission-glass-shadow: 0 18px 48px rgba(30, 58, 138, 0.12);
    --commission-glass-shadow-hover: 0 24px 64px rgba(37, 99, 235, 0.17);
    --commission-glass-blur: 22px;
    --commission-glass-saturate: 155%;
    --commission-transition-fast: 180ms cubic-bezier(.2, .8, .2, 1);
    --commission-transition-smooth: 420ms cubic-bezier(.2, .8, .2, 1);
}

body {
    background: var(--commission-bg-main) !important;
}

.customer-index-page {
    min-height: calc(100vh - 70px);
    padding: 24px 24px 44px;
    background: var(--commission-gradient-page);
    border-radius: 24px;
    color: var(--commission-text);
}

.customer-index-page>.d-flex {
    position: relative;
    padding: 22px 24px;
    margin-bottom: 18px !important;
    border: 1px solid rgba(207, 224, 255, 0.82);
    border-radius: 24px;
    background:
        linear-gradient(135deg, rgba(255, 255, 255, 0.94), rgba(248, 251, 255, 0.88));
    box-shadow: var(--commission-shadow-md);
    overflow: hidden;
}

.customer-index-page>.d-flex::before {
    content: "";
    position: absolute;
    top: -80px;
    right: -70px;
    width: 220px;
    height: 220px;
    border-radius: 999px;
    background: rgba(37, 99, 235, 0.10);
    pointer-events: none;
}

.customer-index-page>.d-flex::after {
    content: "";
    position: absolute;
    bottom: -95px;
    left: 28%;
    width: 190px;
    height: 190px;
    border-radius: 999px;
    background: rgba(6, 182, 212, 0.08);
    pointer-events: none;
}

.customer-index-page h3 {
    position: relative;
    z-index: 1;
    display: block;
    margin: 0;
    color: var(--commission-title);
    font-size: 2rem;
    font-weight: 900;
    line-height: 1.2;
    letter-spacing: -0.035em;
}

.customer-index-page h3::after {
    content: "";
    display: block;
    width: 92px;
    height: 5px;
    margin-top: 12px;
    border-radius: 999px;
    background: var(--commission-gradient-icon);
    box-shadow: 0 8px 18px rgba(37, 99, 235, 0.18);
}

.customer-index-page p {
    position: relative;
    z-index: 1;
    display: block;
    max-width: 620px;
    margin-top: 12px;
    color: var(--commission-muted) !important;
    font-size: 1rem;
    font-weight: 600;
    line-height: 1.55;
}

.customer-index-page .btn-primary {
    position: relative;
    z-index: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-height: 46px;
    border: 0 !important;
    border-radius: 16px;
    background: var(--commission-gradient-icon) !important;
    color: var(--commission-white) !important;
    font-weight: 900;
    padding: 10px 18px;
    box-shadow: 0 14px 28px rgba(37, 99, 235, 0.22);
    transition: all 0.18s ease;
}

.customer-index-page .btn-primary:hover,
.customer-index-page .btn-primary:focus {
    color: var(--commission-white) !important;
    transform: translateY(-1px);
    filter: brightness(1.03);
    box-shadow: 0 18px 34px rgba(37, 99, 235, 0.28);
}

.customer-index-page .alert {
    border: 0;
    border-radius: 18px;
    padding: 14px 18px;
    font-weight: 700;
    box-shadow: var(--commission-shadow-sm);
}

.customer-index-page .alert-success {
    color: var(--commission-green);
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.13), rgba(248, 251, 255, 0.96));
    border: 1px solid rgba(34, 197, 94, 0.18);
}

.customer-index-page .alert-danger {
    color: var(--commission-red);
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.11), rgba(255, 247, 237, 0.96));
    border: 1px solid rgba(239, 68, 68, 0.18);
}

.customer-filter-card {
    position: relative;
    padding: 18px;
    margin-bottom: 18px !important;
    border: 1px solid rgba(207, 224, 255, 0.82);
    border-radius: 24px;
    background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(248, 251, 255, 0.96));
    box-shadow: var(--commission-shadow-md);
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
    height: 46px;
    border: 1px solid var(--commission-border);
    border-radius: 15px;
    background-color: var(--commission-bg-white);
    color: var(--commission-text);
    font-size: 0.98rem;
    font-weight: 700;
    box-shadow: inset 0 1px 0 rgba(15, 23, 42, 0.02);
    transition: all 0.16s ease;
}

.customer-control::placeholder {
    color: var(--commission-muted);
    opacity: 0.88;
}

.customer-control:hover {
    border-color: var(--commission-border-blue);
    background-color: var(--commission-bg-light);
}

.customer-control:focus {
    border-color: var(--commission-blue);
    background-color: var(--commission-bg-white);
    box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.13);
}

.customer-filter-btn {
    height: 46px;
    border: 0 !important;
    border-radius: 15px;
    background: var(--commission-gradient-icon) !important;
    color: var(--commission-white) !important;
    font-size: 0.98rem;
    font-weight: 900;
    box-shadow: 0 12px 24px rgba(37, 99, 235, 0.20) !important;
    transition: all 0.16s ease;
}

.customer-filter-btn:hover,
.customer-filter-btn:focus {
    color: var(--commission-white) !important;
    transform: translateY(-1px);
    filter: brightness(1.03);
    box-shadow: 0 16px 30px rgba(37, 99, 235, 0.25) !important;
}

.customer-reset-btn {
    height: 46px;
    border: 1px solid var(--commission-border) !important;
    border-radius: 15px;
    background: var(--commission-bg-white) !important;
    color: var(--commission-muted) !important;
    font-size: 1rem;
    font-weight: 900;
    box-shadow: var(--commission-shadow-sm) !important;
    transition: all 0.16s ease;
}

.customer-reset-btn:hover,
.customer-reset-btn:focus {
    color: var(--commission-blue) !important;
    border-color: var(--commission-border-blue) !important;
    background: var(--commission-bg-soft-blue) !important;
    transform: translateY(-1px);
}

.customer-table-card {
    position: relative;
    border: 1px solid rgba(207, 224, 255, 0.82);
    border-radius: 24px;
    background: var(--commission-bg-white);
    box-shadow: var(--commission-shadow-lg);
    overflow: visible;
}

.table-responsive {
    overflow: visible;
    border-radius: 24px 24px 0 0;
}

.customer-table {
    margin-bottom: 0;
    color: var(--commission-text);
    border-collapse: separate;
    border-spacing: 0;
    overflow: visible;
}

.customer-table thead th {
    padding: 15px 16px;
    border: 0;
    border-bottom: 1px solid var(--commission-border-blue);
    background: var(--commission-gradient-table-head);
    color: var(--commission-blue-dark);
    font-size: 0.94rem;
    font-weight: 900;
    letter-spacing: 0.01em;
    white-space: nowrap;
}

.customer-table thead th:first-child {
    border-top-left-radius: 24px;
}

.customer-table thead th:last-child {
    border-top-right-radius: 24px;
}

.customer-table tbody td {
    padding: 15px 16px;
    border-bottom: 1px solid var(--commission-border-soft);
    vertical-align: middle;
    color: var(--commission-text);
    background: rgba(255, 255, 255, 0.98);
    font-size: 0.96rem;
    font-weight: 650;
}

.customer-table tbody tr {
    transition: all 0.16s ease;
}

.customer-table tbody tr:hover td {
    background: var(--commission-bg-soft-card);
}

.customer-table tbody tr:last-child td {
    border-bottom: 0;
}

.customer-table tbody td:nth-child(2) {
    color: var(--commission-blue-dark);
    font-weight: 900;
}

.customer-table tbody td:nth-child(3) {
    color: var(--commission-title);
    font-weight: 900;
}

.customer-table tbody td:nth-child(3).text-danger,
.customer-table tbody td:nth-child(3) .text-danger {
    color: var(--commission-red) !important;
}

.customer-table tbody td:nth-child(4) {
    color: var(--commission-blue);
    font-weight: 850;
}

.customer-table tbody td:nth-child(6) .small {
    display: inline-block;
    margin-top: 3px;
    color: var(--commission-muted) !important;
    font-size: 0.84rem;
    font-weight: 700;
}

.customer-badge,
.badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 26px;
    padding: 6px 11px;
    border: 1px solid transparent !important;
    border-radius: 999px !important;
    font-size: 0.78rem;
    font-weight: 900;
    line-height: 1;
    white-space: nowrap;
}

.bg-primary-subtle,
.text-primary {
    background-color: rgba(37, 99, 235, 0.11) !important;
    color: var(--commission-blue) !important;
    border-color: rgba(37, 99, 235, 0.16) !important;
}

.bg-warning-subtle,
.text-warning-emphasis {
    background-color: rgba(250, 204, 21, 0.20) !important;
    color: #9a6700 !important;
    border-color: rgba(250, 204, 21, 0.30) !important;
}

.bg-light.text-dark {
    background-color: var(--commission-bg-soft-blue) !important;
    color: var(--commission-muted) !important;
    border-color: var(--commission-border) !important;
}

.js-role-ctv-badge,
.role-ctv-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 26px;
    padding: 6px 11px;
    border-radius: 999px;
    background: var(--commission-gradient-icon);
    color: var(--commission-white);
    font-size: 0.78rem;
    font-weight: 900;
    line-height: 1;
    white-space: nowrap;
    box-shadow: 0 8px 16px rgba(37, 99, 235, 0.16);
}

.js-stopped-buying-badge,
.stopped-buying-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 24px;
    margin-left: 8px;
    padding: 6px 10px;
    border-radius: 999px;
    background: rgba(239, 68, 68, 0.10);
    color: var(--commission-red);
    border: 1px solid rgba(239, 68, 68, 0.16);
    font-size: 0.76rem;
    font-weight: 900;
    line-height: 1;
    white-space: nowrap;
    vertical-align: middle;
}

.js-buy-again-badge,
.buy-again-badge {
    background: rgba(34, 197, 94, 0.13) !important;
    color: var(--commission-green) !important;
    border-color: rgba(34, 197, 94, 0.20) !important;
}

.js-bought-badge,
.bought-badge {
    background: rgba(37, 99, 235, 0.11) !important;
    color: var(--commission-blue) !important;
    border-color: rgba(37, 99, 235, 0.16) !important;
}

.js-not-bought-badge,
.not-bought-badge {
    background: var(--commission-bg-soft-blue) !important;
    color: var(--commission-muted) !important;
    border-color: var(--commission-border) !important;
}

.customer-action-btn {
    min-height: 38px;
    border: 1px solid var(--commission-border) !important;
    border-radius: 14px;
    background: var(--commission-bg-white) !important;
    color: var(--commission-title) !important;
    font-size: 0.9rem;
    font-weight: 900;
    padding: 7px 13px;
    box-shadow: 0 5px 12px rgba(15, 23, 42, 0.045) !important;
    transition: all 0.16s ease;
}

.customer-action-btn:hover,
.customer-action-btn:focus {
    color: var(--commission-white) !important;
    border-color: transparent !important;
    background: var(--commission-gradient-icon) !important;
    box-shadow: 0 10px 22px rgba(37, 99, 235, 0.22) !important;
}

.customer-action-menu {
    min-width: 238px;
    padding: 10px;
    border: 1px solid var(--commission-border);
    border-radius: 16px;
    background: var(--commission-bg-white);
    box-shadow: var(--commission-shadow-lg);
    z-index: 9999;
}

.customer-action-menu .dropdown-item {
    display: flex;
    align-items: center;
    min-height: 40px;
    border-radius: 11px;
    padding: 9px 12px;
    color: var(--commission-title);
    font-weight: 750;
    white-space: normal;
    transition: all 0.14s ease;
}

.customer-action-menu .dropdown-item:hover {
    color: var(--commission-blue);
    background: var(--commission-bg-soft-blue);
}

.customer-action-menu .dropdown-item.text-success {
    color: var(--commission-green) !important;
}

.customer-action-menu .dropdown-item.text-success:hover {
    background: rgba(34, 197, 94, 0.11);
}

.customer-action-menu .dropdown-item.text-danger {
    color: var(--commission-red) !important;
}

.customer-action-menu .dropdown-item.text-danger:hover {
    background: rgba(239, 68, 68, 0.10);
}

.customer-action-menu form {
    margin: 0;
}

.customer-action-menu .dropdown-divider {
    border-color: var(--commission-border-soft);
}

.stop-buying-content {
    border: 0;
    border-radius: 24px;
    overflow: hidden;
    background: var(--commission-bg-white);
    box-shadow: var(--commission-shadow-modal);
}

.stop-buying-header {
    border-bottom: 0;
    padding: 20px 22px;
    background: var(--commission-gradient-debt);
    color: var(--commission-white);
}

.stop-buying-header .modal-title {
    color: var(--commission-white);
    font-size: 1.18rem;
    font-weight: 900;
    letter-spacing: -0.02em;
}

.stop-buying-body {
    padding: 22px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
}

.stop-buying-body .form-label {
    color: var(--commission-title);
    font-weight: 850;
}

.stop-buying-body .form-select,
.stop-buying-body .form-control {
    border: 1px solid var(--commission-border);
    border-radius: 15px;
    color: var(--commission-text);
    font-weight: 650;
    box-shadow: none;
}

.stop-buying-body .form-select:focus,
.stop-buying-body .form-control:focus {
    border-color: var(--commission-blue);
    box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.13);
}

.stop-buying-footer {
    gap: 10px;
    padding: 17px 22px;
    border-top: 1px solid var(--commission-border-soft);
    background: var(--commission-bg-light);
}

.stop-buying-footer .btn {
    min-height: 42px;
    border-radius: 14px;
    font-weight: 900;
    padding: 8px 16px;
}

.stop-buying-footer .btn-light {
    border-color: var(--commission-border) !important;
    background: var(--commission-bg-white) !important;
    color: var(--commission-muted) !important;
}

.stop-buying-footer .btn-light:hover {
    background: var(--commission-bg-soft-blue) !important;
    color: var(--commission-title) !important;
}

.stop-buying-footer .btn-danger,
.stop-buying-modal .btn-danger {
    border: 0 !important;
    background: var(--commission-gradient-debt) !important;
    color: var(--commission-white) !important;
    box-shadow: 0 12px 24px rgba(239, 68, 68, 0.20);
}

.stop-buying-footer .btn-danger:hover,
.stop-buying-modal .btn-danger:hover {
    color: var(--commission-white) !important;
    filter: brightness(1.03);
    transform: translateY(-1px);
}

.customer-table-footer {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    padding: 14px 16px;
    color: var(--commission-muted);
    border-top: 1px solid var(--commission-border-soft);
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    border-radius: 0 0 24px 24px;
}

.customer-table-footer>div:first-child {
    display: none;
}

.customer-table-footer .pagination {
    margin-bottom: 0;
    gap: 6px;
}

.customer-table-footer .page-link {
    min-width: 34px;
    border: 1px solid var(--commission-border);
    border-radius: 10px;
    color: var(--commission-blue);
    background: var(--commission-bg-white);
    font-size: 0.88rem;
    font-weight: 850;
    padding: 7px 11px;
    box-shadow: var(--commission-shadow-sm);
}

.customer-table-footer .page-link:hover {
    color: var(--commission-white);
    border-color: transparent;
    background: var(--commission-gradient-icon);
}

.customer-table-footer .page-item.active .page-link {
    border-color: transparent;
    background: var(--commission-gradient-icon);
    color: var(--commission-white);
    box-shadow: 0 10px 22px rgba(37, 99, 235, 0.20);
}

.customer-table-footer .page-item.disabled .page-link {
    background: var(--commission-bg-soft-blue);
    color: var(--commission-muted);
    box-shadow: none;
}

.text-muted {
    color: var(--commission-muted) !important;
}

.small {
    font-size: 0.86rem;
}

.sidebar,
.admin-sidebar,
.main-sidebar,
aside {
    background: var(--commission-bg-light) !important;
    border-right: 1px solid var(--commission-border);
}

.sidebar .nav-link,
.admin-sidebar .nav-link,
.main-sidebar .nav-link,
aside .nav-link,
.sidebar a,
.admin-sidebar a,
.main-sidebar a,
aside a {
    color: var(--commission-muted);
    font-weight: 750;
    border-radius: 12px;
    transition: all 0.14s ease;
}

.sidebar .nav-link.active,
.admin-sidebar .nav-link.active,
.main-sidebar .nav-link.active,
aside .nav-link.active,
.sidebar a.active,
.admin-sidebar a.active,
.main-sidebar a.active,
aside a.active {
    background: var(--commission-bg-soft-blue) !important;
    color: var(--commission-blue) !important;
    border-left: 4px solid var(--commission-blue);
}

.sidebar .nav-link:hover,
.admin-sidebar .nav-link:hover,
.main-sidebar .nav-link:hover,
aside .nav-link:hover,
.sidebar a:hover,
.admin-sidebar a:hover,
.main-sidebar a:hover,
aside a:hover {
    background: var(--commission-bg-soft-blue);
    color: var(--commission-blue);
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
    background: var(--commission-bg-white) !important;
    border: 0 !important;
    border-radius: 24px !important;
    overflow: hidden !important;
    box-shadow: var(--commission-shadow-modal) !important;
    pointer-events: auto !important;
}

.stop-buying-modal .modal-header,
.stop-buying-modal .stop-buying-header {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    width: 100% !important;
    min-height: 66px !important;
    padding: 20px 22px !important;
    background: var(--commission-gradient-debt) !important;
    color: var(--commission-white) !important;
    border: 0 !important;
}

.stop-buying-modal .modal-title {
    max-width: calc(100% - 36px) !important;
    margin: 0 !important;
    color: var(--commission-white) !important;
    font-size: 1.18rem !important;
    font-weight: 900 !important;
    line-height: 1.25 !important;
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
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%) !important;
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
    color: var(--commission-title) !important;
    font-size: 1rem !important;
    font-weight: 850 !important;
    line-height: 1.3 !important;
}

.stop-buying-modal .form-select,
.stop-buying-modal .form-control,
.stop-buying-modal textarea {
    display: block !important;
    width: 100% !important;
    min-width: 100% !important;
    max-width: 100% !important;
    border-radius: 15px !important;
    border: 1px solid var(--commission-border) !important;
    background-color: var(--commission-bg-white) !important;
    color: var(--commission-text) !important;
    font-size: 1rem !important;
    font-weight: 650 !important;
    line-height: 1.5 !important;
    box-shadow: none !important;
}

.stop-buying-modal .form-select {
    height: 46px !important;
    min-height: 46px !important;
    padding: 8px 40px 8px 13px !important;
}

.stop-buying-modal textarea.form-control,
.stop-buying-modal textarea {
    min-height: 116px !important;
    resize: vertical !important;
    padding: 12px 13px !important;
}

.stop-buying-modal .form-select:focus,
.stop-buying-modal .form-control:focus,
.stop-buying-modal textarea:focus {
    border-color: var(--commission-blue) !important;
    box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.13) !important;
}

.stop-buying-modal .modal-footer,
.stop-buying-modal .stop-buying-footer {
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 10px !important;
    width: 100% !important;
    padding: 17px 22px !important;
    background: var(--commission-bg-light) !important;
    border-top: 1px solid var(--commission-border-soft) !important;
}

.stop-buying-modal .modal-footer .btn,
.stop-buying-modal .stop-buying-footer .btn {
    min-width: 96px !important;
    min-height: 42px !important;
    border-radius: 14px !important;
    font-size: 1rem !important;
    font-weight: 900 !important;
    padding: 8px 15px !important;
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
        font-size: 1.05rem !important;
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
        padding: 16px;
        border-radius: 20px;
    }

    .customer-index-page>.d-flex {
        padding: 18px;
        border-radius: 20px;
    }

    .customer-index-page h3 {
        font-size: 1.55rem;
    }

    .customer-index-page p {
        font-size: 0.92rem;
    }

    .customer-filter-card {
        padding: 14px;
        border-radius: 20px;
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
        height: 46px;
    }

    .customer-table thead th,
    .customer-table tbody td {
        font-size: 0.88rem;
        padding: 12px;
    }
}

@media (max-width: 576px) {
    .customer-index-page {
        padding: 12px;
        border-radius: 18px;
    }

    .customer-index-page>.d-flex {
        align-items: flex-start !important;
        padding: 16px;
        border-radius: 18px;
    }

    .customer-index-page h3 {
        font-size: 1.32rem;
    }

    .customer-index-page h3::after {
        width: 72px;
        height: 4px;
    }

    .customer-index-page p {
        font-size: 0.88rem;
        line-height: 1.5;
    }

    .customer-index-page .btn-primary {
        width: 100%;
        border-radius: 15px;
    }

    .customer-filter-card {
        padding: 12px;
        border-radius: 18px;
    }

    .customer-table-card {
        border-radius: 18px;
    }

    .table-responsive {
        border-radius: 18px 18px 0 0;
    }

    .customer-table {
        min-width: 1080px;
    }

    .customer-table thead th:first-child {
        border-top-left-radius: 18px;
    }

    .customer-table thead th:last-child {
        border-top-right-radius: 18px;
    }

    .customer-table-footer {
        justify-content: center;
        border-radius: 0 0 18px 18px;
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
    background: var(--commission-bg-white) !important;
    border: 1px solid var(--commission-border) !important;
    border-radius: 16px !important;
    box-shadow: var(--commission-shadow-lg) !important;
}

.customer-action-menu.dropdown-menu-fixed::before {
    content: "";
    position: absolute;
    top: -7px;
    right: 22px;
    width: 14px;
    height: 14px;
    background: var(--commission-bg-white);
    border-left: 1px solid var(--commission-border);
    border-top: 1px solid var(--commission-border);
    transform: rotate(45deg);
}

.customer-action-menu.dropdown-menu-fixed.dropup-fixed::before {
    top: auto;
    bottom: -7px;
    border-left: 0;
    border-top: 0;
    border-right: 1px solid var(--commission-border);
    border-bottom: 1px solid var(--commission-border);
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

/* ================================================================
       LIQUID GLASS – BẢN ỔN ĐỊNH HOVER + ANIMATION NÂNG CAO
       Chỉ tác động CSS, giữ nguyên HTML / Blade / JavaScript / logic
       ================================================================ */

@keyframes customerGlassPageEnter {
    0% {
        opacity: 0;
        transform: translate3d(0, 18px, 0) scale(0.992);
        filter: blur(6px);
    }

    100% {
        opacity: 1;
        transform: translate3d(0, 0, 0) scale(1);
        filter: blur(0);
    }
}

@keyframes customerGlassAmbientOne {

    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1) rotate(0deg);
    }

    35% {
        transform: translate3d(28px, 16px, 0) scale(1.08) rotate(5deg);
    }

    70% {
        transform: translate3d(-10px, 34px, 0) scale(0.96) rotate(-4deg);
    }
}

@keyframes customerGlassAmbientTwo {

    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }

    50% {
        transform: translate3d(-26px, -18px, 0) scale(1.1);
    }
}

@keyframes customerGlassHeaderGlow {

    0%,
    100% {
        box-shadow:
            var(--commission-glass-shadow),
            inset 0 1px 0 rgba(255, 255, 255, 0.92),
            inset 0 -1px 0 rgba(207, 224, 255, 0.26);
    }

    50% {
        box-shadow:
            0 24px 62px rgba(37, 99, 235, 0.16),
            inset 0 1px 0 rgba(255, 255, 255, 1),
            inset 0 -1px 0 rgba(96, 165, 250, 0.34);
    }
}

@keyframes customerGlassShimmer {
    0% {
        left: -55%;
        opacity: 0;
    }

    18% {
        opacity: 0.78;
    }

    62%,
    100% {
        left: 125%;
        opacity: 0;
    }
}

@keyframes customerGlassUnderline {

    0%,
    100% {
        background-position: 0% 50%;
        transform: scaleX(1);
    }

    50% {
        background-position: 100% 50%;
        transform: scaleX(1.08);
    }
}

@keyframes customerGlassButtonPulse {

    0%,
    100% {
        box-shadow: 0 13px 28px rgba(37, 99, 235, 0.20);
    }

    50% {
        box-shadow: 0 17px 36px rgba(37, 99, 235, 0.28);
    }
}

@keyframes customerGlassAlertEnter {
    0% {
        opacity: 0;
        transform: translate3d(0, -10px, 0);
    }

    100% {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

@keyframes customerGlassControlEnter {
    0% {
        opacity: 0;
        transform: translate3d(0, 10px, 0);
    }

    100% {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

@keyframes customerGlassRowEnter {
    0% {
        opacity: 0;
        transform: translate3d(0, 8px, 0);
    }

    100% {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

@keyframes customerGlassMenuReveal {
    0% {
        opacity: 0;
        clip-path: inset(0 0 100% 0 round 16px);
    }

    100% {
        opacity: 1;
        clip-path: inset(0 0 0 0 round 16px);
    }
}

@keyframes customerGlassModalReveal {
    0% {
        opacity: 0;
        transform: scale(0.965);
        filter: blur(5px);
    }

    100% {
        opacity: 1;
        transform: scale(1);
        filter: blur(0);
    }
}

@keyframes customerGlassModalShine {
    0% {
        transform: translateX(-180%) skewX(-18deg);
        opacity: 0;
    }

    16% {
        opacity: 0.55;
    }

    48%,
    100% {
        transform: translateX(390%) skewX(-18deg);
        opacity: 0;
    }
}

@keyframes customerGlassBadgeGlow {

    0%,
    100% {
        filter: saturate(100%);
    }

    50% {
        filter: saturate(125%) brightness(1.035);
    }
}

@keyframes customerGlassRefreshSpin {
    to {
        transform: rotate(360deg);
    }
}

@keyframes customerGlassActivePage {

    0%,
    100% {
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.18);
    }

    50% {
        box-shadow: 0 12px 30px rgba(37, 99, 235, 0.30);
    }
}

body {
    background:
        radial-gradient(circle at 12% 8%, rgba(37, 99, 235, 0.13), transparent 30%),
        radial-gradient(circle at 88% 4%, rgba(6, 182, 212, 0.10), transparent 28%),
        linear-gradient(135deg, var(--commission-bg-main), var(--commission-bg-light) 58%, var(--commission-bg-white)) !important;
    background-attachment: fixed !important;
}

.customer-index-page {
    isolation: isolate;
    position: relative;
    overflow: hidden;
    animation: customerGlassPageEnter 620ms cubic-bezier(.2, .8, .2, 1) both;
}

.customer-index-page::before,
.customer-index-page::after {
    content: "";
    position: absolute;
    z-index: -1;
    border-radius: 999px;
    pointer-events: none;
    filter: blur(12px);
    will-change: transform;
}

.customer-index-page::before {
    top: 7%;
    right: -118px;
    width: 330px;
    height: 330px;
    background:
        radial-gradient(circle at 34% 30%, rgba(255, 255, 255, 0.60), transparent 20%),
        radial-gradient(circle, rgba(37, 99, 235, 0.18), rgba(66, 184, 225, 0.07) 56%, transparent 73%);
    animation: customerGlassAmbientOne 15s ease-in-out infinite;
}

.customer-index-page::after {
    bottom: 5%;
    left: -138px;
    width: 360px;
    height: 360px;
    background:
        radial-gradient(circle at 36% 32%, rgba(255, 255, 255, 0.48), transparent 20%),
        radial-gradient(circle, rgba(6, 182, 212, 0.16), rgba(124, 58, 237, 0.05) 58%, transparent 74%);
    animation: customerGlassAmbientTwo 17s ease-in-out infinite;
}

.customer-index-page>.d-flex,
.customer-filter-card,
.customer-table-card {
    -webkit-backdrop-filter: blur(var(--commission-glass-blur)) saturate(var(--commission-glass-saturate));
    backdrop-filter: blur(var(--commission-glass-blur)) saturate(var(--commission-glass-saturate));
    border-color: var(--commission-glass-border-blue) !important;
    box-shadow:
        var(--commission-glass-shadow),
        inset 0 1px 0 var(--commission-glass-highlight),
        inset 0 -1px 0 rgba(207, 224, 255, 0.26) !important;
}

.customer-index-page>.d-flex {
    background:
        linear-gradient(135deg, rgba(255, 255, 255, 0.80), rgba(248, 251, 255, 0.60)),
        radial-gradient(circle at 92% 12%, rgba(66, 184, 225, 0.17), transparent 34%) !important;
    animation: customerGlassHeaderGlow 8s ease-in-out infinite;
    transition:
        border-color var(--commission-transition-smooth),
        box-shadow var(--commission-transition-smooth),
        background var(--commission-transition-smooth);
}

.customer-index-page>.d-flex::before,
.customer-index-page>.d-flex::after {
    will-change: transform;
}

.customer-index-page>.d-flex::before {
    background:
        radial-gradient(circle at 36% 34%, rgba(255, 255, 255, 0.68), transparent 22%),
        radial-gradient(circle, rgba(37, 99, 235, 0.18), rgba(6, 182, 212, 0.07) 56%, transparent 72%);
    filter: blur(2px);
    animation: customerGlassAmbientOne 12s ease-in-out infinite;
}

.customer-index-page>.d-flex::after {
    background:
        radial-gradient(circle at 34% 30%, rgba(255, 255, 255, 0.56), transparent 20%),
        radial-gradient(circle, rgba(6, 182, 212, 0.15), rgba(124, 58, 237, 0.05) 58%, transparent 74%);
    filter: blur(3px);
    animation: customerGlassAmbientTwo 14s ease-in-out infinite;
}

.customer-index-page h3 {
    text-shadow: 0 1px 0 rgba(255, 255, 255, 0.92);
}

.customer-index-page h3::after {
    background:
        linear-gradient(90deg, var(--commission-blue), var(--commission-cyan), var(--commission-purple), var(--commission-blue));
    background-size: 260% 100%;
    box-shadow:
        0 8px 20px rgba(37, 99, 235, 0.20),
        inset 0 1px 0 rgba(255, 255, 255, 0.72);
    transform-origin: left center;
    animation: customerGlassUnderline 4.5s ease-in-out infinite;
}

.customer-index-page .alert {
    -webkit-backdrop-filter: blur(18px) saturate(150%);
    backdrop-filter: blur(18px) saturate(150%);
    box-shadow:
        0 14px 34px rgba(15, 23, 42, 0.08),
        inset 0 1px 0 rgba(255, 255, 255, 0.86);
    animation: customerGlassAlertEnter 420ms cubic-bezier(.2, .8, .2, 1) both;
}

.customer-index-page .btn-primary,
.customer-filter-btn,
.customer-reset-btn,
.customer-action-btn,
.stop-buying-footer .btn {
    position: relative;
    isolation: isolate;
    overflow: hidden;
    -webkit-backdrop-filter: blur(12px) saturate(145%);
    backdrop-filter: blur(12px) saturate(145%);
    backface-visibility: hidden;
    transform: translateZ(0);
    transition:
        transform var(--commission-transition-fast),
        box-shadow var(--commission-transition-fast),
        filter var(--commission-transition-fast),
        border-color var(--commission-transition-fast),
        background var(--commission-transition-fast),
        color var(--commission-transition-fast);
}

.customer-index-page .btn-primary::before,
.customer-filter-btn::before,
.customer-reset-btn::before,
.customer-action-btn::before,
.stop-buying-footer .btn::before {
    content: "";
    position: absolute;
    z-index: 0;
    top: -85%;
    left: -55%;
    width: 38%;
    height: 280%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.70), transparent);
    transform: skewX(-18deg);
    opacity: 0;
    pointer-events: none;
}

.customer-index-page .btn-primary>*,
.customer-filter-btn>*,
.customer-reset-btn>*,
.customer-action-btn>*,
.stop-buying-footer .btn>* {
    position: relative;
    z-index: 1;
}

.customer-index-page .btn-primary,
.customer-filter-btn {
    animation: customerGlassButtonPulse 4.8s ease-in-out infinite;
}

.customer-index-page .btn-primary:focus-visible,
.customer-filter-btn:focus-visible,
.customer-reset-btn:focus-visible,
.customer-action-btn:focus-visible,
.stop-buying-footer .btn:focus-visible,
.customer-table-footer .page-link:focus-visible,
.customer-action-menu .dropdown-item:focus-visible {
    outline: 3px solid rgba(37, 99, 235, 0.24) !important;
    outline-offset: 3px;
}

.customer-index-page .btn-primary:active,
.customer-filter-btn:active,
.customer-reset-btn:active,
.customer-action-btn:active,
.stop-buying-footer .btn:active {
    transform: translateY(0) scale(0.975) !important;
    transition-duration: 70ms !important;
}

.customer-filter-card {
    background:
        linear-gradient(135deg, rgba(255, 255, 255, 0.74), rgba(248, 251, 255, 0.58)),
        radial-gradient(circle at 8% 0%, rgba(37, 99, 235, 0.08), transparent 32%) !important;
    overflow: hidden;
    transition:
        border-color var(--commission-transition-smooth),
        box-shadow var(--commission-transition-smooth),
        background var(--commission-transition-smooth);
}

.customer-filter-card::before {
    content: "";
    position: absolute;
    inset: 0 0 auto 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.98), transparent);
    pointer-events: none;
}

.customer-filter-card .row>[class*="col-"] {
    animation: customerGlassControlEnter 420ms cubic-bezier(.2, .8, .2, 1) both;
}

.customer-filter-card .row>[class*="col-"]:nth-child(1) {
    animation-delay: 70ms;
}

.customer-filter-card .row>[class*="col-"]:nth-child(2) {
    animation-delay: 110ms;
}

.customer-filter-card .row>[class*="col-"]:nth-child(3) {
    animation-delay: 150ms;
}

.customer-filter-card .row>[class*="col-"]:nth-child(4) {
    animation-delay: 190ms;
}

.customer-filter-card .row>[class*="col-"]:nth-child(5) {
    animation-delay: 230ms;
}

.customer-filter-card .row>[class*="col-"]:nth-child(6) {
    animation-delay: 270ms;
}

.customer-control,
.stop-buying-modal .form-select,
.stop-buying-modal .form-control,
.stop-buying-modal textarea {
    -webkit-backdrop-filter: blur(14px) saturate(145%);
    backdrop-filter: blur(14px) saturate(145%);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.88), rgba(248, 251, 255, 0.70)) !important;
    border-color: rgba(207, 224, 255, 0.82) !important;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.94),
        0 8px 18px rgba(37, 99, 235, 0.05) !important;
    transform: none !important;
    transition:
        border-color var(--commission-transition-fast),
        box-shadow var(--commission-transition-fast),
        background var(--commission-transition-fast),
        color var(--commission-transition-fast);
}

.customer-control:focus,
.stop-buying-modal .form-select:focus,
.stop-buying-modal .form-control:focus,
.stop-buying-modal textarea:focus {
    transform: none !important;
    border-color: var(--commission-blue) !important;
    background: rgba(255, 255, 255, 0.96) !important;
    box-shadow:
        0 0 0 0.22rem rgba(37, 99, 235, 0.13),
        0 12px 28px rgba(37, 99, 235, 0.10),
        inset 0 1px 0 rgba(255, 255, 255, 0.98) !important;
}

.customer-table-card {
    background:
        linear-gradient(135deg, rgba(255, 255, 255, 0.78), rgba(248, 251, 255, 0.60)) !important;
    overflow: visible;
    transition:
        border-color var(--commission-transition-smooth),
        box-shadow var(--commission-transition-smooth),
        background var(--commission-transition-smooth);
}

.customer-table thead th {
    background:
        linear-gradient(180deg, rgba(239, 246, 255, 0.90), rgba(219, 234, 254, 0.76)) !important;
    -webkit-backdrop-filter: blur(16px) saturate(150%);
    backdrop-filter: blur(16px) saturate(150%);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.90);
}

.customer-table tbody tr {
    animation: customerGlassRowEnter 430ms cubic-bezier(.2, .8, .2, 1) both;
}

.customer-table tbody tr:nth-child(1) {
    animation-delay: 35ms;
}

.customer-table tbody tr:nth-child(2) {
    animation-delay: 65ms;
}

.customer-table tbody tr:nth-child(3) {
    animation-delay: 95ms;
}

.customer-table tbody tr:nth-child(4) {
    animation-delay: 125ms;
}

.customer-table tbody tr:nth-child(5) {
    animation-delay: 155ms;
}

.customer-table tbody tr:nth-child(6) {
    animation-delay: 185ms;
}

.customer-table tbody tr:nth-child(7) {
    animation-delay: 215ms;
}

.customer-table tbody tr:nth-child(8) {
    animation-delay: 245ms;
}

.customer-table tbody tr:nth-child(9) {
    animation-delay: 275ms;
}

.customer-table tbody tr:nth-child(10) {
    animation-delay: 305ms;
}

.customer-table tbody td {
    position: relative;
    background: rgba(255, 255, 255, 0.70) !important;
    background-clip: padding-box !important;
    transition:
        background-color var(--commission-transition-fast),
        box-shadow var(--commission-transition-fast),
        color var(--commission-transition-fast);
}

.customer-table tbody td:first-child::before {
    content: "";
    position: absolute;
    top: 14%;
    bottom: 14%;
    left: 0;
    width: 3px;
    border-radius: 0 999px 999px 0;
    background: var(--commission-gradient-icon);
    transform: scaleY(0);
    transform-origin: center;
    transition: transform var(--commission-transition-fast);
    pointer-events: none;
}

.customer-badge,
.customer-table .badge,
.js-role-ctv-badge,
.role-ctv-badge,
.js-stopped-buying-badge,
.stopped-buying-badge,
.js-buy-again-badge,
.buy-again-badge,
.js-bought-badge,
.bought-badge,
.js-not-bought-badge,
.not-bought-badge {
    -webkit-backdrop-filter: blur(10px) saturate(150%);
    backdrop-filter: blur(10px) saturate(150%);
    box-shadow:
        0 7px 16px rgba(15, 23, 42, 0.055),
        inset 0 1px 0 rgba(255, 255, 255, 0.80);
    transition:
        transform var(--commission-transition-fast),
        box-shadow var(--commission-transition-fast),
        filter var(--commission-transition-fast);
}

.js-role-ctv-badge,
.role-ctv-badge {
    animation: customerGlassBadgeGlow 3.8s ease-in-out infinite;
}

.customer-action-menu,
.customer-action-menu.dropdown-menu-fixed {
    background:
        linear-gradient(145deg, rgba(255, 255, 255, 0.90), rgba(248, 251, 255, 0.76)) !important;
    -webkit-backdrop-filter: blur(26px) saturate(165%);
    backdrop-filter: blur(26px) saturate(165%);
    border-color: rgba(255, 255, 255, 0.84) !important;
    box-shadow:
        0 24px 70px rgba(15, 23, 42, 0.18),
        inset 0 1px 0 rgba(255, 255, 255, 0.96) !important;
    transform-origin: top right;
}

.customer-action-menu.show {
    animation: customerGlassMenuReveal 210ms cubic-bezier(.2, .8, .2, 1) both;
}

.customer-action-menu.dropdown-menu-fixed::before {
    background: rgba(255, 255, 255, 0.86) !important;
    -webkit-backdrop-filter: blur(18px);
    backdrop-filter: blur(18px);
}

.customer-action-menu .dropdown-item {
    position: relative;
    overflow: hidden;
    transition:
        color var(--commission-transition-fast),
        background var(--commission-transition-fast),
        box-shadow var(--commission-transition-fast),
        padding-left var(--commission-transition-fast);
}

.customer-action-menu .dropdown-item::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 8px;
    width: 4px;
    height: 4px;
    border-radius: 999px;
    background: currentColor;
    opacity: 0;
    transform: translateY(-50%) scale(0);
    transition:
        opacity var(--commission-transition-fast),
        transform var(--commission-transition-fast);
}

.modal-backdrop.show {
    opacity: 0.44 !important;
    -webkit-backdrop-filter: blur(8px) saturate(120%);
    backdrop-filter: blur(8px) saturate(120%);
    background:
        radial-gradient(circle at 50% 20%, rgba(37, 99, 235, 0.22), transparent 42%),
        rgba(15, 23, 42, 0.70) !important;
}

.stop-buying-modal.show .modal-content,
.stop-buying-modal.show .stop-buying-content {
    animation: customerGlassModalReveal 320ms cubic-bezier(.2, .8, .2, 1) both;
}

.stop-buying-modal .modal-content,
.stop-buying-modal .stop-buying-content {
    background:
        linear-gradient(145deg, rgba(255, 255, 255, 0.92), rgba(248, 251, 255, 0.78)) !important;
    -webkit-backdrop-filter: blur(30px) saturate(165%);
    backdrop-filter: blur(30px) saturate(165%);
    border: 1px solid rgba(255, 255, 255, 0.84) !important;
    box-shadow:
        var(--commission-shadow-modal),
        inset 0 1px 0 rgba(255, 255, 255, 0.97) !important;
    transform-origin: center;
}

.stop-buying-modal .modal-header,
.stop-buying-modal .stop-buying-header {
    position: relative;
    overflow: hidden;
    background:
        linear-gradient(110deg, rgba(255, 255, 255, 0.16), transparent 34%),
        var(--commission-gradient-debt) !important;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.36);
}

.stop-buying-modal .modal-header::after,
.stop-buying-modal .stop-buying-header::after {
    content: "";
    position: absolute;
    z-index: 0;
    top: -85%;
    left: -42%;
    width: 34%;
    height: 270%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.44), transparent);
    animation: customerGlassModalShine 5.2s ease-in-out infinite 900ms;
    pointer-events: none;
}

.stop-buying-modal .modal-header>*,
.stop-buying-modal .stop-buying-header>* {
    position: relative;
    z-index: 1;
}

.stop-buying-modal .modal-body,
.stop-buying-modal .stop-buying-body,
.stop-buying-modal .modal-footer,
.stop-buying-modal .stop-buying-footer {
    background: rgba(248, 251, 255, 0.66) !important;
    -webkit-backdrop-filter: blur(18px) saturate(145%);
    backdrop-filter: blur(18px) saturate(145%);
}

.customer-table-footer {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.78), rgba(248, 251, 255, 0.68)) !important;
    -webkit-backdrop-filter: blur(18px) saturate(150%);
    backdrop-filter: blur(18px) saturate(150%);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.90);
}

.customer-table-footer .page-link {
    -webkit-backdrop-filter: blur(10px) saturate(145%);
    backdrop-filter: blur(10px) saturate(145%);
    background: rgba(255, 255, 255, 0.76) !important;
    transition:
        transform var(--commission-transition-fast),
        box-shadow var(--commission-transition-fast),
        color var(--commission-transition-fast),
        background var(--commission-transition-fast),
        border-color var(--commission-transition-fast);
}

.customer-table-footer .page-item.active .page-link {
    background: var(--commission-gradient-icon) !important;
    animation: customerGlassActivePage 3.2s ease-in-out infinite;
}

.sidebar,
.admin-sidebar,
.main-sidebar,
aside {
    background: rgba(248, 251, 255, 0.76) !important;
    -webkit-backdrop-filter: blur(24px) saturate(155%);
    backdrop-filter: blur(24px) saturate(155%);
    box-shadow:
        12px 0 36px rgba(30, 58, 138, 0.06),
        inset -1px 0 0 rgba(255, 255, 255, 0.84);
}

.sidebar .nav-link,
.admin-sidebar .nav-link,
.main-sidebar .nav-link,
aside .nav-link,
.sidebar a,
.admin-sidebar a,
.main-sidebar a,
aside a {
    transition:
        transform var(--commission-transition-fast),
        background var(--commission-transition-fast),
        color var(--commission-transition-fast),
        box-shadow var(--commission-transition-fast);
}

.sidebar .nav-link.active,
.admin-sidebar .nav-link.active,
.main-sidebar .nav-link.active,
aside .nav-link.active,
.sidebar a.active,
.admin-sidebar a.active,
.main-sidebar a.active,
aside a.active {
    background: linear-gradient(135deg, rgba(219, 234, 254, 0.86), rgba(239, 246, 255, 0.74)) !important;
    box-shadow:
        0 10px 24px rgba(37, 99, 235, 0.09),
        inset 0 1px 0 rgba(255, 255, 255, 0.92);
}

.table-responsive {
    scrollbar-width: thin;
    scrollbar-color: var(--commission-blue-2) rgba(219, 234, 254, 0.48);
}

.table-responsive::-webkit-scrollbar {
    height: 10px;
}

.table-responsive::-webkit-scrollbar-track {
    border-radius: 999px;
    background: rgba(219, 234, 254, 0.48);
}

.table-responsive::-webkit-scrollbar-thumb {
    border: 2px solid rgba(255, 255, 255, 0.68);
    border-radius: 999px;
    background: linear-gradient(90deg, var(--commission-blue-1), var(--commission-cyan));
}

/* Hover chỉ áp dụng trên thiết bị có chuột thật, tránh lỗi hover dính trên điện thoại */
@media (hover: hover) and (pointer: fine) {

    .customer-index-page>.d-flex:hover,
    .customer-filter-card:hover,
    .customer-table-card:hover {
        border-color: rgba(96, 165, 250, 0.72) !important;
        box-shadow:
            var(--commission-glass-shadow-hover),
            inset 0 1px 0 rgba(255, 255, 255, 0.98),
            inset 0 -1px 0 rgba(96, 165, 250, 0.28) !important;
    }

    .customer-index-page .btn-primary:hover,
    .customer-filter-btn:hover {
        transform: translateY(-2px) scale(1.012);
        filter: saturate(112%) brightness(1.035);
        box-shadow: 0 18px 38px rgba(37, 99, 235, 0.30) !important;
    }

    .customer-reset-btn:hover {
        transform: translateY(-2px);
        color: var(--commission-blue) !important;
        border-color: rgba(96, 165, 250, 0.70) !important;
        background: rgba(239, 246, 255, 0.90) !important;
        box-shadow: 0 14px 28px rgba(37, 99, 235, 0.13) !important;
    }

    .customer-reset-btn:hover i {
        animation: customerGlassRefreshSpin 620ms cubic-bezier(.2, .8, .2, 1) both;
    }

    .customer-action-btn:hover,
    .customer-action-btn[aria-expanded="true"] {
        transform: translateY(-1px);
        color: var(--commission-white) !important;
        border-color: transparent !important;
        background: var(--commission-gradient-icon) !important;
        box-shadow: 0 13px 28px rgba(37, 99, 235, 0.24) !important;
    }

    .stop-buying-footer .btn:hover {
        transform: translateY(-1px);
    }

    .customer-index-page .btn-primary:hover::before,
    .customer-filter-btn:hover::before,
    .customer-reset-btn:hover::before,
    .customer-action-btn:hover::before,
    .stop-buying-footer .btn:hover::before {
        animation: customerGlassShimmer 900ms ease-out 1;
    }

    .customer-control:hover,
    .stop-buying-modal .form-select:hover,
    .stop-buying-modal .form-control:hover,
    .stop-buying-modal textarea:hover {
        transform: none !important;
        border-color: rgba(96, 165, 250, 0.68) !important;
        background: rgba(255, 255, 255, 0.92) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.96),
            0 11px 24px rgba(37, 99, 235, 0.08) !important;
    }

    .customer-table tbody tr:hover td {
        background: rgba(239, 246, 255, 0.90) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.92),
            inset 0 -1px 0 rgba(207, 224, 255, 0.48);
    }

    .customer-table tbody tr:hover td:first-child::before {
        transform: scaleY(1);
    }

    .customer-table tbody tr:hover td:nth-child(3),
    .customer-table tbody tr:hover td:nth-child(4) {
        color: var(--commission-blue-dark);
    }

    .customer-badge:hover,
    .customer-table .badge:hover,
    .js-role-ctv-badge:hover,
    .role-ctv-badge:hover,
    .js-stopped-buying-badge:hover,
    .stopped-buying-badge:hover,
    .js-buy-again-badge:hover,
    .buy-again-badge:hover,
    .js-bought-badge:hover,
    .bought-badge:hover,
    .js-not-bought-badge:hover,
    .not-bought-badge:hover {
        transform: translateY(-1px) scale(1.025);
        filter: saturate(118%) brightness(1.025);
        box-shadow:
            0 10px 22px rgba(37, 99, 235, 0.12),
            inset 0 1px 0 rgba(255, 255, 255, 0.94);
    }

    .customer-action-menu .dropdown-item:hover,
    .customer-action-menu .dropdown-item:focus {
        padding-left: 18px;
        background: rgba(239, 246, 255, 0.88) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.88);
    }

    .customer-action-menu .dropdown-item:hover::before,
    .customer-action-menu .dropdown-item:focus::before {
        opacity: 0.62;
        transform: translateY(-50%) scale(1);
    }

    .customer-table-footer .page-link:hover {
        transform: translateY(-2px);
        color: var(--commission-white) !important;
        border-color: transparent !important;
        background: var(--commission-gradient-icon) !important;
        box-shadow: 0 12px 26px rgba(37, 99, 235, 0.18);
    }

    .sidebar .nav-link:hover,
    .admin-sidebar .nav-link:hover,
    .main-sidebar .nav-link:hover,
    aside .nav-link:hover,
    .sidebar a:hover,
    .admin-sidebar a:hover,
    .main-sidebar a:hover,
    aside a:hover {
        transform: translateX(3px);
        background: rgba(239, 246, 255, 0.82) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.86);
    }
}

@supports not ((-webkit-backdrop-filter: blur(1px)) or (backdrop-filter: blur(1px))) {

    .customer-index-page>.d-flex,
    .customer-filter-card,
    .customer-table-card,
    .customer-action-menu,
    .customer-action-menu.dropdown-menu-fixed,
    .stop-buying-modal .modal-content,
    .stop-buying-modal .stop-buying-content,
    .sidebar,
    .admin-sidebar,
    .main-sidebar,
    aside {
        background-color: rgba(255, 255, 255, 0.97) !important;
    }
}

@media (max-width: 992px) {
    .customer-index-page::before {
        width: 230px;
        height: 230px;
        right: -100px;
    }

    .customer-index-page::after {
        width: 250px;
        height: 250px;
        left: -110px;
    }

    .customer-index-page>.d-flex,
    .customer-filter-card,
    .customer-table-card {
        -webkit-backdrop-filter: blur(18px) saturate(145%);
        backdrop-filter: blur(18px) saturate(145%);
    }
}

@media (max-width: 576px) {

    .customer-index-page::before,
    .customer-index-page::after {
        opacity: 0.68;
    }

    .customer-index-page .btn-primary,
    .customer-filter-btn {
        animation-duration: 6.5s;
    }

    .customer-table tbody td:first-child::before {
        width: 2px;
    }
}

@media (prefers-reduced-motion: reduce) {

    *,
    *::before,
    *::after {
        scroll-behavior: auto !important;
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }

    .customer-index-page,
    .customer-index-page::before,
    .customer-index-page::after,
    .customer-index-page>.d-flex,
    .customer-index-page h3::after,
    .customer-filter-card .row>[class*="col-"],
    .customer-table tbody tr,
    .customer-index-page .btn-primary,
    .customer-filter-btn,
    .js-role-ctv-badge,
    .role-ctv-badge,
    .customer-table-footer .page-item.active .page-link,
    .stop-buying-modal .modal-header::after,
    .stop-buying-modal .stop-buying-header::after {
        animation: none !important;
    }
}

/* ================================================================
       FIX DROPDOWN "THAO TÁC" + RESPONSIVE TOÀN BỘ GIAO DIỆN
       - Dropdown được JavaScript đưa ra trực tiếp dưới body nên không bị
         cắt, không bị che bởi bảng, backdrop-filter hoặc sidebar.
       - Bảng vẫn cuộn ngang độc lập trên màn hình nhỏ.
       ================================================================ */

.customer-index-page {
    width: 100%;
    max-width: 100%;
    overflow: hidden;
}

.customer-table-card {
    width: 100%;
    max-width: 100%;
    overflow: hidden !important;
    isolation: isolate;
}

.customer-table-card .table-responsive {
    width: 100%;
    max-width: 100%;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    -webkit-overflow-scrolling: touch;
    overscroll-behavior-inline: contain;
    scrollbar-width: thin;
    scrollbar-color: rgba(37, 99, 235, 0.42) rgba(219, 234, 254, 0.62);
}

.customer-table-card .table-responsive::-webkit-scrollbar {
    height: 9px;
}

.customer-table-card .table-responsive::-webkit-scrollbar-track {
    border-radius: 999px;
    background: rgba(219, 234, 254, 0.62);
}

.customer-table-card .table-responsive::-webkit-scrollbar-thumb {
    border: 2px solid rgba(248, 251, 255, 0.92);
    border-radius: 999px;
    background: var(--commission-gradient-icon);
}

.customer-table-card .customer-table {
    width: 100%;
    min-width: 1180px;
}

body>.customer-action-menu.dropdown-menu-fixed {
    position: fixed !important;
    inset: auto !important;
    display: block !important;
    width: max-content;
    min-width: 238px !important;
    max-width: min(280px, calc(100vw - 24px)) !important;
    max-height: min(420px, calc(100vh - 24px));
    margin: 0 !important;
    overflow-x: hidden;
    overflow-y: auto;
    overscroll-behavior: contain;
    transform: none !important;
    transform-origin: top right;
    visibility: visible !important;
    opacity: 1;
    pointer-events: auto !important;
    z-index: 2147483000 !important;
    isolation: isolate;
    contain: layout paint;
}

body>.customer-action-menu.dropdown-menu-fixed.dropup-fixed {
    transform-origin: bottom right;
}

body>.customer-action-menu.dropdown-menu-fixed .dropdown-item {
    width: 100%;
    min-width: 0;
    white-space: normal;
}

body>.customer-action-menu.dropdown-menu-fixed .dropdown-item i {
    flex: 0 0 auto;
}

body>.customer-action-menu.dropdown-menu-fixed form,
body>.customer-action-menu.dropdown-menu-fixed form .dropdown-item {
    width: 100%;
}

.customer-action-btn.show,
.customer-action-btn[aria-expanded="true"] {
    color: var(--commission-white) !important;
    border-color: transparent !important;
    background: var(--commission-gradient-icon) !important;
    box-shadow: 0 12px 28px rgba(37, 99, 235, 0.24) !important;
}

@media (max-width: 1200px) {
    .customer-index-page {
        padding-inline: 16px;
    }

    .customer-table-card .customer-table {
        min-width: 1120px;
    }
}

@media (max-width: 992px) {
    .customer-index-page {
        padding: 14px;
    }

    .customer-index-page>.d-flex {
        gap: 16px !important;
    }

    .customer-index-page>.d-flex>div:first-child {
        flex: 1 1 100%;
        min-width: 0;
    }

    .customer-index-page>.d-flex .btn-primary {
        flex: 0 0 auto;
    }

    .customer-filter-card .row>[class*="col-"] {
        min-width: 0;
    }

    .customer-table-card .customer-table {
        min-width: 1060px;
    }
}

@media (max-width: 768px) {
    .customer-index-page {
        padding: 12px;
        border-radius: 18px;
    }

    .customer-index-page>.d-flex {
        align-items: stretch !important;
        flex-direction: column;
        padding: 16px;
    }

    .customer-index-page>.d-flex .btn-primary {
        width: 100%;
    }

    .customer-filter-card {
        padding: 12px;
    }

    .customer-control,
    .customer-filter-btn,
    .customer-reset-btn {
        width: 100%;
        min-width: 0;
    }

    .customer-table-card .customer-table {
        min-width: 980px;
    }

    body>.customer-action-menu.dropdown-menu-fixed {
        width: min(252px, calc(100vw - 24px)) !important;
        min-width: min(220px, calc(100vw - 24px)) !important;
        max-width: calc(100vw - 24px) !important;
        border-radius: 15px !important;
    }

    .customer-table-footer {
        justify-content: center;
        padding: 12px;
    }

    .customer-table-footer .pagination {
        justify-content: center;
        flex-wrap: wrap;
    }
}

@media (max-width: 576px) {
    .customer-index-page {
        padding: 8px;
        border-radius: 16px;
    }

    .customer-index-page>.d-flex,
    .customer-filter-card,
    .customer-table-card {
        border-radius: 16px;
    }

    .customer-index-page h3 {
        overflow-wrap: anywhere;
    }

    .customer-index-page p {
        max-width: 100%;
        overflow-wrap: anywhere;
    }

    .customer-filter-card .row {
        --bs-gutter-x: 0.75rem;
        --bs-gutter-y: 0.75rem;
    }

    .customer-table-card .customer-table {
        min-width: 920px;
    }

    .customer-table thead th,
    .customer-table tbody td {
        padding: 11px 10px;
        font-size: 0.84rem;
    }

    body>.customer-action-menu.dropdown-menu-fixed {
        width: min(244px, calc(100vw - 16px)) !important;
        min-width: min(210px, calc(100vw - 16px)) !important;
        max-width: calc(100vw - 16px) !important;
        max-height: calc(100vh - 16px);
    }

    body>.customer-action-menu.dropdown-menu-fixed .dropdown-item {
        min-height: 42px !important;
        padding: 9px 11px !important;
        font-size: 0.88rem;
    }

    .stop-buying-modal.show {
        padding: 8px !important;
        padding-top: 58px !important;
    }

    .stop-buying-modal .modal-dialog {
        width: calc(100vw - 16px) !important;
        max-width: calc(100vw - 16px) !important;
    }

    .stop-buying-modal .modal-footer,
    .stop-buying-modal .stop-buying-footer {
        flex-wrap: wrap;
    }

    .stop-buying-modal .modal-footer .btn,
    .stop-buying-modal .stop-buying-footer .btn {
        flex: 1 1 130px;
    }
}


/* =========================================================
       FIX CUỐI: DROPDOWN "THAO TÁC" LUÔN NẰM TRÊN CÙNG
       ========================================================= */
#customer-action-portal {
    position: fixed !important;
    inset: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    overflow: visible !important;
    pointer-events: none !important;
    isolation: isolate !important;
    z-index: 2147483646 !important;
}

#customer-action-portal>.customer-action-menu.dropdown-menu-fixed {
    position: fixed !important;
    display: block !important;
    width: max-content !important;
    min-width: 238px !important;
    max-width: min(280px, calc(100vw - 24px)) !important;
    max-height: min(420px, calc(100vh - 24px)) !important;
    margin: 0 !important;
    padding: 10px !important;
    overflow-x: hidden !important;
    overflow-y: auto !important;
    visibility: visible !important;
    opacity: 1 !important;
    pointer-events: auto !important;
    transform: none !important;
    filter: none !important;
    isolation: isolate !important;
    contain: none !important;
    z-index: 2147483647 !important;
    border: 1px solid rgba(255, 255, 255, 0.72) !important;
    border-radius: 18px !important;
    background:
        linear-gradient(145deg, rgba(255, 255, 255, 0.97), rgba(239, 246, 255, 0.94)) !important;
    -webkit-backdrop-filter: blur(26px) saturate(175%) !important;
    backdrop-filter: blur(26px) saturate(175%) !important;
    box-shadow:
        0 28px 70px rgba(15, 23, 42, 0.28),
        0 10px 26px rgba(37, 99, 235, 0.18),
        inset 0 1px 0 rgba(255, 255, 255, 0.94) !important;
    animation: customerActionMenuTopIn 0.2s cubic-bezier(0.22, 1, 0.36, 1) both !important;
}

#customer-action-portal>.customer-action-menu.dropdown-menu-fixed::before {
    z-index: -1 !important;
}

#customer-action-portal>.customer-action-menu.dropdown-menu-fixed .dropdown-item,
#customer-action-portal>.customer-action-menu.dropdown-menu-fixed form,
#customer-action-portal>.customer-action-menu.dropdown-menu-fixed button,
#customer-action-portal>.customer-action-menu.dropdown-menu-fixed a {
    position: relative !important;
    z-index: 2 !important;
    pointer-events: auto !important;
    opacity: 1 !important;
    visibility: visible !important;
}

#customer-action-portal>.customer-action-menu.dropdown-menu-fixed .dropdown-item {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    width: 100% !important;
    min-height: 44px !important;
    color: var(--commission-title) !important;
    background: transparent !important;
}

#customer-action-portal>.customer-action-menu.dropdown-menu-fixed .dropdown-item:hover,
#customer-action-portal>.customer-action-menu.dropdown-menu-fixed .dropdown-item:focus {
    color: var(--commission-blue) !important;
    background: rgba(219, 234, 254, 0.78) !important;
    transform: translateX(2px) !important;
}

#customer-action-portal>.customer-action-menu.dropdown-menu-fixed .dropdown-item.text-success {
    color: var(--commission-green) !important;
}

#customer-action-portal>.customer-action-menu.dropdown-menu-fixed .dropdown-item.text-danger {
    color: var(--commission-red) !important;
}

@keyframes customerActionMenuTopIn {
    from {
        opacity: 0;
        transform: translateY(-7px) scale(0.975);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@media (max-width: 576px) {
    #customer-action-portal>.customer-action-menu.dropdown-menu-fixed {
        min-width: min(238px, calc(100vw - 16px)) !important;
        max-width: calc(100vw - 16px) !important;
        max-height: calc(100vh - 16px) !important;
        border-radius: 16px !important;
    }
}

@media (prefers-reduced-motion: reduce) {
    #customer-action-portal>.customer-action-menu.dropdown-menu-fixed {
        animation: none !important;
    }

    #customer-action-portal>.customer-action-menu.dropdown-menu-fixed .dropdown-item:hover,
    #customer-action-portal>.customer-action-menu.dropdown-menu-fixed .dropdown-item:focus {
        transform: none !important;
    }
}


/* =========================================================
       FIX ỔN ĐỊNH CUỐI CÙNG CHO MENU "THAO TÁC"
       - Không dùng Popper của Bootstrap để tránh menu chạy lên góc trái
       - Menu được đo kích thước trước, sau đó mới hiển thị
       - Luôn nằm trên cùng và trong phạm vi màn hình
       ========================================================= */
#customer-action-portal {
    position: fixed !important;
    inset: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    overflow: visible !important;
    pointer-events: none !important;
    z-index: 2147483646 !important;
}

#customer-action-portal>.customer-action-menu.customer-action-menu-portal {
    position: fixed !important;
    inset: auto !important;
    display: none !important;
    width: max-content !important;
    min-width: 238px !important;
    max-width: min(280px, calc(100vw - 24px)) !important;
    max-height: min(420px, calc(100vh - 24px)) !important;
    margin: 0 !important;
    padding: 10px !important;
    overflow-x: hidden !important;
    overflow-y: auto !important;
    visibility: hidden !important;
    opacity: 0 !important;
    pointer-events: auto !important;
    transform: none !important;
    translate: none !important;
    will-change: left, top, opacity !important;
    z-index: 2147483647 !important;
    border: 1px solid rgba(255, 255, 255, 0.76) !important;
    border-radius: 18px !important;
    background:
        linear-gradient(145deg, rgba(255, 255, 255, 0.98), rgba(239, 246, 255, 0.96)) !important;
    -webkit-backdrop-filter: blur(28px) saturate(175%) !important;
    backdrop-filter: blur(28px) saturate(175%) !important;
    box-shadow:
        0 28px 72px rgba(15, 23, 42, 0.28),
        0 10px 28px rgba(37, 99, 235, 0.18),
        inset 0 1px 0 rgba(255, 255, 255, 0.96) !important;
}

#customer-action-portal>.customer-action-menu.customer-action-menu-portal.is-measuring {
    display: block !important;
    left: -9999px !important;
    top: -9999px !important;
    visibility: hidden !important;
    opacity: 0 !important;
    animation: none !important;
}

#customer-action-portal>.customer-action-menu.customer-action-menu-portal.is-open {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    animation: customerStableMenuIn 0.2s cubic-bezier(0.22, 1, 0.36, 1) both !important;
}

#customer-action-portal>.customer-action-menu.customer-action-menu-portal.dropup-fixed.is-open {
    animation-name: customerStableMenuUpIn !important;
}

#customer-action-portal>.customer-action-menu.customer-action-menu-portal::before {
    display: none !important;
}

#customer-action-portal>.customer-action-menu.customer-action-menu-portal .dropdown-item {
    position: relative !important;
    z-index: 1 !important;
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    width: 100% !important;
    min-height: 44px !important;
    border: 0 !important;
    border-radius: 12px !important;
    color: var(--commission-title) !important;
    background: transparent !important;
    font-weight: 800 !important;
    pointer-events: auto !important;
    transition:
        background-color 0.18s ease,
        color 0.18s ease,
        padding-left 0.18s ease !important;
}

#customer-action-portal>.customer-action-menu.customer-action-menu-portal .dropdown-item:hover,
#customer-action-portal>.customer-action-menu.customer-action-menu-portal .dropdown-item:focus-visible {
    color: var(--commission-blue) !important;
    background: rgba(219, 234, 254, 0.82) !important;
    padding-left: 15px !important;
    transform: none !important;
}

#customer-action-portal>.customer-action-menu.customer-action-menu-portal .dropdown-item.text-success {
    color: var(--commission-green) !important;
}

#customer-action-portal>.customer-action-menu.customer-action-menu-portal .dropdown-item.text-success:hover,
#customer-action-portal>.customer-action-menu.customer-action-menu-portal .dropdown-item.text-success:focus-visible {
    background: rgba(34, 197, 94, 0.12) !important;
}

#customer-action-portal>.customer-action-menu.customer-action-menu-portal .dropdown-item.text-danger {
    color: var(--commission-red) !important;
}

#customer-action-portal>.customer-action-menu.customer-action-menu-portal .dropdown-item.text-danger:hover,
#customer-action-portal>.customer-action-menu.customer-action-menu-portal .dropdown-item.text-danger:focus-visible {
    background: rgba(239, 68, 68, 0.11) !important;
}

#customer-action-portal>.customer-action-menu.customer-action-menu-portal form {
    margin: 0 !important;
}

.customer-action-btn.is-menu-open {
    color: var(--commission-white) !important;
    border-color: transparent !important;
    background: var(--commission-gradient-icon) !important;
    box-shadow:
        0 0 0 4px rgba(37, 99, 235, 0.14),
        0 12px 26px rgba(37, 99, 235, 0.24) !important;
}

@keyframes customerStableMenuIn {
    from {
        opacity: 0;
        transform: translateY(-7px) scale(0.975);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes customerStableMenuUpIn {
    from {
        opacity: 0;
        transform: translateY(7px) scale(0.975);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@media (max-width: 576px) {
    #customer-action-portal>.customer-action-menu.customer-action-menu-portal {
        min-width: min(230px, calc(100vw - 16px)) !important;
        max-width: calc(100vw - 16px) !important;
        max-height: calc(100dvh - 16px) !important;
        border-radius: 16px !important;
    }
}

@media (prefers-reduced-motion: reduce) {

    #customer-action-portal>.customer-action-menu.customer-action-menu-portal.is-open,
    #customer-action-portal>.customer-action-menu.customer-action-menu-portal.dropup-fixed.is-open {
        animation: none !important;
    }

    #customer-action-portal>.customer-action-menu.customer-action-menu-portal .dropdown-item {
        transition: none !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let activeActionDropdown = null;
    let actionPositionFrame = null;

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

                buyBadge.classList.remove(
                    'bg-light',
                    'text-dark',
                    'js-buy-again-badge',
                    'js-bought-badge',
                    'js-not-bought-badge'
                );

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

    function getCustomerActionPortal() {
        let portal = document.getElementById('customer-action-portal');

        if (!portal) {
            portal = document.createElement('div');
            portal.id = 'customer-action-portal';
            portal.setAttribute('aria-hidden', 'true');
            document.body.appendChild(portal);
        }

        return portal;
    }

    function disposeBootstrapDropdown(button) {
        if (!button || !window.bootstrap || !bootstrap.Dropdown) {
            return;
        }

        const instance = bootstrap.Dropdown.getInstance(button);

        if (instance) {
            instance.dispose();
        }
    }

    function restoreActionMenu(dropdown) {
        if (!dropdown || !dropdown._customerActionMenu) {
            return;
        }

        const menu = dropdown._customerActionMenu;
        const placeholder = dropdown._customerActionPlaceholder;

        menu.classList.remove(
            'customer-action-menu-portal',
            'is-open',
            'is-measuring',
            'dropup-fixed',
            'show'
        );

        menu.removeAttribute('data-popper-placement');
        menu.style.removeProperty('left');
        menu.style.removeProperty('top');
        menu.style.removeProperty('right');
        menu.style.removeProperty('bottom');
        menu.style.removeProperty('position');
        menu.style.removeProperty('transform');
        menu.style.removeProperty('visibility');
        menu.style.removeProperty('opacity');

        if (placeholder && placeholder.parentNode) {
            placeholder.parentNode.insertBefore(menu, placeholder);
            placeholder.remove();
        }

        dropdown._customerActionPlaceholder = null;
    }

    function closeActionDropdown(dropdown) {
        const targetDropdown = dropdown || activeActionDropdown;

        if (!targetDropdown) {
            return;
        }

        const button = targetDropdown.querySelector('.customer-action-btn');

        if (button) {
            button.classList.remove('is-menu-open');
            button.setAttribute('aria-expanded', 'false');
        }

        restoreActionMenu(targetDropdown);

        if (activeActionDropdown === targetDropdown) {
            activeActionDropdown = null;
        }

        const portal = document.getElementById('customer-action-portal');

        if (portal && !portal.querySelector('.customer-action-menu-portal.is-open')) {
            portal.setAttribute('aria-hidden', 'true');
        }
    }

    function calculateActionMenuPosition(dropdown, menu) {
        const button = dropdown.querySelector('.customer-action-btn');

        if (!button || !menu) {
            return null;
        }

        const buttonRect = button.getBoundingClientRect();
        const viewportWidth = document.documentElement.clientWidth || window.innerWidth;
        const viewportHeight = document.documentElement.clientHeight || window.innerHeight;
        const edgeGap = viewportWidth <= 576 ? 8 : 12;
        const menuGap = 8;

        if (
            buttonRect.bottom < 0 ||
            buttonRect.top > viewportHeight ||
            buttonRect.right < 0 ||
            buttonRect.left > viewportWidth
        ) {
            return null;
        }

        const menuWidth = Math.min(menu.offsetWidth || 238, viewportWidth - (edgeGap * 2));
        const menuHeight = Math.min(menu.offsetHeight || 180, viewportHeight - (edgeGap * 2));
        const spaceBelow = viewportHeight - buttonRect.bottom - edgeGap;
        const spaceAbove = buttonRect.top - edgeGap;
        const openUp = menuHeight > spaceBelow && spaceAbove > spaceBelow;

        let left = buttonRect.right - menuWidth;
        left = Math.max(edgeGap, Math.min(left, viewportWidth - menuWidth - edgeGap));

        let top = openUp ?
            buttonRect.top - menuHeight - menuGap :
            buttonRect.bottom + menuGap;

        top = Math.max(edgeGap, Math.min(top, viewportHeight - menuHeight - edgeGap));

        return {
            left: Math.round(left),
            top: Math.round(top),
            openUp: openUp
        };
    }

    function positionOpenActionDropdown() {
        if (!activeActionDropdown || !activeActionDropdown._customerActionMenu) {
            return;
        }

        const menu = activeActionDropdown._customerActionMenu;
        const position = calculateActionMenuPosition(activeActionDropdown, menu);

        if (!position) {
            closeActionDropdown(activeActionDropdown);
            return;
        }

        menu.classList.toggle('dropup-fixed', position.openUp);
        menu.style.setProperty('left', position.left + 'px', 'important');
        menu.style.setProperty('top', position.top + 'px', 'important');
        menu.style.setProperty('right', 'auto', 'important');
        menu.style.setProperty('bottom', 'auto', 'important');
    }

    function scheduleActionMenuPosition() {
        if (actionPositionFrame !== null) {
            return;
        }

        actionPositionFrame = window.requestAnimationFrame(function() {
            actionPositionFrame = null;
            positionOpenActionDropdown();
        });
    }

    function openActionDropdown(dropdown) {
        const button = dropdown.querySelector('.customer-action-btn');
        const menu = dropdown.querySelector('.customer-action-menu') || dropdown._customerActionMenu;

        if (!button || !menu) {
            return;
        }

        if (activeActionDropdown && activeActionDropdown !== dropdown) {
            closeActionDropdown(activeActionDropdown);
        }

        if (activeActionDropdown === dropdown) {
            closeActionDropdown(dropdown);
            return;
        }

        disposeBootstrapDropdown(button);

        dropdown._customerActionMenu = menu;

        if (!dropdown._customerActionPlaceholder && menu.parentNode) {
            const placeholder = document.createComment('customer-action-menu-placeholder');
            menu.parentNode.insertBefore(placeholder, menu);
            dropdown._customerActionPlaceholder = placeholder;
        }

        const portal = getCustomerActionPortal();
        portal.appendChild(menu);
        portal.setAttribute('aria-hidden', 'false');

        menu.classList.remove('show', 'dropdown-menu-fixed', 'dropup-fixed', 'is-open');
        menu.classList.add('customer-action-menu-portal', 'is-measuring');
        menu.removeAttribute('data-popper-placement');

        menu.style.removeProperty('inset');
        menu.style.setProperty('left', '-9999px', 'important');
        menu.style.setProperty('top', '-9999px', 'important');
        menu.style.setProperty('right', 'auto', 'important');
        menu.style.setProperty('bottom', 'auto', 'important');
        menu.style.setProperty('transform', 'none', 'important');

        activeActionDropdown = dropdown;

        const position = calculateActionMenuPosition(dropdown, menu);

        if (!position) {
            closeActionDropdown(dropdown);
            return;
        }

        menu.classList.toggle('dropup-fixed', position.openUp);
        menu.style.setProperty('left', position.left + 'px', 'important');
        menu.style.setProperty('top', position.top + 'px', 'important');
        menu.classList.remove('is-measuring');
        menu.classList.add('is-open');

        button.classList.add('is-menu-open');
        button.setAttribute('aria-expanded', 'true');
    }

    function showStopBuyingModal(modalButton) {
        const targetSelector = modalButton.getAttribute('data-bs-target');

        if (!targetSelector) {
            return;
        }

        const modal = document.querySelector(targetSelector);

        if (!modal) {
            return;
        }

        closeActionDropdown();
        moveStopBuyingModalsToBody();

        if (window.bootstrap && bootstrap.Modal) {
            const modalInstance = bootstrap.Modal.getOrCreateInstance(modal, {
                backdrop: 'static',
                keyboard: false
            });

            window.setTimeout(function() {
                modalInstance.show();
            }, 30);
        }
    }

    function bindActionDropdowns() {
        document.querySelectorAll('.customer-table .dropdown').forEach(function(dropdown) {
            if (dropdown.dataset.customerManualDropdownBound === '1') {
                return;
            }

            const button = dropdown.querySelector('.customer-action-btn');
            const menu = dropdown.querySelector('.customer-action-menu');

            if (!button || !menu) {
                return;
            }

            dropdown.dataset.customerManualDropdownBound = '1';
            dropdown._customerActionMenu = menu;

            disposeBootstrapDropdown(button);
            button.removeAttribute('data-bs-toggle');
            button.setAttribute('data-customer-action-toggle', 'true');
            button.setAttribute('aria-expanded', 'false');

            button.addEventListener('click', function(event) {
                event.preventDefault();
                event.stopPropagation();
                openActionDropdown(dropdown);
            });

            menu.addEventListener('click', function(event) {
                const modalButton = event.target.closest('[data-bs-toggle="modal"]');

                if (modalButton) {
                    event.preventDefault();
                    event.stopPropagation();
                    showStopBuyingModal(modalButton);
                    return;
                }

                const actionableItem = event.target.closest('a, button[type="submit"]');

                if (actionableItem && !actionableItem.closest('[data-bs-toggle="modal"]')) {
                    window.setTimeout(function() {
                        closeActionDropdown(dropdown);
                    }, 0);
                }
            });
        });
    }

    document.addEventListener('click', function(event) {
        if (!activeActionDropdown) {
            return;
        }

        const menu = activeActionDropdown._customerActionMenu;
        const button = activeActionDropdown.querySelector('.customer-action-btn');

        if (
            (menu && menu.contains(event.target)) ||
            (button && button.contains(event.target))
        ) {
            return;
        }

        closeActionDropdown(activeActionDropdown);
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeActionDropdown();
        }
    });

    window.addEventListener('scroll', scheduleActionMenuPosition, true);
    window.addEventListener('resize', scheduleActionMenuPosition);

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

    getCustomerActionPortal();
    moveStopBuyingModalsToBody();
    formatCustomerTableBadges();
    bindActionDropdowns();

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