<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Assessment;
use App\Models\AssessmentGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Morilog\Jalali\Jalalian;

class ReportController extends Controller
{
    private function calculateMaturityLevel($assessmentGroupId, $userId)
    {
        $assessments = Assessment::where('user_id', $userId)
            ->where('assessment_group_id', $assessmentGroupId)
            ->where('status', 'finalized')
            ->get();

        \Log::debug('ReportController.calculateMaturityLevel - Assessments:', [
            'user_id' => $userId,
            'assessment_group_id' => $assessmentGroupId,
            'assessment_count' => $assessments->count(),
            'assessment_ids' => $assessments->pluck('id')->toArray(),
        ]);

        $levelAverages = array_fill(0, 5, 0);
        $levelCountsProcessed = array_fill(0, 5, 0);
        foreach ($assessments as $assessment) {
            $answers = Answer::where('assessment_id', $assessment->id)->with('question')->get();
            foreach ($answers as $answer) {
                $question = $answer->question;
                if (!$question || !isset($question->Maturity_level)) continue;
                $score = $answer->score ?? 0;
                $maturityLevel = (int)$question->Maturity_level - 1;
                if ($maturityLevel >= 0 && $maturityLevel <= 4) {
                    $levelAverages[$maturityLevel] += $score;
                    $levelCountsProcessed[$maturityLevel]++;
                }
            }
        }

        for ($i = 0; $i < 5; $i++) {
            $levelAverages[$i] = $levelCountsProcessed[$i] > 0 ? $levelAverages[$i] / $levelCountsProcessed[$i] : 0;
        }

        $overallMaturityLevel = 0;
        $threshold = 60;
        for ($level = 0; $level < 5; $level++) {
            $average = $levelAverages[$level];
            if ($average >= $threshold) {
                $overallMaturityLevel = $level + 1;
            } else if ($average < $threshold) {
                if ($level == 0 && $average < $threshold) {
                    $overallMaturityLevel = 1;
                }
                break;
            }
        }
        if ($overallMaturityLevel == 5 && $levelAverages[4] < $threshold) {
            $overallMaturityLevel = 4;
        }

        \Log::debug('ReportController.calculateMaturityLevel - Result:', [
            'user_id' => $userId,
            'assessment_group_id' => $assessmentGroupId,
            'overallMaturityLevel' => $overallMaturityLevel,
            'levelAverages' => $levelAverages,
        ]);

        return [
            'overallMaturityLevel' => $overallMaturityLevel,
            'levelAverages' => array_map(fn($avg) => round($avg, 1), $levelAverages),
            'maturityLevels' => [1, 2, 3, 4, 5],
        ];
    }

