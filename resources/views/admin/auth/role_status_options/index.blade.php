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
<link rel="stylesheet" href="{{ asset('admin/css/pages/auth-role_status_options-index.css') }}">
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