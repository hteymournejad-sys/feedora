<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>گزارش ارزیابی فناوری اطلاعات</title>
    <style>
        body {
            font-family: 'Tahoma', sans-serif; /* استفاده از فونت پیش‌فرض Tahoma */
            direction: rtl;
            text-align: right;
            margin: 0;
            padding: 20px;
        }
        .report-wrapper {
            max-width: 1000px;
            margin: 0 auto;
        }
        .header-container {
            text-align: center;
            margin-bottom: 20mm;
        }
        .logo-container img {
            max-width: 200px;
            margin-bottom: 10px;
        }
        .report-description {
            margin-bottom: 20mm;
            line-height: 1.8;
            font-size: 1.2rem;
        }
        .report-description p {
            margin-bottom: 1rem;
        }
        .report-description .bold-title {
            font-weight: 700;
        }
        .card {
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .card-header {
            padding: 10px;
            text-align: center;
        }
        .card-header h2 {
            margin: 0;
            font-size: 1.5rem;
            color: white;
        }
        .card-body {
            padding: 15px;
        }
        .chart-container {
            margin: 30px 0;
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }
        .chart-wrapper {
            width: 50%;
        }
        .chart-wrapper img {
            width: 100%;
            height: auto;
        }
        .subcategory-chart-container {
            margin: 30px 0;
        }
        .subcategory-chart-container img {
            width: 100%;
            height: auto;
        }
        .risk-chart-container {
            margin: 30px 0;
            text-align: center;
        }
        .risk-chart-container img {
            width: 400px;
            height: auto;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: right;
            font-size: 1.1rem;
        }
        .table th {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .text-center {
            text-align: center;
        }
        hr {
            margin: 50px 0;
        }
    </style>
</head>
<body>
    <div class="report-wrapper">
        <!-- هدر شامل لوگو -->
        <div class="header-container">
            <div class="logo-container">
                <img src="{{ public_path('images/feedora-Rep.png') }}" alt="Feedora Logo">
            </div>
        </div>

        <!-- بخش توضیحات -->
        <div class="card">
            <div class="card-body">
                <div class="report-description">
                    <p class="bold-title">گزارش ارزیابی واحد فناوری اطلاعات شرکت {{ $company_name }}</p>
                    <p>هدف از تهیه این گزارش، بررسی جامع وضعیت فعلی فناوری اطلاعات و ارتباطات (ICT) در شرکت {{ $company_name }} و شناسایی نقاط قوت، ضعف، فرصت‌ها و چالش‌های موجود در این حوزه می‌باشد. این ارزیابی به‌عنوان بخشی از استراتژی بهبود مستمر و توسعه سازمانی طراحی شده است و در دو فاز اصلی انجام شده است:</p>
                    <p>فاز اول: بررسی وجود حداقل امکانات و زیرساخت‌های لازم در حوزه فناوری اطلاعات و شناسایی گلوگاه‌های عملیاتی که می‌توانند بهره‌وری و کارایی سازمان را تحت تأثیر قرار دهند.</p>
                    <p>فاز دوم: ارائه برنامه‌های اصلاحی میان‌مدت و بلندمدت برای ارتقاء سطح فناوری اطلاعات، کاهش آسیب‌پذیری‌ها و همسویی با سیاست‌ها و استراتژی‌های شرکت خواهد بود.</p>
                    <p>این ارزیابی مبتنی بر چارچوب‌های مدیریتی و استانداردهای بین‌المللی از جمله COBIT 5 ، ITIL V4 ، ISMS ، و استانداردهای ISO 27001 (امنیت اطلاعات)، ISO 31000 (مدیریت ریسک) و ISO 20000 (مدیریت خدمات IT) انجام شده است.</p>
                    <p>در این فرآیند، حوزه‌های مختلف شامل مدیریت ریسک و امنیت اطلاعات، حاکمیت فناوری اطلاعات، زیرساخت و شبکه، سرویس‌ها و خدمات پشتیبانی، سامانه‌های نرم‌افزاری، تحول دیجیتال و هوشمندسازی شرکت در تاریخ {{ $report_date }} مورد بررسی دقیق قرار گرفته‌اند. نتایج حاصل از این ارزیابی به‌صورت ساختارمند و با توجه به معیارهای یک شرکت {{ $company_type }} با اندازه {{ $company_size }} تحلیل شده و در ادامه به استحضار خواهد رسید.</p>
                    <p>این گزارش به‌عنوان یک ابزار استراتژیک برای تصمیم‌گیری‌های آینده و بهبود عملکرد فناوری اطلاعات شرکت ارائه می‌شود و پیشنهادات عملیاتی و مدیریتی در آن گنجانده شده است تا به عنوان نقشه‌ای برای تحول دیجیتال و افزایش رقابت‌پذیری شرکت مورد استفاده قرار گیرد。</p>
                </div>
            </div>
        </div>

        <!-- نمودارهای راداری و میله‌ای -->
        <div class="card">
            <div class="card-header" style="background-color: #1E90FF;">
                <h2>درصد عملکرد فناوری اطلاعات</h2>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <div class="chart-wrapper">
                        @if (isset($chartImages['performanceChartRadar']))
                            <img src="{{ $chartImages['performanceChartRadar'] }}" alt="Radar Chart">
                        @else
                            <p class="text-center">نمودار راداری در دسترس نیست.</p>
                        @endif
                    </div>
                    <div class="chart-wrapper">
                        @if (isset($chartImages['performanceChartBar']))
                            <img src="{{ $chartImages['performanceChartBar'] }}" alt="Bar Chart">
                        @else
                            <p class="text-center">نمودار میله‌ای در دسترس نیست.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- نمودارهای میله‌ای افقی برای زیرمجموعه‌ها -->
        @foreach ($subcategories as $domain => $subs)
            @if (!empty($subs))
                <div class="card">
                    <div class="card-header" style="background-color: #17a2b8;">
                        <h2>درصد عملکرد حوزه: {{ $domain }}</h2>
                    </div>
                    <div class="card-body">
                        <div class="subcategory-chart-container">
                            @if (isset($chartImages['subcategoryChart-' . $domain]))
                                <img src="{{ $chartImages['subcategoryChart-' . $domain] }}" alt="Subcategory Chart for {{ $domain }}">
                            @else
                                <p class="text-center">نمودار برای {{ $domain }} در دسترس نیست.</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        <!-- جدول نقاط قوت -->
        <div class="card">
            <div class="card-header" style="background-color: #28a745;">
                <h2>نقاط قوت</h2>
            </div>
            <div class="card-body">
                @if (!empty($strengths))
                    <table class="table">
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
                                        <td>{{ $item['content'] }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-center">داده‌ای برای نمایش نقاط قوت وجود ندارد.</p>
                @endif
            </div>
        </div>

        <!-- جدول ریسک با شدت بالا -->
        <div class="card">
            <div class="card-header" style="background-color: #dc3545;">
                <h2>ریسک با شدت بالا</h2>
            </div>
            <div class="card-body">
                @if (!empty($highRisks))
                    <table class="table">
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
                                        <td>{{ $item['content'] }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-center">داده‌ای برای نمایش ریسک با شدت بالا وجود ندارد.</p>
                @endif
            </div>
        </div>

        <!-- جدول ریسک با شدت متوسط -->
        <div class="card">
            <div class="card-header" style="background-color: #fd7e14;">
                <h2>ریسک با شدت متوسط</h2>
            </div>
            <div class="card-body">
                @if (!empty($mediumRisks))
                    <table class="table">
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
                                        <td>{{ $item['content'] }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-center">داده‌ای برای نمایش ریسک با شدت متوسط وجود ندارد.</p>
                @endif
            </div>
        </div>

        <!-- جدول ریسک با شدت پایین -->
        <div class="card">
            <div class="card-header" style="background-color: #ffc107;">
                <h2>ریسک با شدت پایین</h2>
            </div>
            <div class="card-body">
                @if (!empty($lowRisks))
                    <table class="table">
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
                                        <td>{{ $item['content'] }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-center">داده‌ای برای نمایش ریسک با شدت پایین وجود ندارد.</p>
                @endif
            </div>
        </div>

        <!-- نمودار دایره‌ای برای توزیع ریسک‌ها -->
        @if (isset($chartImages['riskDistributionChart']))
            <div class="card">
                <div class="card-header" style="background-color: #6c757d;">
                    <h2>توزیع ریسک‌ها بر اساس شدت</h2>
                </div>
                <div class="card-body">
                    <div class="risk-chart-container">
                        <img src="{{ $chartImages['riskDistributionChart'] }}" alt="Risk Distribution Chart">
                    </div>
                </div>
            </div>
        @endif

        <!-- جدول فرصت بهبود -->
        <div class="card">
            <div class="card-header" style="background-color: #007bff;">
                <h2>فرصت بهبود</h2>
            </div>
            <div class="card-body">
                @if (!empty($improvementOpportunities))
                    <table class="table">
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
                                        <td>{{ $item['content'] }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-center">داده‌ای برای نمایش فرصت بهبود وجود ندارد.</p>
                @endif
            </div>
        </div>

        <!-- جدول وضعیت در حال توسعه -->
        <div class="card">
            <div class="card-header" style="background-color: #90ee90;">
                <h2>وضعیت در حال توسعه</h2>
            </div>
            <div class="card-body">
                @if (!empty($developingStatus))
                    <table class="table">
                        <thead>
                            <tr>
                                <th>شماره ردیف</th>
                                <th>شرح وضعیت در حال توسعه</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($developingStatus as $index => $item)
                                @if (!empty($item['content']))
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item['content'] }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-center">داده‌ای برای نمایش وضعیت در حال توسعه وجود ندارد.</p>
                @endif
            </div>
        </div>

        <!-- جدول پیشنهاد اصلاح -->
        <div class="card">
            <div class="card-header" style="background-color: #add8e6;">
                <h2>پیشنهاد اصلاح</h2>
            </div>
            <div class="card-body">
                @if (!empty($suggestions))
                    <table class="table">
                        <thead>
                            <tr>
                                <th>شماره ردیف</th>
                                <th>شرح پیشنهاد اصلاح</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($suggestions as $index => $item)
                                @if (!empty($item['content']))
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item['content'] }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-center">داده‌ای برای نمایش پیشنهاد اصلاح وجود ندارد.</p>
                @endif
            </div>
        </div>
    </div>
</body>
</html>