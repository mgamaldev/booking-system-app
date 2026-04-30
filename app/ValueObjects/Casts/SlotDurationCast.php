<?php

namespace App\ValueObjects\Casts;

use App\Models\Slot;
use App\ValueObjects\SlotDuration;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class SlotDurationCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?SlotDuration
    {
        $slot = Slot::find($attributes['slot_id']);
        $value = Carbon::parse($slot->start_time)->diffInMinutes($slot->end_time);

        return $value != null ? new SlotDuration((int) $value) : null;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof SlotDuration
            ? $value->minutes
            : (new SlotDuration((int) $value))->minutes;
    }
}
