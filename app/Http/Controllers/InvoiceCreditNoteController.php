<?php

namespace App\Http\Controllers;

use App\Enums\InvoicePaymentStatus;
use App\Enums\InvoiceType;
use App\Helpers\ActivityLogHelper;
use App\Http\Requests\StoreCreditNoteRequest;
use App\Http\Requests\UpdateCreditNoteRequest;
use App\Models\Bank;
use App\Models\Client;
use App\Models\CompanyDetail;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Services\InvoiceService;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceCreditNoteController extends Controller
{
    private $service;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->service = $invoiceService;
    }

    public function create(Invoice $invoice)
    {
        $invoice->loadSum('payments', 'amount');
        $invoice->load(['credit_note:id,grand_total,parent_invoice_id,currency_id', 'credit_note.currency:id,symbol']);
        $grand_total = (float) $invoice->grand_total;
        $payment_sum_amount =  (float) $invoice->payments_sum_amount;
        $credit_note_amount = (float) ($invoice->credit_note
            ? $invoice->credit_note->grand_total
            : 0);
        $symbol = $invoice->credit_note && $invoice->credit_note->currency
            ? $invoice->credit_note->currency->symbol
            : '';
        $due_amount = max($grand_total - $payment_sum_amount - $credit_note_amount, 0);

        $credit_note = $invoice->replicate();
        $credit_note_number = $this->service->credit_note_number();

        $clients = Client::where('workspace_id', Auth::user()->workspace_id)->orderBy('id', 'DESC')->get(['id', 'name']);
        $company_details = CompanyDetail::where('workspace_id', Auth::user()->workspace_id)->orderBy('name')->get();
        $sales_people = User::query()
            ->whereHas(
                'workspaces',
                function ($query) {
                    $query
                        ->where('workspaces.id', Auth::user()->workspace_id);
                }
            )
            ->get([
                'id',
                'first_name',
                'last_name'
            ]);

        return view('credit_notes.create', compact('credit_note_number', 'clients', 'sales_people', 'credit_note', 'invoice', 'due_amount', 'company_details'));
    }

    public function store(StoreCreditNoteRequest $request, Invoice $invoice)
    {

        $valid = $request->validated();

        DB::beginTransaction();

        try {
            if ($invoice->payment_status == InvoicePaymentStatus::PAID) throw new \Exception('Selected invoice is a paid invoice!');
            if ($invoice->credit_note) throw new \Exception('Invoice already has a credit note!');

            $invoice->loadSum('payments', 'amount');
            $invoice->load(['credit_note:id,grand_total,parent_invoice_id,currency_id', 'credit_note.currency:id,symbol']);
            $invoice_grand_total = (float) $invoice->grand_total;
            $payment_sum_amount =  (float) $invoice->payments_sum_amount;

            $invoice_currency_symbol = $invoice->credit_note && $invoice->credit_note->currency
                ? $invoice->credit_note->currency->symbol
                : '';

            $invoice_due_amount = max($invoice_grand_total - $payment_sum_amount, 0);

            $sub_total = 0;
            $vat_total = 0;
            $grand_total = 0;

            foreach ($valid['invoice_items'] as $key => $invoice_item) {

                $vat = 0;
                $tax_rate = 0;
                $price = (float) $invoice_item['price'];

                if (array_key_exists('tax_type', $invoice_item) && $invoice_item['tax_type'] === 'vat_20') {
                    $tax_rate = 20;
                    $vat = (($price) * $tax_rate) / 100;
                } else {
                    $invoice_item['tax_type'] = 'no_vat';
                }

                $total_price = $vat + $price;
                $sub_total += $price;
                $vat_total += $vat;
                $grand_total += $total_price;
                $grand_total = number_format($grand_total, 2, '.', '');

                if (($grand_total /*- (float) $valid['discount']*/) > $invoice_due_amount) throw new \Exception("Credit note total greater than Invoice due amount: {$invoice_currency_symbol}{$invoice_due_amount} !");

                $valid['invoice_items'][$key]['tax_rate'] = $tax_rate;
                $valid['invoice_items'][$key]['tax_amount'] = $vat;
                $valid['invoice_items'][$key]['total_price'] = $total_price;
                $valid['invoice_items'][$key]['quantity'] = 1;
                $valid['invoice_items'][$key]['sequence'] = $key + 1;

                unset($valid['invoice_items'][$key]['total_amount']);
                unset($valid['invoice_items'][$key]['vat_amount']);
                unset($valid['invoice_items'][$key]['price_amount']);
            }

            $credit_note = new Invoice;

            $credit_note->type                  = InvoiceType::CREDIT_NOTE;
            $credit_note->parent_invoice_id     = $invoice->id;

            $credit_note->client_id             = $valid['client_id'];
            $credit_note->company_detail_id     = $valid['company_detail_id'];
            $credit_note->client_name           = $valid['client_name'] ?? null;
            $credit_note->user_id               = $valid['user_id'] ?? null;
            $credit_note->invoice_number        = $valid['invoice_number'];
            $credit_note->currency_id           = $valid['currency_id'];
            $credit_note->invoice_date          = DateTime::createFromFormat('d-m-Y', $valid['invoice_date']);
            $credit_note->note                  = $valid['note'] ?? '';
            /*$credit_note->discount              = (float) $valid['discount'] ?? 0;*/
            $credit_note->invoice_type          = config('custom.invoices_types.one-off', '0');
            $credit_note->sub_total             = $sub_total;
            $credit_note->vat_total             = $vat_total;
            $credit_note->grand_total           = $grand_total /*- (float) $valid['discount']*/;
            $bank_detail                        = Bank::where('currency_id', $valid['currency_id'])->orderByDesc('created_at')->first();
            if (!$bank_detail) {
                $bank_detail                    = Bank::where('is_default', true)->orderByDesc('created_at')->first();
            }
            $credit_note->bank_detail_id            = $bank_detail->id ?? NULL;

            $credit_note->save();

            $credit_note->invoice_items()->createMany($valid['invoice_items']);

            ActivityLogHelper::log(
                'credit_notes.created',
                'Credit note #' . $valid['invoice_number'] . ' created by admin.',
                [],
                $request,
                Auth::user(),
                $invoice
            );

            $newStatus = $invoice->payment_status;
            $fully_paid_at = $invoice->fully_paid_at;
            $grand_total = number_format($credit_note->grand_total,2);
            $invoice_due_amount = number_format($invoice_due_amount,2);

            if ($grand_total >= $invoice_due_amount) {
                $newStatus = 'paid';
                $fully_paid_at = now();
            } else if ($grand_total < $invoice_due_amount) {
                $newStatus = 'partially_paid';
            }

            $invoice->update([
                'payment_status' => $newStatus,
                'fully_paid_at' => $fully_paid_at,
            ]);

            DB::commit();

            $this->service->makeAndStorePDF($invoice);
            if ($invoice->payments()->count()) $this->service->makeAndStorePDF($invoice, 'payment_receipt');
            $this->service->makeAndStorePDF($credit_note, 'credit_note');

            return response()->json([
                'success' => true
            ], 201);
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'error' => $th
            ], 500);
        }
    }

    public function show(Request $request, Invoice $credit_note)
    {
        $credit_note->loadMissing([
            'invoice_items:id,invoice_id,description,price,total_price,tax_type,tax_rate,tax_amount',
            'client:id,name,country_id,address_line_1,address_line_2,city,email,zip_code',
            'client.country:id,name',
            'sales_person:id,first_name,last_name',
            'currency:id,symbol',
            'parent_invoice:id,invoice_number',
        ])
            ->loadSum('payments', 'amount');
        $email = explode(',',$credit_note->client->email);
        $credit_note->client->email =  $email[0];

        return view('credit_notes.show', compact('credit_note'));
    }

    public function edit(Invoice $credit_note)
    {
        $invoice = $credit_note->parent_invoice;

        $invoice->loadSum('payments', 'amount');

        $view_config            = ['title' => 'Edit'];
        $grand_total            = (float) $invoice->grand_total;
        $payment_sum_amount     = (float) $invoice->payments_sum_amount;
        $symbol                 = $credit_note->currency ? $credit_note->currency->symbol : '';
        $due_amount             = max($grand_total - $payment_sum_amount, 0);
        $credit_note_number     = $credit_note->invoice_number;
        $clients                = Client::where('workspace_id', Auth::user()->workspace_id)->orderBy('id', 'DESC')->get(['id', 'name']);
        $company_details        = CompanyDetail::where('workspace_id', Auth::user()->workspace_id)->orderBy('name')->get();
        $invoice                = $credit_note;

        $sales_people           = User::query()
            ->whereHas(
                'workspaces',
                function ($query) {
                    $query
                        ->where('workspaces.id', Auth::user()->workspace_id);
                }
            )
            ->get([
                'id',
                'first_name',
                'last_name'
            ]);

        return view('credit_notes.create', compact('credit_note_number', 'clients', 'sales_people', 'invoice', 'due_amount', 'view_config', 'company_details'));
    }

    public function update(UpdateCreditNoteRequest $request, Invoice $credit_note)
    {

        DB::beginTransaction();

        try {

            $valid = $request->validated();
            $invoice = $credit_note->parent_invoice;
            $invoice->loadSum('payments', 'amount');

            $invoice_grand_total = (float) $invoice->grand_total;
            $payment_sum_amount =  (float) $invoice->payments_sum_amount;

            $invoice_currency_symbol = $invoice->credit_note && $invoice->credit_note->currency
                ? $invoice->credit_note->currency->symbol
                : '';

            $invoice_due_amount = max($invoice_grand_total - $payment_sum_amount, 0);

            $sub_total      = 0;
            $vat_total      = 0;
            $grand_total    = 0;

            foreach ($valid['invoice_items'] as $key => $invoice_item) {

                $vat = 0;
                $tax_rate = 0;
                $price = (float) $invoice_item['price'];

                if (array_key_exists('tax_type', $invoice_item) && $invoice_item['tax_type'] === 'vat_20') {
                    $tax_rate = 20;
                    $vat = (($price) * $tax_rate) / 100;
                } else {
                    $invoice_item['tax_type'] = 'no_vat';
                }

                $total_price = $vat + $price;
                $sub_total += $price;
                $vat_total += $vat;
                $grand_total += $total_price;

                if (($grand_total/*- (float) $valid['discount']*/) > $invoice_due_amount) throw new \Exception("Credit note total greater than Invoice due amount: {$invoice_currency_symbol}{$invoice_due_amount} !");

                $valid['invoice_items'][$key]['tax_rate'] = $tax_rate;
                $valid['invoice_items'][$key]['tax_amount'] = $vat;
                $valid['invoice_items'][$key]['total_price'] = $total_price;
                $valid['invoice_items'][$key]['quantity'] = 1;
                $valid['invoice_items'][$key]['sequence'] = $key + 1;
                $valid['invoice_items'][$key]['invoice_id'] = $credit_note->id;

                unset($valid['invoice_items'][$key]['total_amount']);
                unset($valid['invoice_items'][$key]['vat_amount']);
                unset($valid['invoice_items'][$key]['price_amount']);
            }
            $invoice_old = $credit_note->getAttributes();
            $credit_note->client_id             = $valid['client_id'];
            $credit_note->company_detail_id     = $valid['company_detail_id'];
            $credit_note->client_name           = $valid['client_name'] ?? null;
            $credit_note->user_id               = $valid['user_id'] ?? null;
            $credit_note->invoice_number        = $valid['invoice_number'];
            $credit_note->currency_id           = $valid['currency_id'];
            $credit_note->invoice_date          = DateTime::createFromFormat('d-m-Y', $valid['invoice_date']);
            $credit_note->note                  = $valid['note'] ?? '';
            /*$credit_note->discount              = (float) $valid['discount'] ?? 0;*/
            $credit_note->invoice_type          = config('custom.invoices_types.one-off', '0');
            $credit_note->sub_total             = $sub_total;
            $credit_note->vat_total             = $vat_total;
            $credit_note->grand_total           = $grand_total/* - (float) $valid['discount']*/;
            $bank_detail                        = Bank::where('currency_id', $valid['currency_id'])->orderByDesc('created_at')->first();
            if (!$bank_detail) {
                $bank_detail                    = Bank::where('is_default', true)->orderByDesc('created_at')->first();
            }
            $credit_note->bank_detail_id            = $bank_detail->id ?? NULL;

            $credit_note->save();
            $invoice_items_to_keep = array_column($valid['invoice_items'], 'id');

            foreach ($credit_note->invoice_items as $invoice_item) {
                if (!in_array($invoice_item->id, $invoice_items_to_keep)) {
                    $invoice_item->delete();
                }
            }

            InvoiceItem::upsert(
                $valid['invoice_items'],
                ['id'],
                [
                    'id',
                    'description',
                    'price',
                    'tax_type',
                    'invoice_id',
                    'tax_rate',
                    'tax_amount',
                    'total_price',
                    'quantity',
                    'sequence',
                    'created_at',
                    'updated_at'
                ]
            );

            $differences = [];
            foreach ($valid as $key => $value) {
                /* Invoice date */
                $invoice_date              = DateTime::createFromFormat('d-m-Y',  $valid['invoice_date']);
                $new_invoice_date          = $invoice_date->format('Y-m-d');
                $formatted_invoice_date    = Carbon::parse($invoice_old['invoice_date'])->format('Y-m-d');

                /* Company detail id */
                $company_detail_id         = intval($valid['company_detail_id']) ?? NULL;

                if (array_key_exists($key, $invoice_old) && $invoice_old[$key] !== $value) {

                    if($key == "client_id" && $value != $invoice_old[$key]){
                        /* Old Client Name With New Client Name Get */
                        $client_name = Client::select('name')->where('id',$invoice_old[$key])->where('workspace_id', Auth::user()->workspace_id)->first();
                        $client_name_new = Client::select('name')->where('id',$value)->where('workspace_id', Auth::user()->workspace_id)->first();
                        $differences[$key] = (isset($client_name->name) && !empty($client_name->name) ? $client_name->name : '') . ' => ' . (isset($client_name_new->name) && !empty($client_name_new->name) ? $client_name_new->name : '');

                    }elseif($key == "invoice_date" && $new_invoice_date != $formatted_invoice_date){
                        /* Old Invoice date With New Invoice date Get */
                        $differences[$key] = "$formatted_invoice_date => $new_invoice_date";

                    }elseif($key == "company_detail_id" && $company_detail_id != $invoice_old[$key]){
                        /* Old Company Name With New Company Name Get */
                        $company_name = CompanyDetail::select('name')->where('id',$invoice_old[$key])->where('workspace_id', Auth::user()->workspace_id)->first();
                        $company_name_new = CompanyDetail::select('name')->where('id',$value)->where('workspace_id', Auth::user()->workspace_id)->first();
                        $differences[$key] = (isset($company_name->name) && !empty($company_name->name) ? $company_name->name : '') . ' => ' . (isset($company_name_new->name) && !empty($company_name_new->name) ? $company_name_new->name : '');
                    }
                }
            }

            $logDescription = '';
            if(isset($differences) && !empty($differences)){
                $logDescription = ' Changes: ' . implode(', ', $differences);
            }

            ActivityLogHelper::log(
                'credit_notes.updated',
                'Credit note #' . $valid['invoice_number'] . ' updated by admin' . $logDescription . '.' ,
                [],
                $request,
                Auth::user(),
                $credit_note
            );

            $newStatus = $invoice->payment_status;
            $fully_paid_at = $invoice->fully_paid_at;

            if ($credit_note->grand_total >= $invoice_due_amount) {
                $newStatus = 'paid';
                $fully_paid_at = now();
            } else if ($credit_note->grand_total < $invoice_due_amount) {
                $newStatus = 'partially_paid';
            }

            $invoice->update([
                'payment_status' => $newStatus,
                'fully_paid_at' => $fully_paid_at,
            ]);

            DB::commit();

            $this->service->makeAndStorePDF($invoice);
            if ($invoice->payments()->count()) $this->service->makeAndStorePDF($invoice, 'payment_receipt');
            $this->service->makeAndStorePDF($credit_note, 'credit_note');

            return response()->json([
                'success' => true
            ], 201);
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'error' => $th
            ], 500);
        }
    }

    public function preview(Invoice $credit_note, Request $request)
    {
        return $this->service->stream($credit_note, $request->input('type', 'credit_note'));
    }

    public function destroy(Request $request, Invoice $credit_note)
    {
        DB::beginTransaction();
        try {

            $invoice = $credit_note->parent_invoice;
            $invoice_grand_total = (float) $invoice->grand_total;
            $payment_sum_amount =  (float) $invoice->payments()->sum('amount');
            $invoice_due_amount = (float) max($invoice_grand_total - $payment_sum_amount, 0);

            $credit_note->delete();

            ActivityLogHelper::log(
                "credit_notes.deleted",
                "Credit note deleted by " . Auth::user()->full_name,
                [],
                $request,
                Auth::user(),
                $credit_note
            );


            $newStatus = $invoice->payment_status;

            if ($payment_sum_amount == 0) {
                $newStatus = 'unpaid';
            } else if ($payment_sum_amount < $invoice_grand_total) {
                $newStatus = 'partially_paid';
            } else  if ($payment_sum_amount >= $invoice_grand_total) {
                $newStatus = 'paid';
            }

            $invoice->update(['payment_status' => $newStatus]);

            DB::commit();

            $this->service->makeAndStorePDF($invoice);
            if ($invoice->payments()->count()) $this->service->makeAndStorePDF($invoice, 'payment_receipt');

            if ($request->ajax()) {
                return response()->json([
                    "success" => true,
                ]);
            }

            return redirect()->route("leads.index");
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error($th);
            if ($request->ajax()) {
                return response()->json([
                    "success" => false,
                    "message" => $th->getMessage(),
                    "error" => $th,
                ], 500);
            }
            return redirect()->route("leads.index");
        }
    }
}
