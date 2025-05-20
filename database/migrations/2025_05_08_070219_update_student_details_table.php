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
        // This migration is no longer needed as we're creating the table with all required columns
        // in the create_student_details_table migration
        if (Schema::hasTable('student_details')) {
            // If the table already exists for some reason, we can add the grade field
            // but this should be redundant now
            if (!Schema::hasColumn('student_details', 'grade')) {
                Schema::table('student_details', function (Blueprint $table) {
                    $table->decimal('grade', 3, 1)->nullable()->comment('Student grade between 1.0 and 5.0');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is no longer needed as we're handling this in the create_student_details_table migration
        if (Schema::hasTable('student_details') && Schema::hasColumn('student_details', 'grade')) {
            Schema::table('student_details', function (Blueprint $table) {
                // Drop grade field if it exists
                $table->dropColumn('grade');
            });
        }
        
        // No need to add back the original user_id column as string
        // as we're now handling this in the create_student_details_table migration
    }
};
