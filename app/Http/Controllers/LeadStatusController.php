<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogHelper;
use App\Helpers\DateHelper;
use App\Helpers\EncryptionHelper;
use App\Http\Requests\StoreLeadStatusRequest;
use App\Http\Requests\UpdateLeadStatusRequest;
use App\Models\LeadStatus;
use App\Services\LeadStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class LeadStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $lead_statuses = LeadStatus::query()
                ->withTrashed()
                ->select(
                    "id",
                    "title",
                    "priority",
                    "css_class",
                    "created_at",
                    "deleted_at"
                )
                ->addSelect(DB::raw("(SELECT count(*) FROM lead_statuses ls2 where ls2.priority >= lead_statuses.priority) AS `order`"))
                ->withCount(['leads']);

            return DataTables::eloquent($lead_statuses)
                ->editColumn(
                    'id',
                    function (LeadStatus $lead_status) {
                        return EncryptionHelper::encrypt($lead_status->id);
                    }
                )
                ->editColumn(
                    'created_at',
                    function (LeadStatus $lead_status) {
                        return $lead_status->created_at->setTimezone(Auth::user()->timezone)->format(DateHelper::LEAD_DATE_FORMAT);
                    }
                )
                ->editColumn('css_class', function (LeadStatus $lead_status) {
                    return "<span class='badge {$lead_status->css_class} bg-gradient' >{$lead_status->title}</span>";
                })
                ->filterColumn(
                    'created_at',
                    function ($query, $keyword) {
                        $format = DateHelper::USER_CREATED_DATE_MYSQL;
                        $timezone_offset = DateHelper::getGmtOffsetFromTimezone(Auth::user()->timezone);
                        $query->orWhereRaw("DATE_FORMAT(CONVERT_TZ(leads.created_at, '+00:00', '{$timezone_offset}'), '{$format}') like '%{$keyword}%'");
                    }
                )
                ->filterColumn(
                    'order',
                    function ($query, $keyword) {
                        $query->orWhereRaw("(SELECT count(*) FROM lead_statuses ls2 where ls2.priority >= lead_statuses.priority) like '%{$keyword}%'");
                    }
                )
                ->rawColumns(['css_class'])
                ->toJson();
        }
        $lead_statuses =  LeadStatus::all(["id", "title"]);

        return view("lead_statuses.index", compact('lead_statuses'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreLeadStatusRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLeadStatusRequest $request)
    {
        $this->authorize('create', LeadStatus::class);
        try {

            $lead_status = (new LeadStatusService)->store($request->validated());

            ActivityLogHelper::log(
                'lead_status.created',
                'Lead status created',
                [],
                $request,
                Auth::user(),
                $lead_status
            );

            return response()
                ->json([
                    'success' => true
                ]);
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LeadStatus  $leadStatus
     * @return \Illuminate\Http\Response
     */
    public function show(LeadStatus $leadStatus)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LeadStatus  $leadStatus
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, LeadStatus $lead_status)
    {
        return response()->json([
            "lead_status" => $lead_status,
            "success" => true,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateLeadStatusRequest  $request
     * @param  \App\Models\LeadStatus  $leadStatus
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLeadStatusRequest $request, LeadStatus $lead_status)
    {
        $this->authorize('update', $lead_status);
        try {
            $lead_status = (new LeadStatusService)->update($lead_status, $request->validated());

            ActivityLogHelper::log(
                'lead_status.updated',
                'Lead status updated',
                [],
                $request,
                Auth::user(),
                $lead_status
            );

            return response()->json(["success" => true]);
        } catch (\Throwable $th) {

            Log::error($th);

            return response()->json([
                "success" => false,
                "error" => $th,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LeadStatus  $lead_status
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, LeadStatus $lead_status)
    {
        $this->authorize('delete', $lead_status);

        DB::beginTransaction();
        try {
            (new LeadStatusService)->delete($lead_status);

            ActivityLogHelper::log(
                'lead_status.deleted',
                'Lead status deleted',
                [],
                $request,
                Auth::user(),
                $lead_status
            );

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    "success" => true
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollback();
            if ($request->ajax()) {
                return response()->json([
                    "success" => false,
                    "error" => $th,
                    "message" => $th->getMessage()
                ], 500);
            }
        }
    }

    public function restore(Request $request, LeadStatus $lead_status)
    {
        $this->authorize('restore', $lead_status);

        try {
            (new LeadStatusService)->restore($lead_status);
            ActivityLogHelper::log(
                'lead_status.restored',
                'Lead status restored',
                [],
                $request,
                Auth::user(),
                $lead_status
            );

            return response()
                ->json([
                    'success' => true
                ]);
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

    public function forceDestroy(Request $request, LeadStatus $lead_status)
    {
        $this->authorize('forceDelete', $lead_status);

        try {
            (new LeadStatusService)->forceDelete($lead_status);
            ActivityLogHelper::log(
                'lead_status.force-deleted',
                'Lead status permanently deleted',
                [],
                $request,
                Auth::user(),
                $lead_status
            );

            return response()
                ->json([
                    'success' => true
                ]);
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

    public function generatePriority()
    {
        $priority = (float) LeadStatus::max('priority');
        return ++$priority;
    }
}
