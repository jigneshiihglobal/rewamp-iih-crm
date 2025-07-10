<?php

namespace App\Http\Controllers;

use App\Helpers\CronActivityLogHelper;
use App\Models\Client;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\PaymentDetail;
use App\Models\PaymentSource;
use App\Models\User;
use App\Models\WisePaymentLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use App\Contracts\CommunicationAPI;
use App\Models\MoreTreeHistory;
use App\Helpers\ActivityLogHelper;
use Illuminate\Support\Facades\Mail;

class WiseWebhookController extends Controller
{
    public $service;
    
    public function __construct(InvoiceService $invoiceService)
    {
        $this->service = $invoiceService;
    }
    

    public function handleWebhook(Request $request){
        try{

            Log::info('Wise Webhook call',$request->all());
            $payload = $request->all();

            if (isset($payload['event_type']) && $payload['event_type'] === 'balances#credit') {
                Log::info('Wise Balances Credit Webhook call',$payload);

                $payment_source = 'WISE';
                $amount = isset($payload['data']['amount']) ? $payload['data']['amount'] : '';
                $currency = isset($payload['data']['currency']) ? $payload['data']['currency'] : '';
                $paymentId = isset($payload['data']['resource']['id']) ? $payload['data']['resource']['id'] : '';
                $status = 'succeeded';
                $paymentMethod = isset($payload['event_type']) ? $payload['event_type'] : '';
                $sent_at = isset($payload['sent_at']) ? strtotime($payload['sent_at']) : null;
                $customerEmail = null;
                $customerName = null;

                $record = [
                    'payment_id' => $paymentId,
                    'payload' => json_encode($payload),
                    'currency' => $currency,
                    'amount_received' => $amount,
                    'sent_at' => $sent_at,
                    'webhook_link_to_payment_received' => 0,
                ];

                $is_exist_WisePaymentLog = WisePaymentLog::where('sent_at',$sent_at)
                ->where('amount_received', $amount)
                ->where('currency', $currency)
                ->exists();            

                if (!$is_exist_WisePaymentLog) {
                    $WisePaymentLog = WisePaymentLog::create($record);
                    $WisePaymentLog_id = $WisePaymentLog->id ?? null;
    
                    $this->createPaymentRecord(
                        $payment_source,
                        $paymentId,
                        $amount ?? '0.00',
                        $currency ?? null,
                        $status ?? null,
                        $customerEmail,
                        $customerName,
                        $paymentMethod ?? null,
                        $WisePaymentLog_id ?? null,
                    );
                    return response()->json(['success' => true]);
                }
            }else{
                $record = [
                    'payment_id' => null,
                    'payload' => json_encode($payload),
                    'amount_received' => 0,
                    'currency' => null,    
                    'sent_at' => isset($payload['sent_at']) ? strtotime($payload['sent_at']) : null,
                    'webhook_link_to_payment_received' => 0,
                ];

                WisePaymentLog::create($record);
            }
    
            

        } catch (\Exception $e) {
            // Other errors
            Log::error('Wise Webhook error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Wise webhook processing failed'], 500);
        }
    }

    protected function createPaymentRecord($payment_source, $paymentId, $amount, $currency, $status, $email = null, $name = null, $paymentMethod = null, $WisePaymentLog_id)
    {
        try {
            $is_invoice_link_to_crm = 0;
            $sales_mail = true;
            $invoice_id = null;
            $invoice_number = null;
        
            $currency_code = Currency::whereRaw('UPPER(code) = ?', [strtoupper($currency)])->first();
            $currency_code_id = $currency_code->id ?? null;
            Log::debug('invoices exist data', [
                'currency_code_id' => $currency_code_id,
            ]);
                    
        
            $payment_status = 'unpaid';
            $invoices = Invoice::where('type','0')
                        ->where('payment_status',$payment_status)
                        ->where('currency_id',$currency_code_id)
                        ->where('grand_total',$amount)
                        ->get();
        
            Log::debug('invoices exist data', [
                'invoices' => [$invoices],
            ]);
        
            if(isset($invoices) && !empty($invoices) && $invoices->count() > 0){
                if ($invoices->count() === 1) {
                    $invoice = $invoices->first();
                    if($invoice->client->workspace->slug == 'iih-global'){
                        Log::debug('first condition data');
                        $is_invoice_link_to_crm = 1;
                        $invoice_id = $invoice->id;
                        $invoice_number = $invoice->invoice_number;
                        $email = $invoice->client->email ?? null;
                        $name =  $invoice->client->name ?? null;
                        $this->receivedPaymentStore($invoice,$amount);
                    }
                } elseif ($invoices->count() > 1) {
                    $client = Client::whereRaw('FIND_IN_SET(?, email)', [$email])->first();
                    if($client){
                        $invoice = Invoice::where('type', '0')
                             ->where('client_id', $client->id)
                             ->where('payment_status', $payment_status)
                             ->where('currency_id', $currency_code_id)
                             ->where('grand_total', $amount)
                             ->orderBy('created_at', 'asc')
                             ->first();
                         if ($invoice) {
                             if($invoice->client->workspace->slug == 'iih-global'){
                                 Log::debug('Second condition data');
                                 $is_invoice_link_to_crm = 1;
                                 $invoice_id = $invoice->id;
                                 $invoice_number = $invoice->invoice_number;
                                 $this->receivedPaymentStore($invoice,$amount);
                             }
                         }
                    }
                }

                $record = [
                    'wise_payment_log_id' => $WisePaymentLog_id,
                    'payment_source' => $payment_source,
                    'invoice_id' => $invoice_id,
                    'invoice_number' => $invoice_number,
                    'payment_id' => $paymentId,
                    'amount_received' => $amount,
                    'currency' => strtoupper($currency),
                    'status' => $status,
                    'customer_email' => $email,
                    'customer_name' => $name,
                    'payment_method' => $paymentMethod,
                    'is_invoice_link_to_crm' => $is_invoice_link_to_crm,
                    'workspace_id' => 1,
                ];
                        
                // Log what we're trying to create for debugging
                Log::debug('Creating WISE Customer Payment Record', [
                    'record' => $record,
                ]);
                PaymentDetail::create($record);

                WisePaymentLog::where('id',$WisePaymentLog_id)->update([
                    'webhook_link_to_payment_received' => 1,
                ]);
            }else{
                Log::info('Invoice Not Found for Wise Webhook');
            }
            
        } catch (\Exception $e) {
            Log::error('Error processing WISE payment', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'context' => [
                    'email' => $email ?? null,
                    'amount' => $amount ?? null,
                    'currency' => $currency ?? null,
                    'paymentId' => $paymentId ?? null,
                ],
            ]);        
        }
    }

    public function receivedPaymentStore($invoice,$amount){

        $invoice->loadSum('payments', 'amount');
        $invoice->load('credit_note:id,grand_total,parent_invoice_id');

        $user = User::role('Superadmin')->first();
        $payment_source = PaymentSource::where('title','Wise')->where('workspace_id',1)->first();
        DB::beginTransaction();
        try{

            $paid_at = Carbon::createFromFormat('d/m/Y', now()->format('d/m/Y'));
        
            $invoicePayment = $invoice->payments()->create([
                'paid_at' => $paid_at,
                'amount' => $amount,
                'reference' => null,
                'payment_source_id' => $payment_source->id,
            ]);

            $invoice->loadSum('payments', 'amount');
            $invoiceNumber = $invoice->invoice_number ?? '';
            $oldStatus = $invoice->payment_status;
            $oldStatusHl = Str::headline($oldStatus);

            $newPaymentStatus = 'paid';
            $newStatusHl = Str::headline($newPaymentStatus);

            if ($newPaymentStatus && $newPaymentStatus != $invoice->payment_status) {
                $invoice->update([
                    'payment_status' => $newPaymentStatus,
                    'fully_paid_at' => $oldStatus != 'paid' && $newPaymentStatus === 'paid' ? now() : $invoice->fully_paid_at,
                ]);
            }
            $workspace_id = $invoice->client->workspace->id ?? 1;
            CronActivityLogHelper::log('invoices.payments.created', "A payment was received for invoice #{$invoiceNumber}. (WISE Webhook)", [], request(), $user, $invoicePayment,$workspace_id);

            if ($newPaymentStatus) {
                CronActivityLogHelper::log(
                    'invoice.payment-status.updated',
                    'Invoice #' . $invoice->invoice_number . "'s payment status updated from {$oldStatusHl} to {$newStatusHl} (WISE Webhook)",
                    [
                        'previousStatus' => $oldStatus,
                        'newStatus' => $newPaymentStatus,
                    ],
                    request(),
                    $user,
                    $invoice,
                    $workspace_id
                );
            }

            DB::commit();

            $this->service->makeAndStorePDF($invoice);
            $this->service->makeAndStorePDF($invoice, 'payment_receipt');

            // DB::commit();

            if (
                $invoice->client &&
                $invoice->client->workspace &&
                ($invoice->client->workspace->slug === 'iih-global')
            ) {
                try {
                    $workspace = $invoice->client->workspace->slug;
                    $msgAmount = number_format((float) $invoicePayment->amount, 2);
                    $msgCurrency = $invoice->currency
                        ? " (" . $invoice->currency->code . ")"
                        : "";
                    $msgCompany = $invoice->client->name ?? '';
                    $msgSource = $invoicePayment->payment_source->title ?? '';
                    $commMsg = "*{$msgAmount}{$msgCurrency}* received from *{$msgCompany}*  in {$msgSource} (Webhook)";
                    $commAPI = App::make(CommunicationAPI::class);
                    $commAPI->sendWebhookMessage($commMsg,$workspace);
                } catch (\Throwable $th) {
                    Log::info($th);
                }
            }
            $payment_method = 'Wise';
            if ($newPaymentStatus === 'paid') {
                $sales_mail = true;
                $this->service->sendPaymentReceiptMail($invoice,$sales_mail,$payment_method);

                $client = $invoice->client;

                if ($client && $client->plant_a_tree) {
                    DB::transaction(function () use ($client) {
                        MoreTreeHistory::create(["client_id" => $client->id]);
                        $client->update(['plant_a_tree' => false, 'is_tree_planted' => true]);
                    });
                }
            }

        } catch (\Throwable $th) {
            DB::rollback();
            Log::info($th);
        }
    }
}
