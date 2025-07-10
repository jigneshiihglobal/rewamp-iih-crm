<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\InvoiceErrorMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvoiceMailWebhookController extends Controller
{
    
    public function mailStatus(Request $request)
    {
        Log::info('Invoice Brevo webhook:', $request->all());
        $payload = $request->all();

        if (isset($payload['event'])) {
            $event = $payload['event'];

            if($event == 'error' || $event == 'soft_bounce' || $event == 'hard_bounce' || $event == 'invalid_email') {
                $this->emailStatus($payload);
            }
            return response()->json(['message' => 'Webhook processed'], 200);
        }
        return response()->json(['message' => 'Unhandled event type'], 400);
    }

     public function emailStatus($payload)
    {
        $email = $payload['email'];
        $event = $payload['event'];
        $invoice_status_event = null;
        $th =[];
        
        // invoice event flag
        if($event == 'error'){
            $invoice_status_event = "Error";
        }elseif($event == 'hard_bounce'){
            $invoice_status_event = "Hard Bounce";
        }elseif($event == 'invalid_email'){
            $invoice_status_event = "Invalid Email";
        }elseif($event == 'soft_bounce'){
            $invoice_status_event = "Soft Bounce";
        }


        try {
            $th = new \Exception("Simulated webhook error for {$email}");

            Mail::to(
                config(
                    'custom.cron_email_recipients.error_mail_address',
                    []
                )
            )
                ->send(new InvoiceErrorMail($invoice_status_event, $th, "Invoice mail sent - Error occurred"));
        } catch (\Throwable $th) {
            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred while sending mail: " . $th->getMessage());
            $this->error($th);
        }
    }
}
