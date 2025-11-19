<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
<title>گزارش مقایسه‌ای فناوری اطلاعات</title>
    <!-- فونت Vazir -->
    <link href="https://cdn.jsdelivr.net/npm/vazir-font@30.1.0/dist/font-face.css" rel="stylesheet" onerror="this.style.display='none';console.error('فونت لود نشد، از فونت پیش‌فرض استفاده می‌شود');">
    <!-- Chart.js و پلاگین DataLabels -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
    <!-- بوت‌استرپ CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <!-- بوت‌استرپ JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <!-- فونت‌آوسام برای آیکون‌ها -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- فاوآیکن فیدورا -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <style>
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
        .card-header {
            text-align: center;
            background: linear-gradient(90deg, #20B2AA, #008B8B);
            color: white;
        }
        .chart-container {
            margin: 30px 0;
            height: 500px !important;
            width: 100% !important;
            position: relative;
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }
        .chart-wrapper {
            width: 50%;
            height: 100%;
        }
        #performanceChartRadar, #performanceChartBar {
            height: 500px !important;
            width: 100% !important;
        }
        .subcategory-chart-container {
            margin: 30px 0;
            height: 300px !important;
            width: 100% !important;
            position: relative;
        }
        .subcategory-chart {
            height: 300px !important;
            width: 100% !important;
        }
        .risk-chart-container, .maturity-chart-container {
            margin: 30px 0;
            height: 400px !important;
            width: 100% !important;
            position: relative;
        }
        #riskDistributionChart, #maturityLevelChart {
            height: 400px !important;
            width: 100% !important;
        }
        .error-message {
            color: #e74c3c;
            text-align: center;
            margin: 20px 0;
        }
        .final-score-card .card-body {
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }
        .final-score-card .score-circle {
            display: inline-block;
            width: 80px;
            height: 80px;
            line-height: 80px;
            border-radius: 50%;
            background-color: #f8f9fa;
            text-align: center;
            margin-left: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .score-difference {
            margin-right: 20px;
            font-size: 1.2rem;
        }
        .report-description .highlight {
            font-weight: bold;
            color: #1E90FF;
        }
        .profile-btn {
            position: fixed;
            bottom: 20px;
            left: 20px;
            padding: 12px 25px;
            color: #fff;
            background: linear-gradient(90deg, #3498db, #2980b9);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            z-index: 1000;
            text-decoration: none;
            transition: all 0.3s ease;
            text-align: center;
        }
        .profile-btn:hover {
            background: linear-gradient(90deg, #2980b9, #3498db);
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <a href="{{ route('profile') }}" class="profile-btn">پروفایل کاربر</a>
    <div class="report-wrapper">
        <div class="header-container">
            <img src="{{ asset('images/Logo.png') }}" alt="Feedora Logo">
        </div>

        <div class="report-title-bar">
            <h1 class="report-title">
                گزارش مقایسه‌ای فناوری اطلاعات شرکت
                <span class="highlight" title="این اطلاعات از پروفایل شما بارگذاری شده است">{{ $targetUser->company_alias ?? 'نام شرکت مشخص نشده' }}</span>
            </h1>
        </div>

      @if($assessment_group_id && $completedGroups->isNotEmpty())
    <!-- بخش توضیحات گزارش بدون هدر -->
    <div class="card">
        <div class="card-body">
            <div class="report-description">
                <p class="bold-title">
                    گزارش مقایسه‌ای ارزیابی‌های فناوری اطلاعات
                </p>
                <p>
                    هدف از تهیه این گزارش، ارائه یک تحلیل جامع و مقایسه‌ای بین دو ارزیابی اخیر وضعیت زیرساخت‌ها، فرآیندها و عملکرد فناوری اطلاعات (ICT) شرکت <span class="highlight">{{ $targetUser->company_alias ?? 'شرکت فناوری اطلاعات' }}</span> است. با توجه به انجام متوالی ارزیابی‌های دوره‌ای، ضروری است تغییرات کمّی و کیفی در حوزه‌های مختلف ICT شامل امنیت اطلاعات، مدیریت شبکه، حاکمیت IT، سرویس‌های پشتیبانی و تحول دیجیتال، به‌صورت دقیق و قابل تحلیل مورد ارزیابی مجدد قرار گیرند.
                </p>
                <p>
                    در این گزارش، نتایج ارزیابی اولیه (تاریخ: <span class="highlight">{{ $previousGroupDate }}</span>) با آخرین ارزیابی مجدد (تاریخ: <span class="highlight">{{ $currentGroupDate }}</span>) مقایسه شده است. این مقایسه به‌منظور شناسایی میزان پیشرفت، اثربخشی اقدامات اصلاحی اعمال شده صورت گرفته است.
                </p>
            </div>
        </div>
    </div>

            <!-- کارت‌های جداگانه برای مقایسه امتیاز نهایی -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card final-score-card">
                        <div class="card-header text-center">
                            <h2>امتیاز ارزیابی فعلی</h2>
                        </div>
                        <div class="card-body">
                            <span>امتیاز: </span>
                            <span class="score-circle">{{ round($currentAssessment->finalScore) }}</span>
                            <?php
                                $scoreDifference = $currentAssessment->finalScore - $previousAssessment->finalScore;
                                $percentageChange = $previousAssessment->finalScore != 0 ? ($scoreDifference / $previousAssessment->finalScore) * 100 : 0;
                            ?>
                            <span class="score-difference {{ $scoreDifference >= 0 ? 'text-success' : 'text-danger' }}">
                                تغییر: {{ round($percentageChange, 2) }}%
                                <i class="fas fa-arrow-{{ $scoreDifference >= 0 ? 'up' : 'down' }}"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card final-score-card">
                        <div class="card-header text-center">
                            <h2>امتیاز ارزیابی قبلی</h2>
                        </div>
                        <div class="card-body">
                            <span>امتیاز: </span>
                            <span class="score-circle">{{ round($previousAssessment->finalScore) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- کارت برای نمودارهای راداری و میله‌ای -->
            <div class="card">
                <div class="card-header">
                    <h2>مقایسه درصد عملکرد فناوری اطلاعات</h2>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <div class="chart-wrapper">
                            <canvas id="performanceChartRadar"></canvas>
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="performanceChartBar"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- نمودار میله‌ای عمودی برای توزیع سطوح بلوغ -->
            <div class="card">
                <div class="card-header">
                    <h2>مقایسه توزیع سطوح بلوغ</h2>
                </div>
                <div class="card-body">
                    <div class="maturity-chart-container">
                        <canvas id="maturityLevelChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- نمودارهای میله‌ای عمودی برای زیرمجموعه‌های هر حوزه -->
            @foreach ($currentAssessment->subcategories as $domain => $subs)
                @if (!empty($subs))
                    <div class="card">
                        <div class="card-header">
                            <h2>مقایسه درصد عملکرد حوزه: {{ $domain }}</h2>
                        </div>
                        <div class="card-body">
                            <div class="subcategory-chart-container">
                                <canvas class="subcategory-chart" data-domain="{{ $domain }}"></canvas>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach

            <!-- نمودار میله‌ای عمودی برای توزیع ریسک‌ها -->
            <div class="card">
                <div class="card-header">
                    <h2>مقایسه توزیع ریسک‌ها بر اساس شدت</h2>
                </div>
                <div class="card-body">
                    <div class="risk-chart-container">
                        <canvas id="riskDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        @else
    <div class="card">
        <div class="card-header">
            <h2>خطا</h2>
        </div>
        <div class="card-body text-center">
            <p class="error-message">حداقل دو ارزیابی تکمیل‌شده برای نمایش گزارش مقایسه‌ای لازم است.</p>
            <a href="{{ route('assessment.domains') }}" class="btn btn-primary">شروع ارزیابی جدید</a>
        </div>
    </div>
@endif
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        console.log('Chart.js script is running...');

        // ثبت پلاگین datalabels
        if (typeof ChartDataLabels !== 'undefined') {
            Chart.register(ChartDataLabels);
            console.log('ChartDataLabels plugin registered successfully');
        } else {
            console.error('ChartDataLabels plugin failed to load');
        }

        // فقط اگر گروه ارزیابی انتخاب شده باشد نمودارها را بساز
        @if($assessment_group_id && $completedGroups->isNotEmpty())
            // داده‌های مشترک برای نمودارهای عملکرد
            const labels = @json($currentAssessment->labels);
            const currentData = @json($currentAssessment->dataValues);
            const previousData = @json($previousAssessment->dataValues);

            console.log('Chart Data:', {
                labels: labels,
                currentData: currentData,
                previousData: previousData
            });

            // نمودار راداری
            const radarCtx = document.getElementById('performanceChartRadar');
            if (!radarCtx) {
                console.error('Canvas element with ID "performanceChartRadar" not found');
            } else {
                const radarChart = new Chart(radarCtx, {
                    type: 'radar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'ارزیابی فعلی',
                                data: currentData,
                                backgroundColor: 'rgba(32, 178, 170, 0.2)',
                                borderColor: '#20B2AA',
                                borderWidth: 2,
                                pointBackgroundColor: '#20B2AA',
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: '#20B2AA'
                            },
                            {
                                label: 'ارزیابی قبلی',
                                data: previousData,
                                backgroundColor: 'rgba(70, 130, 180, 0.2)',
                                borderColor: '#4682B4',
                                borderWidth: 2,
                                pointBackgroundColor: '#4682B4',
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: '#4682B4'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            r: {
                                angleLines: { display: true },
                                grid: { color: 'rgba(0, 0, 0, 0.1)', lineWidth: 1 },
                                ticks: {
                                    beginAtZero: true,
                                    max: 100,
                                    stepSize: 20,
                                    callback: function(value) { return value + '%'; },
                                    font: { size: 12, family: 'Vazir' }
                                },
                                pointLabels: { font: { size: 14, family: 'Vazir' } }
                            }
                        },
                        plugins: {
                            legend: { position: 'top', labels: { font: { size: 14, family: 'Vazir' } } },
                            tooltip: { enabled: true, callbacks: { label: function(ctx) { return ctx.parsed.r + '%'; } }, bodyFont: { family: 'Vazir' } },
                            datalabels: { display: false }
                        },
                        layout: { padding: 20 }
                    }
                });
                console.log('Radar Chart initialized:', radarChart);
            }

            // نمودار میله‌ای عمودی
            const barCtx = document.getElementById('performanceChartBar');
            if (!barCtx) {
                console.error('Canvas element with ID "performanceChartBar" not found');
            } else {
                const barChart = new Chart(barCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'ارزیابی فعلی',
                                data: currentData,
                                backgroundColor: '#20B2AA',
                                borderColor: '#008B8B',
                                borderWidth: 1,
                                borderRadius: 5,
                                hoverBackgroundColor: '#008B8B',
                                hoverBorderColor: '#008B8B'
                            },
                            {
                                label: 'ارزیابی قبلی',
                                data: previousData,
                                backgroundColor: '#4682B4',
                                borderColor: '#4682B4',
                                borderWidth: 1,
                                borderRadius: 5,
                                hoverBackgroundColor: '#4682B4',
                                hoverBorderColor: '#4682B4'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                ticks: { font: { size: 12, family: 'Vazir', weight: 'bold' }, maxRotation: 45, minRotation: 45 },
                                grid: { display: false }
                            },
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: { callback: function(value) { return value + '%'; }, font: { size: 12, family: 'Vazir' } },
                                grid: { color: 'rgba(0, 0, 0, 0.1)' }
                            }
                        },
                        plugins: {
                            legend: { position: 'top', labels: { font: { size: 14, family: 'Vazir' } } },
                            tooltip: { enabled: true, callbacks: { label: function(ctx) { return ctx.parsed.y + '%'; } }, bodyFont: { family: 'Vazir' } },
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                offset: 5,
                                formatter: function(value) { return Math.round(value) + '%'; },
                                font: { size: 12, family: 'Vazir', weight: 'bold' },
                                color: '#333'
                            }
                        },
                        layout: { padding: { top: 30 } }
                    }
                });
                console.log('Bar Chart initialized:', barChart);
            }

            // نمودار میله‌ای عمودی برای توزیع سطوح بلوغ
            const maturityCtx = document.getElementById('maturityLevelChart');
            if (!maturityCtx) {
                console.error('Canvas element with ID "maturityLevelChart" not found');
            } else {
                const currentMaturityData = @json($currentMaturityData['levelAverages']);
                const previousMaturityData = @json($previousMaturityData['levelAverages']);
                const maturityLabels = ['سطح 1', 'سطح 2', 'سطح 3', 'سطح 4', 'سطح 5'];

                const totalCurrentMaturity = currentMaturityData.reduce((sum, val) => sum + val, 0);
                const totalPreviousMaturity = previousMaturityData.reduce((sum, val) => sum + val, 0);

                if (totalCurrentMaturity === 0 && totalPreviousMaturity === 0) {
                    maturityCtx.parentElement.style.display = 'none';
                    const header = maturityCtx.closest('.card').querySelector('.card-header');
                    if (header) header.style.display = 'none';
                } else {
                    const maturityChart = new Chart(maturityCtx, {
                        type: 'bar',
                        data: {
                            labels: maturityLabels,
                            datasets: [
                                {
                                    label: 'ارزیابی فعلی',
                                    data: currentMaturityData,
                                    backgroundColor: '#20B2AA',
                                    borderColor: '#008B8B',
                                    borderWidth: 1,
                                    borderRadius: 5,
                                    hoverBackgroundColor: '#008B8B',
                                    hoverBorderColor: '#008B8B'
                                },
                                {
                                    label: 'ارزیابی قبلی',
                                    data: previousMaturityData,
                                    backgroundColor: '#4682B4',
                                    borderColor: '#4682B4',
                                    borderWidth: 1,
                                    borderRadius: 5,
                                    hoverBackgroundColor: '#4682B4',
                                    hoverBorderColor: '#4682B4'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    ticks: { font: { size: 12, family: 'Vazir' } },
                                    grid: { display: false }
                                },
                                y: {
                                    beginAtZero: true,
                                    max: 100,
                                    ticks: { callback: function(value) { return value + '%'; }, font: { size: 12, family: 'Vazir' } },
                                    grid: { color: 'rgba(0, 0, 0, 0.1)' }
                                }
                            },
                            plugins: {
                                legend: { position: 'top', labels: { font: { size: 14, family: 'Vazir' } } },
                                tooltip: { enabled: true, callbacks: { label: function(ctx) { return ctx.parsed.y + '%'; } }, bodyFont: { family: 'Vazir' } },
                                datalabels: {
                                    anchor: 'end',
                                    align: 'top',
                                    offset: 5,
                                    formatter: function(value) { return Math.round(value) + '%'; },
                                    font: { size: 12, family: 'Vazir', weight: 'bold' },
                                    color: '#333'
                                }
                            },
                            layout: { padding: { top: 30 } }
                        }
                    });
                    console.log('Maturity Level Chart initialized:', maturityChart);
                }
            }

            // تابع برای تجمیع زیرمجموعه‌های مشابه
            function aggregateSubcategories(subs) {
                const grouped = {};
                subs.forEach(item => {
                    const name = item.name;
                    if (!grouped[name]) {
                        grouped[name] = { performances: [], count: 0 };
                    }
                    grouped[name].performances.push(item.performance);
                    grouped[name].count += 1;
                });
                return Object.keys(grouped).map(name => ({
                    name: name,
                    performance: grouped[name].performances.reduce((sum, val) => sum + val, 0) / grouped[name].count
                }));
            }

            // نمودارهای میله‌ای عمودی برای زیرمجموعه‌ها
            const currentSubcategoryData = @json($currentAssessment->subcategories);
            const previousSubcategoryData = @json($previousAssessment->subcategories);
            const subcategoryCharts = document.querySelectorAll('.subcategory-chart');
            subcategoryCharts.forEach(canvas => {
                const domain = canvas.getAttribute('data-domain');
                const currentSubs = currentSubcategoryData[domain] || [];
                const previousSubs = previousSubcategoryData[domain] || [];

                if (currentSubs.length === 0) {
                    canvas.parentElement.style.display = 'none';
                    const header = canvas.closest('.card').querySelector('.card-header');
                    if (header) header.style.display = 'none';
                    return;
                }

                const aggregatedCurrentSubs = aggregateSubcategories(currentSubs);
                const aggregatedPreviousSubs = aggregateSubcategories(previousSubs);
                const subLabels = aggregatedCurrentSubs.map(item => item.name);
                const currentSubData = aggregatedCurrentSubs.map(item => item.performance);
                const previousSubData = subLabels.map(label => {
                    const found = aggregatedPreviousSubs.find(item => item.name === label);
                    return found ? found.performance : 0;
                });

                const subChart = new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: subLabels,
                        datasets: [
                            {
                                label: 'ارزیابی فعلی',
                                data: currentSubData,
                                backgroundColor: '#20B2AA',
                                borderColor: '#008B8B',
                                borderWidth: 1,
                                borderRadius: 5,
                                hoverBackgroundColor: '#008B8B',
                                hoverBorderColor: '#008B8B'
                            },
                            {
                                label: 'ارزیابی قبلی',
                                data: previousSubData,
                                backgroundColor: '#4682B4',
                                borderColor: '#4682B4',
                                borderWidth: 1,
                                borderRadius: 5,
                                hoverBackgroundColor: '#4682B4',
                                hoverBorderColor: '#4682B4'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                ticks: { font: { size: 12, family: 'Vazir', weight: 'bold' }, maxRotation: 45, minRotation: 45 },
                                grid: { display: false }
                            },
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: { callback: function(value) { return value + '%'; }, font: { size: 12, family: 'Vazir' } },
                                grid: { color: 'rgba(0, 0, 0, 0.1)' }
                            }
                        },
                        plugins: {
                            legend: { position: 'top', labels: { font: { size: 14, family: 'Vazir' } } },
                            tooltip: { enabled: true, callbacks: { label: function(ctx) { return ctx.parsed.y + '%'; } }, bodyFont: { family: 'Vazir' } },
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                offset: 5,
                                formatter: function(value) { return Math.round(value) + '%'; },
                                font: { size: 12, family: 'Vazir', weight: 'bold' },
                                color: '#333'
                            }
                        },
                        layout: { padding: { top: 30 } }
                    }
                });
                console.log(`Subcategory Chart for ${domain} initialized:`, subChart);
            });

            // نمودار میله‌ای عمودی برای توزیع ریسک‌ها
            const riskCtx = document.getElementById('riskDistributionChart');
            if (!riskCtx) {
                console.error('Canvas element with ID "riskDistributionChart" not found');
            } else {
                const currentHighRiskCount = {{ count($currentAssessment->highRisks) }};
                const currentMediumRiskCount = {{ count($currentAssessment->mediumRisks) }};
                const currentLowRiskCount = {{ count($currentAssessment->lowRisks) }};
                const previousHighRiskCount = {{ count($previousAssessment->highRisks) }};
                const previousMediumRiskCount = {{ count($previousAssessment->mediumRisks) }};
                const previousLowRiskCount = {{ count($previousAssessment->lowRisks) }};
                const totalCurrentRisks = currentHighRiskCount + currentMediumRiskCount + currentLowRiskCount;
                const totalPreviousRisks = previousHighRiskCount + previousMediumRiskCount + previousLowRiskCount;

                if (totalCurrentRisks === 0 && totalPreviousRisks === 0) {
                    riskCtx.parentElement.style.display = 'none';
                    const header = riskCtx.closest('.card').querySelector('.card-header');
                    if (header) header.style.display = 'none';
                } else {
                    const riskChart = new Chart(riskCtx, {
                        type: 'bar',
                        data: {
                            labels: ['ریسک با شدت بالا', 'ریسک با شدت متوسط', 'ریسک با شدت پایین'],
                            datasets: [
                                {
                                    label: 'ارزیابی فعلی',
                                    data: [currentHighRiskCount, currentMediumRiskCount, currentLowRiskCount],
                                    backgroundColor: '#20B2AA',
                                    borderColor: '#008B8B',
                                    borderWidth: 1,
                                    borderRadius: 5,
                                    hoverBackgroundColor: '#008B8B',
                                    hoverBorderColor: '#008B8B'
                                },
                                {
                                    label: 'ارزیابی قبلی',
                                    data: [previousHighRiskCount, previousMediumRiskCount, previousLowRiskCount],
                                    backgroundColor: '#4682B4',
                                    borderColor: '#4682B4',
                                    borderWidth: 1,
                                    borderRadius: 5,
                                    hoverBackgroundColor: '#4682B4',
                                    hoverBorderColor: '#4682B4'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: { ticks: { font: { size: 12, family: 'Vazir' } }, grid: { display: false } },
                                y: {
                                    beginAtZero: true,
                                    ticks: { stepSize: 1, font: { size: 12, family: 'Vazir' } },
                                    grid: { color: 'rgba(0, 0, 0, 0.1)' }
                                }
                            },
                            plugins: {
                                legend: { position: 'top', labels: { font: { size: 14, family: 'Vazir' } } },
                                tooltip: { enabled: true, bodyFont: { family: 'Vazir' } },
                                datalabels: {
                                    anchor: 'end',
                                    align: 'top',
                                    offset: 5,
                                    formatter: function(value) { return value; },
                                    font: { size: 12, family: 'Vazir', weight: 'bold' },
                                    color: '#333'
                                }
                            },
                            layout: { padding: { top: 30 } }
                        }
                    });
                    console.log('Risk Distribution Chart initialized:', riskChart);
                }
            }
        @endif
    });
    </script>
</body>
</html>