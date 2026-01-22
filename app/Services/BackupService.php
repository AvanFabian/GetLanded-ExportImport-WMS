<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class BackupService
{
    /**
     * Run the backup process.
     *
     * @param bool $onlyDb Whether to backup only the database.
     * @return bool True if successful, false otherwise.
     */
    public function runBackup(bool $onlyDb = false): bool
    {
        try {
            Log::info('Starting system backup...');
            
            $params = ['--disable-notifications' => true];
            
            if ($onlyDb) {
                $params['--only-db'] = true;
            }

            // Using callSilent to avoid output cluttering if called from HTTP
            $exitCode = Artisan::call('backup:run', $params);

            if ($exitCode === 0) {
                Log::info('System backup completed successfully.');
                return true;
            }

            Log::error('System backup failed with exit code: ' . $exitCode);
            return false;

        } catch (\Exception $e) {
            Log::error('System backup exception: ' . $e->getMessage());
            return false;
        }
    }
}
