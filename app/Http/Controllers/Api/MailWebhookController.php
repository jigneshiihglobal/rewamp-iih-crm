<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactedLeadMail;
use App\Models\Lead;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\leadStatusWebhookMail;
use App\Models\MarketingMailStatus;
use Illuminate\Support\Facades\DB;

class MailWebhookController extends Controller
{
    public function mailStatus(Request $request)
    {
        Log::info('Received Brevo webhook:', $request->all());
        $payload = $request->all();

        if (isset($payload['event'])) {
            $event = $payload['event'];

            if($event == 'request' || $event == 'delivered' || $event == 'unique_opened' || $event == 'soft_bounce' || $event == 'opened' || $event == 'proxy_open') {
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
        $webhook_id = $payload['id'];
        $lead_status_event = null;
        $message_id = $payload['message-id'];

        // lead event flag
        if($event == 'request'){
            $lead_status_event = "Sent";
        }elseif($event == 'delivered'){
            $lead_status_event = "Delivered";
        }elseif($event == 'unique_opened' || $event == 'opened' || $event == 'proxy_open'){
            $lead_status_event = "Open";
        }elseif($event == 'soft_bounce'){
            $lead_status_event = "Soft Bounce";
        }

        $date = '2024-07-01 00:00:00';

        // Add and update lead status event flag
         $content_lead_mail = ContactedLeadMail::where('webhook_id',$webhook_id)->where('message_id',$message_id)->where('created_at','>',$date)->first();
          if($content_lead_mail){
              $id = $content_lead_mail->id;
              if($content_lead_mail->lead_status_event != 'Open' && $lead_status_event == 'Delivered'){
                $content_lead_mail->webhook_id  = $webhook_id;
                $content_lead_mail->message_id  = $message_id;
                $content_lead_mail->lead_status_event  = $lead_status_event;
                $content_lead_mail->save();
              }elseif($lead_status_event == 'Open'){
                $content_lead_mail->webhook_id  = $webhook_id;
                $content_lead_mail->message_id  = $message_id;
                $content_lead_mail->lead_status_event  = $lead_status_event;
                $content_lead_mail->save();
              }elseif($lead_status_event == 'Soft Bounce'){
                $content_lead_mail->webhook_id  = $webhook_id;
                $content_lead_mail->message_id  = $message_id;
                $content_lead_mail->lead_status_event  = $lead_status_event;
                $content_lead_mail->save();
              }

              // Marketing mails status add function.
              $this->marketingMailStatus($id,$webhook_id,$lead_status_event,$message_id);
              // Mail send Statis mail address with lead status
              // Mail::to('jignesh.iihglobal@gmail.com')->send(new leadStatusWebhookMail($payload,$lead_status_event));
          }else{
              $new_content_lead_mail = ContactedLeadMail::where(function($query) use ($email) {
                $query->where('email', 'LIKE', $email)
                      ->orWhere('email', 'LIKE', $email . ',%')
                      ->orWhere('email', 'LIKE', '%,' . $email)
                      ->orWhere('email', 'LIKE', '%,' . $email . ',%');
                })
                ->whereNull('webhook_id')->whereNull('message_id')->where('created_at','>',$date)->orderBy('created_at', 'desc')->first();

              if($new_content_lead_mail){
                $new_content_lead_mail->webhook_id  = $webhook_id;
                $new_content_lead_mail->message_id  = $message_id;
                $new_content_lead_mail->lead_status_event  = $lead_status_event;
                $new_content_lead_mail->save();

                $id = $new_content_lead_mail->id;

                // Marketing mails status add function.
                $this->marketingMailStatus($id,$webhook_id,$lead_status_event,$message_id);

                // Mail send Statis mail address with lead status
                // Mail::to('jignesh.iihglobal@gmail.com')->send(new leadStatusWebhookMail($payload,$lead_status_event));
              }
          }
    }

    public function marketingMailStatus($id,$webhook_id,$lead_status_event,$message_id)
    {
        $mail_open = MarketingMailStatus::where('contacted_lead_mail_id',$id)->where('lead_status_event','Open')->whereNotNull('webhook_id')->whereNotNull('message_id')->first();
        if(!$mail_open){
            // Marketing mails status add.
            $mail_status = new MarketingMailStatus;
            $mail_status->contacted_lead_mail_id = $id ?? '';
            $mail_status->webhook_id = $webhook_id ?? '';
            $mail_status->message_id = $message_id ?? '';
            $mail_status->lead_status_event = $lead_status_event ?? '';
            $mail_status->save();
        }
    }
}
