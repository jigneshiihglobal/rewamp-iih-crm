@extends('layouts.app')

@section('vendor-css')
    <link rel="stylesheet"
        href="{{ asset('app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css?v=' . config('versions.css')) }}">
@endsection

@section('page-css')
    <link rel="stylesheet"
        href="{{ asset('app-assets/css/plugins/forms/pickers/form-flat-pickr.css?v=' . config('versions.css')) }}">
@endsection

@section('page-vendor-js')
    <script src="{{ asset('app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js?v=' . config('versions.js')) }}">
    </script>
    <script src="{{ asset('app-assets/js/custom/flatpickr.js?v=' . config('versions.js')) }}"></script>
@endsection

@section('custom-css')
    <style>
        #expense_notes_list {
            max-height: 400px;
            overflow-y: auto;
        }


        #expense_notes_list .note_card {
            padding: 8px 12px;
            border-radius: 0px;
            box-shadow: none;
        }

        .expenseNotesCountBadge {
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
    <section id="expenses_filters">
        <div class="row justify-content-end mb-1">
            <div id="expense_filters_wrapper" class="col-md-10 row align-items-center collapse mx-0">
                <div class="col-12 col-sm-3 col-lg-2">
                    <div class="form-group">
                        <label class="form-label" for="type_filter">Type</label>
                        <select id="type_filter" class="select2-size-sm form-select select2" name="type_filter">
                            <option value="">All</option>
                            <option value="{{ App\Enums\ExpenseType::ONE_OFF }}">One-off</option>
                            <option value="{{ App\Enums\ExpenseType::RECURRING }}">Recurring</option>
                        </select>
                    </div>
                </div>
                <div class="col-12 col-sm-3 col-lg-2" style="display: none;" id="frequency_filter_wrapper">
                    <div class="form-group">
                        <label class="form-label" for="frequency_filter">Frequency</label>
                        <select id="frequency_filter" class="select2-size-sm form-select select2" name="frequency_filter">
                            <option value="">All</option>
                            <option value="{{ App\Enums\ExpenseFrequency::MONTHLY }}">Monthly</option>
                            <option value="{{ App\Enums\ExpenseFrequency::YEARLY }}">Yearly</option>
                        </select>
                    </div>
                </div>
                <div class="col-12 col-sm-3 col-xl-2 form-group">
                    <label for="remind_at_filter" class="form-label">Reminder</label>
                    <select id="remind_at_filter" class="form-select select2 select2-size-sm">
                        <option value="" selected>All</option>
                        <option value="month">This month</option>
                        <option value="last_month">Last month</option>
                        <option value="3_months">Last 3 months</option>
                        <option value="year">Current year</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>
                <div class="col-xxl-2 col-lg-3 col-sm-4 form-group" style="display:none;"
                    id="remind_at_filter_range_wrapper">
                    <label class="form-label">Reminder Range</label>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm" name="remind_at_filter_range"
                            id="remind_at_filter_range">
                    </div>
                </div>
                <div class="col-xxl-2 col-lg-3 col-sm-4 form-group">
                    <div class="form-check form-check-inline me-1 mt-1 mt-sm-0">
                        <input type="checkbox" id="showDeletedCheck" class="form-check-input me-50" value="1" />
                        <label class="form-check-label" for="showDeletedCheck">Show Deleted</label>
                    </div>
                </div>
            </div>
            <div class="col-md-2 d-flex justify-content-end align-items-start">
                <a data-bs-toggle="collapse" href="#expense_filters_wrapper" role="button" aria-expanded="false"
                    aria-controls="expense_filters_wrapper">
                    Advanced Search
                </a>
            </div>
        </div>
    </section>

    <section id="expenses_list">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <table class="table" id="expenses_table">
                    <thead class="table-light">
                        <tr>
                            <th>Client</th>
                            <th>Project</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Reminder</th>
                            <th>Expense Type</th>
                            <th>Expense Sub Type</th>
                            <th>Frequency</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>

    @include('expenses.expense_notes.modal')
@endsection

@section('custom-js')
    <script>
        $(document).ready(function() {

            $.fn.dataTableExt.oStdClasses.sFilter = "dataTables_filter dataTables_filter_sm";

            const redrawExpensesTable = (paging = true) => expensesDataTable && expensesDataTable.draw(paging);
            const $expensesList = $('#expenses_list');
            const $expensesTable = $expensesList.find('#expenses_table');
            const frequencyTypes = @json(App\Enums\ExpenseFrequency::all());
            const types = @json(App\Enums\ExpenseType::all());
            const $typeFilter = $('#type_filter');
            const $frequencyFilter = $('#frequency_filter');
            const $frequencyFilterWrapper = $('#frequency_filter_wrapper');
            const $remindAtFilter = $('#remind_at_filter');
            const $remindAtFilterRange = $('#remind_at_filter_range');
            const $remindAtFilterRangeWrapper = $('#remind_at_filter_range_wrapper');
            const $showDeletedCheck = $('#showDeletedCheck');
            const notesModal = $('#notesModal');
            const expense_notes_list = $('#expense_notes_list');
            const addNoteForm = $('#addNoteForm');

            [$typeFilter, $frequencyFilter, $remindAtFilter].forEach(filter => {
                filter.change(function(e) {
                    redrawExpensesTable();
                });
            });

            $remindAtFilter.change(function(e) {
                if ($(this).val() == 'custom') $remindAtFilterRangeWrapper.show()
                else $remindAtFilterRangeWrapper.hide()
            });

            $typeFilter.change(function(e) {
                if ($(this).val() == types?.RECURRING) $frequencyFilterWrapper.show();
                else $frequencyFilterWrapper.hide();
            });

            var filter_created_at_range_picker = $remindAtFilterRange.flatpickr({
                mode: 'range',
                dateFormat: 'd/m/Y',
                onChange: function(selectedDates, dateStr, instance) {
                    redrawExpensesTable();
                }
            });

            $(document).on('flatpickr:cleared', '#remind_at_filter_range', function(e) {
                redrawExpensesTable();
            })

            $('.select2').each(function() {

                let $this = $(this);
                $this.wrap('<div class="position-relative"></div>');

                let select2Config = {
                    dropdownAutoWidth: true,
                    width: '100%',
                    dropdownParent: $this.parent(),
                    containerCssClass: 'select-sm',
                };

                $this.select2(select2Config);

            });

            $showDeletedCheck
                .on(
                    'change',
                    function(e) {
                        redrawExpensesTable();
                    }
                );


            const expensesDataTable = $expensesTable.DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: route('expenses.index'),
                    data: function(d) {
                        d.show_deleted = $showDeletedCheck.prop('checked');
                        d.type_filter = $typeFilter.val();
                        d.frequency_filter = $frequencyFilter.val();
                        d.remind_at_filter = $remindAtFilter.val();
                        d.remind_at_filter_range = $remindAtFilterRange.val();
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
                    [4, 'asc']
                ],
                drawCallback: function(settings) {},
                columns: [{
                        data: "client.name",
                        name: "client.name",
                    },
                    {
                        data: 'project_name',
                    },
                    {
                        data: 'amount',
                    },
                    {
                        data: 'expense_date',
                    },
                    {
                        data: 'remind_at',
                    },
                    {
                        data: 'expense_type_title',
                        name: 'expense_sub_type.expense_type.title'
                    },
                    {
                        data: 'expense_sub_type_title',
                        name: 'expense_sub_type.title'
                    },
                    {
                        data: 'frequency',
                        searchable: false,
                        orderable: false,
                    },
                    {
                        data: null,
                        searchable: false,
                        orderable: false,
                        "render": function(data, type, full) {

                            let encId = full['encrypted_id'];
                            let deleted = full['deleted_at'];
                            let expenseNotesCount = full['expense_notes_count'];

                            let expenseEditBtn = '<a ' +
                                'href="' + route('expenses.edit', encId) + '" ' +
                                'title="Edit" ' +
                                'class="btn btn-sm btn-icon btn-flat-info">' +
                                feather.icons['edit'].toSvg({
                                    class: 'font-medium-3'
                                }) + '</a>';

                            let expenseShowBtn = '<a ' +
                                'href="' + route('expenses.show', encId) + '" ' +
                                'title="Show" ' +
                                'class="btn btn-sm btn-icon btn-flat-secondary">' +
                                feather.icons['eye'].toSvg({
                                    class: 'font-medium-3'
                                }) + '</a>';

                            let expenseDestroyBtn =
                                '<button ' +
                                'data-id="' + encId + '" ' +
                                'data-invoices-count="' + full['invoices_count'] + '" ' +
                                'title="Delete" ' +
                                'class="btn btn-sm btn-icon btn-flat-danger expenseDeleteBtn" >' +
                                feather.icons['trash'].toSvg({
                                    class: 'font-medium-3'
                                }) + '</button>';

                            let expenseRestoreBtn =
                                '<button ' +
                                'data-id="' + encId + '" ' +
                                'class="btn btn-sm btn-icon btn-flat-primary expenseRestoreBtn"' +
                                'title="Restore"' +
                                ' >' + feather.icons['refresh-cw'].toSvg({
                                    class: 'font-medium-3'
                                }) +
                                '</button>';

                            let expenseCopyBtn =
                                '<a ' +
                                'class="btn btn-sm btn-icon custom-btn-flat-indigo" ' +
                                'title="Copy"' +
                                'href="' +
                                route('expenses.copy', full['encrypted_id']) + '">' +
                                feather.icons['copy'].toSvg({
                                    class: 'font-medium-3'
                                }) + '</a>';


                            let expenseNoteCountBadge = expenseNotesCount > 0 ?
                                '<span class="expenseNotesCountBadge">' +
                                expenseNotesCount +
                                '</span>' :
                                '';
                            let expenseNotesBtn =
                                '<a ' +
                                'class="btn btn-sm btn-icon btn-flat-primary position-relative" ' +
                                'title="Notes" ' +
                                'data-bs-toggle="modal" ' +
                                'data-bs-target="#notesModal" ' +
                                'data-id="' + full['id'] + '"' +
                                '' +
                                '' +
                                'href="' +
                                route('expenses.copy', full['encrypted_id']) + '">' +
                                feather.icons['file-plus'].toSvg({
                                    class: 'font-medium-3'
                                }) + expenseNoteCountBadge + '</a>';

                            let buttons =
                                '<div class="d-flex" role="group">' +
                                (deleted ? '' : expenseShowBtn) +
                                (deleted ? '' : expenseEditBtn) +
                                (deleted ? '' : expenseNotesBtn) +
                                (expenseCopyBtn) +
                                (deleted ? '' : expenseDestroyBtn) +
                                (deleted ? expenseRestoreBtn : '') +
                                '</div>';

                            return buttons;
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
                    $expensesList.find('div.dataTables_length select').addClass('form-select-sm');
                },
                buttons: [{
                    text: `${feather.icons['plus-circle'].toSvg({ class: 'font-medium-1' })}`,
                    className: 'btn btn-primary me-1 btn-icon btn-sm addExpenseBtn',
                    attr: {
                        'data-bs-toggle': 'tooltip',
                        'data-bs-placement': 'bottom',
                        'title': 'Add Expense'
                    },
                    action: function(e, dt, node, config) {
                        // window.location.href = route('expenses.create');
                        window.location.href = route('expenses.create-many');
                    }
                }],
            });

            $(document).on('click', '.expenseDeleteBtn', function(e) {
                e.preventDefault();
                let {
                    id
                } = $(this).data();
                Swal.fire({
                    title: 'Are you sure you want to delete this expense?',
                    text: "You can restore this expense later.",
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
                            url: route('expenses.destroy', id),
                            method: 'DELETE',
                            data: {
                                _token: $('meta[name=csrf-token]').attr('content')
                            },
                            success: function(response) {
                                toastr.success(null, "Expense deleted successfully!");
                                redrawExpensesTable();
                            },
                            error: function(xhr, status, error) {
                                toastr.error(xhr.responseJSON?.message ?? null, error);
                                redrawExpensesTable();
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.expenseRestoreBtn', function(e) {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure you want to restore this expense?',
                    text: "You can revert this by deleting expense again!",
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
                            url: route('expenses.restore', id),
                            method: 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
                            },
                            success: function(response) {
                                toastr.success(null, "Expense restored successfully!");
                                redrawExpensesTable();
                            },
                            error: function(xhr, status, error) {
                                toastr.error(xhr.responseJSON?.message ?? null, error);
                                redrawExpensesTable();
                            }
                        });
                    }
                });
            });

            $(notesModal).on('show.bs.modal', function(event) {
                var notesBtn = event?.relatedTarget;
                var expense_id = $(notesBtn)?.data('id');
                $(this).find('#expense_id').val(expense_id);
                var addExpenseNoteUrl = route("expenses.expense_notes.store", expense_id);
                $(addNoteForm).attr('action', addExpenseNoteUrl);
                fillNotesList();
            });

            $(notesModal).on('hide.bs.modal', function(event) {
                resetNotesModalToDefault();
            });

            $('#notesModal').on('click', '.prevent-clicks', function(e) {
                e.preventDefault();
                e.stopPropagation();
            });

            $('#notesModal').on('click', '.editNoteBtn', function(e) {
                let expenseNoteId = $(this).closest('.note_card').data('expense-note-id');
                let expenseNoteNote = $(`#note_${expenseNoteId}`).text();
                let expenseId = $(notesModal).find('#expense_id').val();
                let updateExpenseRoute = route('expenses.expense_notes.update', [expenseId, expenseNoteId]);
                $(addNoteForm).attr('action', updateExpenseRoute);
                $('#notesModal').find('[name="note"]').val(expenseNoteNote);
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

            $('#notesModal').on('click', '.deleteNoteBtn', function(e) {
                let expenseNoteCard = $(this).closest('.note_card');
                let expenseNoteId = expenseNoteCard.data('expense-note-id');
                let expenseId = $(notesModal).find('#expense_id').val();
                Swal.fire({
                    title: 'Are you sure you want to delete this expense note?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-outline-danger ms-1'
                    },
                    buttonsStyling: false
                }).then(function(result) {
                    if (result.value) {
                        $.ajax({
                            url: route('expenses.expense_notes.destroy', [expenseId,
                                expenseNoteId
                            ]),
                            data: {
                                _token: $('meta[name=csrf-token]').attr('content') ?? ''
                            },
                            method: 'DELETE',
                            success: function(response) {
                                toastr.success(null,
                                    "Expense note deleted successfully!");
                                fillNotesList();
                                redrawExpensesTable();
                            },
                            error: function(xhr, status, error) {
                                toastr.error(xhr.responseJSON?.message ?? null, error);
                                fillNotesList();
                            }
                        });
                    }
                });
            });


            $('#notesModal').on('click', '#note_reset_btn', function(e) {
                resetNotesModalToDefault();
            });

            function resetNotesModalToDefault() {
                let expense_id = $('#notesModal').find('#expense_id').val();
                $(addNoteForm).attr('action', route('expenses.expense_notes.store', [expense_id]));
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
                    submitHandler: function(form, event) {
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
                            success: function(response, status, xhr) {
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
                                        prependExpenseNote(response?.expenseNote);
                                        redrawExpensesTable();
                                    }
                                }
                                $('#note_submit_btn').prop('disabled', false);
                                $('#addNoteForm').unblock();
                                $(notesModal).modal('hide');
                            },
                            error: function(xhr, status, error) {
                                $('#note_submit_btn').prop('disabled', false);
                                $('#addNoteForm').unblock();
                                if (xhr.status == 422) {
                                    $(form).validate().showErrors(JSON.parse(xhr
                                        ?.responseText)?.errors);
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
                let expense_id = $(notesModal).find('#expense_id').val();
                var getExpenseNotesUrl = route("expenses.expense_notes.index", expense_id);
                $.ajax({
                    url: getExpenseNotesUrl,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data, status, xhr) {
                        if (data.success) {
                            var expenseNotes = data?.expenseNotes;
                            if (expenseNotes === null || expenseNotes === undefined || expenseNotes ===
                                '' ||
                                expenseNotes?.length === 0) {
                                $(expense_notes_list).html(
                                    "<div class='text-center' >No notes found!</div>");
                            }
                            if (expenseNotes.length) {
                                var expenseNoteHtml = "";
                                for (const expenseNote of expenseNotes) {
                                    expenseNoteHtml += buildExpenseNoteHtml(expenseNote);
                                }
                                $(expense_notes_list).html(expenseNoteHtml);
                                return;
                            }
                        }

                    },
                    error: function(xhr, status, error) {
                        toastr.error(xhr.responseJSON?.message ?? null, error);
                    }
                });
            }

            function buildExpenseNoteHtml(expenseNote) {
                let edited = expenseNote.last_edited_at;
                let editedIndicator = edited ?
                    `<span class="float-end text-warning prevent-clicks" data-bs-toggle="tooltip" data-bs-placement="top" title="Edited by ${expenseNote?.user?.full_name} at ${expenseNote.formatted_last_edited_at}">${feather.icons['info'].toSvg({ class: 'font-medium-2', style: 'margin-right: 4px; margin-bottom: 2px;' })}</span>` :
                    ``;
                let deleteNoteBtn =
                    `<a class="btn btn-icon p-0 me-1 ${expenseNote.can_delete ? 'deleteNoteBtn' : ''}" href="#" title='Delete' >${feather.icons['trash'].toSvg({ class: 'font-small-4 text-danger' })}</a>`;

                return `<div class="card mb-0 note_card" data-expense-note-id='${expenseNote?.encrypted_id}' >
                        <div class="d-none" id='note_${expenseNote?.encrypted_id}' >${expenseNote?.note}</div>
                        <div class="card-body p-0">
                            <div class="card-text mb-0 d-flex" >
                            <a class="btn btn-icon p-0 editNoteBtn me-1" href="#" title='Edit' >${feather.icons['edit'].toSvg({ class: 'font-small-4 text-primary' })}</a>
                            ${deleteNoteBtn}
                            <div class='flex-grow-1' >
                                <span class="float-start break-white-space">${expenseNote?.note_with_line_breaks ?? (expenseNote?.note ?? '')}</span>
                                <span class="badge float-end bg-primary prevent-clicks">${expenseNote?.user?.full_name ?? ''} at ${expenseNote?.formatted_created_at ?? ''}</span>
                                ${editedIndicator}
                            </div>
                            </div>
                        </div>
                    </div>`;
            }

            function prependExpenseNote(expenseNote) {
                let noteCard = buildExpenseNoteHtml(expenseNote);
                if ($('.note_card').length) {
                    $(expense_notes_list).prepend(noteCard);
                } else {
                    $(expense_notes_list).html(noteCard);
                }
            }
            $(document).on('select2:open', (e) => {
                const selectId = e.target.id;
                $(".select2-search__field[aria-controls='select2-"+selectId+"-results']").each(function (key,value,){
                    value.focus();
                });
            });
        });
    </script>
@endsection
