<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinalScore extends Model
{
    protected $fillable = [
        'user_id', 'assessment_group_id', 'final_score', 'created_at', 'updated_at',
    ];

    protected $casts = [
        'final_score' => 'decimal:2',
    ];

public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}