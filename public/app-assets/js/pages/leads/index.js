
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

var csrf_token = () => $('meta[name="csrf-token"]').attr('content');

var leadsDataTable = $('.user-list-table'),
    newLeadModal = $('.new-user-modal'),
    newLeadForm = $('.add-new-user'),
    select = $('.select2'),
    dtContact = $('.dt-contact'),
    editLeadModal = $("#editLeadModal"),
    leadDetailModal = $("#leadDetailModal"),
    editLeadForm = $('#editLeadForm'),
    notesModal = $('#notesModal'),
    lead_notes_list = $('#lead_notes_list'),
    addNoteForm = $('#addNoteForm'),
    leadsTable,
    prjBudgetObj = {
        "0-500": ":currency_symbol:0 to :currency_symbol:500",
        "500-2500": ":currency_symbol:500 to :currency_symbol:2500",
        "2500-5000": ":currency_symbol:2500 to :currency_symbol:5000",
        "5000": ":currency_symbol:5000+"
    },
    addLeadPrjBudgetSelect = $('#prj_budget'),
    addLeadCurrencySelect = $('#currency_select'),
    editLeadPrjBudgetSelect = $('#edit_prj_budget'),
    editLeadCurrencySelect = $('#edit_currency_id'),
    lead_status_filter_link = $('.lead_status_filter_link'),
    filter_assigned_to = $('#filter_assigned_to'),
    filter_created_at = $('#filter_created_at'),
    created_at_start = $('#created_at_start'),
    created_at_end = $('#created_at_end'),
    lead_restore_button = $('.lead_restore_button'),
    filter_lead_source_id = $('#filter_lead_source_id'),
    filter_show_deleted = $('#filter_show_deleted');

var assetPath = '../../../app-assets/',
    userView = 'app-user-view-account.html';

if ($('body').attr('data-framework') === 'laravel') {
    assetPath = $('body').attr('data-asset-path');
    userView = assetPath + 'app/user/view/account';
}

addLeadPrjBudgetSelect.wrap('<div class="position-relative" style="width: 80%;"></div>');
addLeadCurrencySelect.wrap('<div class="position-relative" style="width: 20%;"></div>');

addLeadPrjBudgetSelect.select2({
    dropdownAutoWidth: true,
    width: 'element',
    dropdownParent: $(addLeadPrjBudgetSelect).parent()
});

addLeadCurrencySelect.select2({
    dropdownAutoWidth: true,
    width: 'element',
    dropdownParent: $(addLeadCurrencySelect).parent()
});

editLeadPrjBudgetSelect.wrap('<div class="position-relative" style="width: 80%;"></div>');
editLeadCurrencySelect.wrap('<div class="position-relative" style="width: 20%;"></div>');

editLeadPrjBudgetSelect.select2({
    dropdownAutoWidth: true,
    width: 'element',
    dropdownParent: $(editLeadPrjBudgetSelect).parent()
});

editLeadCurrencySelect.select2({
    dropdownAutoWidth: true,
    width: 'element',
    dropdownParent: $(editLeadCurrencySelect).parent()
});

select.each(function () {
    var $this = $(this);
    $this.wrap('<div class="position-relative"></div>');
    $this.select2({
        dropdownAutoWidth: true,
        width: '100%',
        dropdownParent: $this.parent()
    });
});

filter_created_at.wrap('<div class="position-relative"></div>');
filter_created_at.select2({
    dropdownAutoWidth: true,
    width: '100%',
    dropdownParent: filter_created_at.parent(),
    dropdownCssClass: 'select2-long-dropdown'
});

$(document).on('select2:open', (e) => {
    const selectId = e.target.id
    $(".select2-search__field[aria-controls='select2-" + selectId + "-results']").each(function (
        key,
        value,
    ) {
        value.focus();
    })
})

var buttons = [
    {
        text: `${feather.icons['plus-circle'].toSvg({ class: 'font-medium-1', })}`,
        className: 'add-new btn btn-primary btn-sm btn-icon add-new-lead-btn',
        attr: {
            'data-bs-toggle': 'modal',
            'data-bs-target': '#addLeadModal'
        },
        init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
        }
    }
];

if (!isMarketing) {
    buttons.push({
        text: `${feather.icons['file-text'].toSvg({ class: 'font-medium-1', })}`,
        className: 'add-new btn btn-secondary btn-sm btn-icon export-to-csv-btn',
        attr: {
            'data-bs-toggle': 'modal',
            'data-bs-target': '#exportLeadsModal'
        },
    });
}

$('#basic-icon-default-email-create').select2({
    tags: true,
    dropdownParent: $('#addLeadForm').get(0),
});
$('#edit_email').select2({
    tags: true,
    dropdownParent: $('#editLeadForm').get(0),
});

$('#addLeadModal').on('show.bs.modal', function (event) {
    $("#addLeadForm")[0].reset();
    $('#basic-icon-default-email-create').val(null).trigger('change');
    $('#addLeadModal #email-errors').text('');
    $("#lead_source_select").val("").trigger("change");
    $("#assigned_to").val("").trigger("change");
});

$('.lead_type_click').on('click',function (){
    var activeElement = $(this);
// Check if there's an active element
    if (activeElement.length > 0) {
        // Get the value of data-lead-status-id attribute
        var leadStatusId = activeElement.data('lead-status-id');
        $('#lead_type').val(leadStatusId);
    }
});

