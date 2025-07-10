<?php

namespace App\Http\Controllers;

use App\Helpers\DateHelper;
use App\Models\User;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            Activity::resolveRelationUsing('workspace', function ($activity) {
                return $activity->belongsTo(Workspace::class, 'workspace_id', 'id');
            });
            $activities = Activity::select('activity_log.*')
                // ->where('workspace_id', Auth::user()->workspace_id)
                ->with(['causer' => function ($query) {
                    $query->withTrashed();
                }, 'subject' => function ($query) {
                    $query->withTrashed();
                }, 'workspace:id,name,slug'])
                ->when($request->user_id, function ($query, $user_id) {
                    $query->where('causer_type', User::class)->where('causer_id', $user_id);
                })
                ->when(!Auth::user()->hasRole(['Admin', 'Superadmin']), function ($query) {
                    $query
                        ->where('causer_type', User::class)
                        ->where('causer_id', Auth::id())
                        ->where('workspace_id', Auth::user()->workspace_id);
                })
                ->when(!Auth::user()->hasRole(['Superadmin']), function ($query) {
                    $query->whereIn('causer_id', User::select('id')->whereDoesntHave('roles', function ($query) {
                        $query->where('name', 'Superadmin');
                    }));
                })
                ->when($request->created_at_start && $request->created_at_end, function ($query) {
                    $start = Carbon::createFromFormat('d/m/Y', request()->created_at_start)
                        ->startOfDay()
                        ->toDateTimeString();

                    $end = Carbon::createFromFormat('d/m/Y', request()->created_at_end)
                        ->endOfDay()
                        ->toDateTimeString();

                    $query->whereBetween("activity_log.created_at", [$start, $end]);
                });

            return DataTables::eloquent($activities)
                ->addColumn(
                    'causer',
                    '{{ $model->causer ? $model->causer->full_name : null }}'
                )
                ->addColumn(
                    'subject',
                    'activities.partials.subject'
                )
                ->addColumn(
                    'ip_address',
                    '{{ $model->properties ? $model->properties->get("ip_address") : null }}'
                )
                ->editColumn(
                    'created_at',
                    '{{ $model->created_at->setTimezone(\Illuminate\Support\Facades\Auth::user()->timezone)->format(\App\Helpers\DateHelper::LEAD_DATE_FORMAT)}}'
                )
                ->filterColumn(
                    'created_at',
                    function ($query, $keyword) {
                        $format = DateHelper::USER_CREATED_DATE_MYSQL;
                        $timezone_offset = DateHelper::getGmtOffsetFromTimezone(Auth::user()->timezone);
                        $query->orWhereRaw("DATE_FORMAT(CONVERT_TZ(activity_log.created_at, '+00:00', '{$timezone_offset}'), '{$format}') like '%{$keyword}%'");
                    }
                )
                ->filterColumn(
                    'ip_address',
                    function ($query, $keyword) {
                        $query->where("activity_log.properties->ip_address", 'like', "%{$keyword}%");
                    }
                )
                ->rawColumns(['subject'])
                ->toJson();
        }

        $users = Auth::user()->hasRole(['Admin', 'Superadmin']) ? User::withTrashed()->get(['first_name', 'last_name', 'id']) : new Collection([]);

        return view("activities.index", compact("users"));
    }
}
