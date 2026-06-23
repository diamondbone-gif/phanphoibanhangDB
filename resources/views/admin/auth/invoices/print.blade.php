@php
$order = $invoice->order;
$customer = $invoice->customer ?? optional($order)->customer;

$customerAddress = $customer?->display_address ?: '---';

$companyName = 'Công Ty TNHH Diamond Bone';
$companyAddress = 'Địa chỉ: 123 Lê Lợi, Q.1, TP.HCM';
$companyPhone = 'Điện thoại: 0909 123 456';

$invoiceCode = $invoice->invoice_code ?: ($order->order_code ?? '');

$invoiceDate = $invoice->invoice_date
? $invoice->invoice_date->format('d/m/Y')
: now()->format('d/m/Y');

$items = $order ? $order->items : collect();

$itemsSubtotal = 0;
$productDiscountAmount = 0;

foreach ($items as $item) {
$quantity = (int) ($item->quantity ?? 0);
$unitPrice = (float) ($item->unit_price ?? 0);
$discountAmount = (float) ($item->discount_amount ?? 0);

$itemsSubtotal += $quantity * $unitPrice;
$productDiscountAmount += $discountAmount;
}

$shippingFee = (float) data_get($order, 'shipping_fee', 0);
$orderDiscountAmount = (float) data_get($order, 'order_discount_amount', 0);
$totalDiscount = $productDiscountAmount + $orderDiscountAmount;
$finalAmount = (float) ($invoice->final_amount ?? data_get($order, 'final_amount', 0));

