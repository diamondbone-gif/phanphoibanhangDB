@extends('admin.auth.dashboardAmin')

@section('title', $item ? 'Sửa ' . $config['title'] : 'Thêm ' . $config['title'])

@section('admin_content')
<div class="container-fluid">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">Cài đặt</li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.customer-options.index', ['type' => $type]) }}">
                    {{ $config['title'] }}
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                {{ $item ? 'Sửa' : 'Thêm mới' }}
            </li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
        <div>
            <h3 class="mb-1">
                {{ $item ? 'Sửa ' . $config['title'] : 'Thêm ' . $config['title'] }}
            </h3>
            <p class="text-muted mb-0">
                Dữ liệu này sẽ hiển thị trong form thêm/sửa khách hàng.
            </p>
        </div>

        <a href="{{ route('admin.customer-options.index', ['type' => $type]) }}" class="btn btn-light border">
            <i class="fa-solid fa-arrow-left me-1"></i>
            Quay lại danh sách
        </a>
    </div>

    @if($errors->any())
    <div class="alert alert-danger">
        <strong>Vui lòng kiểm tra lại thông tin:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ $formAction }}" autocomplete="off">
        @csrf

        @if($method === 'PUT')
        @method('PUT')
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3">

                    @if($config['is_product'])
                    <div class="col-md-6">
                        <label class="form-label">
                            Mã sản phẩm <span class="text-danger">*</span>
                        </label>

                        <input class="form-control @error('product_code') is-invalid @enderror" name="product_code"
                            value="{{ old('product_code', $item->product_code ?? '') }}" type="text" maxlength="50"
                            required>

                        @error('product_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Tên sản phẩm <span class="text-danger">*</span>
                        </label>

                        <input class="form-control @error('product_name') is-invalid @enderror" name="product_name"
                            value="{{ old('product_name', $item->product_name ?? '') }}" type="text" maxlength="255"
                            required>

                        @error('product_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Loại sản phẩm</label>

                        <select class="form-select @error('product_category_id') is-invalid @enderror"
                            name="product_category_id">
                            <option value="">-- Chọn loại sản phẩm --</option>

                            @foreach($productCategories as $category)
                            <option value="{{ $category->id }}" @selected(old('product_category_id', $item->
                                product_category_id ?? '') == $category->id)>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>

                        @error('product_category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Giá bán</label>

                        <input class="form-control @error('price') is-invalid @enderror" name="price"
                            value="{{ old('price', $item->price ?? 0) }}" type="number" min="0" step="1000">

                        @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @else
                    <div class="col-md-6">
                        <label class="form-label">
                            {{ $config['code_label'] }} <span class="text-danger">*</span>
                        </label>

                        <input class="form-control @error('code') is-invalid @enderror" name="code"
                            value="{{ old('code', $item->code ?? '') }}" type="text" maxlength="50" required>

                        @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            {{ $config['name_label'] }} <span class="text-danger">*</span>
                        </label>

                        <input class="form-control @error('name') is-invalid @enderror" name="name"
                            value="{{ old('name', $item->name ?? '') }}" type="text" maxlength="150" required>

                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Mô tả</label>

                        <textarea class="form-control @error('description') is-invalid @enderror" name="description"
                            rows="4">{{ old('description', $item->description ?? '') }}</textarea>

                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Thứ tự hiển thị</label>

                        <input class="form-control @error('sort_order') is-invalid @enderror" name="sort_order"
                            value="{{ old('sort_order', $item->sort_order ?? 0) }}" type="number" min="0">

                        @error('sort_order')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @endif

                    <div class="col-md-6">
                        <label class="form-label">Trạng thái</label>

                        <select class="form-select" name="is_active">
                            <option value="1" @selected(old('is_active', $item->is_active ?? 1) == 1)>
                                Bật
                            </option>
                            <option value="0" @selected(old('is_active', $item->is_active ?? 1) == 0)>
                                Tắt
                            </option>
                        </select>
                    </div>

                </div>
            </div>

            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="{{ route('admin.customer-options.index', ['type' => $type]) }}"
                    class="btn btn-light border px-4">
                    Hủy
                </a>

                <button class="btn btn-primary px-4" type="submit">
                    <i class="fa-solid fa-save me-1"></i>
                    {{ $item ? 'Lưu thay đổi' : 'Thêm mới' }}
                </button>
            </div>
        </div>
    </form>

</div>
@endsection