<?php

namespace App\Services\AI;

use App\Models\AiEvaluationSummary;
use App\Services\AI\CompanyAiChatService;
use App\Services\AI\CompanyComparisonChatService;
use App\Services\AI\LstmClient;
use App\Services\LLM\LlamaClient;

class CompanyAssistantService
{
    protected CompanyAiChatService $singleService;
    protected CompanyComparisonChatService $compareService;
    protected LstmClient $lstmClient;
    protected LlamaClient $llm;

    public function __construct(
        CompanyAiChatService $singleService,
        CompanyComparisonChatService $compareService,
        LstmClient $lstmClient,
        LlamaClient $llm
    ) {
        $this->singleService  = $singleService;
        $this->compareService = $compareService;
        $this->lstmClient     = $lstmClient;
        $this->llm            = $llm;
    }

    /**
     * نقطه‌ی واحد برای پاسخ‌گویی:
     *  - اگر companyB ست شود → مقایسه دو شرکت (سناریوی مقایسه‌ای)
     *  - اگر فقط companyA ست باشد:
     *      - اگر سؤال آینده‌محور باشد → سناریوی پیش‌بینی (LSTM + LLM)
     *      - در غیر این صورت → سناریوی تک‌شرکت (RAG معمولی)
     */
    public function handleQuestion(string $question, $companyA, $companyB = null): array
    {
        $companyA = $companyA ?: null;
        $companyB = $companyB ?: null;

        // ۱) سناریوی مقایسه‌ای دو شرکت (رفتار قبلی)
        if ($companyB) {
            $result = $this->compareService->answerComparisonQuestion($question, $companyA, $companyB);

            return [
                'scenario'   => 'company_compare',
                'company_a'  => $companyA,
                'company_b'  => $companyB,
                'answer'     => $result['answer'],
                'system'     => $result['system'] ?? null,
                'user'       => $result['user'] ?? null,
                'context_a'  => $result['context_a_text'] ?? null,
                'context_b'  => $result['context_b_text'] ?? null,
                'blocks_a'   => $result['context_a_blocks'] ?? [],
                'blocks_b'   => $result['context_b_blocks'] ?? [],
            ];
        }

        // اگر هیچ شرکت انتخاب نشده (سناریوی غیرمجاز)
        if (!$companyA) {
            return [
                'scenario'   => 'no_company_selected',
                'company_a'  => null,
                'company_b'  => null,
                'answer'     => 'برای پاسخ دقیق، لازم است ابتدا یک شرکت را انتخاب کنید.',
                'system'     => null,
                'user'       => null,
                'context_a'  => null,
                'context_b'  => null,
                'blocks_a'   => [],
                'blocks_b'   => [],
            ];
        }

        // ۲) اگر فقط یک شرکت است و سؤال آینده‌محور باشد → سناریوی پیش‌بینی LSTM
        if ($this->isForecastQuestion($question)) {
            return $this->handleForecastScenario($question, (int) $companyA);
        }

        // ۳) در غیر این صورت → سناریوی تک‌شرکت (رفتار RAG قبلی)
        $result = $this->singleService->answerCompanyQuestion($question, $companyA);

        return [
            'scenario'  => 'single_company',
            'company_a' => $companyA,
            'company_b' => null,
            'answer'    => $result['answer'],
            'system'    => $result['system'] ?? null,
            'user'      => $result['user'] ?? null,
            'context_a' => $result['context'] ?? null,
            'context_b' => null,
            'blocks_a'  => $result['blocks'] ?? [],
            'blocks_b'  => [],
        ];
    }

