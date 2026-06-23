@extends('admin.auth.dashboardAmin')

@section('title', 'Quản lý kho')

@section('admin_content')
<div class="inventory-page">
    <div class="inventory-header mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item">Kho sản phẩm</li>
                    <li class="breadcrumb-item active">Quản lý tồn kho</li>
                </ol>
            </nav>

            <h3 class="page-title mb-1">Quản lý Kho hàng</h3>

            <p class="page-subtitle mb-0">
                Theo dõi số lượng tồn kho, lô hàng, hạn sử dụng và nhập hàng.
            </p>
        </div>

        <div class="header-actions">
            <button class="btn btn-outline-primary" type="button" onclick="openMovementHistoryModal()">
                <i class="fa-solid fa-clock-rotate-left me-1"></i>
                Lịch sử nhập/xuất
            </button>

            <button class="btn btn-primary" type="button" onclick="openImportStockModal()" @if($products->count() === 0)
                disabled @endif>
                <i class="fa-solid fa-truck-ramp-box me-1"></i>
                Lập phiếu Nhập kho
            </button>
        </div>
    </div>

    @if($products->count() === 0)
    <div class="alert alert-warning">
        Bạn cần thêm sản phẩm trước, sau đó mới lập phiếu nhập kho được.
    </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <button class="inventory-stat-card" type="button" onclick="quickInventoryFilter('all')">
                <div>
                    <div class="stat-label">Tổng lô hàng</div>
                    <div class="stat-value text-dark">{{ $stats['total_batches'] }}</div>
                </div>

                <div class="stat-icon primary">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </button>
        </div>

        <div class="col-sm-6 col-xl-3">
            <button class="inventory-stat-card" type="button" onclick="quickInventoryFilter('low_stock')">
                <div>
                    <div class="stat-label">Sắp hết hàng</div>
                    <div class="stat-value text-warning">{{ $stats['low_stock'] }}</div>
                </div>

                <div class="stat-icon warning">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </button>
        </div>

        <div class="col-sm-6 col-xl-3">
            <button class="inventory-stat-card" type="button" onclick="quickInventoryFilter('out_stock')">
                <div>
                    <div class="stat-label">Hết hàng</div>
                    <div class="stat-value text-danger">{{ $stats['out_stock'] }}</div>
                </div>

                <div class="stat-icon danger">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>
            </button>
        </div>

        <div class="col-sm-6 col-xl-3">
            <button class="inventory-stat-card" type="button" onclick="quickInventoryFilter('near_expired')">
                <div>
                    <div class="stat-label">Hàng cận Date</div>
                    <div class="stat-value text-danger">{{ $stats['near_expired'] }}</div>
                </div>

                <div class="stat-icon danger">
                    <i class="fa-solid fa-calendar-xmark"></i>
                </div>
            </button>
        </div>
    </div>

    <div class="inventory-filter-card mb-3">
        <div class="row g-3 align-items-center">
            <div class="col-lg-4">
                <input id="inventoryKeyword" class="form-control" placeholder="Tìm tên SP, Mã SP, Số lô...">
            </div>

            <div class="col-lg-3">
                <select id="expiryFilter" class="form-select">
                    <option value="">Lọc theo HSD</option>
                    <option value="near_expired">Cận date (&lt; 6 tháng)</option>
                    <option value="expired">Hết hạn</option>
                </select>
            </div>

            <div class="col-lg-3">
                <select id="stockFilter" class="form-select">
                    <option value="">Tình trạng tồn</option>
                    <option value="in_stock">Còn hàng</option>
                    <option value="low_stock">Sắp hết hàng</option>
                    <option value="out_stock">Hết hàng</option>
                </select>
            </div>

            <div class="col-lg-2">
                <button class="btn btn-secondary w-100" type="button" onclick="loadInventoryTable()">
                    <i class="fa-solid fa-filter me-1"></i>
                    Lọc
                </button>
            </div>
        </div>
    </div>

    <div id="inventoryAlert"></div>

    <div id="inventoryTableContainer">
        @include('admin.auth.inventory._inventory_table', ['batches' => $batches])
    </div>
