<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiConversation extends Model
{
    protected $table = 'ai_conversations';

    protected $fillable = [
        'user_id',
        'scenario_type',
        'company_a_id',
        'company_a_alias',
        'company_b_id',
        'company_b_alias',
        'question',
        'answer',
        'system_prompt',
        'user_prompt',
        'context_a',
        'context_b',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
