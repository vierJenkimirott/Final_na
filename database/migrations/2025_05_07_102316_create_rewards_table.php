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
        // REWARDS
        // This table stores the rewards that can be earned by students.
        // For example: Goingout 6 hours
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('date_issued');
            $table->integer('points_required');
            $table->timestamps();
        });

        //REWARD REQUESTS
        // This table stores the requests made by students to redeem rewards.
        // For example: A student requests to redeem a reward for 6 hours of going out.
        Schema::create('reward_requests', function (Blueprint $table) {
            $table->id();
            $table->string('student_id', 50);
            $table->foreignId('reward_id')->constrained('rewards');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected']);
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->foreign('student_id')->references('student_id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->timestamps();
        });

        //REDEMPTIONS
        // This table stores the redemptions made by students.
        // For example: A student redeems a reward for 6 hours of going out.
        Schema::create('redemptions', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->foreign('user_id')->references('student_id')->on('users')->onDelete('cascade');
            $table->foreignId('reward_id')->constrained('rewards')->onDelete('cascade');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        //MONTHLY POINTS
        // This table stores the monthly points earned by students.
        // For example: A student earns 100 points for the month of January 2025.
        Schema::create('monthly_points', function (Blueprint $table) {
            $table->id();
            $table->string('student_id', 50);
            $table->string('month_year', 7);
            $table->integer('points')->default(100);
            $table->enum('status', ['eligible', 'ineligible'])->default('eligible');
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('student_id')->references('student_id')->on('users')->onDelete('cascade');
        });

        //REDEMPTION HISTORY
        // This table stores the history of redemptions made by students.
        // For example: A student redeems a reward for 6 hours of going out.
        Schema::create('redemption_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('redemption_id')->constrained('redemptions')->onDelete('cascade');
            $table->timestamp('redeemed_at')->useCurrent();
            $table->timestamps();
        });
        //REWARD HISTORY
        // This table stores the history of rewards earned by students.
        // For example: A student earns a reward for 6 hours of going out.



    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redemption_history');
        Schema::dropIfExists('monthly_points');
        Schema::dropIfExists('redemptions');
        Schema::dropIfExists('reward_requests');
        Schema::dropIfExists('rewards');
    }
};