    /**
     * تشخیص اینکه سؤال آینده‌محور / پیش‌بینی است یا نه
     * (اگر یکی از کلیدواژه‌های مشخص را داشته باشد)
     */
    protected function isForecastQuestion(string $question): bool
    {
        $q = mb_strtolower($question, 'UTF-8');

        $keywords = [
            'ارزیابی بعدی',
            'ارزيابي بعدي',
            'دوره بعد',
            'دوره‌ی بعد',
            'دوره بعدی',
            'پیش‌بینی',
            'پيش بيني',
            'پیش بینی',
            'سال بعد',
            'سال آینده',
            'دو سال بعد',
            'دو سال آینده',
            'سه سال بعد',
            'سه سال آینده',
            'پنج سال بعد',
            'پنج سال آینده',
            'در آینده',
            'روند آینده',
            'آینده این شرکت',
        ];

        foreach ($keywords as $kw) {
            if (mb_strpos($q, $kw) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * سناریوی پیش‌بینی ارزیابی بعدی (Next Evaluation) با استفاده از LSTM + آخرین ارزیابی واقعی + LLM
     */
    protected function handleForecastScenario(string $question, int $companyId): array
    {
        // ۱) دریافت پیش‌بینی خام از سرویس LSTM
        $forecast = $this->lstmClient->predictNextEvaluation($companyId);

        if (!$forecast) {
            $fallbackAnswer = 'در حال حاضر امکان محاسبه‌ی خودکار پیش‌بینی برای این شرکت وجود ندارد. '
                . 'احتمالاً تعداد ارزیابی‌های انجام‌شده هنوز برای مدل کافی نیست. '
                . 'لطفاً پس از انجام ارزیابی‌های بیشتر مجدداً تلاش فرمایید.';

            return [
                'scenario'   => 'forecast_next_eval_error',
                'company_a'  => $companyId,
                'company_b'  => null,
                'answer'     => $fallbackAnswer,
                'system'     => null,
                'user'       => null,
                'context_a'  => null,
                'context_b'  => null,
                'blocks_a'   => ['forecast' => null],
                'blocks_b'   => [],
            ];
        }

        $score = $forecast['predicted_final_score_next'] ?? null;
        $level = $forecast['predicted_maturity_level_next'] ?? null;

        // ۲) برای grounding: آخرین ارزیابی واقعی از AiEvaluationSummary
        $lastEval = AiEvaluationSummary::where('company_id', $companyId)
            ->orderByDesc('evaluation_date')
            ->first();

        $lastContextText = null;
        if ($lastEval) {
            $lastContextText = sprintf(
                "آخرین ارزیابی ثبت‌شده برای این شرکت در تاریخ %s انجام شده است. "
                . "امتیاز نهایی آن ارزیابی %.2f از 100 و سطح بلوغ کلی سطح %d از 5 بوده است.",
                optional($lastEval->evaluation_date)->format('Y-m-d'),
                $lastEval->final_score,
                $lastEval->overall_maturity_level
            );
        }

        // ۳) ساخت پرامپت برای توضیح مدیریتی توسط LLM
        $prompt = $this->buildForecastPrompt(
            $question,
            $companyId,
            $score,
            $level,
            $lastContextText
        );

        $answer = $this->llm->chat([
            'role'    => 'user',
            'content' => $prompt,
        ]);

        return [
            'scenario'   => 'forecast_next_eval',
            'company_a'  => $companyId,
            'company_b'  => null,
            'answer'     => $answer,
            'system'     => 'forecast_next_eval_v1',
            'user'       => $prompt,
            'context_a'  => $lastContextText,
            'context_b'  => null,
            'blocks_a'   => [
                'forecast' => $forecast,
                'last_eval' => $lastEval ? [
                    'date'  => optional($lastEval->evaluation_date)->format('Y-m-d'),
                    'score' => $lastEval->final_score,
                    'level' => $lastEval->overall_maturity_level,
                ] : null,
            ],
            'blocks_b'   => [],
        ];
    }

    /**
     * پرامپت توضیح پیش‌بینی ارزیابی بعدی
     */
    protected function buildForecastPrompt(
        string $question,
        int $companyId,
        ?float $score,
        ?int $level,
        ?string $lastContextText
    ): string {
        $scoreText = $score !== null ? number_format($score, 2) : 'نامشخص';
        $levelText = $level !== null ? (string) $level : 'نامشخص';

        $context = $lastContextText ?: 'اطلاعاتی از ارزیابی‌های قبلی در دسترس نیست.';

        return <<<EOT
شما یک تحلیل‌گر ارشد ارزیابی و بلوغ فناوری اطلاعات هستید.

[سؤال کاربر]
{$question}

[خروجی مدل پیش‌بینی (LSTM) برای ارزیابی بعدی این شرکت]
- شناسه شرکت: {$companyId}
- امتیاز نهایی پیش‌بینی‌شده در ارزیابی بعدی: {$scoreText} از 100
- سطح بلوغ پیش‌بینی‌شده در ارزیابی بعدی: سطح {$levelText} از 5

[خلاصه‌ای از آخرین ارزیابی واقعی]
{$context}

لطفاً موارد زیر را به زبان فارسی رسمی و قابل فهم برای مدیرعامل بنویس:

1. در 3 تا 5 جمله توضیح بده که این پیش‌بینی چه تصویری از وضعیت آتی شرکت ارائه می‌دهد (با توجه به آخرین ارزیابی واقعی).
2. اگر سطح بلوغ پیش‌بینی‌شده پایین است (1 یا 2)، به‌صورت محترمانه به ضرورت برنامه‌ی بهبود و سرمایه‌گذاری در حوزه‌ی فناوری اطلاعات اشاره کن.
3. اگر سطح بلوغ پیش‌بینی‌شده متوسط یا بالاست (3 تا 5)، به حفظ دستاوردها و تمرکز بر نقاط قابل بهبود اشاره کن.
4. در انتها، یک «جمع‌بندی یک‌خطی مدیریتی» بنویس که قابل استفاده در گزارش‌های مدیرعامل باشد.

دقت کن:
- از اعداد بالا فقط برای تفسیر استفاده کن و داده‌ی جدید خلق نکن.
- جواب را به صورت متن پیوسته‌ی فارسی برگردان (بدون بولت‌پوینت Markdown).
EOT;
    }
}
