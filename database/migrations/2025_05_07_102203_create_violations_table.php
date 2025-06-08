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
    { // VIOLATIONS
        // This table stores the violations that have been committed by students.
        Schema::create('violations', function (Blueprint $table) {
            $table->id();
            $table->string('student_id');
            $table->foreign('student_id')->references('student_id')->on('users')->onDelete('cascade');
            $table->foreignId('violation_type_id')->constrained();
            $table->string('severity')->nullable()->comment('Severity of the violation');
            $table->date('violation_date');
            $table->enum('penalty', ['W', 'VW', 'WW', 'Pro', 'Exp'])->comment('W=Warning, VW=Verbal Warning, WW=Written Warning, Pro=Probation, Exp=Expulsion');
            $table->text('consequence');
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->foreign('recorded_by')->references('id')->on('users');
            $table->enum('status', ['pending', 'active', 'resolved', 'appealed'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            // Add indexes for frequently queried columns
            $table->index('violation_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('violations');
    }
};  