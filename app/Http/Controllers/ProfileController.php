<?php
namespace App\Http\Controllers;

use App\Models\Answer;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Assessment;
use App\Models\Payment;
use App\Models\Question;
use App\Models\FinalScore;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\AssessmentGroup;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Validation\ValidationException;
use Morilog\Jalali\Jalalian;
use App\Models\NonTechnicalAssessment; // Add this
use App\Models\ItPersonnel; // Add this

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * بررسی دسترسی ادمین
     */
    private function checkAdmin()
    {
        if (!Auth::check() || Auth::user()->is_admin != 1) {
            return redirect()->route('home')->with('error', 'شما اجازه دسترسی به این بخش را ندارید.');
        }
        return null;
    }

    /**
     * بررسی دسترسی مدیر سامانه یا ادمین
     */
private function checkSystemManagerOrAdmin()
{
    if (!Auth::check()) {
        \Log::warning('Unauthorized access attempt: User not authenticated');
        return redirect()->route('home')->with('error', 'شما اجازه دسترسی به این بخش را ندارید.');
    }

    $user = Auth::user();
    if ($user->role != 'system_manager' && $user->is_admin != 1) {
        \Log::warning('Unauthorized access attempt', [
            'user_id' => $user->id,
            'role' => $user->role,
            'is_admin' => $user->is_admin
        ]);
        return redirect()->route('home')->with('error', 'شما اجازه دسترسی به این بخش را ندارید.');
    }
    return null;
}
    /**
     * بررسی دسترسی مدیر سامانه
     */
    private function checkSystemManager()
    {
        if (!Auth::check() || Auth::user()->role != 'system_manager') {
            return redirect()->route('home')->with('error', 'شما اجازه دسترسی به این بخش را ندارید.');
        }
        return null;
    }


public function showNonTechnicalForm()
    {
        \Log::info('Loading non_technical_assessment view');
        $user = Auth::user();
        $nonTechData = NonTechnicalAssessment::where('user_id', $user->id)->latest()->first();
        $itPersonnel = $nonTechData ? ItPersonnel::where('non_technical_assessment_id', $nonTechData->id)->get() : collect();
        $expertises = [
            'امنیت اطلاعات',
            'زیرساخت',
            'سامانه‌ها',
            'هوشمندسازی',
            'تحول دیجیتال',
            'خدمات پشتیبانی',
            'حاکمیت فناوری اطلاعات'
        ];

        return view('non_technical_assessment', compact('nonTechData', 'itPersonnel', 'expertises'));
    }





// ProfileController.php

public function showCompareNonTechCompanies(Request $request)
{
    $validated = $request->validate([
        'company_ids' => 'required|array|min:1',
        'company_ids.*' => 'exists:users,id',
    ]);

    $companyIds = $validated['company_ids'];
    $companies = User::whereIn('id', $companyIds)
        ->where('role', '!=', 'holding') // Assuming subsidiaries are not holdings
        ->get();

    $nonTechData = [];
    $itPersonnelData = [];
    $specialtiesDistribution = [];
    $educationDistribution = [];
    $itStaffWithTrainingPercentage = [];
    foreach ($companies as $company) {
        $data = NonTechnicalAssessment::where('user_id', $company->id)
            ->orderBy('year', 'desc')
            ->first();
        if ($data) {
            // Compute ratios
            $total_it_staff = $data->full_time_it_staff + $data->part_time_it_staff;
            $data->user_to_it_ratio = $total_it_staff > 0 ? round($data->active_users / $total_it_staff, 2) : 0;
            $data->budget_realization = $data->it_budget > 0 ? round(($data->it_expenditure / $data->it_budget) * 100, 2) : 0;
            $data->it_cost_per_user = $data->active_users > 0 ? round($data->it_expenditure / $data->active_users, 2) : 0;
            $data->training_hours_per_it_staff = $total_it_staff > 0 ? round($data->it_training_hours / $total_it_staff, 2) : 0;
            $data->knowledge_activity_index = $total_it_staff > 0 ? round(($data->internal_events + $data->external_events) / $total_it_staff, 2) : 0;

            $nonTechData[$company->id] = $data;

            // Fetch IT Personnel
            $personnel = ItPersonnel::where('non_technical_assessment_id', $data->id)->get();
            $itPersonnelData[$company->id] = $personnel;

            // Calculate distributions
            $specialtiesCount = [];
            $educationCount = [];
            $withTraining = 0;
            foreach ($personnel as $person) {
                // Education
                $educationCount[$person->education] = ($educationCount[$person->education] ?? 0) + 1;

                // Specialties
                $expertises = is_string($person->expertise) ? json_decode($person->expertise, true) : $person->expertise;
                $expertises = is_array($expertises) ? $expertises : [];
                foreach ($expertises as $exp) {
                    $specialtiesCount[$exp] = ($specialtiesCount[$exp] ?? 0) + 1;
                }

                // Training
                if (!empty($person->training_courses)) {
                    $withTraining++;
                }
            }

            $specialtiesDistribution[$company->id] = $specialtiesCount;
            $educationDistribution[$company->id] = $educationCount;
            $totalPersonnel = $personnel->count();
            $itStaffWithTrainingPercentage[$company->id] = $totalPersonnel > 0 ? round(($withTraining / $totalPersonnel) * 100, 2) : 0;
        }
    }

    // Prepare data for charts
    $companyNames = $companies->pluck('company_alias')->toArray();

    // Bar Chart Data: IT Staff
    $itStaffData = [
        'full_time' => $companies->map(fn($c) => $nonTechData[$c->id]->full_time_it_staff ?? 0)->toArray(),
        'part_time' => $companies->map(fn($c) => $nonTechData[$c->id]->part_time_it_staff ?? 0)->toArray(),
    ];

    // Bar Chart Data: Budget and Expenditure
    $budgetCostData = [
        'budget' => $companies->map(fn($c) => $nonTechData[$c->id]->it_budget ?? 0)->toArray(),
        'expenditure' => $companies->map(fn($c) => $nonTechData[$c->id]->it_expenditure ?? 0)->toArray(),
    ];

    // Pie Chart Data: Specialties and Education per company
    $pieData = [];
    foreach ($companies as $company) {
        $pieData[$company->id] = [
            'specialties_labels' => array_keys($specialtiesDistribution[$company->id] ?? []),
            'specialties_data' => array_values($specialtiesDistribution[$company->id] ?? []),
            'education_labels' => array_keys($educationDistribution[$company->id] ?? []),
            'education_data' => array_values($educationDistribution[$company->id] ?? []),
        ];
    }

    // Radar Chart Data: Key Indices
    $radarData = [];
    foreach ($companies as $company) {
        $radarData[] = [
            'label' => $company->company_alias,
            'data' => [
                $nonTechData[$company->id]->user_to_it_ratio ?? 0,
                $nonTechData[$company->id]->budget_realization ?? 0,
                $nonTechData[$company->id]->training_hours_per_it_staff ?? 0,
                $nonTechData[$company->id]->knowledge_activity_index ?? 0,
            ],
        ];
    }

    return view('compare_non_tech', compact(
        'companies', 'nonTechData', 'itStaffWithTrainingPercentage', 'companyNames',
        'itStaffData', 'budgetCostData', 'pieData', 'radarData'
    ));
}











