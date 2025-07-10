@extends('layouts.app')

@section('page-vendor-js')
    <script src="{{ asset('app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js?v=' . config('versions.js')) }}">
    </script>
@endsection

@section('vendor-css')
    <link rel="stylesheet"
          href="{{ asset('app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css?v=' . config('versions.css')) }}">
@endsection

@section('custom-css')
    <style>
        #addFollowUpCallForm .flatpickr-wrapper,
        #addFollowUpEmailForm .flatpickr-wrapper {
            width: 100%;
        }
    </style>
@endsection

@section('content')
    <section class="app-user-list">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <table class="user-list-table table" id="usersTable">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>

    <!-- Won user review Modal -->
    <div class="modal fade" id="reviewUserModal" tabindex="-1" aria-labelledby="reviewUserLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 450px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add User Review</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addReviewForm" class="form form-horizontal">
                        <div class="d-flex justify-content-between my-1">
                            <div>
                                <h6>Total previous review count : <span class="total_review" style="color: #F6931D"> </span></h6>
                            </div>
                        </div>
                        <input type="hidden" id="user_id" name="user_id" value="">
                        <div class="row">
                            <div class="col-12 mb-1">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <label for="review" class="col-form-label">Review Is<span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control form-control-sm" name="review_is"
                                               id="review_is" value="">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 mb-1">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <label for="review" class="col-form-label">Client Name<span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control form-control-sm" name="client_name"
                                               id="client_name" value="">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 mb-1">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <label for="review" class="col-form-label">Add Review<span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-sm-9">
                                        <input type="number" class="form-control form-control-sm" name="Review"
                                               id="Review" value="1">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="row mb-1">
                                    <div class="col-sm-3">
                                        <label for="review_date" class="col-form-label">Review date <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <input type="text" class="form-control form-control-sm"
                                                   name="review_date" id="review_date" value="{{ date('d/m/Y') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-9 offset-sm-3">
                                <button type="submit" class="btn btn-primary btn-sm" id="addReviewSubmitBtn">Submit</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal"
                                        aria-label="Close">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- End Won user review Modal --}}

    {{-- Modals --}}
    @include('users.modals.create')
@endsection

@section('page-js')
    <script src="{{ asset('app-assets/js/custom/flatpickr.js') }}"></script>
@endsection

