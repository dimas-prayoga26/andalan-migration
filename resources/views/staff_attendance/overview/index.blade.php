@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <style>
        .attendance-overview-donut {
            position: relative;
            width: 240px;
            max-width: 100%;
            margin: 0 auto 1.5rem;
        }

        .attendance-overview-donut #pieChart {
            width: 100%;
        }

        .attendance-overview-donut #pieChart:empty {
            min-height: 180px;
        }

        .attendance-overview-donut-icon {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            pointer-events: none;
        }

        .attendance-progress-radial-chart {
            width: 100%;
            max-width: 300px;
            margin-inline: auto;
            overflow: hidden;
        }

        @media (max-width: 767.98px) {
            .attendance-rate-mobile-slider {
                display: flex;
                flex-wrap: nowrap;
                gap: 12px;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                -ms-overflow-style: none;
                scrollbar-width: none;
            }

            .attendance-rate-mobile-slider::-webkit-scrollbar {
                display: none;
                width: 0;
                height: 0;
            }

            .attendance-rate-mobile-slide {
                flex: 0 0 100%;
                width: 100%;
                max-width: 100%;
                scroll-snap-align: start;
            }

            #pieChart,
            #radialBar {
                max-width: 100%;
                overflow: hidden;
            }

            .attendance-overview-donut {
                width: 220px;
            }
        }

        .attendance-year-chart-wrapper {
            position: relative;
            width: 100%;
            height: 280px;
        }

        .attendance-year-chart-wrapper canvas {
            width: 100% !important;
            height: 100% !important;
        }

        @media (max-width: 767.98px) {
            .attendance-year-chart-wrapper {
                height: 260px;
            }

            .attendance-year-chart-card .card-body {
                padding-left: 12px;
                padding-right: 12px;
            }
        }
    </style>
@endsection

@section('navbarTitle', 'Overview')

@section('content')
@include('layouts.breadcrumb', [
    'title' => 'Overview',
    'current' => 'Overview',
    'homeRoute' => 'dashboard',
])

@include('staff_attendance.layouts.profile-index')

<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Overview</h5>
    </div>
</div>

@include('staff_attendance.components.card-analytics')

