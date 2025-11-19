<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AssessmentController;

class TrendForecastController extends Controller
{
    public function index(Request $request)
    {
        // فقط هلدینگ/ادمین
        $user = auth()->user();
        if (!$user || !in_array(($user->role ?? ''), ['holding', 'admin'])) {
            abort(403, 'دسترسی این بخش فقط برای پروفایل هلدینگ مجاز است.');
        }

        // ورودی: شناسه شرکت زیرمجموعه
        $companyId = (int) $request->input('company_id', 0);

        // === اضافه کردن نام شرکت (دو خط جدید) ===
        $company_name = 'نامشخص';
        if ($companyId > 0) {
            $company_name = DB::table('users')
                ->where('id', $companyId)
                ->value('company_alias') 
                ?? DB::table('users')->where('id', $companyId)->value('company_name')
                ?? DB::table('users')->where('id', $companyId)->value('name')
                ?? 'نامشخص';
        }
        // === پایان بخش اضافه‌شده ===

        // سری‌های نمودار
        $scoreLabels      = [];  // تاریخ هر ارزیابی
        $scoreValues      = [];  // امتیاز کل هر ارزیابی (0..100)
        $maturityLabels   = [];  // تاریخ هر ارزیابی
        $maturityValues   = [];  // سطح بلوغ هر ارزیابی (0..100)

        if ($companyId > 0) {
            // 1) گروه‌های ارزیابی نهایی‌شده شرکت + تاریخ نهایی هر گروه
            $groups = DB::table('assessments as a')
                ->select('a.assessment_group_id', DB::raw('MAX(a.finalized_date) as d'))
                ->where('a.user_id', $companyId)
                ->where('a.status', 'finalized')
                ->groupBy('a.assessment_group_id')
                ->orderBy(DB::raw('MAX(a.finalized_date)'))
                ->get();

            // کنترلر ارزیابی برای محاسبه بلوغ
            $assessmentCtrl = app(AssessmentController::class);

            foreach ($groups as $g) {
                $dateLabel = $g->d ? substr($g->d, 0, 10) : 'بدون تاریخ';

                // 2) امتیاز کل هر ارزیابی از final_scores
                $finalScore = DB::table('final_scores')
                    ->where('user_id', $companyId)
                    ->where('assessment_group_id', $g->assessment_group_id)
                    ->value('final_score');

                if ($finalScore !== null) {
                    $fs = (float) $finalScore;
                    $scoreLabels[] = $dateLabel;
                    $scoreValues[] = round(max(0, min(100, $fs)), 1);
                } else {
                    continue;
                }

                // 3) سطح بلوغ هر ارزیابی
                $maturityData = $assessmentCtrl->calculateMaturityLevel($g->assessment_group_id, $companyId);
                $overallLevel = (int) ($maturityData['overallMaturityLevel'] ?? 0);
                if ($overallLevel <= 0) {
                    $overallLevel = $this->fallbackLevelFromScore($fs);
                }
                $maturityPercent = round(($overallLevel / 5) * 100, 1);

                $maturityLabels[] = $dateLabel;
                $maturityValues[] = $maturityPercent;
            }
        }

        // 4) پیش‌بینی +2Y، +3Y، +5Y
        [$score2y, $score3y, $score5y]           = $this->forecastYears($scoreValues, [2,3,5]);
        [$maturity2y, $maturity3y, $maturity5y]  = $this->forecastYears($maturityValues, [2,3,5]);

        return view('trend-forecast', [
            'companyId'               => $companyId,

            // === ارسال نام شرکت به ویو ===
            'company_name'            => $company_name,

            // نمودار 1: امتیاز کل
            'scoreLabels'             => $scoreLabels,
            'scoreValues'             => $scoreValues,
            'scoreForecastLbl'        => ['+2Y', '+3Y', '+5Y'],
            'scoreForecastVal'        => [$score2y, $score3y, $score5y],

            // نمودار 2: سطح بلوغ
            'maturityLabels'          => $maturityLabels,
            'maturityValues'          => $maturityValues,
            'maturityForecastLbl'     => ['+2Y', '+3Y', '+5Y'],
            'maturityForecastVal'     => [$maturity2y, $maturity3y, $maturity5y],
        ]);
    }

    /**
     * نگاشت کمکی سطح بلوغ از امتیاز کل (Fallback)
     */
    private function fallbackLevelFromScore(float $fs): int
    {
        if ($fs >= 80) return 5;
        if ($fs >= 60) return 4;
        if ($fs >= 40) return 3;
        if ($fs >= 20) return 2;
        return 1;
    }

    /**
     * پیش‌بینی چندگانه با رگرسیون خطی
     */
    private function forecastYears(array $values, array $steps): array
    {
        $n = count($values);
        if ($n === 0) return array_fill(0, count($steps), null);
        if ($n === 1) {
            $v = round(max(0, min(100, (float) $values[0])), 1);
            return array_fill(0, count($steps), $v);
        }

        $x = range(0, $n - 1);
        [$m, $b] = $this->linearRegression($x, $values);

        $res = [];
        foreach ($steps as $k) {
            $yk = $m * ($n - 1 + $k) + $b;
            $res[] = round(max(0, min(100, $yk)), 1);
        }
        return $res;
    }

    /**
     * محاسبه ضرایب رگرسیون خطی y = m x + b
     */
    private function linearRegression(array $x, array $y): array
    {
        $n = count($x);
        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = 0.0;
        $sumXX = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumXX += $x[$i] * $x[$i];
        }
        $den = ($n * $sumXX - $sumX * $sumX);
        $m = abs($den) < 1e-9 ? 0.0 : ($n * $sumXY - $sumX * $sumY) / $den;
        $b = ($sumY - $m * $sumX) / $n;

        return [$m, $b];
    }
}