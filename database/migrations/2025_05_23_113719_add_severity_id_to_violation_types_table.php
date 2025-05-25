<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('violation_types', function (Blueprint $table) {
        $table->foreignId('severity_id')->nullable()->constrained('severities')->onDelete('set null');
    });
}

public function down()
{
    Schema::table('violation_types', function (Blueprint $table) {
        $table->dropForeign(['severity_id']);
        $table->dropColumn('severity_id');
    });
}
};
