<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class BookingQueryBuilder extends Builder
{
    public function confirmed(): self
    {
        return $this->where('status', 'confirmed');
    }

    public function upcoming(): self
    {
        return $this->where('start_time', '>', now());
    }

    public function byCustomer(int $customerId): self
    {
        return $this->where('customer_id', $customerId);
    }

    public function bySlot(int $slotId): self
    {
        return $this->where('slot_id', $slotId);
    }

    public function withRelations(): self
    {
        return $this->with([
            'customer',
            'slot',
            'resource',
        ]);
    }
}
