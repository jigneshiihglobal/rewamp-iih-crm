$(document).ready(function () {

    /* invoiceNotesModal show */
    $('#invoiceNotesModal').on('show.bs.modal', function (event) {
        var notesBtn = event?.relatedTarget;
        var invoices_id = $(notesBtn)?.data('id');
        $(this).find('#invoices_id').val(invoices_id);
        var addExpenseNoteUrl = route("invoices.invoice_notes.store");
        $('#invoiceNoteForm').attr('action', addExpenseNoteUrl);
        fillNotesList();
    });

    $('#invoiceNotesModal').on('hide.bs.modal', function (event) {
        resetNotesModalToDefault();
    });

    $('#invoiceNotesModal').on('click', '.prevent-clicks', function (e) {
        e.preventDefault();
        e.stopPropagation();
    });

    $('#invoiceNotesModal').on('click', '#note_reset_btn', function (e) {
        resetNotesModalToDefault();
    });

    function resetNotesModalToDefault() {
        let invoices_id = $('#invoiceNotesModal').find('#invoices_id').val();
        $('#invoiceNoteForm').attr('action', route('invoices.invoice_notes.store'));
        $('#invoiceNotesModal').find('[name="note"]').val("");
        $('#invoiceNotesModal').find('#note_submit_btn').text('Submit');
        $('#invoiceNotesModal').find('#note_reset_btn').text('Reset');
        $('#invoiceNotesModal').find('#notes_method').val('');
        if ($('#invoiceNotesModal').find('[name="note"]').hasClass("error")) {
            $('#invoiceNotesModal').find('[name="note"]').removeClass('error');
        };
        if ($('#invoiceNotesModal').find('#notes-error')) {
            $('#invoiceNotesModal').find('#notes-error').remove();
        };
    }

    /* invoiceNotes edit */
    $('#invoiceNotesModal').on('click', '.editNoteBtn', function (e) {
        let invoiceNoteId = $(this).closest('.note_card').data('invoices-note-id');
        let invoiceNoteNote = $(`#note_${invoiceNoteId}`).text();
        let invoiceId = $(invoiceNotesModal).find('#invoices_id').val();
        let updateInvoiceRoute = route('invoices.invoice_notes.update', [invoiceId, invoiceNoteId]);
        $('#invoiceNoteForm').attr('action', updateInvoiceRoute);
        $('#invoiceNotesModal').find('[name="note"]').val(invoiceNoteNote);
        $('#invoices_note_id').val(invoiceNoteId);
        $('#invoiceNotesModal').find('#note_submit_btn').html('Update');
        $('#invoiceNotesModal').find('#note_reset_btn').html('Cancel');
        $('#invoiceNotesModal').find('#notes_method').val('PUT');
        if ($('#invoiceNotesModal').find('[name="note"]').hasClass("error")) {
            $('#invoiceNotesModal').find('[name="note"]').removeClass('error');
        };
        if ($('#invoiceNotesModal').find('#notes-error')) {
            $('#invoiceNotesModal').find('#notes-error').remove();
        };
    });
    /* invoiceNotes delete */
    $('#invoiceNotesModal').on('click', '.deleteNoteBtn', function (e) {
        let invoiceNoteCard = $(this).closest('.note_card');
        let invoiceNoteId = invoiceNoteCard.data('invoices-note-id');
        let invoiceId = $(invoiceNotesModal).find('#invoices_id').val();
        Swal.fire({
            title: 'Are you sure you want to delete this Invoice note?',
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
                    url: route('invoices.invoice_notes.destroy', [invoiceId,
                        invoiceNoteId
                    ]),
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content') ?? '',
                        invoiceId: invoiceId, invoiceNoteId: invoiceNoteId
                    },
                    method: 'DELETE',
                    success: function (response) {
                        toastr.success(null,
                            "Invoices note deleted successfully!");
                        fillNotesList();
                        $('.invoices-table').DataTable().ajax.reload(null, false);
                    },
                    error: function (xhr, status, error) {
                        toastr.error(xhr.responseJSON?.message ?? null, error);
                        fillNotesList();
                    }
                });
            }
        });
    });

