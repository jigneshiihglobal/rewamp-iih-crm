<?php

namespace App\Http\Controllers;

use App\Enums\InvoicePaymentStatus;
use App\Enums\InvoiceType;
use App\Enums\IsInvoiceNew;
use App\Exports\InvoicesBankExport;
use App\Exports\InvoicesExport;
use App\Exports\PaymentReceiptExport;
use App\Helpers\ActivityLogHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\DateHelper;
use App\Helpers\EncryptionHelper;
use App\Helpers\QuickBooksHelper;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\StoreSubscriptionInvoiceRequest;
use App\Http\Requests\UpdateOneOffInvoiceRequest;
use App\Http\Requests\UpdateSubscriptionInvoiceRequest;
use App\Mail\ClientInvoiceMail;
use App\Mail\PaymentReceiptMail;
use App\Mail\ManuallyPaymentReceiptMail;
use App\Mail\PaymentReceivedMail;
use App\Models\Bank;
use App\Models\Client;
use App\Models\CompanyDetail;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PaymentSource;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\QuickBooksInvoiceService;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    private $service, $view_config;

    public function __construct(InvoiceService $service)
    {
        $this->service = $service;
        $this->view_config = [
            'title' => 'Create',
        ];
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $invoices = Invoice::query()
                ->with([
                    'client:id,name',
                    'client.projectList:id,customer_id,project_id', // Load projectList from client,
                    'company_detail:id,name',
                    'currency:id,symbol',
                    'sales_person:id,first_name,last_name',
                    'credit_note:id,parent_invoice_id,grand_total,invoice_number',
                    'parent_invoice:id,invoice_number',
                    'invoice_notes:id,invoice_id',
                ])
                ->whereHas('client', function ($query) {
                    $query->where('clients.workspace_id', Auth::user()->workspace_id);
                })
                ->select(
                    "invoices.id",
                    "invoices.type",
                    "invoices.invoice_number",
                    "invoices.currency_id",
                    "invoices.invoice_type",
                    "invoices.subscription_type",
                    "invoices.grand_total",
                    "invoices.client_id",
                    "invoices.company_detail_id",
                    "invoices.invoice_date",
                    "invoices.deleted_at",
                    "invoices.user_id",
                    "invoices.payment_status",
                    "invoices.parent_invoice_id",
                    "invoices.subscription_status",
                    "invoices.is_new",
                    "invoices.payment_reminder",
                    "invoices.deleted_at",
                )
                ->withSum('payments', 'amount')
                ->when(
                    $request->filter_show_cancelled,
                    function ($q, $filter_show_cancelled) use ($request) {
                        if($filter_show_cancelled == "true"){
                            $q->withTrashed();
                        }
                        $request
                            ->session()
                            ->put(
                                'invoices_filter_show_cancelled',
                                $filter_show_cancelled
                            );
                    },
                    function ($q) use ($request) {
                        $request
                            ->session()
                            ->forget(
                                'invoices_filter_show_cancelled'
                            );
                    }
                )->when(
                    $request->filter_payment_source_id,
                    function ($q, $payment_source_id) use ($request) {
                        $q->whereHas('payments', function ($q) use ($payment_source_id) {
                            $q->where('payment_source_id', $payment_source_id);
                        });
                        $request
                            ->session()
                            ->put(
                                'invoices_filter_payment_source',
                                $payment_source_id
                            );
                    },
                    function ($q) use ($request) {
                        $request
                            ->session()
                            ->forget(
                                'invoices_filter_payment_source'
                            );
                    }
                )
                ->when(
                    $request->filter_client_id,
                    function ($q, $client_id) use ($request) {
                        $q->where('client_id', $client_id);
                        $request
                            ->session()
                            ->put(
                                'invoices_filter_client',
                                $client_id
                            );
                    },
                    function ($q) use ($request) {
                        $request
                            ->session()
                            ->forget(
                                'invoices_filter_client'
                            );
                    }
                )
                ->when(
                    $request->filter_company_id,
                    function ($q, $company_detail_id) use ($request) {
                        $q->where('company_detail_id', $company_detail_id);
                        $request
                            ->session()
                            ->put(
                                'invoices_filter_company',
                                $company_detail_id
                            );
                    },
                    function ($q) use ($request) {
                        $request
                            ->session()
                            ->forget(
                                'invoices_filter_company'
                            );
                    }
                )
                ->when(
                    $request->filter_company_id,
                    function ($q, $company_detail_id) {
                        $q->where('company_detail_id', $company_detail_id);
                    }
                )
                ->when(
                    $request->filter_payment,
                    function ($query, $payment_status) use ($request) {
                        if(in_array("1", $payment_status)){
                            if(count($payment_status) > 1){
                                $query->where(function ($q) use($payment_status){
                                    $q->whereIn(
                                        'invoices.payment_status',
                                        $payment_status
                                    )->orWhere(
                                        'invoices.type',
                                        InvoiceType::CREDIT_NOTE
                                    )->whereDoesntHave('credit_note');
                                });
                            }else{
                                $query->where(
                                    'type',
                                    InvoiceType::CREDIT_NOTE
                                );
                            }
                        }else{
                            if (in_array('paid', $payment_status)) {
                                $query->whereIn(
                                    'invoices.payment_status',
                                    $payment_status
                                )->where(
                                    'type',
                                    InvoiceType::INVOICE
                                )->whereDoesntHave('credit_note');
                            }else{
                                $query->whereIn(
                                    'invoices.payment_status',
                                    $payment_status
                                )->where(
                                    'type',
                                    InvoiceType::INVOICE
                                );
                            }
                        }
                        $request
                            ->session()
                            ->put(
                                'invoices_filter_status',
                                $payment_status
                            );
                    },
                    function ($q) use ($request) {
                        $request
                            ->session()
                            ->forget(
                                'invoices_filter_status'
                            );
                    }
                )
                ->when(
                    $request->filter_created_at,
                    function ($query, $created_at) use ($request) {
                        $request
                            ->session()
                            ->put(
                                'invoices_filter_created',
                                $created_at
                            );
                        $query
                            ->when($created_at == 'month', function ($query) {
                                $start = date('Y-m-d 00:00:00', strtotime("First day of this month"));
                                $end = date('Y-m-d 23:59:59', strtotime("Last day of this month"));

                                return $query->whereBetween('invoices.invoice_date', [$start, $end]);
                            })
                            ->when($created_at == 'last_month', function ($query) {
                                $start = date('Y-m-d 00:00:00', strtotime("First day of last month"));
                                $end = date('Y-m-d 23:59:59', strtotime("Last day of last month"));

                                return $query->whereBetween('invoices.invoice_date', [$start, $end]);
                            })
                            ->when($created_at == '3_months', function ($query) {
                                $start = date('Y-m-d 00:00:00', strtotime("First day of 3 months ago"));
                                $end = date('Y-m-d 23:59:59', strtotime("Last day of last month"));

                                return $query->whereBetween('invoices.invoice_date', [$start, $end]);
                            })
                            ->when($created_at == 'year', function ($query) {
                                $start = date('Y-01-01 00:00:00');
                                $end = date('Y-12-31 23:59:59');

                                return $query->whereBetween('invoices.invoice_date', [$start, $end]);
                            })
                            ->when(
                                $created_at == 'custom' && $request->filter_created_at_range,
                                function ($query) use ($request) {
                                    $filter_created_at_arr = explode(" to ", $request->filter_created_at_range);
                                    $start = date_create_from_format('d/m/Y H:i:s', $filter_created_at_arr[0] . " 00:00:00");
                                    $end = date_create_from_format('d/m/Y H:i:s', (isset($filter_created_at_arr[1]) ? $filter_created_at_arr[1] : $filter_created_at_arr[0]) . " 23:59:59");

                                    $request
                                        ->session()
                                        ->put(
                                            'invoices_filter_created_start',
                                            $start
                                        );
                                    $request
                                        ->session()
                                        ->put(
                                            'invoices_filter_created_end',
                                            $end
                                        );

                                    return $query->whereBetween('invoices.invoice_date', [$start, $end]);
                                },
                                function ($q) use ($request) {
                                    $request
                                        ->session()
                                        ->forget([
                                            'invoices_filter_created_start',
                                            'invoices_filter_created_end'
                                        ]);
                                }
                            );
                    },
                    function ($q) use ($request) {
                        $request
                            ->session()
                            ->forget(
                                'invoices_filter_created'
                            );
                    }
                );

            return DataTables::eloquent($invoices)
                ->with('session', $request->session()->all())
                ->editColumn(
                    'id',
                    function (Invoice $invoice) {
                        return EncryptionHelper::encrypt($invoice->id);
                    }
                )
                ->editColumn(
                    'invoice_date',
                    function (Invoice $invoice) {
                        return $invoice->invoice_date->setTimezone(Auth::user()->timezone)->format(DateHelper::INVOICE_LIST_CREATED_AT);
                    }
                )
                ->filterColumn(
                    'invoice_date',
                    function ($query, $keyword) {
                        $format = DateHelper::INVOICE_LIST_CREATED_AT_MYSQL;
                        $timezone_offset = DateHelper::getGmtOffsetFromTimezone(Auth::user()->timezone);
                        $query->orWhereRaw("DATE_FORMAT(CONVERT_TZ(invoices.invoice_date, '+00:00', '{$timezone_offset}'), '{$format}') like '%{$keyword}%'");
                    }
                )
                ->orderColumn('invoice_date', function ($query, $order) {
                    $timezone_offset = DateHelper::getGmtOffsetFromTimezone(Auth::user()->timezone);
                    $query
                        ->orderByRaw("DATE(CONVERT_TZ(invoices.invoice_date, '+00:00', '{$timezone_offset}')) {$order}")
                        ->orderBy("invoices.id", $order);
                })
                ->toJson();
        }

        $payment_sources = PaymentSource::where('workspace_id', Auth::user()->workspace_id)->orderBy('title', 'asc')->get(['id', 'title']);
        $clients = Client::where('workspace_id', Auth::user()->workspace_id)->orderBy('id', 'DESC')->get(['id', 'name']);
        $companies = CompanyDetail::where('workspace_id', Auth::user()->workspace_id)->get(['id', 'name']);

        /**
         * Get the previous route
         *
         * @author "Krunal Shrimali"
         */

        if($request->getHttpHost() == '122.170.107.160' || $request->getHttpHost() == '192.168.1.100'){
            // Get the previous URL
            $previousUrl =  url()->previous();

            $my_array=explode("/",$previousUrl,5);
            while (list ($key, $val) = each ($my_array)) {
                $path = "$val";
            }
            // Get the route name associated with the path
            $prevRoute = app('router')->getRoutes()->match(app('request')->create($path))->getName();
        }else{
            $prevRoute = app('router')
                ->getRoutes()
                ->match(
                    app('request')
                        ->create(
                            url()->previous()
                        )
                )
                ->getName();
        }

        /**
         * if user is coming from following routes then
         * remember filters from previous route,
         * else apply default filters
         *
         * @author "Krunal Shrimali"
         */
        $r = [
            'invoices.index',
            'invoices.one-off.create',
            'invoices.edit',
            'invoices.show',
            'invoices.subscription.create',
            'invoices.subscription.edit',
            'invoices.subscription.show',
            'invoices.credit_notes.create',
        ];

        $rememberFilters = in_array($prevRoute, $r);

        return view(
            'invoices.index',
            compact(
                'payment_sources',
                'clients',
                'companies',
                'rememberFilters'
            )
        );
    }

    public function createOneOff(Request $request)
    {
        $invoice = new Invoice;
        $view_config = [];
        if ($request->copy_invoice_id) {
            try {
                $decrypted_copy_invoice_id = EncryptionHelper::decrypt($request->copy_invoice_id);
                $copy_invoice = Invoice::withTrashed()->with([
                    'invoice_items:id,invoice_id,description,price,total_price,tax_type,tax_rate,tax_amount',
                    'client:id,name,country_id,address_line_1,address_line_2,city,email,zip_code',
                    // 'client.country:id,name',
                    'currency:id,symbol'
                ])->find($decrypted_copy_invoice_id);
                if ($copy_invoice) {
                    $invoice = $copy_invoice->replicate();
                    // $invoice->discount = null;
                    $invoice->invoice_date = null;
                    $invoice->due_date = null;
                    $invoice->copying_invoice = true;
                    $view_config['title'] = 'Copy';
                }
            } catch (\Throwable $th) {
            }
        }
        $invoice_number = $this->service->new_number();
        $clients = Client::where('workspace_id', Auth::user()->workspace_id)->orderBy('id', 'DESC')->get(['id', 'name']);
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

        return view('invoices.create-one-off', compact('invoice_number', 'clients', 'sales_people', 'invoice', 'view_config', 'company_details'));
    }

    public function createSub(Request $request)
    {
        $invoice = new Invoice;
        $view_config = [];
        if ($request->copy_invoice_id) {
            try {
                $decrypted_copy_invoice_id = EncryptionHelper::decrypt($request->copy_invoice_id);
                $copy_invoice = Invoice::withTrashed()->with([
                    'invoice_items:id,invoice_id,description,price,total_price,tax_type,tax_rate,tax_amount',
                    'client:id,name,country_id,address_line_1,address_line_2,city,email,zip_code',
                    'currency:id,symbol'
                ])->find($decrypted_copy_invoice_id);
                if ($copy_invoice) {
                    $invoice = $copy_invoice->replicate();
                    // $invoice->discount = null;
                    $invoice->invoice_date = null;
                    $invoice->due_date = null;
                    $invoice->subscription_status = null;
                    $invoice->copying_invoice = true;
                    $view_config['title'] = 'Copy';
                }
            } catch (\Throwable $th) {
            }
        }
        $invoice_number = $this->service->new_number();
        $clients = Client::where('workspace_id', Auth::user()->workspace_id)->orderBy('id', 'DESC')->get(['id', 'name']);
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

        return view('invoices.subscription.create', compact('invoice_number', 'clients', 'sales_people', 'invoice', 'view_config', 'company_details'));
    }

    /**
     * Store a new one-off invoice.
     * Stores new one-off invoice with all corresponding invoice items in database and calculate prices, taxes & discount.
     *
     * @author Krunal Shrimali
     * @param \App\Http\Requests\StoreInvoiceRequest $request;
     *
     * @return \Illuminate\Http\Response;
     */
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

            $invoice = new Invoice;

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
            /*$invoice->note                  = $valid['note'];*/
            /*$invoice->discount              = (float) $valid['discount'] ?? 0;*/
            $invoice->invoice_type          = config('custom.invoices_types.one-off', '0');
            $invoice->sub_total             = $sub_total;
            $invoice->vat_total             = $vat_total;
            //$invoice->grand_total           = $grand_total - (float) $valid['discount'];
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
            // $invoice->payment_status        = $valid['payment_status'];
            // $invoice->fully_paid_at        = $valid['payment_status'] === 'paid' ? now() : null;

            $invoice->save();

            $invoice->invoice_items()->createMany($valid['invoice_items']);

            QuickBooksHelper::refreshAccessToken(Auth::user()->id);
            QuickBooksInvoiceService::syncInvoiceToQuickBooks($invoice);

            ActivityLogHelper::log(
                'invoice.created',
                'Invoice #' . $valid['invoice_number'] . ' created by admin invoice date : '. $invoice->invoice_date->format('d/m/Y') .' and due date : ' . $invoice->due_date->format('d/m/Y') . '.',
                [],
                $request,
                Auth::user(),
                $invoice
            );
            // $newStatus = $valid['payment_status'];
            // $newStatusHl = Str::headline($newStatus);

            // if ($valid['payment_status'] != "unpaid") {
            //     $newStatus = $valid['payment_status'];
            //     ActivityLogHelper::log(
            //         'invoice.paid-invoice.created',
            //         'Newly created invoice #' . $invoice->invoice_number . " was marked as {$newStatusHl}",
            //         [
            //             'newStatus' => $newStatus,
            //         ],
            //         $request,
            //         Auth::user(),
            //         $invoice
            //     );
            // }

            DB::commit();

            // if ($valid['payment_status'] == 'paid') {
            //     $this->service->sendPaymentReceivedMail($invoice, 'full_payment_received');
            //     // $this->service->sendPaymentReceiptMail($invoice);
            // }

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
            /*$invoice->note                  = $valid['note'];*/
            /*$invoice->discount              = (float) $valid['discount'] ?? 0;*/
            $invoice->invoice_type          = config('custom.invoices_types.subscription', '1');
            $invoice->sub_total             = $sub_total;
            $invoice->vat_total             = $vat_total;
            //$invoice->grand_total           = $grand_total - (float) $valid['discount'];
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

            QuickBooksHelper::refreshAccessToken(Auth::user()->id);
            QuickBooksInvoiceService::syncInvoiceToQuickBooks($invoice);

            ActivityLogHelper::log(
                'invoice.subscription.created',
                'Invoice #' . $valid['invoice_number'] . ' created by admin invoice date : '. $invoice->invoice_date->format('d/m/Y') .' and due date : ' . $invoice->due_date->format('d/m/Y') . '.',
                [],
                $request,
                Auth::user(),
                $invoice
            );

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

    public function show(Request $request, Invoice $invoice)
    {
        $redirect_route = url()->previous();
        $slugs = explode ("/", $redirect_route);
        $latestslug = $slugs [(count ($slugs) - 1)];
        if($latestslug == 'edit'){
            $redirect_route = route('invoices.index');
        }

        $invoice->loadMissing([
            'invoice_items:id,invoice_id,description,price,total_price,tax_type,tax_rate,tax_amount',
            'client:id,name,country_id,address_line_1,address_line_2,city,email,zip_code',
            'client.country:id,name',
            'sales_person:id,first_name,last_name',
            'currency:id,symbol',
            'credit_note:id,invoice_number,grand_total,currency_id,parent_invoice_id',
            'credit_note.currency:id,symbol',
        ])
            ->loadSum('payments', 'amount');
        $email = explode(',',$invoice->client->email);
        $invoice->client->email =  $email[0];

        return view('invoices.show', compact('invoice','redirect_route'));
    }

    public function showSub(Request $request, Invoice $invoice)
    {
        $redirect_route = url()->previous();
        $slugs = explode ("/", $redirect_route);
        $latestslug = $slugs [(count ($slugs) - 1)];
        if($latestslug == 'edit'){
            $redirect_route = route('invoices.index');
        }

        $invoice->loadMissing([
            'invoice_items:id,invoice_id,description,price,total_price,tax_type,tax_rate,tax_amount',
            'client:id,name,country_id,address_line_1,address_line_2,city,email,zip_code',
            'client.country:id,name',
            'sales_person:id,first_name,last_name',
            'currency:id,symbol',
            'credit_note:id,invoice_number,grand_total,currency_id,parent_invoice_id',
            'credit_note.currency:id,symbol',
        ])
            ->loadSum('payments', 'amount');
        $email = explode(',',$invoice->client->email);
        $invoice->client->email =  $email[0];

        return view('invoices.subscription.show', compact('invoice','redirect_route'));
    }

    public function edit(Request $request, Invoice $invoice)
    {
        $invoice_number = $this->service->new_number();
        $clients = Client::where('workspace_id', Auth::user()->workspace_id)->orderBy('id', 'DESC')->get(['id', 'name']);
        $company_details = CompanyDetail::select('id', 'name')->where('workspace_id', Auth::user()->workspace_id)->orderBy('name')->get();
        $sales_people = User::query()->withTrashed()
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
        $invoice->loadMissing([
            'invoice_items:id,invoice_id,description,price,total_price,tax_type,tax_rate,tax_amount',
            'client:id,name,country_id,address_line_1,address_line_2,city,email,zip_code',
            'client.country:id,name',
            'currency:id,symbol'
        ])
            ->loadSum('payments', 'amount');

        $view_config = $this->view_config;
        $view_config['title'] = 'Edit';

        return view('invoices.create-one-off', compact('invoice_number', 'clients', 'invoice', 'sales_people', 'view_config', 'company_details'));
    }
    public function editSub(Request $request, Invoice $invoice)
    {
        $invoice_number = $this->service->new_number();
        $clients = Client::where('workspace_id', Auth::user()->workspace_id)->orderBy('id', 'DESC')->get(['id', 'name']);
        $company_details = CompanyDetail::select('id', 'name')->where('workspace_id', Auth::user()->workspace_id)->orderBy('name')->get();
        $sales_people = User::query()->withTrashed()
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
        $invoice->loadMissing([
            'invoice_items:id,invoice_id,description,price,total_price,tax_type,tax_rate,tax_amount',
            'client:id,name,country_id,address_line_1,address_line_2,city,email,zip_code',
            'client.country:id,name',
            'currency:id,symbol'
        ])
            ->loadSum('payments', 'amount');

        $view_config = $this->view_config;
        $view_config['title'] = 'Edit';

        return view('invoices.subscription.create', compact('invoice_number', 'clients', 'invoice', 'sales_people', 'view_config', 'company_details'));
    }

    public function update(UpdateOneOffInvoiceRequest $request, Invoice $invoice)
    {
        $valid = $request->validated();
        if ($invoice->payment_status == InvoicePaymentStatus::UNPAID) {
            $invoice->loadMissing([
                'invoice_items:id,invoice_id,created_at,updated_at',
            ]);
        }

        DB::beginTransaction();

        try {

            if ($invoice->payment_status == InvoicePaymentStatus::UNPAID) {
                // $oldStatus = $invoice->payment_status;
                $sub_total = 0;
                $vat_total = 0;
                $grand_total = 0;
                $now = now();

                foreach ($valid['invoice_items'] as $key => $invoice_item) {

                    $vat = 0;
                    $tax_rate = 0;
                    $price = (float) $invoice_item['price'];
                    if ($invoice_item['id']) {
                        $existing_invoice_item = $invoice->invoice_items->find($invoice_item['id']);
                    }

                    if (array_key_exists('tax_type', $invoice_item) && $invoice_item['tax_type'] === 'vat_20') {
                        $tax_rate = 20;
                        $vat = (($price) * $tax_rate) / 100;
                    } else {
                        $valid['invoice_items'][$key]['tax_type'] = 'no_vat';
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
                    $valid['invoice_items'][$key]['created_at'] = $invoice_item['id'] && $existing_invoice_item ? $existing_invoice_item['created_at'] : $now;
                    $valid['invoice_items'][$key]['updated_at'] = $now;
                    $valid['invoice_items'][$key]['invoice_id'] = $invoice->id;

                    unset($valid['invoice_items'][$key]['total_amount']);
                    unset($valid['invoice_items'][$key]['vat_amount']);
                    unset($valid['invoice_items'][$key]['price_amount']);
                }
            }
            $invoice_old = $invoice->getAttributes();
            $invoice->client_id             = $valid['client_id'];
            $invoice->company_detail_id     = $valid['company_detail_id'];
            $invoice->client_name           = $valid['client_name'];
            $invoice->user_id               = $valid['user_id'] ?: null;
            $invoice->invoice_number        = $valid['invoice_number'];
            $invoice->currency_id           = $valid['currency_id'];
            $invoice->invoice_date          = DateTime::createFromFormat('d-m-Y',  $valid['invoice_date']);
            $invoice->due_date              = DateTime::createFromFormat('d-m-Y', $valid['due_date']);
            /*$invoice->note                  = $valid['note'];*/
            if ($invoice->payment_status == InvoicePaymentStatus::UNPAID) {
                /*$invoice->discount              = (float) $valid['discount'] ?? 0;*/
                $invoice->sub_total             = $sub_total;
                $invoice->vat_total             = $vat_total;
                //$invoice->grand_total           = $grand_total - (float) $valid['discount'];
                $invoice->grand_total           = $grand_total;
            }
            // $invoice->payment_status        = $valid['payment_status'];
            // $invoice->fully_paid_at         = $oldStatus != 'paid' && $valid['payment_status'] === 'paid' ? now() : $invoice->fully_paid_at;
            $company_detail_id              = intval($valid['company_detail_id']) ?? NULL;
            $bank_company_detail_map        = config('custom.bank_company_detail_map', []);
            $bank_company_detail_map        = !is_array($bank_company_detail_map) ? collect([]) : collect($bank_company_detail_map);
            $bank_company_detail            = $bank_company_detail_map->firstWhere('company_detail_id', $company_detail_id);
            $bank_detail_id                 = $bank_company_detail['bank_id'] ?? NULL;
            $bank_detail                    = Bank::find($bank_detail_id);
            $bank_detail                    = $bank_detail ?? Bank::where('currency_id', $valid['currency_id'])->orderByDesc('created_at')->first();
            $bank_detail                    = $bank_detail ?? Bank::where('is_default', true)->orderByDesc('created_at')->first();
            $invoice->bank_detail_id        = $bank_detail->id ?? NULL;

            $invoice->save();

            if ($invoice->payment_status == InvoicePaymentStatus::UNPAID) {
                $invoice_items_to_keep = array_column($valid['invoice_items'], 'id');
                foreach ($invoice->invoice_items as $invoice_item) {
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
            }

            $differences = [];
            foreach ($valid as $key => $value) {
                /* Invoice date */
                $invoice_date              = DateTime::createFromFormat('d-m-Y',  $valid['invoice_date']);
                $new_invoice_date          = $invoice_date->format('Y-m-d');
                $formatted_invoice_date    = Carbon::parse($invoice_old['invoice_date'])->format('Y-m-d');

                /* Due Date */
                $due_date                  = DateTime::createFromFormat('d-m-Y', $valid['due_date']);
                $new_due_date              = $due_date->format('Y-m-d');
                $formatted_due_date        = Carbon::parse($invoice_old['due_date'])->format('Y-m-d');

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


                    }elseif($key == "due_date" && $new_due_date != $formatted_due_date){
                        /* Old Due date With New Due date Get */
                        $differences[$key] = "$formatted_due_date => $new_due_date";

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

            QuickBooksHelper::refreshAccessToken(Auth::user()->id);
            QuickBooksInvoiceService::syncInvoiceToQuickBooks($invoice);

            ActivityLogHelper::log(
                'invoice.updated',
                'Invoice #' . $invoice->invoice_number . ' updated by admin' . $logDescription . '.',
                [],
                $request,
                Auth::user(),
                $invoice
            );

            // if ($valid['payment_status'] != $oldStatus) {
            //     $newStatus = $valid['payment_status'];
            //     $newStatusHl = Str::headline($newStatus);
            //     $oldStatusHl = Str::headline($oldStatus);
            //     ActivityLogHelper::log(
            //         'invoice.payment-status.updated',
            //         'Invoice #' . $invoice->invoice_number . "'s payment status updated from {$oldStatusHl} to {$newStatusHl}",
            //         [
            //             'previousStatus' => $oldStatus,
            //             'newStatus' => $newStatus,
            //         ],
            //         $request,
            //         Auth::user(),
            //         $invoice
            //     );
            // }

            DB::commit();

            // if ($valid['payment_status'] === 'paid' && $valid['payment_status'] != $oldStatus) {
            //     $this->service->sendPaymentReceivedMail($invoice, 'full_payment_received');
            //     // $this->service->sendPaymentReceiptMail($invoice);
            // }

            $this->service->makeAndStorePDF($invoice);
            if ($invoice->payment_status != InvoicePaymentStatus::UNPAID) $this->service->makeAndStorePDF($invoice, 'payment_receipt');

            return response()->json([
                'success' => true
            ], 201);
        } catch (\Throwable $th) {
            DB::rollback();
            dd('controller',$th);
            // throw $th;
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'error' => $th
            ], 500);
        }
    }
    public function updateSub(UpdateSubscriptionInvoiceRequest $request, Invoice $invoice)
    {
        $valid = $request->validated();
        if ($invoice->payment_status == InvoicePaymentStatus::UNPAID) {
            $invoice->loadMissing([
                'invoice_items:id,invoice_id,created_at,updated_at',
            ]);
        }

        DB::beginTransaction();

        try {

            if ($invoice->payment_status == InvoicePaymentStatus::UNPAID) {
                $sub_total = 0;
                $vat_total = 0;
                $grand_total = 0;
                $now = now();

                foreach ($valid['invoice_items'] as $key => $invoice_item) {

                    $vat = 0;
                    $tax_rate = 0;
                    $price = (float) $invoice_item['price'];
                    if ($invoice_item['id']) {
                        $existing_invoice_item = $invoice->invoice_items->find($invoice_item['id']);
                    }

                    if (array_key_exists('tax_type', $invoice_item) && $invoice_item['tax_type'] === 'vat_20') {
                        $tax_rate = 20;
                        $vat = (($price) * $tax_rate) / 100;
                    } else {
                        $valid['invoice_items'][$key]['tax_type'] = 'no_vat';
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
                    $valid['invoice_items'][$key]['created_at'] = $invoice_item['id'] && $existing_invoice_item ? $existing_invoice_item['created_at'] : $now;
                    $valid['invoice_items'][$key]['updated_at'] = $now;
                    $valid['invoice_items'][$key]['invoice_id'] = $invoice->id;

                    unset($valid['invoice_items'][$key]['total_amount']);
                    unset($valid['invoice_items'][$key]['vat_amount']);
                    unset($valid['invoice_items'][$key]['price_amount']);
                }
            }
            $invoice_old = $invoice->getAttributes();
            $invoice->client_id             = $valid['client_id'];
            $invoice->company_detail_id     = $valid['company_detail_id'];
            $invoice->client_name           = $valid['client_name'];
            $invoice->user_id               = $valid['user_id'] ?: null;
            $invoice->invoice_number        = $valid['invoice_number'];
            $invoice->currency_id           = $valid['currency_id'];
            $invoice->subscription_type     = $valid['subscription_type'];
            $invoice->subscription_status   = $valid['subscription_status'];
            $invoice->invoice_date          = DateTime::createFromFormat('d-m-Y',  $valid['invoice_date']);
            $invoice->due_date              = DateTime::createFromFormat('d-m-Y', $valid['due_date']);
            /*$invoice->note                  = $valid['note'];*/
            if ($invoice->payment_status == InvoicePaymentStatus::UNPAID) {
                /*$invoice->discount              = (float) $valid['discount'] ?? 0;*/
                $invoice->sub_total             = $sub_total;
                $invoice->vat_total             = $vat_total;
                //$invoice->grand_total           = $grand_total - (float) $valid['discount'];
                $invoice->grand_total           = $grand_total;
            }
            $company_detail_id              = intval($valid['company_detail_id']) ?? NULL;
            $bank_company_detail_map        = config('custom.bank_company_detail_map', []);
            $bank_company_detail_map        = !is_array($bank_company_detail_map) ? collect([]) : collect($bank_company_detail_map);
            $bank_company_detail            = $bank_company_detail_map->firstWhere('company_detail_id', $company_detail_id);
            $bank_detail_id                 = $bank_company_detail['bank_id'] ?? NULL;
            $bank_detail                    = Bank::find($bank_detail_id);
            $bank_detail                    = $bank_detail ?? Bank::where('currency_id', $valid['currency_id'])->orderByDesc('created_at')->first();
            $bank_detail                    = $bank_detail ?? Bank::where('is_default', true)->orderByDesc('created_at')->first();
            $invoice->bank_detail_id        = $bank_detail->id ?? NULL;

            $invoice->save();

            if ($invoice->payment_status == InvoicePaymentStatus::UNPAID) {
                $invoice_items_to_keep = array_column($valid['invoice_items'], 'id');
                foreach ($invoice->invoice_items as $invoice_item) {
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
            }

            $differences = [];
            foreach ($valid as $key => $value) {
                /* Invoice date */
                $invoice_date              = DateTime::createFromFormat('d-m-Y',  $valid['invoice_date']);
                $new_invoice_date          = $invoice_date->format('Y-m-d');
                $formatted_invoice_date    = Carbon::parse($invoice_old['invoice_date'])->format('Y-m-d');

                /* Due Date */
                $due_date                  = DateTime::createFromFormat('d-m-Y', $valid['due_date']);
                $new_due_date              = $due_date->format('Y-m-d');
                $formatted_due_date        = Carbon::parse($invoice_old['due_date'])->format('Y-m-d');

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

                    }elseif($key == "due_date" && $new_due_date != $formatted_due_date){
                        /* Old Due date With New Due date Get */
                        $differences[$key] = "$formatted_due_date => $new_due_date";

                    }elseif($key == "company_detail_id" && $company_detail_id != $invoice_old[$key]){
                        /* Old Company Name With New Company Name Get */
                        $company_name = CompanyDetail::select('name')->where('id',$invoice_old[$key])->where('workspace_id', Auth::user()->workspace_id)->first();
                        $company_name_new = CompanyDetail::select('name')->where('id',$value)->where('workspace_id', Auth::user()->workspace_id)->first();
                        $differences[$key] = (isset($company_name->name) && !empty($company_name->name) ? $company_name->name : '') . ' => ' . (isset($company_name_new->name) && !empty($company_name_new->name) ? $company_name_new->name : '');

                    }elseif($key == "subscription_type" && $value != $invoice_old[$key]){
                        /* New Sub type value */
                        $sub_type = '';
                        if($value == '0'){
                            $sub_type = 'Monthly';
                        }elseif ($value == '1'){
                            $sub_type = 'Yearly';
                        }
                         /*Old Sub type value */
                        $old_sub_type = '';
                        if($invoice_old[$key] == 0){
                            $old_sub_type = 'Monthly';
                        }elseif ($invoice_old[$key] == 1){
                            $old_sub_type = 'Yearly';
                        }
                        /* Old subscription type With New subscription type Monthly or Yearly*/
                        $differences[$key] = (isset($old_sub_type) && $old_sub_type != null ? $old_sub_type : '') . ' => ' . (isset($sub_type) && $sub_type != null ? $sub_type : '');

                    }elseif($key == "subscription_status" && $value != $invoice_old[$key]){
                        /* Old subscription status With New subscription status */
                        $differences[$key] = (isset($invoice_old[$key]) && $invoice_old[$key] != null ? $invoice_old[$key] : '') . ' => ' . (isset($value) && $value != null ? $value : '');
                    }
                }
            }

            $logDescription = '';
            if(isset($differences) && !empty($differences)){
                $logDescription = ' Changes: ' . implode(', ', $differences);
            }

            QuickBooksHelper::refreshAccessToken(Auth::user()->id);
            QuickBooksInvoiceService::syncInvoiceToQuickBooks($invoice);

            ActivityLogHelper::log(
                'invoice.subscription.updated',
                'Invoice #' . $invoice->invoice_number . ' updated by admin' . $logDescription . '.',
                [],
                $request,
                Auth::user(),
                $invoice
            );

            DB::commit();

            $this->service->makeAndStorePDF($invoice);
            if ($invoice->payment_status != InvoicePaymentStatus::UNPAID) $this->service->makeAndStorePDF($invoice, 'payment_receipt');

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

    public function preview(Invoice $invoice, Request $request)
    {
        return $this->service->stream($invoice, $request->input('type', 'invoice'));
    }

    public function sendMailToClient(Request $request, Invoice $invoice)
    {
        $valid = $request->validate([
            'to' => 'required|array',
            'to.*' => 'required|email',
            'bcc' => 'required|array',
            'bcc.*' => 'required|email',
            'subject' => 'required|string|max:255',
            'content' => 'required'
        ]);

        try {
            $fileName = $this->service->pdf_name($invoice->invoice_number, 'invoice', $invoice->client->workspace->slug ?? 'iih-global');

            Mail::mailer(config('mail.accounts_mail_mailer', 'accounts_smtp'))
                ->to($valid['to'])
                ->bcc($valid['bcc'])
                ->send(new ClientInvoiceMail($valid['subject'], $valid['content'], $this->service->pdfOutput($invoice), $fileName, Auth::user()->active_workspace->slug));

            ActivityLogHelper::log(
                'invoice.mail-sent',
                'Mail sent to customer for Invoice #' . $invoice->invoice_number,
                [],
                $request,
                Auth::user(),
                $invoice
            );

            // $invoice->update(['payment_reminder_sent_at' => now()]);

            $updateValues = [
                'payment_reminder_sent_at' => now(),
            ];

            if ($invoice->is_new != IsInvoiceNew::OLD) {
                $updateValues['is_new'] = IsInvoiceNew::OLD;
            }

            $invoice->update($updateValues);

            // $client = $invoice->client;

            // if ($client && $client->plant_a_tree) {
            //     DB::transaction(function () use ($client) {
            //         MoreTreeHistory::create(["client_id" => $client->id]);
            //         $client->update(['plant_a_tree' => false]);
            //     });
            // }

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

    public function getMailContent(Request $request, Invoice $invoice)
    {
        try {
            if (Auth::user()->active_workspace->slug === 'shalin-designs') {
                $bcc = config('shalin-designs.accounts_mail.bcc', []);
            }else{
                $bcc = config('mail.accounts_mail_bcc', []);
            }

            if ($invoice->sales_person && $invoice->sales_person->email) {
                array_push($bcc, $invoice->sales_person->email);
            }

            $workspaceName = (Auth::user()->active_workspace->slug === 'shalin-designs')
                ? 'Shalin Designs'
                : 'IIH Global';

            $email = explode(',',$invoice->client->email);

            return response()->json([
                'to' => $email,
                'bcc' => $bcc,
                'subject' => "Invoice #{$invoice->invoice_number} issued by " .  $workspaceName,
                "content" => view('invoices.modals.mail-content-template', ['invoice' => $invoice])->render()
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

    public function getSalesStatistics(Request $request)
    {
        $salesArr = [
            "this_month_w_vat" => 0,
            "this_month_wo_vat" => 0,
            "last_month_w_vat" => 0,
            "last_month_wo_vat" => 0,
        ];

        try {

            $firstDayOfLastMonth = date('Y-m-d 00:00:00', strtotime("First day of last month"));
            $lastDayOfLastMonth = date('Y-m-d 23:59:59', strtotime("Last day of last month"));
            $firstDayOfThisMonth = date('Y-m-d 00:00:00', strtotime("First day of this month"));
            $lastDayOfThisMonth = date('Y-m-d 23:59:59', strtotime("Last day of this month"));

            $last2MonthInvoices = Invoice::query()
                ->select(
                    'invoices.id',
                    'invoices.currency_id',
                    'invoices.grand_total',
                    'invoices.sub_total',
                    'invoices.invoice_date',
                    /*'invoices.discount',*/
                )
                ->with([
                    'currency:id,code',
                    'credit_note:id,grand_total,sub_total,parent_invoice_id,currency_id',
                    'credit_note.currency:id,code',
                ])
                ->where('type', InvoiceType::INVOICE)
                ->where('invoices.invoice_date', ">=", $firstDayOfLastMonth)
                ->where('invoices.invoice_date', "<=", $lastDayOfThisMonth)
                ->whereHas('client', function ($q) {
                    $q->where('clients.workspace_id', Auth::user()->workspace_id);
                })
                ->get();

            $last2MonthInvoices->each(
                function ($invoice, $key)
                use ($firstDayOfLastMonth, $lastDayOfLastMonth, $firstDayOfThisMonth, $lastDayOfThisMonth, &$salesArr) {

                    $currency_name = $invoice['currency']['code'];
                    $invoice_date = $invoice['invoice_date'];
                    $currencyGbpRates = CurrencyHelper::convert($currency_name, config('custom.statistics_currency'),$invoice_date);

                    $cn_grand_total = 0;
                    $cn_sub_total = 0;

                    if ($invoice->credit_note) {
                        $cn = $invoice->credit_note;
                        $cn_cur_rate = $currencyGbpRates->base_currency_rate ?? 1;

                        $cn_grand_total = (float) ($cn->grand_total ?? 0);
                        $cn_sub_total = (float) ($cn->sub_total ?? 0);

                        $cn_grand_total = $cn_grand_total * $cn_cur_rate;
                        $cn_sub_total = $cn_sub_total * $cn_cur_rate;
                    }

                    if ($invoice
                        ->invoice_date
                        ->between($firstDayOfLastMonth, $lastDayOfLastMonth)
                    ) {
                        $salesArr['last_month_w_vat'] += $invoice->grand_total * $currencyGbpRates->base_currency_rate;
                        //$salesArr['last_month_wo_vat'] += ($invoice->sub_total - ($invoice->discount ?? 0)) * $currencyGbpRates[$invoice->currency->code];
                        $salesArr['last_month_wo_vat'] += ($invoice->sub_total) * $currencyGbpRates->base_currency_rate;

                        $salesArr['last_month_w_vat'] -= $cn_grand_total;
                        $salesArr['last_month_wo_vat'] -= $cn_sub_total;
                    } else if ($invoice
                        ->invoice_date
                        ->between($firstDayOfThisMonth, $lastDayOfThisMonth)
                    ) {
                        $salesArr['this_month_w_vat'] += $invoice->grand_total * $currencyGbpRates->base_currency_rate;
                        //$salesArr['this_month_wo_vat'] += ($invoice->sub_total - ($invoice->discount ?? 0)) * $currencyGbpRates[$invoice->currency->code];
                        $salesArr['this_month_wo_vat'] += ($invoice->sub_total) * $currencyGbpRates->base_currency_rate;

                        $salesArr['this_month_w_vat'] -= $cn_grand_total;
                        $salesArr['this_month_wo_vat'] -= $cn_sub_total;
                    }
                }
            );

            $salesArr['currency_symbol'] = Currency::where('code', config('custom.statistics_currency', 'GBP'))->first()->symbol ?? "£";

            return response()->json($salesArr);
        } catch (\Throwable $th) {
            // throw $th;
            Log::info($th);
            return response()
                ->json(
                    [
                        'error' => $th,
                        'message' => "Something went wrong while fetching sales statistics!",
                        'success' => false
                    ],
                    500
                );
        }
    }

    public function export(Request $request)
    {
        $invoicesExport = new InvoicesExport();

        Log::info($request->all());

        if ($request->filter_payment_source_id) {
            $invoicesExport->payment_source_id((int)$request->filter_payment_source_id);
        }

        if ($request->filter_client_id) {
            $invoicesExport->client_id((int)$request->filter_client_id);
        }

        if ($request->filter_company_id) {
            $invoicesExport->company_detail_id((int)$request->filter_company_id);
        }

        if ($request->filter_payment) {
            $statuses = explode(',', $request->filter_payment);
            $invoicesExport->payment_statuses($statuses);
        }

        switch ($request->filter_created_at) {
            case 'custom':
                $filter_created_at_arr = explode(
                    " to ",
                    $request->filter_created_at_range
                );
                $start = date_create_from_format(
                    'd/m/Y H:i:s',
                    $filter_created_at_arr[0] . " 00:00:00",
                    new DateTimeZone(Auth::user()->timezone)
                );
                $end = date_create_from_format(
                    'd/m/Y H:i:s',
                    (isset($filter_created_at_arr[1])
                        ? $filter_created_at_arr[1]
                        : $filter_created_at_arr[0]
                    ) . " 23:59:59",
                    new DateTimeZone(Auth::user()->timezone)
                );
                $invoicesExport
                    ->invoice_date_greater_than($start)
                    ->invoice_date_less_than($end);
                break;
            case  'month':
                $start = new DateTime(
                    "First day of this month",
                    new DateTimeZone(Auth::user()->timezone)
                );
                $end = new DateTime(
                    "Last day of this month",
                    new DateTimeZone(Auth::user()->timezone)
                );
                $invoicesExport
                    ->invoice_date_greater_than($start)
                    ->invoice_date_less_than($end);
                break;
            case 'last_month':
                $start = new DateTime(
                    "First day of last month",
                    new DateTimeZone(Auth::user()->timezone)
                );
                $end = new DateTime(
                    "Last day of last month",
                    new DateTimeZone(Auth::user()->timezone)
                );
                $invoicesExport
                    ->invoice_date_greater_than($start)
                    ->invoice_date_less_than($end);
                break;
            case '3_months':
                $start = new DateTime(
                    "First day of 3 months ago",
                    new DateTimeZone(Auth::user()->timezone)
                );
                $end = new DateTime(
                    "Last day of last month",
                    new DateTimeZone(Auth::user()->timezone)
                );
                $invoicesExport
                    ->invoice_date_greater_than($start)
                    ->invoice_date_less_than($end);
                break;
            case 'year':
                $year = date('Y');
                $start = new DateTime(
                    "{$year}/01/01 00:00:00",
                    new DateTimeZone(Auth::user()->timezone)
                );
                $end = new DateTime(
                    "{$year}/12/31 23:59:59",
                    new DateTimeZone(Auth::user()->timezone)
                );
                $invoicesExport
                    ->invoice_date_greater_than($start)
                    ->invoice_date_less_than($end);
                break;

            default:
                break;
        }

        $invoicesExport
            ->workspace(Auth::user()->workspace_id);

        return $invoicesExport->download('invoices.xls', Excel::XLS);
    }

    public function getReceiptMailContent(Request $request, Invoice $invoice)
    {
        try {
            if (Auth::user()->active_workspace->slug === 'shalin-designs') {
                $bcc = config('shalin-designs.accounts_mail.bcc', []);
            }else{
                $bcc = config('mail.accounts_mail_bcc', []);
            }

            if ($invoice->sales_person && $invoice->sales_person->email) {
                array_push($bcc, $invoice->sales_person->email);
            }

            $workspaceName = (Auth::user()->active_workspace->slug === 'shalin-designs')
                ? 'Shalin Designs'
                : 'IIH Global';

            $payment_receipt = "Payment Receipt ".$invoice->invoice_number.".pdf";

            $email = explode(',',$invoice->client->email);

            if (Auth::user()->active_workspace->slug === 'shalin-designs') {
                return response()->json([
                    'receipt_to' => $email,
                    'receipt_bcc' => $bcc,
                    'subject' => "Payment Receipt - {$invoice->invoice_number}",
                    'attachment_receipt' => $payment_receipt,
                    "content" => view('invoices.modals.shalin-payment-receipt-mail', ['invoice' => $invoice])->render()
                ]);
            }else{
                return response()->json([
                    'receipt_to' => $email,
                    'receipt_bcc' => $bcc,
                    'subject' => "Payment Receipt - {$invoice->invoice_number}",
                    'attachment_receipt' => $payment_receipt,
                    "content" => view('invoices.modals.iih-payment-receipt-mail', ['invoice' => $invoice])->render()
                ]);
            }
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while sending mail!',
                'error' => $th
            ], 500);
        }
    }

    public function sendReceiptMail(Request $request, Invoice $invoice)
    {
        try {

            $valid = $request->validate([
                'receipt_to' => 'required|array',
                'receipt_to.*' => 'required|email',
                'receipt_bcc' => 'required|array',
                'receipt_bcc.*' => 'required|email',
                'receipt_subject' => 'required|string|max:255',
                'receiptContent' => 'required'
            ]);

            $custom_attach = $request->custom_attach;
            $receiptFile = $request->payment_receipt;
            $receipt_subject = $request->receipt_subject;
            $receiptContent = $request->receiptContent;

            if(isset($custom_attach) && !empty($custom_attach)){
                foreach ($custom_attach as $key => $attach_file){
                    $fileName = $attach_file->getClientOriginalName();
                    // Move the file to the dynamically created folder
                    $attach_file->storeAs('invoice/' . $invoice->id, $fileName, 'public');
                }
            }

            Mail::mailer(config('mail.accounts_mail_mailer', 'accounts_smtp'))
                ->to($valid['receipt_to'])
                ->bcc($valid['receipt_bcc'])
                ->send(new ManuallyPaymentReceiptMail($invoice,$custom_attach,$receiptFile,$receipt_subject,$receiptContent));

            ActivityLogHelper::log(
                'invoices.payment_received.mail-sent-to-customer',
                "Mail sent manually for payment Receipt (Thank you for payment on Invoice #{$invoice->invoice_number}) to {$invoice->client_name}",
                [],
                request(),
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

    public function paymentReminder(Request $request){
        try {
            $invoice_id = EncryptionHelper::decrypt($request->invoice_id);
            if(!$invoice_id){
                $invoice_id = $request->invoice_id;
            }
            $payment_reminder['payment_reminder'] = isset($request->reminder_status) && $request->reminder_status == 1 ? 0 : 1;
            Invoice::where('id',$invoice_id)->update($payment_reminder);
            $invoice = Invoice::where('id',$invoice_id)->first();

            $message = 'Payment reminder enable successfully!';
            $flag_val = '<a class="btn btn-sm btn-outline-success waves-effect payment_reminder_cls" title="Payment reminder enable" style="padding: 0.2rem 1.2rem;" id="payment_reminder_btn" data-remindervalue="1" data-invoiceid="'.$invoice_id.'" data-bs-toggle="modal" data-bs-target="#payment_reminder_enable_model" >
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                         </a>';
            if($payment_reminder['payment_reminder'] == 0){
                $message = 'Payment reminder disable successfully!';
                $flag_val = '<a class="btn btn-sm btn-outline-soundcloud payment_reminder_cls" title="Payment reminder disable" id="payment_reminder_btn" data-remindervalue="0" data-invoiceid="'.$invoice_id.'" style="padding: 0.310rem 1.2rem;" data-bs-toggle="modal" data-bs-target="#payment_reminder_enable_model">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-slash font-medium-3"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                             </a>';
            }
            ActivityLogHelper::log(
                'invoices.payment_reminder',
                "Invoice #{$invoice->invoice_number} {$message}",
                [],
                request(),
                Auth::user(),
                $invoice
            );
            return response()->json([
                'success' => true,
                'message' => $message,
                'flag_val' => $flag_val,
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

    public function cancelInvoice(Request $request, Invoice $invoice){
        try {
            $invoice->delete();

            ActivityLogHelper::log(
                'invoices.cancel_invoice',
                "Invoice #{$invoice->invoice_number} Cancelled by ".Auth::user()->first_name .' '.Auth::user()->last_name,
                [],
                request(),
                Auth::user(),
                $invoice
            );

            if($request->ajax()){
                return response()->json([
                    "success" => true,
                ]);
            }

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
        }
    }

    public function paymentReceiptExport(Request $request)
    {

        $paymentReceiptExport = new PaymentReceiptExport();

        Log::info($request->all());

        if(isset($request->export_from_date) && isset($request->export_to_date)){
            $start = Carbon::parse($request->export_from_date);
            $end = Carbon::parse($request->export_to_date);
            $paymentReceiptExport
                ->from_date($start)
                ->to_date($end);
        }else{
            $start = Carbon::parse($request->export_from_date);
            $end = Carbon::parse($request->export_from_date);
            $paymentReceiptExport
                ->from_date($start)
                ->to_date($end);
        }

        if ($request->export_payment_source_id) {
            $paymentReceiptExport->payment_source_id((int)$request->export_payment_source_id);
        }

        if ($request->export_client_id) {
            $paymentReceiptExport->client_id((int)$request->export_client_id);
        }

        if ($request->export_company_id) {
            $paymentReceiptExport->company_detail_id((int)$request->export_company_id);
        }

        $paymentReceiptExport->workspace(Auth::user()->workspace_id);

        return $paymentReceiptExport->download('paymentReceipt.xls', Excel::XLS);
    }

    public function restore(Request $request, Invoice $invoice)
    {
        DB::beginTransaction();
        try {
            $invoice->restore();
            ActivityLogHelper::log('invoice.restored',
                "Invoice #{$invoice->invoice_number} Restored by ".Auth::user()->first_name .' '.Auth::user()->last_name,
                [],
                $request,
                Auth::user(),
                $invoice);
            DB::commit();

            return response()->json([
                "success" => true,
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                "success" => false,
            ]);
        }
    }

     public function bankExport(Request $request)
    {
        try{

            $invoicesExport = new InvoicesBankExport();

            Log::info($request->all());

            if ($request->filter_payment_source_id) {
                $invoicesExport->payment_source_id((int)$request->filter_payment_source_id);
            }

            if ($request->filter_client_id) {
                $invoicesExport->client_id((int)$request->filter_client_id);
            }

            if ($request->filter_company_id) {
                $invoicesExport->company_detail_id((int)$request->filter_company_id);
            }

            if ($request->filter_payment) {
                $statuses = explode(',', $request->filter_payment);
                $invoicesExport->payment_statuses($statuses);
            }

            switch ($request->filter_created_at) {
                case 'custom':
                    $filter_created_at_arr = explode(
                        " to ",
                        $request->filter_created_at_range
                    );
                    $start = date_create_from_format(
                        'd/m/Y H:i:s',
                        $filter_created_at_arr[0] . " 00:00:00",
                        new DateTimeZone(Auth::user()->timezone)
                    );
                    $end = date_create_from_format(
                        'd/m/Y H:i:s',
                        (isset($filter_created_at_arr[1])
                            ? $filter_created_at_arr[1]
                            : $filter_created_at_arr[0]
                        ) . " 23:59:59",
                        new DateTimeZone(Auth::user()->timezone)
                    );
                    $invoicesExport
                        ->invoice_date_greater_than($start)
                        ->invoice_date_less_than($end);
                    break;
                case  'month':
                    $start = new DateTime(
                        "First day of this month",
                        new DateTimeZone(Auth::user()->timezone)
                    );
                    $end = new DateTime(
                        "Last day of this month",
                        new DateTimeZone(Auth::user()->timezone)
                    );
                    $invoicesExport
                        ->invoice_date_greater_than($start)
                        ->invoice_date_less_than($end);
                    break;
                case 'last_month':
                    $start = new DateTime(
                        "First day of last month",
                        new DateTimeZone(Auth::user()->timezone)
                    );
                    $end = new DateTime(
                        "Last day of last month",
                        new DateTimeZone(Auth::user()->timezone)
                    );
                    $invoicesExport
                        ->invoice_date_greater_than($start)
                        ->invoice_date_less_than($end);
                    break;
                case '3_months':
                    $start = new DateTime(
                        "First day of 3 months ago",
                        new DateTimeZone(Auth::user()->timezone)
                    );
                    $end = new DateTime(
                        "Last day of last month",
                        new DateTimeZone(Auth::user()->timezone)
                    );
                    $invoicesExport
                        ->invoice_date_greater_than($start)
                        ->invoice_date_less_than($end);
                    break;
                case 'year':
                    $year = date('Y');
                    $start = new DateTime(
                        "{$year}/01/01 00:00:00",
                        new DateTimeZone(Auth::user()->timezone)
                    );
                    $end = new DateTime(
                        "{$year}/12/31 23:59:59",
                        new DateTimeZone(Auth::user()->timezone)
                    );
                    $invoicesExport
                        ->invoice_date_greater_than($start)
                        ->invoice_date_less_than($end);
                    break;

                default:
                    break;
            }

            $invoicesExport
                ->workspace(Auth::user()->workspace_id);

            return $invoicesExport->download('invoices.xls', Excel::XLS);

         } catch (\Throwable $th) {
             return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'error' => $th
            ], 500);
        }
    }
}
