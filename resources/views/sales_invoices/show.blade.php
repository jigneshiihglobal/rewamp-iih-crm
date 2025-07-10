@extends('layouts.app')

@section('page-css')
    <link rel="stylesheet"
        href="{{ asset('app-assets/css/plugins/forms/pickers/form-flat-pickr.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" href="{{ asset('app-assets/css/pages/app-invoice.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/css/plugins/forms/form-quill-editor.css?v=' . config('versions.css')) }}">
@endsection

@section('vendor-css')
    <link rel="stylesheet"
        href="{{ asset('app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/vendors/css/editors/quill/katex.min.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/vendors/css/editors/quill/monokai-sublime.min.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/vendors/css/editors/quill/quill.snow.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/vendors/css/editors/quill/quill.bubble.css?v=' . config('versions.css')) }}">
@endsection

@section('custom-css')
    <style>
        tbody tr td:not(:first-child),
        thead tr th:not(:first-child) {
            text-align: end;
        }

        #contentQuill .ql-editor {
            min-height: 200px;
            resize: vertical;
            overflow: auto;
        }

        .invoice-preview .invoice-total-wrapper,
        .invoice-edit .invoice-total-wrapper,
        .invoice-add .invoice-total-wrapper {
            max-width: 18rem;
        }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-end">
        <div class="mb-1">

                <a class="btn btn-xs btn-info pull-right" href="{{$redirect_route}}" >
                    <i data-feather="arrow-left" color="white"></i>
                    &nbsp;
                    <font color="white"> Back </font>
                </a>

        </div>
    </div>
    <section class="invoice-preview-wrapper">
        <div class="row invoice-preview">
            <!-- Invoice -->
            <div class=" col-12">
                <input type="hidden" class="invoice_cancelled_date" id="invoice_cancelled_date" value="{{$invoice->deleted_at}}">
                <input type="hidden" class="encrypted_id" id="encrypted_id" value="{{$invoice->encrypted_id}}">
                {{-- <div class="col-xl-9 col-md-8 col-12"> --}}
                <div class="card invoice-preview-card">
                    <div class="card-body pb-0">
                        <!-- Header starts -->
                        <div class="d-flex justify-content-between flex-md-row flex-column mt-0">
                            <div class="row invoice-spacing order-last order-md-first">
                                <div class="p-0">
                                    <h6 class="mb-2">Invoice To:</h6>
                                    <h6 class="mb-25">{{ $invoice->client->name ?? '' }}</h6>
                                    <p class="card-text mb-25">{{ $invoice->client->address_line_1 ?? '' }}
                                        {{ $invoice->client->address_line_2 ?? '' }}</p>
                                    <p class="card-text mb-25">{{ $invoice->client->city ?? '' }}
                                        {{ $invoice->client->zip_code ?? '' }}</p>
                                    <p class="card-text mb-25">
                                        {{ $invoice->client && $invoice->client->country ? $invoice->client->country->name : '' }}
                                    </p>
                                    <p class="card-text mb-0">{{ $invoice->client->email ?? '' }}</p>
                                </div>
                            </div>
                            <div class="mt-md-0 mt-2 d-flex flex-column invoice-spacing align-items-md-end">
                                <p class="h4 text-primary">
                                    Invoice
                                    <span class="invoice-number">#{{ $invoice->sales_invoice_number }}</span>
                                </p>
                                <div class="invoice-date-wrapper mt-1">
                                    <p class="invoice-date-title">Invoice Date:</p>
                                    <p class="invoice-date">
                                        {{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d-m-Y') : '' }}</p>
                                </div>
                                <div class="invoice-date-wrapper">
                                    <p class="invoice-date-title">Due Date:</p>
                                    <p class="invoice-date">
                                        {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d-m-Y') : '' }}</p>
                                </div>
                                @php
                                    $isDisabled = in_array($invoice->status, [2, 3, 4]);
                                @endphp

                                <div class="payment-buttons mt-auto">
                                    <a href="{{ $isDisabled ? '#' : route('sales_invoices.edit', $invoice->encrypted_id) }}"
                                        class="btn btn-sm btn-outline-info waves-effect {{ $isDisabled ? 'restricted-btn' : '' }}">
                                        <span>Edit</span>
                                        <i data-feather="edit"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-warning waves-effect {{ $isDisabled ? 'restricted-btn' : '' }}" id="{{ $isDisabled ? '' : 'mailsend' }}">
                                        <span>Send Mail</span>
                                        <i data-feather="send"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- Header ends -->
                    </div>
                    <!-- Invoice Description starts -->
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width: 40%" class="px-2 py-1">Invoice item</th>
                                    <th style="width: 15%" class="px-2 py-1">Price</th>
                                    <th class="px-2 py-1">VAT Type</th>
                                    <th style="width: 15%" class="px-2 py-1">VAT Amount</th>
                                    <th style="width: 15%" class="px-2 py-1">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($invoice->user_invoice_items as $invoice_item)
                                    <tr>
                                        <td class="px-2 py-1" style="width: 45%">
                                            <p class="card-text">{!! nl2br(e($invoice_item->description ?? '')) !!}</p>
                                        </td>
                                        <td class="px-2 py-1" style="width: 15%">
                                            <span
                                                class="fw-bold">{{ $invoice->currency->symbol ?? '' }}{{ number_format((float) ($invoice_item->price ?? ''), 2, '.', ',') }}</span>
                                        </td>
                                        <td class="px-2 py-1">
                                            <span
                                                class="fw-bold">{{ $invoice_item->tax_type == 'vat_20' ? 'VAT 20%' : 'No VAT' }}</span>
                                        </td>
                                        <td class="px-2 py-1" style="width: 15%">
                                            <span
                                                class="fw-bold">{{ $invoice->currency->symbol ?? '' }}{{ number_format((float) ($invoice_item->tax_amount ?? ''), 2, '.', ',') }}</span>
                                        </td>
                                        <td class="px-2 py-1" style="width: 15%">
                                            <span
                                                class="fw-bold">{{ $invoice->currency->symbol ?? '' }}{{ number_format((float) ($invoice_item->total_price ?? ''), 2, '.', ',') }}</span>
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
                                @if ($invoice->sales_person)
                                    <p>
                                        <span class="h6">Sales Person:</span>
                                        <span>{{ $invoice->sales_person->full_name ?? '' }}</span>
                                    </p>
                                @endif
                            </div>
                            <div class="col-md-6 d-flex justify-content-end order-md-2 order-1 p-0">
                                <div class="invoice-total-wrapper">
                                    <div class="invoice-total-item">
                                        <p class="invoice-total-title">Price Total:</p>
                                        <p class="invoice-total-amount">
                                            {{ $invoice->currency->symbol ?? '' }}{{ number_format((float) ($invoice->sub_total ?? 0), 2, '.', ',') }}
                                        </p>
                                    </div>
                                    <div class="invoice-total-item">
                                        <p class="invoice-total-title">VAT:</p>
                                        <p class="invoice-total-amount">
                                            {{ $invoice->currency->symbol ?? '' }}{{ number_format((float) ($invoice->vat_total ?? 0), 2, '.', ',') }}
                                        </p>
                                    </div>
                                    {{--<div class="invoice-total-item">
                                        <p class="invoice-total-title">Discount:</p>
                                        <p class="invoice-total-amount">
                                            {{ $invoice->currency->symbol ?? '' }}{{ number_format((float) ($invoice->discount ?? 0), 2, '.', ',') }}
                                        </p>
                                    </div>--}}
                                    <hr class="my-50" />
                                    <div class="invoice-total-item">
                                        <p class="invoice-total-title">Grand Total:</p>
                                        <p class="invoice-total-amount">
                                            {{ $invoice->currency->symbol ?? '' }}{{ number_format((float) ($invoice->grand_total ?? 0), 2, '.', ',') }}
                                        </p>
                                    </div>
                                    <div class="invoice-total-item text-primary">
                                        <p class="invoice-total-title">
                                            Due Amount:
                                        </p>
                                        <p class="invoice-total-amount">
                                            {{ $invoice->currency->symbol ?? '' }}{{ number_format((float) $invoice->grand_total - (float) $invoice->payments_sum_amount - (float) ($invoice->credit_note->grand_total ?? 0), 2, '.', ',') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Invoice Description ends -->

                    <hr class="invoice-spacing" />
                </div>
            </div>
            <!-- /Invoice -->
        </div>
    </section>
    
    <div class="modal fade text-start" id="sendMailModal" tabindex="-1" aria-labelledby="sendMailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form id="sendMailForm" method="POST" action="{{ route('sales_invoices.send-mail-to-admin', $invoice->encrypted_id) }}">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title" id="sendMailModalLabel">Send Mail</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="to" class="form-label">To <span class="text-danger">*</span></label>
                            <input type="text" name="to[]" id="to" class="form-control" value="{{ config('mail.sales_invoice_sent_mail_address') }}" placeholder="{{ config('mail.sales_invoice_sent_mail_address') }}" readonly>
                        </div>
                        <div class="form-group">
                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" id="subject" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="contentQuill" class="form-label">Content <span class="text-danger">*</span></label>
                            <div id="contentQuill"></div>
                            <input type="hidden" name="content" id="content">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="sendMailSubmitBtn">Send Mail</button>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@include('sales_invoices.modals.restriction-modal')

@endsection

@section('page-js')
    <script src="{{ asset('app-assets/js/scripts/pages/app-invoice.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/js/scripts/forms/form-quill-editor.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/js/custom/invoice_custom.js?v=' . config('versions.js')) }}"></script>
@endsection

@section('page-vendor-js')
    <script src="{{ asset('app-assets/vendors/js/forms/repeater/jquery.repeater.min.js?v=' . config('versions.js')) }}">
    </script>
    <script src="{{ asset('app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js?v=' . config('versions.js')) }}">
    </script>
    <script src="{{ asset('app-assets/vendors/js/editors/quill/katex.min.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/vendors/js/editors/quill/highlight.min.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/vendors/js/editors/quill/quill.min.js?v=' . config('versions.js')) }}"></script>
@endsection

@section('custom-js')
    <script>
        var invoice = @json($invoice)

        var contentQuill = new Quill('#contentQuill', {
            theme: 'snow',
            format: {
                fontFamily: 'Public Sans'
            }
        });

     
        $(document).ready(function() {

            function copyContentToInput() {
                $('#content').val(contentQuill.root.innerHTML);
            }

            copyContentToInput();

            contentQuill.on('text-change', function(delta, oldDelta, source) {
                copyContentToInput();
            });


            var sendMailFormValidator = $('#sendMailForm').validate({
                ignore: [],
                rules: {
                    subject: {
                        required: true,
                    },
                    content: {
                        required: true
                    }
                },
                messages: {
                    subject: {
                        required: "Please enter email subject line",
                    },
                    content: {
                        required: "Please enter email content"
                    }
                },
                submitHandler: function(form, event) {
                    event.preventDefault();
                    $('#sendMailSubmitBtn').prop('disabled', true);
                    $('#sendMailModal > .modal-dialog').block({
                        message: '<div class="spinner-border text-warning" role="status"></div>',
                        css: {
                            backgroundColor: 'transparent',
                            border: '0'
                        },
                        overlayCSS: {
                            backgroundColor: '#fff',
                            opacity: 0.8
                        }
                    });
                    $.ajax({
                        url: form.action,
                        method: 'POST',
                        data: $(form).serialize(),
                        success: function(response) {
                            if (response.errors) {
                                $(form).validate().showErrors(response.errors);
                            } else {
                                $('#sendMailModal').modal('hide');
                                toastr.success(null, "Mail sent successfully!");
                            }
                            $('#sendMailModal > .modal-dialog').unblock();
                            $('#sendMailSubmitBtn').prop('disabled', false);
                        },
                        error: function(xhr, status, error) {
                            $('#sendMailModal > .modal-dialog').unblock();
                            $('#sendMailSubmitBtn').prop('disabled', false);
                            if (xhr.status == 422) {
                                $(form).validate().showErrors(JSON.parse(xhr.responseText)
                                    .errors);
                            } else {
                                Swal.fire({
                                    title: 'An error occurred',
                                    text: error,
                                    icon: 'error',
                                });
                            }
                        }
                    });
                },
                errorPlacement: function(error, element) {
                    if (element.hasClass('select2') || element.attr('type') === 'hidden') {
                        error.appendTo(element.parent())
                    } else {
                        error.insertAfter(element);
                    }
                }
            });

            function fillSendMailForm(subject = "", content = "") {

                $('#subject').val(subject);
                contentQuill.root.innerHTML = content;
                copyContentToInput();
            }

             $(document).on('click', '.restricted-btn', function (e) {
                e.preventDefault();
                const message = $(this).data('restrict-msg') || 'This action is currently restricted.';
                const $messageBox = $('#restrictionModalMessage');
                $messageBox
                    .text(message)
                    .removeClass()
                    .addClass('text-danger fw-semibold fs-4');
                const modal = new bootstrap.Modal(document.getElementById('restrictionModal'));
                modal.show();
            });

            var encrypted_id = $('#encrypted_id').val();
            $(document).on('show.bs.modal', '#sendMailModal', function(e) {
                $('#sendMailSubmitBtn').prop('disabled', true);
                $('#sendMailModal > .modal-dialog').block({
                    message: '<div class="spinner-border text-warning" role="status"></div>',
                    css: {
                        backgroundColor: 'transparent',
                        border: '0'
                    },
                    overlayCSS: {
                        backgroundColor: '#fff',
                        opacity: 0.8
                    }
                });
                $.ajax({
                    url: route('sales_invoices.user-invoice-get-mail-content', encrypted_id),
                    method: 'GET',
                    success: function(response) {
                        fillSendMailForm(response?.subject,
                            response
                                ?.content);
                        $('#sendMailModal > .modal-dialog').unblock();
                        $('#sendMailSubmitBtn').prop('disabled', false);
                    },
                    error: function(xhr, status, error) {
                        $('#sendMailModal > .modal-dialog').unblock();
                        $('#sendMailSubmitBtn').prop('disabled', false);
                        Swal.fire({
                            title: 'An error occurred',
                            text: error,
                            icon: 'error',
                        });
                    }
                });
            });
        });
    </script>
@endsection