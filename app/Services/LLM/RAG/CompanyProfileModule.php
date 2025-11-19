<?php

namespace App\Services\LLM\RAG;

use App\Services\AI\CompanyFullProfileService;
use Illuminate\Support\Str;

class CompanyProfileModule
{
    protected CompanyFullProfileService $profileService;

    public function __construct(CompanyFullProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * ورودی:
     *  - سؤال کاربر (متن کامل)
     *  - نام یا شناسه شرکت
     *
     * خروجی:
     *  - آرایه‌ای شامل:
     *      - context_blocks: بلوک‌های انتخاب‌شده
     *      - context_text: متن نهایی برای تغذیه مدل
     */
    public function buildContextForQuestion(string $question, $company): array
    {
        // 1) گرفتن بلوک‌های کامل از سرویس
        $blocks = $this->profileService->buildLlMBlocks($company);

        // 2) فیلتر‌کردن بر اساس نوع سؤال
        $filteredBlocks = $this->filterBlocksByQuestion($blocks, $question);

        // اگر چیزی فیلتر نشد، همه را بده (سؤال کلی مثل "وضعیت رایتل چطوره؟")
        if (empty($filteredBlocks)) {
            $filteredBlocks = $blocks;
        }

        // 3) تبدیل بلوک‌ها به متن نهایی
        $contextText = $this->buildContextText($filteredBlocks);

        return [
            'context_blocks' => $filteredBlocks,
            'context_text'   => $contextText,
        ];
    }

    /**
     * فیلتر کردن بلوک‌ها با توجه به کلمات کلیدی سؤال
     */
    protected function filterBlocksByQuestion(array $blocks, string $question): array
    {
        $q = Str::of(mb_strtolower($question, 'utf-8'));

        $filtered = [];

        foreach ($blocks as $block) {
            $title   = mb_strtolower($block['title'] ?? '', 'utf-8');
            $content = mb_strtolower($block['content'] ?? '', 'utf-8');
            $text    = $title.' '.$content;

            // چند قانون ساده بر اساس کلمات کلیدی
            $isRiskQuestion = $q->contains('ریسک') || $q->contains('خطر');
            $isStrengthQuestion = $q->contains('نقطه قوت') || $q->contains('قوت') || $q->contains('مزیت');
            $isImprovementQuestion = $q->contains('قابل بهبود') || $q->contains('ضعف') || $q->contains('بهبود');
            $isTrendQuestion = $q->contains('روند') || $q->contains('ترند') || $q->contains('آینده') || $q->contains('دو سال آینده') || $q->contains('پنج سال آینده');
            $isBudgetQuestion = $q->contains('بودجه') || $q->contains('هزینه') || $q->contains('سرمایه گذاری');
            $isHrQuestion = $q->contains('منابع انسانی') || $q->contains('پرسنل') || $q->contains('نیروی انسانی') || $q->contains('کارشناس');

            $match = false;

            if ($isRiskQuestion && Str::contains($title, 'ریسک')) {
                $match = true;
            }

            if ($isStrengthQuestion && Str::contains($title, 'نقاط قوت')) {
                $match = true;
            }

            if ($isImprovementQuestion && Str::contains($title, 'نقاط قابل بهبود')) {
                $match = true;
            }

            if ($isTrendQuestion && Str::contains($title, 'روند امتیاز')) {
                $match = true;
            }

            if ($isBudgetQuestion && Str::contains($title, 'غیر فنی IT')) {
                $match = true;
            }

            if ($isHrQuestion && Str::contains($title, 'نیروی انسانی IT')) {
                $match = true;
            }

            // اگر سؤال خیلی کلی باشد (مثلاً "وضعیت رایتل چطوره؟")
            // می‌توانیم عنوان "خلاصه وضعیت فنی..." را همیشه نگه داریم
            if (!$isRiskQuestion && !$isStrengthQuestion && !$isImprovementQuestion
                && !$isTrendQuestion && !$isBudgetQuestion && !$isHrQuestion
                && Str::contains($title, 'خلاصه وضعیت فنی')
            ) {
                $match = true;
            }

            if ($match) {
                $filtered[] = $block;
            }
        }

        return $filtered;
    }

    /**
     * تبدیل آرایه بلوک‌ها به یک متن واحد برای context مدل
     */
    protected function buildContextText(array $blocks): string
    {
        $parts = [];

        foreach ($blocks as $block) {
            $title   = $block['title'] ?? '';
            $content = $block['content'] ?? '';

            $parts[] = "### {$title}\n\n{$content}";
        }

        return implode("\n\n-----------------------------\n\n", $parts);
    }
}
