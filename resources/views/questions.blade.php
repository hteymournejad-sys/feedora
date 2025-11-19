<!DOCTYPE html>
<html>
<head>
<!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <title>سوالات ارزیابی</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        @font-face {
            font-family: 'IRANSans';
            src: url('/fonts/Iranian Sans.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        body {
            font-family: 'IRANSans', sans-serif;
            direction: rtl;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            width: 100%;
            max-width: 1200px;
            padding: 20px;
        }
        .header {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
        }
        .content {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            width: 100%;
        }
        .description-box {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            text-align: right;
            width: 30%;
            box-sizing: border-box;
        }
        .question-box {
            background-color: white;
            width: 65%;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
            box-sizing: border-box;
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
            background-color: #4CAF50;
            border-radius: 10px;
            width: {{ $progress }}%;
            transition: width 0.3s;
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
            gap: 10px;
            margin-bottom: 10px;
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
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        .btn-previous {
            background-color: #007BFF;
            color: white;
        }
        .btn-exit {
            background-color: #DC3545;
            color: white;
        }
        .btn-guidance {
            background-color: #28A745;
            color: white;
        }
        .auto-save-message {
            display: none;
            color: green;
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
        }
        /* استایل‌های modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            direction: rtl;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 10px;
            text-align: right;
            font-family: 'IRANSans', sans-serif;
        }
        .modal-content h3 {
            margin-top: 0;
        }
        .modal-content p {
            white-space: pre-wrap; /* برای حفظ خطوط جدید و فرمت متن */
        }
        .close {
            color: #aaa;
            float: left;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="title">سوالات مربوط به حوزه {{ $currentQuestion->domain }}</div>
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

        <div style="text-align: center; margin-top: 20px;">
            <p>پرسشنامه</p>
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
