<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Credit Note #{{ $invoice->invoice_number }}</title>
    <link rel="stylesheet" href="{{ public_path('shalin-designs/pdf/one-off.css') }}">
</head>

<body>
    <div class="content">
        <header>
            <table class="w-100">
                <tr>
                    <td class="logo">
                        <img src="{{ public_path('shalin-designs/img/full-logo.png') }}" alt="Shalin designs Logo"
                            class="logo__image">
                    </td>
                    <td class="title">
                        <h2 class="text-end">
                            Credit Note</h2>
                    </td>
                </tr>
            </table>
        </header>
        <br>
        <table class="w-100">
            <tr>
                <td style="width: 55%;">
                    @if ($invoice->company_detail)
                        @if ($invoice->company_detail->name)
                            <h4>{{ $invoice->company_detail->name }}</h4>
                        @endif
                        @if ($invoice->company_detail->address)
                            <p>{!! $invoice->company_detail->address !!}</p>
                        @endif
                        @if ($invoice->company_detail->vat_number)
                            <p>VAT: {{ $invoice->company_detail->vat_number }}</p>
                        @endif
                    @else
                        <h4>Shalin Designs Limited</h4>
                        <p>Regus, Cardinal Point,</p>
                        <p>Park Road, Rickmansworth</p>
                        <p>WD3 1RE</p>
                    @endif
                </td>
                <td style="width: 45%;">
                    <table class="table-bordered w-100">
                        <tr>
                            <td class="bg-orange">
                                Credit Note number
                            </td>
                            <td class="text-end">
                                {{ $invoice->invoice_number }}
                            </td>
                        </tr>
                        <tr>
                            <td class="bg-orange">
                                Credit Note date
                            </td>
                            <td class="text-end">
                                {{ $invoice->invoice_date->format('d/m/Y') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="bg-orange">
                                Reference
                            </td>
                            <td class="text-end">
                                {{ $invoice->parent_invoice->invoice_number ?? '' }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <br>
        <table>
            <tr>
                <td>
                    <h2 style="font-weight: normal;">Credit Note to customer</h2>
                    <br>
                    <h4>{{ $invoice->client->name ?? '' }}</h4>
                    <p>{{ $invoice->client->address_line_1 ?? '' }}</p>
                    <p>{{ $invoice->client->address_line_2 ?? '' }}</p>
                    <p>{{ $invoice->client->city ?? '' }} {{ $invoice->client->zip_code ?? '' }}</p>
                    <p>{{ $invoice->client->country->name ?? '' }}</p>
                    <p>{{ $invoice->client->phone ?? '' }}</p>
                </td>
            </tr>
        </table>
        <br>
        <table class="table-bordered w-100">
            <thead>
                <tr>
                    <th class="bg-orange">Description</th>
                    <th class="bg-orange text-end">Price</th>
                    <th class="bg-orange text-end">VAT</th>
                    <th class="bg-orange text-end">VAT amount</th>
                    <th class="bg-orange text-end">Total amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->invoice_items as $invoice_item)
                    <tr>
                        <td style="width: 45%">
                            {!! nl2br(e($invoice_item->description ?? '')) !!}
                        </td>
                        <td class="text-end" style="width: 17%">
                            <span
                                class="currency_symbol">{!! $invoice->currency->symbol ?? '' !!}</span>{{ number_format((float) ($invoice_item->price ?? '0.00'), 2, '.', ',') }}
                        </td>
                        <td class="text-end" style="width: 7%;">
                            {{ $invoice_item->tax_type == 'vat_20' ? 20 : 0 }}<span class="currency_symbol">%</span>
                        </td>
                        <td class="text-end" style="width: 15%">
                            <span
                                class="currency_symbol">{!! $invoice->currency->symbol ?? '' !!}</span>{{ number_format((float) ($invoice_item->tax_amount ?? '0.00'), 2, '.', ',') }}
                        </td>
                        <td class="text-end" style="width: 16%">
                            <span
                                class="currency_symbol">{!! $invoice->currency->symbol ?? '' !!}</span>{{ number_format((float) ($invoice_item->total_price ?? '0.00'), 2, '.', ',') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <br>
        <table class="w-100">
            <tr>
                <td style="width: 55%"></td>
                <td>
                    <table class="table-bordered w-100">
                        <tr>
                            <td style="width: 50%">Sub total</td>
                            <td class="text-end" style="width: 50%">
                                <span
                                    class="currency_symbol">{!! $invoice->currency->symbol ?? '' !!}</span>{{ number_format((float) ($invoice->sub_total ?? '0.00'), 2, '.', ',') }}
                            </td>
                        </tr>
                        <tr>
                            <td>VAT total</td>
                            <td class="text-end">
                                <span
                                    class="currency_symbol">{!! $invoice->currency->symbol ?? '' !!}</span>{{ number_format((float) ($invoice->vat_total ?? '0.00'), 2, '.', ',') }}
                            </td>
                        </tr>
                        {{--@if ((float) $invoice->discount > 0)
                            <tr>
                                <td>Discount</td>
                                <td class="text-end">
                                    <span
                                        class="currency_symbol">{!! $invoice->currency->symbol ?? '' !!}</span>{{ number_format((float) ($invoice->discount ?? '0.00'), 2, '.', ',') }}
                                </td>
                            </tr>
                        @endif--}}
                        <tr>
                            <td>Grand total</td>
                            <td class="text-end">
                                <span
                                    class="currency_symbol">{!! $invoice->currency->symbol ?? '' !!}</span>{{ number_format((float) ($invoice->grand_total ?? '0.00'), 2, '.', ',') }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <br>
        <table class="w-100">
            <tr>
                <td>
                    <h4>Payment Details</h4>
                </td>
                @php
                    $invoice_total = (float) ($invoice->parent_invoice->grand_total ?? 0);
                    $payments_sum = (float) ($invoice->parent_invoice->payments->sum('amount') ?? 0);
                    $credit_note_total = (float) ($invoice->grand_total ?? 0);

                    $due_amount = max($invoice_total - $payments_sum - $credit_note_total, 0);
                @endphp
                <td class="text-end text-primary">
                    <p>Due Amount
                        <span
                            class="currency_symbol">{!! $invoice->currency->symbol ?? '' !!}</span>{{ number_format((float) $due_amount, 2, '.', ',') }}
                    </p>
                </td>
            </tr>
        </table>
        @if ($invoice->parent_invoice->payments->count())
            <table class="table-bordered w-75">
                <thead>
                    <tr>
                        <th>Amount</th>
                        <th>Pay. Source</th>
                        <th>Reference</th>
                        <th>Paid At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->parent_invoice->payments as $payment)
                        <tr>
                            <td class="text-end" style="width: 22%">
                                <span
                                    class="currency_symbol">{!! $invoice->currency->symbol ?? '' !!}</span>{{ number_format((float) ($payment->amount ?? 0), 2, '.', ',') }}
                            </td>
                            <td style="width: 23%">
                                {{ $payment->payment_source->title ?? '' }}
                            </td>
                            <td style="width: 38%">
                                {{ $payment->reference ?? '' }}
                            </td>
                            <td style="width: 17%">
                                {{ $payment->paid_at->format(App\Helpers\DateHelper::INVOICE_PAID_AT) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <table>
                <tr>
                    <td>
                        <p>No payments received yet.</p>
                    </td>
                </tr>
            </table>
        @endif
        <br>
        {{-- @if ($invoice->currency->code == 'GBP')
            <h4>Bank Detail</h4>
            <table border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td class="pe-1">Account holder:</td>
                    <td>Shalin Designs Ltd</td>
                </tr>
                <tr>
                    <td class="pe-1">Account number:</td>
                    <td>21781867</td>
                </tr>
                <tr>
                    <td class="pe-1">Sort code:</td>
                    <td>608371</td>
                </tr>
            </table>
        @endif --}}
        @switch($invoice->currency->code)
            @case('GBP')
                <h4>Bank Detail</h4>
                <table border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="pe-1">Account holder:</td>
                        <td>Shalin Designs Ltd</td>
                    </tr>
                    <tr>
                        <td class="pe-1">Sort code:</td>
                        <td>60-83-71</td>
                    </tr>
                    <tr>
                        <td class="pe-1">Account number:</td>
                        <td>21781867</td>
                    </tr>
                    <tr>
                        <td class="pe-1">Address:</td>
                        <td>Regus, Cardinal Point, Park Road, WD3 1RE</td>
                    </tr>
                </table>
            @break

            @case('USD')
                <h4>Bank Detail</h4>
                <table border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="pe-1">Account holder:</td>
                        <td>Shalin Designs Ltd</td>
                    </tr>
                    <tr>
                        <td class="pe-1">ACH and Wire routing number:</td>
                        <td>026073150</td>
                    </tr>
                    <tr>
                        <td class="pe-1">Account number:</td>
                        <td>8313682956</td>
                    </tr>
                    <tr>
                        <td class="pe-1">Account type:</td>
                        <td>Checking</td>
                    </tr>
                    <tr>
                        <td class="pe-1">Address:</td>
                        <td>30 W. 26th Street, Sixth Floor New York NY 10010 United States</td>
                    </tr>
                </table>
            @break

            @case('EUR')
                <h4>Bank Detail</h4>
                <table border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="pe-1">Account holder:</td>
                        <td>Shalin Designs Ltd.</td>
                    </tr>
                    <tr>
                        <td class="pe-1">BIC:</td>
                        <td>TRWIBEB1XXX</td>
                    </tr>
                    <tr>
                        <td class="pe-1">IBAN:</td>
                        <td>BE96 9677 4398 4205</td>
                    </tr>
                    <tr>
                        <td class="pe-1">Address:</td>
                        <td>Rue du Trône 100, 3rd floor Brussels 1050 Belgium</td>
                    </tr>
                </table>
            @break

            @case('CAD')
                <h4>Bank Detail</h4>
                <table border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="pe-1">Account holder:</td>
                        <td>Shalin Designs Ltd</td>
                    </tr>
                    <tr>
                        <td class="pe-1">Account number:</td>
                        <td>200110803989</td>
                    </tr>
                    <tr>
                        <td class="pe-1">Institution number:</td>
                        <td>621</td>
                    </tr>
                    <tr>
                        <td class="pe-1">Transit number:</td>
                        <td>16001</td>
                    </tr>
                    <tr>
                        <td class="pe-1">Swift/BIC:</td>
                        <td>TRWICAW1XXX</td>
                    </tr>
                    <tr>
                        <td class="pe-1">Address:</td>
                        <td>Wise Payments Canada Inc. <br>99 Bank Street, Suite 1420 <br>Ottawa <br>K1P 1H4 <br>Canada</td>
                    </tr>
                </table>
            @break

            @default
        @endswitch
    </div>
</body>

</html>
