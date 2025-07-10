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
    <link rel="stylesheet"
        href="{{ asset('app-assets/css/custom/bootstrap-datepicker.css?v=' . config('versions.css')) }}">
@endsection

@section('content')
    <section class="invoices_list">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <table class="clients-table table">
                    <thead class="table-light">
                        <tr>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>City</th>
                            <th>Post Code</th>
                            <th>Country</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>
    @include('clients.modals.create')
    @include('clients.modals.edit')

    <input type="hidden" id="remindervalue" name="remindervalue">
    <input type="hidden" id="clientid" name="clientid">
   <!-- Client invoices Payment Reminder enable Modal -->
    <div class="modal fade" id="payment_reminder_enable_model" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Invoice Payment Reminder</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light">
                    <div class="row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-sm-12 text-center">
                                    <i class="alt_circle" data-feather="alert-circle"></i>
                                    <h4> Are you sure? </h4>
                                    <label for="enable_invoice" class="invoice_message">  </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 text-center">
                            <button type="submit" class="btn btn-primary me-1 waves-effect waves-float waves-light" id="payment_reminder">Yes</button>
                            <button type="button" class="btn btn-outline-secondary" id="payment_reminder_enable_close">cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Client project list Modal -->
    <div class="modal fade" id="customer_project_list_model" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="customer_name"></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light">
                    <form id="projectSelectionForm">
                        <div class="row">
                            <div class="col-12">
                                <div class="row">
                                    <input type="hidden" id="customer_id" name="customer_id">
                                    <div class="col-12 mb-1">
                                        <div class="row">
                                            <div class="col-sm-2">
                                                <label for="customerProjects" class="col-form-label">Projects
                                                    {{-- <span class="text-danger">*</span> --}}
                                                </label>
                                            </div>
                                            <div class="col-sm-10">
                                                <select id="customerProjects" name="customerProjects[]" multiple="multiple" class="form-control"></select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 text-center">
                                <button type="button" class="btn btn-primary me-1 waves-effect waves-float waves-light" id="saveProjects">Link Projects</button>
                                <button type="button" class="btn btn-outline-secondary" id="customer_project_list_model_close">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer review mail send -->
    <div class="modal fade text-start" id="reviewSendMailModal" tabindex="-1" aria-labelledby="reviewSendMailModalLabel" aria-hidden="true">
        <div class="modal-dialog review_modal_lg modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form id="reviewSendMailForm" method="POST" action="#">
                    @csrf
                    <input type="hidden" id="feedback_token" name="feedback_token" value="">
                    <div class="modal-header">
                        <h4 class="modal-title" id="reviewSendMailModalLabel">Review Send Mail</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group" style="text-align: end;">
                            <h5 for="send_mail_count">Send Mail Count : <span class="text-danger send_mail_count"></span></h5>
                        </div>
                        <div class="form-group">
                            <label for="to" class="form-label">To <span class="text-danger">*</span></label>
                            <select name="to[]" id="to" class="form-select select2" multiple='multiple'></select>
                        </div>
                        <div class="form-group">
                            <label for="bcc" class="form-label">BCC</label>
                            <select name="bcc[]" id="bcc" class="form-select select2" multiple='multiple'></select>
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
                        <button type="submit" class="btn btn-primary" id="reviewSendMailSubmitBtn">Send Mail</button>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Customer Feedback Modal -->
    <div class="modal fade" id="feedbackModal" tabindex="-1" aria-hidden="true" aria-labelledby="feedbackModalLabel" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog csm_modal_lg modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="feedbackModalLabel">Customer Feedback</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light">
                    <!-- Accordion will be dynamically inserted here -->
                    <div class="accordion" id="feedbackAccordion"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true" aria-labelledby="messageModalLabel" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-sm modal-dialog-centered message-custom-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel">Feedback Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalMessageContent">
                    <!-- Message will be injected here -->
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade text-start" id="exportClientModal" tabindex="-1" aria-labelledby="exportClientModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <form id="exportClientForm" method="POST" action="{{ route('clients.export-filtered') }}">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title" id="exportClientModalLabel">Export Clients</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Created at</label>
                            <div class="input-group input-daterange2">
                                <input type="text" class="form-control"
                                    value="{{ date('d/m/Y', strtotime('first day of this month')) }}"
                                    name="export_created_at_start" id="export_created_at_start" readonly>
                                <div class="input-group-addon mx-1 my-auto">to</div>
                                <input type="text" class="form-control" value="{{ date('d/m/Y') }}"
                                    name="export_created_at_end" id="export_created_at_end" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="exportClientSubmitBtn">Export</button>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



@endsection


@section('page-vendor-js')
    <script src="{{ asset('app-assets/vendors/js/editors/quill/quill.min.js?v=' . config('versions.js')) }}"></script>
    <script
        src="{{ asset('app-assets/vendors/bootstrap-datepicker/js/bootstrap-datepicker.min.js?v=' . config('versions.js')) }}">
    </script>
