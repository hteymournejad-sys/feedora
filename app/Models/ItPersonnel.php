<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItPersonnel extends Model
{
    use HasFactory;

    protected $table = 'it_personnel'; // صراحتاً نام جدول را مشخص می‌کنیم

    protected $fillable = [
        'non_technical_assessment_id',
        'user_id',
        'full_name',
        'education',
        'expertise',
        'position',
        'work_experience',
        'training_courses'
    ];

    protected $casts = [
        'expertise' => 'array',
    ];
public function nonTechnicalAssessment()
    {
        return $this->belongsTo(NonTechnicalAssessment::class, 'non_technical_assessment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}