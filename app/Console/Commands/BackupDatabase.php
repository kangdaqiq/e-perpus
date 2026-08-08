<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    /**
     * Nama dan signature perintah console.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * Deskripsi perintah console.
     *
     * @var string
     */
    protected $description = 'Backup database MySQL lokal dan unggah ke Cloudflare R2 Storage';

    /**
     * Eksekusi perintah console.
     */
    public function handle()
    {
        $this->info('Starting database backup...');

        $filename = "backup-" . Carbon::now()->format('Y-m-d-H-i-s') . ".sql";
        $path = storage_path("app/backups");

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $filePath = "$path/$filename";

        // Konfigurasi Database
        $host = config('database.connections.mysql.host');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $database = config('database.connections.mysql.database');

        // Path ke mysqldump
        if (PHP_OS_FAMILY === 'Windows') {
            $mysqldumpPath = 'c:\xampp\mysql\bin\mysqldump.exe';
            if (!file_exists($mysqldumpPath)) {
                $mysqldumpPath = 'd:\xampp\mysql\bin\mysqldump.exe';
                if (!file_exists($mysqldumpPath)) {
                    $mysqldumpPath = 'e:\xampp\mysql\bin\mysqldump.exe';
                    if (!file_exists($mysqldumpPath)) {
                        $mysqldumpPath = 'mysqldump';
                    }
                }
            }
        } else {
            $mysqldumpPath = '/usr/bin/mysqldump';
            if (!file_exists($mysqldumpPath)) {
                $mysqldumpPath = 'mysqldump';
            }
        }

        $passwordArg = !empty($password) ? "--password=\"$password\"" : "";
        $command = "\"$mysqldumpPath\" --user=\"$username\" $passwordArg --host=\"$host\" \"$database\" > \"$filePath\" 2>&1";

        $this->info("Executing backup command...");

        $output = [];
        $returnVar = null;
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            $this->info("Backup successful: $filename");

            // Upload ke Cloudflare R2 jika dikonfigurasi
            $r2Endpoint = config('filesystems.disks.r2.endpoint') ?: env('CLOUDFLARE_R2_ENDPOINT');
            if (!empty($r2Endpoint)) {
                $this->info("Uploading backup to Cloudflare R2 Storage (folder: backup-library)...");
                try {
                    Storage::disk('r2')->put("backup-library/$filename", file_get_contents($filePath));
                    $this->info("Backup successfully uploaded to Cloudflare R2.");

                    // Hapus backup lama di Cloudflare R2 (Simpan 7 hari terakhir)
                    $this->cleanOldCloudBackups();
                } catch (\Exception $e) {
                    $this->error("Failed to upload to Cloudflare R2: " . $e->getMessage());
                }
            } else {
                $this->warn("Cloudflare R2 is not configured in .env (CLOUDFLARE_R2_ENDPOINT missing). Local backup saved.");
            }

            // Hapus backup lokal lama (Simpan 7 hari terakhir)
            $this->cleanOldBackups($path);
        } else {
            $this->error("Backup failed with exit code $returnVar");
            if (!empty($output)) {
                $this->error("Error details: " . implode("\n", $output));
            }
        }
    }

    /**
     * Hapus file backup lokal yang lebih lama dari 7 hari.
     */
    private function cleanOldBackups($path)
    {
        $files = glob("$path/*.sql");
        $now = time();
        $keepDays = 7;

        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= 60 * 60 * 24 * $keepDays) {
                    unlink($file);
                    $this->info("Deleted old local backup: " . basename($file));
                }
            }
        }
    }

    /**
     * Hapus file backup di Cloudflare R2 yang lebih lama dari 7 hari.
     */
    private function cleanOldCloudBackups()
    {
        $this->info("Cleaning old backups from Cloudflare R2 (older than 7 days)...");
        try {
            $disk = Storage::disk('r2');
            $files = $disk->files('backup-library');
            $now = time();
            $keepDays = 7;

            foreach ($files as $file) {
                if (!preg_match('/^backup-library\/backup-.*\.sql$/', $file)) {
                    continue;
                }

                try {
                    $lastModified = $disk->lastModified($file);
                    if ($now - $lastModified >= 60 * 60 * 24 * $keepDays) {
                        $disk->delete($file);
                        $this->info("Deleted old cloud backup: " . basename($file));
                    }
                } catch (\Exception $e) {
                    $this->error("Failed to process cloud file " . basename($file) . ": " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            $this->error("Failed to clean old cloud backups: " . $e->getMessage());
        }
    }
}
