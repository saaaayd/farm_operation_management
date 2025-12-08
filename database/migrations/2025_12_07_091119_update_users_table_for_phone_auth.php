<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->after('id');
            $table->string('middle_initial')->nullable()->after('first_name');
            $table->string('last_name')->after('middle_initial');
            $table->string('email')->nullable()->change();
            $table->string('phone')->unique()->change();
            // We can keep 'name' as a computed column or just fill it for backward compatibility
            // For now, let's make it nullable so we don't have to fill it immediately if we don't want to
            $table->string('name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'middle_initial', 'last_name']);
            $table->string('email')->nullable(false)->change();
            // Reverting phone unique constraint might be tricky if there are duplicates, but for down() it's okay
            $table->dropUnique(['phone']);
            $table->string('name')->nullable(false)->change();
        });
    }
};
