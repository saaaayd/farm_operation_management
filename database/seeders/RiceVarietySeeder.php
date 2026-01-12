<?php

namespace Database\Seeders;

use App\Models\RiceVariety;
use Illuminate\Database\Seeder;

class RiceVarietySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Existing Varieties
        RiceVariety::updateOrCreate(
            ['variety_code' => 'RICE-IR64'],
            [
                'name' => 'IR64',
                'description' => 'High-yielding, semi-dwarf Indica variety widely planted in SE Asia.',
                'maturity_days' => 120,
                'average_yield_per_hectare' => 5.6,
                'season' => 'wet',
                'grain_type' => 'long',
                'resistance_level' => 'medium',
                'characteristics' => [
                    'notes' => 'Performs best in irrigated lowland fields with good fertility.',
                ],
                'is_active' => true,
            ]
        );

        RiceVariety::updateOrCreate(
            ['variety_code' => 'RICE-JASMINE'],
            [
                'name' => 'Thai Jasmine',
                'description' => 'Premium fragrant rice valued for aroma and soft texture.',
                'maturity_days' => 110,
                'average_yield_per_hectare' => 4.9,
                'season' => 'dry',
                'grain_type' => 'long',
                'resistance_level' => 'medium',
                'characteristics' => [
                    'notes' => 'Requires consistent irrigation and well-drained fields.',
                ],
                'is_active' => true,
            ]
        );

        RiceVariety::updateOrCreate(
            ['variety_code' => 'RICE-BASMATI'],
            [
                'name' => 'Basmati 370',
                'description' => 'Traditional aromatic Basmati with elongated grains.',
                'maturity_days' => 135,
                'average_yield_per_hectare' => 4.3,
                'season' => 'dry',
                'grain_type' => 'long',
                'resistance_level' => 'medium',
                'characteristics' => [
                    'notes' => 'Prefers cool nights; suited for river-fed plains.',
                ],
                'is_active' => true,
            ]
        );

        RiceVariety::updateOrCreate(
            ['variety_code' => 'RICE-STICKY'],
            [
                'name' => 'Glutinous Sticky Rice',
                'description' => 'Round-grain sticky rice used for traditional delicacies.',
                'maturity_days' => 105,
                'average_yield_per_hectare' => 4.6,
                'season' => 'wet',
                'grain_type' => 'short',
                'resistance_level' => 'high',
                'characteristics' => [
                    'notes' => 'Can tolerate temporary flooding; harvest promptly to retain stickiness.',
                ],
                'is_active' => true,
            ]
        );

        RiceVariety::updateOrCreate(
            ['variety_code' => 'RICE-BROWN'],
            [
                'name' => 'Wholegrain Brown Rice',
                'description' => 'Nutritious variety harvested and milled for brown rice.',
                'maturity_days' => 125,
                'average_yield_per_hectare' => 5.1,
                'season' => 'wet',
                'grain_type' => 'medium',
                'resistance_level' => 'medium',
                'characteristics' => [
                    'notes' => 'Responds well to organic fertilisation; ideal for health-conscious markets.',
                ],
                'is_active' => true,
            ]
        );

        RiceVariety::updateOrCreate(
            ['variety_code' => 'RICE-SWARNA'],
            [
                'name' => 'Swarna',
                'description' => 'High-yielding variety with strong disease resistance.',
                'maturity_days' => 130,
                'average_yield_per_hectare' => 6.3,
                'season' => 'wet',
                'grain_type' => 'medium',
                'resistance_level' => 'high',
                'characteristics' => [
                    'notes' => 'Handles flood-prone paddies; staple in South Asian production.',
                ],
                'is_active' => true,
            ]
        );

        RiceVariety::updateOrCreate(
            ['variety_code' => 'RICE-RED'],
            [
                'name' => 'Heirloom Red Cargo',
                'description' => 'Deep-red wholegrain rice prized for antioxidants.',
                'maturity_days' => 140,
                'average_yield_per_hectare' => 3.9,
                'season' => 'dry',
                'grain_type' => 'medium',
                'resistance_level' => 'medium',
                'characteristics' => [
                    'notes' => 'Requires careful drying; fetches premium prices in niche markets.',
                ],
                'is_active' => true,
            ]
        );

        RiceVariety::updateOrCreate(
            ['variety_code' => 'RICE-KOSHI'],
            [
                'name' => 'Koshihikari',
                'description' => 'Short-grain Japanese rice with excellent eating quality.',
                'maturity_days' => 118,
                'average_yield_per_hectare' => 5.3,
                'season' => 'dry',
                'grain_type' => 'short',
                'resistance_level' => 'high',
                'characteristics' => [
                    'notes' => 'Best grown in cooler climates; top choice for sushi-grade rice.',
                ],
                'is_active' => true,
            ]
        );

        // New Philippine Varieties
        RiceVariety::updateOrCreate(
            ['variety_code' => 'RICE-NSIC-RC222'],
            [
                'name' => 'NSIC Rc 222 (Tubigan 18)',
                'description' => 'High-yielding variety capable of surviving flash floods, also known as Tubigan 18.',
                'maturity_days' => 114,
                'average_yield_per_hectare' => 6.1, // Average of 6-10 range conservative
                'season' => 'wet', // Also dry
                'grain_type' => 'long',
                'resistance_level' => 'high',
                'characteristics' => [
                    'notes' => 'Resistant to stem borer and green leafhopper. Moderate resistance to brown planthopper.',
                ],
                'is_active' => true,
            ]
        );

        RiceVariety::updateOrCreate(
            ['variety_code' => 'RICE-NSIC-RC216'],
            [
                'name' => 'NSIC Rc 216 (Tubigan 17)',
                'description' => 'Popular inbred variety known for good eating quality and high yield potential.',
                'maturity_days' => 112,
                'average_yield_per_hectare' => 6.0,
                'season' => 'dry', // Also wet
                'grain_type' => 'long',
                'resistance_level' => 'medium',
                'characteristics' => [
                    'notes' => 'Moderately resistant to brown planthopper and green leafhopper.',
                ],
                'is_active' => true,
            ]
        );

        RiceVariety::updateOrCreate(
            ['variety_code' => 'RICE-NSIC-RC160'],
            [
                'name' => 'NSIC Rc 160 (Tubigan 14)',
                'description' => 'Known for its premium eating quality, soft and moist when cooked.',
                'maturity_days' => 122,
                'average_yield_per_hectare' => 5.6,
                'season' => 'wet',
                'grain_type' => 'long',
                'resistance_level' => 'medium',
                'characteristics' => [
                    'notes' => 'Highly favored by millers and traders due to high head rice recovery.',
                ],
                'is_active' => true,
            ]
        );

        RiceVariety::updateOrCreate(
            ['variety_code' => 'RICE-DINORADO'],
            [
                'name' => 'Dinorado',
                'description' => 'Indigenous upland rice variety from Mindanao, known for its pinkish grain and aroma.',
                'maturity_days' => 130,
                'average_yield_per_hectare' => 3.5,
                'season' => 'dry',
                'grain_type' => 'medium',
                'resistance_level' => 'low',
                'characteristics' => [
                    'notes' => 'Aromatic and sticky when cooked. Often grown in upland areas.',
                ],
                'is_active' => true,
            ]
        );

        RiceVariety::updateOrCreate(
            ['variety_code' => 'RICE-SINANDOMENG'],
            [
                'name' => 'Sinandomeng',
                'description' => 'Traditional variety popular for domestic consumption, soft and white.',
                'maturity_days' => 115,
                'average_yield_per_hectare' => 4.5,
                'season' => 'wet',
                'grain_type' => 'long',
                'resistance_level' => 'medium',
                'characteristics' => [
                    'notes' => 'Good eating quality, remains soft even when cold.',
                ],
                'is_active' => true,
            ]
        );
    }
}
