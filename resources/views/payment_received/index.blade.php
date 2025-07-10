@extends('layouts.app')
@section('page-css')
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/css/plugins/forms/form-quill-editor.css?v=' . config('versions.css')) }}">
@endsection

@section('vendor-css')
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/vendors/css/editors/quill/quill.snow.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/vendors/css/editors/quill/quill.bubble.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet"
        href="{{ asset('app-assets/vendors/bootstrap-datepicker/css/bootstrap-datepicker.min.css?v=' . config('versions.css')) }}">
@endsection

@section('custom-css')
    <link rel="stylesheet" href="{{ asset('app-assets/css/custom/bootstrap-datepicker.css?v=' . config('versions.css')) }}">
@endsection

@section('content')
    <div class="row align-items-start px-0 mb-1" id="PaymentFilters">
        <div class=" col-xl-2 col-lg-4 col-md-5 col-sm-7 form-group">
        <label for="is_invoice_link_to_crm" class="form-label">Filter By Payment Linked/Not Linked</label>
        <select id="is_invoice_link_to_crm" class="form-select form-select-sm select2">
            <option value="">All</option>
            <option value="1">Linked</option>
            <option value="0" selected>Not Linked</option>
        </select>
        </div>
    </div>
    <section class="invoices_list">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <table class="payment-table table">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice Number</th>
                            <th>Customer Name</th>
                            <th>Customer Email</th>
                            <th>Payment Source</th>
                            <th>Amount Received</th>
                            <th>Currency</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        <div class="modal fade" id="InvoiceLinkModal" tabindex="-1" aria-labelledby="InvoiceLinkModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable mx-auto" style="max-width: 450px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Payment Linked to Invoice</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="InvoiceLinkForm" class="form form-horizontal">
                            <input type="hidden" id="payment_received_id" name="payment_received_id" value="">
                            <div class="row">
                                <div class="col-12 mb-1">
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <label for="invoice_info" class="col-form-label">Invoice<span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-9">
                                            <select name="invoice_info" id="invoice_info" class="form-select form-select-sm">
                                                <option value="">Select Invoice</option>

                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-9 offset-sm-3">
                                    <button type="submit" class="btn btn-primary btn-sm" id="InvoiceLinkSubmitBtn">Submit</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


    </section>
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
        $(document).ready(function () {

            const paymentDataTable = $('.payment-table').DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: "{{ route('payment_detail_index') }}",
                    data: function (d) {
                        d.is_invoice_link_to_crm = $('#is_invoice_link_to_crm').val();
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
                    [6, 'desc']
                ],
                drawCallback: function(settings) {},
                columns: [
                    { data: 'invoice_number', name: 'invoice_number' },
                    { data: 'customer_name', name: 'customer_name' },
                    { data: 'customer_email', name: 'customer_email' },
                    { data: 'payment_source', name: 'payment_source' },
                    { data: 'amount_received', name: 'amount_received' },
                    { data: 'currency', name: 'currency' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                   ],
                dom: '<"d-flex justify-content-between align-items-center header-actions mx-1 row mt-50"r' +
                    '<"col-sm-12 col-lg-4 d-flex justify-content-center justify-content-lg-start" l>' +
                    '<"col-sm-12 col-lg-8 ps-xl-75 ps-0"<"dt-action-buttons d-flex align-items-center justify-content-center justify-content-lg-end flex-lg-nowrap flex-wrap"Bf>>' +
                    '>t' +
                    '<"d-flex justify-content-between mx-2 row mb-1"' +
                    '<"col-sm-12 col-md-6"i>' +
                    '<"col-sm-12 col-md-6"p>' +
                    '>',
                initComplete: function(settings, json) {
                    $('.invoices_list div.dataTables_length select').addClass('form-select-sm');                    
                },
                buttons: [
                ],
            });

            // Define redraw after table is initialized
            const redrawPaymentTable = (paging = false) => paymentDataTable.draw(paging);

            // Trigger on change
            $('#is_invoice_link_to_crm').on('change', function () {
                redrawPaymentTable();
            });

            $('#is_invoice_link_to_crm').on('change', function () {
                redrawPaymentTable();
            });
            

            $('#is_invoice_link_to_crm').select2({
                containerCssClass: 'select-sm',
            });

            $('#invoice_info').select2({
                containerCssClass: 'select-sm',
            });

            


            // Reinitialize feather icons on every draw
            paymentDataTable.on('draw', function () {
                feather.replace();
            });

            $(document).on('click', '.invoice-link-icon', function () {
                const encryptedId = $(this).data('id');
                // Clear old options
                $('#invoice_info').html('<option value="">Loading...</option>');
                // Open the modal
                $('#InvoiceLinkModal').modal('show');
                // Fetch invoices via AJAX
                $.ajax({
                    url: "{{ route('payment_invoices_dropdown') }}", // You'll create this route
                    type: 'GET',
                    data: { id: encryptedId },
                    success: function (response) {
                        $('#payment_received_id').val(encryptedId);
                        let options = '<option value="">Select Invoice</option>';
                        $.each(response.invoices, function (index, invoice) {
                            const clientName = invoice.client ? invoice.client.name : '';
                            options += `<option value="${invoice.id}">${invoice.invoice_number} - ${clientName}</option>`;
                        });
                        $('#invoice_info').html(options);
                    },
                    error: function () {
                        $('#invoice_info').html('<option value="">Failed to load</option>');
                        toastr.error("Failed to load invoices.");
                    }
                });
            });


            $('#InvoiceLinkForm').validate({
                rules: {
                    invoice_info: {
                        required: true
                    }
                },
                messages: {
                    invoice_info: {
                        required: "Please select an invoice"
                    }
                },
                submitHandler: function (form, event) {
                    event.preventDefault();
                    $('#InvoiceLinkSubmitBtn').prop('disabled', true);
                    $('#InvoiceLinkForm').block({
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
                        url: route('payment_invoices_link'),
                        method: 'POST',
                        headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                        'content')
                                },
                        data: $(form).serialize(),
                        success: function (response) {
                            toastr.success(null, "Invoice Link With Payment detail successfully!");
                            $('#InvoiceLinkModal').modal('hide');
                            $('#InvoiceLinkForm')[0].reset();
                            redrawPaymentTable();
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
                    }).always(function(xhr, status, error) {
                            $('#InvoiceLinkSubmitBtn').prop('disabled', false);
                            $('#InvoiceLinkForm').unblock();
                            redrawPaymentTable();
                        });
                }
            });



        });

    </script>
@endsection