// leads list datatable
if (leadsDataTable.length) {
    leadsTable = leadsDataTable.DataTable({
        serverSide: true,
        processing: true,
        ajax: {
            url: route('leads.index'),
            data: function (d) {
                d.lead_status_id = lead_status_filter_link
                    .filter(function () {
                        return $(this).hasClass('active');
                    })
                    .first()
                    .data('lead-status-id');
                d.assigned_to = filter_assigned_to.val();
                d.created_at = filter_created_at.val();
                d.created_at_start = created_at_start.val();
                d.created_at_end = created_at_end.val();
                d.show_deleted = filter_show_deleted.is(':checked');

                const urlParams = new URLSearchParams(window.location.search);
                d.won_at_start = urlParams.get('won_at_start');
                d.won_at_end = urlParams.get('won_at_end');
                d.filter_lead_source_id = filter_lead_source_id.val();
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
        drawCallback: function (settings) {
            var json = this.api().ajax.json();
            changeNewLeadCount(json?.new_leads_count);
        },
        initComplete: function (settings, json) {
            $('.leads-table-wrapper div.dataTables_length select').addClass(
                'form-select-sm');
            $('.leads-table-wrapper div.dataTables_filter').addClass(
                'dataTables_filter_sm');
            $('.add-new-lead-btn').tooltip('dispose');
            $('.export-to-csv-btn').tooltip('dispose');
            $('.add-new-lead-btn').tooltip({ title: 'Add lead', placement: "bottom", });
            $('.export-to-csv-btn').tooltip({ title: 'Export to CSV', placement: "bottom", });
        },
        order: [
            [7, 'DESC'],
            [6, 'DESC']
        ],
        columns: [
            { data: 'lead_source', name: 'lead_source.title' },
            { data: 'full_name' },
            { data: 'email', visible: !isMarketing },
            { data: 'mobile', visible: !isMarketing },
            // { data: 'email' },
            // { data: 'mobile' },
            { data: 'lead_status', name: 'lead_status.title' },
            { data: 'assignee', name: 'assignee.first_name' },
            { data: 'created_at' },
            { data: 'updated_at' },
            { data: '', searchable: false, }, // actions
        ],
        columnDefs: [
            // full name
            {
                // User full name and username
                targets: 1,
                responsivePriority: 1,
                render: function (data, type, full, meta) {
                    var $name = full['firstname'],
                        $email = full['email'],
                        $image = full['avatar'];
                    if ($image) {
                        // For Avatar image
                        var $output =
                            '<img src="' + assetPath + 'images/avatars/' + $image + '" alt="Avatar" height="32" width="32">';
                    } else {
                        // For Avatar badge
                        var stateNum = Math.floor(Math.random() * 6) + 1;
                        var states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
                        var $state = states[stateNum],
                            $name = full['firstname'] + (full['lastname'] ? (" " + full['lastname']) : ""),
                            $initials = $name.match(/\b\w/g) || [];
                        $initials = (($initials.shift() || '') + ($initials.pop() || '')).toUpperCase();
                        $output = '<span class="avatar-content">' + $initials + '</span>';
                    }
                    var colorClass = $image === '' ? ' bg-light-' + $state + ' ' : '';
                    // Creates full output for row
                    var $row_output =
                        '<div class="d-flex justify-content-left align-items-center">' +
                        '<div class="d-flex flex-column">' +
                        $name +
                        '</div>' +
                        '</div>';
                    return $row_output;
                }
            },
            // Actions
            {
                targets: 8,
                responsivePriority: 1,
                title: 'Actions',
                orderable: false,
                render: function (data, type, full, meta) {
                    let delete_lead_url = route("leads.destroy", full['id']);
                    let followUpExceptStatuses = ['Won', 'Not Suitable'];
                    let lead_status = full.lead_status.title;
                    let showFollowUpBtn = !isSuper && !isMarketing && full?.deleted_at === null && !followUpExceptStatuses.includes(full?.lead_status?.title);

                    let lead_detail_btn = `<button class="btn btn-sm btn-icon btn-flat-secondary leadDetailBtn" data-id="${full['id']}" data-bs-toggle="modal" data-bs-target="#leadDetailModal" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Detail" data-bs-original-title="Detail" >${feather.icons['eye'].toSvg({ class: 'font-medium-3' })}</button>`;
                    let lead_edit_btn = `<button data-id="${full['id']}" class="btn btn-sm btn-icon btn-flat-info leadEditBtn" data-bs-toggle="modal" data-bs-target="#editLeadModal" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit" data-bs-original-title="Edit">${feather.icons['edit'].toSvg({ class: 'font-medium-3' })}</button>`;
                    let lead_notes_btn = `<button data-id="${full['id']}" class="btn btn-sm btn-icon btn-flat-primary notesBtn" data-bs-toggle="modal" data-bs-target="#notesModal" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Notes" data-bs-original-title="Notes" >${feather.icons['file-plus'].toSvg({ class: 'font-medium-3' })}</button>`;
                    let lead_delete_btn = `<form action="${delete_lead_url}" method="POST" class="deleteLeadForm"><input type="hidden" name="_token" value="${csrf_token()}" ><input type="hidden" name="_method" value="DELETE" ><button type="submit" class="btn btn-sm btn-icon btn-flat-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete" data-bs-original-title="Delete" >${feather.icons['trash'].toSvg({ class: 'font-medium-3' })}</button></form>`;
                    let lead_restore_btn = `<button data-id="${full['id']}" class="btn btn-sm btn-icon btn-flat-primary lead_restore_button" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Restore" data-bs-original-title="Restore" >${feather.icons['refresh-cw'].toSvg({ class: 'font-medium-3' })}</button>`;
                    let lead_force_delete_btn = `<button data-id="${full['id']}" class="btn btn-sm btn-icon btn-flat-danger lead_force_delete_button" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete permanently" data-bs-original-title="Delete permanently" >${feather.icons['trash'].toSvg({ class: 'font-medium-3' })}</button>`;
                    let followUpBtn = `<button data-lead-id="${full['id']}" class="btn btn-sm btn-icon btn-flat-success leadFollowUpBtn" data-bs-target="#addFollowUpModal" data-bs-toggle="modal" ><span title="Follow Up" data-bs-original-title="Follow Up" data-bs-toggle="tooltip" data-bs-placement="bottom" >${feather.icons['arrow-up-circle'].toSvg({ class: 'font-medium-3' })}</span></button>`;

                    var marketing_mail_reminder_status = full['marketing_mail_reminder_status'];
                    if(marketing_mail_reminder_status){
                        var marketing_mail = `<button type="button" title="Marketing email enabled" data-marketingstatus="${full['marketing_mail_reminder_status']}" data-leadid="${full['id']}" class="btn btn-sm btn-icon btn-flat-secondary marketing_mail_cls" data-bs-toggle="modal" data-bs-target="#marketing_mail_enable_model">
                                                ${feather.icons['check-circle'].toSvg({ class: 'font-medium-3' })}
                                            </button>`;
                    }else{
                        var  marketing_mail = `<a class="btn btn-sm btn-icon btn-flat-secondary marketing_mail_cls" data-marketingstatus="${full['marketing_mail_reminder_status']}" data-leadid="${full['id']}" title="Marketing email disabled" data-bs-toggle="modal" data-bs-target="#marketing_mail_enable_model">
                                            ${feather.icons['slash'].toSvg({ class: 'font-medium-3' })}
                                        </a>`;
                    }

                    let buttons = `<div
                        class="d-flex lead-list__action_buttons"
                        role="group"
                        aria-label="Basic mixed styles example"
                        >
                    ${lead_detail_btn}
                    ${full['deleted_at'] ? '' : lead_edit_btn}
                    ${full['deleted_at'] ? '' : lead_notes_btn}
                    ${showFollowUpBtn ? followUpBtn : ''}
                    ${full['deleted_at'] ? '' : lead_delete_btn}
                    ${full['deleted_at'] ? lead_restore_btn : ''}
                    ${full['deleted_at'] ? lead_force_delete_btn : ''}
                    ${full['deleted_at'] || isMarketing || (lead_status != 'Contacted' && lead_status != 'Lost' && lead_status != 'Future Follow Up' && lead_status != 'Estimated') ? '' : marketing_mail}
                    </div>`;

                    return (buttons);
                }
            },
            // Status
            {
                targets: 4,
                responsivePriority: 2,
                render: function (data, type, full, meta) {
                    return `<span class='badge ${full?.lead_status?.css_class ?? ''} bg-gradient' >${full?.lead_status?.title ?? ''}</span>`;
                }
            },
        ],
        dom:
            // '<"d-flex justify-content-between align-items-center header-actions mx-2 row mt-75"r' +
            '<"d-flex justify-content-between align-items-center header-actions mx-1 mt-50 row"r' +
            '<"col-sm-12 col-lg-4 d-flex justify-content-center justify-content-lg-start" l>' +
            // '<"col-sm-12 col-lg-8 ps-xl-75 ps-0"<"dt-action-buttons d-flex align-items-center justify-content-center justify-content-lg-end flex-lg-nowrap flex-wrap"<"me-1"f>B>>' +
            '<"col-sm-12 col-lg-8 ps-xl-75 ps-0"<"dt-action-buttons d-flex align-items-center justify-content-center justify-content-lg-end flex-lg-nowrap flex-wrap"<"me-1"B>f>>' +
            '>t' +
            '<"d-flex justify-content-between mx-2 row mb-1"' +
            '<"col-sm-12 col-md-6"i>' +
            '<"col-sm-12 col-md-6"p>' +
            '>',
        // Buttons with Dropdown
        buttons: buttons,
        // For responsive popup
        responsive: {
            details: {
                display: $.fn.dataTable.Responsive.display.modal({
                    header: function (row) {
                        var data = row.data();
                        return 'Details of ' + data['firstname'];
                    }
                }),
                type: 'column',
                renderer: function (api, rowIdx, columns) {
                    var data = $.map(columns, function (col, i) {
                        // return col.columnIndex !== 13 // ? Do not show row in modal popup if title is blank (for check box)
                        //   ? '<tr data-dt-row="' +
                        return '<tr data-dt-row="' +
                            col.rowIdx +
                            '" data-dt-column="' +
                            col.columnIndex +
                            '">' +
                            '<td>' +
                            col.title +
                            ':' +
                            '</td> ' +
                            '<td>' +
                            col.data +
                            '</td>' +
                            '</tr>';
                        // '</tr>'
                        // : '';
                    }).join('');
                    return data ? $('<table class="table"/>').append('<tbody>' + data + '</tbody>') : false;
                }
            }
        },
    });

    $(document).on('click','.marketing_mail_cls',function (e){
        e.preventDefault();
        var marketing_mail_reminder_status = $(this).data('marketingstatus');
        $('#marketing_mail_reminder_status').val(marketing_mail_reminder_status);
        var lead_id = $(this).data('leadid');
        $('#leadid').val(lead_id);
        if(marketing_mail_reminder_status){
            $(".lead_message").html('Want to <span style="color: red">Disabled</span> marketing email reminders!');
        }else{
            $(".lead_message").html('Want to <span style="color: green">Enabled</span> marketing email reminders!');
        }
    });

    $(document).on('click','#mail_status_change',function (e){
        var marketing_mail_reminder_status = $('#marketing_mail_reminder_status').val();
        var lead_id = $('#leadid').val();
        $.ajax({
            url: route('leads.marketing_mail_reminder_status'),
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
            },
            data: {lead_id:lead_id,marketing_mail_reminder_status:marketing_mail_reminder_status},
            success: function(data) {
                toastr.success(null, data.message);
                $('.lead_tabel').DataTable().ajax.reload(null, false);
                $('#marketing_mail_enable_model').modal('hide');
            },
            error: function(data) {
                toastr.error(null, "Something went wrong!");
            }
        });
    });

    $(document).on('click','#marketing_mail_enable_close',function(e){
        $('#marketing_mail_enable_model').modal('hide');
    });

    $(leadsDataTable).on("submit", ".deleteLeadForm", function (deleteLeadFormSubmitEvent) {
        deleteLeadFormSubmitEvent.preventDefault();
        let deleteLeadForm = $(this);
        Swal.fire({
            title: 'Are you sure you want to delete this lead?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-outline-danger ms-1'
            },
            buttonsStyling: false
        }).then(function (result) {
            if (result.value) {
                var row = $(deleteLeadForm).closest('tr');
                // make an AJAX request to delete the record
                $.ajax({
                    url: $(deleteLeadForm).attr('action'),
                    method: 'POST',
                    data: $(deleteLeadForm).serialize(),
                    success: function (response) {
                        toastr.success(null, "Lead deleted successfully!");
                        redrawLeadsTable(false);
                    },
                    error: function (xhr, status, error) {
                        toastr.error(xhr.responseJSON?.message ?? null, error);
                        redrawLeadsTable(false);
                    }
                });

            }
        });

    });


    $(leadsDataTable).on("click", ".lead_force_delete_button", function (e) {
        e.preventDefault();
        let lead_id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure you want to delete this lead permanently?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete permanently!',
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-outline-danger ms-1'
            },
            buttonsStyling: false
        }).then(function (result) {
            if (result.value) {
                $.ajax({
                    url: route('leads.force-delete', lead_id),
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
                    },
                    success: function (response) {
                        toastr.success(null, "Lead permanently deleted successfully!");
                        redrawLeadsTable(false);
                    },
                    error: function (xhr, status, error) {
                        toastr.error(xhr.responseJSON?.message ?? null, error);
                        redrawLeadsTable(false);
                    }
                });
            }
        });
    });

    $('.user-list-table').on('click', '.lead_restore_button', function (e) {
        Swal.fire({
            title: 'Are you sure you want to restore this lead?',
            text: "You can revert this by deleting lead again!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Restore it!',
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-outline-danger ms-1'
            },
            buttonsStyling: false
        }).then(function (result) {
            if (result.value) {
                $.ajax({
                    url: route('leads.restore', $(e.target).closest('button').data('id')),
                    method: 'PUT',
                    data: function (d) {
                        d._method = 'PUT';
                        d._token = csrf_token();
                    },
                    success: function (response) {
                        toastr.success(null, "Lead restored successfully!");
                        redrawLeadsTable(false);
                    },
                    error: function (xhr, status, error) {
                        toastr.error(xhr.responseJSON?.message ?? null, error);
                        redrawLeadsTable(false);
                    }
                });
            }
        });
    });
}

