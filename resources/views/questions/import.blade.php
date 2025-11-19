<!DOCTYPE html>
<html>
<head>
<!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">    
<title>وارد کردن سوالات</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>وارد کردن سوالات از فایل اکسل</h1>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('questions.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="file">فایل اکسل را انتخاب کنید:</label>
                <input type="file" name="file" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">وارد کردن</button>
        </form>
    </div>
</body>
</html>