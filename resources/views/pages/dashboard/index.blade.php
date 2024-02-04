@extends('layouts.master')
<title>{{ localize('global.home_page') }}</title>
@section('content')
    <div class="content-wrapper">
        <!-- Content -->

        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <div class="col-md-12 mb-2">
                    <div class="card h-100">

                        <div class="card-body pb-0">
                        <div class="col-md-3">
                                <select id="sender_department_id" name="sender_department_id" onchange="getDep()"
                                    class="recipient_search select2 form-select" data-allow-clear="true">
                                    <option value="">{{ localize('global.documents.sender_department') }}</option>
                                </select>
                            </div>
                            <div id="lineChart" style="height: 400px; width:100%;"></div>
                        </div>
                    </div>
                </div>
                {{-- <div class="col-md-12 mb-2">
                    <div class="card h-100">
                        <div class="card-body pb-0">
                            <div id="heatMapChart" style="height: 250px; width:100%;"></div>
                        </div>
                    </div>
                </div> --}}
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body pb-0">
                            <div id="funnelChart" style="height: 400px; width:100%;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body pb-0">
                            <div id="timeChart" style="height: 400px; width:100%;"></div>
                        </div>
                    </div>
                </div>




                <div class="head-label ">
                    <h5 class="card-header mb-4 text-center display-6">{{ localize('global.notices') }}</h5>
                </div>
                @foreach ($active_notices as $notice)
                    <div class="col-lg-4 mb-4">
                        <div class="card">

                            <div class="d-flex align-items-center row">
                                <div class="col-8">
                                    <div class="card-body">
                                        <h5 class="card-title text-primary text-center">{{ $notice->title }}</h5>
                                        <p class="mb-4">
                                            {{ Str::limit($notice->description, 20) }}
                                        </p>
                                        @php
                                            $date = \Carbon\Carbon::parse($notice->expire_date);
                                            $totalDuration = $date->DiffInSeconds(\Carbon\Carbon::now());
                                        @endphp

                                        <p class="mb-4 text-center"><span
                                                  class="text-danger">{{ \Carbon\CarbonInterval::seconds($totalDuration)->cascade()->forHumans() }}</span>
                                        </p>
                                        <a href="{{ route('notices.show', $notice->id) }}"><button type="button"
                                                    class="btn btn-primary">{{ localize('global.show') }}</button></a>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <img src="/assets/img/illustrations/prize-light.png" width="90" height="130"
                                         class="rounded-start" alt="View Sales">
                                </div>
                            </div>
                        </div>

                    </div>
                @endforeach

            </div>
        </div>
        <!-- / Content -->

        <!-- Footer -->
        @include('layouts.partial.footer')
        <!-- / Footer -->

        <div class="content-backdrop fade"></div>
    </div>
@endsection

