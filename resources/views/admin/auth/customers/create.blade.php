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