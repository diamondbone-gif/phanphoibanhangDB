@extends('admin.auth.dashboardAmin')

@section('title', 'Quản lý Hoa hồng')

@section('admin_content')
@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/pages/auth-commissions-index.css') }}">
@endpush

<div class="commission-page">
    <div class="text-end mb-2">
        <a href="{{ route('admin.commissions.clawbacks.index') }}" class="btn btn-warning">
            <i class="fa-solid fa-rotate-left me-1"></i> Thu hồi do hoàn đơn
        </a>
    </div>
    <h1 class="commission-title">Quản lý Hoa hồng</h1>

    <div class="commission-summary-grid">
        <div class="commission-card bg-total">
            <div class="label">Tổng hoa hồng</div>
            <div class="value">{{ number_format($summary->total_commission ?? 0, 0, ',', '.') }}đ</div>
        </div>

        <div class="commission-card bg-paid">
            <div class="label">Hoa hồng đã chi</div>
            <div class="value">{{ number_format($summary->total_paid ?? 0, 0, ',', '.') }}đ</div>
        </div>

        <div class="commission-card bg-debt">
            <div class="label">Hoa hồng còn nợ</div>
            <div class="value">{{ number_format($summary->total_debt ?? 0, 0, ',', '.') }}đ</div>
        </div>
    </div>

    <div class="commission-box">
        <form method="GET" action="{{ route('admin.commissions.index') }}">
            <input type="text" name="q" value="{{ $search ?? '' }}" class="form-control"
                placeholder="Tìm kiếm theo tên hoặc số điện thoại...">
        </form>
    </div>

    <div class="commission-table-box">
        <div class="table-responsive">
            <table class="table commission-table align-middle">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Tên KH</th>
                        <th>SĐT</th>
                        <th>Tỉ lệ HH</th>
                        <th>Tổng HH</th>
                        <th>Đã chi</th>
                        <th>Còn nợ</th>
                        <th>Ngày chi</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($rows as $index => $row)
                    <tr>
                        <td>
                            {{ method_exists($rows, 'firstItem') ? (($rows->firstItem() ?? 1) + $index) : ($index + 1) }}
                        </td>

                        <td>{{ $row->full_name }}</td>

                        <td>{{ $row->phone }}</td>

                        <td>{{ number_format($row->commission_rate ?? 0, 0, ',', '.') }}%</td>

                        <td>{{ number_format($row->total_commission ?? 0, 0, ',', '.') }}đ</td>

                        <td>{{ number_format($row->total_paid ?? 0, 0, ',', '.') }}đ</td>

                        <td>{{ number_format($row->total_debt ?? 0, 0, ',', '.') }}đ</td>

                        <td>
                            @if(!empty($row->last_paid_at))
                            {{ \Carbon\Carbon::parse($row->last_paid_at)->format('d/m/Y') }}
                            @else
                            -
                            @endif
                        </td>

                        <td>
                            <div class="action-group">
                                <button type="button" class="btn btn-outline-primary btn-open-detail"
                                    data-id="{{ $row->ctv_id }}" data-name="{{ $row->full_name }}"
                                    data-phone="{{ $row->phone }}"
                                    data-address="{{ $row->full_address ?? $row->address ?? 'Chưa cập nhật' }}"
                                    data-total="{{ (float) ($row->total_commission ?? 0) }}"
                                    data-paid="{{ (float) ($row->total_paid ?? 0) }}"
                                    data-debt="{{ (float) ($row->total_debt ?? 0) }}" title="Xem chi tiết">
                                    <i class="fa-solid fa-eye"></i>
                                </button>

                                <button type="button" class="btn btn-success btn-open-pay" data-id="{{ $row->ctv_id }}"
                                    data-name="{{ $row->full_name }}" data-phone="{{ $row->phone }}"
                                    data-address="{{ $row->full_address ?? $row->address ?? 'Chưa cập nhật' }}"
                                    data-total="{{ (float) ($row->total_commission ?? 0) }}"
                                    data-paid="{{ (float) ($row->total_paid ?? 0) }}"
                                    data-debt="{{ (float) ($row->total_debt ?? 0) }}" title="Trả hoa hồng" @if((float)
                                    ($row->total_debt ?? 0) <= 0) disabled @endif>
                                        <i class="fa-solid fa-money-bill-wave"></i>
                                </button>

                                <button type="button" class="btn btn-outline-info btn-open-history"
                                    data-id="{{ $row->ctv_id }}" data-name="{{ $row->full_name }}"
                                    data-phone="{{ $row->phone }}"
                                    data-address="{{ $row->full_address ?? $row->address ?? 'Chưa cập nhật' }}"
                                    data-total="{{ (float) ($row->total_commission ?? 0) }}"
                                    data-paid="{{ (float) ($row->total_paid ?? 0) }}"
                                    data-debt="{{ (float) ($row->total_debt ?? 0) }}" title="Lịch sử thanh toán">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            Chưa có dữ liệu hoa hồng.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if(method_exists($rows, 'hasPages') && $rows->hasPages())
            <div class="commission-main-pagination d-flex justify-content-center mt-3">
                {{ $rows->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal chi tiết đơn hàng --}}
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <div class="modal-body">
                <div class="ctv-info-box">
                    <div class="row">
                        <div class="col-md-4">
                            <span class="info-label">Tên:</span>
                            <span class="info-value" id="detailName"></span>
                        </div>

                        <div class="col-md-4">
                            <span class="info-label">SĐT:</span>
                            <span class="info-value" id="detailPhone"></span>
                        </div>

                        <div class="col-md-4">
                            <span class="info-label">Địa chỉ:</span>
                            <span class="info-address" id="detailAddress"></span>
                        </div>
                    </div>
                </div>

                <div class="commission-modal-stats">
                    <div class="commission-modal-stat blue">
                        <div class="stat-label">Tổng tiền tất cả đơn hàng</div>
                        <div class="stat-value text-money-blue" id="detailTotalOrderAmount"></div>
                    </div>

                    <div class="commission-modal-stat green">
                        <div class="stat-label">Tổng hoa hồng</div>
                        <div class="stat-value text-money-green" id="detailTotalCommission"></div>
                    </div>

                    <div class="commission-modal-stat red">
                        <div class="stat-label">Hoa hồng còn nợ</div>
                        <div class="stat-value text-money-red" id="detailTotalDebt"></div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th style="width: 70px;">STT</th>
                                <th>Mã đơn</th>
                                <th>Sản phẩm</th>
                                <th>Tạm tính</th>
                                <th>Giảm</th>
                                <th>Tổng cuối</th>
                            </tr>
                        </thead>

                        <tbody id="detailOrderBody">
                            <tr>
                                <td colspan="6" class="text-center text-muted">Đang tải...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div id="detailPagination" class="pagination-mini"></div>
            </div>
        </div>
    </div>
