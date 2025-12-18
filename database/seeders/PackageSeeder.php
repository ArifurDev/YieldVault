<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\Package;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * Define packages per level slug
         */
        $packages = [
            // BRONZE
            'bronze-1' => [
                ['name' => 'Bronze Starter 15 Days', 'price' => 10, 'days' => 15],
                ['name' => 'Bronze Starter 30 Days', 'price' => 18, 'days' => 30],
            ],
            'bronze-2' => [
                ['name' => 'Bronze Plus 30 Days', 'price' => 25, 'days' => 30],
            ],
            'bronze-3' => [
                ['name' => 'Bronze Pro 30 Days', 'price' => 40, 'days' => 30],
            ],
            'bronze-4' => [
                ['name' => 'Bronze Elite 30 Days', 'price' => 60, 'days' => 30],
            ],

            // SILVER
            'silver-1' => [
                ['name' => 'Silver Starter 30 Days', 'price' => 100, 'days' => 30],
            ],
            'silver-2' => [
                ['name' => 'Silver Plus 30 Days', 'price' => 150, 'days' => 30],
            ],
            'silver-3' => [
                ['name' => 'Silver Pro 30 Days', 'price' => 200, 'days' => 30],
            ],
            'silver-4' => [
                ['name' => 'Silver Elite 30 Days', 'price' => 300, 'days' => 30],
            ],

            // GOLD
            'gold-1' => [
                ['name' => 'Gold Starter 30 Days', 'price' => 500, 'days' => 30],
            ],
            'gold-2' => [
                ['name' => 'Gold Plus 30 Days', 'price' => 700, 'days' => 30],
            ],
            'gold-3' => [
                ['name' => 'Gold Pro 30 Days', 'price' => 1000, 'days' => 30],
            ],
            'gold-4' => [
                ['name' => 'Gold Elite 30 Days', 'price' => 1500, 'days' => 30],
            ],

            // DIAMOND
            'diamond-1' => [
                ['name' => 'Diamond Starter 30 Days', 'price' => 3000, 'days' => 30],
            ],
            'diamond-2' => [
                ['name' => 'Diamond Plus 30 Days', 'price' => 5000, 'days' => 30],
            ],
            'diamond-3' => [
                ['name' => 'Diamond Pro 30 Days', 'price' => 7000, 'days' => 30],
            ],
            'diamond-4' => [
                ['name' => 'Diamond Elite 30 Days', 'price' => 10000, 'days' => 30],
            ],
        ];

        foreach ($packages as $levelSlug => $items) {

            $level = Level::where('slug', $levelSlug)->first();

            if (!$level) {
                continue;
            }

            foreach ($items as $pkg) {
                Package::updateOrCreate(
                    [
                        'level_id' => $level->id,
                        'name'     => $pkg['name'],
                    ],
                    [
                        'price'        => $pkg['price'],
                        'duration_days'=> $pkg['days'],
                        'is_active'    => true,
                    ]
                );
            }
        }
    }
}
