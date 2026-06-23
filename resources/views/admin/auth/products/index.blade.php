@extends('admin.auth.dashboardAmin')

@section('title', 'Danh sách sản phẩm')

@section('admin_content')
<div class="product-page">
    <div class="page-header mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item">Kho sản phẩm</li>
                    <li class="breadcrumb-item active">Danh sách sản phẩm</li>
                </ol>
            </nav>

            <h3 class="page-title mb-1">Danh sách sản phẩm</h3>
            <p class="page-subtitle mb-0">
                Quản lý thông tin sản phẩm, hình ảnh, giá bán và trạng thái kinh doanh.
            </p>
        </div>

        <button class="btn btn-primary btn-page-action" type="button" onclick="openCreateProductModal()">
            <i class="fa-solid fa-plus me-1"></i>
            Thêm sản phẩm
        </button>
    </div>

    <div class="filter-card mb-3">
        <div class="row g-3 align-items-center">
            <div class="col-lg-5">
                <input id="productKeyword" class="form-control" placeholder="Tìm tên sản phẩm, mã SKU, đơn vị tính...">
            </div>

            <div class="col-lg-3">
                <select id="productCategoryFilter" class="form-select">
                    <option value="">Tất cả danh mục</option>

                    @foreach($categories as $category)
                    <option value="{{ $category->id }}">
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2">
                <select id="productStatusFilter" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active">Đang kinh doanh</option>
                    <option value="inactive">Đã ẩn</option>
                </select>
            </div>

            <div class="col-lg-2">
                <button class="btn btn-secondary w-100" type="button" onclick="loadProductTable()">
                    <i class="fa-solid fa-filter me-1"></i>
                    Lọc
                </button>
            </div>
        </div>
    </div>

    <div id="productAlert"></div>

    <div id="productTableContainer">
        @include('admin.auth.products._product_table', ['products' => $products])
    </div>
</div>

