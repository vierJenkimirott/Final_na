<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class RandomViolationsSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing violations to avoid duplicates
        DB::table('violations')->delete();
        
        // Get all students
        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'student');
        })->get();

        // Get all violation types from the ViolationsSeeder
        $violationTypes = DB::table('violation_types')->get();
        
        // Get severity IDs
        $lowId = DB::table('severities')->where('severity_name', 'Low')->value('id');
        $mediumId = DB::table('severities')->where('severity_name', 'Medium')->value('id');
        $highId = DB::table('severities')->where('severity_name', 'High')->value('id');
        $veryHighId = DB::table('severities')->where('severity_name', 'Very High')->value('id');
        
        // Map default penalties to severities
        $penaltyToSeverity = [
            'W' => $lowId,
            'VW' => $mediumId,
            'WW' => $highId,
            'Pro' => $veryHighId,
            'Exp' => $veryHighId,
        ];

        // Get educator IDs for recorded_by field
        $educatorIds = User::whereHas('roles', function($query) {
            $query->where('name', 'educator');
        })->pluck('id')->toArray();
        
        // Define specific violations to ensure coverage of all categories
        $specificViolations = [
            // General Behavior violations
            ['category' => 'General Behavior', 'penalties' => ['W', 'VW', 'WW', 'Exp']],
            // Dress Code violations
            ['category' => 'Dress Code', 'penalties' => ['W']],
            // Room Rules violations
            ['category' => 'Room Rules', 'penalties' => ['W', 'VW']],
            // Schedule violations
            ['category' => 'Schedule', 'penalties' => ['W', 'VW', 'WW']],
            // Equipment violations
            ['category' => 'Equipment', 'penalties' => ['VW', 'WW']],
            // Center Tasking violations
            ['category' => 'Center Tasking', 'penalties' => ['VW']]
        ];
        
        // Select only 5 specific students to have violations
        // Using student IDs to ensure consistent selection
        $violatorStudentIds = [
            'S2025001', // John Doe
            'S2025003', // Jane Smith
            'S2025006', // Micheal Jovita
            'S2025008', // Nicole Oco
            'S2025010'  // Marie Dasian
        ];
        
        // Filter students to only those in the violator list
        $violators = $students->filter(function($student) use ($violatorStudentIds) {
            return in_array($student->student_id, $violatorStudentIds);
        });
        
        // Log information about the seeding process
        echo "Seeding violations for 5 specific students only:\n";
        foreach ($violators as $violator) {
            echo "- {$violator->name} ({$violator->student_id})\n";
        }
        
        // Generate violations only for the selected students
        foreach ($violators as $student) {
            echo "Creating violations for {$student->name}...\n";
            
            // Each student will have exactly 2-3 violations total
            $totalViolationsForStudent = rand(2, 3);
            echo "  - Will create {$totalViolationsForStudent} violations\n";
            
            // Create a pool of possible violations with different categories and severities
            $violationPool = [];
            
            // Add variety to the pool
            foreach ($specificViolations as $specificViolation) {
                $categoryName = $specificViolation['category'];
                $categoryId = DB::table('offense_categories')->where('category_name', $categoryName)->value('id');
                
                // Get violation types for this category
                $categoryViolations = $violationTypes->where('offense_category_id', $categoryId);
                
                foreach ($specificViolation['penalties'] as $penalty) {
                    // Find a violation type with this penalty
                    $matchingViolations = $categoryViolations->where('default_penalty', $penalty);
                    
                    if ($matchingViolations->count() > 0) {
                        // Add to the pool
                        foreach ($matchingViolations as $violation) {
                            $violationPool[] = [
                                'violation_type' => $violation,
                                'penalty' => $penalty,
                                'severity_id' => $penaltyToSeverity[$penalty] ?? $lowId
                            ];
                        }
                    }
                }
            }
            
            // Shuffle the pool to get random violations
            shuffle($violationPool);
            
            // Select only the number of violations we want
            $selectedViolations = array_slice($violationPool, 0, $totalViolationsForStudent);
            
            // Create the selected violations
            foreach ($selectedViolations as $index => $violationData) {
                $violationType = $violationData['violation_type'];
                $penalty = $violationData['penalty'];
                $severityId = $violationData['severity_id'];
                $severityName = DB::table('severities')->where('id', $severityId)->value('severity_name');
                
                // Generate dates only for January, April, August, and November
                $months = [1, 4, 8, 11]; // Only these specific months
                $month = $months[array_rand($months)];
                $day = rand(1, 28); // Using 28 to be safe for all months
                $year = 2024;
                
                $violationDate = Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
                
                // Set offense as 1st, 2nd, or 3rd based on the violation index
                $offenseCount = $index + 1;
                if ($offenseCount > 3) $offenseCount = 3; // Cap at 3rd offense
                $offenseText = $offenseCount . ($offenseCount == 1 ? 'st' : ($offenseCount == 2 ? 'nd' : 'rd'));
                
                echo "  - Creating {$severityName} severity violation for {$month}/{$day}/{$year}\n";
                
                DB::table('violations')->insert([
                    'student_id' => $student->student_id,
                    'sex' => $student->sex,
                    'violation_type_id' => $violationType->id,
                    'severity' => $severityName,
                    'offense' => $offenseText,
                    'violation_date' => $violationDate,
                    'penalty' => $penalty,
                    'consequence' => 'Violation for behavior chart testing',
                    'recorded_by' => $educatorIds[array_rand($educatorIds)],
                    'status' => 'active',
                    'created_at' => $violationDate . ' ' . rand(8, 17) . ':' . rand(0, 59) . ':' . rand(0, 59),
                    'updated_at' => $violationDate . ' ' . rand(8, 17) . ':' . rand(0, 59) . ':' . rand(0, 59),
                ]);
            }
        }
        
        echo "Violation seeding completed. Only 5 students have violation records.\n";
    }
}
