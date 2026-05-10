<?php

namespace App\Models;

use App\Builders\BookingQueryBuilder;
use App\ValueObjects\SlotDuration;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;


class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;
    protected $fillable =
        [
            'customer_id',
            'resource_id',
            'slot_id',
            'status',
            'star_date',
            'end_date',
            'recurrence_rule',
            'max_participants',
            'type'
        ];

    protected $with = ['slot' , 'resource' , 'customer'];

    protected $appends =  ['duration'];
    protected $casts = [
        'status' => 'string',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(Slot::class);
    }

    public function getDurationAttribute(): SlotDuration
    {
        return  $this->slot->duration();
    }

    public function newEloquentBuilder($query): BookingQueryBuilder
    {
        return new BookingQueryBuilder($query);
    }
}
