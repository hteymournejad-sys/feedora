<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'assessment_group_id', // این خط باید وجود داشته باشه
        'created_date',
        'finalized_date',
        'status',
        'domain',
        'performance_percentage',
        'excel_version',
        'holding_id',
        'last_question_id',
        'created_at',
        'updated_at',
    ];

    protected $dates = [
        'created_date',
        'finalized_date',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function holding()
    {
        return $this->belongsTo(Holding::class);
    }

    public function lastQuestion()
    {
        return $this->belongsTo(Question::class, 'last_question_id');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}