<?php

namespace Database\Seeders;

use App\Models\CancellationFeeSetting;
use Illuminate\Database\Seeder;

class CancellationFeeSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rules = [
            [
                'id' => 1,
                'min_hours_before_slot' => 24,
                'max_hours_before_slot' => null,
                'fee_amount' => 0,
            ],
            [
                'id' => 2,
                'min_hours_before_slot' => 2,
                'max_hours_before_slot' => 24,
                'fee_amount' => 50,
            ],
            [
                'id' => 3,
                'min_hours_before_slot' => 0,
                'max_hours_before_slot' => 2,
                'fee_amount' => 100,
            ],
        ];

        foreach ($rules as $rule) {
            CancellationFeeSetting::updateOrCreate(
                ['id' => $rule['id']],
                [
                    'is_active' => true,
                    'min_hours_before_slot' => $rule['min_hours_before_slot'],
                    'max_hours_before_slot' => $rule['max_hours_before_slot'],
                    'fee_type' => 'fixed',
                    'fee_amount' => $rule['fee_amount'],
                ],
            );
        }
    }
}
