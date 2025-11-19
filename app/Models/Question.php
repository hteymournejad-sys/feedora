<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
protected $fillable = [
        'text',
        'domain',
        'subcategory',
        'weight',
        'applicable_small',
        'applicable_medium',
        'applicable_large',
        'applicable_manufacturing',
        'applicable_service',
        'applicable_distribution',
        'applicable_investment',
        'applicable_project',
        'applicable_university',
        'applicable_research',
        'applicable_hospital',
        'applicable_banking',
        'description',
        'guide',
        'risks',
        'strengths',
        'current_status',
        'improvement_opportunities',
        'Maturity_level',
    ];

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}