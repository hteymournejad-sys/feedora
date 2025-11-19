<?php

namespace App\Jobs;

use App\Http\Controllers\AssessmentController;
use App\Models\AiEvaluationSummary;
use App\Models\AiInsightItem;
use App\Models\AiLstmFeature;
use App\Models\Assessment;
use App\Models\AssessmentGroup;
use App\Models\FinalScore;
use App\Models\Question;
use App\Models\User;
use App\Models\Answer;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BuildAiTablesForAssessmentGroup
{
    use Dispatchable, SerializesModels;

    protected int $companyId;
    protected $assessmentGroupId;

    public function __construct(int $companyId, $assessmentGroupId)
    {
        $this->companyId = $companyId;
        $this->assessmentGroupId = $assessmentGroupId;
    }

    public function handle(): void
    {
        $user = User::find($this->companyId);
        if (!$user) {
            Log::warning('BuildAiTables: user not found', [
                'company_id' => $this->companyId,
                'assessment_group_id' => $this->assessmentGroupId,
            ]);
            return;
        }

        $group = AssessmentGroup::where('user_id', $user->id)
            ->where('assessment_group_id', $this->assessmentGroupId)
            ->first();

        if (!$group) {
            Log::warning('BuildAiTables: assessment group not found', [
                'company_id' => $user->id,
                'assessment_group_id' => $this->assessmentGroupId,
            ]);
            return;
        }

        $finalScore = FinalScore::where('assessment_group_id', $group->assessment_group_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$finalScore) {
            Log::warning('BuildAiTables: final score not found', [
                'company_id' => $user->id,
                'assessment_group_id' => $this->assessmentGroupId,
            ]);
            return;
        }

        $evaluationDate = $finalScore->created_at ?? $group->created_at ?? now();
        $periodLabel = $evaluationDate ? $evaluationDate->format('Y-m') : null;

        /** @var AssessmentController $controller */
        $controller = app(AssessmentController::class);

        // استفاده از منطق موجود در کنترلر
        $assessmentData = $controller->calculateAssessmentData($finalScore, $user->id);
        $maturityData   = $controller->calculateMaturityLevel($group->assessment_group_id, $user->id);

        $this->buildEvaluationSummary($user, $group, $finalScore, $evaluationDate, $periodLabel, $assessmentData, $maturityData);
        $this->buildInsightItems($user, $group, $evaluationDate);
        $this->buildLstmFeatures($user, $group, $evaluationDate);
    }

    protected function buildEvaluationSummary($user, $group, $finalScore, $evaluationDate, $periodLabel, $assessmentData, $maturityData): void
    {
        $labels       = $assessmentData->labels ?? [];
        $dataValues   = $assessmentData->dataValues ?? [];
        $subcategories = $assessmentData->subcategories ?? [];

        $domainScore = function (string $domain) use ($labels, $dataValues) {
            $index = array_search($domain, $labels, true);
            return $index !== false && isset($dataValues[$index]) ? (float) $dataValues[$index] : null;
        };

        $subcategoryScores = [];
        foreach ($subcategories as $domain => $items) {
            $subcategoryScores[$domain] = [];
            foreach ($items as $row) {
                $subcategoryScores[$domain][] = [
                    'name'        => $row['name'] ?? '',
                    'performance' => $row['performance'] ?? 0,
                ];
            }
        }

        $levelAverages        = $maturityData['levelAverages']        ?? [0, 0, 0, 0, 0];
        $overallMaturityLevel = $maturityData['overallMaturityLevel'] ?? null;

        $strengthCount    = is_countable($assessmentData->strengths ?? null)              ? count($assessmentData->strengths) : 0;
        $riskHighCount    = is_countable($assessmentData->highRisks ?? null)             ? count($assessmentData->highRisks) : 0;
        $riskMediumCount  = is_countable($assessmentData->mediumRisks ?? null)           ? count($assessmentData->mediumRisks) : 0;
        $riskLowCount     = is_countable($assessmentData->lowRisks ?? null)              ? count($assessmentData->lowRisks) : 0;
        $improvementCount = is_countable($assessmentData->improvementOpportunities ?? null) ? count($assessmentData->improvementOpportunities) : 0;

        $assessmentIds = Assessment::where('user_id', $user->id)
            ->where('assessment_group_id', $group->assessment_group_id)
            ->where('status', 'finalized')
            ->pluck('id');

        $answeredQuestions = Answer::whereIn('assessment_id', $assessmentIds)
            ->whereNotNull('score')
            ->count();

        $totalQuestions = Question::whereIn('domain', $labels)->count();

        AiEvaluationSummary::updateOrCreate(
            [
                'company_id'          => $user->id,
                'assessment_group_id' => $group->assessment_group_id,
            ],
            [
                'company_alias'       => $user->company_alias ?? null,
                'holding_id'          => $user->parent_id ?? null,
                'evaluation_date'     => $evaluationDate,
                'period_label'        => $periodLabel,
                'final_score'         => (float) ($finalScore->final_score ?? 0),

                'score_it_governance'          => $domainScore('حاکمیت فناوری اطلاعات'),
                'score_info_security'          => $domainScore('امنیت اطلاعات و مدیریت ریسک'),
                'score_infrastructure'         => $domainScore('زیرساخت فناوری'),
                'score_it_support'             => $domainScore('خدمات پشتیبانی'),
                'score_applications'           => $domainScore('سامانه‌های کاربردی'),
                'score_digital_transformation' => $domainScore('تحول دیجیتال'),
                'score_intelligence'          => $domainScore('هوشمندسازی'),

                'subcategory_scores' => $subcategoryScores,

                'overall_maturity_level' => $overallMaturityLevel,
                'maturity_level_1_avg'   => $levelAverages[0] ?? null,
                'maturity_level_2_avg'   => $levelAverages[1] ?? null,
                'maturity_level_3_avg'   => $levelAverages[2] ?? null,
                'maturity_level_4_avg'   => $levelAverages[3] ?? null,
                'maturity_level_5_avg'   => $levelAverages[4] ?? null,

                'strength_count'     => $strengthCount,
                'risk_high_count'    => $riskHighCount,
                'risk_medium_count'  => $riskMediumCount,
                'risk_low_count'     => $riskLowCount,
                'improvement_count'  => $improvementCount,
                'total_questions'    => $totalQuestions,
                'answered_questions' => $answeredQuestions,
            ]
        );
    }

    protected function buildInsightItems($user, $group, $evaluationDate): void
    {
        AiInsightItem::where('company_id', $user->id)
            ->where('assessment_group_id', $group->assessment_group_id)
            ->delete();

        $domains = Question::distinct()->pluck('domain')->toArray();

        foreach ($domains as $domain) {
            $assessmentForDomain = Assessment::where('user_id', $user->id)
                ->where('assessment_group_id', $group->assessment_group_id)
                ->where('status', 'finalized')
                ->where('domain', $domain)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$assessmentForDomain) {
                continue;
            }

            $questionsInDomain = Question::select(
                'questions.id',
                'questions.domain',
                'questions.subcategory',
                'questions.weight',
                'questions.risks',
                'questions.strengths',
                'questions.improvement_opportunities',
                'answers.score as user_score'
            )->leftJoin('answers', function ($join) use ($user, $assessmentForDomain) {
                $join->on('questions.id', '=', 'answers.question_id')
                    ->where('answers.user_id', '=', $user->id)
                    ->where('answers.assessment_id', '=', $assessmentForDomain->id);
            })->where('questions.domain', $domain)
              ->get();

            foreach ($questionsInDomain as $q) {
                $weight      = $q->weight ?? 0;
                $score       = $q->user_score ?? 0;
                $subcategory = $q->subcategory ?? null;

                if ($weight <= 0 || $score <= 0) {
                    continue;
                }

                if (in_array($score, [10, 20, 30]) && $q->risks) {
                    $severity = $this->mapRiskSeverity($domain, (int) $weight);
                    AiInsightItem::create([
                        'company_id'          => $user->id,
                        'company_alias'       => $user->company_alias ?? null,
                        'assessment_group_id' => $group->assessment_group_id,
                        'evaluation_date'     => $evaluationDate,
                        'item_type'           => 'risk',
                        'severity'            => $severity,
                        'domain'              => $domain,
                        'subcategory'         => $subcategory,
                        'question_id'         => $q->id,
                        'weight'              => $weight,
                        'score'               => $score,
                        'content'             => $q->risks,
                    ]);
                } elseif (in_array($score, [40, 50]) && $q->improvement_opportunities) {
                    AiInsightItem::create([
                        'company_id'          => $user->id,
                        'company_alias'       => $user->company_alias ?? null,
                        'assessment_group_id' => $group->assessment_group_id,
                        'evaluation_date'     => $evaluationDate,
                        'item_type'           => 'improvement',
                        'severity'            => null,
                        'domain'              => $domain,
                        'subcategory'         => $subcategory,
                        'question_id'         => $q->id,
                        'weight'              => $weight,
                        'score'               => $score,
                        'content'             => $q->improvement_opportunities,
                    ]);
                } elseif (in_array($score, [70, 80, 90, 100]) && $q->strengths) {
                    AiInsightItem::create([
                        'company_id'          => $user->id,
                        'company_alias'       => $user->company_alias ?? null,
                        'assessment_group_id' => $group->assessment_group_id,
                        'evaluation_date'     => $evaluationDate,
                        'item_type'           => 'strength',
                        'severity'            => null,
                        'domain'              => $domain,
                        'subcategory'         => $subcategory,
                        'question_id'         => $q->id,
                        'weight'              => $weight,
                        'score'               => $score,
                        'content'             => $q->strengths,
                    ]);
                }
            }
        }
    }

    protected function mapRiskSeverity(string $domain, int $weight): ?string
    {
        if (in_array($domain, ['امنیت اطلاعات و مدیریت ریسک'])) {
            if (in_array($weight, [1, 2, 3])) return 'low';
            if (in_array($weight, [4, 5, 6, 7, 8])) return 'medium';
            if (in_array($weight, [9, 10])) return 'high';
        } elseif (in_array($domain, ['زیرساخت فناوری', 'سامانه‌های کاربردی'])) {
            if (in_array($weight, [1, 2, 3, 4, 5])) return 'low';
            if (in_array($weight, [6, 7, 8])) return 'medium';
            if (in_array($weight, [9, 10])) return 'high';
        } elseif (in_array($domain, ['تحول دیجیتال', 'هوشمندسازی', 'خدمات پشتیبانی', 'حاکمیت فناوری اطلاعات'])) {
            if (in_array($weight, [1, 2, 3, 4, 5, 6, 7])) return 'low';
            if (in_array($weight, [8, 9, 10])) return 'medium';
        }

        return null;
    }

    protected function buildLstmFeatures($user, $group, $evaluationDate): void
    {
        $summary = AiEvaluationSummary::where('company_id', $user->id)
            ->where('assessment_group_id', $group->assessment_group_id)
            ->first();

        if (!$summary) {
            return;
        }

        $allSummaries = AiEvaluationSummary::where('company_id', $user->id)
            ->orderBy('evaluation_date')
            ->get();

        $timeIndex = 0;
        foreach ($allSummaries as $index => $row) {
            if ((int) $row->assessment_group_id === (int) $group->assessment_group_id) {
                $timeIndex = $index;
                break;
            }
        }

        $features = [
            'final_score'                => $summary->final_score,
            'score_it_governance'        => $summary->score_it_governance,
            'score_info_security'        => $summary->score_info_security,
            'score_infrastructure'       => $summary->score_infrastructure,
            'score_it_support'           => $summary->score_it_support,
            'score_applications'         => $summary->score_applications,
            'score_digital_transformation' => $summary->score_digital_transformation,
            'score_intelligence'         => $summary->score_intelligence,
            'overall_maturity_level'     => $summary->overall_maturity_level,
            'maturity_level_1_avg'       => $summary->maturity_level_1_avg,
            'maturity_level_2_avg'       => $summary->maturity_level_2_avg,
            'maturity_level_3_avg'       => $summary->maturity_level_3_avg,
            'maturity_level_4_avg'       => $summary->maturity_level_4_avg,
            'maturity_level_5_avg'       => $summary->maturity_level_5_avg,
            'strength_count'             => $summary->strength_count,
            'risk_high_count'            => $summary->risk_high_count,
            'risk_medium_count'          => $summary->risk_medium_count,
            'risk_low_count'             => $summary->risk_low_count,
            'improvement_count'          => $summary->improvement_count,
            'total_questions'            => $summary->total_questions,
            'answered_questions'         => $summary->answered_questions,
        ];

        AiLstmFeature::updateOrCreate(
            [
                'company_id'          => $user->id,
                'assessment_group_id' => $group->assessment_group_id,
            ],
            [
                'evaluation_date'       => $evaluationDate,
                'time_index'            => $timeIndex,
                'features'              => $features,
                'target_final_score'    => $summary->final_score,
                'target_maturity_level' => $summary->overall_maturity_level,
            ]
        );
    }
}