    public function userReport(Request $request)
    {
        $user = Auth::user();
        $strengths = [];
        $highRisks = [];
        $mediumRisks = [];
        $lowRisks = [];
        $improvementOpportunities = [];
        $subcategories = [];
        $finalScore = null;
        $maturityData = ['overallMaturityLevel' => null];
        $labels = [
            'حاکمیت فناوری اطلاعات',
            'امنیت اطلاعات و مدیریت ریسک',
            'زیرساخت فناوری',
            'خدمات پشتیبانی',
            'سامانه‌های کاربردی',
            'تحول دیجیتال',
            'هوشمندسازی'
        ];
        $dataValues = array_fill(0, count($labels), 0);

        $assessment_group_id = $request->query('group_id');
        $completedGroups = AssessmentGroup::where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        \Log::debug('ReportController.userReport - Assessment Group:', [
            'assessment_group_id' => $assessment_group_id,
            'completed_groups_count' => $completedGroups->count(),
        ]);

        if (!$assessment_group_id && $completedGroups->isNotEmpty()) {
            $assessment_group_id = $completedGroups->first()->assessment_group_id;
        }

        if ($assessment_group_id) {
            $maturityData = $this->calculateMaturityLevel($assessment_group_id, $user->id);

            $assessments = Assessment::where('user_id', $user->id)
                ->where('assessment_group_id', $assessment_group_id)
                ->where('status', 'finalized')
                ->get();

            \Log::debug('ReportController.userReport - Assessments:', [
                'assessment_count' => $assessments->count(),
                'assessment_ids' => $assessments->pluck('id')->toArray(),
            ]);

            $domainScores = [];
            $allScoresForFinal = [];

            foreach ($assessments as $assessment) {
                $answers = Answer::where('assessment_id', $assessment->id)
                    ->with('question')
                    ->get();

                foreach ($answers as $answer) {
                    $question = $answer->question;
                    $score = $answer->score;
                    $weight = $question->weight;
                    $domain = $answer->domain ?? $question->domain ?? 'نامشخص';
                    $subcategory = $question->subcategory ?? 'نامشخص';

                    if (!isset($domainScores[$domain])) {
                        $domainScores[$domain] = [];
                    }
                    $domainScores[$domain][] = $score;

                    if (!isset($subcategories[$domain])) {
                        $subcategories[$domain] = [];
                    }
                    $subcategories[$domain][] = [
                        'name' => $subcategory,
                        'performance' => $score,
                    ];

                    if (!isset($allScoresForFinal[$domain])) {
                        $allScoresForFinal[$domain] = [];
                    }
                    $allScoresForFinal[$domain][] = $score;

                    if (in_array($score, [70, 80, 90, 100]) && (in_array($weight, [8, 9, 10]) || ($question->Maturity_level !== null && in_array($question->Maturity_level, [4, 5])))) {
                        $content = $question->strengths;
                        if (is_array($content)) {
                            $content = implode(', ', $content);
                        } elseif (!is_string($content)) {
                            $content = '';
                        }
                        $strengths[] = ['weight' => $weight, 'content' => $content ?? ''];
                    } elseif (in_array($score, [10, 20, 30]) && in_array($weight, [9, 10])) {
                        $content = $question->risks;
                        if (is_array($content)) {
                            $content = implode(', ', $content);
                        } elseif (!is_string($content)) {
                            $content = '';
                        }
                        $highRisks[] = ['weight' => $weight, 'content' => $content ?? ''];
                    } elseif (in_array($score, [10, 20, 30]) && in_array($weight, [1, 2, 3, 4, 5, 6, 7, 8])) {
                        $content = $question->risks;
                        if (is_array($content)) {
                            $content = implode(', ', $content);
                        } elseif (!is_string($content)) {
                            $content = '';
                        }
                        $mediumRisks[] = ['weight' => $weight, 'content' => $content ?? ''];
                    } elseif (in_array($score, [40, 50, 60]) && in_array($weight, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10])) {
                        $content = $question->improvement_opportunities;
                        if (is_array($content)) {
                            $content = implode(', ', $content);
                        } elseif (!is_string($content)) {
                            $content = '';
                        }
                        $improvementOpportunities[] = ['weight' => $weight, 'content' => $content ?? ''];
                    }
                }
            }

            \Log::debug('ReportController.userReport - Improvement Opportunities:', [
                'improvementOpportunities' => $improvementOpportunities,
            ]);

            foreach ($labels as $index => $label) {
                $domainKey = $label;
                if (isset($domainScores[$domainKey])) {
                    $scores = $domainScores[$domainKey];
                    $dataValues[$index] = count($scores) > 0 ? array_sum($scores) / count($scores) : 0;
                }
            }

            $totalScore = 0;
            $scoreCount = 0;
            foreach ($allScoresForFinal as $scores) {
                $totalScore += array_sum($scores);
                $scoreCount += count($scores);
            }
            $finalScore = $scoreCount > 0 ? $totalScore / $scoreCount : null;
        }

        $strengths = collect($strengths)->sortByDesc('weight')->values()->all();
        $highRisks = collect($highRisks)->sortByDesc('weight')->values()->all();
        $mediumRisks = collect($mediumRisks)->sortByDesc('weight')->values()->all();
        $lowRisks = collect($lowRisks)->sortByDesc('weight')->values()->all();
        $improvementOpportunities = collect($improvementOpportunities)->sortByDesc('weight')->values()->all();

