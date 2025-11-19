<?php

namespace App\Services\AI;

use App\Models\AiEvaluationSummary;
use App\Models\AiInsightItem;
use App\Models\AiLstmFeature;
use App\Models\NonTechnicalAssessment;
use App\Models\ItPersonnel;

class CompanyFullProfileService
{
    /**
     * تبدیل ورودی (id یا alias) به company_id
     */
    protected function resolveCompanyId($company): ?int
    {
        // اگر ورودی عدد باشد یعنی company_id
        if (is_numeric($company)) {
            return (int) $company;
        }

        // در غیر این صورت فرض می‌کنیم alias است
        $alias = trim($company);

        $record = AiEvaluationSummary::where('company_alias', $alias)
            ->orderByDesc('evaluation_date')
            ->first();

        return $record ? (int) $record->company_id : null;
    }

    //-----------------------------------------
    // 1) داده‌های فنی (Technical)
    //-----------------------------------------

    public function getTechnicalSummary($company)
    {
        $companyId = $this->resolveCompanyId($company);
        if (!$companyId) {
            return null;
        }

        return AiEvaluationSummary::where('company_id', $companyId)
            ->orderByDesc('evaluation_date')
            ->first();
    }

    public function getScoreTrend($company)
    {
        $companyId = $this->resolveCompanyId($company);
        if (!$companyId) {
            return collect();
        }

        return AiEvaluationSummary::where('company_id', $companyId)
            ->orderBy('evaluation_date')
            ->get(['evaluation_date', 'final_score']);
    }

    public function getMaturityTrend($company)
    {
        $companyId = $this->resolveCompanyId($company);
        if (!$companyId) {
            return collect();
        }

        return AiEvaluationSummary::where('company_id', $companyId)
            ->orderBy('evaluation_date')
            ->get(['evaluation_date', 'overall_maturity_level']);
    }

    public function getRiskStats($company): array
    {
        $companyId = $this->resolveCompanyId($company);
        if (!$companyId) {
            return ['high' => 0, 'medium' => 0, 'low' => 0];
        }

        $rows = AiEvaluationSummary::where('company_id', $companyId)->get();

        return [
            'high'   => (int) $rows->sum('risk_high_count'),
            'medium' => (int) $rows->sum('risk_medium_count'),
            'low'    => (int) $rows->sum('risk_low_count'),
        ];
    }

    public function getStrengthStats($company): array
    {
        $companyId = $this->resolveCompanyId($company);
        if (!$companyId) {
            return ['strength' => 0, 'improvement' => 0];
        }

        $rows = AiEvaluationSummary::where('company_id', $companyId)->get();

        return [
            'strength'    => (int) $rows->sum('strength_count'),
            'improvement' => (int) $rows->sum('improvement_count'),
        ];
    }

    //-----------------------------------------
    // 2) داده‌های متنی AI (Insights)
    //-----------------------------------------

    public function getInsightItems($company, ?string $type = null)
    {
        $companyId = $this->resolveCompanyId($company);
        if (!$companyId) {
            return collect();
        }

        $q = AiInsightItem::where('company_id', $companyId)
            ->orderByDesc('evaluation_date');

        if ($type) {
            $q->where('item_type', $type);
        }

        return $q->get();
    }

    //-----------------------------------------
    // 3) داده‌های غیر فنی IT (HR & Budget)
    //-----------------------------------------

    public function getNonTechnicalProfile($company)
    {
        $companyId = $this->resolveCompanyId($company);
        if (!$companyId) {
            return null;
        }

        return NonTechnicalAssessment::where('user_id', $companyId)
            ->orderByDesc('year')
            ->first();
    }

    public function getItPersonnelProfile($company)
    {
        $companyId = $this->resolveCompanyId($company);
        if (!$companyId) {
            return collect();
        }

        return ItPersonnel::where('user_id', $companyId)->get();
    }

    //-----------------------------------------
    // 4) ساخت کانتکست کامل خام
    //-----------------------------------------

