@extends('layouts.app')

@section('content')
    <section class="lead_sources_list">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <table class="lead_sources_list_table table" id="leadSourcesTable">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>
    @include('lead_sources.modals.create')
    @include('lead_sources.modals.edit')
@endsection

@section('custom-js')
    <script>
        $(document).ready(function() {
            var redrawLeadSourceTable = (paging = false) => leadSourcesDataTable && leadSourcesDataTable.draw(
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
            var leadSourcesTable = $('#leadSourcesTable');
            var leadSourcesDataTable = leadSourcesTable.DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: route('lead_sources.index'),
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
                columns: [{
                        data: 'title'
                    },
                    {
                        data: 'actions',
                        searchable: false,
                        orderable: false
                    },
                ],
                order: [
                    [0, 'ASC']
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
                    targets: 1,
                    title: 'Actions',
                    orderable: false,
                    render: function(data, type, full, meta) {

                        let has_leads = !!(full['leads_count']);
                        let deleted = !!(full['deleted_at']);
                        let lead_source_edit_btn =
                            `<button data-lead-source-id="${full['encrypted_id']}" class="btn btn-sm btn-icon btn-flat-info leadSourceEditBtn" data-bs-toggle="modal" data-bs-target="#editLeadSourceModal" title="Edit">${feather.icons['edit'].toSvg({ class: 'font-medium-3' })}</button>`;
                        let lead_source_delete_btn =
                            `<button data-lead-source-id="${full['encrypted_id']}" data-has-leads="${has_leads}" class="btn btn-sm btn-icon btn-flat-danger leadSourceDeleteBtn" title="Delete">${feather.icons['trash'].toSvg({ class: 'font-medium-3' })}</button>`;
                        let lead_source_force_delete_btn =
                            `<button data-lead-source-id="${full['encrypted_id']}" data-has-leads="${has_leads}" class="btn btn-sm btn-icon btn-flat-danger leadSourceForceDeleteBtn" title="Delete Permanently">${feather.icons['trash'].toSvg({ class: 'font-medium-3' })}</button>`;
                            let lead_source_restore_btn =
                            `<button data-lead-source-id="${full['encrypted_id']}" class="btn btn-sm btn-icon btn-flat-primary leadSourceRestoreBtn" title="Restore">${feather.icons['refresh-cw'].toSvg({ class: 'font-medium-3' })}</button>`;
                        let buttons = '<div class="d-flex" >';
                        buttons += lead_source_edit_btn;
                        if(deleted) {
                            buttons +=  lead_source_restore_btn ;
                            buttons +=  lead_source_force_delete_btn ;
                        } else {
                            buttons += lead_source_delete_btn;
                        }
                        buttons += '</div>';

                        return buttons;
                    }
                }],
                buttons: [{
                    text: 'Add Lead Source',
                    className: 'add-new btn btn-primary',
                    attr: {
                        'data-bs-toggle': 'modal',
                        'data-bs-target': '#addLeadSourceModal'
                    },
                    init: function(api, node, config) {
                        $(node).removeClass('btn-secondary');
                    }
                }]
            });
            $('#addLeadSourceForm').validate({
                rules: {
                    title: {
                        required: true
                    }
                },
                messages: {
                    title: {
                        required: "Please enter title"
                    }
                },
                submitHandler: function(form, event) {
                    event.preventDefault();
                    $('#addLeadSourceSubmitBtn').prop('disabled', true);
                    $('#addLeadSourceForm').block({
                        message: '<div class="spinner-border text-warning" role="source"></div>',
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
                                $('#addLeadSourceModal').modal('hide');
                                toastr.success(null, "Lead source added successfully!");
                                redrawLeadSourceTable();
                                form.reset();
                            }
                            $('#addLeadSourceSubmitBtn').prop('disabled', false);
                            $('#addLeadSourceForm').unblock();
                        },
                        error: function(xhr, source, error) {
                            $('#addLeadSourceSubmitBtn').prop('disabled', false);
                            $('#addLeadSourceForm').unblock();
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
                errorPlacement: function(error, element) {
                    if (element.hasClass('select2') && element.next('.select2-container')
                        .length) {
                        error.insertAfter(element.next('.select2-container'));
                    } else {
                        error.insertAfter(element);
                    }
                }
            });

            $(document).on('click', '.leadSourceDeleteBtn', function() {
                var {
                    hasLeads,
                    leadSourceId
                } = $(this).data();
                if (hasLeads) {
                    Swal.fire({
                        title: 'This source has leads!',
                        text: "Move leads to other source to delete this source",
                        icon: 'error',
                    });
                    return;
                }
                Swal.fire({
                    title: 'Are you sure you want to delete this lead source?',
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
                            url: route('lead_sources.destroy', leadSourceId),
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
                            },
                            success: function(response) {
                                toastr.success(null,
                                    "Lead source deleted successfully!");
                                redrawLeadSourceTable();
                            },
                            error: function(xhr, source, error) {
                                toastr.error(xhr.responseJSON?.message ?? null, error);
                                redrawLeadSourceTable();
                            }
                        });
                        Swal.fire({
                            title: 'Lead source deleted successfully!',
                            text: "Operation successful!",
                            icon: 'success',
                        });
                    }
                });
            });

            $('#editLeadSourceModal').on('show.bs.modal', function(event) {
                $.ajax({
                    url: route("lead_sources.edit", $(event.relatedTarget).data('lead-source-id')),
                    type: 'GET',
                    success: function(data, source, xhr) {
                        $('#edit_lead_source_id').val(data?.lead_source?.encrypted_id);
                        $("#edit_title").val(data?.lead_source?.title);
                    },
                    error: function(xhr, source, error) {
                        toastr.error(xhr.responseJSON?.message ?? null, error);
                    }
                });
            });

            $('#editLeadSourceModal').on("hide.bs.modal", function(editLeadModalHideEvent) {
                $("#edit_lead_source_id").val("").trigger("change");
                $("#edit_title").val("");
                $(this).find('input.error').removeClass('error');
                $(this).find('span.error').remove();
            });

            $('#editLeadSourceForm').validate({
                rules: {
                    title: {
                        required: true
                    }
                },
                messages: {
                    title: {
                        required: "Please enter title"
                    }
                },
                submitHandler: function(form, event) {
                    event.preventDefault();
                    $('#editLeadSourceSubmitBtn').prop('disabled', true);
                    $('#editLeadSourceForm').block({
                        message: '<div class="spinner-border text-warning" role="source"></div>',
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
                        url: route('lead_sources.update', $('#edit_lead_source_id').val()),
                        method: 'PUT',
                        data: $(form).serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
                        },
                        success: function(response) {
                            if (response.errors) {
                                $(form).validate().showErrors(response.errors);
                            } else {
                                $('#editLeadSourceModal').modal('hide');
                                toastr.success(null, "Lead source updated successfully!");
                                redrawLeadSourceTable();
                                form.reset();
                            }
                            $('#editLeadSourceSubmitBtn').prop('disabled', false);
                            $('#editLeadSourceForm').unblock();
                        },
                        error: function(xhr, source, error) {
                            $('#editLeadSourceSubmitBtn').prop('disabled', false);
                            $('#editLeadSourceForm').unblock();
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

            $(document).on('click', '.leadSourceRestoreBtn', function (e) {
                e.preventDefault();
                let { leadSourceId } = $(this).data();
                Swal.fire({
                    title: 'Are you sure you want to restore this source?',
                    text: "You can delete this source again!",
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
                            url: route('lead_sources.restore', leadSourceId),
                            method: 'PUT',
                            headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content') },
                            success: function (response) {
                                toastr.success(null, "Lead source restored successfully!");
                                redrawLeadSourceTable();
                            },
                            error: function (xhr, source, error) {
                                toastr.error(xhr.responseJSON?.message ?? null, error);
                                redrawLeadSourceTable();
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.leadSourceForceDeleteBtn', function (e) {
                e.preventDefault();
                let { leadSourceId, hasLeads } = $(this).data();
                if(hasLeads) {
                    Swal.fire({
                        title: 'This source has leads!',
                        text: "Move leads to other source to permanently delete this source",
                        icon: 'error',
                    });
                    return;
                }
                Swal.fire({
                    title: 'Are you sure you want to permanently delete this source?',
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
                            url: route('lead_sources.force-delete', leadSourceId),
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content') },
                            success: function (response) {
                                toastr.success(null, "Lead source permanently deleted successfully!");
                                redrawLeadSourceTable();
                            },
                            error: function (xhr, source, error) {
                                toastr.error(xhr.responseJSON?.message ?? null, error);
                                redrawLeadSourceTable();
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
