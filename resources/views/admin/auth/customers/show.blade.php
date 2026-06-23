@extends('admin.auth.dashboardAmin')

@section('title', 'Chi tiết khách hàng')

@section('admin_content')
@php
$genderLabel = match ($customer->gender) {
'male' => 'Nam',
'female' => 'Nữ',
'other' => 'Khác',
default => '—',
};

$isCtv = $customer->role?->code === 'ctv';

$birthDate = $customer->birth_date
? $customer->birth_date->format('d/m/Y')
: '—';

$fullAddress = collect([
$customer->detail?->address,
$customer->detail?->ward,
$customer->detail?->district,
$customer->detail?->province,
])->filter()->implode(', ');
@endphp

<div class="container-fluid customer-show-page">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.customers.index') }}">Khách hàng</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Chi tiết</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h3 class="mb-1">
                {{ $customer->full_name }}

                @if($isCtv)
                <span class="badge bg-primary ms-2">CTV</span>
                @else
                <span class="badge bg-secondary ms-2">Khách hàng</span>
                @endif
            </h3>

            <div class="text-muted">
                Mã KH: <strong>{{ $customer->customer_code }}</strong>
                <span class="mx-2">|</span>
                SĐT: <strong>{{ $customer->phone }}</strong>
            </div>
        </div>

        <a href="{{ route('admin.customers.index') }}" class="btn btn-light border">
            <i class="fa-solid fa-arrow-left me-1"></i>
            Quay lại
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="summary-card">
                <div class="summary-label">Tổng đơn hàng</div>
                <div class="summary-value">{{ $customer->orders->count() }}</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="summary-card">
                <div class="summary-label">Tổng tiền đã mua</div>
                <div class="summary-value">
                    {{ number_format($totalOrderAmount, 0, ',', '.') }}đ
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="summary-card">
                <div class="summary-label">Khách đã giới thiệu</div>
                <div class="summary-value">{{ $customer->givenReferrals->count() }}</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="summary-card">
                <div class="summary-label">Hoa hồng phát sinh</div>
                <div class="summary-value">
                    {{ number_format($totalCommissionAsReferrer, 0, ',', '.') }}đ
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <ul class="nav nav-pills customer-tabs mb-4" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-info" type="button">
                        Thông tin chung
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-referral" type="button">
                        Giới thiệu / CTV
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-orders" type="button">
                        Lịch sử mua hàng
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-care" type="button">
                        Lịch sử chăm sóc
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link text-warning fw-bold" data-bs-toggle="pill" data-bs-target="#tab-commission"
                        type="button">
                        Hoa hồng CTV
                    </button>
                </li>
            </ul>

            <div class="tab-content">

                {{-- TAB THÔNG TIN --}}
                <div class="tab-pane fade show active" id="tab-info">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h5 class="section-title">Thông tin cá nhân</h5>

                            <table class="table detail-table">
                                <tr>
                                    <td class="label">Họ tên</td>
                                    <td>{{ $customer->full_name }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Mã KH</td>
                                    <td>{{ $customer->customer_code }}</td>
                                </tr>
                                <tr>
                                    <td class="label">SĐT</td>
                                    <td>{{ $customer->phone }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Email</td>
                                    <td>{{ $customer->email ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Giới tính</td>
                                    <td>{{ $genderLabel }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Ngày sinh</td>
                                    <td>{{ $birthDate }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h5 class="section-title">Phân loại khách hàng</h5>

                            <table class="table detail-table">
                                <tr>
                                    <td class="label">Loại khách</td>
                                    <td>{{ $customer->type?->name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Vai trò</td>
                                    <td>{{ $customer->role?->name ?? 'Khách hàng' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Trạng thái KH</td>
                                    <td>{{ $customer->status?->name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Trạng thái CTV</td>
                                    <td>{{ $customer->ctvStatus?->name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Ngày tạo</td>
                                    <td>{{ $customer->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-12">
                            <h5 class="section-title">Địa chỉ / Nhu cầu / Ghi chú</h5>

                            <div class="info-box">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="mini-label">Địa chỉ</div>
                                        <div>{{ $fullAddress ?: '—' }}</div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mini-label">Khách mua cho ai?</div>
                                        <div>{{ $buyForOptionName ?? '—' }}</div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mini-label">Sản phẩm quan tâm</div>
                                        <div>{{ $interestedProductName ?? '—' }}</div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mini-label">Nhu cầu quan tâm</div>
                                        <div>
                                            @forelse($customerNeeds as $need)
                                            <span class="badge badge-soft-primary me-1">
                                                {{ $need->name }}
                                            </span>
                                            @empty
                                            —
                                            @endforelse
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mini-label text-danger">Ghi chú bệnh lý / đặc biệt</div>
                                        <div>{{ $customer->detail?->medical_note ?? '—' }}</div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mini-label">Ghi chú tư vấn ban đầu</div>
                                        <div>{{ $customer->detail?->consultation_note ?? $customer->note ?? '—' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TAB GIỚI THIỆU --}}
                <div class="tab-pane fade" id="tab-referral">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h5 class="section-title">Người giới thiệu khách này</h5>

                            @if($customer->receivedReferral && $customer->receivedReferral->referrer)
                            @php
                            $referrer = $customer->receivedReferral->referrer;
                            @endphp

                            <div class="info-box">
                                <div class="fw-bold text-primary mb-2">
                                    {{ $referrer->full_name }}
                                </div>

                                <div>SĐT: <strong>{{ $referrer->phone }}</strong></div>
                                <div>Mã KH: <strong>{{ $referrer->customer_code }}</strong></div>
                                <div>ID nội bộ: <strong>{{ $referrer->id }}</strong></div>
                                <div>Vai trò: <strong>{{ $referrer->role?->name ?? 'Khách hàng' }}</strong></div>

                                <div class="mt-2">
                                    Tỷ lệ hoa hồng lượt giới thiệu:
                                    <strong>{{ $customer->receivedReferral->commission_rate ?? 0 }}%</strong>
                                </div>

                                <div class="text-muted mt-2">
                                    Ngày bắt đầu:
                                    {{ $customer->receivedReferral->started_at?->format('d/m/Y H:i') ?? '—' }}
                                </div>
                            </div>
                            @else
                            <div class="alert alert-light border mb-0">
                                Khách này chưa có người giới thiệu.
                            </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <h5 class="section-title">Khách mà người này đã giới thiệu</h5>

                            @if($customer->givenReferrals->count())
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Khách được giới thiệu</th>
                                            <th>SĐT</th>
                                            <th>Hoa hồng</th>
                                            <th>Ngày</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($customer->givenReferrals as $referral)
                                        <tr>
                                            <td>{{ $referral->referred?->full_name ?? '—' }}</td>
                                            <td>{{ $referral->referred?->phone ?? '—' }}</td>
                                            <td>{{ $referral->commission_rate ?? 0 }}%</td>
                                            <td>{{ $referral->started_at?->format('d/m/Y') ?? '—' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="alert alert-light border mb-0">
                                Người này chưa giới thiệu khách nào.
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- TAB ĐƠN HÀNG --}}
                <div class="tab-pane fade" id="tab-orders">
                    <div
                        class="alert alert-info border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div><strong>Tổng số đơn:</strong> {{ $customer->orders->count() }} đơn</div>
                        <div><strong>Tổng tiền:</strong> {{ number_format($totalOrderAmount, 0, ',', '.') }}đ</div>
                    </div>

                    @if($customer->orders->count())
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Ngày mua</th>
                                    <th>Tổng tiền</th>
                                    <th>Tiền tính HH</th>
                                    <th>Sản phẩm</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customer->orders as $order)
                                <tr>
                                    <td>{{ $order->order_code }}</td>
                                    <td>{{ $order->order_date?->format('d/m/Y') ?? '—' }}</td>
                                    <td>{{ number_format($order->total_amount, 0, ',', '.') }}đ</td>
                                    <td>{{ number_format($order->commission_base_amount, 0, ',', '.') }}đ</td>
                                    <td>
                                        @forelse($order->items as $item)
                                        <div>
                                            {{ $item->product_name }}
                                            x {{ $item->quantity }}
                                        </div>
                                        @empty
                                        —
                                        @endforelse
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-light border mb-0">
                        Khách này chưa có đơn hàng.
                    </div>
                    @endif
                </div>

                {{-- TAB CHĂM SÓC --}}
                <div class="tab-pane fade" id="tab-care">
                    <div class="row g-4">
                        <div class="col-md-7">
                            <h5 class="section-title">Lịch sử chăm sóc</h5>

                            @if($customer->careLogs->count())
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Ngày</th>
                                            <th>Nhân viên</th>
                                            <th>Nội dung</th>
                                            <th>Chăm sóc lại</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($customer->careLogs as $log)
                                        <tr>
                                            <td>{{ $log->care_date?->format('d/m/Y H:i') ?? '—' }}</td>
                                            <td>{{ $log->staff?->name ?? '—' }}</td>
                                            <td>
                                                <div>{{ $log->content ?? '—' }}</div>
                                                @if($log->internal_note)
                                                <small class="text-muted">
                                                    Nội bộ: {{ $log->internal_note }}
                                                </small>
                                                @endif
                                            </td>
                                            <td>{{ $log->next_follow_up_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="alert alert-light border mb-0">
                                Chưa có lịch sử chăm sóc.
                            </div>
                            @endif
                        </div>

                        <div class="col-md-5">
                            <h5 class="section-title">Lịch nhắc chăm sóc</h5>

                            @if($customer->careReminders->count())
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Ngày nhắc</th>
                                            <th>Nội dung</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($customer->careReminders as $reminder)
                                        <tr>
                                            <td>
                                                {{ $reminder->reminder_date?->format('d/m/Y') ?? '—' }}
                                                @if($reminder->reminder_time)
                                                {{ $reminder->reminder_time }}
                                                @endif
                                            </td>
                                            <td>{{ $reminder->content ?? '—' }}</td>
                                            <td>
                                                @if($reminder->completed_at)
                                                <span class="badge bg-success">Đã hoàn thành</span>
                                                @else
                                                <span class="badge badge-soft-warning">Đang nhắc</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="alert alert-light border mb-0">
                                Chưa có lịch nhắc chăm sóc.
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- TAB HOA HỒNG --}}
                <div class="tab-pane fade" id="tab-commission">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="summary-card">
                                <div class="summary-label">Tổng hoa hồng</div>
                                <div class="summary-value">
                                    {{ number_format($totalCommissionAsReferrer, 0, ',', '.') }}đ
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="summary-card">
                                <div class="summary-label">Đã chi</div>
                                <div class="summary-value">
                                    {{ number_format($totalPaidCommissionAsReferrer, 0, ',', '.') }}đ
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="summary-card">
                                <div class="summary-label">Chưa chi</div>
                                <div class="summary-value">
                                    {{ number_format($totalPendingCommissionAsReferrer, 0, ',', '.') }}đ
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($commissionsAsReferrer->count())
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Đơn hàng</th>
                                    <th>Khách được giới thiệu</th>
                                    <th>Giá trị đơn</th>
                                    <th>Tỷ lệ</th>
                                    <th>Hoa hồng</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($commissionsAsReferrer as $commission)
                                <tr>
                                    <td>{{ $commission->order_code ?? '—' }}</td>
                                    <td>
                                        {{ $commission->referred_name ?? '—' }}
                                        @if($commission->referred_phone)
                                        <br>
                                        <small class="text-muted">{{ $commission->referred_phone }}</small>
                                        @endif
                                    </td>
                                    <td>{{ number_format($commission->order_amount, 0, ',', '.') }}đ</td>
                                    <td>{{ $commission->commission_rate }}%</td>
                                    <td>{{ number_format($commission->commission_amount, 0, ',', '.') }}đ</td>
                                    <td>
                                        @if($commission->paid_at)
                                        <span class="badge bg-success">Đã chi</span>
                                        @elseif($commission->approved_at)
                                        <span class="badge bg-primary">Đã duyệt</span>
                                        @elseif($commission->cancelled_reason)
                                        <span class="badge bg-danger">Đã hủy</span>
                                        @else
                                        <span class="badge badge-soft-warning">Chờ xử lý</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-light border mb-0">
                        Người này chưa phát sinh hoa hồng.
                    </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    .customer-show-page .card {
        border-radius: 18px;
    }

    .summary-card {
        background: #ffffff;
        border: 1px solid #e5edf7;
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 8px 22px rgba(36, 58, 94, 0.05);
        height: 100%;
    }

    .summary-label {
        color: #6b7890;
        font-size: 0.85rem;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .summary-value {
        color: #111827;
        font-size: 1.3rem;
        font-weight: 800;
    }

    .customer-tabs .nav-link {
        border-radius: 999px;
        font-weight: 700;
        color: #526179;
    }

    .customer-tabs .nav-link.active {
        background: #0d6efd;
        color: #ffffff;
    }

    .section-title {
        color: #0d6efd;
        font-weight: 800;
        margin-bottom: 14px;
    }

    .detail-table .label {
        width: 170px;
        color: #6b7890;
        font-weight: 700;
    }

    .info-box {
        border: 1px solid #e5edf7;
        background: #f8fafc;
        border-radius: 14px;
        padding: 16px;
    }

    .mini-label {
        font-size: 0.8rem;
        color: #6b7890;
        font-weight: 800;
        margin-bottom: 4px;
        text-transform: uppercase;
    }

    .badge-soft-primary {
        background-color: #eaf2ff;
        color: #1d4ed8;
    }

    .badge-soft-warning {
        background-color: #fff4d6;
        color: #845400;
    }

    @media (max-width: 575.98px) {
        .detail-table .label {
            width: 120px;
        }

        .customer-tabs {
            gap: 6px;
        }

        .customer-tabs .nav-link {
            font-size: 0.85rem;
            padding: 8px 10px;
        }
    }
</style>
@endpush