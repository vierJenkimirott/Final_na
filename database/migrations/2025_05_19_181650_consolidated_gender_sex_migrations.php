<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This is a consolidated migration that combines the functionality of:
     * - add_gender_to_users_table.php
     * - add_sex_column_to_users_table.php
     * - add_sex_column_to_users_table_fix.php
     * - rename_gender_to_sex_in_violations_table.php
     */
    public function up(): void
    {
        // From add_gender_to_users_table.php
        if (!Schema::hasColumn('users', 'gender')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('gender')->nullable()->after('email');
            });
        }
        
        // From add_sex_column_to_users_table_fix.php
        if (!Schema::hasColumn('users', 'sex')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('sex')->nullable()->after('gender');
            });
        }
        
        // From rename_gender_to_sex_in_violations_table.php
        if (!Schema::hasColumn('violations', 'sex')) {
            Schema::table('violations', function (Blueprint $table) {
                $table->string('sex')->nullable()->after('student_id');
            });
        }
        
        // Copy data from gender to sex in users table if needed
        if (Schema::hasColumn('users', 'gender') && Schema::hasColumn('users', 'sex')) {
            DB::statement('UPDATE users SET sex = gender WHERE sex IS NULL AND gender IS NOT NULL');
        }
        
        // Copy data from gender to sex in violations table if needed
        if (Schema::hasColumn('violations', 'gender') && Schema::hasColumn('violations', 'sex')) {
            DB::statement('UPDATE violations SET sex = gender WHERE sex IS NULL AND gender IS NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // From rename_gender_to_sex_in_violations_table.php
        if (Schema::hasColumn('violations', 'sex')) {
            Schema::table('violations', function (Blueprint $table) {
                $table->dropColumn('sex');
            });
        }
        
        // From add_sex_column_to_users_table_fix.php
        if (Schema::hasColumn('users', 'sex')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('sex');
            });
        }
        
        // From add_gender_to_users_table.php
        if (Schema::hasColumn('users', 'gender')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('gender');
            });
        }
    }
};
