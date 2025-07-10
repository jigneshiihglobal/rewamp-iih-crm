<?php

namespace App\Console\Commands;

use App\Enums\ExpenseFrequency;
use App\Enums\ExpenseType;
use App\Helpers\ActivityLogHelper;
use App\Helpers\CronActivityLogHelper;
use App\Mail\CronErrorMail;
use App\Mail\UpcomingExpenseReminder;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class UpcomingExpensesReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expenses:upcoming:remind';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends a reminder email for upcoming expenses';

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
        $this->line("[" . date('Y-m-d H:i:s') . "] STARTED COMMAND: \"" . $this->signature . "\"");
        $this->line("[" . date('Y-m-d H:i:s') . "] Fetching upcoming expenses");

        try {

            foreach (['shalin-designs', 'iih-global'] as $workspaceSlug) {

                $mailSentIds = [];

                Expense::query()
                    ->select(
                        [
                            'expenses.id',
                            'expenses.client_id',
                            'expenses.project_name',
                            'expenses.expense_sub_type_id',
                            'expenses.amount',
                            'expenses.currency_id',
                            'expenses.type',
                            'expenses.frequency',
                            'expenses.expense_date',
                            'expenses.deleted_at',
                            'expenses.remind_at',
                        ]
                    )
                    ->with(
                        [
                            'client:id,name,email',
                            'currency:id,symbol',
                            'expense_sub_type:id,expense_type_id,title',
                            'expense_sub_type.expense_type:id,title',
                        ]
                    )
                    ->whereHas('client', function ($q) use ($workspaceSlug) {
                        $q->whereHas('workspace', function ($q) use ($workspaceSlug) {
                            $q->where('slug', $workspaceSlug);
                        });
                    })
                    ->whereRaw('expenses.remind_at=CURDATE()')
                    ->chunkById(
                        10,
                        function ($expenses) use (&$mailSentIds, $workspaceSlug) {

                            $ids = $expenses->pluck('id')->toArray();

                            try {
                                DB::transaction(function () use ($expenses, $ids, &$mailSentIds, $workspaceSlug) {
                                    Expense::whereIn('id', $ids)
                                        ->update([
                                            'remind_at' => DB::raw("( CASE WHEN ((type='1') AND (frequency='0')) THEN DATE_ADD(IFNULL(remind_at, expense_date), INTERVAL 1 MONTH) WHEN ((type='1') AND (frequency='1')) THEN DATE_ADD(IFNULL(remind_at, expense_date), INTERVAL 1 YEAR) ELSE IFNULL(remind_at, expense_date) END )"),
                                        ]);

                                    Mail::to(config('custom.cron_email_recipients.upcoming_expenses', []))
                                        ->send(new UpcomingExpenseReminder($expenses, $workspaceSlug));

                                    $mailSentIds = array_merge($mailSentIds, $ids);
                                }, 3);

                                $this->info("[" . date('Y-m-d H:i:s') . "] Mail sent for expense ID: " . implode(', ', $ids));
                            } catch (\Throwable $th) {

                                $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred sending upcoming expense reminder: " . $th->getMessage());
                                $this->error($th);
                            }
                        },
                        'id'
                    );

                $this->info("[" . date('Y-m-d H:i:s') . "] Upcoming expense reminder mail send complete. Expense IDs:" . implode(', ', $mailSentIds));

                $workspace_id = 1;
                if($workspaceSlug == 'shalin-designs'){
                    $workspace_id = 2;
                }

                CronActivityLogHelper::log(
                    'expense.upcoming_expense_reminder',
                    'Upcoming expense reminder mail send successful',
                    [],
                    request(),
                    User::role('Superadmin')->first(),
                    null,
                    $workspace_id
                );
            }
        } catch (\Throwable $th) {
            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred sending upcoming expense reminder: " . $th->getMessage());
            $this->error($th);
            $this->sendCronErrorMail($th, $th->getMessage());
        } finally {
            $this->line("[" . date('Y-m-d H:i:s') . "] COMMAND RUN COMPLETE");
        }

        return 0;
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
                ->send(new CronErrorMail($title, $th, "Upcoming expense reminder CRON - Error occurred"));
        } catch (\Throwable $th) {
            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred while sending mail: " . $th->getMessage());
            $this->error($th);
        }
    }
}
