<?php

return [

    // آدرس پایه سرور LLM داخلی
    'base_url' => env('LLM_BASE_URL', 'http://127.0.0.1:8081'),

    // مسیر endpoint چت (اگر سرورت OpenAI-compatible هست، معمولاً اینه:)
    'chat_path' => env('LLM_CHAT_PATH', '/v1/chat/completions'),

    // کلید API اگر لازم داشت (اگر نه، خالی می‌مونه)
    'api_key' => env('LLM_API_KEY', null),

    // نام مدل روی سرور LLM
    'model' => env('LLM_MODEL', 'local-llm-model'),

    // هایپِرپارامترها
    'temperature' => (float) env('LLM_TEMPERATURE', 0.3),
    'top_p'       => (float) env('LLM_TOP_P', 0.9),
    'max_tokens'  => (int) env('LLM_MAX_TOKENS', 7000),

    // timeout درخواست
    'timeout' => (int) env('LLM_TIMEOUT', 300),
];
