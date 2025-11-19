<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">    
<title>گزارش ارزیابی فناوری اطلاعات</title>
    <link href="https://cdn.jsdelivr.net/npm/vazir-font@30.1.0/dist/font-face.css" rel="stylesheet" onerror="this.style.display='none';console.error('فونت لود نشد، از فونت پیش‌فرض استفاده می‌شود');">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <script src="https://cdn.jsdelivr.net/npm/jalaali-js@1.2.0/dist/jalaali.min.js"></script>
    <style>
        /* استایل‌های فعلی بدون تغییر (کپی از report.blade.php) */
        body {
            font-family: 'Vazir', Arial, sans-serif;
            background: #f0f4f8;
            margin: 0;
            padding: 20px;
            color: #2c3e50;
            direction: rtl;
            background-image: linear-gradient(135deg, #f5f7fa 25%, #e0e7f0 100%);
            position: relative;
            min-height: 100vh;
        }
        .report-wrapper {
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
            background: linear-gradient(90deg, #3498db, #2980b9);
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
            background: linear-gradient(90deg, #3498db, #2980b9);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            font-weight: 600;
            padding: 0 8px;
            border-radius: 5px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
        }
        .title-highlight {
            background: linear-gradient(90deg, #ffeb3b, #ff9800);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            font-weight: 600;
            padding: 0 8px;
            border-radius: 5px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
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
            border-radius: 10px;
            border: 1px solid #ecf0f1;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
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
            transition: background 0.3s ease;
        }
        .table th {
            text-align: center;
        }
        .table th:hover, .table td:hover {
            background: #f9f9f9;
        }
        .strength-table th {
            background: linear-gradient(90deg, #27ae60, #2ecc71);
            color: #fff;
        }
        .strength-table td:first-child {
            color: #fff;
            background: linear-gradient(90deg, #27ae60, #2ecc71);
        }
        .risk-high-table th {
            background: linear-gradient(90deg, #e74c3c, #c0392b);
            color: #fff;
        }
        .risk-high-table td:first-child {
            color: #fff;
            background: linear-gradient(90deg, #e74c3c, #c0392b);
        }
        .risk-medium-table th {
            background: linear-gradient(90deg, #e67e22, #d35400);
            color: #fff;
        }
        .risk-medium-table td:first-child {
            color: #fff;
            background: linear-gradient(90deg, #e67e22, #d35400);
        }
        .improvement-table th {
            background: linear-gradient(90deg, #3498db, #2980b9);
            color: #fff;
        }
        .improvement-table td:first-child {
            color: #fff;
            background: linear-gradient(90deg, #3498db, #2980b9);
        }
        .risk-low-table th {
            background: linear-gradient(90deg, #f1c40f, #f39c12);
            color: #fff;
        }
        .risk-low-table td:first-child {
            color: #fff;
            background: linear-gradient(90deg, #f1c40f, #f39c12);
        }
        .profile-btn, .print-btn {
            position: fixed;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: 600;
            font-family: 'Vazir', Arial, sans-serif;
            line-height: 1.5;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            z-index: 1000;
            text-decoration: none;
            transition: all 0.3s ease;
            text-align: center;
        }
        .print-btn {
            bottom: 75px;
            left: 20px;
            background: linear-gradient(90deg, #28a745, #218838);
        }
        .print-btn:hover {
            background: linear-gradient(90deg, #218838, #28a745);
            transform: scale(1.05);
        }
        .profile-btn {
            bottom: 20px;
            left: 20px;
            background: linear-gradient(90deg, #3498db, #2980b9);
        }
        .profile-btn:hover {
            background: linear-gradient(90deg, #2980b9, #3498db);
            transform: scale(1.05);
        }
        .metrics-container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 25px;
        }
        .metric-card {
            flex: 1;
            background: linear-gradient(135deg, #ffffff, #f5f7fa);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            min-width: 0;
            transition: transform 0.3s ease;
        }
        .metric-card:hover {
            transform: translateY(-5px);
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
            background: linear-gradient(135deg, #ecf0f1, #bdc3c7);
            text-align: center;
            margin-top: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
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
            .print-btn, .profile-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    @if(isset($assessment_group_id))
        <button onclick="window.open('{{ route('report.print', ['assessment_group_id' => $assessment_group_id]) }}', '_blank')" class="print-btn">چــاپ گـزارش</button>
    @else
        <p class="error-message">شناسه گروه ارزیابی در دسترس نیست. لطفاً دوباره تلاش کنید.</p>
    @endif
    <a href="{{ route('profile') }}" class="profile-btn">پـروفایل کاربر</a>
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
        @if (auth()->user()->remaining_evaluations > 0 && auth()->user()->remaining_days > 0)
            @php
                $inProgressGroup = App\Models\AssessmentGroup::where('user_id', auth()->id())
                    ->where('status', 'in_progress')
                    ->first();
            @endphp
            @if (!$inProgressGroup)
                <div class="card" style="text-align: center;">
                    <a href="{{ route('assessment.domains') }}" class="btn">شروع ارزیابی جدید</a>
                </div>
            @endif
        @endif
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
        });
    </script>
</body>
</html>