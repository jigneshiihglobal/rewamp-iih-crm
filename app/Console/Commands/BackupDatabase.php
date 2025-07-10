<?php

namespace App\Console\Commands;

use App\Helpers\ActivityLogHelper;
use App\Mail\CronErrorMail;
use App\Mail\DatabaseBackupMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup current database, mail backup to admin and delete 1 week old backups';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $this->line("[" . date('Y-m-d H:i:s') . "] Running Command \"" . $this->signature . "\"");

        $orig_max_exec_time = ini_get('max_execution_time');
        ini_set('max_execution_time', 120); // 2 min

        try {
            // Backup current database

            $db_user = config('database.connections.mysql.username', 'root');
            $db_pass = config('database.connections.mysql.password', '');
            $db_host = config('database.connections.mysql.host', 'localhost');
            $db_name = config('database.connections.mysql.database', 'iih_crm');

            $backupsStore = storage_path('app/backups/');

            if (!File::exists($backupsStore)) {
                File::makeDirectory($backupsStore, 0755, true);
            }

            $fileName = $backupsStore . 'iih_crm_' . date('Y_m_d') .  '.sql';

            $mysqlDumpProcess = Process::fromShellCommandline(
                "mysqldump --user={$db_user} --password={$db_pass} --host={$db_host} {$db_name} > \"{$fileName}\""
            );

            $mysqlDumpProcess->run(function ($type, $buffer) {
                if (Process::ERR === $type) {
                    $this->line("[" . date('Y-m-d H:i:s') . "] MYSQLDUMP [" . $type . "]: "  . $buffer);
                } else {
                    $this->line("[" . date('Y-m-d H:i:s') . "] MYSQLDUMP [" . $type . "]: "  . $buffer);
                }
            });

            if (!$mysqlDumpProcess->isSuccessful()) {
                $this->error("[" . date('Y-m-d H:i:s') . "] MYSQLDUMP ERROR OCCURRED");
                return 0;
            }

            $this->info("[" . date('Y-m-d H:i:s') . "] Database backup generated successfully. Now sending backup in mail to admin.");

            $this->sendDBBackupMail($fileName);

            $this->removeOldBackups($backupsStore);

            $this->info("[" . date('Y-m-d H:i:s') . "] Database backup CRON complete.");

            ActivityLogHelper::log(
                'db_backup',
                'Database backup complete',
                [],
                request(),
                User::role('Superadmin')->first(),
            );
        } catch (\Throwable $th) {

            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred while running backup: " . $th->getMessage());
            $this->error($th);
            $this->sendCronErrorMail($th, $th->getMessage());
        } finally {

            ini_set('max_execution_time', $orig_max_exec_time); // revert back to original settings

        }

        return 0;
    }

    private function sendDBBackupMail(string $fileName)
    {
        try {

            Mail::to(
                config(
                    'custom.database.backup.recipient',
                    []
                )
            )
                ->send(new DatabaseBackupMail($fileName));

            $this->info("[" . date('Y-m-d H:i:s') . "] Mail sent successful with database backup file attached.");
        } catch (\Throwable $th) {

            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred while sending mail: " . $th->getMessage());
            $this->error($th);
        }
    }

    private function removeOldBackups(string $backupsStore)
    {
        try {

            $duration = (int) config('custom.database.backup.duration');
            $this->line("[" . date('Y-m-d H:i:s') . "] Deleting {$duration} days old backups.");

            if (File::exists($backupsStore)) {
                $files = File::files($backupsStore);
                $keepAfter = now()->subDays($duration)->timestamp;

                foreach ($files as $file) {
                    $fileTimestamp = File::lastModified($file);

                    if (
                        $fileTimestamp < $keepAfter
                        && pathinfo($file, PATHINFO_EXTENSION) === 'sql'
                        && strpos($file->getFilename(), 'iih_crm_') === 0
                    ) {
                        File::delete($file);
                    }
                }
            }

            $this->info("[" . date('Y-m-d H:i:s') . "] Old backups deleted successfully.");
        } catch (\Throwable $th) {

            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred deleting old backups: " . $th->getMessage());
            $this->error($th);
        }
    }

    public function sendCronErrorMail(\Throwable $th = null, string $title = '')
    {
        try {
            Mail::to(
                config(
                    'custom.cron_email_recipients.error_mail_address',
                    []
                )
            )
                ->send(new CronErrorMail($title, $th, "Database backup CRON - Error occurred"));
        } catch (\Throwable $th) {
            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred while sending mail: " . $th->getMessage());
            $this->error($th);
        }
    }
}
