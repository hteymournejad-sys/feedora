<!-- resources/views/assessment/result.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>نتیجه ارزیابی</title>
    <style>
        body { font-family: Arial, sans-serif; direction: rtl; }
    </style>
</head>
<body>
    <h1>نتیجه ارزیابی</h1>
    <p>درصد عملکرد شما: {{ number_format($assessment->performance_percentage, 2) }}%</p>
    <p>وضعیت: {{ $assessment->status }}</p>
    <p>تاریخ نهایی شدن: {{ $assessment->finalized_date }}</p>
</body>
</html>