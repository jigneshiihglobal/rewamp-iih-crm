<?php

namespace App\Exports;

use App\Helpers\CurrencyHelper;
use App\Helpers\DateHelper;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use DateTime;
use DateTimeZone;

class PaymentReceiptExport implements FromQuery,WithHeadings,WithMapping,WithCustomCsvSettings
{
    use Exportable;

    public function payment_source_id(
        int $payment_source_id
    ): PaymentReceiptExport {
        $this->export_payment_source_id = $payment_source_id;
        return $this;
    }

    public function from_date(
        DateTime $from_date
    ): PaymentReceiptExport {
        $this->export_from_date = $from_date;
        return $this;
    }

    public function to_date(
        DateTime $to_date
    ): PaymentReceiptExport {
        $this->export_to_date = $to_date;
        return $this;
    }

    public function client_id(
        int $client_id
    ): PaymentReceiptExport {
        $this->export_client_id = $client_id;
        return $this;
    }

    public function company_detail_id(
        int $company_detail_id
    ): PaymentReceiptExport {
        $this->export_company_id = $company_detail_id;
        return $this;
    }

    public function workspace(
        int $workspace_id
    ): PaymentReceiptExport {
        $this->workspace_id = $workspace_id;
        return $this;
    }

    public function getCsvSettings(): array
    {
        return [
            // 'delimiter' => ';',
            'use_bom' => true,
            // 'output_encoding' => 'ISO-8859-1',
        ];
    }

    public function headings(): array
    {
        return [
            'Invoice Number',
            'Customer Name',
            'Company Name',
            'Currency_id',
            'symbol',
            'Code',
            'SubTotal',
            'Vat Total',
            'Grand Total',
            'Payment Amount',
            'Payment Source',
            'Source_id',
            'paid_at_date',
        ];
    }

    public function map($invoice): array
    {
        $company_name            = $invoice->company_name ?? '';
        $client_name             = $invoice->client_name ?? '';
        $currency_id             = $invoice->Currency_id ?? '';
        $currency_symbol         = $invoice->symbol ?? '';
        $currency_code           = $invoice->code ?? 'GBP';
        $sub_total               = (float) $invoice->sub_total;
        $sub_total_formatted     = number_format($sub_total, 2, '.', ',');
        $vat_total               = (float) $invoice->vat_total;
        $vat_total_formatted     = $vat_total ? number_format($vat_total, 2, '.', ',') : '';
        $grand_total             = (float) $invoice->grand_total;
        $grand_total_formatted   = number_format($grand_total, 2, '.', ',');
        $payment_sum_amount      = (float) $invoice->amount;
        $payment_source_name     =  $invoice->payment_source_name;
        $payment_source_id       =  $invoice->payment_source_id ?? '';
        $paid_at_date            =  $invoice->paid_at->format('d-m-Y');

        return [
            $invoice->invoice_number ?? '',
            $client_name ?? '',
            $company_name,
            $currency_id,
            $currency_symbol,
            $currency_code,
            $sub_total_formatted,
            $vat_total_formatted,
            $grand_total_formatted,
            $payment_sum_amount,
            $payment_source_name,
            $payment_source_id,
            $paid_at_date,
        ];
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        $timezone_offset = DateHelper::getGmtOffsetFromTimezone(Auth::user()->timezone);

        return Payment::select(
            'invoices.invoice_number',
            'clients.name as client_name',
            'company_details.name as company_name',
            'invoices.Currency_id',
            'currencies.symbol',
            'currencies.code',
            'invoices.sub_total',
            'invoices.vat_total',
            'invoices.grand_total',
            'payments.amount',
            'payment_sources.title as payment_source_name',
            'payments.payment_source_id',
            'payments.paid_at',
        )->leftJoin('invoices', 'invoices.id', 'payments.invoice_id')
            ->leftJoin('payment_sources', 'payment_sources.id', 'payments.payment_source_id')
            ->leftJoin('company_details', 'company_details.id', 'invoices.company_detail_id')
            ->leftJoin('clients', 'clients.id', 'invoices.client_id')
            ->leftJoin('currencies', 'currencies.id', 'invoices.Currency_id')
            ->when(
                isset($this->export_payment_source_id) && !empty($this->export_payment_source_id),
                function ($q) {
                    $q->where('payments.payment_source_id', $this->export_payment_source_id);
                })->when(
                isset($this->export_from_date, $this->export_to_date),
                function ($q) {
                    $q->whereBetween('payments.paid_at', [$this->export_from_date->startOfDay(), $this->export_to_date->endOfDay()]);
                })->when(
                isset($this->export_client_id),
                function ($q) {
                    $q->where('invoices.client_id', $this->export_client_id);
                })->when(
                isset($this->export_company_id),
                function ($q) {
                    $q->where('invoices.company_detail_id', $this->export_company_id);
                })->when(
                isset($this->workspace_id),
                function ($q) {
                        $q->where('clients.workspace_id', $this->workspace_id);
                }
            )->orderBy("invoices.invoice_number", 'asc');

    }
}
