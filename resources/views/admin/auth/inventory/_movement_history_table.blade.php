<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Thời gian</th>
                <th>Mã SP</th>
                <th>Sản phẩm</th>
                <th>Số lô</th>
                <th>Loại</th>
                <th class="text-end">SL thay đổi</th>
                <th class="text-end">Trước</th>
                <th class="text-end">Sau</th>
                <th>Ghi chú</th>
            </tr>
        </thead>

        <tbody>
            @forelse($movements as $movement)
            <tr>
                <td>
                    {{ $movement->movement_date ? $movement->movement_date->format('d/m/Y H:i') : $movement->created_at->format('d/m/Y H:i') }}
                </td>

                <td class="fw-bold">
                    {{ optional($movement->product)->product_code ?? '---' }}
                </td>

                <td>
                    {{ optional($movement->product)->product_name ?? 'Không xác định' }}
                </td>

                <td>
                    @if($movement->batch)
                    <span class="batch-badge">
                        {{ $movement->batch->batch_number }}
                    </span>
                    @else
                    ---
                    @endif
                </td>

                <td>
                    @if($movement->movement_type === 'import')
                    <span class="badge bg-success">Nhập kho</span>
                    @elseif($movement->movement_type === 'export')
                    <span class="badge bg-danger">Xuất kho</span>
                    @elseif($movement->movement_type === 'delete_batch')
                    <span class="badge bg-danger">Xóa lô</span>
                    @else
                    <span class="badge bg-warning text-dark">Điều chỉnh</span>
                    @endif
                </td>

                <td class="text-end fw-bold {{ $movement->quantity < 0 ? 'text-danger' : 'text-success' }}">
                    {{ $movement->quantity > 0 ? '+' : '' }}{{ number_format((int) $movement->quantity) }}
                </td>

                <td class="text-end">
                    {{ number_format((int) $movement->before_quantity) }}
                </td>

                <td class="text-end">
                    {{ number_format((int) $movement->after_quantity) }}
                </td>

                <td>
                    {{ $movement->note ?: '---' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center text-muted py-4">
                    Chưa có lịch sử nhập/xuất kho.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>