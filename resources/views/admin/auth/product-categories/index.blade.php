@extends('admin.auth.dashboardAmin')
@section('title', 'Danh mục sản phẩm')
@push('styles')<link rel="stylesheet" href="{{ asset('admin/css/pages/product-management.css') }}">@endpush

@section('admin_content')
<div class="pm-page">
    <header class="pm-header"><div><span class="pm-eyebrow">Kho sản phẩm</span><h1>Danh mục sản phẩm</h1><p>Phân nhóm sản phẩm để tìm kiếm, báo cáo và thiết lập chính sách chính xác.</p></div><span class="pm-count">{{ $categories->total() }} danh mục</span></header>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <section class="pm-card">
        <h2>Thêm danh mục</h2>
        <form method="POST" action="{{ route('admin.product-categories.store') }}" class="pm-grid">@csrf
            <label>Mã danh mục<input name="code" value="{{ old('code') }}" required maxlength="50"></label>
            <label>Tên danh mục<input name="name" value="{{ old('name') }}" required maxlength="150"></label>
            <label>Thứ tự<input name="sort_order" type="number" min="0" value="{{ old('sort_order', 0) }}"></label>
            <label class="pm-check"><input name="is_active" type="checkbox" value="1" checked> Đang hoạt động</label>
            <label class="pm-span">Mô tả<textarea name="description" rows="2">{{ old('description') }}</textarea></label>
            <div class="pm-actions pm-span"><button class="pm-btn pm-btn-primary">Thêm danh mục</button></div>
        </form>
    </section>

    <section class="pm-card">
        <form class="pm-search"><input name="keyword" value="{{ request('keyword') }}" placeholder="Tìm theo mã hoặc tên..."><button class="pm-btn">Tìm kiếm</button><a href="{{ route('admin.product-categories.index') }}">Đặt lại</a></form>
        <div class="pm-list">
            @forelse($categories as $category)
            <details class="pm-item">
                <summary><span><strong>{{ $category->name }}</strong><small>{{ $category->code }} · {{ $category->products_count }} sản phẩm</small></span><span class="pm-status {{ $category->is_active ? 'is-active' : '' }}">{{ $category->is_active ? 'Hoạt động' : 'Tạm ẩn' }}</span></summary>
                <form method="POST" action="{{ route('admin.product-categories.update', $category) }}" class="pm-grid pm-edit">@csrf @method('PUT')
                    <label>Mã<input name="code" value="{{ $category->code }}" required></label><label>Tên<input name="name" value="{{ $category->name }}" required></label><label>Thứ tự<input name="sort_order" type="number" min="0" value="{{ $category->sort_order }}"></label><label class="pm-check"><input name="is_active" type="checkbox" value="1" @checked($category->is_active)> Hoạt động</label><label class="pm-span">Mô tả<textarea name="description" rows="2">{{ $category->description }}</textarea></label><div class="pm-actions pm-span"><button class="pm-btn pm-btn-primary">Lưu thay đổi</button></div>
                </form>
                <form method="POST" action="{{ route('admin.product-categories.destroy', $category) }}" onsubmit="return confirm('Xóa danh mục này?')">@csrf @method('DELETE')<button class="pm-btn pm-btn-danger" @disabled($category->products_count > 0)>Xóa danh mục</button></form>
            </details>
            @empty<div class="pm-empty">Chưa có danh mục phù hợp.</div>@endforelse
        </div>{{ $categories->links() }}
    </section>
</div>
@endsection
