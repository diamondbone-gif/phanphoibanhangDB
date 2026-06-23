@extends('admin.auth.dashboardAmin')

@section('title', 'Cấu hình Trạng thái & Vai trò')

@section('admin_content')
<div class="container-fluid role-option-page">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}">Hệ thống</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                Cấu hình Trạng thái & Vai trò
            </li>
        </ol>
    </nav>

    <div class="mb-4">
        <h3 class="mb-1 role-page-title">Cấu hình Trạng thái & Vai trò</h3>
        <p class="role-page-desc mb-0">
            Quản lý các danh sách tùy chọn hiển thị trong phần Vai trò & trạng thái của khách hàng.
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

    <div class="role-card">

        <ul class="nav role-tabs" id="roleStatusTabs" role="tablist">
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

        <div class="tab-content role-tab-content" id="roleStatusTabsContent">

            @foreach($tabs as $key => $tab)
            @php
            $storeUrl = \Illuminate\Support\Facades\URL::signedRoute('admin.role-status-options.store', [
            'type' => $key,
            ]);
            @endphp

            <div class="tab-pane fade {{ $activeTab === $key ? 'show active' : '' }}" id="pane-{{ $key }}"
                role="tabpanel" aria-labelledby="tab-{{ $key }}">

                <div class="d-flex justify-content-end mb-3">
                    <button class="btn role-add-btn js-open-role-modal" type="button" data-mode="create"
                        data-type="{{ $key }}" data-title="{{ $tab['modal_add_title'] }}"
                        data-label="{{ $tab['field_label'] }}" data-store-url="{{ $storeUrl }}">
                        <i class="fa-solid fa-plus me-1"></i>
                        {{ $tab['button'] }}
                    </button>
                </div>

                <div class="table-responsive role-table-wrap">
                    <table class="table role-table align-middle mb-0">
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
                            $value = $item->name;

                            $updateUrl =
                            \Illuminate\Support\Facades\URL::signedRoute('admin.role-status-options.update', [
                            'type' => $key,
                            'id' => $item->id,
                            ]);

                            $deleteUrl =
                            \Illuminate\Support\Facades\URL::signedRoute('admin.role-status-options.destroy', [
                            'type' => $key,
                            'id' => $item->id,
                            ]);
                            @endphp

                            <tr>
                                <td data-label="STT">
                                    {{ $loop->iteration }}
                                </td>

                                <td data-label="{{ $tab['table_label'] }}" class="role-name-cell">
                                    {{ $value }}
                                </td>

                                <td data-label="Thao tác" class="text-end">
                                    <div class="role-actions">
                                        <button class="role-icon-btn role-edit-btn js-open-role-modal" type="button"
                                            title="Sửa" data-mode="edit" data-type="{{ $key }}"
                                            data-title="{{ $tab['modal_edit_title'] }}"
                                            data-label="{{ $tab['field_label'] }}" data-update-url="{{ $updateUrl }}"
                                            data-value="{{ e($value) }}">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>

                                        <form method="POST" action="{{ $deleteUrl }}" class="d-inline"
                                            onsubmit="return confirm('Bạn có chắc muốn xóa dòng này không?');">
                                            @csrf
                                            @method('DELETE')

                                            <button class="role-icon-btn role-delete-btn" type="submit" title="Xóa">
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

