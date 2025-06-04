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
        Schema::create('student_details', function (Blueprint $table) {
            $table->id();
             $table->unsignedBigInteger('user_id');
            $table->string('student_id')->unique();
            $table->string('batch')->nullable();
            $table->string('group')->nullable();
            $table->string('student_number')->nullable();
            $table->string('training_code')->nullable();
            $table->decimal('grade', 3, 1)->nullable()->comment('Student grade between 1.0 and 5.0');
            $table->timestamps();
            
            // Add foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_details');
    }
};
