@extends('layouts.app')

@section('vendor-css')
    <link rel="stylesheet" href="{{asset('app-assets/vendors/bootstrap-datepicker/css/bootstrap-datepicker.min.css?v='.config('versions.css'))}}">
@endsection

@section('custom-css')
    <link rel="stylesheet" href="{{asset('app-assets/css/custom/bootstrap-datepicker.css?v='.config('versions.css'))}}">
@endsection

@section('content')

<section id="contact_lead_filter">
    <div class="row justify-content-end mb-1">
        <div class="col-sm-4 col-lg-3 col-xl-2 col-xxl-1 pe-md-1 ps-md-0 ">
            <div class="card mt-1 mt-xl-0 mb-0 mail_send_count" data-id="Sent">
                <div class="card-header p-1 pb-0 padding-left-6px">
                    <h1 class="text-reset total_count">
                        {{ $mail_count['total_counts'] ?? '0' }}
                    </h1>
                </div>
                <div class="card-body row p-1 pt-0">
                    <div class="col-12 d-flex justify-content-center">
                    <span class="nowrap-white-space padding-left-6px">
                        Total sent
                    </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-4 col-lg-3 col-xl-2 col-xxl-1 ps-md-0 ms-md-0" >
            <div class="card mt-1 mt-xl-0 mb-0 mail_send_count delivereds_count" data-id="Delivered">
                <div class="card-header p-1 pb-0 padding-left-6px">
                    <h1 class="text-reset delivered">
                        {{ $mail_count['delivereds'] ?? '0' }}
                    </h1>
                    <span class="text-reset percentage-delivered">
                        ({{ $percentageDelivered != 100 ? number_format($percentageDelivered, 2) : $percentageDelivered }}%)
                    </span>
                </div>
                <div class="card-body row p-1 pt-0">
                    <div class="col-12 d-flex justify-content-center">
                    <span class="nowrap-white-space padding-left-6px">
                        Delivered
                     </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-4 col-lg-3 col-xl-2 col-xxl-1 pe-md-1 ps-md-0 ">
            <div class="card mt-1 mt-xl-0 mb-0 mail_send_count open_count" data-id="Open">
                <div class="card-header p-1 pb-0 padding-left-6px">
                    <h1 class="text-reset open_count">
                        {{ $mail_count['opens'] ?? '0' }}
                    </h1>
                    <span class="text-reset percentage-delivered">
                        ({{ $percentageOpens != 100 ? number_format($percentageOpens,2) : $percentageOpens }}%)
                    </span>
                </div>
                <div class="card-body row p-1 pt-0">
                    <div class="col-12 d-flex justify-content-center">
                    <span class="nowrap-white-space padding-left-6px">
                        Opens
                    </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4 col-lg-3 col-xl-2 col-xxl-1 ps-md-0 ms-md-0">
            <div class="card mt-1 mt-xl-0 mb-0 mail_send_count soft_bounce_count" data-id="Soft Bounce">
                <div class="card-header p-1 pb-0 padding-left-6px">
                    <h1 class="text-reset soft_bounce">
                        {{ $mail_count['soft_bounce'] ?? '0' }}
                    </h1>
                    <span class="text-reset percentage-delivered">
                        ({{ $percentageBounce != 100 ? number_format($percentageBounce,2) : $percentageBounce }}%)
                     </span>
                </div>
                <div class="card-body row p-1 pt-0">
                    <div class="col-12 d-flex justify-content-center">
                    <span class="nowrap-white-space padding-left-6px">
                        Soft Bounce
                    </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

    <section class="app-contacted-list">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <table class="contacted-list-table table">
                    <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Lead Status</th>
                        <th>Day After</th>
                        <th>Status</th>
                        <th>Sent Date</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </section>
    @include('contacted_lead.emailModal')
@endsection

@section('page-js')
    <script src="{{ asset('app-assets/vendors/bootstrap-datepicker/js/bootstrap-datepicker.min.js?v='.config('versions.js'))}}"></script>
@endsection

@section('custom-js')
    <script>
        $(document).ready(function () {
            var mailStatus = '';
            var table = $('.contacted-list-table').DataTable({
                serverSide: true,
                processing: true,
                pageLength: 50,
                ajax: {
                    url: route('contacted-lead.index'),
                    data: function(d) {
                        d.mail_status = mailStatus; // Custom filter parameter
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
                columns: [
                    { data: 'lead_name'},
                    {
                        data: 'email',
                        render: function(data) {
                            return data ? data : '';
                        }
                    },
                    {
                        data: 'mail_subject',
                        render: function(data) {
                            return data ? data : '';
                        }
                    },
                    {
                        data: 'lead_status.title',
                        name: 'lead_status.title',
                        render: function(data) {
                            return data ? data : ''; // Check if data is null or undefined
                        }
                    },
                    {
                        data: 'day_after',
                        render: function(data) {
                            return data ? data : ''; // Check if data is null or undefined
                        }
                    },
                    { data: 'lead_status_event' },
                    {
                        data: 'created_at',
                        render: function(data) {
                            if (data) {
                                var dateObj = new Date(data);
                                var day = dateObj.getDate();
                                var month = dateObj.getMonth() + 1;
                                var year = dateObj.getFullYear();
                                var hours = dateObj.getHours();
                                var minutes = dateObj.getMinutes();

                                // Ensure leading zeros for day, month, hours, and minutes if needed
                                day = (day < 10) ? '0' + day : day;
                                month = (month < 10) ? '0' + month : month;
                                hours = (hours < 10) ? '0' + hours : hours;
                                minutes = (minutes < 10) ? '0' + minutes : minutes;

                                // Return formatted date and time as dd/mm/yyyy HH:MM
                                return day + '/' + month + '/' + year + ' ' + hours + ':' + minutes;
                            } else {
                                return ''; // Handle empty or null dates
                            }
                        },
                        searchable: false
                    },
                    {
                        data: null,
                        searchable: false,
                        orderable: false,
                        "render": function(data, type, full) {
                            let encId = full['encrypted_id'];

                            // Create Email Show button with data-id attribute
                            let expenseShowBtn = '<button ' +
                                'type="button" ' +
                                'class="btn btn-sm btn-icon btn-flat-secondary email-show-btn emailModal" ' +
                                'data-id="'+ encId +'">' +
                                feather.icons['eye'].toSvg({ class: 'font-medium-3' }) +
                                '</button>';

                            return expenseShowBtn;
                        }
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
                buttons: [],
                lengthMenu: [ // Customize the length menu options
                    [50, 100, 250, 500], // Entries per page options: 50, 100, 250, 500
                    [50, 100, 250, 500], // Display labels for the options
                ],
            });

            // Reminder Mail Status Fileter click
            $(document).on('click', '.mail_send_count', function() {
                mailStatus = $(this).data('id');
                $('.mail_send_count').removeClass('colour_selected');
                $(this).addClass('colour_selected');
                table.ajax.reload();
            });


            // Handle click event for Email Show button
            $(document).on('click', '.emailModal', function() {
                var encId = $(this).data('id');
                // Ajax request to fetch data for the modal
                $.ajax({
                    url: "{{ route('contacted-lead.show') }}",
                    data: {encId: encId},
                    type: "GET",
                    success: function(response) {
                        $('#emailModal .lead_email_content').append(response.mail_content);
                        $('#emailModal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                        // Handle error
                    }
                });
            });

            $('#emailModal').on('hide.bs.modal', function (e) {
                $('#emailModal .modal-body').html('');
            });
        });
    </script>
@endsection
