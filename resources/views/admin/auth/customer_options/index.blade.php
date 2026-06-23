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
    .customer-option-page {
        padding-bottom: 40px;
    }

    .option-page-title {
        font-size: 2rem;
        font-weight: 800;
        color: #172033;
    }

    .option-page-desc {
        color: #66748b;
        font-size: 1rem;
    }

    .option-card {
        background: #ffffff;
        border-radius: 22px;
        padding: 20px;
        box-shadow: 0 18px 40px rgba(36, 58, 94, 0.06);
    }

    .option-tabs {
        border-bottom: 1px solid #d9e3ef;
        gap: 0;
        overflow-x: auto;
        overflow-y: hidden;
        flex-wrap: nowrap;
        scrollbar-width: thin;
    }

    .option-tabs .nav-item {
        flex: 0 0 auto;
    }

    .option-tabs .nav-link {
        border: 1px solid transparent;
        border-bottom: 0;
        color: #4c5b73;
        font-weight: 800;
        font-size: 1.05rem;
        padding: 13px 20px;
        border-radius: 12px 12px 0 0;
        background: transparent;
        white-space: nowrap;
    }

    .option-tabs .nav-link.active {
        color: #1764ff;
        background: #ffffff;
        border-color: #cfe0ff;
        box-shadow: 4px 0 0 #cfe0ff inset, -4px 0 0 #cfe0ff inset, 0 -4px 0 #cfe0ff inset;
    }

    .option-tab-content {
        padding-top: 20px;
    }

    .option-add-btn {
        background: #2563eb;
        border: 0;
        color: #ffffff;
        border-radius: 14px;
        padding: 9px 18px;
        font-weight: 800;
        box-shadow: 0 8px 18px rgba(37, 99, 235, 0.18);
    }

    .option-add-btn:hover {
        background: #1d4ed8;
        color: #ffffff;
    }

    .option-table-wrap {
        border-radius: 18px;
        overflow: hidden;
    }

    .option-table thead th {
        background: #f8fafc;
        color: #40506a;
        font-weight: 800;
        font-size: 1rem;
        border-bottom: 1px solid #cfd8e3;
        padding: 13px 16px;
        white-space: nowrap;
    }

    .option-table tbody td {
        padding: 13px 16px;
        border-bottom: 1px solid #d9e1eb;
        font-size: 1rem;
        color: #000;
    }

    .option-name-cell {
        font-weight: 500;
    }

    .option-actions {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .option-icon-btn {
        width: 36px;
        height: 36px;
        border-radius: 13px;
        border: 1px solid #d7e1ed;
        background: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease;
    }

    .option-edit-btn {
        color: #0d6efd;
    }

    .option-edit-btn:hover {
        background: #eaf2ff;
        border-color: #bcd3ff;
    }

    .option-delete-btn {
        color: #e11d48;
    }

    .option-delete-btn:hover {
        background: #fff1f2;
        border-color: #fecdd3;
    }

    .option-modal-dialog {
        max-width: 625px;
    }

    .option-modal-content {
        border: 1px solid #cfd8e3;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(15, 23, 42, 0.25);
    }

    .option-modal-header {
        padding: 20px 22px;
        border-bottom: 1px solid #d9e1eb;
    }

    .option-modal-header .modal-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: #243247;
    }

    .option-modal-close {
        transform: scale(1.15);
    }

    .option-modal-body {
        padding: 22px;
    }

    .option-modal-label {
        font-size: 1rem;
        color: #28384f;
        margin-bottom: 10px;
    }

    .option-modal-input {
        height: 52px;
        border-radius: 13px;
        border-color: #d6e1ef;
        font-size: 1rem;
    }

    .option-modal-textarea {
        border-radius: 13px;
        border-color: #d6e1ef;
        font-size: 1rem;
        min-height: 138px;
        resize: vertical;
    }

    .option-modal-input:focus,
    .option-modal-textarea:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 0.18rem rgba(37, 99, 235, 0.14);
    }

    .option-modal-footer {
        padding: 18px 22px;
        border-top: 1px solid #d9e1eb;
    }

    .option-cancel-btn {
        border: 1px solid #d6e1ef;
        border-radius: 14px;
        padding: 9px 18px;
        font-weight: 800;
    }

    .option-save-btn {
        background: #2563eb;
        border: 0;
        color: #ffffff;
        border-radius: 14px;
        padding: 9px 18px;
        font-weight: 800;
    }

    .option-save-btn:hover {
        background: #1d4ed8;
        color: #ffffff;
    }

    @media (max-width: 991.98px) {
        .option-page-title {
            font-size: 1.55rem;
        }

        .option-card {
            padding: 14px;
        }

        .option-tabs .nav-link {
            font-size: 0.95rem;
            padding: 11px 14px;
        }
    }

    @media (max-width: 767.98px) {
        .option-page-title {
            font-size: 1.35rem;
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
            border: 1px solid #d9e1eb;
            border-radius: 16px;
            padding: 10px 12px;
            margin-bottom: 12px;
            background: #fff;
        }

        .option-table tbody td {
            border-bottom: 0;
            padding: 8px 0;
            display: flex;
            justify-content: space-between;
            gap: 16px;
            text-align: right;
        }

        .option-table tbody td::before {
            content: attr(data-label);
            font-weight: 800;
            color: #526179;
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