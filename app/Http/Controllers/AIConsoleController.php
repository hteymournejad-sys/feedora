<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LLM\LlamaClient;
use App\Services\LLM\RAG\SimpleRetriever;
use App\Services\LLM\RAG\UniversalRetriever; // به‌جای SimpleRetriever
use App\Services\AI\CompanyQaService;
use App\Services\AI\CompanyAiChatService;
use App\Services\AI\CompanyComparisonChatService;
use App\Models\AiEvaluationSummary;
use App\Services\AI\CompanyAssistantService;
use App\Services\AI\LstmClient;

class AIConsoleController extends Controller
{
    public function index()
    {
        return view('ai.console');
    }

    public function query(Request $request, LlamaClient $llm, UniversalRetriever $retriever)
{
set_time_limit(300);
    ini_set('max_execution_time', 300);

    $request->validate(['question' => 'required|string|min:2']);
    $question = trim($request->input('question'));

    $contextBlocks = $retriever->retrieve($question);  // پوشش همهٔ جداول
    $prompt = $this->buildPrompt($question, $contextBlocks);

    $responseText = $llm->chat([
        'role' => 'user',
        'content' => $prompt,
    ]);

    return response()->json([
        'ok' => true,
        'question' => $question,
        'context_used' => $contextBlocks,
        'answer' => $responseText,
    ]);
}

