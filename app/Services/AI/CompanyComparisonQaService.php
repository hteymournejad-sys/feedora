<?php

namespace App\Services\AI;

use App\Services\LLM\RAG\CompanyProfileModule;

class CompanyComparisonQaService
{
    protected CompanyProfileModule $profileModule;

    public function __construct(CompanyProfileModule $profileModule)
    {
        $this->profileModule = $profileModule;
    }

    /**
     * ساخت پرومپت مقایسه‌ای برای دو شرکت
     *
     * ورودی:
     *   - $question : سؤال کاربر (مثلاً: ریسک‌ها و نقاط قوت رایتل و ذوب‌آهن را مقایسه کن)
     *   - $companyA : نام یا شناسه شرکت اول
     *   - $companyB : نام یا شناسه شرکت دوم
     *
     * خروجی:
     *   - [
     *       'system'          => string,
     *       'user'            => string,
     *       'context_a_text'  => string,
     *       'context_b_text'  => string,
     *       'context_a_blocks'=> array,
     *       'context_b_blocks'=> array,
     *     ]
     */
    public function buildPromptForComparisonQuestion(string $question, $companyA, $companyB): array
    {
        // 1) ساخت کانتکست برای هر شرکت با استفاده از همان ماژول RAG
        $ctxA = $this->profileModule->buildContextForQuestion($question, $companyA);
        $ctxB = $this->profileModule->buildContextForQuestion($question, $companyB);

        $contextAText   = $ctxA['context_text'] ?? '';
        $contextABlocks = $ctxA['context_blocks'] ?? [];

        $contextBText   = $ctxB['context_text'] ?? '';
        $contextBBlocks = $ctxB['context_blocks'] ?? [];

        // 2) پیام سیستمی: تمرکز روی مقایسه دو شرکت
        $systemMessage = <<<SYS
شما یک دستیار هوش مصنوعی متخصص در تحلیل و مقایسه وضعیت فناوری اطلاعات، تحول دیجیتال و هوشمندسازی در سازمان‌های بزرگ هستید.
خروجی شما باید تحلیلی، دقیق، مقایسه‌ای و قابل استفاده در تصمیم‌گیری مدیریتی باشد.
در مقایسه‌ها:
- ابتدا تصویر کلی از وضعیت هر دو شرکت ارائه کنید.
- سپس ریسک‌ها، نقاط قوت، نقاط قابل بهبود، روند امتیازات و سطح بلوغ را در کنار هم مقایسه کنید.
- در صورت وجود، وضعیت منابع انسانی IT، بودجه IT و آموزش را نیز در مقایسه لحاظ کنید.
- در پایان یک جمع‌بندی مدیریتی و چند پیشنهاد عملی برای هر دو شرکت ارائه دهید.
پاسخ را به زبان فارسی رسمی و ساختارمند ارائه کنید.
SYS;

        // 3) پیام کاربر: سؤال + کانتکست دو شرکت
        $userMessage = <<<USR
سؤال کاربر:
{$question}

اطلاعات زمینه‌ای درباره شرکت اول:
{$contextAText}

اطلاعات زمینه‌ای درباره شرکت دوم:
{$contextBText}

بر اساس اطلاعات فوق، یک تحلیل مقایسه‌ای جامع بین این دو شرکت ارائه بده.
در پاسخ خود حتماً موارد زیر را پوشش بده:
- خلاصه وضعیت کلی هر شرکت (امتیاز، سطح بلوغ، وضعیت حوزه‌های اصلی)
- مقایسه ریسک‌ها در حوزه‌های مختلف
- مقایسه نقاط قوت و موفقیت‌ها
- مقایسه نقاط قابل بهبود و ضعف‌ها
- در صورت وجود داده، مقایسه وضعیت منابع انسانی IT و بودجه IT
- جمع‌بندی مدیریتی و پیشنهادهای عملی برای بهبود هر دو شرکت
USR;

        return [
            'system'           => $systemMessage,
            'user'             => $userMessage,
            'context_a_text'   => $contextAText,
            'context_b_text'   => $contextBText,
            'context_a_blocks' => $contextABlocks,
            'context_b_blocks' => $contextBBlocks,
        ];
    }
}
