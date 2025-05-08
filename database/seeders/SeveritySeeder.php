<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeveritySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('severities')->insert([
            ['severity_name' => 'Low'],
            ['severity_name' => 'Medium'],
            ['severity_name' => 'High'],
            ['severity_name' => 'Very High'],
        ]);
    }
}
