<?php

namespace App\Services\LLM\RAG;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UniversalRetriever
{
    /** حداکثر بلاک‌های زمینه‌ای که تولید می‌کنیم */
    private int $maxBlocks = 12;

    /** رجیستری ماژول‌های بازیابی بر اساس نیت/موضوع */
    private array $registry;

    public function __construct()
    {
        $this->registry = [
            // عملکرد و امتیازها (فنی)
            'scores.latest'    => fn($ctx) => $this->blockLatestFinalScores($ctx),              // final_scores + users :contentReference[oaicite:1]{index=1}
            'scores.byCompany' => fn($ctx) => $this->blockScoresByCompany($ctx),               // final_scores + users + assessment_groups :contentReference[oaicite:2]{index=2}
            'scores.byDomain'  => fn($ctx) => $this->blockDomainAverages($ctx),                // answers (AVG by domain) :contentReference[oaicite:3]{index=3}

            // سنجه‌های غیر فنی
            'kpis.nontech'     => fn($ctx) => $this->blockNonTechnicalKPIs($ctx),              // non_technical_assessments + users :contentReference[oaicite:4]{index=4}
            'kpis.headcount'   => fn($ctx) => $this->blockITHeadcount($ctx),                   // it_personnel + users :contentReference[oaicite:5]{index=5}

            // تراکنش و اعتبارات
            'billing.payments' => fn($ctx) => $this->blockPayments($ctx),                      // payments + users :contentReference[oaicite:6]{index=6}
            'billing.credit'   => fn($ctx) => $this->blockCreditSettings($ctx),                // credit_settings :contentReference[oaicite:7]{index=7}

            // محتوا/بازخورد
            'content.posts'    => fn($ctx) => $this->blockRecentPosts($ctx),                   // posts + categories + users (تلویحاً) :contentReference[oaicite:8]{index=8}
            'content.comments' => fn($ctx) => $this->blockRecentComments($ctx),                // comments + posts + users :contentReference[oaicite:9]{index=9}
            'content.suggests' => fn($ctx) => $this->blockSuggestions($ctx),                   // suggestions + users :contentReference[oaicite:10]{index=10}

            // ایمنی/رخداد
            'sec.logs'         => fn($ctx) => $this->blockSecurityLogs($ctx),                  // security_logs + users :contentReference[oaicite:11]{index=11}

            // گروه ارزیابی/وضعیت
            'ag.status'        => fn($ctx) => $this->blockAssessmentGroups($ctx),              // assessment_groups + users :contentReference[oaicite:12]{index=12}
            'assess.meta'      => fn($ctx) => $this->blockAssessmentsMeta($ctx),               // assessments (status, performance_percentage, dates) :contentReference[oaicite:13]{index=13}
        ];
    }

    /** ورودی: سؤال | خروجی: بلوک‌های زمینه‌ای ساختاریافته */
    public function retrieve(string $question): array
    {
        $ctx = $this->analyze($question); // تشخیص شرکت/دامنه/بازه/کلیدواژه‌ها
        $blocks = [];

        // ترتیب اجرای ماژول‌ها بر مبنای نیت‌ها
        $intents = $this->inferIntents($ctx);

        foreach ($intents as $intent) {
            if (isset($this->registry[$intent])) {
                $res = ($this->registry[$intent])($ctx);
                foreach ($res as $b) {
                    $blocks[] = $b;
                    if (count($blocks) >= $this->maxBlocks) break 2;
                }
            }
        }

        // اگر چیز خاصی پیدا نشد، چند ماژول عمومی را می‌آوریم
        if (empty($blocks)) {
            foreach (['scores.latest','kpis.nontech','content.posts'] as $fallback) {
                $res = ($this->registry[$fallback])($ctx);
                $blocks = array_merge($blocks, $res);
                if (count($blocks) >= $this->maxBlocks) break;
            }
        }

        return $blocks;
    }

    // ---------------- Core: Intent & Context Analysis ----------------