// Helper method to collect all descendant user IDs (direct and indirect subsidiaries)
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








public function index(Request $request)
{
    $user = Auth::user();
    if (!$user) {
        return redirect()->route('login')->with('error', 'لطفاً ابتدا وارد شوید.');
    }

    if ($user->is_admin == 1) {
        return redirect()->route('admin.profile');
    }

    if ($user->role == 'system_manager') {
        return redirect()->route('system-manager.profile');
    }

    // دریافت تاریخچه ارزیابی‌های کاربر
    $assessmentHistory = FinalScore::where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();

    \Log::info('Assessment History for user:', [
        'user_id' => $user->id,
        'count' => $assessmentHistory->count(),
        'assessment_group_ids' => $assessmentHistory->pluck('assessment_group_id')->toArray(),
    ]);

    // دریافت پرداخت‌های کاربر
    $payments = Payment::where('user_id', $user->id)
        ->orderBy('payment_date', 'desc')
        ->get();
    $totalPayments = $payments->sum('amount');

    // متغیرهای پیش‌فرض
    $subsidiaryAssessments = collect();
    $companiesForComparison = collect();
    $companiesForNonTechComparison = collect();
    $tree = collect();
    $topCompanies = collect(); // جدید: متغیر برای پنج شرکت برتر
    $nonTechData = NonTechnicalAssessment::where('user_id', $user->id)->latest()->first();
    $itPersonnel = $nonTechData ? ItPersonnel::where('non_technical_assessment_id', $nonTechData->id)->get() : collect();
    $holdings = collect();
    $activeTab = $request->query('active_tab', 'settings');
    $aggregatedNonTech = null;
    $allSubsidiaryItPersonnel = collect();

    if ($user->role === 'holding') {
        // کدهای موجود برای allUsers، holdings، subsidiaryIds، subsidiaryUsers بدون تغییر
        $allUsers = User::whereIn('role', ['normal', 'holding'])
            ->where('is_admin', 0)
            ->where('role', '!=', 'system_manager')
            ->when($request->search, function ($query) use ($request) {
                $query->where('company_alias', 'like', '%' . $request->search . '%');
            })
            ->get();

        $holdings = User::where('role', 'holding')
            ->where('is_admin', 0)
            ->where('role', '!=', 'system_manager')
            ->where('id', '!=', $user->id)
            ->get(['id', 'company_alias']);

        $subsidiaryIds = $this->getAllSubsidiaryIds($user->id, $allUsers);
        $subsidiaryUsers = $allUsers->whereIn('id', $subsidiaryIds);

        $hierarchyLevel = $request->input('hierarchy_level');
        $selectedHoldingId = $request->input('holding_id');
        $selectedCompanyType = $request->input('company_type');

        if ($hierarchyLevel) {
            $subsidiaryUsers = $subsidiaryUsers->filter(function ($subsidiary) use ($user, $selectedHoldingId, $hierarchyLevel) {
                $level = 0;
                $current = $subsidiary;
                $targetId = $selectedHoldingId ?: $user->id;
                while ($current && $current->parent_id) {
                    $level++;
                    $current = User::find($current->parent_id);
                    if ($current && $current->id == $targetId) {
                        break;
                    }
                }
                return $level == $hierarchyLevel;
            });
        }

        if ($selectedHoldingId) {
            $subsidiaryIds = $this->getAllSubsidiaryIds($selectedHoldingId, $allUsers);
            $subsidiaryUsers = $subsidiaryUsers->whereIn('id', $subsidiaryIds);
        }

        if ($selectedCompanyType) {
            $subsidiaryUsers = $subsidiaryUsers->filter(function ($subsidiary) use ($selectedCompanyType) {
                $companyTypes = $subsidiary->company_type;
                return is_array($companyTypes) && in_array($selectedCompanyType, $companyTypes);
            });
        }

        $subsidiaryAssessments = FinalScore::whereIn('user_id', $subsidiaryUsers->pluck('id'))
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        // محاسبه پنج شرکت برتر بر اساس امتیاز ارزیابی فنی
        $topCompanies = $subsidiaryUsers->map(function ($subsidiary) {
            $latestAssessment = FinalScore::where('user_id', $subsidiary->id)
                ->orderBy('created_at', 'desc')
                ->first();
            return [
                'id' => $subsidiary->id,
                'company_alias' => $subsidiary->company_alias,
                'latest_assessment_date' => $latestAssessment ? Jalalian::fromDateTime($latestAssessment->created_at)->format('j F Y') : 'ارزیابی نشده',
                'latest_assessment_score' => $latestAssessment ? round($latestAssessment->final_score, 2) : null,
            ];
        })->filter(function ($company) {
            return $company['latest_assessment_score'] !== null; // فقط شرکت‌هایی با امتیاز
        })->sortByDesc('latest_assessment_score')->take(5)->values();

        // کدهای موجود برای aggregatedNonTech، allSubsidiaryItPersonnel، companiesForComparison، companiesForNonTechComparison و tree بدون تغییر
        $aggregatedNonTech = new \stdClass();
        $aggregatedNonTech->active_users = 0;
        $aggregatedNonTech->workstations = 0;
        $aggregatedNonTech->full_time_it_staff = 0;
        $aggregatedNonTech->part_time_it_staff = 0;
        $aggregatedNonTech->it_budget = 0;
        $aggregatedNonTech->it_expenditure = 0;
        $aggregatedNonTech->internal_events = 0;
        $aggregatedNonTech->external_events = 0;
        $aggregatedNonTech->it_training_hours = 0;
        $aggregatedNonTech->general_training_hours = 0;
        $aggregatedNonTech->year = now()->year;

        $allSubsidiaryItPersonnel = collect();

        foreach ($subsidiaryUsers as $subsidiaryUser) {
            $latestNonTech = NonTechnicalAssessment::where('user_id', $subsidiaryUser->id)->latest()->first();
            if ($latestNonTech) {
                $aggregatedNonTech->active_users += $latestNonTech->active_users ?? 0;
                $aggregatedNonTech->workstations += $latestNonTech->workstations ?? 0;
                $aggregatedNonTech->full_time_it_staff += $latestNonTech->full_time_it_staff ?? 0;
                $aggregatedNonTech->part_time_it_staff += $latestNonTech->part_time_it_staff ?? 0;
                $aggregatedNonTech->it_budget += $latestNonTech->it_budget ?? 0;
                $aggregatedNonTech->it_expenditure += $latestNonTech->it_expenditure ?? 0;
                $aggregatedNonTech->internal_events += $latestNonTech->internal_events ?? 0;
                $aggregatedNonTech->external_events += $latestNonTech->external_events ?? 0;
                $aggregatedNonTech->it_training_hours += $latestNonTech->it_training_hours ?? 0;
                $aggregatedNonTech->general_training_hours += $latestNonTech->general_training_hours ?? 0;
                $aggregatedNonTech->year = $latestNonTech->year ?? $aggregatedNonTech->year;

                $personnel = ItPersonnel::where('non_technical_assessment_id', $latestNonTech->id)->get();
                $allSubsidiaryItPersonnel = $allSubsidiaryItPersonnel->merge($personnel);
            }
        }

        $companiesForComparison = $subsidiaryUsers->map(function ($subsidiary) {
            $latestAssessment = FinalScore::where('user_id', $subsidiary->id)
                ->orderBy('created_at', 'desc')
                ->first();
            return [
                'id' => $subsidiary->id,
                'company_alias' => $subsidiary->company_alias,
                'latest_assessment_date' => $latestAssessment ? Jalalian::fromDateTime($latestAssessment->created_at)->format('j F Y') : 'ارزیابی نشده',
                'latest_assessment_score' => $latestAssessment ? round($latestAssessment->final_score, 2) : null,
            ];
        });

        $companiesForNonTechComparison = $subsidiaryUsers->map(function ($subsidiary) {
            $latestNonTech = NonTechnicalAssessment::where('user_id', $subsidiary->id)
                ->orderBy('created_at', 'desc')
                ->first();
            return [
                'id' => $subsidiary->id,
                'company_alias' => $subsidiary->company_alias,
                'active_users' => $latestNonTech ? $latestNonTech->active_users : 'نامشخص',
                'full_time_it_staff' => $latestNonTech ? $latestNonTech->full_time_it_staff : 'نامشخص',
            ];
        });

        $tree = $this->buildTree($allUsers, $user->id);
    }

    if ($user->remaining_evaluations <= 0 || $user->remaining_days <= 0) {
        session()->flash('notification', 'امکان انجام ارزیابی جدید وجود ندارد. لطفاً برای خرید بسته جدید اقدام کنید.');
    }

    return view('profile', compact(
        'user',
        'assessmentHistory',
        'payments',
        'totalPayments',
        'subsidiaryAssessments',
        'companiesForComparison',
        'companiesForNonTechComparison',
        'nonTechData',
        'itPersonnel',
        'holdings',
        'activeTab',
        'tree',
        'aggregatedNonTech',
        'allSubsidiaryItPersonnel',
        'topCompanies' // جدید: اضافه کردن به compact
    ));
}








