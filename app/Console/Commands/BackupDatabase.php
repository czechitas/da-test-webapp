<?php

declare(strict_types=1);

namespace CzechitasApp\Console\Commands;

use Ifsnop\Mysqldump\Mysqldump;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup database to default storage';

    private function getFolder(): string
    {
        return \baseFolderName() . 'db-backup/';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $dump = new Mysqldump(
            \sprintf(
                'mysql:host=%s;port=%s;dbname=%s',
                \config('database.connections.mysql.host'),
                \config('database.connections.mysql.port'),
                \config('database.connections.mysql.database'),
            ),
            \config('database.connections.mysql.username'),
            \config('database.connections.mysql.password'),
            [
                'no-data' => ['password_resets'],
            ],
        );

        $tmpDisk = Storage::disk('local');
        if (!$tmpDisk->exists('tmp')) {
            $tmpDisk->makeDirectory('tmp');
        }
        $filename = \sprintf('%s.sql', Carbon::now()->format('Y-m-d_H-i-s.u'));
        $tmpPath = 'tmp/' . $filename;
        $dump->start($tmpDisk->path($tmpPath));

        if (!Storage::exists($this->getFolder())) {
            Storage::makeDirectory($this->getFolder());
        }

        $finalPath = \sprintf('%s%s', $this->getFolder(), $filename);
        Storage::writeStream($finalPath, $tmpDisk->readStream($tmpPath));
        $tmpDisk->delete($tmpPath);

        $this->info("Database dump created in {$finalPath}");

        return 0;
    }
}