    private function analyze(string $q): array
    {
        $qNorm = mb_strtolower($q,'UTF-8');
        $tokens = preg_split('/[\s,;؛،\.]+/u', $qNorm) ?: [];
        $company = $this->guessCompany($qNorm);
        $domain  = $this->guessDomain($qNorm);
        $time    = $this->guessTimeRange($qNorm);

        $metrics = [
            'avg' => Str::contains($qNorm, ['میانگین','average','avg','میانگین‌گیری']),
            'sum' => Str::contains($qNorm, ['جمع','sum','total']),
            'trend' => Str::contains($qNorm, ['روند','trend','زمان','سال','ماه']),
            'percent'=> Str::contains($qNorm, ['درصد','percentage','percent']),
        ];

        return compact('q','qNorm','tokens','company','domain','time','metrics');
    }

    /** اولویت نیت‌ها را بر اساس متن سؤال می‌چیند */
    private function inferIntents(array $ctx): array
    {
        $intents = [];

        // نیت‌های عملکرد و دامنه
        if ($ctx['domain']) {
            $intents[] = 'scores.byDomain';
        }
        if ($ctx['company']) {
            $intents[] = 'scores.byCompany';
            $intents[] = 'kpis.nontech';
            $intents[] = 'kpis.headcount';
        }

        // اگر واژه‌های پرداخت/اعتبار هست:
        if (Str::contains($ctx['qNorm'], ['پرداخت','فاکتور','صورتحساب','payment','invoice','اعتبار'])) {
            $intents[] = 'billing.payments';
            $intents[] = 'billing.credit';
        }

        // محتوا/بازخورد
        if (Str::contains($ctx['qNorm'], ['مقاله','پست','خبر','وبلاگ','نظر','کامنت','پیشنهاد'])) {
            $intents[] = 'content.posts';
            $intents[] = 'content.comments';
            $intents[] = 'content.suggests';
        }

        // امنیت/رخداد
        if (Str::contains($ctx['qNorm'], ['امنیت','security','log','رخداد','incident'])) {
            $intents[] = 'sec.logs';
        }

        // وضعیت ارزیابی‌ها
        if (Str::contains($ctx['qNorm'], ['گروه ارزیابی','assessment group','وضعیت ارزیابی','finalized','draft'])) {
            $intents[] = 'ag.status';
            $intents[] = 'assess.meta';
        }

        // عمومی
        $intents[] = 'scores.latest';
        $intents = array_values(array_unique($intents));
        return $intents;
    }

    // ---------------- Company / Domain / Time Guess ----------------

    private function guessCompany(string $qNorm): ?string
    {
        // ساده: جست‌وجو روی لیست از users.company_alias
        try {
            $candidates = DB::table('users')->select('company_alias')->limit(200)->pluck('company_alias')->toArray(); // :contentReference[oaicite:14]{index=14}
            foreach ($candidates as $alias) {
                $a = mb_strtolower($alias,'UTF-8');
                if ($a && Str::contains($qNorm, $a)) return $alias;
            }
        } catch (\Throwable $e) {}
        // واژه‌های پرتکرار حوزه شما هم می‌تواند کمک کند:
        foreach (['تاپیکو','شستا','تیپیکو','ایرانول','رایتل','رازک','فن‌آوران','توفیق دارو','بارز','تکام'] as $hint) {
            if (Str::contains($qNorm, mb_strtolower($hint,'UTF-8'))) return $hint;
        }
        return null;
    }

    private function guessDomain(string $qNorm): ?string
    {
        $domains = DB::table('domain_weights')->pluck('domain')->toArray(); // :contentReference[oaicite:15]{index=15}
        foreach ($domains as $d) {
            $dd = mb_strtolower($d,'UTF-8');
            if ($dd && Str::contains($qNorm, $dd)) return $d;
        }
        return null;
    }

    private function guessTimeRange(string $qNorm): ?array
    {
        // نمونهٔ ساده: اگر «سال 2024/1402» دیده شد، یا «سه ماه اخیر»
        if (preg_match('/\b(20\d{2}|13\d{2}|14\d{2})\b/u', $qNorm, $m)) {
            $y = (int)$m[1];
            return ['mode'=>'year','value'=>$y];
        }
        if (Str::contains($qNorm, ['سه ماه','سه‌ماه','quarter'])) {
            return ['mode'=>'months','value'=>3];
        }
        return null;
    }

    private function limit(int $n = 10) { return max(1, min(50, $n)); }

