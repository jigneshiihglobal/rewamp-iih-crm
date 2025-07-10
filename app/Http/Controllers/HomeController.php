<?php

namespace App\Http\Controllers;

use App\Helpers\DateHelper;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\PaymentDetail;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\UserReview;
use Yajra\DataTables\Facades\DataTables;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $sources = LeadSource::orderBy('id')->get(['id', 'title']);
        $timezone_offset = DateHelper::getGmtOffsetFromTimezone(Auth::user()->timezone);

        $graph_total_years = Lead::select(DB::raw("YEAR(CONVERT_TZ(created_at, '+00:00', '{$timezone_offset}')) as year"))
            ->where('workspace_id', Auth::user()->workspace_id)
            ->havingRaw('year IS NOT NULL')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->pluck('year');

        $graph_won_years = Lead::select(DB::raw("YEAR(CONVERT_TZ(won_at, '+00:00', '{$timezone_offset}')) as year"))
            ->where('workspace_id', Auth::user()->workspace_id)
            ->havingRaw('year IS NOT NULL')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->pluck('year');

        $graph_years = $graph_total_years->merge($graph_won_years)->unique()->sortDesc();
        if(!$graph_years->count()) {
            $graph_years->push(date('Y'));
        }

        return view('dashboard', compact('sources', 'graph_years'));
    }

    public function getGraphData(Request $request)
    {
        try {
            $timezone_offset = DateHelper::getGmtOffsetFromTimezone(Auth::user()->timezone);

            $graph_total_data = Lead::select(DB::raw("MONTH(CONVERT_TZ(leads.created_at, '+00:00', '{$timezone_offset}')) as month, COUNT(*) as total, IFNULL(lead_sources.title, 'No Source') as lead_source_title"))
                ->where('leads.workspace_id', Auth::user()->workspace_id)
                ->whereRaw("YEAR(CONVERT_TZ(leads.created_at, '+00:00', '{$timezone_offset}'))=" . request('year', date('Y')))
                ->when($request->input('lead_source_id'), function ($query, $lead_source_id) {
                    $query->where('lead_source_id', $lead_source_id);
                })
                ->leftJoin('lead_sources', 'leads.lead_source_id', '=', 'lead_sources.id')
                ->groupBy('month')
                ->groupBy('lead_source_id')
                ->orderBy('month', 'asc')
                ->get();

            $graph_won_data = Lead::select(
                DB::raw("MONTH(CONVERT_TZ(won_at, '+00:00', '{$timezone_offset}')) as month"),
                DB::raw("COUNT(*) as won"),
                DB::raw("CONCAT(IFNULL(users.first_name, 'No'), ' ', IFNULL(users.last_name, 'Assignee')) as assignee_name"),
                DB::raw("GROUP_CONCAT(DISTINCT lead_sources.title ORDER BY lead_sources.title SEPARATOR ', ') as lead_source_title"),
                )
                ->where('leads.workspace_id', Auth::user()->workspace_id)
                ->whereNotNull('won_at')
                ->where('lead_status_id', '13')
                ->whereRaw("YEAR(CONVERT_TZ(won_at, '+00:00', '{$timezone_offset}')) = ?", [request('year', date('Y'))])
                ->when($request->input('lead_source_id'), function ($query, $lead_source_id) {
                    $query->where('lead_source_id', $lead_source_id);
                })
                ->leftJoin('users', 'leads.assigned_to', '=', 'users.id')
                ->leftJoin('lead_sources', 'leads.lead_source_id', '=', 'lead_sources.id')
                ->groupBy('month')
                ->groupBy('assignee_name')
                ->orderBy('assignee_name', 'asc')
                ->orderBy('month', 'asc')
                ->get();

            $graph_data = collect(array_merge($graph_won_data->toArray(), $graph_total_data->toArray()));

            $data = [
                "month" => [],
                "won" => [],
                "total" => [],
                "month_name" => [],
                "colors" => [],
                "tooltips" => [
                    0 => [],
                    1 => [],
                ],
            ];

            for ($i = 1; $i <= 12; $i++) {
                $month = sprintf('%02d', $i);
                $won_count = 0;
                $total_count = 0;
                $month_datas = $graph_data->where('month', $i);
                $assignee_name = '';

                if ($month_datas->count()) {
                    foreach ($month_datas as $month_data) {
                        if (isset($month_data['total'])) {
                            $total_count += $month_data['total'] ?? 0;
                            $data['tooltips'][0][$i-1][] = [
                                $month_data['lead_source_title'] => $month_data['total'] ?? 0
                            ];
                        }
                        elseif (isset($month_data['won'])) {
                            if($assignee_name != $month_data['assignee_name']){
                                $won_count += $month_data['won'] ?? 0;
                                $assignee_name = $month_data['assignee_name'];
                                $data['tooltips'][1][$i-1][] = [
                                    $month_data['assignee_name'] => $month_data['won'].' ('.$month_data['lead_source_title'] .')'?? 0,
                                ];
                            }
                        }
                    }
                }

                $data['month'][] = $month;
                $data['won'][] = $won_count ?? 0;
                $data['total'][] = $total_count ?? 0;
                $data['colors'][] = '#8B008B';
                $dateObj = DateTime::createFromFormat('!m', $month);
                $data['month_name'][] = $dateObj->format('F');
            }

            return response()->json($data);
        } catch (\Throwable $th) {
            throw $th;
            return response()->json([], 500);
        }
    }
    /* Won lead count Data With Workspace */
    public function getWonLeadData(Request $request)
    {
        try {
            $lead_status_won = 'Won';
            $roleName = 'User';

            if ($request->ajax()) {
                $users = User::select('users.id', 'users.pic','users.first_name', DB::raw('CONCAT(users.first_name, " ", users.last_name) as user_name'),
                    DB::raw('COALESCE(count(leads.assigned_to), 0) as count'),
                    DB::raw('MAX(leads.won_at) as max_won_at'))
                    ->leftJoin('leads', 'leads.assigned_to', '=', 'users.id')
                    ->leftJoin('lead_statuses', 'leads.lead_status_id', '=', 'lead_statuses.id')
                    ->whereHas('roles', function ($query) use ($roleName) {
                        $query->where('name', $roleName);
                    })
                    ->when($lead_status_won, function ($q, $lead_status_won) {
                        $q->where('lead_statuses.title', $lead_status_won);
                    })
                    ->when(
                        $request->winning_filter_create_at,
                        function ($query, $created_at) use ($request) {
                            $request
                                ->session()
                                ->put(
                                    'won_filter_created',
                                    $created_at
                                );
                            $query
                                ->when($created_at == 'won_this_year', function ($query) {
                                    $start = date('Y-01-01 00:00:00');
                                    $end = date('Y-12-31 23:59:59');

                                    return $query->whereBetween('leads.won_at', [$start, $end]);
                                })
                                ->when($created_at == 'won_previous_year', function ($query) {
                                    $start = date('Y-m-d 00:00:00', strtotime('First day of January last year'));
                                    $end = date('Y-m-d 23:59:59', strtotime('last day of December last year'));

                                    return $query->whereBetween('leads.won_at', [$start, $end]);
                                })
                                ->when(
                                    $created_at == 'won_custom' && $request->won_filter_created_at_range,
                                    function ($query) use ($request) {
                                        $filter_created_at_arr = explode(" to ", $request->won_filter_created_at_range);
                                        $start = date_create_from_format('d/m/Y H:i:s', $filter_created_at_arr[0] . " 00:00:00");
                                        $end = date_create_from_format('d/m/Y H:i:s', (isset($filter_created_at_arr[1]) ? $filter_created_at_arr[1] : $filter_created_at_arr[0]) . " 23:59:59");

                                        return $query->whereBetween('leads.won_at', [$start, $end]);
                                    },
                                );
                        },
                        function ($q) use ($request) {
                            $request
                                ->session()
                                ->forget(
                                    'review_filter_created'
                                );
                        }
                    )
                    ->where('users.is_active', 1)
                    ->where('leads.deleted_at', null)
                    ->where('leads.workspace_id', Auth::user()->workspace_id)
                    ->orderBy('count', 'DESC')
                    ->orderBy('max_won_at', 'ASC')
                    ->groupBy('users.id')->get();
            }

            $all_users = User::select('users.id','users.pic','users.first_name',DB::raw('CONCAT(users.first_name, " ", users.last_name) as user_name'))
                ->whereHas('roles', function ($query) use ($roleName) {
                    $query->where('name', $roleName);
                })->where('users.is_active',1)
                ->whereHas('workspaces', function ($query) {
                    $query->where('workspace_id', Auth::user()->workspace_id);
                })
                ->get();

            $wonLeadCollection = collect($users);
            $allUsersCollection = collect($all_users);

            /* Both collection are marge */
            $mergedCollection = $wonLeadCollection->merge($allUsersCollection);

            /* unique user get for both collection */
            $uniqueUsers = $mergedCollection->unique('id')->values();

            return DataTables::of($uniqueUsers)->toJson();
        } catch (\Throwable $th) {
            throw $th;
            return response()->json([], 500);
        }
    }

    /* User Review Data */
    public function getUserReviewData(Request $request)
    {
        try {
            $roleName = 'User';

            if ($request->ajax()) {
                $reviewLead = User::select('users.id','users.pic','users.first_name',DB::raw('CONCAT(users.first_name, " ", users.last_name) as user_name'),
                    DB::raw('COALESCE(SUM(review), 0) as review_count'),
                    DB::raw('MAX(user_reviews.review_date) as max_review_at'))
                    ->leftJoin('user_reviews', 'user_reviews.user_id', '=', 'users.id')
                    ->whereHas('roles', function ($query) use ($roleName) {
                        $query->where('name', $roleName);
                    })
                    ->whereHas('workspaces', function ($query) {
                        $query->where('workspace_id', Auth::user()->workspace_id);
                    })
                    ->when(
                        $request->kudos_filter_create_at,
                        function ($query, $created_at) use ($request) {
                            $request
                                ->session()
                                ->put(
                                    'review_filter_created',
                                    $created_at
                                );
                            $query
                                ->when($created_at == 'this_year', function ($query) {
                                    $start = date('Y-01-01 00:00:00');
                                    $end = date('Y-12-31 23:59:59');

                                    return $query->whereBetween('user_reviews.review_date', [$start, $end]);
                                })
                                ->when($created_at == 'previous_year', function ($query) {
                                    $start = date('Y-m-d 00:00:00', strtotime('First day of January last year'));
                                    $end = date('Y-m-d 23:59:59', strtotime('last day of December last year'));

                                    return $query->whereBetween('user_reviews.review_date', [$start, $end]);
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
                                                'review_filter_created_start',
                                                $start
                                            );
                                        $request
                                            ->session()
                                            ->put(
                                                'review_filter_created_end',
                                                $end
                                            );

                                        return $query->whereBetween('user_reviews.review_date', [$start, $end]);
                                    },
                                    function ($q) use ($request) {
                                        $request
                                            ->session()
                                            ->forget([
                                                'review_filter_created_start',
                                                'review_filter_created_end'
                                            ]);
                                    }
                                );
                        },
                        function ($q) use ($request) {
                            $request
                                ->session()
                                ->forget(
                                    'review_filter_created'
                                );
                        }
                    )
                    ->where('users.is_active',1)
                    ->orderBy('review_count', 'DESC')
                    ->orderBy('max_review_at', 'ASC')
                    ->groupBy('users.id')->get();
            }

            $all_users = User::select('users.id','users.pic','users.first_name',DB::raw('CONCAT(users.first_name, " ", users.last_name) as user_name'))
                ->whereHas('roles', function ($query) use ($roleName) {
                    $query->where('name', $roleName);
                })->where('users.is_active',1)
                 ->whereHas('workspaces', function ($query) {
                    $query->where('workspace_id', Auth::user()->workspace_id);
                })
                ->get();

            $reviewLeadCollection = collect($reviewLead);
            $allUsersCollection = collect($all_users);

            /* Both collection are marge */
            $mergedCollection = $reviewLeadCollection->merge($allUsersCollection);

            /* unique user get for both collection */
            $uniqueUsers = $mergedCollection->unique('id')->values();

            /*$reviewLead =  UserReview::select('users.id','users.pic','users.name as user_name', DB::raw('SUM(review) as review_count'))
                ->leftJoin('users', 'user_reviews.user_id', '=', 'users.id')
                ->orderBy('review_count', 'DESC')
                ->groupBy('users.id')
                ->get();*/

            return DataTables::of($uniqueUsers)->toJson();
        } catch (\Throwable $th) {
            throw $th;
            return response()->json([], 500);
        }
    }


    public function wonLead(Request $request){
        try{
            $assigneId = $request->input('assigneId');
            if ($request->ajax()) {
                $wonLeads = Lead::select(DB::raw("CONCAT(firstname, ' ', lastname) as lead_name"),'won_at','users.name as assigned_user','lead_sources.title as lead_sources_title')
                ->leftJoin('users', 'users.id', '=', 'leads.assigned_to')
                ->leftJoin('lead_sources', 'leads.lead_source_id', '=', 'lead_sources.id')
                ->when(
                    $request->winning_filter_create_at,
                    function ($query, $created_at) use ($request) {
                        $query
                            ->when($created_at == 'won_this_year', function ($query) {
                                $start = date('Y-01-01 00:00:00');
                                $end = date('Y-12-31 23:59:59');

                                return $query->whereBetween('leads.won_at', [$start, $end]);
                            })
                            ->when($created_at == 'won_previous_year', function ($query) {
                                $start = date('Y-m-d 00:00:00', strtotime('First day of January last year'));
                                $end = date('Y-m-d 23:59:59', strtotime('last day of December last year'));

                                return $query->whereBetween('leads.won_at', [$start, $end]);
                            })
                            ->when(
                                $created_at == 'won_custom' && $request->won_filter_created_at_range,
                                function ($query) use ($request) {
                                    $filter_created_at_arr = explode(" to ", $request->won_filter_created_at_range);
                                    $start = date_create_from_format('d/m/Y H:i:s', $filter_created_at_arr[0] . " 00:00:00");
                                    $end = date_create_from_format('d/m/Y H:i:s', (isset($filter_created_at_arr[1]) ? $filter_created_at_arr[1] : $filter_created_at_arr[0]) . " 23:59:59");

                                    return $query->whereBetween('leads.won_at', [$start, $end]);
                                },
                            );
                    }
                )
                ->where('leads.workspace_id', Auth::user()->workspace_id)
                ->where('leads.lead_status_id', '13')
                ->where('leads.deleted_at', null)
                ->where('users.is_active', 1)
                ->where('assigned_to',$assigneId)->whereNotNull('won_at');
            }

            return DataTables::of($wonLeads)->toJson();
        } catch (\Throwable $th) {
            throw $th;
            return response()->json([], 500);
        }
    }

    public function kudosList(Request $request)
    {
        try {
            $assigneId = $request->input('assigneId');

            if ($request->ajax()) {
                $reviewLead = UserReview::
                    where('user_id', $assigneId)
                    ->when(
                        $request->kudos_filter_create_at,
                        function ($query, $created_at) use ($request) {
                            $request
                                ->session()
                                ->put(
                                    'review_filter_created',
                                    $created_at
                                );
                            $query
                                ->when($created_at == 'this_year', function ($query) {
                                    $start = date('Y-01-01 00:00:00');
                                    $end = date('Y-12-31 23:59:59');

                                    return $query->whereBetween('user_reviews.review_date', [$start, $end]);
                                })
                                ->when($created_at == 'previous_year', function ($query) {
                                    $start = date('Y-m-d 00:00:00', strtotime('First day of January last year'));
                                    $end = date('Y-m-d 23:59:59', strtotime('last day of December last year'));

                                    return $query->whereBetween('user_reviews.review_date', [$start, $end]);
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
                                                'review_filter_created_start',
                                                $start
                                            );
                                        $request
                                            ->session()
                                            ->put(
                                                'review_filter_created_end',
                                                $end
                                            );

                                        return $query->whereBetween('user_reviews.review_date', [$start, $end]);
                                    },
                                    function ($q) use ($request) {
                                        $request
                                            ->session()
                                            ->forget([
                                                'review_filter_created_start',
                                                'review_filter_created_end'
                                            ]);
                                    }
                                );
                        },
                        function ($q) use ($request) {
                            $request
                                ->session()
                                ->forget(
                                    'review_filter_created'
                                );
                        }
                    )
                    ->get();
            }

            return DataTables::of($reviewLead)->toJson();
        } catch (\Throwable $th) {
            throw $th;
            return response()->json([], 500);
        }
    }

}
