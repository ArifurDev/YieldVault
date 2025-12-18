<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            // BRONZE
            ['name'=>'Level 1 Bronze','slug'=>'bronze-1','required_xp'=>200,'daily_roi'=>0.5,'icon'=>'backend/assets/media/icon/bronze-1.png'],
            ['name'=>'Level 2 Bronze','slug'=>'bronze-2','required_xp'=>400,'daily_roi'=>0.7,'icon'=>'backend/assets/media/icon/bronze-2.png'],
            ['name'=>'Level 3 Bronze','slug'=>'bronze-3','required_xp'=>600,'daily_roi'=>0.9,'icon'=>'backend/assets/media/icon/bronze-3.png'],
            ['name'=>'Level 4 Bronze','slug'=>'bronze-4','required_xp'=>800,'daily_roi'=>1.0,'icon'=>'backend/assets/media/icon/bronze-4.png'],

            // SILVER
            ['name'=>'Level 5 Silver','slug'=>'silver-1','required_xp'=>1200,'daily_roi'=>0.9,'icon'=>'backend/assets/media/icon/silver-1.png'],
            ['name'=>'Level 6 Silver','slug'=>'silver-2','required_xp'=>1500,'daily_roi'=>1.0,'icon'=>'backend/assets/media/icon/silver-2.png'],
            ['name'=>'Level 7 Silver','slug'=>'silver-3','required_xp'=>1800,'daily_roi'=>1.1,'icon'=>'backend/assets/media/icon/silver-3.png'],
            ['name'=>'Level 8 Silver','slug'=>'silver-4','required_xp'=>2000,'daily_roi'=>1.2,'icon'=>'backend/assets/media/icon/silver-4.png'],

            // GOLD
            ['name'=>'Level 9 Gold','slug'=>'gold-1','required_xp'=>2000,'daily_roi'=>1.2,'icon'=>'backend/assets/media/icon/gold-1.png'],
            ['name'=>'Level 10 Gold','slug'=>'gold-2','required_xp'=>3000,'daily_roi'=>1.5,'icon'=>'backend/assets/media/icon/gold-2.png'],
            ['name'=>'Level 11 Gold','slug'=>'gold-3','required_xp'=>3500,'daily_roi'=>1.6,'icon'=>'backend/assets/media/icon/gold-3.png'],
            ['name'=>'Level 12 Gold','slug'=>'gold-4','required_xp'=>4000,'daily_roi'=>1.8,'icon'=>'backend/assets/media/icon/gold-4.png'],

            // DIAMOND
            ['name'=>'Level 13 Diamond','slug'=>'diamond-1','required_xp'=>9000,'daily_roi'=>2.5,'icon'=>'backend/assets/media/icon/diamond-1.png'],
            ['name'=>'Level 14 Diamond','slug'=>'diamond-2','required_xp'=>9500,'daily_roi'=>3.0,'icon'=>'backend/assets/media/icon/diamond-2.png'],
            ['name'=>'Level 15 Diamond','slug'=>'diamond-3','required_xp'=>10000,'daily_roi'=>3.2,'icon'=>'backend/assets/media/icon/diamond-3.png'],
            ['name'=>'Level 16 Diamond','slug'=>'diamond-4','required_xp'=>11000,'daily_roi'=>3.5,'icon'=>'backend/assets/media/icon/diamond-4.png'],
        ];

        foreach ($levels as $index => $level) {
            Level::updateOrCreate(
                ['slug' => $level['slug']],
                array_merge($level, [
                    'order' => $index + 1,
                    'is_active' => true,
                    'is_coming_soon' => false,
                ])
            );
        }

    }
}
