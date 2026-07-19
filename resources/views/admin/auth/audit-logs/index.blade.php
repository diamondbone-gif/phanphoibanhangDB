@extends('admin.auth.dashboardAmin')

@section('title', 'Nhật ký kiểm toán')

@section('admin_content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h2 class="mb-1">Nhật ký kiểm toán</h2>
        <div class="text-muted">Lịch sử chỉ đọc: người thao tác, IP, lý do và dữ liệu trước/sau.</div>
    </div>

    <form class="card card-body mb-3" method="GET">
        <div class="row g-2">
            <div class="col-md-2"><select class="form-select" name="event"><option value="">Mọi sự kiện</option>@foreach($events as $event)<option value="{{ $event }}" @selected(request('event') === $event)>{{ $event }}</option>@endforeach</select></div>
            <div class="col-md-3"><input class="form-control" name="subject" value="{{ request('subject') }}" placeholder="Loại dữ liệu"></div>
            <div class="col-md-2"><input class="form-control" name="ip" value="{{ request('ip') }}" placeholder="Địa chỉ IP"></div>
            <div class="col-md-2"><input type="date" class="form-control" name="from" value="{{ request('from') }}"></div>
            <div class="col-md-2"><input type="date" class="form-control" name="to" value="{{ request('to') }}"></div>
            <div class="col-md-1"><button class="btn btn-primary w-100">Lọc</button></div>
        </div>
    </form>

    <div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead><tr><th>Thời gian</th><th>Sự kiện</th><th>Đối tượng</th><th>Người/IP</th><th>Lý do</th><th>Thay đổi</th></tr></thead>
        <tbody>
        @forelse($logs as $log)
            <tr>
                <td class="text-nowrap">{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                <td><span class="badge bg-secondary">{{ $log->event }}</span></td>
                <td><div>{{ class_basename($log->auditable_type) }}</div><small class="text-muted">#{{ $log->auditable_id ?? '—' }} · {{ $log->route_name ?: 'CLI' }}</small></td>
                <td><div>#{{ $log->actor_id ?? 'hệ thống' }}</div><small class="text-muted">{{ $log->ip_address ?: '—' }}</small></td>
                <td>{{ $log->reason ?: '—' }}</td>
                <td style="min-width:320px">
                    @if($log->old_values)<details><summary>Trước</summary><pre class="small bg-light p-2 mb-1">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></details>@endif
                    @if($log->new_values)<details><summary>Sau</summary><pre class="small bg-light p-2 mb-0">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></details>@endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Chưa có nhật ký phù hợp.</td></tr>
        @endforelse
        </tbody>
    </table></div></div>
    <div class="mt-3">{{ $logs->links() }}</div>
</div>
@endsection
