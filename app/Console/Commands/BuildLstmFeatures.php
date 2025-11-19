<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\AiEvaluationSummary;
use App\Models\AiLstmFeature;
use Carbon\Carbon;

class BuildLstmFeatures extends Command
{
    protected $signature = 'ai:lstm-build-features
                            {--company_id= : فقط پردازش یک شرکت خاص}
                            {--rebuild-features : بازتولید features از Summary (در غیر این صورت دست نمی‌زنیم)}';

    protected $description = 'همگام‌سازی ai_lstm_features با ai_evaluation_summary و پر کردن targets برای دوره بعد';

    public function handle()
    {
        $companyId       = $this->option('company_id');
        $rebuildFeatures = (bool) $this->option('rebuild-features');

        // لیست شرکت‌ها
        if ($companyId) {
            $companyIds = [(int) $companyId];
        } else {
            $companyIds = AiEvaluationSummary::query()
                ->select('company_id')
                ->distinct()
                ->pluck('company_id')
                ->toArray();
        }

        if (empty($companyIds)) {
            $this->warn('هیچ شرکتی در ai_evaluation_summary یافت نشد.');
            return 0;
        }

        $this->info('شروع پردازش LSTM Features ...');

        DB::beginTransaction();

        try {
            foreach ($companyIds as $cid) {
                $this->info("→ شرکت {$cid}");
                $this->buildForCompany($cid, $rebuildFeatures);
            }

            DB::commit();
            $this->info('✅ عملیات با موفقیت انجام شد.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('❌ خطا: ' . $e->getMessage());
            report($e);
        }

        return 0;
    }

    /**
     * ساخت / آپدیت رکوردهای ai_lstm_features برای یک شرکت
     */
    protected function buildForCompany(int $companyId, bool $rebuildFeatures = false): void
    {
        // ۱) ارزیابی‌های این شرکت از جدول Summary
        $summaries = AiEvaluationSummary::where('company_id', $companyId)
            ->orderBy('evaluation_date')
            ->get();

        if ($summaries->isEmpty()) {
            $this->line("   - هیچ ارزیابی برای این شرکت وجود ندارد.");
            return;
        }

        $this->line("   - تعداد ارزیابی‌ها: {$summaries->count()}");

        $lstmRows = [];

        // ۲) برای هر Summary، یک رکورد در ai_lstm_features می‌سازیم / آپدیت می‌کنیم
        foreach ($summaries as $index => $summary) {
            /** @var \App\Models\AiLstmFeature $row */
            $row = AiLstmFeature::firstOrNew([
                'company_id'          => $summary->company_id,
                'assessment_group_id' => $summary->assessment_group_id,
            ]);

            // تاریخ و time_index همیشه آپدیت شود (تا مرتب بماند)
            $row->evaluation_date = $summary->evaluation_date;
            $row->time_index      = $index; // 0,1,2,...

            // اگر می‌خواهیم features را دوباره بسازیم، یا رکورد جدید است
            if ($rebuildFeatures || !$row->exists || empty($row->features)) {
                $row->final_score = $summary->final_score;

                // این فیلدها در جدول ai_lstm_features تعریف شده‌اند، پس می‌توانیم پرشان کنیم.
                $row->f_score_it_governance         = $summary->score_it_governance;
                $row->f_score_info_security         = $summary->score_info_security;
                $row->f_score_infrastructure        = $summary->score_infrastructure;
                $row->f_score_it_support            = $summary->score_it_support;
                $row->f_score_applications          = $summary->score_applications;
                $row->f_score_digital_transformation= $summary->score_digital_transformation;
                $row->f_score_intelligence          = $summary->score_intelligence;

                // سطح بلوغ در سطوح ۱ تا ۵ (اگر خواستی بعداً در LSTM استفاده کنی)
                $row->f_maturity_1 = $summary->maturity_level_1_avg;
                $row->f_maturity_2 = $summary->maturity_level_2_avg;
                $row->f_maturity_3 = $summary->maturity_level_3_avg;
                $row->f_maturity_4 = $summary->maturity_level_4_avg;
                $row->f_maturity_5 = $summary->maturity_level_5_avg;

                // ریسک‌ها و نقاط قوت / قابل بهبود
                $row->f_risk_high        = $summary->risk_high_count;
                $row->f_risk_medium      = $summary->risk_medium_count;
                $row->f_risk_low         = $summary->risk_low_count;
                $row->f_strength_count   = $summary->strength_count;
                $row->f_improvement_count= $summary->improvement_count;

                // JSON features برای استفاده در LSTM و RAG
                $row->features = [
                    'final_score'            => $summary->final_score,
                    'overall_maturity_level' => $summary->overall_maturity_level,
                    'score_it_governance'    => $summary->score_it_governance,
                    'score_info_security'    => $summary->score_info_security,
                    'score_infrastructure'   => $summary->score_infrastructure,
                    'score_it_support'       => $summary->score_it_support,
                    'score_applications'     => $summary->score_applications,
                    'score_digital_transformation' => $summary->score_digital_transformation,
                    'score_intelligence'     => $summary->score_intelligence,
                    'maturity_level_1_avg'   => $summary->maturity_level_1_avg,
                    'maturity_level_2_avg'   => $summary->maturity_level_2_avg,
                    'maturity_level_3_avg'   => $summary->maturity_level_3_avg,
                    'maturity_level_4_avg'   => $summary->maturity_level_4_avg,
                    'maturity_level_5_avg'   => $summary->maturity_level_5_avg,
                    'risk_high_count'        => $summary->risk_high_count,
                    'risk_medium_count'      => $summary->risk_medium_count,
                    'risk_low_count'         => $summary->risk_low_count,
                    'strength_count'         => $summary->strength_count,
                    'improvement_count'      => $summary->improvement_count,
                    'total_questions'        => $summary->total_questions,
                    'answered_questions'     => $summary->answered_questions,
                ];
            }

            // در این مرحله، هدف دوره بعد را خالی می‌گذاریم، بعداً در حلقه جدا پر می‌کنیم
            $row->target_final_score    = null;
            $row->target_maturity_level = null;

            $row->save();

            $lstmRows[] = $row;
        }

         // --- حالا targets (دوره بعد) را برای هر رکورد تنظیم می‌کنیم ---
        $this->fillNextTargets($lstmRows, $summaries);

        // --- و سپس هدف‌های بلندمدت 2 / 3 / 5 ساله را پر می‌کنیم ---
        $this->fillMultiYearTargets($lstmRows, $summaries);

    }

    /**
     * پر کردن target_final_score و target_maturity_level بر اساس ارزیابی دوره بعد
     */
    protected function fillNextTargets(array $lstmRows, $summaries): void
    {
        $count = count($lstmRows);

        for ($i = 0; $i < $count - 1; $i++) {
            /** @var \App\Models\AiLstmFeature $currentRow */
            $currentRow = $lstmRows[$i];
            $nextSummary = $summaries[$i + 1];

            $currentRow->target_final_score    = $nextSummary->final_score;
            $currentRow->target_maturity_level = $nextSummary->overall_maturity_level;

            $currentRow->save();
        }

        // آخرین رکورد هر شرکت target دوره بعد ندارد (null می‌ماند)
    }


 /**
     * پر کردن تارگت‌های 2 / 3 / 5 ساله
     *
     * برای هر رکورد LSTM (هر ارزیابی)، بر اساس تاریخ ارزیابی و
     * ارزیابی‌های آینده، مقادیر زیر را تنظیم می‌کنیم:
     *
     * - target_score_info_security_y2/y3/y5
     * - target_final_score_y2/y3/y5
     * - target_risk_high_y2/y3/y5
     * - target_strength_count_y2/y3/y5
     * - target_maturity_level_y2/y3/y5
     *
     * @param \App\Models\AiLstmFeature[]                $lstmRows
     * @param \Illuminate\Support\Collection|\App\Models\AiEvaluationSummary[] $summaries
     */
    protected function fillMultiYearTargets(array $lstmRows, $summaries): void
    {
        // نگاشت سال به تعداد ماه
        $horizons = [
            'y2' => 24,
            'y3' => 36,
            'y5' => 60,
        ];

        $count = count($lstmRows);

        for ($i = 0; $i < $count; $i++) {
            /** @var \App\Models\AiLstmFeature $currentRow */
            $currentRow    = $lstmRows[$i];
            $currentSummary = $summaries[$i];

            $currentDate = $currentSummary->evaluation_date instanceof Carbon
                ? $currentSummary->evaluation_date
                : Carbon::parse($currentSummary->evaluation_date);

            // برای هر افق زمانی، Summary هدف را پیدا می‌کنیم
            foreach ($horizons as $suffix => $months) {
                $targetDate = $currentDate->copy()->addMonths($months);

                // اولین ارزیابی که تاریخش >= targetDate باشد
                $targetSummary = $summaries->first(function ($s) use ($targetDate) {
                    $d = $s->evaluation_date instanceof Carbon
                        ? $s->evaluation_date
                        : Carbon::parse($s->evaluation_date);

                    return $d->greaterThanOrEqualTo($targetDate);
                });

                if (!$targetSummary) {
                    // اگر ارزیابی‌ای در این افق نداریم، چیزی ست نمی‌کنیم (null می‌ماند)
                    continue;
                }

                // امتیاز امنیت اطلاعات در آن افق
                $fieldScoreInfoSecurity = "target_score_info_security_{$suffix}";
                // امتیاز نهایی
                $fieldFinalScore        = "target_final_score_{$suffix}";
                // تعداد ریسک‌های High
                $fieldRiskHigh          = "target_risk_high_{$suffix}";
                // تعداد نقاط قوت
                $fieldStrengthCount     = "target_strength_count_{$suffix}";
                // سطح بلوغ کلی
                $fieldMaturityLevel     = "target_maturity_level_{$suffix}";

                // تنظیم مقادیر بر اساس AiEvaluationSummary
                $currentRow->{$fieldScoreInfoSecurity} = $targetSummary->score_info_security;
                $currentRow->{$fieldFinalScore}        = $targetSummary->final_score;
                $currentRow->{$fieldRiskHigh}          = $targetSummary->risk_high_count;
                $currentRow->{$fieldStrengthCount}     = $targetSummary->strength_count;
                $currentRow->{$fieldMaturityLevel}     = $targetSummary->overall_maturity_level;

                // می‌توانی در صورت نیاز، ریسک‌های Medium/Low یا سایر حوزه‌ها را هم مشابه اضافه کنی.
            }

            $currentRow->save();
        }
    }
}
