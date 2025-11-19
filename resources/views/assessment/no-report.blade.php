@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">گزارش موجود نیست</div>
                <div class="card-body">
                    <p>{{ $message }}</p>
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">بازگشت به داشبورد</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection