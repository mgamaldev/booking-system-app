<?php

namespace App\Models;

use Database\Factories\SlotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Slot extends Model
{
    /** @use HasFactory<SlotFactory> */
    use HasFactory;

    protected $fillable = [
        'start_time',
        'end_time',
        'date',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }
}