if ($finalAmount <= 0) { $finalAmount=max(0, $itemsSubtotal + $shippingFee - $totalDiscount); } $note=trim((string)
    ($invoice->note ?? ''));
    @endphp

    <!DOCTYPE html>
    <html lang="vi">

    <head>
        <meta charset="UTF-8">
        <title>Hóa đơn {{ $invoiceCode }}</title>

        <style>
            * {
                box-sizing: border-box;
            }

            body {
                font-family: Arial, Helvetica, sans-serif;
                color: #111;
                background: #f3f4f6;
                margin: 0;
                padding: 24px;
            }

            .invoice-wrapper {
                max-width: 900px;
                margin: 0 auto;
                background: #fff;
                padding: 34px 38px;
                border-radius: 6px;
            }

            .top-small {
                display: flex;
                justify-content: space-between;
                font-size: 12px;
                color: #333;
                margin-bottom: 18px;
            }

            .header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 24px;
                margin-bottom: 14px;
            }

            .company-name {
                font-size: 32px;
                font-weight: 700;
                color: #1d5ed8;
                margin-bottom: 10px;
                line-height: 1.15;
            }

            .company-info {
                font-size: 20px;
                line-height: 1.6;
            }

            .invoice-title {
                text-align: right;
            }

            .invoice-title h1 {
                font-size: 34px;
                margin: 0 0 12px;
                font-weight: 700;
                color: #333;
                line-height: 1.15;
            }

            .invoice-title div {
                font-size: 20px;
                line-height: 1.7;
            }

            .bold {
                font-weight: 700;
            }

            .divider {
                height: 3px;
                background: #000;
                margin: 12px 0 26px;
            }

            .customer-info {
                font-size: 20px;
                line-height: 1.8;
                margin-bottom: 26px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 20px;
            }

            th,
            td {
                border: 1px solid #cfcfcf;
                padding: 12px 10px;
            }

            th {
                font-weight: 700;
                text-align: left;
            }

            .text-center {
                text-align: center;
            }

            .text-end {
                text-align: right;
            }

            .total-row td {
                font-weight: 700;
            }

            .grand-total {
                color: #c82132;
                font-weight: 700;
                font-size: 22px;
            }

            .discount-text {
                color: #c82132;
            }

            .note-box {
                margin-top: 26px;
                border: 1px solid #cfcfcf;
                padding: 14px 16px;
                font-size: 19px;
                line-height: 1.6;
            }

            .note-title {
                font-weight: 700;
                margin-bottom: 4px;
            }

            .thank-you {
                margin-top: 44px;
                text-align: center;
                font-size: 20px;
                font-style: italic;
            }

            .actions {
                max-width: 900px;
                margin: 0 auto 16px;
                display: flex;
                justify-content: flex-end;
                gap: 8px;
            }

            .btn {
                border: 1px solid #d1d5db;
                background: #fff;
                color: #111;
                padding: 9px 14px;
                border-radius: 8px;
                cursor: pointer;
                font-size: 15px;
                text-decoration: none;
            }

            .btn-primary {
                background: #2563eb;
                color: #fff;
                border-color: #2563eb;
            }

            @media print {
                body {
                    background: #fff;
                    padding: 0;
                }

                .actions {
                    display: none !important;
                }

                .invoice-wrapper {
                    max-width: 100%;
                    padding: 22px 28px;
                    border-radius: 0;
                }

                @page {
                    size: A4;
                    margin: 12mm;
                }
            }
        </style>
    </head>

    <body>
        <div class="actions">
            @if($order)
            <a href="{{ route('admin.orders.show', $order) }}" class="btn">
                Quay lại đơn hàng
            </a>
            @endif

            <button type="button" class="btn btn-primary" onclick="window.print()">
                In hóa đơn
            </button>
        </div>

        <div class="invoice-wrapper">
            <div class="top-small">
                <div>{{ now()->format('d/m/y, h:i A') }}</div>
                <div>Quản lý Khách hàng</div>
                <div></div>
            </div>

            <div class="header">
                <div>
                    <div class="company-name">{{ $companyName }}</div>

                    <div class="company-info">
                        <div>{{ $companyAddress }}</div>
                        <div>{{ $companyPhone }}</div>
                    </div>
                </div>

                <div class="invoice-title">
                    <h1>HÓA ĐƠN BÁN HÀNG</h1>
                    <div>Mã HĐ: <span class="bold">{{ $invoiceCode }}</span></div>
                    <div>Ngày lập: <span class="bold">{{ $invoiceDate }}</span></div>
                </div>
            </div>

            <div class="divider"></div>

            <div class="customer-info">
                <div>
                    <span class="bold">Khách hàng:</span>
                    {{ $customer->full_name ?? '---' }}
                </div>

                <div>
                    <span class="bold">Điện thoại:</span>
                    {{ $customer->phone ?? '---' }}
                </div>

                <div>
                    <span class="bold">Địa chỉ:</span>
                    {{ $customerAddress }}
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th class="text-center" style="width: 80px;">SL</th>
                        <th class="text-end" style="width: 160px;">Đơn giá</th>
                        <th class="text-end" style="width: 180px;">Thành tiền</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($items as $item)
                    @php
                    $quantity = (int) ($item->quantity ?? 0);
                    $unitPrice = (float) ($item->unit_price ?? 0);
                    $discountAmount = (float) ($item->discount_amount ?? 0);
                    $lineTotal = max(0, ($quantity * $unitPrice) - $discountAmount);
                    @endphp

                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td class="text-center">{{ $quantity }}</td>
                        <td class="text-end">{{ number_format($unitPrice, 0, ',', '.') }}đ</td>
                        <td class="text-end">{{ number_format($lineTotal, 0, ',', '.') }}đ</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">Chưa có sản phẩm.</td>
                    </tr>
                    @endforelse

                    <tr>
                        <td colspan="3" class="text-end bold">Tạm tính:</td>
                        <td class="text-end">{{ number_format($itemsSubtotal, 0, ',', '.') }}đ</td>
                    </tr>

                    <tr>
                        <td colspan="3" class="text-end bold">Phí giao hàng:</td>
                        <td class="text-end">{{ number_format($shippingFee, 0, ',', '.') }}đ</td>
                    </tr>

                    <tr>
                        <td colspan="3" class="text-end bold">Giảm giá:</td>
                        <td class="text-end discount-text">
                            -{{ number_format($totalDiscount, 0, ',', '.') }}đ
                        </td>
                    </tr>

                    <tr class="total-row">
                        <td colspan="3" class="text-end">Tổng thanh toán:</td>
                        <td class="text-end grand-total">
                            {{ number_format($finalAmount, 0, ',', '.') }}đ
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="note-box">
                <div class="note-title">Ghi chú:</div>
                <div>
                    {{ $note !== '' ? $note : 'Không có ghi chú.' }}
                </div>
            </div>

            <div class="thank-you">
                Cảm ơn quý khách đã tin tưởng và sử dụng dịch vụ!
            </div>
        </div>
    </body>

    </html>