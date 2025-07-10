<?php

namespace App\Http\Controllers;

use App\Contracts\CommunicationAPI;
use App\Helpers\ActivityLogHelper;
use App\Helpers\DateHelper;
use App\Helpers\EncryptionHelper;
use App\Models\Invoice;
use App\Models\MoreTreeHistory;
use App\Models\Payment;
use App\Models\PaymentSource;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class InvoicePaymentController extends Controller
{
    public $service;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->service = $invoiceService;
    }

    public function last5Payments(Request $request, Invoice $invoice)
    {
        $invoice->loadSum('payments', 'amount');
        $grand_total = (float)$invoice->grand_total;
        $payment_sum_amount =  (float)$invoice->payments_sum_amount;
        $invoice->load('credit_note:id,grand_total,parent_invoice_id');
        $credit_note_amount = (float) ($invoice->credit_note ? $invoice->credit_note->grand_total : 0);

        $payments = Payment::query()
            ->select(
                'payments.id',
                'payments.invoice_id',
                'payments.payment_source_id',
                'payments.amount',
                'payments.paid_at',
                'payments.reference',
                'payments.created_at',
            )
            ->with([
                'invoice:id,currency_id',
                'invoice.currency:id,symbol',
                'payment_source:id,title'
            ])
            ->whereHas('invoice', function ($query) use ($invoice) {
                $query->where('id', $invoice->id);
            });

        $previous_payment_source = Invoice::select(
            "invoices.id",
            "invoices.client_id",
            "payments.payment_source_id",
            "payments.amount"
        )->leftJoin('payments', 'payments.invoice_id','invoices.id')
            ->leftJoin('clients', 'clients.id','invoices.client_id')
            ->where('clients.workspace_id', Auth::user()->workspace_id)->where('invoices.client_id',$invoice->client_id)->where('payment_status','!=','unpaid')->orderBy('payments.id','desc')->first();

        $previous_payment_source = $previous_payment_source->payment_source_id ?? null;
        return DataTables::eloquent($payments)
            ->editColumn('amount', function (Payment $payment) {
                return $payment->invoice->currency->symbol . number_format((float)$payment->amount, 2, '.', '');
            })
            ->addColumn('payment_source_title', function (Payment $payment) {
                return $payment->payment_source ? $payment->payment_source->title : '';
            })
            ->editColumn(
                'created_at',
                function (Payment $invoice) {
                    return $invoice->created_at->setTimezone(Auth::user()->timezone)->format(DateHelper::INVOICE_PAID_AT);
                }
            )
            ->editColumn(
                'paid_at',
                function (Payment $invoice) {
                    return $invoice->paid_at->setTimezone(Auth::user()->timezone)->format(DateHelper::INVOICE_PAID_AT);
                }
            )
            ->editColumn(
                'id',
                function (Payment $invoice) {
                    return EncryptionHelper::encrypt($invoice->id);
                }
            )
            ->with('due_amount', number_format(max($grand_total - $payment_sum_amount - $credit_note_amount, 0), 2, '.', ''))
            ->with('has_sales_person', boolval($invoice->user_id))
            ->with('previous_payment_source', $previous_payment_source)
            ->toJson();
    }

    public function store(Request $request, Invoice $invoice)
    {
        $invoice->loadSum('payments', 'amount');
        $invoice->load('credit_note:id,grand_total,parent_invoice_id');
        $credit_note_amount = (float) ($invoice->credit_note ? $invoice->credit_note->grand_total : 0);

        $valid = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', function ($attribute, $value, $fail) use ($invoice, $credit_note_amount) {
                $due_amount = (float)$invoice->grand_total - (float)$invoice->payments_sum_amount - $credit_note_amount;
                $due_amount = number_format($due_amount, 2, '.', '');
                $due_amount_formatted = number_format(max($due_amount, 0), 2, '.', ',');
                if ($value > $due_amount) {
                    $fail('Enter amount less than due amount for the invoice. The due amount for this invoice is ' . $due_amount_formatted);
                }
            }],
            'reference' => 'nullable|string',
            'paid_at' => 'required|date_format:d/m/Y',
            'payment_source_id' => ['nullable', Rule::exists('payment_sources', 'id')->where('workspace_id', Auth::user()->workspace_id)],
        ]);

        DB::beginTransaction();

        try {
            $paid_at = Carbon::createFromFormat(
                'd/m/Y',
                $valid['paid_at']
            )->shiftTimezone(Auth::user()->timezone);

            $invoicePayment = $invoice->payments()->create([
                'paid_at' => $paid_at,
                'amount' => $valid['amount'],
                'reference' => $valid['reference'],
                'payment_source_id' => $valid['payment_source_id'],
            ]);

            $invoice->loadSum('payments', 'amount');
            $payments_sum_amount = (float) $invoice->payments_sum_amount;
            $grand_total = (float) $invoice->grand_total;
            $due_amount = $grand_total - $credit_note_amount;
            $invoiceNumber = $invoice->invoice_number ?? '';
            $oldStatus = $invoice->payment_status;
            $oldStatusHl = Str::headline($oldStatus);

            $newPaymentStatus = null;
            if ($payments_sum_amount > 0) {
                if ($payments_sum_amount >= $due_amount) {
                    $newPaymentStatus = 'paid';
                } else if ($payments_sum_amount < $due_amount) {
                    $newPaymentStatus = 'partially_paid';
                }
            }
            $newStatusHl = Str::headline($newPaymentStatus);

            if ($newPaymentStatus && $newPaymentStatus != $invoice->payment_status) {
                $invoice->update([
                    'payment_status' => $newPaymentStatus,
                    'fully_paid_at' => $oldStatus != 'paid' && $newPaymentStatus === 'paid' ? now() : $invoice->fully_paid_at,
                ]);
            }

            ActivityLogHelper::log('invoices.payments.created', "A payment was received for invoice #{$invoiceNumber}.", [], $request, Auth::user(), $invoicePayment);

            if ($newPaymentStatus) {
                ActivityLogHelper::log(
                    'invoice.payment-status.updated',
                    'Invoice #' . $invoice->invoice_number . "'s payment status updated from {$oldStatusHl} to {$newStatusHl}",
                    [
                        'previousStatus' => $oldStatus,
                        'newStatus' => $newPaymentStatus,
                    ],
                    $request,
                    Auth::user(),
                    $invoice
                );
            }

            DB::commit();

            $this->service->makeAndStorePDF($invoice);
            $this->service->makeAndStorePDF($invoice, 'payment_receipt');
            if ($invoice->credit_note) $this->service->makeAndStorePDF($invoice->credit_note, 'credit_note');

            // DB::commit();

            if (
                $invoice->client &&
                $invoice->client->workspace &&
                ($invoice->client->workspace->slug === 'iih-global' || $invoice->client->workspace->slug === 'shalin-designs')
            ) {
                try {
                    $msgAmount = number_format((float) $invoicePayment->amount, 2);
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

            /*if ($request->boolean('notify_sales_person')) {
                $this->service->sendPaymentReceivedMail($invoice, 'latest_payment');
            }*/

            if ($newPaymentStatus === 'paid') {
                $sales_mail = false;
                if($request->boolean('notify_sales_person')){
                    $sales_mail = true;
                    $this->service->sendPaymentReceiptMail($invoice,$sales_mail,null);
                }else{
                    $this->service->sendPaymentReceiptMail($invoice,$sales_mail,null);
                }

                $client = $invoice->client;

                if ($client && $client->plant_a_tree) {
                    DB::transaction(function () use ($client) {
                        MoreTreeHistory::create(["client_id" => $client->id]);
                        $client->update(['plant_a_tree' => false, 'is_tree_planted' => true]);
                    });
                }
            }

            if ($newPaymentStatus === 'partially_paid') {
                if($request->boolean('notify_sales_person')){
                    $sales_mail = true;
                    $this->service->salespersonSendPaymentReceiptMail($invoice,$sales_mail);
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

    public function paymentUpdate(Request $request,Invoice $invoice){
        $valid = $request->validate([
            'reference' => 'nullable|string',
            'paid_at' => 'required|date_format:d/m/Y',
            'payment_source_id' => ['nullable', Rule::exists('payment_sources', 'id')->where('workspace_id', Auth::user()->workspace_id)],
        ]);

        try {
            $invoiceNumber = $invoice->invoice_number;
            $paymentId = EncryptionHelper::decrypt($request->payment_id);
            $paid_at = Carbon::createFromFormat(
                'd/m/Y',
                $valid['paid_at']
            )->shiftTimezone(Auth::user()->timezone);

            /* payment data update */
            $invoicePayment = Payment::find($paymentId);
            $invoicePayment_data = $invoicePayment->getAttributes();
            if ($invoicePayment) {
                $invoicePayment->paid_at = $paid_at;
                $invoicePayment->reference = $valid['reference'];
                $invoicePayment->payment_source_id = $valid['payment_source_id'];
                $invoicePayment->save();
            }

            $differences = [];
            foreach ($valid as $key => $value) {
                $carbonDate = Carbon::parse($invoicePayment_data['paid_at']);
                $formattedDate = $carbonDate->format('d/m/Y');
                if (array_key_exists($key, $invoicePayment_data) && $invoicePayment_data[$key] !== $value) {
                    if($key == "payment_source_id" && $value != $invoicePayment_data[$key]){
                        $bank_name = PaymentSource::select('title')->where('id',$invoicePayment_data[$key])->where('workspace_id', Auth::user()->workspace_id)->first();
                        $bank_name_new = PaymentSource::select('title')->where('id',$value)->where('workspace_id', Auth::user()->workspace_id)->first();
                        $differences[$key] = (isset($bank_name->title) && !empty($bank_name->title) ? $bank_name->title : '') . ' => ' . (isset($bank_name_new->title) && !empty($bank_name_new->title) ? $bank_name_new->title : '');
                    }elseif($key == "reference"){
                        $differences[$key] = "$invoicePayment_data[$key] => $value";
                    }elseif($key == "paid_at" && $value != $formattedDate){
                        $differences[$key] = "$formattedDate => $value";
                    }
                }
            }

            $logDescription = '';
            if(isset($differences) && !empty($differences)){
                $logDescription = ' Changes: ' . implode(', ', $differences);
            }

            /* payment Log */
            ActivityLogHelper::log('invoices.payments.paymentUpdate', "Payment receipt source has been updated for invoice #{$invoiceNumber} {$logDescription}.", [], $request, Auth::user(), $invoicePayment);

            /* payment pdf store */
            $this->service->makeAndStorePDF($invoice, 'payment_receipt');

            return response()->json(['success' => true], 201);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::info($th);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while adding updating payment!',
                'error' => $th,
            ], 500);
        }
    }
    public function paymentDestroy(Request $request,Invoice $invoice)
    {
        try {
            $paymentId = EncryptionHelper::decrypt($request->payments_id);
            $invoicePayment = Payment::find($paymentId);
            $invoicePayment->delete();

            $invoice_payment_count = Payment::where('invoice_id',$invoice->id)->count();
            if($invoice_payment_count < 1 && ($invoice->payment_status == 'paid' || $invoice->payment_status == 'partially_paid')){
                $invoice->update([
                    'payment_status' => 'unpaid',
                    'fully_paid_at' => null,
                    'receipt_file_name' => null,
                    'receipt_file_path' => null,
                    'receipt_file_disk' => null,
                ]);
            }else{
                $invoice->update([
                    'payment_status' => 'partially_paid',
                    'fully_paid_at' => null,
                ]);

                /* payment receipt pdf store */
                $this->service->makeAndStorePDF($invoice, 'payment_receipt');
            }

            /* invoice preview pdf store */
            $this->service->makeAndStorePDF($invoice);

            ActivityLogHelper::log(
                "invoices.payments.paymentDeleted",
                "Invoices payment deleted by " . Auth::user()->name,
                [],
                $request,
                Auth::user(),
                $invoicePayment
            );
            return response()->json(['success' => true], 201);
        }catch (\Throwable $th){
            DB::rollback();
            Log::info($th);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while deleting payment!',
                'error' => $th,
            ], 500);
        }
    }

}
