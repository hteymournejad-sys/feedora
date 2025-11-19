<?php

namespace App\Services\AI;

use App\Models\AiConversation;
use App\Models\AiEvaluationSummary;
use Illuminate\Support\Facades\Auth;

class CompanyComparisonChatService
{
    protected CompanyComparisonQaService $qaService;
    protected LlmClient $llmClient;

    public function __construct(CompanyComparisonQaService $qaService, LlmClient $llmClient)
    {
        $this->qaService = $qaService;
        $this->llmClient = $llmClient;
    }

    /**
     * سؤال مقایسه‌ای دو شرکت → پرومپت → LLM → پاسخ + ثبت لاگ
     */
    public function answerComparisonQuestion(string $question, $companyA, $companyB): array
    {
        // 1) ساخت پرومپت مقایسه‌ای
        $prompt = $this->qaService->buildPromptForComparisonQuestion($question, $companyA, $companyB);

        // 2) فراخوانی مدل
        $answerText = $this->llmClient->chat($prompt['system'], $prompt['user']);

        // 3) استخراج اطلاعات شرکت‌ها از AiEvaluationSummary
        [$companyAId, $companyAAlias] = $this->resolveCompanyInfo($companyA);
        [$companyBId, $companyBAlias] = $this->resolveCompanyInfo($companyB);

        // 4) ثبت در جدول لاگ
        AiConversation::create([
            'user_id'         => Auth::id(),
            'scenario_type'   => 'company_compare',
            'company_a_id'    => $companyAId,
            'company_a_alias' => $companyAAlias ?? (is_string($companyA) ? $companyA : null),
            'company_b_id'    => $companyBId,
            'company_b_alias' => $companyBAlias ?? (is_string($companyB) ? $companyB : null),
            'question'        => $question,
            'answer'          => $answerText,
            'system_prompt'   => $prompt['system'],
            'user_prompt'     => $prompt['user'],
            'context_a'       => $prompt['context_a_text'] ?? null,
            'context_b'       => $prompt['context_b_text'] ?? null,
            'meta'            => [
                'blocks_a' => $prompt['context_a_blocks'] ?? [],
                'blocks_b' => $prompt['context_b_blocks'] ?? [],
            ],
        ]);

        // 5) خروجی
        return [
            'answer'          => $answerText,
            'system'          => $prompt['system'],
            'user'            => $prompt['user'],
            'context_a_text'  => $prompt['context_a_text'],
            'context_b_text'  => $prompt['context_b_text'],
            'context_a_blocks'=> $prompt['context_a_blocks'],
            'context_b_blocks'=> $prompt['context_b_blocks'],
        ];
    }

    /**
     * کمک‌کننده برای استخراج company_id و alias از AiEvaluationSummary
     */
    protected function resolveCompanyInfo($company): array
    {
        $companyId   = null;
        $companyAlias = null;

        if (is_numeric($company)) {
            $companyId = (int) $company;

            $row = AiEvaluationSummary::where('company_id', $companyId)
                ->orderByDesc('evaluation_date')
                ->first();

            if ($row) {
                $companyAlias = $row->company_alias;
            }
        } else {
            $row = AiEvaluationSummary::where('company_alias', $company)
                ->orderByDesc('evaluation_date')
                ->first();

            if ($row) {
                $companyId    = $row->company_id;
                $companyAlias = $row->company_alias;
            }
        }

        return [$companyId, $companyAlias];
    }
}
