@extends('layouts.app')

@section('vendor-css')
    <link rel="stylesheet"
          href="{{ asset('app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css?v=' . config('versions.css')) }}">
@endsection

@section('page-css')
    <link rel="stylesheet" href="{{ asset('app-assets/css/custom/pages/dashboard.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" href="{{ asset('app-assets/vendors/css/charts/apexcharts.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" href="{{ asset('app-assets/css/plugins/forms/pickers/form-flat-pickr.css?v=' . config('versions.css')) }}">
@endsection

@section('custom-css')
    <style>
        #review-lead-table .flatpickr-wrapper{
            width: 100%;
        }
        .modal-custom {
            max-width: 40% !important; /* Adjust the width as needed */
        }
    </style>
@endsection

@section('content')
    {{-- <div class="container-xxl dashboard-users-list"> --}}
    <div class="dashboard-users-list">
        @unlessrole('User')
        <div class="row">
            {{-- <div class="col-lg-5 row">
                <div class="col-lg-6">

                    <div class="card card-animate overflow-hidden">
                        <div class="position-absolute start-0" style="z-index: 0;">
                            <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 140" width="200"
                                height="140">
                                <style>
                                    .s0 {
                                        opacity: .05;
                                        fill: #3cd188;
                                    }
                                </style>
                                <path id="Shape 8" class="s0"
                                    d="m189.5-25.8c0 0 20.1 46.2-26.7 71.4 0 0-60 15.4-62.3 65.3-2.2 49.8-50.6 59.3-57.8 61.5-7.2 2.3-60.8 0-60.8 0l-11.9-199.4z">
                                </path>
                            </svg>
                        </div>
                        <div class="card-body" style="z-index:1 ;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="dashboard-users-list__title"> Current Month</p>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-0"><span
                                            class="dashboard-users-list__count"
                                            data-leads-count="{{ $leads_count ?? 0 }}">0</span></h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <div id="total_jobs" data-colors="[&quot;--vz-success&quot;]" class="apex-charts"
                                        dir="ltr" style="min-height: 88.5px;">
                                        <div id="apexcharts434kl9plk"
                                            class="apexcharts-canvas apexcharts434kl9plk apexcharts-theme-light"
                                            style="">
                                            <div class="avatar bg-light-warning avatar-lg">
                                                <span class="avatar-content">
                                                    <img src="{{ asset('app-assets/images/logo/icon-logo.svg') }}"
                                                        alt="Logo" width="100%">
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                        <a class="position-absolute bottom-0 end-0 mb-1 me-1"
                            href="{{ route('leads.index', ['created_at' => 'month']) }}"
                            style="z-index: 1;"
                            >
                            View
                            <i data-feather="arrow-right"></i>
                        </a>
                    </div>

                </div>
                @if (isset($users))
                    @foreach ($users as $user)
                        <div class="col-lg-6">
                            <div class="card card-animate overflow-hidden">
                                <div class="position-absolute start-0" style="z-index: 0;">
                                    <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 140"
                                        width="200" height="140">
                                        <style>
                                            .s0 {
                                                opacity: .05;
                                                fill: #3cd188;
                                            }
                                        </style>
                                        <path id="Shape 8" class="s0"
                                            d="m189.5-25.8c0 0 20.1 46.2-26.7 71.4 0 0-60 15.4-62.3 65.3-2.2 49.8-50.6 59.3-57.8 61.5-7.2 2.3-60.8 0-60.8 0l-11.9-199.4z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="card-body" style="z-index:1 ;">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="dashboard-users-list__title">
                                                {{ $user->full_name ?? '' }}</p>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-0"><span
                                                    class="dashboard-users-list__count"
                                                    data-leads-count="{{ $user->leads_count ?? 0 }}">0</span></h4>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <div id="total_jobs" data-colors="[&quot;--vz-success&quot;]"
                                                class="apex-charts" dir="ltr" style="min-height: 88.5px;">
                                                <div id="apexcharts434kl9plk"
                                                    class="apexcharts-canvas apexcharts434kl9plk apexcharts-theme-light"
                                                    style="">
                                                    <div class="avatar bg-light-warning avatar-lg">
                                                        <span class="avatar-content">
                                                            @if ($profilePic = $user->pic)
                                                                <img src="{{ url('storage/' . $profilePic) }}"
                                                                    alt="{{ $user->full_name ?? '' }} Profile Picture"
                                                                    width="100%">
                                                            @else
                                                                <i data-feather="user" class="font-large-1"></i>
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div><!-- end card body -->
                            </div>
                        </div>
                    @endforeach
                @endif
            </div> --}}

            <!-- Graph -->
            {{-- <div class="col-lg-7 "> --}}
            <div class="col">
                <div class="card card-revenue-budget">
                    <div class="row mx-0">
                        <div class="col-12 revenue-report-wrapper">
                            <div class="d-sm-flex justify-content-between align-items-center mb-3">
                                <h4 class="card-title mb-50 mb-sm-0">Leads Report</h4>
                                <div class="d-flex" style="width: 260px">
                                    <div class="me-1">
                                        <select name="graphYear" id="graphYear">
                                            @foreach ($graph_years as $year)
                                                <option value="{{ $year }}"
                                                    {{ $year == date('Y') ? 'selected' : '' }}>
                                                    {{ $year }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex-grow-1">
                                        <select name="lead_source_id" id="lead_source_id">
                                            <option value="" selected>All</option>
                                            @foreach ($sources as $source)
                                                <option value="{{ $source->id }}">{{ $source->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="d-flex align-items-center me-2">
                                        <span class="bullet bullet-warning font-small-3 me-50 cursor-pointer"></span>
                                        <span>Total</span>
                                    </div>
                                    <div class="d-flex align-items-center ms-75">
                                        <span class="bullet bullet-success font-small-3 me-50 cursor-pointer"></span>
                                        <span>Won</span>
                                    </div>
                                </div>
                            </div>
                            <div id="revenue-report-chart"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!--/ Graph -->
        </div>
        @endunlessrole

        @if(Auth::user()->active_workspace->slug === 'iih-global')
        @unlessrole('Marketing')
        <div class="row">
            <div class="col-12 col-xl-6 col-sm-12 order-1 order-lg-2 mb-4 mb-lg-0">
                <div class="card">
                    <div class="card-datatable table-responsive">
                        <div class="row ms-2 me-3">
                            <div class="col-12 col-md-6 d-flex align-items-center justify-content-center justify-content-md-start">
                                <div class="dataTables_length" id="DataTables_Table_0_length">
                                    <div class="winning_counter">
                                        <h4 style="color: #f6931d">Winning Counts</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 d-flex align-items-center justify-content-end flex-column flex-md-row pe-3 gap-md-2">
                                <div class="form-group @if (!request('won_created_at_start') && !request('won_created_at_end')) d-none @endif">
                                    <div class="input-group winning_custom_filter" style="margin-top: 10px;">
                                        <input type="text" class="form-control form-control-sm text-end pe-1" name="won_filter_created_at_range" id="won_filter_created_at_range">
                                    </div>
                                </div>
                                <div class="winning_filter">
                                    <select id="winning_filter_create_at" class="form-select">
                                        <option value="all">All</option>
                                        <option value="won_this_year" selected>This Year</option>
                                        <option value="won_previous_year" {{ request()->input('created_at') === 'previous_year' ? 'selected' : '' }}>Previous Year</option>
                                        <option value="won_custom" {{ request()->input('created_at') === 'custom' ? 'selected' : '' }}>Custom</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <table class="won-lead-table datatables-projects {{--table--}} border-top dataTable no-footer dtr-column"
                            id="won-lead-table" aria-describedby="DataTables_Table_0_info" style="width: 922px;">
                            <thead class="border-top">
                            <tr>
                                <th tabindex="0" colspan="1" style="width: 600px;">Name</th>
                                <th tabindex="0" class="text_position" style="width: 280px;">Total Won Leads</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6 col-sm-12 order-1 order-lg-2 mb-4 mb-lg-0">
                <div class="card">
                    <div class="card-datatable table-responsive">
                        <div class="row ms-2 me-3">
                            <div class="col-12 col-md-6 d-flex align-items-center justify-content-center justify-content-md-start">
                                <div class="dataTables_length" id="DataTables_Table_0_length">
                                    <div class="kudos_counter">
                                        <h4 style="color: #28c76f">Kudos Counter</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 d-flex align-items-center justify-content-end flex-column flex-md-row pe-3 gap-md-2">
                                <div class="form-group @if (!request('created_at_start') && !request('created_at_end')) d-none @endif">
                                    <div class="input-group custom_filter" style="margin-top: 10px;">
                                        <input type="text" class="form-control form-control-sm text-end pe-1" name="filter_created_at_range" id="filter_created_at_range">
                                    </div>
                                </div>
                                <div class="kudos_filter">
                                    <select id="kudos_filter_create_at" class="form-select">
                                        <option value="all">All</option>
                                        <option value="this_year" selected>This Year</option>
                                        <option value="previous_year" {{ request()->input('created_at') === 'previous_year' ? 'selected' : '' }}>Previous Year</option>
                                        <option value="custom" {{ request()->input('created_at') === 'custom' ? 'selected' : '' }}>Custom
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <table class="review-lead-table datatables-projects {{--table--}} border-top dataTable no-footer dtr-column"
                            id="review-lead-table" aria-describedby="DataTables_Table_0_info" style="width: 922px;">
                            <thead class="border-top">
                            <tr>
                                <th tabindex="0" colspan="1" style="width: 600px;">Name</th>
                                <th tabindex="0" class="text_position" style="width: 280px;">Total Reviews</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endunlessrole
        @endif
    </div>

    <div class="modal fade" id="wonLeadModal" tabindex="-1" aria-labelledby="leadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-custom"> <!-- Added modal-custom class for custom width -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="leadModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="LeadModalBody">
                    <div class="table-responsive">
                        <table class="table dt-head-right" id="wonLeadsTable">
                            <thead>
                                <tr>
                                    <th>
                                        Source
                                    </th>
                                    <th>
                                        Lead Name
                                    </th>
                                    <th>
                                        Won At
                                    </th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="kudosModal" tabindex="-1" aria-labelledby="kudosLeadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-custom"> <!-- Added modal-custom class for custom width -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="kudosLeadModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="kudosLeadModalBody">
                    <div class="table-responsive">
                        <table class="table dt-head-right" id="kudosTable">
                            <thead>
                                <tr>
                                    <th>
                                        Review Is
                                    </th>
                                    <th>
                                        Client Name
                                    </th>
                                    <th>
                                        Review
                                    </th>
                                    <th>
                                        Review Date
                                    </th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('page-vendor-js')
    <script src="{{ asset('app-assets/vendors/js/charts/apexcharts.min.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/vendors/js/extensions/moment.min.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/js/custom/flatpickr.js?v=' . config('versions.js')) }}"></script>
@endsection

@section('custom-js')
    <script>
        $(document).ready(function() {
            var userRoles = @json(auth()->user()->hasRole('Marketing'));

            var chartEvents = {};

            /* Winning Lead data */
            var wonFilterCreatedAtRangeFormGroup = $('#won_filter_created_at_range').closest('.form-group');

            var won_filter_created_at_range_picker = $('#won_filter_created_at_range').flatpickr({
                mode: 'range',
                dateFormat: 'd/m/Y',
                defaultDate: [
                    "{{ request('won_created_at_start', date('d/m/Y', strtotime('first day of this year'))) }}",
                    "{{ request('won_created_at_end', date('d/12/Y', strtotime('last day of this year'))) }}"
                ],
                onChange: function(selectedDates, dateStr, instance) {
                    redrawWinning();
                }
            });

            $(document).on('flatpickr:cleared', '#won_filter_created_at_range', function(e) {
                redrawWinning();
            });

            var redrawWinning = (paging = true) => winningLead && winningLead.draw(paging);

            /* User review data */
            var filterCreatedAtRangeFormGroup = $('#filter_created_at_range').closest('.form-group');

            var filter_created_at_range_picker = $('#filter_created_at_range').flatpickr({
                mode: 'range',
                dateFormat: 'd/m/Y',
                defaultDate: [
                    "{{ request('created_at_start', date('d/m/Y', strtotime('first day of this year'))) }}",
                    "{{ request('created_at_end', date('d/12/Y', strtotime('last day of this year'))) }}"
                ],
                onChange: function(selectedDates, dateStr, instance) {
                    redrawReviewLead();
                }
            });

            $(document).on('flatpickr:cleared', '#filter_created_at_range', function(e) {
                redrawReviewLead();
            });

            var redrawReviewLead = (paging = true) => reviewLead && reviewLead.draw(paging);

            /* Marketing User graph click event disable  */
            if (!userRoles) {
                chartEvents.dataPointMouseEnter= function(event) {
                    event.target.style.cursor = "pointer";
                },
                chartEvents.dataPointSelection = function(event, chartContext, config) {
                    var dataPointIndex = config.dataPointIndex ?? 0;
                    var seriesIndex = config.seriesIndex ?? 0;
                    var month = config.w?.config?.xaxis?.categories[
                            dataPointIndex] ??
                        undefined;
                    var year = $('#graphYear').val();
                    var filter_lead_source_id = $('#lead_source_id').val();

                    const startDate = moment(`${month} ${year}`, "MMMM YYYY")
                        .startOf(
                            "month")
                        .format("DD/MM/YYYY");
                    const endDate = moment(`${month} ${year}`, "MMMM YYYY").endOf(
                        "month").format(
                        "DD/MM/YYYY");

                    var params = {
                        created_at: 'custom',
                        filter_lead_source_id,
                        created_at_start: startDate,
                        created_at_end: endDate
                    };
                    if (seriesIndex) {
                        params = {
                            won_at_start: startDate,
                            won_at_end: endDate,
                            filter_lead_source_id,
                        };
                        params['lead_status_id'] = 13;
                    }
                    var url = route('leads.index', params)
                    window.location.href = url;
                }
            }

            $('.dashboard-users-list__count').each(function() {
                $(this).prop('Counter', 0).animate({
                    Counter: $(this).data('leads-count')
                }, {
                    duration: 1000,
                    easing: 'swing',
                    step: function(now) {
                        $(this).text(Math.ceil(now));
                    }
                });
            });
            var chart;
            $(document).on('select2:open', (e) => {
                const selectId = e.target.id;
                $(".select2-search__field[aria-controls='select2-"+selectId+"-results']").each(function (key,value,){
                    value.focus();
                });
            });

            async function renderChart() {
                try {
                    let response = await fetch(route('dashboard.graph-data', {
                        year: $('#graphYear').val(),
                        lead_source_id: $('#lead_source_id').val()
                    }));
                    if (!response.ok) {
                        throw new Error(response.status);
                    }
                    let data = await response.json();
                    let customTooltip = function({
                        series,
                        seriesIndex,
                        dataPointIndex,
                        w
                    }) {
                        let lineData = data['tooltips']?.[seriesIndex]?.[dataPointIndex] ?? [];
                        let total = data?.['total']?.[dataPointIndex] ?? 0;
                        let won = data?.['won']?.[dataPointIndex] ?? 0;
                        let headerTitle = seriesIndex == 0 ? 'Total' : 'Won';
                        let headerCount = seriesIndex == 0 ? total : won;

                        return '<div>' +
                            '<header class="bg-light pt-50 pb-25 ps-1">' +
                            '<b>' + headerTitle + ': </b>' + headerCount +
                            '</header>' +
                            '<hr class="my-0">' +
                            '<div  class="p-50 ps-1">' +
                            lineData?.map(
                                dataobj => {
                                    let keys = Object.keys(dataobj);
                                    if (!keys?.length) {
                                        return '';
                                    }
                                    return '<span class="py-2 font-small-3">' + '<b>' +
                                        keys?.[0] +
                                        ":</b> " +
                                        dataobj?.[keys?.[0]] +
                                        '</span>';
                                }).join('<br/>') +
                            '</div>' +
                            '</div>';
                    };
                    if (!chart) {
                        var chartOptions = {
                            chart: {
                                height: 450,
                                width: "100%",
                                type: 'bar',
                                toolbar: {
                                    show: false
                                },
                                events: chartEvents,
                                animations: {
                                    enabled: true,
                                    easing: 'easeinout',
                                    speed: 800,
                                    animateGradually: {
                                        enabled: false,
                                    },
                                    dynamicAnimation: {
                                        enabled: false,
                                    }
                                }
                            },
                            plotOptions: {
                                bar: {
                                    // columnWidth: '30%',
                                    endingShape: 'rounded',
                                },
                                distributed: true
                            },
                            colors: [window.colors.solid.warning, window.colors.solid.success],
                            series: [{
                                    name: 'Total',
                                    data: data.total
                                },
                                {
                                    name: 'Won',
                                    data: data.won
                                }
                            ],
                            dataLabels: {
                                enabled: true,
                                style: {
                                    colors: data['colors']
                                }
                            },
                            legend: {
                                show: false
                            },
                            grid: {
                                // padding: {
                                //     top: -20,
                                //     bottom: -10
                                // },
                                yaxis: {
                                    lines: {
                                        show: false
                                    }
                                }
                            },
                            xaxis: {
                                categories: data.month_name,
                                labels: {
                                    style: {
                                        colors: $textMutedColor,
                                        fontSize: '0.86rem'
                                    }
                                },
                                axisTicks: {
                                    show: false
                                },
                                axisBorder: {
                                    show: false
                                }
                            },
                            yaxis: {
                                labels: {
                                    style: {
                                        colors: $textMutedColor,
                                        fontSize: '0.86rem'
                                    }
                                }
                            },
                            tooltip: {
                                custom: customTooltip
                            },
                        };
                        chart = new ApexCharts(document.querySelector('#revenue-report-chart'), chartOptions);
                        await chart.render();
                        window.dispatchEvent(new Event("resize"));
                    } else {
                        await chart.updateOptions({
                            xaxis: {
                                categories: data.month_name
                            },
                            series: [{
                                    name: 'Total',
                                    data: data.total
                                },
                                {
                                    name: 'Won',
                                    data: data.won
                                }
                            ],
                            tooltip: {
                                custom: customTooltip,
                            },
                        });
                    }
                } catch (error) {
                    console.error(error);
                    toastr.error(error.message ?? null, error);
                }
            }

            /* User Role then not call below function*/
            var Role_user = @json(auth()->user()->hasRole('User'));
            if(!Role_user){
                renderChart();
            }

            var $textMutedColor = '#b9b9c3';
            $('#graphYear').wrap('<div class="position-relative"></div>');
            $('#graphYear').select2({
                dropdownAutoWidth: true,
                width: '100%',
                dropdownParent: $('#graphYear').parent()
            });

            $('#lead_source_id').wrap('<div class="position-relative"></div>');
            $('#lead_source_id').select2({
                dropdownAutoWidth: true,
                width: '100%',
                dropdownParent: $('#lead_source_id').parent()
            });

            $('#graphYear').on('change', function(e) {
                renderChart();
            });
            $('#lead_source_id').on('change', function(e) {
                renderChart();
            });
            var baseUrl = '{{ url('storage/') }}';

            /* Won lead data table and filter */
            var winningLead = $('.won-lead-table').DataTable({
                serverSide: true,
                processing: true,
                searching: false,
                ordering: false,
                info: false,
                paging: false,
                ajax: {
                    url: route('dashboard.won_lead'),
                    method: 'GET',
                    data: function (d) {
                        d.winning_filter_create_at = $('#winning_filter_create_at').val();
                        d.won_filter_created_at_range = $('#won_filter_created_at_range').val();
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        if (xhr.status === 401) {
                            // Redirect to the login page if unauthorized
                            window.location.href = "{{ route('login') }}";
                        } else {
                            // Handle other errors
                            toastr.error(null,'Session hase been expire!');
                        }
                    },
                },
                drawCallback: function (settings) {
                    var api = this.api()
                    api.state.save();
                },
                initComplete: function (settings, json) {
                    $('.won-table-wrapper div.dataTables_length select').addClass(
                        'form-select-sm');
                },
                columns: [{
                    data: 'user_name', name: 'name',
                    render: function (data, type, row, meta) {
                        var cap_image = '{{ asset('app-assets/images/icons/cap_image.svg') }}';
                        var imageUrl = row.pic ? baseUrl + '/' + row.pic : '{{ asset('app-assets/images/icons/user.svg') }}';
                        var name = row.user_name;
                        var isFirst = meta.row < 1;

                        return '<div class="profile_image"><div class="lead_count_image"><img src="' + imageUrl + '" class="round profile-picture"></div><div class="cap_image"> ' +
                            (isFirst && meta.row == 0 && row.count != null && row.count != 0 ? '<img src="' + cap_image + '" class="profile-picture"> ' : '') +
                            '</div><label>' + name + '</label></div>';
                    }
                },
                    {data: 'count', name: 'count',
                        render: function(data, type, row, meta) {
                            var count = row.count ?? '0';
                            // return count;
                            return '<a href="#" class="count-clickable" data-id="' + row.id + '" data-user="' + row.first_name + '">' + count + '</a>';
                        }
                    },
                ],
                dom: '<"d-flex justify-content-between align-items-center header-actions mx-1 row mt-50 won-table-wrapper"r' +
                    '<"col-sm-12 col-lg-4 d-flex justify-content-center justify-content-lg-start" l>' +
                    '<"col-sm-12 col-lg-8 ps-xl-75 ps-0"<"dt-action-buttons d-flex align-items-center justify-content-center justify-content-lg-end flex-lg-nowrap flex-wrap"<"me-1"B>f>>' +
                    '>t' +
                    '<"d-flex justify-content-between mx-2 row mb-1"' +
                    '<"col-sm-12 col-md-6"i>' +
                    '<"col-sm-12 col-md-6"p>' +
                    '>',
                buttons: [],
            });

            var leadsDataTable, redrawleadsTable = (paging = false) => leadsDataTable && leadsDataTable
                .draw(paging);

            const leadsDTConfig = (assigneId) => ({
                serverSide: true,
                processing: true,
                searching: false,
                bLengthChange: false,
                drawCallback: function(settings) {
                    var json = this.api().ajax.json();
                },
                ajax: {
                    url: route('won-lead'),
                    data: function(d) {
                        d.assigneId = assigneId;  // Add the assigneId to the request data
                        d.winning_filter_create_at = $('#winning_filter_create_at').val();
                        d.won_filter_created_at_range = $('#won_filter_created_at_range').val();
                    }
                },
                order: [
                    [2, 'desc']
                ],
                columns: [{
                        data: 'lead_sources_title',
                    },{
                        data: 'lead_name',
                    }, {
                        data: 'won_at',
                        render: function(data, type, row) {
                            if (data) {
                                // Assuming data is in ISO format (YYYY-MM-DD HH:MM:SS)
                                const date = new Date(data);
                                const day = String(date.getDate()).padStart(2, '0');
                                const month = String(date.getMonth() + 1).padStart(2, '0'); // Month is 0-indexed
                                const year = date.getFullYear();
                                return `${day}/${month}/${year}`;
                            }
                            return '';
                        }
                    },
                ],
                dom: '<"d-flex justify-content-between align-items-center header-actions mx-2 row mt-75"r' +
                    '<"col-sm-12 col-lg-4 d-flex justify-content-center justify-content-lg-start" l>' +
                    '<"col-sm-12 col-lg-8 ps-xl-75 ps-0"<"dt-action-buttons d-flex align-items-center justify-content-center justify-content-lg-end flex-lg-nowrap flex-wrap"B<"me-1"f>>>' +
                    '>t' +
                    '<"d-flex justify-content-between row mb-1"' +
                    '<"col-sm-12 col-md-6"i>' +
                    '<"col-sm-12 col-md-6"p>' +
                    '>',
                buttons: [],
            });



            $(document).on('click', '.count-clickable', function(e) {
                e.preventDefault();
                var assigneId = $(this).data('id');
                var user_name = $(this).data('user');

                $.ajax({
                    url: '{{ route("check_session") }}',
                    type: 'GET',
                    success: function(response) {
                        if (response.status === 'authenticated') {
                        leadsDataTable = $('#wonLeadsTable').DataTable(leadsDTConfig(assigneId));
                        $('#leadModalLabel').text(user_name+"'s Won Leads");
                        $('#wonLeadModal').modal('show');
                        }
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        if (xhr.status === 401) {
                            // Redirect to the login page if unauthorized
                            window.location.href = "{{ route('login') }}";
                        } else {
                            // Handle other errors
                            toastr.error('Failed to check session status. Please try again.');
                        }
                    }
                });
            });

            $('#wonLeadModal').on('hide.bs.modal', function(e) {
                if ($.fn.DataTable.isDataTable('#wonLeadsTable')) {
                    $('#wonLeadsTable').DataTable().destroy();
                }
                $('#wonLeadsTable tbody').empty();
            });


            $('#winning_filter_create_at').select2({
                containerCssClass: 'select-sm',
                dropdownCssClass: 'select2-long-dropdown',
            });

            $('#winning_filter_create_at').on('change', function(e) {
                if ($(e.target).val() === 'won_custom') {
                    wonFilterCreatedAtRangeFormGroup.removeClass('d-none');
                } else {
                    wonFilterCreatedAtRangeFormGroup.addClass('d-none');
                }
                redrawWinning();
            });

            /* User Review Data table and filter */
            var reviewLead = $('.review-lead-table').DataTable({
                serverSide: true,
                processing: true,
                searching: false,
                ordering: false,
                info: false,
                paging: false,
                ajax: {
                    url: route('dashboard.review_user'),
                    method: 'GET',
                    data: function(d) {
                        d.kudos_filter_create_at = $('#kudos_filter_create_at').val();
                        d.filter_created_at_range = $('#filter_created_at_range').val();
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        if (xhr.status === 401) {
                            // Redirect to the login page if unauthorized
                            window.location.href = "{{ route('login') }}";
                        } else {
                            // Handle other errors
                            toastr.error(null,'Session hase been expire!');
                        }
                    },
                },
                drawCallback: function(settings) {
                    var api = this.api()
                    api.state.save();
                },
                initComplete: function(settings, json) {
                    $('.review-table-wrapper div.dataTables_length select').addClass(
                        'form-select-sm');
                },
                columns: [{
                    data: 'user_name', name: 'name',
                        render: function(data, type, row, meta) {
                            var shield_gold_1 = '{{ asset('app-assets/images/icons/shield_1.svg') }}';
                            var imageUrl = row.pic ? baseUrl + '/' + row.pic : '{{ asset('app-assets/images/icons/user.svg') }}';
                            var name = row.user_name;
                            var isFirstThree = meta.row < 1;

                            return '<div class="profile_image"><div class="won_lead_image"><img src="' + imageUrl + '" class="round profile-picture"></div><div class="shield_image">' +
                                (isFirstThree && meta.row == 0 && row.review_count != null && row.review_count != 0 ?  '<img src="' + shield_gold_1 + '" class="profile-picture"> ' : '') +
                                '</div>' +
                                '<label>' + name + '</label></div>';
                        }
                    },
                    {data: 'review_count', name: 'review_count',
                        render: function(data, type, row, meta) {
                            var review_count = row.review_count ?? '0';
                            // return count;
                            return '<a href="#" class="kudos-count-clickable" data-id="' + row.id + '" data-user="' + row.first_name + '">' + review_count + '</a>';
                        }
                    },
                ],
                dom: '<"d-flex justify-content-between align-items-center header-actions mx-1 row mt-50 review-table-wrapper"r' +
                    '<"col-sm-12 col-lg-4 d-flex justify-content-center justify-content-lg-start" l>' +
                    '<"col-sm-12 col-lg-8 ps-xl-75 ps-0"<"dt-action-buttons d-flex align-items-center justify-content-center justify-content-lg-end flex-lg-nowrap flex-wrap"<"me-1"B>f>>' +
                    '>t' +
                    '<"d-flex justify-content-between mx-2 row mb-1"' +
                    '<"col-sm-12 col-md-6"i>' +
                    '<"col-sm-12 col-md-6"p>' +
                    '>',
                buttons: [],
            });

            $('#kudos_filter_create_at').select2({
                containerCssClass: 'select-sm',
                dropdownCssClass: 'select2-long-dropdown',
            });

            var kudosDataTable, kudosredrawleadsTable = (paging = false) => kudosDataTable && kudosDataTable
                .draw(paging);

            const kudosDTConfig = (assigneId) => ({
                serverSide: true,
                processing: true,
                searching: false,
                bLengthChange: false,
                drawCallback: function(settings) {
                    var json = this.api().ajax.json();
                },
                ajax: {
                    url: route('kudos-list'),
                    data: function(d) {
                        d.assigneId = assigneId;  // Add the assigneId to the request data
                        d.kudos_filter_create_at = $('#kudos_filter_create_at').val();
                        d.filter_created_at_range = $('#filter_created_at_range').val();
                    }
                },
                order: [
                    [3, 'desc']
                ],
                columns: [{
                        data: 'review_is',
                    },{
                        data: 'client_name',
                    },{
                        data: 'review',
                        className: 'text-center',
                    }, {
                        data: 'review_date',
                        render: function(data, type, row) {
                            if (data) {
                                // Assuming data is in ISO format (YYYY-MM-DD HH:MM:SS)
                                const date = new Date(data);
                                const day = String(date.getDate()).padStart(2, '0');
                                const month = String(date.getMonth() + 1).padStart(2, '0'); // Month is 0-indexed
                                const year = date.getFullYear();
                                return `${day}/${month}/${year}`;
                            }
                            return '';
                        }
                    },
                ],
                dom: '<"d-flex justify-content-between align-items-center header-actions mx-2 row mt-75"r' +
                    '<"col-sm-12 col-lg-4 d-flex justify-content-center justify-content-lg-start" l>' +
                    '<"col-sm-12 col-lg-8 ps-xl-75 ps-0"<"dt-action-buttons d-flex align-items-center justify-content-center justify-content-lg-end flex-lg-nowrap flex-wrap"B<"me-1"f>>>' +
                    '>t' +
                    '<"d-flex justify-content-between row mb-1"' +
                    '<"col-sm-12 col-md-6"i>' +
                    '<"col-sm-12 col-md-6"p>' +
                    '>',
                buttons: [],
            });


            $(document).on('click', '.kudos-count-clickable', function(e) {
                e.preventDefault();
                var assigneId = $(this).data('id');
                var user_name = $(this).data('user');

                $.ajax({
                    url: '{{ route("check_session") }}',
                    type: 'GET',
                    success: function(response) {
                        if (response.status === 'authenticated') {
                        kudosDataTable = $('#kudosTable').DataTable(kudosDTConfig(assigneId));
                        $('#kudosLeadModalLabel').text(user_name+"'s Reviews");
                        $('#kudosModal').modal('show');
                        }
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        if (xhr.status === 401) {
                            // Redirect to the login page if unauthorized
                            window.location.href = "{{ route('login') }}";
                        } else {
                            // Handle other errors
                            toastr.error('Failed to check session status. Please try again.');
                        }
                    }
                });
            });

            $('#kudosModal').on('hide.bs.modal', function(e) {
                if ($.fn.DataTable.isDataTable('#kudosTable')) {
                    $('#kudosTable').DataTable().destroy();
                }
                $('#kudosTable tbody').empty();
            });

            $('#kudos_filter_create_at').on('change', function(e) {
                if ($(e.target).val() === 'custom') {
                    filterCreatedAtRangeFormGroup.removeClass('d-none');
                } else {
                    filterCreatedAtRangeFormGroup.addClass('d-none');
                }
                redrawReviewLead();
            });
        });
    </script>
@endsection
