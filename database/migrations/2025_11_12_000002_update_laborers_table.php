<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laborers', function (Blueprint $table) {
            if (!Schema::hasColumn('laborers', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            }

            if (!Schema::hasColumn('laborers', 'phone')) {
                $table->string('phone')->nullable()->after('name');
            }

            if (!Schema::hasColumn('laborers', 'email')) {
                $table->string('email')->nullable()->after('phone');
            }

            if (!Schema::hasColumn('laborers', 'address')) {
                $table->string('address')->nullable()->after('email');
            }

            if (!Schema::hasColumn('laborers', 'skill_level')) {
                $table->string('skill_level')->nullable()->after('address');
            }

            if (!Schema::hasColumn('laborers', 'specialization')) {
                $table->string('specialization')->nullable()->after('skill_level');
            }

            if (!Schema::hasColumn('laborers', 'status')) {
                $table->string('status')->nullable()->after('specialization');
            }

            if (!Schema::hasColumn('laborers', 'hire_date')) {
                $table->date('hire_date')->nullable()->after('status');
            }

            if (!Schema::hasColumn('laborers', 'emergency_contact')) {
                $table->string('emergency_contact')->nullable()->after('hire_date');
            }

            if (!Schema::hasColumn('laborers', 'notes')) {
                $table->text('notes')->nullable()->after('emergency_contact');
            }
        });

        // Migrate existing contact value into phone if present
        try {
            DB::statement("UPDATE laborers SET phone = contact WHERE phone IS NULL AND contact IS NOT NULL");
        } catch (\Throwable $e) {
            // ignore
        }
    }

    public function down(): void
    {
        Schema::table('laborers', function (Blueprint $table) {
            if (Schema::hasColumn('laborers', 'user_id')) {
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('laborers', 'phone')) {
                $table->dropColumn('phone');
            }
            if (Schema::hasColumn('laborers', 'email')) {
                $table->dropColumn('email');
            }
            if (Schema::hasColumn('laborers', 'address')) {
                $table->dropColumn('address');
            }
            if (Schema::hasColumn('laborers', 'skill_level')) {
                $table->dropColumn('skill_level');
            }
            if (Schema::hasColumn('laborers', 'specialization')) {
                $table->dropColumn('specialization');
            }
            if (Schema::hasColumn('laborers', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('laborers', 'hire_date')) {
                $table->dropColumn('hire_date');
            }
            if (Schema::hasColumn('laborers', 'emergency_contact')) {
                $table->dropColumn('emergency_contact');
            }
            if (Schema::hasColumn('laborers', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
