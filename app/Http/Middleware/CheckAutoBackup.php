<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Ifsnop\Mysqldump\Mysqldump;

class CheckAutoBackup
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only run for authenticated users to avoid overhead on login page
        // And maybe only for write operations? No, user said "when database has changes but backup file update 1 minute after"
        // This implies time-based.
        // Let's run it on every request if auth check passes, or just rely on 'web' group which includes auth middleware usually?
        // Actually 'web' group runs before 'auth' middleware usually.
        // So Auth::check() might false if this runs before auth.
        // But if I append to 'web', it runs after session start.

        $response = $next($request);

        // Run AFTER the request is handled (Terminable middleware style would be better but this is fine for now)
        // Check if we need to backup
        $this->checkAndRunBackup();

        return $response;
    }

    protected function checkAndRunBackup()
    {
        try {
            $configPath = storage_path('app/backup_config.json');
            if (!file_exists($configPath)) {
                return;
            }

            $config = json_decode(file_get_contents($configPath), true);
            if (!$config || (empty($config['minutes']) && empty($config['seconds']))) {
                return; // Not configured or 0
            }

            $interval = ($config['minutes'] * 60) + $config['seconds'];
            $lastBackup = $config['last_backup_at'] ?? 0;

            if (time() - $lastBackup >= $interval) {
                // Time to backup!
                $this->runBackup();

                // Update last backup time
                $config['last_backup_at'] = time();
                file_put_contents($configPath, json_encode($config));
            }

        } catch (\Exception $e) {
            Log::error('Auto scheduling backup failed: ' . $e->getMessage());
        }
    }

    protected function runBackup()
    {
        try {
            $backupPath = storage_path('app/backups');
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            // Filename for scheduled backup
            // User might want a history or just one latest file?
            // "example I set 1 minute then when database has changes but backup file update 1 minute after"
            // This suggests updating a specific file or creating new ones.
            // Creating new ones every minute will fill up disk fast.
            // Let's create a specific file "backup_auto_scheduled.sql" and update it?
            // Or maybe rotate them?
            // Let's stick to valid timestamped files but limit them?
            // Or just update the "V1" file?
            // User Logic: "backup file update 1 minute after".
            // Let's update "backup_auto_scheduled.sql" to be safe and simple.
            // Or better: update the daily V1 file! 
            // "Nghĩa là tạo bản sao thì vẫn có dữ liệu mới" -> User likes the V1 concept.
            // Let's update the V1 file of the day.

            $date = date('Y-m-d');
            $filenameV1 = "backup_{$date}_auto_v1.sql";
            $filepath = $backupPath . '/' . $filenameV1;

            $host = config('database.connections.mysql.host');
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $port = config('database.connections.mysql.port');

            $dump = new Mysqldump(
                "mysql:host={$host};port={$port};dbname={$database}",
                $username,
                $password,
                [
                    'compress' => Mysqldump::NONE,
                    'add-drop-table' => true
                ]
            );
            $dump->start($filepath);

            // Also ensure V2 exists
            $filenameV2 = "backup_{$date}_auto_v2.sql";
            if (!file_exists($backupPath . '/' . $filenameV2)) {
                copy($filepath, $backupPath . '/' . $filenameV2);
            }

        } catch (\Exception $e) {
            Log::error('Scheduled backup execution failed: ' . $e->getMessage());
        }
    }
}