@push('custom-js')
    <script src="{{ asset('assets/js/echarts.js') }}"></script>


    <script type="text/javascript">
        var counts = @json($counts);
        var updatedCounts = @json($updatedCounts);
        var deletedCounts = @json($deletedCounts);
        var allDays = Object.keys(counts);

        var chart = echarts.init(document.getElementById('lineChart'));
        var option = {
            title: {
                text: "{{ localize('global.orders_by_day') }}",
                textStyle: {
                    color: '#333',
                    fontSize: 20,
                    fontWeight: 'bold',
                    fontFamily: 'persian_font'
                },
                left: 'center' // Center the title
            },
            tooltip: {
                trigger: 'axis'
            },
            xAxis: {
                type: 'category',
                data: allDays,
                name: "{{ localize('global.days') }}", // Label for X axis
                axisLabel: {
                    textStyle: {
                        color: '#666'
                    }
                }
            },
            yAxis: {
                type: 'value',
                name: "{{ localize('global.orders_count') }}", // Label for Y axis
                axisLabel: {
                    textStyle: {
                        color: '#666'
                    }
                }
            },
            legend: {
                top: 30, // Adjust the top position of the legend
                textStyle: {
                    color: '#666',
                    fontFamily: 'persian_font',
                    fontSize: 16
                },
                data: [{
                        name: "{{ localize('global.total_orders') }}",
                        icon: 'circle'
                    },
                    {
                        name: "{{ localize('global.updated_orders') }}",
                        icon: 'circle'
                    },
                    {
                        name: "{{ localize('global.deleted_orders') }}",
                        icon: 'circle'
                    }
                ]
            },
            color: ['#696cff', '#8592a3', '#ff3e1d'], // Specify the colors here

            series: [{
                    name: "{{ localize('global.total_orders') }}",
                    data: Object.values(counts),
                    type: 'line',
                    symbol: 'circle',
                    symbolSize: 8,
                    lineStyle: {
                        color: '#696cff', // Replace the color here
                        width: 2
                    },
                    itemStyle: {
                        color: '#696cff' // Replace the color here
                    },
                    areaStyle: {
                        color: 'rgba(105, 108, 255, 0.3)' // Replace the color here
                    },
                    smooth: true
                },
                {
                    name: "{{ localize('global.updated_orders') }}",
                    data: Object.values(updatedCounts),
                    type: 'line',
                    symbol: 'circle',
                    symbolSize: 8,
                    lineStyle: {
                        color: '#8592a3', // Replace the color here
                        width: 2
                    },
                    itemStyle: {
                        color: '#8592a3' // Replace the color here
                    },
                    areaStyle: {
                        color: 'rgba(133, 146, 163, 0.3)' // Replace the color here
                    },
                    smooth: true
                },
                {
                    name: "{{ localize('global.deleted_orders') }}",
                    data: Object.values(deletedCounts),
                    type: 'line',
                    symbol: 'circle',
                    symbolSize: 8,
                    lineStyle: {
                        color: '#ff3e1d', // Replace the color here
                        width: 2
                    },
                    itemStyle: {
                        color: '#ff3e1d' // Replace the color here
                    },
                    areaStyle: {
                        color: 'rgba(255, 62, 29, 0.3)' // Replace the color here
                    },
                    smooth: true
                }
            ]
        };

        chart.setOption(option);
    </script>



    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the chart
            var funnelChart = echarts.init(document.getElementById('funnelChart'));

            // Retrieve the order counts from the Blade view's data
            var statuses = @json($statuses);

            // Prepare the data for the funnel chart
            var data = [];
            for (var status in statuses) {
                var statusName = getStatusName(status); // Replace with your own logic to get the status name
                data.push({
                    value: statuses[status],
                    name: statusName
                });
            }

            // Set the options for the funnel chart
            var options = {
                tooltip: {
                    trigger: 'item',
                    formatter: "{a} <br/>{b}: {c}"
                },
                legend: {
                    orient: 'horizontal',
                    top: 'top',
                    textStyle: {
                        color: '#555',
                        fontSize: 16,
                        fontFamily: 'persian_font'
                    },
                    data: Object.values(data).map(item => item.name)
                },
                calculable: true,
                series: [{
                    name: 'اسناد',
                    type: 'funnel',
                    width: '80%',
                    height: '80%',
                    left: '10%',
                    right: '10%',
                    bottom: '10%',
                    minSize: '20%',
                    maxSize: '70%',
                    sort: 'descending',
                    gap: 10,
                    funnelAlign: 'center', // Align the funnel chart in the center
                    label: {
                        show: true,
                        position: 'inside',
                        formatter: function(params) {
                            return params.data.name + ' (' + params.data.value + ')';
                        },
                        fontSize: 16,
                        color: '#fff', // Set label text color
                        fontFamily: 'persian_font'
                    },
                    labelLine: {
                        show: false
                    },
                    itemStyle: {
                        color: function(params) { // Set color dynamically based on index
                            var colors = ['#ff3e1d', '#8592a3', '#71dd37', '#fd7e14','#ffc107'];
                            return colors[params.dataIndex % colors.length];
                        },
                        borderColor: '#fff',
                        borderWidth: 1
                    },
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: 14
                        }
                    },
                    data: data
                }]
            };

            // Set the options and render the funnel chart
            funnelChart.setOption(options);
        });

        // Function to get the status name based on the status value
        function getStatusName(status) {
            switch (status) {
                case '0':
                    return "{{ localize('global.ajra_nashoda') }}";
                case '1':
                    return "{{ localize('global.under_ajra') }}";
                case '2':
                    return "{{ localize('global.ajra_shoda') }}";
                case '3':
                    return "{{ localize('global.waiting_for_instruction') }}";
                case '4':
                    return "{{ localize('global.waiting_for_sign') }}";
                default:
                    return '';
            }
        }
    </script>


    <script type="text/javascript">
        var chart = echarts.init(document.getElementById('timeChart'));
        var data = @json($data);

        var formatHour = function(hour) {
            var suffix = hour < 12 ? 'AM' : 'PM';
            hour = hour % 12 || 12;
            return hour + ':00 ' + suffix;
        };

        var option = {
            title: {
                text: "{{ localize('global.orders_by_hours') }}",
                textStyle: {
                    color: '#333',
                    fontSize: 16,
                    fontWeight: 'bold',
                    fontFamily: 'persian_font'
                },
                left: 'center' // Center the title
            },
            tooltip: {
                trigger: 'axis',
                formatter: '{b}: {c} orders'
            },
            legend: {
                data: ["{{ localize('global.orders') }}"],
                textStyle: {
                    color: '#666',
                    fontFamily: 'persian_font'
                }
            },
            xAxis: {
                type: 'category',
                boundaryGap: false,
                data: data.map(function(item) {
                    return formatHour(item[0]); // Format hour with AM/PM
                }),
                axisLabel: {
                    textStyle: {
                        color: '#666'
                    }
                }
            },
            yAxis: {
                type: 'value',
                name: "{{ localize('global.orders_created_today') }}", // Y-axis label
                axisLabel: {
                    textStyle: {
                        color: '#666'
                    }
                }
            },
            series: [{
                name: "{{ localize('global.orders_created_today') }}",
                type: 'line',
                smooth: true,
                symbol: 'circle',
                symbolSize: 8,
                lineStyle: {
                    color: '#696cff',
                    width: 2
                },
                itemStyle: {
                    color: '#696cff'
                },
                areaStyle: {
                    color: 'rgba(105, 108, 255, 0.3)'
                },
                data: data.map(function(item) {
                    return item[1]; // Order count
                })
            }]
        };

        chart.setOption(option);
    </script>



    {{-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            var chartDom = document.getElementById('heatMapChart');
            var myChart = echarts.init(chartDom);
            var option;

            option = {
                title: {
                    text: "{{ localize('global.created_orders_by_month') }}",
                    top: 0,
                    left: 'center',
                    textStyle: {
                        fontSize: 16,
                        fontWeight: 'bold',
                        fontFamily: 'persian_font'
                    }
                },
                tooltip: {
                    formatter: function(params) {
                        if (params.value[1] !== 0) {
                            return "{{ localize('global.date') }} " + params.value[0] + '<br/>' +
                                "{{ localize('global.orders_count') }} " + params.value[1];
                        }
                    }
                },
                visualMap: {
                    show: false,
                    min: 0,
                    max: "{{ $maxValue = !empty($heatMapData) ? max(array_column($heatMapData, 1)) : 0 }}",
                    calculable: true,
                    orient: 'horizontal',
                    left: 'center',
                    bottom: 0,
                    inRange: {
                        color: ['#E0D1FF', '#C0A2FF', '#9373FF', '#6951FF', '#4230FF']
                    },
                    textStyle: {
                        color: '#333'
                    }
                },
                calendar: {
                    range: [new Date().getFullYear()],
                    itemStyle: {
                        borderWidth: 0.5, // Set the border width to 0 to remove borders
                        borderColor: '#696cff' // Set border color to transparent

                    },
                    splitLine: {
                        show: true,
                        lineStyle: {
                            color: '#696cff', // Set the color of the split lines between months
                            width: 1.5
                        }
                    },
                    splitArea: {
                        show: true,
                        areaStyle: {
                            color: 'green' // Set the color of the split areas between months
                        }
                    }
                },
                series: {
                    type: 'heatmap',
                    coordinateSystem: 'calendar',
                    data: @json($heatMapData)
                }
            };

            option && myChart.setOption(option);

        });
    </script> --}}

    <script>
        // Add an event listener to the dropdown menu
        // document.getElementById('sender_department_id').addEventListener('change', function() {
        //     // Get the selected month value
        //     var selectedMonth = this.value;
        //     alert(selectedMonth);

        //     // Redirect to the current page with the selected month parameter
        //     window.location.href = '{{ route("home") }}?month=' + selectedMonth;
        // });

        function getDep() {
           var selectedDep = $('#sender_department_id').val();

           window.location.href = '{{ route("home_filter")}}/'+selectedDep;
        } 
 
    $(document).ready(function() {
            $(".recipient_search").select2({
                ajax: {
                    url: "{{ route('recipients.get-search') }}",
                    type: "GET",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchTerm: params.term // search term
                        };
                    },
                    processResults: function(response) {
                        return {
                            results: response
                        };
                    },
                    cache: true
                }
            });
        });
    </script>
@endpush
