<?php

namespace App\Services\AI;

use GuzzleHttp\Client;
use Throwable;

class LlmClient
{
    protected Client $http;
    protected array $config;

    public function __construct()
    {
        $this->config = config('llm', []);

        $this->http = new Client([
            'timeout' => $this->config['timeout'] ?? 300,
            'base_uri' => rtrim($this->config['base_url'] ?? '', '/'),
        ]);
    }

    /**
     * ارسال یک مکالمه ساده (system + user) به مدل و دریافت پاسخ متنی
     */
    public function chat(string $systemMessage, string $userMessage): string
    {
        $chatPath = $this->config['chat_path'] ?? '/v1/chat/completions';

        if (empty($this->config['base_url'])) {
            return 'LLM_BASE_URL در فایل .env تنظیم نشده است.';
        }

        $payload = [
            'model'       => $this->config['model'] ?? 'local-llm-model',
            'temperature' => $this->config['temperature'] ?? 0.3,
            'top_p'       => $this->config['top_p'] ?? 0.9,
            'max_tokens'  => $this->config['max_tokens'] ?? 768,
            'messages'    => [
                ['role' => 'system', 'content' => $systemMessage],
                ['role' => 'user',   'content' => $userMessage],
            ],
        ];

        $headers = [
            'Content-Type' => 'application/json',
        ];

        if (!empty($this->config['api_key'])) {
            $headers['Authorization'] = 'Bearer '.$this->config['api_key'];
        }

        try {
            $response = $this->http->post($chatPath, [
                'headers' => $headers,
                'json'    => $payload,
            ]);

            $data = json_decode((string) $response->getBody(), true);

            // ساختار سبک OpenAI: choices[0].message.content
            return $data['choices'][0]['message']['content'] ?? 'پاسخی از مدل دریافت نشد.';
        } catch (Throwable $e) {
            return 'خطا در ارتباط با سرور LLM: '.$e->getMessage();
        }
    }
}