@php
    $attendanceOverviewMonthLabel = (string) ($profileAttendanceOverviewMonthLabel ?? now('Asia/Jakarta')->format('F'));
    $attendanceOverviewSeries = array_values($profileAttendanceOverviewSeries ?? [0, 0, 0, 0]);
    $attendanceOverviewChartSeries = array_sum($attendanceOverviewSeries) > 0 ? $attendanceOverviewSeries : [1];
    $attendanceOverviewChartColors = array_sum($attendanceOverviewSeries) > 0
        ? ['#27BC48', '#FF3282', '#1EA7C5', '#FFBC11']
        : ['#F2F3F8'];
    $attendanceProgressPercent = (float) ($profileAttendanceProgressPercent ?? 0);
    $attendanceDaysCount = (int) ($profileAttendanceDaysCount ?? 0);
    $elapsedWorkingDaysCount = (int) ($profileElapsedWorkingDaysCount ?? 0);
    $workingDaysCount = (int) ($profileWorkingDaysCount ?? 0);
    $progressOnTimePercent = (float) ($profileProgressOnTimePercent ?? 0);
    $progressOnTimeLabel = rtrim(rtrim(number_format($progressOnTimePercent, 2, '.', ''), '0'), '.');
    $progressOnTimeCount = (int) ($profileProgressOnTimeCount ?? 0);
    $progressOnTimeTotal = (int) ($profileProgressOnTimeTotal ?? $attendanceDaysCount);
    $progressLatePercent = (float) ($profileProgressLatePercent ?? 0);
    $progressLateLabel = rtrim(rtrim(number_format($progressLatePercent, 2, '.', ''), '0'), '.');
    $progressLateCount = (int) ($profileProgressLateCount ?? 0);
    $progressLateTotal = (int) ($profileProgressLateTotal ?? $attendanceDaysCount);
    $weeklyRequiredHours = (float) ($profileWeeklyRequiredHours ?? 0);
    $weeklyRequiredHoursTarget = (int) ($profileWeeklyRequiredHoursTarget ?? 40);
    $weeklyRequiredHoursPercent = (int) ($profileWeeklyRequiredHoursPercent ?? 0);
    $weeklyOvertimeHours = (float) ($profileWeeklyOvertimeHours ?? 0);
    $weeklyOvertimeHoursTarget = (int) ($profileWeeklyOvertimeHoursTarget ?? 18);
    $weeklyOvertimeHoursPercent = (int) ($profileWeeklyOvertimeHoursPercent ?? 0);
    $weeklyRequiredHoursLabel = rtrim(rtrim(number_format($weeklyRequiredHours, 2, '.', ''), '0'), '.');
    $weeklyOvertimeHoursLabel = rtrim(rtrim(number_format($weeklyOvertimeHours, 2, '.', ''), '0'), '.');
    $defaultYearMonthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $emptyYearSeries = array_fill(0, 12, 0);
    $yearChartYear = (int) ($profileYearChartYear ?? now('Asia/Jakarta')->year);
    $yearMonthLabels = array_values($profileYearMonthLabels ?? []);
    $yearMonthLabels = count($yearMonthLabels) === 12 ? $yearMonthLabels : $defaultYearMonthLabels;
    $yearAttendanceOnTimeSeries = array_values($profileYearAttendanceOnTimeSeries ?? $emptyYearSeries);
    $yearAttendanceLateSeries = array_values($profileYearAttendanceLateSeries ?? $emptyYearSeries);
    $yearAttendanceLeaveSeries = array_values($profileYearAttendanceLeaveSeries ?? $emptyYearSeries);
    $yearLeaveSeries = array_values($profileYearLeaveSeries ?? $emptyYearSeries);
    $yearSickSeries = array_values($profileYearSickSeries ?? $emptyYearSeries);
    $yearBusinessTripSeries = array_values($profileYearBusinessTripSeries ?? $emptyYearSeries);
    $yearOvertimeHoursSeries = array_values($profileYearOvertimeHoursSeries ?? $emptyYearSeries);
@endphp

