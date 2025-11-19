<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentDomain extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'domain',
        'status',
        'performance_percentage',
        'answers',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'performance_percentage' => 'float',
        'answers' => 'array', // اضافه کردن برای مدیریت داده‌های JSON یا آرایه
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }
}