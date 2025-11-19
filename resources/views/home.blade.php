@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">داشبورد</div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <h5>وضعیت پروفایل</h5>
                    @if ($profile)
                        <p>پروفایل شما کامل است: {{ $profile->company_activity }}، {{ $profile->company_size }}، {{ $profile->total_employees }} نفر</p>
                    @else
                        <p>پروفایل شما هنوز کامل نشده. <a href="{{ route('profile') }}">کامل کنید</a></p>
                    @endif

                    <h5>وضعیت سوالات</h5>
                    <p>شما به {{ $answered_questions }} از {{ $total_questions }} سوال جواب داده‌اید.</p>
                    <a href="{{ route('questions') }}" class="btn btn-primary">پاسخ به سوالات</a>
                    <a href="{{ route('analysis') }}" class="btn btn-secondary">مشاهده تحلیل</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection