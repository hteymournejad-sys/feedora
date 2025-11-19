<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>چاپ گزارش ارزیابی فناوری اطلاعات</title>
    <link href="https://cdn.jsdelivr.net/npm/vazir-font@30.1.0/dist/font-face.css" rel="stylesheet" onerror="this.style.display='none';console.error('فونت لود نشد، از فونت پیش‌فرض استفاده می‌شود');">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <script src="https://cdn.jsdelivr.net/npm/jalaali-js@1.2.0/dist/jalaali.min.js"></script>
    <style>
        body {
            font-family: 'Vazir', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #2c3e50;
            direction: rtl;
        }
        .report-wrapper {
            max-width: 1100px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
        }
        .header-container {
            text-align: center;
            margin-bottom: 40px;
        }
        .header-container img {
            max-width: 250px;
            height: auto;
        }
        .report-title-bar {
            background: #3498db;
            padding: 15px 30px;
            border-radius: 10px 10px 0 0;
            margin-bottom: 20px;
        }
        .report-title {
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            text-align: center;
            margin: 0;
        }
        .report-description {
            line-height: 1.8;
            margin-bottom: 30px;
            color: #34495e;
        }
        .report-description p {
            margin-bottom: 1.5rem;
        }
        .report-description .bold-title {
            font-weight: 700;
            font-size: 1.8rem;
            color: #2980b9;
        }
        .highlight {
            font-weight: 600;
            padding: 0 8px;
        }
        .title-highlight {
            font-weight: 600;
            padding: 0 8px;
        }
        .separator {
            height: 2px;
            background: linear-gradient(to left, transparent, #3498db, transparent);
            margin: 30px 0;
        }
        .card {
            background: #ffffff;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #ecf0f1;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table th, .table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: right;
        }
        .table th {
            text-align: center;
        }
        .strength-table th {
            background: #27ae60;
            color: #fff;
        }
        .strength-table td:first-child {
            color: #fff;
            background: #27ae60;
        }
        .risk-high-table th {
            background: #e74c3c;
            color: #fff;
        }
        .risk-high-table td:first-child {
            color: #fff;
            background: #e74c3c;
        }
        .risk-medium-table th {
            background: #e67e22;
            color: #fff;
        }
        .risk-medium-table td:first-child {
            color: #fff;
            background: #e67e22;
        }
        .improvement-table th {
            background: #3498db;
            color: #fff;
        }
        .improvement-table td:first-child {
            color: #fff;
            background: #3498db;
        }
        .risk-low-table th {
            background: #f1c40f;
            color: #fff;
        }
        .risk-low-table td:first-child {
            color: #fff;
            background: #f1c40f;
        }
        .metrics-container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 25px;
        }
        .metric-card {
            flex: 1;
            background: #ffffff;
            padding: 25px;
            text-align: center;
            min-width: 0;
        }
        .metric-card h2 {
            font-size: 1.4rem;
            margin-bottom: 15px;
            color: #2c3e50;
            font-weight: 600;
        }
        .metric-card .score-circle {
            display: inline-block;
            width: 120px;
            height: 120px;
            line-height: 120px;
            border-radius: 50%;
            background: #ecf0f1;
            text-align: center;
            margin-top: 15px;
            font-weight: 700;
            font-size: 1.8rem;
            color: #2c3e50;
            position: relative;
            overflow: hidden;
        }
        .metric-card .score-circle::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(52, 152, 219, 0.2);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(0); opacity: 1; }
            100% { transform: scale(1.5); opacity: 0; }
        }
        .error-message {
            color: #e74c3c;
            text-align: center;
            margin: 20px 0;
        }
        .table td ul {
            margin: 0;
            padding-right: 20px;
            line-height: 1.6;
        }
        .table td li {
            margin-bottom: 8px;
        }
        .table td p {
            margin: 0 0 10px 0;
            line-height: 1.6;
        }
        @media print {
            body {
                padding: 0;
                background: #fff;
            }
            .report-wrapper {
                box-shadow: none;
                padding: 0;
            }
            .header-container img {
                max-width: 200px;
            }
            .score-circle::after {
                display: none;
            }
            .card, .metric-card {
                box-shadow: none;
                border: none;
            }
            .no-print {
                display: none;
            }
            .table th, .table td {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color: #000 !important;
                border: 1px solid #000 !important;
            }
            .strength-table th, .strength-table td:first-child {
                background-color: #27ae60 !important;
            }
            .risk-high-table th, .risk-high-table td:first-child {
                background-color: #e74c3c !important;
            }
            .risk-medium-table th, .risk-medium-table td:first-child {
                background-color: #e67e22 !important;
            }
            .improvement-table th, .improvement-table td:first-child {
                background-color: #3498db !important;
            }
            .risk-low-table th, .risk-low-table td:first-child {
                background-color: #f1c40f !important;
            }
            .report-description {
                page-break-inside: auto;
            }
            .report-description p, .report-description ul {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="report-wrapper">
        <div class="header-container">
            <img src="{{ asset('images/Logo.png') }}" alt="Feedora Logo">
        </div>
        <div class="report-title-bar">
            <h1 class="report-title">
                گزارش ارزیابی واحد فناوری اطلاعات شرکت
                <span class="title-highlight" title="این اطلاعات از پروفایل شما بارگذاری شده است">{{ e($company_name ?? 'نام شرکت مشخص نشده') }}</span>
            </h1>
        </div>
        <div class="report-description">
            <p>این گزارش با هدف ارزیابی جامع وضعیت فناوری اطلاعات در شرکت <span class="highlight">{{ e($company_name ?? 'نام شرکت مشخص نشده') }}</span> تهیه شده است. تمرکز اصلی این بررسی، شناسایی ظرفیت‌های موجود، نقاط قوت و ضعف، فرصت‌های بهبود و چالش‌های پیش‌رو در حوزه فناوری اطلاعات است. نتایج حاصل، مبنایی خواهد بود برای تصمیم‌سازی‌های راهبردی و گامی مؤثر در جهت تحقق چشم‌انداز دیجیتال شرکت.</p>
            <p>این ارزیابی به عنوان بخشی از برنامه کلان "تحول و توسعه مستمر"، در دو فاز اصلی انجام شده است:</p>
            <p>فاز نخست بر سنجش وجود حداقل الزامات، زیرساخت‌ها و قابلیت‌های پایه فناوری اطلاعات متمرکز بوده است. در این مرحله، گلوگاه‌ها و نارسایی‌هایی شناسایی شده‌اند که می‌توانند مانع بهره‌وری، چابکی و پایداری عملیات سازمان شوند.</p>
            <p>فاز دوم بر طراحی و پیشنهاد برنامه‌های اصلاحی، میان‌مدت و بلندمدت تمرکز دارد. هدف این مرحله، ارتقاء سطح بلوغ فناوری اطلاعات، کاهش ریسک‌ها، و همراستاسازی آن با راهبردهای کلان شرکت بوده است.</p>
            <p class="bold-title">چارچوب‌های مرجع ارزیابی</p>
            <p>فرآیند ارزیابی با بهره‌گیری از بهترین الگوها و استانداردهای بین‌المللی و مدیریتی صورت پذیرفت؛ از جمله:</p>
            <ul>
                <li>COBIT 2019 برای حاکمیت و مدیریت فناوری اطلاعات</li>
                <li>ITIL V4 برای مدیریت خدمات فناوری</li>
                <li>ISMS و ISO 27001 برای امنیت اطلاعات</li>
                <li>ISO 31000 برای مدیریت ریسک</li>
                <li>ISO 20000 برای چارچوب خدمات IT</li>
            </ul>
            <p>این چارچوب‌ها تضمین می‌کنند که ارزیابی انجام‌شده، هم‌راستا با رویکردهای حرفه‌ای جهانی بوده و قابلیت تطبیق با نیازهای عملیاتی و استراتژیک شرکت را دارا باشد.</p>
            <p class="bold-title">دامنه ارزیابی</p>
            <p>در تاریخ <span class="highlight" id="jalali-date">{{ e($report_date ?? 'تاریخ مشخص نشده') }}</span>، حوزه‌های کلیدی زیر به‌طور دقیق مورد بررسی قرار گرفتند:</p>
            <ul>
                <li>مدیریت ریسک و امنیت اطلاعات</li>
                <li>حاکمیت فناوری اطلاعات</li>
                <li>زیرساخت‌ها و شبکه</li>
                <li>خدمات و پشتیبانی فناوری اطلاعات</li>
                <li>سامانه‌های کاربردی</li>
                <li>تحول دیجیتال</li>
                <li>هوشمندسازی</li>
            </ul>
            <p>فرآیند ارزیابی به نحوی طراحی شده که متناسب با ماهیت و نیازهای متنوع سازمان‌ها از جمله شرکت‌های <span class="highlight">{{ e($company_type ?? 'نوع شرکت مشخص نشده') }}</span> در مقیاس <span class="highlight">{{ e($company_size ?? 'اندازه شرکت مشخص نشده') }}</span> باشد. بنابراین تحلیل‌های ارائه‌شده در این گزارش نیز با در نظر گرفتن همین رویکرد، ساختار یافته و هدفمند تدوین شده‌اند.</p>
            <p class="bold-title">ارزش راهبردی گزارش</p>
            <p>این گزارش نه صرفاً یک گزارش توصیفی، بلکه ابزاری راهبردی برای مدیران سازمان است؛ راهنمایی برای حرکت به‌سوی بلوغ دیجیتال، بهبود بهره‌وری، تقویت رقابت‌پذیری و تسهیل تصمیم‌گیری‌های کلان در حوزه فناوری اطلاعات.</p>
            <p>پیشنهادات ارائه‌شده در این سند، ترکیبی از توصیه‌های مدیریتی، فنی و اجرایی هستند که می‌توانند مبنای تدوین نقشه راه تحول دیجیتال سازمان قرار گیرند.</p>
            <p class="bold-title">تحلیل سطح بلوغ فناوری اطلاعات</p>
            <p>در بخش کلیدی این ارزیابی، سطح بلوغ فرآیندهای IT شرکت با تکیه بر مدل بلوغ COBIT سنجیده شده است. این مدل، میزان بلوغ سازمان در پیاده‌سازی و بهره‌برداری از فرآیندهای حیاتی فناوری اطلاعات را در پنج سطح به‌صورت زیر طبقه‌بندی می‌کند:</p>
            <ul>
                <li>سطح ۱ – تلاش فردی (Initial)</li>
                <li>سطح ۲ – کنترل شده (Managed)</li>
                <li>سطح ۳ – استاندارد شده (Defined)</li>
                <li>سطح ۴ – پیشرفته (Quantitatively Managed)</li>
                <li>سطح ۵ – نوآور و پیشرو (Optimizing)</li>
            </ul>
            <p>محاسبه سطح بلوغ، بر پایه میانگین امتیازات سؤالات مربوط به هر سطح انجام گرفته است. تنها سؤالاتی در محاسبه لحاظ شده‌اند که حداقل امتیاز ۶۰ را کسب کرده‌اند. اگر میانگین یک سطح نیز از ۶۰ بالاتر بوده باشد، آن سطح به عنوان سطح تحقق‌یافته در نظر گرفته شده است.</p>
            <p>نتیجه این تحلیل، نمایی روشن از جایگاه کنونی شرکت در مسیر بلوغ فناوری اطلاعات ارائه می‌دهد و نقطه شروعی برای برنامه‌ریزی بهبودهای آینده خواهد بود.</p>
        </div>
        <div class="separator"></div>
        <div class="metrics-container">
            @if (!is_null($maturityData['overallMaturityLevel'] ?? null))
                <div class="metric-card">
                    <h2>سطح بلوغ کلی فناوری اطلاعات</h2>
                    <div>
                        <span class="score-circle">
                            @php
                                $levelText = [
                                    1 => 'یک',
                                    2 => 'دو',
                                    3 => 'سه',
                                    4 => 'چهار',
                                    5 => 'پنج'
                                ];
                                $level = $maturityData['overallMaturityLevel'] ?? null;
                                echo isset($levelText[$level]) ? $levelText[$level] : 'نامشخص';
                            @endphp
                        </span>
                    </div>
                </div>
            @else
                <div class="metric-card" style="color: #e74c3c;">داده کافی برای محاسبه سطح بلوغ کلی وجود ندارد.</div>
            @endif
            @if (!is_null($finalScore))
                <div class="metric-card">
                    <h2>امتیاز نهایی کسب شده از صد نمره</h2>
                    <div>
                        <span class="score-circle">{{ e(is_numeric($finalScore) ? floor($finalScore) : 'نامشخص') }}</span>
                    </div>
                </div>
            @endif
        </div>
        <div class="card">
            <div>
                @if (!empty($strengths))
                    <table class="table strength-table">
                        <thead>
                            <tr>
                                <th>شماره ردیف</th>
                                <th>شرح نقاط قوت</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($strengths as $index => $item)
                                @if (!empty($item['content']))
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @php
                                                $lines = array_filter(explode("\n", $item['content']), 'trim');
                                                $isList = false;
                                                foreach ($lines as $line) {
                                                    if (preg_match('/^[\-\*\d+\.\s]+/', $line)) {
                                                        $isList = true;
                                                        break;
                                                    }
                                                }
                                            @endphp
                                            @if ($isList)
                                                <ul>
                                                    @foreach ($lines as $line)
                                                        <li>{{ e(trim(preg_replace('/^[\-\*\d+\.\s]+/', '', $line))) }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                {!! nl2br(e($item['content'])) !!}
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p style="text-align: center;">داده‌ای برای نمایش نقاط قوت وجود ندارد</p>
                @endif
            </div>
        </div>
        <div class="card">
            <div>
                @if (!empty($highRisks))
                    <table class="table risk-high-table">
                        <thead>
                            <tr>
                                <th>شماره ردیف</th>
                                <th>شرح ریسک با شدت بالا</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($highRisks as $index => $item)
                                @if (!empty($item['content']))
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @php
                                                $lines = array_filter(explode("\n", $item['content']), 'trim');
                                                $isList = false;
                                                foreach ($lines as $line) {
                                                    if (preg_match('/^[\-\*\d+\.\s]+/', $line)) {
                                                        $isList = true;
                                                        break;
                                                    }
                                                }
                                            @endphp
                                            @if ($isList)
                                                <ul>
                                                    @foreach ($lines as $line)
                                                        <li>{{ e(trim(preg_replace('/^[\-\*\d+\.\s]+/', '', $line))) }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                {!! nl2br(e($item['content'])) !!}
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p style="text-align: center;">داده‌ای برای نمایش ریسک با شدت بالا وجود ندارد</p>
                @endif
            </div>
        </div>
        <div class="card">
            <div>
                @if (!empty($mediumRisks))
                    <table class="table risk-medium-table">
                        <thead>
                            <tr>
                                <th>شماره ردیف</th>
                                <th>شرح ریسک با شدت متوسط</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mediumRisks as $index => $item)
                                @if (!empty($item['content']))
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @php
                                                $lines = array_filter(explode("\n", $item['content']), 'trim');
                                                $isList = false;
                                                foreach ($lines as $line) {
                                                    if (preg_match('/^[\-\*\d+\.\s]+/', $line)) {
                                                        $isList = true;
                                                        break;
                                                    }
                                                }
                                            @endphp
                                            @if ($isList)
                                                <ul>
                                                    @foreach ($lines as $line)
                                                        <li>{{ e(trim(preg_replace('/^[\-\*\d+\.\s]+/', '', $line))) }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                {!! nl2br(e($item['content'])) !!}
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p style="text-align: center;">داده‌ای برای نمایش ریسک با شدت متوسط وجود ندارد</p>
                @endif
            </div>
        </div>
        <div class="card">
            <div>
                @if (!empty($improvementOpportunities))
                    <table class="table improvement-table">
                        <thead>
                            <tr>
                                <th>شماره ردیف</th>
                                <th>شرح فرصت بهبود</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($improvementOpportunities as $index => $item)
                                @if (!empty($item['content']))
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @php
                                                $lines = array_filter(explode("\n", $item['content']), 'trim');
                                                $isList = false;
                                                foreach ($lines as $line) {
                                                    if (preg_match('/^[\-\*\d+\.\s]+/', $line)) {
                                                        $isList = true;
                                                        break;
                                                    }
                                                }
                                            @endphp
                                            @if ($isList)
                                                <ul>
                                                    @foreach ($lines as $line)
                                                        <li>{{ e(trim(preg_replace('/^[\-\*\d+\.\s]+/', '', $line))) }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                {!! nl2br(e($item['content'])) !!}
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p style="text-align: center;">داده‌ای برای نمایش فرصت بهبود وجود ندارد</p>
                @endif
            </div>
        </div>
        <div class="card">
            <div>
                @if (!empty($lowRisks))
                    <table class="table risk-low-table">
                        <thead>
                            <tr>
                                <th>شماره ردیف</th>
                                <th>شرح ریسک با شدت پایین</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lowRisks as $index => $item)
                                @if (!empty($item['content']))
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @php
                                                $lines = array_filter(explode("\n", $item['content']), 'trim');
                                                $isList = false;
                                                foreach ($lines as $line) {
                                                    if (preg_match('/^[\-\*\d+\.\s]+/', $line)) {
                                                        $isList = true;
                                                        break;
                                                    }
                                                }
                                            @endphp
                                            @if ($isList)
                                                <ul>
                                                    @foreach ($lines as $line)
                                                        <li>{{ e(trim(preg_replace('/^[\-\*\d+\.\s]+/', '', $line))) }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                {!! nl2br(e($item['content'])) !!}
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p style="text-align: center;">داده‌ای برای نمایش ریسک با شدت پایین وجود ندارد</p>
                @endif
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const jalaliDateElement = document.getElementById('jalali-date');
            if (jalaliDateElement && typeof jalaali !== 'undefined') {
                const gregorianDate = new Date(jalaliDateElement.textContent);
                const jalali = jalaali.toJalaali(gregorianDate.getFullYear(), gregorianDate.getMonth() + 1, gregorianDate.getDate());
                jalaliDateElement.textContent = `${jalali.jy}/${jalali.jm}/${jalali.jd}`;
            } else {
                console.error('Jalaali-js یا تاریخ ناموجود است.');
            }
            window.print();
            window.onafterprint = function() {
                window.close();
            };
        });
    </script>
</body>
</html>