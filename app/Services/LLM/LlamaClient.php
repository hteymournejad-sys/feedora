<?php

namespace App\Services\LLM;


use Illuminate\Support\Facades\Http;


class LlamaClient
{
protected string $baseUrl;
protected float $temperature;
protected float $topP;
protected int $maxTokens;


public function __construct()
{
$this->baseUrl = config('llm.base_url', 'http://127.0.0.1:8081');
$this->temperature = (float) config('llm.temperature', 0.3);
$this->topP = (float) config('llm.top_p', 0.9);
$this->maxTokens = (int) config('llm.max_tokens', 768);
}


public function chat(array $userMessage): string
{
$payload = [
'model' => 'qwen2.5-14b-instruct-q4_k_m',
'messages' => [
['role' => 'system', 'content' => 'You are a helpful and precise Persian data analyst assistant.'],
$userMessage,
],
'temperature' => $this->temperature,
'top_p' => $this->topP,
'max_tokens' => $this->maxTokens,
'stream' => false,
];


$resp = Http::timeout(120)->post($this->baseUrl . '/v1/chat/completions', $payload);


if (!$resp->ok()) {
// fallback to legacy /completion
$prompt = $userMessage['content'] ?? '';
return $this->completion($prompt);
}


$data = $resp->json();
return $data['choices'][0]['message']['content'] ?? 'پاسخی دریافت نشد.';
}


public function completion(string $prompt): string
{
$payload = [
'prompt' => $prompt,
'n_predict' => $this->maxTokens,
'temperature' => $this->temperature,
'top_p' => $this->topP,
];


$resp = Http::timeout(120)->post($this->baseUrl . '/completion', $payload);
if (!$resp->ok()) {
return 'خطا در ارتباط با LLM: ' . $resp->status() . ' - ' . $resp->body();
}
$data = $resp->json();
return $data['content'] ?? ($data['choices'][0]['text'] ?? 'پاسخی دریافت نشد.');
}
}