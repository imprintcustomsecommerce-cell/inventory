<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup {--keep=14 : Number of recent backups to retain}';

    protected $description = 'Dump the MySQL database to storage/app/backups';

    public function handle(): int
    {
        $db = config('database.connections.mysql');
        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);

        $file = $dir . '/backup-' . now()->format('Y-m-d_His') . '.sql';
        $dump = env('MYSQLDUMP_PATH', 'mysqldump');

        $args = [
            $dump,
            '--host=' . $db['host'],
            '--port=' . $db['port'],
            '--user=' . $db['username'],
        ];
        if (!empty($db['password'])) {
            $args[] = '--password=' . $db['password'];
        }
        $args[] = $db['database'];

        $this->info("Backing up {$db['database']}…");

        $process = new Process($args, timeout: 300);
        $handle = fopen($file, 'w');
        $process->run(function ($type, $buffer) use ($handle) {
            if ($type === Process::OUT) {
                fwrite($handle, $buffer);
            }
        });
        fclose($handle);

        if (!$process->isSuccessful()) {
            @unlink($file);
            $this->error('Backup failed: ' . trim($process->getErrorOutput()));
            $this->line('Tip: set MYSQLDUMP_PATH in .env to the full mysqldump path.');
            return self::FAILURE;
        }

        $this->pruneOldBackups($dir, (int) $this->option('keep'));

        $this->info('Backup saved: ' . $file . ' (' . $this->humanSize(filesize($file)) . ')');
        return self::SUCCESS;
    }

    private function pruneOldBackups(string $dir, int $keep): void
    {
        $files = collect(File::files($dir))
            ->filter(fn ($f) => str_ends_with($f->getFilename(), '.sql'))
            ->sortByDesc(fn ($f) => $f->getMTime())
            ->values();

        $files->slice($keep)->each(fn ($f) => File::delete($f->getPathname()));
    }

    private function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 1) . ' ' . $units[$i];
    }
}
