@extends('admin.auth.dashboardAmin')

@section('title', 'Danh mục tùy chọn')

@section('admin_content')
<div class="container-fluid customer-option-page">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.customers.index') }}">Khách hàng</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Danh mục tùy chọn</li>
        </ol>
    </nav>

    <div class="mb-4">
        <h3 class="mb-1 option-page-title">Quản lý danh mục tùy chọn (Dropdown)</h3>
        <p class="option-page-desc mb-0">
            Thêm, sửa, xóa các tùy chọn hiển thị trong biểu mẫu thêm/sửa khách hàng.
            Không có tìm kiếm, mỗi tab là một danh sách quản lý riêng.
        </p>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
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

    <div class="option-card">

        <ul class="nav option-tabs" id="customerOptionTabs" role="tablist">
            @foreach($tabs as $key => $tab)
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === $key ? 'active' : '' }}" id="tab-{{ $key }}"
                    data-bs-toggle="tab" data-bs-target="#pane-{{ $key }}" type="button" role="tab"
                    aria-controls="pane-{{ $key }}" aria-selected="{{ $activeTab === $key ? 'true' : 'false' }}"
                    data-tab-key="{{ $key }}">
                    {{ $tab['title'] }}
                </button>
            </li>
            @endforeach
        </ul>

        <div class="tab-content option-tab-content" id="customerOptionTabsContent">

            @foreach($tabs as $key => $tab)
            @php
            $storeUrl = \Illuminate\Support\Facades\URL::signedRoute('admin.customer-options.store', [
            'type' => $key,
            ]);
            @endphp

            <div class="tab-pane fade {{ $activeTab === $key ? 'show active' : '' }}" id="pane-{{ $key }}"
                role="tabpanel" aria-labelledby="tab-{{ $key }}">

                <div class="d-flex justify-content-end mb-3">
                    <button class="btn option-add-btn js-open-option-modal" type="button" data-mode="create"
                        data-type="{{ $key }}" data-title="{{ $tab['modal_add_title'] }}"
                        data-label="{{ $tab['field_label'] }}" data-store-url="{{ $storeUrl }}"
                        data-is-note="{{ $tab['is_note'] ? '1' : '0' }}">
                        <i class="fa-solid fa-plus me-1"></i>
                        {{ $tab['button'] }}
                    </button>
                </div>

                <div class="table-responsive option-table-wrap">
                    <table class="table option-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 70px;">STT</th>
                                <th>{{ $tab['table_label'] }}</th>
                                <th class="text-end" style="width: 170px;">Thao tác</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($tab['items'] as $item)
                            @php
                            $value = $tab['is_note']
                            ? $item->content
                            : ($tab['is_product'] ? $item->product_name : $item->name);

                            $updateUrl = \Illuminate\Support\Facades\URL::signedRoute('admin.customer-options.update', [
                            'type' => $key,
                            'id' => $item->id,
                            ]);

                            $deleteUrl = \Illuminate\Support\Facades\URL::signedRoute('admin.customer-options.destroy',
                            [
                            'type' => $key,
                            'id' => $item->id,
                            ]);
                            @endphp

                            <tr>
                                <td data-label="STT">
                                    {{ $loop->iteration }}
                                </td>

                                <td data-label="{{ $tab['table_label'] }}" class="option-name-cell">
                                    {{ $value }}
                                </td>

                                <td data-label="Thao tác" class="text-end">
                                    <div class="option-actions">
                                        <button class="option-icon-btn option-edit-btn js-open-option-modal"
                                            type="button" title="Sửa" data-mode="edit" data-type="{{ $key }}"
                                            data-title="{{ $tab['modal_edit_title'] }}"
                                            data-label="{{ $tab['field_label'] }}" data-update-url="{{ $updateUrl }}"
                                            data-value="{{ e($value) }}"
                                            data-is-note="{{ $tab['is_note'] ? '1' : '0' }}">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>

                                        <form method="POST" action="{{ $deleteUrl }}" class="d-inline"
                                            onsubmit="return confirm('Bạn có chắc muốn xóa dòng này không?');">
                                            @csrf
                                            @method('DELETE')

                                            <button class="option-icon-btn option-delete-btn" type="submit" title="Xóa">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    Chưa có dữ liệu.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
            @endforeach

        </div>
    </div>