    public function buildFullContext($company): array
    {
        $companyId       = $this->resolveCompanyId($company);

        $technicalSummary  = $this->getTechnicalSummary($company);
        $scoreTrend        = $this->getScoreTrend($company);
        $maturityTrend     = $this->getMaturityTrend($company);
        $riskStats         = $this->getRiskStats($company);
        $strengthStats     = $this->getStrengthStats($company);
        $nonTechnical      = $this->getNonTechnicalProfile($company);
        $itPersonnel       = $this->getItPersonnelProfile($company);

        return [
            'company_id'        => $companyId,
            'technical_summary' => $technicalSummary,
            'score_trend'       => $scoreTrend,
            'maturity_trend'    => $maturityTrend,
            'risk_stats'        => $riskStats,
            'strength_stats'    => $strengthStats,
            'insights'          => [
                'risks'        => $this->getInsightItems($company, 'risk'),
                'strengths'    => $this->getInsightItems($company, 'strength'),
                'improvements' => $this->getInsightItems($company, 'improvement'),
                'summaries'    => $this->getInsightItems($company, 'company_summary'),
            ],
            'non_technical'     => $nonTechnical,
            'it_personnel'      => $itPersonnel,
        ];
    }

    //-----------------------------------------
    // 5) ساخت بلوک‌های متنی برای LLM (RAG)
    //-----------------------------------------

