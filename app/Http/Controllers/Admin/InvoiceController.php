<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerInvoice;

class InvoiceController extends Controller
{
    public function print(CustomerInvoice $invoice)
    {
        $invoice->load([
            'customer.detail',
            'order.customer.detail',
            'order.items.product',
        ]);

        return view('admin.auth.invoices.print', compact('invoice'));
    }
}
