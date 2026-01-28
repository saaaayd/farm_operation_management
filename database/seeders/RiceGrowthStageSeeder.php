<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RiceGrowthStage;

class RiceGrowthStageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stages = [
            [
                'name' => 'Germination & Seedling',
                'stage_code' => 'stage_1_seedling',
                'description' => 'From soaking seeds to transplanting.',
                'typical_duration_days' => 20,
                'order_sequence' => 1,
            ],
            [
                'name' => 'Tillering',
                'stage_code' => 'stage_2_tillering',
                'description' => 'Development of tillers.',
                'typical_duration_days' => 30,
                'order_sequence' => 2,
            ],
            [
                'name' => 'Panicle Initiation',
                'stage_code' => 'stage_3_panicle',
                'description' => 'Start of reproductive phase.',
                'typical_duration_days' => 15,
                'order_sequence' => 3,
            ],
            [
                'name' => 'Flowering',
                'stage_code' => 'stage_4_flowering',
                'description' => 'Pollination and grain formation.',
                'typical_duration_days' => 15,
                'order_sequence' => 4,
            ],
            [
                'name' => 'Ripening',
                'stage_code' => 'stage_5_ripening',
                'description' => 'Grain filling and maturation.',
                'typical_duration_days' => 30,
                'order_sequence' => 5,
            ],
        ];

        foreach ($stages as $stageData) {
            RiceGrowthStage::firstOrCreate(
                ['stage_code' => $stageData['stage_code']],
                array_merge($stageData, [
                    'is_active' => true,
                    'key_activities' => ['watering'],
                    'weather_requirements' => ['warm'],
                ])
            );
        }
    }
}
