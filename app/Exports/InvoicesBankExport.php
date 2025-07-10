<?php

namespace App\Exports;

use App\Enums\InvoiceType;
use App\Helpers\CurrencyHelper;
use App\Helpers\DateHelper;
use App\Models\Invoice;
use App\Models\Payment;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoicesBankExport implements FromCollection, WithHeadings, WithMapping, WithCustomCsvSettings, WithColumnFormatting, WithStyles
{
    use Exportable;

    private array $filter_statuses;
    private DateTime $filter_created_at_start;
    private DateTime $filter_created_at_end;
    private int $filter_payment_source_id;
    private int $workspace_id;
    private array $currencyRates;
    private int $filter_client_id;
    private int $filter_company_id;

    public function payment_statuses(array $statuses): InvoicesBankExport
    {
        $this->filter_statuses = $statuses;
        return $this;
    }

    public function invoice_date_greater_than(DateTime $start_date): InvoicesBankExport
    {
        $this->filter_created_at_start = $start_date;
        return $this;
    }

    public function invoice_date_less_than(DateTime $end_date): InvoicesBankExport
    {
        $this->filter_created_at_end = $end_date;
        return $this;
    }

    public function payment_source_id(int $payment_source_id): InvoicesBankExport
    {
        $this->filter_payment_source_id = $payment_source_id;
        return $this;
    }

    public function client_id(int $client_id): InvoicesBankExport
    {
        $this->filter_client_id = $client_id;
        return $this;
    }

    public function company_detail_id(int $company_detail_id): InvoicesBankExport
    {
        $this->filter_company_id = $company_detail_id;
        return $this;
    }

    public function workspace(int $workspace_id): InvoicesBankExport
    {
        $this->workspace_id = $workspace_id;
        return $this;
    }

    public function getCsvSettings(): array
    {
        return ['use_bom' => true];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'J' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'K' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'M' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function headings(): array
    {
        return [
            'Bank Date',
            'Currency',
            'Bank Amount',
            'Bank Amount GBP',
            'Bank Name',
            'CRM Invoice No.',
            'Invoice Date',
            'Currency',
            'Project Charges FX',
            'UK VAT',
            'Invoice Amount GBP',
            'Client Name',
            'Outstading Payment',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        foreach ($sheet->getRowIterator(2) as $row) {
            $range = 'A' . $row->getRowIndex() . ':' . $sheet->getHighestColumn() . $row->getRowIndex();
            $cellValue = $sheet->getCell('B' . $row->getRowIndex())->getValue();

            if (strtolower($cellValue) === 'yes') {
                $sheet->getStyle($range)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FF0000'],
                    ],
                ]);
            }
        }
    }

    public function map($invoice): array
    {
        $currency = $invoice->currency->code ?? '';
        $currency_code = $invoice->currency->code ?? 'GBP';
        $currencyRates = CurrencyHelper::convert($currency_code, config('custom.statistics_currency'), $invoice->invoice_date);
        $gbpRate = $currencyRates->base_currency_rate ?? 1;
        $payment_sum_amount = (float) $invoice->payments_sum_amount;
        $sub_total = (float) $invoice->sub_total;
        $vat_total = (float) $invoice->vat_total;
        $grand_total = (float) $invoice->grand_total;
        $grand_total_converted = $grand_total * ((float) $gbpRate);
        $credit_note_amount = (float) ($invoice->credit_note->grand_total ?? '');
        $due_amount = (float) max(0, $grand_total - $credit_note_amount - $payment_sum_amount);

        $sub_total_formatted = number_format($sub_total, 2, '.', ',');
        $vat_total_formatted = $vat_total ? number_format($vat_total, 2, '.', ',') : '';
        $grand_total_converted_formatted = number_format($grand_total_converted, 2, '.', ',');
        $invoice_date = $invoice->invoice_date->format(DateHelper::INVOICE_EXPORT_INVOICE_DATE);
        $due_amount_formatted = number_format($due_amount, 2, '.', ',');

        // Invoice Number Set 
        if(isset($invoice->parent_invoice) && !empty($invoice->parent_invoice)){
            $invoice_number = $invoice->invoice_number.'/'.$invoice->parent_invoice->invoice_number ?? '';
        }elseif(isset($invoice->credit_note) && !empty($invoice->credit_note)){
            $invoice_number = $invoice->invoice_number.'/'.$invoice->credit_note->invoice_number ?? '';
        }else{
            $invoice_number = $invoice->invoice_number ?? '';
        }

        if ($invoice->type == InvoiceType::CREDIT_NOTE) {
            return [
                '', '', '', '', '',
                $invoice_number,
                $invoice_date,
                $currency,
                $sub_total_formatted,
                $vat_total_formatted,
                $grand_total_converted_formatted,
                $invoice->client->name ?? '',
                '',
            ];
        }

        if ($invoice->payments && $invoice->payments->count() > 0) {
            return $invoice->payments->map(function ($payment) use (
                $currency, $invoice, $invoice_date,
                $sub_total_formatted, $vat_total_formatted,$invoice_number,
                $grand_total_converted_formatted, $due_amount_formatted
            ) {
                $amount = (float) $payment->amount;
                $bankCurrencyRates = CurrencyHelper::convert($currency, config('custom.statistics_currency'), $payment->paid_at);
                $gbpRate = $bankCurrencyRates->base_currency_rate ?? 1;
                $amount_gbp = $amount * $gbpRate;

                return [
                    $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('d/m/Y') : '',
                    $currency,
                    $amount,
                    $amount_gbp,
                    $payment->payment_source->title ?? '',
                    $invoice_number,
                    $invoice_date,
                    $currency,
                    $sub_total_formatted,
                    $vat_total_formatted,
                    $grand_total_converted_formatted,
                    $invoice->client->name ?? '',
                    $due_amount_formatted,
                ];
            })->toArray();
        }

        return [[
            '', '', '', '', '',
            $invoice_number,
            $invoice_date,
            $currency,
            $sub_total_formatted,
            $vat_total_formatted,
            $grand_total_converted_formatted,
            $invoice->client->name ?? '',
            $due_amount_formatted,
        ]];
    }

    public function collection()
    {
        $timezone_offset = DateHelper::getGmtOffsetFromTimezone(Auth::user()->timezone);

        $payment_invoice_ids = Payment::select('invoices.id')
            ->leftJoin('invoices', 'invoices.id', 'payments.invoice_id')
            ->leftJoin('payment_sources', 'payment_sources.id', 'payments.payment_source_id')
            ->leftJoin('company_details', 'company_details.id', 'invoices.company_detail_id')
            ->leftJoin('clients', 'clients.id', 'invoices.client_id')
            ->leftJoin('currencies', 'currencies.id', 'invoices.currency_id')
            ->when(!empty($this->filter_payment_source_id), fn($q) => $q->where('payments.payment_source_id', $this->filter_payment_source_id))
            ->when(isset($this->filter_created_at_start, $this->filter_created_at_end), function ($q) {
                $start = $this->filter_created_at_start->format('Y-m-d');
                $end = $this->filter_created_at_end->format('Y-m-d');
                $q->whereRaw("DATE(invoices.invoice_date) NOT BETWEEN ? AND ?", [$start, $end]);
                $q->whereRaw("DATE(payments.paid_at) BETWEEN ? AND ?", [$start, $end]);
            })
            ->when(isset($this->filter_client_id), fn($q) => $q->where('invoices.client_id', $this->filter_client_id))
            ->when(isset($this->filter_company_id), fn($q) => $q->where('invoices.company_detail_id', $this->filter_company_id))
            ->when(isset($this->workspace_id), fn($q) => $q->where('clients.workspace_id', $this->workspace_id))
            ->pluck('invoices.id')
            ->toArray();

        $select = [
            'invoices.id',
            'invoices.invoice_number',
            'invoices.type',
            'invoices.user_id',
            'invoices.client_id',
            'invoices.invoice_date',
            'invoices.due_date',
            'invoices.sub_total',
            'invoices.grand_total',
            'invoices.vat_total',
            'invoices.discount',
            'invoices.payment_status',
            'invoices.parent_invoice_id',
            'invoices.currency_id',
            'invoices.company_detail_id',
            'invoices.deleted_at',
        ];
        
        $with = [
            'client:id,name',
            'sales_person:id,first_name,last_name',
            'currency:id,code',
            'credit_note:id,invoice_number,parent_invoice_id,currency_id,grand_total,sub_total,vat_total',
            'credit_note.currency:id,code',
            'parent_invoice:id,invoice_number,currency_id,grand_total,sub_total,vat_total',
            'parent_invoice.currency:id,code',
            'company_detail:id,name',
            'payments:id,invoice_id,amount,paid_at,payment_source_id',
            'payments.payment_source:id,title',
        ];

        $filteredInvoices = Invoice::withTrashed()
            ->select($select)->with($with)
            ->withSum('payments', 'amount')
            ->when(!empty($payment_invoice_ids), fn($q) => $q->whereNotIn('invoices.id', $payment_invoice_ids))
            ->when(isset($this->filter_client_id), fn($q) => $q->where('client_id', $this->filter_client_id))
            ->when(isset($this->filter_company_id), fn($q) => $q->where('company_detail_id', $this->filter_company_id))
            ->when(isset($this->workspace_id), function ($q) {
                $q->whereHas('client', fn($q) => $q->where('clients.workspace_id', $this->workspace_id));
            })
            ->when(isset($this->filter_statuses), function ($q) {
                if (count($this->filter_statuses)) {
                    $q->where(function ($q) {
                        $q->where(function ($q) {
                            $status = $this->filter_statuses;
                            if (in_array("1", $status)) {
                                if (count($status) > 1) {
                                    $q->where(function ($q) use ($status) {
                                        $q->whereIn('invoices.payment_status', $status)
                                            ->orWhere('invoices.type', InvoiceType::CREDIT_NOTE);
                                    });
                                } else {
                                    $q->where('type', InvoiceType::CREDIT_NOTE);
                                }
                            } else {
                                $q->whereIn('invoices.payment_status', $status)
                                    ->where('type', InvoiceType::INVOICE);
                            }
                        })->orWhere(function ($q) {
                            $q->where('type', InvoiceType::CREDIT_NOTE)
                                ->whereHas('parent_invoice', fn($q) => $q->whereIn('payment_status', $this->filter_statuses));
                        });
                    });
                }
            })
            ->when(isset($this->filter_created_at_start), function ($q) {
                $start = $this->filter_created_at_start->setTimezone(new \DateTimeZone(config('app.timezone', 'UTC')));
                $q->where(function ($q) use ($start) {
                    $q->where(function ($q) use ($start) {
                        $q->where('type', InvoiceType::INVOICE)
                            ->where('invoices.invoice_date', '>=', $start);
                    })->orWhere(function ($q) use ($start) {
                        $q->where('type', InvoiceType::CREDIT_NOTE)
                            ->whereHas('parent_invoice', fn($q) => $q->where('invoice_date', '>=', $start));
                    });
                });
            })
            ->when(isset($this->filter_created_at_end), function ($q) {
                $end = $this->filter_created_at_end->setTimezone(new \DateTimeZone(config('app.timezone', 'UTC')));
                $q->where(function ($q) use ($end) {
                    $q->where(function ($q) use ($end) {
                        $q->where('type', InvoiceType::INVOICE)
                            ->where('invoices.invoice_date', '<=', $end);
                    })->orWhere(function ($q) use ($end) {
                        $q->where('type', InvoiceType::CREDIT_NOTE)
                            ->whereHas('parent_invoice', fn($q) => $q->where('invoice_date', '<=', $end));
                    });
                });
            })
            ->when(isset($this->filter_payment_source_id), function ($q) {
                $q->where(function ($q) {
                    $q->where(function ($q) {
                        $q->where('type', InvoiceType::INVOICE)
                            ->whereHas('payments', fn($q) => $q->where('payments.payment_source_id', $this->filter_payment_source_id));
                    })->orWhere(function ($q) {
                        $q->where('type', InvoiceType::CREDIT_NOTE)
                            ->whereHas('parent_invoice', fn($q) => $q->whereHas('payments', fn($q) => $q->where('payments.payment_source_id', $this->filter_payment_source_id)));
                    });
                });
            });

        $extraInvoices = Invoice::withTrashed()
            ->select($select)->with($with)
            ->withSum('payments', 'amount')
            ->whereIn('invoices.id', $payment_invoice_ids);

        $invoices = $filteredInvoices->get()->merge($extraInvoices->get());

        return $invoices->sortBy(function ($invoice) use ($timezone_offset) {
            return \Carbon\Carbon::parse($invoice->invoice_date)->setTimezone($timezone_offset)->toDateString() . $invoice->invoice_number;
        })->values();
    }
}