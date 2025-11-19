<!-- resources/views/assessment/finalize.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>نهایی کردن ارزیابی</title>
    <style>
        body { font-family: Arial, sans-serif; direction: rtl; }
    </style>
</head>
<body>
    <h1>نهایی کردن ارزیابی</h1>
    <p>پاسخ‌های شما با موفقیت ذخیره شد. آیا می‌خواهید ارزیابی را نهایی کنید؟</p>
    <form action="{{ route('assessment.finalize', $assessment->id) }}" method="GET">
        <button type="submit">نهایی کردن</button>
    </form>
</body>
</html>