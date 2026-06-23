<!-- <div class="inventory-table-card">
    <div class="table-responsive">
        <table class="table inventory-table align-middle">
            <thead>
                <tr>
                    <th class="ps-3">Mã SP</th>
                    <th>Tên sản phẩm</th>
                    <th>Thể loại</th>
                    <th class="text-end">SL ban đầu</th>
                    <th class="text-end">SL còn lại</th>
                    <th>Số lô</th>
                    <th>NSX</th>
                    <th>HSD</th>
                    <th>Trạng thái</th>
                    <th class="text-end pe-3">Thao tác</th>
                </tr>
            </thead>

            <tbody>
                @forelse($batches as $batch)
                @php
                $product = $batch->product;
                $categoryName = optional(optional($product)->category)->name;
                @endphp

                <tr>
                    <td class="ps-3 fw-bold" data-label="Mã SP">
                        {{ optional($product)->product_code ?? '---' }}
                    </td>

                    <td class="fw-bold" data-label="Tên sản phẩm">
                        {{ optional($product)->product_name ?? 'Không xác định' }}
                    </td>

                    <td data-label="Thể loại">
                        {{ optional($product)->unit_name ?: ($categoryName ?: '---') }}
                    </td>

                    <td class="text-end fw-bold" data-label="SL ban đầu">
                        {{ number_format((int) $batch->initial_quantity) }}
                    </td>

                    <td class="text-end fw-bold {{ (int) $batch->current_quantity <= 0 ? 'text-danger' : '' }}"
                        data-label="SL còn lại">
                        {{ number_format((int) $batch->current_quantity) }}
                    </td>

                    <td data-label="Số lô">
                        <span class="batch-badge">
                            {{ $batch->batch_number }}
                        </span>
                    </td>

                    <td data-label="NSX">
                        {{ $batch->manufacture_date ? $batch->manufacture_date->format('d/m/Y') : '---' }}
                    </td>

                    <td data-label="HSD">
                        @if($batch->expiry_date)
                        <span
                            class="{{ $batch->expiry_date->isPast() ? 'text-danger fw-bold' : 'text-success fw-semibold' }}">
                            {{ $batch->expiry_date->format('d/m/Y') }}
                        </span>
                        @else
                        <span class="text-muted">---</span>
                        @endif
                    </td>

                    <td data-label="Trạng thái">
                        <div class="batch-status-wrap">
                            <input class="form-check-input table-switch" type="checkbox"
                                data-batch-id="{{ $batch->id }}" onchange="toggleBatchStatus(this.dataset.batchId)"
                                @checked($batch->is_active)>

                            <span class="badge {{ $batch->status_badge_class }}">
                                {{ $batch->status_text }}
                            </span>
                        </div>
                    </td>

                    <td class="text-end pe-3" data-label="Thao tác">
                        <button class="action-btn edit me-1" type="button" title="Sửa lô hàng"
                            data-batch-id="{{ $batch->id }}" onclick="openEditBatchModal(this.dataset.batchId)">
                            <i class="fa-solid fa-pen"></i>
                        </button>

                        <button class="action-btn delete" type="button" title="Xóa lô hàng"
                            data-batch-id="{{ $batch->id }}" onclick="deleteBatch(this.dataset.batchId)">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">
                        Chưa có dữ liệu tồn kho. Vui lòng thêm sản phẩm trước, sau đó lập phiếu nhập kho.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($batches->hasPages())
    <div class="p-3 bg-white border-top">
        {{ $batches->links() }}
    </div>
    @endif
</div> -->


<div class="inventory-table-card">
    <div class="table-responsive">
        <table class="table inventory-table align-middle">
            <thead>
                <tr>
                    <th class="ps-3">Mã SP</th>
                    <th>Tên sản phẩm</th>
                    <th>Thể loại</th>
                    <th class="text-end">SL ban đầu</th>
                    <th class="text-end">SL còn lại</th>
                    <th>Số lô</th>
                    <th>NSX</th>
                    <th>HSD</th>
                    <th>Trạng thái</th>
                    <th class="text-end pe-3">Thao tác</th>
                </tr>
            </thead>

            <tbody>
                @forelse($batches as $batch)
                @php
                $product = $batch->product;
                $categoryName = optional(optional($product)->category)->name;
                @endphp

                <tr>
                    <td class="ps-3 fw-bold" data-label="Mã SP">
                        {{ optional($product)->product_code ?? '---' }}
                    </td>

                    <td class="fw-bold" data-label="Tên sản phẩm">
                        {{ optional($product)->product_name ?? 'Không xác định' }}
                    </td>

                    <td data-label="Thể loại">
                        {{ optional($product)->unit_name ?: ($categoryName ?: '---') }}
                    </td>

                    <td class="text-end fw-bold" data-label="SL ban đầu">
                        {{ number_format((int) $batch->initial_quantity) }}
                    </td>

                    <td class="text-end fw-bold {{ (int) $batch->current_quantity <= 0 ? 'text-danger' : '' }}"
                        data-label="SL còn lại">
                        {{ number_format((int) $batch->current_quantity) }}
                    </td>

                    <td data-label="Số lô">
                        <span class="batch-badge">
                            {{ $batch->batch_number }}
                        </span>
                    </td>

                    <td data-label="NSX">
                        {{ $batch->manufacture_date ? $batch->manufacture_date->format('d/m/Y') : '---' }}
                    </td>

                    <td data-label="HSD">
                        @if($batch->expiry_date)
                        <span
                            class="{{ $batch->expiry_date->isPast() ? 'text-danger fw-bold' : 'text-success fw-semibold' }}">
                            {{ $batch->expiry_date->format('d/m/Y') }}
                        </span>
                        @else
                        <span class="text-muted">---</span>
                        @endif
                    </td>

                    <td data-label="Trạng thái">
                        <div class="batch-status-wrap">
                            <input class="form-check-input table-switch" type="checkbox"
                                data-batch-id="{{ $batch->id }}" onchange="toggleBatchStatus(this.dataset.batchId)"
                                @checked($batch->is_active)
                            >

                            <span class="badge {{ $batch->status_badge_class }}">
                                {{ $batch->status_text }}
                            </span>
                        </div>
                    </td>

                    <td class="text-end pe-3" data-label="Thao tác">
                        <div class="inventory-action-group">
                            <button class="table-action-btn action-edit" type="button" title="Sửa lô hàng"
                                data-batch-id="{{ $batch->id }}" onclick="openEditBatchModal(this.dataset.batchId)">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>

                            <button class="table-action-btn action-delete" type="button" title="Xóa lô hàng"
                                data-batch-id="{{ $batch->id }}" onclick="deleteBatch(this.dataset.batchId)">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">
                        Chưa có dữ liệu tồn kho. Vui lòng thêm sản phẩm trước, sau đó lập phiếu nhập kho.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($batches->hasPages())
    <div class="p-3 bg-white border-top">
        {{ $batches->links() }}
    </div>
    @endif
</div>