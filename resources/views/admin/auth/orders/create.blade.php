@extends('admin.auth.dashboardAmin')

@section('admin_content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">Bán hàng</li>
        <li class="breadcrumb-item active">Lên đơn hàng</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Lên đơn hàng</h3>

    <a href="{{ route('admin.orders.index') }}" class="btn btn-light border">
        <i class="fa-solid fa-arrow-left"></i> Quay lại
    </a>
</div>

@include('admin.auth.partials.alerts')

<form method="POST" action="{{ route('admin.orders.store') }}" id="orderForm">
    @csrf

    <div class="row g-3">
        <div class="col-lg-8">

            {{-- THÔNG TIN KHÁCH HÀNG --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-primary">
                        <i class="fa-solid fa-user me-2"></i>Thông tin khách hàng
                    </h5>
                </div>

                <div class="card-body position-relative">
                    <label class="form-label">
                        Tìm khách hàng <span class="text-danger">*</span>
                    </label>

                    <input type="text" id="customerSearchInput" class="form-control"
                        placeholder="Nhập tên, số điện thoại hoặc mã khách hàng..." autocomplete="off">

                    <input type="hidden" name="customer_id" id="customerIdInput" value="{{ old('customer_id') }}">

                    <div id="customerSearchResults" class="list-group position-absolute w-100 shadow-sm d-none"
                        style="z-index: 9999; left: 0; right: 0; margin-top: 4px;">
                    </div>

                    <div id="selectedCustomerBox" class="mt-2 d-none">
                        <span class="badge bg-primary">
                            Đã chọn: <span id="selectedCustomerText"></span>
                        </span>

                        <button type="button" class="btn btn-sm btn-link text-danger" onclick="clearSelectedCustomer()">
                            Bỏ chọn
                        </button>
                    </div>

                    <div class="form-text">
                        Gõ ít nhất 2 ký tự để tìm khách hàng nhanh.
                    </div>
                </div>
            </div>

            {{-- GIỎ HÀNG --}}
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
                                <option value="{{ data_get($product, 'id') }}" data-id="{{ data_get($product, 'id') }}"
                                    data-code="{{ e(data_get($product, 'product_code', '')) }}"
                                    data-name="{{ e(data_get($product, 'product_name', '')) }}"
                                    data-price="{{ (float) data_get($product, 'price', 0) }}"
                                    data-total-quantity="{{ (int) data_get($product, 'total_quantity', 0) }}"
                                    data-product-type="{{ data_get($product, 'product_type', 'physical') }}"
                                    data-allow-sell-without-stock="{{ data_get($product, 'allow_sell_without_stock', false) ? 1 : 0 }}"
                                    data-is-discountable="{{ data_get($product, 'is_discountable', true) ? 1 : 0 }}">
                                    {{ data_get($product, 'product_name') }}
                                    -
                                    {{ number_format((float) data_get($product, 'price', 0), 0, ',', '.') }}đ
                                    | Tồn: {{ data_get($product, 'total_quantity', 0) }}
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
                            <button type="button" class="btn btn-primary w-100" id="btnAddProduct">
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
                </div>
            </div>
        </div>

        {{-- TẠM TÍNH HÓA ĐƠN --}}
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
                            class="form-control" min="0" max="100" value="{{ old('order_discount_percent', 0) }}">
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
                            value="{{ old('paid_amount', 0) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Còn lại</label>
                        <input type="text" id="debtAmountText" class="form-control" value="0đ" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phương thức thanh toán</label>
                        <select name="payment_method" class="form-select">
                            <option value="">-- Chọn --</option>
                            <option value="cash" @selected(old('payment_method')==='cash' )>Tiền mặt</option>
                            <option value="bank_transfer" @selected(old('payment_method')==='bank_transfer' )>Chuyển
                                khoản</option>
                            <option value="card" @selected(old('payment_method')==='card' )>Thẻ</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ghi chú</label>
                        <textarea name="note" class="form-control" rows="3">{{ old('note') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="btnSubmitOrder">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Tạo đơn hàng
                    </button>

                    <div class="small text-muted mt-3">
                        Nút “Thêm” chỉ thêm sản phẩm vào bảng và tạm tính.
                        Chỉ khi bấm “Tạo đơn hàng” hệ thống mới lưu xuống database.
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    let cart = [];
    let customerSearchTimer = null;

    function money(number) {
        number = Math.max(0, Math.round(Number(number) || 0));
        return number.toLocaleString('vi-VN') + 'đ';
    }

    function toNumber(value) {
        return Number(value || 0);
    }

    function toBoolean(value) {
        return value === true || value === 1 || value === '1' || value === 'true';
    }

    function escapeHtml(value) {
        return String(value || '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function getSelectedProductFromOption() {
        const select = document.getElementById('productSelect');
        const option = select.options[select.selectedIndex];

        if (!option || !option.value) {
            return null;
        }

        return {
            id: option.dataset.id || option.value,
            product_code: option.dataset.code || '',
            product_name: option.dataset.name || '',
            price: toNumber(option.dataset.price),
            total_quantity: toNumber(option.dataset.totalQuantity),
            product_type: option.dataset.productType || 'physical',
            allow_sell_without_stock: toBoolean(option.dataset.allowSellWithoutStock),
            is_discountable: toBoolean(option.dataset.isDiscountable)
        };
    }

    function getCartQuantityByProduct(productId) {
        return cart
            .filter(function(item) {
                return Number(item.product_id) === Number(productId);
            })
            .reduce(function(total, item) {
                return total + Number(item.quantity);
            }, 0);
    }

    function addCartItem() {
        const product = getSelectedProductFromOption();
        const productQty = document.getElementById('productQty');
        const productDiscount = document.getElementById('productDiscount');

        const qty = parseInt(productQty.value || 1);
        let discountPercent = parseFloat(productDiscount.value || 0);

        if (!product) {
            alert('Vui lòng chọn sản phẩm.');
            return;
        }

        if (!Number.isInteger(qty) || qty <= 0) {
            alert('Số lượng phải lớn hơn 0.');
            productQty.focus();
            return;
        }

        discountPercent = Math.max(0, Math.min(100, discountPercent));

        if (!product.is_discountable) {
            discountPercent = 0;
        }

        const currentCartQuantity = getCartQuantityByProduct(product.id);
        const newTotalQuantity = currentCartQuantity + qty;

        if (
            !product.allow_sell_without_stock &&
            product.product_type === 'physical' &&
            newTotalQuantity > product.total_quantity
        ) {
            alert('Số lượng vượt quá tồn kho. Tồn hiện tại: ' + product.total_quantity);
            return;
        }

        const existingItem = cart.find(function(item) {
            return Number(item.product_id) === Number(product.id) &&
                Number(item.discount_percent) === Number(discountPercent);
        });

        if (existingItem) {
            existingItem.quantity += qty;
        } else {
            cart.push({
                product_id: product.id,
                product_code: product.product_code,
                product_name: product.product_name,
                quantity: qty,
                unit_price: product.price,
                discount_percent: discountPercent,
                total_quantity: product.total_quantity,
                product_type: product.product_type,
                allow_sell_without_stock: product.allow_sell_without_stock,
                is_discountable: product.is_discountable
            });
        }

        productQty.value = 1;
        productDiscount.value = 0;

        renderCart();
    }

    function removeCartItem(index) {
        cart.splice(index, 1);
        renderCart();
    }

    function updateCartQuantity(index, value) {
        const qty = parseInt(value || 1);

        if (!Number.isInteger(qty) || qty <= 0) {
            alert('Số lượng phải lớn hơn 0.');
            renderCart();
            return;
        }

        const item = cart[index];

        if (!item) {
            return;
        }

        const otherQuantity = cart
            .filter(function(cartItem, cartIndex) {
                return cartIndex !== index && Number(cartItem.product_id) === Number(item.product_id);
            })
            .reduce(function(total, cartItem) {
                return total + Number(cartItem.quantity);
            }, 0);

        const newTotalQuantity = otherQuantity + qty;

        if (
            !item.allow_sell_without_stock &&
            item.product_type === 'physical' &&
            newTotalQuantity > item.total_quantity
        ) {
            alert('Số lượng vượt quá tồn kho. Tồn hiện tại: ' + item.total_quantity);
            renderCart();
            return;
        }

        item.quantity = qty;
        renderCart();
    }

    function updateCartDiscount(index, value) {
        const item = cart[index];

        if (!item) {
            return;
        }

        let discountPercent = parseFloat(value || 0);
        discountPercent = Math.max(0, Math.min(100, discountPercent));

        if (!item.is_discountable) {
            discountPercent = 0;
        }

        item.discount_percent = discountPercent;
        renderCart();
    }

    function renderCart() {
        const cartBody = document.getElementById('cartBody');
        const hiddenItems = document.getElementById('hiddenItems');

        cartBody.innerHTML = '';
        hiddenItems.innerHTML = '';

        if (cart.length === 0) {
            cartBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        Chưa có sản phẩm.
                    </td>
                </tr>
            `;

            updateSummary();
            return;
        }

        cart.forEach(function(item, index) {
            const originalTotal = item.quantity * item.unit_price;
            const discountAmount = Math.round(originalTotal * item.discount_percent / 100);
            const finalTotal = Math.max(0, originalTotal - discountAmount);

            cartBody.innerHTML += `
                <tr>
                    <td data-label="Sản phẩm">
                        <div class="fw-bold">${escapeHtml(item.product_name)}</div>
                        <small class="text-muted">${escapeHtml(item.product_code)}</small>
                    </td>

                    <td data-label="SL" class="text-center" style="width: 110px;">
                        <input
                            type="number"
                            min="1"
                            class="form-control form-control-sm text-center"
                            value="${item.quantity}"
                            onchange="updateCartQuantity(${index}, this.value)"
                        >
                    </td>

                    <td data-label="Đơn giá" class="text-end">
                        ${money(item.unit_price)}
                    </td>

                    <td data-label="Giảm" class="text-end" style="width: 120px;">
                        <input
                            type="number"
                            min="0"
                            max="100"
                            class="form-control form-control-sm text-end"
                            value="${item.discount_percent}"
                            ${item.is_discountable ? '' : 'disabled'}
                            onchange="updateCartDiscount(${index}, this.value)"
                        >
                    </td>

                    <td data-label="Thành tiền" class="text-end fw-bold">
                        ${money(finalTotal)}
                    </td>

                    <td data-label="Xóa" class="text-end">
                        <button type="button" class="btn btn-sm btn-light border text-danger" onclick="removeCartItem(${index})">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

            hiddenItems.innerHTML += `
                <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
                <input type="hidden" name="items[${index}][discount_percent]" value="${item.discount_percent}">
            `;
        });

        updateSummary();
    }

    function updateSummary() {
        const subtotalText = document.getElementById('subtotalText');
        const productDiscountText = document.getElementById('productDiscountText');
        const orderDiscountPercentEl = document.getElementById('orderDiscountPercent');
        const orderDiscountText = document.getElementById('orderDiscountText');
        const finalText = document.getElementById('finalText');
        const paidAmountEl = document.getElementById('paidAmount');
        const debtAmountText = document.getElementById('debtAmountText');

        let subtotal = 0;
        let productDiscountAmount = 0;

        cart.forEach(function(item) {
            const originalTotal = item.quantity * item.unit_price;
            const discountAmount = Math.round(originalTotal * item.discount_percent / 100);

            subtotal += originalTotal;
            productDiscountAmount += discountAmount;
        });

        let orderDiscountPercentValue = parseFloat(orderDiscountPercentEl.value || 0);
        orderDiscountPercentValue = Math.max(0, Math.min(100, orderDiscountPercentValue));

        const afterProductDiscount = Math.max(0, subtotal - productDiscountAmount);
        const orderDiscountAmount = Math.round(afterProductDiscount * orderDiscountPercentValue / 100);
        const finalAmount = Math.max(0, afterProductDiscount - orderDiscountAmount);

        let paidValue = parseFloat(paidAmountEl.value || 0);
        paidValue = Math.max(0, paidValue);

        if (paidValue > finalAmount) {
            paidValue = finalAmount;
            paidAmountEl.value = finalAmount;
        }

        const debtAmount = Math.max(0, finalAmount - paidValue);

        subtotalText.textContent = money(subtotal);
        productDiscountText.textContent = '-' + money(productDiscountAmount);
        orderDiscountText.textContent = '-' + money(orderDiscountAmount);
        finalText.textContent = money(finalAmount);
        debtAmountText.value = money(debtAmount);
    }

    function searchCustomers(keyword) {
        const resultsBox = document.getElementById('customerSearchResults');

        if (keyword.length < 2) {
            resultsBox.classList.add('d-none');
            resultsBox.innerHTML = '';
            return;
        }

        fetch(`{{ route('admin.orders.customers.search') }}?q=${encodeURIComponent(keyword)}`, {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(customers) {
                resultsBox.innerHTML = '';

                if (!Array.isArray(customers) || customers.length === 0) {
                    resultsBox.innerHTML = `
                        <button type="button" class="list-group-item list-group-item-action disabled">
                            Không tìm thấy khách hàng
                        </button>
                    `;
                    resultsBox.classList.remove('d-none');
                    return;
                }

                customers.forEach(function(customer) {
                    const button = document.createElement('button');

                    button.type = 'button';
                    button.className = 'list-group-item list-group-item-action';
                    button.innerHTML = `
                        <div class="fw-bold">${escapeHtml(customer.full_name)}</div>
                        <small class="text-muted">${escapeHtml(customer.phone)} ${customer.customer_code ? ' - ' + escapeHtml(customer.customer_code) : ''}</small>
                    `;

                    button.addEventListener('click', function() {
                        selectCustomer(customer);
                    });

                    resultsBox.appendChild(button);
                });

                resultsBox.classList.remove('d-none');
            })
            .catch(function() {
                resultsBox.innerHTML = `
                    <button type="button" class="list-group-item list-group-item-action disabled">
                        Lỗi tìm kiếm khách hàng
                    </button>
                `;
                resultsBox.classList.remove('d-none');
            });
    }

    function selectCustomer(customer) {
        document.getElementById('customerIdInput').value = customer.id;
        document.getElementById('customerSearchInput').value = customer.label;
        document.getElementById('selectedCustomerText').textContent = customer.label;
        document.getElementById('selectedCustomerBox').classList.remove('d-none');

        const resultsBox = document.getElementById('customerSearchResults');
        resultsBox.classList.add('d-none');
        resultsBox.innerHTML = '';
    }

    function clearSelectedCustomer() {
        document.getElementById('customerIdInput').value = '';
        document.getElementById('customerSearchInput').value = '';
        document.getElementById('selectedCustomerText').textContent = '';
        document.getElementById('selectedCustomerBox').classList.add('d-none');
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('btnAddProduct').addEventListener('click', addCartItem);

        document.getElementById('orderDiscountPercent').addEventListener('input', updateSummary);
        document.getElementById('paidAmount').addEventListener('input', updateSummary);

        document.getElementById('customerSearchInput').addEventListener('input', function() {
            const keyword = this.value.trim();

            document.getElementById('customerIdInput').value = '';
            document.getElementById('selectedCustomerBox').classList.add('d-none');

            clearTimeout(customerSearchTimer);

            customerSearchTimer = setTimeout(function() {
                searchCustomers(keyword);
            }, 300);
        });

        document.addEventListener('click', function(event) {
            const searchInput = document.getElementById('customerSearchInput');
            const resultsBox = document.getElementById('customerSearchResults');

            if (!searchInput.contains(event.target) && !resultsBox.contains(event.target)) {
                resultsBox.classList.add('d-none');
            }
        });

        document.getElementById('orderForm').addEventListener('submit', function(event) {
            const customerId = document.getElementById('customerIdInput').value;

            if (!customerId) {
                event.preventDefault();
                alert('Vui lòng tìm và chọn khách hàng.');
                return;
            }

            if (cart.length === 0) {
                event.preventDefault();
                alert('Vui lòng thêm ít nhất 1 sản phẩm vào giỏ hàng.');
                return;
            }

            const confirmed = confirm(
                'Xác nhận tạo đơn hàng? Sau khi tạo, hệ thống mới lưu xuống database và trừ kho.');

            if (!confirmed) {
                event.preventDefault();
            }
        });

        updateSummary();
    });
</script>
@endsection