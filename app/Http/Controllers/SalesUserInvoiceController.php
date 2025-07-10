<?php

namespace App\Http\Controllers;

use App\Enums\SalesInvoice;
use App\Mail\SalesUserInvoiceMail;
use App\Models\Client;
use App\Models\CompanyDetail;
use App\Models\Currency;
use App\Models\EmailSignature;
use App\Models\SalesInvoiceAccess;
use App\Models\User;
use App\Models\SalesUserInvoice;
use App\Models\SalesUserInvoiceItem;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use DateTime;
use DateTimeZone;
use App\Helpers\ActivityLogHelper;
use Illuminate\Support\Facades\Mail;

class SalesUserInvoiceController extends Controller
{
    public function index(Request $request)
    {

         if ($request->ajax()) {
            
            $sales_invoice_access = SalesInvoiceAccess::where('sales_id',Auth::user()->id)->get()->pluck('client_id')->toArray(); 
        $results = SalesUserInvoice::with(['client', 'company_detail','currency'])
            ->whereIn('client_id',$sales_invoice_access)
            ->whereHas('client', function ($q) {
                $q->where('workspace_id', Auth::user()->workspace_id);
            });

        if ($request->filled('is_sales_invoice_status')) {
            $results->where('status', $request->is_sales_invoice_status);
        }

        $query = $results->get();

        return DataTables::of($query)
            ->editColumn('sales_invoice_number', function ($row) {
                return '#'.$row->sales_invoice_number;
            })
            ->editColumn('type', function ($row) {
                return $row->type == 1 ? 'Subscription' : 'One-off';
            })
            ->addColumn('name', function ($row) {
                return $row->client->name ?? ($row->client->name ?? '-');
            })
            ->editColumn('company', function ($row) {
                return $row->company_detail->name ?? '-';
            })
            ->editColumn('invoice_date', function ($row) {
                return Carbon::parse($row->invoice_date)->format('d/m/Y');
            })
            ->editColumn('grand_total', function ($row) {
                return number_format($row->grand_total, 2);
            })
            ->editColumn('status', function ($row) {
                $status = strtolower($row->status ?? 'pending');
                return $status;
            })
            ->rawColumns(['status'])
            ->make(true);

        }
    

        // Determine allowed invoice routes
        $invoiceRoutes = [
            'sales_invoices.index',
            'sales_invoices.create',
            'sales_invoices.store',
            'sales_invoices.show',
            'sales_invoices.edit',
            'sales_invoices.update',
            'sales_invoices.destroy',
            'sales_invoices.user-invoice-get-mail-content',
            'sales_invoices.send-mail-to-admin'            
        ];

        try {
            $previousRoute = app('router')->getRoutes()->match(
                app('request')->create(url()->previous())
            )->getName();
        } catch (\Throwable $e) {
            $previousRoute = null;
        }

        $currentRoute = \Route::currentRouteName();

        return view('sales_invoices.index', compact('previousRoute', 'currentRoute', 'invoiceRoutes'));
    }

    public function create(Request $request)
    {
        $sales_invoice_access = SalesInvoiceAccess::where('sales_id',Auth::user()->id)->get()->pluck('client_id')->toArray(); 
        $clients = Client::where('workspace_id', Auth::user()->workspace_id)->whereIn('id',$sales_invoice_access)->orderBy('id', 'DESC')->get(['id', 'name']);
        $company_details = CompanyDetail::select('id', 'name')->where('workspace_id', Auth::user()->workspace_id)->orderBy('name')->get();
        $sales_people = User::query()
            ->whereHas(
                'workspaces',
                function ($query) {
                    $query->where('workspaces.id', Auth::user()->workspace_id);
                }
            )
            ->get([
                'id',
                'first_name',
                'last_name'
            ]);
        $currencies = Currency::select('id','symbol','name')->get();
        $invoice = [];
        $view_config['title'] = 'Create';
            
        return view('sales_invoices.create', compact( 'clients', 'sales_people', 'invoice', 'view_config', 'company_details','currencies'));
    }

    public function store(Request $request)
    {
        
        $valid = $request->validate([
            'currency_id' => 'required|exists:currencies,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'client_id' => 'required|exists:clients,id',
            'company_detail_id' => 'required|exists:company_details,id',
            'client_name' => 'nullable|string|max:255',

            'user_invoice_items' => 'required|array|min:1',
            'user_invoice_items.*.price' => 'required|numeric|min:0.01',
            'user_invoice_items.*.description' => 'required|string|max:1000',
            'user_invoice_items.*.tax_type' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {

            $sub_total = 0;
            $vat_total = 0;
            $grand_total = 0;

            foreach ($valid['user_invoice_items'] as $key => $invoice_item) {

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

                $valid['user_invoice_items'][$key]['tax_rate'] = $tax_rate;
                $valid['user_invoice_items'][$key]['tax_amount'] = $vat;
                $valid['user_invoice_items'][$key]['total_price'] = $total_price;
                $valid['user_invoice_items'][$key]['quantity'] = 1;
                $valid['user_invoice_items'][$key]['sequence'] = $key + 1;

                unset($valid['user_invoice_items'][$key]['total_amount']);
                unset($valid['user_invoice_items'][$key]['vat_amount']);
                unset($valid['user_invoice_items'][$key]['price_amount']);
            }

            $client = Client::where('id',$valid['client_id'])->first();

            $sales_invoice_number = SalesUserInvoice::generateNextSalesInvoiceNumber();
                        
            $invoice = new SalesUserInvoice();
            $invoice->sales_invoice_number  = $sales_invoice_number;
            $invoice->type                  = $request->invoice_type ?? '0';
            if($request->invoice_type == 1){
                $invoice->subscription_type     = $request->subscription_type ?? '0';
            }
            $invoice->client_id             = $valid['client_id'];
            $invoice->invoice_date          = DateTime::createFromFormat('d-m-Y', $valid['invoice_date']);
            $invoice->due_date              = DateTime::createFromFormat('d-m-Y', $valid['due_date']);
            $invoice->currency_id           = $valid['currency_id'];
            $invoice->company_detail_id     = $valid['company_detail_id'];
            $invoice->user_id               = Auth::user()->id;
            $invoice->sub_total             = $sub_total;
            $invoice->vat_total             = $vat_total;
            $invoice->grand_total           = $grand_total;
            $invoice->client_name           = $valid['client_name'];
            $invoice->status                = SalesInvoice::PENDING;
            $invoice->save();

            $invoice->user_invoice_items()->createMany($valid['user_invoice_items']);

            ActivityLogHelper::log(
                'sales_invoices.create',
                'Sales Invoice #'.$invoice->sales_invoice_number. ' created by '. Auth::user()->first_name.' '.Auth::user()->last_name .' invoice date : '. $invoice->invoice_date->format('d/m/Y') .' and due date : ' . $invoice->due_date->format('d/m/Y') . '.',
                [],
                $request,
                Auth::user(),
                $invoice
            );

            DB::commit();

            return response()->json([
                'success' => true
            ], 201);
        } catch (\Throwable $th) {
            DB::rollback();
            // throw $th;
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'error' => $th
            ], 500);
        }
    }

    public function show($user_invoice){
          // Decrypt if needed
            $id = \App\Helpers\EncryptionHelper::decrypt($user_invoice);
            $redirect_route = url()->previous();
            $slugs = explode ("/", $redirect_route);
            $latestslug = $slugs [(count ($slugs) - 2)];
            if($latestslug == 'edit'){
                $redirect_route = route('sales_invoices.index');
            }
            $invoice = SalesUserInvoice::with(['client', 'currency','sales_person']) // Add relationships as needed
                        ->findOrFail($id);
            
            return view('sales_invoices.show', compact('invoice','redirect_route'));
    }

    public function edit($user_invoice)
    {
        $id = \App\Helpers\EncryptionHelper::decrypt($user_invoice);
        $invoice = SalesUserInvoice::with('user_invoice_items')->findOrFail($id);
        $sales_invoice_access = SalesInvoiceAccess::where('sales_id',Auth::user()->id)->get()->pluck('client_id')->toArray(); 
        $clients = Client::whereIn('id',$sales_invoice_access)->get();
        $currencies = Currency::all();
        $company_details = CompanyDetail::select('id', 'name')->where('workspace_id', Auth::user()->workspace_id)->orderBy('name')->get();
        $sales_people = User::query()
            ->whereHas(
                'workspaces',
                function ($query) {
                    $query->where('workspaces.id', Auth::user()->workspace_id);
                }
            )
            ->get([
                'id',
                'first_name',
                'last_name'
            ]);
        $view_config['title'] = 'Update';
        return view('sales_invoices.edit', compact('invoice', 'clients', 'currencies','sales_people','company_details','view_config'));
    }

