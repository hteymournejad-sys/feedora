<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'email', 'first_name', 'last_name', 'mobile', 'national_code', 'company_alias', 'company_size', 'company_type', 'holding_affiliation_code',
        'total_employees', 'remaining_evaluations', 'remaining_days',
        'self_assessments', 'password', 'role', 'parent_id' // اضافه کردن role و parent_id
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'total_employees' => 'integer',
        'remaining_evaluations' => 'integer',
        'remaining_days' => 'integer',
        'self_assessments' => 'integer',
        'company_type' => 'array', // برای تبدیل خودکار JSON به آرایه
    ];

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    // رابطه برای والد (برای ساختار درختی)
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    // رابطه برای زیرمجموعه‌ها
    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }
}
