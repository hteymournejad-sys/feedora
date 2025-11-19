<?php

namespace App\Services\AI;

use App\Services\LLM\RAG\CompanyProfileModule;

class CompanyQaService
{
    protected CompanyProfileModule $companyProfileModule;

    public function __construct(CompanyProfileModule $companyProfileModule)
    {
        $this->companyProfileModule = $companyProfileModule;
    }

    /**
     * ساخت پرومپت کامل برای مدل هوش مصنوعی
     *
     * ورودی:
     *   - $question : متن سؤال کاربر
     *   - $company  : نام یا شناسه شرکت (مثلاً "رایتل" یا 86)
     *
     * خروجی:
     *   - [
     *       'system'         => string (پیام سیستمی برای مدل),
     *       'user'           => string (پیام کاربر برای مدل),
     *       'context_text'   => string (متن کانتکست ترکیبی),
     *       'context_blocks' => array  (لیست بلوک‌ها برای دیباگ / نمایش)
     *     ]
     */
    public function buildPromptForCompanyQuestion(string $question, $company): array
    {
        // 1) ساخت کانتکست از ماژول پروفایل شرکت
        $contextResult = $this->companyProfileModule->buildContextForQuestion($question, $company);

        $contextText   = $contextResult['context_text'] ?? '';
        $contextBlocks = $contextResult['context_blocks'] ?? [];

        // 2) پیام سیستمی (role: system)
        $systemMessage = <<<SYS
شما یک دستیار هوش مصنوعی متخصص در تحلیل وضعیت فناوری اطلاعات، تحول دیجیتال و هوشمندسازی در سازمان‌های بزرگ هستید.
خروجی شما باید تحلیلی، دقیق، مبتنی بر داده و قابل استفاده در گزارش‌های مدیریتی باشد.
در تحلیل‌های خود:
- به امتیازها، روندها، سطح بلوغ، ریسک‌ها و نقاط قوت توجه کنید.
- در صورت وجود، وضعیت منابع انسانی IT، بودجه و آموزش را نیز در نظر بگیرید.
- نتیجه را به زبان فارسی رسمی و قابل ارائه به مدیران ارشد سازمان بیان کنید.
SYS;

        // 3) پیام کاربر (role: user) که شامل سؤال + کانتکست است
        $userMessage = <<<USR
سؤال کاربر:
{$question}

اطلاعات زمینه‌ای درباره شرکت (آمتیازها، روند، ریسک‌ها، منابع انسانی IT و بودجه):
{$contextText}

بر اساس اطلاعات فوق، یک پاسخ تحلیلی، منسجم و قابل استفاده در تصمیم‌گیری مدیریتی ارائه بده.
USR;

        return [
            'system'         => $systemMessage,
            'user'           => $userMessage,
            'context_text'   => $contextText,
            'context_blocks' => $contextBlocks,
        ];
    }
}