        $userInfo = User::find($user->id);
        $company_name = is_array($userInfo->company_alias) ? implode(', ', $userInfo->company_alias) : ($userInfo->company_alias ?? 'نامشخص');
        $company_size = is_array($userInfo->company_size) ? implode(', ', $userInfo->company_size) : ($userInfo->company_size ?? 'نامشخص');
        $company_type = is_array($userInfo->company_type) ? implode(', ', $userInfo->company_type) : ($userInfo->company_type ?? 'نامشخص');

        $report_date = $assessments->isNotEmpty() && $assessments->first()->finalized_date
            ? Jalalian::fromDateTime($assessments->first()->finalized_date)->format('j F Y')
            : 'نامشخص';

        \Log::debug('ReportController.userReport - Variables:', [
            'company_name' => $company_name,
            'company_type' => $company_type,
            'company_size' => $company_size,
            'report_date' => $report_date,
            'strengths' => $strengths,
            'highRisks' => $highRisks,
            'mediumRisks' => $mediumRisks,
            'improvementOpportunities' => $improvementOpportunities,
            'finalScore' => $finalScore,
            'maturityData' => $maturityData,
        ]);

        return view('assessment.report', compact(
            'strengths',
            'highRisks',
            'mediumRisks',
            'lowRisks',
            'improvementOpportunities',
            'company_name',
            'company_size',
            'company_type',
            'report_date',
            'completedGroups',
            'assessment_group_id',
            'labels',
            'dataValues',
            'subcategories',
            'finalScore',
            'maturityData'
        ));
    }

    public function printReport(Request $request)
    {
        $user = Auth::user();
        $strengths = [];
        $highRisks = [];
        $mediumRisks = [];
        $lowRisks = [];
        $improvementOpportunities = [];
        $subcategories = [];
        $finalScore = null;
        $maturityData = ['overallMaturityLevel' => null];
        $labels = [
            'حاکمیت فناوری اطلاعات',
            'امنیت اطلاعات و مدیریت ریسک',
            'زیرساخت فناوری',
            'خدمات پشتیبانی',
            'سامانه‌های کاربردی',
            'تحول دیجیتال',
            'هوشمندسازی'
        ];
        $dataValues = array_fill(0, count($labels), 0);

        $assessment_group_id = $request->query('group_id');
        $completedGroups = AssessmentGroup::where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        \Log::debug('ReportController.printReport - Assessment Group:', [
            'assessment_group_id' => $assessment_group_id,
            'completed_groups_count' => $completedGroups->count(),
        ]);

        if (!$assessment_group_id && $completedGroups->isNotEmpty()) {
            $assessment_group_id = $completedGroups->first()->assessment_group_id;
        }

        if ($assessment_group_id) {
            $maturityData = $this->calculateMaturityLevel($assessment_group_id, $user->id);

            $assessments = Assessment::where('user_id', $user->id)
                ->where('assessment_group_id', $assessment_group_id)
                ->where('status', 'finalized')
                ->get();

            \Log::debug('ReportController.printReport - Assessments:', [
                'assessment_count' => $assessments->count(),
                'assessment_ids' => $assessments->pluck('id')->toArray(),
            ]);

            $domainScores = [];
            $allScoresForFinal = [];

            foreach ($assessments as $assessment) {
                $answers = Answer::where('assessment_id', $assessment->id)
                    ->with('question')
                    ->get();

                foreach ($answers as $answer) {
                    $question = $answer->question;
                    $score = $answer->score;
                    $weight = $question->weight;
                    $domain = $answer->domain ?? $question->domain ?? 'نامشخص';
                    $subcategory = $question->subcategory ?? 'نامشخص';

                    if (!isset($domainScores[$domain])) {
                        $domainScores[$domain] = [];
                    }
                    $domainScores[$domain][] = $score;

                    if (!isset($subcategories[$domain])) {
                        $subcategories[$domain] = [];
                    }
                    $subcategories[$domain][] = [
                        'name' => $subcategory,
                        'performance' => $score,
                    ];

                    if (!isset($allScoresForFinal[$domain])) {
                        $allScoresForFinal[$domain] = [];
                    }
                    $allScoresForFinal[$domain][] = $score;

                    if (in_array($score, [70, 80, 90, 100]) && (in_array($weight, [8, 9, 10]) || ($question->Maturity_level !== null && in_array($question->Maturity_level, [4, 5])))) {
                        $content = $question->strengths;
                        if (is_array($content)) {
                            $content = implode(', ', $content);
                        } elseif (!is_string($content)) {
                            $content = '';
                        }
                        $strengths[] = ['weight' => $weight, 'content' => $content ?? ''];
                    } elseif (in_array($score, [10, 20, 30]) && in_array($weight, [9, 10])) {
                        $content = $question->risks;
                        if (is_array($content)) {
                            $content = implode(', ', $content);
                        } elseif (!is_string($content)) {
                            $content = '';
                        }
                        $highRisks[] = ['weight' => $weight, 'content' => $content ?? ''];
                    } elseif (in_array($score, [10, 20, 30]) && in_array($weight, [1, 2, 3, 4, 5, 6, 7, 8])) {
                        $content = $question->risks;
                        if (is_array($content)) {
                            $content = implode(', ', $content);
                        } elseif (!is_string($content)) {
                            $content = '';
                        }
                        $mediumRisks[] = ['weight' => $weight, 'content' => $content ?? ''];
                    } elseif (in_array($score, [40, 50, 60]) && in_array($weight, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10])) {
                        $content = $question->improvement_opportunities;
                        if (is_array($content)) {
                            $content = implode(', ', $content);
                        } elseif (!is_string($content)) {
                            $content = '';
                        }
                        $improvementOpportunities[] = ['weight' => $weight, 'content' => $content ?? ''];
                    }
                }
            }

            \Log::debug('ReportController.printReport - Improvement Opportunities:', [
                'improvementOpportunities' => $improvementOpportunities,
            ]);

            foreach ($labels as $index => $label) {
                $domainKey = $label;
                if (isset($domainScores[$domainKey])) {
                    $scores = $domainScores[$domainKey];
                    $dataValues[$index] = count($scores) > 0 ? array_sum($scores) / count($scores) : 0;
                }
            }

            $totalScore = 0;
            $scoreCount = 0;
            foreach ($allScoresForFinal as $scores) {
                $totalScore += array_sum($scores);
                $scoreCount += count($scores);
            }
            $finalScore = $scoreCount > 0 ? $totalScore / $scoreCount : null;
        }

        $strengths = collect($strengths)->sortByDesc('weight')->values()->all();
        $highRisks = collect($highRisks)->sortByDesc('weight')->values()->all();
        $mediumRisks = collect($mediumRisks)->sortByDesc('weight')->values()->all();
        $lowRisks = collect($lowRisks)->sortByDesc('weight')->values()->all();
        $improvementOpportunities = collect($improvementOpportunities)->sortByDesc('weight')->values()->all();

        $userInfo = User::find($user->id);
        $company_name = is_array($userInfo->company_alias) ? implode(', ', $userInfo->company_alias) : ($userInfo->company_alias ?? 'نامشخص');
        $company_size = is_array($userInfo->company_size) ? implode(', ', $userInfo->company_size) : ($userInfo->company_size ?? 'نامشخص');
        $company_type = is_array($userInfo->company_type) ? implode(', ', $userInfo->company_type) : ($userInfo->company_type ?? 'نامشخص');

        $report_date = $assessments->isNotEmpty() && $assessments->first()->finalized_date
            ? Jalalian::fromDateTime($assessments->first()->finalized_date)->format('j F Y')
            : 'نامشخص';

        \Log::debug('ReportController.printReport - Variables:', [
            'company_name' => $company_name,
            'company_type' => $company_type,
            'company_size' => $company_size,
            'report_date' => $report_date,
            'strengths' => $strengths,
            'highRisks' => $highRisks,
            'mediumRisks' => $mediumRisks,
            'improvementOpportunities' => $improvementOpportunities,
            'finalScore' => $finalScore,
            'maturityData' => $maturityData,
        ]);

        return view('assessment.print-report', compact(
            'strengths',
            'highRisks',
            'mediumRisks',
            'lowRisks',
            'improvementOpportunities',
            'company_name',
            'company_size',
            'company_type',
            'report_date',
            'completedGroups',
            'assessment_group_id',
            'labels',
            'dataValues',
            'subcategories',
            'finalScore',
            'maturityData'
        ));
    }

    public function compareCompanies(Request $request)
    {
        $user = Auth::user();
        $comparisonData = [
            'company_names' => [],
            'data_values' => [],
            'maturity_data' => [],
            'final_scores' => [],
            'maturity_levels' => [],
            'high_risk_counts' => [],
        ];
        $labels = [
            'حاکمیت فناوری اطلاعات',
            'امنیت اطلاعات و مدیریت ریسک',
            'زیرساخت فناوری',
            'خدمات پشتیبانی',
            'سامانه‌های کاربردی',
            'تحول دیجیتال',
            'هوشمندسازی'
        ];

        $completedGroups = AssessmentGroup::where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($completedGroups as $group) {
            $assessments = Assessment::where('assessment_group_id', $group->assessment_group_id)
                ->where('status', 'finalized')
                ->get();

            if ($assessments->isEmpty()) {
                continue;
            }

            $companyName = $group->company_name ?? 'شرکت بدون نام';
            $domainScores = [];
            $allScoresForFinal = [];
            $highRiskCount = 0;

            foreach ($assessments as $assessment) {
                $answers = Answer::where('assessment_id', $assessment->id)
                    ->with('question')
                    ->get();

                foreach ($answers as $answer) {
                    $question = $answer->question;
                    $score = $answer->score ?? 0;
                    $weight = $question->weight ?? 0;
                    $domain = $answer->domain ?? ($question->domain ?? 'نامشخص');

                    if (!isset($domainScores[$domain])) {
                        $domainScores[$domain] = [];
                    }
                    $domainScores[$domain][] = $score;

                    if (!isset($allScoresForFinal[$domain])) {
                        $allScoresForFinal[$domain] = [];
                    }
                    $allScoresForFinal[$domain][] = $score;

                    if (in_array($score, [10, 20, 30]) && in_array($weight, [9, 10])) {
                        $highRiskCount++;
                    }
                }
            }

            $dataValues = array_fill(0, count($labels), 0);
            foreach ($labels as $index => $label) {
                if (isset($domainScores[$label]) && !empty($domainScores[$label])) {
                    $dataValues[$index] = array_sum($domainScores[$label]) / count($domainScores[$label]);
                }
            }

            $totalScore = 0;
            $scoreCount = 0;
            foreach ($allScoresForFinal as $scores) {
                $totalScore += array_sum($scores);
                $scoreCount += count($scores);
            }
            $finalScore = $scoreCount > 0 ? $totalScore / $scoreCount : null;

            $maturityData = $this->calculateMaturityLevel($group->assessment_group_id, $user->id);

            $comparisonData['company_names'][] = $companyName;
            $comparisonData['data_values'][] = $dataValues;
            $comparisonData['maturity_data'][] = ['level_averages' => $maturityData['levelAverages']];
            $comparisonData['final_scores'][] = $finalScore;
            $comparisonData['maturity_levels'][] = $maturityData['overallMaturityLevel'];
            $comparisonData['high_risk_counts'][] = $highRiskCount;
        }

        return view('assessment.compare-companies', compact('comparisonData', 'labels'));
    }
}