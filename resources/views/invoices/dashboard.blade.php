@extends('layouts.app')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('app-assets/css/custom/pages/dashboard.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" href="{{ asset('app-assets/vendors/css/charts/apexcharts.css?v=' . config('versions.css')) }}">
@endsection

@section('content')
    <div class="dashboard-users-list">
        <div class="row">
            <div class="col">
                <div class="card card-revenue-budget">
                    <div class="row mx-0">
                        <div class="col-12 revenue-report-wrapper">
                            <div class="d-sm-flex justify-content-between align-items-center mb-3">
                                <h4 class="card-title mb-50 mb-sm-0">Sales Report
                                    ({{ config('custom.invoice_dashboard_currency') }})</h4>
                                <div class="d-flex" style="min-width: 360px;">
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
                                        <select name="sales_person_id" id="sales_person_id">
                                            <option value="" selected>All</option>
                                            @foreach ($sales_people as $sales_person)
                                                <option value="{{ $sales_person->id }}">{{ $sales_person->full_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="ms-1" style="min-width: 80px;">
                                        <select name="payment_status" id="payment_status">
                                            <option value="{{App\Enums\InvoicePaymentStatus::ALL}}">ALL</option>
                                            <option value="{{App\Enums\InvoicePaymentStatus::PAID}}">Paid</option>
                                            <option value="{{App\Enums\InvoicePaymentStatus::UNPAID}}">Unpaid</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="d-flex align-items-center me-2">
                                        <span class="bullet font-small-3 me-50 cursor-pointer"
                                            style="background-color: #008ffb;"></span>
                                        <span>With VAT</span>
                                    </div>
                                    <div class="d-flex align-items-center ms-75">
                                        <span class="bullet  font-small-3 me-50 cursor-pointer"
                                            style="background-color: #feb019;"></span>
                                        <span>Without VAT</span>
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

        {{-- VAT Amount Chart --}}
        <div class="col">
            <div class="card card-revenue-budget">
                <div class="row mx-0">
                    <div class="col-12 revenue-report-wrapper">
                        <div class="d-sm-flex justify-content-between align-items-center mb-3">
                            <h4 class="card-title mb-50 mb-sm-0">Vat Report
                                ({{ config('custom.invoice_dashboard_currency') }})</h4>
                            <div class="d-flex" style="min-width: 360px;">
                                <div class="me-1">
                                    <select name="vatGraphYear" id="vatGraphYear">
                                        @foreach ($graph_years as $year)
                                            <option value="{{ $year }}"
                                                {{ $year == date('Y') ? 'selected' : '' }}>
                                                {{ $year }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex-grow-1">
                                    <select name="company_id" id="company_id">
                                        <option value="" selected>All</option>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="d-flex align-items-center ms-75">
                                        <span class="bullet  font-small-3 me-50 cursor-pointer"
                                              style="background-color: #999ffb;"></span>
                                    <span>VAT Amount</span>
                                </div>
                            </div>
                        </div>
                        <div id="vat-report-chart"></div>
                    </div>
                </div>
            </div>
        </div>
        {{-- End VAT Amount Chart --}}
    </div>
@endsection

@section('page-vendor-js')
    <script src="{{ asset('app-assets/vendors/js/charts/apexcharts.min.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/vendors/js/extensions/moment.min.js?v=' . config('versions.js')) }}"></script>
@endsection

@section('custom-js')
    <script>
        $(document).ready(function() {
            var chart;
            async function renderChart() {
                try {
                    let response = await fetch(route('invoices.dashboard.graph-data', {
                        year: $('#graphYear').val(),
                        sales_person_id: $('#sales_person_id').val(),
                        payment_status: $('#payment_status').val()
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
                        let cur_symbol = data?.['currency_symbol'] ?? '';
                        let lineData = data?.['tooltips']?.[seriesIndex]?.[dataPointIndex] ?? [];
                        let with_vat = data?.['with_vat']?.[dataPointIndex] ?? 0;
                        let without_vat = data?.['without_vat']?.[dataPointIndex] ?? 0;
                        let headerTitle = seriesIndex == 0 ? 'With VAT' : 'Without VAT';
                        let headerCount = seriesIndex == 0 ? with_vat : without_vat;
                        headerCount = parseFloat(headerCount) ?? headerCount;
                        headerCount = headerCount?.toLocaleString(
                            'us', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2,
                            }) ?? headerCount;

                        return '<div>' +
                            '<header class="bg-light pt-50 pb-25 ps-1">' +
                            '<b>' + headerTitle + ': </b>' + cur_symbol + headerCount +
                            '</header>' +
                            '<hr class="my-0">' +
                            '<div  class="p-50 ps-1">' +
                            Object.keys(lineData)?.map(
                                keyName => {
                                    let amount = lineData?.[keyName] ?? 0;
                                    amount = parseFloat(amount) ?? amount;
                                    amount = amount?.toLocaleString(
                                        'us', {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2,
                                        }) ?? amount;

                                    return '<span class="py-2 font-small-3">' + '<b>' +
                                        keyName +
                                        ":</b> " +
                                        cur_symbol + amount +
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
                                events: {
                                    dataPointMouseEnter: function(event) {
                                        event.target.style.cursor = "pointer";
                                    },
                                    dataPointSelection: function(event, chartContext, config) {
                                        var dataPointIndex = config.dataPointIndex ?? 0;
                                        var seriesIndex = config.seriesIndex ?? 0;
                                        var month = config.w?.config?.xaxis?.categories[
                                                dataPointIndex] ??
                                            undefined;
                                        var year = $('#graphYear').val();

                                        const startDate = moment(`${month} ${year}`, "MMMM YYYY")
                                            .startOf(
                                                "month")
                                            .format("DD/MM/YYYY");
                                        const endDate = moment(`${month} ${year}`, "MMMM YYYY").endOf(
                                            "month").format(
                                            "DD/MM/YYYY");

                                        var params = {
                                            created_at: 'custom',
                                            created_at_start: startDate,
                                            created_at_end: endDate,
                                            filter_payment: $('#payment_status').val(),
                                        };

                                        var url = route('invoices.index', params)
                                        window.location.href = url;
                                    }
                                },
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
                                    // columnWidth: '90%',
                                    // endingShape: 'rounded',
                                },
                                distributed: true,
                            },
                            colors: ['#008ffb', '#feb019'],
                            series: [{
                                    name: 'With VAT',
                                    data: data.with_vat
                                },
                                {
                                    name: 'Without VAT',
                                    data: data.without_vat
                                }
                            ],
                            dataLabels: {
                                enabled: true,
                                style: {
                                    colors: data['colors']
                                },
                                formatter: function(value, {
                                    seriesIndex,
                                    dataPointIndex,
                                    w
                                }) {
                                    return value > 0 ? '' : value;
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
                                    name: 'With VAT',
                                    data: data.with_vat
                                },
                                {
                                    name: 'Without VAT',
                                    data: data.without_vat
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

            var vatChart
            async function vatRenderChart() {
                try {
                    let response = await fetch(route('invoices.dashboard.vat-graph-data', {
                        year: $('#vatGraphYear').val(),
                        company_id: $('#company_id').val(),
                    }));
                    if (!response.ok) {
                        throw new Error(response.status);
                    }
                    let data = await response.json();
                    if (!vatChart) {
                        var chartOptions = {
                            chart: {
                                height: 450,
                                width: "100%",
                                type: 'bar',
                                toolbar: {
                                    show: false
                                },
                                events: { },
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
                            colors: '#999ffb',
                            series: [{
                                name: 'VAT Amount',
                                data: data.vat_amt
                            }
                            ],
                            plotOptions: {
                                bar: {
                                    borderRadius: 10,
                                    dataLabels: {
                                        position: 'top', // top, center, bottom
                                    },
                                }
                            },
                            dataLabels: {
                                enabled: true,
                                formatter: function (val) {
                                    return "£" + parseFloat(val).toFixed(2);
                                },
                                offsetY: -20,
                                style: {
                                    colors: data['colors'],
                                    fontSize: '16px'
                                }
                            },
                            legend: {
                                show: false
                            },
                            grid: {
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
                                "enabled": false
                            },
                        };
                        vatChart = new ApexCharts(document.querySelector('#vat-report-chart'), chartOptions);
                        await vatChart.render();
                        window.dispatchEvent(new Event("resize"));
                    } else {
                        await vatChart.updateOptions({
                            xaxis: {
                                categories: data.month_name
                            },
                            series: [{
                                name: 'VAT Amount',
                                data: data.vat_amt
                            }
                            ],
                            tooltip: {
                                "enabled": false
                            },
                        });
                    }
                } catch (error) {
                    console.error(error);
                    toastr.error(error.message ?? null, error);
                }
            }

            $(document).on('select2:open', (e) => {
                const selectId = e.target.id;
                $(".select2-search__field[aria-controls='select2-"+selectId+"-results']").each(function (key,value,){
                    value.focus();
                });
            });
            renderChart();

            var $textMutedColor = '#b9b9c3';
            $('#graphYear').wrap('<div class="position-relative"></div>');
            $('#graphYear').select2({
                dropdownAutoWidth: true,
                width: '100%',
                dropdownParent: $('#graphYear').parent()
            });

            $('#sales_person_id').wrap('<div class="position-relative"></div>');
            $('#sales_person_id').select2({
                dropdownAutoWidth: true,
                width: '100%',
                dropdownParent: $('#sales_person_id').parent()
            });

            $('#graphYear').on('change', function(e) {
                renderChart();
            });
            $('#sales_person_id').on('change', function(e) {
                renderChart();
            });

            $('#payment_status').on('change', renderChart);
            $('#payment_status').wrap('<div class="position-relative"></div>');
            $('#payment_status').select2({
                dropdownAutoWidth: true,
                width: '100%',
                dropdownParent: $('#payment_status').parent()
            });

            vatRenderChart();
            var $textMutedColor = '#b9b9c3';
            $('#vatGraphYear').wrap('<div class="position-relative"></div>');
            $('#vatGraphYear').select2({
                dropdownAutoWidth: true,
                width: '100%',
                dropdownParent: $('#vatGraphYear').parent()
            });

            $('#vatGraphYear').on('change', function(e) {
                vatRenderChart();
            });

            $('#company_id').wrap('<div class="position-relative"></div>');
            $('#company_id').select2({
                dropdownAutoWidth: true,
                width: '100%',
                dropdownParent: $('#company_id').parent()
            });

            $('#company_id').on('change', function(e) {
                vatRenderChart();
            });
        });
    </script>
@endsection
