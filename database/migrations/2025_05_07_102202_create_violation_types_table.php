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
        Schema::create('violation_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offense_category_id')->constrained()->onDelete('cascade');
            $table->string('violation_name', 500);
            $table->text('description')->nullable();
            $table->enum('default_penalty', ['W', 'VW', 'WW', 'Pro', 'Exp'])->comment('W=Warning, VW=Verbal Warning, WW=Written Warning, Pro=Probation, Exp=Expulsion');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('violation_types');
    }
}; 