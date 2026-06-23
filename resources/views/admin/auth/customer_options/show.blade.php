@extends('admin.auth.dashboardAmin')

@section('title', 'Chi tiết ' . $config['title'])

@section('admin_content')
@php
$editUrl = \Illuminate\Support\Facades\URL::signedRoute('admin.customer-options.edit', [
'type' => $type,
'id' => $item->id,
]);

$deleteUrl = \Illuminate\Support\Facades\URL::signedRoute('admin.customer-options.destroy', [
'type' => $type,
'id' => $item->id,
]);

$codeValue = $config['is_product'] ? $item->product_code : $item->code;
$nameValue = $config['is_product'] ? $item->product_name : $item->name;
@endphp

<div class="container-fluid">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">Cài đặt</li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.customer-options.index', ['type' => $type]) }}">
                    {{ $config['title'] }}
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Chi tiết</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
        <div>
            <h3 class="mb-1">{{ $nameValue }}</h3>
            <p class="text-muted mb-0">
                Chi tiết dữ liệu đang dùng trong form khách hàng.
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.customer-options.index', ['type' => $type]) }}" class="btn btn-light border">
                <i class="fa-solid fa-arrow-left me-1"></i>
                Quay lại
            </a>

            <a href="{{ $editUrl }}" class="btn btn-primary">
                <i class="fa-regular fa-pen-to-square me-1"></i>
                Sửa
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <table class="table detail-table">
                <tr>
                    <td class="label">ID</td>
                    <td>{{ $item->id }}</td>
                </tr>

                <tr>
                    <td class="label">{{ $config['code_label'] }}</td>
                    <td>{{ $codeValue }}</td>
                </tr>

                <tr>
                    <td class="label">{{ $config['name_label'] }}</td>
                    <td>{{ $nameValue }}</td>
                </tr>

                @if($config['is_product'])
                <tr>
                    <td class="label">Giá bán</td>
                    <td>{{ number_format($item->price ?? 0, 0, ',', '.') }}đ</td>
                </tr>

                <tr>
                    <td class="label">Loại sản phẩm ID</td>
                    <td>{{ $item->product_category_id ?? '—' }}</td>
                </tr>
                @else
                <tr>
                    <td class="label">Mô tả</td>
                    <td>{{ $item->description ?? '—' }}</td>
                </tr>

                <tr>
                    <td class="label">Thứ tự hiển thị</td>
                    <td>{{ $item->sort_order ?? 0 }}</td>
                </tr>
                @endif

                <tr>
                    <td class="label">Trạng thái</td>
                    <td>
                        @if($item->is_active)
                        <span class="badge badge-soft-success">Đang bật</span>
                        @else
                        <span class="badge badge-soft-secondary">Đang tắt</span>
                        @endif
                    </td>
                </tr>

                <tr>
                    <td class="label">Ngày tạo</td>
                    <td>{{ $item->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                </tr>

                <tr>
                    <td class="label">Cập nhật</td>
                    <td>{{ $item->updated_at?->format('d/m/Y H:i') ?? '—' }}</td>
                </tr>
            </table>
        </div>

        <div class="card-footer bg-white d-flex justify-content-end">
            <form method="POST" action="{{ $deleteUrl }}"
                onsubmit="return confirm('Bạn có chắc muốn xóa dữ liệu này không?');">
                @csrf
                @method('DELETE')

                <button class="btn btn-outline-danger" type="submit">
                    <i class="fa-solid fa-trash me-1"></i>
                    Xóa dữ liệu
                </button>
            </form>
        </div>
    </div>

</div>
@endsection