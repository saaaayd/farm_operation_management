<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('laborers', function (Blueprint $table) {
            $table->string('emergency_contact_name')->nullable()->after('emergency_contact');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
        });

        // Migrate existing data
        // Assuming existing contact string is the name, or checking if it looks like a number might be too complex for SQL alone reliably without regex support in all DBs.
        // For simplicity and safety, we'll assign the whole string to emergency_contact_name.
        DB::statement("UPDATE laborers SET emergency_contact_name = emergency_contact WHERE emergency_contact IS NOT NULL");

        Schema::table('laborers', function (Blueprint $table) {
            $table->dropColumn('emergency_contact');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laborers', function (Blueprint $table) {
            $table->string('emergency_contact')->nullable()->after('hire_date');
        });

        // Restore data roughly (concat)
        DB::statement("UPDATE laborers SET emergency_contact = CONCAT(COALESCE(emergency_contact_name, ''), ' ', COALESCE(emergency_contact_phone, '')) WHERE emergency_contact_name IS NOT NULL OR emergency_contact_phone IS NOT NULL");

        Schema::table('laborers', function (Blueprint $table) {
            $table->dropColumn(['emergency_contact_name', 'emergency_contact_phone']);
        });
    }
};
