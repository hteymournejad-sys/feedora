<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <title>سوالات ارزیابی</title>
    <!-- فونت Vazir -->
    <link href="https://cdn.jsdelivr.net/npm/vazir-font@30.1.0/dist/font-face.css" rel="stylesheet" onerror="this.style.display='none';console.error('فونت لود نشد، از فونت پیش‌فرض استفاده می‌شود');">
    <!-- بوت‌استرپ CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <!-- بوت‌استرپ JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
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
        .container {
            max-width: 1100px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
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
        .content {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            width: 100%;
        }
        .description-box {
            background-color: #f8f9fa;
            border: 1px solid #ecf0f1;
            border-radius: 10px;
            padding: 20px;
            text-align: right;
            width: 30%;
            box-sizing: border-box;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .description-box:hover {
            transform: translateY(-5px);
        }
        .question-box {
            background-color: #ffffff;
            width: 65%;
            padding: 20px;
            border: 1px solid #ecf0f1;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
            box-sizing: border-box;
            transition: transform 0.3s ease;
        }
        .question-box:hover {
            transform: translateY(-5px);
        }
        .progress-bar {
            width: 100%;
            background-color: #ddd;
            height: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .progress {
            height: 100%;
            background-color: #20B2AA;
            border-radius: 10px;
            width: {{ $progress }}%;
            transition: width 0.3s ease;
        }
        .question-number {
            font-size: 18px;
            margin-bottom: 10px;
            color: #555;
        }
        .question-text {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }
        .options {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 20px;
            padding-right: 20px;
        }
        .option {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .option label {
            font-size: 16px;
            color: #444;
        }
        .buttons {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            font-family: 'Vazir', sans-serif;
            transition: all 0.3s ease;
        }
        .btn-previous {
            background: linear-gradient(90deg, #3498db, #2980b9);
            color: white;
        }
        .btn-previous:hover {
            background: linear-gradient(90deg, #2980b9, #3498db);
            transform: scale(1.05);
        }
        .btn-exit {
            background-color: #e74c3c;
            color: white;
        }
        .btn-exit:hover {
            background-color: #c0392b;
            transform: scale(1.05);
        }
        .btn-guidance {
            background-color: #20B2AA;
            color: white;
            width: 100%;
            margin-top: 20px;
        }
        .btn-guidance:hover {
            background-color: #008B8B;
            transform: scale(1.05);
        }
        .auto-save-message {
            display: none;
            color: #20B2AA;
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
            font-weight: bold;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            direction: rtl;
        }
        .modal-content {
            background-color: #ffffff;
            margin: 2% auto;
            padding: 20px;
            border: 2px solid #ecf0f1;
            width: 80%;
            max-width: 900px;
            border-radius: 10px;
            text-align: right;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            font-family: 'Vazir', sans-serif;
        }
        .modal-content h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .modal-content p {
            white-space: pre-wrap;
        }
        .close {
            color: #aaa;
            float: left;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: #2c3e50;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="report-title-bar">
            <h1 class="report-title">سوالات مربوط به حوزه {{ $currentQuestion->domain }}</h1>
        </div>

        <div class="progress-bar">
            <div class="progress"></div>
        </div>

        <div class="content">
            <div class="question-box">
                <div class="question-number">
                    سوال {{ $questions->search(fn($q) => $q->id === $currentQuestion->id) + 1 }} از {{ $questions->count() }}
                </div>
                <div class="question-text">{{ $currentQuestion->text }}</div>

                <form action="{{ route('assessment.answer', [$assessment->id, $currentQuestion->id]) }}" method="POST" id="answerForm">
                    @csrf
                    <div class="options">
                        @php
                            $options = [
                                10 => 'هیچ اقدام مشخصی انجام نشده',
                                20 => 'اقدامات اولیه آغاز شده است',
                                30 => 'پیشرفت محدودی حاصل شده است',
                                40 => 'شواهد اولیه‌ای از اقدامات وجود دارد',
                                50 => 'عملکرد متوسطی ثبت شده است',
                                60 => 'عملکرد قابل‌قبولی مشاهده می‌شود',
                                70 => 'عملکرد بسیار خوبی ارائه شده است',
                                80 => 'عملکرد برجسته‌ای ثبت شده است',
                                90 => 'شواهد در سطح پیشرفته جهانی موجود است',
                                100 => 'سازمان در سطح استانداردهای جهانی پیشرو است',
                            ];
                            $existingAnswer = $assessment->answers->firstWhere('question_id', $currentQuestion->id);
                        @endphp
                        @foreach ($options as $score => $label)
                            <div class="option">
                                <input type="radio" name="score" value="{{ $score }}" id="score-{{ $score }}"
                                    {{ $existingAnswer && $existingAnswer->score == $score ? 'checked' : '' }} required>
                                <label for="score-{{ $score }}">{{ $score }} - {{ $label }}</label>
                            </div>
                        @endforeach
                    </div>
                </form>

                <div id="auto-save-message" class="auto-save-message">
                    پاسخ شما به‌صورت خودکار ذخیره شد.
                </div>

                <div class="buttons">
                    @if ($previousQuestion)
                        <a href="{{ route('assessment.previous', [$assessment->id, $previousQuestion->id]) }}" class="btn btn-previous">بازگشت به سوال قبل</a>
                    @else
                        <div></div>
                    @endif
                    <button class="btn btn-exit" onclick="window.location='{{ route('assessment.exit', $assessment->id) }}'">خروج</button>
                </div>
            </div>

            <div class="description-box">
                <h3>توضیح در خصوص سوال</h3>
                <p>{{ $currentQuestion->description ?? 'لطفاً سوال را با دقت بخوانید و گزینه مناسب را انتخاب کنید.' }}</p>
                <button class="btn btn-guidance" onclick="showGuidance()">راهنمای کامل پاسخ به سوال</button>
            </div>
        </div>
    </div>

    <!-- Modal برای نمایش راهنما -->
    <div id="guidanceModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeGuidance()">&times;</span>
            <h3>راهنمای کامل پاسخ به سوال</h3>
            <p id="guidanceText">{{ $currentQuestion->guide ?? 'راهنمایی برای این سوال تعریف نشده است.' }}</p>
        </div>
    </div>

    <script>
        try {
            console.log('جاوااسکریپت لود شد');

            const inputs = document.querySelectorAll('input[name="score"]');
            console.log('تعداد گزینه‌های رادیویی پیدا شده:', inputs.length);
            if (inputs.length === 0) {
                console.error('هیچ گزینه رادیویی پیدا نشد! مطمئن شوید که input با name="score" وجود دارد.');
            }

            inputs.forEach((input, index) => {
                console.log(`گزینه ${index + 1}:`, input.value);
                input.addEventListener('change', () => {
                    console.log('گزینه انتخاب شد:', input.value);
                    const form = document.getElementById('answerForm');
                    if (form) {
                        console.log('فرم در حال ارسال است...');
                        form.submit();
                    } else {
                        console.error('فرم با id="answerForm" پیدا نشد!');
                    }
                });
            });

            setInterval(() => {
                const form = document.getElementById('answerForm');
                const message = document.getElementById('auto-save-message');
                if (form) {
                    const formData = new FormData(form);
                    const selectedScore = formData.get('score');
                    if (selectedScore) {
                        console.log('Auto Save اجرا شد');
                        fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        }).then(response => {
                            console.log('Auto Save انجام شد:', response.status);
                            if (response.ok) {
                                message.style.display = 'block';
                                setTimeout(() => {
                                    message.style.display = 'none';
                                }, 2000);
                            }
                        }).catch(error => {
                            console.error('خطا در Auto Save:', error);
                        });
                    } else {
                        console.log('Auto Save: گزینه‌ای انتخاب نشده است.');
                    }
                } else {
                    console.error('فرم برای Auto Save پیدا نشد!');
                }
            }, 30000);

            // توابع برای نمایش و بستن modal
            function showGuidance() {
                const modal = document.getElementById('guidanceModal');
                modal.style.display = 'block';
            }

            function closeGuidance() {
                const modal = document.getElementById('guidanceModal');
                modal.style.display = 'none';
            }

            // بستن modal با کلیک روی پس‌زمینه
            window.onclick = function(event) {
                const modal = document.getElementById('guidanceModal');
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }
        } catch (error) {
            console.error('خطا در اجرای جاوااسکریپت:', error);
        }
    </script>
</body>
</html>