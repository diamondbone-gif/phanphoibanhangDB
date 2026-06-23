<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerInvoice;

class InvoiceController extends Controller
{
    public function print(CustomerInvoice $invoice)
    {
        $invoice->load(['order.items', 'customer']);

        return view('admin.auth.invoices.print', compact('invoice'));
    }
}
