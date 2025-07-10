<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule
            ->command('db:backup')
            ->dailyAt('20:00')
            ->appendOutputTo(storage_path('logs/backup_database.log'));

        $schedule
            ->command('invoices:subscriptions:renew')
            ->dailyAt('04:00')
            ->appendOutputTo(storage_path('logs/renew_subscription_invoices.log'));

        $schedule
            ->command('invoices:payments:remind')
            ->dailyAt("05:00")
            ->appendOutputTo(storage_path('logs/remind_payments_invoices.log'));

        $schedule
            ->command('more_trees:plant')
            ->hourly()
            ->appendOutputTo(storage_path('logs/more_trees.log'));

        $schedule
            ->command('expenses:upcoming:remind')
            ->dailyAt('09:10')
            ->appendOutputTo(storage_path('logs/upcoming_expense_reminder.log'));

        $schedule
            ->command('follow_ups:remind')
            ->everyFifteenMinutes()
            ->appendOutputTo(storage_path('logs/follow_up_reminder.log'));

        /*$schedule
            ->command('live_currencies:store')
            ->everyFourHours()->unlessBetween('20:30', '7:30')
            ->appendOutputTo(storage_path('logs/live_currencies.log'));*/

        $schedule
            ->command('monthly_live_currencies:monthlyStore')
            ->monthly()
            ->appendOutputTo(storage_path('logs/monthly_live_currencies.log'));

        $schedule
            ->command('contacted_lead_status')
            ->dailyAt('09:30')
            ->appendOutputTo(storage_path('logs/lead_status.log'));

        $schedule
            ->command('follow_up_and_lost')
            ->dailyAt('09:45')
            ->appendOutputTo(storage_path('logs/follow_up_and_lost.log'));

        $schedule
            ->command('web_summit')
            ->dailyAt('09:55')
            ->appendOutputTo(storage_path('logs/web_summit.log'));

        /* Below Cron set in 23/4 10:00 AM for client mail send to refer and earn */
        /*$schedule
            ->command('refer_and_earn')
            ->cron('0 10 23 4 *')
            ->appendOutputTo(storage_path('logs/refer_and_earn.log'));*/
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
