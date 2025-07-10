@extends('layouts.app')

@section('content')
    <section class="lead_statuses_list">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <table class="lead_statuses_list_table table" id="leadStatusesTable">
                    <thead class="table-light">
                        <tr>
                            <th>Order</th>
                            <th>Name</th>
                            <th>Color</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>
    @include('lead_statuses.modals.create')
    @include('lead_statuses.modals.edit')
@endsection

@section('custom-js')
    <script>
        $(document).ready(function() {
            const lead_statuses = @json($lead_statuses);
            var redrawLeadStatusTable = (paging = false) => leadStatusesDataTable && leadStatusesDataTable.draw(
                paging);
            $('.select2').each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>');
                $this.select2({
                    dropdownAutoWidth: true,
                    width: '100%',
                    dropdownParent: $this.parent()
                });
            });
            var leadStatusesTable = $('#leadStatusesTable');
            var leadStatusesDataTable = leadStatusesTable.DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: route('lead_statuses.index'),
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
                    [0, 'ASC']
                ],
                columns: [{
                        data: 'order'
                    },
                    {
                        data: 'title'
                    },
                    {
                        data: 'css_class'
                    },
                    {
                        data: 'actions',
                        searchable: false,
                        orderable: false
                    }
                ],
                dom: '<"d-flex justify-content-between align-items-center header-actions mx-2 row mt-75"r' +
                    '<"col-sm-12 col-lg-4 d-flex justify-content-center justify-content-lg-start" l>' +
                    '<"col-sm-12 col-lg-8 ps-xl-75 ps-0"<"dt-action-buttons d-flex align-items-center justify-content-center justify-content-lg-end flex-lg-nowrap flex-wrap"<"me-1"f>B>>' +
                    '>t' +
                    '<"d-flex justify-content-between mx-2 row mb-1"' +
                    '<"col-sm-12 col-md-6"i>' +
                    '<"col-sm-12 col-md-6"p>' +
                    '>',
                columnDefs: [{
                    targets: 3,
                    title: 'Actions',
                    orderable: false,
                    render: function(data, type, full, meta) {

                        let has_leads = !!(full['leads_count']);
                        let deleted = !!(full['deleted_at']);
                        let lead_status_edit_btn =
                            `<button data-lead-status-id="${full['encrypted_id']}" class="btn btn-sm btn-icon btn-flat-info leadStatusEditBtn" data-bs-toggle="modal" data-bs-target="#editLeadStatusModal" title="Edit">${feather.icons['edit'].toSvg({ class: 'font-medium-3' })}</button>`;
                        let lead_status_delete_btn =
                            `<button data-lead-status-id="${full['encrypted_id']}" data-has-leads="${has_leads}" class="btn btn-sm btn-icon btn-flat-danger leadStatusDeleteBtn" title="Delete">${feather.icons['trash'].toSvg({ class: 'font-medium-3' })}</button>`;
                        let lead_status_force_delete_btn =
                            `<button data-lead-status-id="${full['encrypted_id']}" data-has-leads="${has_leads}" class="btn btn-sm btn-icon btn-flat-danger leadStatusForceDeleteBtn" title="Delete Permanently">${feather.icons['trash'].toSvg({ class: 'font-medium-3' })}</button>`;
                            let lead_status_restore_btn =
                            `<button data-lead-status-id="${full['encrypted_id']}" class="btn btn-sm btn-icon btn-flat-primary leadStatusRestoreBtn" title="Restore">${feather.icons['refresh-cw'].toSvg({ class: 'font-medium-3' })}</button>`;
                        let buttons = '<div class="d-flex" >';
                        buttons += lead_status_edit_btn;
                        if(deleted) {
                            buttons +=  lead_status_restore_btn ;
                            buttons +=  lead_status_force_delete_btn ;
                        } else {
                            buttons += lead_status_delete_btn;
                        }
                        buttons += '</div>';

                        return buttons;
                    }
                }],
                buttons: [{
                    text: 'Add Lead Status',
                    className: 'add-new btn btn-primary',
                    attr: {
                        'data-bs-toggle': 'modal',
                        'data-bs-target': '#addLeadStatusModal'
                    },
                    init: function(api, node, config) {
                        $(node).removeClass('btn-secondary');
                    }
                }]
            });
            $('#addLeadStatusForm').validate({
                rules: {
                    title: {
                        required: true
                    },
                    css_class: {
                        required: true
                    }
                },
                messages: {
                    title: {
                        required: "Please enter title"
                    },
                    css_class: {
                        required: 'Please select a color'
                    }
                },
                submitHandler: function(form, event) {
                    event.preventDefault();
                    $('#addLeadStatusSubmitBtn').prop('disabled', true);
                    $('#addLeadStatusForm').block({
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
                                $('#addLeadStatusModal').modal('hide');
                                toastr.success(null, "Lead status added successfully!");
                                redrawLeadStatusTable();
                                form.reset();
                            }
                            $('#addLeadStatusSubmitBtn').prop('disabled', false);
                            $('#addLeadStatusForm').unblock();
                        },
                        error: function(xhr, status, error) {
                            $('#addLeadStatusSubmitBtn').prop('disabled', false);
                            $('#addLeadStatusForm').unblock();
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
                    if (element.hasClass('select2') && element.next('.select2-container')
                        .length) {
                        error.insertAfter(element.next('.select2-container'));
                    } else if (element.attr('name') == 'css_class') {
                        error.insertAfter(element.closest('.d-flex'));
                    } else {
                        error.insertAfter(element);
                    }
                }
            });

            $(document).on('click', '.leadStatusDeleteBtn', function() {
                var {
                    hasLeads,
                    leadStatusId
                } = $(this).data();
                if (hasLeads) {
                    Swal.fire({
                        title: 'This status has leads!',
                        text: "Move leads to other status to delete this status",
                        icon: 'error',
                    });
                    return;
                }
                Swal.fire({
                    title: 'Are you sure you want to delete this lead status?',
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
                            url: route('lead_statuses.destroy', leadStatusId),
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
                            },
                            success: function(response) {
                                toastr.success(null,
                                    "Lead status deleted successfully!");
                                redrawLeadStatusTable();
                            },
                            error: function(xhr, status, error) {
                                toastr.error(xhr.responseJSON?.message ?? null, error);
                                redrawLeadStatusTable();
                            }
                        });
                        Swal.fire({
                            title: 'Lead status deleted successfully!',
                            text: "Operation successful!",
                            icon: 'success',
                        });
                    }
                });
            });

            $('#editLeadStatusModal').on('show.bs.modal', function(event) {
                $.ajax({
                    url: route("lead_statuses.edit", $(event.relatedTarget).data('lead-status-id')),
                    type: 'GET',
                    success: function(data, status, xhr) {
                        $('#edit_lead_status_id').val(data?.lead_status?.encrypted_id);
                        $("#edit_title").val(data?.lead_status?.title);
                        $("#edit_css_class").val(data?.lead_status?.css_class).trigger(
                            'change');
                    },
                    error: function(xhr, status, error) {
                        toastr.error(xhr.responseJSON?.message ?? null, error);
                    }
                });
            });

            $('#editLeadStatusModal').on("hide.bs.modal", function(editLeadModalHideEvent) {
                $("#edit_lead_status_id").val("").trigger("change");
                $("#edit_title").val("");
                $("#edit_css_class").val("");
            });

            $('#editLeadStatusForm').validate({
                rules: {
                    title: {
                        required: true
                    },
                    css_class: {
                        required: true
                    }
                },
                messages: {
                    title: {
                        required: "Please enter title"
                    },
                    css_class: {
                        required: 'Please select a color'
                    }
                },
                submitHandler: function(form, event) {
                    event.preventDefault();
                    $('#editLeadStatusSubmitBtn').prop('disabled', true);
                    $('#editLeadStatusForm').block({
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
                        url: route('lead_statuses.update', $('#edit_lead_status_id').val()),
                        method: 'PUT',
                        data: $(form).serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
                        },
                        success: function(response) {
                            if (response.errors) {
                                $(form).validate().showErrors(response.errors);
                            } else {
                                $('#editLeadStatusModal').modal('hide');
                                toastr.success(null, "Lead status updated successfully!");
                                redrawLeadStatusTable();
                                form.reset();
                            }
                            $('#editLeadStatusSubmitBtn').prop('disabled', false);
                            $('#editLeadStatusForm').unblock();
                        },
                        error: function(xhr, status, error) {
                            $('#editLeadStatusSubmitBtn').prop('disabled', false);
                            $('#editLeadStatusForm').unblock();
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
                    if (element.hasClass('select2') && element.next('.select2-container')
                        .length) {
                        error.insertAfter(element.next('.select2-container'));
                    } else if (element.attr('name') == 'css_class') {
                        error.insertAfter(element.closest('.d-flex'));
                    } else {
                        error.insertAfter(element);
                    }
                }
            });

            $(document).on('click', '.leadStatusRestoreBtn', function (e) {
                e.preventDefault();
                let { leadStatusId } = $(this).data();
                Swal.fire({
                    title: 'Are you sure you want to restore this status?',
                    text: "You can delete this status again!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Restore',
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-outline-danger ms-1'
                    },
                    buttonsStyling: false
                }).then(function (result) {
                    if (result.value) {
                        $.ajax({
                            url: route('lead_statuses.restore', leadStatusId),
                            method: 'PUT',
                            headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content') },
                            success: function (response) {
                                toastr.success(null, "Lead status restored successfully!");
                                redrawLeadStatusTable();
                            },
                            error: function (xhr, status, error) {
                                toastr.error(xhr.responseJSON?.message ?? null, error);
                                redrawLeadStatusTable();
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.leadStatusForceDeleteBtn', function (e) {
                e.preventDefault();
                let { leadStatusId, hasLeads } = $(this).data();
                if(hasLeads) {
                    Swal.fire({
                        title: 'This status has leads!',
                        text: "Move leads to other status to permanently delete this status",
                        icon: 'error',
                    });
                    return;
                }
                Swal.fire({
                    title: 'Are you sure you want to permanently delete this status?',
                    text: "You won't be able to revert this!",
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-outline-danger ms-1'
                    },
                    buttonsStyling: false
                }).then(function (result) {
                    if (result.value) {
                        $.ajax({
                            url: route('lead_statuses.force-delete', leadStatusId),
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content') },
                            success: function (response) {
                                toastr.success(null, "Lead status permanently deleted successfully!");
                                redrawLeadStatusTable();
                            },
                            error: function (xhr, status, error) {
                                toastr.error(xhr.responseJSON?.message ?? null, error);
                                redrawLeadStatusTable();
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