</div>

{{-- Modal trả hoa hồng --}}
<div class="modal fade" id="payModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="payForm">
            @csrf

            <div class="modal-header modal-blue-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-money-bill-wave me-2"></i>
                    Trả tiền Hoa hồng
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="payCtvId">

                <div class="payment-info-box">
                    <div class="payment-info-line">
                        <span class="info-label">Tên:</span>
                        <span class="info-value" id="payName"></span>
                    </div>

                    <div class="payment-info-line">
                        <span class="info-label">SĐT:</span>
                        <span class="info-value" id="payPhone"></span>
                    </div>

                    <div class="payment-info-line">
                        <span class="info-label">Địa chỉ:</span>
                        <span class="info-address" id="payAddress"></span>
                    </div>
                </div>

                <div class="payment-stat-box">
                    <div class="row">
                        <div class="col-4 stat-col">
                            <div class="payment-stat-label">Tổng HH</div>
                            <div class="payment-stat-value text-money-blue" id="payTotal"></div>
                        </div>

                        <div class="col-4 stat-col">
                            <div class="payment-stat-label">Đã chi</div>
                            <div class="payment-stat-value text-money-green" id="payPaid"></div>
                        </div>

                        <div class="col-4 stat-col">
                            <div class="payment-stat-label">Còn nợ</div>
                            <div class="payment-stat-value text-money-red" id="payDebt"></div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Hình thức thanh toán *</label>
                    <select class="form-select" name="payout_type" id="payType">
                        <option value="all">Thanh toán toàn bộ</option>
                        <option value="installment">Thanh toán chia theo đợt</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Số tiền chi (VNĐ) *</label>
                    <input type="number" name="amount" id="payAmount" class="form-control" min="1000" step="1000"
                        required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ngày chi</label>
                        <input type="date" name="paid_date" id="payDate" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phương thức</label>
                        <select name="payment_method" id="paymentMethod" class="form-select" required>
                            <option value="Chuyển khoản">Chuyển khoản</option>
                            <option value="Tiền mặt">Tiền mặt</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ghi chú thanh toán</label>
                    <textarea name="note" id="payNote" rows="3" class="form-control"
                        placeholder="Ghi chú đợt thanh toán..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Hủy</button>

                <button type="submit" class="btn btn-primary px-4" id="paySubmitBtn">
                    <i class="fa-solid fa-check me-1"></i>
                    Xác nhận trả
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal lịch sử --}}
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lịch sử thanh toán hoa hồng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <div class="modal-body">
                <div class="ctv-info-box">
                    <div class="row">
                        <div class="col-md-4">
                            <span class="info-label">Khách hàng:</span>
                            <span class="info-value" id="historyName"></span>
                        </div>

                        <div class="col-md-4">
                            <span class="info-label">SĐT:</span>
                            <span class="info-value" id="historyPhone"></span>
                        </div>

                        <div class="col-md-4">
                            <span class="info-label">Địa chỉ:</span>
                            <span class="info-address" id="historyAddress"></span>
                        </div>
                    </div>
                </div>

                <div class="commission-modal-stats">
                    <div class="commission-modal-stat blue">
                        <div class="stat-label">Tổng hoa hồng</div>
                        <div class="stat-value text-money-blue" id="historyTotalCommission"></div>
                    </div>

                    <div class="commission-modal-stat green">
                        <div class="stat-label">Hoa hồng đã chi</div>
                        <div class="stat-value text-money-green" id="historyPaid"></div>
                    </div>

                    <div class="commission-modal-stat red">
                        <div class="stat-label">Hoa hồng còn nợ</div>
                        <div class="stat-value text-money-red" id="historyDebt"></div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Ngày chi</th>
                                <th>Số tiền</th>
                                <th>Hình thức</th>
                                <th>Phương thức</th>
                                <th>Ghi chú</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>

                        <tbody id="historyBody">
                            <tr>
                                <td colspan="7" class="text-center text-muted">Đang tải...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal sửa lịch sử thanh toán --}}
