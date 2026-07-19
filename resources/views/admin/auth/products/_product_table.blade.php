<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 product-table">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Mã SKU</th>
                        <th>Sản phẩm</th>
                        <th>Danh mục</th>
                        <th class="text-end">Số lượng</th>
                        <th>Đơn vị tính</th>
                        <th class="text-end">Giá bán</th>
                        <th>Lô gần nhất</th>
                        <th>Hạn sử dụng</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-end pe-3">Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($products as $product)
                    @php
                    $latestBatch = $product->latestBatch;

                    $quantityClass = 'text-dark';

                    if ((int) $product->total_quantity <= 0) { $quantityClass='text-danger' ; } elseif ( (int)
                        $product->min_quantity_alert > 0 &&
                        (int) $product->total_quantity <= (int) $product->min_quantity_alert
                            ) {
                            $quantityClass = 'text-warning';
                            }
                            @endphp

                            <tr>
                                <td class="ps-3 fw-semibold" data-label="Mã SKU">
                                    {{ $product->product_code }}
                                </td>

                                <td data-label="Sản phẩm">
                                    <div class="d-flex align-items-center gap-3">
                                        @if($product->image_url)
                                        <img src="{{ $product->image_url }}" class="product-thumb"
                                            alt="{{ $product->product_name }}">
                                        @else
                                        <div class="product-thumb-placeholder">
                                            <i class="fa-solid fa-box"></i>
                                        </div>
                                        @endif

                                        <div>
                                            <div class="fw-bold">
                                                {{ $product->product_name }}
                                            </div>

                                            @if($product->short_description)
                                            <small class="text-muted">
                                                {{ $product->short_description }}
                                            </small>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td data-label="Danh mục">
                                    {{ optional($product->category)->name ?? 'Chưa phân loại' }}
                                </td>

                                <td class="text-end fw-bold {{ $quantityClass }}" data-label="Số lượng">
                                    {{ number_format((int) $product->total_quantity) }}
                                </td>

                                <td data-label="Đơn vị tính">
                                    {{ $product->unit_name ?: '---' }}
                                </td>

                                <td class="text-end fw-semibold text-danger" data-label="Giá bán">
                                    {{ number_format((float) $product->price, 0, ',', '.') }}đ
                                </td>

                                <td data-label="Lô gần nhất">
                                    @if($latestBatch)
                                    <span class="badge badge-soft-secondary">
                                        {{ $latestBatch->batch_number }}
                                    </span>
                                    @else
                                    <span class="text-muted">Chưa có</span>
                                    @endif
                                </td>

                                <td data-label="Hạn sử dụng">
                                    @if($latestBatch && $latestBatch->expiry_date)
                                    {{ $latestBatch->expiry_date->format('d/m/Y') }}
                                    @else
                                    <span class="text-muted">---</span>
                                    @endif
                                </td>

                                <td class="text-center" data-label="Trạng thái">
                                    <div class="d-flex align-items-center justify-content-lg-center">
                                        <div class="form-check form-switch m-0 d-flex justify-content-center p-0">
                                            <input class="form-check-input table-switch m-0" type="checkbox"
                                                role="switch" onchange="toggleProductStatus('{{ $product->id }}')"
                                                @checked($product->is_active)
                                            >
                                        </div>

                                        @if($product->is_active)
                                        <span class="ms-2 small text-success fw-medium">
                                            Hoạt động
                                        </span>
                                        @else
                                        <span class="ms-2 small text-muted">
                                            Đã ẩn
                                        </span>
                                        @endif
                                    </div>
                                </td>

                                <td class="text-end pe-3" data-label="Thao tác">
                                    <button class="btn btn-sm btn-light border text-primary me-1" type="button"
                                        title="Sửa" onclick="openEditProductModal('{{ $product->id }}')">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>

                                    <button class="btn btn-sm btn-light border text-danger" type="button" title="Xóa"
                                        onclick="deleteProduct('{{ $product->id }}')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    Chưa có sản phẩm nào.
                                </td>
                            </tr>
                            @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($products->hasPages())
    <div class="card-footer bg-white">
        {{ $products->links() }}
    </div>
    @endif
</div>
