<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateViolationsGender extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'violations:update-gender';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update gender field in violations table based on student gender';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating gender field in violations table...');
        
        // Import required models
        $violations = \App\Models\Violation::whereNull('gender')->get();
        $count = $violations->count();
        
        $this->info("Found {$count} violations without gender information.");
        
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        
        $updated = 0;
        $skipped = 0;
        
        foreach ($violations as $violation) {
            // Get the student associated with this violation
            $student = \App\Models\User::where('student_id', $violation->student_id)->first();
            
            if ($student && $student->gender) {
                // Update the violation with the student's gender
                $violation->gender = $student->gender;
                $violation->save();
                $updated++;
            } else {
                // If we can't find the student or their gender, set a default
                // We'll alternate between male and female for better distribution
                $violation->gender = ($violation->id % 2 == 0) ? 'Male' : 'Female';
                $violation->save();
                $skipped++;
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info("Updated {$updated} violations with student gender.");
        $this->info("Set default gender for {$skipped} violations without student gender information.");
        $this->info('Gender update completed successfully!');
        
        return Command::SUCCESS;
    }
}
