<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <title>انتخاب حوزه ارزیابی</title>
    <!-- فونت Vazir -->
    <link href="https://cdn.jsdelivr.net/npm/vazir-font@30.1.0/dist/font-face.css" rel="stylesheet" onerror="this.style.display='none';console.error('فونت لود نشد، از فونت پیش‌فرض استفاده می‌شود');">
    <!-- بوت‌استرپ CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <!-- بوت‌استرپ JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: 'Vazir', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 25%, #e0e7f0 100%);
            margin: 0;
            padding: 20px;
            color: #2c3e50;
            direction: rtl;
            min-height: 100vh;
        }
        .domains-container {
            max-width: 1100px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .header-container {
            text-align: center;
            margin-bottom: 40px;
        }
        .header-container img {
            max-width: 250px;
            height: auto;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            transition: transform 0.3s ease;
        }
        .header-container img:hover {
            transform: scale(1.05);
        }
        .report-title-bar {
            background: linear-gradient(90deg, #2F4F4F, #1C2526);
            padding: 15px 30px;
            border-radius: 10px 10px 0 0;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .report-title {
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            text-align: center;
            margin: 0;
        }
        .domain-cards {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }
        .domain-card {
            background-color: white;
            width: 200px;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #ecf0f1;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .domain-card.completed {
            background-color: #e6f4e6;
        }
        .domain-card.incomplete {
            background-color: #fff3cd;
        }
        .domain-card.locked {
            cursor: not-allowed;
            opacity: 0.6;
        }
        .domain-card:not(.locked):hover {
            transform: translateY(-5px);
        }
        .domain-card h3 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .success-message {
            color: #20B2AA;
            text-align: center;
            margin: 20px 0;
            font-weight: bold;
        }
        .error-message {
            color: #e74c3c;
            text-align: center;
            margin: 20px 0;
            font-weight: bold;
        }
        .divider {
            border-top: 1px solid #ccc;
            margin: 40px 0 20px 0;
        }
        .buttons-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }
        .action-button {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-family: 'Vazir', sans-serif;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            background: linear-gradient(90deg, #3498db, #2980b9);
            color: white;
            transition: all 0.3s ease;
        }
        .action-button:hover {
            background: linear-gradient(90deg, #2980b9, #3498db);
            transform: scale(1.05);
        }
        .legend-container {
            text-align: right;
            margin-top: 20px;
            font-size: 14px;
            color: #333;
        }
        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .legend-color {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            margin-left: 10px;
        }
        .legend-color.completed {
            background-color: #e6f4e6;
        }
        .legend-color.incomplete {
            background-color: #fff3cd;
        }
        .legend-color.not-started {
            background-color: white;
            border: 1px solid #ccc;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            direction: rtl;
        }
        .modal-content {
            background-color: #ffffff;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #ecf0f1;
            width: 80%;
            max-width: 400px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            font-family: 'Vazir', sans-serif;
        }
        .modal-content h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .modal-buttons {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .modal-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Vazir', sans-serif;
            font-size: 14px;
            transition: transform 0.3s ease;
        }
        .btn-continue {
            background-color: #20B2AA;
            color: white;
        }
        .btn-restart {
            background-color: #e74c3c;
            color: white;
        }
        .modal-btn:hover {
            transform: scale(1.05);
        }
        .close {
            color: #aaa;
            float: left;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: #2c3e50;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="domains-container">
        <div class="header-container">
            <img src="{{ asset('images/feedora-Qu.png') }}" alt="لوگوی فدورا">
        </div>
        <div class="report-title-bar">
            <h1 class="report-title">انتخاب حوزه ارزیابی</h1>
        </div>

        @if (session('success'))
            <div class="success-message">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="error-message">{{ session('error') }}</div>
        @endif

        <div class="domain-cards">
            @foreach ($domains as $domain)
                <div class="domain-card" data-domain="{{ $domain }}" onclick="checkDomainStatus('{{ $domain }}')">
                    <h3>{{ $domain }}</h3>
                </div>
            @endforeach
        </div>

        <div class="divider"></div>

        <div class="buttons-container">
            <a href="{{ route('profile') }}" class="action-button">پروفایل کاربر</a>
        </div>

        <div class="legend-container">
            <div class="legend-item"><span>برای مشاهده نتایج ارزیابی‌های قبلی، به بخش «نتایج ارزیابی فنی» در پروفایل خود مراجعه کنید.</span></div>
            <div class="legend-item"><span class="legend-color completed"></span><span>کادر سبز رنگ: ارزیابی این حوزه کامل شده و نتیجه ثبت شده است</span></div>
            <div class="legend-item"><span class="legend-color incomplete"></span><span>کادر زرد رنگ: پاسخ‌ها ناقص است و هنوز برخی از سوالات بی‌پاسخ مانده‌اند</span></div>
            <div class="legend-item"><span class="legend-color not-started"></span><span>کادر سفید رنگ: هیچ پاسخی در این حوزه ثبت نشده است</span></div>
        </div>
    </div>

    <div id="domainModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">×</span>
            <h3>ادامه ارزیابی</h3>
            <p>شما قبلاً در این حوزه ارزیابی را نیمه‌کاره رها کرده‌اید. آیا می‌خواهید از ادامه سوالات پاسخ دهید یا از ابتدا شروع کنید؟</p>
            <div class="modal-buttons">
                <a id="continueLink" class="modal-btn btn-continue">ادامه سوالات</a>
                <a id="restartLink" class="modal-btn btn-restart">شروع از ابتدا</a>
            </div>
        </div>
    </div>

    <script>
        let currentDomain = '';
        let allDomainsCompleted = true;

        function checkDomainStatus(domain) {
            const card = document.querySelector(`.domain-card[data-domain="${domain}"]`);
            if (card.classList.contains('locked')) {
                return;
            }

            currentDomain = domain;
            fetch('{{ route('assessment.check-domain') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ domain: domain })
            })
            .then(response => response.json())
            .then(data => {
                console.log('CheckDomainStatus Response:', data);
                if (data.hasIncompleteAssessment) {
                    document.getElementById('continueLink').href = '{{ route('assessment.questions') }}?domain=' + domain;
                    document.getElementById('restartLink').href = '{{ route('assessment.questions') }}?domain=' + domain + '&restart=1';
                    const modal = document.getElementById('domainModal');
                    modal.style.display = 'block';
                } else {
                    window.location.href = '{{ route('assessment.questions') }}?domain=' + domain;
                }
            })
            .catch(error => {
                console.error('خطا در بررسی وضعیت حوزه:', error);
                alert('خطایی رخ داده است. لطفاً دوباره تلاش کنید.');
            });
        }

        function closeModal() {
            const modal = document.getElementById('domainModal');
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('domainModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const domainCards = document.querySelectorAll('.domain-card');
            let completedDomains = 0;
            const totalDomains = domainCards.length;

            domainCards.forEach(card => {
                const domain = card.getAttribute('data-domain');
                fetch('{{ route('assessment.check-domain') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ domain: domain })
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Domain Status:', { domain: domain, status: data.status });
                    if (data.status === 'completed') {
                        card.classList.add('completed');
                        card.classList.add('locked');
                        completedDomains++;
                    } else if (data.status === 'incomplete') {
                        card.classList.add('incomplete');
                        allDomainsCompleted = false;
                    } else {
                        allDomainsCompleted = false;
                    }
                })
                .catch(error => {
                    console.error('خطا در بررسی وضعیت حوزه ' + domain + ':', error);
                    allDomainsCompleted = false;
                });
            });
        });
    </script>
</body>
</html>