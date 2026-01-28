<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add the new allowed value 'planned' to the status check constraint
        DB::statement("
            ALTER TABLE plantings
            DROP CONSTRAINT IF EXISTS plantings_status_check
        ");

        DB::statement("
            ALTER TABLE plantings
            ADD CONSTRAINT plantings_status_check
            CHECK (status IN ('planned', 'planted', 'growing', 'ready', 'harvested', 'failed'))
        ");

        // Ensure existing rows with future planting dates are marked as planned
        DB::statement("
            UPDATE plantings
            SET status = 'planned'
            WHERE status = 'planted' AND planting_date > NOW()
        ");
    }

    public function down(): void
    {
        // Revert back to the original constraint
        DB::statement("
            ALTER TABLE plantings
            DROP CONSTRAINT IF EXISTS plantings_status_check
        ");

        DB::statement("
            ALTER TABLE plantings
            ADD CONSTRAINT plantings_status_check
            CHECK (status IN ('planted', 'growing', 'ready', 'harvested', 'failed'))
        ");
    }
};

