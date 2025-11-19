<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'invoice_number',
        'user_id',
        'amount',
        'status',
        'evaluation_count',
        'payment_date',
        'start_date',
        'duration_days',
        'payment_id',
        'receipt_image',
        'invoice_file',
        'payment_step',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}