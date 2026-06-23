@extends('admin.auth.dashboardAmin')

@section('title', 'Sửa khách hàng')

@section('admin_content')
@php
$detail = $customer->detail;

$sourceChannels = $sourceChannels ?? collect();
$buyForOptions = $buyForOptions ?? collect();
$products = $products ?? collect();
$customerNeeds = $customerNeeds ?? collect();

$customerKind = old('customer_kind', $customerKind ?? 'self');

$customerName = old('full_name', data_get($customer, 'full_name'));
$customerPhone = old('phone', data_get($customer, 'phone'));
$customerEmail = old('email', data_get($customer, 'email'));
$customerGender = old('gender', data_get($customer, 'gender'));

$rawBirthDate = data_get($customer, 'birth_date')
?: data_get($customer, 'birthday')
?: data_get($customer, 'date_of_birth');

if ($rawBirthDate instanceof \Carbon\Carbon) {
$rawBirthDate = $rawBirthDate->format('Y-m-d');
}

$customerBirthDate = old('birth_date', $rawBirthDate);

$province = old('province', data_get($detail, 'province'));
$district = old('district', data_get($detail, 'district'));
$ward = old('ward', data_get($detail, 'ward'));
$address = old('address', data_get($detail, 'address'));

$sourceChannelId = old('source_channel_id', data_get($detail, 'source_channel_id'));
$medicalNote = old('medical_note', data_get($detail, 'medical_note'));
$buyForOptionId = old('buy_for_option_id', data_get($detail, 'buy_for_option_id'));
$interestedProductId = old('interested_product_id', data_get($detail, 'interested_product_id'));
$consultationNote = old('consultation_note', data_get($detail, 'consultation_note'));
$customerNote = old('note', data_get($customer, 'note'));

$selectedNeedId = old('customer_need_id', $selectedNeedId ?? null);
$referrerPhone = old('referrer_phone', $currentReferrerPhone ?? '');
$commissionRate = old('commission_rate', $currentCommissionRate ?? 5);
@endphp

<style>
    .customer-form-card {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        overflow: hidden;
        background: #fff;
    }

    .customer-form-card .card-header {
        background: #fff;
        border-bottom: 0;
        padding: 18px 18px 0;
    }

    .customer-form-title {
        font-weight: 800;
        color: #0d6efd;
        font-size: 1.15rem;
    }

    .customer-form-card .card-body {
        padding: 18px;
    }

    .customer-type-option {
        position: relative;
        cursor: pointer;
        width: 100%;
    }

    .customer-type-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .customer-type-box {
        border: 1px solid #d9e3f0;
        border-radius: 16px;
        padding: 22px 18px;
        text-align: center;
        transition: all 0.18s ease;
        background: #fff;
        min-height: 86px;
    }

    .customer-type-box strong {
        display: block;
        color: #0f172a;
        font-weight: 800;
        margin-bottom: 6px;
        font-size: 1.02rem;
    }

    .customer-type-box span {
        color: #64748b;
        font-size: 0.92rem;
    }

    .customer-type-option input:checked+.customer-type-box {
        border-color: #0d6efd;
        background: #eaf2ff;
        box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.22);
    }

    .sub-box {
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        padding: 16px;
    }

    .text-red-label {
        color: #dc3545;
    }
</style>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ route('admin.customers.index') }}">Khách hàng</a>
        </li>
        <li class="breadcrumb-item active">Sửa khách hàng</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h3 class="mb-1">Sửa khách hàng</h3>
        <div class="text-muted">
            {{ $customer->full_name }} - {{ $customer->phone }}
        </div>
    </div>

    <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-light border">
        <i class="fa-solid fa-arrow-left"></i> Quay lại
    </a>
</div>

@include('admin.auth.partials.alerts')

