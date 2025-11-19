<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentGroup extends Model
{
        protected $table = 'assessment_groups'; // مشخص کردن اسم جدول
	protected $primaryKey = 'id'; // کلید اصلی جدول
	protected $fillable = [
        'user_id',
        'assessment_group_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class, 'assessment_group_id', 'id');
    }
}