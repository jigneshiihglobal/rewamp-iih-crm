@extends('layouts.app')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('app-assets/css/pages/app-invoice.css?v=' . config('versions.css')) }}">
@endsection

@section('custom-css')
    <style>
        tbody tr td:not(:first-child),
        thead tr th:not(:first-child) {
            text-align: end;
        }

        .invoice-preview .invoice-total-wrapper,
        .invoice-edit .invoice-total-wrapper,
        .invoice-add .invoice-total-wrapper {
            max-width: 14rem;
        }

        .invoice-preview .invoice-date-wrapper .invoice-date-title,
        .invoice-edit .invoice-date-wrapper .invoice-date-title,
        .invoice-add .invoice-date-wrapper .invoice-date-title {
            width: 10rem;
        }

        .invoice-preview .invoice-date-wrapper .invoice-date,
        .invoice-edit .invoice-date-wrapper .invoice-date,
        .invoice-add .invoice-date-wrapper .invoice-date {
            width: 7rem;
        }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-end">
        <div class="mb-1">

            <a class="btn btn-xs btn-info pull-right" href="{{URL::previous()}}" >
                <i data-feather="arrow-left" color="white"></i>
                &nbsp;
                <font color="white"> Back </font>
            </a>

        </div>
    </div>
    <section class="invoice-preview-wrapper">
        <div class="row invoice-preview">
            <div class=" col-12">
                <div class="card invoice-preview-card">
                    <div class="card-body pb-0">
                        <div class="d-flex justify-content-between flex-md-row flex-column mt-0">
                            <div class="row invoice-spacing order-last order-md-first mt-0 mb-25">
                                <div class="p-0">
                                    <h6 class="mb-2">Credit Note To:</h6>
                                    <h6 class="mb-25">{{ $credit_note->client->name ?? '' }}</h6>
                                    <p class="card-text mb-25">{{ $credit_note->client->address_line_1 ?? '' }}
                                        {{ $credit_note->client->address_line_2 ?? '' }}</p>
                                    <p class="card-text mb-25">{{ $credit_note->client->city ?? '' }}
                                        {{ $credit_note->client->zip_code ?? '' }}</p>
                                    <p class="card-text mb-25">
                                        {{ $credit_note->client && $credit_note->client->country ? $credit_note->client->country->name : '' }}
                                    </p>
                                    <p class="card-text mb-0">{{ $credit_note->client->email ?? '' }}</p>
                                </div>
                            </div>
                            <div class="mt-md-0 mt-2 d-flex flex-column invoice-spacing align-items-md-end">
                                <p class="h4 text-primary" target="_blank">
                                    Credit Note#
                                    <span class="invoice-number">{{ $credit_note->invoice_number }}</span>
                                </p>
                                <div class="invoice-date-wrapper mt-1">
                                    <p class="invoice-date-title">Credit Note Date:</p>
                                    <p class="invoice-date">
                                        {{ $credit_note->invoice_date ? $credit_note->invoice_date->format('d-m-Y') : '' }}
                                    </p>
                                </div>
                                <div class="invoice-date-wrapper">
                                    <p class="invoice-date-title">Invoice:</p>
                                    <p class="invoice-date">
                                        {{ $credit_note->parent_invoice ? $credit_note->parent_invoice->invoice_number : '' }}
                                    </p>
                                </div>
                                <div class="payment-buttons  mt-auto">
                                    <a href="{{ route('credit_notes.preview', $credit_note->encrypted_id) }}?v={{config('versions.pdf')}}"
                                        class="btn btn-sm btn-outline-secondary waves-effect" target="__blank">
                                        <span>Preview</span>
                                        <i data-feather="eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width: 40%" class="px-2 py-1">Credit Note item</th>
                                    <th style="width: 15%" class="px-2 py-1">Price</th>
                                    <th class="px-2 py-1">VAT Type</th>
                                    <th style="width: 15%" class="px-2 py-1">VAT Amount</th>
                                    <th style="width: 15%" class="px-2 py-1">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($credit_note->invoice_items as $invoice_item)
                                    <tr>
                                        <td class="px-2 py-1" style="width: 45%">
                                            <p class="card-text">{!! nl2br(e($invoice_item->description ?? '')) !!}</p>
                                        </td>
                                        <td class="px-2 py-1" style="width: 15%">
                                            <span
                                                class="fw-bold">{{ $credit_note->currency->symbol ?? '' }}{{ number_format((float) ($invoice_item->price ?? ''), 2, '.', ',') }}</span>
                                        </td>
                                        <td class="px-2 py-1">
                                            <span
                                                class="fw-bold">{{ $invoice_item->tax_type == 'vat_20' ? 'VAT 20%' : 'No VAT' }}</span>
                                        </td>
                                        <td class="px-2 py-1" style="width: 15%">
                                            <span
                                                class="fw-bold">{{ $credit_note->currency->symbol ?? '' }}{{ number_format((float) ($invoice_item->tax_amount ?? ''), 2, '.', ',') }}</span>
                                        </td>
                                        <td class="px-2 py-1" style="width: 15%">
                                            <span
                                                class="fw-bold">{{ $credit_note->currency->symbol ?? '' }}{{ number_format((float) ($invoice_item->total_price ?? ''), 2, '.', ',') }}</span>
                                        </td>
                                    </tr>

                                @empty
                                    <tr>
                                        <td class="py-1" colspan="5">No invoice items.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="card-body invoice-padding pb-0">
                        <div class="row invoice-sales-total-wrapper">
                            <div class="col-md-6 p-0 mt-xl-0 mt-2">
                                @if ($credit_note->sales_person)
                                    <p>
                                        <span class="h6">Sales Person:</span>
                                        <span>{{ $credit_note->sales_person->full_name ?? '' }}</span>
                                    </p>
                                @endif
                            </div>
                            <div class="col-md-6 d-flex justify-content-end order-md-2 order-1 p-0">
                                <div class="invoice-total-wrapper">
                                    <div class="invoice-total-item">
                                        <p class="invoice-total-title">Price Total:</p>
                                        <p class="invoice-total-amount">
                                            {{ $credit_note->currency->symbol ?? '' }}{{ number_format((float) ($credit_note->sub_total ?? 0), 2, '.', ',') }}
                                        </p>
                                    </div>
                                    <div class="invoice-total-item">
                                        <p class="invoice-total-title">VAT:</p>
                                        <p class="invoice-total-amount">
                                            {{ $credit_note->currency->symbol ?? '' }}{{ number_format((float) ($credit_note->vat_total ?? 0), 2, '.', ',') }}
                                        </p>
                                    </div>
                                    {{--<div class="invoice-total-item">
                                        <p class="invoice-total-title">Discount:</p>
                                        <p class="invoice-total-amount">
                                            {{ $credit_note->currency->symbol ?? '' }}{{ number_format((float) ($credit_note->discount ?? 0), 2, '.', ',') }}
                                        </p>
                                    </div>--}}
                                    <hr class="my-50" />
                                    <div class="invoice-total-item">
                                        <p class="invoice-total-title">Grand Total:</p>
                                        <p class="invoice-total-amount">
                                            {{ $credit_note->currency->symbol ?? '' }}{{ number_format((float) ($credit_note->grand_total ?? 0), 2, '.', ',') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="invoice-spacing" />

                    <div class="card-body pt-0">
                        <div class="row">
                            <div class="col-12">
                                <span class="fw-bold">Note:</span>
                                <span class="break-white-space">{!! $credit_note->note ?? '' !!}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('invoices.modals.invoice-preview-modal')

@endsection
