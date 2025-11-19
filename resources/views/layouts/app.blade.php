<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>فیدورا - ارزیابی فناوری اطلاعات</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <!-- لود فونت Bebas Neue از Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <!-- لود Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- تعریف فونت ایران‌سنس و استایل‌های نوار ناوبری و فوتر -->
    <style>
        @font-face {
            font-family: 'IRANSans';
            src: url('/fonts/Iranian Sans.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        /* تنظیم ارتفاع کل صفحه */
        html, body {
            height: 100vh;
            margin: 0;
            padding: 0;
            font-family: 'IRANSans', sans-serif !important;
        }

        /* تنظیم div اصلی برای Flexbox */
        #app {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* تنظیم main برای گرفتن فضای باقی‌مانده */
        main {
            flex: 1 0 auto;
            padding-bottom: 20px; /* فاصله از فوتر */
        }

        /* استایل سایر المان‌ها */
        body, h1, h2, h3, h4, h5, h6, p, table, th, td, span, a, li, ul {
            font-family: 'IRANSans', sans-serif !important;
        }

        /* استایل نوار ناوبری */
        .navbar {
            background: linear-gradient(to right, #1a252f 0%, #4a6a8f 100%); /* گرادیانت از پررنگ به کم‌رنگ */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            padding: 10px 0;
            transition: all 0.3s ease;
        }

        /* استایل لوگوی Feedora */
        .navbar-brand.feedora-brand {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 32px;
            font-weight: 700;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: transform 0.3s ease, color 0.3s ease;
        }
        .navbar-brand.feedora-brand:hover {
            color: #00aaff;
            transform: scale(1.05);
        }

        /* استایل دکمه خروج */
        .nav-link.logout-btn {
            color: #ffffff;
            background-color: #007bff;
            padding: 8px 16px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        .nav-link.logout-btn:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        /* استایل فوتر */
        .footer {
            background: linear-gradient(to right, #1a252f 0%, #4a6a8f 100%); /* گرادیانت از پررنگ به کم‌رنگ */
            color: #ffffff;
            text-align: center;
            padding: 20px 0;
            box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.3);
            flex-shrink: 0;
            width: 100%;
        }
        .footer p {
            margin: 0;
            font-size: 16px;
        }
        .footer .copyright {
            font-size: 12px;
            margin-top: 8px;
            opacity: 0.8;
        }

        /* ریسپانسیو کردن نوار ناوبری و فوتر */
        @media (max-width: 767px) {
            .navbar-brand.feedora-brand {
                font-size: 24px;
            }
            .nav-link.logout-btn {
                padding: 6px 12px;
                font-size: 14px;
            }
            .footer p {
                font-size: 14px;
            }
            .footer .copyright {
                font-size: 10px;
            }
        }
    </style>

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light shadow-sm" id="mainNavbar">
            <div class="container">
                <!-- لوگوی Feedora با لینک به پروفایل -->
                <a class="navbar-brand feedora-brand" href="{{ route('profile') }}">
                    Feedora
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Right Side Of Navbar (فقط منوی خروج برای کاربران لاگین‌شده) -->
                    <ul class="navbar-nav ms-auto">
                        @auth
                            <li class="nav-item">
                                <a class="nav-link logout-btn" href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                            document.getElementById('logout-form').submit();">
                                    <i class="bi bi-box-arrow-right"></i> {{ __('خروج کاربر') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        @endauth
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>

        <!-- فوتر -->
        <footer class="footer">
            <div class="container">
                <p>سیستم جامع ارزیابی و پایش فناوری اطلاعات فیدورا</p>
                <p class="copyright">هر گونه کپی برداری از محتوا، شکل و سایر اجزای سایت صرفا با موافقت مکتوب مجاز می باشد</p>
            </div>
        </footer>
    </div>
</body>
</html>