    private function likeCompany($query, ?string $company)
    {
        if ($company) $query->where('users.company_alias','LIKE',"%{$company}%");
        return $query;
    }

    // ---------------- Blocks (each returns array of [title, content]) ----------------

    private function blockLatestFinalScores(array $ctx): array
    {
        if (!$this->has(['final_scores','assessment_groups','users'])) return [];
        $rows = DB::table('final_scores')
            ->select('final_scores.assessment_group_id','final_scores.final_score','users.company_alias','final_scores.updated_at')
            ->join('assessment_groups','assessment_groups.assessment_group_id','=','final_scores.assessment_group_id')
            ->join('users','users.id','=','assessment_groups.user_id')
            ->orderByDesc('final_scores.updated_at')
            ->limit($this->limit(12))
            ->get(); // :contentReference[oaicite:16]{index=16}

        if (!$rows->count()) return [];
        $lines = $rows->map(fn($r)=> "گروه {$r->assessment_group_id} ({$r->company_alias}) → امتیاز: {$r->final_score} | به‌روز: {$r->updated_at}")->implode("\n");
        return [[ 'title'=>'آخرین امتیازهای نهایی','content'=>$lines ]];
    }

    private function blockScoresByCompany(array $ctx): array
    {
        if (!$this->has(['final_scores','assessment_groups','users'])) return [];
        $q = DB::table('final_scores')
            ->select('final_scores.assessment_group_id','final_scores.final_score','final_scores.updated_at')
            ->join('assessment_groups','assessment_groups.assessment_group_id','=','final_scores.assessment_group_id')
            ->join('users','users.id','=','assessment_groups.user_id');
        $q = $this->likeCompany($q, $ctx['company'])
            ->orderByDesc('final_scores.updated_at')
            ->limit($this->limit(10));

        $rows = $q->get(); // :contentReference[oaicite:17]{index=17}
        if (!$rows->count()) return [];
        $lines = $rows->map(fn($r)=> "گروه {$r->assessment_group_id} → امتیاز: {$r->final_score} (به‌روز: {$r->updated_at})")->implode("\n");
        $title = $ctx['company'] ? "امتیازهای نهایی «{$ctx['company']}»" : "امتیازهای نهایی براساس شرکت";
        return [[ 'title'=>$title, 'content'=>$lines ]];
    }

    private function blockDomainAverages(array $ctx): array
    {
        if (!$this->has(['answers'])) return [];
        $q = DB::table('answers')
            ->select('answers.domain', DB::raw('AVG(answers.score) as avg_score'), DB::raw('COUNT(*) as n'));

        if ($ctx['company'] && $this->has(['assessments','users'])) {
            $q->join('assessments','assessments.id','=','answers.assessment_id')
              ->join('users','users.id','=','assessments.user_id')
              ->where('users.company_alias','LIKE','%'.$ctx['company'].'%');
        }
        if ($ctx['domain']) { $q->where('answers.domain','LIKE','%'.$ctx['domain'].'%'); }

        $rows = $q->groupBy('answers.domain')
                  ->orderByDesc('avg_score')
                  ->limit($this->limit(20))
                  ->get(); // :contentReference[oaicite:18]{index=18}

        if (!$rows->count()) return [];
        $lines = $rows->map(fn($r)=> "{$r->domain}: میانگین {$r->avg_score} (n={$r->n})")->implode("\n");
        $title = $ctx['company'] ? "میانگین نمرات دامین برای «{$ctx['company']}»" : "میانگین نمرات به تفکیک دامین";
        return [[ 'title'=>$title, 'content'=>$lines ]];
    }

