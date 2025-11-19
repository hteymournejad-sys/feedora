<?php

namespace App\Services\AI;

use App\Models\AiConversation;
use App\Models\AiEvaluationSummary;
use Illuminate\Support\Facades\Auth;

class CompanyAiChatService
{
    protected CompanyQaService $qaService;
    protected LlmClient $llmClient;

    public function __construct(CompanyQaService $qaService, LlmClient $llmClient)
    {
        $this->qaService = $qaService;
        $this->llmClient = $llmClient;
    }

    /**
     * سؤال کاربر + شرکت → ساخت پرومپت → ارسال به LLM → دریافت پاسخ + ثبت لاگ
     */
    public function answerCompanyQuestion(string $question, $company): array
    {
        // 1) ساخت پرومپت (system + user + context)
        $prompt = $this->qaService->buildPromptForCompanyQuestion($question, $company);

        $systemMessage = $prompt['system'];
        $userMessage   = $prompt['user'];

        // 2) ارسال به LLM
        $answerText = $this->llmClient->chat($systemMessage, $userMessage);

        // 3) استخراج اطلاعات شرکت برای لاگ (company_id و alias از AiEvaluationSummary)
        $companyId   = null;
        $companyAlias = null;

        // اگر ورودی عدد بود، مستقیم company_id است
        if (is_numeric($company)) {
            $companyId = (int) $company;

            $row = AiEvaluationSummary::where('company_id', $companyId)
                ->orderByDesc('evaluation_date')
                ->first();

            if ($row) {
                $companyAlias = $row->company_alias;
            }
        } else {
            // فرض می‌کنیم alias است و از روی آن company_id را در می‌آوریم
            $row = AiEvaluationSummary::where('company_alias', $company)
                ->orderByDesc('evaluation_date')
                ->first();

            if ($row) {
                $companyId    = $row->company_id;
                $companyAlias = $row->company_alias;
            }
        }

        // 4) ثبت در جدول لاگ
        AiConversation::create([
            'user_id'        => Auth::id(),
            'scenario_type'  => 'single_company',
            'company_a_id'   => $companyId,
            'company_a_alias'=> $companyAlias ?? (is_string($company) ? $company : null),
            'question'       => $question,
            'answer'         => $answerText,
            'system_prompt'  => $systemMessage,
            'user_prompt'    => $userMessage,
            'context_a'      => $prompt['context_text'] ?? null,
            'context_b'      => null,
            'meta'           => [
                'blocks_a' => $prompt['context_blocks'] ?? [],
            ],
        ]);

        // 5) خروجی برای Controller / UI
        return [
            'answer'   => $answerText,
            'system'   => $systemMessage,
            'user'     => $userMessage,
            'context'  => $prompt['context_text'],
            'blocks'   => $prompt['context_blocks'],
        ];
    }
}
