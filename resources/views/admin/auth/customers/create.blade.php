@extends('admin.auth.dashboardAmin')

@section('title', 'Thêm khách hàng')

@section('admin_content')
<div class="container-fluid customer-create-page">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.customers.index') }}">Khách hàng</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Thêm khách hàng</li>
        </ol>
    </nav>

    <h3 class="mb-4">Thêm khách hàng</h3>

    @if ($errors->any())
    <div class="alert alert-danger">
        <strong>Vui lòng kiểm tra lại thông tin:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.customers.store') }}" autocomplete="off">
        @csrf

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="section-title">
                    <i class="fa-regular fa-address-card me-2"></i>
                    Thông tin khách hàng
                </h5>

                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <label class="form-label">
                            Họ tên khách hàng <span class="text-danger">*</span>
                        </label>

                        <input class="form-control @error('full_name') is-invalid @enderror" name="full_name"
                            value="{{ old('full_name') }}" placeholder="Nhập họ và tên" type="text" maxlength="255"
                            required>

                        @error('full_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Số điện thoại <span class="text-danger">*</span>
                        </label>

                        <input class="form-control @error('phone') is-invalid @enderror" id="phoneInput" name="phone"
                            value="{{ old('phone') }}" placeholder="Nhập số điện thoại" type="text" maxlength="20"
                            inputmode="numeric" required>

                        @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <small class="text-muted d-block mt-1">
                            Mã khách hàng sẽ được lấy theo số điện thoại.
                        </small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Giới tính</label>

                        <select class="form-select @error('gender') is-invalid @enderror" name="gender">
                            <option value="">Chọn giới tính</option>
                            <option value="male" @selected(old('gender')==='male' )>Nam</option>
                            <option value="female" @selected(old('gender')==='female' )>Nữ</option>
                            <option value="other" @selected(old('gender')==='other' )>Khác</option>
                        </select>

                        @error('gender')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Ngày tháng năm sinh</label>

                        <input class="form-control @error('birth_date') is-invalid @enderror" name="birth_date"
                            value="{{ old('birth_date') }}" type="date">

                        @error('birth_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <small class="text-muted d-block mt-1">
                            Lưu đầy đủ ngày / tháng / năm sinh của khách hàng.
                        </small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tỉnh / Thành phố</label>

                        <input class="form-control" name="province" value="{{ old('province') }}"
                            placeholder="Nhập Tỉnh/Thành" type="text" maxlength="100">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Quận / Huyện</label>

                        <input class="form-control" name="district" value="{{ old('district') }}"
                            placeholder="Nhập Quận/Huyện" type="text" maxlength="100">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Phường / Xã</label>

                        <input class="form-control" name="ward" value="{{ old('ward') }}" placeholder="Nhập Phường/Xã"
                            type="text" maxlength="100">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Địa chỉ chi tiết</label>

                        <input class="form-control" name="address" value="{{ old('address') }}"
                            placeholder="Số nhà, tên đường..." type="text" maxlength="255">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label text-danger">
                            Tình trạng bệnh lý / Ghi chú đặc biệt của khách
                        </label>

                        <textarea class="form-control border-danger-subtle" name="medical_note" rows="2"
                            placeholder="Ví dụ: Khách bị tiểu đường, dị ứng thành phần X, hay đau dạ dày...">{{ old('medical_note') }}</textarea>

                        <small class="text-muted">
                            Ghi chú nhanh các vấn đề sức khỏe để lưu ý khi tư vấn.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="section-title">
                    <i class="fa-solid fa-tags me-2"></i>
                    Loại khách hàng <span class="text-danger">*</span>
                </h5>

                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label class="radio-card-label">
                            <input class="radio-card-input" type="radio" name="customer_source" value="direct"
                                @checked(old('customer_source', 'direct' )==='direct' )>

                            <div class="radio-card-content">
                                <div class="radio-card-title">Khách tự tìm đến</div>
                                <div class="radio-card-desc">
                                    Khách tự biết đến công ty qua quảng cáo, MXH...
                                </div>
                            </div>
                        </label>
                    </div>

                    <div class="col-md-6">
                        <label class="radio-card-label">
                            <input class="radio-card-input" type="radio" name="customer_source" value="ctv_referral"
                                @checked(old('customer_source')==='ctv_referral' )>

                            <div class="radio-card-content">
                                <div class="radio-card-title">Khách do CTV giới thiệu</div>
                                <div class="radio-card-desc">
                                    Khách được CTV hoặc khách hàng cũ giới thiệu
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                @error('customer_source')
                <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror

                <div class="direct-source-box mt-4" id="directSourceBox">
                    <h6 class="fw-bold mb-3">Thông tin nhận diện</h6>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">
                                Khách biết đến từ đâu? <span class="text-danger">*</span>
                            </label>

                            <select name="source_channel_id" id="sourceChannelSelect"
                                class="form-select @error('source_channel_id') is-invalid @enderror">
                                <option value="">-- Chọn thông tin nhận diện --</option>

                                @foreach(($sourceChannels ?? collect()) as $channel)
                                <option value="{{ $channel->id }}" @selected(old('source_channel_id')==$channel->id)>
                                    {{ $channel->name }}
                                </option>
                                @endforeach
                            </select>

                            @error('source_channel_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <small class="text-muted d-block mt-1">
                                Dữ liệu này được lấy từ mục DS Ghi chú ban đầu → Thông tin nhận diện.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="ctv-box mt-4" id="ctvReferralBox">
                    <h6 class="fw-bold mb-3">Thông tin CTV/người giới thiệu</h6>

                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">
                                Số điện thoại CTV/người giới thiệu <span class="text-danger">*</span>
                            </label>

                            <input class="form-control @error('referrer_phone') is-invalid @enderror"
                                id="referrerPhoneInput" name="referrer_phone" value="{{ old('referrer_phone') }}"
                                placeholder="Nhập SĐT người giới thiệu" type="text" maxlength="20" inputmode="numeric">

                            @error('referrer_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <button class="btn btn-outline-primary w-100" type="button" id="checkCtvBtn">
                                Kiểm tra CTV
                            </button>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Tỷ lệ hoa hồng</label>

                            <input class="form-control" name="referral_commission_rate"
                                value="{{ old('referral_commission_rate') }}" placeholder="VD: 5" type="number" min="0"
                                max="100" step="0.01">
                        </div>
                    </div>

                    <div class="small mt-3" id="ctvCheckText"></div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="section-title">
                    <i class="fa-solid fa-clipboard-list me-2"></i>
                    Nhu cầu / Ghi chú ban đầu
                </h5>

                <div class="row g-3 mt-2">
                    <div class="col-md-4">
                        <label class="form-label">Khách mua cho ai?</label>

                        <select class="form-select" name="buy_for_option_id">
                            <option value="">-- Chọn --</option>

                            @foreach($buyForOptions as $option)
                            <option value="{{ $option->id }}" @selected(old('buy_for_option_id')==$option->id)>
                                {{ $option->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Sản phẩm quan tâm</label>

                        <select class="form-select" name="interested_product_id">
                            <option value="">-- Chọn --</option>

                            @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected(old('interested_product_id')==$product->id)>
                                {{ $product->product_name ?? $product->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Nhu cầu quan tâm</label>

                        <select class="form-select" name="customer_need_ids[]">
                            <option value="">-- Chọn --</option>

                            @foreach($customerNeeds as $need)
                            <option value="{{ $need->id }}" @selected(in_array($need->id, old('customer_need_ids',
                                [])))>
                                {{ $need->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Ghi chú tư vấn ban đầu</label>

                        <textarea class="form-control" name="consultation_note" rows="3"
                            placeholder="Ví dụ: Khách mua cho mẹ 65 tuổi, đang quan tâm sản phẩm hỗ trợ xương khớp, cần tư vấn dạng dễ dùng.">{{ old('consultation_note') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('admin.customers.index') }}" class="btn btn-light border px-4">
                Hủy
            </a>

            <button class="btn btn-primary px-4" type="submit">
                <i class="fa-solid fa-save me-1"></i>
                Lưu khách hàng
            </button>
        </div>
    </form>

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/customerForm.css') }}">

<style>
    :root {

        /* ===== Màu chữ ===== */
        --commission-text: #111827;
        --commission-title: #0f172a;
        --commission-muted: #64748b;
        --commission-white: #ffffff;

        /* ===== Màu nền tổng thể ===== */
        --commission-bg-main: #eef5ff;
        --commission-bg-light: #f8fbff;
        --commission-bg-white: #ffffff;
        --commission-bg-table-head: #e6f0fe;
        --commission-bg-soft-blue: #eff6ff;
        --commission-bg-soft-card: #f2f9ff;

        /* ===== Viền ===== */
        --commission-border: #dbeafe;
        --commission-border-soft: #edf4ff;
        --commission-border-blue: #cfe0ff;

        /* ===== Màu xanh dương ===== */
        --commission-blue: #2563eb;
        --commission-blue-dark: #1e3a8a;
        --commission-blue-1: #236ae9;
        --commission-blue-2: #1984e2;
        --commission-blue-3: #42b8e1;
        --commission-cyan: #06b6d4;

        /* ===== Màu xanh lá ===== */
        --commission-green: #16a34a;
        --commission-green-1: #17a64c;
        --commission-green-2: #1baf51;
        --commission-green-3: #51cc7e;
        --commission-green-light: #22c55e;

        /* ===== Màu đỏ / cam ===== */
        --commission-red: #ef4444;
        --commission-red-1: #f04840;
        --commission-orange: #f97316;
        --commission-orange-1: #f35831;
        --commission-orange-2: #f98a51;

        /* ===== Màu phụ ===== */
        --commission-purple: #7c3aed;
        --commission-teal: #0f766e;
        --commission-warning: #facc15;
        --commission-danger-bg: #fff7ed;

        /* ===== Shadow ===== */
        --commission-shadow-sm: 0 6px 16px rgba(15, 23, 42, 0.045);
        --commission-shadow-md: 0 10px 28px rgba(37, 99, 235, 0.10);
        --commission-shadow-lg: 0 18px 45px rgba(15, 23, 42, 0.10);
        --commission-shadow-modal: 0 30px 90px rgba(15, 23, 42, 0.26);

        /* ===== Gradient chính ===== */
        --commission-gradient-page:
            radial-gradient(circle at top left, rgba(37, 99, 235, 0.18), transparent 30%),
            radial-gradient(circle at top right, rgba(14, 165, 233, 0.16), transparent 34%),
            linear-gradient(135deg, #eef5ff 0%, #f8fbff 55%, #ffffff 100%);

        --commission-gradient-total: linear-gradient(135deg, #236ae9 0%, #1984e2 45%, #42b8e1 100%);
        --commission-gradient-paid: linear-gradient(135deg, #17a64c 0%, #1baf51 45%, #51cc7e 100%);
        --commission-gradient-debt: linear-gradient(135deg, #f04840 0%, #f35831 55%, #f98a51 100%);

        --commission-gradient-icon: linear-gradient(135deg, #2563eb 0%, #06b6d4 100%);
        --commission-gradient-modal-header: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
        --commission-gradient-box: linear-gradient(135deg, #eff6ff 0%, #f8fbff 100%);
        --commission-gradient-table-head: linear-gradient(180deg, #eff6ff 0%, #dbeafe 100%);
    }

    .customer-create-page {
        min-height: calc(100vh - 80px);
        padding: 22px;
        color: var(--commission-text);
        background: var(--commission-gradient-page);
        border-radius: 24px;
    }

    .customer-create-page .breadcrumb {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 10px 14px;
        margin-bottom: 18px;
        background: rgba(255, 255, 255, 0.72);
        border: 1px solid var(--commission-border);
        border-radius: 999px;
        box-shadow: var(--commission-shadow-sm);
    }

    .customer-create-page .breadcrumb-item,
    .customer-create-page .breadcrumb-item.active {
        font-size: 13px;
        font-weight: 600;
        color: var(--commission-muted);
    }

    .customer-create-page .breadcrumb-item a {
        color: var(--commission-blue);
        text-decoration: none;
    }

    .customer-create-page .breadcrumb-item a:hover {
        color: var(--commission-blue-dark);
    }

    .customer-create-page>h3 {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px !important;
        color: var(--commission-title);
        font-size: 26px;
        font-weight: 800;
        letter-spacing: -0.03em;
    }

    .customer-create-page>h3::before {
        content: "";
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        border-radius: 16px;
        background: var(--commission-gradient-icon);
        box-shadow: var(--commission-shadow-md);
    }

    .customer-create-page .alert-danger {
        color: var(--commission-red-1);
        background: var(--commission-danger-bg);
        border: 1px solid rgba(239, 68, 68, 0.22);
        border-radius: 18px;
        box-shadow: var(--commission-shadow-sm);
    }

    .customer-create-page .card {
        overflow: hidden;
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid var(--commission-border-soft) !important;
        border-radius: 24px;
        box-shadow: var(--commission-shadow-md) !important;
        backdrop-filter: blur(10px);
    }

    .customer-create-page .card-body {
        padding: 24px;
    }

    .customer-create-page .section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 14px;
        padding-bottom: 14px;
        color: var(--commission-title);
        font-size: 18px;
        font-weight: 800;
        border-bottom: 1px solid var(--commission-border-soft);
    }

    .customer-create-page .section-title i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        color: var(--commission-white);
        font-size: 15px;
        background: var(--commission-gradient-icon);
        border-radius: 14px;
        box-shadow: var(--commission-shadow-sm);
    }

    .customer-create-page .form-label {
        margin-bottom: 7px;
        color: var(--commission-title);
        font-size: 14px;
        font-weight: 700;
    }

    .customer-create-page .text-muted {
        color: var(--commission-muted) !important;
    }

    .customer-create-page .text-danger {
        color: var(--commission-red) !important;
    }

    .customer-create-page .form-control,
    .customer-create-page .form-select {
        min-height: 44px;
        color: var(--commission-text);
        font-size: 14px;
        background-color: var(--commission-bg-light);
        border: 1px solid var(--commission-border-blue);
        border-radius: 14px;
        box-shadow: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }

    .customer-create-page textarea.form-control {
        min-height: auto;
        resize: vertical;
    }

    .customer-create-page .form-control::placeholder {
        color: #94a3b8;
    }

    .customer-create-page .form-control:focus,
    .customer-create-page .form-select:focus {
        background-color: var(--commission-white);
        border-color: var(--commission-blue);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
    }

    .customer-create-page .form-control.is-invalid,
    .customer-create-page .form-select.is-invalid {
        border-color: var(--commission-red);
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.08);
    }

    .customer-create-page .invalid-feedback,
    .customer-create-page .text-danger.small {
        font-weight: 600;
    }

    .customer-create-page .border-danger-subtle {
        background: var(--commission-danger-bg);
        border-color: rgba(239, 68, 68, 0.25) !important;
    }

    .customer-create-page .radio-card-label {
        position: relative;
        display: block;
        height: 100%;
        margin: 0;
        cursor: pointer;
    }

    .customer-create-page .radio-card-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .customer-create-page .radio-card-content {
        height: 100%;
        padding: 18px 18px 18px 54px;
        background: var(--commission-gradient-box);
        border: 1px solid var(--commission-border);
        border-radius: 20px;
        box-shadow: var(--commission-shadow-sm);
        transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .customer-create-page .radio-card-content::before {
        content: "";
        position: absolute;
        top: 21px;
        left: 18px;
        width: 22px;
        height: 22px;
        background: var(--commission-white);
        border: 2px solid var(--commission-border-blue);
        border-radius: 50%;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .customer-create-page .radio-card-content::after {
        content: "";
        position: absolute;
        top: 27px;
        left: 24px;
        width: 10px;
        height: 10px;
        background: var(--commission-blue);
        border-radius: 50%;
        opacity: 0;
        transform: scale(0.5);
        transition: opacity 0.2s ease, transform 0.2s ease;
    }

    .customer-create-page .radio-card-label:hover .radio-card-content {
        transform: translateY(-2px);
        border-color: var(--commission-blue);
        box-shadow: var(--commission-shadow-md);
    }

    .customer-create-page .radio-card-input:checked+.radio-card-content {
        background:
            linear-gradient(135deg, rgba(37, 99, 235, 0.10), rgba(6, 182, 212, 0.08)),
            var(--commission-bg-white);
        border-color: var(--commission-blue);
        box-shadow: var(--commission-shadow-md);
    }

    .customer-create-page .radio-card-input:checked+.radio-card-content::before {
        border-color: var(--commission-blue);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
    }

    .customer-create-page .radio-card-input:checked+.radio-card-content::after {
        opacity: 1;
        transform: scale(1);
    }

    .customer-create-page .radio-card-title {
        color: var(--commission-title);
        font-size: 15px;
        font-weight: 800;
    }

    .customer-create-page .radio-card-desc {
        margin-top: 5px;
        color: var(--commission-muted);
        font-size: 13px;
        line-height: 1.5;
    }

    .customer-create-page .direct-source-box,
    .customer-create-page .ctv-box {
        padding: 20px;
        background: var(--commission-bg-soft-card);
        border: 1px solid var(--commission-border);
        border-radius: 22px;
        box-shadow: var(--commission-shadow-sm);
    }

    .customer-create-page .direct-source-box h6,
    .customer-create-page .ctv-box h6 {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--commission-blue-dark);
        font-size: 15px;
    }

    .customer-create-page .direct-source-box h6::before,
    .customer-create-page .ctv-box h6::before {
        content: "";
        width: 10px;
        height: 10px;
        background: var(--commission-gradient-icon);
        border-radius: 999px;
        box-shadow: 0 0 0 5px rgba(37, 99, 235, 0.10);
    }

    .customer-create-page #ctvCheckText {
        min-height: 24px;
        padding: 10px 12px;
        color: var(--commission-muted);
        background: rgba(255, 255, 255, 0.72);
        border: 1px dashed var(--commission-border-blue);
        border-radius: 14px;
    }

    .customer-create-page .btn {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 14px;
        font-weight: 800;
        border-radius: 14px;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease, background 0.2s ease;
    }

    .customer-create-page .btn:hover {
        transform: translateY(-1px);
    }

    .customer-create-page .btn-primary {
        color: var(--commission-white);
        background: var(--commission-gradient-total);
        border: 0;
        box-shadow: var(--commission-shadow-md);
    }

    .customer-create-page .btn-primary:hover,
    .customer-create-page .btn-primary:focus {
        color: var(--commission-white);
        box-shadow: var(--commission-shadow-lg);
    }

    .customer-create-page .btn-outline-primary {
        color: var(--commission-blue);
        background: var(--commission-bg-white);
        border-color: var(--commission-blue);
    }

    .customer-create-page .btn-outline-primary:hover,
    .customer-create-page .btn-outline-primary:focus {
        color: var(--commission-white);
        background: var(--commission-gradient-total);
        border-color: transparent;
        box-shadow: var(--commission-shadow-md);
    }

    .customer-create-page .btn-light {
        color: var(--commission-title);
        background: var(--commission-bg-white);
        border-color: var(--commission-border-blue) !important;
        box-shadow: var(--commission-shadow-sm);
    }

    .customer-create-page .btn-light:hover {
        color: var(--commission-blue-dark);
        background: var(--commission-bg-soft-blue);
        border-color: var(--commission-blue) !important;
    }

    .customer-create-page .d-flex.justify-content-end {
        padding: 18px;
        background: rgba(255, 255, 255, 0.72);
        border: 1px solid var(--commission-border-soft);
        border-radius: 22px;
        box-shadow: var(--commission-shadow-sm);
        backdrop-filter: blur(10px);
    }

    @media (max-width: 767.98px) {
        .customer-create-page {
            padding: 14px;
            border-radius: 18px;
        }

        .customer-create-page>h3 {
            font-size: 22px;
        }

        .customer-create-page .card-body {
            padding: 18px;
        }

        .customer-create-page .section-title {
            font-size: 16px;
        }

        .customer-create-page .direct-source-box,
        .customer-create-page .ctv-box {
            padding: 16px;
        }

        .customer-create-page .d-flex.justify-content-end {
            flex-direction: column-reverse;
        }

        .customer-create-page .d-flex.justify-content-end .btn,
        .customer-create-page .btn.px-4 {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    window.CustomerFormConfig = {
        checkReferrerUrl: "{{ route('admin.customers.check-referrer') }}",
        csrfToken: "{{ csrf_token() }}"
    };
</script>
<script src="{{ asset('admin/js/customerForm.js') }}"></script>
@endpush