@endsection
@section('custom-js')
    <script>

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
            var pathUrl = "{{ config('custom.hrms_customer_project.hrms_url') }}";
            var auth_token = "{{ config('custom.hrms_customer_project.auth_token') }}";

            const customerProjectModal = new bootstrap.Modal(document.getElementById('customer_project_list_model'), {
                backdrop: 'static'
            });

            $.fn.dataTableExt.oStdClasses.sFilter = "dataTables_filter dataTables_filter_sm";

            // Phone Number
            $(document).on('input', '#phone', function() {
                let inputVal = this.value;
                let regex = /^(\+)?([0-9]+(\s)?)+$/;
                if (!regex.test(inputVal)) {
                    let cleanedVal = inputVal.replace(/[^0-9+\s]/g, '');
                    if (cleanedVal.startsWith('+')) {
                        cleanedVal = '+' + cleanedVal.replace(/[^\d\s]/g, '');
                    } else {
                        cleanedVal = cleanedVal.replace(/[^\d\s]/g, '');
                    }
                    this.value = cleanedVal.trim();
                }
            });

            // Select2
            $('.select2').each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>');
                $this.select2({
                    dropdownAutoWidth: true,
                    width: '100%',
                    dropdownParent: $this.parent()
                });
            });

            // focus searchbox when select2 dropdown opened
            $(document).on('select2:open', (e) => {
                const selectId = e.target.id
                $(".select2-search__field[aria-controls='select2-" + selectId + "-results']").each(function(
                    key,
                    value,
                ) {
                    value.focus();
                })
            })

            const redrawClientsTable = (paging = false) => clientsDataTable && clientsDataTable.draw(paging);
            const addClientForm = $('#addClientForm');
            const editClientForm = $('#editClientForm');

            const clientsDataTable = $('.clients-table').DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: route('clients.index'),
                    data: function(d) {},
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
                    [5, 'desc']
                ],
                drawCallback: function(settings) {},
                columns: [{
                        data: "name",
                        "render": function(data, type, row) {
                            var $name = row['name'],
                                $is_tree_planted = row['is_tree_planted'],
                                $email = row['email'];

                            // For Avatar badge
                            // var stateNum = Math.floor(Math.random() * 6) + 1;
                            // var states = ['success', 'danger', 'warning', 'info', 'dark',
                            //     'primary', 'secondary'
                            // ];
                            // var $state = states[stateNum],
                            var $state = 'primary',
                                $name = row['name'],
                                $initials = $name.match(/\b\w/g) || [];
                            $initials = (($initials.shift() || '') + ($initials.pop() || ''))
                                .toUpperCase();
                            $output = '<span class="avatar-content">' + $initials + '</span>';
                            var colorClass = ' bg-light-' + $state + ' ';
                            // Creates full output for row
                            var $row_output =
                                '<div class="d-flex justify-content-left align-items-center">' +
                                '<div class="avatar-wrapper">' +
                                '<div class="avatar ' +
                                colorClass +
                                ' me-1">' +
                                $output +
                                '</div>' +
                                '</div>' +
                                '<div class="d-flex flex-column">' +
                                `<a href="${route(row['type'] == '' ? 'clients.show' : 'clients.show', row['encrypted_id'])}" class="user_name text-truncate text-body"><span class="fw-bolder">` +
                                $name +
                                ' </span>' +
                                (
                                    $is_tree_planted ?
                                    '<img src="{{ asset('app-assets/images/png/plant.png') }}" height="20" alt="tree" title="Tree planted" />' :
                                    ''
                                ) +
                                '</a>' +
                                '<small class="emp_post text-muted">' +
                                $email +
                                '</small>' +
                                '</div>' +
                                '</div>';
                            return $row_output;
                        }
                    },
                    {
                        data: 'phone',
                    },
                    {
                        data: 'city',
                    },
                    {
                        data: 'zip_code',
                    },
                    {
                        data: 'country.name'
                    },
                    {
                        data: 'created_at',
                    },
                    {
                        data: null,
                        searchable: false,
                        orderable: false,
                        "render": function(data, type, full) {
                          let customer_detail_btn =
                                    `<a class="btn btn-sm btn-icon btn-flat-secondary" title="Detail" href="${route(full['type'] == '' ? 'clients.show' : 'clients.show', full['encrypted_id'])}" >${feather.icons['eye'].toSvg({ class: 'font-medium-3' })}</a>`;
                            let encId = full['encrypted_id'];
                            let client_edit_btn =
                                `<button data-id="${encId}" class="btn btn-sm btn-icon btn-flat-info clientEditBtn" data-bs-toggle="modal" data-bs-target="#editClientModal" >${feather.icons['edit'].toSvg({ class: 'font-medium-3' })}</button>`;
                            let client_delete_btn =
                                `<button data-id="${encId}" data-invoices-count="${full['invoices_count']}" class="btn btn-sm btn-icon btn-flat-danger clientDeleteBtn" >${feather.icons['trash'].toSvg({ class: 'font-medium-3' })}</button>`;
                            let client_restore_btn =
                                `<button data-id="${encId}" class="btn btn-sm btn-icon btn-flat-primary clientRestoreBtn" >${feather.icons['refresh-cw'].toSvg({ class: 'font-medium-3' })}</button>`;
                            let client_project_btn;
                                if(full['project_list'].length != 0){
                                    client_project_btn = `<button data-id="${encId}" style="color: green;" data-client_name="${full['name']}" title="HRMS Linked Projects" data-selectd_projects_list='${JSON.stringify(full['project_list'])}' class="btn btn-sm btn-icon btn-flat-primary customer_project_modal" id="customer_project_modal" >${feather.icons['link'].toSvg({ class: 'font-medium-3' })}</button>`;
                                }else{
                                    client_project_btn = `<button data-id="${encId}" data-client_name="${full['name']}" title="HRMS Linked Projects" data-selectd_projects_list='${JSON.stringify(full['project_list'])}' class="btn btn-sm btn-icon btn-flat-primary customer_project_modal" id="customer_project_modal" >${feather.icons['link'].toSvg({ class: 'font-medium-3' })}</button>`;
                                }

                            let customer_review_email = `<button data-id="${encId}" data-client_email="${full['email']}" data-client_name="${full['name']}" title="Customer Review Email" class="btn btn-sm btn-icon btn-flat-primary customer_review_email" id="customer_review_email">${feather.icons['mail'].toSvg({ class: 'font-medium-3' })}</button>`;
                            
                            let customer_email_preview_history = '';

                            if(full.client_feedback_mail_count > 0){
                                customer_email_preview_history = `<button data-id="${encId}" title="Email History" class="btn btn-sm btn-icon btn-flat-primary customer_email_preview_history" id="customer_email_preview_history"> 
                                    ${feather.icons['clock'].toSvg({ class: 'font-medium-3'})} 
                                </button>`;
                            }



                            let paymentReminder = '';
                                if(full?.payment_reminder == '1'){
                                        paymentReminder = `<button
                                            type="button"
                                            title="Invoice payment reminder enable"
                                            data-remindervalue="${full?.payment_reminder}"
                                            data-clientid="${full?.encrypted_id}"
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
                                            data-clientid="${full?.encrypted_id}"
                                            title="Invoice payment reminder disable"
                                            data-bs-toggle="modal"
                                            data-bs-target="#payment_reminder_enable_model">
                                            ${feather.icons['slash'].toSvg({ class: 'font-medium-3' })}
                                            </a>`;
                                    }

                            let buttons =
                                `<div class="d-flex" role="group">
                                ${customer_detail_btn}
                                    ${full['deleted_at'] ? '' : client_edit_btn}
                                    ${full['deleted_at'] ? '' : client_delete_btn}
                                    ${full['deleted_at'] ? client_restore_btn : ''}
                                    ${full['deleted_at'] ? '' : paymentReminder}
                                    ${full['deleted_at'] || full['workspace_id'] == 2 ? '' : client_project_btn}
                                    ${full['deleted_at'] || full['workspace_id'] == 2 ? '' : customer_review_email}
                                    ${full['deleted_at'] || full['workspace_id'] == 2 ? '' : customer_email_preview_history}
                                </div>`;

                            return (buttons);
                        }
                    }
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
                    $('.add_client_button').tooltip('dispose');
                    $('.add_client_button').tooltip({
                        title: "Add Customer",
                        placement: 'bottom'
                    });
                    $('.export-to-csv-btn').tooltip({
                        title: "Export Customer CSV",
                        placement: 'bottom'
                    });
                },
                buttons: [{
                    text: `${feather.icons['plus-circle'].toSvg({ class: 'font-medium-1' })}`,
                    className: 'add-new btn btn-primary btn-icon btn-sm add_client_button',
                    attr: {
                        'data-bs-toggle': 'modal',
                        'data-bs-target': '#addClientModal'
                    }
                },
                {
                    text: `${feather.icons['file-text'].toSvg({ class: 'font-medium-1', })}`,
                    className: 'add-new btn btn-secondary btn-sm btn-icon me-1 export-to-csv-btn',
                    attr: {
                        'data-bs-toggle': 'modal',
                        'data-bs-target': '#exportClientModal'
                    },
                }
            ],
            });
            $('#basic-icon-default-email').select2({
                tags: true,
                dropdownParent: $('#addClientForm').get(0),
            });
            $('#edit_email').select2({
                tags: true,
                dropdownParent: $('#editClientForm').get(0),
            });
            var rules = {
                'name': {
                    required: true
                },
                'email[]': {
                    required: true,
                    validEmails: true,
                },
                'phone': {
                    required: false
                },
                'address_line_1': {
                    required: false
                },
                'address_line_2': {
                    required: false
                },
                'city': {
                    required: false
                },
                'country': {
                    required: false
                },
                'plant_a_tree': {
                    required: false
                },
                'zip_code': {
                    required: false,
                }
            };

            var messages = {
                'name': {
                    required: "Please enter name"
                },
                'email[]': {
                    required: "Please enter email",
                    validEmails: "Please enter a valid email",
                },
            };

            $('#addClientModal').on('show.bs.modal', function (event) {
                $("#addClientForm")[0].reset();
                $('#basic-icon-default-email').val(null).trigger('change');
            });

            // Form Validation
            if (addClientForm.length) {
                addClientForm.validate({
                    rules,
                    messages,
                    submitHandler: function(form, event) {
                        event.preventDefault();
                        let handleSubmit = (form) => {
                            $('#addClientSubmitBtn').prop('disabled', true);
                            $('#addClientModal > .modal-dialog').block({
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
                            var myFormdata = new FormData(form);
                            $.ajax({
                                url: form.action,
                                method: 'POST',
                                data: myFormdata,
                                contentType: false,
                                processData: false,
                                success: function(response) {
                                    if (response.errors) {
                                        $(form).validate().showErrors(response.errors);
                                    } else {
                                        $('#addClientModal').modal('hide');
                                        toastr.success(null,
                                            "Client added successfully!");
                                        redrawClientsTable();
                                        form.reset();
                                    }
                                    $('#addClientModal > .modal-dialog').unblock();
                                    $('#addClientSubmitBtn').prop('disabled', false);
                                },
                                error: function(xhr, status, error) {
                                    $('#addClientModal > .modal-dialog').unblock();
                                    $('#addClientSubmitBtn').prop('disabled', false);
                                    if (xhr.status == 422) {
                                        $(form).validate().showErrors(JSON.parse(xhr
                                            .responseText).errors);
                                    } else {
                                        Swal.fire({
                                            title: 'An error occurred',
                                            text: error,
                                            icon: 'error',
                                        });
                                    }
                                }
                            });
                        }
                        if (!$('#plant_a_tree').prop('checked')) {
                            handleSubmit(form);
                            return;
                        }
                        Swal.fire({
                            title: 'Are you sure you want to plant a tree?',
                            text: "You can revert this by editing this user before sending him invoice mail!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes',
                            customClass: {
                                confirmButton: 'btn btn-primary',
                                cancelButton: 'btn btn-outline-danger ms-1'
                            },
                            buttonsStyling: false
                        }).then(function(result) {
                            if (!result.value) {
                                return;
                            }
                            handleSubmit(form);
                        });
                    }
                });
            }
            if (editClientForm.length) {
                editClientForm.validate({
                    rules,
                    messages,
                    submitHandler: function(form, event) {
                        event.preventDefault();
                        let handleSubmit = (form, event) => {
                            $('#editClientSubmitBtn').prop('disabled', true);
                            $('#editClientModal > .modal-dialog').block({
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
                                url: route('clients.update', [$(
                                        '#editClientModal input[name=client_id]')
                                    .val()
                                ]),
                                method: 'PUT',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                        'content')
                                },
                                data: $(form).serialize(),
                                success: function(response) {
                                    if (response.errors) {
                                        $(form).validate().showErrors(response.errors);
                                    } else {
                                        $('#editClientModal').modal('hide');
                                        toastr.success(null,
                                            "Customer updated successfully!");
                                        redrawClientsTable();
                                        form.reset();
                                        $(form).find(".select2").trigger('change');
                                    }
                                    $('#editClientSubmitBtn').prop('disabled', false);
                                    $('#editClientModal > .modal-dialog').unblock();
                                },
                                error: function(xhr, status, error) {
                                    $('#editClientSubmitBtn').prop('disabled', false);
                                    $('#editClientModal > .modal-dialog').unblock();
                                    if (xhr.status == 422) {
                                        $(form).validate().showErrors(JSON.parse(xhr
                                            .responseText).errors);
                                    } else {
                                        Swal.fire({
                                            title: 'An error occurred',
                                            text: error,
                                            icon: 'error',
                                        });
                                    }
                                }
                            });
                        };
                        if (!$('#edit_plant_a_tree').prop('checked')) {
                            handleSubmit(form, event);
                            return;
                        }
                        Swal.fire({
                            title: 'Are you sure you want to plant a tree?',
                            text: "You can revert this by editing this user before sending him invoice mail!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes',
                            customClass: {
                                confirmButton: 'btn btn-primary',
                                cancelButton: 'btn btn-outline-danger ms-1'
                            },
                            buttonsStyling: false
                        }).then(function(result) {
                            if (!result.value) {
                                return;
                            }
                            handleSubmit(form, event);
                        });
                    },
                    errorPlacement: function(error, element) {
                        if (element.hasClass('select2') && element.next('.select2-container')
                            .length) {
                            error.insertAfter(element.next('.select2-container'));
                        } else {
                            error.insertAfter(element);
                        }
                    }
                });
            }

            $('#editClientModal').on('show.bs.modal', function(event) {
                var {
                    id
                } = $(event.relatedTarget).data();
                $.ajax({
                    url: route("clients.show", id),
                    type: 'GET',
                    dataType: 'json',
                    success: function(data, status, xhr) {
                        $('#editClientForm input[name=client_id]').val(id);
                        if (data.client && typeof data.client === 'object') {
                            console.log(data.client.sales_user_list);
                            Object.keys(data.client).forEach(property => {
                                $('#edit_' + property).val(data.client[property]);
                                if ($('#edit_' + property).hasClass('select2')) {
                                    $('#edit_' + property).trigger('change');
                                } else if ($('#edit_' + property).hasClass(
                                        'form-check-input')) {
                                    $('#edit_' + property).prop('checked', data.client[
                                        property]);
                                }else if ($('#edit_' + property).hasClass('dt-email')) {
                                    $('#edit_' + property).empty();
                                    var client_email = data.client[property].split(',');
                                    if (Array.isArray(client_email) && client_email.length > 1) {
                                        client_email.forEach(email => {
                                            $('#edit_' + property).append(new Option(email, email, true, true));
                                        });
                                    }else {
                                        $('#edit_' + property).append(new Option(client_email[0], client_email[0], true, true));
                                    }
                                }
                            });
                            const salesAccessList = data.client.sales_user_list; // or adjust based on actual structure
                            const selectedIds = salesAccessList.map(item => item.sales_id);
                            $('#edit_sales_user_id').val(selectedIds).trigger('change');

                        }
                    },
                    error: function(xhr, status, error) {
                        toastr.error(xhr.responseJSON?.message ?? null, error);
                    }
                });
            });

            $('#editClientModal').on('hide.bs.modal', function(event) {
                $('#editClientForm input[name=client_id]').val('');
                $('input[id^="edit_"]').each(function(idx, el) {
                    $(el).val('');
                    if ($(el).hasClass('select2')) {
                        $(el).trigger('change');
                    }
                });
                $('#edit_plant_a_tree').prop('checked', false);
            });

            $(document).on('click', '.clientDeleteBtn', function(e) {
                e.preventDefault();
                let {
                    id,
                    invoicesCount
                } = $(this).data();
                if (invoicesCount) {
                    Swal.fire({
                        title: 'This customer has invoices!',
                        text: "Unable to delete this customer",
                        icon: 'error',
                    });
                    return;
                }
                Swal.fire({
                    title: 'Are you sure you want to delete this customer?',
                    text: "You can restore this customer later.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-outline-danger ms-1'
                    },
                    buttonsStyling: false
                }).then(function(result) {
                    if (result.value) {
                        $.ajax({
                            url: route('clients.destroy', id),
                            method: 'DELETE',
                            data: {
                                _token: $('meta[name=csrf-token]').attr('content')
                            },
                            success: function(response) {
                                toastr.success(null, "Customer deleted successfully!");
                                redrawClientsTable();
                            },
                            error: function(xhr, status, error) {
                                toastr.error(xhr.responseJSON?.message ?? null, error);
                                redrawClientsTable();
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.clientRestoreBtn', function(e) {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure you want to restore this customer?',
                    text: "You can revert this by deleting customer again!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Restore it!',
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-outline-danger ms-1'
                    },
                    buttonsStyling: false
                }).then(function(result) {
                    if (result.value) {
                        $.ajax({
                            url: route('clients.restore', id),
                            method: 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
                            },
                            success: function(response) {
                                toastr.success(null, "Customer restored successfully!");
                                redrawClientsTable();
                            },
                            error: function(xhr, status, error) {
                                toastr.error(xhr.responseJSON?.message ?? null, error);
                                redrawClientsTable();
                            }
                        });
                    }
                });
            });

            $('#customerProjects').select2({
                placeholder: 'Select Projects',
                closeOnSelect: false,
                allowClear: true,
                width: '100%'
            });


            var apiUrl = `${pathUrl}/api/crm/get_hrms_all_projects`;

            // Show modal and fetch data when button is clicked
            $(document).on('click', '.customer_project_modal', function (e) {
                let id = $(this).data('id'); // Get the customer ID
                let client_name = $(this).data('client_name');
                let projects_list = $(this).data('selectd_projects_list');

                // Selectd project list
                if (typeof projects_list === 'string') {
                    projects_list = JSON.parse(projects_list);
                }

                if (id) {
                    // Fetch projects from the API
                    $.ajax({
                        url: apiUrl, // API URL orignal https://hrms.iihglobal.net
                        method: 'GET',
                        headers: {
                            'Authorization': auth_token
                        },
                        success: function (response) {
                            // Clear previous options
                            $('#customerProjects').empty();
                            $('#customer_project_list_model #customer_name').text(client_name + ' projects list');
                            $('#customer_project_list_model #customer_id').val(id);

                            // Populate the dropdown with API data
                            if (response.projects && response.projects.length > 0) {
                                response.projects.forEach(function (project) {
                                    const isCustomer = projects_list.some(pc => pc.customer_id == project.crm_contact_id);
                                    // Check if the project is already associated with the customer
                                    if(project.crm_contact_id == null || isCustomer){
                                        const isSelected = projects_list.some(p => p.project_id === project.id); // Assuming each project has an 'id' property
                                        $('#customerProjects').append(
                                            `<option value="${project.id}" ${isSelected ? 'selected' : ''}>${project.title}</option>`
                                        );
                                    }

                                });
                            } else {
                                // Show no options if no data
                                $('#customerProjects').append(
                                    `<option disabled>No projects available</option>`
                                );
                            }

                            // Reinitialize Select2 to refresh options
                            $('#customerProjects').trigger('change');
                            $('#customer_project_list_model').modal('show'); // Show modal
                        },
                        error: function (xhr, status, error) {
                            // Handle errors
                            toastr.error(xhr.responseJSON?.message ?? 'An error occurred', error);
                        }
                    });
                }
            });

            $('#saveProjects').on('click', function () {
                // Get selected project IDs
                let selectedProjects = $('#customerProjects').val();
                let customer_id = $('#customer_id').val();
                // if (selectedProjects && selectedProjects.length > 0) {
                    $('#customer_project_list_model').block({
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
                    // Example: You can send the selected IDs to the server here
                    $.ajax({
                        url: route('clients.save_selected_projects'), // Your server-side route
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content') // Laravel CSRF token
                        },
                        data: {
                            projects: selectedProjects,
                            customer_id: customer_id,
                        },
                        success: function (response) {
                            toastr.success('Projects saved successfully!');
                            $('#customer_project_list_model').modal('hide');
                            $('.clients-table').DataTable().ajax.reload(null, false);
                        },
                        error: function (xhr, status, error) {
                            toastr.error(xhr.responseJSON?.message ?? 'Failed to save projects', error);
                        }
                    }).always(function () {
                        // Unblock the UI and hide the spinner
                        $('#customer_project_list_model').unblock();
                    });
                // }
                // else {
                //     // toastr.warning('Please select at least one project.');
                //     $('#customer_project_list_model').modal('hide');
                //     $('.clients-table').DataTable().ajax.reload(null, false);
                // }
            });

            $('#customer_project_list_model_close').on('click', function() {
                $('#customer_project_list_model').modal('hide');
            });


            $(document).on('click','.payment_reminder_cls',function (e){
                e.preventDefault();
                var reminder_status = $(this).data('remindervalue');
                $('#remindervalue').val(reminder_status);
                var client_id = $(this).data('clientid');
                $('#clientid').val(client_id);
                if(reminder_status == 1){
                    $(".invoice_message").html('Want to <span style="color: red">Disable</span> client invoice payment reminders!');
                }else{
                    $(".invoice_message").html('Want to <span style="color: green">Enable</span> client invoice payment reminders!');
                }
            });

            $(document).on('click','#payment_reminder',function (e){
                var reminder_status = $('#remindervalue').val();
                var client_id = $('#clientid').val();
                $.ajax({
                    url: route('clients.client_payment_reminder'),
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
                    },
                    data: {client_id:client_id,reminder_status:reminder_status},
                    success: function(data) {
                        toastr.success(null, data.message);
                        $('.clients-table').DataTable().ajax.reload(null, false);
                        $('#payment_reminder_btn').replaceWith(data.flag_val);
                        $('#payment_reminder_enable_model').modal('hide');
                    },
                    error: function(data) {
                        toastr.error(null, "Something went wrong !");
                    }
                });
            });

            $(document).on('click','#payment_reminder_enable_close',function(e){
                $('#payment_reminder_enable_model').modal('hide');
            });

            function copyContentToInput() {
                $('#content').val(contentQuill.root.innerHTML);
            }

            copyContentToInput();

            contentQuill.on('text-change', function(delta, oldDelta, source) {
                copyContentToInput();
            });

            /* Send Mail Ctr+Ent */
            $('#customer_review_email').click(function () {
                $('#reviewSendMailModal').keydown(function (event) {
                /*$("body").keypress(function (event) {*/
                    if (event.ctrlKey && (event.keyCode == 13 || event.keyCode == 10)) {
                        $('#reviewSendMailSubmitBtn').submit();
                    }
                });
            });

            $('#to').select2({
                tags: true,
                dropdownParent: $('#reviewSendMailModal').get(0),
            });

            $('#bcc').select2({
                tags: true,
                dropdownParent: $('#reviewSendMailModal').get(0),
            });

            var client_id = '';
            $(document).on('click','#customer_review_email',function (){
                client_id = $(this).data('id');
                //let timestampInSeconds = Math.floor(Date.now() / 1000);
                //$('#feedback_token').val(timestampInSeconds);  
                $('#reviewSendMailModal').modal('show');
            })

            $(document).on('show.bs.modal', '#reviewSendMailModal', function(e) {
                $('#reviewSendMailSubmitBtn').prop('disabled', true);
                $('#reviewSendMailModal > .modal-dialog').block({
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
                    url: route('clients.review_get_mail_content', client_id),
                    method: 'GET',
                    success: function(response) {
                        fillSendMailForm(response?.to, response?.bcc, response?.subject,
                            response?.content, response?.send_mail_count);
                            
                        $('#reviewSendMailModal #feedback_token').val(response?.time_stamp);
                        $('#reviewSendMailModal > .modal-dialog').unblock();
                        $('#reviewSendMailSubmitBtn').prop('disabled', false);
                    },
                    error: function(xhr, status, error) {
                        $('#reviewSendMailModal > .modal-dialog').unblock();
                        $('#reviewSendMailSubmitBtn').prop('disabled', false);
                        Swal.fire({
                            title: 'An error occurred',
                            text: error,
                            icon: 'error',
                        });
                    }
                });
            });

            var sendMailFormValidator = $('#reviewSendMailForm').validate({
                ignore: [],
                rules: {
                    "to[]": {
                        required: true,
                        validEmails: true,
                    },
                    "bcc[]": {
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
                    $('#reviewSendMailSubmitBtn').prop('disabled', true);
                    $('#reviewSendMailModal > .modal-dialog').block({
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
                        url: route('clients.review_get_mail_content', client_id),
                        method: 'post',
                        data: $(form).serialize(),
                        success: function(response) {
                            if (response.errors) {
                                $(form).validate().showErrors(response.errors);
                            } else {
                                $('#reviewSendMailModal').modal('hide');
                                toastr.success(null, "Mail sent successfully!");
                            }
                            $('#reviewSendMailModal > .modal-dialog').unblock();
                            $('#reviewSendMailSubmitBtn').prop('disabled', false);
                        },
                        error: function(xhr, status, error) {
                            $('#reviewSendMailModal > .modal-dialog').unblock();
                            $('#reviewSendMailSubmitBtn').prop('disabled', false);
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

            $(document).on('hide.bs.modal', '#reviewSendMailModal', function(e) {
                $('#to').empty();
                $('#bcc').empty();
                $('#subject').val('');
                $('.send_mail_count').text('0');
                contentQuill.root.innerHTML = '';
                copyContentToInput();
                $('form#reviewSendMailForm').get(0).reset();
                sendMailFormValidator.resetForm();
            });

            function fillSendMailForm(to = [], bcc = [], subject = "", content = "", send_mail_count= "") {

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
                $('.send_mail_count').text(send_mail_count);
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

            var fileContentsArray = [];
            $('#custom_attach').on('change', function(e) {
                const files_con = e.target.files;
                let TotalFiles = $('#custom_attach')[0].files.length; //Total files
                var fileSize = $('#custom_attach').get(0).files[0].size; // in bytes
                var maxSize = 5000000;
                if(fileSize > maxSize){
                    alert('file size is more then 5MB bytes');
                    return false;
                }

                for (let i = 0; i < files_con.length; i++) {
                    const file = files_con[i];
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        // Store the file content in the array
                        fileContentsArray.push({
                            name: file.name,
                            type: file.type,
                            size: file.size,
                            content: e.target.result // The file content
                        });
                        $("<div id='attachment_data_"+file.name+"'>"+"<span data-name='"+file.name+"' style='color:red;margin-right: 10px;cursor: pointer' class='attach_file'>X</span><span class='custom_file_attach'>"+file.name+"</span></div>").appendTo("#receiptNewFile");
                    };
                    // Read the file as text (you can use other methods like readAsDataURL for images)
                    reader.readAsText(file);
                }
            });

            $(document).on('click','.customer_email_preview_history',function(e){
                var client_id = $(this).data('id');
                if (client_id) {
                    // First API call
                    $.ajax({
                        url: route('clients.review_email_history', client_id),
                        method: 'GET',
                        headers: {
                            'Authorization': auth_token
                        },
                        success: function (response) {
                            console.log(response.client_feedbacks);
                            // Populate the modal with fetched feedbacks
                            populateFeedbackModal(response.client_feedbacks);

                            // Open the modal
                            $('#feedbackModal').modal('show');
                        },
                        error: function (xhr, status, error) {
                            toastr.error(xhr.responseJSON?.message ?? 'Project log api not responding', error);
                        }
                    });
                }
            });

           // Function to generate stars with Feather icons
            function generateStars(count) {
                let stars = '';
                for (let i = 0; i < 5; i++) {
                    if (i < count) {
                        stars += feather.icons['star'].toSvg({ class: 'font-medium-3 text-warning' });
                    } else {
                        stars += feather.icons['star'].toSvg({ class: 'font-medium-3 text-secondary' }); // Different class for empty stars
                    }
                }
                return stars;
            }


            // Function to populate the modal with feedback data
            function populateFeedbackModal(feedbacks) {
                let groupedFeedbacks = {};

                if (feedbacks.length === 0) {
                    document.getElementById("feedbackAccordion").innerHTML = `
                        <div class="alert text-center"><h6>No feedback data found.</h6></div>
                    `;
                    return;
                }

                // Group feedbacks by date
                feedbacks.forEach(feedback => {
                    let dateObj = new Date(feedback.created_at);
                    let formattedDate = dateObj.toLocaleDateString('en-GB', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric'
                    });

                    if (!groupedFeedbacks[formattedDate]) {
                        groupedFeedbacks[formattedDate] = [];
                    }
                    groupedFeedbacks[formattedDate].push(feedback);
                });


                let accordionHtml = "";
                let index = 0;

                for (let date in groupedFeedbacks) {
                    let feedbackList = groupedFeedbacks[date];
                    let tableRows = "";

                    feedbackList.forEach(feedback => {

                        tableRows += `
                            <tr>
                                <td class="center_lable">${generateStars(feedback.communication)}</td>
                                <td class="center_lable">${generateStars(feedback.quality_of_work)}</td>
                                <td class="center_lable">${generateStars(feedback.collaboration)}</td>
                                <td class="center_lable">${generateStars(feedback.value_for_money)}</td>
                                <td class="center_lable">${generateStars(feedback.overall_satisfaction)}</td>
                                <td class="center_lable">${feedback.recommendation ? 'Yes' : 'No'}</td>
                                <td>
                                    <button class="btn btn-sm view-message" data-message="${feedback.message_box}">
                                        ${feather.icons['eye'].toSvg({ class: 'font-medium-3', 'stroke-width': 2 })}
                                    </button>
                                </td>
                            </tr>
                        `;
                    });

                    accordionHtml += `
                        <div class="accordion-item mb-1">
                            <h2 class="accordion-header" id="heading${index}">
                                <button class="accordion-button ${index === 0 ? "" : ""}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${index}" aria-expanded="${index === 0}" aria-controls="collapse${index}">
                                    Feedback for ${date}
                                </button>
                            </h2>
                            <div id="collapse${index}" class="mt-1 accordion-collapse collapse ${index === 0 ? "show" : ""}" aria-labelledby="heading${index}" data-bs-parent="#feedbackAccordion">
                                <div class="accordion-body">
                                    <table class="table table-bordered">
                                       <thead>
                                            <tr class="small_text">
                                                <th>Communication</th>
                                                <th>Quality of Work</th>
                                                <th>Collaboration</th>
                                                <th>Value for Money</th>
                                                <th>Overall Satisfaction</th>
                                                <th>Recommendation</th>
                                                <th>Message</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${tableRows}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;

                    index++;
                }

                document.getElementById("feedbackAccordion").innerHTML = accordionHtml;
            }

            $(document).on('click', '.view-message', function () {
                    const message = $(this).data('message');
                    const formattedMessage = message.replace(/\n/g, '<br>'); // Replace line breaks with <br>
                    $('#modalMessageContent').html(formattedMessage);
                    $('#messageModal').modal('show');
                });

                // Feather icon init
                feather.replace();

            $('.input-daterange2').datepicker(
                {
                    format: 'dd/mm/yyyy',
                    autoclose: true,
                    inputs: $('.input-daterange2 .form-control'),
                    container: '#exportClientModal'
                }
            ).on('hide', function (e) {
                e.stopPropagation();
            });

            $('form#exportClientForm').on('submit', function (e) {
                e.preventDefault();
                let $form = this;
                $($form).find('#exportClientSubmitBtn').prop('disabled', true);
                $('#exportClientModal > .modal-dialog .modal-content').block({
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
                    url: $form.action,
                    method: 'POST',
                    data: new FormData($form),
                    contentType: false,
                    processData: false,
                    success: function (response, status, xhr) {
                        // Extract the filename from the response headers
                        var filename = '';
                        var disposition = xhr.getResponseHeader('Content-Disposition');
                        if (disposition && disposition.indexOf('attachment') !== -1) {
                            var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                            var matches = filenameRegex.exec(disposition);
                            if (matches != null && matches[1]) {
                                filename = matches[1].replace(/['"]/g, '');
                            }
                        }
                        var blobObj = new Blob([response], { type: xhr?.getResponseHeader('Content-Type') });

                        // Create a temporary anchor element
                        var link = document.createElement('a');
                        link.href = URL.createObjectURL(blobObj);
                        link.download = filename || 'client.csv';

                        // Trigger a click event to initiate the download
                        link.click();

                        // Clean up the temporary anchor element
                        link.remove();

                        $('#exportClientModal').modal('hide');
                        $('#exportClientModal > .modal-dialog .modal-content').unblock();
                        $('#exportClientSubmitBtn').prop('disabled', false);
                        toastr.success(null, "Client exported successfully!");
                    },
                    error: function (xhr, status, error) {
                        $('#exportClientModal > .modal-dialog .modal-content').unblock();
                        $('#exportClientSubmitBtn').prop('disabled', false);
                        Swal.fire({
                            title: 'An error occurred',
                            text: error,
                            icon: 'error',
                        });
                    }
                });

            });

            $('.input-daterange').datepicker(
                {
                    format: 'dd/mm/yyyy',
                    autoclose: true,
                    inputs: $('.input-daterange .form-control'),
                    orientation: 'bottom auto',
                }
            ).on('hide', function (e) {
                e.stopPropagation();
            });

            $('#exportClientModal').on('hide.bs.modal', function (e) {
                $('form#exportClientForm').get(0).reset();
            });

        });
    </script>
@endsection
