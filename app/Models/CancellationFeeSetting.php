<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CancellationFeeSetting extends Model
{
    protected $fillable = [
        'is_active',
        'min_hours_before_slot',
        'max_hours_before_slot',
        'fee_type',
        'fee_amount',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_hours_before_slot' => 'integer',
        'max_hours_before_slot' => 'integer',
        'fee_amount' => 'decimal:2',
    ];
}
