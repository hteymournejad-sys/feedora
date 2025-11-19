<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Assessment;
use App\Models\Question;
use App\Models\Answer;
use App\Models\DomainWeight;
use App\Models\FinalScore;
use App\Models\AssessmentGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Morilog\Jalali\Jalalian;

class AssessmentController extends Controller
{
    
// --- [ADD] Helpers: place these inside the AssessmentController class ---

/**
 * Return all descendant user IDs (any depth) for a given ancestor (holding).
 * Includes the ancestor itself by default.
 */
private function getDescendantIds(int $ancestorId, bool $includeSelf = true, int $maxDepth = 20): array
{
    $visited = [];
    $queue = [$ancestorId];
    $depth = 0;

    if ($includeSelf) {
        $visited[] = $ancestorId;
    }

    while (!empty($queue) && $depth < $maxDepth) {
        // fetch children of all nodes in queue
        $children = User::whereIn('parent_id', $queue)->pluck('id')->all();
        // de-duplicate and drop already-visited
        $children = array_values(array_diff(array_unique($children), $visited));

        if (empty($children)) {
            break;
        }

        // mark visited
        $visited = array_merge($visited, $children);
        // next layer
        $queue = $children;
        $depth++;
    }

    // unique final list
    return array_values(array_unique($visited));
}

/**
 * Check if $candidateId is the same as $ancestorId or a descendant (any depth).
 * Walks upward from candidate to root for safety and performance.
 */
private function isDescendantOrSelf(int $ancestorId, int $candidateId, int $maxDepth = 20): bool
{
    if ($ancestorId === $candidateId) {
        return true;
    }

    $currentId = $candidateId;
    $depth = 0;

    while ($currentId && $depth < $maxDepth) {
        $parentId = User::where('id', $currentId)->value('parent_id');
        if (!$parentId) {
            return false;
        }
        if ((int)$parentId === (int)$ancestorId) {
            return true;
        }
        $currentId = (int)$parentId;
        $depth++;
    }

    return false;
}





public function calculateAssessmentData($group, $user_id, $isHolding = false)
    {
        $assessment = new \stdClass();
        $assessment_group_id = $group->assessment_group_id;

        $labels = Assessment::where('user_id', $user_id)
            ->where('status', 'finalized')
            ->where('assessment_group_id', $assessment_group_id)
            ->distinct()
            ->pluck('domain')
            ->toArray();

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
            Log::warning('No finalized assessments found, using default labels', [
                'user_id' => $user_id,
                'assessment_group_id' => $assessment_group_id,
                'timestamp' => now()
            ]);
        }

        $dataValues = [];
        $subcategories = [];
        $highRisks = $mediumRisks = $lowRisks = $strengths = $improvementOpportunities = $developingStatus = $suggestions = [];

        foreach ($labels as $domain) {
            $assessmentForDomain = Assessment::where('user_id', $user_id)
                ->where('assessment_group_id', $assessment_group_id)
                ->where('status', 'finalized')
                ->where('domain', $domain)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$assessmentForDomain) {
                $dataValues[] = 0;
                $subcategories[$domain] = [];
                Log::warning('No assessment found for domain', [
                    'domain' => $domain,
                    'user_id' => $user_id,
                    'assessment_group_id' => $assessment_group_id,
                    'timestamp' => now()
                ]);
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
                'questions.current_status',
                'answers.score as user_score'
            )->leftJoin('answers', function ($join) use ($user_id, $assessmentForDomain) {
                $join->on('questions.id', '=', 'answers.question_id')
                    ->where('answers.user_id', '=', $user_id)
                    ->where('answers.assessment_id', '=', $assessmentForDomain->id);
            })->where('questions.domain', $domain)
            ->get();

            $totalWeightedScore = 0;
            $totalWeight = 0;
            $tempSubcategories = [];

            foreach ($questionsInDomain as $q) {
                $weight = $q->weight ?? 0;
                $score = $q->user_score ?? 0;
                $subcategory = $q->subcategory ?? 'نامشخص';

                $normalizedScore = $score / 100;
                $weightedScore = $normalizedScore * $weight;
                $totalWeightedScore += $weightedScore;
                $totalWeight += $weight;

                if ($weight > 0 && $score > 0) {
                    if (!isset($tempSubcategories[$subcategory])) {
                        $tempSubcategories[$subcategory] = ['total_weight' => 0, 'total_weighted_score' => 0];
                    }
                    $tempSubcategories[$subcategory]['total_weight'] += $weight;
                    $tempSubcategories[$subcategory]['total_weighted_score'] += $weightedScore;

                    // شروط جدید برای دسته‌بندی ریسک‌ها، فرصت‌های بهبود و نقاط قوت
                    if (in_array($score, [10, 20, 30]) && $q->risks) {
                        if ($domain == 'امنیت اطلاعات و مدیریت ریسک') {
                            if (in_array($weight, [1, 2, 3, 4, 5])) {
                                $lowRisks[] = ['content' => $q->risks];
                            } elseif (in_array($weight, [6, 7])) {
                                $mediumRisks[] = ['content' => $q->risks];
                            } elseif (in_array($weight, [8, 9, 10])) {
                                $highRisks[] = ['content' => $q->risks];
                            }
                        } elseif (in_array($domain, ['زیرساخت فناوری', 'سامانه‌های کاربردی'])) {
                            if (in_array($weight, [1, 2, 3, 4, 5])) {
                                $lowRisks[] = ['content' => $q->risks];
                            } elseif (in_array($weight, [6, 7, 8])) {
                                $mediumRisks[] = ['content' => $q->risks];
                            } elseif (in_array($weight, [9, 10])) {
                                $highRisks[] = ['content' => $q->risks];
                            }
                        } elseif (in_array($domain, ['تحول دیجیتال', 'هوشمندسازی', 'خدمات پشتیبانی', 'حاکمیت فناوری اطلاعات'])) {
                            if (in_array($weight, [1, 2, 3, 4, 5, 6, 7])) {
                                $lowRisks[] = ['content' => $q->risks];
                            } elseif (in_array($weight, [8, 9, 10])) {
                                $mediumRisks[] = ['content' => $q->risks];
                            }
                        }
                    } elseif (in_array($score, [40, 50]) && $q->improvement_opportunities) {
                        $improvementOpportunities[] = ['content' => $q->improvement_opportunities];
                    } elseif (in_array($score, [70, 80, 90, 100]) && $q->strengths) {
                        $strengths[] = ['content' => $q->strengths];
                    }
                }
            }

            $performance = $totalWeight > 0 ? ($totalWeightedScore / $totalWeight) * 100 : 0;
            $dataValues[] = round($performance, 1);

