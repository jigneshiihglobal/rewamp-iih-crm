<?php

namespace App\Http\Controllers;

use App\Enums\ExpenseFrequency;
use App\Enums\ExpenseType as EnumsExpenseType;
use App\Helpers\ActivityLogHelper;
use App\Helpers\DateHelper;
use App\Helpers\EncryptionHelper;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\StoreExpensesRequest;
use App\Models\Client;
use App\Models\Expense;
use App\Models\ExpenseSubType;
use App\Models\ExpenseType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $expenses = Expense::query()
                ->select([
                    'expenses.deleted_at',
                    'expenses.id',
                    'expenses.client_id',
                    'expenses.project_name',
                    'expenses.expense_sub_type_id',
                    'expenses.amount',
                    'expenses.currency_id',
                    'expenses.type',
                    'expenses.frequency',
                    'expenses.expense_date',
                    'expenses.remind_at',
                ])
                ->whereHas('client', function ($q) {
                    $q->where(
                        'clients.workspace_id',
                        Auth::user()->workspace_id
                    );
                })
                ->when($request->boolean('show_deleted'), function ($q) {
                    $q->withTrashed();
                })
                ->when($request->type_filter !== null, function ($q) use ($request) {
                    $q
                        ->where('type', $request->type_filter)
                        ->when($request->frequency_filter !== null, function ($q) use ($request) {
                            $q->where('frequency', $request->frequency_filter);
                        });
                })
                ->when($request->remind_at_filter, function ($query, $remind_at) use ($request) {
                    $query
                        ->when($remind_at == 'month', function ($query) {
                            $start = date('Y-m-d 00:00:00', strtotime("First day of this month"));
                            $end = date('Y-m-d 23:59:59', strtotime("Last day of this month"));

                            return $query->whereBetween('expenses.remind_at', [$start, $end]);
                        })
                        ->when($remind_at == 'last_month', function ($query) {
                            $start = date('Y-m-d 00:00:00', strtotime("First day of last month"));
                            $end = date('Y-m-d 23:59:59', strtotime("Last day of last month"));

                            return $query->whereBetween('expenses.remind_at', [$start, $end]);
                        })
                        ->when($remind_at == '3_months', function ($query) {
                            $start = date('Y-m-d 00:00:00', strtotime("First day of 3 months ago"));
                            $end = date('Y-m-d 23:59:59', strtotime("Last day of last month"));

                            return $query->whereBetween('expenses.remind_at', [$start, $end]);
                        })
                        ->when($remind_at == 'year', function ($query) {
                            $start = date('Y-01-01 00:00:00');
                            $end = date('Y-12-31 23:59:59');

                            return $query->whereBetween('expenses.remind_at', [$start, $end]);
                        })
                        ->when(
                            $remind_at == 'custom' && $request->remind_at_filter_range,
                            function ($query) use ($request) {
                                $filter_created_at_arr = explode(" to ", $request->remind_at_filter_range);
                                $start = date_create_from_format('d/m/Y H:i:s', $filter_created_at_arr[0] . " 00:00:00");
                                $end = date_create_from_format('d/m/Y H:i:s', (isset($filter_created_at_arr[1]) ? $filter_created_at_arr[1] : $filter_created_at_arr[0]) . " 23:59:59");

                                return $query->whereBetween('expenses.remind_at', [$start, $end]);
                            }
                        );
                })
                ->withCount('expense_notes')
                ->with(
                    [
                        'client:id,name,email',
                        'currency:id,symbol',
                        'expense_sub_type:id,expense_type_id,title',
                        'expense_sub_type.expense_type:id,title',
                    ]
                );

            return DataTables::eloquent($expenses)
                ->editColumn(
                    'id',
                    function (Expense $expense) {
                        return EncryptionHelper::encrypt($expense->id);
                    }
                )
                ->editColumn(
                    'expense_date',
                    function (Expense $expense) {
                        return $expense->expense_date->setTimezone(Auth::user()->timezone)->format(DateHelper::EXPENSE_DATE);
                    }
                )
                ->editColumn(
                    'remind_at',
                    function (Expense $expense) {
                        return $expense->remind_at->setTimezone(Auth::user()->timezone)->format(DateHelper::EXPENSE_REMIND_AT);
                    }
                )
                ->editColumn(
                    'expense_sub_type_title',
                    function (Expense $expense) {
                        return $expense->expense_sub_type
                            ? ($expense->expense_sub_type->title ?? '')
                            : '';
                    }
                )
                ->editColumn(
                    'expense_type_title',
                    function (Expense $expense) {
                        return ($expense->expense_sub_type && $expense->expense_sub_type->expense_type)
                            ? ($expense->expense_sub_type->expense_type->title ?? '')
                            : '';
                    }
                )
                ->editColumn('amount', function (Expense $expense) {
                    $symbol = $expense->currency->symbol ?? "";
                    $amount_formatted = $expense->amount
                        ? number_format($expense->amount, 2, '.', ',')
                        : $expense->amount;

                    return "{$symbol}{$amount_formatted}";
                })
                ->editColumn('frequency', function (Expense $expense) {
                    return ($expense->type == EnumsExpenseType::RECURRING)
                        ? (
                            ($expense->frequency == ExpenseFrequency::YEARLY)
                            ? "Yearly"
                            : 'Monthly'
                        )
                        : 'One-off';
                })
                ->filterColumn(
                    'expense_date',
                    function ($query, $keyword) {
                        $format = DateHelper::EXPENSE_DATE_MYSQL;
                        $timezone_offset = DateHelper::getGmtOffsetFromTimezone(Auth::user()->timezone);
                        $query->orWhereRaw("DATE_FORMAT(CONVERT_TZ(expenses.expense_date, '+00:00', '{$timezone_offset}'), '{$format}') like '%{$keyword}%'");
                    }
                )
                ->filterColumn(
                    'remind_at',
                    function ($query, $keyword) {
                        $format = DateHelper::EXPENSE_REMIND_AT_MYSQL;
                        $timezone_offset = DateHelper::getGmtOffsetFromTimezone(Auth::user()->timezone);
                        $query->orWhereRaw("DATE_FORMAT(CONVERT_TZ(expenses.remind_at, '+00:00', '{$timezone_offset}'), '{$format}') like '%{$keyword}%'");
                    }
                )
                ->orderColumn(
                    'remind_at',
                    "(
                        CASE
                            WHEN expenses.remind_at > CURDATE() THEN 0
                            WHEN expenses.remind_at <= CURDATE() THEN 1
                        END
                    ),
                    expenses.remind_at $1"
                )
                ->toJson();
        }

        return view('expenses.index');
    }

    public function show(Request $request, Expense $expense)
    {
        return view('expenses.show', compact('expense'));
    }

    public function create()
    {
        $expense = new Expense;
        $clients = Client::select('id', 'name')->where('workspace_id', Auth::user()->workspace_id)->orderBy('id', 'DESC')->get();
        $expense_types = ExpenseType::all(['id', 'title']);
        $expense_sub_types = !$expense_types->count()
            ? collect()
            : $expense_types
            ->first()
            ->expense_sub_types()
            ->select('id', 'title')
            ->get();

        return view('expenses.create', compact('clients', 'expense_types', 'expense_sub_types', 'expense'));
    }

    public function store(StoreExpenseRequest $request)
    {
        try {

            $valid          = $request->validated();
            $expense_date   = Carbon::createFromFormat('d/m/Y', $valid['expense_date'])->startOfDay();
            $type           = $valid['type'];
            $frequency      = $valid['frequency'];
            $remind_at      = $expense_date->copy();

            if ($type == EnumsExpenseType::RECURRING) {
                $today = today();
                if ($frequency == ExpenseFrequency::MONTHLY) {
                    $remind_at->subDays(5);
                    while ($remind_at->lte($today)) {
                        $remind_at->addMonths(1);
                    }
                } elseif ($frequency == ExpenseFrequency::YEARLY) {
                    $remind_at->subDays(5);
                    while ($remind_at->lte($today)) {
                        $remind_at->addYears(1);
                    }
                }
            }

            $expense                        = new Expense;
            $expense->client_id             = $valid['client_id'];
            $expense->project_name          = $valid['project_name'];
            $expense->expense_type_id       = $valid['expense_type_id'];
            $expense->expense_sub_type_id   = $valid['expense_sub_type_id'];
            $expense->amount                = $valid['amount'];
            $expense->currency_id           = $valid['currency_id'];
            $expense->type                  = $valid['type'];
            $expense->expense_date          = $expense_date->format('Y-m-d');
            $expense->remind_at             = $remind_at->format('Y-m-d');
            $expense->frequency             = ($valid['type'] == EnumsExpenseType::RECURRING)
                ? $valid['frequency']
                : NULL;
            $expense->save();

            ActivityLogHelper::log(
                'expense.store',
                'Expanse created by '.Auth::user()->first_name.' '.Auth::user()->last_name,
                [],
                $request,
                Auth::user(),
                $expense
            );

            DB::commit();

            return response()->json(['success' => true], 201);
        } catch (\Throwable $th) {
            Log::info($th);

            return response()->json([
                'error' => $th,
                'success' => false,
                'message' => "Something went wrong while adding expense!"
            ], 500);
        }
    }

    public function edit(Expense $expense)
    {
        abort_if(
            $expense->client->workspace_id != Auth::user()->workspace_id,
            400,
            "Workspace Error"
        );
        $view_config['title'] = 'Edit';
        $clients = Client::select('id', 'name')->where('workspace_id', Auth::user()->workspace_id)->orderBy('id', 'DESC')->get();
        $expense_types = ExpenseType::all(['id', 'title']);
        $expense_sub_types = ExpenseSubType::where('expense_type_id', $expense->expense_type_id)
            ->select('id', 'title')
            ->get();

        return view('expenses.create', compact('clients', 'expense_types', 'expense_sub_types', 'expense', 'view_config'));
    }

    public function update(StoreExpenseRequest $request, Expense $expense)
    {
        abort_if(
            $expense->client->workspace_id != Auth::user()->workspace_id,
            400,
            "Workspace Error"
        );
        try {

            $valid          = $request->validated();
            $expense_date   = Carbon::createFromFormat('d/m/Y', $valid['expense_date'])->startOfDay();
            $type           = $valid['type'];
            $frequency      = $valid['frequency'];
            $remind_at      = $expense_date->copy();

            if ($type == EnumsExpenseType::RECURRING) {
                $today = today();
                if ($frequency == ExpenseFrequency::MONTHLY) {
                    $remind_at->subDays(5);
                    while ($remind_at->lte($today)) {
                        $remind_at->addMonths(1);
                    }
                } elseif ($frequency == ExpenseFrequency::YEARLY) {
                    $remind_at->subDays(5);
                    while ($remind_at->lte($today)) {
                        $remind_at->addYears(1);
                    }
                }
            }

            $expense->client_id             = $valid['client_id'];
            $expense->project_name          = $valid['project_name'];
            $expense->expense_type_id       = $valid['expense_type_id'];
            $expense->expense_sub_type_id   = $valid['expense_sub_type_id'];
            $expense->amount                = $valid['amount'];
            $expense->currency_id           = $valid['currency_id'];
            $expense->type                  = $valid['type'];
            $expense->expense_date          = $expense_date->format('Y-m-d');
            $expense->remind_at             = $remind_at->format('Y-m-d');
            $expense->frequency             = ($valid['type'] == EnumsExpenseType::RECURRING)
                ? $valid['frequency']
                : NULL;

            $expense->save();

            ActivityLogHelper::log(
                'expense.update',
                'Expanse updated by '.Auth::user()->first_name.' '.Auth::user()->last_name,
                [],
                $request,
                Auth::user(),
                $expense
            );

            return response()->json(['success' => true], 200);
        } catch (\Throwable $th) {
            Log::info($th);

            return response()->json([
                'error' => $th,
                'success' => false,
                'message' => "Something went wrong while adding expense!"
            ], 500);
        }
    }

    public function destroy(Expense $expense)
    {
        abort_if(
            $expense->client->workspace_id != Auth::user()->workspace_id,
            400,
            "Workspace Error"
        );
        try {

            $expense->delete();

            ActivityLogHelper::log(
                'expense.delete',
                'Expanse deleted by '.Auth::user()->first_name.' '.Auth::user()->last_name,
                [],
                request(),
                Auth::user(),
                $expense
            );

            return response()->json(['success' => true], 200);
        } catch (\Throwable $th) {
            Log::info($th);

            return response()->json([
                'error' => $th,
                'success' => false,
                'message' => "Something went wrong while deleting expense!"
            ], 500);
        }
    }

    public function restore(Expense $deleted_expense)
    {

        abort_if(
            $deleted_expense->client->workspace_id != Auth::user()->workspace_id,
            400,
            "Workspace Error"
        );
        try {

            $deleted_expense->restore();

            ActivityLogHelper::log(
                'expense.restore',
                'Expanse restored by '.Auth::user()->first_name.' '.Auth::user()->last_name,
                [],
                request(),
                Auth::user(),
                $deleted_expense
            );

            return response()->json(['success' => true], 200);
        } catch (\Throwable $th) {
            Log::info($th);

            return response()->json([
                'error' => $th,
                'success' => false,
                'message' => "Something went wrong while restoring expense!"
            ], 500);
        }
    }

    public function copy(Expense $any_expense)
    {
        abort_if(
            $any_expense->client->workspace_id != Auth::user()->workspace_id,
            400,
            "Workspace Error"
        );
        $view_config['title'] = 'Copy';
        $expense = $any_expense->replicate();
        $expense->expense_date = null;
        $expense->expense_sub_type_id = null;
        $expense->expense_type_id = null;
        $clients = Client::select('id', 'name')->where('workspace_id', Auth::user()->workspace_id)->orderBy('id', 'DESC')->get();
        $expense_types = ExpenseType::all(['id', 'title']);
        $expense_sub_types = !$expense_types->count()
            ? collect()
            : $expense_types
            ->first()
            ->expense_sub_types()
            ->select('id', 'title')
            ->get();

        return view('expenses.create', compact('clients', 'expense_types', 'expense_sub_types', 'expense', 'view_config'));
    }

    public function createMany()
    {
        $clients = Client::where('workspace_id', Auth::user()->workspace_id)->orderBy('id', 'DESC')->get(['id', 'name']);
        $expense_types = ExpenseType::orderBy('id')->get(['id', 'title']);
        $expense_sub_types = !$expense_types->count()
            ? collect()
            : $expense_types
            ->first()
            ->expense_sub_types()
            ->select('id', 'title')
            ->orderBy('title')
            ->get();

        return view('expenses.create-many', compact('clients', 'expense_types', 'expense_sub_types'));
    }

    public function storeMany(StoreExpensesRequest $request)
    {
        DB::beginTransaction();
        try {

            $valid          = $request->validated();
            $expenses_data  = $valid['expenses'];
            foreach ($expenses_data as $index => $expense_data) {
                $expense_date   = Carbon::createFromFormat('d/m/Y', $expense_data['expense_date'])->startOfDay();
                $type           = $expense_data['type'];
                $frequency      = $expense_data['frequency'];
                $remind_at      = $expense_date->copy();

                if ($type == EnumsExpenseType::RECURRING) {
                    $today = today();
                    if ($frequency == ExpenseFrequency::MONTHLY) {
                        $remind_at->subDays(5);
                        while ($remind_at->lte($today)) {
                            $remind_at->addMonths(1);
                        }
                    } elseif ($frequency == ExpenseFrequency::YEARLY) {
                        $remind_at->subDays(5);
                        while ($remind_at->lte($today)) {
                            $remind_at->addYears(1);
                        }
                    }
                }

                $expense                        = new Expense;
                $expense->client_id             = $expense_data['client_id'];
                $expense->project_name          = $expense_data['project_name'];
                $expense->expense_type_id       = $expense_data['expense_type_id'];
                $expense->expense_sub_type_id   = $expense_data['expense_sub_type_id'];
                $expense->amount                = $expense_data['amount'];
                $expense->currency_id           = $expense_data['currency_id'];
                $expense->type                  = $expense_data['type'];
                $expense->expense_date          = $expense_date->format('Y-m-d');
                $expense->remind_at             = $remind_at->format('Y-m-d');
                $expense->frequency             = ($expense_data['type'] == EnumsExpenseType::RECURRING)
                    ? $expense_data['frequency']
                    : NULL;
                $expense->save();

                ActivityLogHelper::log(
                    'expense.store',
                    'Expanse created by '.Auth::user()->first_name.' '.Auth::user()->last_name,
                    [],
                    $request,
                    Auth::user(),
                    $expense
                );
            }

            DB::commit();

            return response()->json(['success' => true], 201);
        } catch (\Throwable $th) {

            DB::rollback();
            Log::info($th);

            return response()->json([
                'error' => $th,
                'success' => false,
                'message' => "Something went wrong while adding expense!"
            ], 500);
        }
    }
}
