@extends('admin.auth.dashboardAmin')

@section('admin_content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">Bán hàng</li>
        <li class="breadcrumb-item active">Sửa đơn hàng</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Sửa đơn hàng {{ $order->order_code }}</h3>

    <a href="{{ route('admin.orders.index') }}" class="btn btn-light border">
        <i class="fa-solid fa-arrow-left"></i> Quay lại
    </a>
</div>

@include('admin.auth.partials.alerts')

<form method="POST" action="{{ route('admin.orders.update', $order) }}" id="orderForm">
    @csrf
    @method('PUT')

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-primary">
                        <i class="fa-solid fa-user me-2"></i>Thông tin khách hàng
                    </h5>
                </div>

                <div class="card-body">
                    <label class="form-label">
                        Chọn khách hàng <span class="required-star">*</span>
                    </label>

                    <select name="customer_id" class="form-select" required>
                        <option value="">-- Chọn khách hàng --</option>

                        @foreach($customers as $customer)
                        <option value="{{ $customer->id }}"
                            {{ old('customer_id', $order->customer_id) == $customer->id ? 'selected' : '' }}>
                            {{ $customer->full_name }} - {{ $customer->phone }}
                        </option>
                        @endforeach
                    </select>

                    @error('customer_id')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-primary">
                        <i class="fa-solid fa-box-open me-2"></i>Giỏ hàng
                    </h5>
                </div>

                <div class="card-body">
                    <div class="row g-2 align-items-end mb-3">
                        <div class="col-md-5">
                            <label class="form-label">Sản phẩm</label>

                            <select class="form-select" id="productSelect">
                                <option value="">-- Chọn sản phẩm --</option>

                                @foreach($products as $product)
                                <option value="{{ $product->id }}">
                                    {{ $product->product_name }} - {{ number_format($product->price, 0, ',', '.') }}đ
                                    | Tồn: {{ $product->total_quantity }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Số lượng</label>
                            <input type="number" min="1" value="1" id="productQty" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Giảm %</label>
                            <input type="number" min="0" max="100" value="0" id="productDiscount" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary w-100" onclick="addCartItem()">
                                <i class="fa-solid fa-plus"></i> Thêm
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle config-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th class="text-center">SL</th>
                                    <th class="text-end">Đơn giá</th>
                                    <th class="text-end">Giảm</th>
                                    <th class="text-end">Thành tiền</th>
                                    <th class="text-end">Xóa</th>
                                </tr>
                            </thead>

                            <tbody id="cartBody">
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Chưa có sản phẩm.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="hiddenItems"></div>

                    @error('items')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-primary">
                        <i class="fa-solid fa-file-invoice-dollar me-2"></i>Tạm tính hóa đơn
                    </h5>
                </div>

                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tổng gốc</span>
                        <strong id="subtotalText">0đ</strong>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Giảm sản phẩm</span>
                        <strong id="productDiscountText" class="text-danger">0đ</strong>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Giảm tổng đơn (%)</label>
                        <input type="number" name="order_discount_percent" id="orderDiscountPercent"
                            class="form-control" min="0" max="100"
                            value="{{ old('order_discount_percent', $order->order_discount_percent ?? 0) }}">

                        @error('order_discount_percent')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Giảm tổng đơn</span>
                        <strong id="orderDiscountText" class="text-danger">0đ</strong>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between fs-5 mb-3">
                        <span class="fw-bold">Tổng cuối</span>
                        <strong id="finalText" class="text-danger">0đ</strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Khách thanh toán</label>
                        <input type="number" name="paid_amount" id="paidAmount" class="form-control" min="0"
                            value="{{ old('paid_amount', $order->paid_amount ?? 0) }}">

                        @error('paid_amount')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phương thức thanh toán</label>

                        <select name="payment_method" class="form-select">
                            <option value="">-- Chọn --</option>
                            <option value="cash"
                                {{ old('payment_method', $order->payment_method ?? '') == 'cash' ? 'selected' : '' }}>
                                Tiền mặt
                            </option>
                            <option value="bank_transfer"
                                {{ old('payment_method', $order->payment_method ?? '') == 'bank_transfer' ? 'selected' : '' }}>
                                Chuyển khoản
                            </option>
                            <option value="card"
                                {{ old('payment_method', $order->payment_method ?? '') == 'card' ? 'selected' : '' }}>
                                Thẻ
                            </option>
                        </select>

                        @error('payment_method')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ghi chú</label>
                        <textarea name="note" class="form-control"
                            rows="3">{{ old('note', $order->note ?? '') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" onclick="return confirmBeforeSubmit()">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Cập nhật đơn hàng
                    </button>

                    <div class="small text-muted mt-3">
                        Số tiền ở đây chỉ là tạm tính bằng JS. Khi lưu, hệ thống sẽ tính lại bằng Laravel.
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script type="application/json" id="productsData">
    {
        !!json_encode($products, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!
    }
</script>

<script type="application/json" id="cartData">
    {
        !!json_encode($order - > items - > groupBy('product_id') - > map(function($items) {
            $first = $items - > first();

            return [
                'product_id' => $first - > product_id,
                'product_code' => $first - > product_code,
                'product_name' => $first - > product_name,
                'quantity' => $items - > sum('quantity'),
                'unit_price' => (float) $first - > unit_price,
                'discount_percent' => (float) $first - > discount_percent,
                'total_quantity' => optional($first - > product) - > total_quantity ?? 0,
                'allow_sell_without_stock' => optional($first - > product) - > allow_sell_without_stock ??
                false,
                'product_type' => optional($first - > product) - > product_type ?? 'physical',
            ];
        }) - > values(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!
    }
</script>

<script>
    const productsDataElement = document.getElementById('productsData');
    const products = JSON.parse(productsDataElement.textContent || '[]');

    const cartDataElement = document.getElementById('cartData');
    let cart = JSON.parse(cartDataElement.textContent || '[]');

    const money = number => {
        number = Math.max(0, Math.round(Number(number) || 0));
        return number.toLocaleString('vi-VN') + 'đ';
    };

    function findProduct(id) {
        return products.find(p => Number(p.id) === Number(id));
    }

    function addCartItem() {
        const productId = document.getElementById('productSelect').value;
        const qty = parseInt(document.getElementById('productQty').value || 1);
        let discount = parseFloat(document.getElementById('productDiscount').value || 0);

        if (!productId) {
            alert('Vui lòng chọn sản phẩm.');
            return;
        }

        const product = findProduct(productId);

        if (!product) {
            alert('Sản phẩm không hợp lệ.');
            return;
        }

        if (qty <= 0) {
            alert('Số lượng phải lớn hơn 0.');
            return;
        }

        const allowSellWithoutStock = Boolean(Number(product.allow_sell_without_stock));
        const productType = product.product_type || 'physical';
        const totalQuantity = Number(product.total_quantity || 0);

        if (!allowSellWithoutStock && productType === 'physical' && qty > totalQuantity) {
            alert('Sản phẩm không đủ tồn kho.');
            return;
        }

        const isDiscountable = Boolean(Number(product.is_discountable));

        if (!isDiscountable) {
            discount = 0;
        }

        discount = Math.max(0, Math.min(100, discount));

        const existing = cart.find(item =>
            Number(item.product_id) === Number(productId) &&
            Number(item.discount_percent) === Number(discount)
        );

        if (existing) {
            const newQty = Number(existing.quantity) + qty;

            if (!existing.allow_sell_without_stock && existing.product_type === 'physical' && newQty > existing
                .total_quantity) {
                alert('Sản phẩm không đủ tồn kho.');
                return;
            }

            existing.quantity = newQty;
        } else {
            cart.push({
                product_id: product.id,
                product_code: product.product_code,
                product_name: product.product_name,
                quantity: qty,
                unit_price: Number(product.price || 0),
                discount_percent: discount,
                total_quantity: totalQuantity,
                allow_sell_without_stock: allowSellWithoutStock,
                product_type: productType
            });
        }

        renderCart();
    }

    function removeCartItem(index) {
        cart.splice(index, 1);
        renderCart();
    }

    function renderCart() {
        const cartBody = document.getElementById('cartBody');
        const hiddenItems = document.getElementById('hiddenItems');

        if (cart.length === 0) {
            cartBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">Chưa có sản phẩm.</td>
                </tr>
            `;

            hiddenItems.innerHTML = '';
            updateSummary();
            return;
        }

        let html = '';
        let hidden = '';

        cart.forEach((item, index) => {
            const original = Number(item.quantity) * Number(item.unit_price);
            const discountAmount = Math.round(original * Number(item.discount_percent) / 100);
            const final = original - discountAmount;

            html += `
                <tr>
                    <td data-label="Sản phẩm">
                        <div class="fw-bold">${item.product_name}</div>
                        <small class="text-muted">${item.product_code || ''}</small>
                    </td>
                    <td data-label="SL" class="text-center">${item.quantity}</td>
                    <td data-label="Đơn giá" class="text-end">${money(item.unit_price)}</td>
                    <td data-label="Giảm" class="text-end text-danger">-${money(discountAmount)}</td>
                    <td data-label="Thành tiền" class="text-end fw-bold">${money(final)}</td>
                    <td data-label="Xóa" class="text-end">
                        <button type="button" class="btn btn-sm btn-light border text-danger" onclick="removeCartItem(${index})">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

            hidden += `
                <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
                <input type="hidden" name="items[${index}][discount_percent]" value="${item.discount_percent}">
            `;
        });

        cartBody.innerHTML = html;
        hiddenItems.innerHTML = hidden;

        updateSummary();
    }

    function updateSummary() {
        let subtotal = 0;
        let productDiscount = 0;

        cart.forEach(item => {
            const original = Number(item.quantity) * Number(item.unit_price);
            const discountAmount = Math.round(original * Number(item.discount_percent) / 100);

            subtotal += original;
            productDiscount += discountAmount;
        });

        let orderDiscountPercent = parseFloat(document.getElementById('orderDiscountPercent').value || 0);
        orderDiscountPercent = Math.max(0, Math.min(100, orderDiscountPercent));

        const afterProductDiscount = Math.max(0, subtotal - productDiscount);
        const orderDiscountAmount = Math.round(afterProductDiscount * orderDiscountPercent / 100);
        const finalAmount = Math.max(0, afterProductDiscount - orderDiscountAmount);

        document.getElementById('subtotalText').innerText = money(subtotal);
        document.getElementById('productDiscountText').innerText = '-' + money(productDiscount);
        document.getElementById('orderDiscountText').innerText = '-' + money(orderDiscountAmount);
        document.getElementById('finalText').innerText = money(finalAmount);

        const paidInput = document.getElementById('paidAmount');

        if (Number(paidInput.value || 0) > finalAmount) {
            paidInput.value = finalAmount;
        }
    }

    function confirmBeforeSubmit() {
        if (cart.length === 0) {
            alert('Vui lòng chọn ít nhất 1 sản phẩm.');
            return false;
        }

        return confirm('Xác nhận cập nhật đơn hàng? Hệ thống sẽ tính lại tiền và kho bằng Laravel.');
    }

    document.getElementById('orderDiscountPercent').addEventListener('input', updateSummary);
    document.getElementById('paidAmount').addEventListener('input', updateSummary);

    renderCart();
</script>
@endsection