<?php

namespace App\Console\Commands;

use App\Enums\InvoiceSubscriptionStatus;
use App\Enums\IsInvoiceNew;
use App\Helpers\ActivityLogHelper;
use App\Helpers\CronActivityLogHelper;
use App\Mail\SubscriptionCronErrorMail;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class RenewSubscriptionInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:subscriptions:renew';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renew subscription invoices';

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
    public function handle(InvoiceService $service)
    {
        $this->line("[" . date('Y-m-d H:i:s') . "] Running Command \"" . $this->signature . "\"");

        $today = now();
        // $today = Carbon::createFromDate(2024, 3, 31);
        $monthAgoStartOfDay = $today->copy()->subMonth()->startOfDay();
        $monthAgoEndOfDay = $monthAgoStartOfDay->copy()->endOfDay();
        $yearAgoStartOfDay = $today->copy()->subYear()->startOfDay();
        $yearAgoEndOfDay = $yearAgoStartOfDay->copy()->endOfDay();

        if (in_array($today->month, [2, 4, 6, 9, 11])) { // previous month days greater than current month
            if ($today->isLastOfMonth()) {
                $monthAgoEndOfDay->endOfMonth();
            }
            if ($today->day == 29 && $today->month == 2) { // leap year and yearly invoices
                $yearAgoStartOfDay->subDay()->startOfDay();
                $yearAgoEndOfDay = $yearAgoStartOfDay->copy()->endOfDay();
            }
            if ($today->year % 4 == 1 && $today->month == 2 && $today->day == 28) {
                $yearAgoEndOfDay->endOfMonth();
            }
        } else if (in_array($today->month, [3, 5, 7, 10, 12])) { // previous month days less than current month
            while ($today->month == $monthAgoEndOfDay->month) {
                $monthAgoStartOfDay->subDay()->startOfDay();
                $monthAgoEndOfDay = $monthAgoStartOfDay->copy()->endOfDay();
            }
        }

        try {
            $subscription_invoices = Invoice::query()
                ->select(['id', 'currency_id', 'note', 'discount', 'invoice_type', 'subscription_type', 'sub_total', 'vat_total', 'grand_total', 'client_id', 'user_id', 'payment_link', 'payment_link_add_at', 'client_name', 'original_subscription_invoice_id', 'invoice_date', 'bank_detail_id','company_detail_id'])
                ->where('invoice_type', config('custom.invoices_types.subscription', '1'))
                ->whereNotNull('subscription_type')
                ->where('subscription_status', '!=', InvoiceSubscriptionStatus::CANCELLED)
                ->where(function ($query) use ($monthAgoStartOfDay, $monthAgoEndOfDay, $yearAgoStartOfDay, $yearAgoEndOfDay) {
                    $query->where(function ($subQuery) use ($monthAgoStartOfDay, $monthAgoEndOfDay) {
                        $subQuery->where('subscription_type', config('custom.subscription_types.monthly'))
                            ->whereBetween('invoice_date', [$monthAgoStartOfDay, $monthAgoEndOfDay]);
                    })->orWhere(function ($subQuery) use ($yearAgoStartOfDay, $yearAgoEndOfDay) {
                        $subQuery->where('subscription_type', config('custom.subscription_types.yearly'))
                            ->whereBetween('invoice_date', [$yearAgoStartOfDay, $yearAgoEndOfDay]);
                    });
                })
                ->whereHas('client', function ($query) {
                    $query->whereNull('deleted_at');
                })
                ->with([
                    'invoice_items:id,invoice_id,description,price,tax_type,tax_rate,tax_amount,total_price,quantity',
                    'original_subscription_invoice:id,invoice_date',
                ])
                ->orderByDesc('invoices.invoice_date')
                ->orderByDesc('invoices.id')
                ->lazy();

            $i = 0;

            $this->line("[" . date('Y-m-d H:i:s') . "] Running cron for " . $subscription_invoices->count() . " subscription invoices.");

            $monthlyInvoiceCount = 0;
            $yearlyInvoiceCount = 0;
            $created_invoices = [];

            $subscription_invoices->each(function ($invoice, $key) use ($service, &$monthlyInvoiceCount, &$yearlyInvoiceCount, &$monthAgoEndOfDay, &$monthAgoStartOfDay, &$yearAgoEndOfDay, &$yearAgoStartOfDay, &$created_invoices, $today, &$i) {

                try {
                    if (in_array($today->month, [3, 5, 7, 10, 12])) { // previous month days less than current month
                        if ($today->day >= $invoice->invoice_date->day) {
                            if ($invoice->original_subscription_invoice) { // Invoice original subscription object in exist
                                if ($today->day < $invoice->original_subscription_invoice->invoice_date->day) { // Today day less than original subscription invoice day like : 25 < 30
                                    if (!$today->isLastOfMonth()) {  // Today day is not this month of last day : Like : (Current day = 25-3-2024 = true) , (Current day = 31-3-2024 = false)
                                        $this->line("[" . date('Y-m-d H:i:s') . "] Return Renewing invoice line Number: 117" );
                                        return true;
                                    }
                                } else if ($today->day > $invoice->original_subscription_invoice->invoice_date->day) { // Today day grater then original sub day like : 25 > 23 true
                                    if(in_array($today->day, [29, 30, 31])){ // Today day is (29, 30, 31) than true.
                                        $this->line("[" . date('Y-m-d H:i:s') . "] Return Renewing invoice line Number: 122" );
                                        return true;
                                    }
                                }
                            } else if ($today->day > $invoice->invoice_date->day) { // Today day greater than invoice date
                                $this->line("[" . date('Y-m-d H:i:s') . "] Return Renewing invoice line Number: 127" );
                                return true;
                            }
                        }
                    }
                    if ($invoice->subscription_type == config('custom.subscription_types.yearly')) {
                        if ($today->month == 2) {
                            if ($today->day == 28) {
                                if (!$today->isLastOfMonth() && $invoice->original_subscription_invoice && $invoice->original_subscription_invoice->invoice_date->day == 29) {
                                    $this->line("[" . date('Y-m-d H:i:s') . "] Return Renewing invoice line Number: 136" );
                                    return true;
                                }
                            }
                        }
                    }

                    /* This Condition is Working only Leap year */
                    /* Today second month : like : February, Today day is 29(leap year) like : (29-2-24), Invoice day is 28 */
                    if ($today->month == 2 && $today->day == 29 && $invoice->invoice_date->day == 28) {
                        /* Not Invoice original sub Yaa to Invoice original sub day is 28 */
                        if(!$invoice->original_subscription_invoice || $invoice->original_subscription_invoice->invoice_date->day == 28) {
                            $this->line("[" . date('Y-m-d H:i:s') . "] Return  invoice line Number: 148" );
                            return true;
                        }
                    }


                    if ($invoice->subscription_type == config('custom.subscription_types.monthly') && $invoice->invoice_date->between($monthAgoStartOfDay, $monthAgoEndOfDay)) {
                        /* Monthly subscription are auto create && create */
                        $monthlyInvoiceCount += 1;
                    } else if ($invoice->subscription_type == config('custom.subscription_types.yearly') && $invoice->invoice_date->between($yearAgoStartOfDay, $yearAgoEndOfDay)) {
                        /* Yearly subscription are auto create && create */
                        $yearlyInvoiceCount += 1;
                    } /*else {      //  subscription type is monthly and yearly so not required for this condition.
                        return true;
                    }*/

                    $invoice_number = $service->new_numbers($invoice->client_id);
                    $client = Client::where('id',$invoice->client_id)->first();
            
                    $invoice_items_data = [];
                    $this->line("[" . date('Y-m-d H:i:s') . "] Renewing invoice id: " . $invoice->id);

                    $new_invoice = $invoice->replicate();
                    $new_invoice->invoice_number = $invoice_number ?? $service->new_number();
                    $i++;
                    $new_invoice->invoice_date = $today->copy()->startOfDay();
                    $new_invoice->due_date = $today->copy()->addDays(5)->startOfDay();
                    $new_invoice->subscription_status = InvoiceSubscriptionStatus::AUTO_CREATED;
                    $new_invoice->original_subscription_invoice_id = $invoice->original_subscription_invoice_id ?? $invoice->id;
                    $new_invoice->parent_subscription_invoice_id = $invoice->id;
                    $new_invoice->is_new = IsInvoiceNew::NEW;
                    if(isset($invoice->payment_link) && !empty($invoice->payment_link)){
                        $new_invoice->payment_link =  $invoice->payment_link;
                        $new_invoice->payment_link_add_at = isset($invoice->payment_link_add_at) && !empty($invoice->payment_link_add_at) ? $invoice->payment_link_add_at : now();
                    }
                    $new_invoice->bank_detail_id = $invoice->bank_detail_id;
                    $new_invoice->company_detail_id = $invoice->company_detail_id;
                    $new_invoice->payment_reminder      = $client->payment_reminder ?? 0;
                    $new_invoice->save();

                    $invoice->invoice_items->each(function ($invoice_item, $key) use (&$invoice_items_data, &$new_invoice, $today) {
                        $new_invoice_item = $invoice_item->replicate();
                        $new_invoice_item->invoice_id = $new_invoice->id;
                        $new_invoice_item_arr = $new_invoice_item->toArray();
                        if (isset($new_invoice_item_arr['encrypted_id'])) unset($new_invoice_item_arr['encrypted_id']);
                        $new_invoice_item_arr['created_at'] = $new_invoice_item_arr['updated_at'] = $today->copy();
                        array_push($invoice_items_data, $new_invoice_item_arr);
                    });

                    InvoiceItem::insert($invoice_items_data);
                    array_push($created_invoices, $new_invoice->invoice_number);
                    $workspace_id = $invoice->client->workspace->id ?? 1;
                    CronActivityLogHelper::log(
                        'invoice.subscriptions.renewal_successful',
                        'Subscription invoice renewal by cron successful invoice number : '.$new_invoice->invoice_number,
                        [],
                        request(),
                        User::role('Superadmin')->first(),
                        $invoice,
                        $workspace_id
                    );

                } catch (\Throwable $th) {
                    $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred while renewing invoice #" . $invoice->invoice_number . ": " . $th->getMessage());
                    $this->error($th);
                    $this->sendSubscriptionCronErrorMail($th, $invoice);
                }
            });

            $this->info("[" . date('Y-m-d H:i:s') . "] " . $monthlyInvoiceCount . " monthly & " . $yearlyInvoiceCount . " yearly subscription invoices renewed successfully!");
            $this->info("[" . date('Y-m-d H:i:s') . "]  Created invoices: " . implode(', ', $created_invoices));
        } catch (\Throwable $th) {
            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred while renewing invoices: " . $th->getMessage());
            $this->error($th);
            $this->sendSubscriptionCronErrorMail($th);
        }

        return 0;
    }

    private function sendSubscriptionCronErrorMail(Throwable $th = null, $invoice = null)
    {
        try {
            Mail::to(
                config(
                    'custom.cron_email_recipients.error_mail_address',
                    []
                )
            )
                ->send(new SubscriptionCronErrorMail($th, $invoice ?? null));
        } catch (\Throwable $th) {
            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred while sending mail: " . $th->getMessage());
            $this->error($th);
        }
    }
}
