<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $table = 'user_profile';
    protected $primaryKey = 'user_id'; // کلید اصلی رو مشخص می‌کنیم
    public $incrementing = false; // چون user_id خودکار افزایش نمی‌کنه
    protected $fillable = ['user_id', 'company_activity', 'company_size', 'total_employees'];
    public $timestamps = true;
}