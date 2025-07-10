@extends('layouts.app')

@section('vendor-css')
    <link rel="stylesheet"
        href="{{ asset('app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet"
        href="{{ asset('app-assets/vendors/bootstrap-datepicker/css/bootstrap-datepicker.min.css?v=' . config('versions.css')) }}">
@endsection

@section('page-css')
    <link rel="stylesheet"
        href="{{ asset('app-assets/css/plugins/forms/pickers/form-flat-pickr.css?v=' . config('versions.css')) }}">
@endsection

@section('custom-css')
    <link rel="stylesheet" href="{{ asset('app-assets/css/custom/bootstrap-datepicker.css?v=' . config('versions.css')) }}">
    <style>
        #paymentsTableContainer {
            max-height: 348px;
            overflow-y: auto;
        }

        #addPaymentModal .modal-dialog .modal-content .flatpickr-wrapper {
            width: 100%;
        }

        #invoiceNotesModal .modal-dialog .modal-content .flatpickr-wrapper {
            width: 100%;
        }

        .padding-right-6px {
            padding-right: 6px !important;
        }
        .invoicehighlight {
            background:#C4D9CD;
        }

        .invoiceCancelledhighlight {
            background:#ea5455;
        }
        .form-check.show_cancelled {
            padding: 18px;
            margin: 6px;
        }
        .padding-left-6px {
            padding-left: 6px !important;
        }

        .padding-bottom-6px {
            padding-bottom: 6px !important;
        }

        #createNewInvoiceModal .custom-option-item {
            --bs-bg-opacity: 0.2;
            color: #655b75;
        }

        #createNewInvoiceModal .custom-option-item-title {
            color: #655b75;
        }

        .invoiceNotesCountBadge {
            position: absolute;
            top: -2px;
            right: -2px;
            background-color: #ff9f43;
            border-radius: .55rem;
            font-size: .55rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            padding: .275rem;
            min-height: 1.1rem;
            min-width: 1.1rem;
        }
    </style>
@endsection

