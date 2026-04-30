<?php

namespace App\ValueObjects;

final readonly class SlotDuration
{
    public function __construct(public readonly int $minutes)
    {
        if ($minutes < 0) {
            throw new \InvalidArgumentException('Slot duration cannot be negative');
        }
    }

}
