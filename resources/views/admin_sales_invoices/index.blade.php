@extends('layouts.app')

@section('vendor-css')
    <link rel="stylesheet" href="{{ asset('app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" href="{{ asset('app-assets/vendors/bootstrap-datepicker/css/bootstrap-datepicker.min.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" href="{{ asset('app-assets/vendors/css/tables/datatable/dataTables.bootstrap5.min.css') }}">
@endsection

@section('page-css')
    <link rel="stylesheet" href="{{ asset('app-assets/css/plugins/forms/pickers/form-flat-pickr.css?v=' . config('versions.css')) }}">
@endsection

@section('custom-css')
    <style>
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .invoicehighlight {
            background:#C4D9CD;
        }

        .invoiceCancelledhighlight {
            background:#ea5455;
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
        .btn-flat-danger {
            color: #ea5455 !important;
        }
    </style>
@endsection

@section('content')
<div class="row align-items-start px-0 mb-1" id="PaymentFilters">
    <div class=" col-xl-2 col-lg-4 col-md-5 col-sm-7 form-group">
    <label for="is_sales_invoice_status" class="form-label">Filter By Sales invoice Status</label>
    <select id="is_sales_invoice_status" class="form-select form-select-sm select2">
        <option value="">All</option>
        <option value="4" selected>Pending</option>
        <option value="2">Approved</option>
        <option value="3">Rejected</option>
    </select>
    </div>
</div>
<section class="invoices_list">
    <div class="card">
        <div class="card-datatable table-responsive p-1">
            <table class="user-invoices-table table">
                <thead class="table-light">
                    <tr>
                        <th>Sales Invoice Number</th>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Invoice Date</th>
                        <th class="text-end">Grand Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</section>

    @include('admin_sales_invoices.modals.restriction-modal')

    @include('invoices.modals.create-new-invoice-modal')


@endsection

@section('page-vendor-js')
    <script src="{{ asset('app-assets/vendors/js/extensions/moment.min.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/vendors/bootstrap-datepicker/js/bootstrap-datepicker.min.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('app-assets/vendors/js/tables/datatable/dataTables.bootstrap5.min.js') }}"></script>
@endsection

@section('page-js')
<script>
    const currentRoute = @json($currentRoute);
    const previousRoute = @json($previousRoute);
    const allowedRoutes = @json($invoiceRoutes);
</script>
<script>
    $(document).ready(function () {
        $('.flatpickr').flatpickr();

        if (allowedRoutes.includes(currentRoute) && !allowedRoutes.includes(previousRoute)) {
            sessionStorage.removeItem('sales_invoice_status_filter');
        }

        const savedStatus = sessionStorage.getItem('sales_invoice_status_filter');
        if (savedStatus !== null) {
            $('#is_sales_invoice_status').val(savedStatus).trigger('change'); // trigger change for reload if needed
        }

        const table = $('.user-invoices-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('sales_invoice_index') }}",  
                data: function (d) {
                    d.is_sales_invoice_status = $('#is_sales_invoice_status').val();
                }
            },
            order: [[0, 'desc']],
            initComplete: function(settings, json) {
                $('.invoice-table__buttons').tooltip({
                    title: "Sales Create Invoice",
                    placement: "bottom",
                });
            },
            columns: [
                {
                    data: 'sales_invoice_number',
                    name: 'sales_invoice_number',
                    render: function (data, type, full) {
                        const typeLabel = full.subscription_type == '0'
                            ? '(M)'
                            : full.subscription_type == '1'
                            ? '(Y)'
                            : '';

                        const salesInvoiceNumber = `<div class="text-primary fw-bold">${data} ${typeLabel}</div>`;
                        const invoiceNumber = full.invoice_number
                            ? `<div class="fw-bold fs-6 text-dark">#${full.invoice_number}</div>`
                            : '';

                        return `${salesInvoiceNumber}${invoiceNumber}`;
                    }
                },
                { data: 'type', name: 'type' },
                { data: 'name', name: 'name' },
                { data: 'company', name: 'company' },
                { data: 'invoice_date', name: 'invoice_date' },
                {
                    data: 'grand_total',
                    name: 'grand_total',
                    className: "text-end",
                    render: function (data, type, full) {
                        const currencySymbol = full.currency?.symbol ?? '';
                        const amount = typeof data === 'string' ? Number(data.replace(/,/g, '')) : Number(data);

                        if (isNaN(amount)) return `${currencySymbol}0.00`;

                        // Format number with commas and 2 decimals
                        const formatted = new Intl.NumberFormat('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }).format(amount);

                        return `${currencySymbol}${formatted}`;
                    }
                },
                {
                    data: 'status',
                    name: 'status',
                    render: function (data, type, full) {
                        let status = data?.toLowerCase?.() || '4';

                        switch (status) {
                            case '1':
                                return '<span class="badge rounded-pill custom-badge-pending custom-badge-line">Pending</span>';
                            case '2':
                                return '<span class="badge bg-gradient bg-success rounded-pill custom-badge-line">Approved</span>';
                            case '3':
                                return '<span class="badge bg-gradient bg-danger rounded-pill custom-badge-line">Rejected</span>';
                            case '4':
                                return '<span class="badge rounded-pill custom-badge-pending custom-badge-line">Pending</span>';
                            case 'deleted':
                                return '<span class="badge bg-gradient bg-danger rounded-pill custom-badge-line">Deleted</span>';
                            default:
                                return `<span class="badge bg-gradient bg-secondary rounded-pill custom-badge-line">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
                        }
                    }
                },
                {
                    data: 'actions',
                    name: 'actions',
                   render: function (data, type, full, meta) {
                        const status = full.status;
                        const restrictedStatuses = ['2', '3'];
                        const statusMessages = {
                            '2': 'Action not allowed for approved invoice.',
                            '3': 'Action not allowed for rejected invoice.',
                        };

                        const isRestricted = restrictedStatuses.includes(status);
                        const tooltipMessage = isRestricted ? statusMessages[status] : '';

                        return `
                            <a class="btn btn-sm btn-icon btn-flat-primary ${isRestricted ? 'restricted-btn' : ''}" 
                                title="${isRestricted ? tooltipMessage : 'Create Invoice'}"
                                ${isRestricted ? `data-restrict-msg="${tooltipMessage}" href="javascript:void(0);"` : `href="${route('sales_invoice_create', { sales_invoice: full.encrypted_id })}"`}
                                data-bs-toggle="tooltip">
                                    ${feather.icons['plus'].toSvg({ class: 'font-medium-3' })}
                            </a>
                            <a class="btn btn-sm btn-icon btn-flat-secondary" 
                                title="Detail" 
                                href="${route('sales_invoice_show', { sales_invoice: full.encrypted_id })}" 
                                data-bs-toggle="tooltip">
                                    ${feather.icons['eye'].toSvg({ class: 'font-medium-3' })}
                            </a>
                            <a class="btn btn-sm btn-icon btn-flat-danger ${isRestricted ? 'restricted-btn' : 'payment_delete'}" 
                                title="${isRestricted ? tooltipMessage : 'Delete'}" 
                                ${isRestricted ? `data-restrict-msg="${tooltipMessage}" href="javascript:void(0);"` : ''}
                                data-id="${full.id}" data-invoice_id="${full.encrypted_id}" 
                                data-bs-toggle="tooltip">
                                    ${feather.icons['trash'].toSvg({ class: 'font-medium-3' })}
                            </a>
                        `;
                    },
                    orderable: false,
                    searchable: false,
                }
            ],
        });

        $('#is_sales_invoice_status').on('change', function () {
            const selectedStatus = $(this).val();
            sessionStorage.setItem('sales_invoice_status_filter', selectedStatus);
            table.ajax.reload();
        });

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




        $(document).on('click', '.payment_delete', function(e) {
            e.preventDefault();
            const id = $(this).data('invoice_id');
            Swal.fire({
                title: 'Are you sure you want to delete this invoice?',
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
                        url: route('sales_invoice_destroy', id),
                        method: 'DELETE',
                        data: {
                            _token: $('meta[name=csrf-token]').attr('content')
                        },
                        success: function(response) {
                            toastr.success(null,
                                "Invoice Deleted successfully!");
                            $('.user-invoices-table').DataTable().ajax.reload(null, false); // reload without reset
                        },
                        error: function(xhr, status, error) {
                            toastr.error(xhr.responseJSON?.message ?? null, error);
                            $('.user-invoices-table').DataTable().ajax.reload(null, false); // reload without reset
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
