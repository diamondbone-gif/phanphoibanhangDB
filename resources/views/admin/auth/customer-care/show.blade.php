@extends('admin.auth.dashboardAmin')

@section('title', 'Chăm sóc ' . $customer->full_name)

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/customer-care.css') }}">
@endpush

@section('admin_content')
<div class="container-fluid customer-care-page">
    @php
    $address = implode(', ', array_filter([
    $customer->address,
    $customer->ward,
    $customer->district,
    $customer->province,
    ]));

    $birthDateDisplay = $customer->birth_date
    ? \Carbon\Carbon::parse(
    $customer->birth_date
    )->format('d/m/Y')
    : 'Chưa cập nhật';
    @endphp

    {{-- ================================================================
    | TIÊU ĐỀ
    ================================================================= --}}
    <header class="care-page-header care-glass mb-4">
        <div>
            <a href="{{ route('admin.customer-care.index') }}" class="care-back-link">
                ← Quay lại danh sách
            </a>

            <h1 class="care-page-title">
                Chăm sóc {{ $customer->full_name }}
            </h1>

            <p class="care-page-description">
                Lưu nội dung tư vấn theo từng lần, xem lại lịch sử
                và sửa đúng bản ghi cần cập nhật.
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
            <strong>
                Không thể lưu dữ liệu vì:
            </strong>

            <ul class="mt-2 mb-0">
                @foreach($errors->all() as $error)
                <li>
                    {{ $error }}
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- ================================================================
    | THÔNG TIN KHÁCH HÀNG
    ================================================================= --}}
    <section class="care-panel care-glass mb-4">
        <div class="mb-3">
            <h2 class="care-section-title">
                Thông tin khách hàng
            </h2>

            <p class="care-section-description">
                Kiểm tra đúng khách hàng trước khi ghi nhận tư vấn.
            </p>
        </div>

        <div class="care-info-grid">
            <div class="care-info-item">
                <span>
                    Họ và tên
                </span>

                <strong>
                    {{ $customer->full_name }}
                </strong>
            </div>

            <div class="care-info-item">
                <span>
                    Mã khách hàng
                </span>

                <strong>
                    {{
                        $customer->customer_code
                        ?: 'Chưa có mã'
                    }}
                </strong>
            </div>

            <div class="care-info-item">
                <span>
                    Số điện thoại
                </span>

                <strong>
                    @if($customer->phone)
                    <a href="tel:{{ $customer->phone }}" class="care-phone-link">
                        {{ $customer->phone }}
                    </a>
                    @else
                    Chưa cập nhật
                    @endif
                </strong>
            </div>

            <div class="care-info-item">
                <span>
                    Email
                </span>

                <strong>
                    @if($customer->email)
                    <a href="mailto:{{ $customer->email }}" class="care-phone-link">
                        {{ $customer->email }}
                    </a>
                    @else
                    Chưa cập nhật
                    @endif
                </strong>
            </div>

            <div class="care-info-item">
                <span>
                    Ngày sinh
                </span>

                <strong>
                    {{ $birthDateDisplay }}
                </strong>
            </div>

            <div class="care-info-item">
                <span>
                    Địa chỉ
                </span>

                <strong>
                    {{
                        $address
                        ?: 'Chưa cập nhật'
                    }}
                </strong>
            </div>

            <div class="care-info-item">
                <span>
                    Ghi chú khách hàng
                </span>

                <strong>
                    {{
                        $customer->note
                        ?: 'Chưa có ghi chú khách hàng'
                    }}
                </strong>
            </div>

            <div class="care-info-item">
                <span>
                    Ghi chú hồ sơ tư vấn
                </span>

                <strong>
                    {{
                        $customer->consultation_note
                        ?: 'Chưa có ghi chú hồ sơ'
                    }}
                </strong>
            </div>

            <div class="care-info-item">
                <span>
                    Ghi chú sức khỏe
                </span>

                <strong>
                    {{
                        $customer->medical_note
                        ?: 'Chưa cập nhật'
                    }}
                </strong>
            </div>

            <div class="care-info-item">
                <span>
                    Tình trạng tư vấn
                </span>

                <strong>
                    @if($consultationCount > 0)
                    <span class="care-badge care-badge-success">
                        Đã tư vấn
                        {{ number_format($consultationCount) }}
                        lần
                    </span>
                    @else
                    <span class="care-badge care-badge-danger">
                        Chưa tư vấn
                    </span>
                    @endif
                </strong>
            </div>
        </div>
    </section>

    {{-- ================================================================
    | CẢNH BÁO CHƯA TƯ VẤN
    ================================================================= --}}
    @if($consultationCount === 0)
    <div class="care-alert care-alert-warning mb-4">
        <div>
            <strong>
                Khách hàng này chưa có nội dung tư vấn.
            </strong>

            <div class="mt-1">
                Nhập nội dung đã trao đổi vào biểu mẫu bên dưới
                để hệ thống ghi nhận là đã tư vấn.
            </div>
        </div>
    </div>
    @endif

    {{-- ================================================================
    | THÊM NỘI DUNG TƯ VẤN
    ================================================================= --}}
    <section id="add-consultation" class="care-panel care-glass mb-4">
        <div class="mb-3">
            <h2 class="care-section-title">
                Thêm nội dung tư vấn mới
            </h2>

            <p class="care-section-description">
                Mỗi lần tư vấn được lưu thành một bản ghi riêng,
                không ghi đè lịch sử cũ.
            </p>
        </div>

        <form action="{{ route(
                'admin.customer-care.logs.store',
                [
                    'customerId' => $customer->id,
                ]
            ) }}" method="POST" class="row g-3">
            @csrf

            <div class="col-12 col-md-6 col-xl-3">
                <label for="staff_id" class="care-form-label">
                    Người tư vấn
                </label>

                <select id="staff_id" name="staff_id" class="form-select care-form-control">
                    <option value="">
                        Tự động lấy tài khoản hiện tại
                    </option>

                    @foreach($staffMembers as $staff)
                    <option value="{{ $staff->id }}" @selected( (string) old( 'staff_id' , $currentStaffId )===(string)
                        $staff->id
                        )
                        >
                        {{ $staff->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <label for="care_channel_id" class="care-form-label">
                    Kênh tư vấn
                </label>

                <select id="care_channel_id" name="care_channel_id" class="form-select care-form-control">
                    <option value="">
                        Chọn kênh tư vấn
                    </option>

                    @foreach($careChannels as $channel)
                    <option value="{{ $channel->id }}" @selected( (string) old( 'care_channel_id' )===(string)
                        $channel->id
                        )
                        >
                        {{ $channel->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <label for="care_date" class="care-form-label">
                    Ngày giờ tư vấn *
                </label>

                <input id="care_date" type="datetime-local" name="care_date" class="form-control care-form-control"
                    value="{{ old(
                        'care_date',
                        now()->format('Y-m-d\TH:i')
                    ) }}" required>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <label for="care_status_id" class="care-form-label">
                    Trạng thái tư vấn
                </label>

                <select id="care_status_id" name="care_status_id" class="form-select care-form-control">
                    <option value="">
                        Chọn trạng thái
                    </option>

                    @foreach($careStatuses as $status)
                    <option value="{{ $status->id }}" @selected( (string) old( 'care_status_id' ,
                        $defaultCompletedStatusId )===(string) $status->id
                        )
                        >
                        {{ $status->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <label for="content" class="care-form-label">
                    Nội dung đã tư vấn *
                </label>

                <textarea id="content" name="content" class="form-control care-form-control" rows="5"
                    placeholder="Nhập đầy đủ nội dung đã trao đổi với khách hàng..."
                    required>{{ old('content') }}</textarea>
            </div>

            <div class="col-12 col-lg-6">
                <label for="internal_note" class="care-form-label">
                    Ghi chú nội bộ
                </label>

                <textarea id="internal_note" name="internal_note" class="form-control care-form-control" rows="4"
                    placeholder="Thông tin nội bộ, không bắt buộc">{{ old('internal_note') }}</textarea>
            </div>

            <div class="col-12 col-lg-6">
                <div class="row g-3">
                    <div class="col-12">
                        <label for="next_follow_up_at" class="care-form-label">
                            Ngày giờ liên hệ lại
                        </label>

                        <input id="next_follow_up_at" type="datetime-local" name="next_follow_up_at"
                            class="form-control care-form-control" value="{{ old('next_follow_up_at') }}">

                        <div class="care-muted">
                            Khi có thời gian liên hệ lại, hệ thống
                            tự tạo lịch nhắc tương ứng.
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="care_priority_id" class="care-form-label">
                            Mức ưu tiên
                        </label>

                        <select id="care_priority_id" name="care_priority_id" class="form-select care-form-control">
                            <option value="">
                                Chọn mức ưu tiên
                            </option>

                            @foreach($carePriorities as $priority)
                            <option value="{{ $priority->id }}" @selected( (string) old( 'care_priority_id' ,
                                $defaultNormalPriorityId )===(string) $priority->id
                                )
                                >
                                {{ $priority->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn care-btn care-btn-primary">
                    Lưu nội dung tư vấn
                </button>
            </div>
        </form>
    </section>

    {{-- ================================================================
    | LỊCH SỬ TƯ VẤN
    ================================================================= --}}
    <section id="care-history" class="care-panel care-table-panel care-glass mb-4">
        <div class="care-panel-heading">
            <div>
                <h2 class="care-section-title">
                    Lịch sử chăm sóc và tư vấn
                </h2>

                <p class="care-section-description">
                    Nội dung tư vấn có thể sửa. Nhật ký hệ thống
                    không được tính là một lần tư vấn.
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

                        <th style="width: 165px;">
                            Thời gian
                        </th>

                        <th style="width: 145px;">
                            Loại bản ghi
                        </th>

                        <th style="width: 380px;">
                            Nội dung
                        </th>

                        <th style="width: 160px;">
                            Phụ trách
                        </th>

                        <th style="width: 165px;">
                            Liên hệ lại
                        </th>

                        <th style="width: 140px;">
                            Trạng thái
                        </th>

                        <th style="width: 210px;" class="text-end">
                            Thao tác
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($careLogs as $careLog)
                    @php
                    /*
                    | Dữ liệu mới:
                    | log_type = consultation.
                    |
                    | Dữ liệu cũ:
                    | log_type có thể bằng NULL.
                    */
                    $isConsultation = isset(
                    $careLog->is_consultation
                    )
                    ? (bool) $careLog->is_consultation
                    : (
                    $careLog->log_type === null
                    || $careLog->log_type
                    === 'consultation'
                    );
                    @endphp

                    <tr>
                        <td>
                            <strong>
                                {{
                                        ($careLogs->firstItem() ?? 1)
                                        + $loop->index
                                    }}
                            </strong>
                        </td>

                        <td>
                            <strong>
                                {{
                                        $careLog->care_date_display
                                        ?: 'Chưa cập nhật'
                                    }}
                            </strong>

                            <div class="care-muted">
                                {{
                                        $careLog->channel_name
                                        ?: 'Chưa chọn kênh'
                                    }}
                            </div>
                        </td>

                        <td>
                            @if($isConsultation)
                            <span class="care-badge care-badge-info">
                                Nội dung tư vấn
                            </span>
                            @else
                            <span class="care-badge care-badge-warning">
                                Nhật ký hệ thống
                            </span>
                            @endif
                        </td>

                        <td>
                            <div class="care-note-preview">
                                {{
                                        $careLog->content
                                        ?: 'Chưa có nội dung'
                                    }}
                            </div>

                            @if($careLog->internal_note)
                            <div class="care-muted mt-2">
                                Ghi chú nội bộ:
                                {{ $careLog->internal_note }}
                            </div>
                            @endif

                            @if($careLog->priority_name)
                            <div class="care-muted mt-1">
                                Ưu tiên:
                                {{ $careLog->priority_name }}
                            </div>
                            @endif
                        </td>

                        <td>
                            {{
                                    $careLog->staff_name
                                    ?: 'Chưa phân công'
                                }}
                        </td>

                        <td>
                            {{
                                    $careLog->next_follow_up_display
                                    ?: 'Không đặt lịch'
                                }}
                        </td>

                        <td>
                            @if(
                            $careLog->status_code
                            === 'completed'
                            )
                            <span class="care-badge care-badge-success">
                                {{
                                            $careLog->status_name
                                            ?: 'Hoàn thành'
                                        }}
                            </span>
                            @elseif(
                            $careLog->status_code
                            === 'cancelled'
                            )
                            <span class="care-badge care-badge-danger">
                                {{
                                            $careLog->status_name
                                            ?: 'Đã hủy'
                                        }}
                            </span>
                            @else
                            <span class="care-badge care-badge-warning">
                                {{
                                            $careLog->status_name
                                            ?: 'Chưa cập nhật'
                                        }}
                            </span>
                            @endif
                        </td>

                        <td class="text-end">
                            @if($isConsultation)
                            <div class="care-actions">
                                <button type="button" class="btn care-btn care-btn-secondary js-edit-care-log"
                                    data-log-id="{{ $careLog->id }}" data-staff-id="{{ $careLog->staff_id }}"
                                    data-channel-id="{{ $careLog->care_channel_id }}"
                                    data-care-date="{{ $careLog->care_date_form }}"
                                    data-content="{{ $careLog->content }}"
                                    data-internal-note="{{ $careLog->internal_note }}"
                                    data-next-follow-up-at="{{ $careLog->next_follow_up_form }}"
                                    data-priority-id="{{ $careLog->care_priority_id }}"
                                    data-status-id="{{ $careLog->care_status_id }}">
                                    Sửa tư vấn
                                </button>

                                <form action="{{ route(
                                                'admin.customer-care.logs.destroy',
                                                [
                                                    'logId' => $careLog->id,
                                                ]
                                            ) }}" method="POST"
                                    data-confirm="Bạn có chắc chắn muốn xóa nội dung tư vấn này?">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="care-btn care-btn-danger">
                                        Xóa
                                    </button>
                                </form>
                            </div>
                            @else
                            <span class="care-muted">
                                Không thể sửa
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="care-empty-state">
                            Khách hàng chưa có lịch sử chăm sóc.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($careLogs->hasPages())
        <div class="care-pagination">
            {{
                    $careLogs->links(
                        'pagination::bootstrap-5'
                    )
                }}
        </div>
        @endif
    </section>

    {{-- ================================================================
    | TẠO LỊCH NHẮC THỦ CÔNG
    ================================================================= --}}
    <section class="care-panel care-glass mb-4">
        <div class="mb-3">
            <h2 class="care-section-title">
                Tạo lịch nhắc chăm sóc
            </h2>

            <p class="care-section-description">
                Dùng cho trường hợp cần tạo lịch nhắc riêng,
                không bắt buộc liên kết với một nội dung tư vấn.
            </p>
        </div>

        <form action="{{ route(
                'admin.customer-care.reminders.store',
                [
                    'customerId' => $customer->id,
                ]
            ) }}" method="POST" class="row g-3">
            @csrf

            <div class="col-12 col-md-6 col-xl-3">
                <label for="assigned_staff_id" class="care-form-label">
                    Người phụ trách
                </label>

                <select id="assigned_staff_id" name="assigned_staff_id" class="form-select care-form-control">
                    <option value="">
                        Tài khoản hiện tại
                    </option>

                    @foreach($staffMembers as $staff)
                    <option value="{{ $staff->id }}" @selected( (string) old( 'assigned_staff_id' , $currentStaffId
                        )===(string) $staff->id
                        )
                        >
                        {{ $staff->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-6 col-xl-2">
                <label for="reminder_date" class="care-form-label">
                    Ngày nhắc *
                </label>

                <input id="reminder_date" type="date" name="reminder_date" class="form-control care-form-control" value="{{ old(
                        'reminder_date',
                        today()->format('Y-m-d')
                    ) }}" required>
            </div>

            <div class="col-12 col-md-6 col-xl-2">
                <label for="reminder_time" class="care-form-label">
                    Giờ nhắc *
                </label>

                <input id="reminder_time" type="time" name="reminder_time" class="form-control care-form-control" value="{{ old(
                        'reminder_time',
                        now()->format('H:i')
                    ) }}" required>
            </div>

            <div class="col-12 col-md-6 col-xl-2">
                <label for="reminder_priority_id" class="care-form-label">
                    Mức ưu tiên
                </label>

                <select id="reminder_priority_id" name="care_priority_id" class="form-select care-form-control">
                    <option value="">
                        Chọn mức ưu tiên
                    </option>

                    @foreach($carePriorities as $priority)
                    <option value="{{ $priority->id }}" @selected( (string) old( 'care_priority_id' ,
                        $defaultNormalPriorityId )===(string) $priority->id
                        )
                        >
                        {{ $priority->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <label for="reminder_status_id" class="care-form-label">
                    Trạng thái
                </label>

                <select id="reminder_status_id" name="care_status_id" class="form-select care-form-control">
                    <option value="">
                        Chọn trạng thái
                    </option>

                    @foreach($careStatuses as $status)
                    <option value="{{ $status->id }}" @selected( (string) old( 'care_status_id' ,
                        $defaultPendingStatusId )===(string) $status->id
                        )
                        >
                        {{ $status->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <label for="reminder_content" class="care-form-label">
                    Nội dung cần nhắc *
                </label>

                <textarea id="reminder_content" name="content" class="form-control care-form-control" rows="3"
                    placeholder="Ví dụ: Gọi lại hỏi tình trạng sử dụng sản phẩm..."
                    required>{{ old('content') }}</textarea>
            </div>

            <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn care-btn care-btn-primary">
                    Lưu lịch nhắc
                </button>
            </div>
        </form>
    </section>

    {{-- ================================================================
    | DANH SÁCH LỊCH NHẮC
    ================================================================= --}}
    <section class="care-panel care-table-panel care-glass">
        <div class="care-panel-heading">
            <div>
                <h2 class="care-section-title">
                    Lịch nhắc của khách hàng
                </h2>

                <p class="care-section-description">
                    Có {{ number_format($reminders->total()) }}
                    lịch chăm sóc.
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

                        <th style="width: 180px;">
                            Thời gian
                        </th>

                        <th style="width: 360px;">
                            Nội dung
                        </th>

                        <th style="width: 170px;">
                            Phụ trách
                        </th>

                        <th style="width: 145px;">
                            Trạng thái
                        </th>

                        <th style="width: 240px;" class="text-end">
                            Thao tác
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($reminders as $reminder)
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
                            <div class="care-note-preview">
                                {{
                                        $reminder->content
                                        ?: 'Chưa có nội dung'
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
                                @if(!$reminder->is_completed)
                                <form action="{{ route(
                                                'admin.customer-care.reminders.complete',
                                                [
                                                    'reminderId' =>
                                                        $reminder->id,
                                                ]
                                            ) }}" method="POST" data-confirm="Xác nhận hoàn thành lịch này?">
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
                                                        $reminder->id,
                                                ]
                                            ) }}" method="POST" data-confirm="Bạn muốn mở lại lịch này?">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" class="care-btn care-btn-warning">
                                        Mở lại
                                    </button>
                                </form>
                                @endif

                                <form action="{{ route(
                                            'admin.customer-care.reminders.destroy',
                                            [
                                                'reminderId' =>
                                                    $reminder->id,
                                            ]
                                        ) }}" method="POST" data-confirm="Bạn có chắc chắn muốn xóa lịch nhắc này?">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="care-btn care-btn-danger">
                                        Xóa
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="care-empty-state">
                            Khách hàng chưa có lịch nhắc chăm sóc.
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

