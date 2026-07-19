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
                <input id="productKeyword" class="form-control" placeholder="Tìm tên sản phẩm, SKU, đơn vị tính...">
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

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">
                            Tên sản phẩm <span class="text-danger">*</span>
                        </label>

                        <input type="text" name="product_name" id="product_name" class="form-control" maxlength="255" required
                            placeholder="Ví dụ: Canxi BoneCare" autocomplete="off" autofocus>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">
                            Giá bán (VNĐ) <span class="text-danger">*</span>
                        </label>

                        <input type="number" name="price" id="price" class="form-control text-end" min="0" step="1000"
                            inputmode="decimal" value="0" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Danh mục</label>
                        <select name="product_category_id" id="product_category_id" class="form-select">
                            <option value="">Chưa phân loại</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Đơn vị tính</label>
                        <input type="text" name="unit_name" id="unit_name" class="form-control"
                            maxlength="100" placeholder="Mặc định: Sản phẩm">
                    </div>

                    <div class="col-md-12">
                        <button class="product-advanced-toggle" type="button" data-bs-toggle="collapse"
                            data-bs-target="#productAdvancedFields" aria-expanded="false" aria-controls="productAdvancedFields">
                            <span><i class="fa-solid fa-sliders me-2"></i>Thông tin thêm</span>
                            <i class="fa-solid fa-chevron-down toggle-chevron"></i>
                        </button>
                    </div>

                    <div class="collapse col-12" id="productAdvancedFields">
                        <div class="product-advanced-panel">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="product-image-box mb-0">
                                        <label for="main_image" class="product-image-preview" id="productImagePreview">
                                            <i class="fa-regular fa-image"></i>
                                            <span class="camera-badge"><i class="fa-solid fa-camera"></i></span>
                                        </label>
                                        <input type="file" name="main_image" id="main_image" class="d-none" accept="image/*">
                                        <div class="image-note">Ảnh vuông, tối đa 4 MB</div>
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Mã SKU</label>
                                            <input type="text" name="product_code" id="product_code" class="form-control"
                                                placeholder="Để trống để tự tạo">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Cảnh báo khi tồn dưới</label>
                                            <input type="number" name="min_quantity_alert" id="min_quantity_alert"
                                                class="form-control" min="0" value="0">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Trạng thái</label>
                                            <select name="is_active" id="is_active" class="form-select">
                                                <option value="1">Đang kinh doanh</option>
                                                <option value="0">Tạm ẩn</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Mô tả ngắn</label>
                                    <textarea name="short_description" id="short_description" class="form-control" rows="2"
                                        placeholder="Thông tin ngắn giúp nhân viên nhận biết sản phẩm"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12" id="openingStockSection">
                        <div class="opening-stock-card">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="enableOpeningStock">
                                <label class="form-check-label fw-bold" for="enableOpeningStock">Nhập tồn ban đầu ngay</label>
                            </div>
                            <div class="small text-muted mt-1">Hệ thống sẽ tự tạo lô và phiếu nhập kho, không chỉnh tồn trực tiếp.</div>

                            <fieldset id="openingStockFields" class="opening-stock-fields mt-3" disabled>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                                        <input type="number" name="initial_quantity" id="initial_quantity" class="form-control"
                                            min="1" value="1">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Số lô <span class="text-danger">*</span></label>
                                        <input type="text" name="batch_number" id="batch_number" class="form-control"
                                            maxlength="100" placeholder="VD: LOT-072026">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Hạn sử dụng <span class="text-danger">*</span></label>
                                        <input type="date" name="expiry_date" id="expiry_date" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Ngày sản xuất</label>
                                        <input type="date" name="manufacture_date" id="manufacture_date" class="form-control">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Ghi chú nhập kho</label>
                                        <input type="text" name="stock_note" id="stock_note" class="form-control"
                                            maxlength="1000" placeholder="Nhà cung cấp hoặc ghi chú kiểm kê ban đầu">
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
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
<link rel="stylesheet" href="{{ asset('admin/css/pages/auth-products-index.css') }}">
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
    const productAdvancedFields = document.getElementById('productAdvancedFields');
    const productAdvancedCollapse = bootstrap.Collapse.getOrCreateInstance(productAdvancedFields, { toggle: false });
    const openingStockSection = document.getElementById('openingStockSection');
    const enableOpeningStock = document.getElementById('enableOpeningStock');
    const openingStockFields = document.getElementById('openingStockFields');

    function setOpeningStockEnabled(enabled) {
        enableOpeningStock.checked = enabled;
        openingStockFields.disabled = !enabled;
        openingStockFields.classList.toggle('is-enabled', enabled);
    }

    enableOpeningStock.addEventListener('change', () => {
        setOpeningStockEnabled(enableOpeningStock.checked);

        if (enableOpeningStock.checked) {
            document.getElementById('initial_quantity').focus();
        }
    });

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
        document.getElementById('productModalTitle').innerText = 'Thêm sản phẩm nhanh';
        document.getElementById('productFormErrors').innerHTML = '';
        document.getElementById('is_active').value = '1';
        openingStockSection.classList.remove('d-none');
        setOpeningStockEnabled(false);
        productAdvancedCollapse.hide();

        resetImagePreview();

        productForm.action = productRoutes.store;
        productModal.show();
        document.getElementById('productModal').addEventListener('shown.bs.modal', () => {
            document.getElementById('product_name').focus();
        }, { once: true });
    }

    function openEditProductModal(productId) {
        productForm.reset();

        document.getElementById('productFormErrors').innerHTML = '';
        resetImagePreview();

        fetch(routeWithId(productRoutes.edit, productId))
            .then(response => response.json())
            .then(data => {
                const product = data.product;

                document.getElementById('productModalTitle').innerText = 'Cập nhật sản phẩm';
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
                document.getElementById('is_active').value = product.is_active ? '1' : '0';

                if (product.image_url) {
                    productImagePreview.innerHTML = `
                        <img src="${product.image_url}" alt="Ảnh sản phẩm">
                        <span class="camera-badge">
                            <i class="fa-solid fa-camera"></i>
                        </span>
                    `;
                }

                productAdvancedCollapse.show();
                setOpeningStockEnabled(false);
                openingStockSection.classList.add('d-none');
                productModal.show();
            });
    }

    productForm.addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(productForm);
        const submitButton = productForm.querySelector('button[type="submit"]');
        const originalButtonHtml = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Đang lưu...';
        document.getElementById('productFormErrors').innerHTML = '';

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
                        productAdvancedCollapse.show();
                    }

                    throw new Error(data.message || 'Không thể lưu sản phẩm.');
                }

                productModal.hide();
                showProductAlert(data.message);
                loadProductTable();
            })
            .catch(error => {
                if (!document.getElementById('productFormErrors').innerHTML) {
                    renderFormErrors({ general: [error.message] });
                }
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonHtml;
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
