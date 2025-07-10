<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogHelper;
use App\Helpers\DateHelper;
use App\Helpers\EncryptionHelper;
use App\Http\Requests\StoreLeadSourceRequest;
use App\Http\Requests\UpdateLeadSourceRequest;
use App\Models\LeadSource;
use App\Services\LeadSourceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class LeadSourceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $lead_statuses = LeadSource::query()
                ->withTrashed()
                ->select(
                    "id",
                    "title",
                    "created_at",
                    "deleted_at"
                )
                ->withCount(['leads']);

            return DataTables::eloquent($lead_statuses)
                ->editColumn(
                    'id',
                    function (LeadSource $lead_statuses) {
                        return EncryptionHelper::encrypt($lead_statuses->id);
                    }
                )
                ->editColumn(
                    'created_at',
                    function (LeadSource $lead_statuses) {
                        return $lead_statuses->created_at->setTimezone(Auth::user()->timezone)->format(DateHelper::LEAD_DATE_FORMAT);
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
                ->toJson();
        }

        return view(
            "lead_sources.index"
        );
    }

    public function store(StoreLeadSourceRequest $request)
    {
        try {
            $lead_source = (new LeadSourceService)->store($request->validated());

            ActivityLogHelper::log(
                'lead_source.created',
                'Lead source created',
                [],
                $request,
                Auth::user(),
                $lead_source
            );

            return response()
                ->json([
                    'success' => true
                ], 201);
        } catch (\Throwable $th) {
            Log::info($th);
            return response()
                ->json([
                    'success' => false,
                    'message' => $th->getMessage(),
                    'error' => $th
                ], 500);
        }
    }

    public function edit(LeadSource $lead_source)
    {
        return response()->json([
            "lead_source" => $lead_source,
            "success" => true,
        ]);
    }

    public function update(UpdateLeadSourceRequest $request, LeadSource $lead_source)
    {
        $this->authorize('update', $lead_source);
        try {
            $lead_source = (new LeadSourceService)->update($lead_source, $request->validated());

            ActivityLogHelper::log(
                'lead_source.updated',
                'Lead status updated',
                [],
                $request,
                Auth::user(),
                $lead_source
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

    public function destroy(Request $request, LeadSource $lead_source)
    {

        $this->authorize('delete', $lead_source);

        DB::beginTransaction();
        try {
            (new LeadSourceService)->delete($lead_source);

            ActivityLogHelper::log(
                'lead_source.deleted',
                'Lead source deleted',
                [],
                $request,
                Auth::user(),
                $lead_source
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

    public function restore(Request $request, LeadSource $lead_source)
    {
        $this->authorize('restore', $lead_source);

        try {
            (new LeadSourceService)->restore($lead_source);
            ActivityLogHelper::log(
                'lead_source.restored',
                'Lead source restored',
                [],
                $request,
                Auth::user(),
                $lead_source
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

    public function forceDestroy(Request $request, LeadSource $lead_source)
    {
        $this->authorize('forceDelete', $lead_source);

        try {
            (new LeadSourceService)->forceDelete($lead_source);
            ActivityLogHelper::log(
                'lead_source.force-deleted',
                'Lead source permanently deleted',
                [],
                $request,
                Auth::user(),
                $lead_source
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
}
