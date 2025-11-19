
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
 <title>گزارش تحلیلی فناوری اطلاعات</title>
    <!-- فونت Vazir -->
    <link href="https://cdn.jsdelivr.net/npm/vazir-font@30.1.0/dist/font-face.css" rel="stylesheet" onerror="this.style.display='none';console.error('فونت لود نشد، از فونت پیش‌فرض استفاده می‌شود');">
    <!-- Chart.js و پلاگین DataLabels -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
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
        .risk-chart-container {
            margin: 30px 0;
            height: 400px !important;
            width: 400px !important;
            margin-left: auto;
            margin-right: auto;
            position: relative;
        }
        #riskDistributionChart {
            height: 400px !important;
            width: 400px !important;
        }
        #maturityChartBar {
            height: 400px !important;
            width: 100% !important;
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
        .error-message {
            color: #e74c3c;
            text-align: center;
            margin: 20px 0;
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
                گزارش تحلیلی فناوری اطلاعات شرکت
                <span class="highlight" title="این اطلاعات از پروفایل شما بارگذاری شده است">{{ isset($company_name) ? $company_name : 'نام شرکت مشخص نشده' }}</span>
            </h1>
        </div>

        @if($assessment_group_id && $completedGroups->isNotEmpty())
            <!-- نمودارهای راداری و میله‌ای برای درصد عملکرد -->
            <div class="card">
                <div class="card-header" style="background: linear-gradient(90deg, #20B2AA, #008B8B); color: white;">
                    <h2>درصد عملکرد فناوری اطلاعات</h2>
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

            <!-- نمودار میله‌ای برای سطح بلوغ -->
            @if (!empty($maturityData['levelAverages']))
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(90deg, #87CEEB, #4682B4); color: white;">
                        <h2>توزیع سطح بلوغ</h2>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 400px !important;">
                            <canvas id="maturityChartBar"></canvas>
                        </div>
                    </div>
                </div>
            @endif

            <!-- نمودار دایره‌ای برای توزیع ریسک‌ها -->
            <div class="card">
                <div class="card-header" style="background: linear-gradient(90deg, #6c757d, #5a6268); color: white;">
                    <h2>توزیع ریسک‌ها بر اساس شدت</h2>
                </div>
                <div class="card-body">
                    <div class="risk-chart-container">
                        <canvas id="riskDistributionChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- نمودارهای میله‌ای عمودی برای زیرمجموعه‌های هر حوزه -->
            @foreach ($subcategories as $domain => $subs)
                @if (!empty($subs))
                    <div class="card">
                        <div class="card-header" style="background: linear-gradient(90deg, 
                            @if ($domain == 'امنیت اطلاعات و مدیریت ریسک') #dc3545, #c82333
                            @elseif ($domain == 'تحول دیجیتال') #fd7e14, #e06b12
                            @elseif ($domain == 'خدمات پشتیبانی') #2F4F4F, #1C2526
                            @elseif ($domain == 'حاکمیت فناوری اطلاعات') #007bff, #0056b3
                            @elseif ($domain == 'زیرساخت فناوری') #6f42c1, #5a32a3
                            @elseif ($domain == 'سامانه‌های کاربردی') #e83e8c, #d63384
                            @else #17a2b8, #138496 @endif
                        ); color: white;">
                            <h2>درصد عملکرد حوزه: {{ $domain }}</h2>
                        </div>
                        <div class="card-body">
                            <div class="subcategory-chart-container">
                                <canvas class="subcategory-chart" data-domain="{{ $domain }}"></canvas>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @else
            <div class="card">
                <div class="card-body text-center">
                    <p class="error-message">هنوز هیچ ارزیابی تکمیل‌شده‌ای وجود ندارد. لطفاً ابتدا یک ارزیابی را تکمیل کنید.</p>
                    <a href="{{ route('assessment.domains') }}" class="btn btn-primary">شروع ارزیابی جدید</a>
                </div>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            console.log('Chart.js script is running...');

            if (typeof ChartDataLabels !== 'undefined') {
                Chart.register(ChartDataLabels);
                console.log('ChartDataLabels plugin registered successfully');
            } else {
                console.error('ChartDataLabels plugin failed to load');
            }

            @if($assessment_group_id && $completedGroups->isNotEmpty())
                // داده‌ها از کنترلر
                const labels = @json($labels);
                const dataValues = @json($dataValues);
                const maturityData = @json($maturityData['levelAverages']);
                const maturityLabels = ['تلاش فردی', 'قدم‌های ساخت‌یافته', 'رویکرد سازمان‌یافته', 'تصمیم‌گیری مبتنی بر داده', 'نوآور و پیشرو'];
                const subcategories = @json($subcategories);
                const highRisks = @json($highRisks);
                const mediumRisks = @json($mediumRisks);
                const lowRisks = @json($lowRisks);

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

                // نمودار راداری
                const radarCtx = document.getElementById('performanceChartRadar');
                if (radarCtx) {
                    new Chart(radarCtx, {
                        type: 'radar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'درصد عملکرد',
                                data: dataValues,
                                backgroundColor: 'rgba(30, 144, 255, 0.2)',
                                borderColor: '#1E90FF',
                                borderWidth: 2,
                                pointBackgroundColor: '#1E90FF',
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: '#1E90FF'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                r: {
                                    angleLines: { display: true },
                                    grid: { color: 'rgba(0, 0, 0, 0.1)', lineWidth: 1 },
                                    ticks: { beginAtZero: true, max: 100, stepSize: 20, callback: v => v + '%', font: { size: 12, family: 'Vazir' } },
                                    pointLabels: { font: { size: 14, family: 'Vazir' } }
                                }
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: { enabled: true, callbacks: { label: ctx => ctx.parsed.r + '%' }, bodyFont: { family: 'Vazir' } },
                                datalabels: { display: false }
                            },
                            layout: { padding: 20 }
                        }
                    });
                    console.log('Radar Chart initialized');
                } else {
                    console.error('Canvas element with ID "performanceChartRadar" not found');
                }

                // نمودار میله‌ای عمودی
                const barCtx = document.getElementById('performanceChartBar');
                if (barCtx) {
                    new Chart(barCtx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'درصد عملکرد',
                                data: dataValues,
                                backgroundColor: '#20B2AA',
                                borderColor: '#008B8B',
                                borderWidth: 1,
                                borderRadius: 5,
                                hoverBackgroundColor: '#008B8B',
                                hoverBorderColor: '#008B8B'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: { ticks: { font: { size: 12, family: 'Vazir', weight: 'bold' }, maxRotation: 45, minRotation: 45 }, grid: { display: false } },
                                y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%', font: { size: 12, family: 'Vazir' } }, grid: { color: 'rgba(0, 0, 0, 0.1)' } }
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: { enabled: true, callbacks: { label: ctx => ctx.parsed.y + '%' }, bodyFont: { family: 'Vazir' } },
                                datalabels: { anchor: 'end', align: 'top', offset: 5, formatter: v => Math.round(v) + '%', font: { size: 12, family: 'Vazir', weight: 'bold' }, color: '#333' }
                            },
                            layout: { padding: { top: 30 } }
                        }
                    });
                    console.log('Bar Chart initialized');
                } else {
                    console.error('Canvas element with ID "performanceChartBar" not found');
                }

                // نمودار میله‌ای برای سطح بلوغ
                const maturityCtx = document.getElementById('maturityChartBar');
                if (maturityCtx) {
                    new Chart(maturityCtx, {
                        type: 'bar',
                        data: {
                            labels: maturityLabels,
                            datasets: [{
                                label: 'میانگین امتیاز سطح بلوغ',
                                data: maturityData,
                                backgroundColor: '#87CEEB',
                                borderColor: '#4682B4',
                                borderWidth: 1,
                                borderRadius: 5,
                                hoverBackgroundColor: '#4682B4',
                                hoverBorderColor: '#4682B4'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: { ticks: { font: { size: 12, family: 'Vazir', weight: 'bold' } }, grid: { display: false } },
                                y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%', font: { size: 12, family: 'Vazir' } }, grid: { color: 'rgba(0, 0, 0, 0.1)' } }
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: { enabled: true, callbacks: { label: ctx => ctx.parsed.y + '%' }, bodyFont: { family: 'Vazir' } },
                                datalabels: { anchor: 'end', align: 'top', offset: 5, formatter: v => Math.round(v) + '%', font: { size: 12, family: 'Vazir', weight: 'bold' }, color: '#333' }
                            },
                            layout: { padding: { top: 30 } }
                        }
                    });
                    console.log('Maturity Chart initialized');
                } else {
                    console.error('Canvas element with ID "maturityChartBar" not found');
                }

                // نمودارهای میله‌ای عمودی برای زیرمجموعه‌ها
                const subcategoryCharts = document.querySelectorAll('.subcategory-chart');
                subcategoryCharts.forEach(canvas => {
                    const domain = canvas.getAttribute('data-domain');
                    const subs = subcategories[domain] || [];
                    if (subs.length === 0) {
                        canvas.parentElement.style.display = 'none';
                        const header = canvas.closest('.card').querySelector('.card-header');
                        if (header) header.style.display = 'none';
                        return;
                    }
                    // تجمیع زیرمجموعه‌های مشابه
                    const aggregatedSubs = aggregateSubcategories(subs);
                    const subLabels = aggregatedSubs.map(item => item.name);
                    const subData = aggregatedSubs.map(item => item.performance);
                    let backgroundColor, borderColor, hoverBackgroundColor, hoverBorderColor;
                    switch (domain) {
                        case 'امنیت اطلاعات و مدیریت ریسک':
                            backgroundColor = '#dc3545';
                            borderColor = '#c82333';
                            hoverBackgroundColor = '#c82333';
                            hoverBorderColor = '#c82333';
                            break;
                        case 'تحول دیجیتال':
                            backgroundColor = '#fd7e14';
                            borderColor = '#e06b12';
                            hoverBackgroundColor = '#e06b12';
                            hoverBorderColor = '#e06b12';
                            break;
                        case 'خدمات پشتیبانی':
                            backgroundColor = '#2F4F4F';
                            borderColor = '#1C2526';
                            hoverBackgroundColor = '#1C2526';
                            hoverBorderColor = '#1C2526';
                            break;
                        case 'حاکمیت فناوری اطلاعات':
                            backgroundColor = '#007bff';
                            borderColor = '#0056b3';
                            hoverBackgroundColor = '#0056b3';
                            hoverBorderColor = '#0056b3';
                            break;
                        case 'زیرساخت فناوری':
                            backgroundColor = '#6f42c1';
                            borderColor = '#5a32a3';
                            hoverBackgroundColor = '#5a32a3';
                            hoverBorderColor = '#5a32a3';
                            break;
                        case 'سامانه‌های کاربردی':
                            backgroundColor = '#e83e8c';
                            borderColor = '#d63384';
                            hoverBackgroundColor = '#d63384';
                            hoverBorderColor = '#d63384';
                            break;
                        default:
                            backgroundColor = '#17a2b8';
                            borderColor = '#138496';
                            hoverBackgroundColor = '#138496';
                            hoverBorderColor = '#138496';
                    }
                    new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels: subLabels,
                            datasets: [{
                                label: 'درصد عملکرد',
                                data: subData,
                                backgroundColor: backgroundColor,
                                borderColor: borderColor,
                                borderWidth: 1,
                                borderRadius: 5,
                                hoverBackgroundColor: hoverBackgroundColor,
                                hoverBorderColor: hoverBorderColor
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: { ticks: { font: { size: 12, family: 'Vazir', weight: 'bold' }, maxRotation: 45, minRotation: 45 }, grid: { display: false } },
                                y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%', font: { size: 12, family: 'Vazir' } }, grid: { color: 'rgba(0, 0, 0, 0.1)' } }
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: { enabled: true, callbacks: { label: ctx => ctx.parsed.y + '%' }, bodyFont: { family: 'Vazir' } },
                                datalabels: { anchor: 'end', align: 'top', offset: 5, formatter: v => Math.round(v) + '%', font: { size: 12, family: 'Vazir', weight: 'bold' }, color: '#333' }
                            },
                            layout: { padding: { top: 30 } }
                        }
                    });
                    console.log(`Subcategory Chart for ${domain} initialized`);
                });

                // نمودار دایره‌ای برای توزیع ریسک‌ها
                const riskCtx = document.getElementById('riskDistributionChart');
                if (riskCtx) {
                    const highRiskCount = {{ count(array_filter($highRisks, fn($item) => !empty($item['content']))) }};
                    const mediumRiskCount = {{ count(array_filter($mediumRisks, fn($item) => !empty($item['content']))) }};
                    const lowRiskCount = {{ count(array_filter($lowRisks, fn($item) => !empty($item['content']))) }};
                    const totalRisks = highRiskCount + mediumRiskCount + lowRiskCount;
                    if (totalRisks === 0) {
                        riskCtx.parentElement.style.display = 'none';
                        const header = riskCtx.closest('.card').querySelector('.card-header');
                        if (header) header.style.display = 'none';
                    } else {
                        new Chart(riskCtx, {
                            type: 'pie',
                            data: {
                                labels: ['ریسک با شدت بالا', 'ریسک با شدت متوسط', 'ریسک با شدت پایین'],
                                datasets: [{
                                    data: [highRiskCount, mediumRiskCount, lowRiskCount],
                                    backgroundColor: ['#dc3545', '#fd7e14', '#ffc107'],
                                    borderColor: ['#dc3545', '#fd7e14', '#ffc107'],
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { position: 'bottom', labels: { font: { size: 14, family: 'Vazir' } } },
                                    tooltip: { enabled: true, callbacks: { label: ctx => { const v = ctx.parsed; const t = ctx.dataset.data.reduce((a, b) => a + b, 0); return `${ctx.label}: ${v} (${((v / t) * 100).toFixed(1)}%)`; } }, bodyFont: { family: 'Vazir' } },
                                    datalabels: { formatter: (v, ctx) => { const t = ctx.dataset.data.reduce((a, b) => a + b, 0); return `${((v / t) * 100).toFixed(1)}%`; }, color: '#fff', font: { size: 14, family: 'Vazir', weight: 'bold' } }
                                }
                            }
                        });
                        console.log('Risk Distribution Chart initialized');
                    }
                } else {
                    console.error('Canvas element with ID "riskDistributionChart" not found');
                }
            @endif
        });
    </script>
</body>
</html>
