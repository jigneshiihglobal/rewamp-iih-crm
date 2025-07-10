<?php

namespace App\Http\Controllers;

use App\Mail\StripepaymentMail;
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

class WiseShalinDesignsWebhookController extends Controller
{
    
    public function handleWebhook(Request $request){
        try{

            Log::info('New Wise Webhook call',$request->all());
            $payload = $request->all();

            $record = [
                'payment_id' => null,
                'payload' => json_encode($payload),
                'currency' => null,
                'amount_received' => 0,
                'sent_at' => null,
                'webhook_link_to_payment_received' => 0,
            ];

            WisePaymentLog::create($record);            

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
}
