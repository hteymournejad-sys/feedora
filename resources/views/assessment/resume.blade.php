<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ادامه ارزیابی</title>
</head>
<body>
    <h1>ادامه ارزیابی</h1>
    <p>شما قبلاً به سوالاتی از حوزه {{ $selectedDomain }} پاسخ داده‌اید.</p>
    <a href="{{ route('assessment.questions', [$assessment->id, 'domain' => $selectedDomain]) }}">ادامه دادن</a>
</body>
</html>