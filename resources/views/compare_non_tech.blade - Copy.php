<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <title>مقایسه غیر فنی شرکت‌ها</title>
    <!-- فونت Vazir -->
    <link href="https://cdn.jsdelivr.net/npm/vazir-font@30.1.0/dist/font-face.css" rel="stylesheet" onerror="console.error('فونت لود نشد، از فونت پیش‌فرض استفاده می‌شود');">
    <!-- بوت‌استرپ CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <!-- Chart.js و پلاگین DataLabels -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
    <!-- فونت‌آوسام برای آیکون‌ها -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- فاوآیکن فیدورا -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
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
        .report-wrapper {
            max-width: 1200px;
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
            max-width: 200px;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
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
            margin-bottom: 30px;
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
            text-align: center;
            padding: 15px;
            border-radius: 8px 8px 0 0;
        }
        .chart-container {
            margin: 50px 0;
            height: 400px !important;
            width: 100% !important;
            position: relative;
        }
        .chart-container canvas {
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
            font-size: 1.2rem;
        }
        .chart-divider {
            border: 0;
            height: 1px;
            background: rgba(0, 0, 0, 0.1);
            margin: 30px 0;
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
            <h1 class="report-title">مقایسه غیر فنی شرکت‌های انتخاب‌شده</h1>
        </div>

        <!-- نمودارهای تحلیلی -->
        <div class="card">
            <div class="card-header">
                <h3>نمودارهای تحلیلی مقایسه‌ای</h3>
            </div>
            <div class="card-body">
                <!-- Bar Chart: مقایسه بودجه و هزینه واقعی IT -->
                <div class="chart-container">
                    <h4>مقایسه بودجه و هزینه واقعی IT</h4>
                    <canvas id="budgetCostBarChart"></canvas>
                </div>
                <hr class="chart-divider">

                <!-- Bar Chart: مقایسه تعداد پرسنل IT -->
                <div class="chart-container">
                    <h4>مقایسه تعداد پرسنل IT</h4>
                    <canvas id="itStaffBarChart"></canvas>
                </div>
                <hr class="chart-divider">

                
                <!-- Bar Chart: فراوانی پرسنل IT بر اساس تخصص در کل شرکت‌ها -->
                <div class="chart-container">
                    <h4>فراوانی پرسنل IT بر اساس تخصص در کل شرکت‌ها</h4>
                    <canvas id="allSpecialtiesBarChart"></canvas>
                </div>
                <hr class="chart-divider">

                <!-- Bar Chart: فراوانی پرسنل IT بر اساس تحصیلات در کل شرکت‌ها -->
                <div class="chart-container">
                    <h4>فراوانی پرسنل IT بر اساس تحصیلات در کل شرکت‌ها</h4>
                    <canvas id="allEducationBarChart"></canvas>
                </div>
                <hr class="chart-divider">

                <!-- Bar Chart: مقایسه تعداد کاربران فعال -->
                <div class="chart-container">
                    <h4>مقایسه تعداد کاربران فعال شرکت‌ها</h4>
                    <canvas id="activeUsersBarChart"></canvas>
                </div>
<hr class="chart-divider">
<!-- Bar Chart: تناسب تعداد پرسنل IT با کاربران فعال -->
                <div class="chart-container">
                    <h4>تناسب تعداد پرسنل IT با کاربران فعال</h4>
                    <canvas id="itStaffRatioBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Register Chart.js DataLabels plugin
            Chart.register(ChartDataLabels);

            // Aggregate specialties and education data across all companies
            const allSpecialties = {};
            const allEducation = {};
            @foreach($companies as $company)
                @if(!empty($pieData[$company->id]['specialties_labels']))
                    @foreach($pieData[$company->id]['specialties_labels'] as $index => $label)
                        if (!allSpecialties['{{ $label }}']) {
                            allSpecialties['{{ $label }}'] = 0;
                        }
                        allSpecialties['{{ $label }}'] += {{ $pieData[$company->id]['specialties_data'][$index] ?? 0 }};
                    @endforeach
                @endif
                @if(!empty($pieData[$company->id]['education_labels']))
                    @foreach($pieData[$company->id]['education_labels'] as $index => $label)
                        if (!allEducation['{{ $label }}']) {
                            allEducation['{{ $label }}'] = 0;
                        }
                        allEducation['{{ $label }}'] += {{ $pieData[$company->id]['education_data'][$index] ?? 0 }};
                    @endforeach
                @endif
            @endforeach

            const specialtiesLabels = Object.keys(allSpecialties);
            const specialtiesData = Object.values(allSpecialties);
            const educationLabels = Object.keys(allEducation);
            const educationData = Object.values(allEducation);

            // Bar Chart: Budget and Expenditure
        const budgetCostCtx = document.getElementById('budgetCostBarChart');
        if (budgetCostCtx) {
            new Chart(budgetCostCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($companyNames) !!},
                    datasets: [
                        {
                            label: 'بودجه',
                            data: {!! json_encode($budgetCostData['budget']) !!},
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1,
                            borderRadius: 5
                        },
                        {
                            label: 'هزینه',
                            data: {!! json_encode($budgetCostData['expenditure']) !!},
                            backgroundColor: 'rgba(153, 102, 255, 0.2)',
                            borderColor: 'rgba(153, 102, 255, 1)',
                            borderWidth: 1,
                            borderRadius: 5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { ticks: { font: { size: 12, family: 'Vazir' } }, grid: { display: false } },
                        y: { beginAtZero: true, ticks: { font: { size: 12, family: 'Vazir' } } }
                    },
                    plugins: {
                        legend: { position: 'top', labels: { font: { size: 14, family: 'Vazir' } } },
                        tooltip: { bodyFont: { family: 'Vazir' } },
                            datalabels: { display: false }
                    }
                }
            });
        } else {
            console.error('Canvas element with ID "budgetCostBarChart" not found');
        }

            // Bar Chart: IT Staff
            const itStaffCtx = document.getElementById('itStaffBarChart');
            if (itStaffCtx) {
                new Chart(itStaffCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($companyNames) !!},
                        datasets: [
                            {
                                label: 'تمام‌وقت',
                                data: {!! json_encode($itStaffData['full_time']) !!},
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1,
                                borderRadius: 5
                            },
                            {
                                label: 'پاره‌وقت',
                                data: {!! json_encode($itStaffData['part_time']) !!},
                                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                                borderColor: 'rgba(153, 102, 255, 1)',
                                borderWidth: 1,
                                borderRadius: 5
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: { ticks: { font: { size: 12, family: 'Vazir' } }, grid: { display: false } },
                            y: { beginAtZero: true, ticks: { font: { size: 12, family: 'Vazir' } } }
                        },
                        plugins: {
                            legend: { position: 'top', labels: { font: { size: 14, family: 'Vazir' } } },
                            tooltip: { bodyFont: { family: 'Vazir' } },
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                formatter: (value) => Math.round(value),
                                font: { size: 12, family: 'Vazir', weight: 'bold' },
                                color: '#333'
                            }
                        }
                    }
                });
            } else {
                console.error('Canvas element with ID "itStaffBarChart" not found');
            }

            // Bar Chart: IT Staff to User Ratio
            const itStaffRatioCtx = document.getElementById('itStaffRatioBarChart');
            if (itStaffRatioCtx) {
                const ratioData = @json($companies->map(fn($c) => $nonTechData[$c->id]->user_to_it_ratio ?? 0));
                const backgroundColors = ratioData.map(value => value <= 50 ? 'rgba(75, 192, 192, 0.2)' : 'rgba(153, 102, 255, 0.2)');
                const borderColors = ratioData.map(value => value <= 50 ? 'rgba(75, 192, 192, 1)' : 'rgba(153, 102, 255, 1)');

                new Chart(itStaffRatioCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($companyNames) !!},
                        datasets: [{
                            label: 'کاربران به ازای هر پرسنل IT',
                            data: ratioData,
                            backgroundColor: backgroundColors,
                            borderColor: borderColors,
                            borderWidth: 1,
                            borderRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: { ticks: { font: { size: 12, family: 'Vazir' } }, grid: { display: false } },
                            y: {
                                beginAtZero: true,
                                ticks: { font: { size: 12, family: 'Vazir' } },
                                grid: { color: 'rgba(0, 0, 0, 0.1)' }
                            }
                        },
                        plugins: {
                            legend: { position: 'top', labels: { font: { size: 14, family: 'Vazir' } } },
                            tooltip: { bodyFont: { family: 'Vazir' }, callbacks: { label: function(ctx) { return ctx.parsed.y + ' کاربر/نفر IT'; } } },
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                formatter: (value) => Math.round(value),
                                font: { size: 12, family: 'Vazir', weight: 'bold' },
                                color: '#333'
                            },
                            annotation: {
                                annotations: [{
                                    type: 'line',
                                    yMin: 50,
                                    yMax: 50,
                                    borderColor: '#000',
                                    borderWidth: 2,
                                    borderDash: [5, 5],
                                    label: {
                                        content: 'حد مطلوب (50)',
                                        enabled: true,
                                        position: 'center',
                                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                                        color: '#fff',
                                        font: { size: 12, family: 'Vazir', weight: 'bold' }
                                    }
                                }]
                            }
                        }
                    }
                });
            } else {
                console.error('Canvas element with ID "itStaffRatioBarChart" not found');
            }

            // Bar Chart: All Specialties
            const allSpecialtiesCtx = document.getElementById('allSpecialtiesBarChart');
            if (allSpecialtiesCtx) {
                new Chart(allSpecialtiesCtx, {
                    type: 'bar',
                    data: {
                        labels: specialtiesLabels,
                        datasets: [{
                            label: 'تعداد پرسنل',
                            data: specialtiesData,
                            backgroundColor: ['rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)', 'rgba(255, 206, 86, 0.2)', 'rgba(75, 192, 192, 0.2)', 'rgba(153, 102, 255, 0.2)', 'rgba(255, 159, 64, 0.2)', 'rgba(201, 203, 207, 0.2)'],
                            borderColor: ['rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)', 'rgba(75, 192, 192, 1)', 'rgba(153, 102, 255, 1)', 'rgba(255, 159, 64, 1)', 'rgba(201, 203, 207, 1)'],
                            borderWidth: 1,
                            borderRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: { ticks: { font: { size: 12, family: 'Vazir' } }, grid: { display: false } },
                            y: { beginAtZero: true, ticks: { font: { size: 12, family: 'Vazir' } } }
                        },
                        plugins: {
                            legend: { position: 'top', labels: { font: { size: 14, family: 'Vazir' } } },
                            tooltip: { bodyFont: { family: 'Vazir' } },
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                formatter: (value) => Math.round(value),
                                font: { size: 12, family: 'Vazir', weight: 'bold' },
                                color: '#333'
                            }
                        }
                    }
                });
            } else {
                console.error('Canvas element with ID "allSpecialtiesBarChart" not found');
            }

            // Bar Chart: All Education
            const allEducationCtx = document.getElementById('allEducationBarChart');
            if (allEducationCtx) {
                new Chart(allEducationCtx, {
                    type: 'bar',
                    data: {
                        labels: educationLabels,
                        datasets: [{
                            label: 'تعداد پرسنل',
                            data: educationData,
                            backgroundColor: ['rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)', 'rgba(255, 206, 86, 0.2)', 'rgba(75, 192, 192, 0.2)', 'rgba(153, 102, 255, 0.2)', 'rgba(255, 159, 64, 0.2)'],
                            borderColor: ['rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)', 'rgba(75, 192, 192, 1)', 'rgba(153, 102, 255, 1)', 'rgba(255, 159, 64, 1)'],
                            borderWidth: 1,
                            borderRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: { ticks: { font: { size: 12, family: 'Vazir' } }, grid: { display: false } },
                            y: { beginAtZero: true, ticks: { font: { size: 12, family: 'Vazir' } } }
                        },
                        plugins: {
                            legend: { position: 'top', labels: { font: { size: 14, family: 'Vazir' } } },
                            tooltip: { bodyFont: { family: 'Vazir' } },
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                formatter: (value) => Math.round(value),
                                font: { size: 12, family: 'Vazir', weight: 'bold' },
                                color: '#333'
                            }
                        }
                    }
                });
            } else {
                console.error('Canvas element with ID "allEducationBarChart" not found');
            }

            // Bar Chart: Active Users
            const activeUsersCtx = document.getElementById('activeUsersBarChart');
            if (activeUsersCtx) {
                new Chart(activeUsersCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($companyNames) !!},
                        datasets: [{
                            label: 'تعداد کاربران فعال',
                            data: @json($companies->map(fn($c) => $nonTechData[$c->id]->active_users ?? 0)),
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1,
                            borderRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: { ticks: { font: { size: 12, family: 'Vazir' } }, grid: { display: false } },
                            y: { beginAtZero: true, ticks: { font: { size: 12, family: 'Vazir' } } }
                        },
                        plugins: {
                            legend: { position: 'top', labels: { font: { size: 14, family: 'Vazir' } } },
                            tooltip: { bodyFont: { family: 'Vazir' } },
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                formatter: (value) => Math.round(value),
                                font: { size: 12, family: 'Vazir', weight: 'bold' },
                                color: '#333'
                            }
                        }
                    }
                });
            } else {
                console.error('Canvas element with ID "activeUsersBarChart" not found');
            }
        });
    </script>
</body>
</html>