    private function buildPrompt(string $question, array $contexts): string
    {
set_time_limit(300);
    ini_set('max_execution_time', 300);
        $header = <<<EOT
شما یک دستیار تحلیلی هستید که باید هم توضیح بدهید هم محاسبه کنید.
- زبان پاسخ: فارسی رسمی و موجز.
- اگر نیاز به محاسبه است، مرحله‌به‌مرحله بنویسید و عدد نهایی بدهید.
- فقط از «اطلاعات زمینه‌ای زیر» برای استناد استفاده کن. اگر چیز مهمی در زمینه نبود، با صراحت بگو.

[سؤال کاربر]
{$question}

[اطلاعات زمینه‌ای ساختاریافته از دیتابیس فیدورا]
EOT;

        $blocks = '';
        foreach ($contexts as $ctx) {
            $title = $ctx['title'] ?? 'بلوک اطلاعاتی';
            $content = $ctx['content'] ?? '';
            $blocks .= "\n\n### {$title}\n{$content}";
        }

        $guidance = <<<EOG

[راهنما]
- اگر سؤال به یک شرکت/هلدینگ خاص مربوط است، فقط از داده‌های همان استفاده کن.
- برای درصدها و میانگین‌ها از داده‌های زمینه استفاده کن (نه حدس).
- در پایان، یک «جمع‌بندی یک‌خطی مدیریتی» بده.

پایان زمینه.
EOG;

        return $header . $blocks . $guidance;
    }

public function companyQuestion(Request $request, CompanyAiChatService $companyAiChat)
{

set_time_limit(300);
    ini_set('max_execution_time', 300);

    $request->validate([
        'question' => 'required|string',
        'company'  => 'required',
    ]);

    $question = $request->input('question');
    $company  = $request->input('company');

    // سوال + شرکت → پاسخ مدل + اطلاعات RAG
    $result = $companyAiChat->answerCompanyQuestion($question, $company);

    return response()->json([
        'status'   => 'ok',
        'question' => $question,
        'company'  => $company,
        'answer'   => $result['answer'],   // متن نهایی برای نمایش به کاربر
        'system'   => $result['system'],   // فقط برای دیباگ
        'user'     => $result['user'],     // پرومپت کامل (سؤال + context)
        'context'  => $result['context'],  // متن زمینه خلاصه
        'blocks'   => $result['blocks'],   // بلوک‌های ساختاریافته
    ]);
}

public function companyCompare(Request $request, CompanyComparisonChatService $comparisonChat)
{
set_time_limit(300);
    ini_set('max_execution_time', 300);

    $request->validate([
        'question'  => 'required|string',
        'company_a' => 'required',
        'company_b' => 'required',
    ]);

    $question = $request->input('question');
    $companyA = $request->input('company_a');
    $companyB = $request->input('company_b');

    $result = $comparisonChat->answerComparisonQuestion($question, $companyA, $companyB);

    return response()->json([
        'status'      => 'ok',
        'question'    => $question,
        'company_a'   => $companyA,
        'company_b'   => $companyB,
        'answer'      => $result['answer'],          // متن نهایی برای نمایش
        'system'      => $result['system'],          // برای دیباگ
        'user'        => $result['user'],            // پرومپت کامل
        'context_a'   => $result['context_a_text'],  // کانتکست شرکت اول
        'context_b'   => $result['context_b_text'],  // کانتکست شرکت دوم
        'blocks_a'    => $result['context_a_blocks'],
        'blocks_b'    => $result['context_b_blocks'],
    ]);
}

public function chatConsole(Request $request, CompanyAssistantService $assistant)
{
set_time_limit(300);
    ini_set('max_execution_time', 300);

    // لیست شرکت‌ها از ai_evaluation_summary
    $companies = AiEvaluationSummary::select('company_id', 'company_alias')
        ->distinct()
        ->orderBy('company_alias')
        ->get();

    $answer    = null;
    $scenario  = null;
    $question  = null;
    $companyA  = null;
    $companyB  = null;
    $contextA  = null;
    $contextB  = null;
    $blocksA   = null;
    $blocksB   = null;

    if ($request->isMethod('post')) {
        $request->validate([
            'question'  => 'required|string',
            'company_a' => 'required',
            // company_b اختیاری است؛ اگر پر شود می‌شود مقایسه‌ای
        ]);

        $question = $request->input('question');
        $companyA = $request->input('company_a');
        $companyB = $request->input('company_b') ?: null;

        $result   = $assistant->handleQuestion($question, $companyA, $companyB);

        $scenario = $result['scenario'];
        $answer   = $result['answer'];

        $contextA = $result['context_a'] ?? null;
        $contextB = $result['context_b'] ?? null;
        $blocksA  = $result['blocks_a'] ?? null;
        $blocksB  = $result['blocks_b'] ?? null;
    }

    return view('ai.chat_console', compact(
        'companies',
        'answer',
        'scenario',
        'question',
        'companyA',
        'companyB',
        'contextA',
        'contextB',
        'blocksA',
        'blocksB'
    ));
}
public function lstmPredict(Request $request, LstmClient $lstmClient)
{
    set_time_limit(60);
    ini_set('max_execution_time', 60);

    $request->validate([
        'company_id' => 'required|integer',
    ]);

    $companyId = (int) $request->input('company_id');

    $result = $lstmClient->predictNextEvaluation($companyId);

    if (!$result) {
        return response()->json([
            'status'  => 'error',
            'message' => 'امکان دریافت پیش‌بینی از سرویس LSTM وجود ندارد.',
        ], 500);
    }

    return response()->json([
        'status' => 'ok',
        'data'   => $result,
    ]);
}
public function lstmExplain(Request $request, LstmClient $lstmClient, LlamaClient $llm)
{
    set_time_limit(120);
    ini_set('max_execution_time', 120);

    $request->validate([
        'company_id' => 'required|integer',
    ]);

    $companyId = (int) $request->input('company_id');

    // ۱) گرفتن پیش‌بینی خام از سرویس LSTM
    $forecast = $lstmClient->predictNextEvaluation($companyId);

    if (!$forecast) {
        return response()->json([
            'status'  => 'error',
            'message' => 'امکان دریافت پیش‌بینی از سرویس LSTM وجود ندارد.',
        ], 500);
    }

    // ۲) ساخت پرامپت برای توضیح مدیریتی
    $score   = $forecast['predicted_final_score_next'] ?? null;
    $level   = $forecast['predicted_maturity_level_next'] ?? null;

    $prompt = <<<EOT
شما یک تحلیل‌گر ارشد ارزیابی بلوغ فناوری اطلاعات هستید.
خروجی یک مدل پیش‌بینی (LSTM) برای «ارزیابی بعدی» یک شرکت به شرح زیر است:

- شناسه شرکت: {$companyId}
- امتیاز نهایی پیش‌بینی‌شده در ارزیابی بعدی: {$score} از 100
- سطح بلوغ پیش‌بینی‌شده در ارزیابی بعدی: سطح {$level} از 5

لطفاً موارد زیر را به زبان فارسی رسمی و قابل فهم برای مدیرعامل بنویس:

1. یک پاراگراف 3 تا 5 جمله‌ای که این نتایج را تفسیر کند (مثلاً نسبت به وضعیت معمول امتیاز بالا/پایین است و چه معنایی برای عملکرد کلی دارد).
2. اگر سطح بلوغ پایین است (1 یا 2)، به‌صورت محترمانه به ضرورت برنامه‌ی بهبود اشاره کن.
3. اگر سطح بلوغ متوسط یا خوب است (3 تا 5)، به حفظ دستاوردها و تمرکز بر نقاط قابل بهبود اشاره کن.
4. در انتها، یک «جمع‌بندی یک‌خطی مدیریتی» ارائه کن.

دقت کن:
- فقط براساس همین اعداد صحبت کن و از خودت داده‌ی جدید نساز.
- جواب را در قالب متن ساده فارسی برگردان، بدون بولت‌پوینت Markdown.
EOT;

    // ۳) گرفتن توضیح از LLM
    $explanation = $llm->chat([
        'role'    => 'user',
        'content' => $prompt,
    ]);

    return response()->json([
        'status'      => 'ok',
        'company_id'  => $companyId,
        'forecast'    => $forecast,    // اعداد خام LSTM
        'explanation' => $explanation, // متن تحلیلی
    ]);
}
public function chatAjax(Request $request, CompanyAssistantService $assistant)
{
    set_time_limit(300);
    ini_set('max_execution_time', 300);

    try {
        $validated = $request->validate([
            'company_a' => 'required|string',
            'company_b' => 'nullable|string',
            'question'  => 'required|string',
        ]);

        $companyA = $validated['company_a'];
        $companyB = $validated['company_b'] ?? null;
        $question = $validated['question'];

        // خروجی دستیار (می‌تواند آرایه‌ی غنی یا حتی فقط یک رشته باشد)
        $rawResult = $assistant->handleQuestion($question, $companyA, $companyB);

        $answer   = '';
        $scenario = null;
        $contextA = null;
        $contextB = null;

        if (is_array($rawResult)) {
            $answer   = $rawResult['answer']   ?? '';
            $scenario = $rawResult['scenario'] ?? null;

            // نام کلیدها را با خروجی واقعی سرویس هماهنگ کردیم
            $contextA = $rawResult['context_a'] ?? ($rawResult['contextA'] ?? null);
            $contextB = $rawResult['context_b'] ?? ($rawResult['contextB'] ?? null);
        } else {
            // اگر سرویس فقط متن برگرداند
            $answer = (string) $rawResult;
        }

        return response()->json([
            'status'   => 'ok',
            'answer'   => $answer,
            'scenario' => $scenario,
            'contextA' => $contextA,
            'contextB' => $contextB,
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // خطای ولیدیشن → ۴۲۲ با JSON
        return response()->json([
            'status' => 'validation_error',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Throwable $e) {
        \Log::error('AI chatAjax error', [
            'msg'   => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'status'  => 'server_error',
            'message' => 'اشکال داخلی در پردازش پرسش رخ داد.',
        ], 500);
    }
}

}
