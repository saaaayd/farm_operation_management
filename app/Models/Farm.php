<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Farm extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'location',
        'description',
        'total_area',
        'cultivated_area',
        'soil_type',
        'soil_ph',
        'water_source',
        'irrigation_type',
        'primary_rice_variety_id',
        'farm_coordinates',
        'elevation',
        'slope',
        'drainage_system',
        'is_setup_complete',
    ];

    protected $casts = [
        'farm_coordinates' => 'array',
        'total_area' => 'decimal:2',
        'cultivated_area' => 'decimal:2',
        'soil_ph' => 'decimal:1',
        'elevation' => 'decimal:2',
        'slope' => 'decimal:2',
        'is_setup_complete' => 'boolean',
    ];
}
