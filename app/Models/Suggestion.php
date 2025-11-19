<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suggestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'suggestion_topic',
        'suggestion_text',
    ];

    // ارتباط با کاربر
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}