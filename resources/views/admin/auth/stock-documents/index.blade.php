@extends('admin.auth.dashboardAmin')

@section('title', 'Chứng từ và nhiều kho')

@section('admin_content')
<div class="container-fluid py-4">
    <h2>Chứng từ và tồn nhiều kho</h2>
    <p class="text-muted">Tồn thực tế, giữ chỗ, khả dụng và giá vốn được cập nhật trong transaction.</p>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <form method="POST" action="{{ route('admin.warehouses.store') }}" class="card card-body mb-4">@csrf
        <h5>Thêm kho</h5><div class="row g-2"><div class="col-md-3"><input name="warehouse_code" class="form-control" required placeholder="Mã kho"></div><div class="col-md-4"><input name="warehouse_name" class="form-control" required placeholder="Tên kho"></div><div class="col-md-3"><input name="address" class="form-control" placeholder="Địa chỉ"></div><div class="col-md-2"><button class="btn btn-outline-primary w-100">Tạo kho</button></div></div>
    </form>

    <div class="card card-body mb-4">
        <h5>Tạo và ghi sổ chứng từ</h5>
        <form method="POST" action="{{ route('admin.stock-documents.store') }}">@csrf
            <div class="row g-2 mb-3">
                <div class="col-md-3"><label class="form-label">Loại phiếu</label><select name="document_type" class="form-select" required>@foreach($types as $type)<option value="{{ $type->value }}">{{ $type->value }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label">Kho nguồn</label><select name="source_warehouse_id" class="form-select"><option value="">—</option>@foreach($warehouses as $warehouse)<option value="{{ $warehouse->id }}">{{ $warehouse->warehouse_name }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label">Kho đích</label><select name="destination_warehouse_id" class="form-select"><option value="">—</option>@foreach($warehouses as $warehouse)<option value="{{ $warehouse->id }}">{{ $warehouse->warehouse_name }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label">Ngày chứng từ</label><input type="datetime-local" name="document_date" value="{{ now()->format('Y-m-d\TH:i') }}" class="form-control" required></div>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-md-4"><label class="form-label">Sản phẩm</label><select name="items[0][product_id]" class="form-select" required>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->product_code }} - {{ $product->product_name }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label">Lô (nếu quản lý lô)</label><select name="items[0][product_batch_id]" class="form-select"><option value="">—</option>@foreach($batches as $batch)<option value="{{ $batch->id }}">#{{ $batch->product_id }} / {{ $batch->batch_number }}</option>@endforeach</select></div>
                <div class="col-md-2"><label class="form-label">Số lượng</label><input type="number" min="1" name="items[0][quantity]" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label">Giá vốn/đơn vị</label><input type="number" min="0" step="0.01" name="items[0][unit_cost]" class="form-control"></div>
            </div>
            <div class="row g-2"><div class="col-md-8"><input name="reason" class="form-control" required placeholder="Lý do nghiệp vụ"></div><div class="col-md-4"><button class="btn btn-primary w-100">Ghi sổ chứng từ</button></div></div>
        </form>
    </div>

    <div class="card mb-4"><div class="card-header"><strong>Tồn theo kho</strong></div><div class="table-responsive"><table class="table table-sm align-middle mb-0">
        <thead><tr><th>Kho</th><th>Sản phẩm</th><th>Lô</th><th>Thực tế</th><th>Giữ chỗ</th><th>Khả dụng</th></tr></thead>
        <tbody>@forelse($stocks as $stock)<tr><td>{{ $stock->warehouse->warehouse_name }}</td><td>{{ $stock->product->product_name }}</td><td>{{ $stock->batch?->batch_number ?? '—' }}</td><td>{{ $stock->on_hand_quantity }}</td><td>{{ $stock->reserved_quantity }}</td><td><strong>{{ $stock->available_quantity }}</strong></td></tr>@empty<tr><td colspan="6" class="text-center py-3">Chưa có tồn kho.</td></tr>@endforelse</tbody>
    </table></div></div>

    <div class="card"><div class="card-header"><strong>Lịch sử chứng từ</strong></div><div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th>Mã</th><th>Loại</th><th>Kho nguồn</th><th>Kho đích</th><th>Số dòng</th><th>Trạng thái</th><th>Ngày</th><th>Lý do</th></tr></thead>
        <tbody>@forelse($documents as $document)<tr><td>{{ $document->document_code }}</td><td>{{ $document->document_type->value }}</td><td>{{ $document->sourceWarehouse?->warehouse_name ?? '—' }}</td><td>{{ $document->destinationWarehouse?->warehouse_name ?? '—' }}</td><td>{{ $document->items->count() }}</td><td>{{ $document->status->value }}</td><td>{{ $document->document_date?->format('d/m/Y H:i') }}</td><td>{{ $document->reason }}</td></tr>@empty<tr><td colspan="8" class="text-center py-3">Chưa có chứng từ.</td></tr>@endforelse</tbody>
    </table></div></div><div class="mt-3">{{ $documents->links() }}</div>
</div>
@endsection
