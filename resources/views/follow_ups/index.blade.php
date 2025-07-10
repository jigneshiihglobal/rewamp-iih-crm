@extends('layouts.app')

@section('page-vendor-js')
    <script src="{{ asset('app-assets/vendors/js/editors/quill/katex.min.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/vendors/js/editors/quill/highlight.min.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/vendors/js/editors/quill/quill.min.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js?v=' . config('versions.js')) }}">
    </script>
@endsection

@section('vendor-css')
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/vendors/css/editors/quill/katex.min.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/vendors/css/editors/quill/monokai-sublime.min.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/vendors/css/editors/quill/quill.snow.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/vendors/css/editors/quill/quill.bubble.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet"
        href="{{ asset('app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css?v=' . config('versions.css')) }}">
@endsection

@section('custom-css')
    <link rel="stylesheet" href="{{ asset('app-assets/css/custom/bootstrap-datepicker.css?v=' . config('versions.css')) }}">
    <style>
        div#contentQuill {
            height: 140px;
        }

        #editFollowUpEmailForm .flatpickr-wrapper,
        #editFollowUpCallForm .flatpickr-wrapper {
            width: 100%;
        }

        #contentQuill {
            min-height: 240px;
            resize: vertical;
            overflow: auto;
        }
    </style>
@endsection

@section('page-css')
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/css/plugins/forms/form-quill-editor.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet"
        href="{{ asset('app-assets/css/plugins/forms/pickers/form-flat-pickr.css?v=' . config('versions.css')) }}">
@endsection

@section('content')
    <section id="follow_up_filters">
        <div class="row justify-content-end mb-1">
            <div id="followUpFilterWrapper" class="col-md-10 row collapse mx-0 align-items-center">
                <div class="col-12 col-sm-3 col-xl-2 form-group">
                    <label for="send_reminder_at_filter" class="form-label">Reminder</label>
                    <select id="send_reminder_at_filter" class="form-select select2 select2-size-sm">
                        <option value="" selected>All</option>
                        <option value="month">This month</option>
                        <option value="last_month">Last month</option>
                        <option value="3_months">Last 3 months</option>
                        <option value="year">Current year</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>
                <div class="col-xxl-2 col-lg-3 col-sm-4 form-group" style="display:none;"
                    id="send_reminder_at_filter_range_wrapper">
                    <label class="form-label">Reminder Range</label>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm" name="send_reminder_at_filter_range"
                            id="send_reminder_at_filter_range">
                    </div>
                </div>
                <div class="col-xxl-2 col-lg-3 col-sm-4 form-group mt-1">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="show_completed_filter"
                            name="show_completed_filter" aria-describedby="show_completed_checkbox_msg" />
                        <label class="form-check-label" for="show_completed_filter">Show completed</label>
                    </div>
                </div>
            </div>
            <div class="col-md-2 d-flex justify-content-end align-items-start">
                <a data-bs-toggle="collapse" href="#followUpFilterWrapper" role="button" aria-expanded="false"
                    aria-controls="followUpFilterWrapper">
                    Advanced Search
                </a>
            </div>
        </div>
    </section>

    <section class="followUpList">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <table class="followUpTable table" id="followUpTable">
                    <thead class="table-light">
                        <tr>
                            <th>Type</th>
                            <th>Lead</th>
                            <th>Status</th>
                            <th>Remind at</th>
                            <th>Created at</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>

    @include('follow_ups.modals.edit-call')

    @include('follow_ups.modals.edit-email')
@endsection

@section('page-js')
    <script src="{{ asset('app-assets/js/scripts/forms/form-quill-editor.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/js/custom/flatpickr.js') }}"></script>
@endsection

