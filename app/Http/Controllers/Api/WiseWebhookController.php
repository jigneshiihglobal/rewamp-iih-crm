<?php

namespace App\Http\Controllers\Api;

use App\Contracts\CommunicationAPI;
use App\Helpers\ActivityLogHelper;
use App\Helpers\DateHelper;
use App\Http\Controllers\Controller;
use App\Mail\InvoicePaymentMail;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\MoreTreeHistory;
use App\Models\PaymentSource;
use App\Models\WiseNotification;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WiseWebhookController extends Controller
{

    /**
     * This endpoint receives incoming call from wise payments
     * whenever a balance update event occurs and sends a
     * slack message.
     *
     * @author Krunal Shrimali
     */
    public $service;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->service = $invoiceService;
    }
    public function __invoke(Request $request)
    {
        try {

            $response_body = $request->all();
            $this->_storeInDb($response_body);
            $this->_sendSlackMessage($response_body);

            return response()
                ->json([
                    'status' => true
                ], 200);
        } catch (\Throwable $th) {
            Log::info($th);
            return response()
                ->json([
                    'status' => false,
                    'error' => $th
                ], 500);
        }
    }

    /**
     * Store the wise notification in database for
     * future reference
     *
     * @author Krunal Shrimali
     * @param array $data The payload received from Wise to be
     * inserted in `wise_notifications` table
     *
     * @return void
     */
    private function _storeInDb(array $data): void
    {
        WiseNotification::create([
            'data' => isset($data['data'])
                ? json_encode($data['data'])
                : null,
            'sent_at' => isset($data['sent_at'])
                ? Carbon::parse($data['sent_at'])->toDateTime()
                : null,
            'subscription_id' => isset($data['subscription_id'])
                ? $data['subscription_id']
                : null,
            'event_type' => isset($data['event_type'])
                ? $data['event_type']
                : null,
            'schema_version' => isset($data['schema_version'])
                ? $data['schema_version']
                : null,
        ]);
    }

    /**
     * Notify via slack message when wise event received
     *
     * @author Krunal Shrimali
     * @param array $data The payload for creating and sending
     * slack message
     * @return string|bool The response of the curl call to
     * slack API or false if the curl call failed
     */
    private function _sendSlackMessage(array $data)
    {
        // Send slack message
        $ch = curl_init(config('services.slack.url'));
        $data = $this->_buildSlackMessage($data);
        if (!$data) return;

        curl_setopt($ch, CURLOPT_POST, 1); // Post request
        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            json_encode($data)
        ); // request body
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array('Content-Type: application/json')
        ); // request header for json content
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }

    /**
     * Build the slack message to send when wise
     * notification received
     *
     * @author Krunal Shrimali
     * @param array $response_body The payload received from the Wise
     * web-hook call
     * @return mixed The payload/message for Slack API
     */
    private function _buildSlackMessage(array $response_body)
    {
        $event_type = $response_body['event_type'] ?? '';
        $allowedEvents = [
            'transfers#state-change',
            'balances#credit'
        ];

        if (!in_array($event_type, $allowedEvents)) {
            return null;
        }

        $text = '';

        $amount = isset($response_body['data']['amount'])
            ? $response_body['data']['amount']
            : '';

        $currency = isset($response_body['data']['currency'])
            ? $response_body['data']['currency']
            : '';

        $occurred_at = isset($response_body['data']['occurred_at'])
            ? $response_body['data']['occurred_at']
            : '';

        $transaction_type = isset($response_body['data']['transaction_type'])
            ? $response_body['data']['transaction_type']
            : '';

        $current_state = isset($response_body['data']['current_state'])
            ? $response_body['data']['current_state']
            : '';

        $allowedStates = ['outgoing_payment_sent'];
        if ($event_type == 'transfers#state-change' && !in_array($current_state, $allowedStates)) {
            return null;
        }

        $previousStatus = isset($response_body['data']['previousStatus'])
            ? $response_body['data']['previousStatus']
            : '';

        $company_or_customer_name = 'company or customer name';

        $currentStatus = isset($response_body['data']['currentStatus'])
            ? $response_body['data']['currentStatus']
            : '';

        $resource = isset($response_body['data']['resource'])
            ? $response_body['data']['resource']
            : '';

        $card_token = isset($resource['card_token'])
            ? $resource['card_token']
            : '';

        $transfer_statuses = config('wise.transfer_statuses', []);

        switch ($response_body['event_type']) {

            case 'transfers#state-change':

                $previous_state = isset($response_body['data']['previous_state'])
                    ? $response_body['data']['previous_state']
                    : '';

                $previous_state = isset($transfer_statuses[$previous_state])
                    ? $transfer_statuses[$previous_state]
                    : '';

                $current_state = isset($transfer_statuses[$current_state])
                    ? $transfer_statuses[$current_state]
                    : '';

                $sender_name = 'company/customer name';
                $recipient_name = 'company/customer name';

                try {
                    $transferResource = $this->_getWiseTransferResouce($response_body['data']['resource']['id']);
                    if (!$transferResource) {
                        throw new Exception('Unable to get company/customer name');
                    }

                    if (isset($transferResource['targetAccount'])) {
                        $recipientAccountResource = $this->_getWiseRecipientAccountResource($transferResource['targetAccount']);

                        if (!$recipientAccountResource) {
                            throw new Exception('Unable to get sender name');
                        }

                        if (isset($recipientAccountResource['accountHolderName'])) {
                            $sender_name = $recipientAccountResource['accountHolderName'];
                        }
                    }
                } catch (\Throwable $th) {
                    Log::info($th);
                }

                try {
                    $recipientAccountResource = $this->_getWiseRecipientAccountResource($response_body['data']['resource']['account_id']);

                    if (!$recipientAccountResource) {
                        throw new Exception('Unable to get recipient name');
                    }

                    if (isset($recipientAccountResource['accountHolderName'])) {
                        $recipient_name = $recipientAccountResource['accountHolderName'];
                    }
                } catch (\Throwable $th) {
                    Log::info($th);
                }

                $text = "Your fund transfer's status is updated:\n\n" .
                    "from *{$previous_state}*\n\n" .
                    "to *{$current_state}*\n\n" .
                    "Sender Company/Customer: *{$sender_name}*\n\n" .
                    "Recipient Company/Customer: *{$recipient_name}*\n\n";

                break;

            case 'transfers#active-cases':

                $active_cases = isset($response_body['data']['active_cases'])
                    ? $response_body['data']['active_cases']
                    : [];

                $active_cases_str = implode(
                    ", ",
                    array_map(
                        function ($string) {
                            return Str::headline($string);
                        },
                        $active_cases
                    )
                );

                $text = "Potential problems in your transfer:\n\n" .
                    "Active cases: {$active_cases_str}";

                break;

            case 'transfers#payout-failure':

                $failure_reason_code = isset($response_body['data']['failure_reason_code'])
                    ? $response_body['data']['failure_reason_code']
                    : '';

                $failure_description = isset($response_body['data']['failure_description'])
                    ? $response_body['data']['failure_description']
                    : '';

                $text = "Payout failed:\n\n" .
                    "Failure Reason Code: *{$failure_reason_code}*\n\n" .
                    "Failure Description: *{$failure_description}*\n\n";

                break;

            case 'balances#credit':

                $post_transaction_balance_amount = isset($response_body['data']['post_transaction_balance_amount'])
                    ? $response_body['data']['post_transaction_balance_amount']
                    : '';

                $text = "You received *{$amount} {$currency}* from *{$company_or_customer_name}*\n\n" .
                    "Updated Balance: *{$post_transaction_balance_amount} {$currency}*\n\n";

                $this->wisePaymentSend($amount,$currency,$occurred_at);

                break;

            case 'balances#update':

                $transfer_reference = isset($response_body['data']['transfer_reference'])
                    ? $response_body['data']['transfer_reference']
                    : '';

                $txn_msg = $transaction_type === 'credit'
                    ? 'credited in'
                    : 'debited from';

                $txn_msg_upper_first = ucfirst($txn_msg);

                $text = "*{$amount} {$currency}* {$txn_msg_upper_first} your balance\n\n" .
                    "Company/Customer: *{$company_or_customer_name}*\n\n" .
                    "Transfer Reference: *{$transfer_reference}*\n\n";

                break;

                // case 'profiles#verification-state-change':

                //     $current_state = Str::headline($current_state);

                //     $text = "A connected profile's verification status changed:\n\n" .
                //         "Current status: `{$current_state}`\n" .
                //         "Occurred at: {$occurred_at_formatted}";

                //     break;

                // case 'batch-payment-initiations#state-change':

                //     $current_state = Str::headline($current_state);

                //     $text = "Batch payment status changed:\n\n" .
                //         "Previous status: `{$previousStatus}`\n" .
                //         "Current status: `{$currentStatus}`\n" .
                //         "Occurred at: {$ocurredAtFormatted}";

                //     break;

                // // below message is ignored because it will be duplicated
                // // since it will contain same details as the events
                // // `balances#credit` or `transfers#state-change`
                // case 'swift-in#credit':

                //     $text = "Batch payment status changed:\n\n" .
                //     "Current status: `{$currentStatus}`\n" .
                //     "Previous status: `{$previousStatus}`\n" .
                //     "Occurred at: {$ocurredAtFormatted}";

                //     break;

                // case 'cards#transaction-state-change':
                //     $fee_types = config('wise.fee_types', []);

                //     $transaction_states = config('wise.transaction_states', []);

                //     $transaction_id = isset($responseBody['data']['transaction_id'])
                //         ? $responseBody['data']['transaction_id']
                //         : '';

                //     $decline_reason = isset($responseBody['data']['decline_reason'])
                //         ? $responseBody['data']['decline_reason']
                //         : '';

                //     $transaction_state = isset($responseBody['data']['transaction_state'])
                //         ? $responseBody['data']['transaction_state']
                //         : '';

                //     $authorisation_method = isset($responseBody['data']['authorisation_method'])
                //         ? $responseBody['data']['authorisation_method']
                //         : '';

                //     $transaction_amount = isset($responseBody['data']['transaction_amount'])
                //         ? $responseBody['data']['transaction_amount']
                //         : [];

                //     $transaction_amount_with_fees = isset($responseBody['data']['transaction_amount_with_fees'])
                //         ? $responseBody['data']['transaction_amount_with_fees']
                //         : [];

                //     $fees = isset($responseBody['data']['fees'])
                //         ? $responseBody['data']['fees']
                //         : [];

                //     $text = "Card transaction state changed:\n\n" .
                //         "Card Identifier: `{$card_token}`\n" .
                //         "Transaction ID: `{$transaction_id}`\n" .
                //         "Transaction Type: `{$transaction_type}`\n" .
                //         "Transaction State: `{$transaction_state}, ({$transaction_states[$transaction_state]})`\n" .
                //         "Transaction Amount: `{$transaction_amount['value']} {$transaction_amount['currency']}`\n" .
                //         "Transaction Amount With Fees: `{$transaction_amount_with_fees['value']} {$transaction_amount_with_fees['currency']}`\n" .
                //         "Transaction : `{$transaction_amount['currency']}`\n" .
                //         $decline_reason ? "Decline Reason: `{$decline_reason}`\n" : "" .
                //         (count($fees)
                //             ? "Fees:\n" . implode(
                //                 "\n",
                //                 array_map(function ($fee) use ($fee_types) {
                //                     return "- Amount: `{$fee['amount']} {$fee['currency']}`\n" .
                //                         "- Type: `{$fee['fee_type']}, ({$fee_types[$fee['fee_type']]})`\n";
                //                 }, $fees)
                //             ) : '') .
                //         "Authorisation Method: `{$authorisation_method}`\n" .
                //         "Occurred at: {$occurred_at_formatted}";

                //     break;

                // case 'profiles#cdd-check-state-change':

                //     $review_outcome = isset($responseBody['data']['review_outcome'])
                //         ? $responseBody['data']['review_outcome']
                //         : '';

                //     $source_of_income = isset($responseBody['data']['source_of_income'])
                //         ? $responseBody['data']['source_of_income']
                //         : '';

                //     $source_of_funding = isset($responseBody['data']['source_of_funding'])
                //         ? $responseBody['data']['source_of_funding']
                //         : '';

                //     $required_evidences = isset($responseBody['data']['required_evidences'])
                //         ? $responseBody['data']['required_evidences']
                //         : [];

                //     $review_outcomes = config('wise.review_outcomes', []);

                //     $verification_states = config('wise.verification_states', []);

                //     $text = "Additional verification of customer updated:\n\n" .
                //         "Current State: `{$current_state}, ({$verification_states[$current_state]})`\n" .
                //         "Review Outcome: `{$review_outcome}, ({$review_outcomes[$review_outcome]})`\n" .
                //         (count($required_evidences)
                //             ? "Required Evidences:\n" . implode(
                //                 "\n",
                //                 array_map(function ($required_evidence) {
                //                     return "- {$required_evidence['amount']}";
                //                 }, $required_evidences)
                //             ) : '') .
                //         "Source of Income: `{$source_of_income}`\n" .
                //         "Source of Funding: `{$source_of_funding}`\n" .
                //         "Occurred at: {$occurred_at_formatted}";

                //     break;

                // case 'cards#card-status-change':

                //     $card_status = isset($responseBody['data']['card_status'])
                //         ? $responseBody['data']['card_status']
                //         : '';

                //     $card_statuses = config('wise.card_statuses', []);

                //     $text = "Card status changed:\n\n" .
                //         "Card Identifier: `{$card_token}`\n" .
                //         "Card Status: `{$card_status}, ({$card_statuses[$card_status]})`\n";

                //     break;

            default:

                return null;

                break;
        }

        return [
            'text' => $text
        ];
    }

    /**
     * Get the transfer resource from the Wise API
     *
     * @author Krunal Shrimali
     * @param string $transfer_id The id of the Transfer
     * resource on Wise received in the web-hook call.
     */
    public function _getWiseTransferResouce(string $transfer_id)
    {
        try {
            $endpoint = str_replace(
                '{{transferId}}',
                $transfer_id,
                config('wise.api_endpoints.GET_TRANSFER_BY_ID')
            );
            $response = Http::withToken(config('wise.api_token', ''))
                ->get($endpoint);

            if ($response->failed()) {
                return null;
            }

            return $response->json();
        } catch (\Throwable $th) {
            Log::info($th);
            return null;
        }
    }

    /**
     * Get the recipient account resource from the Wise API
     *
     * @author Krunal Shrimali
     * @param string $account_id The id of the recipient
     * account resource on Wise
     */
    public function _getWiseRecipientAccountResource(string $account_id)
    {
        try {
            $endpoint = str_replace(
                '{{accountId}}',
                $account_id,
                config('wise.api_endpoints.GET_RECIPIENT_ACCOUNT_BY_ID')
            );
            $response = Http::withToken(config('wise.api_token', ''))
                ->get($endpoint);

            if ($response->failed()) {
                return null;
            }

            return $response->json();
        } catch (\Throwable $th) {
            Log::info($th);
            return null;
        }
    }

    /* Wise payment receive */
    public function wisePaymentSend($amount, $currency, $occurred_at)
    /*public function wisePaymentSend()*/
    {
        try {
/*            $currency = 'USD';
            $amount = 520.45;*/
            $currency_id = Currency::where(['code' => $currency])->first();
            $invoices = Invoice::where(['grand_total' => $amount, 'currency_id' => $currency_id->id])->where('type','0')->where('payment_status','unpaid')->where('is_new','0')->whereNull('deleted_at')->get();
            $payment_source_id = PaymentSource::where(['title' => 'Wise','workspace_id' => 1])->first();
            if(count($invoices) == 1){
                $invoice = $invoices[0];

                if ($invoice->client->workspace->id == 1 && !empty($invoice)) {
                    /* Invoice payment receive using wise payment source */
                    /*$paid_at = Carbon::createFromFormat('Y-m-d\TH:i:s\Z', $occurred_at)->format('d/m/Y');

                    $invoicePayment = $invoice->payments()->create([
                        'paid_at' => $paid_at,
                        'amount' => $amount,
                        'reference' => '',
                        'payment_source_id' => $payment_source_id->id,
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

                    ActivityLogHelper::log('invoices.payments.created', "A payment was received for invoice #{$invoiceNumber}.", [], request(), Auth::user(), $invoicePayment);

                    ActivityLogHelper::log(
                        'invoice.payment-status.updated',
                        'Invoice #' . $invoice->invoice_number . "'s payment status updated from {$oldStatusHl} to {$newStatusHl}",
                        [
                            'previousStatus' => $oldStatus,
                            'newStatus' => $newPaymentStatus,
                        ],
                        request(),
                        Auth::user(),
                        $invoice
                    );

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
                            $msgAmount = number_format((float)$invoicePayment->amount, 2);
                            $msgCurrency = $invoice->currency
                                ? " (" . $invoice->currency->code . ")"
                                : "";
                            $msgCompany = $invoice->client->name ?? '';
                            $msgSource = $invoicePayment->payment_source->title ?? '';
                            $commMsg = "*{$msgAmount}{$msgCurrency}* received from *{$msgCompany}*  in {$msgSource}";
                            $commAPI = App::make(CommunicationAPI::class);
                            $commAPI->sendMessage($commMsg);
                        } catch (\Throwable $th) {
                            Log::info($th);
                        }
                    }

                    if ($newPaymentStatus === 'paid') {
                        $sales_mail = true;
                        $this->service->sendPaymentReceiptMail($invoice, $sales_mail);

                        $client = $invoice->client;

                        if ($client && $client->plant_a_tree) {
                            DB::transaction(function () use ($client) {
                                MoreTreeHistory::create(["client_id" => $client->id]);
                                $client->update(['plant_a_tree' => false, 'is_tree_planted' => true]);
                            });
                        }
                    }*/

                    /* Invoice payment receive send mail */
                    $to_mail = config('mail.wise_payment.to_address');
                    $bcc_mail = config('mail.wise_payment.bcc_address');
                    $to = $to_mail;
                    $bcc = $bcc_mail;
                    Mail::mailer(config('mail.default'))
                        ->to($to)
                        ->bcc($bcc)
                        ->send(new InvoicePaymentMail($invoice,$currency,$amount));
                }
            }
            return response()->json(['success' => true], 201);
        } catch (\Throwable $th) {

            DB::rollback();
            Log::info($th);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while adding payment!',
                'error' => $th,
            ], 500);
        }
    }
}
