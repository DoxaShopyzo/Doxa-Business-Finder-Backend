<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'doxa:backup-database';
    protected $aliases = ['backup:database'];
    protected $description = 'Creates a compressed MySQL database backup and prunes backups older than 7 days';

    public function handle(): int
    {
        $backupDir = storage_path('backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $host = config('database.connections.mysql.host', '127.0.0.1');
        $port = config('database.connections.mysql.port', '3306');
        $database = config('database.connections.mysql.database', 'doxa_business_finder');
        $username = config('database.connections.mysql.username', 'root');
        $password = config('database.connections.mysql.password', '');

        $timestamp = now()->format('Y-m-d_His');
        $sqlFile = "{$backupDir}/backup_{$database}_{$timestamp}.sql";
        $gzFile = "{$sqlFile}.gz";

        // Find mysqldump path in WAMP or system PATH
        $mysqldump = 'mysqldump';
        $wampMysql = 'D:/wamp64/bin/mysql';
        if (File::exists($wampMysql)) {
            $dirs = File::directories($wampMysql);
            if (!empty($dirs) && File::exists($dirs[0] . '/bin/mysqldump.exe')) {
                $mysqldump = '"' . str_replace('/', '\\', $dirs[0] . '/bin/mysqldump.exe') . '"';
            }
        }

        $pwdParam = !empty($password) ? "-p\"{$password}\"" : "";
        $cmd = "{$mysqldump} -h {$host} -P {$port} -u {$username} {$pwdParam} {$database} > \"{$sqlFile}\"";

        $this->info("Running mysqldump for [{$database}]...");
        exec($cmd, $output, $returnVar);

        if ($returnVar !== 0 || !File::exists($sqlFile) || File::size($sqlFile) === 0) {
            $this->error("mysqldump failed with error code: {$returnVar}");
            return self::FAILURE;
        }

        // Compress SQL file with gzip
        $data = file_get_contents($sqlFile);
        $gzData = gzencode($data, 9);
        file_put_contents($gzFile, $gzData);
        File::delete($sqlFile);

        $sizeKb = round(File::size($gzFile) / 1024, 2);
        $this->info("Database backup created successfully: [{$gzFile}] ({$sizeKb} KB)");

        // Prune backups older than 7 days
        $files = File::files($backupDir);
        $prunedCount = 0;
        foreach ($files as $file) {
            if ($file->getExtension() === 'gz' && now()->diffInDays(now()->createFromTimestamp($file->getMTime())) > 7) {
                File::delete($file->getPathname());
                $prunedCount++;
            }
        }

        if ($prunedCount > 0) {
            $this->info("Pruned {$prunedCount} old backups (>7 days).");
        }

        return self::SUCCESS;
    }
}