<div class="modal fade" id="editHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="editHistoryForm">
            @csrf

            <div class="modal-header modal-blue-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-pen-to-square me-2"></i>
                    Sửa lịch sử thanh toán
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="editHistoryCtvId">
                <input type="hidden" id="editHistoryPayoutId">

                <div class="mb-3">
                    <label class="form-label">Hình thức thanh toán *</label>
                    <select class="form-select" name="payout_type" id="editPayoutType">
                        <option value="all">Thanh toán toàn bộ</option>
                        <option value="installment">Thanh toán chia theo đợt</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Số tiền chi (VNĐ) *</label>
                    <input type="number" name="amount" id="editAmount" class="form-control" min="1000" step="1000"
                        required>
                    <small class="text-muted" id="editMaxAmountText"></small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ngày chi *</label>
                    <input type="date" name="paid_date" id="editPaidDate" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Phương thức *</label>
                    <select name="payment_method" id="editPaymentMethod" class="form-select" required>
                        <option value="Chuyển khoản">Chuyển khoản</option>
                        <option value="Tiền mặt">Tiền mặt</option>
                        <option value="Khác">Khác</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ghi chú thanh toán</label>
                    <textarea name="note" id="editNote" rows="3" class="form-control"
                        placeholder="Ghi chú đợt thanh toán..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Hủy</button>

                <button type="submit" class="btn btn-primary px-4" id="editHistorySubmitBtn">
                    <i class="fa-solid fa-floppy-disk me-1"></i>
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof bootstrap === 'undefined') {
            console.error(
                'Bootstrap JS chưa được load. Kiểm tra layout phải có bootstrap.bundle.min.js và @stack("scripts").'
            );
            return;
        }

        const csrfToken = '{{ csrf_token() }}';

        const detailUrlTemplate = "{{ route('admin.commissions.detail', ['ctv' => 'CTV_ID']) }}";
        const payUrlTemplate = "{{ route('admin.commissions.pay', ['ctv' => 'CTV_ID']) }}";
        const historyUrlTemplate = "{{ route('admin.commissions.history', ['ctv' => 'CTV_ID']) }}";
        const editHistoryUrlTemplate =
            "{{ route('admin.commissions.history.edit', ['ctv' => 'CTV_ID', 'payout' => 'PAYOUT_ID']) }}";
        const updateHistoryUrlTemplate =
            "{{ route('admin.commissions.history.update', ['ctv' => 'CTV_ID', 'payout' => 'PAYOUT_ID']) }}";

        const detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
        const payModal = new bootstrap.Modal(document.getElementById('payModal'));
        const historyModal = new bootstrap.Modal(document.getElementById('historyModal'));
        const editHistoryModal = new bootstrap.Modal(document.getElementById('editHistoryModal'));

        let currentDebtAmount = 0;
        let currentEditMaxAmount = 0;
        let detailOrdersData = [];
        const detailPerPage = 10;

        function ensureToastWrap() {
            let wrap = document.querySelector('.commission-toast-wrap');

            if (!wrap) {
                wrap = document.createElement('div');
                wrap.className = 'commission-toast-wrap';
                document.body.appendChild(wrap);
            }

            return wrap;
        }

        function showToast(message, type = 'info') {
            const wrap = ensureToastWrap();
            const toast = document.createElement('div');
            toast.className = `commission-toast ${type}`;
            toast.textContent = message;
            wrap.appendChild(toast);

            setTimeout(function() {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-8px) scale(0.98)';
                toast.style.transition = '0.18s ease';
            }, 2800);

            setTimeout(function() {
                toast.remove();
            }, 3100);
        }

        function money(value) {
            const number = Number(value || 0);
            return new Intl.NumberFormat('vi-VN').format(number) + 'đ';
        }

        function dateVi(value) {
            if (!value) return '-';

            const raw = String(value);
            const match = raw.match(/^(\d{4})-(\d{2})-(\d{2})/);

            if (match) {
                return `${match[3]}/${match[2]}/${match[1]}`;
            }

            const date = new Date(value);

            if (Number.isNaN(date.getTime())) return '-';

            return date.toLocaleDateString('vi-VN');
        }

        function todayInputValue() {
            const date = new Date();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${date.getFullYear()}-${month}-${day}`;
        }

        function toInputDate(value) {
            if (!value) return todayInputValue();

            const raw = String(value);
            const match = raw.match(/^(\d{4})-(\d{2})-(\d{2})/);

            if (match) {
                return `${match[1]}-${match[2]}-${match[3]}`;
            }

            const date = new Date(value);

            if (Number.isNaN(date.getTime())) return todayInputValue();

            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${date.getFullYear()}-${month}-${day}`;
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function payoutTypeLabel(value) {
            return value === 'installment' ?
                'Thanh toán chia theo đợt' :
                'Thanh toán toàn bộ';
        }

        function buildUrl(template, ctvId, payoutId = null) {
            let url = template.replace('CTV_ID', encodeURIComponent(ctvId));

            if (payoutId !== null) {
                url = url.replace('PAYOUT_ID', encodeURIComponent(payoutId));
            }

            return url;
        }

        function getArrayFromResponse(data, primaryKey) {
            if (Array.isArray(data?.[primaryKey])) return data[primaryKey];
            if (Array.isArray(data?.data)) return data.data;
            if (Array.isArray(data)) return data;
            return [];
        }

        function normalizePerson(person, fallback = {}) {
            const item = person || {};

            return {
                full_name: item.full_name ||
                    item.name ||
                    item.customer_name ||
                    item.ctv_name ||
                    fallback.full_name ||
                    fallback.name ||
                    '',

                phone: item.phone ||
                    item.phone_number ||
                    item.mobile ||
                    item.tel ||
                    fallback.phone ||
                    '',

                full_address: item.full_address ||
                    item.address ||
                    item.customer_address ||
                    item.ctv_address ||
                    fallback.full_address ||
                    fallback.address ||
                    'Chưa cập nhật'
            };
        }

        function normalizeSummary(summary, fallback = {}) {
            const item = summary || {};

            return {
                total_commission: Number(
                    item.total_commission ??
                    item.total ??
                    item.commission_total ??
                    fallback.total_commission ??
                    fallback.total ??
                    0
                ),

                total_paid: Number(
                    item.total_paid ??
                    item.paid ??
                    item.paid_amount ??
                    fallback.total_paid ??
                    fallback.paid ??
                    0
                ),

                total_debt: Number(
                    item.total_debt ??
                    item.debt ??
                    item.debt_amount ??
                    fallback.total_debt ??
                    fallback.debt ??
                    0
                )
            };
        }

        function normalizeOrder(item) {
            const productDiscount = Number(item.product_discount_amount || 0);
            const comboDiscount = Number(item.combo_discount_amount || 0);
            const orderDiscount = Number(item.order_discount_amount || 0);

            return {
                order_code: item.order_code || item.code || item.invoice_code || '-',
                product_text: item.product_text || item.products_text || item.product_name || item.products || '-',
                subtotal_amount: Number(item.subtotal_amount || item.subtotal || item.total_before_discount || 0),
                discount_amount: Number(item.discount_amount || productDiscount + comboDiscount + orderDiscount),
                final_amount: Number(item.final_amount || item.total_amount || item.grand_total || 0)
            };
        }

        function normalizeHistory(item) {
            return {
                id: item.id || item.payout_id,
                paid_at: item.paid_at || item.paid_date || item.created_at,
                total_amount: Number(item.total_amount || item.amount || 0),
                payout_type: item.payout_type || 'all',
                payout_type_label: item.payout_type_label || '',
                payment_method: item.payment_method || item.method || '',
                note: item.note || ''
            };
        }

        function setButtonBusy(button, busy) {
            if (!button) return;

            if (busy) {
                button.dataset.oldDisabled = button.disabled ? '1' : '0';
                button.disabled = true;
                button.classList.add('is-busy');
                return;
            }

            button.disabled = button.dataset.oldDisabled === '1';
            button.classList.remove('is-busy');
            delete button.dataset.oldDisabled;
        }

        function ajaxHeaders(extraHeaders = {}) {
            return {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache',
                ...extraHeaders
            };
        }

        async function requestJson(url, options = {}) {
            let response;

            try {
                response = await fetch(url, {
                    credentials: 'same-origin',
                    ...options,
                    headers: ajaxHeaders(options.headers || {})
                });
            } catch (networkError) {
                throw new Error('Không kết nối được tới server. Kiểm tra lại php artisan serve.');
            }

            const contentType = response.headers.get('content-type') || '';
            let data = null;

            if (contentType.includes('application/json')) {
                try {
                    data = await response.json();
                } catch (parseError) {
                    throw new Error('Server trả JSON không hợp lệ.');
                }
            } else {
                const text = await response.text();
                let message = 'Server đang trả về HTML thay vì JSON. Kiểm tra lại route hoặc lỗi backend.';

                if (response.redirected || response.status === 401 || text.includes('admin/login') || text
                    .includes('name="password"')) {
                    message = 'Phiên đăng nhập admin có thể đã hết hạn. Đăng nhập lại rồi thử lại.';
                } else if (response.status === 404) {
                    message = 'Không tìm thấy route xử lý thao tác này. Kiểm tra route admin.commissions.*';
                } else if (response.status === 419) {
                    message = 'Token bảo mật đã hết hạn. Tải lại trang rồi thử lại.';
                } else if (response.status >= 500 || text.includes('Exception') || text.includes(
                        'Stack trace')) {
                    message = 'Server đang bị lỗi xử lý. Mở laravel.log để xem chi tiết.';
                }

                throw new Error(message);
            }

            if (!response.ok) {
                throw new Error(data?.message || `Có lỗi xảy ra. Mã lỗi: ${response.status}`);
            }

            return data || {};
        }

        function renderErrorRow(bodyId, colspan, message) {
            document.getElementById(bodyId).innerHTML = `
            <tr>
                <td colspan="${colspan}" class="text-center commission-error-cell py-4">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                    ${escapeHtml(message)}
                </td>
            </tr>
        `;
        }

        function renderLoadingRow(bodyId, colspan, message = 'Đang tải...') {
            document.getElementById(bodyId).innerHTML = `
            <tr>
                <td colspan="${colspan}" class="text-center text-muted py-4">
                    <i class="fa-solid fa-spinner fa-spin me-1"></i>
                    ${escapeHtml(message)}
                </td>
            </tr>
        `;
        }

        function setDetailInfo(person, summary, totalOrderAmount = 0) {
            document.getElementById('detailName').textContent = person.full_name || '';
            document.getElementById('detailPhone').textContent = person.phone || '';
            document.getElementById('detailAddress').textContent = person.full_address || 'Chưa cập nhật';

            document.getElementById('detailTotalOrderAmount').textContent = money(totalOrderAmount);
            document.getElementById('detailTotalCommission').textContent = money(summary.total_commission);
            document.getElementById('detailTotalDebt').textContent = money(summary.total_debt);
        }

        function setHistoryInfo(person, summary) {
            document.getElementById('historyName').textContent = person.full_name || '';
            document.getElementById('historyPhone').textContent = person.phone || '';
            document.getElementById('historyAddress').textContent = person.full_address || 'Chưa cập nhật';

            document.getElementById('historyTotalCommission').textContent = money(summary.total_commission);
            document.getElementById('historyPaid').textContent = money(summary.total_paid);
            document.getElementById('historyDebt').textContent = money(summary.total_debt);
        }

        function renderDetailPage(page) {
            const body = document.getElementById('detailOrderBody');
            const pagination = document.getElementById('detailPagination');

            if (!detailOrdersData.length) {
                body.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        Chưa có đơn hàng hoa hồng.
                    </td>
                </tr>
            `;
                pagination.innerHTML = '';
                return;
            }

            const start = (page - 1) * detailPerPage;
            const rows = detailOrdersData.slice(start, start + detailPerPage);

            body.innerHTML = rows.map((item, index) => `
            <tr>
                <td>${start + index + 1}</td>
                <td>${escapeHtml(item.order_code)}</td>
                <td>${escapeHtml(item.product_text)}</td>
                <td>${money(item.subtotal_amount)}</td>
                <td>${money(item.discount_amount)}</td>
                <td>${money(item.final_amount)}</td>
            </tr>
        `).join('');

            const totalPages = Math.ceil(detailOrdersData.length / detailPerPage);

            if (totalPages <= 1) {
                pagination.innerHTML = '';
                return;
            }

            let buttons = '';

            for (let i = 1; i <= totalPages; i++) {
                buttons += `
                <button
                    type="button"
                    class="btn btn-sm ${i === page ? 'btn-primary' : 'btn-outline-primary'} detail-page-btn"
                    data-page="${i}"
                >
                    ${i}
                </button>
            `;
            }

            pagination.innerHTML = buttons;
        }

        document.addEventListener('click', async function(event) {
            const detailPageBtn = event.target.closest('.detail-page-btn');
            const detailBtn = event.target.closest('.btn-open-detail');
            const payBtn = event.target.closest('.btn-open-pay');
            const historyBtn = event.target.closest('.btn-open-history');
            const editHistoryBtn = event.target.closest('.btn-edit-history');

            if (detailPageBtn) {
                event.preventDefault();
                renderDetailPage(Number(detailPageBtn.dataset.page));
                return;
            }

            if (detailBtn) {
                event.preventDefault();
                setButtonBusy(detailBtn, true);

                const ctvId = detailBtn.dataset.id;
                const url = buildUrl(detailUrlTemplate, ctvId);

                const fallbackPerson = {
                    full_name: detailBtn.dataset.name || '',
                    phone: detailBtn.dataset.phone || '',
                    full_address: detailBtn.dataset.address || 'Chưa cập nhật'
                };

                const fallbackSummary = {
                    total_commission: Number(detailBtn.dataset.total || 0),
                    total_paid: Number(detailBtn.dataset.paid || 0),
                    total_debt: Number(detailBtn.dataset.debt || 0)
                };

                detailOrdersData = [];

                setDetailInfo(
                    normalizePerson(null, fallbackPerson),
                    normalizeSummary(null, fallbackSummary),
                    0
                );

                document.getElementById('detailPagination').innerHTML = '';
                renderLoadingRow('detailOrderBody', 6);

                detailModal.show();

                try {
                    const data = await requestJson(url);

                    const person = normalizePerson(data.ctv || data.customer || data.user,
                        fallbackPerson);
                    const summary = normalizeSummary(data.summary || data.ctv_summary || data,
                        fallbackSummary);

                    detailOrdersData = getArrayFromResponse(data, 'orders').map(normalizeOrder);

                    const totalOrderAmount = detailOrdersData.reduce(function(total, item) {
                        return total + Number(item.final_amount || 0);
                    }, 0);

                    setDetailInfo(person, summary, totalOrderAmount);
                    renderDetailPage(1);
                } catch (error) {
                    renderErrorRow('detailOrderBody', 6, error.message);
                } finally {
                    setButtonBusy(detailBtn, false);
                }

                return;
            }

            if (payBtn) {
                event.preventDefault();

                const debt = Number(payBtn.dataset.debt || 0);

                currentDebtAmount = debt;

                document.getElementById('payCtvId').value = payBtn.dataset.id;
                document.getElementById('payName').textContent = payBtn.dataset.name || '';
                document.getElementById('payPhone').textContent = payBtn.dataset.phone || '';
                document.getElementById('payAddress').textContent = payBtn.dataset.address ||
                    'Chưa cập nhật';

                document.getElementById('payTotal').textContent = money(payBtn.dataset.total);
                document.getElementById('payPaid').textContent = money(payBtn.dataset.paid);
                document.getElementById('payDebt').textContent = money(payBtn.dataset.debt);

                document.getElementById('payType').value = 'all';
                document.getElementById('payAmount').value = Math.round(debt);
                document.getElementById('payAmount').max = Math.round(debt);
                document.getElementById('payAmount').readOnly = true;
                document.getElementById('payDate').value = todayInputValue();
                document.getElementById('paymentMethod').value = 'Chuyển khoản';
                document.getElementById('payNote').value = '';

                payModal.show();
                return;
            }

            if (historyBtn) {
                event.preventDefault();
                setButtonBusy(historyBtn, true);

                const ctvId = historyBtn.dataset.id;
                const url = buildUrl(historyUrlTemplate, ctvId);

                const fallbackPerson = {
                    full_name: historyBtn.dataset.name || '',
                    phone: historyBtn.dataset.phone || '',
                    full_address: historyBtn.dataset.address || 'Chưa cập nhật'
                };

                const fallbackSummary = {
                    total_commission: Number(historyBtn.dataset.total || 0),
                    total_paid: Number(historyBtn.dataset.paid || 0),
                    total_debt: Number(historyBtn.dataset.debt || 0)
                };

                setHistoryInfo(
                    normalizePerson(null, fallbackPerson),
                    normalizeSummary(null, fallbackSummary)
                );

                renderLoadingRow('historyBody', 7);

                historyModal.show();

                try {
                    const data = await requestJson(url);

                    const person = normalizePerson(data.ctv || data.customer || data.user,
                        fallbackPerson);
                    const summary = normalizeSummary(data.summary || data.ctv_summary || data,
                        fallbackSummary);

                    setHistoryInfo(person, summary);

                    const body = document.getElementById('historyBody');
                    const histories = getArrayFromResponse(data, 'histories').map(normalizeHistory);

                    if (!histories.length) {
                        body.innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Chưa có lịch sử thanh toán.
                            </td>
                        </tr>
                    `;
                        return;
                    }

                    body.innerHTML = histories.map((item, index) => {
                        const typeLabel = item.payout_type_label || payoutTypeLabel(item
                            .payout_type || 'all');

                        return `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${dateVi(item.paid_at)}</td>
                            <td>${money(item.total_amount)}</td>
                            <td>${escapeHtml(typeLabel)}</td>
                            <td>${escapeHtml(item.payment_method || '')}</td>
                            <td>${escapeHtml(item.note || '')}</td>
                            <td>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-warning btn-edit-history"
                                    data-ctv="${escapeHtml(ctvId)}"
                                    data-payout="${escapeHtml(item.id)}"
                                >
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    Sửa
                                </button>
                            </td>
                        </tr>
                    `;
                    }).join('');
                } catch (error) {
                    renderErrorRow('historyBody', 7, error.message);
                } finally {
                    setButtonBusy(historyBtn, false);
                }

                return;
            }

            if (editHistoryBtn) {
                event.preventDefault();
                setButtonBusy(editHistoryBtn, true);

                const ctvId = editHistoryBtn.dataset.ctv;
                const payoutId = editHistoryBtn.dataset.payout;
                const url = buildUrl(editHistoryUrlTemplate, ctvId, payoutId);

                document.getElementById('editHistoryCtvId').value = ctvId;
                document.getElementById('editHistoryPayoutId').value = payoutId;
                document.getElementById('editPayoutType').value = 'installment';
                document.getElementById('editAmount').value = '';
                document.getElementById('editAmount').readOnly = false;
                document.getElementById('editPaidDate').value = todayInputValue();
                document.getElementById('editPaymentMethod').value = 'Chuyển khoản';
                document.getElementById('editNote').value = '';
                document.getElementById('editMaxAmountText').textContent = '';

                editHistoryModal.show();

                try {
                    const data = await requestJson(url);
                    const payout = data.payout || data.history || {};

                    currentEditMaxAmount = Number(data.max_edit_amount || payout.total_amount || payout
                        .amount || 0);

                    document.getElementById('editPayoutType').value = payout.payout_type ||
                        'installment';
                    document.getElementById('editAmount').value = Math.round(Number(payout
                        .total_amount || payout.amount || 0));
                    document.getElementById('editAmount').max = Math.round(currentEditMaxAmount);
                    document.getElementById('editPaidDate').value = toInputDate(payout.paid_at || payout
                        .paid_date);
                    document.getElementById('editPaymentMethod').value = payout.payment_method || payout
                        .method || 'Chuyển khoản';
                    document.getElementById('editNote').value = payout.note || '';
                    document.getElementById('editMaxAmountText').textContent = 'Tối đa có thể sửa: ' +
                        money(currentEditMaxAmount);

                    if ((payout.payout_type || 'installment') === 'all') {
                        document.getElementById('editAmount').value = Math.round(currentEditMaxAmount);
                        document.getElementById('editAmount').readOnly = true;
                    }
                } catch (error) {
                    editHistoryModal.hide();
                    showToast(error.message, 'danger');
                } finally {
                    setButtonBusy(editHistoryBtn, false);
                }
            }
        });

        document.getElementById('payType').addEventListener('change', function() {
            const payAmountInput = document.getElementById('payAmount');

            if (this.value === 'all') {
                payAmountInput.value = Math.round(currentDebtAmount);
                payAmountInput.readOnly = true;
            } else {
                payAmountInput.value = '';
                payAmountInput.readOnly = false;
                payAmountInput.focus();
            }
        });

        document.getElementById('editPayoutType').addEventListener('change', function() {
            const editAmountInput = document.getElementById('editAmount');

            if (this.value === 'all') {
                editAmountInput.value = Math.round(currentEditMaxAmount);
                editAmountInput.readOnly = true;
            } else {
                editAmountInput.readOnly = false;
                editAmountInput.focus();
            }
        });

        document.getElementById('payForm').addEventListener('submit', async function(event) {
            event.preventDefault();

            const ctvId = document.getElementById('payCtvId').value;
            const url = buildUrl(payUrlTemplate, ctvId);
            const submitBtn = document.getElementById('paySubmitBtn');
            const amount = Number(document.getElementById('payAmount').value || 0);

            if (amount <= 0) {
                showToast('Số tiền chi phải lớn hơn 0.', 'danger');
                return;
            }

            if (amount > currentDebtAmount) {
                showToast('Số tiền chi không được lớn hơn số hoa hồng còn nợ.', 'danger');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang lưu...';

            try {
                const formData = new FormData(this);

                const data = await requestJson(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData
                });

                showToast(data.message || 'Đã lưu thanh toán hoa hồng.', 'success');

                setTimeout(function() {
                    window.location.reload();
                }, 650);
            } catch (error) {
                showToast(error.message, 'danger');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Xác nhận trả';
            }
        });

        document.getElementById('editHistoryForm').addEventListener('submit', async function(event) {
            event.preventDefault();

            const ctvId = document.getElementById('editHistoryCtvId').value;
            const payoutId = document.getElementById('editHistoryPayoutId').value;
            const url = buildUrl(updateHistoryUrlTemplate, ctvId, payoutId);
            const submitBtn = document.getElementById('editHistorySubmitBtn');
            const amount = Number(document.getElementById('editAmount').value || 0);

            if (amount <= 0) {
                showToast('Số tiền chi phải lớn hơn 0.', 'danger');
                return;
            }

            if (currentEditMaxAmount > 0 && amount > currentEditMaxAmount) {
                showToast('Số tiền sửa không được lớn hơn mức tối đa cho phép.', 'danger');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang lưu...';

            try {
                const formData = new FormData(this);
                formData.append('_method', 'PUT');

                const data = await requestJson(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData
                });

                showToast(data.message || 'Đã cập nhật lịch sử thanh toán.', 'success');

                setTimeout(function() {
                    window.location.reload();
                }, 650);
            } catch (error) {
                showToast(error.message, 'danger');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Lưu thay đổi';
            }
        });
    });
</script>
@endpush