public function storeNonTechnical(Request $request)
    {
        try {
            $user = Auth::user();

            // Log incoming request data for debugging
            \Log::info('NonTechnicalAssessment Request Data:', $request->all());

            $validated = $request->validate([
                'active_users' => 'nullable|integer|min:0',
                'workstations' => 'nullable|integer|min:0',
                'full_time_it_staff' => 'nullable|integer|min:0',
                'part_time_it_staff' => 'nullable|integer|min:0',
                'it_budget' => 'nullable|numeric|min:0',
                'it_expenditure' => 'nullable|numeric|min:0',
                'internal_events' => 'nullable|integer|min:0',
                'external_events' => 'nullable|integer|min:0',
                'it_training_hours' => 'nullable|integer|min:0',
                'general_training_hours' => 'nullable|integer|min:0',
                'personnel' => 'nullable|array',
                'personnel.*.full_name' => 'required|string|max:255',
                'personnel.*.education' => 'required|in:دیپلم,فوق‌دیپلم,لیسانس,فوق‌لیسانس,دکتری,فوق‌دکتری',
                'personnel.*.expertise' => 'nullable|array',
                'personnel.*.expertise.*' => 'in:امنیت اطلاعات,زیرساخت,سامانه‌ها,هوشمندسازی,تحول دیجیتال,خدمات پشتیبانی,حاکمیت فناوری اطلاعات',
                'personnel.*.position' => 'required|string|max:255',
                'personnel.*.work_experience' => 'required|integer|min:0',
                'personnel.*.training_courses' => 'nullable|string|max:500',
            ]);

            // Filter out empty personnel rows
            $filteredPersonnel = array_filter($validated['personnel'] ?? [], function ($person) {
                return !empty($person['full_name']) && !empty($person['position']) && !empty($person['work_experience']);
            });

            // Log filtered personnel data
            \Log::info('Filtered Personnel Data:', $filteredPersonnel);

            // Store or update non-technical assessment
            $nonTechData = NonTechnicalAssessment::updateOrCreate(
    ['user_id' => $user->id, 'year' => now()->year - 1],
                [
                    'active_users' => $validated['active_users'],
                    'workstations' => $validated['workstations'],
                    'full_time_it_staff' => $validated['full_time_it_staff'],
                    'part_time_it_staff' => $validated['part_time_it_staff'],
                    'it_budget' => $validated['it_budget'],
                    'it_expenditure' => $validated['it_expenditure'],
                    'internal_events' => $validated['internal_events'],
                    'external_events' => $validated['external_events'],
                    'it_training_hours' => $validated['it_training_hours'],
                    'general_training_hours' => $validated['general_training_hours'],
                ]
            );

            // Log non-technical assessment data
            \Log::info('NonTechnicalAssessment Saved:', $nonTechData->toArray());

            // Delete existing personnel records to avoid duplicates
            if ($nonTechData) {
                ItPersonnel::where('non_technical_assessment_id', $nonTechData->id)->delete();
            }

            // Store IT personnel
            if (!empty($filteredPersonnel)) {
                foreach ($filteredPersonnel as $person) {
                    $personnel = ItPersonnel::create([
                        'non_technical_assessment_id' => $nonTechData->id,
                        'user_id' => $user->id,
                        'full_name' => $person['full_name'],
                        'education' => $person['education'],
                        'expertise' => $person['expertise'] ?? [],
                        'position' => $person['position'],
                        'work_experience' => $person['work_experience'],
                        'training_courses' => $person['training_courses'] ?? null,
                    ]);

                    // Log each personnel record
                    \Log::info('ItPersonnel Saved:', $personnel->toArray());
                }
            }

            return redirect()->route('profile')->with('success', 'ارزیابی غیر فنی با موفقیت ثبت شد.');
        } catch (\Exception $e) {
            \Log::error('Error in storeNonTechnical:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'خطایی در ذخیره‌سازی اطلاعات رخ داد. لطفاً دوباره تلاش کنید.');
        }
    }



public function changePassword(Request $request)
{
    $user = Auth::user();
    if (!$user) {
        return redirect()->route('login')->with('error', 'لطفاً ابتدا وارد شوید.');
    }

    $validated = $request->validate([
        'current_password' => 'required|string',
        'new_password' => 'required|string|min:8|confirmed',
    ]);

    // بررسی رمز عبور فعلی
    if (!Hash::check($validated['current_password'], $user->password)) {
        return redirect()->back()->withErrors(['current_password' => 'رمز عبور فعلی نادرست است.']);
    }

    // به‌روزرسانی رمز عبور
    $user->password = Hash::make($validated['new_password']);
    $user->save();

    return redirect()->route('profile', ['active_tab' => 'settings'])->with('success', 'رمز عبور با موفقیت تغییر کرد.');
}


public function updatePassword(Request $request, $userId)
{
    if ($redirect = $this->checkSystemManager()) {
        return $redirect;
    }

    $user = User::find($userId);
    if (!$user || !in_array($user->role, ['normal', 'holding']) || $user->role === 'system_manager') {
        return redirect()->route('system-manager.profile', ['active_tab' => 'edit-users'])->with('error', 'کاربر یافت نشد یا دسترسی به آن ممکن نیست.');
    }

    $validated = $request->validate([
        'password' => 'required|string|min:8|confirmed',
    ]);

    $user->password = Hash::make($validated['password']);
    $user->save();

    return redirect()->route('system-manager.profile', ['active_tab' => 'edit-users'])->with('success', 'رمز عبور کاربر با موفقیت به‌روزرسانی شد.');
}

public function toggleBlockUser($userId)
    {
        if ($redirect = $this->checkSystemManager()) {
            return $redirect;
        }

        $user = User::find($userId);
        if (!$user || !in_array($user->role, ['normal', 'holding']) || $user->role === 'system_manager') {
            return redirect()->route('system-manager.profile', ['active_tab' => 'delete-deactivate-users'])->with('error', 'کاربر یافت نشد یا دسترسی به آن ممکن نیست.');
        }

        $user->is_blocked = !$user->is_blocked;
        $user->save();

        $status = $user->is_blocked ? 'بلاک' : 'رفع بلاک';
        return redirect()->route('system-manager.profile', ['active_tab' => 'delete-deactivate-users'])->with('success', "کاربر با موفقیت $status شد.");
    }

public function updateUser(Request $request, $userId)
{
    if ($redirect = $this->checkSystemManagerOrAdmin()) {
        return $redirect;
    }

    $user = User::find($userId);
    if (!$user || !in_array($user->role, ['normal', 'holding']) || $user->role === 'system_manager') {
        return redirect()->back()->with('error', 'کاربر یافت نشد یا دسترسی به آن ممکن نیست.');
    }

    $validated = $request->validate([
        'company_alias' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $userId,
        'company_type' => 'required|array|min:1',
        'company_size' => 'required|in:بزرگ,متوسط,کوچک',
        'role' => 'required|in:normal,holding',
        'parent_id' => 'nullable|exists:users,id',
    ]);

    // بررسی اینکه parent_id به یک هلدینگ اشاره کند
    if ($validated['parent_id']) {
        $parent = User::find($validated['parent_id']);
        if (!$parent || $parent->role !== 'holding') {
            return redirect()->back()->with('error', 'والد انتخاب‌شده باید یک هلدینگ باشد.');
        }
        // جلوگیری از حلقه در ساختار درختی
        if ($this->hasCycle($userId, $validated['parent_id'])) {
            return redirect()->back()->with('error', 'نمی‌توان والد را انتخاب کرد زیرا باعث ایجاد حلقه در ساختار درختی می‌شود.');
        }
    }

    $user->update([
        'company_alias' => $validated['company_alias'],
        'email' => $validated['email'],
        'company_type' => $validated['company_type'],
        'company_size' => $validated['company_size'],
        'role' => $validated['role'],
        'parent_id' => $validated['parent_id'], // ذخیره parent_id بدون شرط
    ]);

    \Log::info('User updated successfully', [
        'user_id' => $user->id,
        'email' => $user->email,
        'role' => $user->role,
        'parent_id' => $user->parent_id,
        'updated_by' => Auth::id(),
    ]);

    $redirectRoute = Auth::user()->is_admin == 1 ? 'admin.profile' : 'system-manager.profile';
    return redirect()->route($redirectRoute, ['active_tab' => 'edit-users'])->with('success', 'اطلاعات کاربر با موفقیت به‌روزرسانی شد.');
}

// متد کمکی برای بررسی حلقه در ساختار درختی
protected function hasCycle($userId, $parentId)
{
    $current = User::find($parentId);
    while ($current) {
        if ($current->id == $userId) {
            return true; // حلقه تشخیص داده شد
        }
        $current = $current->parent;
    }
    return false;
}


    public function showCompareCompanies(Request $request)
{
    $user = Auth::user();
    if (!$user || $user->role !== 'holding') {
        return redirect()->route('profile', ['active_tab' => 'compare-companies'])->with('error', 'شما اجازه دسترسی به این بخش را ندارید.');
    }

    $validated = $request->validate([
        'company_ids' => 'required|array|min:2',
        'company_ids.*' => 'integer|exists:users,id',
    ]);
    $companyIds = $validated['company_ids'];

    $companies = User::whereIn('id', $companyIds)->get();

    if ($companies->count() < 2) {
        return redirect()->route('profile', ['active_tab' => 'compare-companies'])->with('error', 'لطفاً حداقل دو شرکت را برای مقایسه انتخاب کنید.');
    }

    $comparisonData = [
        'company_names' => [],
        'data_values' => [], // برای نمودارهای راداری و میله‌ای
        'maturity_data' => [], // برای نمودار سطح بلوغ
        'risk_counts' => [], // برای نمودار توزیع ریسک‌ها
        'subcategories' => [], // برای نمودارهای زیرشاخه‌ها
        'high_risks' => [],
        'medium_risks' => [],
        'low_risks' => [],
        'final_scores' => [], // اضافه‌شده: امتیاز نهایی هر شرکت
        'assessment_dates' => [], // اضافه‌شده: تاریخ آخرین ارزیابی
        'assessment_group_ids' => [], // اضافه‌شده: ID گروه ارزیابی
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

    // --- اضافه برای محاسبه بلوغ با منطق متد 1 ---
    $assessmentController = app(AssessmentController::class);

    foreach ($companies as $company) {
        $companyData = [
            'company_name' => $company->company_alias,
            'data_values' => array_fill(0, count($labels), 0),
            'maturity_data' => [
    // هر دو نام‌گذاری برای سازگاری با ویو/چارت
    'level_averages'       => [0, 0, 0, 0, 0],
    'levelAverages'        => [0, 0, 0, 0, 0],
    'overall_maturity_level' => null,
    'overallMaturityLevel'   => null,
],

            'risk_counts' => ['high' => 0, 'medium' => 0, 'low' => 0],
            'subcategories' => [],
            'high_risks' => [],
            'medium_risks' => [],
            'low_risks' => [],
        ];

        $latestCompletedGroup = AssessmentGroup::where('user_id', $company->id)
            ->where('status', 'completed')
            ->orderBy('updated_at', 'desc')
            ->first();

        // دریافت امتیاز نهایی و تاریخ از FinalScore (بدون تغییر)
        $latestFinalScore = FinalScore::where('user_id', $company->id)
            ->orderBy('created_at', 'desc')
            ->first();

        $comparisonData['final_scores'][] = $latestFinalScore ? $latestFinalScore->final_score : null;
        $comparisonData['assessment_dates'][] = $latestFinalScore ? Jalalian::fromDateTime($latestFinalScore->created_at)->format('j F Y') : null;
        $comparisonData['assessment_group_ids'][] = $latestFinalScore ? $latestFinalScore->assessment_group_id : null;

        if ($latestCompletedGroup) {
            $assessments = Assessment::where('user_id', $company->id)
                ->where('assessment_group_id', $latestCompletedGroup->assessment_group_id)
                ->where('status', 'finalized')
                ->with(['answers.question'])
                ->get();

            $domainScores = [];

            foreach ($assessments as $assessment) {
                $answers = $assessment->answers;

                foreach ($answers as $answer) {
                    $question = $answer->question;
                    $score = $answer->score;
                    $weight = $question->weight;
                    $domain = $answer->domain ?? $question->domain ?? 'نامشخص';
                    $subcategory = $question->subcategory ?? 'نامشخص';

                    // جمع‌آوری امتیازات برای domain_scores
                    if (!isset($domainScores[$domain])) {
                        $domainScores[$domain] = [];
                    }
                    $domainScores[$domain][] = $score;

                    // جمع‌آوری برای زیرمجموعه‌ها
                    if (!isset($companyData['subcategories'][$domain])) {
                        $companyData['subcategories'][$domain] = [];
                    }
                    $companyData['subcategories'][$domain][] = [
                        'name' => $subcategory,
                        'performance' => $score,
                    ];

                    // دسته‌بندی ریسک‌ها (بدون تغییر)
                    if ($weight == 1 || $weight == 8) {
                        if (in_array($score, [10, 20])) {
                            $companyData['low_risks'][] = ['weight' => $weight, 'content' => $question->risks];
                            $companyData['risk_counts']['low']++;
                        }
                    } elseif ($weight == 6 || $weight == 7 || $weight == 9) {
                        if (in_array($score, [10, 20])) {
                            $companyData['medium_risks'][] = ['weight' => $weight, 'content' => $question->risks];
                            $companyData['risk_counts']['medium']++;
                        }
                    } elseif ($weight == 10) {
                        if (in_array($score, [10, 20])) {
                            $companyData['high_risks'][] = ['weight' => $weight, 'content' => $question->risks];
                            $companyData['risk_counts']['high']++;
                        }
                    }
                }
            }

            // محاسبه dataValues برای نمودارها (بدون تغییر)
            foreach ($labels as $index => $label) {
                if (isset($domainScores[$label])) {
                    $scores = $domainScores[$label];
                    $companyData['data_values'][$index] = count($scores) > 0 ? array_sum($scores) / count($scores) : 0;
                }
            }

            // --------- بخش «محاسبه بلوغ» با منطق متد 1 (جایگزین شده) ----------
            // از calculateMaturityLevel استفاده می‌کنیم و خروجی را به کلیدهای snake_case نگاشت می‌کنیم
            if ($latestFinalScore && $latestFinalScore->assessment_group_id) {
                try {
                    $maturityData = $assessmentController->calculateMaturityLevel(
                        $latestFinalScore->assessment_group_id,
                        $company->id
                    );

                    $companyData['maturity_data'] = [
                        'level_averages' => $maturityData['levelAverages'] ?? [0, 0, 0, 0, 0],
                        'overall_maturity_level' => $maturityData['overallMaturityLevel'] ?? null,
                    ];
                } catch (\Exception $e) {
                    \Log::error('Error calculating maturity (method2 replaced logic)', [
                        'company_id' => $company->id,
                        'company_alias' => $company->company_alias,
                        'error' => $e->getMessage(),
                        'timestamp' => now()
                    ]);
                    // در خطا همان پیش‌فرض‌های companyData باقی می‌ماند
                }
            }
            // -------------------------------------------------------------------
        }

        // افزودن به comparisonData (بدون تغییر)
        $comparisonData['company_names'][] = $company->company_alias;
        $comparisonData['data_values'][] = $companyData['data_values'];
        $comparisonData['maturity_data'][] = $companyData['maturity_data'];
        $comparisonData['risk_counts'][] = $companyData['risk_counts'];
        $comparisonData['subcategories'][$company->company_alias] = $companyData['subcategories'];
        $comparisonData['high_risks'][$company->company_alias] = $companyData['high_risks'];
        $comparisonData['medium_risks'][$company->company_alias] = $companyData['medium_risks'];
        $comparisonData['low_risks'][$company->company_alias] = $companyData['low_risks'];
    }

    \Log::info('Comparison Data:', $comparisonData);

    return view('compare-companies', [
        'comparisonData' => $comparisonData,
        'labels' => $labels,
        'companyIds' => $companyIds,
    ]);
}





public function createUser(Request $request)
{
    // بررسی دسترسی (ادمین یا مدیر سامانه)
    if ($redirect = $this->checkSystemManagerOrAdmin()) {
        return $redirect;
    }

    // اعتبارسنجی درخواست
    $validated = $request->validate([
        'company_alias' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|confirmed|min:8',
        'company_type' => 'required|array',
        'company_size' => 'required|in:بزرگ,متوسط,کوچک',
        'role' => 'required|in:normal,holding',
        'parent_id' => 'nullable|exists:users,id',
    ]);

    // ایجاد کاربر با تنظیم فیلدهای موردنظر
    $user = User::create([
        'company_alias' => $validated['company_alias'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'company_type' => json_encode($validated['company_type']),
        'company_size' => $validated['company_size'],
        'role' => $validated['role'],
        'parent_id' => $validated['parent_id'],
        // تنظیم شرطی برای کاربران معمولی
        'remaining_evaluations' => ($validated['role'] === 'normal') ? 9999 : 0,
        'remaining_days' => ($validated['role'] === 'normal') ? 9999 : 0,
    ]);

    // بازگشت با پیام موفقیت
    $redirectRoute = Auth::user()->is_admin == 1 ? 'admin.profile' : 'system-manager.profile';
    return redirect()->route($redirectRoute, ['active_tab' => 'create-users'])
        ->with('success', 'کاربر با موفقیت ایجاد شد.');
}





    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'لطفاً ابتدا وارد شوید.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15',
            'national_code' => 'required|string|max:10',
        ]);

        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];
        $user->mobile = $validated['mobile'];
        $user->national_code = $validated['national_code'];
        $user->save();

        return redirect()->back()->with('success', 'اطلاعات کاربر با موفقیت به‌روزرسانی شد.');
    }

    public function systemManagerProfile(Request $request)
    {
        if ($redirect = $this->checkSystemManager()) {
            return $redirect;
        }

        $user = Auth::user();

        // فقط کاربران با نقش normal یا holding و غیر ادمین را نمایش بده
        $lastUsers = User::whereIn('role', ['normal', 'holding'])
            ->where('is_admin', 0)
            ->where('role', '!=', 'system_manager')
            ->latest()
            ->take(5)
            ->get();

        $holdings = User::where('role', 'holding')
            ->where('is_admin', 0)
            ->where('role', '!=', 'system_manager')
            ->get();

        $allUsers = User::whereIn('role', ['normal', 'holding'])
            ->where('is_admin', 0)
            ->where('role', '!=', 'system_manager')
            ->when($request->search, function ($query) use ($request) {
                $query->where('company_alias', 'like', '%' . $request->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $tree = $this->buildTree($allUsers);

        $activeTab = $request->query('active_tab', 'create-users');

        return view('system-manager-profile', compact('user', 'lastUsers', 'holdings', 'allUsers', 'tree', 'activeTab'));
    }

    protected function buildTree($users, $parentId = null)
    {
        $branch = [];

        foreach ($users as $user) {
            if ($user->parent_id == $parentId) {
                $children = $this->buildTree($users, $user->id);
                if ($children) {
                    $user->children = $children;
                }
                $branch[] = $user;
            }
        }

        return $branch;
    }

    public function adminProfile(Request $request)
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }

        $user = Auth::user();

        $posts = Post::latest()->take(5)->get();

        $totalUsers = User::where('is_admin', 0)->count();
        $usersWithCredit = User::where('is_admin', 0)
            ->where(function ($query) {
                $query->where('remaining_evaluations', '>', 0)
                    ->orWhere('remaining_days', '>', 0);
            })
            ->count();
        $largeCompanies = User::where('is_admin', 0)->where('company_size', 'بزرگ')->count();
        $mediumCompanies = User::where('is_admin', 0)->where('company_size', 'متوسط')->count();
        $smallCompanies = User::where('is_admin', 0)->where('company_size', 'کوچک')->count();

        $users = User::where('is_admin', 0)
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('first_name', 'like', '%' . $request->search . '%')
                        ->orWhere('last_name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%')
                        ->orWhere('company_alias', 'like', '%' . $request->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $usersList = $users->map(function ($user) {
            $completedAssessments = FinalScore::where('user_id', $user->id)->count();
            return [
                'id' => $user->id,
                'email' => $user->email,
                'company_alias' => $user->company_alias,
                'company_size' => $user->company_size,
                'company_type' => is_array($user->company_type) ? implode(', ', $user->company_type) : $user->company_type,
                'completed_assessments' => $completedAssessments,
                'remaining_assessments' => $user->remaining_evaluations,
                'remaining_days' => $user->remaining_days,
            ];
        });

        $systemManagers = User::where('role', 'system_manager')
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('first_name', 'like', '%' . $request->search . '%')
                        ->orWhere('last_name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $lastUsers = User::whereIn('role', ['normal', 'holding'])
            ->where('is_admin', 0)
            ->where('role', '!=', 'system_manager')
            ->latest()
            ->take(5)
            ->get();

        $holdings = User::where('role', 'holding')
            ->where('is_admin', 0)
            ->where('role', '!=', 'system_manager')
            ->get();

        $allUsers = User::whereIn('role', ['normal', 'holding'])
            ->where('is_admin', 0)
            ->where('role', '!=', 'system_manager')
            ->when($request->search, function ($query) use ($request) {
                $query->where('company_alias', 'like', '%' . $request->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $tree = $this->buildTree($allUsers);

        $activeTab = $request->query('active_tab', 'overview');

        return view('admin-profile', compact(
            'user',
            'posts',
            'users',
            'usersList',
            'systemManagers',
            'totalUsers',
            'usersWithCredit',
            'largeCompanies',
            'mediumCompanies',
            'smallCompanies',
            'lastUsers',
            'holdings',
            'allUsers',
            'tree',
            'activeTab'
        ));
    }

    public function toggleBlockUserAdmin($userId)
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->back()->with('error', 'کاربر یافت نشد.');
        }

        $user->is_blocked = !$user->is_blocked;
        $user->save();

        $status = $user->is_blocked ? 'بلاک' : 'رفع بلاک';
        return redirect()->back()->with('success', "کاربر با موفقیت $status شد.");
    }

    public function createSystemManager(Request $request)
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'company_alias' => $validated['first_name'] . ' ' . $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'system_manager',
        ]);

        return redirect()->route('admin.profile')->with('success', 'مدیر سامانه با موفقیت ایجاد شد.');
    }

    public function updateSystemManagerInfo(Request $request, $userId)
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }

        $user = User::find($userId);
        if (!$user || $user->role != 'system_manager') {
            return redirect()->back()->with('error', 'مدیر سامانه یافت نشد.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $userId,
        ]);

        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];
        $user->company_alias = $validated['first_name'] . ' ' . $validated['last_name'];
        $user->email = $validated['email'];
        $user->save();

        return redirect()->back()->with('success', 'اطلاعات مدیر سامانه با موفقیت به‌روزرسانی شد.');
    }

    public function updateSystemManagerPassword(Request $request, $userId)
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }

        $user = User::find($userId);
        if (!$user || $user->role != 'system_manager') {
            return redirect()->back()->with('error', 'مدیر سامانه یافت نشد.');
        }

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->back()->with('success', 'رمز عبور مدیر سامانه با موفقیت به‌روزرسانی شد.');
    }

    public function toggleBlockSystemManager($userId)
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }

        $user = User::find($userId);
        if (!$user || $user->role != 'system_manager') {
            return redirect()->back()->with('error', 'مدیر سامانه یافت نشد.');
        }

        $user->is_blocked = !$user->is_blocked;
        $user->save();

        $status = $user->is_blocked ? 'بلاک' : 'رفع بلاک';
        return redirect()->back()->with('success', "مدیر سامانه با موفقیت $status شد.");
    }

