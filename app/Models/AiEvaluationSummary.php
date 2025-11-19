<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiEvaluationSummary extends Model
{
    protected $table = 'ai_evaluation_summary';

    protected $fillable = [
        'company_id',
        'company_alias',
        'holding_id',
        'assessment_group_id',
        'evaluation_date',
        'period_label',
        'final_score',
        'score_it_governance',
        'score_info_security',
        'score_infrastructure',
        'score_it_support',
        'score_applications',
        'score_digital_transformation',
        'score_intelligence',
        'subcategory_scores',
        'overall_maturity_level',
        'maturity_level_1_avg',
        'maturity_level_2_avg',
        'maturity_level_3_avg',
        'maturity_level_4_avg',
        'maturity_level_5_avg',
        'strength_count',
        'risk_high_count',
        'risk_medium_count',
        'risk_low_count',
        'improvement_count',
        'total_questions',
        'answered_questions',
    ];

    protected $casts = [
        'evaluation_date' => 'datetime',
        'subcategory_scores' => 'array',
    ];
}
