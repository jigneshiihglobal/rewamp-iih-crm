@extends('layouts.app')

@section('vendor-css')
    <link rel="stylesheet" href="{{asset('app-assets/vendors/bootstrap-datepicker/css/bootstrap-datepicker.min.css?v='.config('versions.css'))}}">
@endsection

@section('custom-css')
<link rel="stylesheet" href="{{asset('app-assets/css/custom/bootstrap-datepicker.css?v='.config('versions.css'))}}">
@endsection

@section('content')
    <section class="app-activities-list">
        <div class="card">
            @role('Admin|Superadmin')
            <div class="card-body border-bottom">
                <form class="row align-items-end" id="activityFilters">
                    <div class="form-group col-md-3">
                        <label for="user_id" class="form-label">User</label>
                        <select id="user_id" name="user_id" class="form-select select2 activity_filter">
                            <option value="" selected>All</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-5">
                        <label class="form-label">Created at</label>
                        <div class="input-group input-daterange">
                            <input type="text" class="form-control" value="{{date('d/m/Y', strtotime('-30 days'))}}" name="created_at_start" id="created_at_start" readonly >
                            <div class="input-group-addon mx-1 my-auto">to</div>
                            <input type="text" class="form-control" value="{{date('d/m/Y')}}" name="created_at_end" id="created_at_end" readonly >
                        </div>
                    </div>
                </form>
            </div>
            @endrole
            <div class="card-datatable table-responsive pt-0">
                <table class="activities-list-table table">
                    <thead class="table-light">
                        <tr>
                            <th>Description</th>
                            <th>User</th>
                            <th>Subject</th>
                            <th>IP</th>
                            <th>Workspace</th>
                            <th>Created at</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </section>
@endsection

@section('page-js')
    <script src="{{ asset('app-assets/vendors/bootstrap-datepicker/js/bootstrap-datepicker.min.js?v='.config('versions.js'))}}"></script>
@endsection

@section('custom-js')
    <script>
        $(document).ready(function () {
            var redrawActivitiesTable = (paging = false) => activitiesDatatable && activitiesDatatable.draw(paging);
            var activitiesDatatable = $('.activities-list-table').DataTable({
                serverSide: true,
                processing: true,
                pageLength: 25,
                ajax: {
                    url: route('activities.index'),
                    data: function (d) {
                        d.user_id= $('#user_id').val();
                        d.created_at_start= $('#created_at_start').val();
                        d.created_at_end= $('#created_at_end').val();
                    },
                    error: function (xhr, status, error) {
                        var errors = xhr.responseJSON.errors;
                        console.log(errors);
                        if (xhr.status === 401) {
                            // Redirect to the login page if unauthorized
                            window.location.href = "{{ route('login') }}";
                        }
                        if(errors && errors.length) {
                            $('.activities-list-table').validate().showErrors(errors);
                        }
                    },
                },
                order: [[5, 'DESC']],
                columns: [
                    { data: 'description' },
                    { data: 'causer' },
                    { data: 'subject' },
                    { data: 'ip_address' },
                    {
                        data: 'workspace.name',
                        name: 'workspace.name',
                        visible: @json(Auth::user()->hasRole(['Admin', 'Superadmin'])),
                        defaultContent: '',
                    },
                    { data: 'created_at'}
                ],
                dom: '<"d-flex justify-content-between align-items-center header-actions mx-2 row mt-75"r' +
                    '<"col-sm-12 col-lg-4 d-flex justify-content-center justify-content-lg-start" l>' +
                    '<"col-sm-12 col-lg-8 ps-xl-75 ps-0"<"dt-action-buttons d-flex align-items-center justify-content-center justify-content-lg-end flex-lg-nowrap flex-wrap"<"me-1"f>B>>' +
                    '>t' +
                    '<"d-flex justify-content-between mx-2 row mb-1"' +
                    '<"col-sm-12 col-md-6"i>' +
                    '<"col-sm-12 col-md-6"p>' +
                    '>',
                buttons: []
            });
            var select = $('.select2');
            select.each(function () {
                var $this = $(this);
                $this.select2();
            });

            $('#user_id, #created_at_start, #created_at_end').on('change', function () {
                redrawActivitiesTable(true);
            });

            $('.input-daterange').datepicker(
                {
                    format: 'dd/mm/yyyy',
                    autoclose: true,
                    inputs: $('.input-daterange .form-control')
                }
            );
            @unlessrole('Admin|Superadmin')
            if(activitiesDatatable) {
                activitiesDatatable.column(1).visible(false);
            }
            @endunlessrole
        });
    </script>
@endsection
