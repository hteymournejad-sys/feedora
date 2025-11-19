<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiInsightItem extends Model
{
    protected $table = 'ai_insight_items';

    protected $fillable = [
        'company_id',
        'company_alias',
        'assessment_group_id',
        'evaluation_date',
        'item_type',      // risk / strength / improvement
        'severity',       // high / medium / low / null
        'domain',
        'subcategory',
        'question_id',
        'weight',
        'score',
        'content',
    ];

    protected $casts = [
        'evaluation_date' => 'datetime',
    ];
}