@section('custom-js')
    <script>
        $(document).ready(function() {

            $.fn.dataTableExt.oStdClasses.sFilter = "dataTables_filter dataTables_filter_sm";

            const redraw = (paging = false) => followUpDT && followUpDT.draw(
                paging);
            const followUpTypes = @json(App\Enums\FollowUpType::all());
            const followUpStatuses = @json(App\Enums\FollowUpStatus::all());
            const $followUpList = $('.followUpList');
            const authuser = @json(auth()->user());
            const $sendReminderAtFilter = $('#send_reminder_at_filter');
            const $sendReminderAtFilterRange = $('#send_reminder_at_filter_range');
            const $sendReminderAtFilterRangeWrapper = $('#send_reminder_at_filter_range_wrapper');
            const $showCompletedFilter = $('#show_completed_filter');
            const followUpTypeIcons = {
                [followUpTypes?.EMAIL]: 'mail',
                [followUpTypes?.CALL]: 'phone',
            };
            const followUpTypeClasses = {
                [followUpTypes?.EMAIL]: 'text-primary',
                [followUpTypes?.CALL]: 'text-warning',
            };

            $('.select2').each(function() {
                var $this = $(this);
                if(!$this.parent().hasClass('position-relative')) $this.wrap('<div class="position-relative"></div>');
                $this.select2({
                    dropdownAutoWidth: true,
                    width: '100%',
                    dropdownParent: $this.parent(),
                    containerCssClass: 'select-sm',
                });
            });
            var followUpTable = $('#followUpTable');
            var followUpDT = followUpTable.DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: route('follow_ups.index'),
                    data: function(d) {
                        d.send_reminder_at_filter = $sendReminderAtFilter.val();
                        d.send_reminder_at_filter_range = $sendReminderAtFilterRange.val();
                        d.show_completed_filter = $showCompletedFilter.prop('checked');
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
                initComplete: function(settings, json) {
                    $('.followUpList div.dataTables_length select').addClass('form-select-sm');
                },
                columns: [{
                        data: 'type',
                        name: 'type',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, full, meta) {
                            return '<span class="' + followUpTypeClasses[full?.type] + '">' +
                                feather.icons[followUpTypeIcons[full?.type]].toSvg({
                                    class: 'font-medium-3'
                                }) + '</span>';
                        }
                    },
                    {
                        data: 'lead_name',
                        name: 'lead_name',
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render(data, type, full, meta) {
                            switch (full?.status) {
                                case followUpStatuses?.COMPLETED:
                                    return '<span class=\'badge rounded-pill bg-success bg-gradient\' >Complete</span>';
                                    break;
                                default:
                                    return '<span class=\'badge rounded-pill bg-danger bg-gradient\' >Pending</span>';
                                    break;
                            }
                        },
                    },
                    {
                        data: 'send_reminder_at',
                        name: 'send_reminder_at'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        searchable: false,
                        orderable: false,
                        render: function(data, type, full, meta) {

                            let showEditBtn = full['deleted_at'] || full?.status == followUpStatuses
                                ?.COMPLETED;
                            let editFollowUpBtn = full?.type == followUpTypes?.CALL ?
                                `<a href="javascript:void(0);" data-id="${full['encrypted_id']}" class="btn btn-sm btn-icon btn-flat-info" data-bs-toggle="modal" data-bs-target="#editFollowUpCallModal" ><span data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" data-bs-original-title="Edit">${feather.icons['edit'].toSvg({ class: 'font-medium-3' })}</span></a>` :
                                `<a href="javascript:void(0);" data-id="${full['encrypted_id']}" class="btn btn-sm btn-icon btn-flat-info" data-bs-toggle="modal" data-bs-target="#editFollowUpEmailModal" ><span data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" data-bs-original-title="Edit">${feather.icons['edit'].toSvg({ class: 'font-medium-3' })}</span></a>`;

                            let deleteFollowUpBtn =
                                `<button data-id="${full['encrypted_id']}" class="btn btn-sm btn-icon btn-flat-danger deleteFollowUpBtn" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete" data-bs-original-title="Delete">${feather.icons['trash'].toSvg({ class: 'font-medium-3' })}</button>`;

                            let buttons = `<div
                                    class="d-flex lead-list__action_buttons"
                                    role="group"
                                    aria-label="Basic mixed styles example"
                                    >
                                ${showEditBtn ? '' : editFollowUpBtn}
                                ${full['deleted_at'] ? '' : deleteFollowUpBtn}
                                </div>`;

                            return (buttons);
                        }
                    },
                ],
                order: [
                    [2, 'ASC'],
                    [3, 'ASC']
                ],
                dom: '<"d-flex justify-content-between align-items-center header-actions mx-1 row mt-50 invoices-table-wrapper"r' +
                    '<"col-sm-12 col-lg-4 d-flex justify-content-center justify-content-lg-start" l>' +
                    '<"col-sm-12 col-lg-8 ps-xl-75 ps-0"<"dt-action-buttons d-flex align-items-center justify-content-center justify-content-lg-end flex-lg-nowrap flex-wrap"<"me-1"B>f>>' +
                    '>t' +
                    '<"d-flex justify-content-between mx-2 row"' +
                    '<"col-sm-12 col-md-6"i>' +
                    '<"col-sm-12 col-md-6"p>' +
                    '>',
                buttons: []
            });

            var followUpDatePickr, followUpTimePickr;
            var addFollowUpValidator;

            $('#editFollowUpCallModal').on('show.bs.modal', function(e) {
                let id = e.relatedTarget.dataset?.id;
                fetchDataAndFillForm(id, $(this), $('#editFollowUpCallForm'));
                followUpDatePickr = $(this).find('#follow_up_date').flatpickr({
                    static: true,
                    minDate: 'today',
                    dateFormat: "d/m/Y",
                });
                followUpTimePickr = $(this).find('#follow_up_time').flatpickr({
                    static: true,
                    enableTime: true,
                    noCalendar: true,
                    time_24hr: false,
                    minuteIncrement: 15,
                    dateFormat: "G:i K",
                });

                addFollowUpValidator = $('#editFollowUpCallForm').validate({
                    ignore: [],
                    rules: {
                        "sales_person_phone[]": {
                            required: true,
                            validPhones: true,
                        },
                        follow_up_date: {
                            required: true,
                        },
                        follow_up_time: {
                            required: true,
                        },
                    },
                    messages: {
                        "sales_person_phone[]": {
                            required: 'Please enter phone number',
                            validPhones: "Please enter valid phone numbers",
                        },
                        follow_up_date: {
                            required: "Please select date",
                        },
                        follow_up_time: {
                            required: "Please select time",
                        },
                    },
                    submitHandler: function(form, event) {
                        event.preventDefault();
                        $('#editFollowUpCallSubmitBtn').prop('disabled', true);
                        $('#editFollowUpCallModal > .modal-dialog .modal-content').block({
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
                            method: 'PUT',
                            data: $(form).serialize(),
                            success: function(response) {
                                if (response.errors) {
                                    $(form).validate().showErrors(response.errors);
                                } else {
                                    $('#editFollowUpCallModal').modal('hide');
                                    toastr.success(null,
                                        "Follow up updated successfully!");
                                }
                                $('#editFollowUpCallModal > .modal-dialog .modal-content')
                                    .unblock();
                                $('#editFollowUpCallSubmitBtn').prop('disabled',
                                    false);
                                redraw();
                            },
                            error: function(xhr, status, error) {
                                $('#editFollowUpCallModal > .modal-dialog .modal-content')
                                    .unblock();
                                $('#editFollowUpCallSubmitBtn').prop('disabled',
                                    false);
                                if (xhr.status == 422) {
                                    $(form).validate().showErrors(JSON.parse(xhr
                                            .responseText)
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
                        if (element.hasClass('select2')) {
                            error.appendTo(element.parent())
                        } else if (element.attr('name') === 'content') {
                            error.insertAfter(element);
                        } else if (element.attr('type') === 'hidden') {
                            error.appendTo('#editFollowUpForm');
                        } else {
                            error.insertAfter(element);
                        }
                    }
                });
            });

            $('#editFollowUpEmailModal').on('show.bs.modal', function(e) {
                let id = e.relatedTarget.dataset?.id;
                fetchDataAndFillForm(id, $(this), $('#editFollowUpEmailForm'));
                followUpDatePickr = $(this).find('#follow_up_date').flatpickr({
                    static: true,
                    minDate: 'today',
                    dateFormat: "d/m/Y",
                });
                followUpTimePickr = $(this).find('#follow_up_time').flatpickr({
                    static: true,
                    noCalendar: true,
                    enableTime: true,
                    time_24hr: false,
                    minuteIncrement: 15,
                    dateFormat: "G:i K",
                });

                addFollowUpValidator = $('#editFollowUpEmailForm').validate({
                    ignore: [],
                    rules: {
                        "to[]": {
                            required: true,
                            validEmails: true,
                        },
                        "bcc[]": {
                            required: false,
                            validEmails: true,
                        },
                        subject: {
                            required: true,
                        },
                        content: {
                            required: true
                        },
                        follow_up_date: {
                            required: true,
                        },
                        follow_up_time: {
                            required: true,
                        },
                    },
                    messages: {
                        "to[]": {
                            required: 'Please enter email',
                            validEmails: "Please enter valid email",
                        },
                        "bcc[]": {
                            validEmails: "Please enter valid email",
                        },
                        subject: {
                            required: 'Please enter subject',
                        },
                        content: {
                            required: 'Please enter content'
                        },
                        follow_up_date: {
                            required: "Please select date",
                        },
                        follow_up_time: {
                            required: "Please select time",
                        },
                    },
                    submitHandler: function(form, event) {
                        event.preventDefault();
                        $('#editFollowUpEmailSubmitBtn').prop('disabled', true);
                        $('#editFollowUpEmailModal > .modal-dialog .modal-content').block({
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
                            method: 'PUT',
                            data: $(form).serialize(),
                            success: function(response) {
                                if (response.errors) {
                                    $(form).validate().showErrors(response.errors);
                                } else {
                                    $('#editFollowUpEmailModal').modal('hide');
                                    toastr.success(null,
                                        "Follow up updated successfully!");
                                }
                                $('#editFollowUpEmailModal > .modal-dialog .modal-content')
                                    .unblock();
                                $('#editFollowUpEmailSubmitBtn').prop('disabled',
                                    false);
                                redraw();
                            },
                            error: function(xhr, status, error) {
                                $('#editFollowUpEmailModal > .modal-dialog .modal-content')
                                    .unblock();
                                $('#editFollowUpEmailSubmitBtn').prop('disabled',
                                    false);
                                if (xhr.status == 422) {
                                    $(form).validate().showErrors(JSON.parse(xhr
                                            .responseText)
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
                        if (element.hasClass('select2')) {
                            error.appendTo(element.parent())
                        } else if (element.attr('name') === 'content') {
                            error.insertAfter(element);
                        } else if (element.attr('type') === 'hidden') {
                            error.appendTo('#editFollowUpForm');
                        } else {
                            error.insertAfter(element);
                        }
                    }
                });
            });

            function fetchDataAndFillForm(id, $modal, $form) {
                $modal.find('input[name=id]').val(id);
                var followUpEditUrl = route("follow_ups.edit", id);

                $.ajax({
                    url: followUpEditUrl,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data, status, xhr) {
                        $form.attr('action', route("follow_ups.update", id));
                        fillEditFollowUpForm(
                            data?.to,
                            data?.bcc,
                            data?.subject,
                            data?.follow_up_at,
                            data?.content,
                            data?.sales_person_phone,
                            data?.type,
                            data?.email_signature_id,
                            data?.smtp_credential_id,
                        );
                    },
                    error: function(xhr, status, error) {
                        toastr
                            .error(
                                xhr.responseJSON?.message ?? null,
                                error
                            );
                    }
                });
            }

            $('#to').select2({
                tags: true,
                dropdownParent: $('#editFollowUpEmailModal').get(0),
                containerCssClass: 'select-sm',
            });

            $('#bcc').select2({
                tags: true,
                dropdownParent: $('#editFollowUpEmailModal').get(0),
                containerCssClass: 'select-sm',
            });

            $('#sales_person_phone').select2({
                tags: true,
                dropdownParent: $('#editFollowUpCallModal').get(0),
                containerCssClass: 'select-sm',
            });

            $('#email_signature_id').select2({
                dropdownParent: $('#editFollowUpEmailModal').get(0),
                containerCssClass: 'select-sm',
            });

            $('#smtp_credential_id').select2({
                dropdownParent: $('#editFollowUpEmailModal').get(0),
                containerCssClass: 'select-sm',
            });

            var contentQuill = new Quill('#contentQuill', {
                theme: 'snow',
                format: {
                    fontFamily: 'Public Sans'
                }
            });

            function copyContentToInput() {
                $('#content').val(contentQuill.root.innerHTML);
            }

            copyContentToInput();

            contentQuill.on('text-change', function(delta, oldDelta, source) {
                copyContentToInput();
            });

            $(document).on('hide.bs.modal', '#editFollowUpEmailModal', function(e) {
                $(this).find('input[name=id]').val('');
                resetAndDestroyFollowUpValidator($('form#editFollowUpEmailForm').get(0))
            });

            $(document).on('hide.bs.modal', '#editFollowUpCallModal', function(e) {
                $(this).find('input[name=id]').val('');
                resetAndDestroyFollowUpValidator($('form#editFollowUpCallForm').get(0))
            });


            function resetAndDestroyFollowUpValidator($form = null) {
                if (addFollowUpValidator) {
                    addFollowUpValidator.resetForm();
                    addFollowUpValidator.destroy();
                    addFollowUpValidator = undefined;
                }
                if ($form) $form.reset();
                contentQuill.root.innerHTML = '';
                copyContentToInput();
            }

            function fillEditFollowUpForm(
                to = [],
                bcc = [],
                subject = '',
                followUpAt = '',
                content = '',
                sales_person_phones = [],
                type = 'email',
                email_signature_id = '',
                smtp_credential_id = '',
            ) {
                if (type == followUpTypes?.CALL) {

                    if (Array.isArray(sales_person_phones) && sales_person_phones.length) {
                        $('#sales_person_phone').empty();
                        sales_person_phones.forEach(phone => {
                            if (phone != '') $('#sales_person_phone').append(new Option(phone, phone, true,
                                true));
                        });
                    }

                } else {

                    $('#to').empty();
                    if (Array.isArray(to) && to.length) {
                        to.forEach(email => {
                            if (email != '') $('#to').append(new Option(email, email, true, true));
                        });
                    }

                    $('#bcc').empty();
                    if (Array.isArray(bcc) && bcc.length) {
                        $('#bcc').empty();
                        bcc.forEach(email => {
                            if (email != '') $('#bcc').append(new Option(email, email, true, true));
                        });
                    }

                    if (content != '') {
                        contentQuill.root.innerHTML = content;
                        copyContentToInput();
                    }

                    if (subject !== '') {
                        $('#subject').val(subject);
                    }

                    if(email_signature_id !== '') $('#email_signature_id').val(email_signature_id).trigger('change')
                    if(smtp_credential_id !== '') $('#smtp_credential_id').val(smtp_credential_id).trigger('change')
                }

                if (followUpAt !== '') {
                    let followUpAtDate = new Date(followUpAt);
                    followUpDatePickr.setDate(flatpickr.formatDate(followUpAtDate, 'd/m/Y'));
                    followUpTimePickr.setDate(flatpickr.formatDate(followUpAtDate, 'G:i K'));
                }
            }

            $(document).on('click', '.deleteFollowUpBtn', function(e) {
                e.preventDefault();
                let {
                    id
                } = $(this).data();

                Swal.fire({
                    title: 'Are you sure you want to delete this follow up?',
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
                            url: route('follow_ups.destroy', id),
                            method: 'DELETE',
                            data: {
                                _token: $('meta[name=csrf-token]').attr('content')
                            },
                            success: function(response) {
                                toastr.success(null, "Follow up deleted successfully!");
                                redraw();
                            },
                            error: function(xhr, status, error) {
                                toastr.error(xhr.responseJSON?.message ?? null, error);
                                redraw();
                            }
                        });
                    }
                });
            });

            [
                $sendReminderAtFilter,
                $sendReminderAtFilterRange,
                $showCompletedFilter
            ]
            .forEach(filter => filter.change(e => redraw()));

            $sendReminderAtFilter.change(function(e) {
                if ($(this).val() == 'custom') $sendReminderAtFilterRangeWrapper.show()
                else $sendReminderAtFilterRangeWrapper.hide()
            });

            var filter_created_at_range_picker = $sendReminderAtFilterRange.flatpickr({
                mode: 'range',
                dateFormat: 'd/m/Y',
                onChange: function(selectedDates, dateStr, instance) {
                    redraw();
                }
            });

            $(document).on('flatpickr:cleared', '#send_reminder_at_filter_range', function(e) {
                redraw();
            })
        });
    </script>
@endsection
