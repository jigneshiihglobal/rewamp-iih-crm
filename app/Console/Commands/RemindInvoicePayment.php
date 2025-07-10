<?php

namespace App\Console\Commands;

use App\Enums\InvoiceType;
use App\Helpers\ActivityLogHelper;
use App\Helpers\CronActivityLogHelper;
use App\Mail\CronErrorMail;
use App\Mail\PaymentReminderMail;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class RemindInvoicePayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:payments:remind';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminder to customers for payment for due invoices';

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
        $this->line("[" . date('Y-m-d H:i:s') . "] Fetching invoices where payments are due");

        $interval = config('custom.payment_reminder.interval_in_days', 7);
        $orig_max_exec_time = ini_get('max_execution_time');

        ini_set('max_execution_time', 120); // 2 min

        try {

            $unpaidOrPartiallyPaidInvoices = Invoice::query()
                ->select(
                    'invoices.id',
                    'invoices.invoice_number',
                    'invoices.currency_id',
                    'invoices.discount',
                    'invoices.sub_total',
                    'invoices.vat_total',
                    'invoices.grand_total',
                    'invoices.client_id',
                    'invoices.due_date',
                    'invoices.invoice_date',
                    'invoices.created_at',
                    'invoices.client_name',
                    'invoices.payment_reminder_sent_at',
                    'invoices.user_id',
                    'invoices.payment_reminder',
                    DB::raw("DATEDIFF(NOW(), invoices.payment_reminder_sent_at) AS invoice_age_in_days"),
                    'invoices.payment_link',
                )
                ->withSum('payments', 'amount')
                ->with([
                    'client:id,name,email,address_line_1,address_line_2,city,zip_code,country_id,phone,workspace_id',
                    'client.country:id,code,name',
                    'client.workspace:id,slug',
                    'currency:id,code,name,symbol',
                    'payments:id,invoice_id,amount,payment_source_id,reference,paid_at,created_at',
                    'payments.payment_source:id,title',
                    'invoice_items:id,invoice_id,description,price,tax_type,tax_rate,tax_amount,total_price,created_at',
                    'sales_person:id,email',
                ])
                ->where('invoices.payment_status', '!=', 'paid')
                ->where('invoices.payment_reminder', 1)
                ->whereNotNull('invoices.payment_reminder_sent_at')
                ->whereHas('client') //here to mail sent shalin and also iih-crm invoice and below commited code only shalin
                /*->whereHas('client', function ($q) {
                    $q->whereHas(
                        'workspace',
                        function ($q) {
                            $q->where(
                                'slug',
                                'shalin-designs'
                            );
                        }
                    );
                })*/
                ->having('invoice_age_in_days', '>=', $interval)
                ->whereDate('invoices.invoice_date', '<', DB::raw('CURDATE()'))
                ->havingRaw('MOD(invoice_age_in_days, ?) = 0', [$interval])
                ->where('invoices.type', InvoiceType::INVOICE)
                ->where(function ($q) {
                    $q
                        ->having('payments_sum_amount', '<', DB::raw('invoices.grand_total'))
                        ->orHaving('payments_sum_amount', '')
                        ->orHaving('payments_sum_amount', NULL);
                })
                ->orderBy('invoices.invoice_date', 'ASC')
                ->cursor();

            $this->line("[" . date('Y-m-d H:i:s') . "] # of unpaid/partially paid invoices: " . $unpaidOrPartiallyPaidInvoices->count());
            $this->line("[" . date('Y-m-d H:i:s') . "] Sending out emails to it's customers");

            // $bcc = config('mail.accounts_mail_bcc', []);

            $successInvoices = $failureInvoices = [];

            foreach ($unpaidOrPartiallyPaidInvoices as $invoice) {

                $mail_sent = 0;
                if($invoice->client && $invoice->client->workspace && $invoice->client->workspace->slug === 'shalin-designs') {
                    $bcc = config('shalin-designs.accounts_mail.bcc', []);
                } else {
                    $bcc = config('mail.accounts_mail_bcc', []);
                }
                if (is_array($bcc) && $invoice->sales_person && $invoice->sales_person->email) array_push($bcc, $invoice->sales_person->email);
                $bcc = array_unique($bcc);

                try {
                    $client_email = explode(',',$invoice->client->email);
                    if(isset($client_email) && !empty($client_email)){
                        Mail::mailer(config('mail.accounts_mail_mailer', 'accounts_smtp'))
                            ->to($client_email)
                            ->bcc($bcc)
                            ->send(new PaymentReminderMail($invoice));
                    }
                    /*Mail::mailer(config('mail.accounts_mail_mailer', 'accounts_smtp'))
                        ->to($invoice->client)
                        ->bcc($bcc)
                        ->send(new PaymentReminderMail($invoice));*/

                    $mail_sent = 1;
                    $this->info("[" . date('Y-m-d H:i:s') . "] Mail sent to customer for invoice #" . $invoice->invoice_number);

                    array_push($successInvoices, $invoice->invoice_number);
                } catch (\Throwable $th) {

                    $mail_sent = 0;
                    array_push($failureInvoices, $invoice->invoice_number);

                    $this->error("[" . date('Y-m-d H:i:s') . "] Error: Failed to send mail for invoice #" . $invoice->invoice_number);
                    $this->error($th);

                    continue;
                }

                try {
                    if ($mail_sent) {
                        $invoice->update(['payment_reminder_sent_at' => now()]);
                        $workspace_id = $invoice->client->workspace->id ?? 1;
                        CronActivityLogHelper::log(
                            'invoice.payments.reminder.mail-sent',
                            'Payment reminder Mail sent to customer for Invoice #' . $invoice->invoice_number,
                            [],
                            request(),
                            User::role('Superadmin')->first(),
                            $invoice,
                            $workspace_id
                        );
                    };
                } catch (\Throwable $th) {
                }
            }

            $this->info("[" . date('Y-m-d H:i:s') . "] Successfully sent " . count($successInvoices) . " mails for following invoices:");
            $this->info("[" . date('Y-m-d H:i:s') . "] " . implode(', ', $successInvoices));
            if (count($failureInvoices)) {
                $this->error("[" . date('Y-m-d H:i:s') . "] Failed sending " . count($failureInvoices) . " mails for following invoices:");
                $this->error("[" . date('Y-m-d H:i:s') . "] " . implode(', ', $failureInvoices));
            }
            $this->info("[" . date('Y-m-d H:i:s') . "] Invoice payment reminder mails send complete.");
        } catch (\Throwable $th) {
            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred sending payment reminder to customers: " . $th->getMessage());
            $this->error($th);
            $this->sendCronErrorMail($th, $th->getMessage());
        } finally {
            ini_set('max_execution_time', $orig_max_exec_time); // revert back to original settings
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
                ->send(new CronErrorMail($title, $th, "Reminder invoice Payment CRON - Error occurred"));
        } catch (\Throwable $th) {
            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred while sending mail: " . $th->getMessage());
            $this->error($th);
        }
    }
}
