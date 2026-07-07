<div class="card h-auto px-md-2 pt-md-2">

    @include('project_management.layouts.profile-header')
    
    @include('project_management.layouts.profile-navbar')
    
</div>

@once
    @php
        $profileApexChartsPath = public_path('assets/vendor/apexcharts/dist/apexcharts.min.js');
        $profileApexChartsVersion = file_exists($profileApexChartsPath) ? filemtime($profileApexChartsPath) : time();
    @endphp
    <script src="{{ asset('assets/vendor/apexcharts/dist/apexcharts.min.js') }}?v={{ $profileApexChartsVersion }}"></script>
    <script>
        (function () {
            function parseProfileChartData(value, fallback) {
                if (! value) {
                    return fallback;
                }

                try {
                    var parsedValue = JSON.parse(value);

                    return Array.isArray(parsedValue) ? parsedValue : fallback;
                } catch (error) {
                    return fallback;
                }
            }

            function isVisibleChart(chartElement) {
                return !!(chartElement.offsetWidth || chartElement.offsetHeight || chartElement.getClientRects().length);
            }

            function renderProfileProgressChart(chartElement) {
                if (! chartElement || chartElement.dataset.profileProgressRendered === 'true' || typeof window.ApexCharts === 'undefined') {
                    return;
                }

                var currentYear = new Date().getFullYear();
                var defaultLabels = Array.from({ length: 12 }, function (_, index) {
                    return new Date(currentYear, index, 1).toLocaleString('en-US', { month: 'long' });
                });
                var defaultSeries = Array.from({ length: 12 }, function () {
                    return 0;
                });
                var progressSeries = parseProfileChartData(chartElement.getAttribute('data-progress-series'), defaultSeries)
                    .map(function (value) {
                        var numericValue = Number(value);

                        return Number.isFinite(numericValue) ? numericValue : 0;
                    });
                var progressLabels = parseProfileChartData(chartElement.getAttribute('data-progress-labels'), defaultLabels)
                    .map(function (value) {
                        return String(value);
                    });

                if (progressSeries.length === 0) {
                    progressSeries = defaultSeries;
                }

                if (progressLabels.length !== progressSeries.length) {
                    progressLabels = defaultLabels.slice(0, progressSeries.length);
                }

                chartElement.innerHTML = '';
                chartElement.dataset.profileProgressRendered = 'true';

                new ApexCharts(chartElement, {
                    series: [
                        {
                            name: 'Task Completed',
                            data: progressSeries,
                        },
                    ],
                    chart: {
                        type: 'area',
                        height: 100,
                        toolbar: {
                            show: false,
                        },
                        zoom: {
                            enabled: false,
                        },
                        sparkline: {
                            enabled: true,
                        },
                    },
                    colors: [
                        'var(--bs-primary)',
                    ],
                    dataLabels: {
                        enabled: false,
                    },
                    legend: {
                        show: false,
                    },
                    stroke: {
                        show: true,
                        width: 2,
                        curve: 'straight',
                        colors: ['var(--bs-primary)'],
                    },
                    grid: {
                        show: false,
                        borderColor: '#eee',
                        padding: {
                            top: 0,
                            right: 0,
                            bottom: 0,
                            left: -1,
                        },
                    },
                    states: {
                        normal: {
                            filter: {
                                type: 'none',
                                value: 0,
                            },
                        },
                        hover: {
                            filter: {
                                type: 'none',
                                value: 0,
                            },
                        },
                        active: {
                            allowMultipleDataPointsSelection: false,
                            filter: {
                                type: 'none',
                                value: 0,
                            },
                        },
                    },
                    xaxis: {
                        categories: progressLabels,
                        axisBorder: {
                            show: false,
                        },
                        axisTicks: {
                            show: false,
                        },
                        labels: {
                            show: false,
                            style: {
                                fontSize: '12px',
                            },
                        },
                        crosshairs: {
                            show: false,
                            position: 'front',
                            stroke: {
                                width: 1,
                                dashArray: 3,
                            },
                        },
                        tooltip: {
                            enabled: false,
                            formatter: undefined,
                            offsetY: 0,
                            style: {
                                fontSize: '12px',
                            },
                        },
                    },
                    yaxis: {
                        show: false,
                    },
                    fill: {
                        opacity: 0.9,
                        colors: 'var(--bs-primary)',
                        type: 'gradient',
                        gradient: {
                            colorStops: [
                                {
                                    offset: 0,
                                    color: 'var(--bs-primary)',
                                    opacity: .4,
                                },
                                {
                                    offset: 0.6,
                                    color: 'var(--bs-primary)',
                                    opacity: .4,
                                },
                                {
                                    offset: 100,
                                    color: 'white',
                                    opacity: 0,
                                },
                            ],
                        },
                    },
                    tooltip: {
                        enabled: true,
                        style: {
                            fontSize: '12px',
                        },
                        y: {
                            formatter: function (value) {
                                return value + ' tasks';
                            },
                        },
                    },
                }).render();
            }

            function renderVisibleProfileProgressChart() {
                var chartTargets = Array.prototype.slice.call(document.querySelectorAll('#chartProfileProgressDesktop, #chartProfileProgress'));
                var visibleChartTarget = chartTargets.find(isVisibleChart) || chartTargets[0];

                renderProfileProgressChart(visibleChartTarget);
            }

            window.addEventListener('load', function () {
                renderVisibleProfileProgressChart();
            });

            window.addEventListener('resize', function () {
                renderVisibleProfileProgressChart();
            });

            renderVisibleProfileProgressChart();
        })();
    </script>
@endonce
