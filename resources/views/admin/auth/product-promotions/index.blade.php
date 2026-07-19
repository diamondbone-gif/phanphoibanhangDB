@extends('admin.auth.dashboardAmin')
@section('title', 'Combo và khuyến mãi')
@push('styles')<link rel="stylesheet" href="{{ asset('admin/css/pages/product-management.css') }}">@endpush

@section('admin_content')
<div class="pm-page">
    <header class="pm-header"><div><span class="pm-eyebrow">Kho sản phẩm</span><h1>Combo & khuyến mãi</h1><p>Tạo gói sản phẩm, giảm theo phần trăm, số tiền hoặc giá combo cố định.</p></div><span class="pm-count">{{ $promotions->total() }} chương trình</span></header>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <section class="pm-card">
        <h2>Tạo chương trình</h2>
        <form method="POST" action="{{ route('admin.product-promotions.store') }}" class="pm-grid promotion-form">@csrf
            @include('admin.auth.product-promotions.partials.fields', ['promotion' => null])
            <div class="pm-actions pm-span"><button class="pm-btn pm-btn-primary">Lưu chương trình</button></div>
        </form>
    </section>

    <section class="pm-card">
        <form class="pm-search"><input name="keyword" value="{{ request('keyword') }}" placeholder="Tìm mã hoặc tên chương trình..."><button class="pm-btn">Tìm kiếm</button><a href="{{ route('admin.product-promotions.index') }}">Đặt lại</a></form>
        <div class="pm-list">
        @forelse($promotions as $promotion)
            <details class="pm-item"><summary><span><strong>{{ $promotion->name }}</strong><small>{{ $promotion->code }} · {{ $promotion->promotion_type === 'combo' ? 'Combo' : 'Giảm sản phẩm' }} · {{ $promotion->items->count() }} sản phẩm</small></span><span class="pm-status {{ $promotion->is_active ? 'is-active' : '' }}">{{ $promotion->is_active ? 'Hoạt động' : 'Tạm ẩn' }}</span></summary>
                <form method="POST" action="{{ route('admin.product-promotions.update', $promotion) }}" class="pm-grid pm-edit promotion-form">@csrf @method('PUT') @include('admin.auth.product-promotions.partials.fields', compact('promotion'))<div class="pm-actions pm-span"><button class="pm-btn pm-btn-primary">Lưu thay đổi</button></div></form>
                <form method="POST" action="{{ route('admin.product-promotions.destroy', $promotion) }}" onsubmit="return confirm('Xóa chương trình này?')">@csrf @method('DELETE')<button class="pm-btn pm-btn-danger">Xóa chương trình</button></form>
            </details>
        @empty<div class="pm-empty">Chưa có chương trình phù hợp.</div>@endforelse
        </div>{{ $promotions->links() }}
    </section>
</div>
@endsection

@push('scripts')<script src="{{ asset('admin/js/product-promotions.js') }}"></script>@endpush