{{-- MODAL THÊM / SỬA SẢN PHẨM --}}
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content product-modal" id="productForm" enctype="multipart/form-data">
            @csrf

            <input type="hidden" id="productFormMethod" name="_method" value="POST">
            <input type="hidden" id="productId">

            <div class="modal-header">
                <h5 class="modal-title" id="productModalTitle">
                    Thêm / Sửa Sản phẩm
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div id="productFormErrors"></div>

                <div class="product-image-box mb-4">
                    <label for="main_image" class="product-image-preview" id="productImagePreview">
                        <i class="fa-regular fa-image"></i>

                        <span class="camera-badge">
                            <i class="fa-solid fa-camera"></i>
                        </span>
                    </label>

                    <input type="file" name="main_image" id="main_image" class="d-none" accept="image/*">

                    <div class="image-note">
                        Kích thước chuẩn: 500×500px
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">
                            Mã SKU <span class="text-danger">*</span>
                        </label>

                        <input type="text" name="product_code" id="product_code" class="form-control"
                            placeholder="VD: SP-001">
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">
                            Tên sản phẩm <span class="text-danger">*</span>
                        </label>

                        <input type="text" name="product_name" id="product_name" class="form-control"
                            placeholder="Nhập tên sản phẩm">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">
                            Đơn vị tính <span class="text-danger">*</span>
                        </label>

                        <input type="text" name="unit_name" id="unit_name" class="form-control"
                            placeholder="VD: Hộp, Chai...">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">
                            Giá bán (VNĐ) <span class="text-danger">*</span>
                        </label>

                        <input type="number" name="price" id="price" class="form-control text-end" min="0" step="1000"
                            value="0">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Trạng thái</label>

                        <select name="is_active" id="is_active" class="form-select">
                            <option value="1">Đang kinh doanh</option>
                            <option value="0">Đã ẩn</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Mô tả ngắn</label>

                        <textarea name="short_description" id="short_description" class="form-control" rows="3"
                            placeholder="Nhập mô tả hoặc thành phần công dụng sản phẩm..."></textarea>
                    </div>

                    <input type="hidden" name="product_category_id" id="product_category_id" value="">
                    <input type="hidden" name="description" id="description" value="">
                    <input type="hidden" name="min_quantity_alert" id="min_quantity_alert" value="0">
                    <input type="hidden" name="sort_order" id="sort_order" value="0">
                    <input type="hidden" name="track_batch" value="1">
                    <input type="hidden" name="track_expiry" value="1">
                    <input type="hidden" name="is_commissionable" value="1">
                    <input type="hidden" name="default_commission_rate" value="0">
                    <input type="hidden" name="allow_sell_without_stock" value="0">
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light border" type="button" data-bs-dismiss="modal">
                    Hủy
                </button>

                <button class="btn btn-primary" type="submit">
                    <i class="fa-solid fa-floppy-disk me-1"></i>
                    Lưu sản phẩm
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .product-page {
        padding-bottom: 40px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
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

    .breadcrumb {
        font-size: 14px;
    }

    .breadcrumb-item {
        color: #2563eb;
        font-weight: 600;
    }

    .btn-page-action {
        min-height: 42px;
        padding-left: 18px;
        padding-right: 18px;
        font-weight: 700;
        border-radius: 12px;
    }

    .filter-card {
        background: #fff;
        border-radius: 18px;
        padding: 16px;
        box-shadow: 0 10px 28px rgba(36, 58, 94, 0.08);
    }

    .form-control,
    .form-select {
        border-radius: 12px;
        border-color: #d8e3f0;
        min-height: 42px;
    }

    /*
    |--------------------------------------------------------------------------
    | TABLE DANH SÁCH SẢN PHẨM
    |--------------------------------------------------------------------------
    */

    .product-table-card,
    .card.border-0.shadow-sm {
        background: #fff;
        border-radius: 18px !important;
        overflow: hidden;
        box-shadow: 0 10px 28px rgba(36, 58, 94, 0.08) !important;
    }

    .product-table {
        width: 100%;
        margin-bottom: 0;
        table-layout: auto;
    }

    .product-table thead th {
        background: #f8fafc;
        color: #465670;
        font-weight: 800;
        border-bottom: 1px solid #d8e0ec;
        padding: 14px 12px;
        white-space: nowrap;
        vertical-align: middle;
    }

    .product-table tbody td {
        padding: 12px;
        vertical-align: middle;
        border-bottom: 1px solid #e6edf5;
        white-space: nowrap;
    }

    .product-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .product-table td[data-label="Sản phẩm"] {
        min-width: 280px;
        white-space: normal;
    }

    .product-table td[data-label="Danh mục"] {
        min-width: 120px;
        max-width: 160px;
        white-space: normal;
        line-height: 1.35;
    }

    .product-table td[data-label="Trạng thái"] {
        min-width: 125px;
    }

    .product-table td[data-label="Thao tác"] {
        min-width: 95px;
    }

    /*
    |--------------------------------------------------------------------------
    | ÉP HÌNH SẢN PHẨM NHỎ LẠI NHƯ HÌNH MẪU
    |--------------------------------------------------------------------------
    | Dù file table đang dùng .product-thumb hay img thường,
    | CSS này vẫn ép ảnh về 44x44px.
    |--------------------------------------------------------------------------
    */

    .product-thumb,
    .product-icon-box,
    .product-table .product-thumb,
    .product-table .product-icon-box {
        width: 44px !important;
        height: 44px !important;
        min-width: 44px !important;
        max-width: 44px !important;
        min-height: 44px !important;
        max-height: 44px !important;
        border-radius: 10px !important;
        border: 1px solid #edf2f7 !important;
        background: #f5f7fb !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        overflow: hidden !important;
        flex: 0 0 44px !important;
        color: #2563eb !important;
        font-size: 18px !important;
    }

    .product-thumb img,
    .product-icon-box img,
    .product-table .product-thumb img,
    .product-table .product-icon-box img,
    .product-table td[data-label="Sản phẩm"] img {
        width: 44px !important;
        height: 44px !important;
        min-width: 44px !important;
        max-width: 44px !important;
        min-height: 44px !important;
        max-height: 44px !important;
        object-fit: cover !important;
        border-radius: 10px !important;
        display: block !important;
    }

    .product-table td[data-label="Sản phẩm"]>img {
        width: 44px !important;
        height: 44px !important;
        object-fit: cover !important;
        border-radius: 10px !important;
    }

    .product-thumb-placeholder {
        width: 44px !important;
        height: 44px !important;
        min-width: 44px !important;
        border-radius: 10px !important;
        border: 1px solid #edf2f7 !important;
        background: #f5f7fb !important;
        color: #2563eb !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 18px !important;
    }

    .product-name,
    .product-table .fw-bold {
        font-weight: 800;
        color: #000;
    }

    .price-text,
    .product-table .text-danger {
        color: #ef233c !important;
        font-weight: 800;
    }

    /*
    |--------------------------------------------------------------------------
    | SỐ LÔ CÓ MÀU
    |--------------------------------------------------------------------------
    */

    .batch-badge,
    .badge-soft-secondary,
    .product-table .badge {
        background: #edf2f7 !important;
        color: #536174 !important;
        padding: 5px 10px !important;
        border-radius: 999px !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        line-height: 1 !important;
        white-space: nowrap !important;
    }

    /*
    |--------------------------------------------------------------------------
    | SWITCH TRẠNG THÁI
    |--------------------------------------------------------------------------
    */

    .status-wrap {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .status-switch,
    .table-switch {
        width: 44px !important;
        height: 22px !important;
        cursor: pointer;
        margin: 0 !important;
    }

    .status-switch:checked,
    .table-switch:checked {
        background-color: #10b981 !important;
        border-color: #10b981 !important;
    }

    /*
    |--------------------------------------------------------------------------
    | NÚT THAO TÁC
    |--------------------------------------------------------------------------
    */

    .action-btn,
    .product-table .btn-sm {
        width: 34px !important;
        height: 34px !important;
        border-radius: 10px !important;
        border: 1px solid #dce6f2 !important;
        background: #fff !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0 !important;
    }

    .action-btn.edit,
    .product-table .text-primary {
        color: #2563eb !important;
    }

    .action-btn.delete,
    .product-table .text-danger {
        color: #ef233c !important;
    }

    /*
    |--------------------------------------------------------------------------
    | MODAL THÊM / SỬA SẢN PHẨM
    |--------------------------------------------------------------------------
    */

    .product-modal {
        border-radius: 8px;
        overflow: hidden;
    }

    .product-modal .modal-title {
        font-weight: 800;
        color: #2d3b52;
    }

    .product-image-box {
        text-align: center;
    }

    .product-image-preview {
        width: 120px;
        height: 120px;
        border-radius: 6px;
        border: 1px solid #d7e1ee;
        background: #f8fafc;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 34px;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .product-image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .camera-badge {
        position: absolute;
        right: 14px;
        bottom: 0;
        width: 34px;
        height: 34px;
        background: #2563eb;
        color: #fff;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        border: 3px solid #fff;
    }

    .image-note {
        margin-top: 8px;
        color: #6b7280;
        font-size: 14px;
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
        }

        .btn-page-action {
            width: 100%;
        }

        .product-table tbody td {
            white-space: normal;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const productRoutes = {
        table: "{{ route('admin.products.table') }}",
        store: "{{ route('admin.products.store') }}",
        edit: "{{ route('admin.products.edit', ['product' => '__ID__']) }}",
        update: "{{ route('admin.products.update', ['product' => '__ID__']) }}",
        destroy: "{{ route('admin.products.destroy', ['product' => '__ID__']) }}",
        toggleStatus: "{{ route('admin.products.toggle-status', ['product' => '__ID__']) }}",
    };

    const productModal = new bootstrap.Modal(document.getElementById('productModal'));
    const productForm = document.getElementById('productForm');
    const mainImageInput = document.getElementById('main_image');
    const productImagePreview = document.getElementById('productImagePreview');

    function routeWithId(template, id) {
        return template.replace('__ID__', id);
    }

    function showProductAlert(message, type = 'success') {
        document.getElementById('productAlert').innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    }

    function renderFormErrors(errors) {
        let html = '<div class="alert alert-danger"><ul class="mb-0">';

        Object.values(errors).forEach(messages => {
            messages.forEach(message => {
                html += `<li>${message}</li>`;
            });
        });

        html += '</ul></div>';

        document.getElementById('productFormErrors').innerHTML = html;
    }

    function resetImagePreview() {
        productImagePreview.innerHTML = `
            <i class="fa-regular fa-image"></i>
            <span class="camera-badge">
                <i class="fa-solid fa-camera"></i>
            </span>
        `;
    }

    mainImageInput.addEventListener('change', function() {
        const file = this.files[0];

        if (!file) {
            resetImagePreview();
            return;
        }

        const reader = new FileReader();

        reader.onload = function(event) {
            productImagePreview.innerHTML = `
                <img src="${event.target.result}" alt="Ảnh sản phẩm">
                <span class="camera-badge">
                    <i class="fa-solid fa-camera"></i>
                </span>
            `;
        };

        reader.readAsDataURL(file);
    });

    function loadProductTable(url = null) {
        const params = new URLSearchParams({
            keyword: document.getElementById('productKeyword').value,
            category_id: document.getElementById('productCategoryFilter').value,
            status: document.getElementById('productStatusFilter').value,
        });

        fetch(url || `${productRoutes.table}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('productTableContainer').innerHTML = html;
            });
    }

    function openCreateProductModal() {
        productForm.reset();

        document.getElementById('productFormMethod').value = 'POST';
        document.getElementById('productId').value = '';
        document.getElementById('productModalTitle').innerText = 'Thêm / Sửa Sản phẩm';
        document.getElementById('productFormErrors').innerHTML = '';
        document.getElementById('is_active').value = '1';

        resetImagePreview();

        productForm.action = productRoutes.store;
        productModal.show();
    }

    function openEditProductModal(productId) {
        productForm.reset();

        document.getElementById('productFormErrors').innerHTML = '';
        resetImagePreview();

        fetch(routeWithId(productRoutes.edit, productId))
            .then(response => response.json())
            .then(data => {
                const product = data.product;

                document.getElementById('productModalTitle').innerText = 'Thêm / Sửa Sản phẩm';
                document.getElementById('productFormMethod').value = 'PUT';
                document.getElementById('productId').value = product.id;

                productForm.action = routeWithId(productRoutes.update, product.id);

                document.getElementById('product_code').value = product.product_code || '';
                document.getElementById('product_name').value = product.product_name || '';
                document.getElementById('unit_name').value = product.unit_name || '';
                document.getElementById('price').value = parseInt(product.price || 0);
                document.getElementById('short_description').value = product.short_description || '';
                document.getElementById('product_category_id').value = product.product_category_id || '';
                document.getElementById('min_quantity_alert').value = product.min_quantity_alert || 0;
                document.getElementById('sort_order').value = product.sort_order || 0;
                document.getElementById('is_active').value = product.is_active ? '1' : '0';

                if (product.image_url) {
                    productImagePreview.innerHTML = `
                        <img src="${product.image_url}" alt="Ảnh sản phẩm">
                        <span class="camera-badge">
                            <i class="fa-solid fa-camera"></i>
                        </span>
                    `;
                }

                productModal.show();
            });
    }

    productForm.addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(productForm);

        fetch(productForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData
            })
            .then(async response => {
                const data = await response.json();

                if (!response.ok) {
                    if (data.errors) {
                        renderFormErrors(data.errors);
                    }

                    return;
                }

                productModal.hide();
                showProductAlert(data.message);
                loadProductTable();
            });
    });

    function deleteProduct(productId) {
        if (!confirm('Bạn có chắc muốn xóa sản phẩm này không?')) {
            return;
        }

        fetch(routeWithId(productRoutes.destroy, productId), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                showProductAlert(data.message);
                loadProductTable();
            });
    }

    function toggleProductStatus(productId) {
        fetch(routeWithId(productRoutes.toggleStatus, productId), {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                showProductAlert(data.message);
                loadProductTable();
            });
    }

    document.addEventListener('click', function(event) {
        const paginationLink = event.target.closest('#productTableContainer .pagination a');

        if (paginationLink) {
            event.preventDefault();
            loadProductTable(paginationLink.href);
        }
    });

    document.getElementById('productKeyword').addEventListener('keyup', function(event) {
        if (event.key === 'Enter') {
            loadProductTable();
        }
    });
</script>
@endpush