@section('custom-js')
    <script>
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });
        $(document).ready(function() {
            const isAdmin = @json(auth()->user()->hasRole('Admin'));
            $.fn.dataTableExt.oStdClasses.sFilter = "dataTables_filter dataTables_filter_sm";
            var redrawUsersTable = (paging = false) => usersDataTable && usersDataTable.draw(paging);
            $('.select2').each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>');
                $this.select2({
                    dropdownAutoWidth: true,
                    width: '100%',
                    dropdownParent: $this.parent()
                });
            });
            var baseUrl = '{{ url('storage/') }}';
            var usersTable = $('#usersTable');
            var usersDataTable = usersTable.DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: route('users.index'),
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
                order: [[4, 'asc']],
                columns: [{
                        data: 'first_name',
                    render: function (data, type, row, meta) {
                        var imageUrl = row.pic ? baseUrl + '/' + row.pic : '{{ asset('app-assets/images/icons/user.svg') }}';
                        var name = row.first_name + ' ' + row.last_name;

                        return '<div class="user_profile_image"><div class="won_lead_image"><img src="' + imageUrl + '" class="round profile-picture"></div><div class="shield_image">' +
                            '</div>' +
                            '<label>' + name + '</label></div>';
                    }
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'role'
                    },
                    {
                        data: 'is_active',
                        searchable: false
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'actions',
                        searchable: false,
                        orderable: false
                    },
                ],
                dom: '<"d-flex justify-content-between align-items-center header-actions mx-1 row mt-50"r' +
                    '<"col-sm-12 col-lg-4 d-flex justify-content-center justify-content-lg-start" l>' +
                    '<"col-sm-12 col-lg-8 ps-xl-75 ps-0"<"dt-action-buttons d-flex align-items-center justify-content-center justify-content-lg-end flex-lg-nowrap flex-wrap"<"me-1"B>f>>' +
                    '>t' +
                    '<"d-flex justify-content-between mx-2 row mb-1"' +
                    '<"col-sm-12 col-md-6"i>' +
                    '<"col-sm-12 col-md-6"p>' +
                    '>',
                columnDefs: [{
                    targets: 3,
                    render(data, type, full, meta) {
                        return data ? 'Active' : "Inactive";
                    }
                }, {
                    targets: 2,
                    render(data, type, full, meta) {
                        return data ? full['role'] : "";
                    }
                }, {
                    targets: 5,
                    title: 'Actions',
                    orderable: false,
                    render: function(data, type, full, meta) {

                        let deleted_at = full['deleted_at'];
                        let is_me = full['is_me'];
                        let showUserBtn =
                            `<a href="${route('users.show', full['id'])}" class="btn btn-sm btn-icon btn-flat-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="View Profile" data-bs-original-title="View Profile" >${feather.icons['eye'].toSvg({ class: 'font-medium-3' })}</a>`;
                        let restoreUserBtn =
                            `<button data-user-id="${full['id']}" class="restoreUserBtn btn btn-sm btn-icon btn-flat-primary" title="Restore User" >${feather.icons['refresh-cw'].toSvg({ class: 'font-medium-3' })}</button>`;
                        let destroyUserBtn =
                            `<button data-user-id="${full['id']}" data-leads-count='${full['leads_count']}' class="destroyUserBtn btn btn-sm btn-icon btn-flat-danger" title="Delete User" >${feather.icons['trash'].toSvg({ class: 'font-medium-3' })}</button>`;
                        let forceDestroyUserBtn =
                            `<button data-user-id="${full['id']}" data-leads-count='${full['leads_count']}' class="forceDestroyUserBtn btn btn-sm btn-icon btn-flat-danger" title="Delete User Permanently" >${feather.icons['trash'].toSvg({ class: 'font-medium-3' })}</button>`;
                        let user_review_btn =
                            `<button data-id="${full['id']}" class="btn btn-sm btn-icon btn-flat-primary userReviewBtn" id="userReviewBtn" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Review" data-bs-original-title="Review">${feather.icons['star'].toSvg({ class: 'font-medium-3' })}</button>`;

                        if(isAdmin){
                            user_review_btn = '';
                        }

                        if(full['role'] == "Superadmin" && full['auth_user'] == 'Admin'){
                            showUserBtn = '';
                            destroyUserBtn = '';
                        }

                        let buttons = `<div
                                class="d-flex lead-list__action_buttons"
                                role="group"
                                aria-label="Basic mixed styles example"
                                >
                                    ${showUserBtn}
                                    ${is_me ? '' : (deleted_at ? restoreUserBtn + forceDestroyUserBtn : destroyUserBtn)}
                                    ${full['role'] == 'User' && full['deleted_at'] == null && full['is_active'] == true ? user_review_btn : ''}
                                </div>`;

                        return (buttons);
                    }
                }],
                initComplete: function(settings, json) {
                    $('.app-user-list div.dataTables_length select').addClass('form-select-sm');
                    $('.add_user_btn').tooltip('dispose');
                    $('.add_user_btn').tooltip({
                        title: "Add User",
                        placement: 'bottom'
                    });
                },
                buttons: [{
                    text: `${feather.icons['plus-circle'].toSvg({ class: 'font-medium-1' })}`,
                    className: 'add-new btn btn-primary btn-icon btn-sm add_user_btn',
                    attr: {
                        'data-bs-toggle': 'modal',
                        'data-bs-target': '#addUserModal'
                    },
                    init: function(api, node, config) {
                        $(node).removeClass('btn-secondary');
                    }
                }]
            });

            $('#addUserForm').validate({
                rules: {
                    first_name: {
                        required: true
                    },
                    last_name: {
                        required: true
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    gender: {
                        required: true
                    },
                    role: {
                        required: true
                    },
                    @role('Superadmin')
                    "workspaces[]": {
                        required: true,
                        minlength: 1
                    },
                    @endrole
                    timezone: {
                        required: true
                    }
                },
                messages: {
                    first_name: {
                        required: "Please enter first name"
                    },
                    last_name: {
                        required: "Please enter last name"
                    },
                    email: {
                        required: "Please enter email address",
                        email: "Please enter valid email address"
                    },
                    gender: {
                        required: "Please select gender"
                    },
                    role: {
                        required: "Please select role"
                    },
                    @role('Superadmin')
                    "workspaces[]": {
                        required: "Please select a workspace",
                        minlength: "Pelase select at lease 1 workspace"
                    },
                    @endrole
                    timezone: {
                        required: "Please select user's timezone"
                    }
                },
                submitHandler: function(form, event) {
                    event.preventDefault();
                    $('#addUserSubmitBtn').prop('disabled', true);
                    $('#addUserForm').block({
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
                                $('#addUserModal').modal('hide');
                                toastr.success(null, "User added successfully!");
                                redrawUsersTable();
                                form.reset();
                                $(form).find(".select2").trigger('change');
                            }
                            $('#addUserSubmitBtn').prop('disabled', false);
                            $('#addUserForm').unblock();
                        },
                        error: function(xhr, status, error) {
                            $('#addUserSubmitBtn').prop('disabled', false);
                            $('#addUserForm').unblock();
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
                    } else if (element.attr('name') == 'workspaces[]') {
                        error.insertAfter(element.parent().parent());
                    } else {
                        error.insertAfter(element);
                    }
                }
            });

            $(document).on('click', '.forceDestroyUserBtn', function (e) {
                e.preventDefault();
                let { userId, leadsCount } = $(this).data();
                if(leadsCount) {
                    Swal.fire({
                        title: 'This user has leads assigned!',
                        text: "Assign leads to other user to permanently delete this user",
                        icon: 'error',
                    });
                    return;
                }
                Swal.fire({
                    title: 'Are you sure you want to permanently delete this user?',
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
                            url: route('users.force.destroy', userId),
                            method: 'DELETE',
                            data: { _token: $('meta[name=csrf-token]').attr('content') },
                            success: function (response) {
                                toastr.success(null, "User permanently deleted successfully!");
                                redrawUsersTable();
                            },
                            error: function (xhr, status, error) {
                                toastr.error(xhr.responseJSON?.message ?? null, error);
                                redrawUsersTable();
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.destroyUserBtn', function (e) {
                e.preventDefault();
                let { userId, leadsCount } = $(this).data();
                if(leadsCount) {
                    Swal.fire({
                        title: 'This user has leads assigned!',
                        text: "Assign leads to other user to delete this user",
                        icon: 'error',
                    });
                    return;
                }
                Swal.fire({
                    title: 'Are you sure you want to delete this user?',
                    text: "You can restore this user later.",
                    icon: 'warning',
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
                            url: route('users.destroy', userId),
                            method: 'DELETE',
                            data: { _token: $('meta[name=csrf-token]').attr('content') },
                            success: function (response) {
                                toastr.success(null, "User deleted successfully!");
                                redrawUsersTable();
                            },
                            error: function (xhr, status, error) {
                                toastr.error(xhr.responseJSON?.message ?? null, error);
                                redrawUsersTable();
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.restoreUserBtn', function (e) {
                e.preventDefault();
                let { userId } = $(this).data();
                Swal.fire({
                    title: 'Are you sure you want to restore this user?',
                    text: "You can delete this user again!",
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
                            url: route('users.restore', userId),
                            method: 'PUT',
                            data: { _token: $('meta[name=csrf-token]').attr('content') },
                            success: function (response) {
                                toastr.success(null, "User restored successfully!");
                                redrawUsersTable();
                            },
                            error: function (xhr, status, error) {
                                toastr.error(xhr.responseJSON?.message ?? null, error);
                                redrawUsersTable();
                            }
                        });
                    }
                });
            });

            $(document).on('click','#userReviewBtn',function (){
                var user_id = $(this).data('id');
                $.ajax({
                    url: route('users.user_review', user_id),
                    method: 'GET',
                    success: function (response) {
                        if (response.errors) {
                            $(form).validate().showErrors(response.errors);
                        } else {
                            $('#reviewUserModal').modal('show');
                            $('#addReviewForm input[name="user_id"]').val(user_id);
                            $('#addReviewForm span.total_review').text(response);
                        }
                    },
                    error: function (xhr, status, error) {
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
            });

            $('#review_date').flatpickr({
                dateFormat: 'd/m/Y',
                static: true,
                maxDate: 'today',
            });

            $(document).on('hide.bs.modal','#reviewUserModal', function (event) {
                $('form#addReviewForm').get(0).reset();
            });

            if ($('#addReviewForm').length) {
                $('#addReviewForm').validate({
                    errorClass: 'error',
                    rules: {
                        'review_is': {
                            required: true,
                        },
                        'client_name': {
                            required: true,
                        },
                        'Review': {
                            required: true,
                            min: 1
                        },
                        'review_date': {
                            required: true,
                        },
                    },
                    messages: {
                        'review_is': {
                            required: "Please enter review is",
                        },
                        'client_name': {
                            required: "Please enter client name",
                        },
                        'Review': {
                            required: "Please enter review",
                            min:"please enter valid review"
                        },
                        'review_date': {
                            required: "Please enter review date",
                        },
                    },
                    submitHandler: function (form, event) {
                        event.preventDefault();
                        $('#addReviewSubmitBtn').prop('disabled', true);
                        $('#reviewUserModal > .modal-dialog').block({
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
                        var user_id = $('#addReviewForm input[name="user_id"]').val();
                        $.ajax({
                            url: route('users.review-store', user_id),
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: myFormdata,
                            contentType: false,
                            processData: false,
                            success: function (response) {
                                if (response.errors) {
                                    $(form).validate().showErrors(response.errors);
                                } else {
                                    $('#reviewUserModal').modal('hide');
                                    toastr.success(null, "Review added successfully!");
                                    form.reset();
                                    $(form).find(".select2").trigger('change');
                                }
                                $('#reviewUserModal > .modal-dialog').unblock();
                                $('#addReviewSubmitBtn').prop('disabled', false);
                            },
                            error: function (xhr, status, error) {
                                $('#reviewUserModal > .modal-dialog').unblock();
                                $('#addReviewSubmitBtn').prop('disabled', false);
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
                });
            }
        });
    </script>
@endsection