$(editLeadModal).on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    var lead_edit_url = route("leads.edit", id);
    $.ajax({
        url: lead_edit_url,
        type: 'GET',
        dataType: 'json',
        success: function (data, status, xhr) {

            var lead_update_url = route("leads.update", id);
            $(editLeadForm).attr('action', lead_update_url);

            $('#edit_id').val(data?.lead?.id);
            $("#edit_firstname").val(data?.lead?.firstname);
            $("#edit_lastname").val(data?.lead?.lastname);
            /*$("#edit_email").val(data?.lead?.email);*/
            $("#edit_mobile").val(data?.lead?.mobile);
            $("#edit_country_id").val(data?.lead?.country_id).trigger('change');
            $("#edit_requirement").val(data?.lead?.requirement ?? '');
            // $("#edit_project_budget").val(data?.lead?.project_budget);
            $("#edit_prj_budget").val(data?.lead?.prj_budget ?? "").trigger('change');
            $("#edit_currency_id").val(data?.lead?.currency_id ?? "").trigger('change');
            $("#edit_lead_source_id").val(`${data?.lead?.lead_source_id}`).trigger('change');
            // $("#edit_lead_type_id").val(`${data?.lead?.lead_type_id}`).trigger('change');
            $("#edit_lead_status_id").val(`${data?.lead?.lead_status_id}`).trigger('change');
            $("#edit_assigned_to").val(`${data?.lead?.assigned_to}`).trigger('change');
            // $("#edit_skype_id").val(data?.lead?.skype_id);
            // $("#edit_linkdin_url").val(data?.lead?.linkdin_url);


            if (data.lead && typeof data.lead === 'object') {
                Object.keys(data.lead).forEach(property => {
                    $('#edit_' + property).val(data.lead[property]);
                    if ($('#edit_' + property).hasClass('select2')) {
                        $('#edit_' + property).trigger('change');
                    } else if ($('#edit_' + property).hasClass(
                        'form-check-input')) {
                        $('#edit_' + property).prop('checked', data.lead[
                            property]);
                    }else if ($('#edit_' + property).hasClass('dt-email')) {
                        $('#edit_' + property).empty();
                        var client_email = data.lead[property].split(',');
                        if (Array.isArray(client_email) && client_email.length > 1) {
                            client_email.forEach(email => {
                                $('#edit_' + property).append(new Option(email, email, true, true));
                            });
                        }else {
                            $('#edit_' + property).append(new Option(client_email[0], client_email[0], true, true));
                        }
                    }
                });
            }

        },
        error: function (xhr, status, error) {
            toastr.error(xhr.responseJSON?.message ?? null, error);
        }
    });
});