<div class="row align-items-stretch">
    <div class="col-md-3 col-12 d-flex">
        <div class="row flex-fill">
            <div class="col-md-12 d-flex">
                <div class="card flex-fill">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title">Attendance Overview ({{ $attendanceOverviewMonthLabel }})</h4>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-center">
                        <div class="attendance-overview-donut">
                            <div id="pieChart"></div>
                            <div class="attendance-overview-donut-icon">
                                <svg width="39" height="74" viewBox="0 0 39 74" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M30.5325 18.9448C27.7921 15.402 23.5761 13.6 18.0001 13.6C12.4241 13.6 8.2081 15.402 5.4677 18.9448C0.082099 25.908 2.8701 36.9376 2.9925 37.4C3.34508 38.8603 4.81456 39.7583 6.27486 39.4057C7.71986 39.0568 8.61712 37.6123 8.2897 36.1624C8.2897 36.0808 6.6985 27.8596 10.3297 23.3988L10.5269 23.1676V36.6588L9.1669 65.1508C9.0921 66.6164 10.1934 67.8771 11.6557 68H11.8801C13.2659 68.0095 14.4372 66.9758 14.6001 65.5996L17.5309 40.8H18.4625L21.4001 65.5996C21.563 66.9758 22.7343 68.0095 24.1201 68H24.3513C25.8136 67.8771 26.9149 66.6164 26.8401 65.1508L25.4801 36.6588V23.1744L25.6637 23.392C29.3357 27.88 27.7037 36.074 27.7037 36.176C27.3657 37.6407 28.279 39.1021 29.7437 39.44C31.2084 39.778 32.6697 38.8647 33.0077 37.4C33.1301 36.9376 35.9181 25.908 30.5325 18.9448Z" fill="#ff9900"/>
                                    <path d="M18.0001 12.24C21.3801 12.24 24.1201 9.49998 24.1201 6.12C24.1201 2.74002 21.3801 0 18.0001 0C14.6201 0 11.8801 2.74002 11.8801 6.12C11.8801 9.49998 14.6201 12.24 18.0001 12.24Z" fill="#ff9900"/>
                                    <mask id="mask0" maskUnits="userSpaceOnUse" x="0" y="19" width="39" height="55">
                                    <path d="M0 26.0017C0 24.1758 1.37483 22.6428 3.18995 22.4448L3.26935 22.4361C4.23614 22.3306 5.1115 21.8163 5.67413 21.023L6.13877 20.3679C7.48483 18.4701 10.3941 18.7986 11.2832 20.9487L11.4217 21.2836C12.2534 23.2951 14.9783 23.5955 16.2283 21.8136C17.323 20.253 19.6329 20.247 20.7357 21.8019L21.5961 23.0149C22.4113 24.1642 23.7948 24.7693 25.1921 24.5877L28.4801 24.1603C34.0567 23.4354 39 27.7777 39 33.4012V54.5C39 65.2695 30.2696 74 19.5 74C8.73045 74 0 65.2696 0 54.5V26.0017Z" fill="#C4C4C4"/>
                                    </mask>
                                    <g mask="url(#mask0)">
                                    <path d="M30.5324 18.9448C27.792 15.402 23.576 13.6 18 13.6C12.424 13.6 8.20798 15.402 5.46758 18.9448C0.0819769 25.908 2.86998 36.9376 2.99238 37.4C3.34496 38.8603 4.81444 39.7583 6.27474 39.4057C7.71974 39.0568 8.617 37.6123 8.28958 36.1624C8.28958 36.0808 6.69838 27.8596 10.3296 23.3988L10.5268 23.1676V36.6588L9.16678 65.1508C9.09198 66.6164 10.1932 67.8771 11.6556 68H11.88C13.2658 68.0095 14.4371 66.9758 14.6 65.5996L17.5308 40.8H18.4624L21.4 65.5996C21.5628 66.9758 22.7341 68.0095 24.12 68H24.3512C25.8135 67.8771 26.9148 66.6164 26.84 65.1508L25.48 36.6588V23.1744L25.6636 23.392C29.3356 27.88 27.7036 36.074 27.7036 36.176C27.3656 37.6407 28.2789 39.1021 29.7436 39.44C31.2083 39.778 32.6696 38.8647 33.0076 37.4C33.13 36.9376 35.918 25.908 30.5324 18.9448Z" fill="#0B2A97"/>
                                    <path d="M17.9999 12.24C21.3799 12.24 24.12 9.49998 24.12 6.12C24.12 2.74002 21.3799 0 17.9999 0C14.62 0 11.8799 2.74002 11.8799 6.12C11.8799 9.49998 14.62 12.24 17.9999 12.24Z" fill="#0B2A97"/>
                                    </g>
                                </svg>
                            </div>
                        </div>
                        <ul class="row row-cols-2 g-2 list-unstyled mb-0 mx-auto w-100" style="max-width: 240px;">
                            <li class="col d-flex align-items-center gap-2">
                                <i class="fa fa-circle text-success"></i>
                                <span class="fs-12 text-black">On Time</span>
                            </li>
                            <li class="col d-flex align-items-center gap-2">
                                <i class="fa fa-circle text-danger"></i>
                                <span class="fs-12 text-black">Late</span>
                            </li>
                            <li class="col d-flex align-items-center gap-2">
                                <i class="fa fa-circle text-info"></i>
                                <span class="fs-12 text-black">Leave</span>
                            </li>
                            <li class="col d-flex align-items-center gap-2">
                                <i class="fa fa-circle text-warning"></i>
                                <span class="fs-12 text-black">Alpha</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-9 col-12 d-flex">
        <div class="card flex-fill">
            <div class="card-header flex-wrap border-0 pb-0">
                <h4 class="card-title">Progress ({{ $attendanceOverviewMonthLabel }})</h4>
            </div>
            <div class="card-body pt-0 pb-3">
                <div class="row align-items-center">
                    <div class="col-lg-4 mb-lg-0 mb-4 text-center radialBar">
                        <div id="radialBar" class="attendance-progress-radial-chart"></div>
                        <h4 class="fs-18 text-black">Days Worked ({{ $elapsedWorkingDaysCount }}/{{ $workingDaysCount }} Days)</h4>
                        <p class="fs-14">Tracking your scheduled attendance and active working days for the current month.</p>
                    </div>
                    <div class="col-lg-8">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center mb-sm-5 mb-3">
                                    <div class="position-relative me-3">
                                        <span class="donut" data-peity='{ "fill": ["var(--bs-success)", "var(--bs-light)"],   "innerRadius": 34, "radius": 10}'>{{ $progressOnTimeCount }}/{{ max($progressOnTimeTotal, 1) }}</span>

                                        <small class="position-absolute top-50 start-50 translate-middle">
                                            <i class="fa-solid fa-user-check fs-20 text-success" aria-hidden="true"></i>
                                        </small>
                                    </div>
                                    <div>
                                        <h4 class="fs-18 text-black">On Time ({{ $progressOnTimeLabel }}%)</h4>
                                        <span>{{ $progressOnTimeCount }} {{ Str::plural('Day', $progressOnTimeCount) }} / {{ $progressOnTimeTotal }} {{ Str::plural('Day', $progressOnTimeTotal) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center mb-sm-5 mb-3">
                                    <div class="position-relative me-3">
                                        <span class="donut" data-peity='{ "fill": ["var(--bs-danger)", "var(--bs-light)"],   "innerRadius": 34, "radius": 10}'>{{ $progressLateCount }}/{{ max($progressLateTotal, 1) }}</span>
                                        <small class="position-absolute top-50 start-50 translate-middle">
                                            <i class="fa-solid fa-clock fs-20 text-danger" aria-hidden="true"></i>
                                        </small>
                                    </div>
                                    <div>
                                        <h4 class="fs-18 text-black">Late ({{ $progressLateLabel }}%)</h4>
                                        <span>{{ $progressLateCount }} {{ Str::plural('Day', $progressLateCount) }} / {{ $progressLateTotal }} {{ Str::plural('Day', $progressLateTotal) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center mb-sm-5 mb-3">
                                    <div class="position-relative me-3">
                                        <span class="donut" data-peity='{ "fill": ["var(--bs-secondary)", "var(--bs-light)"],   "innerRadius": 34, "radius": 10}'>{{ $weeklyRequiredHours }}/{{ max($weeklyRequiredHoursTarget, 1) }}</span>
                                        <small class="position-absolute top-50 start-50 translate-middle">
                                            <i class="fa-solid fa-hourglass-half fs-20 text-secondary" aria-hidden="true"></i>
                                        </small>
                                    </div>
                                    <div>
                                        <h4 class="fs-18 text-black">Required Hours ({{ $weeklyRequiredHoursPercent }}%)</h4>
                                        <span>{{ $weeklyRequiredHoursLabel }} Hrs / {{ $weeklyRequiredHoursTarget }} Hrs Per Week</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center mb-sm-5 mb-3">
                                    <div class="position-relative me-3">
                                        <span class="donut" data-peity='{ "fill": ["var(--bs-info)", "var(--bs-light)"],   "innerRadius": 34, "radius": 10}'>{{ $weeklyOvertimeHours }}/{{ max($weeklyOvertimeHoursTarget, 1) }}</span>
                                        <small class="position-absolute top-50 start-50 translate-middle">
                                            <i class="fa-solid fa-business-time fs-20 text-info" aria-hidden="true"></i>
                                        </small>
                                    </div>
                                    <div>
                                        <h4 class="fs-18 text-black">Overtime Logged ({{ $weeklyOvertimeHoursPercent }}%)</h4>
                                        <span>{{ $weeklyOvertimeHoursLabel }} Hrs / {{ $weeklyOvertimeHoursTarget }} Hrs Per Week</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-12">
        <div class="row">
            <div class="col-md-12">
                <div class="card attendance-year-chart-card">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title">Attendance Overview ({{ $yearChartYear }})</h4>
                    </div>
                    <div class="card-body">
                        <div class="attendance-year-chart-wrapper">
                            <canvas id="barChart_3"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-12">
        <div class="row">
            <div class="col-md-12">
                <div class="card attendance-year-chart-card">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title">Leave Overview ({{ $yearChartYear }})</h4>
                    </div>
                    <div class="card-body">
                        <div class="attendance-year-chart-wrapper">
                            <canvas id="lineChart_3"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-12">
        <div class="row">
            <div class="col-md-12">
                <div class="card attendance-year-chart-card">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title">Business Trip Overview ({{ $yearChartYear }})</h4>
                    </div>
                    <div class="card-body">
                        <div class="attendance-year-chart-wrapper">
                            <canvas id="barChart_1"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-12">
        <div class="row">
            <div class="col-md-12">
                <div class="card attendance-year-chart-card">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title">Overtime Overview ({{ $yearChartYear }})</h4>
                    </div>
                    <div class="card-body">
                        <div class="attendance-year-chart-wrapper">
                            <canvas id="lineChart_2"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
        $chartJsPath = public_path('assets/vendor/chart-js/chart.bundle.min.js');
        $chartJsVersion = file_exists($chartJsPath) ? filemtime($chartJsPath) : time();
    @endphp
    <script src="{{ asset('assets/vendor/chart-js/chart.bundle.min.js') }}?v={{ $chartJsVersion }}"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
    <script>
        function isMobileChartViewport() {
            return window.matchMedia('(max-width: 767.98px)').matches;
        }

        function formatPercentageLabel(value) {
            var numericValue = Number(value) || 0;

            return numericValue.toFixed(2).replace(/\.?0+$/, '');
        }

        function canvasLegendOptions() {
            var isMobile = isMobileChartViewport();

            return {
                display: true,
                position: 'top',
                labels: {
                    boxWidth: isMobile ? 14 : 40,
                    padding: isMobile ? 10 : 16,
                    font: {
                        size: isMobile ? 10 : 12
                    }
                }
            };
        }

        function canvasAxisTickOptions() {
            var isMobile = isMobileChartViewport();

            return {
                autoSkip: true,
                maxTicksLimit: isMobile ? 6 : 12,
                maxRotation: isMobile ? 0 : 50,
                minRotation: 0,
                padding: isMobile ? 4 : 8,
                font: {
                    size: isMobile ? 10 : 12
                }
            };
        }

        function pieChart() {
            var chartElement = document.querySelector('#pieChart');
            if (!chartElement || typeof ApexCharts === 'undefined') {
                return;
            }

            new ApexCharts(chartElement, {
                series: @json($attendanceOverviewChartSeries),
                chart: {
                    type: 'donut',
                    height: 200,
                    parentHeightOffset: 0
                },
                labels: ['On Time', 'Late', 'Leave', 'Alpha'],
                legend: {
                    show: false
                },
                stroke: {
                    width: 0
                },
                colors: @json($attendanceOverviewChartColors),
                dataLabels: {
                    enabled: false
                },
                responsive: [{
                    breakpoint: 768,
                    options: {
                        chart: {
                            height: 180,
                            parentHeightOffset: 0
                        }
                    }
                }]
            }).render();
        }

        function radialBar() {
            var chartElement = document.querySelector('#radialBar');
            if (!chartElement || typeof ApexCharts === 'undefined') {
                return;
            }

            new ApexCharts(chartElement, {
                series: [{{ $attendanceProgressPercent }}],
                chart: {
                    height: 280,
                    type: 'radialBar',
                    offsetY: -10,
                    parentHeightOffset: 0
                },
                plotOptions: {
                    radialBar: {
                        startAngle: -135,
                        endAngle: 135,
                        dataLabels: {
                            name: {
                                fontSize: '16px',
                                offsetY: 120
                            },
                            value: {
                                offsetY: 0,
                                fontSize: '34px',
                                color: 'var(--bs-body-color)',
                                formatter: function (value) {
                                    return formatPercentageLabel(value) + '%';
                                }
                            }
                        }
                    }
                },
                fill: {
                    type: 'gradient',
                    colors: '#0B2A97',
                    gradient: {
                        shade: 'dark',
                        shadeIntensity: 0.15,
                        inverseColors: false,
                        opacityFrom: 1,
                        opacityTo: 1,
                        stops: [0, 50, 65, 91]
                    }
                },
                stroke: {
                    lineCap: 'round',
                    colors: '#0B2A97'
                },
                labels: [''],
                responsive: [{
                    breakpoint: 768,
                    options: {
                        chart: {
                            height: 230,
                            offsetY: -5
                        },
                        plotOptions: {
                            radialBar: {
                                dataLabels: {
                                    name: {
                                        fontSize: '14px',
                                        offsetY: 95
                                    },
                                    value: {
                                        fontSize: '28px'
                                    }
                                }
                            }
                        }
                    }
                }]
            }).render();
        }

        function donut() {
            if (typeof jQuery === 'undefined' || typeof jQuery.fn.peity === 'undefined') {
                return;
            }

            jQuery('span.donut').peity('donut', {
                width: 90,
                height: 90
            });
        }

        function createCanvasChart(chartId, configuration) {
            var chartElement = document.getElementById(chartId);
            if (!chartElement || typeof Chart === 'undefined') {
                return;
            }

            new Chart(chartElement.getContext('2d'), configuration);
        }

        function barChart_3() {
            createCanvasChart('barChart_3', {
                type: 'bar',
                data: {
                    labels: @json($yearMonthLabels),
                    datasets: [
                        {
                            label: 'On Time',
                            data: @json($yearAttendanceOnTimeSeries),
                            backgroundColor: '#27BC48'
                        },
                        {
                            label: 'Late',
                            data: @json($yearAttendanceLateSeries),
                            backgroundColor: '#FF3282'
                        },
                        {
                            label: 'Leave',
                            data: @json($yearAttendanceLeaveSeries),
                            backgroundColor: '#1EA7C5'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: canvasLegendOptions()
                    },
                    scales: {
                        x: {
                            stacked: true,
                            ticks: canvasAxisTickOptions()
                        },
                        y: {
                            beginAtZero: true,
                            stacked: true,
                            ticks: {
                                precision: 0,
                                font: canvasAxisTickOptions().font
                            }
                        }
                    }
                }
            });
        }

        function lineChart_3() {
            createCanvasChart('lineChart_3', {
                type: 'line',
                data: {
                    labels: @json($yearMonthLabels),
                    datasets: [
                        {
                            label: 'Leave',
                            data: @json($yearLeaveSeries),
                            borderColor: '#A02CFA',
                            backgroundColor: 'transparent',
                            tension: 0.4
                        },
                        {
                            label: 'Sick',
                            data: @json($yearSickSeries),
                            borderColor: '#FFBC11',
                            backgroundColor: 'transparent',
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: canvasLegendOptions()
                    },
                    scales: {
                        x: {
                            ticks: canvasAxisTickOptions()
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                font: canvasAxisTickOptions().font
                            }
                        }
                    }
                }
            });
        }

        function barChart_1() {
            createCanvasChart('barChart_1', {
                type: 'bar',
                data: {
                    labels: @json($yearMonthLabels),
                    datasets: [
                        {
                            label: 'Business Trips',
                            data: @json($yearBusinessTripSeries),
                            backgroundColor: '#0B2A97'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: canvasLegendOptions()
                    },
                    scales: {
                        x: {
                            ticks: canvasAxisTickOptions()
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                font: canvasAxisTickOptions().font
                            }
                        }
                    }
                }
            });
        }

        function lineChart_2() {
            createCanvasChart('lineChart_2', {
                type: 'line',
                data: {
                    labels: @json($yearMonthLabels),
                    datasets: [
                        {
                            label: 'Overtime Hours',
                            data: @json($yearOvertimeHoursSeries),
                            borderColor: '#1EA7C5',
                            backgroundColor: 'rgba(30, 167, 197, 0.15)',
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: canvasLegendOptions()
                    },
                    scales: {
                        x: {
                            ticks: canvasAxisTickOptions()
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                font: canvasAxisTickOptions().font
                            }
                        }
                    }
                }
            });
        }

        $(function () {
            $('.attendance-tab-btn').on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                var targetUrl = $(this).data('href');
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
            });

            pieChart();
            radialBar();
            donut();
            barChart_3();
            lineChart_3();
            barChart_1();
            lineChart_2();
        });
    </script>
@endsection
