@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card" style="direction: rtl; text-align: right;">
                <div class="card-header">ادامه ارزیابی</div>
                <div class="card-body">
                    <p>شما قبلاً تا سوال {{ $last_question_id }} پیش رفتید ({{ $answered_questions }} از {{ $total_questions }} سوال جواب داده شده).</p>
                    <p>آیا می‌خواهید ارزیابی را از سوال {{ $last_question_id + 1 }} ادامه دهید یا از ابتدا شروع کنید؟</p>
                    <div class="mt-3">
                        <a href="{{ route('questions', ['resume' => 'yes', 'refresh' => 'true']) }}" class="btn btn-primary">ادامه از سوال {{ $last_question_id + 1 }}</a>
                        <a href="{{ route('questions', ['resume' => 'no', 'refresh' => 'true']) }}" class="btn btn-secondary">شروع از ابتدا</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection