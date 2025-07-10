<?php

namespace App\Http\Controllers;

use App\Enums\IsInvoiceNew;
use App\Enums\SalesInvoice;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\StoreSubscriptionInvoiceRequest;
use App\Models\Client;
use App\Models\CompanyDetail;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\User;
use App\Models\SalesUserInvoice;
use App\Models\SalesUserInvoiceItem;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;
use App\Helpers\EncryptionHelper;
use App\Helpers\DateHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Helpers\ActivityLogHelper;
use DateTime;
use DateTimeZone;
use App\Models\Bank;

class SalesInvoiceController extends Controller
{
    private $service, $view_config;

    public function __construct(InvoiceService $service)
    {
        $this->service = $service;
        $this->view_config = [
            'title' => 'Create',
        ];
    }

    
    public function index(Request $request){
        if ($request->ajax()) {
            
            $results = SalesUserInvoice::with(['client', 'company_detail','currency'])
            ->whereHas('client', function ($q) {
                $q->where('workspace_id', Auth::user()->workspace_id);
            })
            ->where('status','!=',SalesInvoice::PENDING);

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
                    $status = strtolower($row->status ?? 'Mail send');
                    return $status;
                })
                ->rawColumns(['status'])
                ->make(true);
        }
    

        // Determine allowed invoice routes
        $invoiceRoutes = [
            'sales_invoice_index',
            'sales_invoice_create',
            'sales_invoice_store_one_off',
            'sales_invoice_store_sub',
            'sales_invoice_show',
            'sales_invoice_destroy'
        ];

        try {
            $previousRoute = app('router')->getRoutes()->match(
                app('request')->create(url()->previous())
            )->getName();
        } catch (\Throwable $e) {
            $previousRoute = null;
        }

        $currentRoute = \Route::currentRouteName();

        return view('admin_sales_invoices.index', compact('previousRoute', 'currentRoute', 'invoiceRoutes'));

    }

    public function show($user_invoice){
        // Decrypt if needed
        $id = EncryptionHelper::decrypt($user_invoice);
        $redirect_route = url()->previous();
        $slugs = explode ("/", $redirect_route);
        $latestslug = $slugs [(count ($slugs) - 2)];
        if($latestslug == 'admin-sales-invoice-create'){
            $redirect_route = route('sales_invoice_index');
        }

        $invoice = SalesUserInvoice::with(['client', 'currency']) // Add relationships as needed
                    ->findOrFail($id);
            
        return view('admin_sales_invoices.show', compact('invoice','redirect_route'));
    }

    public function edit($user_invoice)
    {
        $id = EncryptionHelper::decrypt($user_invoice);
        $invoice = SalesUserInvoice::with('user_invoice_items')->findOrFail($id);
        $clients = Client::where('workspace_id', Auth::user()->workspace_id)->orderBy('id', 'DESC')->get();
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
        $view_config['title'] = 'Create';
        $invoice_number = $this->service->new_number();

        $users = User::select('email','id')
                ->where('is_active','1')
                ->where('workspace_id',Auth::user()->workspace_id)
                ->where('is_invoice_access','1')
                ->get();

        return view('admin_sales_invoices.edit', compact('invoice_number','invoice', 'clients', 'currencies','sales_people','company_details','view_config','users'));
    }

    public function storeOneOff(StoreInvoiceRequest $request)
    {
        
        $valid = $request->validated();

        DB::beginTransaction();

        try {

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

                $valid['invoice_items'][$key]['tax_rate'] = $tax_rate;
                $valid['invoice_items'][$key]['tax_amount'] = $vat;
                $valid['invoice_items'][$key]['total_price'] = $total_price;
                $valid['invoice_items'][$key]['quantity'] = 1;
                $valid['invoice_items'][$key]['sequence'] = $key + 1;

                unset($valid['invoice_items'][$key]['total_amount']);
                unset($valid['invoice_items'][$key]['vat_amount']);
                unset($valid['invoice_items'][$key]['price_amount']);
            }

            $client = Client::where('id',$valid['client_id'])->first();

            $invoice = new Invoice();

            $invoice->client_id             = $valid['client_id'];
            $invoice->company_detail_id     = $valid['company_detail_id'];
            $invoice->client_name           = $valid['client_name'];
            $invoice->user_id               = $valid['user_id'] ?: null;
            $invoice->invoice_number        = $valid['invoice_number'];
            $invoice->currency_id           = $valid['currency_id'];
            $invoice->invoice_date          = DateTime::createFromFormat('d-m-Y', $valid['invoice_date']);
            $invoice->payment_link          = null;
            $invoice->payment_link_add_at   = null;
            if(isset($request->payment_link) && !empty($request->payment_link)){
                $invoice->payment_link          = $request->payment_link;
                $invoice->payment_link_add_at   = now();
            }
            $invoice->due_date              = DateTime::createFromFormat('d-m-Y', $valid['due_date']);
            $invoice->invoice_type          = config('custom.invoices_types.one-off', '0');
            $invoice->sub_total             = $sub_total;
            $invoice->vat_total             = $vat_total;
            $invoice->grand_total           = $grand_total;
            $invoice->is_new                = IsInvoiceNew::NEW;
            $company_detail_id              = intval($valid['company_detail_id']) ?? NULL;
            $bank_company_detail_map        = config('custom.bank_company_detail_map', []);
            $bank_company_detail_map        = !is_array($bank_company_detail_map) ? collect([]) : collect($bank_company_detail_map);
            $bank_company_detail            = $bank_company_detail_map->firstWhere('company_detail_id', $company_detail_id);
            $bank_detail_id                 = $bank_company_detail['bank_id'] ?? NULL;
            $bank_detail                    = Bank::find($bank_detail_id);
            $bank_detail                    = $bank_detail ?? Bank::where('currency_id', $valid['currency_id'])->orderByDesc('created_at')->first();
            $bank_detail                    = $bank_detail ?? Bank::where('is_default', true)->orderByDesc('created_at')->first();
            $invoice->bank_detail_id        = $bank_detail->id ?? NULL;
            $invoice->payment_reminder      = $client->payment_reminder ?? 0;

            $invoice->save();

            $invoice->invoice_items()->createMany($valid['invoice_items']);

            $sales_user_invoice = SalesUserInvoice::where('id',$request->sales_invoice_id)->first();
            SalesUserInvoice::where('id',$request->sales_invoice_id)->update([
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'status' => SalesInvoice::APPROVED,
            ]);

            $logs = [
                [
                    'event' => 'invoice.created',
                    'message' => 'Invoice #' . $valid['invoice_number'] . ' created by admin. Invoice Date: ' . $invoice->invoice_date->format('d/m/Y') . ', Due Date: ' . $invoice->due_date->format('d/m/Y') . '.',
                    'model' => $invoice
                ],
                [
                    'event' => 'invoice.created',
                    'message' => 'Invoice #' . $sales_user_invoice->sales_invoice_number . ' has been approved by the admin (#'. $valid['invoice_number'] .'). Invoice date: ' . Carbon::parse($sales_user_invoice->invoice_date)->format('d/m/Y') . ', Due date: ' . Carbon::parse($sales_user_invoice->due_date)->format('d/m/Y') . '.',
                    'model' => $invoice
                ]
            ];

            foreach ($logs as $log) {
                ActivityLogHelper::log(
                    $log['event'],
                    $log['message'],
                    [],
                    $request,
                    Auth::user(),
                    $log['model']
                );
            }

            DB::commit();

            $this->service->makeAndStorePDF($invoice);

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

      public function storeSub(StoreSubscriptionInvoiceRequest $request)
    {
        
        $valid = $request->validated();

        DB::beginTransaction();

        try {

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

                $valid['invoice_items'][$key]['tax_rate'] = $tax_rate;
                $valid['invoice_items'][$key]['tax_amount'] = $vat;
                $valid['invoice_items'][$key]['total_price'] = $total_price;
                $valid['invoice_items'][$key]['quantity'] = 1;
                $valid['invoice_items'][$key]['sequence'] = $key + 1;

                unset($valid['invoice_items'][$key]['total_amount']);
                unset($valid['invoice_items'][$key]['vat_amount']);
                unset($valid['invoice_items'][$key]['price_amount']);
            }

            $client = Client::where('id',$valid['client_id'])->first();

            $invoice = new Invoice;

            $invoice->client_id             = $valid['client_id'];
            $invoice->company_detail_id     = $valid['company_detail_id'];
            $invoice->client_name           = $valid['client_name'];
            $invoice->user_id               = $valid['user_id'] ?: null;
            $invoice->invoice_number        = $valid['invoice_number'];
            $invoice->currency_id           = $valid['currency_id'];
            $invoice->subscription_type     = $valid['subscription_type'];
            $invoice->payment_link          = null;
            $invoice->payment_link_add_at   = null;
            if(isset($request->payment_link) && !empty($request->payment_link)){
                $invoice->payment_link          = $request->payment_link;
                $invoice->payment_link_add_at   = now();
            }
            $invoice->subscription_status   = $valid['subscription_status'];
            $invoice->invoice_date          = DateTime::createFromFormat('d-m-Y', $valid['invoice_date']);
            $invoice->due_date              = DateTime::createFromFormat('d-m-Y', $valid['due_date']);
            $invoice->invoice_type          = config('custom.invoices_types.subscription', '1');
            $invoice->sub_total             = $sub_total;
            $invoice->vat_total             = $vat_total;
            $invoice->grand_total           = $grand_total;
            $invoice->is_new                = IsInvoiceNew::NEW;
            $company_detail_id              = intval($valid['company_detail_id']) ?? NULL;
            $bank_company_detail_map        = config('custom.bank_company_detail_map', []);
            $bank_company_detail_map        = !is_array($bank_company_detail_map) ? collect([]) : collect($bank_company_detail_map);
            $bank_company_detail            = $bank_company_detail_map->firstWhere('company_detail_id', $company_detail_id);
            $bank_detail_id                 = $bank_company_detail['bank_id'] ?? NULL;
            $bank_detail                    = Bank::find($bank_detail_id);
            $bank_detail                    = $bank_detail ?? Bank::where('currency_id', $valid['currency_id'])->orderByDesc('created_at')->first();
            $bank_detail                    = $bank_detail ?? Bank::where('is_default', true)->orderByDesc('created_at')->first();
            $invoice->bank_detail_id        = $bank_detail->id ?? NULL;
            $invoice->payment_reminder      = $client->payment_reminder ?? 0;

            $invoice->save();

            $invoice->invoice_items()->createMany($valid['invoice_items']);

            $sales_user_invoice = SalesUserInvoice::where('id',$request->sales_invoice_id)->first();
            SalesUserInvoice::where('id',$request->sales_invoice_id)->update([
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'status' => SalesInvoice::APPROVED
            ]);

             $logs = [
                [
                    'event' => 'invoice.created',
                    'message' => 'Invoice #' . $valid['invoice_number'] . ' created by admin invoice date : '. $invoice->invoice_date->format('d/m/Y') .' and due date : ' . $invoice->due_date->format('d/m/Y') . '.',
                    'model' => $invoice
                ],
                [
                    'event' => 'invoice.created',
                    'message' => 'Invoice #' . $sales_user_invoice->sales_invoice_number . ' has been approved by the admin (#'. $valid['invoice_number'] .'). Invoice date: ' . Carbon::parse($sales_user_invoice->invoice_date)->format('d/m/Y') . ', Due date: ' . Carbon::parse($sales_user_invoice->due_date)->format('d/m/Y') . '.',
                    'model' => $invoice
                ]
            ];

            foreach ($logs as $log) {
                ActivityLogHelper::log(
                    $log['event'],
                    $log['message'],
                    [],
                    $request,
                    Auth::user(),
                    $log['model']
                );
            }

            DB::commit();

            $this->service->makeAndStorePDF($invoice);

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

    public function destroy(Request $request,$user_invoice)
    {
        try {
            $id = EncryptionHelper::decrypt($user_invoice);
            $user_invoice_item = SalesUserInvoiceItem::where('sales_invoice_id',$id)->update([
                'delete_by_admin' => SalesInvoice::DELETED_BY_ADMIN,
            ]);
            $sales_invoice = SalesUserInvoice::where('id',$id)->first();
            if($user_invoice_item){
                SalesUserInvoice::where('id',$id)->update([
                    'delete_by_admin' => SalesInvoice::DELETED_BY_ADMIN,
                    'status' => SalesInvoice::REJECTED
                ]);
            }

            ActivityLogHelper::log(
                'sales_invoice_destroy',
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
}
