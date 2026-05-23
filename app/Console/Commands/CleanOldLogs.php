<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\LogRequest;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanOldLogs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'logs:clean';

    /**
     * The console command description.
     */
    protected $description = 'Delete log entries older than 73 hours';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cutoff  = Carbon::now()->subHours(73);
        $deleted = LogRequest::where('called_at', '<', $cutoff)->delete();

        $this->info("Deleted {$deleted} log entries older than 73 hours.");

        return Command::SUCCESS;
    }
}