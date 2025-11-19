@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card" style="direction: rtl; text-align: right;">
                <div class="card-header">وضعیت پرداخت</div>
                <div class="card-body">
                    @if (session('success'))
                        <p class="text-success">{{ session('success') }}</p>
                        <p>شناسه تراکنش: {{ $refId }}</p>
                    @elseif (session('error'))
                        <p class="text-danger">{{ session('error') }}</p>
                    @endif
                    <a href="{{ session('redirect', route('profile')) }}" class="btn btn-primary mt-3">بازگشت به پروفایل</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection