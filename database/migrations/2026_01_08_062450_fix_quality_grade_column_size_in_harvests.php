<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Fix quality_grade column to allow longer values like 'grade_a', 'premium'
        DB::statement('ALTER TABLE harvests ALTER COLUMN quality_grade TYPE VARCHAR(50)');
    }

    public function down(): void
    {
        // Revert to varchar(1) - though this may lose data
        DB::statement('ALTER TABLE harvests ALTER COLUMN quality_grade TYPE VARCHAR(1)');
    }
};