<form method="POST" action="{{ route('admin.customers.update', $customer) }}">
    @csrf
    @method('PUT')

    <div class="card customer-form-card mb-3">
        <div class="card-header">
            <div class="customer-form-title">
                <i class="fa-solid fa-id-card me-2"></i>Thông tin khách hàng
            </div>
        </div>

        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">
                        Họ tên khách hàng <span class="text-danger">*</span>
                    </label>

                    <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror"
                        value="{{ $customerName }}" placeholder="Nhập họ và tên" required>

                    @error('full_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        Số điện thoại <span class="text-danger">*</span>
                    </label>

                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                        value="{{ $customerPhone }}" placeholder="Nhập số điện thoại" required>

                    <div class="form-text">Mã khách hàng sẽ được lấy theo số điện thoại.</div>

                    @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>

                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                        value="{{ $customerEmail }}" placeholder="Nhập email">

                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Giới tính</label>

                    <select name="gender" class="form-select">
                        <option value="">Chọn giới tính</option>
                        <option value="male" @selected($customerGender==='male' || $customerGender==='Nam' )>Nam
                        </option>
                        <option value="female" @selected($customerGender==='female' || $customerGender==='Nữ' )>Nữ
                        </option>
                        <option value="other" @selected($customerGender==='other' || $customerGender==='Khác' )>Khác
                        </option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Ngày tháng năm sinh</label>

                    <input type="date" name="birth_date" class="form-control" value="{{ $customerBirthDate }}">

                    <div class="form-text">Lưu đầy đủ ngày / tháng / năm sinh của khách hàng.</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Tỉnh / Thành phố</label>

                    <input type="text" name="province" class="form-control" value="{{ $province }}"
                        placeholder="Nhập Tỉnh/Thành">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Quận / Huyện</label>

                    <input type="text" name="district" class="form-control" value="{{ $district }}"
                        placeholder="Nhập Quận/Huyện">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Phường / Xã</label>

                    <input type="text" name="ward" class="form-control" value="{{ $ward }}"
                        placeholder="Nhập Phường/Xã">
                </div>

                <div class="col-12">
                    <label class="form-label">Địa chỉ chi tiết</label>

                    <input type="text" name="address" class="form-control" value="{{ $address }}"
                        placeholder="Số nhà, tên đường...">
                </div>

                <div class="col-12">
                    <label class="form-label text-red-label">
                        Tình trạng bệnh lý / Ghi chú đặc biệt của khách
                    </label>

                    <textarea name="medical_note" class="form-control border-danger" rows="3"
                        placeholder="Ví dụ: Khách bị tiểu đường, dị ứng thành phần X, hay đau dạ dày...">{{ $medicalNote }}</textarea>

                    <div class="form-text">Ghi chú nhanh các vấn đề sức khỏe để lưu ý khi tư vấn.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card customer-form-card mb-3">
        <div class="card-header">
            <div class="customer-form-title">
                <i class="fa-solid fa-tags me-2"></i>Loại khách hàng <span class="text-danger">*</span>
            </div>
        </div>

        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="customer-type-option">
                        <input type="radio" name="customer_kind" value="self" @checked($customerKind==='self' )
                            onchange="toggleCustomerKind()">

                        <div class="customer-type-box">
                            <strong>Khách tự tìm đến</strong>
                            <span>Khách tự biết đến công ty qua quảng cáo, MXH...</span>
                        </div>
                    </label>
                </div>

                <div class="col-md-6">
                    <label class="customer-type-option">
                        <input type="radio" name="customer_kind" value="ctv" @checked($customerKind==='ctv' )
                            onchange="toggleCustomerKind()">

                        <div class="customer-type-box">
                            <strong>Khách do CTV giới thiệu</strong>
                            <span>Khách được CTV hoặc khách hàng cũ giới thiệu</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="sub-box" id="sourceBox">
                <div class="fw-bold mb-3">Thông tin nhận diện</div>

                <label class="form-label">
                    Khách biết đến từ đâu? <span class="text-danger">*</span>
                </label>

                <select name="source_channel_id" class="form-select">
                    <option value="">-- Chọn thông tin nhận diện --</option>

                    @foreach($sourceChannels as $channel)
                    <option value="{{ $channel->id }}" @selected((string) $sourceChannelId===(string) $channel->id)>
                        {{ $channel->name }}
                    </option>
                    @endforeach
                </select>

                <div class="form-text">
                    Dữ liệu này được lấy từ mục DS Ghi chú ban đầu → Thông tin nhận diện.
                </div>
            </div>

            <div class="sub-box d-none" id="referrerBox">
                <div class="fw-bold mb-3">Thông tin CTV/người giới thiệu</div>

                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">
                            Số điện thoại CTV/người giới thiệu <span class="text-danger">*</span>
                        </label>

                        <input type="text" name="referrer_phone" id="referrerPhoneInput" class="form-control"
                            value="{{ $referrerPhone }}" placeholder="Nhập SĐT người giới thiệu">
                    </div>

                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-primary w-100" onclick="checkReferrer()">
                            Kiểm tra CTV
                        </button>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tỷ lệ hoa hồng</label>

                        <input type="number" name="commission_rate" class="form-control" min="0" max="100" step="0.01"
                            value="{{ $commissionRate }}" placeholder="VD: 5">
                    </div>
                </div>

                <div id="referrerCheckResult" class="mt-3"></div>
            </div>
        </div>
    </div>

    <div class="card customer-form-card mb-3">
        <div class="card-header">
            <div class="customer-form-title">
                <i class="fa-solid fa-clipboard-list me-2"></i>Nhu cầu / Ghi chú ban đầu
            </div>
        </div>

        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Khách mua cho ai?</label>

                    <select name="buy_for_option_id" class="form-select">
                        <option value="">-- Chọn --</option>

                        @foreach($buyForOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) $buyForOptionId===(string) $option->id)>
                            {{ $option->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Sản phẩm quan tâm</label>

                    <select name="interested_product_id" class="form-select">
                        <option value="">-- Chọn --</option>

                        @foreach($products as $product)
                        <option value="{{ $product->id }}" @selected((string) $interestedProductId===(string) $product->
                            id)>
                            {{ $product->product_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Nhu cầu quan tâm</label>

                    <select name="customer_need_id" class="form-select">
                        <option value="">-- Chọn --</option>

                        @foreach($customerNeeds as $need)
                        <option value="{{ $need->id }}" @selected((string) $selectedNeedId===(string) $need->id)>
                            {{ $need->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <label class="form-label">Ghi chú tư vấn ban đầu</label>

            <textarea name="consultation_note" class="form-control" rows="4"
                placeholder="Ví dụ: Khách mua cho mẹ 65 tuổi, đang quan tâm sản phẩm hỗ trợ xương khớp...">{{ $consultationNote }}</textarea>

            <div class="mt-3">
                <label class="form-label">Ghi chú chung</label>

                <textarea name="note" class="form-control" rows="3"
                    placeholder="Ghi chú thêm về khách hàng...">{{ $customerNote }}</textarea>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-4">
        <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-light border">
            Hủy
        </a>

        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk me-1"></i>
            Cập nhật khách hàng
        </button>
    </div>
</form>

<script>
    function toggleCustomerKind() {
        const checked = document.querySelector('input[name="customer_kind"]:checked');
        const value = checked ? checked.value : 'self';

        const sourceBox = document.getElementById('sourceBox');
        const referrerBox = document.getElementById('referrerBox');

        if (value === 'ctv') {
            sourceBox.classList.add('d-none');
            referrerBox.classList.remove('d-none');
        } else {
            sourceBox.classList.remove('d-none');
            referrerBox.classList.add('d-none');
        }
    }

    function checkReferrer() {
        const phoneInput = document.getElementById('referrerPhoneInput');
        const resultBox = document.getElementById('referrerCheckResult');
        const phone = phoneInput.value.trim();

        if (!phone) {
            resultBox.innerHTML = `
                <div class="alert alert-warning mb-0">
                    Vui lòng nhập số điện thoại CTV/người giới thiệu.
                </div>
            `;
            return;
        }

        resultBox.innerHTML = `
            <div class="alert alert-info mb-0">
                Đang kiểm tra CTV/người giới thiệu...
            </div>
        `;

        fetch(`{{ route('admin.customers.check-referrer') }}?phone=${encodeURIComponent(phone)}`, {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.found) {
                    resultBox.innerHTML = `
                        <div class="alert alert-success mb-0">
                            <strong>Đã tìm thấy:</strong> ${escapeHtml(data.full_name)} - ${escapeHtml(data.phone)}
                        </div>
                    `;
                } else {
                    resultBox.innerHTML = `
                        <div class="alert alert-danger mb-0">
                            ${escapeHtml(data.message || 'Không tìm thấy CTV/người giới thiệu.')}
                        </div>
                    `;
                }
            })
            .catch(function() {
                resultBox.innerHTML = `
                    <div class="alert alert-danger mb-0">
                        Có lỗi khi kiểm tra CTV/người giới thiệu.
                    </div>
                `;
            });
    }

    function escapeHtml(value) {
        return String(value || '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleCustomerKind();

        const referrerPhone = document.getElementById('referrerPhoneInput');

        if (referrerPhone && referrerPhone.value.trim()) {
            checkReferrer();
        }
    });
</script>
@endsection