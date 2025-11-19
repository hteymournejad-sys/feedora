<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentGuideController extends Controller
{
    public function index()
    {
        return view('payment-guide');
    }
}