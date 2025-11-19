<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\FinalScore;
use App\Models\Assessment;
use App\Models\Question;
use App\Models\Answer;
use App\Models\DomainWeight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CompanyComparisonController extends Controller
{
    public function compareCompanies(Request $request)
    {
        $validated = $request->validate([
            'company_ids' => 'required|array|min:2',
            'company_ids.*' => 'exists:users,id',
        ]);

        $companyIds = $validated['company_ids'];
        $user = Auth::user();

        // بررسی شرکت‌های زیرمجموعه هلدینگ
        $companies = User::whereIn('id', $companyIds)
            ->where('holding_affiliation_code', $user->holding_affiliation_code)
            ->get();

        if ($companies->count() != count($companyIds)) {
            return redirect()->route('profile')->with('error', 'یکی از شرکت‌های انتخاب‌شده جزو زیرمجموعه شما نیست.');
        }

        // آماده‌سازی داده‌های مقایسه
        $comparisonData = [
            'company_names' => [],
            'final_scores' => [],
            'data_values' => [],
            'maturity_data' => [],
            'assessment_dates' => [],
        ];

        $labels = DomainWeight::pluck('domain')->toArray();
        if (empty($labels)) {
            $labels = [
                'حاکمیت فناوری اطلاعات',
                'امنیت اطلاعات و مدیریت ریسک',
                'زیرساخت فناوری',
                'خدمات پشتیبانی',
                'سامانه‌های کاربردی',
                'تحول دیجیتال',
                'هوشمندسازی'
            ];
            Log::warning('No domains found in DomainWeight, using default labels');
        }

        foreach ($companies as $company) {
            // گرفتن آخرین ارزیابی شرکت
            $latestFinalScore = FinalScore::where('user_id', $company->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($latestFinalScore) {
                $comparisonData['company_names'][] = $company->company_alias ?? 'نامشخص';
                $comparisonData['final_scores'][] = $latestFinalScore->final_score ?? 0;
                $comparisonData['assessment_dates'][] = \Carbon\Carbon::parse($latestFinalScore->created_at)->format('Y/m/d H:i');

                // محاسبه درصد عملکرد برای هر حوزه
                $dataValues = $this->calculateDomainPerformance($latestFinalScore, $company->id, $labels);
                $comparisonData['data_values'][] = $dataValues;

                // محاسبه سطح بلوغ
                $maturityData = $this->calculateMaturityLevel($latestFinalScore->assessment_group_id, $company->id);
                $comparisonData['maturity_data'][] = $maturityData;
            }
        }

        if (count($comparisonData['company_names']) < 2) {
            return redirect()->route('profile')->with('error', 'حداقل دو شرکت باید ارزیابی تکمیل‌شده داشته باشند تا مقایسه انجام شود.');
        }

        // لاگ‌گیری برای دیباگ
        Log::info('Comparison Data Prepared', [
            'user_id' => $user->id,
            'company_ids' => $companyIds,
            'comparison_data' => $comparisonData,
            'labels' => $labels,
        ]);

        return view('assessment.compare_multiple', compact('comparisonData', 'labels'));
    }

    private function calculateDomainPerformance($finalScore, $userId, $labels)
    {
        $dataValues = array_fill(0, count($labels), 0);

        foreach ($labels as $index => $domain) {
            $assessment = Assessment::where('user_id', $userId)
                ->where('assessment_group_id', $finalScore->assessment_group_id)
                ->where('domain', $domain)
                ->where('status', 'finalized')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($assessment) {
                $questions = Question::select('questions.id', 'questions.weight', 'answers.score as user_score')
                    ->leftJoin('answers', function ($join) use ($userId, $assessment) {
                        $join->on('questions.id', '=', 'answers.question_id')
                            ->where('answers.user_id', '=', $userId)
                            ->where('answers.assessment_id', '=', $assessment->id);
                    })
                    ->where('questions.domain', $domain)
                    ->get();

                $totalWeightedScore = 0;
                $totalWeight = 0;

                foreach ($questions as $question) {
                    if ($question->user_score !== null) {
                        $normalizedScore = $question->user_score / 100;
                        $weightedScore = $normalizedScore * ($question->weight ?? 1);
                        $totalWeightedScore += $weightedScore;
                        $totalWeight += $question->weight ?? 1;
                    }
                }

                $dataValues[$index] = $totalWeight > 0 ? round(($totalWeightedScore / $totalWeight) * 100, 1) : 0;
            }
        }

        return $dataValues;
    }

    private function calculateMaturityLevel($assessmentGroupId, $userId)
    {
        $assessments = Assessment::where('user_id', $userId)
            ->where('assessment_group_id', $assessmentGroupId)
            ->where('status', 'finalized')
            ->get();

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
            } else {
                if ($level == 0 && $average < $threshold) {
                    $overallMaturityLevel = 1;
                }
                break;
            }
        }

        if ($overallMaturityLevel == 5 && $levelAverages[4] < $threshold) {
            $overallMaturityLevel = 4;
        }

        return [
            'overallMaturityLevel' => $overallMaturityLevel,
            'level_averages' => array_map(fn($avg) => round($avg, 1), $levelAverages),
            'maturityLevels' => [1, 2, 3, 4, 5],
        ];
    }
}