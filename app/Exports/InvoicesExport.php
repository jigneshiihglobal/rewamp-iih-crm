<?php

namespace App\Exports;

use App\Enums\InvoiceType;
use App\Helpers\CurrencyHelper;
use App\Helpers\DateHelper;
use App\Models\Invoice;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class InvoicesExport implements FromQuery, WithHeadings, WithMapping, WithCustomCsvSettings, WithColumnFormatting, WithStyles
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

    public function payment_statuses(
        array $statuses
    ): InvoicesExport {
        $this->filter_statuses = $statuses;
        return $this;
    }

    public function invoice_date_greater_than(
        DateTime $start_date
    ): InvoicesExport {
        $this->filter_created_at_start = $start_date;
        return $this;
    }

    public function invoice_date_less_than(
        DateTime $end_date
    ): InvoicesExport {
        $this->filter_created_at_end = $end_date;
        return $this;
    }

    public function payment_source_id(
        int $payment_source_id
    ): InvoicesExport {
        $this->filter_payment_source_id = $payment_source_id;
        return $this;
    }

    public function client_id(
        int $client_id
    ): InvoicesExport {
        $this->filter_client_id = $client_id;
        return $this;
    }

    public function company_detail_id(
        int $company_detail_id
    ): InvoicesExport {
        $this->filter_company_id = $company_detail_id;
        return $this;
    }

    public function workspace(
        int $workspace_id
    ): InvoicesExport {
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

    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'K' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'M' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'N' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'Q' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'S' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'U' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'AA' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function headings(): array
    {
        return [
            'Number',
            'Is Cancelled',
            'Type',
            'Customer Name',
            'Company Name',
            'Sales Person Name',
            'Sub Total Currency',
            'Sub Total',
            'VAT Total Currency',
            'VAT Total',
            'Discount Currency',
            'Discount',
            'Grand Total Currency',
            'Grand Total',
            'Grand Total (GBP)',
            'Credit Note Number',
            'Credit Note Currency',
            'Credit Note Amount',
            'Paid Amount Currency',
            'Paid Amount',
            'Due Amount Currency',
            'Due Amount',
            'Invoice Date',
            'Due Date',
            'Payment Status',
            'Parent Invoice Number',
            'Parent Invoice Currency',
            'Parent Invoice Amount',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Iterate through each row in the sheet starting from the second row (assuming the header is in the first row)
        foreach ($sheet->getRowIterator(2) as $row) {
            // Get the cell value in column 'B' (assuming the $deleted_at column is in the second column)
            $range = 'A' . $row->getRowIndex() . ':' . $sheet->getHighestColumn() . $row->getRowIndex();
            $cellValue = $sheet->getCell('B' . $row->getRowIndex())->getValue();

            // Check if $deleted_at is 'yes' and apply color
            if (strtolower($cellValue) === 'yes') {
                $sheet->getStyle($range)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FF0000'], // Replace with your desired color
                    ],
                ]);
            }
        }
    }

    public function map($invoice): array
    {
        $currency                = $invoice->currency->code ?? '';
        $currency_code           = $invoice->currency->code ?? 'GBP';
        $currencyRates           = CurrencyHelper::convert($currency_code,config('custom.statistics_currency'), $invoice->invoice_date);
        $gbpRate                 = $currencyRates->base_currency_rate ?? 1;
        $payment_sum_amount      = (float) $invoice->payments_sum_amount;
        $sub_total               = (float) $invoice->sub_total;
        $vat_total               = (float) $invoice->vat_total;
        $grand_total             = (float) $invoice->grand_total;
        $grand_total_converted   = $grand_total * ((float) $gbpRate);
        $credit_note_amount      = (float) ($invoice->credit_note->grand_total ?? '');
        $discount                = (float) ($invoice->discount ?? '');
        $discount_formatted      = $discount ? number_format($discount, 2, '.', ',') : '';
        $credit_note_currency    = $invoice->credit_note->currency->code ?? '';
        $parent_invoice_currency = $invoice->parent_invoice->currency->code ?? '';
        $due_amount              = $grand_total - $credit_note_amount - $payment_sum_amount;
        $due_amount              = (float) max(0, $due_amount);
        $sub_total_formatted     = number_format($sub_total, 2, '.', ',');
        $vat_total_formatted     = $vat_total ? number_format($vat_total, 2, '.', ',') : '';
        $grand_total_formatted   = number_format($grand_total, 2, '.', ',');
        $grand_total_converted_formatted   = number_format($grand_total_converted, 2, '.', ',');
        $company_name            = $invoice->company_detail->name ?? '';

        $type                   = ($invoice->type == InvoiceType::CREDIT_NOTE)
            ? 'Credit Note'
            : 'Invoice';

        $deleted_at = "";
        if($invoice->deleted_at != null){
            $deleted_at = 'Yes';
        }

        $sales_person_name      = ($invoice->sales_person->first_name ?? "") .
            " " .
            ($invoice->sales_person->last_name ?? '');

        $invoice_date           = $invoice
            ->invoice_date
            ->format(DateHelper::INVOICE_EXPORT_INVOICE_DATE);

        $due_date               = $invoice->due_date
            ? $invoice->due_date->format(DateHelper::INVOICE_EXPORT_DUE_DATE)
            : $invoice->due_date;

        if ($invoice->type == InvoiceType::CREDIT_NOTE) {
            $parent_invoice_amount   = (float) ($invoice->parent_invoice->grand_total ?? '');
            $parent_invoice_amount_formatted = number_format($parent_invoice_amount, 2, '.', ',');
            return [
                $invoice->invoice_number ?? '',
                $deleted_at,
                $type,
                $invoice->client->name ?? '',
                $company_name,
                $sales_person_name,
                $currency,
                $sub_total_formatted,
                $currency,
                $vat_total_formatted,
                $discount ? $currency : '',
                $discount_formatted,
                $currency,
                $grand_total_formatted,
                $grand_total_converted_formatted,
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                $invoice_date,
                $due_date,
                '',
                $invoice->parent_invoice->invoice_number ?? '',
                $parent_invoice_currency,
                $parent_invoice_amount_formatted,
            ];
        }

        $credit_note_amount_formatted = $credit_note_amount
            ? number_format($credit_note_amount, 2, '.', ',')
            : '';
        $payment_sum_amount_formatted = $payment_sum_amount ? number_format($payment_sum_amount, 2, '.', ',') : '';
        $due_amount_formatted = $due_amount ? number_format($due_amount, 2, '.', ',') : '';

        return [
            $invoice->invoice_number ?? '',
            $deleted_at,
            $type,
            $invoice->client->name ?? '',
            $company_name,
            $sales_person_name,
            $currency,
            $sub_total_formatted,
            $currency,
            $vat_total_formatted,
            $discount ? $currency : '',
            $discount_formatted,
            $currency,
            $grand_total_formatted,
            $grand_total_converted_formatted,
            $invoice->credit_note->invoice_number ?? '',
            $credit_note_amount ? $credit_note_currency : '',
            $credit_note_amount_formatted ?? '',
            $payment_sum_amount ? $currency : '',
            $payment_sum_amount_formatted,
            $due_amount ? $currency : '',
            $due_amount_formatted,
            $invoice_date,
            $due_date,
            Str::headline($invoice->payment_status),
            '',
            '',
            '',
        ];
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        $timezone_offset = DateHelper::getGmtOffsetFromTimezone(Auth::user()->timezone);

        return Invoice::withTrashed()->select(
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
        )
            ->with([
                'client:id,name',
                'sales_person:id,first_name,last_name',
                'currency:id,code',
                'credit_note:id,invoice_number,parent_invoice_id,currency_id,grand_total,sub_total,vat_total',
                'credit_note.currency:id,code',
                'parent_invoice:id,invoice_number,currency_id,grand_total,sub_total,vat_total',
                'parent_invoice.currency:id,code',
                'company_detail:id,name',
            ])
            ->withSum('payments', 'amount')
            ->when(
                isset($this->filter_client_id),
                function ($q) {
                    $q->where('client_id', $this->filter_client_id);
                }
            )
            ->when(
                isset($this->filter_company_id),
                function ($q) {
                    $q->where(
                        'company_detail_id',
                        $this->filter_company_id
                    );
                }
            )
            ->when(
                isset($this->workspace_id),
                function ($q) {
                    $q->whereHas(
                        'client',
                        function ($q) {
                            $q
                                ->where(
                                    'clients.workspace_id',
                                    $this->workspace_id
                                );
                        }
                    );
                }
            )
            ->when(
                isset($this->filter_statuses),
                function ($q) {
                    if (count($this->filter_statuses)) {
                        $q
                            ->where(function ($q) {
                                $q->where(
                                    function ($q) {
                                        $payment_status = $this->filter_statuses;
                                        if(in_array("1", $payment_status)){
                                            if(count($payment_status) > 1){
                                                $q->where(function ($q) use($payment_status){
                                                    $q->whereIn(
                                                        'invoices.payment_status',
                                                        $payment_status
                                                    )->orWhere(
                                                        'invoices.type',
                                                        InvoiceType::CREDIT_NOTE
                                                    );
                                                });
                                            }else{
                                                $q->where(
                                                    'type',
                                                    InvoiceType::CREDIT_NOTE
                                                );
                                            }
                                        }else{
                                            $q->whereIn(
                                                'invoices.payment_status',
                                                $payment_status
                                            )->where(
                                                'type',
                                                InvoiceType::INVOICE
                                            );
                                        }
                                    }
                                )
                                    ->orWhere(
                                        function ($q) {
                                            $q->where(
                                                'type',
                                                InvoiceType::CREDIT_NOTE
                                            )->whereHas(
                                                'parent_invoice',
                                                function ($q) {
                                                    $q
                                                        ->whereIn(
                                                            'payment_status',
                                                            $this->filter_statuses
                                                        );
                                                }
                                            );
                                        }
                                    );
                            });
                    }
                }
            )
            ->when(
                isset($this->filter_created_at_start),
                function ($q) {
                    $q->where(function ($q) {
                        $q->where(
                            function ($q) {
                                $q->where(
                                    'type',
                                    InvoiceType::INVOICE
                                )->where(
                                    "invoices.invoice_date",
                                    ">=",
                                    $this
                                        ->filter_created_at_start
                                        ->setTimezone(
                                            new DateTimeZone(
                                                config(
                                                    'app.timezone',
                                                    'UTC'
                                                )
                                            )
                                        )
                                );
                            }
                        )->orWhere(
                            function ($q) {
                                $q->where(
                                    'type',
                                    InvoiceType::CREDIT_NOTE
                                )
                                    ->whereHas(
                                        'parent_invoice',
                                        function ($q) {
                                            $q
                                                ->where(
                                                    "invoice_date",
                                                    ">=",
                                                    $this
                                                        ->filter_created_at_start
                                                        ->setTimezone(
                                                            new DateTimeZone(
                                                                config(
                                                                    'app.timezone',
                                                                    'UTC'
                                                                )
                                                            )
                                                        )
                                                );
                                        }
                                    );
                            }
                        );
                    });
                }
            )
            ->when(
                isset($this->filter_created_at_end),
                function ($q) {
                    $q
                        ->where(
                            function ($q) {
                                $q->where(
                                    function ($q) {
                                        $q->where(
                                            'type',
                                            InvoiceType::INVOICE
                                        )->where(
                                            "invoices.invoice_date",
                                            "<=",
                                            $this
                                                ->filter_created_at_end
                                                ->setTimezone(
                                                    new DateTimeZone(
                                                        config(
                                                            'app.timezone',
                                                            'UTC'
                                                        )
                                                    )
                                                )
                                        );
                                    }
                                )->orWhere(
                                    function ($q) {
                                        $q->where(
                                            'type',
                                            InvoiceType::CREDIT_NOTE
                                        )->whereHas('parent_invoice', function ($q) {
                                            $q->where(
                                                "invoice_date",
                                                "<=",
                                                $this
                                                    ->filter_created_at_end
                                                    ->setTimezone(
                                                        new DateTimeZone(
                                                            config(
                                                                'app.timezone',
                                                                'UTC'
                                                            )
                                                        )
                                                    )
                                            );
                                        });
                                    }
                                );
                            }
                        );
                }
            )
            ->when(
                isset($this->filter_payment_source_id),
                function ($q) {
                    $q->where(
                        function ($q) {
                            $q->where(
                                function ($q) {
                                    $q
                                        ->where(
                                            'type',
                                            InvoiceType::INVOICE
                                        )
                                        ->whereHas(
                                            'payments',
                                            function ($q) {
                                                $q->where(
                                                    'payments.payment_source_id',
                                                    $this->filter_payment_source_id
                                                );
                                            }
                                        );
                                }
                            )->orWhere(
                                function ($q) {
                                    $q->where(
                                        'type',
                                        InvoiceType::CREDIT_NOTE
                                    )
                                        ->whereHas(
                                            'parent_invoice',
                                            function ($q) {
                                                $q->whereHas(
                                                    'payments',
                                                    function ($q) {
                                                        $q->where(
                                                            'payments.payment_source_id',
                                                            $this->filter_payment_source_id
                                                        );
                                                    }
                                                );
                                            }
                                        );
                                }
                            );
                        }
                    );
                }
            )
            ->orderByRaw("DATE(CONVERT_TZ(invoices.invoice_date, '+00:00', '{$timezone_offset}')) asc")
            ->orderBy("invoices.id", 'asc');
    }
}
