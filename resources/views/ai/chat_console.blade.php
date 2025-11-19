<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>دستیار هوشمند فیدورا - تحلیل شرکت‌ها</title>
  <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Vazirmatn', sans-serif;
      background: #f8fafc;
      margin: 0; padding: 0;
      display: flex; justify-content: center; align-items: flex-start;
      min-height: 100vh;
    }
    .container {
      width: 95%;
      max-width: 800px;
      background: #fff;
      margin-top: 60px;
      padding: 40px 50px;
      border-radius: 16px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.08);
    }
    h1 {
      font-weight: 600;
      color: #0f172a;
      text-align: center;
      margin-bottom: 10px;
    }
    p.subtitle {
      text-align: center;
      color: #64748b;
      font-size: 15px;
      margin-bottom: 30px;
    }

    .label {
      color: #334155;
      font-weight: 600;
      margin-top: 20px;
      margin-bottom: 6px;
      font-size: 14px;
      display: block;
    }

    textarea {
      width: 100%;
      min-height: 120px;
      border-radius: 12px;
      border: 1px solid #cbd5e1;
      padding: 12px 16px;
      font-family: inherit;
      font-size: 15px;
      line-height: 1.8;
      resize: vertical;
      outline: none;
      transition: border-color 0.3s;
    }
    textarea:focus {
      border-color: #0ea5e9;
      box-shadow: 0 0 0 2px rgba(14,165,233,0.2);
    }

    select {
      width: 100%;
      border-radius: 12px;
      border: 1px solid #cbd5e1;
      padding: 10px 14px;
      font-family: inherit;
      font-size: 14px;
      outline: none;
      background: #fff;
      transition: border-color 0.3s;
    }
    select:focus {
      border-color: #0ea5e9;
      box-shadow: 0 0 0 2px rgba(14,165,233,0.2);
    }

    button {
      background: linear-gradient(90deg, #28a745, #1e7e34);
      border: none;
      color: #fff;
      padding: 12px 25px;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      text-align: center;
      display: inline-block;
      font-family: 'Vazirmatn', sans-serif;
      margin-top: 15px;
    }
    button:hover {
      background: linear-gradient(90deg, #1e7e34, #28a745);
      transform: scale(1.05);
      box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
    }

    .answer-box {
      background: #f9fafb;
      border: 1px solid #e2e8f0;
      padding: 16px 20px;
      border-radius: 12px;
      margin-top: 15px;
      font-size: 14px;
      line-height: 1.9;
      white-space: pre-wrap;
      direction: rtl;
      text-align: justify;
      min-height: 60px;
    }

    .help-text {
      font-size: 12px;
      color: #64748b;
      margin-top: 4px;
    }

    .row {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
    }
    .col {
      flex: 1 1 200px;
    }

    .error-box {
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: #991b1b;
      padding: 10px 14px;
      border-radius: 12px;
      margin-top: 20px;
      font-size: 13px;
    }

    .section-title {
      color: #334155;
      font-weight: 600;
      margin-top: 25px;
      margin-bottom: 6px;
      font-size: 15px;
    }

    .context-box {
      background: #f9fafb;
      border: 1px dashed #cbd5e1;
      padding: 12px 16px;
      border-radius: 12px;
      margin-top: 8px;
      font-size: 12px;
      white-space: pre-wrap;
      direction: rtl;
      text-align: justify;
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
      font-family: 'Vazirmatn', sans-serif;
      font-size: 16px;
      font-weight: 500;
    }
    .profile-btn:hover {
      background: linear-gradient(90deg, #2980b9, #3498db);
      transform: scale(1.05);
      box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
    }

    .loading {
      animation: pulse 1.2s ease-in-out infinite;
      color: #0ea5e9;
    }
    @keyframes pulse {
      0%,100% { opacity: 0.4; }
      50% { opacity: 1; }
    }

  </style>
</head>
<body>
  <a href="{{ route('profile') }}" class="profile-btn">پروفایل کاربر</a>

  <div class="container">
    <h1>💡 دستیار هوشمند فیدورا</h1>
    <p class="subtitle">
      این نسخه برای تحلیل یک شرکت یا مقایسه‌ی دو شرکت بر اساس داده‌های فیدورا طراحی شده است.
    </p>

    <form id="chat-form">
      @csrf

      <div class="row">
        <div class="col">
          <label class="label">شرکت اصلی</label>
          <select name="company_a" required>
            <option value="">انتخاب کنید...</option>
            @foreach($companies as $c)
              @php $val = $c->company_alias ?: $c->company_id; @endphp
              <option value="{{ $val }}" {{ (isset($companyA) && $companyA == $val) ? 'selected' : '' }}>
                {{ $c->company_alias ?? ('ID: '.$c->company_id) }}
              </option>
            @endforeach
          </select>
          <div class="help-text">شرکت اصلی</div>
        </div>

        <div class="col">
          <label class="label">شرکت دوم (اختیاری)</label>
          <select name="company_b">
            <option value="">— فقط تحلیل شرکت اصلی —</option>
            @foreach($companies as $c)
              @php $val = $c->company_alias ?: $c->company_id; @endphp
              <option value="{{ $val }}" {{ (isset($companyB) && $companyB == $val) ? 'selected' : '' }}>
                {{ $c->company_alias ?? ('ID: '.$c->company_id) }}
              </option>
            @endforeach
          </select>
          <div class="help-text">برای مقایسه پر کنید</div>
        </div>
      </div>

      <label class="label">سؤال شما:</label>
      <textarea id="question" name="question" required>{{ old('question', $question) }}</textarea>

      <button type="submit">ارسال و تحلیل</button>
    </form>

    <div class="section-title">پاسخ مدل:</div>
    <div class="answer-box" id="answer-box">
      @if($answer)
        {{ $answer }}
      @else
        —
      @endif
    </div>

  </div>

<script>
  (function () {
    const form = document.getElementById('chat-form');
    const answerBox = document.getElementById('answer-box');
    let typingInterval = null;

    function typeWriter(text) {
      if (typingInterval) clearInterval(typingInterval);
      answerBox.textContent = '';
      let i = 0;
      const speed = 15;

      typingInterval = setInterval(() => {
        if (i >= text.length) {
          clearInterval(typingInterval);
          return;
        }
        answerBox.textContent += text[i];
        i++;
      }, speed);
    }

    form.addEventListener('submit', async function (e) {
      e.preventDefault();

      const formData = new FormData(form);
      const payload = {
        company_a: formData.get('company_a'),
        company_b: formData.get('company_b'),
        question:  formData.get('question'),
      };

      answerBox.textContent = 'در حال پردازش...';
      answerBox.classList.add('loading');

      try {
        const resp = await fetch("{{ route('ai.console.chat.ajax') }}", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
          },
          body: JSON.stringify(payload)
        });

        let data = await resp.json();

        answerBox.classList.remove('loading');

        if (!resp.ok || (data.status && data.status !== 'ok')) {
          answerBox.textContent = 'خطا در ارتباط با مدل.';
          console.error(data);
          return;
        }

        // پاک‌سازی فقط دو کاراکتر # و *
        const answerText = (data.answer || '—')
              .replace(/#/g, '')
              .replace(/\*/g, '');

        typeWriter(answerText);

      } catch (err) {
        answerBox.classList.remove('loading');
        answerBox.textContent = 'خطا در ارتباط با مدل.';
        console.error(err);
      }
    });
  })();
</script>

</body>
</html>
