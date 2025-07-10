<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\DateHelper;
use App\Helpers\EncryptionHelper;
use App\Models\Currency;
use App\Models\MarketingExpense;
use App\Models\MarketingExpenseType;
use Carbon\Carbon;
use Faker\Extension\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class ExpenseMarketingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $marketing_expenses = MarketingExpense::query()
                ->select([
                    'marketing_expenses.deleted_at',
                    'marketing_expenses.id',
                    'marketing_expenses.amount',
                    'marketing_expenses.currency_id',
                    'marketing_expenses.marketing_expense_date',
                    'marketing_expenses.marketing_expense_type_id',
                ])->when($request->boolean('show_deleted'), function ($q) {
                    $q->withTrashed();
                })
                ->where('workspace_id',Auth::user()->workspace_id)
                ->withCount('marketing_expense_notes')
                ->with(
                    [
                        'currency:id,symbol',
                        'marketing_expense_type:id,title',
                    ]
                );

            return DataTables::eloquent($marketing_expenses)
                ->editColumn(
                    'id',
                    function (MarketingExpense $marketing_expenses) {
                        return EncryptionHelper::encrypt($marketing_expenses->id);
                    }
                )
                ->editColumn(
                    'marketing_expense_date',
                    function (MarketingExpense $marketing_expenses) {
                        return $marketing_expenses->marketing_expense_date->setTimezone(Auth::user()->timezone)->format(DateHelper::EXPENSE_DATE);
                    }
                )
                ->editColumn(
                    'marketing_expense_type_title',
                    function (MarketingExpense $marketing_expenses) {
                        return ($marketing_expenses->marketing_expense_types)
                            ? ($expense->marketing_expense_types->title ?? '')
                            : '';
                    }
                )
                ->editColumn('amount', function (MarketingExpense $marketing_expenses) {
                    $symbol = $marketing_expenses->currency->symbol ?? "";
                    $amount_formatted = $marketing_expenses->amount
                        ? number_format($marketing_expenses->amount, 2, '.', ',')
                        : $marketing_expenses->amount;

                    return "{$symbol}{$amount_formatted}";
                })
                ->filterColumn(
                    'marketing_expense_date',
                    function ($query, $keyword) {
                        $format = DateHelper::EXPENSE_DATE_MYSQL;
                        $timezone_offset = DateHelper::getGmtOffsetFromTimezone(Auth::user()->timezone);
                        $query->orWhereRaw("DATE_FORMAT(CONVERT_TZ(marketing_expenses.marketing_expense_date, '+00:00', '{$timezone_offset}'), '{$format}') like '%{$keyword}%'");
                    }
                )->toJson();
        }

        return view('marketing_expenses.index');
    }

    public function show(Request $request)
    {
        $id = collect(request()->segments())->last();
        $id = EncryptionHelper::decrypt($id);
        $expense = MarketingExpense::find($id);
        return view('marketing_expenses.show', compact('expense'));
    }

    public function store(Request $request)
    {
        try {
            $expense_data = $request->toArray();
            $expense_date   = Carbon::createFromFormat('d/m/Y', $expense_data['marketing_expense_date'])->startOfDay();

            $marketing_expense                                = new MarketingExpense;
            $marketing_expense->marketing_expense_type_id     = $expense_data['marketing_expense_type_id'];
            $marketing_expense->amount                        = $expense_data['amount'];
            $marketing_expense->currency_id                   = $expense_data['currency_id'];
            $marketing_expense->marketing_expense_date        = $expense_date->format('Y-m-d');
            $marketing_expense->workspace_id                  = Auth::user()->workspace_id;
            $marketing_expense->save();

            ActivityLogHelper::log(
                'marketing.expense.store',
                'Marketing Expanse created by '.Auth::user()->first_name.' '.Auth::user()->last_name,
                [],
                $request,
                Auth::user(),
                $marketing_expense
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

    public function edit(MarketingExpense $MarketingExpense)
    {
        $id = collect(request()->segments())->last();
        $id = EncryptionHelper::decrypt($id);
        $expense = MarketingExpense::find($id);
        $view_config['title'] = 'Edit Marketing';
        $expense_types = MarketingExpenseType::all(['id', 'title']);
        $currencies = Currency::orderBy('id')->get();

        return view('marketing_expenses.create', compact( 'expense_types', 'expense', 'view_config','currencies'));
    }

    public function update(Request $request)
    {
        try {
            $id = collect(request()->segments())->last();
            $id = EncryptionHelper::decrypt($id);

            $expense_data = $request->toArray();
            $expense_date   = Carbon::createFromFormat('d/m/Y', $expense_data['marketing_expense_date'])->startOfDay();

            $marketing_expense = MarketingExpenseType::orWhere(function($query) use ($expense_data) {
                $query->where('id', 'like', $expense_data['marketing_expense_type_id'] . '%');
            })->orWhere('title',$expense_data['marketing_expense_type_id'])->first();

            if($marketing_expense == null || empty($marketing_expense)){
                $marketing_expense =new  MarketingExpenseType;
                $marketing_expense->title = $expense_data['marketing_expense_type_id'];
                $marketing_expense->save();
            }

            $marketing_expense_type = $marketing_expense->id;

            $marketing_expense = MarketingExpense::find($id);
            $marketing_expense->marketing_expense_type_id       = $marketing_expense_type;
            $marketing_expense->amount                = $expense_data['amount'];
            $marketing_expense->currency_id           = $expense_data['currency_id'];
            $marketing_expense->marketing_expense_date          = $expense_date->format('Y-m-d');
            $marketing_expense->save();

            ActivityLogHelper::log(
                'marketing.expense.update',
                'Marketing Expanse updated by '.Auth::user()->first_name.' '.Auth::user()->last_name,
                [],
                $request,
                Auth::user(),
                $marketing_expense
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

    public function destroy(Request $request)
    {
        try {
            $id = collect(request()->segments())->last();
            $id = EncryptionHelper::decrypt($id);
            $expense = MarketingExpense::find($id);

            $expense->delete();

            ActivityLogHelper::log(
                'marketing.expense.delete',
                'Marketing expanse deleted by '.Auth::user()->first_name.' '.Auth::user()->last_name,
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

    public function marketingRestore(Request $request)
    {
        try {
            $id = EncryptionHelper::decrypt($request->id);

            $deleted_expense = MarketingExpense::onlyTrashed()->find($id);
            $deleted_expense->restore();

            ActivityLogHelper::log(
                'marketing.expense.restore',
                'Marketing expanse restored by '.Auth::user()->first_name.' '.Auth::user()->last_name,
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

    public function marketingCopy(MarketingExpense $any_marketing_expense)
    {
        $view_config['title'] = 'Copy Marketing';
        $id = request()->segment(3);
        $id = EncryptionHelper::decrypt($id);
        $expense = MarketingExpense::withTrashed()->find($id);
        $expense->marketing_expense_date = null;
        $expense->marketing_expense_type_id = null;
        $expense_types = MarketingExpenseType::all(['id', 'title']);
        $currencies = Currency::orderBy('id')->get();
        return view('marketing_expenses.create', compact( 'expense_types', 'expense', 'view_config','currencies'));
    }

    public function marketingCreateMany()
    {
        $expense_types = MarketingExpenseType::orderBy('id')->get(['id', 'title']);
        $currencies = Currency::orderBy('id')->get();

        return view('marketing_expenses.create-many', compact( 'expense_types','currencies'));
    }

    public function marketingStoreMany(Request $request)
    {
        DB::beginTransaction();
        try {
            $expenses_data  = $request['expenses'];

            foreach ($expenses_data as $index => $expense_data) {

                $marketing_expense = MarketingExpenseType::orWhere(function($query) use ($expense_data) {
                    $query->where('id', 'like', $expense_data['marketing_expense_type_id'] . '%');
                })->orWhere('title',$expense_data['marketing_expense_type_id'])->first();

                if($marketing_expense == null || empty($marketing_expense)){
                    $marketing_expense =new  MarketingExpenseType;
                    $marketing_expense->title = $expense_data['marketing_expense_type_id'];
                    $marketing_expense->save();
                }

                $marketing_expense_type = $marketing_expense->id;


                $expense_date   = Carbon::createFromFormat('d/m/Y', $expense_data['marketing_expense_date'])->startOfDay();
                $marketing_expense                             = new MarketingExpense;
                $marketing_expense->marketing_expense_type_id  = $marketing_expense_type;
                $marketing_expense->amount                     = $expense_data['amount'];
                $marketing_expense->currency_id                = $expense_data['currency_id'];
                $marketing_expense->marketing_expense_date     = $expense_date->format('Y-m-d');
                $marketing_expense->workspace_id               = Auth::user()->workspace_id;
                $marketing_expense->save();

                ActivityLogHelper::log(
                    'marketing.expense.store',
                    'Marketing expanse created by '.Auth::user()->first_name.' '.Auth::user()->last_name,
                    [],
                    $request,
                    Auth::user(),
                    $marketing_expense
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

    public function getMarketingRateCurrency(Request $request)
    {
        $salesArr = [
            "this_year_mar_exp" => 0,
            "last_year_mar_exp" => 0,
            "last_month_mar_exp" => 0,
            "this_month_mar_exp" => 0,
        ];

        try {
            $firstDayOfLastMonth = date('Y-m-d 00:00:00', strtotime("First day of last month"));
            $lastDayOfLastMonth = date('Y-m-d 23:59:59', strtotime("Last day of last month"));

            $firstDayOfThisMonth = date('Y-m-d 00:00:00', strtotime("First day of this month"));
            $lastDayOfThisMonth = date('Y-m-d 23:59:59', strtotime("Last day of this month"));

            $last2MonthInvoices = MarketingExpense::query()
                ->select(
                    'marketing_expenses.id',
                    'marketing_expenses.currency_id',
                    'marketing_expenses.amount',
                    'marketing_expenses.marketing_expense_date',
                )
                ->with([
                    'currency:id,code',
                ])
                ->where('marketing_expenses.marketing_expense_date', ">=", $firstDayOfLastMonth)
                ->where('marketing_expenses.marketing_expense_date', "<=", $lastDayOfThisMonth)
                ->where('marketing_expenses.workspace_id', Auth::user()->workspace_id)
                ->get();

            $last2MonthInvoices->each(function ($marketing_expense) use (
                $firstDayOfLastMonth,
                $lastDayOfLastMonth,
                $firstDayOfThisMonth,
                $lastDayOfThisMonth,
                &$salesArr
            ) {
                $currency_name = $marketing_expense['currency']['code'];
                $marketing_expense_date = $marketing_expense['marketing_expense_date'];
                $currencyGbpRates = CurrencyHelper::convert($currency_name, config('custom.statistics_currency'), $marketing_expense_date);

                $amount_in_gbp = $marketing_expense->amount * $currencyGbpRates->base_currency_rate;

                if ($marketing_expense->marketing_expense_date->between($firstDayOfLastMonth, $lastDayOfLastMonth)) {
                    $salesArr['last_month_mar_exp'] += $amount_in_gbp;
                } elseif ($marketing_expense->marketing_expense_date->between($firstDayOfThisMonth, $lastDayOfThisMonth)) {
                    $salesArr['this_month_mar_exp'] += $amount_in_gbp;
                }
            });

            $currentYear = date('Y');
            $previousYear = $currentYear - 1;

            $currentYearExpenses = $this->getYearlyExpenses($currentYear, Auth::user()->workspace_id);
            $previousYearExpenses = $this->getYearlyExpenses($previousYear, Auth::user()->workspace_id);

            $salesArr['this_year_mar_exp'] += $currentYearExpenses['total_yearly_expense'];
            $salesArr['last_year_mar_exp'] += $previousYearExpenses['total_yearly_expense'];

            $salesArr['currency_symbol'] = Currency::where('code', config('custom.statistics_currency', 'GBP'))->first()->symbol ?? "£";

            return response()->json($salesArr);
        } catch (\Throwable $th) {
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

    private function getYearlyExpenses($year, $workspaceId)
    {
        $yearlyExpenses = [];
        $totalYearlyExpense = 0;

        for ($month = 1; $month <= 12; $month++) {
            $firstDayOfMonth = date("Y-m-01", strtotime("$year-$month-01"));
            $lastDayOfMonth = date("Y-m-t", strtotime("$year-$month-01"));

            $monthInvoices = MarketingExpense::query()
                ->select('id', 'currency_id', 'amount', 'marketing_expense_date')
                ->with(['currency:id,code'])
                ->where('marketing_expense_date', '>=', $firstDayOfMonth)
                ->where('marketing_expense_date', '<=', $lastDayOfMonth)
                ->where('workspace_id', $workspaceId)
                ->get();

            $totalMonthlyExpense = 0;
            $monthInvoices->each(function ($marketing_expense) use (&$totalMonthlyExpense) {
                $currency_name = $marketing_expense->currency->code;
                $currencyGbpRates = CurrencyHelper::convert($currency_name, config('custom.statistics_currency'), $marketing_expense->marketing_expense_date);
                $amount_in_gbp = $marketing_expense->amount * $currencyGbpRates->base_currency_rate;
                $totalMonthlyExpense += $amount_in_gbp;
            });

            $yearlyExpenses[$month] = $totalMonthlyExpense;
            $totalYearlyExpense += $totalMonthlyExpense;
        }

        $yearlyExpenses['total_yearly_expense'] = $totalYearlyExpense;

        return $yearlyExpenses;
    }
}
