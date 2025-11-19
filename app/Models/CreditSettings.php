<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditSettings extends Model
{
    protected $table = 'credit_settings';

    protected $fillable = [
        'amount',
        'days',
        'evaluations',
    ];

    public $timestamps = true;
}