</div>

<div class="modal fade" id="optionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered option-modal-dialog">
        <div class="modal-content option-modal-content">

            <form method="POST" id="optionForm" autocomplete="off">
                @csrf
                <input type="hidden" name="_method" id="optionFormMethod" value="POST">

                <div class="modal-header option-modal-header">
                    <h5 class="modal-title" id="optionModalTitle">Thêm mới</h5>

                    <button type="button" class="btn-close option-modal-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body option-modal-body">
                    <label class="form-label option-modal-label">
                        <span id="optionFieldLabel">Tên</span>
                        <span class="text-danger">*</span>
                    </label>

                    <input class="form-control option-modal-input" type="text" name="value" id="optionValueInput"
                        maxlength="255">

                    <textarea class="form-control option-modal-textarea d-none" name="value" id="optionValueTextarea"
                        rows="5" maxlength="5000"></textarea>
                </div>

                <div class="modal-footer option-modal-footer">
                    <button type="button" class="btn btn-light option-cancel-btn" data-bs-dismiss="modal">
                        Hủy
                    </button>

                    <button type="submit" class="btn option-save-btn">
                        <i class="fa-solid fa-floppy-disk me-1"></i>
                        Lưu lại
                    </button>
                </div>
            </form>

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
    }

    .customer-option-page {
        min-height: calc(100vh - 80px);
        padding: 24px 24px 40px;
        color: var(--commission-text);
        background: var(--commission-gradient-page);
        border-radius: 24px;
    }

    .customer-option-page .breadcrumb {
        display: inline-flex;
        align-items: center;
        padding: 10px 15px;
        margin-bottom: 18px;
        background: rgba(255, 255, 255, 0.78);
        border: 1px solid var(--commission-border);
        border-radius: 999px;
        box-shadow: var(--commission-shadow-sm);
    }

    .customer-option-page .breadcrumb-item,
    .customer-option-page .breadcrumb-item.active {
        font-size: 13px;
        font-weight: 700;
        color: var(--commission-muted);
    }

    .customer-option-page .breadcrumb-item a {
        color: var(--commission-blue);
        text-decoration: none;
    }

    .customer-option-page .breadcrumb-item a:hover {
        color: var(--commission-blue-dark);
    }

    .option-page-title {
        position: relative;
        display: flex;
        align-items: center;
        gap: 13px;
        font-size: 2rem;
        font-weight: 900;
        color: var(--commission-title);
        letter-spacing: -0.04em;
    }

    .option-page-title::before {
        content: "";
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        background: var(--commission-gradient-icon);
        border-radius: 16px;
        box-shadow: var(--commission-shadow-md);
    }

    .option-page-desc {
        max-width: 760px;
        margin-top: 8px;
        color: var(--commission-muted);
        font-size: 1rem;
        line-height: 1.6;
    }

    .customer-option-page .alert {
        border-radius: 18px;
        border: 1px solid transparent;
        box-shadow: var(--commission-shadow-sm);
    }

    .customer-option-page .alert-success {
        color: var(--commission-green);
        background: rgba(34, 197, 94, 0.10);
        border-color: rgba(34, 197, 94, 0.22);
    }

    .customer-option-page .alert-danger {
        color: var(--commission-red-1);
        background: var(--commission-danger-bg);
        border-color: rgba(239, 68, 68, 0.22);
    }

    .option-card {
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid var(--commission-border-soft);
        border-radius: 24px;
        padding: 22px;
        box-shadow: var(--commission-shadow-md);
        backdrop-filter: blur(10px);
    }

    .option-tabs {
        border-bottom: 1px solid var(--commission-border);
        gap: 8px;
        overflow-x: auto;
        overflow-y: hidden;
        flex-wrap: nowrap;
        scrollbar-width: thin;
        padding-bottom: 1px;
    }

    .option-tabs::-webkit-scrollbar {
        height: 6px;
    }

    .option-tabs::-webkit-scrollbar-thumb {
        background: var(--commission-border-blue);
        border-radius: 999px;
    }

    .option-tabs .nav-item {
        flex: 0 0 auto;
    }

    .option-tabs .nav-link {
        position: relative;
        border: 1px solid transparent;
        border-bottom: 0;
        color: var(--commission-muted);
        font-weight: 800;
        font-size: 1rem;
        padding: 13px 20px;
        border-radius: 16px 16px 0 0;
        background: transparent;
        white-space: nowrap;
        transition: all 0.18s ease;
    }

    .option-tabs .nav-link:hover {
        color: var(--commission-blue);
        background: var(--commission-bg-soft-blue);
    }

    .option-tabs .nav-link.active {
        color: var(--commission-blue-dark);
        background: var(--commission-bg-white);
        border-color: var(--commission-border-blue);
        box-shadow:
            0 -4px 0 var(--commission-blue) inset,
            var(--commission-shadow-sm);
    }

    .option-tab-content {
        padding-top: 22px;
    }

    .option-add-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 42px;
        background: var(--commission-gradient-total);
        border: 0;
        color: var(--commission-white);
        border-radius: 15px;
        padding: 10px 20px;
        font-weight: 800;
        box-shadow: var(--commission-shadow-md);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .option-add-btn:hover,
    .option-add-btn:focus {
        transform: translateY(-1px);
        background: var(--commission-gradient-total);
        color: var(--commission-white);
        box-shadow: var(--commission-shadow-lg);
    }

    .option-table-wrap {
        border: 1px solid var(--commission-border);
        border-radius: 20px;
        overflow: hidden;
        background: var(--commission-bg-white);
        box-shadow: var(--commission-shadow-sm);
    }

    .option-table {
        --bs-table-bg: transparent;
    }

    .option-table thead th {
        background: var(--commission-gradient-table-head);
        color: var(--commission-blue-dark);
        font-weight: 900;
        font-size: 0.96rem;
        border-bottom: 1px solid var(--commission-border-blue);
        padding: 15px 16px;
        white-space: nowrap;
    }

    .option-table tbody td {
        padding: 15px 16px;
        border-bottom: 1px solid var(--commission-border-soft);
        font-size: 0.98rem;
        color: var(--commission-text);
        background: var(--commission-bg-white);
        vertical-align: middle;
    }

    .option-table tbody tr:hover td {
        background: var(--commission-bg-soft-blue);
    }

    .option-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .option-name-cell {
        font-weight: 700;
        color: var(--commission-title) !important;
    }

    .option-actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
    }

    .option-icon-btn {
        width: 38px;
        height: 38px;
        border-radius: 14px;
        border: 1px solid var(--commission-border-blue);
        background: var(--commission-bg-white);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.16s ease;
        box-shadow: var(--commission-shadow-sm);
    }

    .option-icon-btn:hover {
        transform: translateY(-1px);
    }

    .option-edit-btn {
        color: var(--commission-blue);
    }

    .option-edit-btn:hover {
        color: var(--commission-white);
        background: var(--commission-gradient-total);
        border-color: transparent;
        box-shadow: var(--commission-shadow-md);
    }

    .option-delete-btn {
        color: var(--commission-red);
    }

    .option-delete-btn:hover {
        color: var(--commission-white);
        background: var(--commission-gradient-debt);
        border-color: transparent;
        box-shadow: var(--commission-shadow-md);
    }

    .option-modal-dialog {
        max-width: 625px;
    }

    .option-modal-content {
        border: 1px solid var(--commission-border-blue);
        border-radius: 22px;
        overflow: hidden;
        box-shadow: var(--commission-shadow-modal);
    }

    .option-modal-header {
        padding: 20px 22px;
        background: var(--commission-gradient-modal-header);
        border-bottom: 0;
    }

    .option-modal-header .modal-title {
        font-size: 1.35rem;
        font-weight: 900;
        color: var(--commission-white);
    }

    .option-modal-close {
        transform: scale(1.08);
        filter: invert(1) grayscale(100%) brightness(200%);
        opacity: 0.9;
    }

    .option-modal-body {
        padding: 24px 22px;
        background: var(--commission-bg-light);
    }

    .option-modal-label {
        font-size: 1rem;
        font-weight: 800;
        color: var(--commission-title);
        margin-bottom: 10px;
    }

    .option-modal-input {
        height: 52px;
        border-radius: 15px;
        border: 1px solid var(--commission-border-blue);
        background: var(--commission-bg-white);
        color: var(--commission-text);
        font-size: 1rem;
        box-shadow: none;
    }

    .option-modal-textarea {
        border-radius: 15px;
        border: 1px solid var(--commission-border-blue);
        background: var(--commission-bg-white);
        color: var(--commission-text);
        font-size: 1rem;
        min-height: 138px;
        resize: vertical;
        box-shadow: none;
    }

    .option-modal-input:focus,
    .option-modal-textarea:focus {
        border-color: var(--commission-blue);
        box-shadow: 0 0 0 0.22rem rgba(37, 99, 235, 0.14);
    }

    .option-modal-footer {
        padding: 18px 22px;
        background: var(--commission-bg-white);
        border-top: 1px solid var(--commission-border);
    }

    .option-cancel-btn {
        border: 1px solid var(--commission-border-blue);
        border-radius: 14px;
        padding: 9px 18px;
        font-weight: 800;
        color: var(--commission-title);
        background: var(--commission-bg-white);
    }

    .option-cancel-btn:hover {
        color: var(--commission-blue-dark);
        background: var(--commission-bg-soft-blue);
        border-color: var(--commission-blue);
    }

    .option-save-btn {
        background: var(--commission-gradient-total);
        border: 0;
        color: var(--commission-white);
        border-radius: 14px;
        padding: 9px 18px;
        font-weight: 800;
        box-shadow: var(--commission-shadow-md);
    }

    .option-save-btn:hover,
    .option-save-btn:focus {
        background: var(--commission-gradient-total);
        color: var(--commission-white);
        box-shadow: var(--commission-shadow-lg);
    }

    @media (max-width: 991.98px) {
        .customer-option-page {
            padding: 18px 16px 34px;
        }

        .option-page-title {
            font-size: 1.55rem;
        }

        .option-card {
            padding: 16px;
        }

        .option-tabs .nav-link {
            font-size: 0.95rem;
            padding: 11px 14px;
        }
    }

    @media (max-width: 767.98px) {
        .customer-option-page {
            padding: 14px 12px 30px;
            border-radius: 18px;
        }

        .option-page-title {
            align-items: flex-start;
            font-size: 1.35rem;
            line-height: 1.3;
        }

        .option-page-title::before {
            width: 38px;
            height: 38px;
            flex-basis: 38px;
            border-radius: 14px;
        }

        .option-page-desc {
            font-size: 0.9rem;
        }

        .option-card {
            border-radius: 18px;
            padding: 12px;
        }

        .option-tab-content {
            padding-top: 16px;
        }

        .option-add-btn {
            width: 100%;
        }

        .option-table-wrap {
            border: 0;
            background: transparent;
            box-shadow: none;
        }

        .option-table thead {
            display: none;
        }

        .option-table,
        .option-table tbody,
        .option-table tr,
        .option-table td {
            display: block;
            width: 100%;
        }

        .option-table tr {
            border: 1px solid var(--commission-border);
            border-radius: 18px;
            padding: 12px 14px;
            margin-bottom: 12px;
            background: var(--commission-bg-white);
            box-shadow: var(--commission-shadow-sm);
        }

        .option-table tbody td {
            border-bottom: 0;
            padding: 8px 0;
            display: flex;
            justify-content: space-between;
            gap: 16px;
            text-align: right;
            background: transparent;
        }

        .option-table tbody tr:hover td {
            background: transparent;
        }

        .option-table tbody td::before {
            content: attr(data-label);
            font-weight: 900;
            color: var(--commission-blue-dark);
            text-align: left;
            flex: 0 0 42%;
        }

        .option-table tbody td:last-child {
            justify-content: flex-end;
        }

        .option-table tbody td:last-child::before {
            display: none;
        }

        .option-modal-dialog {
            max-width: calc(100% - 24px);
            margin-left: auto;
            margin-right: auto;
        }

        .option-modal-header,
        .option-modal-body {
            padding: 16px;
        }

        .option-modal-footer {
            padding: 14px 16px;
            flex-wrap: nowrap;
        }

        .option-cancel-btn,
        .option-save-btn {
            flex: 1;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const optionModalElement = document.getElementById('optionModal');
        const optionModal = new bootstrap.Modal(optionModalElement);

        const optionForm = document.getElementById('optionForm');
        const optionFormMethod = document.getElementById('optionFormMethod');

        const optionModalTitle = document.getElementById('optionModalTitle');
        const optionFieldLabel = document.getElementById('optionFieldLabel');

        const optionValueInput = document.getElementById('optionValueInput');
        const optionValueTextarea = document.getElementById('optionValueTextarea');

        const openButtons = document.querySelectorAll('.js-open-option-modal');

        function setFieldMode(isNote) {
            if (isNote) {
                optionValueInput.classList.add('d-none');
                optionValueInput.disabled = true;
                optionValueInput.removeAttribute('required');

                optionValueTextarea.classList.remove('d-none');
                optionValueTextarea.disabled = false;
                optionValueTextarea.setAttribute('required', 'required');
            } else {
                optionValueTextarea.classList.add('d-none');
                optionValueTextarea.disabled = true;
                optionValueTextarea.removeAttribute('required');

                optionValueInput.classList.remove('d-none');
                optionValueInput.disabled = false;
                optionValueInput.setAttribute('required', 'required');
            }
        }

        openButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const mode = button.dataset.mode;
                const isNote = button.dataset.isNote === '1';

                optionModalTitle.textContent = button.dataset.title;
                optionFieldLabel.textContent = button.dataset.label;

                setFieldMode(isNote);

                if (mode === 'create') {
                    optionForm.action = button.dataset.storeUrl;
                    optionFormMethod.value = 'POST';

                    optionValueInput.value = '';
                    optionValueTextarea.value = '';
                }

                if (mode === 'edit') {
                    optionForm.action = button.dataset.updateUrl;
                    optionFormMethod.value = 'PUT';

                    const value = button.dataset.value || '';

                    optionValueInput.value = value;
                    optionValueTextarea.value = value;
                }

                optionModal.show();

                setTimeout(function() {
                    if (isNote) {
                        optionValueTextarea.focus();
                    } else {
                        optionValueInput.focus();
                    }
                }, 300);
            });
        });

        const tabButtons = document.querySelectorAll('#customerOptionTabs button[data-bs-toggle="tab"]');

        tabButtons.forEach(function(button) {
            button.addEventListener('shown.bs.tab', function(event) {
                const tabKey = event.target.dataset.tabKey;

                const url = new URL(window.location.href);
                url.searchParams.set('tab', tabKey);

                window.history.replaceState({}, '', url.toString());
            });
        });
    });
</script>
@endpush