<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">    
    <title>گزارش مقایسه‌ای فناوری اطلاعات شرکت‌های زیرمجموعه</title>
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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .header-container {
            text-align: center;
            margin-bottom: 40px;
        }
        .header-container img {
            max-width: 200px;
            height: auto;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            transition: transform 0.3s ease;
        }
        .header-container img:hover {
            transform: scale(1.05);
        }
        .report-title-bar {
            background: linear-gradient(90deg, #3498db, #2980b9);
            padding: 15px 30px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .report-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #fff;
            text-align: center;
            margin: 0;
        }
        .card {
            background: #ffffff;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-3px);
        }
        .card-header {
            text-align: center;
            background: linear-gradient(90deg, #3498db, #2980b9);
            color: white;
            padding: 10px;
            border-radius: 8px 8px 0 0;
        }
        .chart-container {
            margin: 30px 0;
            height: 400px !important;
            width: 100% !important;
            position: relative;
        }
        #performanceChartBar, #maturityChartBar, #finalScoreChartBar, #overallMaturityChartBar {
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
            font-weight: bold;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 30px;
        }
        .summary-table th, .summary-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
            transition: background 0.3s ease;
        }
        .summary-table th {
            background: linear-gradient(90deg, #3498db, #2980b9);
            color: #fff;
        }
        .summary-table td:hover {
            background: #f9f9f9;
        }
        .tooltip {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }
        .tooltip .tooltiptext {
            visibility: hidden;
            width: 200px;
            background-color: #555;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            margin-left: -100px;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
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
                گزارش مقایسه‌ای شرکت‌های:
                <span class="highlight" title="این اطلاعات از پروفایل شما بارگذاری شده است">
                    {{ implode('، ', $comparisonData['company_names']) }}
                </span>
            </h1>
        </div>

        @if(count($comparisonData['company_names']) < 2)
            <div class="card">
                <div class="card-body text-center">
                    <p class="error-message">لطفاً حداقل دو شرکت را برای مقایسه انتخاب کنید.</p>
                    <a href="{{ route('profile') }}" class="btn btn-primary">بازگشت به پروفایل</a>
                </div>
            @else
                <!-- جدول خلاصه عملکرد شرکت‌ها -->
                <div class="card">
                    <div class="card-header">
                        <h2>خلاصه عملکرد شرکت‌های زیرمجموعه</h2>
                    </div>
                    <div class="card-body">
                        @if(empty($comparisonData['company_names']))
                            <p class="error-message">هیچ داده‌ای برای مقایسه شرکت‌ها یافت نشد. لطفاً مطمئن شوید که حداقل دو شرکت ارزیابی تکمیل‌شده دارند.</p>
                        @else
                            <table class="summary-table">
                                <thead>
                                    <tr>
                                        <th>نام شرکت</th>
                                        <th>امتیاز آخرین ارزیابی (از ۱۰۰)</th>
                                        <th>سطح بلوغ کلی</th>
                                        <th>تاریخ آخرین ارزیابی</th>
                                        <th>جزئیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($comparisonData['company_names'] as $index => $companyName)
                                        <tr>
                                            <td>{{ $companyName }}</td>
                                            <td>
                                                @if(isset($comparisonData['final_scores'][$index]) && is_numeric($comparisonData['final_scores'][$index]))
                                                    {{ round($comparisonData['final_scores'][$index], 2) }}
                                                @else
                                                    <span style="color: red;">بدون داده ( {{ $companyName }})</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
    $md = $comparisonData['maturity_data'][$index] ?? null;
    $overall = $md['overallMaturityLevel'] ?? ($md['overall_maturity_level'] ?? null);
    $levels = [1=>'سطح یک',2=>'سطح دو',3=>'سطح سه',4=>'سطح چهار',5=>'سطح پنج'];
@endphp
@if($overall)
    {{ $levels[$overall] ?? 'نامشخص' }}
@else
    <span style="color: red;">نامشخص</span>
@endif

                                            </td>
                                            <td>
                                                @if(isset($comparisonData['assessment_dates'][$index]))
                                                    {{ $comparisonData['assessment_dates'][$index] }}
                                                @else
                                                    نامشخص
                                                @endif
                                            </td>
                                            <td>
                                                @if(isset($comparisonData['assessment_group_ids'][$index]))
                                                    <a href="{{ route('assessment.group.report', ['assessment_group_id' => $comparisonData['assessment_group_ids'][$index]]) }}" class="btn btn-sm btn-info">مشاهده جزئیات</a>
                                                @else
                                                    <span style="color: red;">نامشخص</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>


                <!-- نمودار میله‌ای برای امتیاز نهایی -->
                <div class="card">
                    <div class="card-header">
                        <h2>مقایسه امتیاز نهایی ارزیابی</h2>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="finalScoreChartBar"></canvas>
                        </div>
                    </div>
                </div>


                <!-- نمودار جدید: مقایسه سطح بلوغ کلی -->
                <div class="card">
                    <div class="card-header">
                        <h2>مقایسه سطح بلوغ کلی شرکت‌ها</h2>
                    </div>
                    <div class="card-body">
                    @if(empty($comparisonData['maturity_data']))
    <p class="error-message">داده‌های سطح بلوغ برای نمایش نمودار در دسترس نیست. لطفاً مطمئن شوید که ارزیابی‌های شرکت‌ها کامل شده‌اند.</p>
@else
    <div class="chart-container">
        <canvas id="overallMaturityChartBar"></canvas>
    </div>
@endif
                    </div>
                </div>

                <!-- نمودار میله‌ای برای درصد عملکرد -->
                <div class="card">
                    <div class="card-header">
                        <h2>مقایسه درصد عملکرد فناوری اطلاعات</h2>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="performanceChartBar"></canvas>
                        </div>
                    </div>
                </div>



                <!-- نمودار میله‌ای برای سطح بلوغ -->
                <div class="card">
                    <div class="card-header">
                        <h2>مقایسه توزیع سطح بلوغ</h2>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 400px !important;">
                            <canvas id="maturityChartBar"></canvas>
                        </div>
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

            @if(count($comparisonData['company_names']) >= 1)
                // داده‌ها از کنترلر
                const labels = @json($labels);
                const comparisonData = @json($comparisonData);
                const companyNames = comparisonData.company_names;
                const dataValues = comparisonData.data_values;
                const maturityData = comparisonData.maturity_data;
                const finalScores = comparisonData.final_scores;

                // دیباگ داده‌ها
                console.log('Comparison Data:', comparisonData);
                console.log('Maturity Data:', maturityData);
                console.log('Maturity Levels:', maturityData.map(item => item?.overallMaturityLevel));

                // بررسی وجود overallMaturityLevel
               const hasMaturityLevels = maturityData && Array.isArray(maturityData) && maturityData.every(item => {
    const level = item?.overallMaturityLevel ?? item?.overall_maturity_level;
    return item && typeof level === 'number' && level >= 1 && level <= 5;
});
                console.log('Has Valid Maturity Levels:', hasMaturityLevels);

                // رنگ‌های متمایز برای هر شرکت
                const companyColors = [
                    { bg: 'rgba(52, 152, 219, 0.2)', border: '#3498db' },
                    { bg: 'rgba(46, 204, 113, 0.2)', border: '#2ecc71' },
                    { bg: 'rgba(231, 76, 60, 0.2)', border: '#e74c3c' },
                    { bg: 'rgba(155, 89, 182, 0.2)', border: '#9b59b6' },
                    { bg: 'rgba(241, 196, 15, 0.2)', border: '#f1c40f' }
                ];




                // نمودار میله‌ای عمودی برای درصد عملکرد
                const barCtx = document.getElementById('performanceChartBar');
                if (barCtx) {
                    new Chart(barCtx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: companyNames.map((name, index) => ({
                                label: name,
                                data: dataValues[index] || [],
                                backgroundColor: companyColors[index % companyColors.length].border,
                                borderColor: companyColors[index % companyColors.length].border,
                                borderWidth: 1,
                                borderRadius: 5,
                                hoverBackgroundColor: companyColors[index % companyColors.length].border,
                                hoverBorderColor: companyColors[index % companyColors.length].border
                            }))
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: { ticks: { font: { size: 12, family: 'Vazir', weight: 'bold' }, maxRotation: 45, minRotation: 45 }, grid: { display: false } },
                                y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%', font: { size: 12, family: 'Vazir' } }, grid: { color: 'rgba(0, 0, 0, 0.1)' } }
                            },
                            plugins: {
                                legend: { position: 'top', labels: { font: { size: 14, family: 'Vazir' } } },
                                tooltip: { enabled: true, callbacks: { label: ctx => ctx.parsed.y + '%' }, bodyFont: { family: 'Vazir' } },
                                datalabels: { display: false } // صراحتاً غیرفعال کردن datalabels
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
                            labels: ['تلاش فردی', 'قدم‌های ساخت‌یافته', 'رویکرد سازمان‌یافته', 'تصمیم‌گیری مبتنی بر داده', 'نوآور و پیشرو'],
                            datasets: companyNames.map((name, index) => ({
                                label: name,
                                data: maturityData[index]?.level_averages || [],
                                backgroundColor: companyColors[index % companyColors.length].border,
                                borderColor: companyColors[index % companyColors.length].border,
                                borderWidth: 1,
                                borderRadius: 5,
                                hoverBackgroundColor: companyColors[index % companyColors.length].border,
                                hoverBorderColor: companyColors[index % companyColors.length].border
                            }))
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: { ticks: { font: { size: 12, family: 'Vazir', weight: 'bold' } }, grid: { display: false } },
                                y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%', font: { size: 12, family: 'Vazir' } }, grid: { color: 'rgba(0, 0, 0, 0.1)' } }
                            },
                            plugins: {
                                legend: { position: 'top', labels: { font: { size: 14, family: 'Vazir' } } },
                                tooltip: { enabled: true, callbacks: { label: ctx => ctx.parsed.y + '%' }, bodyFont: { family: 'Vazir' } },
                               datalabels: { display: false } // صراحتاً غیرفعال کردن datalabels
                            },
                            layout: { padding: { top: 30 } }
                        }
                    });
                    console.log('Maturity Chart initialized');
                } else {
                    console.error('Canvas element with ID "maturityChartBar" not found');
                }





   // مرتب‌سازی امتیازات نهایی
            const pairedData = companyNames.map((name, index) => ({
                name: name,
                score: finalScores[index] || 0,
                color: companyColors[index % companyColors.length]
            }));

            // مرتب‌سازی نزولی بر اساس امتیاز
            pairedData.sort((a, b) => b.score - a.score);

            // استخراج داده‌های مرتب‌شده
            const sortedCompanyNames = pairedData.map(item => item.name);
            const sortedFinalScores = pairedData.map(item => item.score);
            const sortedColors = pairedData.map(item => item.color.border);

            // نمودار میله‌ای برای امتیاز نهایی
            const finalScoreCtx = document.getElementById('finalScoreChartBar');
            if (finalScoreCtx) {
                new Chart(finalScoreCtx, {
                    type: 'bar',
                    data: {
                        labels: sortedCompanyNames,
                        datasets: [{
                            label: 'امتیاز نهایی',
                            data: sortedFinalScores,
                            backgroundColor: sortedColors,
                            borderColor: sortedColors,
                            borderWidth: 1,
                            borderRadius: 5,
                            hoverBackgroundColor: sortedColors,
                            hoverBorderColor: sortedColors
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
                            legend: { position: 'top', labels: { font: { size: 14, family: 'Vazir' } } },
                            tooltip: { enabled: true, callbacks: { label: ctx => ctx.parsed.y + '%' }, bodyFont: { family: 'Vazir' } },
                            datalabels: { anchor: 'end', align: 'top', offset: 5, formatter: v => Math.round(v) + '%', font: { size: 12, family: 'Vazir', weight: 'bold' }, color: '#333' }
                        },
                        layout: { padding: { top: 30 } }
                    }
                });
                console.log('Final Score Chart initialized');
            } else {
                console.error('Canvas element with ID "finalScoreChartBar" not found');
            }








    // مرتب‌سازی سطح بلوغ کلی
            const pairedDataMaturity = companyNames.map((name, index) => ({
                name: name,
                level: maturityData[index]?.overallMaturityLevel ?? maturityData[index]?.overall_maturity_level ?? 0,
                color: companyColors[index % companyColors.length]
            }));
            pairedDataMaturity.sort((a, b) => b.level - a.level);
            const sortedCompanyNamesMaturity = pairedDataMaturity.map(item => item.name);
            const sortedMaturityLevels = pairedDataMaturity.map(item => item.level);
            const sortedColorsMaturity = pairedDataMaturity.map(item => item.color.border);

            // نمودار جدید میله‌ای برای سطح بلوغ کلی
            const overallMaturityCtx = document.getElementById('overallMaturityChartBar');
            if (overallMaturityCtx) {
                new Chart(overallMaturityCtx, {
                    type: 'bar',
                    data: {
                        labels: sortedCompanyNamesMaturity,
                        datasets: [{
                            label: 'سطح بلوغ کلی',
                            data: sortedMaturityLevels,
                            backgroundColor: sortedColorsMaturity,
                            borderColor: sortedColorsMaturity,
                            borderWidth: 1,
                            borderRadius: 5,
                            hoverBackgroundColor: sortedColorsMaturity,
                            hoverBorderColor: sortedColorsMaturity
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: { ticks: { font: { size: 12, family: 'Vazir', weight: 'bold' }, maxRotation: 45, minRotation: 45 }, grid: { display: false } },
                            y: {
                                beginAtZero: true,
                                max: 5,
                                ticks: {
                                    stepSize: 1,
                                    callback: v => {
                                        const levels = {
                                            0: 'نامشخص',
                                            1: 'سطح یک',
                                            2: 'سطح دو',
                                            3: 'سطح سه',
                                            4: 'سطح چهار',
                                            5: 'سطح پنج'
                                        };
                                        return levels[v] || v;
                                    },
                                    font: { size: 12, family: 'Vazir' }
                                },
                                grid: { color: 'rgba(0, 0, 0, 0.1)' }
                            }
                        },
                        plugins: {
                            legend: { position: 'top', labels: { font: { size: 14, family: 'Vazir' } } },
                            tooltip: {
                                enabled: true,
                                callbacks: {
                                    label: ctx => {
                                        const levels = {
                                            0: 'نامشخص',
                                            1: 'سطح یک',
                                            2: 'سطح دو',
                                            3: 'سطح سه',
                                            4: 'سطح چهار',
                                            5: 'سطح پنج'
                                        };
                                        return levels[ctx.parsed.y] || ctx.parsed.y;
                                    }
                                },
                                bodyFont: { family: 'Vazir' }
                            },
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                offset: 5,
                                formatter: v => {
                                    const levels = {
                                        0: 'نامشخص',
                                        1: 'سطح یک',
                                        2: 'سطح دو',
                                        3: 'سطح سه',
                                        4: 'سطح چهار',
                                        5: 'سطح پنج'
                                    };
                                    return levels[v] || v;
                                },
                                font: { size: 12, family: 'Vazir', weight: 'bold' },
                                color: '#333'
                            }
                        },
                        layout: { padding: { top: 30 } }
                    }
                });
                console.log('Overall Maturity Chart initialized');
            } else {
                console.error('Canvas element with ID "overallMaturityChartBar" not found');
            }
        @endif
    });
    </script>
</body>
</html>