    private function blockNonTechnicalKPIs(array $ctx): array
    {
        if (!$this->has(['non_technical_assessments'])) return [];
        $q = DB::table('non_technical_assessments')
            ->select('non_technical_assessments.*');

        if ($ctx['company'] && $this->has(['users'])) {
            $q->join('users','users.id','=','non_technical_assessments.user_id')
              ->where('users.company_alias','LIKE','%'.$ctx['company'].'%');
        }

        // اگر سال مشخص شد:
        if ($ctx['time'] && $ctx['time']['mode']==='year') {
            $q->where('non_technical_assessments.year', (int)$ctx['time']['value']);
        }

        $rows = $q->orderByDesc('non_technical_assessments.updated_at')
                  ->limit($this->limit(10))
                  ->get(); // :contentReference[oaicite:19]{index=19}

        if (!$rows->count()) return [];
        $lines = [];
        foreach ($rows as $r) {
            $alias = $this->has(['users']) ? (DB::table('users')->where('id',$r->user_id)->value('company_alias')) : $r->user_id; // :contentReference[oaicite:20]{index=20}
            $burn = ($r->it_budget && $r->it_expenditure) ? round(($r->it_expenditure/$r->it_budget)*100,2).'%' : 'نامشخص';
            $lines[] = "{$alias} ({$r->year}): کاربران‌فعال={$r->active_users}, ایستگاه‌ها={$r->workstations}, نیروی تمام‌وقت IT={$r->full_time_it_staff}, بودجه‌IT={$r->it_budget}, هزینه‌IT={$r->it_expenditure}, درصد مصرف≈{$burn}";
        }
        return [[ 'title'=>'نماگرهای غیر فنی', 'content'=>implode("\n",$lines) ]];
    }

    private function blockITHeadcount(array $ctx): array
    {
        if (!$this->has(['it_personnel'])) return [];
        $q = DB::table('it_personnel')
            ->select('it_personnel.full_name','it_personnel.education','it_personnel.position','it_personnel.work_experience','it_personnel.user_id');

        if ($ctx['company'] && $this->has(['users'])) {
            $q->join('users','users.id','=','it_personnel.user_id')
              ->where('users.company_alias','LIKE','%'.$ctx['company'].'%');
        }

        $rows = $q->orderByDesc('it_personnel.updated_at')->limit($this->limit(12))->get(); // :contentReference[oaicite:21]{index=21}
        if (!$rows->count()) return [];
        $lines = $rows->map(function($r){
            return "{$r->full_name} ({$r->position}, {$r->education}) — سابقه {$r->work_experience} سال";
        })->implode("\n");
        $title = $ctx['company'] ? "پرسنل IT «{$ctx['company']}»" : "نمونهٔ پرسنل IT";
        return [[ 'title'=>$title, 'content'=>$lines ]];
    }

    private function blockPayments(array $ctx): array
    {
        if (!$this->has(['payments'])) return [];
        $q = DB::table('payments')
            ->select('payments.invoice_number','payments.amount','payments.status','payments.payment_date','payments.user_id');

        if ($ctx['company'] && $this->has(['users'])) {
            $q->join('users','users.id','=','payments.user_id')
              ->where('users.company_alias','LIKE','%'.$ctx['company'].'%');
        }

        $rows = $q->orderByDesc('payments.payment_date')->limit($this->limit(10))->get(); // :contentReference[oaicite:22]{index=22}
        if (!$rows->count()) return [];
        $lines = $rows->map(fn($r)=> "فاکتور {$r->invoice_number} → مبلغ: {$r->amount} | وضعیت: {$r->status} | تاریخ: {$r->payment_date}")->implode("\n");
        return [[ 'title'=>'پرداخت‌ها', 'content'=>$lines ]];
    }

    private function blockCreditSettings(array $ctx): array
    {
        if (!$this->has(['credit_settings'])) return [];
        $row = DB::table('credit_settings')->orderByDesc('updated_at')->first(); // :contentReference[oaicite:23]{index=23}
        if (!$row) return [];
        $content = "مبلغ اعتبار: {$row->amount} تومان | مدت: {$row->days} روز | دفعات ارزیابی: {$row->evaluations}";
        return [[ 'title'=>'سیاست اعتباردهی', 'content'=>$content ]];
    }

    private function blockRecentPosts(array $ctx): array
    {
        if (!$this->has(['posts'])) return [];
        $rows = DB::table('posts')->select('title','slug','published_at')->orderByDesc('published_at')->limit($this->limit(6))->get(); // :contentReference[oaicite:24]{index=24}
        if (!$rows->count()) return [];
        $lines = $rows->map(fn($r)=> "{$r->title} — {$r->published_at}")->implode("\n");
        return [[ 'title'=>'آخرین پست‌ها', 'content'=>$lines ]];
    }

