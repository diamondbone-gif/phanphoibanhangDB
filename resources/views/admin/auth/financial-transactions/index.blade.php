@extends('admin.auth.dashboardAmin')

@section('title', 'Sổ giao dịch tài chính')

@section('admin_content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h2 class="mb-1">Sổ giao dịch tài chính</h2><div class="text-muted">Phiếu thu, đặt cọc, hoàn tiền và điều chỉnh</div></div>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    <form class="card card-body mb-3" method="GET">
        <div class="row g-2">
            <div class="col-md-4"><select class="form-select" name="type"><option value="">Tất cả loại</option>@foreach($types as $type)<option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->value }}</option>@endforeach</select></div>
            <div class="col-md-4"><select class="form-select" name="status"><option value="">Tất cả trạng thái</option>@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->value }}</option>@endforeach</select></div>
            <div class="col-md-4"><button class="btn btn-primary">Lọc dữ liệu</button></div>
        </div>
    </form>
    <div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead><tr><th>Mã</th><th>Loại</th><th>Đơn/Phiếu hoàn</th><th>Số tiền</th><th>Phương thức</th><th>Trạng thái</th><th>Thời gian</th><th>Thao tác</th></tr></thead>
        <tbody>@forelse($transactions as $transaction)<tr>
            <td><strong>{{ $transaction->transaction_code }}</strong></td><td>{{ $transaction->type->value }}</td>
            <td>{{ $transaction->order?->order_code ?? '—' }}<br><small>{{ $transaction->orderReturn?->return_code }}</small></td>
            <td>{{ number_format((float)$transaction->amount, 0, ',', '.') }}đ</td><td>{{ $transaction->payment_method ?: '—' }}</td>
            <td><span class="badge bg-secondary">{{ $transaction->status->value }}</span>@if($transaction->failure_reason)<div class="small text-danger">{{ $transaction->failure_reason }}</div>@endif</td>
            <td>{{ $transaction->created_at?->format('d/m/Y H:i') }}</td><td class="d-flex gap-1 flex-wrap">
                @if($transaction->status === \App\Enums\FinancialTransactionState::Requested)<form method="POST" action="{{ route('admin.financial-transactions.approve', $transaction) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-success">Duyệt</button></form>@endif
                @if($transaction->status === \App\Enums\FinancialTransactionState::Approved)<form method="POST" action="{{ route('admin.financial-transactions.complete', $transaction) }}">@csrf @method('PATCH')<input class="form-control form-control-sm mb-1" name="bank_reference" placeholder="Mã ngân hàng"><button class="btn btn-sm btn-primary">Đã chi/thu</button></form>@endif
                @if($transaction->status !== \App\Enums\FinancialTransactionState::Completed)<form method="POST" action="{{ route('admin.financial-transactions.fail', $transaction) }}">@csrf @method('PATCH')<input class="form-control form-control-sm mb-1" name="failure_reason" required placeholder="Lý do thất bại"><button class="btn btn-sm btn-outline-danger">Thất bại</button></form>@endif
            </td>
        </tr>@empty<tr><td colspan="8" class="text-center text-muted py-4">Chưa có giao dịch.</td></tr>@endforelse</tbody>
    </table></div></div>
    <div class="mt-3">{{ $transactions->links() }}</div>
</div>
@endsection
