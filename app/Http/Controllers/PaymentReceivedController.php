<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Invoice;
use App\Models\PaymentDetail;
use App\Models\PaymentSource;
use App\Models\User;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;
use App\Helpers\EncryptionHelper;
use App\Helpers\DateHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Helpers\ActivityLogHelper;
use App\Contracts\CommunicationAPI;
use Illuminate\Support\Facades\App;
use App\Models\MoreTreeHistory;
use App\Services\InvoiceService;
use App\Helpers\PaymentHelper;

class PaymentReceivedController extends Controller
{
    public $service;
    
    public function __construct(InvoiceService $invoiceService)
    {
        $this->service = $invoiceService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            
            $payment_received = PaymentDetail::query()
            ->join('currencies', 'payment_details.currency', '=', 'currencies.code')
            ->select('payment_details.*', 'currencies.symbol')
            ->where('payment_details.workspace_id',Auth::user()->workspace_id);

            if ($request->has('is_invoice_link_to_crm')) {
                $value = $request->input('is_invoice_link_to_crm');
                if ($value == '1') {
                    $payment_received->where('is_invoice_link_to_crm','1');
                } elseif ($value == '0') {
                    $payment_received->where('is_invoice_link_to_crm','0');
                }
            }

            return DataTables::eloquent($payment_received)
                ->filter(function ($query) use ($request) {
                    // Global search (from DataTable search box)
                    if ($search = $request->get('search')['value']) {
                        $query->where(function ($q) use ($search) {
                            $q->where('invoice_number', 'like', "%{$search}%")
                            ->orWhere('customer_name', 'like', "%{$search}%")
                            ->orWhere('payment_source', 'like', "%{$search}%")
                            ->orWhere('customer_email', 'like', "%{$search}%")
                            ->orWhereRaw("CONCAT(currencies.symbol, payment_details.amount_received) LIKE ?", ["%{$search}%"])
                            ->orWhere('currency', 'like', "%{$search}%");
                        });
                    }
                })
                ->addColumn('invoice_number', function ($row) {
                    return $row->invoice_number;
                })
                ->addColumn('customer_name', function ($row) {
                    return $row->customer_name;
                })

                ->addColumn('customer_email', function ($row) {
                    return $row->customer_email;
                })
                ->addColumn('payment_source', function ($row) {
                    return $row->payment_source;
                })
                ->addColumn('amount_received', function ($row) {
                    return $row->symbol.''.$row->amount_received;
                })
                ->addColumn('currency', function ($row) {
                    return $row->currency;
                })
                ->editColumn('created_at', function ($row) {
                    $timezone = Auth::user()->timezone;
                    return \Carbon\Carbon::parse($row->created_at)
                        ->timezone($timezone)
                        ->format(DateHelper::CLIENT_DATE_FORMAT_MYSQL);
                })
                ->filterColumn('created_at', function ($query, $keyword) {
                    $format = DateHelper::CLIENT_DATE_FORMAT_MYSQL;
                    $timezone_offset = DateHelper::getGmtOffsetFromTimezone(Auth::user()->timezone);
                    $query->orWhereRaw("DATE_FORMAT(CONVERT_TZ(created_at, '+00:00', '{$timezone_offset}'), '{$format}') like ?", ["%{$keyword}%"]);
                })
                ->addColumn('action', function ($row) {
                    if ($row->is_invoice_link_to_crm == 0) {
                        $id = EncryptionHelper::encrypt($row->id);
                        return '<a href="javascript:void(0)" class="invoice-link-icon" data-id="' . $id . '" title="Payment Linked to Invoice">
                                <i data-feather="link" class="font-medium-3"></i>
                            </a>';
                    }
                    return '';
                })     
                ->make(true);
        }

        return view('payment_received.index');
    }

    public function invoicesDropdown(Request $request){
        try{
            $paymentId = EncryptionHelper::decrypt($request->id);
            $payment_detail = PaymentDetail::where('id',$paymentId)->where('payment_details.workspace_id',Auth::user()->workspace_id)->first();
            $currency_code = Currency::whereRaw('UPPER(code) = ?', [strtoupper($payment_detail->currency)])->first();
            $currency_code_id = $currency_code->id ?? null;
    
            $amount =  round($payment_detail->amount_received, 2);
    
            $payment_status = 'unpaid';
            $invoices = Invoice::with('client:id,name')
                            ->where('type','0')
                            ->where('payment_status',$payment_status)
                            ->where('currency_id',$currency_code_id)
                            ->where('grand_total',$amount)
                            ->get();
    
            return response()->json(['invoices' => $invoices]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while sending mail!',
                'error' => $th
            ], 500);
        }
    }

    public function paymentInvoicesLink(Request $request){
        try{
            $payment_received_id = $request->payment_received_id;
            $invoice_info = $request->invoice_info;
            
            $paymentId = EncryptionHelper::decrypt($payment_received_id);
            $payment_detail = PaymentDetail::where('id',$paymentId)->first();
            $invoice = Invoice::where('id',$invoice_info)->first();
            $amount = $payment_detail->amount_received;
            $project_link = 'Manually Project Link';

            $payment = PaymentDetail::find($paymentId);
            if ($payment) {
                $dataToUpdate = [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'is_invoice_link_to_crm' => '1',
                ];
            
                if (empty($payment->customer_email)) {
                    $dataToUpdate['customer_email'] = $invoice->client->email ?? null;
                }
            
                if (empty($payment->customer_name)) {
                    $dataToUpdate['customer_name'] = $invoice->client->name ?? null;
                }
            
                $payment->update($dataToUpdate);
            }

            $this->receivedPaymentStore($invoice,$amount,$project_link);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while sending mail!',
                'error' => $th
            ], 500);
        }
    }

    public function receivedPaymentStore($invoice,$amount,$project_link){

        $invoice->loadSum('payments', 'amount');
        $invoice->load('credit_note:id,grand_total,parent_invoice_id');

        $user = Auth::user();
        $payment_source = PaymentSource::where('title','Stripe - IIH Global')->where('workspace_id',Auth::user()->workspace_id)->first();
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

            ActivityLogHelper::log('invoices.payments.created', "A payment was received for invoice #{$invoiceNumber}. ({$project_link})", [], request(), $user, $invoicePayment);

            if ($newPaymentStatus) {
                ActivityLogHelper::log(
                    'invoice.payment-status.updated',
                    'Invoice #' . $invoice->invoice_number . "'s payment status updated from {$oldStatusHl} to {$newStatusHl} ({$project_link})",
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

            if ($invoice->client && $invoice->client->workspace && ($invoice->client->workspace->slug === 'iih-global')) {
                try {
                    $msgAmount = number_format((float) $invoicePayment->amount, 2);
                    $msgCurrency = $invoice->currency
                        ? " (" . $invoice->currency->code . ")"
                        : "";
                    $msgCompany = $invoice->client->name ?? '';
                    $msgSource = $invoicePayment->payment_source->title ?? '';
                    $commMsg = "*{$msgAmount}{$msgCurrency}* received from *{$msgCompany}*  in {$msgSource} (Invoice Linked) ";
                    $commAPI = App::make(CommunicationAPI::class);
                    $commAPI->sendMessage($commMsg);
                } catch (\Throwable $th) {
                    Log::info($th);
                }
            }

            if ($newPaymentStatus === 'paid') {
                $sales_mail = true;
                $this->service->sendPaymentReceiptMail($invoice,$sales_mail,null);

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
