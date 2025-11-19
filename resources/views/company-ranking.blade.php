<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <title>رتبه‌بندی شرکت‌ها</title>
    <!-- فونت Vazir -->
    <link href="https://cdn.jsdelivr.net/npm/vazir-font@30.1.0/dist/font-face.css" rel="stylesheet" onerror="this.style.display='none';console.error('فونت لود نشد، از فونت پیش‌فرض استفاده می‌شود');">
    <!-- فاوآیکن فیدورا -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            padding: 10px;
            border-radius: 8px;
        }
        .error-message {
            color: #e74c3c;
            text-align: center;
            margin: 20px 0;
            font-size: 1.1rem;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th, .table td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .table th {
            background-color: #f4f4f4;
            font-weight: bold;
            font-size: 1.1rem;
        }
        .table td {
            font-size: 1rem;
        }
        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .table tr:hover {
            background-color: #e6f3ff;
        }
        .filter-form {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .filter-form .form-select, .filter-form button {
            padding: 10px 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            background: #fff;
            font-family: 'Vazir', sans-serif;
            font-size: 1rem;
        }
        .filter-form .form-select:focus, .filter-form button:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }
        .filter-form button {
            background: linear-gradient(90deg, #007bff, #0056b3);
            color: #fff;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .filter-form button:hover {
            background: linear-gradient(90deg, #0056b3, #007bff);
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
            font-family: 'Vazir', sans-serif;
            font-size: 1rem;
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

        <div class="card">
            <div class="card-header" style="background: linear-gradient(90deg, #007bff, #0056b3); color: white;">
                <h2>جدول رتبه‌بندی</h2>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('profile.ranking') }}" class="filter-form">
                    <div class="col-md-2">
                        <label class="form-label">سطح سلسله‌مراتب</label>
                        <select class="form-select" name="hierarchy_level" id="hierarchy_level" onchange="this.form.submit();">
                            <option value="" {{ !request('hierarchy_level') ? 'selected' : '' }}>همه سطوح</option>
                            <option value="1" {{ request('hierarchy_level') == '1' ? 'selected' : '' }}>مستقیم (سطح 1)</option>
                            <option value="2" {{ request('hierarchy_level') == '2' ? 'selected' : '' }}>غیرمستقیم (سطح 2)</option>
                            <option value="3" {{ request('hierarchy_level') == '3' ? 'selected' : '' }}>غیرمستقیم (سطح 3)</option>
                            <option value="4" {{ request('hierarchy_level') == '4' ? 'selected' : '' }}>غیرمستقیم (سطح 4)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">هلدینگ</label>
                        <select class="form-select" name="holding_id" id="holding_id" onchange="this.form.submit();">
                            <option value="" {{ !request('holding_id') ? 'selected' : '' }}>همه هلدینگ‌ها</option>
                            @foreach($holdings as $holding)
                                <option value="{{ $holding->id }}" {{ request('holding_id') == $holding->id ? 'selected' : '' }}>{{ $holding->company_alias }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">نوع شرکت</label>
                        <select class="form-select" name="company_type" id="company_type" onchange="this.form.submit();">
                            <option value="" {{ !request('company_type') ? 'selected' : '' }}>همه انواع</option>
                            @foreach(['تولیدی', 'پخش', 'دانشگاهی', 'پروژه‌ای', 'خدماتی', 'تحقیقاتی', 'بانکی', 'سرمایه‌گذاری'] as $type)
                                <option value="{{ $type }}" {{ request('company_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">مرتب‌سازی بر اساس</label>
                        <select class="form-select" name="sort_by" id="sort_by" onchange="this.form.submit();">
                            <option value="final_score" {{ request('sort_by') == 'final_score' || !request('sort_by') ? 'selected' : '' }}>جمع امتیازات</option>
                            <option value="maturity_level" {{ request('sort_by') == 'maturity_level' ? 'selected' : '' }}>سطح بلوغ</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            
            <div class="card-body">
                @if ($allRankings->isEmpty())
                    <p class="error-message">هیچ شرکتی برای نمایش یافت نشد.</p>
                @else
                    <table class="table">
                        <thead>
                            <tr>
                                <th>رتبه</th>
                                <th>نام شرکت</th>
                                <th>هلدینگ بالاسری</th>
                                <th>تاریخ آخرین ارزیابی</th>
                                <th>جمع امتیازات</th>
                                <th>سطح بلوغ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allRankings as $index => $ranking)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $ranking->company_alias }}</td>
                                    <td>{{ $ranking->parent_holding }}</td>
                                    <td>{{ $ranking->latest_date }}</td>
                                    <td>{{ $ranking->latest_score }}</td>
                                    <td>{{ $ranking->maturity_level ?? 'نامشخص' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>