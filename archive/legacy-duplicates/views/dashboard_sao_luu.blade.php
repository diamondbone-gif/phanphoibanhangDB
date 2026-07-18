@extends('admin.auth.dashboardAmin')

@section('title', 'Dashboard | BoneCare CRM')

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/bonecare-dashboard.css') }}">
@endpush

@section('admin_content')
<div id="bc-dashboard-data" class="d-none"
    data-chart="{{ base64_encode(json_encode($chartData ?? [], JSON_UNESCAPED_UNICODE)) }}">
</div>

<div class="bc-topbar">
    <div>
        <h1 class="bc-page-title">Chào buổi sáng, Admin! 👋</h1>
        <p class="bc-page-subtitle">Dữ liệu dưới đây được lấy trực tiếp từ database.</p>
    </div>

    <div class="bc-top-actions">
        <a href="{{ url('/admin/customers/create') }}" class="bc-btn">
            <i class="fa-solid fa-user-plus"></i>
            Khách hàng mới
        </a>

        <a href="{{ url('/admin/sales/orders/create') }}" class="bc-btn bc-btn-primary">
            <i class="fa-solid fa-plus"></i>
            Tạo đơn hàng
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-5 col-lg-12">
        <div class="bc-card h-100">
            <div class="bc-card-body">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="bc-mini-label">{{ $revenueProgress['title'] }}</div>
                        <div class="bc-big-number">{{ $revenueProgress['current'] }}</div>
                        <div class="bc-muted small fw-semibold">{{ $revenueProgress['description'] }}</div>
                    </div>

                    <div class="bc-pill">
                        <i class="fa-solid fa-chart-line"></i>
                        {{ $revenueProgress['badge'] }}
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-3">
                    @foreach($periodTabs as $key => $label)
                    <a href="{{ route('admin.dashboard', ['revenue_period' => $key]) }}"
                        class="bc-btn {{ $revenueProgress['period'] === $key ? 'bc-btn-primary' : '' }}">
                        {{ $label }}
                    </a>
                    @endforeach
                </div>

                <div class="bc-progress">
                    <div class="bc-progress-bar" data-progress="{{ $revenueProgress['percent'] ?? 0 }}">
                    </div>
                </div>

                <div class="bc-progress-footer">
                    <span>{{ $revenueProgress['start_label'] }}</span>
                    <span>{{ $revenueProgress['order_count'] }}</span>
                    <span>{{ $revenueProgress['end_label'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-7">
        <div class="bc-card h-100">
            <div class="bc-card-body">
                <div class="bc-mini-label mb-2">Phễu chuyển đổi (Tuần này)</div>

                @foreach($conversion as $item)
                <div class="bc-conversion-row">
                    <div class="bc-conversion-left">
                        <div class="bc-square-icon {{ $item['color'] }}">
                            <i class="{{ $item['icon'] }}"></i>
                        </div>

                        <div>
                            <div class="bc-row-subtitle">{{ $item['label'] }}</div>
                        </div>
                    </div>

                    <div class="bc-row-number">
                        {{ $item['value'] }}

                        @if($item['percent'])
                        <span class="bc-percent-green small">({{ $item['percent'] }})</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-5">
        <div class="bc-card h-100">
            <div class="bc-card-header">
                <h3 class="bc-card-title fs-6 text-uppercase text-secondary">Nguồn khách hàng</h3>
            </div>

            <div class="bc-donut-wrap">
                <canvas id="bcSourceChart"></canvas>
            </div>

            <div class="bc-legend-list">
                @foreach($sourceStats as $source)
                <div class="bc-legend-row">
                    <div class="bc-legend-left">
                        <span class="bc-dot {{ $source['dot'] }}"></span>
                        {{ $source['label'] }}
                    </div>
                    <strong>{{ $source['percent'] }}%</strong>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    @foreach($stats as $stat)
    <div class="col-xl-3 col-md-6">
        <div class="bc-card h-100">
            <div class="bc-stat-card">
                <div class="bc-square-icon {{ $stat['icon_color'] }}">
                    <i class="{{ $stat['icon'] }}"></i>
                </div>

                <div>
                    <div class="bc-mini-label">{{ $stat['label'] }}</div>
                    <div class="bc-stat-value">{{ $stat['value'] }}</div>

                    @if($stat['change_type'] === 'up')
                    <div class="bc-stat-change bc-percent-green">
                        <i class="fa-solid fa-arrow-trend-up"></i>
                        {{ $stat['change'] }}
                    </div>
                    @elseif($stat['change_type'] === 'down')
                    <div class="bc-stat-change bc-percent-red">
                        <i class="fa-solid fa-arrow-trend-down"></i>
                        {{ $stat['change'] }}
                    </div>
                    @else
                    <div class="bc-stat-change bc-muted">
                        {{ $stat['change'] }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="bc-card h-100">
            <div class="bc-card-header">
                <h3 class="bc-card-title">Biểu đồ doanh thu & Hoa hồng</h3>
                <span class="bc-pill">7 ngày gần nhất</span>
            </div>

            <div class="bc-chart-box">
                <canvas id="bcRevenueChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="bc-card h-100">
            <div class="bc-card-header">
                <h3 class="bc-card-title">Trạng thái đơn hàng</h3>
            </div>

            <div class="bc-donut-wrap">
                <canvas id="bcOrderStatusChart"></canvas>
            </div>

            <div class="bc-legend-list">
                @foreach($orderStatusStats as $status)
                <div class="bc-legend-row">
                    <div class="bc-legend-left">
                        <span class="bc-dot {{ $status['dot'] }}"></span>
                        {{ $status['label'] }}
                    </div>
                    <strong>{{ $status['percent'] }}%</strong>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-4 col-lg-6">
        <div class="bc-card h-100">
            <div class="bc-card-header">
                <h3 class="bc-card-title">Top Sản phẩm</h3>
                <a href="{{ url('/admin/products') }}" class="bc-link-small">Xem tất cả</a>
            </div>

            <div class="bc-list">
                @forelse($topProducts as $product)
                <div class="bc-list-row">
                    <div class="bc-list-left">
                        <div class="bc-square-icon soft-blue">
                            <i class="{{ $product['icon'] }}"></i>
                        </div>

                        <div>
                            <div class="bc-row-title">{{ $product['name'] }}</div>
                            <div class="bc-row-subtitle">{{ $product['desc'] }}</div>
                        </div>
                    </div>

                    <div class="bc-list-price">{{ $product['amount'] }}</div>
                </div>
                @empty
                <div class="bc-row-subtitle p-3">Chưa có sản phẩm bán ra trong tháng này.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-6">
        <div class="bc-card h-100">
            <div class="bc-card-header">
                <h3 class="bc-card-title">Top Bán Hàng</h3>
                <a href="{{ url('/admin/collaborators') }}" class="bc-link-small">Xem tất cả</a>
            </div>

            <div class="bc-list">
                @forelse($topSellers as $seller)
                <div class="bc-list-row">
                    <div class="bc-list-left">
                        <div class="bc-square-icon soft-green">
                            <i class="{{ $seller['icon'] }}"></i>
                        </div>

                        <div>
                            <div class="bc-row-title">{{ $seller['name'] }}</div>
                            <div class="bc-row-subtitle">{{ $seller['desc'] }}</div>
                        </div>
                    </div>

                    <div class="bc-list-price text-primary">{{ $seller['amount'] }}</div>
                </div>
                @empty
                <div class="bc-row-subtitle p-3">Chưa có dữ liệu bán hàng từ CTV trong tháng này.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="bc-card h-100">
            <div class="bc-card-header">
                <h3 class="bc-card-title">Cảnh báo tồn kho</h3>
                <a href="{{ url('/admin/inventory') }}" class="bc-link-small">Quản lý kho</a>
            </div>

            <div class="bc-list">
                @forelse($inventoryAlerts as $alert)
                <div class="bc-list-row">
                    <div class="bc-list-left">
                        <div class="bc-square-icon {{ $alert['icon_color'] }}">
                            <i class="{{ $alert['icon'] }}"></i>
                        </div>

                        <div>
                            <div class="bc-row-title">{{ $alert['name'] }}</div>
                            <div class="bc-row-subtitle">{{ $alert['desc'] }}</div>
                        </div>
                    </div>

                    <span class="bc-badge {{ $alert['badge_color'] }}">
                        {{ $alert['badge'] }}
                    </span>
                </div>
                @empty
                <div class="bc-row-subtitle p-3">Hiện chưa có sản phẩm dưới mức cảnh báo.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-6">
        <div class="bc-card h-100">
            <div class="bc-card-header">
                <h3 class="bc-card-title">Hoạt động gần đây</h3>
            </div>

            <div class="bc-timeline">
                @forelse($activities as $activity)
                <div class="bc-timeline-item">
                    <div class="bc-timeline-icon {{ $activity['color'] }}">
                        <i class="{{ $activity['icon'] }}"></i>
                    </div>

                    <div class="bc-row-title">{{ $activity['title'] }}</div>
                    <div class="bc-row-subtitle">{{ $activity['desc'] }}</div>
                    <div class="bc-row-subtitle mt-1">{{ $activity['time'] }}</div>
                </div>
                @empty
                <div class="bc-row-subtitle p-3">Chưa có hoạt động gần đây.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="bc-card h-100">
            <div class="bc-card-header">
                <h3 class="bc-card-title">Lịch chăm sóc (Hôm nay)</h3>
                <span class="bc-alert-pill">{{ count($careSchedules) }} cần gọi</span>
            </div>

            <div class="bc-list">
                @forelse($careSchedules as $schedule)
                <div class="bc-list-row">
                    <div>
                        <div class="bc-row-title">{{ $schedule['name'] }}</div>
                        <div class="bc-row-subtitle">
                            <i class="fa-solid fa-clock me-1"></i>
                            {{ $schedule['desc'] }}
                        </div>
                    </div>

                    <a href="tel:{{ $schedule['phone'] }}" class="bc-phone-btn">
                        <i class="fa-solid fa-phone"></i>
                    </a>
                </div>
                @empty
                <div class="bc-row-subtitle p-3">Hôm nay chưa có lịch chăm sóc cần gọi.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('admin/js/bonecare-dashboard.js') }}"></script>
@endpush