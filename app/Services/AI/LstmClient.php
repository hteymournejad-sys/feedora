<?php

namespace App\Services\AI;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class LstmClient
{
    protected Client $http;
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.lstm.base_url'), '/');

        $this->http = new Client([
            'base_uri' => $this->baseUrl,
            'timeout'  => 10.0,
        ]);
    }

    /**
     * پیش‌بینی ارزیابی بعدی برای یک شرکت
     */
    public function predictNextEvaluation(int $companyId): ?array
    {
        try {
            $response = $this->http->get('/lstm/predict', [
                'query' => ['company_id' => $companyId],
            ]);

            $data = json_decode((string) $response->getBody(), true);

            return $data;
        } catch (\Throwable $e) {
            Log::error('LSTM API error: ' . $e->getMessage(), [
                'company_id' => $companyId,
            ]);
            return null;
        }
    }
}
