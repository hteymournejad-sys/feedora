<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <title>پیش‌بینی روند ارزیابی‌ها</title>

    <!-- فونت Vazir -->
    <link href="https://cdn.jsdelivr.net/npm/vazir-font@30.1.0/dist/font-face.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

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
            background: linear-gradient(90deg, #20B2AA, #008B8B);
            color: white;
            padding: 12px;
            border-radius: 8px 8px 0 0;
            margin: -20px -20px 20px -20px;
            text-align: center;
            font-weight: bold;
        }
        .chart-box {
            height: 420px;
            position: relative;
            margin: 20px 0;
        }
        .legend-tip {
            font-size: 0.9rem;
            color: #666;
            margin-top: 10px;
            text-align: center;
            font-style: italic;
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
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }
        .profile-btn:hover {
            background: linear-gradient(90deg, #2980b9, #3498db);
            transform: scale(1.05);
            box-shadow: 0 6px 16px rgba(52, 152, 219, 0.4);
        }
    </style>
</head>
<body>

<a href="{{ route('profile') }}" class="profile-btn">پروفایل کاربر</a>

<div class="report-wrapper">
    <div class="header-container">
        <img src="{{ asset('images/Logo.png') }}" alt="Feedora Logo">
    </div>

    <!-- تیتر: پیش‌بینی روند ارزیابی شرکت (نام شرکت) -->
    <div class="report-title-bar">
        <h1 class="report-title">
            پیش‌بینی روند ارزیابی‌ شرکت
            <span class="highlight">
                {{ $company_name ?? 'نام شرکت مشخص نشده' }}
            </span>
        </h1>
    </div>

    <!-- نمودار میانگین کل امتیاز -->
    <div class="card">
        <div class="card-header">
            <h2>میانگین کل امتیاز هر ارزیابی (تمام دوره‌ها) + پیش‌بینی</h2>
        </div>
        <div class="chart-box">
            <canvas id="overallScoreChart"></canvas>
        </div>
        <div class="legend-tip">
            سه میله‌ی آخر با برچسب «+2Y», «+3Y», «+5Y» مقادیر پیش‌بینی هستند.
        </div>
    </div>

    <!-- نمودار سطح بلوغ -->
    <div class="card">
        <div class="card-header">
            <h2>سطح بلوغ هر ارزیابی (تمام دوره‌ها) + پیش‌بینی</h2>
        </div>
        <div class="chart-box">
            <canvas id="maturityChart"></canvas>
        </div>
        <div class="legend-tip">
            سه میله‌ی آخر با برچسب «+2Y», «+3Y», «+5Y» مقادیر پیش‌بینی هستند.
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const scoreLabels       = @json($scoreLabels ?? []);
    const scoreValues       = @json($scoreValues ?? []);
    const scoreForecastLbl  = @json($scoreForecastLbl ?? ['+2Y','+3Y','+5Y']);
    const scoreForecastVal  = @json($scoreForecastVal ?? [null,null,null]);

    const maturityLabels      = @json($maturityLabels ?? []);
    const maturityValues      = @json($maturityValues ?? []);
    const maturityForecastLbl = @json($maturityForecastLbl ?? ['+2Y','+3Y','+5Y']);
    const maturityForecastVal = @json($maturityForecastVal ?? [null,null,null]);

    const histColor  = '#20B2AA';
    const histBorder = '#008B8B';
    const fcColor    = '#95a5a6';
    const fcBorder   = '#7f8c8d';

    // 1) میانگین کل امتیاز
    const overallCtx = document.getElementById('overallScoreChart');
    if (overallCtx && scoreLabels.length > 0) {
        new Chart(overallCtx, {
            type: 'bar',
            data: {
                labels: [...scoreLabels, ...scoreForecastLbl],
                datasets: [{
                    label: 'میانگین کل (0-100)',
                    data: [...scoreValues, ...scoreForecastVal],
                    backgroundColor: [
                        ...Array(scoreValues.length).fill(histColor),
                        ...Array(scoreForecastVal.length).fill(fcColor),
                    ],
                    borderColor: [
                        ...Array(scoreValues.length).fill(histBorder),
                        ...Array(scoreForecastVal.length).fill(fcBorder),
                    ],
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } },
                    x: { ticks: { maxRotation: 45, minRotation: 0, font: { family: 'Vazir' } } }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => (ctx.parsed.y ?? 0) + '%' }, bodyFont: { family: 'Vazir' } }
                }
            }
        });
    }

    // 2) سطح بلوغ
    const maturityCtx = document.getElementById('maturityChart');
    if (maturityCtx && maturityLabels.length > 0) {
        new Chart(maturityCtx, {
            type: 'bar',
            data: {
                labels: [...maturityLabels, ...maturityForecastLbl],
                datasets: [{
                    label: 'سطح بلوغ (0-100)',
                    data: [...maturityValues, ...maturityForecastVal],
                    backgroundColor: [
                        ...Array(maturityValues.length).fill(histColor),
                        ...Array(maturityForecastVal.length).fill(fcColor),
                    ],
                    borderColor: [
                        ...Array(maturityValues.length).fill(histBorder),
                        ...Array(maturityForecastVal.length).fill(fcBorder),
                    ],
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } },
                    x: { ticks: { maxRotation: 45, minRotation: 0, font: { family: 'Vazir' } } }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => (ctx.parsed.y ?? 0) + '%' }, bodyFont: { family: 'Vazir' } }
                }
            }
        });
    }
});
</script>

</body>
</html>