    public function update(Request $request, $user_invoice)
    {
        $id = \App\Helpers\EncryptionHelper::decrypt($user_invoice);
        $invoice = SalesUserInvoice::with('user_invoice_items')->findOrFail($id);

        $valid = $request->validate([
            'currency_id' => 'required|exists:currencies,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'client_id' => 'required|exists:clients,id',
            'company_detail_id' => 'required|exists:company_details,id',
            'client_name' => 'nullable|string|max:255',

            'user_invoice_items' => 'required|array|min:1',
            'user_invoice_items.*.price' => 'required|numeric|min:0.01',
            'user_invoice_items.*.description' => 'required|string|max:1000',
            'user_invoice_items.*.tax_type' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $sub_total = 0;
            $vat_total = 0;
            $grand_total = 0;

            foreach ($valid['user_invoice_items'] as $key => $invoice_item) {
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

                $valid['user_invoice_items'][$key]['tax_rate'] = $tax_rate;
                $valid['user_invoice_items'][$key]['tax_amount'] = $vat;
                $valid['user_invoice_items'][$key]['total_price'] = $total_price;
                $valid['user_invoice_items'][$key]['quantity'] = 1;
                $valid['user_invoice_items'][$key]['sequence'] = $key + 1;

                unset($valid['user_invoice_items'][$key]['total_amount']);
                unset($valid['user_invoice_items'][$key]['vat_amount']);
                unset($valid['user_invoice_items'][$key]['price_amount']);
            }


            // Update the invoice
            $updateData = [
                'type' => $request->invoice_type ?? $invoice->type,
                'client_id' => $valid['client_id'],
                'invoice_date' => DateTime::createFromFormat('d-m-Y', $valid['invoice_date']),
                'due_date' => DateTime::createFromFormat('d-m-Y', $valid['due_date']),
                'currency_id' => $valid['currency_id'],
                'company_detail_id' => $valid['company_detail_id'],
                'user_id' => Auth::user()->id,
                'sub_total' => $sub_total,
                'vat_total' => $vat_total,
                'grand_total' => $grand_total,
                'client_name' => $valid['client_name'],
                'status' => SalesInvoice::PENDING,
                'mail_send_at' => null,
            ];

            if ($request->invoice_type == 1) {
                $updateData['subscription_type'] = $request->subscription_type ?? '0';
            } else {
                $updateData['subscription_type'] = null;
            }

            $invoice->update($updateData);

            // Remove old items
            $invoice->user_invoice_items()->delete();

            // Insert new items
            $invoice->user_invoice_items()->createMany($valid['user_invoice_items']);

            ActivityLogHelper::log(
                'sales_invoices.update',
                'Sales Invoice #'.$invoice->sales_invoice_number. ' updated by '. Auth::user()->first_name.' '.Auth::user()->last_name .' invoice date : '. $invoice->invoice_date->format('d/m/Y') .' and due date : ' . $invoice->due_date->format('d/m/Y') . '.',
                [],
                $request,
                Auth::user(),
                $invoice
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully.',
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'error' => $th
            ], 500);
        }
    }


    public function destroy(Request $request,$user_invoice)
    {
        try {
            $id = \App\Helpers\EncryptionHelper::decrypt($user_invoice);
            $sales_invoice = SalesUserInvoice::where('id',$id)->first();
            $user_invoice_item = SalesUserInvoiceItem::where('sales_invoice_id',$id)->delete();
            if($user_invoice_item){
                $invoice = SalesUserInvoice::findOrFail($id);
                $invoice->delete();
            }

            ActivityLogHelper::log(
                'sales_invoices.destroy',
                'Invoice #' . $sales_invoice->sales_invoice_number .' deleted by ' . Auth::user()->first_name . ' ' . Auth::user()->last_name . 
                '. Invoice date: ' . Carbon::parse($sales_invoice->invoice_date)->format('d/m/Y') .
                ', Due date: ' . Carbon::parse($sales_invoice->due_date)->format('d/m/Y') . '.',
                [],
                $request,
                Auth::user(),
                $sales_invoice
            );

            return response()->json(['success' => true]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()
                ->json([
                    'success' => false,
                    'message' => $th->getMessage(),
                    'error' => $th
                ], 500);
        }
    }

    public function userInvoiceGetMailContent($user_invoice)
    {
        try {
            $id = \App\Helpers\EncryptionHelper::decrypt($user_invoice);
            $invoice = SalesUserInvoice::findOrFail($id);
            $workspaceName = (Auth::user()->active_workspace->slug === 'shalin-designs')
                ? 'Shalin Designs'
                : 'IIH Global';
                
            return response()->json([
                'subject' => "Invoice #{$invoice->sales_invoice_number} issued by " .  Auth::user()->first_name.' '.Auth::user()->last_name,
                "content" => view('sales_invoices.modals.sales-invoice-mail-content-template', ['invoice' => $invoice])->render()
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while sending mail!',
                'error' => $th
            ], 500);
        }
    }

    public function sendMailToAdmin(Request $request,$user_invoice)
    {
        $valid = $request->validate([
            'to' => 'required|array',
            'to.*' => 'required|email',
            'subject' => 'required|string|max:255',
            'content' => 'required'
        ]);

        try {
            $content = preg_replace('/<p>\s*<br\s*\/?>\s*<\/p>/i', '', nl2br($valid['content']));
            $id = \App\Helpers\EncryptionHelper::decrypt($user_invoice);
            $invoice = SalesUserInvoice::findOrFail($id);
            $invoice->status = SalesInvoice::MAIL_SEND;
            $invoice->mail_send_at = Carbon::today();
            $invoice->update();

            $email_signature = EmailSignature::find($id);

            Mail::mailer(config('mail.default', 'default'))
                ->to($valid['to'])
                ->send(new SalesUserInvoiceMail($valid['subject'], $content, Auth::user()->active_workspace->slug,$email_signature));

            ActivityLogHelper::log(
                'sales_invoices.mail-sent',
                'Mail sent by '. Auth::user()->first_name.' '.Auth::user()->last_name .' for Sales-Invoice #' . $invoice->sales_invoice_number,
                [],
                $request,
                Auth::user(),
                $invoice
            );

            return response()->json([
                'success' => true
            ], 200);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while sending mail!',
                'error' => $th
            ], 500);
        }
    }

}
