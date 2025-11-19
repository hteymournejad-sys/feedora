@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">داشبورد</div>
                <div class="card-body">
                    <h1 class="text-center">خوش آمدید، {{ Auth::user()->name }}!</h1>
                    <p class="text-center">به داشبورد کاربری خود خوش آمدید.</p>
                    <div class="text-center">
                        <a href="{{ route('assessment.domains') }}" class="btn btn-primary">شروع ارزیابی</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection