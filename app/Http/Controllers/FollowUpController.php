<?php

namespace App\Http\Controllers;

use App\Enums\FollowUpStatus;
use App\Enums\FollowUpType;
use App\Helpers\ActivityLogHelper;
use App\Helpers\DateHelper;
use App\Helpers\EncryptionHelper;
use App\Http\Requests\StoreFollowUpRequest;
use App\Http\Requests\UpdateFollowUpRequest;
use App\Models\FollowUp;
use DateInterval;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class FollowUpController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $follow_ups = FollowUp::query()
                ->select(
                    'follow_ups.id',
                    'follow_ups.lead_id',
                    'follow_ups.send_reminder_at',
                    'follow_ups.type',
                    'follow_ups.status',
                    'follow_ups.created_at',
                    'follow_ups.deleted_at',
                )
                ->where('sales_person_id', Auth::id())
                ->whereHas('lead', function ($q) {
                    $q->where('leads.workspace_id', Auth::user()->workspace_id);
                })
                ->with(["lead:id,firstname,lastname"])
                ->when(
                    $request->send_reminder_at_filter,
                    function ($query, $remind_at)
                    use ($request) {
                        $query
                            ->when(
                                $remind_at == 'month',
                                function ($query) {
                                    $start = date(
                                        'Y-m-d 00:00:00',
                                        strtotime("First day of this month")
                                    );
                                    $end = date(
                                        'Y-m-d 23:59:59',
                                        strtotime("Last day of this month")
                                    );
                                    $start = date_create_from_format(
                                        'Y-m-d H:i:s',
                                        $start,
                                        new DateTimeZone(Auth::user()->timezone)
                                    )
                                        ->setTimezone(
                                            new DateTimeZone('UTC')
                                        );
                                    $end = date_create_from_format(
                                        'Y-m-d H:i:s',
                                        $end,
                                        new DateTimeZone(Auth::user()->timezone)
                                    )
                                        ->setTimezone(
                                            new DateTimeZone('UTC')
                                        );

                                    return $query
                                        ->whereBetween(
                                            'follow_ups.send_reminder_at',
                                            [$start, $end]
                                        );
                                }
                            )
                            ->when(
                                $remind_at == 'last_month',
                                function ($query) {
                                    $start = date(
                                        'Y-m-d 00:00:00',
                                        strtotime("First day of last month")
                                    );
                                    $end = date(
                                        'Y-m-d 23:59:59',
                                        strtotime("Last day of last month")
                                    );
                                    $start = date_create_from_format(
                                        'Y-m-d H:i:s',
                                        $start,
                                        new DateTimeZone(Auth::user()->timezone)
                                    )
                                        ->setTimezone(
                                            new DateTimeZone('UTC')
                                        );
                                    $end = date_create_from_format(
                                        'Y-m-d H:i:s',
                                        $end,
                                        new DateTimeZone(Auth::user()->timezone)
                                    )
                                        ->setTimezone(
                                            new DateTimeZone('UTC')
                                        );

                                    return $query
                                        ->whereBetween(
                                            'follow_ups.send_reminder_at',
                                            [$start, $end]
                                        );
                                }
                            )
                            ->when(
                                $remind_at == '3_months',
                                function ($query) {
                                    $start = date(
                                        'Y-m-d 00:00:00',
                                        strtotime("First day of 3 months ago")
                                    );
                                    $end = date(
                                        'Y-m-d 23:59:59',
                                        strtotime("Last day of last month")
                                    );
                                    $start = date_create_from_format(
                                        'Y-m-d H:i:s',
                                        $start,
                                        new DateTimeZone(Auth::user()->timezone)
                                    )
                                        ->setTimezone(new DateTimeZone('UTC'));
                                    $end = date_create_from_format(
                                        'Y-m-d H:i:s',
                                        $end,
                                        new DateTimeZone(Auth::user()->timezone)
                                    )
                                        ->setTimezone(new DateTimeZone('UTC'));

                                    return $query
                                        ->whereBetween(
                                            'follow_ups.send_reminder_at',
                                            [$start, $end]
                                        );
                                }
                            )
                            ->when(
                                $remind_at == 'year',
                                function ($query) {
                                    $start = date('Y-01-01 00:00:00');
                                    $end = date('Y-12-31 23:59:59');
                                    $start = date_create_from_format(
                                        'Y-m-d H:i:s',
                                        $start,
                                        new DateTimeZone(Auth::user()->timezone)
                                    )
                                        ->setTimezone(new DateTimeZone('UTC'));
                                    $end = date_create_from_format(
                                        'Y-m-d H:i:s',
                                        $end,
                                        new DateTimeZone(Auth::user()->timezone)
                                    )
                                        ->setTimezone(new DateTimeZone('UTC'));

                                    return $query
                                        ->whereBetween('follow_ups.send_reminder_at', [$start, $end]);
                                }
                            )
                            ->when(
                                $remind_at == 'custom' && $request->send_reminder_at_filter_range,
                                function ($query) use ($request) {
                                    $filter_created_at_arr = explode(
                                        " to ",
                                        $request->send_reminder_at_filter_range
                                    );
                                    $start = date_create_from_format(
                                        'd/m/Y H:i:s',
                                        $filter_created_at_arr[0] . " 00:00:00",
                                        new DateTimeZone(Auth::user()->timezone)
                                    )
                                        ->setTimezone(new DateTimeZone('UTC'));
                                    $end = date_create_from_format(
                                        'd/m/Y H:i:s',
                                        (isset($filter_created_at_arr[1])
                                            ? $filter_created_at_arr[1]
                                            : $filter_created_at_arr[0]
                                        ) .
                                            " 23:59:59",
                                        new DateTimeZone(Auth::user()->timezone)
                                    )
                                        ->setTimezone(new DateTimeZone('UTC'));

                                    return $query->whereBetween('follow_ups.send_reminder_at', [$start, $end]);
                                }
                            );
                    }
                )
                ->when(
                    $request->boolean('show_completed_filter'),
                    function ($q) {
                        $q
                            ->whereIn(
                                'status',
                                FollowUpStatus::values()
                            );
                    },
                    function ($q) {
                        $q
                            ->where(
                                'status',
                                FollowUpStatus::PENDING
                            );
                    }
                );

            return DataTables::eloquent($follow_ups)
                ->addColumn(
                    'lead_name',
                    function (FollowUp $follow_up) {
                        return
                            $follow_up->lead->firstname .
                            " "  .
                            $follow_up->lead->lastname;
                    }
                )
                ->editColumn(
                    'encrypted_id',
                    function (FollowUp $follow_up) {
                        return EncryptionHelper::encrypt($follow_up->id);
                    }
                )
                ->editColumn(
                    'created_at',
                    function (FollowUp $follow_up) {
                        return $follow_up
                            ->created_at
                            ->setTimezone(Auth::user()->timezone)
                            ->format(DateHelper::FOLLOW_UP_CREATED);
                    }
                )
                ->editColumn(
                    'send_reminder_at',
                    function (FollowUp $follow_up) {
                        return $follow_up
                            ->send_reminder_at
                            ->setTimezone(Auth::user()->timezone)
                            ->format(DateHelper::FOLLOW_UP_REMIND_AT);
                    }
                )
                ->filterColumn(
                    'lead_name',
                    function ($q, $s) {
                        $q
                            ->whereHas('lead', function ($q) use ($s) {
                                $q
                                    ->whereRaw(
                                        'CONCAT(IFNULL(leads.firstname," "), " ", IFNULL(leads.lastname," ")) LIKE ?',
                                        ["%" . $s . "%"]
                                    )
                                    ->orWhereRaw(
                                        'CONCAT(IFNULL(leads.lastname," "), " ", IFNULL(leads.firstname," ")) LIKE ?',
                                        ["%" . $s . "%"]
                                    )
                                    ->orWhereRaw(
                                        'IFNULL(leads.firstname," ") LIKE ?',
                                        ["%" . $s . "%"]
                                    )
                                    ->orWhereRaw(
                                        'IFNULL(leads.lastname," ") LIKE ?',
                                        ["%" . $s . "%"]
                                    );
                            });
                    }
                )
                ->filterColumn(
                    'created_at',
                    function ($query, $keyword) {
                        $format = DateHelper::FOLLOW_UP_CREATED_MYSQL;
                        $timezone_offset = DateHelper::getGmtOffsetFromTimezone(Auth::user()->timezone);
                        $query->orWhereRaw("DATE_FORMAT(CONVERT_TZ(follow_ups.created_at, '+00:00', '{$timezone_offset}'), '{$format}') like '%{$keyword}%'");
                    }
                )
                ->filterColumn(
                    'send_reminder_at',
                    function ($query, $keyword) {
                        $format = DateHelper::FOLLOW_UP_REMIND_AT_MYSQL;
                        $timezone_offset = DateHelper::getGmtOffsetFromTimezone(Auth::user()->timezone);
                        $query->orWhereRaw("DATE_FORMAT(CONVERT_TZ(follow_ups.send_reminder_at, '+00:00', '{$timezone_offset}'), '{$format}') like '%{$keyword}%'");
                    }
                )
                ->toJson();
        }

        return view("follow_ups.index");
    }

    public function store(StoreFollowUpRequest $request)
    {

        try {
            $valid = $request->validated();

            $valid['sales_person_id'] = Auth::id();

            $valid['send_reminder_at'] = date_create_from_format(
                DateHelper::FOLLOW_UP_DATE,
                $valid['follow_up_at'],
                new DateTimeZone(Auth::user()->timezone)
            )
                ->setTimezone(new DateTimeZone('UTC'));

            $valid['follow_up_at'] = date_create_from_format(
                DateHelper::FOLLOW_UP_DATE,
                $valid['follow_up_at'],
                new DateTimeZone(Auth::user()->timezone)
            )
                ->setTimezone(new DateTimeZone('UTC'));

            if ($valid['type'] == FollowUpType::CALL) {
                $valid['send_reminder_at'] = $valid['send_reminder_at']
                    ->sub(new DateInterval("PT1H")); // (follow_up_at minus 1 hour)
            }

            $follow_up = FollowUp::create($valid);

            ActivityLogHelper::log(
                'follow_ups.created',
                'Follow up created',
                [],
                $request,
                Auth::user(),
                $follow_up
            );

            return response()->json([
                'success' => true
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'error' => $th
            ], 500);
        }
    }

    public function edit(FollowUp $follow_up)
    {
        abort_if($follow_up->sales_person_id != Auth::id(), 404, "Follow up not found!");
        try {

            $follow_up->loadMissing(['sales_person', 'lead']);

            return response()->json($follow_up);
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'error' => $th
            ], 500);
        }
    }

    public function update(UpdateFollowUpRequest $request, FollowUp $follow_up)
    {
        abort_if($follow_up->sales_person_id != Auth::id(), 404, "Follow up not found!");
        try {
            $valid = $request->validated();
            $valid['bcc'] = isset($valid['bcc']) ? $valid['bcc'] : NULL;

            $valid['send_reminder_at'] = date_create_from_format(
                DateHelper::FOLLOW_UP_DATE,
                $valid['follow_up_date'] . " " . $valid['follow_up_time'],
                new DateTimeZone(Auth::user()->timezone)
            )
                ->setTimezone(new DateTimeZone('UTC'));

            $valid['follow_up_at'] = date_create_from_format(
                DateHelper::FOLLOW_UP_DATE,
                $valid['follow_up_date'] . " " . $valid['follow_up_time'],
                new DateTimeZone(Auth::user()->timezone)
            )
                ->setTimezone(new DateTimeZone('UTC'));

            if ($valid['type'] == FollowUpType::CALL) {
                $valid['send_reminder_at'] = $valid['send_reminder_at']
                    ->sub(new DateInterval("PT1H")); // (follow_up_at minus 1 hour)
            }

            unset($valid['follow_up_date'], $valid['follow_up_time']);

            $follow_up->update($valid);

            ActivityLogHelper::log(
                'follow_ups.updated',
                'Follow up updated',
                [
                    "params" => $valid
                ],
                $request,
                Auth::user(),
                $follow_up
            );

            return response()->json([
                'success' => true
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'error' => $th
            ], 500);
        }
    }

    public function destroy(Request $request,  FollowUp  $follow_up)
    {
        abort_if($follow_up->sales_person_id != Auth::id(), 404, "Follow up not found!");
        try {

            $follow_up->delete();

            ActivityLogHelper::log(
                'follow_ups.deleted',
                'Follow up deleted',
                [],
                $request,
                Auth::user(),
                $follow_up
            );

            return response()
                ->json([
                    'success' => true
                ], 200);
        } catch (\Throwable $th) {
            return response()
                ->json([
                    'success' => false,
                    'message' => $th->getMessage(),
                    'error' => $th
                ], 500);
        }
    }
}
