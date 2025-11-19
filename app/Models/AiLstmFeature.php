<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiLstmFeature extends Model
{
    protected $table = 'ai_lstm_features';

    protected $fillable = [
        'company_id',
        'assessment_group_id',
        'evaluation_date',
        'time_index',
        'features',                // JSON
        'target_final_score',
        'target_maturity_level',
    ];

    protected $casts = [
        'evaluation_date' => 'datetime',
        'features' => 'array',
    ];
}
