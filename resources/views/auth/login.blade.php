<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
<!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">   
 <title>ورود به سامانه فیدورا</title>

    <!-- Bootstrap RTL برای استایل‌های پارسی -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous">

    <!-- آیکون‌های Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- فونت Vazir -->
    <link href="https://cdn.fontcdn.ir/Font/Persian/Vazir/Vazir.css" rel="stylesheet">

    <!-- استایل‌های سفارشی -->
    <style> 
        /* اعمال فونت Vazir به کل صفحه */
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Vazir', sans-serif;
        }

        /* کادر ورود */
        .login-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1s ease-in-out;
 	    width: 350px;       /* یا هر درصدی/پیکسلی که می‌خوای */
  	    margin: 0 auto;   /* باعث میشه وسط قرار بگیره */
        }

        /* هدر کادر */
        .card-header {
            background-color: #4e73df;
            color: white;
            font-weight: bold;
            padding: 1.5rem;
            border-bottom: none;
        }

        /* لوگو */
        .logo {
            max-width: 180px;
            height: auto;
            display: block;
            margin: 2rem auto 1.5rem auto; /* فاصله بیشتر از بالا برای خودنمایی */
            transition: transform 0.3s ease;
        }
        .logo:hover {
            transform: rotate(5deg) scale(1.05);
        }

        /* فیلدهای ورودی */
        .input-group {
            margin-bottom: 1.5rem;
    max-width: 300px; /* عرض دلخواه */
    margin-left: auto; /* وسط‌چین کردن */
    margin-right: auto; /* وسط‌چین کردن */
        }
        .form-control {
            border-radius: 10px;
            padding: 0.75rem;
            transition: border-color 0.3s ease;
        }
        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        /* دکمه ورود */
        .btn-primary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border: none;
            border-radius: 50px;
            padding: 0.75rem 2rem;
            color: white; /* رنگ متن سفید */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(78, 115, 223, 0.4);
        }

        /* خط جداکننده */
        .divider {
            border-top: 1px solid #dee2e6;
            margin: 2rem 0;
        }

        /* دکمه راهنما */
        .guide-btn {
            background-color: #ffdab9;
            border-color: #ffdab9;
            color: #000;
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            transition: background-color 0.3s ease;
        }
        .guide-btn:hover {
            background-color: #ffb74d;
            border-color: #ffb74d;
        }

        /* متن خوش‌آمدگویی */
        .welcome-text {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 2rem;
            text-align: center;
            animation: slideIn 1s ease-in-out;
        }

        /* وسط‌چین کردن دکمه ورود */
        .center-button {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }

        /* افزایش فضای پایین کادر */
        .card-body {
            padding-bottom: 5rem; /* فضای بیشتر برای دکمه راهنما */
        }

        /* انیمیشن‌ها */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* ریسپانسیو برای موبایل */
        @media (max-width: 576px) {
            .login-card {
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container-lg"> <!-- تغییر به container-lg برای max-width بزرگ‌تر -->
        <div class="row justify-content-center">
            <div class="col-md-10"> <!-- عرض کادر 1.5 برابر (از 7 به 10) -->
                <div class="card login-card">
                    <div class="card-header text-center">ورود به سامانه فیدورا</div>
                    <div class="card-body p-4">
                        <!-- لوگو -->
                        <div class="text-center mb-4">
                            <img src="{{ asset('images/Logo01.png') }}" alt="فیدورا لوگو" class="logo">
                        </div>

                        <!-- متن خوش‌آمدگویی -->
                        <div class="welcome-text">
                            خوش آمدید
                        </div>

                        <!-- فرم ورود -->
                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <!-- فیلد نام‌کاربری -->
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0">
                                    <i class="bi bi-person-fill text-muted"></i>
                                </span>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="نام‌ کاربری">
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- فیلد رمزعبور -->
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0">
                                    <i class="bi bi-lock-fill text-muted"></i>
                                </span>
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="رمزعبور">
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- دکمه ورود -->
                            <div class="center-button">
                                <button type="submit" class="btn btn-primary">ورود به سامانه</button>
                            </div>
                        </form>

                        <!-- خط جداکننده -->
                        <hr class="divider">

                        <!-- بخش راهنما -->
                        <div class="text-center">
                            <p class="mb-2">لطفا قبل از ارزیابی، فایل راهنما را مطالعه بفرمایید!</p>
                            <a href="#" class="btn guide-btn">مطالعه راهنما</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- اسکریپت Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>