@section('content')
    <div class="col-md-12 d-flex justify-content-end align-items-start">
        <a class=" me-1" data-bs-toggle="collapse" href="#payment_receipt_export" role="button" aria-expanded="false" aria-controls="payment_receipt_export">
            Payment receipt export
        </a>
    </div>
    <form id="exportForm" method="post" action="#">
    <div class="collapse row mb-1 align-items-center receipt_export" id="payment_receipt_export">
        <div class="col-xxl-2 col-xl-2 col-lg-3 col-md-4 col-sm-6 form-group">
            <label class="form-label">From To Date Range<span class="text-danger"> *</span></label>
            <div class="input-group">
                <input type="text" class="form-control form-control-sm text-end pe-1" name="from_to_date_range" id="from_to_date_range">
            </div>
        </div>
        <div class="col-12 col-sm-3 col-md-3 col-lg-3 col-xl-2 form-group">
            <label for="export_payment_source_id" class="form-label">Source</label>
            <select id="export_payment_source_id" class="form-select form-select-sm select2 select2-size-sm">
                <option value="" selected>All</option>
                @foreach ($payment_sources as $source)
                    <option value="{{ $source->id }}">{{ $source->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-sm-3 col-md-3 col-lg-3 col-xl-2 form-group">
            <label for="export_client_id" class="form-label">Customer</label>
            <select id="export_client_id" class="form-select form-select-sm select2 select2-size-sm">
                <option value="" selected>All</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-2 form-group">
            <label for="export_company_id" class="form-label">Company</label>
            <select id="export_company_id" class="form-select form-select-sm select2 select2-size-sm">
                <option value="" selected>All</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-2 form-group">
        <button type="button" class="btn btn-primary me-1" {{--data-bs-toggle = "modal"
            data-bs-target= "#paymentReceiptExportModal"--}} id="payment_receipt_export_Confirm">Export</button>
        </div>
    </div>
    </form>
    <div class="row align-items-start px-0 mb-1" id="invoiceFilters">
        <div class=" col-xl-2 col-lg-4 col-md-5 col-sm-7 form-group">
            <label for="filter_payment" class="form-label">Filter</label>
            @if(request('filter_payment') === 'all')
                <select id="filter_payment" class="form-select form-select-sm select2 text-end" multiple="multiple">
                    <option value="paid" @if (request('filter_payment')) selected @endif>Paid</option>
                    <option value="partially_paid" @if (request('filter_payment')) selected @endif>Partially Paid</option>
                    <option value="unpaid" @if (request('filter_payment')) selected @endif>Unpaid</option>
                    <option value="{{App\Enums\InvoiceType::CREDIT_NOTE}}" @if (request('filter_payment') === '1') selected @endif>Credit Note</option>
                </select>
            @else
                <select id="filter_payment" class="form-select form-select-sm select2 text-end" multiple="multiple">
                    <option value="paid" @if (request('filter_payment') === 'paid') selected @endif>Paid</option>
                    <option value="partially_paid" @if (request('filter_payment')) selected @endif>Partially Paid</option>
                    <option value="unpaid" @if (request('filter_payment') === 'unpaid') selected @endif>Unpaid</option>
                    <option value="{{App\Enums\InvoiceType::CREDIT_NOTE}}" @if (request('filter_payment') === '1') selected @endif>Credit Note</option>
                </select>
            @endif
        </div>
        {{-- <div class=" col-xl-3 col-lg-4 col-sm-6 form-group filter_date_range">
            <label class="form-label">Due Date</label>
            <div class="input-group input-group-merge">
                <input type="text" class="form-control form-control-sm text-end pe-1" name="filter_due_date"
                    id="filter_due_date">
                <span class="input-group-text bg-transparent" data-flatpickr-clear-target="#filter_due_date">
                    <i data-feather="x-circle"></i>
                </span>
            </div>
        </div> --}}
        <div class="col-xl-2 col-lg-2 col-md-3 col-sm-5 form-group">
            <label for="filter_created_at" class="form-label">Invoice Date</label>
            <select id="filter_created_at" class="form-select select2">
                <option value="all">All</option>
                <option value="month" selected>This month</option>
                <option value="last_month" {{ request()->input('created_at') === 'last_month' ? 'selected' : '' }}>Last
                    month</option>
                <option value="3_months" {{ request()->input('created_at') === '3_months' ? 'selected' : '' }}>Last 3
                    months</option>
                <option value="year" {{ request()->input('created_at') === 'year' ? 'selected' : '' }}>Current year
                </option>
                <option value="custom" {{ request()->input('created_at') === 'custom' ? 'selected' : '' }}>Custom
                </option>
            </select>
        </div>
        <div
            class="col-xxl-2 col-xl-2 col-lg-3 col-md-4 col-sm-6 form-group @if (!request('created_at_start') && !request('created_at_end')) d-none @endif">
            <label class="form-label">Invoice Date Range</label>
            <div class="input-group">
                <input type="text" class="form-control form-control-sm text-end pe-1" name="filter_created_at_range"
                    id="filter_created_at_range">
            </div>
        </div>
        {{-- <div class="w-100 d-none"></div> --}}
        <div class="col-sm-4 col-lg-3 col-xl-2 col-xxl-2 ms-lg-auto pe-md-1 ps-md-0 order-last ms-sm-auto order-sm-4">
            <div class="card mt-1 mt-xl-0 mb-0 last-month-stats bg-light-warning">
                <div class="card-header p-1 pb-0 padding-left-6px">
                    <h6 class="text-reset">
                        Last month
                    </h6>
                </div>
                <div class="card-body row p-0">
                    <div class="col-12 d-flex justify-content-between">
                        <span class="nowrap-white-space padding-left-6px">
                            With VAT
                        </span>
                        <span class="ms-auto nowrap-white-space padding-right-6px" id="last_month_w_vat">
                            £0
                        </span>
                    </div>
                    <div class="col-12 d-flex justify-content-between">
                        <span class="nowrap-white-space padding-left-6px">
                            Without VAT
                        </span>
                        <span id="last_month_wo_vat" class="nowrap-white-space padding-right-6px padding-bottom-6px">
                            £0
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4 col-lg-3 col-xl-2 col-xxl-2 ps-md-0 order-last ms-md-0">
            <div class="card mt-1 mt-xl-0 mb-0 this-month-stats bg-light-success">
                <div class="card-header p-1 pb-0 padding-left-6px">
                    <h6 class="text-reset">This month</h6>
                </div>
                <div class="card-body row p-0">
                    <div class="col-12 d-flex justify-content-between">
                        <span class="nowrap-white-space padding-left-6px">
                            With VAT
                        </span>
                        <span class="ms-auto nowrap-white-space padding-right-6px" id="this_month_w_vat">
                            £0
                        </span>
                    </div>
                    <div class="col-12 d-flex justify-content-between">
                        <span class="nowrap-white-space padding-left-6px">
                            Without VAT
                        </span>
                        <span id="this_month_wo_vat" class="nowrap-white-space padding-right-6px padding-bottom-6px">
                            £0
                        </span>
                    </div>
                </div>
            </div>
        </div>
        {{-- <div class="w-100"></div> --}}
        <div class="col-12 col-sm-3 col-md-3 col-lg-3 col-xl-2 form-group">
            <label for="filter_payment_source_id" class="form-label">Source</label>
            <select id="filter_payment_source_id" class="form-select form-select-sm select2 select2-size-sm">
                <option value="" selected>All</option>
                @foreach ($payment_sources as $source)
                    <option value="{{ $source->id }}">{{ $source->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-sm-3 col-md-3 col-lg-3 col-xl-2 form-group">
            <label for="filter_client_id" class="form-label">Customer</label>
            <select id="filter_client_id" class="form-select form-select-sm select2 select2-size-sm">
                <option value="" selected>All</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-sm-6 col-md-4 col-lg-2 form-group">
            <label for="filter_company_id" class="form-label">Company</label>
            <select id="filter_company_id" class="form-select form-select-sm select2 select2-size-sm">
                <option value="" selected>All</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-2 form-group">
            <div class="form-check show_cancelled">
                <input class="form-check-input" type="checkbox" id="filter_show_cancelled">
                <label class="form-check-label" for="filter_show_cancelled">
                    Show Cancelled Invoice
                </label>
            </div>
        </div>
        <div class="row d-none d-sm-flex d-lg-none d-xl-flex mb-1"></div>
    </div>
    {{-- <div class="col-md-2 order-first order-sm-last d-flex justify-content-end align-items-start">
            <a class="me-1" data-bs-toggle="collapse" href="#invoiceFilters" role="button" aria-expanded="false"
                aria-controls="invoiceFilters">
                Advanced Search
            </a>
        </div> --}}
    </div>
    <section class="invoices_list">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <table class="invoices-table table">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice No.</th>
                            <th>Type</th>
                            <th>Name</th>
                            <th>Company</th>
                            <th>Invoice Date</th>
                            <th class="text_right">Grand Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>

    @include('invoices.modals.add-payment-modal')

    @include('invoices.modals.notes-modal')

    @include('invoices.modals.invoice-preview-modal')

    @include('invoices.modals.payment-reminder')

    @include('invoices.modals.create-new-invoice-modal')

    @include('invoices.modals.export-modal')

    @include('invoices.modals.note-reminders-modal')
@endsection

@section('page-js')
    <script src="{{ asset('app-assets/js/custom/invoice_custom.js?v=' . config('versions.js')) }}"></script>
@endsection

@section('page-vendor-js')
    <script src="{{ asset('app-assets/vendors/js/extensions/moment.min.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js?v=' . config('versions.js')) }}">
    </script>
    <script src="{{ asset('app-assets/js/custom/flatpickr.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/vendors/bootstrap-datepicker/js/bootstrap-datepicker.min.js?v=' . config('versions.js')) }}"></script>
@endsection

@section('custom-js')
    <script>
        const invoiceNoteForm = $('#invoiceNoteForm');
        $(document).ready(function() {
            $.fn.dataTableExt.oStdClasses.sFilter = "dataTables_filter dataTables_filter_sm";
            const invoiceTypeOneOff = @json(config('custom.invoices_types.one-off', '0'));
            const invoiceTypeSubscription = @json(config('custom.invoices_types.subscription', '1'));
            const subscriptionTypeMonthly = @json(config('custom.subscription_types.monthly', '0'));
            const subscriptionTypeYearly = @json(config('custom.subscription_types.yearly', '1'));
            const invoiceTypes = @json(App\Enums\InvoiceType::all());
            const pdfVersion = @json(config('versions.pdf'));
            const invoicePaymentStatuses = @json(App\Enums\InvoicePaymentStatus::all());
            const $filterPaymentSource = $('#filter_payment_source_id');
            const $filterClient = $('#filter_client_id');
            const $filterCompany = $('#filter_company_id');
            const $filterCreated = $('#filter_created_at');
            const $filterPayment = $('#filter_payment');
            const $filter_show_cancelled = $('#filter_show_cancelled');

            const $exportPaymentSource = $('#export_payment_source_id');
            const $exportClientId = $('#export_client_id');
            const $exportCompanyId = $('#export_company_id');

            const isInvoiceNewValues = @json(App\Enums\IsInvoiceNew::all());
            var invoiceTableStateSave = !localStorage.getItem('invoices.list.page-first');
            localStorage.removeItem('invoices.list.page-first')

            var filterCreatedAtRangeFormGroup = $('#filter_created_at_range').closest('.form-group');
            // var due_date_flatpickr = $('#filter_due_date').flatpickr({
            //     mode: 'range',
            //     dateFormat: 'd/m/Y',
            //     defaultDate: ["{{ date('d/m/Y', strtotime('first day of this month')) }}",
            //         "{{ date('d/m/Y', strtotime('last day of this month')) }}"
            //     ],
            //     onChange: function(selectedDates, dateStr, instance) {
            //         if (selectedDates.length === 2) redrawInvoiceTable();
            //     }
            // });

            // $(document).on('flatpickr:cleared', '#filter_due_date', function(e) {
            //     redrawInvoiceTable();
            // })
            var filter_created_at_range_picker = $('#filter_created_at_range').flatpickr({
                mode: 'range',
                dateFormat: 'd/m/Y',
                defaultDate: [
                    "{{ request('created_at_start', date('d/m/Y', strtotime('first day of this month'))) }}",
                    "{{ request('created_at_end', date('d/m/Y', strtotime('last day of this month'))) }}"
                ],
                onChange: function(selectedDates, dateStr, instance) {
                    redrawInvoiceTable();
                }
            });

            var from_to_date_range_picker = $('#from_to_date_range').flatpickr({
                mode: 'range',
                dateFormat: 'd-m-Y',
                defaultDate: [
                    "{{ request('from_at_start', date('d-m-Y', strtotime('-1 month'))) }}",
                    "{{ request('to_at_end', date('d-m-Y')) }}"
                ],
            });

            $(document).on('flatpickr:cleared', '#filter_created_at_range', function(e) {
                redrawInvoiceTable();
            });

            $('.select2').select2();

            $('#filter_payment').select2({
                multiple: true,
                containerCssClass: 'select-sm',
            });

            $filterPaymentSource.select2({
                containerCssClass: 'select-sm'
            });

            $exportPaymentSource.select2({
                containerCssClass: 'select-sm'
            });
            $exportClientId.select2({
                containerCssClass: 'select-sm'
            });
            $exportCompanyId.select2({
                containerCssClass: 'select-sm'
            });

            $filterClient.select2({
                containerCssClass: 'select-sm'
            });

            $filterCompany.select2({
                containerCssClass: 'select-sm'
            });

            $filterPaymentSource.on('change.select2', function(e) {
                redrawInvoiceTable();
            });

            $exportPaymentSource.on('change.select2', function(e) {
                redrawInvoiceTable();
            });
            $exportClientId.on('change.select2', function(e) {
                redrawInvoiceTable();
            });
            $exportCompanyId.on('change.select2', function(e) {
                redrawInvoiceTable();
            });

            $('#filter_show_cancelled').on('change', function(e) {
                $('#filter_show_cancelled').val(this.checked);
                redrawInvoiceTable();
            });

            $filterClient.on('change.select2', function(e) {
                redrawInvoiceTable();
            });

            $filterCompany.on('change.select2', function(e) {
                redrawInvoiceTable();
            });

            $('#filter_payment').on('change.select2', function(e) {
                redrawInvoiceTable();
            });

            $.validator.addMethod("validDate", function(value, element, params) {
                return this.optional(element) || moment(value, params ?? "DD/MM/YYYY").isValid();
            }, "Please enter a valid date in the format {0}");

            $('#paid_at').flatpickr({
                dateFormat: 'd/m/Y',
                static: true,
            });

            var redrawInvoiceTable = (paging = true) => invoicesDataTable && invoicesDataTable.draw(paging);

            const rememberFilters = @json($rememberFilters);
            const invoicesFilterPaymentSource = @json(request()->session()->get('invoices_filter_payment_source'));
            const invoicesFilterClient = @json(request()->session()->get('invoices_filter_client'));
            const invoicesFilterCompany = @json(request()->session()->get('invoices_filter_company'));
            const invoicesFilterCreated = @json(request()->session()->get('invoices_filter_created'));
            const invoicesFilterCreatedEnd = @json(request()->session()->get('invoices_filter_created_end'));
            const invoicesFilterCreatedStart = @json(request()->session()->get('invoices_filter_created_start'));
            const invoicesFilterStatus = @json(request()->session()->get('invoices_filter_status'));
            const invoicesFilterShowCancelled  = @json(request()->session()->get('invoices_filter_show_cancelled'));

            if (rememberFilters) {
                if (invoicesFilterPaymentSource) $filterPaymentSource
                    .val(invoicesFilterPaymentSource)
                    .trigger('change.select2');
                if (invoicesFilterClient) $filterClient
                    .val(invoicesFilterClient)
                    .trigger('change.select2');
                if (invoicesFilterCompany) $filterCompany
                    .val(invoicesFilterCompany)
                    .trigger('change.select2');
                if (invoicesFilterCreated) {
                    $filterCreated
                        .val(invoicesFilterCreated)
                        .trigger('change');
                    if (invoicesFilterCreated == 'custom') {
                        $('#filter_created_at_range')
                            .closest('.form-group')
                            .removeClass('d-none');
                        if (invoicesFilterCreatedStart && invoicesFilterCreatedEnd) {
                            let start = new Date(invoicesFilterCreatedStart?.date);
                            let end = new Date(invoicesFilterCreatedEnd?.date);
                            filter_created_at_range_picker.setDate([start, end], true);
                        }
                    }
                }
                if(invoicesFilterShowCancelled) $filter_show_cancelled
                    .val(invoicesFilterShowCancelled);

                if(invoicesFilterShowCancelled == "true"){
                    $filter_show_cancelled.attr( 'checked', true )
                }

                if (invoicesFilterStatus && invoicesFilterStatus.length) {
                    $filterPayment
                        .val('')
                        .trigger('change.select2');
                    invoicesFilterStatus.forEach(status => {
                        $filterPayment
                            .find(`option[value=${status}]`)
                            .prop('selected', true);
                    });
                    $filterPayment.trigger('change.select2');
                }
            }

            var invoicesDataTable = $('.invoices-table').DataTable({
                serverSide: true,
                processing: true,
                stateSave: invoiceTableStateSave,
                ajax: {
                    url: route('invoices.index'),
                    data: function(d) {
                        d.filter_created_at = $('#filter_created_at').val();
                        d.filter_created_at_range = $('#filter_created_at_range').val();
                        // d.filter_due_date = $('#filter_due_date').val();
                        d.filter_payment = $('#filter_payment').val();
                        d.filter_payment_source_id = $filterPaymentSource.val();
                        d.filter_client_id = $filterClient.val();
                        d.filter_company_id = $filterCompany.val();
                        d.filter_show_cancelled = $filter_show_cancelled.val();
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        if (xhr.status === 401) {
                            // Redirect to the login page if unauthorized
                            window.location.href = "{{ route('login') }}";
                        } else {
                            // Handle other errors
                            toastr.error(null,'Session hase been expire!');
                        }
                    }
                },
                order: [
                    [4, 'desc']
                ],
                drawCallback: function(settings) {
                    var api = this.api()
                    api.state.save();
                },
                initComplete: function(settings, json) {
                    $('.invoices-table-wrapper div.dataTables_length select').addClass(
                        'form-select-sm');
                    $('.invoice-table__buttons').tooltip('dispose');
                    $('.invoice-table__buttons').tooltip({
                        title: "Create Invoice",
                        placement: "bottom",
                    });
                    $('.invoice-table-buttons__export-btn').tooltip('dispose');
                    $('.invoice-table-buttons__export-btn').tooltip({
                        title: "Export to XLS",
                        placement: "bottom",
                    });
                    $('.invoice-table-with-bank-buttons__export-btn').tooltip('dispose');
                    $('.invoice-table-with-bank-buttons__export-btn').tooltip({
                        title: "Export to XLS",
                        placement: "bottom",
                    });
                    $('.invoice-table-buttons__note-btn').tooltip('dispose');
                    $('.invoice-table-buttons__note-btn').tooltip({
                        title: "Invoice Note",
                        placement: "bottom",
                    });
                },
                columns: [{
                        data: 'invoice_number',
                        name: 'invoice_number',
                        searchable: true,
                        render: function(data, type, full) {
                            var pdfVersion = "{{ config('versions.pdf') }}";
                            if (full?.type == invoiceTypes?.INVOICE) {

                                let subType = full['invoice_type'] == invoiceTypeOneOff ?
                                    '' :
                                    (full['subscription_status'] != 'cancelled' ? (full['subscription_type'] == subscriptionTypeYearly ? '(Y)' : '(M)') : '');
                                var invoicepriview = "{{ route('invoices.preview', ":id") }}";
                                invoicepriview = invoicepriview.replace(':id', full['encrypted_id']);

                                return '<a href="' + invoicepriview + '?v='+pdfVersion+'" class="text-primary" target="_blank">' +
                                    '<span class="invoice-number">#' + full['invoice_number'] + '</span>' +
                                    '</a>&nbsp;' +
                                    subType;
                            } else if (full?.type == invoiceTypes?.CREDIT_NOTE) {
                                var invoicepriview = "{{ route('credit_notes.preview', ":id") }}";
                                invoicepriview = invoicepriview.replace(':id', full['encrypted_id']);

                                return '<a href="' + invoicepriview + '?v=' + pdfVersion + '" class="text-primary" target="_blank">' +
                                    '<span class="invoice-number">' + '#' + full['invoice_number'] +'</span>' +
                                    '</a>' +
                                    '&nbsp;';
                            } else {
                                return '';
                            }
                        }
                    },
                    {
                        data: 'type',
                        searchable: false,
                        render: function(data, type, full) {
                            switch (full?.type) {
                                case invoiceTypes?.INVOICE:
                                    return "Invoice";
                                    break;
                                case invoiceTypes?.CREDIT_NOTE:
                                    return "Credit Note";
                                    break;
                                default:
                                    return "Invoice";
                                    break;
                            }
                        },
                    },
                    {
                        data: 'client.name',
                        name: 'client.name',
                        className: 'text-nowrap',
                        searchable: true,
                    },
                    {
                        data: 'company_detail.name',
                        name: 'company_detail.name',
                        defaultContent: '',
                        className: 'text-nowrap',
                        searchable: false,
                    },
                    {
                        data: 'invoice_date',
                        searchable: false,
                    },
                    {
                        data: 'grand_total',
                        searchable: true,
                        createdCell: function(td, cellData, rowData, row, col) {
                             /*Parent_invoice is not null or Not deleted then below condition true */
                        if(rowData?.parent_invoice_id != null && rowData?.parent_invoice != null){
                             let invoiceprev = rowData?.parent_invoice.encrypted_id;
                             var url = '{{ route("invoices.preview", ":id") }}';
                                url = url.replace(':id', invoiceprev);
                            }

                            let curSymbol = rowData?.currency?.symbol ?? '';
                             if(rowData.credit_note != null){
                                var credpreview = rowData.credit_note?.encrypted_id
                                var credpreviewurl = '{{ route("credit_notes.preview", ":id") }}';
                                credpreviewurl = credpreviewurl.replace(':id', credpreview);
                              var  fullpedcred = rowData.credit_note?.invoice_number;
                            }
                            if(rowData.parent_invoice?.invoice_number != null){
                               var invoiceno = rowData.parent_invoice?.invoice_number;
                           }
                            td.classList.add('text-end');
                            if (rowData?.type == invoiceTypes?.CREDIT_NOTE) {
                                td.classList.add('text-danger');
                                td.innerHTML = '-' + curSymbol + cellData;
                                 td.innerHTML += `<div class="text-warning"> <a href="${url}"
                                        class="text-primary" target="__blank">
                                    #${invoiceno}
                                    </a></div>`;

                            }else if (rowData.credit_note !=null) {
                                td.innerHTML =  curSymbol + cellData;
                                td.innerHTML += `<div class="text-warning"><a href="${credpreviewurl}"
                                        class="text-primary" target="__blank">#${fullpedcred}
                                    </a></div>`;
                                $(td).closest('tr').addClass('invoicehighlight');
                            }
                             else {
                                td.innerHTML = curSymbol + cellData;
                                if (rowData?.payment_status == invoicePaymentStatuses
                                    ?.PARTIALLY_PAID) {
                                    let paymentSumAmt = Number(rowData?.payments_sum_amount ?? 0);
                                    let creditNoteAmt = Number(rowData?.credit_note?.grand_total ??
                                        0);
                                    let grandTotal = Number(rowData?.grand_total ?? 0);
                                    let dueAmount = grandTotal - paymentSumAmt - creditNoteAmt;
                                    dueAmount = dueAmount > 0 ? dueAmount : 0;
                                    dueAmount = curSymbol + dueAmount.toLocaleString(
                                        'us', {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2,
                                        });
                                    td.innerHTML += `<div class="text-warning">${dueAmount}</div>`;
                                }
                            }
                             if(rowData?.deleted_at != null){
                                 td.innerHTML =  curSymbol + cellData;
                                 $(td).closest('tr').addClass('invoiceCancelledhighlight');
                             }
                        },
                    },
                    {
                        data: 'payment_status',
                        searchable: false,
                        orderable: true,
                        render: function(data, type, full) {
                            if (full?.type == invoiceTypes?.CREDIT_NOTE || full.credit_note != null) {
                                return '';
                            }
                            switch (full['payment_status']) {
                                case 'paid':
                                    return '<span class="badge bg-gradient bg-success rounded-pill">Paid</span>';
                                    break;
                                case 'partially_paid':
                                    return '<span class="badge bg-gradient bg-warning rounded-pill">Partially Paid</span>';
                                    break;
                                case 'unpaid':
                                    if (full?.is_new == isInvoiceNewValues?.NEW) {
                                        return '<span class="badge bg-gradient bg-blue rounded-pill">New</span>';
                                    }
                                    return '<span class="badge bg-gradient bg-danger rounded-pill">Unpaid</span>';
                                    break;
                                default:
                                    return '';
                                    break;
                            }
                        }
                    },
                    {
                        data: null,
                        searchable: false,
                        orderable: false,
                        "render": function(data, type, full) {
                            if (full?.type == invoiceTypes?.INVOICE) {
                                let invoiceNotesCount = full['invoice_notes'].length;
                                let invoice_detail_btn =
                                    `<a class="btn btn-sm btn-icon btn-flat-secondary" title="Detail" href="${route(full['invoice_type'] == invoiceTypeSubscription ? 'invoices.subscription.show' : 'invoices.show', full['encrypted_id'])}" >${feather.icons['eye'].toSvg({ class: 'font-medium-3' })}</a>`;

                                let invoice_edit_btn =
                                    `<a class="btn btn-sm btn-icon btn-flat-info" title="Edit" href="${route(full['invoice_type'] == invoiceTypeSubscription ? 'invoices.subscription.edit' : 'invoices.edit', full['encrypted_id'])}" >${feather.icons['edit'].toSvg({ class: 'font-medium-3' })}</a>`;
                                let salesPersonName = full.sales_person?.full_name ?
                                    `(${full.sales_person?.full_name})` : '';
                                let invoice_payment_btn = `<button
                                    data-invoice-id="${full['encrypted_id']}"
                                    data-status="${full['payment_status']}"
                                    data-sales-person-name="${salesPersonName}"
                                    type="button"
                                    class="btn btn-sm btn-icon btn-flat-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#addPaymentModal"
                                    >
                                    ${feather.icons['dollar-sign'].toSvg({ class: 'font-medium-3' })}
                                </button>`;

                                let invoice_copy_btn =
                                    `<a class="btn btn-sm btn-icon custom-btn-flat-indigo" title="Copy invoice" href="${route(full['invoice_type'] == invoiceTypeSubscription && full['subscription_status'] != 'cancelled' && full['invoice_type'] == '1' ? 'invoices.subscription.create' : 'invoices.one-off.create', {copy_invoice_id: full['encrypted_id'] } )}">${feather.icons['copy'].toSvg({ class: 'font-medium-3' })}</a>`;

                                let creditNoteBtn =
                                    '<a ' +
                                    'class="btn btn-sm btn-icon btn-flat-secondary" ' +
                                    'title="Add Credit Note" ' +
                                    'href="' +
                                    (route('invoices.credit_notes.create', full?.encrypted_id)) +
                                    '">' +
                                    '<img src="app-assets/images/ico/credit_note.svg" height="20" width="20" />'+
                                '</a>';

                                var paymentReminder = '';
                                if(full?.payment_status == 'paid'){
                                    if(full?.payment_reminder == '1'){
                                        paymentReminder =
                                            `<a
                                            class="btn btn-sm btn-icon btn-flat-secondary"
                                            title="Payment reminder "
                                            data-bs-toggle="modal"
                                            data-bs-target="#invoice_already_paid_model">
                                            ${feather.icons['check-circle'].toSvg({ class: 'font-medium-3' })}
                                            </a>`;
                                    }else{
                                            paymentReminder =
                                                `<a
                                            class="btn btn-sm btn-icon btn-flat-secondary"
                                            title="Payment reminder "
                                            data-bs-toggle="modal"
                                            data-bs-target="#invoice_already_paid_model">
                                            ${feather.icons['slash'].toSvg({ class: 'font-medium-3' })}
                                            </a>`;
                                    }
                                }else {
                                    if(full?.payment_reminder == '1'){
                                        paymentReminder = `<button
                                            type="button"
                                            title="Payment reminder enable"
                                            data-remindervalue="${full?.payment_reminder}"
                                            data-invoiceid="${full?.encrypted_id}"
                                            class="btn btn-sm btn-icon btn-flat-secondary payment_reminder_cls"
                                            data-bs-toggle="modal"
                                            data-bs-target="#payment_reminder_enable_model">
                                            ${feather.icons['check-circle'].toSvg({ class: 'font-medium-3' })}
                                        </button>`;
                                    }else {
                                        paymentReminder =
                                            `<a
                                            class="btn btn-sm btn-icon btn-flat-secondary payment_reminder_cls"
                                            data-remindervalue="${full?.payment_reminder}"
                                            data-invoiceid="${full?.encrypted_id}"
                                            title="Payment reminder disable"
                                            data-bs-toggle="modal"
                                            data-bs-target="#payment_reminder_enable_model">
                                            ${feather.icons['slash'].toSvg({ class: 'font-medium-3' })}
                                            </a>`;
                                    }
                                }

                                let invoiceNoteCountBadge = invoiceNotesCount > 0 ?
                                    '<span class="invoiceNotesCountBadge">'+
                                    invoiceNotesCount
                                    +'</span>' :
                                    '';

                                let invoice_notes_btn =
                                    `<button
                                    data-id="${full['encrypted_id']}"
                                    data-sales-person-name="${salesPersonName}"
                                    title="Add Notes"
                                    type="button"
                                    class="btn btn-sm btn-icon btn-flat-warning position-relative"
                                    data-bs-toggle="modal"
                                    data-bs-target="#invoiceNotesModal"
                                    >
                                    ${feather.icons['file-plus'].toSvg({ class: 'font-medium-3' })} ${invoiceNoteCountBadge}
                                </button>`;


                                let showCreditNoteBtn = (full?.type != invoiceTypes?.CREDIT_NOTE) &&
                                    (full?.payment_status == 'unpaid' || full?.payment_status ==
                                        'partially_paid') && !(full?.credit_note);

                                var invoice_cancelled_btn = '';
                                if(full?.payment_status == 'unpaid'){
                                    invoice_cancelled_btn =
                                        '<button ' +
                                        'data-id="' + full?.encrypted_id + '" ' +
                                        'class="btn btn-sm btn-icon btn-flat-danger invoiceCancelledBtn" ' +
                                        'title="Invoice cancelled " ' +
                                        '>' +
                                        feather.icons['trash'].toSvg({
                                            class: 'font-medium-3'
                                        }) +
                                        '</button>';
                                }

                                let invoice_restore_btn =
                                    `<button data-id="${full['encrypted_id']}" title="Invoice Restore" class="btn btn-sm btn-icon btn-flat-primary invoiceRestoreBtn" >${feather.icons['refresh-cw'].toSvg({ class: 'font-medium-3' })}</button>`;

                                let buttons = '';

                                if (full?.deleted_at != null) {
                                    buttons = `<div
                                    class="d-flex invoice-list__action_buttons"
                                    role="group"
                                    aria-label="Basic mixed styles example"
                                    >
                                ${invoice_detail_btn}
                                ${invoice_notes_btn}
                                ${invoice_copy_btn}
                                ${invoice_restore_btn}
                                </div>`;
                                } else {
                                    buttons = `<div
                                    class="d-flex invoice-list__action_buttons"
                                    role="group"
                                    aria-label="Basic mixed styles example"
                                    >
                                ${invoice_detail_btn}
                                ${invoice_edit_btn}
                                ${invoice_notes_btn}
                                ${invoice_payment_btn}
                                ${invoice_copy_btn}
                                ${paymentReminder}
                                ${ showCreditNoteBtn ? creditNoteBtn : '' }
                                ${invoice_cancelled_btn}
                                </div>`;
                                }

                                return (buttons);
                            } else if (full?.type == invoiceTypes?.CREDIT_NOTE) {
                                let invoiceNotesCount = full['invoice_notes'].length;
                                let creditNoteDetailBtn =
                                    `<a class="btn btn-sm btn-icon btn-flat-secondary" title="Detail" href="${route('credit_notes.show', {credit_note: full?.encrypted_id}) }" >${feather.icons['eye'].toSvg({ class: 'font-medium-3' })}</a>`;

                                let creditNoteEditBtn =
                                    `<a class="btn btn-sm btn-icon btn-flat-info" title="Edit" href="${route('credit_notes.edit', full['encrypted_id'])}" >${feather.icons['edit'].toSvg({ class: 'font-medium-3' })}</a>`;

                                /*let creditNoteDeleteBtn =
                                    '<button ' +
                                    'data-id="' + full?.encrypted_id + '" ' +
                                    'class="btn btn-sm btn-icon btn-flat-danger creditNoteDeleteBtn" ' +
                                    'title="Delete" ' +
                                    '>' +
                                    feather.icons['trash'].toSvg({
                                        class: 'font-medium-3'
                                    }) +
                                    '</button>';*/

                                let invoiceNoteCountBadge = invoiceNotesCount > 0 ?
                                    '<span class="invoiceNotesCountBadge">'+
                                    invoiceNotesCount
                                    +'</span>' :
                                    '';

                                 let invoice_notes_btn =
                                    `<button
                                    data-id="${full['encrypted_id']}"
                                    data-sales-person-name="${salesPersonName}"
                                    title="Add Notes"
                                    type="button"
                                    class="btn btn-sm btn-icon btn-flat-warning position-relative"
                                    data-bs-toggle="modal"
                                    data-bs-target="#invoiceNotesModal"
                                    >
                                    ${feather.icons['file-plus'].toSvg({ class: 'font-medium-3' })} ${invoiceNoteCountBadge}
                                </button>`;

                                let buttons = `<div
                                    class="d-flex invoice-list__action_buttons"
                                    role="group"
                                    aria-label="Basic mixed styles example"
                                    >
                                ${creditNoteDetailBtn}
                                ${creditNoteEditBtn}
                                ${invoice_notes_btn}

                                </div>`;

                                return buttons;
                            } else {
                                return '';
                            }
                        }
                    }
                ],
                dom: '<"d-flex justify-content-between align-items-center header-actions mx-1 row mt-50 invoices-table-wrapper"r' +
                    '<"col-sm-12 col-lg-4 d-flex justify-content-center justify-content-lg-start" l>' +
                    '<"col-sm-12 col-lg-8 ps-xl-75 ps-0"<"dt-action-buttons d-flex align-items-center justify-content-center justify-content-lg-end flex-lg-nowrap flex-wrap"<"me-1"B>f>>' +
                    '>t' +
                    '<"d-flex justify-content-between mx-2 row mb-1"' +
                    '<"col-sm-12 col-md-6"i>' +
                    '<"col-sm-12 col-md-6"p>' +
                    '>',

                buttons: [
                    {
                        text: `${feather.icons['plus-circle'].toSvg({ class: 'font-small-4' })}`,
                        className: 'add-new btn  btn-primary btn-icon btn-sm invoice-table__buttons',
                        attr: {
                            'data-bs-toggle': 'modal',
                            'data-bs-target': '#createNewInvoiceModal',
                        }
                    },
                    /*{
                        text: `${feather.icons['file-text'].toSvg({ class: 'font-small-4' })}`,
                        className: 'btn btn-secondary btn-icon btn-sm invoice-table-buttons__export-btn',
                        attr: {
                            'data-bs-toggle': 'modal',
                            'data-bs-target': '#exportModal',
                        }
                    },*/
                    {
                        text: `${feather.icons['file-text'].toSvg({ class: 'font-small-4' })}`,
                        className: 'btn btn-secondary btn-icon btn-sm invoice-table-with-bank-buttons__export-btn',
                        attr: {
                            'data-bs-toggle': 'modal',
                            'data-bs-target': '#exportBankModal',
                        }
                    },
                    {
                        text: `${feather.icons['file-plus'].toSvg({ class: 'font-small-4' })}`,
                        className: 'add-new btn  btn-info btn-icon btn-sm invoice-table-buttons__note-btn noteRemindersModalclass',
                        attr: {
                            // 'data-bs-toggle': 'modal',
                            // 'data-bs-target': '#noteRemindersModal',
                        }
                    },
                ],
            });
            var paymentsDataTable, redrawPaymentsTable = (paging = false) => paymentsDataTable && paymentsDataTable
                .draw(paging);

            const paymentDTConfig = (invoiceId) => ({
                serverSide: true,
                processing: true,
                searching: false,
                bLengthChange: false,
                pagingType: 'numbers',
                drawCallback: function(settings) {
                    var json = this.api().ajax.json();

                    if(json?.previous_payment_source != null){
                        $('#addPaymentModal').find('#payment_source_id').val(json?.previous_payment_source);
                    }else{
                        $(document).on('shown.bs.modal', '#addPaymentModal', function(e) {
                            if(!$('#addPaymentModal').find('#payment_source_id').val()){
                                $(this).find('#payment_source_id').select2('focus');
                                $(this).find('#payment_source_id').select2('open');
                            }
                        });
                    }
                    $('#addPaymentModal').find('#payment_source_id').select2({
                        containerCssClass: 'select-sm',
                        dropdownParent: $('#addPaymentModal').get(0),
                    });

                    if (json?.due_amount == '0.00') {
                        $('#addPaymentSubmitBtn').prop('disabled', true);
                    }
                    $('#addPaymentModal').find('.due_amount').text(json?.due_amount);
                    $('#addPaymentModal').find('#amount').val(json?.due_amount ?? $('#addPaymentModal')
                        .find('#amount').val());
                    if (json?.has_sales_person == false) {
                        $('#notify_sales_person')
                            .prop('checked', false)
                            .data('sales-disabled', true);
                    } else {
                        $('#notify_sales_person')
                            .prop('checked', true)
                            .data('sales-disabled', false);
                    }
                },
                ajax: {
                    url: route('invoices.payments.last-5-payments', invoiceId),
                },
                order: [
                    [1, 'desc']
                ],
                columns: [{
                        data: 'reference',
                    }, {
                        data: 'paid_at',
                    }, {
                        data: 'payment_source.title',
                        data: 'payment_source_title',
                    },
                    {
                        data: 'amount',
                        className: 'text-end'
                    },{
                        data: null,
                        searchable: false,
                        orderable: false,
                        "render": function(data, type, full) {
                                let buttons = '';
                                let payment_edit_btn =
                                    `<a class="btn btn-sm btn-icon btn-flat-info payment_edit_model" title="Edit"
                                        data-id="${full['id']}" data-invoice_id="${full?.invoice.encrypted_id}" data-paid_at="${full['paid_at']}" data-reference="${full['reference']}" data-payment_source_id="${full['payment_source_id']}" href="#">
                                        ${feather.icons['edit'].toSvg({ class: 'font-medium-3' })}</a>`;

                            let payment_delete_btn =
                                `<a class="btn btn-sm btn-icon btn-flat-danger payment_delete" title="Delete" data-id="${full['id']}" data-invoice_id="${full?.invoice.encrypted_id}" href="#">
                                        ${feather.icons['trash'].toSvg({ class: 'font-medium-3' })}</a>`;


                            buttons = `<div
                                    class="d-flex invoice-list__action_buttons"
                                    role="group"
                                    aria-label="Basic mixed styles example"
                                    >
                                ${payment_edit_btn}
                                ${payment_delete_btn}
                                </div>`;

                        return (buttons);
                        },
                    },
                ],
                dom: '<"d-flex justify-content-between align-items-center header-actions mx-2 row mt-75"r' +
                    '<"col-sm-12 col-lg-4 d-flex justify-content-center justify-content-lg-start" l>' +
                    '<"col-sm-12 col-lg-8 ps-xl-75 ps-0"<"dt-action-buttons d-flex align-items-center justify-content-center justify-content-lg-end flex-lg-nowrap flex-wrap"B<"me-1"f>>>' +
                    '>t' +
                    '<"d-flex justify-content-between row mb-1"' +
                    '<"col-sm-12 col-md-6"i>' +
                    '<"col-sm-12 col-md-6"p>' +
                    '>',
                buttons: [],
            });

            $('#invoicePreviewModal').on('show.bs.modal', function(e) {
                $('#invoicePreviewIFrame').attr('src', '')
                if ($(e.relatedTarget).data('invoice-type') && $(e.relatedTarget).data('invoice-type') ==
                    invoiceTypes?.CREDIT_NOTE) {
                    $('#invoicePreviewIFrame').attr('src', route('credit_notes.preview', $(e.relatedTarget)
                        .data('invoice-id')) + `?v=${pdfVersion}`)
                    return;
                }
                $('#invoicePreviewIFrame').attr('src', route('invoices.preview', $(e.relatedTarget).data(
                    'invoice-id')) + `?v=${pdfVersion}`)
            });

            $('#addPaymentModal').on('show.bs.modal', function(e) {
                $.ajax({
                    url: '{{ route("check_session") }}',
                    type: 'GET',
                    success: function(response) {
                        if (response.status === 'authenticated') {
                            let {
                                invoiceId,
                                status,
                                salesPersonName
                            } = $(e.relatedTarget).data();

                            if (status === 'paid') {
                                $('#addPaymentSubmitBtn').prop('disabled', true);
                            }
                            $('#addPaymentForm').find('[name=invoice_id]').val(invoiceId);
                            $('#addPaymentForm').find('#salesPersonName').text(salesPersonName);
                            paymentsDataTable = $('#paymentsTable').DataTable(paymentDTConfig(invoiceId));
                        }
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        if (xhr.status === 401) {
                            // Redirect to the login page if unauthorized
                            window.location.href = "{{ route('login') }}";
                        } else {
                            // Handle other errors
                            toastr.error('Failed to check session status. Please try again.');
                        }
                    }
                });
            });

            $('#addPaymentModal').on('hide.bs.modal', function(e) {
                $('#addPaymentForm').find('[name=invoice_id]').val('');
                if ($(this).find('#salesPersonName').text() != '') {
                    $(this).find('#salesPersonName').text('');
                }

                if ($.fn.DataTable.isDataTable('#paymentsTable')) {
                    $('#paymentsTable').DataTable().destroy();
                }
                $('#paymentsTable tbody').empty();
                addPaymentFormValidator.resetForm();
                if ($('#addPaymentSubmitBtn').prop('disabled')) {
                    $('#addPaymentSubmitBtn').prop('disabled', false);
                }
                $('#addPaymentForm').get(0).reset();
                $('#addPaymentForm').find('#payment_source_id').trigger('change');
            });

            var addPaymentFormValidator = $('#addPaymentForm').validate({
                rules: {
                    amount: {
                        required: true,
                        number: true,
                        min: 0.01
                    },
                    paid_at: {
                        required: true,
                        validDate: 'DD/MM/YYYY'
                    },
                    payment_source_id: {
                        required: false,
                    },
                },
                messages: {
                    amount: {
                        required: "Please enter amount",
                        number: "Please enter only number in amount",
                        min: "Please enter minimum {0} in amount",
                    },
                    paid_at: {
                        required: "Please enter paid time",
                        validDate: "Please enter valid paid date"
                    },
                },
                errorClass: 'error',
                submitHandler: function(form, event) {
                    event.preventDefault();
                    $('#addPaymentSubmitBtn').prop('disabled', true);
                    $('#addPaymentForm').block({
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
                            url: route('invoices.payments.store', $('#addPaymentForm').find(
                                '[name=invoice_id]').val()),
                            method: 'POST',
                            data: $(form).serialize(),
                            error: function(xhr, textStatus, errorThrown) {
                                if (xhr.status === 401) {
                                    // Redirect to the login page if unauthorized
                                    window.location.href = "{{ route('login') }}";
                                } else {
                                    // Handle other errors
                                    toastr.error(null,'Session hase been expire!');
                                }
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
                            },
                            success: function(response, status, xhr) {
                                if (response.errors) {
                                    $(form).validate().showErrors(response.errors);
                                } else {
                                    toastr.success(null, "Payment added successfully!");
                                    $('#addPaymentModal').modal('hide');
                                }
                            },
                            error: function(xhr, status, error) {
                                if (xhr.status == 422) {
                                    $(form).validate().showErrors(JSON.parse(xhr?.responseText)
                                        ?.errors);
                                } else {
                                    Swal.fire({
                                        title: 'An error occurred',
                                        text: error,
                                        icon: 'error',
                                    });
                                }
                            }
                        })
                        .always(function(xhr, status, error) {
                            $('#addPaymentSubmitBtn').prop('disabled', false);
                            $('#addPaymentForm').unblock();
                            redrawPaymentsTable();
                            redrawInvoiceTable();
                            updateSalesStatistics();
                        })
                },
            });

            $('#filter_created_at').select2({
                containerCssClass: 'select-sm',
                dropdownCssClass: 'select2-long-dropdown',
            });

            $(document).on('change', '#filter_created_at', function(e) {
                if ($(e.target).val() === 'custom') {
                    filterCreatedAtRangeFormGroup.removeClass('d-none');
                } else {
                    filterCreatedAtRangeFormGroup.addClass('d-none');
                }
                redrawInvoiceTable();
            });

            /*$('#payment_source_id').select2({
                containerCssClass: 'select-sm',
                dropdownParent: $('#addPaymentModal').get(0),
            });*/

            $('#notify_sales_person').on('change', function(e) {
                if ($(this).data('sales-disabled')) {
                    $(this).prop('checked', false);
                    Swal.fire({
                        title: 'No sales person is assigned to selected Invoice',
                        text: "Please assign a sales person first then try again",
                        icon: 'warning',
                    })
                }
            });

            function updateSalesStatistics() {
                $.ajax({
                    url: route('invoices.sales-statistics'),
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
                    },
                    success: function(response, status, xhr) {
                        if (response.errors) {
                            $(form).validate().showErrors(response.errors);
                        } else {
                            let symbol = response?.['currency_symbol'] ?? '£';
                            Object.keys(response).forEach(key => {
                                let amount = response?.[key] ?? 0;
                                amount = parseFloat(amount) ?? amount;
                                amount = amount?.toLocaleString(
                                    'us', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2,
                                    }) ?? amount;

                                $(`#${key}`).text(symbol + amount)
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status == 422) {
                            $(form).validate().showErrors(JSON.parse(xhr?.responseText)
                                ?.errors);
                        } else {
                            Swal.fire({
                                title: 'An error occurred while fetching sales statistics',
                                text: error,
                                icon: 'error',
                            });
                        }
                    }
                })
            }

            updateSalesStatistics();

            /*$(document).on('shown.bs.modal', '#addPaymentModal', function(e) {
                $(this).find('#payment_source_id').select2('focus');
                $(this).find('#payment_source_id').select2('open');
            });*/

            $(document).on('click', '.invoiceCancelledBtn', function(e) {
                e.preventDefault();
                let {
                    id,
                } = $(this).data();
                Swal.fire({
                    title: 'Are you sure you want to cancel this invoice?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-outline-danger ms-1'
                    },
                    buttonsStyling: false
                }).then(function(result) {
                    if (result.value) {
                        $.ajax({
                            url: route('invoices.cancelled', id),
                            method: 'DELETE',
                            data: {
                                _token: $('meta[name=csrf-token]').attr('content')
                            },
                            success: function(response) {
                                toastr.success(null,
                                    "Invoice Cancelled successfully!");
                                redrawInvoiceTable();
                            },
                            error: function(xhr, status, error) {
                                toastr.error(xhr.responseJSON?.message ?? null, error);
                                redrawInvoiceTable();
                            }
                        });
                    }
                });
            });

            $('#exportModal').on('hide.bs.modal', function(e) {
                $('ul#appliedExportFilters').empty();
                $('#exportHiddenInputs input').each(function(i) {
                    $(this).val('');
                });
            });
            
            $('#exportModal').on('show.bs.modal', function(e) {
                let appliedFilters = [];
                let filter_created_at = $('#filter_created_at').find(':selected');
                if (filter_created_at) {
                    appliedFilters.push({
                        name: 'Invoice date',
                        value: filter_created_at.text(),
                    });
                    $('#exportHiddenInputs input[name=filter_created_at]').val($('#filter_created_at')
                        .val());
                }
                if (filter_created_at.val() === 'custom') {
                    let filter_created_at_range = filter_created_at_range_picker?.selectedDates;
                    if (filter_created_at_range?.length) {
                        let rangeArr = filter_created_at_range.map(function(selectedDate) {
                            return flatpickr.formatDate(selectedDate, 'd/m/Y');
                        });
                        appliedFilters.push({
                            name: 'Invoice date range',
                            value: rangeArr.join(' to '),
                        });
                        $('#exportHiddenInputs input[name=filter_created_at_range]').val($(
                            '#filter_created_at_range').val());
                    }
                }
                let filter_payment = $('#filter_payment').find(":selected");
                let paymentArr = [];
                filter_payment.each(function(i) {
                    paymentArr.push($(this).text());
                });
                appliedFilters.push({
                    name: 'Payment status',
                    value: paymentArr?.length ? paymentArr.join(', ') : "All",
                });
                $('#exportHiddenInputs input[name="filter_payment"]').val($('#filter_payment').val());
                let filter_payment_source_id = $filterPaymentSource.find(":selected");
                if (filter_payment_source_id?.length) {
                    appliedFilters.push({
                        name: 'Payment source',
                        value: filter_payment_source_id.text(),
                    });
                    $('#exportHiddenInputs input[name="filter_payment_source_id"]').val($(
                        '#filter_payment_source_id').val());
                }
                let filter_client_id = $filterClient.find(":selected");
                if (filter_client_id?.length) {
                    appliedFilters.push({
                        name: 'Customer',
                        value: filter_client_id.text(),
                    });
                    $('#exportHiddenInputs input[name="filter_client_id"]').val($('#filter_client_id')
                        .val());
                }
                let filter_company_id = $filterCompany.find(":selected");
                if (filter_company_id?.length) {
                    appliedFilters.push({
                        name: 'Company',
                        value: filter_company_id.text(),
                    });
                    $('#exportHiddenInputs input[name="filter_company_id"]').val($('#filter_company_id')
                        .val());
                }
                if (appliedFilters.length) {
                    appliedFilters.forEach(filter => {
                        let li = '<li class="d-flex justify-content-between py-50">' +
                            '<strong>' + filter['name'] + ':</strong>' +
                            '<span class="text-end">' + filter['value'] + '</span>' +
                            '</li>';
                        $('ul#appliedExportFilters').append(li);
                    });
                }
            });
            $('#exportSubmitBtn').click(function(e) {
                setTimeout(() => {
                    $('#exportModal').modal('hide');
                }, 1000);
            });


            // export Bank Modal : New Export

            $('#exportBankModal').on('hide.bs.modal', function(e) {
                $('ul#appliedExportFilters').empty();
                $('#exportHiddenInputs input').each(function(i) {
                    $(this).val('');
                });
            });
            
            $('#exportBankModal').on('show.bs.modal', function(e) {
                let appliedFilters = [];
                let filter_created_at = $('#filter_created_at').find(':selected');
                if (filter_created_at) {
                    appliedFilters.push({
                        name: 'Invoice date',
                        value: filter_created_at.text(),
                    });
                    $('#exportHiddenInputs input[name=filter_created_at]').val($('#filter_created_at')
                        .val());
                }
                if (filter_created_at.val() === 'custom') {
                    let filter_created_at_range = filter_created_at_range_picker?.selectedDates;
                    if (filter_created_at_range?.length) {
                        let rangeArr = filter_created_at_range.map(function(selectedDate) {
                            return flatpickr.formatDate(selectedDate, 'd/m/Y');
                        });
                        appliedFilters.push({
                            name: 'Invoice date range',
                            value: rangeArr.join(' to '),
                        });
                        $('#exportHiddenInputs input[name=filter_created_at_range]').val($(
                            '#filter_created_at_range').val());
                    }
                }
                let filter_payment = $('#filter_payment').find(":selected");
                let paymentArr = [];
                filter_payment.each(function(i) {
                    paymentArr.push($(this).text());
                });
                appliedFilters.push({
                    name: 'Payment status',
                    value: paymentArr?.length ? paymentArr.join(', ') : "All",
                });
                $('#exportHiddenInputs input[name="filter_payment"]').val($('#filter_payment').val());
                let filter_payment_source_id = $filterPaymentSource.find(":selected");
                if (filter_payment_source_id?.length) {
                    appliedFilters.push({
                        name: 'Payment source',
                        value: filter_payment_source_id.text(),
                    });
                    $('#exportHiddenInputs input[name="filter_payment_source_id"]').val($(
                        '#filter_payment_source_id').val());
                }
                let filter_client_id = $filterClient.find(":selected");
                if (filter_client_id?.length) {
                    appliedFilters.push({
                        name: 'Customer',
                        value: filter_client_id.text(),
                    });
                    $('#exportHiddenInputs input[name="filter_client_id"]').val($('#filter_client_id')
                        .val());
                }
                let filter_company_id = $filterCompany.find(":selected");
                if (filter_company_id?.length) {
                    appliedFilters.push({
                        name: 'Company',
                        value: filter_company_id.text(),
                    });
                    $('#exportHiddenInputs input[name="filter_company_id"]').val($('#filter_company_id')
                        .val());
                }
                if (appliedFilters.length) {
                    appliedFilters.forEach(filter => {
                        let li = '<li class="d-flex justify-content-between py-50">' +
                            '<strong>' + filter['name'] + ':</strong>' +
                            '<span class="text-end">' + filter['value'] + '</span>' +
                            '</li>';
                        $('ul#appliedExportFilters').append(li);
                    });
                }
            });
            $('#exportBankSubmitBtn').click(function(e) {
                setTimeout(() => {
                    $('#exportBankModal').modal('hide');
                }, 1000);
            });

            $('#paymentReceiptExportSubmitBtn').click(function(e) {
                setTimeout(() => {
                    $('#paymentReceiptExportModal').modal('hide');
                    $('ul#appliedReceiptExportFilters').empty();
                    $('#paymentReceiptExportHiddenInputs input').each(function(i) {
                        $(this).val('');
                    });
                }, 1000);
            });


            $(document).on('click','#paymentReceiptClose',function (){
                $('ul#appliedReceiptExportFilters').empty();
                $('#paymentReceiptExportHiddenInputs input').each(function(i) {
                    $(this).val('');
                });
            });

            $("#exportForm").validate({
                rules: {
                    from_to_date_range: {
                        required: true,
                    }
                },
                messages: {
                    from_to_date_range: {
                        required: "Please select date !",
                    },
                },
            });

            $(document).on('click','#payment_receipt_export_Confirm',function (){
                var dateRangeString = $('#from_to_date_range').val();
                var dateParts = dateRangeString.split(" to ");
                var export_from_date = dateParts[0];
                var export_to_date = dateParts[1];
                if($("#exportForm").valid()){
                    let appliedReceiptFilters = [];
                    if (export_from_date?.length) {
                        appliedReceiptFilters.push({
                            name: 'From date',
                            value: export_from_date,
                        });
                        $('#paymentReceiptExportHiddenInputs input[name=export_from_date]').val(export_from_date);
                    }
                    if (export_to_date?.length) {
                        appliedReceiptFilters.push({
                            name: 'To date',
                            value: export_to_date,
                        });
                        $('#paymentReceiptExportHiddenInputs input[name=export_to_date]').val(export_to_date);
                    }
                    let export_payment_source_id = $exportPaymentSource.find(":selected");
                    if (export_payment_source_id?.length) {
                        appliedReceiptFilters.push({
                            name: 'Payment source',
                            value: export_payment_source_id.text(),
                        });
                        $('#paymentReceiptExportHiddenInputs input[name="export_payment_source_id"]').val($(
                            '#export_payment_source_id').val());
                    }
                    let export_client_id = $exportClientId.find(":selected");
                    if (export_client_id?.length) {
                        appliedReceiptFilters.push({
                            name: 'Customer',
                            value: export_client_id.text(),
                        });
                        $('#paymentReceiptExportHiddenInputs input[name="export_client_id"]').val($(
                            '#export_client_id').val());
                    }
                    let export_company_id = $exportCompanyId.find(":selected");
                    if (export_company_id?.length) {
                        appliedReceiptFilters.push({
                            name: 'Company',
                            value: export_company_id.text(),
                        });
                        $('#paymentReceiptExportHiddenInputs input[name="export_company_id"]').val($(
                            '#export_company_id').val());
                    }

                    if (appliedReceiptFilters.length) {
                        appliedReceiptFilters.forEach(Receiptfilter => {
                            let li = '<li class="d-flex justify-content-between py-50">' +
                                '<strong>' + Receiptfilter['name'] + ':</strong>' +
                                '<span class="text-end">' + Receiptfilter['value'] + '</span>' +
                                '</li>';
                            $('ul#appliedReceiptExportFilters').append(li);
                        });
                    }
                    $('#paymentReceiptExportModal').modal('show');
                }
            });
        });
    </script>
@endsection
