<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('laborers', function (Blueprint $table) {
            if (!Schema::hasColumn('laborers', 'profile_picture')) {
                $table->string('profile_picture')->nullable()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('laborers', function (Blueprint $table) {
            if (Schema::hasColumn('laborers', 'profile_picture')) {
                $table->dropColumn('profile_picture');
            }
        });
    }
};
