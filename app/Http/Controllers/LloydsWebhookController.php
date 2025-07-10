<?php

namespace App\Http\Controllers;

use App\Mail\StripepaymentMail;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\PaymentDetail;
use App\Models\PaymentSource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use PhpParser\Token;
use App\Models\Currency;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use App\Contracts\CommunicationAPI;
use App\Models\MoreTreeHistory;
use App\Helpers\ActivityLogHelper;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Mail;

class LloydsWebhookController extends Controller
{
    private $client_id;
    private $secret;
    private $env;

    public $service;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->client_id = config('services.plaid.client_id');
        $this->secret = config('services.plaid.secret');
        $this->env = config('services.plaid.env'); // for sandbox only
        $this->service = $invoiceService;
    }

    public function handleWebhook(Request $request)
    {
        // Log all payloads
        Log::info('TrueLayer Webhook Received:', $request->all());

        $type = $request->input('type');
        $payload = $request->all();

        if ($type === 'external_payment_received') {
            $payment_source = $type ?? 'external_payment_received';
            $paymentId = $payload['payment_id'];
            $received_at = $payload['received_at'];
            $amount = $payload['amount_in_minor'];
            $currency = $payload['currency'];
            $status = null;
            $email = null;
            $name = null;
            $paymentMethod = null;
        }else{
            $payment_source = $type ?? 'not find the event';
            $paymentId = null;
            $received_at = null;
            $amount = null;
            $currency = null;
            $status = null;
            $email = null;
            $name = null;
            $paymentMethod = null;
        }
        

         // Handle external payment
         Log::info('External payment received:', [
            'payment_id'     => $paymentId,
            'received_at'    => $received_at,
            'amount_in_minor'=> $amount,
            'currency'       => $currency,
        ]);

        $this->createPaymentRecord(
            $payment_source,
            $paymentId,
            $amount,
            $currency,
            $status,
            $email,
            $name,
            $paymentMethod,
            $payload,
        );

        return response()->json(['status' => 'success']);
    }

    // public function handleWebhook(Request $request){
    //     try{
    //         $data = $request->all();
    //         Log::info('Lloyds Webhook call',$data);
    //         // if ($data['webhook_type'] === 'TRANSACTIONS' && $data['webhook_code'] === 'INITIAL_UPDATE') {
    //         //     $itemId = $data['item_id'];
    //         //     $accessToken = 'access-sandbox-1ed2c50e-200b-40aa-a40e-46d238ff07e9';

    //         //     if (!$accessToken) {
    //         //         Log::error('Access token missing from session.');
    //         //         return response()->json(['error' => 'Access token not found'], 400);
    //         //     }
                
    //         //     Log::info('Lloyds Webhook client information',[
    //         //         'response' => $accessToken,
    //         //     ]);

    //         //     $response = Http::post('https://sandbox.plaid.com/transactions/get', [
    //         //         'client_id' => $this->client_id,
    //         //         'secret' => $this->secret,
    //         //         'access_token' => $accessToken,
    //         //         //'start_date' => now()->subDays(30)->toDateString(),
    //         //         'start_date' => now()->toDateString(),
    //         //         'end_date' => now()->toDateString(),
    //         //     ]);
    
    //         //    // Log the raw response
    //         //     Log::info('Lloyds Webhook full response', [
    //         //         'response' => $response->json(),
    //         //     ]);

    //         //     // Get the single most recent transaction
    //         //     $lastTransaction = $response->json();
    //         //     $institutionName = $lastTransaction['item']['institution_name'] ?? null;
    //         //     Log::info('Bank name : '.$institutionName);

    //         //     if($institutionName == 'Lloyds Bank - Personal'){
    //         //         if (!empty($lastTransaction['transactions'])) {
    //         //             $latestTransaction = collect($lastTransaction['transactions'] ?? [])
    //         //                 ->sortByDesc(function ($transaction) {
    //         //                     return $transaction['authorized_datetime']
    //         //                         ?? $transaction['datetime']
    //         //                         ?? $transaction['date'];
    //         //                 })
    //         //                 ->first();

    //         //             $payload  = json_encode($latestTransaction);

    //         //             $payment_source = 'Lloyds Bank';
    //         //             $paymentId = $latestTransaction['transaction_id'] ?? null;
    //         //             $amount   = $latestTransaction['amount'] ?? null;
    //         //             $currency = $latestTransaction['iso_currency_code'] ?? null;
    //         //             $status = 'succeeded';
    //         //             $email = null;
    //         //             $merchant = $latestTransaction['merchant_name'] ?? $latestTransaction['name'] ?? 'N/A';
    //         //             $paymentMethod = $latestTransaction['payment_meta']['payment_method'] ?? null;
    
    //         //             // Extracting details
    //         //             $this->createPaymentRecord(
    //         //                 $payment_source,
    //         //                 $paymentId,
    //         //                 $amount,
    //         //                 $currency,
    //         //                 $status,
    //         //                 $email,
    //         //                 $merchant,
    //         //                 $paymentMethod,
    //         //                 $payload,
    //         //             );

    //         //             Log::info('Lloyds Webhook last transaction', [
    //         //                 'last_transaction' => $latestTransaction,
    //         //             ]);
    //         //         } else {
    //         //             Log::info('Lloyds Webhook: No transactions found.');
    //         //         }
    //         //     }else{
    //         //         Log::info('Other Bank.');
    //         //     }
    
    //         //     return response()->json(['message' => 'Webhook processed successfully']);
    //         // }
    //         // local.INFO: Lloyds Webhook call {"environment":"sandbox","error":null,"item_id":"G1bPovwoP5HXARGLPNGGHo58vAnpnAf1nkjWM","new_transactions":116,"webhook_code":"INITIAL_UPDATE","webhook_type":"TRANSACTIONS"} 
    //     } catch (\Exception $e) {
    //         // Other errors
    //         Log::error('Lloyds Webhook error: ' . $e->getMessage(), [
    //             'file' => $e->getFile(),
    //             'line' => $e->getLine(),
    //             'trace' => $e->getTraceAsString()
    //         ]);
    //         return response()->json(['error' => 'Lloyds webhook processing failed'], 500);
    //     }
    // }

    public function createLinkToken()
    {
        
        $client = new \GuzzleHttp\Client();
        
        $response = $client->post("{$this->env}/link/token/create", [
            'json' => [
                'client_id' => $this->client_id,
                'secret' => $this->secret,
                'client_name' => 'IIH GLOBAL TEST',
                'user' => ['client_user_id' => 'user-18'],
                'products' => ['transactions'],
                'country_codes' => ['GB'],
                'language' => 'en',
                'webhook' => 'https://b6e6-122-170-107-160.ngrok-free.app/lloyds/webhook',
            ]
        ]);

        Log::info('Lloyds Webhook createLinkToken',[
            'response' => $response,
        ]);

        return response()->json(json_decode($response->getBody(), true));
    }

    public function getAccessToken(Request $request)
    {
        $request->validate([
            'public_token' => 'required|string',
        ]);

        $client = new \GuzzleHttp\Client();

        $response = $client->post("{$this->env}/item/public_token/exchange", [
            'json' => [
                'client_id' => $this->client_id,
                'secret' => $this->secret,
                'public_token' => $request->public_token
            ]
        ]);
        $data = json_decode($response->getBody(), true);


        Log::info('Lloyds Webhook data',[
            'data' => $data,
        ]);

        session(['plaid_access_token' => $data['access_token']]);

        return response()->json(json_decode($response->getBody(), true));
    }

    // Test Function 
    protected function createPaymentRecord($payment_source, $paymentId, $amount, $currency, $status, $email = null, $name = null, $paymentMethod = null, $payload = [])
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
        
            if(isset($invoices) && !empty($invoices)){
                if ($invoices->count() === 1) {
                    $invoice = $invoices->first();
                    if($invoice->client->workspace->slug == 'iih-global'){
                        Log::debug('first condition data');
                        $is_invoice_link_to_crm = 1;
                        $invoice_id = $invoice->id;
                        $invoice_number = $invoice->invoice_number;
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
                            }
                        }
                    }
                }
            }  
        
            $record = [
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
            ];
                    
            // Log what we're trying to create for debugging
            Log::debug('Creating Lloyds Customer Payment Record', [
                'record' => $record,
            ]);
            $sales_mail = true;

            $this->sendPaymentReceiptMailStripe($record,$sales_mail,$payload);
            
        } catch (\Exception $e) {
            Log::error('Error processing Lloyds payment', [
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

    public function sendPaymentReceiptMailStripe($invoice,$sales_mail = false,$payload){
        try {
            $email = 'jignesh.iihglobal@gmail.com';

            Mail::mailer(config('mail.default'))
                ->to($email)
                ->send(new StripepaymentMail($invoice,$payload));

        } catch (\Throwable $th) {
            Log::info($th);
        }
    }          

    // protected function createPaymentRecord($payment_source, $paymentId, $amount, $currency, $status, $email = null, $name = null, $paymentMethod = null)
    // {
    //     try {
    //         $is_invoice_link_to_crm = 0;
    //         $sales_mail = true;
    //         $invoice_id = null;
    //         $invoice_number = null;
        
    //         $currency_code = Currency::whereRaw('UPPER(code) = ?', [strtoupper($currency)])->first();
    //         $currency_code_id = $currency_code->id ?? null;
    //         Log::debug('invoices exist data', [
    //             'currency_code_id' => $currency_code_id,
    //         ]);
                    
        
    //         $payment_status = 'unpaid';
    //         $invoices = Invoice::where('type','0')
    //                     ->where('payment_status',$payment_status)
    //                     ->where('currency_id',$currency_code_id)
    //                     ->where('grand_total',$amount)
    //                     ->get();
        
    //         Log::debug('invoices exist data', [
    //             'invoices' => [$invoices],
    //         ]);
        
    //         if(isset($invoices) && !empty($invoices) && $invoices->count() > 0){
    //             if ($invoices->count() === 1) {
    //                 $invoice = $invoices->first();
    //                 if($invoice->client->workspace->slug == 'iih-global'){
    //                     Log::debug('first condition data');
    //                     $is_invoice_link_to_crm = 1;
    //                     $invoice_id = $invoice->id;
    //                     $invoice_number = $invoice->invoice_number;
    //                     $this->receivedPaymentStore($invoice,$amount);
    //                 }
    //             } elseif ($invoices->count() > 1) {
    //                 $client = Client::whereRaw('FIND_IN_SET(?, email)', [$email])->first();
    //                 if($client){
    //                     $invoice = Invoice::where('type', '0')
    //                          ->where('client_id', $client->id)
    //                          ->where('payment_status', $payment_status)
    //                          ->where('currency_id', $currency_code_id)
    //                          ->where('grand_total', $amount)
    //                          ->orderBy('created_at', 'asc')
    //                          ->first();
    //                      if ($invoice) {
    //                          if($invoice->client->workspace->slug == 'iih-global'){
    //                              Log::debug('Second condition data');
    //                              $is_invoice_link_to_crm = 1;
    //                              $invoice_id = $invoice->id;
    //                              $invoice_number = $invoice->invoice_number;
    //                              $this->receivedPaymentStore($invoice,$amount);
    //                          }
    //                      }
    //                 }
    //             }

    //             $record = [
    //                 'payment_source' => $payment_source,
    //                 'invoice_id' => $invoice_id,
    //                 'invoice_number' => $invoice_number,
    //                 'payment_id' => $paymentId,
    //                 'amount_received' => $amount,
    //                 'currency' => strtoupper($currency),
    //                 'status' => $status,
    //                 'customer_email' => $email,
    //                 'customer_name' => $name,
    //                 'payment_method' => $paymentMethod,
    //                 'is_invoice_link_to_crm' => $is_invoice_link_to_crm,
    //             ];
                        
    //             // Log what we're trying to create for debugging
    //             Log::debug('Creating Lloyds Bank Customer Payment Record', [
    //                 'record' => $record,
    //             ]);
    
    //             PaymentDetail::create($record);

    //         }else{
    //             Log::info('Invoice Not Found for Lloyds Bank Webhook');
    //         }
            
    //     } catch (\Exception $e) {
    //         Log::error('Error processing Lloyds Bank payment', [
    //             'message' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //             'context' => [
    //                 'email' => $email ?? null,
    //                 'amount' => $amount ?? null,
    //                 'currency' => $currency ?? null,
    //                 'paymentId' => $paymentId ?? null,
    //             ],
    //         ]);        
    //     }
    // }

    public function receivedPaymentStore($invoice,$amount){

        $invoice->loadSum('payments', 'amount');
        $invoice->load('credit_note:id,grand_total,parent_invoice_id');

        $user = User::role('Superadmin')->first();
        $payment_source = PaymentSource::where('title','Lloyds Bank')->where('workspace_id',1)->first();
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

            ActivityLogHelper::log('invoices.payments.created', "A payment was received for invoice #{$invoiceNumber}. (Lloyds Bank Webhook)", [], request(), $user, $invoicePayment);

            if ($newPaymentStatus) {
                ActivityLogHelper::log(
                    'invoice.payment-status.updated',
                    'Invoice #' . $invoice->invoice_number . "'s payment status updated from {$oldStatusHl} to {$newStatusHl} (Lloyds Bank Webhook)",
                    [
                        'previousStatus' => $oldStatus,
                        'newStatus' => $newPaymentStatus,
                    ],
                    request(),
                    $user,
                    $invoice
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
            $payment_method = 'Lloyds Bank';
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