public function deleteUser($userId)
{
    if ($redirect = $this->checkSystemManagerOrAdmin()) {
        return $redirect;
    }

    $user = User::find($userId);
    if (!$user || !in_array($user->role, ['normal', 'holding']) || $user->role === 'system_manager') {
        return redirect()->back()->with('error', 'کاربر یافت نشد یا دسترسی به آن ممکن نیست.');
    }

    // بررسی اینکه آیا کاربر زیرمجموعه‌هایی دارد (برای هلدینگ‌ها)
    if ($user->role === 'holding') {
        $subsidiaries = User::where('parent_id', $user->id)->count();
        if ($subsidiaries > 0) {
            $redirectRoute = Auth::user()->is_admin == 1 ? 'admin.profile' : 'system-manager.profile';
            return redirect()->route($redirectRoute, ['active_tab' => 'delete-deactivate-users'])->with('error', 'این هلدینگ دارای شرکت‌های زیرمجموعه است و نمی‌تواند حذف شود.');
        }
    }

    $user->delete();

    $redirectRoute = Auth::user()->is_admin == 1 ? 'admin.profile' : 'system-manager.profile';
    return redirect()->route($redirectRoute, ['active_tab' => 'delete-deactivate-users'])->with('success', 'کاربر با موفقیت حذف شد.');
}


    public function deleteSystemManager($userId)
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }

        $user = User::find($userId);
        if (!$user || $user->role != 'system_manager') {
            return redirect()->back()->with('error', 'مدیر سامانه یافت نشد.');
        }

        $user->delete();
        return redirect()->back()->with('success', 'مدیر سامانه با موفقیت حذف شد.');
    }

