<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment; // فرض می‌کنیم مدل Payment برای پرداخت‌ها وجود دارد

class InvoiceController extends Controller
{
    public function show($id)
    {
        // پیدا کردن پرداخت با استفاده از ID
        $payment = Payment::findOrFail($id);

        // ارسال داده به ویو
        return view('invoices.show', compact('payment'));
    }
}