            $subcategories[$domain] = [];
            foreach ($tempSubcategories as $subcat => $data) {
                $subPerformance = $data['total_weight'] > 0 ? ($data['total_weighted_score'] / $data['total_weight']) * 100 : 0;
                $subcategories[$domain][] = [
                    'name' => $subcat,
                    'performance' => round($subPerformance, 1),
                ];
            }
        }

        $finalScore = FinalScore::where('user_id', $user_id)
            ->where('assessment_group_id', $assessment_group_id)
            ->first()
            ->final_score ?? 0;

        // لاگ برای دیباگ ریسک‌ها
        Log::info('Calculated Risks in calculateAssessmentData', [
            'user_id' => $user_id,
            'assessment_group_id' => $assessment_group_id,
            'highRisks_count' => count($highRisks),
            'mediumRisks_count' => count($mediumRisks),
            'lowRisks_count' => count($lowRisks),
            'strengths_count' => count($strengths),
            'improvementOpportunities_count' => count($improvementOpportunities),
            'timestamp' => now()
        ]);

        $assessment->labels = $labels;
        $assessment->dataValues = $dataValues;
        $assessment->subcategories = $subcategories;
        $assessment->highRisks = $highRisks;
        $assessment->mediumRisks = $mediumRisks;
        $assessment->lowRisks = $lowRisks;
        $assessment->strengths = $strengths;
        $assessment->improvementOpportunities = $improvementOpportunities;
        $assessment->developingStatus = $developingStatus;
        $assessment->suggestions = $suggestions;
        $assessment->finalScore = $finalScore;

        if ($isHolding) {
            $subsidiaries = User::where('parent_id', $user_id)
                ->where('id', '!=', $user_id)
                ->get();

            $subsidiaryAssessments = [];
            foreach ($subsidiaries as $subsidiary) {
                $subsidiaryAssessment = new \stdClass();
                $subsidiaryAssessment->company_name = $subsidiary->company_alias ?? 'نامشخص';

                $subAssessment = $this->calculateAssessmentData($group, $subsidiary->id, false);
                $subsidiaryAssessment->labels = $subAssessment->labels;
                $subsidiaryAssessment->dataValues = $subAssessment->dataValues;
                $subsidiaryAssessment->subcategories = $subAssessment->subcategories;
                $subsidiaryAssessment->finalScore = $subAssessment->finalScore;

                $subsidiaryAssessments[] = $subsidiaryAssessment;
            }

            $assessment->subsidiaryAssessments = $subsidiaryAssessments;
        } else {
            $assessment->subsidiaryAssessments = [];
        }

        return $assessment;
    }

    public function showQuestions(Request $request)
    {
        $user = Auth::user();
        $selectedDomain = $request->input('domain');
        $groupId = $request->input('group_id');

        Log::info('showQuestions Debug - Input Parameters', [
            'user_id' => $user->id,
            'selectedDomain' => $selectedDomain,
            'groupId' => $groupId,
        ]);

        if (!$selectedDomain) {
            return redirect()->route('assessment.domains')->with('error', 'لطفاً یک حوزه انتخاب کنید.');
        }

        // پیدا کردن گروه در حال انجام فقط بر اساس user_id و status
        $inProgressGroup = AssessmentGroup::where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->first();

        Log::info('showQuestions Debug - inProgressGroup', [
            'inProgressGroup' => $inProgressGroup ? $inProgressGroup->toArray() : null,
        ]);

        if (!$inProgressGroup) {
            return redirect()->route('assessment.domains')->with('error', 'گروه ارزیابی معتبر نیست.');
        }

        $assessment = Assessment::where('user_id', $user->id)
            ->where('status', 'draft')
            ->where('domain', $selectedDomain)
            ->where('assessment_group_id', $inProgressGroup->assessment_group_id)
            ->first();

        if (!$assessment) {
            $assessment = Assessment::create([
                'user_id' => $user->id,
                'created_date' => now(),
                'status' => 'draft',
                'domain' => $selectedDomain,
                'assessment_group_id' => $inProgressGroup->assessment_group_id,
                'excel_version' => 1,
            ]);
            Log::info('New Assessment created', [
                'assessment_id' => $assessment->id,
                'assessment_group_id' => $assessment->assessment_group_id,
                'domain' => $assessment->domain,
            ]);
        }

        if ($request->has('restart')) {
            $assessment->answers()->delete();
            $assessment->update(['last_question_id' => null]);
            Log::info('Assessment restarted');
        }

        $questionsQuery = Question::where('domain', $selectedDomain);
        $this->applyCompanyFilters($questionsQuery, $user);
        $questions = $questionsQuery->orderBy('id')->get();

        // پیدا کردن اولین سوال بدون پاسخ
        $answeredQuestionIds = Answer::where('assessment_id', $assessment->id)
            ->where('user_id', $user->id)
            ->pluck('question_id')
            ->toArray();
        
        $currentQuestion = $questions->first(function ($question) use ($answeredQuestionIds) {
            return !in_array($question->id, $answeredQuestionIds);
        });

        // اگر تمام سوالات پاسخ داده شده‌اند، به آخرین سوال پاسخ‌داده‌شده برو
        if (!$currentQuestion && $assessment->last_question_id) {
            $currentQuestion = $questions->firstWhere('id', $assessment->last_question_id);
        }

        // اگر هیچ سوالی پیدا نشد، اولین سوال را انتخاب کن
        if (!$currentQuestion) {
            $currentQuestion = $questions->first();
        }

        if (!$currentQuestion) {
            return redirect()->route('assessment.domains')->with('error', 'سوالی برای نمایش پیدا نشد.');
        }

        $previousQuestion = $questions->where('id', '<', $currentQuestion->id)->last();
        $nextQuestion = $questions->where('id', '>', $currentQuestion->id)->first();

        $totalQuestions = $questions->count();
        $answeredQuestions = $assessment->answers()->count();
        $progress = $totalQuestions > 0 ? ($answeredQuestions / $totalQuestions) * 100 : 0;

        return view('assessment.questions', compact('questions', 'assessment', 'currentQuestion', 'previousQuestion', 'nextQuestion', 'progress'));
    }

    public function groupReport(Request $request, $assessment_group_id)
    {
        $user = Auth::user();

        // پیدا کردن امتیاز نهایی برای گروه ارزیابی
        $finalScore = FinalScore::where('assessment_group_id', $assessment_group_id)
            ->whereIn('user_id', function ($query) use ($user) {
                $query->select('id')
                    ->from('users')
                    ->where('parent_id', $user->id)
                    ->orWhere('id', $user->id); // شامل خود کاربر هلدینگ هم می‌شود
            })
            ->first();

        if (!$finalScore) {
            Log::warning('No FinalScore found for group report', [
                'user_id' => $user->id,
                'assessment_group_id' => $assessment_group_id,
                'timestamp' => now()
            ]);
            return redirect()->route('profile')->with('error', 'گزارش برای گروه ارزیابی مورد نظر یافت نشد.');
        }

        // پیدا کردن کاربر مرتبط با finalScore
        $company = User::find($finalScore->user_id);

        if (!$company || ($company->parent_id != $user->id && $company->id != $user->id)) {
            Log::warning('Unauthorized access to group report', [
                'user_id' => $user->id,
                'assessment_group_id' => $assessment_group_id,
                'company_id' => $finalScore->user_id,
                'timestamp' => now()
            ]);
            return redirect()->route('profile')->with('error', 'شما اجازه دسترسی به این گزارش را ندارید.');
        }

        // دریافت ارزیابی‌های مرتبط
        $assessments = Assessment::where('assessment_group_id', $assessment_group_id)
            ->where('user_id', $finalScore->user_id)
            ->where('status', 'finalized')
            ->get();

        // تبدیل تاریخ به فرمت جلالی
        $report_date = 'نامشخص';
        if ($finalScore->created_at) {
            try {
                $report_date = Jalalian::fromDateTime($finalScore->created_at)->format('j F Y');
            } catch (\Exception $e) {
                Log::error('Failed to convert date to Jalali in groupReport', [
                    'user_id' => $user->id,
                    'assessment_group_id' => $assessment_group_id,
                    'error' => $e->getMessage(),
                    'timestamp' => now()
                ]);
                $report_date = $finalScore->created_at->format('Y/m/d');
            }
        }

        // محاسبه داده‌های ارزیابی مشابه متد report
        $assessmentData = $this->calculateAssessmentData($finalScore, $finalScore->user_id);
        $maturityData = $this->calculateMaturityLevel($assessment_group_id, $finalScore->user_id);

        $companyTypeArray = is_array($company->company_type) ? $company->company_type : (json_decode($company->company_type, true) ?? []);
        $company_type = $companyTypeArray ? implode('، ', $companyTypeArray) : 'خدماتی';
        $company_size = $company->company_size ?? 'متوسط';

        $labels = !empty($assessmentData->labels) ? $assessmentData->labels : [
            'حاکمیت فناوری اطلاعات',
            'امنیت اطلاعات و مدیریت ریسک',
            'زیرساخت فناوری',
            'خدمات پشتیبانی',
            'سامانه‌های کاربردی',
            'تحول دیجیتال',
            'هوشمندسازی'
        ];
        $dataValues = !empty($assessmentData->dataValues) ? $assessmentData->dataValues : [70, 85, 60, 75, 80, 65, 90];
        $subcategories = !empty($assessmentData->subcategories) ? $assessmentData->subcategories : [
            'حاکمیت فناوری اطلاعات' => [['name' => 'زیرحوزه 1', 'performance' => 75], ['name' => 'زیرحوزه 2', 'performance' => 65]],
            'امنیت اطلاعات و مدیریت ریسک' => [['name' => 'زیرحوزه 3', 'performance' => 80]],
        ];

        return view('assessment.group_report', [
            'assessment_group_id' => $assessment_group_id,
            'company_name' => $company->company_alias ?? 'نام شرکت',
            'report_date' => $report_date,
            'company_type' => $company_type,
            'company_size' => $company_size,
            'labels' => $labels,
            'dataValues' => $dataValues,
            'subcategories' => $subcategories,
            'strengths' => $assessmentData->strengths,
            'highRisks' => $assessmentData->highRisks,
            'mediumRisks' => $assessmentData->mediumRisks,
            'lowRisks' => $assessmentData->lowRisks,
            'improvementOpportunities' => $assessmentData->improvementOpportunities,
            'developingStatus' => $assessmentData->developingStatus ?? [],
            'suggestions' => $assessmentData->suggestions ?? [],
            'finalScore' => $assessmentData->finalScore,
            'maturityData' => $maturityData,
            'assessments' => $assessments, // اگر نیاز باشد
        ]);
    }

    public function getLatestCompletedGroup(Request $request)
    {
        $user = Auth::user();

        $latestCompletedGroup = AssessmentGroup::where('user_id', $user->id)
            ->where('status', 'completed')
            ->latest('created_at')
            ->first();

        return response()->json([
            'assessment_group_id' => $latestCompletedGroup ? $latestCompletedGroup->assessment_group_id : null,
        ]);
    }

    private function applyCompanyFilters($query, $user)
    {
        $sizeFilterApplied = false;
        if (empty($user->company_size)) {
            $query->where('applicable_large', 1); // پیش‌فرض: شرکت بزرگ
            $sizeFilterApplied = true;
        } elseif ($user->company_size == 'کوچک') {
            $query->where('applicable_small', 1);
            $sizeFilterApplied = true;
        } elseif ($user->company_size == 'متوسط') {
            $query->where('applicable_medium', 1);
            $sizeFilterApplied = true;
        } elseif ($user->company_size == 'بزرگ') {
            $query->where('applicable_large', 1);
            $sizeFilterApplied = true;
        }

        if (is_array($user->company_type)) {
            $companyTypeArray = $user->company_type;
        } else {
            $companyTypeArray = json_decode($user->company_type, true) ?? [];
        }

        if (!empty($companyTypeArray)) {
            $query->where(function ($q) use ($companyTypeArray) {
                $typeFilterApplied = false;
                if (in_array('تولیدی', $companyTypeArray)) {
                    $q->orWhere('applicable_manufacturing', 1);
                    $typeFilterApplied = true;
                }
                if (in_array('خدماتی', $companyTypeArray)) {
                    $q->orWhere('applicable_service', 1);
                    $typeFilterApplied = true;
                }
                if (in_array('پخش', $companyTypeArray)) {
                    $q->orWhere('applicable_distribution', 1);
                    $typeFilterApplied = true;
                }
                if (in_array('سرمایه‌گذاری', $companyTypeArray)) {
                    $q->orWhere('applicable_investment', 1);
                    $typeFilterApplied = true;
                }
                if (in_array('پروژه‌ای', $companyTypeArray)) {
                    $q->orWhere('applicable_project', 1);
                    $typeFilterApplied = true;
                }
                if (in_array('دانشگاهی', $companyTypeArray)) {
                    $q->orWhere('applicable_university', 1);
                    $typeFilterApplied = true;
                }
                if (in_array('تحقیقاتی', $companyTypeArray)) {
                    $q->orWhere('applicable_research', 1);
                    $typeFilterApplied = true;
                }
                if (in_array('بیمارستانی', $companyTypeArray)) {
                    $q->orWhere('applicable_hospital', 1);
                    $typeFilterApplied = true;
                }
                if (in_array('بانکی', $companyTypeArray)) {
                    $q->orWhere('applicable_banking', 1);
                    $typeFilterApplied = true;
                }
                if (!$typeFilterApplied) {
                    $q->orWhere('applicable_manufacturing', 1); // پیش‌فرض: تولیدی
                }
            });
        } else {
            $query->where('applicable_manufacturing', 1); // پیش‌فرض: تولیدی اگه خالی باشه
        }

        return $query;
    }

    public function compareMultiple(Request $request)
    {
        $validated = $request->validate([
            'company_ids' => 'required|array|min:2',
            'company_ids.*' => 'exists:users,id',
        ]);

        $companyIds = $validated['company_ids'];
        $user = Auth::user();

        $companies = User::whereIn('id', $companyIds)
            ->where('parent_id', $user->id)
            ->get();

        if ($companies->count() != count($companyIds)) {
            return redirect()->back()->with('error', 'یکی از شرکت‌های انتخاب‌شده جزو زیرمجموعه شما نیست.');
        }

        $assessments = [];
        foreach ($companies as $company) {
            $latestAssessment = FinalScore::where('user_id', $company->id)
                ->orderBy('created_at', 'desc')
                ->first();
            if ($latestAssessment) {
                $assessments[] = [
                    'company_alias' => $company->company_alias,
                    'assessment' => $latestAssessment,
                ];
            }
        }

        if (count($assessments) < 2) {
            return redirect()->back()->with('error', 'حداقل دو شرکت باید ارزیابی داشته باشند تا مقایسه انجام شود.');
        }

        return view('assessment.compare_multiple', compact('assessments'));
    }

    public function compareReports(Request $request)
    {
        $user = Auth::user();
        $assessment_group_id = $request->input('group_id');

        // دریافت تمام گروه‌های تکمیل‌شده کاربر، مرتب‌شده بر اساس تاریخ نزولی
        $completedGroups = FinalScore::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($completedGroups->isEmpty()) {
            return redirect()->route('profile')->with('error', 'هنوز هیچ ارزیابی تکمیل‌شده‌ای وجود ندارد.');
        }

        // پیدا کردن گروه فعلی
        $currentGroup = $completedGroups->firstWhere('assessment_group_id', $assessment_group_id);
        if (!$currentGroup) {
            return redirect()->route('profile')->with('error', 'ارزیابی موردنظر یافت نشد.');
        }

        // تنظیم تاریخ گروه فعلی
        $currentGroupDate = 'نامشخص';
        if ($currentGroup->created_at) {
            try {
                $currentGroupDate = Jalalian::fromDateTime($currentGroup->created_at)->format('j F Y');
            } catch (\Exception $e) {
                Log::error('Failed to convert date to Jalali in compareReports', [
                    'user_id' => $user->id,
                    'assessment_group_id' => $assessment_group_id,
                    'error' => $e->getMessage(),
                    'timestamp' => now()
                ]);
                $currentGroupDate = $currentGroup->created_at->format('Y/m/d');
            }
        }

        // پیدا کردن گروه قبلی (بلافاصله قبل از گروه فعلی)
        $previousGroup = $completedGroups
            ->where('created_at', '<', $currentGroup->created_at)
            ->sortByDesc('created_at')
            ->first();

        // تنظیم تاریخ گروه قبلی
        $previousGroupDate = 'نامشخص';
        if ($previousGroup && $previousGroup->created_at) {
            try {
                $previousGroupDate = Jalalian::fromDateTime($previousGroup->created_at)->format('j F Y');
            } catch (\Exception $e) {
                Log::error('Failed to convert previous date to Jalali in compareReports', [
                    'user_id' => $user->id,
                    'assessment_group_id' => $previousGroup->assessment_group_id,
                    'error' => $e->getMessage(),
                    'timestamp' => now()
                ]);
                $previousGroupDate = $previousGroup->created_at->format('Y/m/d');
            }
        }

        // محاسبه داده‌های ارزیابی برای گروه فعلی و قبلی
        $currentAssessment = $this->calculateAssessmentData($currentGroup, $user->id);
        $previousAssessment = $previousGroup ? $this->calculateAssessmentData($previousGroup, $user->id) : null;

        // محاسبه داده‌های بلوغ برای گروه فعلی و قبلی
        $currentMaturityData = $this->calculateMaturityLevel($assessment_group_id, $user->id);
        $previousMaturityData = $previousGroup ? $this->calculateMaturityLevel($previousGroup->assessment_group_id, $user->id) : [
            'overallMaturityLevel' => 0,
            'levelAverages' => [0, 0, 0, 0, 0],
            'maturityLevels' => [1, 2, 3, 4, 5],
        ];

        return view('assessment.compare', [
            'completedGroups' => $completedGroups,
            'assessment_group_id' => $assessment_group_id,
            'currentAssessment' => $currentAssessment,
            'previousAssessment' => $previousAssessment,
            'targetUser' => $user,
            'currentGroupDate' => $currentGroupDate,
            'previousGroupDate' => $previousGroupDate,
            'currentMaturityData' => $currentMaturityData,
            'previousMaturityData' => $previousMaturityData,
        ]);
    }

    public function storeAnswer(Request $request, $assessmentId, $questionId)
    {
        $user = Auth::user();
        $assessment = Assessment::where('id', $assessmentId)
            ->where('user_id', $user->id)
            ->where('status', 'draft')
            ->firstOrFail();

        $question = Question::findOrFail($questionId);

        $request->validate([
            'score' => 'required|integer|in:10,20,30,40,50,60,70,80,90,100',
        ]);

        Answer::updateOrCreate(
            [
                'user_id' => $user->id,
                'question_id' => $questionId,
                'assessment_id' => $assessment->id,
            ],
            [
                'score' => $request->score,
                'domain' => $question->domain,
            ]
        );

        $questionsQuery = Question::where('domain', $question->domain);
        $this->applyCompanyFilters($questionsQuery, $user);
        $questions = $questionsQuery->orderBy('id')->get();

        $nextQuestion = $questions->where('id', '>', $questionId)->first();

        if ($nextQuestion) {
            $assessment->update(['last_question_id' => $questionId]);
            return redirect()->route('assessment.questions', [
                'domain' => $question->domain,
                'question' => $nextQuestion->id,
            ])->with('success', 'پاسخ شما ثبت شد.');
        }

        $assessment->update(['last_question_id' => null]);
        return redirect()->route('assessment.finalize', $assessment->id)
            ->with('success', 'پاسخ‌ها با موفقیت ذخیره شدند.');
    }

    public function resumeAssessment(Request $request, $assessmentId)
    {
        $user = Auth::user();
        $assessment = Assessment::where('user_id', $user->id)
            ->where('id', $assessmentId)
            ->firstOrFail();

        $selectedDomain = $request->session()->get('selected_domain', []);

        return view('assessment.resume', compact('assessment', 'selectedDomain'));
    }

    public function finalize(Request $request, $assessmentId)
    {
        $assessment = Assessment::where('id', $assessmentId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $user = Auth::user();

        if (!$assessment->assessment_group_id || $assessment->assessment_group_id == 0) {
            $newGroupId = time() . rand(1000, 9999);
            $assessment->update(['assessment_group_id' => $newGroupId]);
            AssessmentGroup::create([
                'user_id' => $user->id,
                'assessment_group_id' => $newGroupId,
                'status' => 'in_progress',
            ]);
            $assessment = Assessment::find($assessment->id);
        }

        $answers = Answer::where('assessment_id', $assessment->id)->get();
        $totalWeightedScore = 0;
        $totalWeight = 0;

        foreach ($answers as $answer) {
            $question = Question::find($answer->question_id);
            if ($question) {
                $weightedScore = ($answer->score / 100) * $question->weight;
                $totalWeightedScore += $weightedScore;
                $totalWeight += $question->weight;
            }
        }

        $performancePercentage = $totalWeight > 0 ? ($totalWeightedScore / $totalWeight) * 100 : 0;

        $assessment->update([
            'status' => 'finalized',
            'finalized_date' => now(),
            'last_question_id' => null,
            'performance_percentage' => $performancePercentage,
        ]);

        $groupId = $assessment->assessment_group_id;
        if (!$groupId || $groupId == 0) {
            return redirect()->route('assessment.domains')
                ->with('error', 'خطایی در نهایی کردن ارزیابی رخ داد.');
        }

        $assessmentsInGroup = Assessment::where('assessment_group_id', $groupId)
            ->where('user_id', $user->id)
            ->get();

        $allDomains = DomainWeight::pluck('domain')->toArray();
        $completedDomains = $assessmentsInGroup->where('status', 'finalized')->pluck('domain')->toArray();

        if (count(array_intersect($allDomains, $completedDomains)) === count($allDomains)) {
            AssessmentGroup::where('assessment_group_id', $groupId)
                ->where('user_id', $user->id)
                ->update(['status' => 'completed']);

            $domainWeights = DomainWeight::pluck('weight', 'domain')->toArray();
            $totalWeightedScore = 0;
            $totalWeightSum = 0;

            foreach ($assessmentsInGroup as $assess) {
                $domain = $assess->domain;
                $weight = $domainWeights[$domain] ?? 0;
                $totalWeightedScore += ($assess->performance_percentage * $weight);
                $totalWeightSum += $weight;
            }

            $finalScore = $totalWeightSum > 0 ? $totalWeightedScore / $totalWeightSum : 0;

            FinalScore::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'assessment_group_id' => $groupId,
                ],
                [
                    'final_score' => $finalScore,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            if ($user->remaining_evaluations > 0) {
                $user->remaining_evaluations -= 1;
            } else {
                return redirect()->back()->with('error', 'اعتبار ارزیابی شما به پایان رسیده است.');
            }

            $user->self_assessments += 1;
            $creditStartDate = $user->credit_start_date ?? $user->created_at;
            $daysRemaining = max(0, 365 - now()->diffInDays($creditStartDate));
            $user->remaining_days = $daysRemaining;

            $user->save();

            if ($user->remaining_evaluations > 0 && $user->remaining_days > 0) {
                $newGroupId = time() . rand(1000, 9999);
                Assessment::create([
                    'user_id' => $user->id,
                    'assessment_group_id' => $newGroupId,
                    'created_date' => now(),
                    'status' => 'draft',
                    'domain' => null,
                ]);
                AssessmentGroup::create([
                    'user_id' => $user->id,
                    'assessment_group_id' => $newGroupId,
                    'status' => 'in_progress',
                ]);

                return redirect()->route('assessment.domains')
                    ->with('success', 'فرایند ارزیابی با موفقیت نهایی شد. برای مشاهده نتایج، به بخش «سوابق ارزیابی» در پروفایل خود مراجعه کنید.');
            }
        }

        return redirect()->route('assessment.domains')
            ->with('success', 'پاسخ‌های مربوط به این حوزه ارزیابی با موفقیت تکمیل شد.');
    }

    public function exitAssessment($assessmentId)
    {
        $assessment = Assessment::where('id', $assessmentId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return redirect()->route('assessment.domains')
            ->with('success', 'شما از ارزیابی خارج شدید.');
    }

    public function showDomains(Request $request)
    {
        $user = Auth::user();

        $inProgressGroup = AssessmentGroup::where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->first();

        if (!$inProgressGroup) {
            if ($user->remaining_evaluations > 0 && $user->remaining_days > 0) {
                $completedGroups = AssessmentGroup::where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->count();
                $newGroupId = time() . rand(1000, 9999) . ($completedGroups + 1);
                $inProgressGroup = AssessmentGroup::create([
                    'user_id' => $user->id,
                    'assessment_group_id' => $newGroupId,
                    'status' => 'in_progress',
                ]);
            } else {
                return redirect()->route('profile')->with('error', 'اعتبار شما برای ارزیابی جدید به پایان رسیده است.');
            }
        }

        session(['current_assessment_group_id' => $inProgressGroup->assessment_group_id]);
        $domains = DomainWeight::pluck('domain')->toArray();

        return view('assessment.domains', compact('domains'));
    }

    public function checkDomain(Request $request)
    {
        $user = Auth::user();
        $domain = $request->input('domain');

        Log::info('CheckDomain Debug', [
            'user_id' => $user->id,
            'domain' => $domain,
            'request_body' => $request->all(),
        ]);

        $inProgressGroup = AssessmentGroup::where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->first();

        Log::info('CheckDomain Debug - inProgressGroup', [
            'inProgressGroup' => $inProgressGroup ? $inProgressGroup->toArray() : null,
        ]);

        if (!$inProgressGroup) {
            return response()->json([
                'status' => 'completed',
                'hasIncompleteAssessment' => false,
            ]);
        }

        $assessment = Assessment::where('user_id', $user->id)
            ->where('domain', $domain)
            ->where('assessment_group_id', $inProgressGroup->assessment_group_id)
            ->first();

        Log::info('CheckDomain Debug - assessment', [
            'assessment' => $assessment ? $assessment->toArray() : null,
        ]);

        $status = 'not_started';
        $questionsQuery = Question::where('domain', $domain);

        $this->applyCompanyFilters($questionsQuery, $user);
        $questions = $questionsQuery->get();
        $totalQuestions = $questions->count();
        $validQuestionIds = $questions->pluck('id')->toArray();

        $answeredQuestions = Answer::where('user_id', $user->id)
            ->where('domain', $domain)
            ->whereIn('question_id', $validQuestionIds)
            ->whereIn('assessment_id', Assessment::where('assessment_group_id', $inProgressGroup->assessment_group_id)->pluck('id'))
            ->count();

        Log::info('CheckDomain Debug - answeredQuestions', [
            'totalQuestions' => $totalQuestions,
            'answeredQuestions' => $answeredQuestions,
            'validQuestionIds' => $validQuestionIds,
        ]);

        if ($assessment && $assessment->status === 'finalized') {
            $status = 'completed';
        } elseif ($answeredQuestions > 0 && $answeredQuestions < $totalQuestions) {
            $status = 'incomplete';
        } elseif ($answeredQuestions > 0) {
            $status = 'incomplete';
        } else {
            $status = 'not_started';
        }

        Log::info('CheckDomain Debug - final status', [
            'status' => $status,
            'assessment' => $assessment ? $assessment->toArray() : null,
            'answeredQuestions' => $answeredQuestions,
            'totalQuestions' => $totalQuestions,
        ]);

        return response()->json([
            'status' => $status,
            'hasIncompleteAssessment' => $assessment && $assessment->status === 'draft' && $answeredQuestions > 0,
        ]);
    }

    public function previousQuestion($assessmentId, $questionId)
    {
        $user = Auth::user();
        $assessment = Assessment::where('id', $assessmentId)
            ->where('user_id', $user->id)
            ->where('status', 'draft')
            ->firstOrFail();

        $question = Question::findOrFail($questionId);

        Answer::where('assessment_id', $assessment->id)
            ->where('question_id', $questionId)
            ->delete();

        $assessment->update(['last_question_id' => $questionId]);

        return redirect()->route('assessment.questions', ['domain' => $question->domain]);
    }

    public function report(Request $request, $assessment_group_id = null)
{
    $user = Auth::user();
    if (!$user) {
        Log::error('User not authenticated in report method', [
            'assessment_group_id' => $assessment_group_id,
            'timestamp' => now()
        ]);
        return redirect()->route('login')->with('error', 'لطفاً ابتدا وارد سیستم شوید.');
    }

    if (!$assessment_group_id) {
        Log::warning('No assessment_group_id provided in report method', [
            'user_id' => $user->id,
            'timestamp' => now()
        ]);
        return redirect()->route('profile')->with('error', 'شناسه گروه ارزیابی مشخص نشده است.');
    }

    Log::info('AssessmentController report method started', [
        'user_id' => $user->id,
        'assessment_group_id' => $assessment_group_id,
        'role' => $user->role,
        'timestamp' => now()
    ]);

    $isHolding = $user->role === 'holding';
    $finalScoreQuery = FinalScore::where('assessment_group_id', $assessment_group_id);
    if (!$isHolding) {
        $finalScoreQuery->where('user_id', $user->id);
    }
    $finalScore = $finalScoreQuery->first();

    if (!$finalScore) {
        Log::warning('No FinalScore found for report', [
            'user_id' => $user->id,
            'assessment_group_id' => $assessment_group_id,
            'timestamp' => now()
        ]);
        return redirect()->route('profile')->with('error', 'ارزیابی موردنظر یافت نشد.');
    }

    $targetUser = $user;
   // --- [REPLACE only inside report(...), in the `$isHolding` block] ---

if ($isHolding) {
    // اجازه بده هلدینگ به هر سطح از زیرمجموعه دسترسی داشته باشد
    if (!$this->isDescendantOrSelf($user->id, (int)$finalScore->user_id)) {
        Log::warning('Unauthorized descendant access attempt in report', [
            'holding_user_id' => $user->id,
            'final_score_user_id' => $finalScore->user_id,
            'assessment_group_id' => $assessment_group_id,
            'timestamp' => now()
        ]);
        return redirect()->route('profile')->with('error', 'شما اجازه دسترسی به این گزارش را ندارید.');
    }

    $targetUser = User::find($finalScore->user_id);
    if (!$targetUser) {
        Log::warning('Target user not found for holding user in report (after descendant check)', [
            'holding_user_id' => $user->id,
            'final_score_user_id' => $finalScore->user_id,
            'assessment_group_id' => $assessment_group_id,
            'timestamp' => now()
        ]);
        return redirect()->route('profile')->with('error', 'شناسه کاربر مقصد نامعتبر است.');
    }
} else {
        if ($finalScore->user_id != $user->id) {
            Log::warning('Unauthorized access attempt in report', [
                'user_id' => $user->id,
                'final_score_user_id' => $finalScore->user_id,
                'assessment_group_id' => $assessment_group_id,
                'timestamp' => now()
            ]);
            return redirect()->route('profile')->with('error', 'شما اجازه دسترسی به این گزارش را ندارید.');
        }
    }

    $completedGroups = FinalScore::where('user_id', $targetUser->id)
        ->orderBy('created_at', 'desc')
        ->get();

    if ($completedGroups->isEmpty()) {
        Log::warning('No completed groups found for target user', [
            'user_id' => $targetUser->id,
            'assessment_group_id' => $assessment_group_id,
            'timestamp' => now()
        ]);
        return redirect()->route('profile')->with('error', 'هنوز هیچ ارزیابی تکمیل‌شده‌ای وجود ندارد.');
    }

    if (!$completedGroups->firstWhere('assessment_group_id', $assessment_group_id)) {
        Log::warning('Assessment group not found in completed groups', [
            'user_id' => $targetUser->id,
            'assessment_group_id' => $assessment_group_id,
            'completed_groups' => $completedGroups->pluck('assessment_group_id')->toArray(),
            'timestamp' => now()
        ]);
        return redirect()->route('profile')->with('error', 'ارزیابی موردنظر برای این کاربر یافت نشد.');
    }

    $companyTypeArrayTarget = is_array($targetUser->company_type) ? $targetUser->company_type : (json_decode($targetUser->company_type, true) ?? []);
    $company_name = $targetUser->company_alias ?? 'نام شرکت';
    $company_type = $companyTypeArrayTarget ? implode('، ', $companyTypeArrayTarget) : 'خدماتی';
    $company_size = $targetUser->company_size ?? 'متوسط';

    $report_date = 'نامشخص';
    if ($finalScore && $finalScore->created_at) {
        $miladi_date = $finalScore->created_at->format('Y-m-d');
        Log::info('Report Date Retrieved from FinalScore', [
            'user_id' => $targetUser->id,
            'assessment_group_id' => $assessment_group_id,
            'miladi_date' => $miladi_date,
            'raw_created_at' => $finalScore->created_at,
            'timestamp' => now()
        ]);

        try {
            $jalali_date = Jalalian::fromDateTime($finalScore->created_at);
            $report_date = $jalali_date->format('j F Y');
            Log::info('Report Date Converted to Jalali', [
                'user_id' => $targetUser->id,
                'assessment_group_id' => $assessment_group_id,
                'jalali_date' => $report_date,
                'miladi_input' => $miladi_date,
                'timestamp' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to convert date to Jalali in report', [
                'user_id' => $targetUser->id,
                'assessment_group_id' => $assessment_group_id,
                'miladi_date' => $miladi_date,
                'error' => $e->getMessage(),
                'timestamp' => now()
            ]);
            $report_date = $miladi_date;
        }
    } else {
        Log::warning('No FinalScore record found for assessment group in report', [
            'user_id' => $targetUser->id,
            'assessment_group_id' => $assessment_group_id,
            'timestamp' => now()
        ]);
    }

    $assessmentData = $this->calculateAssessmentData($finalScore, $targetUser->id, $isHolding);
    $maturityData = $this->calculateMaturityLevel($assessment_group_id, $targetUser->id);

    Log::info('Report Data Debug', [
        'assessment_group_id' => $assessment_group_id,
        'user_id' => $targetUser->id,
        'lowRisks_count' => count($assessmentData->lowRisks),
        'mediumRisks_count' => count($assessmentData->mediumRisks),
        'highRisks_count' => count($assessmentData->highRisks),
        'strengths_count' => count($assessmentData->strengths),
        'improvementOpportunities_count' => count($assessmentData->improvementOpportunities),
        'labels' => $assessmentData->labels,
        'dataValues' => $assessmentData->dataValues,
        'subcategories' => $assessmentData->subcategories,
        'maturityData' => $maturityData,
        'timestamp' => now()
    ]);

    $labels = !empty($assessmentData->labels) ? $assessmentData->labels : [
        'حاکمیت فناوری اطلاعات',
        'امنیت اطلاعات و مدیریت ریسک',
        'زیرساخت فناوری',
        'خدمات پشتیبانی',
        'سامانه‌های کاربردی',
        'تحول دیجیتال',
        'هوشمندسازی'
    ];
    $dataValues = !empty($assessmentData->dataValues) ? $assessmentData->dataValues : [70, 85, 60, 75, 80, 65, 90];
    $subcategories = !empty($assessmentData->subcategories) ? $assessmentData->subcategories : [
        'حاکمیت فناوری اطلاعات' => [['name' => 'زیرحوزه 1', 'performance' => 75], ['name' => 'زیرحوزه 2', 'performance' => 65]],
        'امنیت اطلاعات و مدیریت ریسک' => [['name' => 'زیرحوزه 3', 'performance' => 80]],
    ];

    return view('assessment.report', [
        'completedGroups' => $completedGroups,
        'assessment_group_id' => $assessment_group_id,
        'company_name' => $company_name,
        'report_date' => $report_date,
        'company_type' => $company_type,
        'company_size' => $company_size,
        'labels' => $labels,
        'dataValues' => $dataValues,
        'subcategories' => $subcategories,
        'strengths' => $assessmentData->strengths,
        'highRisks' => $assessmentData->highRisks,
        'mediumRisks' => $assessmentData->mediumRisks,
        'lowRisks' => $assessmentData->lowRisks,
        'improvementOpportunities' => $assessmentData->improvementOpportunities,
        'developingStatus' => $assessmentData->developingStatus,
        'suggestions' => $assessmentData->suggestions,
        'finalScore' => $assessmentData->finalScore,
        'isHolding' => $isHolding,
        'subsidiaryAssessments' => $assessmentData->subsidiaryAssessments,
        'maturityData' => $maturityData,
    ]);
}

    public function printReport(Request $request, $assessment_group_id)
{
    $user = Auth::user();
    if (!$user) {
        Log::error('User not authenticated in printReport method', [
            'assessment_group_id' => $assessment_group_id,
            'timestamp' => now()
        ]);
        return redirect()->route('login')->with('error', 'لطفاً ابتدا وارد سیستم شوید.');
    }

    Log::info('AssessmentController printReport method started', [
        'user_id' => $user->id,
        'assessment_group_id' => $assessment_group_id,
        'role' => $user->role,
        'timestamp' => now()
    ]);

    $isHolding = $user->role === 'holding';
    $targetUser = $user;

    $finalScoreQuery = FinalScore::where('assessment_group_id', $assessment_group_id);
    if (!$isHolding) {
        $finalScoreQuery->where('user_id', $user->id);
    }
    $finalScore = $finalScoreQuery->first();

    if (!$finalScore) {
        Log::warning('No FinalScore found for printReport', [
            'user_id' => $user->id,
            'assessment_group_id' => $assessment_group_id,
            'timestamp' => now()
        ]);
        return redirect()->route('profile')->with('error', 'ارزیابی موردنظر یافت نشد.');
    }

    if ($isHolding) {
        $targetUser = User::where('id', $finalScore->user_id)
            ->where(function ($query) use ($user) {
                $query->where('parent_id', $user->id)
                      ->orWhere('id', $user->id);
            })
            ->first();

        if (!$targetUser) {
            Log::warning('Target user not found for holding user in printReport', [
                'user_id' => $user->id,
                'final_score_user_id' => $finalScore->user_id,
                'assessment_group_id' => $assessment_group_id,
                'timestamp' => now()
            ]);
            return redirect()->route('profile')->with('error', 'شما اجازه دسترسی به این گزارش را ندارید.');
        }
    } else {
        if ($finalScore->user_id != $user->id) {
            Log::warning('Unauthorized access attempt in printReport', [
                'user_id' => $user->id,
                'final_score_user_id' => $finalScore->user_id,
                'assessment_group_id' => $assessment_group_id,
                'timestamp' => now()
            ]);
            return redirect()->route('profile')->with('error', 'شما اجازه دسترسی به این گزارش را ندارید.');
        }
    }

    $completedGroups = FinalScore::where('user_id', $targetUser->id)
        ->orderBy('created_at', 'desc')
        ->get();

    if ($completedGroups->isEmpty()) {
        Log::warning('No completed groups found for target user in printReport', [
            'user_id' => $targetUser->id,
            'assessment_group_id' => $assessment_group_id,
            'timestamp' => now()
        ]);
        return redirect()->route('profile')->with('error', 'هنوز هیچ ارزیابی تکمیل‌شده‌ای وجود ندارد.');
    }

    if (!$completedGroups->firstWhere('assessment_group_id', $assessment_group_id)) {
        Log::warning('Assessment group not found in completed groups in printReport', [
            'user_id' => $targetUser->id,
            'assessment_group_id' => $assessment_group_id,
            'completed_groups' => $completedGroups->pluck('assessment_group_id')->toArray(),
            'timestamp' => now()
        ]);
        return redirect()->route('profile')->with('error', 'ارزیابی موردنظر برای این کاربر یافت نشد.');
    }

    $companyTypeArrayTarget = is_array($targetUser->company_type) ? $targetUser->company_type : (json_decode($targetUser->company_type, true) ?? []);
    $company_name = $targetUser->company_alias ?? 'نام شرکت';
    $company_type = $companyTypeArrayTarget ? implode('، ', $companyTypeArrayTarget) : 'خدماتی';
    $company_size = $targetUser->company_size ?? 'متوسط';

    $report_date = 'نامشخص';
    if ($finalScore && $finalScore->created_at) {
        $miladi_date = $finalScore->created_at->format('Y-m-d');
        Log::info('Print Report Date Retrieved from FinalScore', [
            'user_id' => $targetUser->id,
            'assessment_group_id' => $assessment_group_id,
            'miladi_date' => $miladi_date,
            'raw_created_at' => $finalScore->created_at,
            'timestamp' => now()
        ]);

        try {
            $jalali_date = Jalalian::fromDateTime($finalScore->created_at);
            $report_date = $jalali_date->format('j F Y');
            Log::info('Print Report Date Converted to Jalali', [
                'user_id' => $targetUser->id,
                'assessment_group_id' => $assessment_group_id,
                'jalali_date' => $report_date,
                'miladi_input' => $miladi_date,
                'timestamp' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to convert date to Jalali in printReport', [
                'user_id' => $targetUser->id,
                'assessment_group_id' => $assessment_group_id,
                'miladi_date' => $miladi_date,
                'error' => $e->getMessage(),
                'timestamp' => now()
            ]);
            $report_date = $miladi_date;
        }
    } else {
        Log::warning('No FinalScore record found for assessment group in printReport', [
            'user_id' => $targetUser->id,
            'assessment_group_id' => $assessment_group_id,
            'timestamp' => now()
        ]);
    }

    $assessmentData = $this->calculateAssessmentData($finalScore, $targetUser->id, $isHolding);
    $maturityData = $this->calculateMaturityLevel($assessment_group_id, $targetUser->id);

    Log::info('Print Report Data Debug', [
        'assessment_group_id' => $assessment_group_id,
        'user_id' => $targetUser->id,
        'lowRisks_count' => count($assessmentData->lowRisks),
        'mediumRisks_count' => count($assessmentData->mediumRisks),
        'highRisks_count' => count($assessmentData->highRisks),
        'strengths_count' => count($assessmentData->strengths),
        'improvementOpportunities_count' => count($assessmentData->improvementOpportunities),
        'labels' => $assessmentData->labels,
        'dataValues' => $assessmentData->dataValues,
        'subcategories' => $assessmentData->subcategories,
        'maturityData' => $maturityData,
        'timestamp' => now()
    ]);

    $labels = !empty($assessmentData->labels) ? $assessmentData->labels : [
        'حاکمیت فناوری اطلاعات',
        'امنیت اطلاعات و مدیریت ریسک',
        'زیرساخت فناوری',
        'خدمات پشتیبانی',
        'سامانه‌های کاربردی',
        'تحول دیجیتال',
        'هوشمندسازی'
    ];
    $dataValues = !empty($assessmentData->dataValues) ? $assessmentData->dataValues : [70, 85, 60, 75, 80, 65, 90];
    $subcategories = !empty($assessmentData->subcategories) ? $assessmentData->subcategories : [
        'حاکمیت فناوری اطلاعات' => [['name' => 'زیرحوزه 1', 'performance' => 75], ['name' => 'زیرحوزه 2', 'performance' => 65]],
        'امنیت اطلاعات و مدیریت ریسک' => [['name' => 'زیرحوزه 3', 'performance' => 80]],
    ];

    return view('assessment.print-report', [
        'assessment_group_id' => $assessment_group_id,
        'company_name' => $company_name,
        'report_date' => $report_date,
        'company_type' => $company_type,
        'company_size' => $company_size,
        'labels' => $labels,
        'dataValues' => $dataValues,
        'subcategories' => $subcategories,
        'strengths' => $assessmentData->strengths,
        'highRisks' => $assessmentData->highRisks,
        'mediumRisks' => $assessmentData->mediumRisks,
        'lowRisks' => $assessmentData->lowRisks,
        'improvementOpportunities' => $assessmentData->improvementOpportunities,
        'developingStatus' => $assessmentData->developingStatus,
        'suggestions' => $assessmentData->suggestions,
        'finalScore' => $assessmentData->finalScore,
        'isHolding' => $isHolding,
        'subsidiaryAssessments' => $assessmentData->subsidiaryAssessments,
        'maturityData' => $maturityData,
    ]);
}

    public function showResult(Request $request, $assessmentId)
    {
        $user = Auth::user();
        $assessment = Assessment::where('id', $assessmentId)
            ->where('user_id', $user->id)
            ->where('status', 'finalized')
            ->firstOrFail();

        $finalScore = FinalScore::where('assessment_group_id', $assessment->assessment_group_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$finalScore) {
            return redirect()->route('profile')->with('error', 'امتیاز نهایی برای این ارزیابی یافت نشد.');
        }

        $assessmentData = $this->calculateAssessmentData($finalScore, $user->id);
        $maturityData = $this->calculateMaturityLevel($assessment->assessment_group_id, $user->id);

        return view('assessment.result', [
            'assessment' => $assessment,
            'finalScore' => $finalScore->final_score,
            'labels' => $assessmentData->labels,
            'dataValues' => $assessmentData->dataValues,
            'maturityData' => $maturityData,
        ]);
    }

    public function calculateMaturityLevel($assessmentGroupId, $userId)
    {
        $user = User::find($userId);
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

        $overallMaturityLevel = 0; // شروع از صفر
        $threshold = 50; // حداقل 50%
        for ($level = 0; $level < 5; $level++) {
            $average = $levelAverages[$level];
            if ($average >= $threshold) {
                $overallMaturityLevel = $level + 1; // به‌روزرسانی به بالاترین سطح پاس‌شده
            } else if ($average < $threshold) {
                if ($level == 0 && $average < $threshold) {
                    $overallMaturityLevel = 1; // اگه سطح 1 رد بشه، 1 برگردونه
                }
                break; // توقف تو اولین سطحی که رد می‌شه
            }
        }
        // اگه همه سطوح 0 تا 3 پاس شدن و سطح 4 رد بشه، سطح 4 رو نگه می‌داره
        if ($overallMaturityLevel == 5 && $levelAverages[4] < $threshold) {
            $overallMaturityLevel = 4; // بالاترین سطحی که کامل پاس شده
        }

        // فقط داده‌های ضروری رو برگردون، بدون نمایش مستقیم آرایه خام
        return [
            'overallMaturityLevel' => $overallMaturityLevel,
            'levelAverages' => array_map(fn($avg) => round($avg, 1), $levelAverages),
            'maturityLevels' => [1, 2, 3, 4, 5],
        ];
    }
}