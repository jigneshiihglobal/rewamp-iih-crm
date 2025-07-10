<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UpcomingExpenseReminder extends Mailable
{
    use Queueable, SerializesModels;
    public $expenses;
    public string $workspaceSlug;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Collection $expenses, string $workspaceSlug)
    {
        $this->expenses = $expenses;
        $this->workspaceSlug = $workspaceSlug;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        switch ($this->workspaceSlug) {
            case 'shalin-designs':
                $view = 'emails.shalin-designs.expenses.upcoming_expense_reminder';
                $from_addr = config('shalin-designs.mail_from_emails.upcoming_expense_reminder');
                $from_name = config('shalin-designs.mail_from_names.upcoming_expense_reminder', 'Shalin Designs - Expenses');
                break;

            default:
                $view = 'emails.cron.upcoming_expense_reminder';
                $from_addr = config('mail.from.address');
                $from_name = config('custom.mail.from.name.upcoming_expense_reminder', 'IIH CRM - Expenses');
                break;
        }
        return $this
            ->subject('Reminder: Expenses due soon')
            ->from($from_addr, $from_name)
            ->view($view);
    }
}