public function showCompanyRanking(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'holding') {
            return redirect()->route('profile')->with('error', 'شما اجازه دسترسی به این بخش را ندارید.');
        }

        // دریافت تمام کاربران برای ساخت درخت زیرمجموعه
        $allUsers = User::whereIn('role', ['normal', 'holding'])
            ->where('is_admin', 0)
            ->where('role', '!=', 'system_manager')
            ->get();

        // دریافت تمام هلدینگ‌ها برای فیلتر
        $holdings = User::where('role', 'holding')
            ->where('is_admin', 0)
            ->where('role', '!=', 'system_manager')
            ->where('id', '!=', $user->id)
            ->get(['id', 'company_alias']);

        // دریافت IDهای زیرمجموعه‌ها (مستقیم و غیرمستقیم)
        $selectedHoldingId = $request->input('holding_id', $user->id);
        $subsidiaryIds = $this->getAllSubsidiaryIds($selectedHoldingId, $allUsers);
        $subsidiaryUsers = $allUsers->whereIn('id', $subsidiaryIds);

        // اعمال فیلترهای دیگر
        $hierarchyLevel = $request->input('hierarchy_level');
        $selectedCompanyType = $request->input('company_type');
        $sortBy = $request->input('sort_by', 'final_score'); // خواندن پارامتر sort_by

        if ($hierarchyLevel) {
            $subsidiaryUsers = $subsidiaryUsers->filter(function ($subsidiary) use ($selectedHoldingId, $hierarchyLevel) {
                $level = 0;
                $current = $subsidiary;
                $targetId = $selectedHoldingId;
                while ($current && $current->parent_id) {
                    $level++;
                    $current = User::find($current->parent_id);
                    if ($current && $current->id == $targetId) {
                        break;
                    }
                }
                return $level == $hierarchyLevel;
            });
        }

        if ($selectedCompanyType) {
            $subsidiaryUsers = $subsidiaryUsers->filter(function ($subsidiary) use ($selectedCompanyType) {
                $companyTypes = $subsidiary->company_type;
                return is_array($companyTypes) ? in_array($selectedCompanyType, $companyTypes) : $companyTypes == $selectedCompanyType;
            });
        }

        // مپینگ توصیف‌های سطح بلوغ
        $maturityDescriptions = [
            1 => 'یک - تلاش فردی',
            2 => 'دو - قدم‌های ساخت‌یافته',
            3 => 'سه - رویکرد سازمان‌یافته',
            4 => 'چهار - تصمیم‌گیری مبتنی بر داده',
            5 => 'پنج - نوآور و پیشرو',
        ];

        // محاسبه همه rankings بر اساس زیرمجموعه‌های فیلترشده
        $subsidiaryIds = $subsidiaryUsers->pluck('id')->toArray();
        $allRankings = FinalScore::whereIn('user_id', $subsidiaryIds)
            ->select('user_id', 'final_score', 'assessment_group_id', 'created_at')
            ->orderBy('final_score', 'desc') // مرتب‌سازی اولیه برای بهینه‌سازی کوئری
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($score) use ($maturityDescriptions) {
                $user = User::find($score->user_id);
                $parentHolding = $user->parent_id ? User::find($user->parent_id) : null;
                try {
                    $date = $score->created_at ? Jalalian::fromDateTime($score->created_at)->format('j F Y') : 'نامشخص';
                } catch (\Exception $e) {
                    \Log::error('Failed to parse date in showCompanyRanking: ' . $e->getMessage(), [
                        'created_at' => $score->created_at,
                        'user_id' => $score->user_id,
                    ]);
                    $date = 'نامشخص';
                }
                try {
                    $maturityData = app(AssessmentController::class)->calculateMaturityLevel($score->assessment_group_id, $score->user_id);
                    $maturityLevel = $maturityDescriptions[$maturityData['overallMaturityLevel'] ?? 0] ?? 'نامشخص';
                    $maturityLevelNumeric = $maturityData['overallMaturityLevel'] ?? 0;
                } catch (\Exception $e) {
                    \Log::error('Failed to calculate maturity level in showCompanyRanking: ' . $e->getMessage(), [
                        'assessment_group_id' => $score->assessment_group_id,
                        'user_id' => $score->user_id,
                    ]);
                    $maturityLevel = 'نامشخص';
                    $maturityLevelNumeric = 0;
                }
                return (object)[
                    'company_alias' => $user ? $user->company_alias : 'نامشخص',
                    'parent_holding' => $parentHolding ? $parentHolding->company_alias : 'بدون هلدینگ',
                    'latest_score' => $score->final_score ?? 0,
                    'latest_date' => $date,
                    'maturity_level' => $maturityLevel,
                    'maturity_level_numeric' => $maturityLevelNumeric, // مقدار عددی برای مرتب‌سازی
                ];
            });

        // مرتب‌سازی نهایی بر اساس sort_by
        if ($sortBy === 'maturity_level') {
            $allRankings = $allRankings->sortByDesc('maturity_level_numeric');
        } else {
            $allRankings = $allRankings->sortByDesc('latest_score');
        }

        // تبدیل به آرایه برای نمایش در ویو
        $allRankings = $allRankings->values();

        return view('company-ranking', [
            'user' => $user,
            'holdings' => $holdings,
            'allRankings' => $allRankings,
            'selectedHoldingId' => $selectedHoldingId,
            'hierarchyLevel' => $hierarchyLevel,
            'selectedCompanyType' => $selectedCompanyType,
            'sortBy' => $sortBy, // برای حفظ مقدار فیلتر در ویو
        ]);
    }
}
?>