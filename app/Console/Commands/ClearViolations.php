<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClearViolations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'violations:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all violations from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $count = DB::table('violations')->count();
            DB::table('violations')->delete();
            
            $this->info("Successfully removed {$count} violations from the database.");
            Log::info("All violations cleared via command. {$count} records removed.");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to clear violations: " . $e->getMessage());
            Log::error("Failed to clear violations: " . $e->getMessage());
            
            return Command::FAILURE;
        }
    }
}
