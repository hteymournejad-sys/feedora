<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>دستیار هوشمند فیدورا</title>
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
      margin-bottom: 40px;
    }
    textarea {
      width: 100%;
      min-height: 140px;
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

    /* دکمه ارسال و تحلیل - سبز و مشابه پروفایل */
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
      margin-top: 25px;
      font-size: 15px;
      line-height: 1.9;
      white-space: pre-wrap;
    }
    .label {
      color: #334155;
      font-weight: 600;
      margin-top: 30px;
      margin-bottom: 6px;
      font-size: 15px;
    }
    .loading {
      animation: pulse 1.2s ease-in-out infinite;
      color: #0ea5e9;
    }
    @keyframes pulse {
      0%,100% { opacity: 0.4; }
      50% { opacity: 1; }
    }

    /* دکمه پروفایل کاربر - آبی */
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
  </style>
</head>
<body>
  <a href="{{ route('profile') }}" class="profile-btn">پروفایل کاربر</a>

  <div class="container">
    <h1>💡 دستیار هوشمند فیدورا</h1>
    <p class="subtitle">پرسش خود را مطرح کنید تا مدل هوش مصنوعی فیدورا پاسخ دهد</p>

    <form id="qform">
      <label class="label" for="question">سؤال شما:</label>
      <textarea id="question" name="question" placeholder="مثلاً: عملکرد کلی واحد فناوری اطلاعات تاپیکو در سال گذشته چگونه بوده است؟"></textarea>
      <br>
      <button type="submit">ارسال و تحلیل</button>
    </form>

    <div class="label">پاسخ مدل:</div>
    <div class="answer-box" id="answer">—</div>
  </div>

  <script>
  (async function(){
    const form = document.getElementById('qform');
    const answerBox = document.getElementById('answer');

    form.addEventListener('submit', async (e)=>{
      e.preventDefault();
      const q = document.getElementById('question').value.trim();
      if(!q) return;

      answerBox.textContent = 'در حال پردازش...';
      answerBox.classList.add('loading');

      try {
        const resp = await fetch("{{ route('ai.console.query') }}", {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({question: q})
        });
        const data = await resp.json();
        answerBox.classList.remove('loading');
        answerBox.textContent = data.answer || '—';
      } catch(err){
        answerBox.classList.remove('loading');
        answerBox.textContent = 'خطا در ارتباط با مدل.';
        console.error(err);
      }
    });
  })();
  </script>
</body>
</html>