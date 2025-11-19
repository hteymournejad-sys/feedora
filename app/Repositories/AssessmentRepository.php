<?php
namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class AssessmentRepository {

  /** آخرین assessment_group کامل هر شرکت */
  public function latestCompletedGroupId(int $userId): ?int {
    $row = DB::table('assessment_groups')
      ->select('id','created_at')
      ->where('user_id',$userId)->where('status','completed')
      ->orderByDesc('created_at')->first();
    return $row?->id;
  }

  /** پاسخ‌ها + نگاشت به questions (ستون‌های حیاتی) */
  public function answersWithQuestions(int $groupId) {
    return DB::table('answers as a')
      ->join('questions as q','q.id','=','a.question_id')
      ->selectRaw('q.id as qid, q.domain, q.subcategory, q.weight,
                  q.text, q.description, q.guide, q.risks, q.strengths, q.improvement_opportunities,
                  a.score, a.result, a.updated_at')
      ->where('a.assessment_group_id',$groupId)
      ->limit(2000) // احتیاط
      ->get();
  }

  /** تاریخ آخرین ارزیابی کامل */
  public function latestCompletedDate(int $userId): ?string {
    $row = DB::table('assessment_groups')
      ->where('user_id',$userId)->where('status','completed')
      ->orderByDesc('created_at')->select('created_at')->first();
    return $row?->created_at?->format('Y-m-d');
  }
}
