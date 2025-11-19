<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NonTechnicalAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'holding_id', 'year', 'active_users', 'workstations',
        'full_time_it_staff', 'part_time_it_staff', 'it_budget', 'it_expenditure',
        'internal_events', 'external_events', 'it_training_hours', 'general_training_hours'
    ];
}

