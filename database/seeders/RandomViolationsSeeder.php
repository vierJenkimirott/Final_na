<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationType;
use Carbon\Carbon;

class RandomViolationsSeeder extends Seeder
{
    /**
     * Run the database seeds to create random violations for testing the behavior chart.
     */
    public function run(): void
    {
        // Clear existing violations first
        DB::table('violations')->truncate();
        
        // Get all student users
        $students = User::role('student')->get();
        
        if ($students->isEmpty()) {
            $this->command->info('No students found. Please run the UserTableSeeder first.');
            return;
        }
        
        // Log the students found
        $this->command->info('Found ' . $students->count() . ' students in the database.');
        
        // Get violation types
        $violationTypes = ViolationType::all();
        
        if ($violationTypes->isEmpty()) {
            $this->command->info('No violation types found. Please run the ViolationTypeSeeder first.');
            return;
        }
        
        // Severity levels and their corresponding penalties
        $severityLevels = [
            'Low' => 'VW', // Verbal Warning
            'Medium' => 'WW', // Written Warning
            'High' => 'Pro', // Probation
            'Very High' => 'Exp', // Expulsion
        ];
        
        // Create violations for each month of the current year
        $year = date('Y');
        $months = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ];
        
        // Select 7 random students for violations
        $selectedStudents = $students->random(min(7, $students->count()));
        
        $this->command->info('Selected 7 students for violations:');
        foreach ($selectedStudents as $index => $student) {
            $this->command->info(($index + 1) . ". Student ID: {$student->student_id}, Name: {$student->name}, Gender: {$student->gender}");
        }
        
        $violationsCreated = 0;
        $maleViolations = array_fill_keys(array_values($months), 0);
        $femaleViolations = array_fill_keys(array_values($months), 0);
        $studentViolationCounts = [];
        
        // Assign 2-3 violations to each selected student in different months
        foreach ($selectedStudents as $student) {
            // Decide how many violations for this student (2 or 3)
            $numViolations = rand(2, 3);
            $this->command->info("Creating {$numViolations} violations for {$student->name}");
            
            // Select random months for this student's violations
            $studentMonths = array_rand($months, $numViolations);
            if (!is_array($studentMonths)) {
                $studentMonths = [$studentMonths]; // Convert to array if only one month selected
            }
            
            foreach ($studentMonths as $monthNum) {
                $monthName = $months[$monthNum];
                
                // Select a random violation type
                $violationType = $violationTypes->random();
                
                // Select a random severity level
                $severity = array_rand($severityLevels);
                $penalty = $severityLevels[$severity];
                
                // Generate a random date within the month
                $day = rand(1, Carbon::createFromDate($year, $monthNum, 1)->daysInMonth);
                $date = Carbon::createFromDate($year, $monthNum, $day);
                
                // Create the violation
                $violation = new Violation();
                $violation->student_id = $student->student_id;
                $violation->violation_type_id = $violationType->id;
                $violation->violation_date = $date->format('Y-m-d');
                $violation->offense = "Random violation for testing: {$violationType->name}";
                $violation->severity = $severity;
                $violation->penalty = $penalty;
                $violation->consequence = $penalty === 'Exp' ? 'Expulsion' : 
                                        ($penalty === 'Pro' ? 'Probation' : 
                                        ($penalty === 'WW' ? 'Written Warning' : 'Verbal Warning'));
                $violation->status = 'active';
                $violation->sex = $student->gender;
                $violation->save();
                
                $violationsCreated++;
                
                // Track violations by gender and month
                if (strtolower($student->gender) === 'male') {
                    $maleViolations[$monthName]++;
                } else if (strtolower($student->gender) === 'female') {
                    $femaleViolations[$monthName]++;
                }
                
                // Track violations per student
                $studentViolationCounts[$student->name] = isset($studentViolationCounts[$student->name]) ?
                    $studentViolationCounts[$student->name] + 1 : 1;
                
                $this->command->info("  - Created violation in {$monthName} {$year}");
            }
        }
        
        // Summarize violations created
        $this->command->info("\nTotal violations created: {$violationsCreated}");
        $this->command->info("Male violations by month: " . json_encode($maleViolations));
        $this->command->info("Female violations by month: " . json_encode($femaleViolations));
        
        // Display violations per student
        $this->command->info("\nViolations per student:");
        foreach ($studentViolationCounts as $name => $count) {
            $this->command->info("- {$name}: {$count} violations");
        }
    }
}