{{-- ===================================================================
| MODAL SỬA NỘI DUNG TƯ VẤN
=================================================================== --}}
<div class="modal fade care-modal" id="editCareLogModal" tabindex="-1" aria-labelledby="editCareLogModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <form id="editCareLogForm" method="POST" action="">
                @csrf
                @method('PATCH')

                <div class="modal-header">
                    <h5 class="modal-title" id="editCareLogModalLabel">
                        Sửa nội dung tư vấn
                    </h5>

                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Đóng"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="edit_staff_id" class="care-form-label">
                                Người tư vấn
                            </label>

                            <select id="edit_staff_id" name="staff_id" class="form-select care-form-control">
                                <option value="">
                                    Tài khoản hiện tại
                                </option>

                                @foreach($staffMembers as $staff)
                                <option value="{{ $staff->id }}">
                                    {{ $staff->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="edit_care_channel_id" class="care-form-label">
                                Kênh tư vấn
                            </label>

                            <select id="edit_care_channel_id" name="care_channel_id"
                                class="form-select care-form-control">
                                <option value="">
                                    Chọn kênh tư vấn
                                </option>

                                @foreach($careChannels as $channel)
                                <option value="{{ $channel->id }}">
                                    {{ $channel->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="edit_care_date" class="care-form-label">
                                Ngày giờ tư vấn *
                            </label>

                            <input id="edit_care_date" type="datetime-local" name="care_date"
                                class="form-control care-form-control" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="edit_care_status_id" class="care-form-label">
                                Trạng thái
                            </label>

                            <select id="edit_care_status_id" name="care_status_id"
                                class="form-select care-form-control">
                                <option value="">
                                    Chọn trạng thái
                                </option>

                                @foreach($careStatuses as $status)
                                <option value="{{ $status->id }}">
                                    {{ $status->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="edit_content" class="care-form-label">
                                Nội dung đã tư vấn *
                            </label>

                            <textarea id="edit_content" name="content" class="form-control care-form-control" rows="6"
                                required></textarea>
                        </div>

                        <div class="col-12 col-lg-6">
                            <label for="edit_internal_note" class="care-form-label">
                                Ghi chú nội bộ
                            </label>

                            <textarea id="edit_internal_note" name="internal_note"
                                class="form-control care-form-control" rows="4"></textarea>
                        </div>

                        <div class="col-12 col-lg-6">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="edit_next_follow_up_at" class="care-form-label">
                                        Ngày giờ liên hệ lại
                                    </label>

                                    <input id="edit_next_follow_up_at" type="datetime-local" name="next_follow_up_at"
                                        class="form-control care-form-control">
                                </div>

                                <div class="col-12">
                                    <label for="edit_care_priority_id" class="care-form-label">
                                        Mức ưu tiên
                                    </label>

                                    <select id="edit_care_priority_id" name="care_priority_id"
                                        class="form-select care-form-control">
                                        <option value="">
                                            Chọn mức ưu tiên
                                        </option>

                                        @foreach($carePriorities as $priority)
                                        <option value="{{ $priority->id }}">
                                            {{ $priority->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn care-btn care-btn-secondary" data-bs-dismiss="modal">
                        Hủy
                    </button>

                    <button type="submit" class="btn care-btn care-btn-primary">
                        Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================================================================
| CẤU HÌNH JAVASCRIPT
================================================================= --}}
<div id="customerCareConfig" class="d-none" data-update-log-url-template="{{ route(
        'admin.customer-care.logs.update',
        [
            'logId' => '__LOG_ID__',
        ]
    ) }}" aria-hidden="true"></div>
@endsection

@push('scripts')
<script>
    (function() {
        'use strict';

        const configElement = document.getElementById(
            'customerCareConfig'
        );

        window.CustomerCareConfig = {
            updateLogUrlTemplate: configElement ?
                configElement.getAttribute(
                    'data-update-log-url-template'
                ) : ''
        };
    })();
</script>

<script src="{{ asset('admin/js/customer-care.js') }}"></script>
@endpush