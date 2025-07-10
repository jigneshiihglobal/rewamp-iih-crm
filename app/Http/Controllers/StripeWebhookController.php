<?php

namespace App\Http\Controllers;

use App\Helpers\CronActivityLogHelper;
use App\Mail\StripepaymentMail;
use App\Models\Client;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\PaymentDetail;
use App\Models\PaymentSource;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;
use Illuminate\Support\Facades\Log;
use App\Mail\PaymentReceiptMail;
use Illuminate\Support\Facades\Mail;
use App\Helpers\ActivityLogHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use App\Contracts\CommunicationAPI;
use App\Models\MoreTreeHistory;

class StripeWebhookController extends Controller
{

    public $service;
    
    public function __construct(InvoiceService $invoiceService)
    {
        $this->service = $invoiceService;
    }
    
    public function handleWebhook(Request $request)
    {
        Stripe::setApiKey(config('stripe.secret_key'));
        $endpointSecret = config('stripe.webhook_key');

        // Log the event type for debugging
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            // Verify the event came from Stripe
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $endpointSecret
            );
            
            Log::info('Webhook type: ' . $event->type);

            // Handle the event based on type
            switch ($event->type) {        
                // It's a one-time payment
                case 'checkout.session.completed':
                    $this->safelyProcessEvent(function() use ($event) {
                        $session = $event->data->object;
                        if (empty($session->subscription)) {
                            $this->handleCheckoutSessionCompleted($session);   
                        }
                    }, 'checkout.session.completed', $event->id);
                    break;
                    
                // It's a subscription payment
                case 'invoice.payment_succeeded':
                    $this->safelyProcessEvent(function() use ($event) {
                        $invoice = $event->data->object;
                        if (!empty($invoice->subscription)) {
                            $this->handleInvoicePaymentSucceeded($invoice);
                        }
                    }, 'invoice.payment_succeeded', $event->id);
                    break;
                    
                default:
                    Log::info('Stripe received unknown event type: ' . $event->type);
            }

            return response()->json(['status' => 'success']);
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            Log::error('Stripe webhook signature verification failed: ' . $e->getMessage());
            return response()->json(['error' => 'Stripe webhook signature verification failed'], 400);
        } catch (\Exception $e) {
            // Other errors
            Log::error('Stripe Webhook error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Stripe webhook processing failed'], 500);
        }
    }

    /**
     * Safely process an event with error handling
     */
    protected function safelyProcessEvent(callable $callback, $eventType, $eventId)
    {
        try {
            $callback();
        } catch (\Exception $e) {
            Log::error("Error processing $eventType event: " . $e->getMessage(), [
                'event_id' => $eventId,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            // Don't re-throw - we want to return 200 to Stripe
        }
    }

    protected function handleInvoicePaymentSucceeded($invoice)
    {
        // Log::debug('Invoice payment subscription', [
        //     'invoice data' => $invoice
        // ]);
        Log::info('Invoice subscription payment');
        
        $paymentId = $invoice->payment_intent;
        $amount = $invoice->amount_paid;
        $currency = $invoice->currency;
        $status = $invoice->status;
        $customerEmail = $invoice->customer_email;
        $customerName = $invoice->customer_name;
    
        // Fetch the PaymentIntent to get payment method details
        $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentId);

        if ($paymentId) {
            Log::info('Subscription Invoice paymentId: ' . $paymentId);
            $this->createPaymentRecord(
                 $paymentId,
                 $amount ?? '0.00',
                 $currency ?? null,
                 $status ?? null,
                 $customerEmail,
                 $customerName,
                 $paymentIntent->payment_method ?? null,
             );
        }
    }



    public function handleCheckoutSessionCompleted($session)
    {
        Log::info('Invoice one-time payment');

        // Extract customer info from session if available
        $customerEmail = null;
        $customerName = null;
        $paymentIntentId = $session->payment_intent ?? null;
        
        // Safely access customer details
        if (isset($session->customer_details)) {
            $customerEmail = $session->customer_details->email ?? null;
            $customerName = $session->customer_details->name ?? null;
        }

        // Fetch payment intent if available
        if ($paymentIntentId) {
            try {
                $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);
                $this->createPaymentRecord(
                    $paymentIntent->id,
                    $paymentIntent->amount ?? '0.00',
                    $paymentIntent->currency ?? null,
                    $paymentIntent->status ?? null,
                    $customerEmail,
                    $customerName,
                    $paymentIntent->payment_method ?? null,
                );
            } catch (\Exception $e) {
                Log::error('Failed to retrieve payment intent: ' . $e->getMessage());
                $this->createPaymentRecord(
                    $session->payment_intent,
                    $session->amount_total ?? '0.00',
                    $session->currency ?? null,
                    'completed',
                    $customerEmail,
                    $customerName,
                    null,
                );
            }
        } else {
            $this->createPaymentRecord(
                $session->id,
                $session->amount_total ?? '0.00',
                $session->currency ?? null,
                'completed',
                $customerEmail,
                $customerName,
                null,
            );
        }
    }
    
    /**
     * Create a payment record with proper error handling
     */
    protected function createPaymentRecord($paymentId, $amount, $currency, $status, $email = null, $name = null, $paymentMethod = null)
    {
        try {
            $amount =  round($amount / 100, 2);
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
                        $this->receivedPaymentStore($invoice,$amount);
                    }
                } elseif ($invoices->count() > 1) {
                    $client = Client::whereRaw('FIND_IN_SET(?, email)', [$email])->first();
                    Log::debug('Client email check.', [
                        'client' => [$client],
                        'email' => [$email],
                    ]);
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
                    'payment_source' => 'STRIPE',
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
                Log::debug('Creating Stripe Customer Payment Record', [
                    'record' => $record,
                ]);
                PaymentDetail::create($record);       
            }else{
                Log::info('Invoice Not Found for Stripe Webhook');
            }
            
        } catch (\Exception $e) {
            Log::error('Error processing Stripe payment', [
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
            $payment_source = PaymentSource::where('title','Stripe - IIH Global')->where('workspace_id',1)->first();
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
            CronActivityLogHelper::log('invoices.payments.created', "A payment was received for invoice #{$invoiceNumber}. (Stripe Webhook)", [], request(), $user, $invoicePayment,$workspace_id);

            if ($newPaymentStatus) {
                CronActivityLogHelper::log(
                    'invoice.payment-status.updated',
                    'Invoice #' . $invoice->invoice_number . "'s payment status updated from {$oldStatusHl} to {$newStatusHl} (Stripe Webhook)",
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
            if ($invoice->client && $invoice->client->workspace && ($invoice->client->workspace->slug === 'iih-global')) {
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
            $payment_method = 'Stripe';
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