    public function buildLlMBlocks($company): array
    {
        $ctx = $this->buildFullContext($company);

        if (!$ctx['company_id']) {
            return [[
                'title'   => 'اطلاعات در دسترس نیست',
                'content' => 'برای این شرکت هیچ داده‌ای در جداول ارزیابی فنی یا غیر فنی موجود نیست.',
            ]];
        }

        $blocks = [];

        // 1) خلاصه فنی
        if ($ctx['technical_summary']) {
            $t = $ctx['technical_summary'];

            $blocks[] = [
                'title'   => 'خلاصه وضعیت فنی و سطح بلوغ شرکت',
                'content' =>
                    "خلاصه آخرین ارزیابی فنی شرکت:\n".
                    "- تاریخ ارزیابی: ".$t->evaluation_date->format('Y-m-d')."\n".
                    "- امتیاز کل: ".(float) $t->final_score."\n".
                    "- سطح بلوغ کلی: ".(int) $t->overall_maturity_level."\n".
                    "- امتیاز حاکمیت IT: ".($t->score_it_governance ?? 'نامشخص')."\n".
                    "- امتیاز امنیت اطلاعات: ".($t->score_info_security ?? 'نامشخص')."\n".
                    "- امتیاز زیرساخت: ".($t->score_infrastructure ?? 'نامشخص')."\n".
                    "- امتیاز خدمات پشتیبانی: ".($t->score_it_support ?? 'نامشخص')."\n".
                    "- امتیاز سامانه‌های کاربردی: ".($t->score_applications ?? 'نامشخص')."\n".
                    "- امتیاز تحول دیجیتال: ".($t->score_digital_transformation ?? 'نامشخص')."\n".
                    "- امتیاز هوشمندسازی: ".($t->score_intelligence ?? 'نامشخص')."\n"
            ];
        }

        // 2) روند امتیاز و بلوغ
        if ($ctx['score_trend'] instanceof \Illuminate\Support\Collection && $ctx['score_trend']->count() > 0) {
            $scoreLines = [];
            foreach ($ctx['score_trend'] as $row) {
                $scoreLines[] = $row->evaluation_date->format('Y-m-d').": ".(float) $row->final_score;
            }

            $maturityLines = [];
            if ($ctx['maturity_trend'] instanceof \Illuminate\Support\Collection) {
                foreach ($ctx['maturity_trend'] as $row) {
                    $maturityLines[] = $row->evaluation_date->format('Y-m-d').": سطح ".(int) $row->overall_maturity_level;
                }
            }

            $blocks[] = [
                'title'   => 'روند امتیاز کل و سطح بلوغ در ارزیابی‌های مختلف',
                'content' =>
                    "روند امتیاز کل ارزیابی‌ها (به ترتیب زمان):\n".
                    implode("\n", $scoreLines)."\n\n".
                    "روند سطح بلوغ سازمان:\n".
                    implode("\n", $maturityLines)."\n"
            ];
        }

        // 3) آمار ریسک‌ها و نقاط قوت
        $risk = $ctx['risk_stats'] ?? ['high' => 0, 'medium' => 0, 'low' => 0];
        $str  = $ctx['strength_stats'] ?? ['strength' => 0, 'improvement' => 0];

        $blocks[] = [
            'title'   => 'آمار ریسک‌ها، نقاط قوت و فرصت‌های بهبود',
            'content' =>
                "بر اساس تمام ارزیابی‌های ثبت‌شده برای این شرکت:\n".
                "- تعداد ریسک‌های شدت بالا: ".($risk['high'] ?? 0)."\n".
                "- تعداد ریسک‌های شدت متوسط: ".($risk['medium'] ?? 0)."\n".
                "- تعداد ریسک‌های شدت پایین: ".($risk['low'] ?? 0)."\n".
                "- تعداد کل نقاط قوت: ".($str['strength'] ?? 0)."\n".
                "- تعداد کل نقاط قابل بهبود: ".($str['improvement'] ?? 0)."\n"
        ];

        // 4) ریسک‌ها، قوت‌ها، بهبودها (نمونه متنی)
        $riskItems     = $ctx['insights']['risks'] ?? collect();
        $strengthItems = $ctx['insights']['strengths'] ?? collect();
        $improvItems   = $ctx['insights']['improvements'] ?? collect();

        if ($riskItems instanceof \Illuminate\Support\Collection && $riskItems->count() > 0) {
            $topRisks = $riskItems->take(5)->map(function ($item) {
                $sev = $item->severity ? " (شدت: {$item->severity})" : '';
                return "- [{$item->domain} / {$item->subcategory}]{$sev}\n  ".$item->content;
            })->implode("\n\n");

            $blocks[] = [
                'title'   => 'نمونه‌ای از ریسک‌های مهم شناسایی‌شده',
                'content' => $topRisks,
            ];
        }

        if ($strengthItems instanceof \Illuminate\Support\Collection && $strengthItems->count() > 0) {
            $topStr = $strengthItems->take(5)->map(function ($item) {
                return "- [{$item->domain} / {$item->subcategory}]\n  ".$item->content;
            })->implode("\n\n");

            $blocks[] = [
                'title'   => 'نمونه‌ای از مهم‌ترین نقاط قوت شرکت',
                'content' => $topStr,
            ];
        }

        if ($improvItems instanceof \Illuminate\Support\Collection && $improvItems->count() > 0) {
            $topImp = $improvItems->take(5)->map(function ($item) {
                return "- [{$item->domain} / {$item->subcategory}]\n  ".$item->content;
            })->implode("\n\n");

            $blocks[] = [
                'title'   => 'نمونه‌ای از مهم‌ترین نقاط قابل بهبود',
                'content' => $topImp,
            ];
        }

        // 5) بلوک غیر فنی (بودجه، نفرات IT، آموزش)
        if ($ctx['non_technical']) {
            $nt = $ctx['non_technical'];

            $blocks[] = [
                'title'   => 'خلاصه وضعیت غیر فنی IT (بودجه، نفرات، آموزش)',
                'content' =>
                    "خلاصه اطلاعات غیر فنی IT برای سال {$nt->year}:\n".
                    "- تعداد کاربران فعال: ".($nt->active_users ?? 'نامشخص')."\n".
                    "- تعداد ایستگاه‌های کاری: ".($nt->workstations ?? 'نامشخص')."\n".
                    "- تعداد پرسنل IT تمام‌وقت: ".($nt->full_time_it_staff ?? 'نامشخص')."\n".
                    "- تعداد پرسنل IT پاره‌وقت: ".($nt->part_time_it_staff ?? 'نامشخص')."\n".
                    "- بودجه IT: ".($nt->it_budget ?? 'نامشخص')."\n".
                    "- هزینه‌کرد IT: ".($nt->it_expenditure ?? 'نامشخص')."\n".
                    "- ساعات آموزش IT: ".($nt->it_training_hours ?? 'نامشخص')."\n".
                    "- ساعات آموزش عمومی: ".($nt->general_training_hours ?? 'نامشخص')."\n"
            ];
        }

        // 6) خلاصه منابع انسانی IT
        if ($ctx['it_personnel'] instanceof \Illuminate\Support\Collection && $ctx['it_personnel']->count() > 0) {
            $pers   = $ctx['it_personnel'];
            $total  = $pers->count();
            $avgExp = round($pers->avg('work_experience') ?? 0, 1);

            $samples = $pers->take(5)->map(function ($p) {
                return "- {$p->full_name} ({$p->position}) – تحصیلات: {$p->education}, سابقه: {$p->work_experience} سال";
            })->implode("\n");

            $blocks[] = [
                'title'   => 'خلاصه ترکیب نیروی انسانی IT',
                'content' =>
                    "خلاصه وضعیت نیروی انسانی IT:\n".
                    "- تعداد کل نفرات IT: {$total}\n".
                    "- میانگین سابقه کاری (سال): {$avgExp}\n".
                    "نمونه‌ای از سمت‌ها و تخصص‌ها:\n".
                    $samples
            ];
        }

        return $blocks;
    }
}
