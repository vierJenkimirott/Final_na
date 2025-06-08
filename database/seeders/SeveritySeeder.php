<?php

namespace Database\Seeders;

use App\Models\Severity;
use Illuminate\Database\Seeder;

class SeveritySeeder extends Seeder
{
    public function run(): void
    {
        $severities = [
            ['severity_name' => 'Low'],
            ['severity_name' => 'Medium'],
            ['severity_name' => 'High'],
            ['severity_name' => 'Very High'],
        ];

        foreach ($severities as $severity) {
            Severity::updateOrCreate($severity);
        }
    }
}
