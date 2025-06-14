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
        Schema::table('violations', function (Blueprint $table) {
            $table->datetime('incident_datetime')->nullable()->after('violation_date')->comment('Date and time when the incident occurred');
            $table->string('incident_place')->nullable()->after('incident_datetime')->comment('Location where the incident occurred');
            $table->text('incident_details')->nullable()->after('incident_place')->comment('Detailed description of the incident');
            $table->string('prepared_by')->nullable()->after('incident_details')->comment('Name of the educator who prepared the report');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            $table->dropColumn(['incident_datetime', 'incident_place', 'incident_details', 'prepared_by']);
        });
    }
};