$(notesModal).on('show.bs.modal', function (event) {
    var notesBtn = event?.relatedTarget;
    var lead_id = $(notesBtn)?.data('id');
    $(this).find('#lead_id').val(lead_id);
    var addLeadNoteUrl = route("leads.notes.store", lead_id);
    $(addNoteForm).attr('action', addLeadNoteUrl);
    fillNotesList();
});

$(notesModal).on('hide.bs.modal', function (event) {
    resetNotesModalToDefault();
});

$('#notesModal').on('click', '.prevent-clicks', function (e) {
    e.preventDefault();
    e.stopPropagation();
});

$('#notesModal').on('click', '.editNoteBtn', function (e) {
    let leadNoteId = $(this).closest('.note_card').data('lead-note-id');
    let leadNoteNote = $(`#note_${leadNoteId}`).text();
    let leadId = $(notesModal).find('#lead_id').val();
    let updateLeadRoute = route('leads.notes.update', [leadId, leadNoteId]);
    $(addNoteForm).attr('action', updateLeadRoute);
    $('#notesModal').find('[name="note"]').val(leadNoteNote);
    $('#notesModal').find('#note_submit_btn').html('Update');
    $('#notesModal').find('#note_reset_btn').html('Cancel');
    $('#notesModal').find('#notes_method').val('PUT');
    if ($('#notesModal').find('[name="note"]').hasClass("error")) {
        $('#notesModal').find('[name="note"]').removeClass('error');
    };
    if ($('#notesModal').find('#notes-error')) {
        $('#notesModal').find('#notes-error').remove();
    };
});

$('#notesModal').on('click', '.deleteNoteBtn', function (e) {
    let leadNoteCard = $(this).closest('.note_card');
    let leadNoteId = leadNoteCard.data('lead-note-id');
    let leadId = $(notesModal).find('#lead_id').val();
    Swal.fire({
        title: 'Are you sure you want to delete this lead note?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-outline-danger ms-1'
        },
        buttonsStyling: false
    }).then(function (result) {
        if (result.value) {
            $.ajax({
                url: route('leads.notes.destroy', [leadId, leadNoteId]),
                method: 'DELETE',
                success: function (response) {
                    toastr.success(null, "Lead note deleted successfully!");
                    fillNotesList();
                },
                error: function (xhr, status, error) {
                    toastr.error(xhr.responseJSON?.message ?? null, error);
                    fillNotesList();
                }
            });
        }
    });
});

$('#leadDetailModal').on('click', '.deleteAttachmentBtn', function (e) {
    let attachment = $(this).data('id');
    let lead = $('#notesModal').find('#lead_id').val();
    let leadAttachmentDiv = $(this).closest('.leadAttachment');
    Swal.fire({
        title: 'Are you sure you want to delete this attachment?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-outline-danger ms-1'
        },
        buttonsStyling: false
    }).then(function (result) {
        if (result.value) {
            $.ajax({
                url: route('leads.attachments.destroy', { lead, attachment }),
                method: 'DELETE',
                success: function (response) {
                    toastr.success(null, "Lead attachment deleted successfully!");
                    leadAttachmentDiv.remove();
                },
                error: function (xhr, status, error) {
                    toastr.error(xhr.responseJSON?.message ?? null, error);
                }
            });
        }
    });
});

$('#notesModal').on('click', '#note_reset_btn', function (e) {
    resetNotesModalToDefault();
});

function resetNotesModalToDefault() {
    let lead_id = $('#notesModal').find('#lead_id').val();
    $(addNoteForm).attr('action', route('leads.notes.store', [lead_id]));
    $('#notesModal').find('[name="note"]').val("");
    $('#notesModal').find('#note_submit_btn').text('Submit');
    $('#notesModal').find('#note_reset_btn').text('Reset');
    $('#notesModal').find('#notes_method').val('');
    if ($('#notesModal').find('[name="note"]').hasClass("error")) {
        $('#notesModal').find('[name="note"]').removeClass('error');
    };
    if ($('#notesModal').find('#notes-error')) {
        $('#notesModal').find('#notes-error').remove();
    };
    $('#notesModal').find('#notes').attr('style', '');
}

