<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStudentFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Skip adding student_id as it already exists
            $table->string('fname')->nullable()->after('name');
            $table->string('lname')->nullable()->after('fname');
            
            // Add index to student_id for foreign key constraint if it doesn't exist
            if (!Schema::hasIndex('users', 'users_student_id_index')) {
                $table->index('student_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['fname', 'lname']);
            
            // Only drop the index if we created it
            if (Schema::hasIndex('users', 'users_student_id_index')) {
                $table->dropIndex('users_student_id_index');
            }
        });
    }
}

return new AddStudentFieldsToUsersTable;