<div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered role-modal-dialog">
        <div class="modal-content role-modal-content">

            <form method="POST" id="roleForm" autocomplete="off">
                @csrf
                <input type="hidden" name="_method" id="roleFormMethod" value="POST">

                <div class="modal-header role-modal-header">
                    <h5 class="modal-title" id="roleModalTitle">Thêm mới</h5>

                    <button type="button" class="btn-close role-modal-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>

                <div class="modal-body role-modal-body">
                    <label class="form-label role-modal-label">
                        <span id="roleFieldLabel">Tên</span>
                        <span class="text-danger">*</span>
                    </label>

                    <input class="form-control role-modal-input" type="text" name="value" id="roleValueInput"
                        maxlength="100" required>
                </div>

                <div class="modal-footer role-modal-footer">
                    <button type="button" class="btn btn-light role-cancel-btn" data-bs-dismiss="modal">
                        Hủy
                    </button>

                    <button type="submit" class="btn role-save-btn">
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
    .role-option-page {
        padding-bottom: 40px;
    }

    .role-page-title {
        font-size: 2rem;
        font-weight: 800;
        color: #172033;
    }

    .role-page-desc {
        color: #66748b;
        font-size: 1rem;
    }

    .role-card {
        background: #ffffff;
        border-radius: 22px;
        padding: 20px;
        box-shadow: 0 18px 40px rgba(36, 58, 94, 0.06);
    }

    .role-tabs {
        border-bottom: 1px solid #d9e3ef;
        gap: 0;
        overflow-x: auto;
        overflow-y: hidden;
        flex-wrap: nowrap;
        scrollbar-width: thin;
    }

    .role-tabs .nav-item {
        flex: 0 0 auto;
    }

    .role-tabs .nav-link {
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

    .role-tabs .nav-link.active {
        color: #1764ff;
        background: #ffffff;
        border-color: #cfe0ff;
        box-shadow:
            4px 0 0 #cfe0ff inset,
            -4px 0 0 #cfe0ff inset,
            0 -4px 0 #cfe0ff inset;
    }

    .role-tab-content {
        padding-top: 20px;
    }

    .role-add-btn {
        background: #2563eb;
        border: 0;
        color: #ffffff;
        border-radius: 14px;
        padding: 9px 18px;
        font-weight: 800;
        box-shadow: 0 8px 18px rgba(37, 99, 235, 0.18);
    }

    .role-add-btn:hover {
        background: #1d4ed8;
        color: #ffffff;
    }

    .role-table-wrap {
        border-radius: 18px;
        overflow: hidden;
    }

    .role-table thead th {
        background: #f8fafc;
        color: #40506a;
        font-weight: 800;
        font-size: 1rem;
        border-bottom: 1px solid #cfd8e3;
        padding: 13px 16px;
        white-space: nowrap;
    }

    .role-table tbody td {
        padding: 13px 16px;
        border-bottom: 1px solid #d9e1eb;
        font-size: 1rem;
        color: #000;
    }

    .role-name-cell {
        font-weight: 500;
    }

    .role-actions {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .role-icon-btn {
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

    .role-edit-btn {
        color: #0d6efd;
    }

    .role-edit-btn:hover {
        background: #eaf2ff;
        border-color: #bcd3ff;
    }

    .role-delete-btn {
        color: #e11d48;
    }

    .role-delete-btn:hover {
        background: #fff1f2;
        border-color: #fecdd3;
    }

    .role-modal-dialog {
        max-width: 610px;
    }

    .role-modal-content {
        border: 1px solid #cfd8e3;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(15, 23, 42, 0.25);
    }

    .role-modal-header {
        padding: 20px 22px;
        border-bottom: 1px solid #d9e1eb;
    }

    .role-modal-header .modal-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: #243247;
    }

    .role-modal-close {
        transform: scale(1.15);
    }

    .role-modal-body {
        padding: 22px;
    }

    .role-modal-label {
        font-size: 1rem;
        color: #28384f;
        margin-bottom: 10px;
    }

    .role-modal-input {
        height: 52px;
        border-radius: 13px;
        border-color: #d6e1ef;
        font-size: 1rem;
    }

    .role-modal-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 0.18rem rgba(37, 99, 235, 0.14);
    }

    .role-modal-footer {
        padding: 18px 22px;
        border-top: 1px solid #d9e1eb;
    }

    .role-cancel-btn {
        border: 1px solid #d6e1ef;
        border-radius: 14px;
        padding: 9px 18px;
        font-weight: 800;
    }

    .role-save-btn {
        background: #2563eb;
        border: 0;
        color: #ffffff;
        border-radius: 14px;
        padding: 9px 18px;
        font-weight: 800;
    }

    .role-save-btn:hover {
        background: #1d4ed8;
        color: #ffffff;
    }

    @media (max-width: 991.98px) {
        .role-page-title {
            font-size: 1.55rem;
        }

        .role-card {
            padding: 14px;
        }

        .role-tabs .nav-link {
            font-size: 0.95rem;
            padding: 11px 14px;
        }
    }

    @media (max-width: 767.98px) {
        .role-page-title {
            font-size: 1.35rem;
        }

        .role-page-desc {
            font-size: 0.9rem;
        }

        .role-card {
            border-radius: 18px;
            padding: 12px;
        }

        .role-tab-content {
            padding-top: 16px;
        }

        .role-add-btn {
            width: 100%;
        }

        .role-table thead {
            display: none;
        }

        .role-table,
        .role-table tbody,
        .role-table tr,
        .role-table td {
            display: block;
            width: 100%;
        }

        .role-table tr {
            border: 1px solid #d9e1eb;
            border-radius: 16px;
            padding: 10px 12px;
            margin-bottom: 12px;
            background: #fff;
        }

        .role-table tbody td {
            border-bottom: 0;
            padding: 8px 0;
            display: flex;
            justify-content: space-between;
            gap: 16px;
            text-align: right;
        }

        .role-table tbody td::before {
            content: attr(data-label);
            font-weight: 800;
            color: #526179;
            text-align: left;
            flex: 0 0 42%;
        }

        .role-table tbody td:last-child {
            justify-content: flex-end;
        }

        .role-table tbody td:last-child::before {
            display: none;
        }

        .role-modal-dialog {
            max-width: calc(100% - 24px);
            margin-left: auto;
            margin-right: auto;
        }

        .role-modal-header,
        .role-modal-body {
            padding: 16px;
        }

        .role-modal-footer {
            padding: 14px 16px;
            flex-wrap: nowrap;
        }

        .role-cancel-btn,
        .role-save-btn {
            flex: 1;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleModalElement = document.getElementById('roleModal');

        if (!roleModalElement) {
            return;
        }

        const roleModal = new bootstrap.Modal(roleModalElement);

        const roleForm = document.getElementById('roleForm');
        const roleFormMethod = document.getElementById('roleFormMethod');

        const roleModalTitle = document.getElementById('roleModalTitle');
        const roleFieldLabel = document.getElementById('roleFieldLabel');
        const roleValueInput = document.getElementById('roleValueInput');

        const openButtons = document.querySelectorAll('.js-open-role-modal');

        openButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const mode = button.dataset.mode;

                roleModalTitle.textContent = button.dataset.title;
                roleFieldLabel.textContent = button.dataset.label;

                if (mode === 'create') {
                    roleForm.action = button.dataset.storeUrl;
                    roleFormMethod.value = 'POST';
                    roleValueInput.value = '';
                }

                if (mode === 'edit') {
                    roleForm.action = button.dataset.updateUrl;
                    roleFormMethod.value = 'PUT';
                    roleValueInput.value = button.dataset.value || '';
                }

                roleModal.show();

                setTimeout(function() {
                    roleValueInput.focus();
                }, 300);
            });
        });

        const tabButtons = document.querySelectorAll('#roleStatusTabs button[data-bs-toggle="tab"]');

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