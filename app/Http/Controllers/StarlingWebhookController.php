<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Currency;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\StripepaymentMail;

class StarlingWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        Log::info('starling Webhook Received:', $request->all());

        $payload = $request->all();

        if($payload['webhookType'] == 'STANDING_ORDER_PAYMENT'){
            $payment_source = $payload['webhookType'] ?? '';
            $paymentId = $payload['content']['paymentUid'] ?? null;
            $received_at = $payload['content']['paymentOrder']['nextDate'] ?? null;
            $amount = $payload['content']['paymentOrder']['amount']['minorUnits'] ?? 0;
            $currency = $payload['content']['paymentOrder']['amount']['currency'] ?? null;
            $status = $payload['content']['success'] ? 'success' : 'failed';
            $email = null;
            $name = null;
            $paymentMethod = null;
        }else{
            $payment_source = $payload['webhookType'] ?? 'not find the event';
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
        Log::info('Starling External payment received:', [
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
            Log::debug('Creating Starling Customer Payment Record', [
                'record' => $record,
            ]);
            $sales_mail = true;

            $this->sendPaymentReceiptMailStripe($record,$sales_mail,$payload);
            
        } catch (\Exception $e) {
            Log::error('Error processing Starling payment', [
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
            //$email = 'ashish.php.iih@gmail.com';
            $email = 'jignesh.iihglobal@gmail.com';

            Mail::mailer(config('mail.default'))
                ->to($email)
                ->send(new StripepaymentMail($invoice,$payload));

        } catch (\Throwable $th) {
            Log::info($th);
        }
    } 
}