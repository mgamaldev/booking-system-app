<?php

namespace Database\Seeders;

use App\Models\Resource;
use Illuminate\Database\Seeder;

class ResourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $resources = [
            [
                'name' => 'Main Meeting Room',
                'type' => 'room',
                'capacity' => 12,
                'price' => 500,
                'status' => 'active',
                'image' => null,
            ],
            [
                'name' => 'Private Consultation Room',
                'type' => 'room',
                'capacity' => 4,
                'price' => 300,
                'status' => 'active',
                'image' => null,
            ],
            [
                'name' => 'Window Table',
                'type' => 'table',
                'capacity' => 4,
                'price' => 150,
                'status' => 'active',
                'image' => null,
            ],
            [
                'name' => 'Shared Desk Chair',
                'type' => 'chair',
                'capacity' => 1,
                'price' => 75,
                'status' => 'active',
                'image' => null,
            ],
            [
                'name' => 'Maintenance Room',
                'type' => 'room',
                'capacity' => 2,
                'price' => 100,
                'status' => 'inactive',
                'image' => null,
            ],
        ];

        foreach ($resources as $resource) {
            Resource::query()->updateOrCreate(
                ['name' => $resource['name']],
                $resource
            );
        }
    }
}
