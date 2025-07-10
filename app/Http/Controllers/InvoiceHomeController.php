<?php

namespace App\Http\Controllers;

use App\Enums\InvoicePaymentStatus;
use App\Enums\InvoiceType;
use App\Helpers\CurrencyHelper;
use App\Helpers\DateHelper;
use App\Models\CompanyDetail;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceHomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $sales_people = User::active()
            ->whereHas('workspaces', function ($query) {
                $query->where('workspaces.id', Auth::user()->workspace_id);
            })
            ->orderByRaw('CONCAT(users.first_name," ", users.last_name)')
            ->get(['id', 'first_name', 'last_name']);
        $timezone_offset = DateHelper::getGmtOffsetFromTimezone(Auth::user()->timezone);
        $graph_years = Invoice::select(DB::raw("YEAR(CONVERT_TZ(invoice_date, '+00:00', '{$timezone_offset}')) as year"))
            ->whereHas('client', function ($query) {
                $query->where('workspace_id',  Auth::user()->workspace_id);
            })
            ->havingRaw('year IS NOT NULL')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->sortDesc();

        if (!$graph_years->count()) {
            $graph_years->push(date('Y'));
        }

        $companies = CompanyDetail::where('workspace_id', Auth::user()->workspace_id)->get(['id', 'name']);

        return view('invoices.dashboard', compact('sales_people', 'graph_years','companies'));
    }

    public function getGraphData(Request $request)
    {
        try {
            $timezone_offset = DateHelper::getGmtOffsetFromTimezone(Auth::user()->timezone);
            $creditNoteSub = DB::table('invoices')
                ->select('grand_total', 'parent_invoice_id', 'deleted_at', 'type')
                ->orderByDesc('id');

            $invoices = DB::table('invoices', 'Invoice')
                ->when(!$request->has('payment_status') || $request->payment_status == InvoicePaymentStatus::PAID, function ($query) use ($timezone_offset) {
                    $paymentsSub = DB::table('payments')
                        ->select(
                            'invoice_id',
                            DB::raw('SUM(amount) as payments_sum_amount'),
                        )
                        ->groupBy('invoice_id');
                    $query
                        ->select(
                            DB::raw("currencies.code AS currency_code"),
                            DB::raw("CONCAT(IFNULL(users.first_name, 'No'), ' ', IFNULL(users.last_name, 'Sales Person')) as sales_person_name"),
                            DB::raw('SUM(IF(IFNULL(payments_sum_amount,0)>(Invoice.sub_total-IFNULL(Invoice.discount,0)), (Invoice.sub_total-IFNULL(Invoice.discount,0)), IFNULL(payments_sum_amount,0))) AS without_vat'),
                            DB::raw('SUM(IFNULL(payments_sum_amount,0)) AS with_vat'),
                            DB::raw("MONTH(CONVERT_TZ(Invoice.invoice_date, '+00:00', '{$timezone_offset}')) as month"),
                        )
                        ->leftJoinSub($paymentsSub, 'Payment', function ($join) {
                            $join->on('Payment.invoice_id', '=', 'Invoice.id');
                        });
                })
                ->when($request->payment_status == InvoicePaymentStatus::UNPAID, function ($query) use ($timezone_offset) {
                    $paymentsSub = DB::table('payments')
                        ->select(
                            'invoice_id',
                            DB::raw('SUM(amount) as payments_sum_amount'),
                        )
                        ->groupBy('invoice_id');
                    $query
                        ->select(
                            DB::raw("currencies.code AS currency_code"),
                            DB::raw("CONCAT(IFNULL(users.first_name, 'No'), ' ', IFNULL(users.last_name, 'Sales Person')) as sales_person_name"),
                            DB::raw('SUM(IF(IFNULL(Payment.payments_sum_amount,0)>(Invoice.sub_total-IFNULL(Invoice.discount,0)), 0, (Invoice.sub_total-IFNULL(Invoice.discount,0))-IFNULL(Payment.payments_sum_amount,0))) AS without_vat'),
                            DB::raw('SUM(GREATEST(Invoice.grand_total-IFNULL(Payment.payments_sum_amount,0)-IFNULL(CreditNote.grand_total,0), 0)) AS with_vat'),
                            DB::raw("MONTH(CONVERT_TZ(Invoice.invoice_date, '+00:00', '{$timezone_offset}')) as month"),
                        )
                        ->leftJoinSub($paymentsSub, 'Payment', function ($join) {
                            $join->on('Payment.invoice_id', '=', 'Invoice.id');
                        });
                })
                ->leftJoin('currencies', 'currencies.id', '=', 'Invoice.currency_id')
                ->leftJoin('users', 'users.id', '=', 'Invoice.user_id')
                ->leftJoinSub($creditNoteSub, 'CreditNote', function ($join) {
                    $join
                        ->on('CreditNote.parent_invoice_id', '=', 'Invoice.id')
                        ->whereNull('CreditNote.deleted_at')
                        ->where('CreditNote.type', InvoiceType::CREDIT_NOTE)
                        ->whereNotNull('CreditNote.parent_invoice_id');
                })
                ->where('Invoice.type', InvoiceType::INVOICE)
                ->whereRaw("YEAR(CONVERT_TZ(Invoice.invoice_date, '+00:00', '{$timezone_offset}'))=" . request('year', date('Y')))
                ->whereNull('Invoice.deleted_at')
                ->when($request->input('sales_person_id'), function ($query, $sales_person_id) {
                    $query->where('Invoice.user_id', $sales_person_id);
                })
                ->having('with_vat', '>', 0)
                ->groupBy('month', 'Invoice.currency_id', 'Invoice.user_id')
                ->get();

            $currency_codes = $invoices->pluck('currency_code')->unique()->toArray();
            $currency_rates = CurrencyHelper::convert($currency_codes, config('custom.invoice_dashboard_currency', 'GBP'));
            $display_currency_symbol = optional(Currency::select('symbol')
                ->where('code', config('custom.invoice_dashboard_currency', 'GBP'))
                ->first())->symbol ?? '£';

            $data = [
                "month" => [],
                "without_vat" => [],
                "with_vat" => [],
                "month_name" => [],
                "colors" => [],
                "tooltips" => [
                    0 => [],
                    1 => [],
                ],
                "currency_symbol" => $display_currency_symbol,
            ];

            for ($i = 1; $i <= 12; $i++) {
                $month = sprintf('%02d', $i);
                $without_vat_sum = 0;
                $with_vat_sum = 0;
                $month_datas = $invoices->where('month', $i);

                if ($month_datas->count()) {
                    foreach ($month_datas as $month_data) {
                        $currency_code  = $month_data->currency_code ?? 'GBP';
                        $display_currency_rate = $currency_rates->currency_rate ?? 1;
                        $with_vat = $month_data->with_vat ?? 0;
                        $without_vat = $month_data->without_vat ?? 0;
                        $with_vat_display_currency = $with_vat / $display_currency_rate;
                        $without_vat_display_currency = $without_vat / $display_currency_rate;
                        if (!isset($data['tooltips'][0][$i - 1])) {
                            $data['tooltips'][0][$i - 1] = [];
                        }
                        if (!isset($data['tooltips'][1][$i - 1])) {
                            $data['tooltips'][1][$i - 1] = [];
                        }

                        if (isset($month_data->with_vat)) {
                            $with_vat_sum += $with_vat_display_currency;
                            if (!isset($data['tooltips'][0][$i - 1][$month_data->sales_person_name])) {
                                $data['tooltips'][0][$i - 1][$month_data->sales_person_name] = 0;
                            }
                            $data['tooltips'][0][$i - 1][$month_data->sales_person_name] += $with_vat_display_currency ?? 0;
                        }
                        if (isset($month_data->without_vat)) {
                            $without_vat_sum += $without_vat_display_currency;
                            if (!isset($data['tooltips'][1][$i - 1][$month_data->sales_person_name])) {
                                $data['tooltips'][1][$i - 1][$month_data->sales_person_name] = 0;
                            }
                            $data['tooltips'][1][$i - 1][$month_data->sales_person_name] += $without_vat_display_currency ?? 0;
                        }
                    }
                }

                $data['month'][] = $month;
                $data['without_vat'][] = number_format((float)$without_vat_sum ?? 0, 2, '.', '');
                $data['with_vat'][] = number_format((float)$with_vat_sum ?? 0, 2, '.', '');
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
    public function getGraphDataV2(Request $request)
    {
        try {
            $timezone_offset = DateHelper::getGmtOffsetFromTimezone(Auth::user()->timezone);

            $invoices = Invoice::query()
                ->select(
                    'invoices.id',
                    'invoices.currency_id',
                    'invoices.sub_total',
                    'invoices.vat_total',
                    'invoices.grand_total',
                    /*'invoices.discount',*/
                    'invoices.user_id',
                    'invoices.invoice_date',
                    DB::raw("MONTH(CONVERT_TZ(invoices.invoice_date, '+00:00', '{$timezone_offset}')) as month"),
                )
                ->withSum('payments', 'amount')
                ->with([
                    'credit_note:id,parent_invoice_id,currency_id,grand_total',
                    'currency:id,code',
                ])
                ->where('invoices.type', InvoiceType::INVOICE)
                ->whereRaw("YEAR(CONVERT_TZ(invoices.invoice_date, '+00:00', '{$timezone_offset}'))=" . request('year', date('Y')))
                ->when($request->input('sales_person_id'), function ($query, $sales_person_id) {
                    $query->where('invoices.user_id', $sales_person_id);
                })
                ->whereHas('client', function ($query) {
                    $query->where('clients.workspace_id', Auth::user()->workspace_id);
                })
                ->get();

            $sales_people_ids = $invoices->pluck('user_id')->unique()->filter()->toArray();
            $sales_people = User::withTrashed()->whereIn('users.id', $sales_people_ids)->whereHas('workspaces', function ($query) {
                $query->where('workspaces.id', Auth::user()->workspace_id);
            })->get(['id', 'first_name', 'last_name']);
/*            $currency_codes = $invoices->pluck('currency.code','invoice_date')->toArray();
            $currency_rates = CurrencyHelper::rates($currency_codes, config('custom.invoice_dashboard_currency', 'GBP'));*/
            $display_currency_symbol = optional(Currency::select('symbol')
                ->where('code', config('custom.invoice_dashboard_currency', 'GBP'))
                ->first())->symbol ?? '£';

            $data = [
                "month" => [],
                "without_vat" => [],
                "with_vat" => [],
                "month_name" => [],
                "colors" => [],
                "tooltips" => [
                    0 => [],
                    1 => [],
                ],
                "currency_symbol" => $display_currency_symbol,
            ];

            for ($i = 1; $i <= 12; $i++) {
                $month = sprintf('%02d', $i);
                $without_vat_sum = 0;
                $with_vat_sum = 0;
                $month_invoices = $invoices->where('month', $i);

                if ($month_invoices->count()) {
                    foreach ($month_invoices as $invoice) {
                        $currency_codes = $invoice->currency->code;
                        $invoice_date = $invoice->invoice_date;
                        $currency_rates = CurrencyHelper::convert($currency_codes, config('custom.invoice_dashboard_currency', 'GBP'),$invoice_date);
                        $sales_person = $invoice->user_id
                            ? $sales_people->firstWhere('id', $invoice->user_id)
                            : null;
                        $sales_person_name = $sales_person
                            ? $sales_person->full_name
                            : 'No Sales Person';
                        $sub_total  = (float) $invoice->sub_total;
                        $payments_amount  = (float) $invoice->payments_sum_amount;
                        $currency_code  = /*'GBP'.*/$invoice->currency->code ?? 'GBP';
                        $display_currency_rate = (float) ($currency_rates->base_currency_rate ?? 1);
                        if ($request->has('payment_status') && $request->payment_status === InvoicePaymentStatus::UNPAID) {
                            $grand_total  = (float) $invoice->grand_total;
                            $cn_amount = (float) ($invoice->credit_note ? $invoice->credit_note->grand_total : 0);
                            /*$discount  = (float) $invoice->discount;*/
                            $due_amount_w_vat = $grand_total - $cn_amount - $payments_amount;
                            $due_amount_wo_vat = (float) max(0, ($sub_total - $cn_amount - $payments_amount));
                            /*$due_amount_wo_vat = (float) max(0, ($sub_total - $discount - $cn_amount - $payments_amount));*/
                            $with_vat = ($due_amount_w_vat * $display_currency_rate);
                            $without_vat = ($due_amount_wo_vat * $display_currency_rate);
                        } elseif ($request->has('payment_status') && $request->payment_status === InvoicePaymentStatus::PAID) {
                            $paid_amount_w_vat = $payments_amount;
                            $paid_amount_wo_vat = min($payments_amount, $sub_total);
                            $with_vat = ($paid_amount_w_vat * $display_currency_rate);
                            $without_vat = ($paid_amount_wo_vat * $display_currency_rate);
                        }else{
                            $cn_amount = (float) ($invoice->credit_note ? $invoice->credit_note->grand_total : 0);
                            /*$discount  = (float) $invoice->discount;*/
                            $all_amount_with_vat = ($invoice->grand_total - $cn_amount);
                            $all_amount_without_vat = (float) max(0, ($sub_total - $cn_amount));
                            /*$all_amount_without_vat = (float) max(0, ($sub_total - $discount - $cn_amount));*/
                            $with_vat = ($all_amount_with_vat * $display_currency_rate);
                            $without_vat =($all_amount_without_vat * $display_currency_rate);
                        }
                        $with_vat_sum += $with_vat;
                        $without_vat_sum += $without_vat;
                        if (!isset($data['tooltips'][0][$i - 1])) {
                            $data['tooltips'][0][$i - 1] = [];
                        }
                        if (!isset($data['tooltips'][1][$i - 1])) {
                            $data['tooltips'][1][$i - 1] = [];
                        }
                        if ($with_vat) {
                            if (!isset($data['tooltips'][0][$i - 1][$sales_person_name])) {
                                $data['tooltips'][0][$i - 1][$sales_person_name] = 0;
                            }
                            $data['tooltips'][0][$i - 1][$sales_person_name] += $with_vat;
                        }
                        if ($without_vat) {
                            if (!isset($data['tooltips'][1][$i - 1][$sales_person_name])) {
                                $data['tooltips'][1][$i - 1][$sales_person_name] = 0;
                            }
                            $data['tooltips'][1][$i - 1][$sales_person_name] += $without_vat;
                        }
                    }
                }

                $data['month'][] = $month;
                $data['without_vat'][] = number_format((float)$without_vat_sum, 2, '.', '');
                $data['with_vat'][] = number_format((float)$with_vat_sum, 2, '.', '');
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

    public function getVatGraphData(Request $request)
    {
        try {
            $timezone_offset = DateHelper::getGmtOffsetFromTimezone(Auth::user()->timezone);

            /* payment query */
            $company_id = $request->input('company_id');
            $workspace_id = Auth::user()->workspace_id;
            $invoices = Payment::select(
              'invoices.id',
              'invoices.currency_id',
              'invoices.sub_total',
              'invoices.vat_total',
              'invoices.grand_total',
              'invoices.user_id',
              'payments.paid_at',
              'currencies.code',
              'payments.amount',
              DB::raw("MONTH(CONVERT_TZ(paid_at, '+00:00', '{$timezone_offset}')) as month"),
          )->leftJoin('invoices', 'invoices.id', 'payments.invoice_id')
              ->leftJoin('payment_sources', 'payment_sources.id', 'payments.payment_source_id')
              ->leftJoin('company_details', 'company_details.id', 'invoices.company_detail_id')
              ->leftJoin('clients', 'clients.id', 'invoices.client_id')
              ->leftJoin('currencies', 'currencies.id', 'invoices.Currency_id')
              ->whereRaw("YEAR(CONVERT_TZ(paid_at, '+00:00', '{$timezone_offset}'))=" . request('year', date('Y')))
              ->where('invoices.type', InvoiceType::INVOICE)
              ->when(
                  isset($company_id),
                  function ($q) use($company_id){
                      $q->where('invoices.company_detail_id', $company_id);
                  })
              ->when(
                  isset($workspace_id),
                  function ($q) use($workspace_id){
                      $q->where('clients.workspace_id', $workspace_id);
                  })
              ->get();

            $display_currency_symbol = optional(Currency::select('symbol')
                ->where('code', config('custom.invoice_dashboard_currency', 'GBP'))
                ->first())->symbol ?? '£';

            $data = [
                "month" => [],
                "vat_amt" => [],
                "month_name" => [],
                "colors" => [],
                "currency_symbol" => $display_currency_symbol,
            ];

            for ($i = 1; $i <= 12; $i++) {
                $month = sprintf('%02d', $i);
                $vat_sum = 0;
                $month_invoices = $invoices->where('month', $i);

                if ($month_invoices->count()) {
                    foreach ($month_invoices as $invoice) {
                        $currency_codes = $invoice->code;
                        $invoice_date = $invoice->paid_at;
                        $currency_rates = CurrencyHelper::convert($currency_codes, config('custom.invoice_dashboard_currency', 'GBP'),$invoice_date);

                        $display_currency_rate = (float) ($currency_rates->base_currency_rate ?? 1);
                        $payments_amount  = (float) $invoice->grand_total;
                        $paid_amount  = (float) $invoice->amount;
                        $vat_amount  = (float) $invoice->vat_total;

                        /* paid vat */
                        $vat_amt =  (($paid_amount * $vat_amount / $payments_amount) * $display_currency_rate);
                        $vat_sum += $vat_amt;
                    }
                }

                $data['month'][] = $month;
                $data['vat_amt'][] = number_format((float)$vat_sum, 2, '.', '');
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
}
