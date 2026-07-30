<?php

namespace Database\Seeders;

use App\Models\Slot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dates = [
            Carbon::today(),
            Carbon::today()->addDay(),
            Carbon::today()->addDays(2),
        ];

        $times = [
            ['09:00:00', '10:00:00'],
            ['10:30:00', '11:30:00'],
            ['13:00:00', '14:00:00'],
            ['15:00:00', '16:00:00'],
        ];

        foreach ($dates as $date) {
            foreach ($times as [$startTime, $endTime]) {
                Slot::query()->updateOrCreate(
                    [
                        'date' => $date->toDateString(),
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                    ],
                    [
                        'status' => 'active',
                    ]
                );
            }
        }

        Slot::query()->updateOrCreate(
            [
                'date' => Carbon::today()->addDays(3)->toDateString(),
                'start_time' => '09:00:00',
                'end_time' => '10:00:00',
            ],
            [
                'status' => 'inactive',
            ]
        );
    }
}
