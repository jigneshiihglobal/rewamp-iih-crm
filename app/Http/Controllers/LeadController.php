<?php

namespace App\Http\Controllers;

use App\Events\LeadCreatedEvent;
use App\Events\LeadDeletedEvent;
use App\Events\LeadRestoredEvent;
use App\Events\LeadUpdatedEvent;
use App\Exports\LeadsExport;
use App\Helpers\ActivityLogHelper;
use App\Helpers\DateHelper;
use App\Helpers\EncryptionHelper;
use App\Helpers\FileHelper;
use App\Helpers\SlackHelper;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Country;
use App\Models\Lead;
use App\Models\LeadNote;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\User;
use App\Models\Workspace;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Error;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if ($user->hasRole('Admin') || $user->hasRole('Superadmin') || $user->hasRole('Marketing')) {
            $user->update(
                [
                    'show_deleted_leads' => $request->boolean(
                        'show_deleted',
                        $user->show_deleted_leads
                    )
                ]
            );
        }

        if ($request->ajax()) {
            $leads = Lead::query()
                ->where('leads.workspace_id', $user->workspace_id)
                ->with([
                    "lead_source:id,title",
                    "assignee:id,name,first_name,last_name,email",
                    "lead_status:id,title,css_class"
                ])
                ->select(
                    "leads.id",
                    "leads.lead_status_id",
                    "leads.lead_source_id",
                    "leads.assigned_to",
                    "leads.firstname",
                    "leads.lastname",
                    DB::raw("TRIM(CONCAT(COALESCE(leads.firstname, ''), ' ', COALESCE(leads.lastname, ''))) AS full_name"),
                    "leads.mobile",
                    "leads.email",
                    "leads.marketing_mail_reminder_status",
                    "leads.created_at",
                    "leads.updated_at",
                    "leads.deleted_at"
                )
                ->when(
                    !$user->hasRole(['Admin', 'Superadmin','Marketing']),
                    function ($query) {
                        $query->where('assigned_to', Auth::id());
                    },
                    function ($query) use ($request) {
                        if(auth()->user()->hasRole('Marketing')){
                            $query->when($request->lead_status_id == 'deleted', function ($query) {
                                $query->whereDoesntHave('assignee');
                            });
                        }
                        $query->when($request->lead_status_id == '1', function ($query) {
                            $query->whereDoesntHave('assignee');
                        });
                    }
                )
                ->when($request->filter_lead_source_id, function ($q, $lead_source_id) {
                    $q->where('leads.lead_source_id', $lead_source_id);
                })
                ->when(
                    $request->lead_status_id,
                    function ($query, $lead_status_id) {
                        $query->when(
                            $lead_status_id === 'deleted',
                            function ($query) {
                                $query->onlyTrashed();
                            },
                            function ($query) use ($lead_status_id) {
                                $query->where('lead_status_id', $lead_status_id);
                            }
                        );
                    }
                )
                ->when(
                    $request->assigned_to && $user->hasRole(['Admin', 'Superadmin','Marketing']),
                    function ($query) use ($request) {
                        $query->where(
                            'assigned_to',
                            $request->assigned_to
                        );
                    }
                )->when(
                    $request->boolean('show_deleted')
                        && ($user->hasRole('Admin') || $user->hasRole('Superadmin') || $user->hasRole('Marketing')),
                    function ($query) {
                        return $query->withTrashed();
                    }
                )
                ->when($request->created_at, function ($query, $created_at) use ($request) {
                    $query
                        ->when($created_at == 'week', function ($query) {
                            $start = date('Y-m-d 00:00:00', strtotime("Monday this week"));
                            $end = date('Y-m-d 23:59:59', strtotime("Sunday this week"));
                            $start = (new DateTime($start, new DateTimeZone(Auth::user()->timezone)))->setTimezone(new DateTimeZone(config('app.timezone', 'UTC')));
                            $end = (new DateTime($end, new DateTimeZone(Auth::user()->timezone)))->setTimezone(new DateTimeZone(config('app.timezone', 'UTC')));

                            return $query->whereBetween('leads.created_at', [$start, $end]);
                        })
                        ->when($created_at == 'month', function ($query) {
                            $start = date('Y-m-d 00:00:00', strtotime("First day of this month"));
                            $end = date('Y-m-d 23:59:59', strtotime("Last day of this month"));
                            $start = (new DateTime($start, new DateTimeZone(Auth::user()->timezone)))->setTimezone(new DateTimeZone(config('app.timezone', 'UTC')));
                            $end = (new DateTime($end, new DateTimeZone(Auth::user()->timezone)))->setTimezone(new DateTimeZone(config('app.timezone', 'UTC')));

                            return $query->whereBetween('leads.created_at', [$start, $end]);
                        })
                        ->when($created_at == 'last_month', function ($query) {
                            $start = date('Y-m-d 00:00:00', strtotime("First day of last month"));
                            $end = date('Y-m-d 23:59:59', strtotime("Last day of last month"));
                            $start = (new DateTime($start, new DateTimeZone(Auth::user()->timezone)))->setTimezone(new DateTimeZone(config('app.timezone', 'UTC')));
                            $end = (new DateTime($end, new DateTimeZone(Auth::user()->timezone)))->setTimezone(new DateTimeZone(config('app.timezone', 'UTC')));

                            return $query->whereBetween('leads.created_at', [$start, $end]);
                        })
                        ->when($created_at == '3_months', function ($query) {
                            $start = date('Y-m-d 00:00:00', strtotime("First day of 3 months ago"));
                            $end = date('Y-m-d 23:59:59', strtotime("Last day of last month"));
                            $start = (new DateTime($start, new DateTimeZone(Auth::user()->timezone)))->setTimezone(new DateTimeZone(config('app.timezone', 'UTC')));
                            $end = (new DateTime($end, new DateTimeZone(Auth::user()->timezone)))->setTimezone(new DateTimeZone(config('app.timezone', 'UTC')));

                            return $query->whereBetween('leads.created_at', [$start, $end]);
                        })
                        ->when($created_at == 'year', function ($query) {
                            $start = date('Y-01-01 00:00:00');
                            $end = date('Y-12-31 23:59:59');
                            $start = (new DateTime($start, new DateTimeZone(Auth::user()->timezone)))->setTimezone(new DateTimeZone(config('app.timezone', 'UTC')));
                            $end = (new DateTime($end, new DateTimeZone(Auth::user()->timezone)))->setTimezone(new DateTimeZone(config('app.timezone', 'UTC')));

                            return $query->whereBetween('leads.created_at', [$start, $end]);
                        })
                        ->when(
                            $created_at == 'custom' && $request->created_at_start && $request->created_at_end,
                            function ($query) use ($request) {
                                $start = date_create_from_format('d/m/Y H:i:s', $request->created_at_start . " 00:00:00", new DateTimeZone(Auth::user()->timezone));
                                $end = date_create_from_format('d/m/Y H:i:s', $request->created_at_end . " 23:59:59", new DateTimeZone(Auth::user()->timezone));
                                $start = $start->setTimezone(new Datetimezone(config('app.timezone',  'UTC')));
                                $end = $end->setTimezone(new Datetimezone(config('app.timezone',  'UTC')));

                                return $query->whereBetween('leads.created_at', [$start, $end]);
                            }
                        );
                })
                ->when(
                    $request->won_at_start && $request->won_at_end,
                    function ($query) use ($request) {
                        $start = Carbon::createFromFormat('d/m/Y', $request->won_at_start, Auth::user()->timezone)
                            ->startOfDay()
                            ->setTimezone(config('app.timezone', 'UTC'));
                        $end = Carbon::createFromFormat('d/m/Y', $request->won_at_end, Auth::user()->timezone)
                            ->endOfDay()
                            ->setTimezone(config('app.timezone', 'UTC'));

                        return $query->whereBetween('leads.won_at', [$start, $end]);
                    }
                );

            return DataTables::eloquent($leads)
                ->editColumn(
                    'id',
                    function (Lead $lead) {
                        return EncryptionHelper::encrypt($lead->id);
                    }
                )
                ->editColumn(
                    'lead_source',
                    function (Lead $lead) {
                        return $lead->lead_source
                            ? $lead->lead_source->title
                            : "";
                    }
                )
                ->editColumn(
                    'assignee',
                    function (Lead $lead) {
                        return $lead->assignee
                            ? ($lead->assignee->first_name . " " . $lead->assignee->last_name)
                            : "";
                    }
                )
                ->addColumn(
                    'assignee_email',
                    function (Lead $lead) {
                        return $lead->assignee
                        ? ($lead->assignee->email)
                        : "";
                    }
                )
                ->editColumn(
                    'created_at',
                    function (Lead $lead) {
                        return $lead->created_at->setTimezone(Auth::user()->timezone)->format(DateHelper::LEAD_DATE_FORMAT);
                    }
                )
                ->editColumn(
                    'updated_at',
                    function (Lead $lead) {
                        return $lead->updated_at->setTimezone(Auth::user()->timezone)->format(DateHelper::LEAD_DATE_FORMAT);
                    }
                )
                ->editColumn(
                    'deleted_at',
                    function (Lead $lead) {
                        return $lead->deleted_at ? $lead->deleted_at->setTimezone(Auth::user()->timezone)->format(DateHelper::LEAD_DATE_FORMAT) : null;
                    }
                )
                ->filterColumn(
                    'full_name',
                    function ($query, $keyword) {
                        $query
                            ->where('firstname', 'like', "%{$keyword}%")
                            ->orWhere('lastname', 'like', "%{$keyword}%")
                            ->orWhereRaw("CONCAT(leads.firstname, ' ', leads.lastname) LIKE ?", ["%{$keyword}%"]);
                            //->orWhereRaw("CONCAT(leads.lastname, ' ', leads.firstname) LIKE ?", ["%{$keyword}%"]);
                    }
                )
                ->filterColumn(
                    'assignee.first_name',
                    function ($query, $keyword) {
                        return $query->whereHas(
                            'assignee',
                            function (Builder $innerQuery) use ($keyword) {
                                return $innerQuery
                                    ->where('first_name', 'like', "%{$keyword}%")
                                    ->orWhere('last_name', 'like', "%{$keyword}%")
                                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$keyword}%"]);
                                    //->orWhereRaw("CONCAT(last_name, ' ', first_name) LIKE ?", ["%{$keyword}%"]);
                            }
                        );
                    }
                )
                ->filterColumn(
                    'created_at',
                    function ($query, $keyword) {
                        $format = DateHelper::USER_CREATED_DATE_MYSQL;
                        $timezone_offset = DateHelper::getGmtOffsetFromTimezone(Auth::user()->timezone);
                        $query->orWhereRaw("DATE_FORMAT(CONVERT_TZ(leads.created_at, '+00:00', '{$timezone_offset}'), '{$format}') like '%{$keyword}%'");
                    }
                )
                ->filterColumn(
                    'updated_at',
                    function ($query, $keyword) {
                        $format = DateHelper::USER_CREATED_DATE_MYSQL;
                        $timezone_offset = DateHelper::getGmtOffsetFromTimezone(Auth::user()->timezone);
                        $query->orWhereRaw("DATE_FORMAT(CONVERT_TZ(leads.updated_at, '+00:00', '{$timezone_offset}'), '{$format}') like '%{$keyword}%'");
                    }
                )
                ->with('new_leads_count', Lead::where('lead_status_id', 1)
                    ->where('leads.workspace_id', $user->workspace_id)
                    ->when(
                        Auth::user()->hasRole(['Admin', 'Superadmin','Marketing']),
                        function ($query) use ($request) {
                            $query->when(Auth::user()->show_deleted_leads, function ($query) {
                                $query->withTrashed();
                            })
                                ->whereDoesntHave('assignee', function ($query) {
                                    $query->withTrashed();
                                })->when($request->created_at, function ($query, $created_at) use ($request) {
                                    $query
                                        ->when($created_at == 'week', function ($query) {
                                            $start = now()
                                                ->startOfWeek()
                                                ->shiftTimezone(Auth::user()->timezone)
                                                ->setTimezone(config('app.timezone', 'UTC'));
                                            $end = now()
                                                ->endOfDay()
                                                ->shiftTimezone(Auth::user()->timezone)
                                                ->setTimezone(config('app.timezone', 'UTC'));

                                            return $query->whereBetween('leads.created_at', [$start, $end]);
                                        })
                                        ->when($created_at == 'month', function ($query) {
                                            $start = now()
                                                ->startOfDay()
                                                ->startOfMonth()
                                                ->shiftTimezone(Auth::user()->timezone)
                                                ->setTimezone(config('app.timezone', 'UTC'));
                                            $end = now()
                                                ->endOfDay()
                                                ->shiftTimezone(Auth::user()->timezone)
                                                ->setTimezone(config('app.timezone', 'UTC'));

                                            return     $query->whereBetween('leads.created_at', [$start, $end]);
                                        })
                                        ->when($created_at == 'last_month', function ($query) {
                                            $start = now()
                                                ->startOfMonth()
                                                ->subMonth()
                                                ->startOfDay()
                                                ->shiftTimezone(Auth::user()->timezone)
                                                ->setTimezone(config('app.timezone', 'UTC'));
                                            $end = now()
                                                ->subMonth()
                                                ->endOfMonth()
                                                ->endOfDay()
                                                ->shiftTimezone(Auth::user()->timezone)
                                                ->setTimezone(config('app.timezone', 'UTC'));

                                            return     $query->whereBetween('leads.created_at', [$start, $end]);
                                        })
                                        ->when($created_at == '3_months', function ($query) {
                                            $start = now()
                                                ->startOfDay()
                                                ->subMonths(3)
                                                ->addDay()
                                                ->shiftTimezone(Auth::user()->timezone)
                                                ->setTimezone(config('app.timezone', 'UTC'));
                                            $end = now()
                                                ->subMonth()
                                                ->endOfMonth()
                                                ->endOfDay()
                                                ->shiftTimezone(Auth::user()->timezone)
                                                ->setTimezone(config('app.timezone', 'UTC'));

                                            return $query->whereBetween('leads.created_at', [$start, $end]);
                                        })
                                        ->when($created_at == 'year', function ($query) {
                                            $start = now()
                                                ->startOfYear()
                                                ->startOfDay()
                                                ->shiftTimezone(Auth::user()->timezone)
                                                ->setTimezone(config('app.timezone', 'UTC'));
                                            $end = now()
                                                ->endOfDay()
                                                ->shiftTimezone(Auth::user()->timezone)
                                                ->setTimezone(config('app.timezone', 'UTC'));

                                            return $query->whereBetween('leads.created_at', [$start, $end]);
                                        })
                                        ->when(
                                            $created_at == 'custom' && $request->created_at_start && $request->created_at_end,
                                            function ($query) use ($request) {
                                                $start = Carbon::createFromFormat('d/m/Y', $request->created_at_start, Auth::user()->timezone)
                                                    ->startOfDay()
                                                    ->setTimezone(config('app.timezone', 'UTC'));
                                                $end = Carbon::createFromFormat('d/m/Y', $request->created_at_end, Auth::user()->timezone)
                                                    ->endOfDay()
                                                    ->setTimezone(config('app.timezone', 'UTC'));

                                                return $query->whereBetween('leads.created_at', [$start, $end]);
                                            }
                                        );
                                });
                        },
                        function ($query) {
                            $query->where('assigned_to', Auth::id());
                        }
                    )
                    ->count())
                ->toJson();
        }

        $lead_statuses = LeadStatus::query()
            ->orderByDesc('priority')
            ->get([
                'id',
                'title',
                'css_class'
            ]);
        $lead_sources = LeadSource::all(['id', 'title']);
        $users = User::whereHas('workspaces', function ($query) {
            $query->where('workspaces.id', Auth::user()->workspace_id);
        })->get(['id', 'name', 'first_name', 'last_name']);
        $countries = Country::all(['id', 'name']);

        return view(
            "leads.index",
            compact(
                "lead_statuses",
                "lead_sources",
                "users",
                "countries",
            )
        );
    }

    public function store(StoreLeadRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'email.*' => 'email', // Ensure each input is a valid email format
        ], [
            'email.*.email' => 'Please enter a valid email address.',
        ]);

        $validator->addRules([
            'email.*' => [
                function ($attribute, $value, $fail) {
                    // Get all existing email values from the leads table
                    $existingEmails = DB::table('leads')
                        ->select('email')
                        ->get()
                        ->pluck('email')
                        ->toArray();

                    $existingEmails = array_map('trim', explode(',', implode(',', $existingEmails)));

                    if (in_array(strtolower($value), array_map('strtolower', $existingEmails))) {
                        $lead = Lead::with('lead_status')->where(DB::raw('LOWER(email)'), 'LIKE', '%' . strtolower($value) . '%')->first();
                        $fail('This email/lead already exists in the system under ('.$lead['lead_status']->title.')');
                        return;
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        try {
            $workspaces = Workspace::where('name','IIH Global')->first();
            $valid = $request->safe()->except(['attachments']);
            $attachments = $request->has('attachments')
                ? $request->safe(['attachments'])['attachments']
                : [];
            $lead_status = LeadStatus::where('id',$valid['lead_status_id'])->first();
            $valid['workspace_id'] = Auth::user()->workspace_id;
            if(Auth::user()->workspace_id == $workspaces->id){
                $valid['status_date'] = $lead_status->title == 'Contacted' || $lead_status->title == 'Estimated'
                    ? date('Y-m-d')
                    : null;

                $valid['follow_up_at'] = $lead_status->title == 'Lost' || $lead_status->title == 'Future Follow Up'
                    ? date('Y-m-d')
                    : null;
            }

            $valid['won_at'] = $valid['lead_status_id'] == '13'
                ? date('Y-m-d')
                : null;
            if($request->email != ''){
                $valid['email'] = implode(',',$request->email);
            }
            $lead = Lead::create($valid);
            $files = [];
            if ($attachments && count($attachments)) {
                foreach ($attachments as $file) {
                    $filename = time() . '-' . bin2hex(random_bytes(10)) . '.' . $file->getClientOriginalExtension();
                    $path = Storage::disk('public')->putFileAs(FileHelper::LEADS_ATTACHMENT_DIR, $file, $filename);
                    $files[] = [
                        "name" => $filename,
                        "filename" => $file->getClientOriginalName(),
                        "mime" => $file->getClientMimeType(),
                        "path" => $path,
                        "disk" => 'public',
                        "collection" => 'leads.attachments',
                        "size" => $file->getSize(),
                        "uploaded_by_user_id" => Auth::id()
                    ];
                }
            }
            if (count($files)) {
                $lead->attachments()->createMany($files);
            }

            $lead_source_title = ['social media', 'seo / web inquiry', 'phone call', 'google ads', 'clutch', 'email campaign'];
            $old_lead_source_id = LeadSource::where(function($query) use ($lead_source_title) {
                foreach ($lead_source_title as $title) {
                    $query->orWhereRaw('LOWER(title) LIKE ?', ["%" . strtolower($title) . "%"]);
                }
            })->pluck('id')->toArray();

            $lead_source_id = $valid['lead_source_id'];
            $result = in_array($lead_source_id, $old_lead_source_id) ? 1 : 0;
            
            if ($valid['lead_status_id'] == 13 && $result) {
                $new_lead_source = LeadSource::where('id', $valid['lead_source_id'])->first();
                $message = "🎉 Hey Team!\nGreat news! We've just welcomed a new client from a {$new_lead_source->title} lead.\nFantastic job, everyone! Your hard work is truly appreciated. Let's keep the momentum going! 🚀";
            
                $webhookUrl = config('slack.CHANNEL'); // or hardcode if needed
                SlackHelper::leadMessages($webhookUrl, $message);
            }

            ActivityLogHelper::log(
                "lead.created",
                "Lead (".$lead->firstname.' '.$lead->lastname .") created by " . Auth::user()->full_name,
                [],
                $request,
                Auth::user(),
                $lead
            );

            LeadCreatedEvent::dispatch($lead);

            if ($request->ajax()) {
                return response()->json([
                    "success" => true,
                ], 201);
            }

            return redirect()
                ->route('leads.index');
        } catch (\Throwable $th) {
            Log::error($th);
            if ($request->ajax()) {
                return response()->json([
                    "success" => false,
                    "message" => $th->getMessage(),
                    "error" => $th,
                ], 500);
            }
            return redirect()
                ->route('leads.index');
        }
    }

    public function show(Lead $lead, Request $request)
    {
        try {
            $lead = Lead::query()
                ->where('id', $lead->id)
                ->select(
                    'leads.id',
                    'leads.lead_source_id',
                    // 'leads.lead_type_id',
                    'leads.lead_status_id',
                    'leads.firstname',
                    'leads.lastname',
                    'leads.mobile',
                    'leads.email',
                    'leads.country_id',
                    'leads.requirement',
                    // 'leads.project_budget',
                    'leads.prj_budget',
                    'leads.currency_id',
                    'leads.assigned_to',
                    'leads.created_at',
                    'leads.updated_at',
                    'leads.deleted_at'
                )
                ->with([
                    // "lead_type:id,title",
                    "lead_source:id,title",
                    "assignee:id,name,first_name,last_name",
                    "lead_status:id,title,css_class",
                    'currency:id,symbol',
                    'attachments:id,fileable_id,fileable_type,disk,path,name,filename',
                    "country_rel:id,name"
                ])
                ->when(Auth::user()->hasRole(['Admin', 'Superadmin','Marketing']), function ($query) {
                    return $query->withTrashed();
                })
                ->first();

            if (!Auth::user()->hasRole(['Admin', 'Superadmin','Marketing'])) {
                if ($lead->assigned_to != Auth::id()) {
                    throw new Error("Unauthorized");
                }
            }

            $lead_created_at = $lead->created_at
                ? $lead->created_at->setTimezone(Auth::user()->timezone)->format(DateHelper::LEAD_DATE_FORMAT)
                : null;
            $lead_updated_at = $lead->updated_at
                ? $lead->updated_at->setTimezone(Auth::user()->timezone)->format(DateHelper::LEAD_DATE_FORMAT)
                : null;
            $lead_deleted_at = $lead->deleted_at
                ? $lead->deleted_at->setTimezone(Auth::user()->timezone)->format(DateHelper::LEAD_DATE_FORMAT)
                : null;

            $lead = $lead->toArray();
            $lead['id'] = EncryptionHelper::encrypt($lead['id']);
            $lead['created_at'] = $lead_created_at;
            $lead['updated_at'] = $lead_updated_at;
            $lead['deleted_at'] = $lead_deleted_at;

            if ($request->ajax()) {
                return response()->json([
                    "lead" =>  $lead,
                    "success" => true,
                ]);
            }
        } catch (\Throwable $th) {
            Log::error($th);
            if ($request->ajax()) {
                return response()->json([
                    "success" => false,
                    "error" => $th,
                    "message" => $th->getMessage()
                ], 500);
            }
        }
    }

    public function edit(Lead $lead, Request $request)
    {
        try {
            if (!Auth::user()->hasRole(['Admin', 'Superadmin','Marketing'])) {
                if ($lead->assigned_to != Auth::id()) {
                    abort(403);
                }
            }
            $lead = Lead::query()
                ->where('id', $lead->id)
                ->select(
                    'leads.id',
                    'leads.lead_source_id',
                    // 'leads.lead_type_id',
                    'leads.lead_status_id',
                    'leads.firstname',
                    'leads.lastname',
                    'leads.mobile',
                    'leads.email',
                    'leads.country_id',
                    'leads.requirement',
                    // 'leads.project_budget',
                    'leads.prj_budget',
                    'leads.currency_id',
                    'leads.assigned_to',
                    'leads.created_at',
                    'leads.updated_at'
                )
                ->with([
                    // "lead_type:id,title",
                    "lead_source:id,title",
                    "assignee:id,name,first_name,last_name",
                    'currency:id,symbol',
                    "lead_status:id,title,css_class"
                ])
                ->first();

            $lead = $lead->toArray();
            $lead['id'] = EncryptionHelper::encrypt($lead['id']);

            if ($request->ajax()) {
                return response()->json([
                    "lead" => $lead,
                    "success" => true,
                ]);
            }
        } catch (\Throwable $th) {
            Log::error($th);
            if ($request->ajax()) {
                return response()->json([
                    "success" => false,
                    "error" => $th,
                    "message" => $th->getMessage()
                ], 500);
            }
        }
    }

    public function update(UpdateLeadRequest $request, Lead $lead)
    {
        try {
            $workspaces = Workspace::where('name','IIH Global')->first();
            if (!Auth::user()->hasRole(['Admin', 'Superadmin','Marketing'])) {
                if ($lead->assigned_to != Auth::id()) {
                    abort(403);
                }
            }
            $oldLead = $lead->replicate();
            $valid = $request->safe()->except(['attachments']);

            $lead_status = LeadStatus::where('id',$valid['lead_status_id'])->first();
            $old_date = $lead_status->title == 'New' ? null : $oldLead->status_date;

            if(Auth::user()->workspace_id == $workspaces->id){
                $valid['status_date'] = $lead_status->title == 'Contacted' && $oldLead->status_date == null
                    ? date('Y-m-d')
                    : $old_date ;


                $valid['follow_up_at'] = ($lead_status->title == 'Lost' || $lead_status->title == 'Future Follow Up')   && $oldLead->follow_up_at == null
                     ? date('Y-m-d')
                     : $oldLead->follow_up_at ;
            }

            $valid['won_at'] = $valid['lead_status_id'] == '13' /*&& $valid['lead_status_id'] != $oldLead->lead_status_id*/
                ? date('Y-m-d')
                : null;
            if($oldLead->lead_status_id == $valid['lead_status_id']){
                $valid['won_at'] = $oldLead->won_at;
            }

            if(!Auth::user()->hasRole('Marketing')){
                if(isset($request->email) && !empty($request->email)){
                    $valid['email'] = implode(',',$request->email);
                }else{
                    $valid['email'] = NULL;
                }
            }

            $lead->update($valid);
            $attachments = $request->has('attachments')
                ? $request->safe(['attachments'])['attachments']
                : [];
            $files = [];
            if ($attachments && count($attachments)) {
                foreach ($attachments as $file) {
                    $filename = time() . '-' . bin2hex(random_bytes(10)) . '.' . $file->getClientOriginalExtension();
                    $path = Storage::disk('public')->putFileAs(FileHelper::LEADS_ATTACHMENT_DIR, $file, $filename);
                    $files[] = [
                        "name" => $filename,
                        "filename" => $file->getClientOriginalName(),
                        "mime" => $file->getClientMimeType(),
                        "path" => $path,
                        "disk" => 'public',
                        "collection" => 'leads.attachments',
                        "size" => $file->getSize(),
                        "uploaded_by_user_id" => Auth::id()
                    ];
                }
            }
            if (count($files)) {
                $lead->attachments()->createMany($files);
            }

            $differences = [];
            if($oldLead->firstname != $valid['firstname']){
                $differences['firstname'] = $oldLead->firstname . ' => ' .$valid['firstname'];
            }
            if($oldLead->lastname != $valid['lastname']){
                $differences['lastname'] = $oldLead->lastname . ' => ' .$valid['lastname'];
            }

            if(!Auth::user()->hasRole('Marketing')){
                if($oldLead->mobile != $valid['mobile']){
                    $old_mobile =  $oldLead->mobile ?? '';
                    $new_mobile =  $valid['mobile'] ?? '';
                    $differences['mobile'] =  $old_mobile. ' => ' . $new_mobile;
                }
                $email = null;
                if(isset($valid['email']) && !empty($valid['email'])){
                    $email = $valid['email'];
                }
                if($oldLead->email != $email){
                    $old_email =  $oldLead->email ?? '';
                    $new_email =  $valid['email'] ?? '';
                    $differences['email'] = $old_email . ' => ' .$new_email;
                }
            }
            if($oldLead->lead_source_id != $valid['lead_source_id']){
                $old_lead_source = LeadSource::where('id',$oldLead->lead_source_id)->first();
                $new_lead_source = LeadSource::where('id',$valid['lead_source_id'])->first();
                $old_lead_source_title = $old_lead_source->title ?? '';
                $differences['lead_source_id'] = $old_lead_source_title . ' => ' .$new_lead_source->title;
            }
            if($oldLead->lead_status_id != $valid['lead_status_id']){
                $old_lead_status = LeadStatus::where('id',$oldLead->lead_status_id)->first();
                $new_lead_status = LeadStatus::where('id',$valid['lead_status_id'])->first();
                $old_lead_status_title = $old_lead_status->title ?? '';
                $differences['lead_status_id'] = $old_lead_status_title . ' => ' .$new_lead_status->title;
            }
            if($oldLead->assigned_to != $valid['assigned_to']){
                $old_assigned_to = User::where('id',$oldLead->assigned_to)->first();
                $new_assigned_to = User::where('id',$valid['assigned_to'])->first();
                $old_name = $old_assigned_to->name ?? '';
                $differences['assigned_to'] = $old_name . ' => ' .$new_assigned_to->name;
            }

            $logDescription = '';
            if(isset($differences) && !empty($differences)){
                $logDescription = ' Changes: ' . implode(', ', $differences);
            }

            $lead_source_title = ['social media', 'seo / web inquiry', 'phone call', 'google ads', 'clutch', 'email campaign'];
            $old_lead_source_id = LeadSource::where(function($query) use ($lead_source_title) {
                foreach ($lead_source_title as $title) {
                    $query->orWhereRaw('LOWER(title) LIKE ?', ["%" . strtolower($title) . "%"]);
                }
            })->pluck('id')->toArray();

            $lead_source_id = $valid['lead_source_id'];
            $result = in_array($lead_source_id, $old_lead_source_id) ? 1 : 0;

            if ($valid['lead_status_id'] == 13 && $result) {
                $new_lead_source = LeadSource::where('id', $valid['lead_source_id'])->first();
                $message = "🎉 Hey Team!\nGreat news! We've just welcomed a new client from a {$new_lead_source->title} lead.\nFantastic job, everyone! Your hard work is truly appreciated. Let's keep the momentum going! 🚀";
            
                $webhookUrl = config('slack.CHANNEL'); // or hardcode if needed
                SlackHelper::leadMessages($webhookUrl, $message);
            }

            ActivityLogHelper::log(
                "lead.updated",
                "Lead (".$lead->firstname.' '.$lead->lastname .") updated by " . Auth::user()->full_name . $logDescription,
                [],
                $request,
                Auth::user(),
                $lead
            );

            LeadUpdatedEvent::dispatch($lead, $oldLead);
            $user = User::where('id',$request->assigned_to)->first();
            if ($request->ajax()) {
                return response()->json([
                    "success" => true,
                    "show_confetti" => (($lead->lead_status_id == '13') && ($oldLead->lead_status_id != '13')),
                    "user_name" => ($user->first_name.' '.$user->last_name),
                    "customer_name" => ($request->firstname.' '.$request->lastname),
                ]);
            }

            return redirect()
                ->route('leads.index');
        } catch (\Throwable $th) {
            Log::error($th);
            if ($request->ajax()) {
                return response()->json([
                    "success" => false,
                    "message" => $th->getMessage(),
                    "error" => $th,
                ], 500);
            }
            return redirect()
                ->route('leads.index');
        }
    }

    public function destroy(Lead $lead, Request $request)
    {
        try {
            if (!Auth::user()->hasRole(['Admin', 'Superadmin','Marketing'])) {
                if ($lead->assigned_to != Auth::id()) {
                    abort(403);
                }
            }
            $lead->delete();

            ActivityLogHelper::log(
                "lead.deleted",
                "Lead deleted by " . Auth::user()->full_name,
                [],
                $request,
                Auth::user(),
                $lead
            );

            LeadDeletedEvent::dispatch($lead);

            if ($request->ajax()) {
                return response()->json([
                    "success" => true,
                ]);
            }

            return redirect()->route("leads.index");
        } catch (\Throwable $th) {
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

    public function restore(Request $request, Lead $lead)
    {
        try {
            if (!Auth::user()->hasRole(['Admin', 'Superadmin','Marketing'])) {
                if ($lead->assigned_to != Auth::id()) {
                    abort(403);
                }
            }

            $lead->restore();

            ActivityLogHelper::log(
                "lead.restored",
                "Lead restored by " . Auth::user()->full_name,
                [],
                $request,
                Auth::user(),
                $lead
            );

            LeadRestoredEvent::dispatch($lead);

            if ($request->ajax()) {
                return response()->json([
                    "success" => true,
                ]);
            }

            return redirect()
                ->route('leads.index');
        } catch (\Throwable $th) {
            Log::error($th);
            if ($request->ajax()) {
                return response()->json([
                    "success" => false,
                    "message" => $th->getMessage(),
                    "error" => $th,
                ], 500);
            }
            return redirect()
                ->route('leads.index');
        }
    }

    public function forceDelete(Lead $lead, Request $request)
    {
        DB::beginTransaction();
        try {
            $lead->load(['attachments', 'lead_notes']);
            $leadArr = $lead->toArray();
            $note_ids = $lead->lead_notes->pluck('id');

            Activity::where(function ($q) use ($lead) {
                $q->where('subject_id', $lead->id)->where('subject_type', Lead::class);
            })
                ->orWhere(function ($q) use ($note_ids) {
                    $q->whereIn('subject_id', $note_ids)->where('subject_type', LeadNote::class);
                })
                ->delete();
            LeadNote::where('lead_id', $lead->id)->forceDelete();
            $lead->forceDelete();
            ActivityLogHelper::log(
                "lead.force-deleted",
                "Lead permanently deleted by " . Auth::user()->full_name,
                [
                    'lead' => $leadArr
                ],
                $request,
                Auth::user(),
                null
            );
            DB::commit();
            if ($request->ajax()) {
                return response()->json([
                    "success" => true,
                ]);
            }
            return redirect()->route("leads.index");
        } catch (\Throwable $th) {
            Log::error($th);
            DB::rollback();
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

    public function exportFiltered(Request $request)
    {
        try {
            $lead_type = $request->lead_type ?? '';
            $start = Carbon::createFromFormat('d/m/Y', $request->export_created_at_start)
                ->startOfDay()
                ->shiftTimezone(Auth::user()->timezone)
                ->setTimezone(config('app.timezone', 'UTC'));

            $end = Carbon::createFromFormat('d/m/Y', $request->export_created_at_end)
                ->endOfDay()
                ->shiftTimezone(Auth::user()->timezone)
                ->setTimezone(config('app.timezone', 'UTC'));

            return (new LeadsExport)
                ->createdAtFrom($start)
                ->createdAtTo($end)
                ->leadType($lead_type)
                ->download('leads.csv', \Maatwebsite\Excel\Excel::CSV);
        } catch (\Throwable $th) {
            Log::info($th);
            return back();
        }
    }

    public function followUpDetails(Lead $lead)
    {
        return response()->json([
            'lead_email' =>  $lead->email ?? '',
            'sales_person_email'  => Auth::user()->email ?? '',
            'sales_person_phone'  => Auth::user()->phone ?? '',
        ]);
    }

    public function marketingMailReminderStatus(Request $request){
        try{
            $lead_id = EncryptionHelper::decrypt($request->lead_id);

            $mail_status['marketing_mail_reminder_status'] = isset($request->marketing_mail_reminder_status) && $request->marketing_mail_reminder_status == 1 ? 0 : 1;
            Lead::where('id',$lead_id)->update($mail_status);
            $lead = Lead::where('id',$lead_id)->first();

            $message = 'Marketing email enabled successfully!';
            if($mail_status['marketing_mail_reminder_status'] == 0){
                $message = 'Marketing email disabled successfully!';
            }
            $name = $lead->firstname.' '.$lead->lastname;

            ActivityLogHelper::log(
                'leads.marketing_mail_reminder_status',
                "Lead ({$name}) {$message}",
                [],
                request(),
                Auth::user(),
                $lead
            );
            return response()->json([
                'success' => true,
                'message' => $message,
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