$(leadDetailModal).on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    $('#notesModal').find('#lead_id').val(id);
    var lead_show_url = route("leads.show", id);
    $.ajax({
        url: lead_show_url,
        type: 'GET',
        dataType: 'json',
        success: function (data, status, xhr) {
            $('#detail_id').val(data?.lead?.id);
            $("#detail_firstname").html(data?.lead?.firstname);
            $("#detail_lastname").html(data?.lead?.lastname);
            $("#detail_mobile").html(data?.lead?.mobile);
            $("#detail_email").html(data?.lead?.email);
            $("#detail_country").html(`${data?.lead?.country_rel?.name ?? ''}`);
            $("#detail_requirement").html(data?.lead?.requirement_with_line_breaks ?? (data?.lead?.requirement ?? ''));
            let prj_budget = prjBudgetObj[data?.lead?.prj_budget] ?? null;
            prj_budget = prj_budget ? prj_budget.replaceAll(':currency_symbol:', data?.lead?.currency?.symbol ?? '') : prj_budget;
            $("#detail_project_budget").html(prj_budget ?? "");
            $("#detail_lead_source").html(`${data?.lead?.lead_source?.title ?? ''}`);
            $("#detail_lead_status").html(`${data?.lead?.lead_status?.title ?? ''}`).removeClass().addClass(`badge ${data?.lead?.lead_status?.css_class ?? ''}`);
            $("#detail_assignee").html(`${data?.lead?.assignee?.full_name ?? ''}`);
            $("#detail_created_at").html(`${data?.lead?.created_at ?? ''}`);
            $("#detail_updated_at").html(`${data?.lead?.updated_at ?? ''}`);
            $('#detail_attachments').html(buildDetailAttachments(data?.lead?.attachments ?? []));
        },
        error: function (xhr, status, error) {
            toastr.error(xhr.responseJSON?.message ?? null, error);
        }
    });
});