    private function blockRecentComments(array $ctx): array
    {
        if (!$this->has(['comments','posts'])) return [];
        $rows = DB::table('comments')
            ->select('comments.content','comments.created_at','comments.post_id')
            ->orderByDesc('comments.created_at')->limit($this->limit(6))->get(); // :contentReference[oaicite:25]{index=25}
        if (!$rows->count()) return [];
        $postTitles = [];
        foreach ($rows as $r) {
            $title = DB::table('posts')->where('id',$r->post_id)->value('title') ?: $r->post_id; // :contentReference[oaicite:26]{index=26}
            $postTitles[] = "روی «{$title}» — {$r->created_at}\n" . Str::limit(trim($r->content), 200);
        }
        return [[ 'title'=>'آخرین نظرات', 'content'=>implode("\n\n", $postTitles) ]];
    }

    private function blockSuggestions(array $ctx): array
    {
        if (!$this->has(['suggestions'])) return [];
        $rows = DB::table('suggestions')->select('suggestion_topic','suggestion_text','created_at')->orderByDesc('created_at')->limit($this->limit(6))->get(); // :contentReference[oaicite:27]{index=27}
        if (!$rows->count()) return [];
        $lines = $rows->map(fn($r)=> "موضوع: {$r->suggestion_topic} — {$r->created_at}\n" . Str::limit(trim($r->suggestion_text), 220))->implode("\n\n");
        return [[ 'title'=>'پیشنهادهای کاربران', 'content'=>$lines ]];
    }

    private function blockSecurityLogs(array $ctx): array
    {
        if (!$this->has(['security_logs'])) return [];
        $rows = DB::table('security_logs')->select('timestamp','user_id','action','details')->orderByDesc('timestamp')->limit($this->limit(10))->get(); // :contentReference[oaicite:28]{index=28}
        if (!$rows->count()) return [];
        $lines = $rows->map(fn($r)=> "{$r->timestamp} | کاربر={$r->user_id} | {$r->action} → ".Str::limit($r->details ?? '-', 160))->implode("\n");
        return [[ 'title'=>'گزارش‌های امنیتی اخیر', 'content'=>$lines ]];
    }

    private function blockAssessmentGroups(array $ctx): array
    {
        if (!$this->has(['assessment_groups','users'])) return [];
        $q = DB::table('assessment_groups')
              ->select('assessment_groups.assessment_group_id','assessment_groups.status','users.company_alias')
              ->join('users','users.id','=','assessment_groups.user_id');

        $q = $this->likeCompany($q, $ctx['company'])
            ->orderByDesc('assessment_groups.updated_at')->limit($this->limit(10));

        $rows = $q->get(); // :contentReference[oaicite:29]{index=29}
        if (!$rows->count()) return [];
        $lines = $rows->map(fn($r)=> "{$r->assessment_group_id} ({$r->company_alias}) → وضعیت: {$r->status}")->implode("\n");
        return [[ 'title'=>'وضعیت گروه‌های ارزیابی', 'content'=>$lines ]];
    }

    private function blockAssessmentsMeta(array $ctx): array
    {
        if (!$this->has(['assessments'])) return [];
        $q = DB::table('assessments')
            ->select('id','status','domain','performance_percentage','created_date','finalized_date');

        if ($ctx['domain']) $q->where('domain','LIKE','%'.$ctx['domain'].'%');
        if ($ctx['time'] && $ctx['time']['mode']==='year') {
            $q->whereYear('created_date', (int)$ctx['time']['value']);
        }

        $rows = $q->orderByDesc('updated_at')->limit($this->limit(10))->get(); // :contentReference[oaicite:30]{index=30}
        if (!$rows->count()) return [];
        $lines = $rows->map(fn($r)=> "ID={$r->id} | {$r->status} | {$r->domain} | %عملکرد={$r->performance_percentage} | از {$r->created_date} تا {$r->finalized_date}")->implode("\n");
        return [[ 'title'=>'فرادادهٔ ارزیابی‌ها', 'content'=>$lines ]];
    }

    // ---------------- Utils ----------------

    private function has(array $tables): bool
    {
        try {
            foreach ($tables as $t) {
                if (!DB::getSchemaBuilder()->hasTable($t)) return false;
            }
            return true;
        } catch (\Throwable $e) { return false; }
    }
}
