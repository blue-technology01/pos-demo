<?php

namespace App\Http\Controllers\Invoice;

use App\Models\Sale;
use App\Http\Controllers\Controller;

class InvoiceController extends Controller
{
    public function print(Sale $sale)
    {
        $sale->load(['items', 'user', 'customer']);
        return view('invoices.invoice', compact('sale'));
    }
}