$(editLeadModal).on("hide.bs.modal", function (editLeadModalHideEvent) {
    $("#edit_lead_source_id").val("").trigger("change");
    $("#edit_lead_status_id").val("").trigger("change");
    $("#edit_assigned_to").val("").trigger("change");
});
// Form Validation
if (newLeadForm.length) {
    newLeadForm.validate({
        errorClass: 'error',
        rules: {
            'firstname': {
                required: true
            },
            'lastname': {
                required: true
            },
            'email[]': {
                required: false,
                validEmails: true,
            },
            'lead_status_id': {
                required: true,
            },
            'lead_source_id': {
                required: true,
            },
            'assigned_to': {
                required: true,
            },
            'attachments[]': {
                filesizeMax: 1024 * 1024,
                maxFiles: 5,
                extensionArr: 'pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,jpg,jpeg,png,gif,bmp,webp,svg,tiff'
            },
            'currency_id': {
                required: function (el) {
                    return $('#prj_budget').val() != '';
                }
            }
        },
        messages: {
            'firstname': {
                required: "Please enter first name"
            },
            'lastname': {
                required: "Please enter last name"
            },
            'email[]': {
                required: "Please enter email",
                validEmails: "Please enter a valid email",
            },
            'lead_status_id': {
                required: "Please select a lead status",
            },
            'lead_source_id': {
                required: "Please select a lead source",
            },
            'assigned_to': {
                required: "Please select an assignee",
            },
            'currency_id': {
                required: "Please select a currency"
            }
        },
        submitHandler: function (form, event) {
            event.preventDefault();
            $('#addLeadSubmitBtn').prop('disabled', true);
            $('#addLeadModal > .modal-dialog').block({
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
                success: function (response) {
                    if (response.errors) {
                        $(form).validate().showErrors(response.errors);
                    } else {
                        newLeadModal.modal('hide');
                        toastr.success(null, "Lead added successfully!");
                        if (leadsTable) {
                            redrawLeadsTable();
                        }
                        form.reset();
                        $(form).find(".select2").trigger('change');
                    }
                    $('#addLeadModal > .modal-dialog').unblock();
                    $('#addLeadSubmitBtn').prop('disabled', false);
                },
                error: function (xhr, status, error) {
                    $('#addLeadModal > .modal-dialog').unblock();
                    $('#addLeadSubmitBtn').prop('disabled', false);
                    if (xhr.status == 422) {
                        var errors = JSON.parse(xhr.responseText).errors;
                        $('#addLeadModal #email-errors').text(errors[0]);
                        $(form).validate().showErrors(JSON.parse(xhr.responseText).errors);
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
            if (element.attr("name") == "currency_id") {
                error.insertAfter($('#prj_budget').parent());
            } else if (element.attr("name") == "prj_budget") {
                error.insertAfter($('#prj_budget').parent());
            } else if (element.hasClass('select2') && element.next('.select2-container').length) {
                error.insertAfter(element.next('.select2-container'));
            } else {
                error.insertAfter(element);
            }
        }
    });
}
if (editLeadForm.length) {
    editLeadForm.validate({
        errorClass: 'error',
        rules: {
            'firstname': {
                required: true
            },
            'lastname': {
                required: true
            },
            'email[]': {
                required: false,
                validEmails: true,
            },
            'lead_status_id': {
                required: true,
            },
            'lead_source_id': {
                required: true,
            },
            'assigned_to': {
                required: true,
            },
            'attachments[]': {
                filesizeMax: 1024 * 1024,
                maxFiles: 5,
                extensionArr: 'pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,jpg,jpeg,png,gif,bmp,webp,svg,tiff'
            },
            'currency_id': {
                required: function (el) {
                    return $('#edit_prj_budget').val() != '';
                }
            }
        },
        messages: {
            'firstname': {
                required: "Please enter first name"
            },
            'lastname': {
                required: "Please enter last name"
            },
            'email[]': {
                required: "Please enter email",
                validEmails: "Please enter a valid email",
            },
            'lead_status_id': {
                required: "Please select a lead status",
            },
            'lead_source_id': {
                required: "Please select a lead source",
            },
            'assigned_to': {
                required: "Please select an assignee",
            },
            'currency_id': {
                required: "Please select a currency"
            }
        },
        submitHandler: function (form, event) {
            event.preventDefault();
            $('#editLeadSubmitBtn').prop('disabled', true);
            $('#editLeadModal > .modal-dialog').block({
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
                data: new FormData(form),
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.errors) {
                        $(form).validate().showErrors(response.errors);
                    } else {
                        editLeadModal.modal('hide');
                        toastr.success(null, "Lead updated successfully!");
                        if (leadsTable) {
                            redrawLeadsTable();
                        }
                        form.reset();
                        $(form).find(".select2").trigger('change');
                        if (response.show_confetti) {
                            /* Animation */
                            showConfetti();

                            /* Random modal open  */
                            $('.user_name').text(response.user_name);
                            $('.customer_name').text(response.customer_name);
                            const modal_arr = ["modal_one", "modal_two", "modal_third", "modal_fourth", "modal_fifth","modal_six", "modal_seven", "modal_eight", "modal_nine", "modal_ten"];
                            const random = Math.floor(Math.random() * modal_arr.length);
                            var congrats_model = modal_arr[random];
                            $("#"+congrats_model).modal('show');
                            $('body').addClass('modal-active');
                        }
                    }
                    $('#editLeadSubmitBtn').prop('disabled', false);
                    $('#editLeadModal > .modal-dialog').unblock();
                },
                error: function (xhr, status, error) {
                    $('#editLeadSubmitBtn').prop('disabled', false);
                    $('#editLeadModal > .modal-dialog').unblock();
                    if (xhr.status == 422) {
                        $(form).validate().showErrors(JSON.parse(xhr.responseText).errors);
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
            if (element.attr("name") == "currency_id" || element.attr("name") == "prj_budget") {
                error.insertAfter($('#edit_prj_budget').parent());
            } else if (element.hasClass('select2') && element.next('.select2-container').length) {
                error.insertAfter(element.next('.select2-container'));
            } else {
                error.insertAfter(element);
            }
        }
    });
}

// Form Validation
if (addNoteForm.length) {
    addNoteForm.validate({
        errorClass: 'error',
        rules: {
            note: {
                required: true
            },
        },
        messages: {
            note: {
                required: "Please enter a note"
            },
        },
        submitHandler: function (form, event) {
            event.preventDefault();
            $('#note_submit_btn').prop('disabled', true);
            $('#addNoteForm').block({
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
                success: function (response, status, xhr) {
                    if (response.errors) {
                        $(form).validate().showErrors(response.errors);
                    } else {
                        if (xhr.status === 200) {
                            toastr.success(null, "Note updated successfully!");
                            form.reset();
                            resetNotesModalToDefault();
                            fillNotesList();
                        } else if (xhr.status === 201) {
                            toastr.success(null, "Note added successfully!");
                            form.reset();
                            prependLeadNote(response?.leadNote);
                        }
                    }
                    $('#note_submit_btn').prop('disabled', false);
                    $('#addNoteForm').unblock();
                    $(notesModal).modal('hide');
                },
                error: function (xhr, status, error) {
                    $('#note_submit_btn').prop('disabled', false);
                    $('#addNoteForm').unblock();
                    if (xhr.status == 422) {
                        $(form).validate().showErrors(JSON.parse(xhr?.responseText)?.errors);
                        resetNotesModalToDefault();
                    } else {
                        resetNotesModalToDefault();
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
}

function fillNotesList() {
    let lead_id = $(notesModal).find('#lead_id').val();
    var getLeadNotesUrl = route("leads.notes.index", lead_id);
    $.ajax({
        url: getLeadNotesUrl,
        type: 'GET',
        dataType: 'json',
        success: function (data, status, xhr) {
            if (data.success) {
                var leadNotes = data?.leadNotes;
                if (leadNotes === null || leadNotes === undefined || leadNotes === '' || leadNotes?.length === 0) {
                    $(lead_notes_list).html("<div class='text-center' >No notes found!</div>");
                }
                if (leadNotes.length) {
                    var leadNoteHtml = "";
                    for (const leadNote of leadNotes) {
                        leadNoteHtml += buildLeadNoteHtml(leadNote);
                    }
                    $(lead_notes_list).html(leadNoteHtml);
                    return;
                }
            }

        },
        error: function (xhr, status, error) {
            toastr.error(xhr.responseJSON?.message ?? null, error);
        }
    });
}

function buildLeadNoteHtml(leadNote) {
    let edited = leadNote.last_edited_at;
    let editedIndicator = edited ? `<span class="float-end text-warning prevent-clicks" data-bs-toggle="tooltip" data-bs-placement="top" title="Edited by ${leadNote?.last_edited_by?.full_name} at ${leadNote.formatted_last_edited_at}">${feather.icons['info'].toSvg({ class: 'font-medium-2', style: 'margin-right: 4px; margin-bottom: 2px;' })}</span>` : ``;
    let deleteNoteBtn = `<a class="btn btn-icon p-0 me-1 ${leadNote.can_delete ? 'deleteNoteBtn' : ''}" href="#" title='Delete' >${feather.icons['trash'].toSvg({ class: 'font-small-4 text-danger' })}</a>`;

    return `<div class="card mb-0 note_card" data-lead-note-id='${leadNote?.encrypted_id}' >
    <div class="d-none" id='note_${leadNote?.encrypted_id}' >${leadNote?.note}</div>
    <div class="card-body p-0">
        <div class="card-text mb-0 d-flex" >
          <a class="btn btn-icon p-0 editNoteBtn me-1" href="#" title='Edit' >${feather.icons['edit'].toSvg({ class: 'font-small-4 text-primary' })}</a>
          ${deleteNoteBtn}
          <div class='flex-grow-1' >
            <span class="float-start break-white-space">${leadNote?.note_with_line_breaks ?? (leadNote?.note ?? '')}</span>
            <span class="badge float-end bg-primary prevent-clicks">${leadNote?.user?.full_name ?? ''} at ${leadNote?.formatted_created_at ?? ''}</span>
            ${editedIndicator}
          </div>
        </div>
    </div>
  </div>`;
}

function prependLeadNote(leadNote) {
    let noteCard = buildLeadNoteHtml(leadNote);
    if ($('.note_card').length) {
        $(lead_notes_list).prepend(noteCard);
    } else {
        $(lead_notes_list).html(noteCard);
    }
}

$(lead_status_filter_link).click(function (e) {
    let clickedLink = $(this);
    let { leadStatusId, classes } = clickedLink.data();
    $(".lead_status_filter_link").each(function (i) {
        let link = $(this);
        let { leadStatusId, classes } = link.data();
        link.removeClass('active');
        classes.toString().split(' ').map(function (cls) {
            link.removeClass(cls);
        });
    });
    clickedLink.addClass('active');
    classes.toString().split(' ').map(function (cls) {
        clickedLink.addClass(cls);
    })
    if (leadsTable) {
        leadsTable.column(4).visible(leadStatusId === 'deleted' || !leadStatusId);
    }
    redrawLeadsTable();
    clearQueryParams();
})

$(filter_assigned_to).change(function (e) {
    redrawLeadsTable();
    clearQueryParams();
});

$(filter_show_deleted).change(function (e) {
    redrawLeadsTable();
    clearQueryParams();
});

$(filter_lead_source_id).change(function (e) {
    redrawLeadsTable();
    clearQueryParams();
});

function redrawLeadsTable(paging = true) {
    if (leadsTable) {
        leadsTable.draw(paging);
    }
}

// Phone Number
$(document).on('input', '.dt-contact', function () {
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

function buildDetailAttachments(attachments = []) {
    let html = ``;
    attachments.forEach(function (attachment, index) {
        let filename = attachment?.filename ?? '';
        let download_name = filename.split('.').slice(0, -1).join('.');
        let download_url = attachment?.download_url ?? '';
        let file_id = attachment?.encrypted_id ?? '';
        html += `<div class='mb-0 leadAttachment' ><button class="btn btn-icon p-0 deleteAttachmentBtn" data-id="${file_id}" style="margin-right: 4px" >${feather.icons['x'].toSvg({ class: 'text-danger' })}</button><a href="${download_url}" download="${download_name}">${feather.icons['download'].toSvg({ class: 'text-info' })} ${filename}</a></div>`;
    })
    return html;
}

function changeNewLeadCount(newLeadCount) {
    newLeadCount = Number(newLeadCount);
    let { leadStatusId } = $('.nav-link.active').first().data();
    if (newLeadCount && leadStatusId != '1') {
        $('#newLeadCount').html(newLeadCount).removeClass('d-none');
    } else {
        $('#newLeadCount').addClass('d-none');
    }
}

/**
 * This function shows a confetti animation on screen. This function
 * makes use of canvas-confetti library V1.6
 * (https://github.com/catdad/canvas-confetti) So Please import
 * corresponding js files in proper location in the HTML document.
 *
 * @returns void
 * @author Krunal Shrimali
 */
function showConfetti() {
    let duration = 5 * 1000;
    let animationEnd = Date.now() + duration;
    let defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };

    function randomInRange(min, max) {
        return Math.random() * (max - min) + min;
    }

    let interval = setInterval(function () {
        let timeLeft = animationEnd - Date.now();

        if (timeLeft <= 0) {
            return clearInterval(interval);
        }

        let particleCount = 50 * (timeLeft / duration);
        confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 } }));
        confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 } }));
    }, 250);
}

// initialize date range picker for custom created at filter
$('.input-daterange2').datepicker(
    {
        format: 'dd/mm/yyyy',
        autoclose: true,
        inputs: $('.input-daterange2 .form-control'),
        container: '#exportLeadsModal'
    }
).on('hide', function (e) {
    e.stopPropagation();
});

$('form#exportLeadsForm').on('submit', function (e) {
    e.preventDefault();
    let $form = this;
    $($form).find('#exportLeadsSubmitBtn').prop('disabled', true);
    $('#exportLeadsModal > .modal-dialog .modal-content').block({
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
            link.download = filename || 'leads.csv';

            // Trigger a click event to initiate the download
            link.click();

            // Clean up the temporary anchor element
            link.remove();

            $('#exportLeadsModal').modal('hide');
            $('#exportLeadsModal > .modal-dialog .modal-content').unblock();
            $('#exportLeadsSubmitBtn').prop('disabled', false);
            toastr.success(null, "Leads exported successfully!");
        },
        error: function (xhr, status, error) {
            $('#exportLeadsModal > .modal-dialog .modal-content').unblock();
            $('#exportLeadsSubmitBtn').prop('disabled', false);
            Swal.fire({
                title: 'An error occurred',
                text: error,
                icon: 'error',
            });
        }
    });

});

$('#exportLeadsModal').on('hide.bs.modal', function (e) {
    $('form#exportLeadsForm').get(0).reset();
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

$('#leadFilters').on('change', '#filter_created_at', function (e) {
    let filter_created_at_value = $(this).val();
    if (filter_created_at_value === 'custom') {
        $('.filter_date_range').show();
    } else {
        $('.filter_date_range').hide();
    }
    redrawLeadsTable();
    clearQueryParams();
});

$('#leadFilters').on('change', '.input-daterange input', function (e) {
    redrawLeadsTable();
    clearQueryParams();
});

$('body').on('hide.bs.collapse', '#leadFilters', function (e) {
    filter_created_at.val('').trigger('change');
});

function clearQueryParams() {
    var url = new URL(window.location.href);
    var params = new URLSearchParams(url.search);
    params.delete('won_at_start');
    params.delete('won_at_end');
    params.delete('lead_status_id');
    var newUrl = url.origin + url.pathname;
    history.replaceState(null, '', newUrl);
    return newUrl;
}

const $addFollowUpModal = $('#addFollowUpModal');

// const $addFollowUpCallModal = $('#addFollowUpCallModal');
// const $addFollowUpEmailModal = $('#addFollowUpEmailModal');
// const $sales_person_phone = $addFollowUpCallModal.find('select[name="sales_person_phone[]"]');
// const $to = $addFollowUpEmailModal.find('select[name="to[]"]');
// const $bcc = $addFollowUpEmailModal.find('select[name="bcc[]"]');
// const $contentQuill = $addFollowUpEmailModal.find('#contentQuill');
// const $content = $addFollowUpEmailModal.find('input[name=content]');
// var contentQuill = new Quill($contentQuill.get(0), {
//     theme: 'snow',
//     format: {
//         fontFamily: 'Public Sans'
//     }
// });

// var $follow_up_at;
// var followUpAtFP;
// var followUpValidator;

// function copyContentToInput() {
//     $content.val(contentQuill.root.innerHTML);
// }

// function resetAndDestroyFollowUpValidator($form = null) {
//     if (followUpValidator) {
//         followUpValidator.resetForm();
//         followUpValidator.destroy();
//         followUpValidator = undefined;
//     }
//     if ($form) $form.reset();
//     contentQuill.root.innerHTML = '';
//     copyContentToInput();
// }

// contentQuill.root.innerHTML = '';
// copyContentToInput();

// contentQuill.on('text-change', function (delta, oldDelta, source) {
//     copyContentToInput();
// });

// const handleAddFollowUpSubmit = function ($submitBtn, $modalDialog, type, $modal) {
//     return function (form, event) {
//         event.preventDefault();
//         $submitBtn.prop('disabled', true);
//         $modalDialog.block({
//             message: '<div class="spinner-border text-warning" role="status"></div>',
//             css: {
//                 backgroundColor: 'transparent',
//                 border: '0'
//             },
//             overlayCSS: {
//                 backgroundColor: '#fff',
//                 opacity: 0.8
//             }
//         });
//         $.ajax({
//             url: route('follow_ups.store'),
//             method: 'POST',
//             data: $(form).serialize(),
//             success: function (response) {
//                 if (response.errors) {
//                     $(form).validate().showErrors(response.errors);
//                 } else {
//                     $modal.modal('hide');
//                     toastr.success(null, "Follow up added successfully!");
//                 }
//                 $modalDialog.unblock();
//                 $submitBtn.prop('disabled', false);
//             },
//             error: function (xhr, status, error) {
//                 $modalDialog.unblock();
//                 $submitBtn.prop('disabled', false);
//                 if (xhr.status == 422) {
//                     $(form).validate().showErrors(JSON.parse(xhr.responseText)
//                         .errors);
//                 } else {
//                     Swal.fire({
//                         title: 'An error occurred',
//                         text: error,
//                         icon: 'error',
//                     });
//                 }
//             }
//         });
//     };
// }

// $to.select2({
//     tags: true,
//     dropdownParent: $addFollowUpEmailModal.get(0),
//     containerCssClass: 'select-sm',
// });

// $bcc.select2({
//     tags: true,
//     dropdownParent: $addFollowUpEmailModal.get(0),
//     containerCssClass: 'select-sm',
// });

// $sales_person_phone.select2({
//     tags: true,
//     dropdownParent: $addFollowUpCallModal.get(0),
//     containerCssClass: 'select-sm',
// });


// $addFollowUpCallModal.on('show.bs.modal', function (e) {
//     $addFollowUpModal.modal('hide');
//     let lead_id = $addFollowUpModal.find('input[name=lead_id]').val();
//     $(this).find('input[name=lead_id]').val(lead_id);
//     followUpValidator = $('#addFollowUpCallForm').validate({
//         ignore: [],
//         rules: {
//             "lead_id": {
//                 required: true,
//             },
//             "sales_person_phone[]": {
//                 required: true,
//                 validPhones: true,
//             },
//             follow_up_at: {
//                 required: true,
//             },
//         },
//         messages: {
//             "lead_id": {
//                 required: "Please select lead",
//             },
//             "sales_person_phone[]": {
//                 required: 'Please enter phone number',
//                 validPhones: "Please enter valid phone numbers",
//             },
//             follow_up_at: {
//                 required: "Please select date & time",
//             },
//         },
//         submitHandler: handleAddFollowUpSubmit($('#addFollowUpCallSubmitBtn'), $('#addFollowUpCallModal > .modal-dialog .modal-content'), 'call', $addFollowUpCallModal),
//         errorPlacement: function (error, element) {
//             if (element.hasClass('select2')) {
//                 error.appendTo(element.parent())
//             } else if (element.attr('name') === 'content') {
//                 error.insertAfter(element);
//             } else if (element.attr('type') === 'hidden') {
//                 error.appendTo('#addFollowUpCallForm');
//             } else {
//                 error.insertAfter(element);
//             }
//         }
//     });
//     $follow_up_at = $(this).find('input[name=follow_up_at]');
//     followUpAtFP = $follow_up_at.flatpickr({
//         static: true,
//         enableTime: true,
//         minDate: 'today',
//         time_24hr: false,
//         minuteIncrement: 15,
//         dateFormat: "d/m/Y h:i K",
//     });
//     $sales_person_phone.empty();
//     $.ajax({
//         url: route('leads.follow-up-details', lead_id),
//         method: 'GET',
//         data: { _token: $('meta[name=csrf-token]').attr('content') },
//         success: function (response) {
//             if (response?.sales_person_phone) {
//                 $sales_person_phone.empty();
//                 $sales_person_phone.append(new Option(response?.sales_person_phone, response?.sales_person_phone, true, true))
//             }
//         },
//         error: function (xhr, status, error) {
//             Swal.fire({
//                 title: 'An error occurred',
//                 text: error,
//                 icon: 'error',
//             });
//         }
//     });
// });

// $addFollowUpEmailModal.on('show.bs.modal', function (e) {
//     $addFollowUpModal.modal('hide');
//     let lead_id = $addFollowUpModal.find('input[name=lead_id]').val();
//     $(this).find('input[name=lead_id]').val(lead_id);
//     $follow_up_at = $(this).find('input[name=follow_up_at]');
//     followUpValidator = $('#addFollowUpEmailForm').validate({
//         ignore: [],
//         rules: {
//             "lead_id": {
//                 required: true,
//             },
//             "to[]": {
//                 required: true,
//                 validEmails: true,
//             },
//             "bcc[]": {
//                 required: true,
//                 validEmails: true,
//             },
//             subject: {
//                 required: true,
//             },
//             content: {
//                 required: true,
//             },
//             follow_up_at: {
//                 required: true,
//             },
//         },
//         messages: {
//             "lead_id": {
//                 required: "Please select lead",
//             },
//             "to[]": {
//                 required: 'Please enter email',
//                 validEmails: "Please enter valid email",
//             },
//             "bcc[]": {
//                 required: 'Please enter email',
//                 validEmails: "Please enter valid email",
//             },
//             subject: {
//                 required: 'Please enter subject',
//             },
//             content: {
//                 required: 'Please enter content'
//             },
//             follow_up_at: {
//                 required: "Please select date & time",
//             },
//         },
//         submitHandler: handleAddFollowUpSubmit($('#addFollowUpEmailSubmitBtn'), $('#addFollowUpEmailModal > .modal-dialog .modal-content'), 'email', $addFollowUpEmailModal),
//         errorPlacement: function (error, element) {
//             if (element.hasClass('select2')) {
//                 error.appendTo(element.parent())
//             } else if (element.attr('name') === 'content') {
//                 error.insertAfter(element);
//             } else if (element.attr('type') === 'hidden') {
//                 error.appendTo('#addFollowUpEmailForm');
//             } else {
//                 error.insertAfter(element);
//             }
//         }
//     });
//     followUpAtFP = $follow_up_at.flatpickr({
//         static: true,
//         enableTime: true,
//         minDate: 'today',
//         time_24hr: false,
//         minuteIncrement: 15,
//         dateFormat: "d/m/Y h:i K",
//     });
//     $.ajax({
//         url: route('leads.follow-up-details', lead_id),
//         method: 'GET',
//         data: { _token: $('meta[name=csrf-token]').attr('content') },
//         success: function (response) {
//             if (response?.sales_person_email) {
//                 $bcc.empty();
//                 $bcc.append(new Option(response?.sales_person_email, response?.sales_person_email, true, true))
//             }
//             if (response?.lead_email) {
//                 $to.empty();
//                 $to.append(new Option(response?.lead_email, response?.lead_email, true, true))
//             }
//         },
//         error: function (xhr, status, error) {
//             Swal.fire({
//                 title: 'An error occurred',
//                 text: error,
//                 icon: 'error',
//             });
//         }
//     });
// });

// $addFollowUpCallModal.on('hide.bs.modal', function (e) {
//     $(this).find('input[name=lead_id]').val('');
//     resetAndDestroyFollowUpValidator($('form#addFollowUpCallForm').get(0));
// });

// $addFollowUpEmailModal.on('hide.bs.modal', function (e) {
//     $(this).find('input[name=lead_id]').val('');
//     resetAndDestroyFollowUpValidator($('form#addFollowUpEmailForm').get(0));
// });

$addFollowUpModal.on('show.bs.modal', function (e) {
    $('#followUpEmailLink')
        .attr(
            'href',
            route('leads.follow-ups.bulk-edit', {
                lead: e.relatedTarget?.dataset?.leadId ?? '',
                type: followUpTypes?.EMAIL ?? '',
            })
        );
    $('#followUpCallLink')
        .attr(
            'href',
            route('leads.follow-ups.bulk-edit', {
                lead: e.relatedTarget?.dataset?.leadId ?? '',
                type: followUpTypes?.CALL ?? '',
            })
        );
});

$addFollowUpModal.on('hide.bs.modal', function (e) {
    $('#followUpEmailLink')
        .attr('href', '#');
    $('#followUpCallLink')
        .attr('href', '#');
});


$('.close-modal').click(function () {
    $('#modal-container').addClass('out');
    $('body').removeClass('modal-active');
    $('.modal-backdrop').removeClass('show');
    $('.modal-backdrop').css("display", "none");
    $('.modal').hide();
});