</div>

{{-- MODAL LẬP PHIẾU NHẬP KHO --}}
<div class="modal fade" id="importStockModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content inventory-modal" id="importStockForm">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">
                    Lập Phiếu Nhập Kho / Cập nhật Lô
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div id="importStockErrors"></div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            Chọn sản phẩm <span class="text-danger">*</span>
                        </label>

                        <select name="product_id" id="import_product_id" class="form-select required-input">
                            <option value="">Chọn sản phẩm</option>

                            @foreach($products as $product)
                            <option value="{{ $product->id }}">
                                {{ $product->product_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Số lô hàng <span class="text-danger">*</span>
                        </label>

                        <input type="text" name="batch_number" id="import_batch_number"
                            class="form-control required-input" placeholder="VD: LO-0626">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Ngày sản xuất <span class="text-danger">*</span>
                        </label>

                        <input type="date" name="manufacture_date" id="import_manufacture_date"
                            class="form-control required-input">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Hạn sử dụng <span class="text-danger">*</span>
                        </label>

                        <input type="date" name="expiry_date" id="import_expiry_date"
                            class="form-control required-input">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Số lượng nhập <span class="text-danger">*</span>
                        </label>

                        <input type="number" name="quantity" id="import_quantity" class="form-control required-input"
                            min="1" placeholder="100">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nhà cung cấp / Nguồn</label>

                        <input type="text" name="supplier_name" id="import_supplier_name" class="form-control"
                            placeholder="Xưởng sản xuất...">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Ghi chú phiếu nhập</label>

                        <textarea name="note" id="import_note" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light border" type="button" data-bs-dismiss="modal">
                    Hủy
                </button>

                <button class="btn btn-primary" id="saveImportStockBtn" type="submit">
                    <i class="fa-solid fa-check me-1"></i>
                    Lưu lô hàng
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL LỊCH SỬ NHẬP XUẤT --}}
<div class="modal fade" id="movementHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content inventory-modal">
            <div class="modal-header">
                <h5 class="modal-title">
                    Lịch sử Nhập/Xuất
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input id="movementKeyword" class="form-control mb-3"
                    placeholder="Tìm theo tên sản phẩm, mã SP, số lô..." onkeyup="loadMovementHistory()">

                <div id="movementHistoryContainer" class="text-center text-muted py-4">
                    Đang tải dữ liệu...
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL SỬA LÔ HÀNG --}}
<div class="modal fade" id="editBatchModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content inventory-modal" id="editBatchForm">
            @csrf
            @method('PUT')

            <input type="hidden" id="edit_batch_id">

            <div class="modal-header">
                <h5 class="modal-title">Sửa lô hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div id="editBatchErrors"></div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Sản phẩm</label>
                        <input type="text" id="edit_product_name" class="form-control" disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Số lô hàng <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="batch_number" id="edit_batch_number"
                            class="form-control required-input">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nhà cung cấp / Nguồn</label>
                        <input type="text" name="supplier_name" id="edit_supplier_name" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Ngày sản xuất <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="manufacture_date" id="edit_manufacture_date"
                            class="form-control required-input">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Hạn sử dụng <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="expiry_date" id="edit_expiry_date" class="form-control required-input">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            SL ban đầu <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="initial_quantity" id="edit_initial_quantity"
                            class="form-control required-input" min="1">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            SL còn lại <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="current_quantity" id="edit_current_quantity"
                            class="form-control required-input" min="0">

                        <div id="quantityCompareWarning" class="quantity-warning">
                            SL còn lại không được lớn hơn SL ban đầu.
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Ghi chú</label>
                        <textarea name="note" id="edit_note" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light border" type="button" data-bs-dismiss="modal">
                    Hủy
                </button>

                <button class="btn btn-primary" type="submit">
                    <i class="fa-solid fa-floppy-disk me-1"></i>
                    Cập nhật
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .inventory-header {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
    }

    .header-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .page-title {
        font-size: 28px;
        font-weight: 800;
        color: #172033;
    }

    .page-subtitle {
        color: #6b7890;
        font-size: 15px;
    }

    .inventory-stat-card,
    .inventory-filter-card {
        background: #fff;
        border: 1px solid #e5edf7;
        border-radius: 18px;
        box-shadow: 0 10px 28px rgba(36, 58, 94, 0.08);
    }

    .inventory-stat-card {
        width: 100%;
        padding: 20px;
        min-height: 104px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        text-align: left;
        transition: all 0.25s ease;
    }

    .inventory-stat-card:hover {
        transform: translateY(-4px);
        border-color: #c9ddf8;
        box-shadow: 0 16px 36px rgba(36, 58, 94, 0.14);
    }

    .inventory-filter-card {
        padding: 16px;
    }

    .stat-label {
        color: #6b7890;
        font-weight: 700;
        font-size: 14px;
        margin-bottom: 8px;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 900;
        line-height: 1;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.25s ease;
    }

    .inventory-stat-card:hover .stat-icon {
        transform: scale(1.08) rotate(-4deg);
    }

    .stat-icon.primary {
        background: #eaf2ff;
        color: #2563eb;
    }

    .stat-icon.warning {
        background: #fff0c7;
        color: #e0a000;
    }

    .stat-icon.danger {
        background: #ffe1e5;
        color: #e63946;
    }

    .form-control,
    .form-select {
        border-radius: 12px;
        border-color: #d8e3f0;
        min-height: 42px;
        transition: all 0.22s ease;
    }

    .form-control:hover,
    .form-select:hover {
        border-color: #b7c7dc;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
    }

    .inventory-modal {
        border-radius: 14px;
        overflow: hidden;
        border: 0;
        box-shadow: 0 22px 60px rgba(15, 23, 42, 0.18);
    }

    .inventory-modal .modal-title {
        color: #2d3b52;
        font-weight: 800;
    }

    .inventory-modal .modal-header {
        background: #f8fbff;
        border-bottom: 1px solid #e6eef8;
    }

    .inventory-modal .modal-footer {
        background: #fbfdff;
        border-top: 1px solid #e6eef8;
    }

    .inventory-table-card {
        background: #fff;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 10px 28px rgba(36, 58, 94, 0.08);
    }

    .inventory-table {
        margin-bottom: 0;
    }

    .inventory-table thead th {
        background: #f8fafc;
        color: #465670;
        font-weight: 800;
        border-bottom: 1px solid #d8e0ec;
        padding: 14px 12px;
        white-space: nowrap;
    }

    .inventory-table tbody td {
        padding: 13px 12px;
        vertical-align: middle;
        border-bottom: 1px solid #e6edf5;
        white-space: nowrap;
    }

    .batch-badge {
        background: #edf2f7;
        color: #536174;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        display: inline-flex;
    }

    .table-switch {
        width: 44px !important;
        height: 22px !important;
        cursor: pointer;
    }

    .table-switch:checked {
        background-color: #10b981;
        border-color: #10b981;
    }

    .batch-status-wrap {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .inventory-action-group {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: nowrap;
    }

    .table-action-btn {
        width: 36px !important;
        height: 36px !important;
        min-width: 36px !important;
        border: none !important;
        outline: none !important;
        border-radius: 12px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        font-size: 16px !important;
        line-height: 1 !important;
        padding: 0 !important;
        text-decoration: none !important;
        box-shadow: none !important;
        transition: all 0.22s ease;
    }

    .table-action-btn i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        pointer-events: none;
    }

    .table-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.14) !important;
    }

    .table-action-btn:active {
        transform: scale(0.95);
    }

    .table-action-btn.action-edit {
        background: #eaf3ff !important;
        color: #0d6efd !important;
    }

    .table-action-btn.action-edit:hover {
        background: #0d6efd !important;
        color: #ffffff !important;
    }

    .table-action-btn.action-delete {
        background: #ffe8e8 !important;
        color: #dc3545 !important;
    }

    .table-action-btn.action-delete:hover {
        background: #dc3545 !important;
        color: #ffffff !important;
    }

    .input-error {
        border-color: #dc3545 !important;
        background: #fff7f7 !important;
        box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.12) !important;
        animation: inputShake 0.28s ease;
    }

    .input-success {
        border-color: #20c997 !important;
        background: #f6fffb !important;
    }

    .quantity-warning {
        margin-top: 8px;
        color: #dc3545;
        font-size: 13px;
        font-weight: 700;
        display: none;
    }

    .quantity-warning.show {
        display: block;
        animation: fadeDown 0.25s ease;
    }

    .alert {
        border-radius: 14px;
        animation: fadeDown 0.25s ease;
    }

    @keyframes inputShake {
        0% {
            transform: translateX(0);
        }

        25% {
            transform: translateX(-4px);
        }

        50% {
            transform: translateX(4px);
        }

        75% {
            transform: translateX(-3px);
        }

        100% {
            transform: translateX(0);
        }
    }

    @keyframes fadeDown {
        from {
            opacity: 0;
            transform: translateY(-4px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 768px) {
        .inventory-header {
            flex-direction: column;
        }

        .header-actions {
            width: 100%;
        }

        .header-actions .btn {
            flex: 1;
        }

        .page-title {
            font-size: 23px;
        }

        .table-action-btn {
            width: 34px !important;
            height: 34px !important;
            min-width: 34px !important;
            border-radius: 10px !important;
            font-size: 14px !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const inventoryRoutes = {
        table: "{{ route('admin.inventory.table') }}",
        importStock: "{{ route('admin.inventory.import-stock') }}",
        editBatch: "{{ route('admin.inventory.batches.edit', ['batch' => '__ID__']) }}",
        updateBatch: "{{ route('admin.inventory.batches.update', ['batch' => '__ID__']) }}",
        destroyBatch: "{{ route('admin.inventory.batches.destroy', ['batch' => '__ID__']) }}",
        toggleBatchStatus: "{{ route('admin.inventory.batches.toggle-status', ['batch' => '__ID__']) }}",
        movementHistory: "{{ route('admin.inventory.movement-history') }}",
    };

    const importStockModal = new bootstrap.Modal(document.getElementById('importStockModal'));
    const editBatchModal = new bootstrap.Modal(document.getElementById('editBatchModal'));
    const movementHistoryModal = new bootstrap.Modal(document.getElementById('movementHistoryModal'));

    const importStockForm = document.getElementById('importStockForm');
    const editBatchForm = document.getElementById('editBatchForm');
    const saveImportStockBtn = document.getElementById('saveImportStockBtn');

    function routeWithId(template, id) {
        return template.replace('__ID__', id);
    }

    function renderErrors(containerId, errors) {
        let html = '<div class="alert alert-danger"><ul class="mb-0">';

        Object.values(errors).forEach(messages => {
            messages.forEach(message => {
                html += `<li>${message}</li>`;
            });
        });

        html += '</ul></div>';

        document.getElementById(containerId).innerHTML = html;
    }

    function showInventoryAlert(message, type = 'success') {
        document.getElementById('inventoryAlert').innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    }

    function clearInputErrors(form) {
        form.querySelectorAll('.input-error, .input-success').forEach(input => {
            input.classList.remove('input-error', 'input-success');
        });
    }

    function setInputError(input) {
        if (input) {
            input.classList.remove('input-success');
            input.classList.add('input-error');
        }
    }

    function setInputSuccess(input) {
        if (input && input.value) {
            input.classList.remove('input-error');
            input.classList.add('input-success');
        }
    }

    function validateImportForm() {
        clearInputErrors(importStockForm);

        const productInput = document.getElementById('import_product_id');
        const batchInput = document.getElementById('import_batch_number');
        const manufactureInput = document.getElementById('import_manufacture_date');
        const expiryInput = document.getElementById('import_expiry_date');
        const quantityInput = document.getElementById('import_quantity');

        const errors = {};

        if (!productInput.value) {
            errors.product_id = ['Vui lòng chọn sản phẩm trước khi lưu lô hàng.'];
            setInputError(productInput);
        }

        if (!batchInput.value.trim()) {
            errors.batch_number = ['Vui lòng nhập số lô hàng.'];
            setInputError(batchInput);
        }

        if (!manufactureInput.value) {
            errors.manufacture_date = ['Vui lòng chọn ngày sản xuất.'];
            setInputError(manufactureInput);
        }

        if (!expiryInput.value) {
            errors.expiry_date = ['Vui lòng chọn hạn sử dụng.'];
            setInputError(expiryInput);
        }

        if (!quantityInput.value || Number(quantityInput.value) <= 0) {
            errors.quantity = ['Số lượng nhập phải lớn hơn 0.'];
            setInputError(quantityInput);
        }

        if (manufactureInput.value && expiryInput.value && manufactureInput.value > expiryInput.value) {
            errors.date = ['Hạn sử dụng phải lớn hơn hoặc bằng ngày sản xuất.'];
            setInputError(manufactureInput);
            setInputError(expiryInput);
        }

        if (Object.keys(errors).length > 0) {
            renderErrors('importStockErrors', errors);
            return false;
        }

        [productInput, batchInput, manufactureInput, expiryInput, quantityInput].forEach(setInputSuccess);
        return true;
    }

    function validateEditQuantities() {
        const initialInput = document.getElementById('edit_initial_quantity');
        const currentInput = document.getElementById('edit_current_quantity');
        const warning = document.getElementById('quantityCompareWarning');

        const initialQuantity = Number(initialInput.value);
        const currentQuantity = Number(currentInput.value);

        initialInput.classList.remove('input-error', 'input-success');
        currentInput.classList.remove('input-error', 'input-success');

        if (warning) {
            warning.classList.remove('show');
        }

        if (!initialInput.value || !currentInput.value) {
            setInputError(initialInput);
            setInputError(currentInput);

            renderErrors('editBatchErrors', {
                quantity: ['Vui lòng nhập đầy đủ SL ban đầu và SL còn lại.']
            });

            return false;
        }

        if (initialQuantity <= 0) {
            setInputError(initialInput);

            renderErrors('editBatchErrors', {
                initial_quantity: ['SL ban đầu phải lớn hơn 0.']
            });

            return false;
        }

        if (currentQuantity < 0) {
            setInputError(currentInput);

            renderErrors('editBatchErrors', {
                current_quantity: ['SL còn lại không được nhỏ hơn 0.']
            });

            return false;
        }

        if (currentQuantity > initialQuantity) {
            setInputError(initialInput);
            setInputError(currentInput);

            if (warning) {
                warning.classList.add('show');
            }

            renderErrors('editBatchErrors', {
                quantity: ['SL còn lại không được lớn hơn SL ban đầu.']
            });

            return false;
        }

        setInputSuccess(initialInput);
        setInputSuccess(currentInput);
        return true;
    }

    function validateEditForm() {
        clearInputErrors(editBatchForm);

        const batchInput = document.getElementById('edit_batch_number');
        const manufactureInput = document.getElementById('edit_manufacture_date');
        const expiryInput = document.getElementById('edit_expiry_date');

        const errors = {};

        if (!batchInput.value.trim()) {
            errors.batch_number = ['Vui lòng nhập số lô hàng.'];
            setInputError(batchInput);
        }

        if (!manufactureInput.value) {
            errors.manufacture_date = ['Vui lòng chọn ngày sản xuất.'];
            setInputError(manufactureInput);
        }

        if (!expiryInput.value) {
            errors.expiry_date = ['Vui lòng chọn hạn sử dụng.'];
            setInputError(expiryInput);
        }

        if (manufactureInput.value && expiryInput.value && manufactureInput.value > expiryInput.value) {
            errors.date = ['Hạn sử dụng phải lớn hơn hoặc bằng ngày sản xuất.'];
            setInputError(manufactureInput);
            setInputError(expiryInput);
        }

        if (Object.keys(errors).length > 0) {
            renderErrors('editBatchErrors', errors);
            return false;
        }

        if (!validateEditQuantities()) {
            return false;
        }

        [batchInput, manufactureInput, expiryInput].forEach(setInputSuccess);
        return true;
    }

    function liveCheckEditQuantities() {
        const initialInput = document.getElementById('edit_initial_quantity');
        const currentInput = document.getElementById('edit_current_quantity');
        const warning = document.getElementById('quantityCompareWarning');

        if (!initialInput || !currentInput) {
            return;
        }

        const initialQuantity = Number(initialInput.value);
        const currentQuantity = Number(currentInput.value);

        initialInput.classList.remove('input-error', 'input-success');
        currentInput.classList.remove('input-error', 'input-success');

        if (warning) {
            warning.classList.remove('show');
        }

        if (!initialInput.value || !currentInput.value) {
            return;
        }

        if (currentQuantity > initialQuantity) {
            setInputError(initialInput);
            setInputError(currentInput);

            if (warning) {
                warning.classList.add('show');
            }

            return;
        }

        setInputSuccess(initialInput);
        setInputSuccess(currentInput);
    }

    function loadInventoryTable(url = null) {
        const params = new URLSearchParams({
            keyword: document.getElementById('inventoryKeyword').value,
            expiry_filter: document.getElementById('expiryFilter').value,
            stock_filter: document.getElementById('stockFilter').value,
        });

        fetch(url || `${inventoryRoutes.table}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                }
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('inventoryTableContainer').innerHTML = html;
            });
    }

    function quickInventoryFilter(type) {
        document.getElementById('inventoryKeyword').value = '';
        document.getElementById('expiryFilter').value = '';
        document.getElementById('stockFilter').value = '';

        if (type === 'low_stock') {
            document.getElementById('stockFilter').value = 'low_stock';
        }

        if (type === 'out_stock') {
            document.getElementById('stockFilter').value = 'out_stock';
        }

        if (type === 'near_expired') {
            document.getElementById('expiryFilter').value = 'near_expired';
        }

        loadInventoryTable();
    }

    function openImportStockModal() {
        importStockForm.reset();
        clearInputErrors(importStockForm);
        document.getElementById('importStockErrors').innerHTML = '';
        importStockModal.show();
    }

    importStockForm.addEventListener('submit', function(event) {
        event.preventDefault();

        document.getElementById('importStockErrors').innerHTML = '';

        if (!validateImportForm()) {
            return;
        }

        const formData = new FormData(importStockForm);

        if (saveImportStockBtn) {
            saveImportStockBtn.disabled = true;
            saveImportStockBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang lưu...';
        }

        fetch(inventoryRoutes.importStock, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(async response => {
                const data = await response.json();

                if (!response.ok) {
                    if (data.errors) {
                        renderErrors('importStockErrors', data.errors);
                        return;
                    }

                    throw new Error(data.message || 'Không thể lưu lô hàng.');
                }

                importStockModal.hide();
                showInventoryAlert(data.message || 'Lưu lô hàng thành công.');
                window.location.reload();
            })
            .catch(error => {
                document.getElementById('importStockErrors').innerHTML = `
            <div class="alert alert-danger">
                <strong>Không lưu được lô hàng.</strong><br>
                ${error.message}
            </div>
        `;
            })
            .finally(() => {
                if (saveImportStockBtn) {
                    saveImportStockBtn.disabled = false;
                    saveImportStockBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Lưu lô hàng';
                }
            });
    });

    function openEditBatchModal(batchId) {
        editBatchForm.reset();
        clearInputErrors(editBatchForm);
        document.getElementById('editBatchErrors').innerHTML = '';
        document.getElementById('quantityCompareWarning').classList.remove('show');

        fetch(routeWithId(inventoryRoutes.editBatch, batchId), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                const batch = data.batch;

                document.getElementById('edit_batch_id').value = batch.id;
                document.getElementById('edit_product_name').value = batch.product_name || '';
                document.getElementById('edit_batch_number').value = batch.batch_number || '';
                document.getElementById('edit_supplier_name').value = batch.supplier_name || '';
                document.getElementById('edit_manufacture_date').value = batch.manufacture_date || '';
                document.getElementById('edit_expiry_date').value = batch.expiry_date || '';
                document.getElementById('edit_initial_quantity').value = batch.initial_quantity || 0;
                document.getElementById('edit_current_quantity').value = batch.current_quantity || 0;
                document.getElementById('edit_note').value = batch.note || '';

                editBatchForm.action = routeWithId(inventoryRoutes.updateBatch, batch.id);
                editBatchModal.show();

                setTimeout(liveCheckEditQuantities, 100);
            })
            .catch(() => {
                showInventoryAlert('Không lấy được dữ liệu lô hàng để sửa.', 'danger');
            });
    }

    editBatchForm.addEventListener('submit', function(event) {
        event.preventDefault();

        document.getElementById('editBatchErrors').innerHTML = '';

        if (!validateEditForm()) {
            return;
        }

        const formData = new FormData(editBatchForm);

        fetch(editBatchForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(async response => {
                const data = await response.json();

                if (!response.ok) {
                    if (data.errors) {
                        renderErrors('editBatchErrors', data.errors);
                        return;
                    }

                    throw new Error(data.message || 'Không thể cập nhật lô hàng.');
                }

                editBatchModal.hide();
                showInventoryAlert(data.message || 'Cập nhật lô hàng thành công.');
                window.location.reload();
            })
            .catch(error => {
                document.getElementById('editBatchErrors').innerHTML = `
            <div class="alert alert-danger">
                <strong>Không cập nhật được lô hàng.</strong><br>
                ${error.message}
            </div>
        `;
            });
    });

    function deleteBatch(batchId) {
        if (!confirm('Bạn có chắc muốn xóa lô hàng này không?')) {
            return;
        }

        fetch(routeWithId(inventoryRoutes.destroyBatch, batchId), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(async response => {
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Không thể xóa lô hàng.');
                }

                showInventoryAlert(data.message || 'Xóa lô hàng thành công.');
                window.location.reload();
            })
            .catch(error => {
                showInventoryAlert(error.message, 'danger');
            });
    }

    function toggleBatchStatus(batchId) {
        fetch(routeWithId(inventoryRoutes.toggleBatchStatus, batchId), {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(async response => {
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Không thể cập nhật trạng thái.');
                }

                showInventoryAlert(data.message || 'Cập nhật trạng thái thành công.');
                loadInventoryTable();
            })
            .catch(error => {
                showInventoryAlert(error.message, 'danger');
            });
    }

    function openMovementHistoryModal() {
        movementHistoryModal.show();
        loadMovementHistory();
    }

    function loadMovementHistory() {
        const keyword = document.getElementById('movementKeyword').value;

        fetch(`${inventoryRoutes.movementHistory}?keyword=${encodeURIComponent(keyword)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                }
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('movementHistoryContainer').innerHTML = html;
            });
    }

    document.getElementById('inventoryKeyword').addEventListener('keyup', function(event) {
        if (event.key === 'Enter') {
            loadInventoryTable();
        }
    });

    document.getElementById('edit_initial_quantity').addEventListener('input', liveCheckEditQuantities);
    document.getElementById('edit_current_quantity').addEventListener('input', liveCheckEditQuantities);

    document.querySelectorAll('.required-input').forEach(input => {
        input.addEventListener('input', function() {
            if (this.value) {
                this.classList.remove('input-error');
            }
        });

        input.addEventListener('change', function() {
            if (this.value) {
                this.classList.remove('input-error');
            }
        });
    });
</script>
@endpush