// invoiceNoteForm Form Validation
    if ($('#invoiceNoteForm').length) {
        $('#invoiceNoteForm').validate({
            errorClass: 'error',
            rules: {
                note: {
                    required: true
                },
                numeric_value: {
                    min: 1
                },
            },
            messages: {
                note: {
                    required: "Please enter a note"
                },
                numeric_value: {
                    min: "Please enter valid amount"
                },
            },
            submitHandler: function (form, event) {
                event.preventDefault();
                updateHiddenInputs();
                $('#note_submit_btn').prop('disabled', true);
                $('#invoiceNoteForm').block({
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
                if ($('.error-message:visible').length > 0) {
                    event.preventDefault(); // Prevent form submission
                    toastr.error(null,'Please fix the errors before submitting the form.');
                    $('#note_reminder_submit_btn').prop('disabled', false);
                    $('#noteReminderForm').unblock();
                    return false;
                }
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
                                prependInvoicesNote(response?.invoicesNote);
                                $('.invoices-table').DataTable().ajax.reload(null, false);
                            }
                        }
                        $('#note_submit_btn').prop('disabled', false);
                        $('#invoiceNoteForm').unblock();
                        $(invoiceNotesModal).modal('hide');
                    },
                    error: function (xhr, status, error) {
                        $('#note_submit_btn').prop('disabled', false);
                        $('#invoiceNoteForm').unblock();
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
        let invoices_id = $(invoiceNotesModal).find('#invoices_id').val();
        var getInvoicesNotesUrl = route("invoices.invoice_notes.index");
        $.ajax({
            url: getInvoicesNotesUrl,
            type: 'GET',
            data : {invoices_id:invoices_id},
            dataType: 'json',
            success: function (data, status, xhr) {
                if (data.success) {
                    var invoicesNotes = data?.invoicesNotes;
                    if (invoicesNotes === null || invoicesNotes === undefined || invoicesNotes ===
                        '' ||
                        invoicesNotes?.length === 0) {
                        $(invoices_notes_list).html(
                            "<div class='text-center' >No notes found!</div>");
                    }
                    if (invoicesNotes.length) {
                        var invoicesNoteHtml = "";
                        for (const invoicesNote of invoicesNotes) {
                            invoicesNoteHtml += buildInvoicesNoteHtml(invoicesNote);
                        }
                        $(invoices_notes_list).html(invoicesNoteHtml);
                        return;
                    }
                }

            },
            error: function (xhr, status, error) {
                toastr.error(xhr.responseJSON?.message ?? null, error);
            }
        });
    }

    function buildInvoicesNoteHtml(invoicesNote) {
        let edited = invoicesNote.last_edited_at;
        let editedIndicator = edited ?
            `<span class="float-end text-warning prevent-clicks" data-bs-toggle="tooltip" data-bs-placement="top" title="Edited by ${invoicesNote?.user?.full_name ?? ''} at ${invoicesNote.formatted_last_edited_at}">${feather.icons['info'].toSvg({
                class: 'font-medium-2',
                style: 'margin-right: 4px; margin-bottom: 2px;'
            })}</span>` :
            ``;
        let deleteNoteBtn =
            `<a class="btn btn-icon p-0 me-1 ${invoicesNote.can_delete ? 'deleteNoteBtn' : ''}" href="#" title='Delete' >${feather.icons['trash'].toSvg({class: 'font-small-4 text-danger'})}</a>`;

        return `<div class="card mb-0 note_card invoice_note_card" data-invoices-note-id='${invoicesNote?.encrypted_id}' >
                        <div class="d-none" id='note_${invoicesNote?.encrypted_id}' >${invoicesNote?.note}</div>
                        <div class="card-body p-0">
                            <div class="card-text mb-0 d-flex" >
                            <a class="btn btn-icon p-0 editNoteBtn me-1" href="#" title='Edit' >${feather.icons['edit'].toSvg({class: 'font-small-4 text-primary'})}</a>
                            ${deleteNoteBtn}
                            <div class='flex-grow-1' >
                                <span class="float-start break-white-space">${invoicesNote?.note_with_line_breaks ?? (invoicesNote?.note ?? '')}</span>
                                <span class="badge float-end bg-primary prevent-clicks">${invoicesNote?.user?.full_name ?? ''} at ${invoicesNote?.formatted_created_at ?? ''}</span>
                                ${editedIndicator}
                            </div>
                            </div>
                        </div>
                    </div>`;
    }

    function prependInvoicesNote(invoicesNote) {
        let noteCard = buildInvoicesNoteHtml(invoicesNote);
        if ($('.note_card').length) {
            $(invoices_notes_list).prepend(noteCard);
        } else {
            $(invoices_notes_list).html(noteCard);
        }
    }


    /* Send Mail Ctr+Ent */
    $('#mailsend').click(function () {
        $('#sendMailModal').keydown(function (event) {
        /*$("body").keypress(function (event) {*/
            if (event.ctrlKey && (event.keyCode == 13 || event.keyCode == 10)) {
                $('#sendMailSubmitBtn').submit();
            }
        });
    });


    /* Payment Receipt Custom Send Mail Ctr+Ent */
    $('#paymentReceiptSend').click(function () {
        $("#sendReceiptMailModal").keydown(function (event) {
        /*$("body").keypress(function (event) {*/
            if (event.ctrlKey && (event.keyCode == 13 || event.keyCode == 10) ) {
                $('#sendReceiptMailSubmitBtn').submit();
            }
        });
    });

    $(document).on('click','.payment_reminder_cls',function (e){
        e.preventDefault();
        var reminder_status = $(this).data('remindervalue');
        $('#remindervalue').val(reminder_status);
        var invoice_id = $(this).data('invoiceid');
        $('#invoiceid').val(invoice_id);
        if(reminder_status == 1){
            $(".invoice_message").html('Want to <span style="color: red">Disable</span> invoice payment reminders!');
        }else{
            $(".invoice_message").html('Want to <span style="color: green">Enable</span> invoice payment reminders!');
        }
    });

    $(document).on('click','#payment_reminder',function (e){
        var reminder_status = $('#remindervalue').val();
        var invoice_id = $('#invoiceid').val();
        $.ajax({
            url: route('invoices.payment_reminder'),
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
            },
            data: {invoice_id:invoice_id,reminder_status:reminder_status},
            success: function(data) {
                toastr.success(null, data.message);
                $('.invoices-table').DataTable().ajax.reload(null, false);
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

    $(document).on('click','#invoice_already_paid_close',function(e){
        $('#invoice_already_paid_model').modal('hide');
    });

    $(document).on('change','#filter_show_cancelled',function(e){
        $('#filter_show_cancelled').val(this.checked);
        var show_cancelled = $(this).val();
    });

    var invoice_cancelled_date = $("#invoice_cancelled_date").val();
    if(invoice_cancelled_date != ''){
        $('.payment-buttons').addClass('cancelled_date_model');
    }

    $(document).on('click','.cancelled_date_model',function (e){
        $('#invoice-cancelled-modal').modal('show');
        return false;
    });

    $(document).on('click','#paymentLinkBtn',function (){
        if(invoice_cancelled_date != ''){
            $('#invoice-cancelled-modal').modal('show');
        }else{
            $('#paymentLinkModal').modal('show');
        }
    });

    $(document).on('click','#mailsend',function (){
        if(invoice_cancelled_date != ''){
            $('#invoice-cancelled-modal').modal('show');
        }else{
            $('#sendMailModal').modal('show');
        }
    })

    $(document).on('click','#payment_reminder_btn',function (){
        if(invoice_cancelled_date != ''){
            $('#invoice-cancelled-modal').modal('show');
        }else{
            $('#payment_reminder_enable_model').modal('show');
        }
    });

    $(document).on('click','#invoice_already_paid_btn',function (){
        if(invoice_cancelled_date != ''){
            $('#invoice-cancelled-modal').modal('show');
        }else{
            $('#invoice_already_paid_model').modal('show');
        }
    });

    $(document).on('click','#paymentReceiptSend',function (){
        if(invoice_cancelled_date != ''){
            $('#invoice-cancelled-modal').modal('show');
        }else{
            $('#sendReceiptMailModal').modal('show');
        }
    });

    $(document).on('click','#invoice_cancelled_close',function(e){
        $('#invoice-cancelled-modal').modal('hide');
    });

    $(document).on('select2:open', (e) => {
        const selectId = e.target.id;
        $(".select2-search__field[aria-controls='select2-"+selectId+"-results']").each(function (key,value,){
            value.focus();
        });
    });

    /* creditNote delete */
    $(document).on('click', '.creditNoteDeleteBtn', function(e) {
        e.preventDefault();
        let {
            id,
        } = $(this).data();
        Swal.fire({
            title: 'Are you sure you want to delete this Credit Note?',
            // text: "You can restore this Credit Note later.",
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
                    url: route('credit_notes.destroy', id),
                    method: 'DELETE',
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content')
                    },
                    success: function(response) {
                        toastr.success(null,
                            "Credit note deleted successfully!");
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

    /* Cancelled Invoice Restore */
    $(document).on('click', '.invoiceRestoreBtn', function(e) {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure you want to restore this invoice?',
            text: "You can revert this by cancelled invoice again!",
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
                    url: route('invoices.restore', id),
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
                    },
                    success: function(response) {
                        toastr.success(null, "Invoice restored successfully!");
                        $('.invoices-table').DataTable().ajax.reload(null, false);
                    },
                    error: function(xhr, status, error) {
                        toastr.error(xhr.responseJSON?.message ?? null, error);
                        $('.invoices-table').DataTable().ajax.reload(null, false);
                    }
                });
            }
        });
    });

    $('#paid_edit_at').flatpickr({
        dateFormat: 'd/m/Y',
        static: true,
    });

    $('#invoice-payment-edit-modal').on('hide.bs.modal', function(e) {
        $('#paymentsTable').DataTable().ajax.reload(null, false);
        $('#payment_source_id').select2({
            containerCssClass: 'select-sm',
            dropdownParent: $('#addPaymentModal').get(0),
        });
        $(this).find('#payment_source_id').select2('focus');
        $(this).find('#payment_source_id').select2('open');
    });

    $('#payment_edit_source_id').select2({
        containerCssClass: 'select-sm',
        dropdownParent: $('#invoice-payment-edit-modal').get(0),
    });

    $(document).on('click', '.payment_edit_model', function(e) {
        let id = $(this).data('id');
        let invoice_id = $(this).data('invoice_id');
        let paid_at = $(this).data('paid_at');
        let reference = $(this).data('reference');
        let payment_source_id = $(this).data('payment_source_id');
        $('#paymentEditForm').find('[name=payment_id]').val(id);
        $('#paymentEditForm').find('[name=invoice_id]').val(invoice_id);
        $('#paymentEditForm').find('[name=reference]').text(reference);
        $('#paymentEditForm').find('[name=paid_at]').val(paid_at);
        $('#paymentEditForm').find('[name=payment_source_id]').val(payment_source_id);
        $('#invoice-payment-edit-modal').modal('show');
        $('.payment_source_id').each(function() {
            var $this = $(this);
            $this.wrap('<div class="position-relative"></div>');
            if ($this.attr('name') == 'payment_source_id') {
                $this.select2({
                    placeholder: {
                        text: 'Select bank name',
                        selected: 'selected'
                    },
                    containerCssClass: 'select-sm',
                    width: '100%',
                    dropdownParent: $('body'),
                });
            }
        });
    });

    $('#paymentEditForm').validate({
        rules: {
            paid_at: {
                required: true,
                validDate: 'DD/MM/YYYY'
            },
        },
        messages: {
            paid_at: {
                required: "Please enter paid date",
                validDate: "Please enter valid paid date"
            },
        },
        errorClass: 'error',
        submitHandler: function(form, event) {
            event.preventDefault();
            $('#paymentEditForm').block({
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
            var invoice_id = $('#paymentEditForm').find('[name=invoice_id]').val();
            $.ajax({
                url: route('invoices.payments.paymentUpdate', invoice_id),
                method: 'PUT',
                data: $(form).serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
                },
                success: function(response) {
                   toastr.success(null, "Payment update successfully!");
                   $('#paymentsTable').DataTable().ajax.reload(null, false);
                   $('#invoice-payment-edit-modal').modal('hide');
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
            }) .always(function(xhr, status, error) {
                $('#paymentEditForm').unblock();
            })
        },
    });

    /* Payment delete */
    $(document).on('click', '.payment_delete', function (e) {
        e.preventDefault();
        let payments_id = $(this).data('id');
        let invoice_id = $(this).data('invoice_id');
        Swal.fire({
            title: 'Are you sure you want to delete this Invoice payments ?',
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
                    url: route('invoices.payments.paymentDestroy',invoice_id),
                    method: 'DELETE',
                    data: {
                        payments_id: payments_id,invoice_id:invoice_id
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
                    },
                    success: function (response) {
                        toastr.success(null, "Invoice payment deleted successfully!");
                        $('#paymentsTable').DataTable().ajax.reload(null, false);
                        $('.invoices-table').DataTable().ajax.reload(null, false);
                    },
                    error: function (xhr, status, error) {
                        toastr.error(xhr.responseJSON?.message ?? null, error);
                    }
                }).always(function(xhr, status, error) {
                    $('#addPaymentForm').unblock();
                });
            }
        });
    });

    /* Note reminders Listing */
    $(document).on('click', '.noteRemindersModalclass', function(event) {
        fillNoteReminderList();
        resetNoteRemindersModalToDefault();
        $('#noteRemindersModal').modal('show');
        var addExpenseNoteUrl = route("invoices.note_reminders.store");
        $('#noteReminderForm').attr('action', addExpenseNoteUrl);
    });

    $(document).on('click', '.btn-close', function(event) {
        resetNoteRemindersModalToDefault();
    });


    // $('#noteRemindersModal').on('hide.bs.modal', function (event) {
    //     resetNoteRemindersModalToDefault();
    // });

    $('#noteRemindersModal').on('click', '.prevent-clicks', function (e) {
        e.preventDefault();
        e.stopPropagation();
    });

    $('#noteRemindersModal').on('click', '#note_reminder_reset_btn', function (e) {
        resetNoteRemindersModalToDefault();
    });

    $(document).ready(function() {
        $('#noteRemindersModal #client_id').select2({
            placeholder: 'Select Customer',
            allowClear: true,
            width: '100%',
            dropdownParent: $('body'),
        });
    });
    

    function resetNoteRemindersModalToDefault() {
        $('#noteRemindersModal').find('#noteReminderForm').attr('action', route('invoices.note_reminders.store'));
        $('#noteRemindersModal').find('[name="note"]').val("");
        $('#noteRemindersModal').find('#note_reminder_submit_btn').text('Submit');
        $('#noteRemindersModal').find('#note_reminder_reset_btn').text('Reset');
        $('#noteRemindersModal').find('#notes_reminder_method').val('');
        $('#noteRemindersModal').find('#note_reminder_id').val('');
        $('#noteRemindersModal').find('.select2-selection__choice').remove();
        if ($('#noteRemindersModal').find('[name="note"]').hasClass("error")) {
            $('#noteRemindersModal').find('[name="note"]').removeClass('error');
        };
        if ($('#noteRemindersModal').find('#notes-error')) {
            $('#noteRemindersModal').find('#notes-error').remove();
        };
        $('#noteRemindersModal').find('#note_reminder_submit_btn').text('Submit');
        $('#numeric_value').val('').prop('readonly', false);
        $('#numeric_value_table tbody').empty();
        $('#add_row_btn').attr('disabled', false);
        $('#noteRemindersModal #noteReminderForm #vat_add').attr('checked', false);
    }

    /* NoteReminders edit */
    $('#noteRemindersModal').on('click', '.editNoteReminderBtn', function (e) {
        var oldSelectedClientId = $("#noteRemindersModal").find('#client_id').val();
        if (oldSelectedClientId && typeof oldSelectedClientId === 'object') {
            oldSelectedClientId.forEach(oldClientId => {
                if(oldClientId != ''){
                    // Find the corresponding option with the value equal to the client ID
                    $('#client_id option[value="' + oldClientId + '"]').prop('selected', false);
                }else{
                    $('#client_id').val('');
                }
            });
        }
        let noteReminderId = $(this).closest('.note_reminder_card').data('note-reminder-id');
        let NoteReminderNotes = $(`#note_${noteReminderId}`).text();
        let updateNoteReminderRoute = route('invoices.note_reminders.update',noteReminderId);
        $.ajax({
            url: route("invoices.note_reminders.show", noteReminderId),
            data: {
                _token: $('meta[name=csrf-token]').attr('content') ?? '',
                noteReminderId: noteReminderId
            },
            method: 'GET',
            success: function(data, status, xhr) {
                var selectedClientIds = data.client;
                var note_reminder_amounts = data.note_reminder_amount;
                $('#noteReminderForm').attr('action', updateNoteReminderRoute);
                $('#noteRemindersModal').find('[name="note"]').val(NoteReminderNotes);
                $('#note_reminder_id').val(noteReminderId);
                $('#noteRemindersModal').find('#note_reminder_submit_btn').html('Update');
                $('#noteRemindersModal').find('#note_reminder_reset_btn').html('Cancel');
                $('#noteRemindersModal').find('#notes_reminder_method').val('PUT');
                if ($('#noteRemindersModal').find('[name="note"]').hasClass("error")) {
                    $('#noteRemindersModal').find('[name="note"]').removeClass('error');
                };
                if ($('#noteRemindersModal').find('#notes-error')) {
                    $('#noteRemindersModal').find('#notes-error').remove();
                };
                $('#noteRemindersModal').find('#note_reminder_submit_btn').html('Update');
                if (selectedClientIds && typeof selectedClientIds === 'object') {
                    selectedClientIds.forEach(clientId => {
                        if(clientId != ''){
                            // Find the corresponding option with the value equal to the client ID
                            $('#client_id option[value="' + clientId + '"]').prop('selected', true);
                        }else{
                            $('#client_id').val('');
                        }
                    });
                    // Trigger the change event to update the select2 dropdown
                    $('#client_id').trigger('change');
                }
                $('#noteRemindersModal #noteReminderForm #numeric_value').val(parseFloat(data.total_amt).toFixed(2)).prop('readonly',false);
                if(data.total_amt != '' || data.total_amt != 0){
                    $('#noteRemindersModal #noteReminderForm #numeric_value').val(parseFloat(data.total_amt).toFixed(2)).prop('readonly', true);
                }
                $('#noteRemindersModal #noteReminderForm #vat_add').attr('checked', false);
                if(data.vat_status == 1){
                    $('#noteRemindersModal #noteReminderForm #vat_add').attr('checked', true).on('click', function(event) {
                        return false;
                    });
                }
                $('#noteRemindersModal #numeric_value_table tbody').empty();
                if(note_reminder_amounts && note_reminder_amounts.length > 0){
                    note_reminder_amounts.forEach(note_reminder_amount => {
                        if(note_reminder_amount != ''){
                            var amountValue = note_reminder_amount.received_amount;
                            var pending_amount = note_reminder_amount.pending_amount;
                            var received_at = note_reminder_amount.received_at;
                            var formattedDate = 'null';
                            if(received_at){
                                // Parse the date
                                var date = new Date(received_at);

                                // Extract day, month, and year
                                var day = String(date.getDate()).padStart(2, '0');
                                var month = String(date.getMonth() + 1).padStart(2, '0'); // getMonth() returns month from 0 to 11
                                var year = date.getFullYear();

                                // Format the date to DD/MM/YYYY
                                formattedDate = day + '-' + month + '-' + year;
                            }
                            $('#noteRemindersModal #numeric_value_table tbody').append('<tr>' +
                                '<td>' + parseFloat(amountValue).toFixed(2) + '</td>' +
                                '<td>' + parseFloat(pending_amount).toFixed(2) + '</td>' +
                                '<td>' + formattedDate + '</td>' +
                                '<input class="received-amount" type="hidden" name="received_amounts[]" value="' + amountValue + '">' +
                                '<input class="pending-amount" type="hidden" name="pending_amounts[]" value="' + pending_amount + '">' +
                                '<input type="hidden" class="received-at" name="received_ats[]" value="' + formattedDate + '">' +
                                '</tr>');
                        }
                    });
                }else {
                    $('#noteRemindersModal #numeric_value_table tbody').empty();
                }
            },
            error: function(xhr, status, error) {
                toastr.error(xhr.responseJSON?.message ?? null, error);
            }
        });
    });

    $('#noteRemindersModal').on('click', '.deleteNoteReminderBtn', function (e) {
        let noteReminderId = $(this).closest('.note_reminder_card').data('note-reminder-id');
        Swal.fire({
            title: 'Are you sure you want to delete this Invoice note?',
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
                    url: route('invoices.note_reminders.destroy',noteReminderId),
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content') ?? '',
                        noteReminderId: noteReminderId
                    },
                    method: 'DELETE',
                    success: function (response) {
                        toastr.success(null,"Note deleted successfully!");
                        fillNoteReminderList();
                        $('.invoices-table').DataTable().ajax.reload(null, false);
                    },
                    error: function (xhr, status, error) {
                        toastr.error(xhr.responseJSON?.message ?? null, error);
                        fillNoteReminderList();
                    }
                });
            }
        });
    });

        var totalAmount = 0;

        // Function to add new row
        function addNewRow(receivedAmount, pendingAmount, readOnly) {
            var date = new Date();

            // Extract day, month, and year
            var day = String(date.getDate()).padStart(2, '0');
            var month = String(date.getMonth() + 1).padStart(2, '0'); // getMonth() returns month from 0 to 11
            var year = date.getFullYear();

            // Format the date to DD/MM/YYYY
            var formattedDate = day + '-' + month + '-' + year;

            var newRow = '<tr>' +
                            '<td>' +
                                '<input type="number" class="form-control received-amount" value="' + parseFloat(receivedAmount).toFixed(2) + '" ' + (readOnly ? 'readonly' : '') + '>' +
                                '<div class="error-message text-danger" style="display:none;">Received amount exceeds pending amount</div>' +
                            '</td>' +
                            '<td><input type="number" class="form-control pending-amount" value="' + parseFloat(pendingAmount).toFixed(2) + '" readonly></td>' +
                            '<td><input type="text" class="form-control received-at reminder-date-picker" name="received_ats[]" placeholder="' + formattedDate + '" aria-invalid="false"></td>';
                         '</tr>';
            $('#numeric_value_table tbody').append(newRow);
            updateHiddenInputs();
            initializeDatePickers();
        }

        // Function to update hidden inputs
        function updateHiddenInputs() {
            var tableData = [];
            $('#numeric_value_table tbody tr').each(function() {
                var receivedAmount = parseFloat($(this).find('.received-amount').val()) || 0;
                var pendingAmount = parseFloat($(this).find('.pending-amount').val()) || 0;
                var receivedAt = $(this).find('.received-at').val();
                tableData.push({ received_amount: receivedAmount, pending_amount: pendingAmount, received_at: receivedAt });
            });

            $('#hidden_inputs_container').empty();
            tableData.forEach(function(row, index) {
                var hiddenInputs = '<input type="hidden" name="table_data[' + index + '][received_amount]" value="' + row.received_amount + '">' +
                                   '<input type="hidden" name="table_data[' + index + '][pending_amount]" value="' + row.pending_amount + '">' +
                                   '<input type="hidden" name="table_data[' + index + '][received_at]" value="' + row.received_at + '">';
                $('#hidden_inputs_container').append(hiddenInputs);
            });
        }

        function initializeDatePickers() {
            $("#noteRemindersModal #numeric_value_table tbody .reminder-date-picker").each(function() {
                // Initialize only if not already initialized
                if (!$(this).data('datepicker')) {
                    $(this).datepicker({
                        format: 'dd-mm-yyyy',  // Adjust format as needed
                        endDate: new Date(),   // Set the maximum date to today
                        defaultDate: new Date(),  // Set default date to today
                        autoclose: true,  // Close the datepicker after selection
                        onSelect: function(dateText, inst) {
                            // Update the corresponding received-at input field
                            $(this).closest('tr').find('.received-at').val(dateText);
                            updateHiddenInputs();
                        }
                    }).datepicker("setDate", new Date());  // Initialize with today's date
                }
            });
        }

        // Initial row with total amount set to the numeric value input
        $('#numeric_value').on('keyup', function() {
            totalAmount = parseFloat($(this).val()) || 0;
            if (totalAmount <= 0) {
                $('#numeric_value_table tbody').empty();
            }else{
                $('#numeric_value_table tbody').empty();
                addNewRow(0, totalAmount, false);
            }
        });

        $('#noteRemindersModal').on('change', '#vat_add', function (e) {
            var numeric_value = parseFloat($('#numeric_value').val());
            if(!isNaN(numeric_value)){
                var old_numeric_value = $('#noteRemindersModal #noteReminderForm #numeric_value').val();
                if ($(this).is(':checked')) {
                    totalAmount = old_numeric_value;
                    var vat = numeric_value * 0.2;
                    var total_with_vat = numeric_value + vat;
                    $('#noteRemindersModal #noteReminderForm #without_vat').val(numeric_value.toFixed(2));
                    $('#noteRemindersModal #noteReminderForm #vat_amount').val(vat.toFixed(2));
                    $('#numeric_value').val(total_with_vat.toFixed(2));
                    if (total_with_vat <= 0) {
                        $('#numeric_value_table tbody').empty();
                    }else{
                        $('#numeric_value_table tbody').empty();
                        addNewRow(0, total_with_vat, false);
                    }
                }else{
                    if(old_numeric_value != 0){
                        $('#numeric_value').val(totalAmount);
                    }else{
                        $('#numeric_value').val(totalAmount.toFixed(2));
                    }
                    $('#noteRemindersModal #noteReminderForm #without_vat').val(totalAmount);
                    $('#noteRemindersModal #noteReminderForm #vat_amount').val(null);
                    if (totalAmount <= 0) {
                        $('#numeric_value_table tbody').empty();
                    }else{
                        $('#numeric_value_table tbody').empty();
                        addNewRow(0, totalAmount, false);
                    }
                }
            }
        });


        // Add new row button click event
        $('#add_row_btn').on('click', function() {
            var lastRow = $('#numeric_value_table tbody tr:last');
            var lastReceivedAmount = parseFloat(lastRow.find('.received-amount').val()) || 0;
            var lastPendingAmount = parseFloat(lastRow.find('.pending-amount').val()) || 0;
            var isFirstRow = $('#numeric_value_table tbody tr').length === 1;
            var isLastRow = lastRow.is(':last-child');

            if (lastPendingAmount > 0 && lastReceivedAmount !== 0 && !isNaN(lastReceivedAmount)) {
                if (lastPendingAmount > 0) {
                    addNewRow(0, lastPendingAmount, false);
                    lastRow.find('.received-amount').attr('readonly', true);
                } else {
                    toastr.error(null, "Pending amount should be greater than 0 and received amount should not be 0 or empty to add a new row.");
                }
            } else if (isFirstRow) {
                addNewRow(0, lastPendingAmount, false);
                lastRow.find('.received-amount').attr('readonly', true);
            }else{
                toastr.error(null, "Pending amount should be greater than 0 and received amount should not be 0 or empty to add a new row.");
            }
        });

        // Handle received amount input change
        $(document).on('keyup', '.received-amount', function() {
            var $row = $(this).closest('tr');
            var receivedAmount = parseFloat($(this).val()) || 0;
            var $previousRow = $row.prev();
            var lastPendingAmount = $previousRow.length ? parseFloat($previousRow.find('.pending-amount').val()) : totalAmount;

            if (receivedAmount > lastPendingAmount || receivedAmount <= 0) {
                $row.find('.error-message').show();
                $('#add_row_btn').attr('disabled', true);
            } else {
                $row.find('.error-message').hide();
                $('#add_row_btn').attr('disabled', false);
                var pendingAmount = lastPendingAmount - receivedAmount;
                $row.find('.pending-amount').val(parseFloat(pendingAmount).toFixed(2));
            }

            // Ensure totalAmount reflects the initial numeric value input
            if ($previousRow.length === 0) {
                totalAmount = parseFloat($('#numeric_value').val()) || 0;
            }

            $('#invoiceNoteForm').on('submit', function(event) {
                if ($('.error-message:visible').length > 0) {
                    event.preventDefault(); // Prevent form submission
                    toastr.error(null,'Please fix the errors before submitting the form.');
                }
            });

            updateHiddenInputs();
        });

        // Clear the input and table on form reset
        $('#note_reminder_reset_btn').on('click', function() {
            $('#numeric_value').val('');
            $('#numeric_value_table tbody').empty();
            totalAmount = 0;
            $('#hidden_inputs_container').empty();
        });

    // invoice reminders Form Validation
    if ($('#noteReminderForm').length) {
        $('#noteReminderForm').validate({
            errorClass: 'error',
            rules: {
                note: {
                    required: true
                },
                numeric_value: {
                    min: 1
                },
            },
            messages: {
                note: {
                    required: "Please enter a note"
                },
                numeric_value: {
                    min: "Please enter valid amount"
                },
            },
            submitHandler: function (form, event) {
                event.preventDefault();
                updateHiddenInputs();
                $('#note_reminder_submit_btn').prop('disabled', true);
                $('#noteReminderForm').block({
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
                if ($('.error-message:visible').length > 0) {
                    event.preventDefault(); // Prevent form submission
                    toastr.error(null,'Please fix the errors before submitting the form.');
                    $('#note_reminder_submit_btn').prop('disabled', false);
                    $('#noteReminderForm').unblock();
                    return false;
                }
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
                                resetNoteRemindersModalToDefault();
                                fillNoteReminderList();
                            } else if (xhr.status === 201) {
                                toastr.success(null, "Note added successfully!");
                                form.reset();
                                resetNoteRemindersModalToDefault();
                                fillNoteReminderList();
                                prependNoteReminder(response?.NoteReminder);
                            }
                        }
                        $('#note_reminder_submit_btn').prop('disabled', false);
                        $('#noteReminderForm').unblock();
                    },
                    error: function (xhr, status, error) {
                        $('#note_reminder_submit_btn').prop('disabled', false);
                        $('#noteReminderForm').unblock();
                        if (xhr.status == 422) {
                            $(form).validate().showErrors(JSON.parse(xhr
                                ?.responseText)?.errors);
                            resetNoteRemindersModalToDefault();
                        } else {
                            resetNoteRemindersModalToDefault();
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

    function fillNoteReminderList() {
        var getNoteRemindersUrl = route("invoices.note_reminders.index");
        $.ajax({
            url: getNoteRemindersUrl,
            type: 'GET',
            dataType: 'json',
            success: function (data, status, xhr) {
                if (data.success) {
                    var noteReminders = data?.note_reminders;
                    if (noteReminders === null || noteReminders === undefined || noteReminders ===
                        '' ||
                        noteReminders?.length === 0) {
                        $(note_reminders_list).html(
                            "<div class='text-center' >No note reminders found!</div>");
                    }
                    if (noteReminders.length) {
                        var NoteReminderHtml = "";
                        for (const noteReminder of noteReminders) {
                            NoteReminderHtml += buildNoteReminderHtml(noteReminder);
                        }
                        $(note_reminders_list).html(NoteReminderHtml);
                        return;
                    }
                }
            },
            error: function (xhr, status, error) {
                toastr.error(xhr.responseJSON?.message ?? null, error);
            }
        });
    }

    function buildNoteReminderHtml(noteReminders) {
        let edited = noteReminders.last_edited_at;
        let editedIndicator = edited ?
            `<span class="float-end text-warning prevent-clicks" data-bs-toggle="tooltip" data-bs-placement="top" title="Edited by ${noteReminders?.user?.full_name ?? ''} at ${noteReminders.formatted_last_edited_at}">${feather.icons['info'].toSvg({
                class: 'font-medium-2',
                style: 'margin-right: 4px; margin-bottom: 2px;'
            })}</span>` :
            ``;
        let deleteNoteReminderBtn =
            `<a class="btn btn-icon p-0 me-1 ${noteReminders.can_delete ? 'deleteNoteReminderBtn' : ''}" href="#" title='Delete' >${feather.icons['trash'].toSvg({class: 'font-small-4 text-danger'})}</a>`;

        return `<div class="card mb-0 note_reminder_card invoice_note_card" data-note-reminder-id='${noteReminders?.encrypted_id}'>
                        <div class="d-none" id='note_${noteReminders?.encrypted_id}' >${noteReminders?.note}</div>
                        <div class="card-body p-0">
                            <div class="card-text mb-0 d-flex" >
                            <a class="btn btn-icon p-0 editNoteReminderBtn me-1" href="#" title='Edit' >${feather.icons['edit'].toSvg({class: 'font-small-4 text-primary'})}</a>
                            ${deleteNoteReminderBtn}
                            ${noteReminders?.encrypt_assign_client_id
                                ? `<a class="btn btn-icon p-0 addNoteBtn me-1"
                                        href="#"
                                        title="Create Invoice"
                                        data-client-id="${noteReminders.encrypt_assign_client_id}">
                                        ${feather.icons['plus'].toSvg({class: 'font-small-4', style: 'stroke: #f6931d;'})}
                                    </a>`
                                : `<a class="btn btn-icon p-0 me-1 invisible">
                                        ${feather.icons['plus'].toSvg({class: 'font-small-4', style: 'stroke: #f6931d;'})}
                                    </a>`
                            }                            
                            <div class='flex-grow-1' >
                                <span class="float-start break-white-space">${noteReminders?.note_with_line_breaks ?? (noteReminders?.note ?? '')}${noteReminders?.client_name != null ? `<span class="client-name-color"><b>(${noteReminders?.note_with_line_breaks ?? (noteReminders?.client_name ?? '')})</b></span>` : ''}</span>
                                <span class="badge float-end bg-primary prevent-clicks">${noteReminders?.user?.full_name ?? ''} at ${noteReminders?.formatted_created_at ?? ''}</span>
                                ${editedIndicator}
                            </div>
                            </div>
                        </div>
                    </div>`;
    }

    $(document).on('click', '.addNoteBtn', function(e) {
        e.preventDefault();
    
        const encryptedClientId = $(this).data('client-id');
    
        if (encryptedClientId) {
            window.location.href = `/invoices/create-one-off?source_invoice=${encryptedClientId}`;
        } else {
            alert('Invoice ID not found');
        }
    });
    

    function prependNoteReminder(noteReminders) {
        let noteReminderCard = buildNoteReminderHtml(noteReminders);
        if ($('.note_reminder_card').length) {
            $(note_reminders_list).prepend(noteReminderCard);
        } else {
            $(note_reminders_list).html(noteReminderCard);
        }
    }
});

