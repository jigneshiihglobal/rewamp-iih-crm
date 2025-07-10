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
        <div class="mb-1" >
            <a class="btn btn-xs btn-info pull-right" href="{{$redirect_route}}" >
                <i data-feather="arrow-left" color="white"></i>
                    &nbsp;
                    <font color="white"> Back </font>
            </a>
        </div>
    </div>
    <section class="invoice-preview-wrapper">
        <div class="row invoice-preview">
            <input type="hidden" class="invoice_cancelled_date" id="invoice_cancelled_date" value="{{$invoice->deleted_at}}">
            <!-- Invoice -->
            <div class=" col-12">
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
                                    Invoice#
                                    <span class="invoice-number">{{ $invoice->invoice_number }}</span>
                                </p>
                                <div class="invoice-date-wrapper mt-1">
                                    <p class="invoice-date-title">Invoice Date:</p>
                                    <p class="invoice-date">
                                        {{ $invoice->invoice_date ? $invoice->invoice_date->format('d-m-Y') : '' }}</p>
                                </div>
                                <div class="invoice-date-wrapper">
                                    <p class="invoice-date-title">Due Date:</p>
                                    <p class="invoice-date">
                                        {{ $invoice->due_date ? $invoice->due_date->format('d-m-Y') : '' }}</p>
                                </div>
                                <div class="payment-buttons  mt-auto">
                                    <a href="{{ route('invoices.subscription.edit', $invoice->encrypted_id) }}"
                                        class="btn btn-sm btn-outline-info waves-effect">
                                        <span>Edit</span>
                                        <i data-feather="edit"></i>
                                    </a>
                                    <a href="{{ route('invoices.preview', $invoice->encrypted_id) }}?v={{ config('versions.pdf') }}"
                                        class="btn btn-sm btn-outline-secondary waves-effect" target="__blank">
                                        <span>Preview</span>
                                        <i data-feather="eye"></i>
                                    </a>
                                    @if ($invoice->payment_status != 'unpaid')
                                        <a href="#"
                                           class="btn btn-sm btn-outline-receipt waves-effect" id="paymentReceiptSend">
                                            <span>Send Receipt</span>
                                            <i data-feather="send"></i>
                                        </a>

                                        <a href="{{ route('invoices.preview', ['invoice' => $invoice->encrypted_id, 'type' => 'payment_receipt', 'v' => config('versions.pdf')]) }}"
                                            class="btn btn-sm btn-outline-odnoklassniki waves-effect" target="__blank">
                                            <span>Receipt</span>
                                            <i data-feather="file-text"></i>
                                        </a>
                                    @endif
                                    <a href="#" class="btn btn-sm btn-outline-warning waves-effect"
                                         id="mailsend">
                                        <span>Send Mail</span>
                                        <i data-feather="send"></i>
                                    </a>
                                    @if ($invoice->payment_status == 'paid')
                                        <a href="#" class="btn btn-sm btn-outline-success waves-effect" id="paymentLinkBtn">
                                            <span>Payment link</span>
                                            <i data-feather="dollar-sign"></i>
                                        </a>

                                        @if($invoice->payment_reminder == 1)
                                            <a class="btn btn-sm btn-outline-success payment_reminder_cls" title="Payment reminder" id="invoice_already_paid_btn" style="padding: 0.2rem 1.2rem;">
                                                <i data-feather="check-circle"></i>
                                            </a>
                                        @else
                                            <a class="btn btn-sm btn-outline-soundcloud payment_reminder_cls" title="Payment reminder" id="invoice_already_paid_btn" style="padding: 0.2rem 1.2rem;">
                                                <i data-feather="slash"></i>
                                            </a>
                                        @endif
                                    @else
                                        <a href="#" class="btn btn-sm btn-outline-success waves-effect"
                                            id="paymentLinkBtn">
                                            <span>Payment link</span>
                                            <i data-feather="dollar-sign"></i>
                                        </a>

                                        @if($invoice->payment_reminder == 1)
                                            <a class="btn btn-sm btn-outline-success waves-effect payment_reminder_cls" title="Payment reminder enable" id="payment_reminder_btn" data-remindervalue="{{$invoice->payment_reminder}}" data-invoiceid="{{$invoice->id}}"
                                                style="padding: 0.2rem 1.2rem;">
                                                <i data-feather="check-circle"></i>
                                            </a>
                                        @else
                                            <a class="btn btn-sm btn-outline-soundcloud payment_reminder_cls" title="Payment reminder disable" id="payment_reminder_btn" data-remindervalue="{{$invoice->payment_reminder}}" data-invoiceid="{{$invoice->id}}"
                                                style="padding: 0.2rem 1.2rem;">
                                                <i data-feather="slash"></i>
                                            </a>
                                        @endif
                                    @endif
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
                                @forelse ($invoice->invoice_items as $invoice_item)
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
                                    @if ($invoice->credit_note)
                                        <div class="invoice-total-item">
                                            <p class="invoice-total-title">Credit Note
                                                #{{ $invoice->credit_note->invoice_number ?? '' }}:</p>
                                            <p class="invoice-total-amount text-danger">
                                                -{{ $invoice->credit_note->currency->symbol ?? '' }}{{ number_format((float) ($invoice->credit_note->grand_total ?? 0), 2, '.', ',') }}
                                            </p>
                                        </div>
                                    @endif
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

                    <!-- Invoice Note starts -->
                    {{--<div class="card-body pt-0">
                        <div class="row">
                            <div class="col-12">
                                <span class="fw-bold">Note:</span>
                                <span class="break-white-space">{!! $invoice->note ?? '' !!}</span>
                            </div>
                        </div>
                    </div>--}}
                    <!-- Invoice Note ends -->
                </div>
            </div>
            <!-- /Invoice -->
        </div>
    </section>

    @include('invoices.modals.send-mail-modal')

    @include('invoices.modals.send-receipt-mail-modal')

    @include('invoices.modals.invoice-preview-modal')

    @include('invoices.modals.payment-reminder')

    @include('invoices.modals.payment-link-modal')

    @include('invoices.modals.invoice-cancelled-modal')
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

        var contentReceiptQuill = new Quill('#contentReceiptQuill', {
            theme: 'snow',
            format: {
                fontFamily: 'Public Sans'
            }
        });
        $(document).ready(function() {
            /* Send Mail  */
            $('#to').select2({
                tags: true,
                dropdownParent: $('#sendMailModal').get(0),
            });
            $('#bcc').select2({
                tags: true,
                dropdownParent: $('#sendMailModal').get(0),
            });

            function copyReceiptContentToInput() {
                $('#receiptContent').val(contentReceiptQuill.root.innerHTML);
            }

            copyReceiptContentToInput();

            contentReceiptQuill.on('text-change', function(delta, oldDelta, source) {
                copyReceiptContentToInput();
            });

            function copyContentToInput() {
                $('#content').val(contentQuill.root.innerHTML);
            }

            copyContentToInput();

            contentQuill.on('text-change', function(delta, oldDelta, source) {
                copyContentToInput();
            });

            $(document).on('show.bs.modal', '#paymentLinkModal', function(e) {
                $('#paymentLinkSubmitBtn').prop('disabled', true);
                $('#paymentLinkModal > .modal-dialog').block({
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
                    url: route('invoices.payments.link.show', invoice.encrypted_id ?? '0'),
                    method: 'GET',
                    success: function(response) {
                        $('#payment_link').val(response.payment_link ?? '');
                        $('#paymentLinkModal > .modal-dialog').unblock();
                        $('#paymentLinkSubmitBtn').prop('disabled', false);
                    },
                    error: function(xhr, status, error) {
                        $('#paymentLinkModal > .modal-dialog').unblock();
                        $('#paymentLinkSubmitBtn').prop('disabled', false);
                    }
                });
            });

            $(document).on('hide.bs.modal', '#paymentLinkModal', function(e) {
                $('#payment_link').val('');
            });

            var sendMailFormValidator = $('#sendMailForm').validate({
                ignore: [],
                rules: {
                    "to[]": {
                        required: true,
                        validEmails: true,
                    },
                    "bcc[]": {
                        required: true,
                        validEmails: true,
                    },
                    subject: {
                        required: true,
                    },
                    content: {
                        required: true
                    }
                },
                messages: {
                    "to[]": {
                        required: "Plese enter a to email address",
                        validEmails: "Please enter valid email addresses",
                    },
                    "bcc[]": {
                        required: "Please enter a bcc email address",
                        validEmails: "Please enter valid email addresses",
                    },
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


            var paymentLinkValidator = $('#paymentLinkForm').validate({
                /*rules: {
                    payment_link: {
                        required: true,
                        url: true,
                    },
                },
                messages: {
                    payment_link: {
                        required: "Please enter payment link",
                        url: "Please enter a valid url",
                    },
                },*/
                submitHandler: function(form, event) {
                    event.preventDefault();
                    $('#paymentLinkSubmitBtn').prop('disabled', true);
                    $('#paymentLinkModal > .modal-dialog').block({
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
                                $('#paymentLinkModal').modal('hide');
                                toastr.success(null, "Link saved successfully!");
                                form.reset();
                                paymentLinkValidator.resetForm();
                            }
                            $('#paymentLinkModal > .modal-dialog').unblock();
                            $('#paymentLinkSubmitBtn').prop('disabled', false);
                        },
                        error: function(xhr, status, error) {
                            $('#paymentLinkModal > .modal-dialog').unblock();
                            $('#paymentLinkSubmitBtn').prop('disabled', false);
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
            });

            $('#paymentLinkModal').on('hide.bs.modal', function(e) {
                $('#paymentLinkForm').get(0).reset();
                paymentLinkValidator.resetForm();
            });

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
                    url: route('invoices.get-mail-content', invoice?.encrypted_id),
                    method: 'GET',
                    success: function(response) {
                        fillSendMailForm(response?.to, response?.bcc, response?.subject,
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
            $(document).on('hide.bs.modal', '#sendMailModal', function(e) {
                $('#to').empty();
                $('#bcc').empty();
                $('#subject').val('');
                contentQuill.root.innerHTML = '';
                copyContentToInput();
                $('form#sendMailForm').get(0).reset();
                sendMailFormValidator.resetForm();
            });

            function fillSendMailForm(to = [], bcc = [], subject = "", content = "") {

                if (Array.isArray(to) && to.length) {
                    to.forEach(email => {
                        $('#to').append(new Option(email, email, true, true));
                    });
                }

                if (Array.isArray(bcc) && bcc.length) {
                    bcc.forEach(email => {
                        $('#bcc').append(new Option(email, email, true, true));
                    });
                }
                $('#subject').val(subject);
                contentQuill.root.innerHTML = content;
                copyContentToInput();
            }

            /* Payment Receipt Custom Mail Send */
            $('#receipt_to').select2({
                tags: true,
                dropdownParent: $('#sendReceiptMailModal').get(0),
            });

            $('#receipt_bcc').select2({
                tags: true,
                dropdownParent: $('#sendReceiptMailModal').get(0),
            });

            var names = [];
            $('#custom_attach').on('change', function(e) {
                let TotalFiles = $('#custom_attach')[0].files.length; //Total files
                var fileSize = $('#custom_attach').get(0).files[0].size; // in bytes
                var maxSize = 5000000;
                if(fileSize > maxSize){
                    alert('file size is more then 5MB bytes');
                    return false;
                }

                for (var i = 0; i < $(this).get(0).files.length; i++) {
                    names.push($('#custom_attach')[0].files);
                }
                /*if(names != ''){
                    var name_len = (names.length + 1);
                    names.insert(name_len,$('#custom_attach')[0].files);
                }else{
                    names.push($('#custom_attach')[0].files);
                }*/
                let files = $('#custom_attach')[0];
                for (let i = 0; i < TotalFiles; i++) {
                    $("<div id='attachment_data_"+files.files[i].name+"'>"+"<span class='custom_file_attach'>"+files.files[i].name+"</span></div>").appendTo("#receiptNewFile");
                    /*<span style='color: red;margin-right: 10px' data-name='"+files.files[i].name+"' class='attach_file'>X</span>*/
                }
            });
            $(document).on('click', '.attach_file', function(e){
                var attach_name = $(this).data('name');
                names.forEach(function(file_name, index) {
                    let fileName = file_name[index].name;
                    if (attach_name == fileName) {
                        $("[id*='attachment_data_" + attach_name + "']").remove();
                        names = $.grep(names, function(item) {
                            return item[0].name !== attach_name;
                        });
                    }
                });
            });

            $('.receiptFile').on('click',function(){
                $(this).remove();
                $("#receiptFile").remove();
            });

            var sendReceiptMailForm = $('#sendReceiptMailForm').validate({
                ignore: [],
                rules: {
                    "receipt_to[]": {
                        required: true,
                        validEmails: true,
                    },
                    "receipt_bcc[]": {
                        required: true,
                        validEmails: true,
                    },
                    receipt_subject: {
                        required: true,
                    },
                    receipt_content: {
                        required: true
                    }
                },
                messages: {
                    "receipt_to[]": {
                        required: "Plese enter a to email address",
                        validEmails: "Please enter valid email addresses",
                    },
                    "receipt_bcc[]": {
                        required: "Please enter a bcc email address",
                        validEmails: "Please enter valid email addresses",
                    },
                    receipt_subject: {
                        required: "Please enter email subject line",
                    },
                    receipt_content: {
                        required: "Please enter email content"
                    }
                },
                submitHandler: function (form, event) {
                    event.preventDefault();
                    let formData = new FormData(form);
                    /*const custom_attach = $("#custom_attach")[0].files.length;
                    let files = $("#custom_attach")[0];
                    for (let i = 0; i < custom_attach; i++) {
                        formData.append('custom_attach[]' + i, files.files[i]);
                    }*/
                    formData.append("payment_receipt", $('#receiptFile').text());
                    $('#sendReceiptMailSubmitBtn').prop('disabled', true);
                    $('#sendReceiptMailModal > .modal-dialog').block({
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
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: formData,
                        processData: false,
                        cache: false,
                        contentType: false,
                        success: function (response) {
                            if (response.errors) {
                                $(form).validate().showErrors(response.errors);
                            } else {
                                $('#sendReceiptMailModal').modal('hide');
                                toastr.success(null, "Mail sent successfully!");
                            }
                            $('#sendReceiptMailModal > .modal-dialog').unblock();
                            $('#sendReceiptMailSubmitBtn').prop('disabled', false);
                        },
                        error: function (xhr, status, error) {
                            $('#sendReceiptMailModal > .modal-dialog').unblock();
                            $('#sendReceiptMailSubmitBtn').prop('disabled', false);
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
                errorPlacement: function (error, element) {
                    if (element.hasClass('select2') || element.attr('type') === 'hidden') {
                        error.appendTo(element.parent())
                    } else {
                        error.insertAfter(element);
                    }
                }
            });

            $(document).on('show.bs.modal', '#sendReceiptMailModal', function(e) {
                $('#sendReceiptMailSubmitBtn').prop('disabled', true);
                $('#sendReceiptMailModal > .modal-dialog').block({
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
                    url: route('invoices.get-receipt-mail-content', invoice?.encrypted_id),
                    method: 'GET',
                    success: function(response) {
                        fillSendReceiptMailForm(response?.receipt_to, response?.receipt_bcc, response?.subject,
                            response
                                ?.content, response?.attachment_receipt, response?.custom_attach);
                        $('#sendReceiptMailModal > .modal-dialog').unblock();
                        $('#sendReceiptMailSubmitBtn').prop('disabled', false);
                    },
                    error: function(xhr, status, error) {
                        $('#sendReceiptMailModal > .modal-dialog').unblock();
                        $('#sendReceiptMailSubmitBtn').prop('disabled', false);
                        Swal.fire({
                            title: 'An error occurred',
                            text: error,
                            icon: 'error',
                        });
                    }
                });
            });

            $(document).on('hide.bs.modal', '#sendReceiptMailModal', function(e) {
                $('#receipt_to').empty();
                $('#receipt_bcc').empty();
                $('#receipt_subject').val('');
                $('#custom_attach').val('');
                $('#attachment_receipt').empty();
                $('#receiptNewFile').empty();
                contentReceiptQuill.root.innerHTML = '';
                copyReceiptContentToInput();
                $('form#sendReceiptMailForm').get(0).reset();
                sendReceiptMailForm.resetForm();
            });

            function fillSendReceiptMailForm(receipt_to = [], receipt_bcc = [], subject = "", content = "", attachment_receipt = "") {

                if (Array.isArray(receipt_to) && receipt_to.length) {
                    receipt_to.forEach(email => {
                        $('#receipt_to').append(new Option(email, email, true, true));
                    });
                }

                if (Array.isArray(receipt_bcc) && receipt_bcc.length) {
                    receipt_bcc.forEach(email => {
                        $('#receipt_bcc').append(new Option(email, email, true, true));
                    });
                }
                $('#receipt_subject').val(subject);
                $('#receiptFile').text(attachment_receipt);
                contentReceiptQuill.root.innerHTML = content;
                copyReceiptContentToInput();
            }

        });
    </script>
@endsection
