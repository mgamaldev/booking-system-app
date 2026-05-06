<?php

namespace App\ValueObjects;

use Carbon\CarbonInterface;

final readonly class SlotDuration
{
    public function __construct(public  int $minutes)
    {
        if ($minutes <= 0) {
            throw new \InvalidArgumentException('Slot duration cannot be negative');
        }
    }

    public static function fromTimes(CarbonInterface $start , CarbonInterface $end): self
    {
        return new self($start->diffInMinutes($end));
    }

}
