@extends('admin.auth.dashboardAmin')

@section('title', 'Chăm sóc khách hàng')

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/customer-care.css') }}">
@endpush

@section('admin_content')
<div class="container-fluid customer-care-page">

    {{-- ================================================================
    | TIÊU ĐỀ TRANG
    ================================================================= --}}
    <header class="care-page-header care-glass mb-4">
        <div>
            <div class="care-breadcrumb">
                Khách hàng / Chăm sóc khách hàng
            </div>

            <h1 class="care-page-title">
                Chăm sóc khách hàng
            </h1>

            <p class="care-page-description">
                Theo dõi nội dung đã tư vấn, nhận biết khách hàng chưa được
                tư vấn và quản lý lịch chăm sóc đến thời gian xử lý.
            </p>
        </div>
    </header>

    {{-- ================================================================
    | THÔNG BÁO
    ================================================================= --}}
    @if(session('success'))
    <div class="care-alert care-alert-success mb-4">
        <div>
            {{ session('success') }}
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="care-alert care-alert-danger mb-4">
        <div>
            {{ session('error') }}
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="care-alert care-alert-danger mb-4">
        <div>
            <strong>Vui lòng kiểm tra lại dữ liệu:</strong>

            <ul class="mt-2 mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- ================================================================
    | THỐNG KÊ
    ================================================================= --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl">
            <div class="care-stat-card care-stat-blue">
                <span>Tổng khách hàng</span>

                <strong>
                    {{ number_format($statistics['total_customers']) }}
                </strong>

                <small>Khách hàng trong hệ thống</small>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl">
            <div class="care-stat-card care-stat-cyan">
                <span>Chưa tư vấn</span>

                <strong>
                    {{ number_format($statistics['not_consulted']) }}
                </strong>

                <small>Chưa có nội dung tư vấn</small>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl">
            <div class="care-stat-card care-stat-purple">
                <span>Lịch hôm nay</span>

                <strong>
                    {{ number_format($statistics['today_reminders']) }}
                </strong>

                <small>Lịch chưa hoàn thành</small>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl">
            <div class="care-stat-card care-stat-green">
                <span>Hoàn thành hôm nay</span>

                <strong>
                    {{ number_format($statistics['completed_today']) }}
                </strong>

                <small>Lịch đã được xử lý</small>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl">
            <div class="care-stat-card care-stat-red">
                <span>Đã đến giờ</span>

                <strong>
                    {{ number_format($statistics['due_reminders']) }}
                </strong>

                <small>Cần xử lý ngay</small>
            </div>
        </div>
    </div>

    {{-- ================================================================
    | CẢNH BÁO KHÁCH CHƯA TƯ VẤN
    ================================================================= --}}
    @if($statistics['not_consulted'] > 0)
    <div class="care-alert care-alert-warning mb-4">
        <div>
            <strong>
                Có {{ number_format($statistics['not_consulted']) }}
                khách hàng chưa được tư vấn.
            </strong>

            <div class="mt-1">
                Các khách hàng này chưa có bản ghi tư vấn thực tế
                trong lịch sử chăm sóc.
            </div>
        </div>

        <a href="{{ route('admin.customer-care.index', [
                    'consultation_status' => 'not_consulted'
                ]) }}" class="care-btn care-btn-warning">
            Xem khách chưa tư vấn
        </a>
    </div>
    @endif

    {{-- ================================================================
    | TÌM KHÁCH HÀNG
    ================================================================= --}}
    <section class="care-panel care-glass mb-4">
        <div class="mb-3">
            <h2 class="care-section-title">
                Tìm khách hàng
            </h2>

            <p class="care-section-description">
                Tìm theo tên, mã khách hàng, số điện thoại hoặc email và lọc
                theo tình trạng tư vấn.
            </p>
        </div>

        <form action="{{ route('admin.customer-care.index') }}" method="GET" class="row g-3 align-items-end">
            {{-- Giữ lại điều kiện lọc lịch --}}
            <input type="hidden" name="reminder_phone" value="{{ $reminderPhone }}">

            <input type="hidden" name="reminder_date" value="{{ $reminderDate }}">

            <input type="hidden" name="reminder_status" value="{{ $reminderStatus }}">

            <div class="col-12 col-lg-7">
                <label for="customer_keyword" class="care-form-label">
                    Tên, mã khách hàng, số điện thoại hoặc email
                </label>

                <input id="customer_keyword" type="text" name="customer_keyword" class="form-control care-form-control"
                    value="{{ $customerKeyword }}" placeholder="Nhập tên, mã khách hàng, số điện thoại hoặc email">
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <label for="consultation_status" class="care-form-label">
                    Tình trạng tư vấn
                </label>

                <select id="consultation_status" name="consultation_status" class="form-select care-form-control">
                    <option value="all" @selected($consultationStatus==='all' )>
                        Tất cả khách hàng
                    </option>

                    <option value="not_consulted" @selected($consultationStatus==='not_consulted' )>
                        Chưa tư vấn
                    </option>

                    <option value="consulted" @selected($consultationStatus==='consulted' )>
                        Đã tư vấn
                    </option>
                </select>
            </div>

            <div class="col-12 col-md-6 col-lg-2">
                <button type="submit" class="btn care-btn care-btn-primary w-100">
                    Tìm khách hàng
                </button>
            </div>
        </form>
    </section>

    {{-- ================================================================
    | DANH SÁCH KHÁCH HÀNG
    ================================================================= --}}
    <section class="care-panel care-table-panel care-glass mb-4">
        <div class="care-panel-heading">
            <div>
                <h2 class="care-section-title">
                    Danh sách khách hàng
                </h2>

                <p class="care-section-description">
                    Có {{ number_format($customers->total()) }}
                    khách hàng phù hợp.
                </p>
            </div>

            @if(
            $consultationStatus !== 'all'
            || $customerKeyword !== ''
            )
            <a href="{{ route('admin.customer-care.index') }}" class="care-btn care-btn-secondary">
                Xóa bộ lọc
            </a>
            @endif
        </div>

        <div class="care-table-wrap">
            <table class="table care-table align-middle">
                <thead>
                    <tr>
                        <th style="width: 70px;">STT</th>

                        <th style="width: 210px;">
                            Khách hàng
                        </th>

                        <th style="width: 250px;">
                            Địa chỉ
                        </th>

                        <th style="width: 340px;">
                            Nội dung tư vấn gần nhất
                        </th>

                        <th style="width: 170px;">
                            Lần tư vấn gần nhất
                        </th>

                        <th style="width: 140px;">
                            Trạng thái
                        </th>

                        <th style="width: 180px;" class="text-end">
                            Thao tác
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($customers as $customer)
                    @php
                    $address = implode(
                    ', ',
                    array_filter([
                    $customer->address,
                    $customer->ward,
                    $customer->district,
                    $customer->province,
                    ])
                    );

                    $hasConsultation =
                    (int) $customer->consultation_count > 0;
                    @endphp

                    <tr>
                        <td>
                            <strong>
                                {{
                                        ($customers->firstItem() ?? 1)
                                        + $loop->index
                                    }}
                            </strong>
                        </td>

                        <td>
                            <strong class="care-customer-name">
                                {{ $customer->full_name }}
                            </strong>

                            <div class="care-muted">
                                {{
                                        $customer->customer_code
                                        ?: 'Chưa có mã khách hàng'
                                    }}
                            </div>

                            @if($customer->phone)
                            <a href="tel:{{ $customer->phone }}" class="care-phone-link">
                                {{ $customer->phone }}
                            </a>
                            @else
                            <div class="care-muted">
                                Chưa có số điện thoại
                            </div>
                            @endif

                            @if($customer->email)
                            <a href="mailto:{{ $customer->email }}" class="care-email-link">
                                {{ $customer->email }}
                            </a>
                            @else
                            <div class="care-muted">
                                Chưa có email
                            </div>
                            @endif
                        </td>

                        <td>
                            {{
                                    $address
                                    ?: 'Chưa cập nhật địa chỉ'
                                }}
                        </td>

                        <td>
                            @if($hasConsultation)
                            <div class="care-note-preview">
                                {{
                                            $customer
                                                ->latest_consultation_content
                                            ?: 'Chưa cập nhật nội dung tư vấn'
                                        }}
                            </div>
                            @else
                            <span class="care-badge care-badge-danger">
                                Chưa có nội dung tư vấn
                            </span>

                            @if(
                            $customer->note
                            || $customer->consultation_note
                            )
                            <div class="care-muted mt-2">
                                Ghi chú hồ sơ:

                                {{
                                                $customer->note
                                                ?: $customer
                                                    ->consultation_note
                                            }}
                            </div>
                            @endif
                            @endif
                        </td>

                        <td>
                            @if($hasConsultation)
                            <strong>
                                {{
                                            $customer
                                                ->latest_consultation_date_display
                                            ?: 'Chưa cập nhật'
                                        }}
                            </strong>

                            <div class="care-muted">
                                Tổng

                                {{
                                            number_format(
                                                $customer
                                                    ->consultation_count
                                            )
                                        }}

                                lần tư vấn
                            </div>
                            @else
                            <span class="care-muted">
                                Chưa phát sinh
                            </span>
                            @endif
                        </td>

                        <td>
                            @if($hasConsultation)
                            <span class="care-badge care-badge-success">
                                Đã tư vấn
                            </span>
                            @else
                            <span class="care-badge care-badge-danger">
                                Chưa tư vấn
                            </span>
                            @endif
                        </td>

                        <td class="text-end">
                            <a href="{{ route(
                                        'admin.customer-care.show',
                                        [
                                            'customerId' => $customer->id
                                        ]
                                    ) }}{{ $hasConsultation
                                        ? '#care-history'
                                        : '#add-consultation'
                                    }}" class="care-btn {{
                                        $hasConsultation
                                        ? 'care-btn-secondary'
                                        : 'care-btn-primary'
                                    }}">
                                {{
                                        $hasConsultation
                                        ? 'Xem / sửa'
                                        : 'Tư vấn ngay'
                                    }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="care-empty-state">
                            Không tìm thấy khách hàng phù hợp.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
        <div class="care-pagination">
            {{
                    $customers->links(
                        'pagination::bootstrap-5'
                    )
                }}
        </div>
        @endif
    </section>

    {{-- ================================================================
    | TÌM LỊCH CHĂM SÓC
    ================================================================= --}}
    <section class="care-panel care-glass mb-4">
        <div class="mb-3">
            <h2 class="care-section-title">
                Tìm lịch hẹn chăm sóc
            </h2>

            <p class="care-section-description">
                Tìm theo số điện thoại, ngày hẹn và trạng thái lịch.
            </p>
        </div>

        <form action="{{ route('admin.customer-care.index') }}" method="GET" class="row g-3 align-items-end">
            {{-- Giữ lại điều kiện lọc khách hàng --}}
            <input type="hidden" name="customer_keyword" value="{{ $customerKeyword }}">

            <input type="hidden" name="consultation_status" value="{{ $consultationStatus }}">

            <div class="col-12 col-md-6 col-xl-4">
                <label for="reminder_phone" class="care-form-label">
                    Số điện thoại
                </label>

                <input id="reminder_phone" type="text" name="reminder_phone" class="form-control care-form-control"
                    value="{{ $reminderPhone }}" placeholder="Nhập số điện thoại">
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <label for="reminder_date" class="care-form-label">
                    Ngày hẹn
                </label>

                <input id="reminder_date" type="date" name="reminder_date" class="form-control care-form-control"
                    value="{{ $reminderDate }}">
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <label for="reminder_status" class="care-form-label">
                    Trạng thái
                </label>

                <select id="reminder_status" name="reminder_status" class="form-select care-form-control">
                    <option value="all" @selected($reminderStatus==='all' )>
                        Tất cả trạng thái
                    </option>

                    <option value="pending" @selected($reminderStatus==='pending' )>
                        Chưa hoàn thành
                    </option>

                    <option value="overdue" @selected($reminderStatus==='overdue' )>
                        Đã đến giờ / quá hạn
                    </option>

                    <option value="completed" @selected($reminderStatus==='completed' )>
                        Đã hoàn thành
                    </option>
                </select>
            </div>

            <div class="col-12 col-md-6 col-xl-2">
                <button type="submit" class="btn care-btn care-btn-primary w-100">
                    Tìm lịch
                </button>
            </div>
        </form>
    </section>

    {{-- ================================================================
    | DANH SÁCH LỊCH CHĂM SÓC
    ================================================================= --}}
    <section class="care-panel care-table-panel care-glass">
        <div class="care-panel-heading">
            <div>
                <h2 class="care-section-title">
                    Lịch chăm sóc khách hàng
                </h2>

                <p class="care-section-description">
                    Có {{ number_format($reminders->total()) }}
                    lịch phù hợp.
                </p>
            </div>
        </div>

        <div class="care-table-wrap">
            <table class="table care-table align-middle">
                <thead>
                    <tr>
                        <th style="width: 70px;">
                            STT
                        </th>

                        <th style="width: 170px;">
                            Thời gian
                        </th>

                        <th style="width: 210px;">
                            Khách hàng
                        </th>

                        <th style="width: 240px;">
                            Địa chỉ
                        </th>

                        <th style="width: 330px;">
                            Nội dung
                        </th>

                        <th style="width: 160px;">
                            Phụ trách
                        </th>

                        <th style="width: 145px;">
                            Trạng thái
                        </th>

                        <th style="width: 220px;" class="text-end">
                            Thao tác
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($reminders as $reminder)
                    @php
                    $reminderAddress = implode(
                    ', ',
                    array_filter([
                    $reminder->address,
                    $reminder->ward,
                    $reminder->district,
                    $reminder->province,
                    ])
                    );
                    @endphp

                    <tr>
                        <td>
                            <strong>
                                {{
                                        ($reminders->firstItem() ?? 1)
                                        + $loop->index
                                    }}
                            </strong>
                        </td>

                        <td>
                            <strong>
                                {{
                                        $reminder->reminder_at_display
                                        ?: 'Chưa đặt thời gian'
                                    }}
                            </strong>

                            @if($reminder->is_due)
                            <div class="care-muted care-text-danger">
                                Đã đến thời gian xử lý
                            </div>
                            @endif
                        </td>

                        <td>
                            <strong class="care-customer-name">
                                {{ $reminder->full_name }}
                            </strong>

                            @if($reminder->phone)
                            <a href="tel:{{ $reminder->phone }}" class="care-phone-link">
                                {{ $reminder->phone }}
                            </a>
                            @else
                            <div class="care-muted">
                                Chưa có số điện thoại
                            </div>
                            @endif

                            @if($reminder->email)
                            <a href="mailto:{{ $reminder->email }}" class="care-email-link">
                                {{ $reminder->email }}
                            </a>
                            @else
                            <div class="care-muted">
                                Chưa có email
                            </div>
                            @endif
                        </td>

                        <td>
                            {{
                                    $reminderAddress
                                    ?: 'Chưa cập nhật địa chỉ'
                                }}
                        </td>

                        <td>
                            <div class="care-note-preview">
                                {{
                                        $reminder->content
                                        ?: 'Chưa có nội dung chăm sóc'
                                    }}
                            </div>

                            @if($reminder->priority_name)
                            <div class="care-muted mt-1">
                                Ưu tiên:
                                {{ $reminder->priority_name }}
                            </div>
                            @endif
                        </td>

                        <td>
                            {{
                                    $reminder->staff_name
                                    ?: 'Chưa phân công'
                                }}
                        </td>

                        <td>
                            @if($reminder->is_completed)
                            <span class="care-badge care-badge-success">
                                Đã hoàn thành
                            </span>
                            @elseif($reminder->is_due)
                            <span class="care-badge care-badge-danger">
                                Đã đến giờ
                            </span>
                            @else
                            <span class="care-badge care-badge-warning">
                                {{
                                            $reminder->status_name
                                            ?: 'Chờ chăm sóc'
                                        }}
                            </span>
                            @endif
                        </td>

                        <td class="text-end">
                            <div class="care-actions">
                                <a href="{{ route(
                                            'admin.customer-care.show',
                                            [
                                                'customerId' =>
                                                    $reminder->customer_id
                                            ]
                                        ) }}" class="care-btn care-btn-secondary">
                                    Chi tiết
                                </a>

                                @if(!$reminder->is_completed)
                                <form action="{{ route(
                                                'admin.customer-care.reminders.complete',
                                                [
                                                    'reminderId' =>
                                                        $reminder->id
                                                ]
                                            ) }}" method="POST" data-confirm="Xác nhận hoàn thành lịch chăm sóc này?">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" class="care-btn care-btn-success">
                                        Hoàn thành
                                    </button>
                                </form>
                                @else
                                <form action="{{ route(
                                                'admin.customer-care.reminders.reopen',
                                                [
                                                    'reminderId' =>
                                                        $reminder->id
                                                ]
                                            ) }}" method="POST" data-confirm="Bạn muốn mở lại lịch chăm sóc này?">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" class="care-btn care-btn-warning">
                                        Mở lại
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="care-empty-state">
                            Không tìm thấy lịch chăm sóc phù hợp.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reminders->hasPages())
        <div class="care-pagination">
            {{
                    $reminders->links(
                        'pagination::bootstrap-5'
                    )
                }}
        </div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script src="{{ asset('admin/js/customer-care.js') }}"></script>
@endpush
