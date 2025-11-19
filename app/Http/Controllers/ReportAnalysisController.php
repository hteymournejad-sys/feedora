<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Assessment;
use App\Models\AssessmentGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // اضافه کردن فاساد Log
use Morilog\Jalali\Jalalian;

class ReportAnalysisController extends Controller
{
    


protected function getAllSubsidiaryIds($parentId, $users)
{
    $subsidiaryIds = [];
    $directSubsidiaries = $users->where('parent_id', $parentId)->pluck('id')->toArray();

    foreach ($directSubsidiaries as $subsidiaryId) {
        $subsidiaryIds[] = $subsidiaryId;
        $subsidiaryIds = array_merge($subsidiaryIds, $this->getAllSubsidiaryIds($subsidiaryId, $users));
    }

    return array_unique($subsidiaryIds);
}




public function analysisReport(Request $request)
    {
        $user = Auth::user();
        $assessment_group_id = $request->query('group_id');
        $highRisks = [];
        $mediumRisks = [];
        $lowRisks = [];
        $subcategories = [];
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
        $maturityData = [
            'levelAverages' => [0, 0, 0, 0, 0],
            'overallMaturityLevel' => null
        ];
        $assessmentGroup = null; // مقدار پیش‌فرض

        // لاگ برای دیباگ
        Log::info('analysisReport called', [
            'user_id' => $user->id,
            'role' => $user->role,
            'assessment_group_id' => $assessment_group_id,
            'timestamp' => now()
        ]);

        // دریافت گروه‌های ارزیابی تکمیل‌شده
        $completedGroups = collect();
       if ($user->role === 'holding') {
    $allUsers = User::all(); // همه کاربران برای ساخت درخت
    $subsidiaryIds = $this->getAllSubsidiaryIds($user->id, $allUsers); // استفاده از متد recursive
    $completedGroups = AssessmentGroup::whereIn('user_id', $subsidiaryIds)
        ->where('status', 'completed')
        ->orderBy('created_at', 'desc')
        ->get();
} else {
    $completedGroups = AssessmentGroup::where('user_id', $user->id)
        ->where('status', 'completed')
        ->orderBy('created_at', 'desc')
        ->get();
}
        Log::info('Completed groups retrieved', [
            'user_id' => $user->id,
            'completed_groups_count' => $completedGroups->count(),
            'assessment_group_ids' => $completedGroups->pluck('assessment_group_id')->toArray(),
            'timestamp' => now()
        ]);

        // پیدا کردن گروه ارزیابی
        if ($assessment_group_id) {
            $assessmentGroup = AssessmentGroup::where('assessment_group_id', $assessment_group_id)
                ->where('status', 'completed')
                ->first();

            if (!$assessmentGroup) {
                Log::warning('Assessment group not found', [
                    'user_id' => $user->id,
                    'assessment_group_id' => $assessment_group_id,
                    'timestamp' => now()
                ]);
                return view('assessment.report-analysis', compact(
                    'highRisks',
                    'mediumRisks',
                    'lowRisks',
                    'subcategories',
                    'company_name',
                    'completedGroups',
                    'assessment_group_id',
                    'labels',
                    'dataValues',
                    'maturityData',
                    'assessmentGroup'
                ))->with('error', 'ارزیابی موردنظر یافت نشد یا تکمیل نشده است.');
            }

            // بررسی دسترسی کاربر
           $assessmentUser = User::find($assessmentGroup->user_id);
$hasAccess = false;

if ($user->id === $assessmentGroup->user_id) {
    $hasAccess = true; // دسترسی به گزارش خودش
} elseif ($user->role === 'holding') {
    $allUsers = User::all(); // همه کاربران برای درخت
    $subsidiaryIds = $this->getAllSubsidiaryIds($user->id, $allUsers);
    if (in_array($assessmentGroup->user_id, $subsidiaryIds)) {
        $hasAccess = true; // چک recursive برای همه سطوح
    }
}

if (!$assessmentUser || !$hasAccess) {
    Log::warning('Unauthorized access to assessment', [
        'user_id' => $user->id,
        'assessment_user_id' => $assessmentGroup->user_id,
        'assessment_group_id' => $assessment_group_id,
        'timestamp' => now()
    ]);
    $company_name = 'نامشخص'; // fallback برای جلوگیری از undefined
    return view('assessment.report-analysis', compact(
        'highRisks',
        'mediumRisks',
        'lowRisks',
        'subcategories',
        'company_name', // حالا تعریف‌شده
        'completedGroups',
        'assessment_group_id',
        'labels',
        'dataValues',
        'maturityData',
        'assessmentGroup'
    ))->with('error', 'شما اجازه دسترسی به این گزارش را ندارید.');
}
            // دریافت داده‌های ارزیابی
            $assessments = Assessment::where('assessment_group_id', $assessment_group_id)
                ->where('status', 'finalized')
                ->get();

            $domainScores = [];
            $maturitySums = array_fill(0, 5, 0);
            $maturityCounts = array_fill(0, 5, 0);

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
                    $maturityLevel = $question->Maturity_level ?? 0;

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

                    if ($maturityLevel >= 1 && $maturityLevel <= 5) {
                        $maturitySums[$maturityLevel - 1] += $score;
                        $maturityCounts[$maturityLevel - 1]++;
                    }

                    if ($weight == 1 || $weight == 8) {
                        if (in_array($score, [10, 20])) {
                            $lowRisks[] = ['weight' => $weight, 'content' => $question->risks];
                        }
                    } elseif (in_array($weight, [6, 7, 9])) {
                        if (in_array($score, [10, 20])) {
                            $mediumRisks[] = ['weight' => $weight, 'content' => $question->risks];
                        }
                    } elseif ($weight == 10) {
                        if (in_array($score, [10, 20])) {
                            $highRisks[] = ['weight' => $weight, 'content' => $question->risks];
                        }
                    }
                }
            }

            foreach ($labels as $index => $label) {
                $domainKey = $label;
                if (isset($domainScores[$domainKey])) {
                    $scores = $domainScores[$domainKey];
                    $dataValues[$index] = count($scores) > 0 ? array_sum($scores) / count($scores) : 0;
                }
            }

            $maturityData['levelAverages'] = array_map(function ($sum, $count) {
                return $count > 0 ? round($sum / $count, 1) : 0;
            }, $maturitySums, $maturityCounts);
        }

        $highRisks = collect($highRisks)->sortByDesc('weight')->values()->all();
        $mediumRisks = collect($mediumRisks)->sortByDesc('weight')->values()->all();
        $lowRisks = collect($lowRisks)->sortByDesc('weight')->values()->all();

        $company_name = $user->company_alias ?? 'نامشخص';
        if ($assessment_group_id && $assessmentGroup) {
            $companyOwner = User::find($assessmentGroup->user_id);
            $company_name = $companyOwner ? $companyOwner->company_alias : $company_name;
        }

        return view('assessment.report-analysis', compact(
            'highRisks',
            'mediumRisks',
            'lowRisks',
            'subcategories',
            'company_name',
            'completedGroups',
            'assessment_group_id',
            'labels',
            'dataValues',
            'maturityData',
            'assessmentGroup'
        ));
    }
}