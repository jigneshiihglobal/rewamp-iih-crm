<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceType;
use App\Enums\PlantTreeStatus;
use App\Exports\ClientExport;
use App\Helpers\ActivityLogHelper;
use App\Helpers\DateHelper;
use App\Helpers\EncryptionHelper;
use App\Mail\ClientReviewMail;
use App\Models\ClientFeedback;
use App\Models\ClientReviewMailSendHistory;
use App\Models\ContactedLeadMail;
use App\Models\FeedbackToken;
use App\Models\NoteReminder;
use App\Models\PaymentSource;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\SalesInvoiceAccess;
use App\Models\User;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Models\ClientReview;
use Illuminate\Support\Facades\Log;
use App\Models\CustomerProject;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class ClientController extends Controller
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

            $clients = Client::select("clients.*")
                ->withTrashed()
                ->with(['country:id,name','projectList:id,customer_id,project_id'])
                ->withCount(['invoices','clientFeedbackMail'])
                ->where('workspace_id', Auth::user()->workspace_id);

            return DataTables::eloquent($clients)
                ->editColumn(
                    'id',
                    function (Client $client) {
                        return EncryptionHelper::encrypt($client->id);
                    }
                )
                ->editColumn(
                    'created_at',
                    function (Client $client) {
                        return $client->created_at->setTimezone(Auth::user()->timezone)->format(DateHelper::LEAD_DATE_FORMAT);
                    }
                )
                ->addColumn(
                    'country_name',
                    function (Client $client) {
                        return $client->country->name;
                    }
                )
                ->addColumn(
                    'email',
                    function (Client $client) {
                        $email = explode(',',$client->email);
                        return $email[0];
                    }
                )
                ->filterColumn(
                    'created_at',
                    function ($query, $keyword) {
                        $format = DateHelper::USER_CREATED_DATE_MYSQL;
                        $timezone_offset = DateHelper::getGmtOffsetFromTimezone(Auth::user()->timezone);
                        $query->orWhereRaw("DATE_FORMAT(CONVERT_TZ(clients.created_at, '+00:00', '{$timezone_offset}'), '{$format}') like '%{$keyword}%'");
                    }
                )
                ->toJson();
        }

        $users = User::select('email','id')
                ->where('is_active','1')
                ->where('workspace_id',Auth::user()->workspace_id)
                ->where('is_invoice_access','1')
                ->get();

        return view('clients.index',compact('users'));
    }

    public function store(Request $request)
    {
        $valid = $request->validate([
            'name' => 'required',
            'email' => 'required|array',
            'email.*' => 'required|email',
            'city' => 'nullable',
            'country_id' => 'nullable|exists:countries,id',
            'address_line_1' => 'nullable',
            'address_line_2' => 'nullable',
            'phone' => 'nullable|string',
            'zip_code' => 'nullable',
            'vat_number' => 'nullable',
        ]);

        $valid['email'] = implode(',',$request->email);
        $valid['workspace_id'] = Auth::user()->workspace_id;
        $valid['plant_a_tree'] = Auth::user()->hasRole(['User']) ? '1' : $request->has('plant_a_tree');
        $valid['created_by'] = Auth::user()->id;

        DB::beginTransaction();

        try {
            $client = Client::create($valid);
            
            $sales_user_ids = $request->sales_user_id;
            if(Auth::user()->hasRole(['User'])){
                $sales_user_ids[] = Auth::user()->id;
            }
            if(isset($sales_user_ids) && !empty($sales_user_ids)){
                foreach ($sales_user_ids as $sales_user_id) {
                    SalesInvoiceAccess::create([
                        'client_id' => $client->id,
                        'sales_id'  => $sales_user_id,
                    ]);
                }
            }

            ActivityLogHelper::log('client.created', 'Customer created.', [], $request, Auth::user(), $client);

            DB::commit();

            return response()->json([
                'client' => $client
            ], 201);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong! Unable to create Customer!',
                'error' => $th
            ], 500);
        }
    }

    public function show(Request $request, Client $client)
    {

        if($request->ajax()){
            $client->load('salesUserList');
            return response()->json([
                "client" =>  $client,
                "success" => true,
            ]);
        }


        if ($request->ajax()) {
            $invoices = Invoice::withTrashed()->query()
                ->with([
                    'client:id,name,email',
                    'client.projectList:id,customer_id,project_id', // Load projectList from client,
                    'currency:id,symbol',
                    'sales_person:id,first_name,last_name',
                    'credit_note:id,parent_invoice_id,grand_total',
                    'parent_invoice:id',
                    'salesUserList:id,client_id,sales_id',
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
                    "invoices.invoice_date",
                    "invoices.deleted_at",
                    "invoices.user_id",
                    "invoices.payment_status",
                    "invoices.parent_invoice_id",
                    "invoices.subscription_status",
                    "invoices.is_new",
                )
                ->withSum('payments', 'amount')
                ->when(
                    $request->filter_payment_source_id,
                    function ($q, $payment_source_id) {
                        $q->whereHas('payments', function ($q) use ($payment_source_id) {
                            $q->where('payment_source_id', $payment_source_id);
                        });
                    }
                )
                ->when(
                    $client->client_id,
                    function ($q, $client_id) {
                        $q->where('invoices.client_id', $client_id);
                    }
                )
                ->when(
                    $request->filter_payment,
                    function ($query, $payment_status) {
                        $query->whereIn('invoices.payment_status', $payment_status)->where('type', InvoiceType::INVOICE);
                    }
                )
                ->when($request->filter_created_at, function ($query, $created_at) use ($request) {
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

                                return $query->whereBetween('invoices.invoice_date', [$start, $end]);
                            }
                        );
                });


            return DataTables::eloquent($invoices)
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
        // $clients = Client::where('workspace_id', Auth::user()->workspace_id)->get(['id', 'name']);
        $clients = Client::withTrashed()->with(['projectList:id,customer_id,project_id','salesUserList:id,client_id,sales_id'])->where('id', $client->id)->first(['id', 'name']);
        $invoice = Invoice::where('client_id',$client->id)->get();
        $customer_createddate = $client->created_at->setTimezone(Auth::user()->timezone)->format(DateHelper::LEAD_DATE_FORMAT);
        $client = Client::with([
                'projectList:id,customer_id,project_id',
                'salesUserList:id,client_id,sales_id',
                'salesUserList.sales_person:id,first_name,last_name', // make sure to eager load this too
                'createdUser:id,name,first_name,last_name'
            ])
            ->withTrashed()
            ->where('id', $client->id)
            ->first();

        $salesNames = optional($client->salesUserList)
            ->map(function ($item) {
                $firstName = optional($item->sales_person)->first_name;
                $lastName = optional($item->sales_person)->last_name;
                return trim("{$firstName} {$lastName}");
            })
            ->filter()
            ->implode(', ');

        // dd($client,$clients);
        return view('clients.show', compact('client','invoice','payment_sources','clients','customer_createddate','salesNames'));

    }

    public function update(Request $request, Client $client)
    {
        $valid = $request->validate([
            'name' => 'required',
            'email' => 'required|array',
            'email.*' => 'required|email',
            'city' => 'nullable',
            'country_id' => 'nullable|exists:countries,id',
            'address_line_1' => 'nullable',
            'address_line_2' => 'nullable',
            'phone' => 'nullable|string',
            'zip_code' => 'nullable',
            'vat_number' => 'nullable',
        ]);

        $valid['plant_a_tree'] = $request->has('plant_a_tree');
        $valid['email'] = implode(',',$request->email);

        $clients = $client->getAttributes();
        $differences = [];
        foreach ($valid as $key => $value) {
            if (array_key_exists($key, $clients) && $clients[$key] !== $value) {
                if($key != 'country_id'){
                    if($clients['plant_a_tree'] != $valid['plant_a_tree'] && $key == 'plant_a_tree'){
                        $differences[$key] = "$clients[$key] => $value";
                    }else if($key != 'plant_a_tree'){
                        $differences[$key] = "$clients[$key] => $value";
                    }
                    if($clients['name'] != $valid['name'] && $key == 'name'){
                        $differences[$key] = "$clients[$key] => $value";
                    }
                    if($clients['address_line_1'] != $valid['address_line_1'] && $key == 'address_line_1'){
                        $differences[$key] = "$clients[$key] => $value";
                    }
                    if($clients['address_line_2'] != $valid['address_line_2'] && $key == 'address_line_2'){
                        $differences[$key] = "$clients[$key] => $value";
                    }
                    if($clients['zip_code'] != $valid['zip_code'] && $key == 'zip_code'){
                        $differences[$key] = "$clients[$key] => $value";
                    }
                    if($clients['vat_number'] != $valid['vat_number'] && $key == 'vat_number'){
                        $differences[$key] = "$clients[$key] => $value";
                    }
                }
            }
        }

        DB::beginTransaction();

        try {
            $client->update($valid);

            $oldSalesUserIds = SalesInvoiceAccess::where('client_id', $client->id)->pluck('sales_id')->toArray();
            $sales_user_ids = $request->edit_sales_user_id ?? [];
            if(Auth::user()->hasRole(['User'])){
                $sales_user_ids[] = Auth::user()->id;
            }
            // Remove duplicates just in case
            $newSalesUserIds = array_unique($sales_user_ids);

            $addedUsers = array_diff($newSalesUserIds, $oldSalesUserIds);
            $removedUsers = array_diff($oldSalesUserIds, $newSalesUserIds);

            if (!empty($addedUsers) || !empty($removedUsers)) {
                $addedNames = User::whereIn('id', $addedUsers)->pluck('name')->toArray();
                $removedNames = User::whereIn('id', $removedUsers)->pluck('name')->toArray();

                if (!empty($addedNames)) {
                    $differences['sales_added'] = 'Added Sales Users: ' . implode(', ', $addedNames);
                }
                if (!empty($removedNames)) {
                    $differences['sales_removed'] = 'Removed Sales Users: ' . implode(', ', $removedNames);
                }
            }
            if (!empty($sales_user_ids)) {
                SalesInvoiceAccess::where('client_id', $client->id)->delete();
                foreach ($sales_user_ids as $sales_user_id) {
                    SalesInvoiceAccess::create([
                        'client_id' => $client->id,
                        'sales_id' => $sales_user_id,
                    ]);
                }
            }
            
            $logDescription = '';
            if(isset($differences) && !empty($differences)){
                $logDescription = ' Changes: ' . implode(', ', $differences);
            }

            if(isset($differences['name']) || isset($differences['address_line_1']) || isset($differences['address_line_2']) || isset($differences['zip_code']) || isset($differences['vat_number'])){
                $invoices = Invoice::where('client_id',$clients['id'])->where('type','0')->where('payment_status','unpaid')->get();
                foreach ($invoices as $invoice){
                    $this->service->makeAndStorePDF($invoice);
                }
            }
            ActivityLogHelper::log('client.updated', 'Customer updated.'.$logDescription, [], $request, Auth::user(), $client);

            DB::commit();

            return response()->json([], 201);
        } catch (\Throwable $th) {
            DB::rollback();
            dd('controller error',$th);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong! Unable to update Customer!',
                'error' => $th
            ], 500);
        }
    }

    public function destroy(Request $request, Client $client)
    {
        DB::beginTransaction();
        try {
            $client->delete();
            ActivityLogHelper::log('client.deleted', 'Customer deleted by superadmin', [], $request, Auth::user(), $client);
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

    public function restore(Request $request, Client $client)
    {
        DB::beginTransaction();
        try {
            $client->restore();
            ActivityLogHelper::log('client.restored', 'Customer restored by superadmin', [], $request, Auth::user(), $client);
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

    public function preferredSalesPerson(Request $request, Client $client)
    {
        $NoteReminder = NoteReminder::where('workspace_id',Auth::user()->workspace_id)->whereRaw('FIND_IN_SET("'.$client->id.'",assign_client_id)')->get();

        $Note_exist = 0;
        $notes = '';
        if(isset($NoteReminder) && count($NoteReminder)){
            $notes = $NoteReminder;
            $Note_exist = 1;
        }
        $sales_person_id = $client->invoices()
            ->select(
                'invoices.id',
                'invoices.user_id'
            )
            ->whereNotNull('invoices.user_id')
            ->whereHas(
                'sales_person',
                function ($q) {
                    $q->whereHas(
                        'workspaces',
                        function ($q) {
                            $q->where(
                                'workspaces.id',
                                Auth::user()->workspace_id
                            );
                        }
                    );
                }
            )
            ->orderBy('created_at', 'desc')
            ->first()
            ->user_id ?? null;
        return response()->json(['sales_person_id' => $sales_person_id,'Note_exist' => $Note_exist,'notes' => $notes]);
    }

    public function clientReview(Request $request)
    {
        dd('hello');
        try {
            $clients = ClientReview::leftJoin('clients', 'clients.id', '=', 'client_reviews.client_id')->get();
            dd('hay');
            return view('clients.review');
        } catch (\Throwable $th) {
            dd($th);
            DB::rollback();
            return response()->json([
                "success" => false,
            ]);
        }
    }

    public function paymentReminder(Request $request){
        try {
            $client_id = EncryptionHelper::decrypt($request->client_id);
            if(!$client_id){
                $client_id = $request->client_id;
            }
            $payment_reminder['payment_reminder'] = isset($request->reminder_status) && $request->reminder_status == 1 ? 0 : 1;
            Client::where('id',$client_id)->update($payment_reminder);
            $client = Client::where('id',$client_id)->first();

            Invoice::where('client_id',$client->id)->where('payment_status', '!=', 'paid')->update($payment_reminder);


            $message = 'Invoice payment reminder enable successfully!';
            $flag_val = '<a class="btn btn-sm btn-outline-success waves-effect payment_reminder_cls" title="Payment reminder enable" style="padding: 0.2rem 1.2rem;" id="payment_reminder_btn" data-remindervalue="1" data-clientid="'.$client_id.'" data-bs-toggle="modal" data-bs-target="#payment_reminder_enable_model" >
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                         </a>';
            if($payment_reminder['payment_reminder'] == 0){
                $message = 'Invoice payment reminder disable successfully!';
                $flag_val = '<a class="btn btn-sm btn-outline-soundcloud payment_reminder_cls" title="Payment reminder disable" id="payment_reminder_btn" data-remindervalue="0" data-clientid="'.$client_id.'" style="padding: 0.310rem 1.2rem;" data-bs-toggle="modal" data-bs-target="#payment_reminder_enable_model">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-slash font-medium-3"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                             </a>';
            }
            ActivityLogHelper::log(
                'clients.client_payment_reminder',
                "Client #{$client->name} {$message}",
                [],
                request(),
                Auth::user(),
                $client
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

    public function saveSelectedProjects(Request $request)
    {
        try {

            $auth_token = config('custom.hrms_customer_project.auth_token');
            $path_url = config('custom.hrms_customer_project.hrms_url');


            $projects = $request->input('projects') ?? [];
            $client_id = EncryptionHelper::decrypt($request->customer_id);

            $client = Client::find($client_id);

            // Fetch existing project IDs for the customer
            $existingProjects = CustomerProject::where('customer_id', $client_id)
            ->pluck('project_id')
            ->toArray() ?? [];

            // Calculate projects to be added
            $projectsToAdd = array_diff($projects, $existingProjects);

            // Calculate projects to be removed
            $projectsToRemove = array_diff($existingProjects, $projects);

            //Remove old projects that are no longer associated
            if (!empty($projectsToRemove)) {
                CustomerProject::where('customer_id', $client_id)
                    ->whereIn('project_id', $projectsToRemove)
                    ->delete();
            }

            // Add new projects
            if (!empty($projectsToAdd)) {
                foreach ($projectsToAdd as $projectId) {
                    $customerProject = new CustomerProject();
                    $customerProject->customer_id = $client_id;
                    $customerProject->project_id = $projectId;
                    $customerProject->save();
                }
            }

            // if(empty($projects)){
            //     $projects = $projectsToRemove;
            // }

            // Call the external API after saving projects
            $apiResponse = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization'=> $auth_token,
            ])->put($path_url.'/api/crm/update_crm_client_hrms', [
                'crm_contact_id' => $client_id,
                'id' => $projects, // Send the project IDs
            ]);
            Log::error('Response check HRMS: ' . $apiResponse->successful());
            // Check the response from the external API
            if ($apiResponse->successful()) {
                $responseData = $apiResponse->json(); // Decode the response into an array

                // Extract removed and added IDs
                $removedIds = $responseData['removedIds'] ?? [];
                $addedIds = $responseData['addedIds'] ?? [];

                // Use implode to format them into a string
                $removedIdsString = !empty($removedIds) ? implode(', ', $removedIds) : 'None';
                $addedIdsString = !empty($addedIds) ? implode(', ', $addedIds) : 'None';

                //  'Added Projects: $addedIdsString. Removed Projects: $removedIdsString.";
                $Adeed_project =  $addedIdsString != 'None' ? 'Added Projects: '.$addedIdsString : '';
                $remove_project =  $removedIdsString != 'None' ? 'Removed Projects: '.$removedIdsString : '';
                $message = $client->name ." customer HRMS projects updated successfully.". $Adeed_project.' '.$remove_project;

                ActivityLogHelper::log('client.save_selected_projects', $message, [], $request, Auth::user(), $client);

                return response()->json(['message' => 'Projects saved and CRM updated successfully!'], 200);
            } else {
                // Handle unsuccessful response
                Log::error('Failed to update HRMS: ' . $apiResponse->body());
                return response()->json(['message' => 'Projects saved but HRMS project response failed.'], 500);
            }

            //return response()->json(['message' => 'Projects saved successfully!'], 200);

        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong please try again!',
                'error' => $th
            ], 500);
        }
    }


    public function getMailContent(Request $request, Client $client)
    {
        try {

            $bcc = config('mail.accounts_mail_bcc', []);

            $email = explode(',',$client->email);

            $time_stamp = now()->timestamp;

            $client_review_send_mail = ClientReviewMailSendHistory::where('client_id', $client->id)->count();

            $client_id = EncryptionHelper::encrypt($client->id);

            return response()->json([
                'to' => $email,
                'bcc' => $bcc,
                'send_mail_count' => $client_review_send_mail,
                'subject' => "Your Feedback Matters: Help Us Improve!",
                'time_stamp' => $time_stamp,
                "content" => view('clients.emails.review-mail-content-template', ['client' => $client,'client_id' => $client_id,'time_stamp'=>$time_stamp])->render()
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



    public function sendMailToClient(Request $request, Client $client)
    {
        $valid = $request->validate([
            'to' => 'required|array',
            'to.*' => 'required|email',
            'bcc' => 'array',
            'bcc.*' => 'email',
            'subject' => 'required|string|max:255',
            'content' => 'required'
        ]);

        try {
            
            Mail::mailer(config('mail.default'))
                ->to($valid['to'])
                ->bcc($valid['bcc'] ?? [])
                ->send(new ClientReviewMail($valid['subject'], $valid['content']));

            $feedback_token  = new FeedbackToken;
            $feedback_token->client_id = $client->id;
            $feedback_token->feedback_form_token = $request->feedback_token;
            $feedback_token->save();

            $toEmails = is_array($valid['to']) ? implode(', ', $valid['to']) : $valid['to'];

            ActivityLogHelper::log(
                'clients.review_send_mail',
                'Review Mail sent to customer# '. $toEmails,
                [],
                $request,
                Auth::user(),
                $client
            );

            $updateValues = [
                'review_mail_send_date' => now(),
            ];
            $client->update($updateValues);

            // Client review mail add as review history
            $client_review_store = new ClientReviewMailSendHistory();
            $client_review_store->client_id = $client->id;
            $client_review_store->review_mail_send_date_time = Carbon::now();
            $client_review_store->save();

            // Marketing mail add
            $mail_sent =new ContactedLeadMail();
            $mail_sent->lead_status_id	= null;
            $mail_sent->lead_name	    = $client->name;
            $mail_sent->email	        = $client->email;
            $mail_sent->day_after	    = null;
            $mail_sent->mail_subject	= $valid['subject'];
            $mail_sent->mail_content	= $valid['content'];
            $mail_sent->save();

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

    public function getReviewEmailHistory(Request $request, Client $client)
    {
        try {

            $client_feedbacks = ClientFeedback::where('client_id', $client->id)->orderBy('created_at', 'desc')->get();

            return response()->json([
                'client_feedbacks' => $client_feedbacks,
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


    public function exportFiltered(Request $request)
    {
        try {
            $start = Carbon::createFromFormat('d/m/Y', $request->export_created_at_start)
                ->startOfDay()
                ->shiftTimezone(Auth::user()->timezone)
                ->setTimezone(config('app.timezone', 'UTC'));

            $end = Carbon::createFromFormat('d/m/Y', $request->export_created_at_end)
                ->endOfDay()
                ->shiftTimezone(Auth::user()->timezone)
                ->setTimezone(config('app.timezone', 'UTC'));

            return (new ClientExport)
                ->createdAtFrom($start)
                ->createdAtTo($end)
                ->download('client.csv', \Maatwebsite\Excel\Excel::CSV);
        } catch (\Throwable $th) {
            Log::info($th);
            return back();
        }
    }

    

}
