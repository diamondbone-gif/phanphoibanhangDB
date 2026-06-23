<div class="modal fade" id="modalImportStock" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="importStockForm">
                <div class="modal-header">
                    <h5 class="modal-title">Lập Phiếu Nhập Kho / Cập nhật Lô</h5>
                    <button class="btn-close" data-bs-dismiss="modal" type="button"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                Chọn sản phẩm <span class="text-danger">*</span>
                            </label>

                            <select class="form-select" id="import_product_id" name="product_id" required>
                                <option value="">-- Chọn sản phẩm --</option>

                                @foreach ($productOptions as $item)
                                <option value="{{ $item->id }}">
                                    {{ $item->product_name }} - {{ $item->product_code }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Số lô hàng <span class="text-danger">*</span>
                            </label>

                            <input type="text" class="form-control" name="batch_number" placeholder="VD: LO-0626"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Ngày sản xuất
                            </label>

                            <input type="date" class="form-control" name="manufacture_date">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Hạn sử dụng <span class="text-danger">*</span>
                            </label>

                            <input type="date" class="form-control" name="expiry_date" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Số lượng nhập <span class="text-danger">*</span>
                            </label>

                            <input type="number" class="form-control" name="quantity" placeholder="100" min="1"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Nhà cung cấp / Nguồn
                            </label>

                            <input type="text" class="form-control" name="source" placeholder="Xưởng sản xuất...">
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                Ghi chú phiếu nhập
                            </label>

                            <textarea class="form-control" name="note" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-light border" data-bs-dismiss="modal" type="button">
                        Hủy
                    </button>

                    <button class="btn btn-primary" type="button" onclick="saveImportStock()">
                        <i class="fa-solid fa-check me-1"></i> Lưu lô hàng
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>