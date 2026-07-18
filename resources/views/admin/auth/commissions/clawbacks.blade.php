@extends('admin.auth.dashboardAmin')

@section('title', 'Thu hồi hoa hồng do hoàn đơn')

@section('admin_content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h2 class="mb-1">Thu hồi hoa hồng do hoàn đơn</h2><div class="text-muted">Theo dõi khách hoàn hàng và khoản CTV phải hoàn hoặc khấu trừ kỳ sau.</div></div>
        <a href="{{ route('admin.commissions.index') }}" class="btn btn-outline-primary">Quay lại hoa hồng</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card card-body"><small class="text-muted">Tổng phải thu hồi</small><strong class="fs-4 text-danger">{{ number_format((float)$summary->total, 0, ',', '.') }}đ</strong></div></div>
        <div class="col-md-4"><div class="card card-body"><small class="text-muted">Đã thu hồi/khấu trừ</small><strong class="fs-4 text-success">{{ number_format((float)$summary->recovered, 0, ',', '.') }}đ</strong></div></div>
        <div class="col-md-4"><div class="card card-body"><small class="text-muted">CTV còn phải hoàn</small><strong class="fs-4 text-warning">{{ number_format((float)$summary->outstanding, 0, ',', '.') }}đ</strong></div></div>
    </div>

    @forelse($adjustments as $item)
        @php($remaining = max(0, (float)$item->amount - (float)$item->recovered_amount))
        <div class="card mb-3 border-{{ $remaining > 0 ? 'warning' : 'success' }}">
            <div class="card-header d-flex justify-content-between flex-wrap gap-2">
                <div><strong>{{ $item->adjustment_code }}</strong> · CTV: <strong>{{ $item->ctv_name }}</strong> ({{ $item->ctv_phone }})</div>
                <span class="badge bg-{{ $remaining > 0 ? 'warning text-dark' : 'success' }}">{{ $remaining > 0 ? 'Còn phải thu hồi' : 'Đã xử lý đủ' }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-8">
                        <div class="table-responsive"><table class="table table-sm align-middle">
                            <tr><th>Khách hoàn hàng</th><td>{{ $item->buyer_name }} · {{ $item->buyer_phone }}</td><th>Ngày hoàn</th><td>{{ $item->returned_at ? \Carbon\Carbon::parse($item->returned_at)->format('d/m/Y H:i') : '—' }}</td></tr>
                            <tr><th>Đơn hàng</th><td>{{ $item->order_code }}</td><th>Phiếu hoàn</th><td>{{ $item->return_code ?: '—' }}</td></tr>
                            <tr><th>Giá trị đơn ban đầu</th><td>{{ number_format((float)$item->final_amount, 0, ',', '.') }}đ</td><th>Khách được hoàn</th><td>{{ number_format((float)$item->refund_amount, 0, ',', '.') }}đ</td></tr>
                            <tr><th>Giá trị còn lại</th><td>{{ number_format((float)$item->net_amount, 0, ',', '.') }}đ</td><th>Tỷ lệ hoa hồng</th><td>{{ $item->commission_rate_percent }}%</td></tr>
                            <tr><th>Hoa hồng hợp lệ mới</th><td>{{ number_format((float)$item->commission_amount, 0, ',', '.') }}đ</td><th>Đã chi cho CTV</th><td>{{ number_format((float)$item->paid_amount, 0, ',', '.') }}đ</td></tr>
                            <tr class="table-warning"><th>Phải thu hồi</th><td>{{ number_format((float)$item->amount, 0, ',', '.') }}đ</td><th>Còn lại</th><td><strong>{{ number_format($remaining, 0, ',', '.') }}đ</strong></td></tr>
                        </table></div>
                        @foreach($recoveries->get($item->id, collect()) as $recovery)
                            <div class="small border-top py-2">{{ \Carbon\Carbon::parse($recovery->recovered_date)->format('d/m/Y') }} · {{ number_format((float)$recovery->amount, 0, ',', '.') }}đ · {{ ['offset_next_payout'=>'Khấu trừ kỳ sau','cash'=>'CTV hoàn tiền mặt','bank_transfer'=>'CTV chuyển khoản'][$recovery->recovery_method] ?? $recovery->recovery_method }} {{ $recovery->note ? '· '.$recovery->note : '' }}</div>
                        @endforeach
                    </div>
                    <div class="col-lg-4">
                        @if($remaining > 0)
                        <form method="POST" action="{{ route('admin.commissions.clawbacks.recover', $item->id) }}" class="border rounded p-3 bg-light">
                            @csrf
                            <h6>Ghi nhận xử lý</h6>
                            <div class="mb-2"><label class="form-label">Số tiền</label><input class="form-control" type="number" name="amount" min="1" max="{{ $remaining }}" value="{{ $remaining }}" required></div>
                            <div class="mb-2"><label class="form-label">Hình thức</label><select class="form-select" name="recovery_method" required><option value="offset_next_payout">Khấu trừ kỳ hoa hồng sau</option><option value="bank_transfer">CTV chuyển khoản hoàn</option><option value="cash">CTV hoàn tiền mặt</option></select></div>
                            <div class="mb-2"><label class="form-label">Ngày xử lý</label><input class="form-control" type="date" name="recovered_date" value="{{ now()->toDateString() }}" required></div>
                            <div class="mb-3"><label class="form-label">Ghi chú/chứng từ</label><textarea class="form-control" name="note" rows="2"></textarea></div>
                            <button class="btn btn-primary w-100">Xác nhận thu hồi</button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-info">Chưa có khoản hoa hồng nào phải thu hồi do hoàn đơn.</div>
    @endforelse
    {{ $adjustments->links() }}
</div>
@endsection
