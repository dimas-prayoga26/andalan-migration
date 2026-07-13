@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <style>
        .admin-attendance-detail-avatar {
            align-items: center;
            aspect-ratio: 1 / 1;
            border: 1px solid #e2e7f0;
            border-radius: 50%;
            color: #0b2a97;
            display: inline-flex;
            font-size: 42px;
            font-weight: 700;
            height: 120px;
            justify-content: center;
            overflow: hidden;
            width: 120px;
        }

        .admin-attendance-detail-avatar img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }
    </style>
@endsection

@section('navbarTitle', 'Attendance')

@section('content')
@php
    $recapDetailEmployee = $recapDetailEmployee ?? [];
    $recapDetailMetrics = $recapDetailMetrics ?? [];
    $recapDetailCharts = $recapDetailCharts ?? [];
    $recapDetailAttendanceRows = $recapDetailAttendanceRows ?? collect();
    $recapDetailPeriodLabel = (string) ($recapDetailPeriodLabel ?? now('Asia/Jakarta')->format('F Y'));
    $recapDetailMonth = (int) ($recapDetailMonth ?? now('Asia/Jakarta')->month);
    $recapDetailYear = (int) ($recapDetailYear ?? now('Asia/Jakarta')->year);
    $recapDetailPeityValue = static fn (mixed $percent): string => max(0, min(100, (int) $percent)).'/100';
@endphp

@include('director_attendance.layout.navbar')

<!-- Start - Attendance -->
<div class="col-lg-12">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h5 class="mb-0">Attendance Details</h5>
	</div>
</div>		

<div class="row">
    
    <!-- Start - Portfolio -->
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body py-sm-5">
                <div class="text-center mb-3">
                    <div class="admin-attendance-detail-avatar mx-auto">
                        @if (! empty($recapDetailEmployee['avatar_url']))
                            <img src="{{ $recapDetailEmployee['avatar_url'] }}" alt="{{ $recapDetailEmployee['name'] ?? 'Employee' }}">
                        @else
                            <span>{{ $recapDetailEmployee['initials'] ?? '-' }}</span>
                        @endif
                    </div>
                    <div class="clearfix mt-3">
                        <h6 class="mb-0">{{ $recapDetailEmployee['name'] ?? '-' }}</h6>
                        <span>{{ $recapDetailEmployee['position'] ?? '-' }}</span> <br>
                        <span class="fw-semibold">{{ $recapDetailEmployee['department'] ?? '-' }}</span>
                        <span class="badge badge-sm light badge-danger fw-bold mt-1">{{ $recapDetailEmployee['company'] ?? '-' }}</span>
                    </div>
                </div>
                <div class="row py-1">
                    <div class="col-12">
                        <span>Employee ID :</span>
                    </div>
                    <div class="col-12">
                        <span>{{ $recapDetailEmployee['employee_code'] ?? '-' }}</span> <br>
                    </div>
                </div>
                <div class="row py-1">
                    <div class="col-12">
                        <span>Phone Number :</span>
                    </div>
                    <div class="col-12">
                        <span>{{ $recapDetailEmployee['phone'] ?? '-' }}</span> <br>
                    </div>
                </div>
                <div class="row py-1">
                    <div class="col-12">
                        <span>Email :</span>
                    </div>
                    <div class="col-12">
                        <span>{{ $recapDetailEmployee['email'] ?? '-' }}</span> <br>
                    </div>
                </div>
                <div class="row py-1">
                    <div class="col-12">
                        <span>Base :</span>
                    </div>
                    <div class="col-12">
                        <span>{{ $recapDetailEmployee['base'] ?? '-' }}</span> <br>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End - Portfolio -->

    <!-- Start - Account Setup -->
    <div class="col-md-9">
        <div class="card h-100">
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-2 col-6">
                        <div class="bg-success-subtle rounded px-3 py-2 text-center">
                            <span class="fs-14 text-success fw-semibold">On Time</span>
                            <h4 class="mb-0 fw-semibold">{{ $recapDetailMetrics['on_time'] ?? '0 days' }}</h4>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="bg-danger-subtle rounded px-3 py-2 text-center">
                            <span class="fs-14 text-danger fw-semibold fw-semibold">Late</span>
                            <h4 class="mb-0 fw-semibold">{{ $recapDetailMetrics['late'] ?? '0 days' }}</h4>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="bg-info-subtle rounded px-3 py-2 text-center">
                            <span class="fs-14 text-info fw-semibold">Leave</span>
                            <h4 class="mb-0 fw-semibold">{{ $recapDetailMetrics['leave'] ?? '0 days' }}</h4>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="bg-secondary-subtle rounded px-3 py-2 text-center">
                            <span class="fs-14 text-secondary fw-semibold">Deviation</span>
                            <h4 class="mb-0 fw-semibold">{{ $recapDetailMetrics['deviation'] ?? '0 days' }}</h4>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="bg-dark-subtle rounded px-3 py-2 text-center">
                            <span class="fs-14 text-black fw-semibold">Alpha</span>
                            <h4 class="mb-0 fw-semibold">{{ $recapDetailMetrics['alpha'] ?? '0 days' }}</h4>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="bg-info-subtle rounded px-3 py-2 text-center">
                            <span class="fs-14 text-info fw-semibold">Trip</span>
                            <h4 class="mb-0 fw-semibold">{{ $recapDetailMetrics['trip'] ?? '0 days' }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="bg-primary-subtle rounded px-3 py-2 text-center">
                            <span class="fs-14 text-primary fw-semibold">Annual Leave</span>
                            <h4 class="mb-0 fw-semibold">{{ $recapDetailMetrics['annual_leave'] ?? '0 days' }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="bg-warning-subtle rounded px-3 py-2 text-center">
                            <span class="fs-14 text-warning fw-semibold">Sick Leave</span>
                            <h4 class="mb-0 fw-semibold">{{ $recapDetailMetrics['sick_leave'] ?? '0 days' }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="bg-secondary-subtle rounded px-3 py-2 text-center">
                            <span class="fs-14 text-secondary fw-semibold">Special Leave</span>
                            <h4 class="mb-0 fw-semibold">{{ $recapDetailMetrics['special_leave'] ?? '0 days' }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="bg-light-subtle rounded px-3 py-2 text-center">
                            <span class="fs-14 text-dark fw-semibold">Unpaid Leave</span>
                            <h4 class="mb-0 fw-semibold">{{ $recapDetailMetrics['unpaid_leave'] ?? '0 days' }}</h4>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mt-3">
                        <h4 class="card-title">Progress ({{ $recapDetailPeriodLabel }})</h4>
                        <div class="row align-items-center">
                            <div class="col-lg-4 mb-lg-0 mb-3 text-center radialBar">
                                <div id="radialBar"></div>
                                <h4 class="fs-16 text-black">Days Worked <br> ({{ $recapDetailCharts['days_worked_label'] ?? '0/0 days' }})</h4>
                            </div>
                            <div class="col-lg-8">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center mb-sm-5 mb-3">
                                            <div class="position-relative me-3">
                                                <span class="donut" data-peity='{ "fill": ["var(--bs-success)", "var(--bs-light)"],   "innerRadius": 34, "radius": 10}'>{{ $recapDetailPeityValue($recapDetailCharts['on_time_percent'] ?? 0) }}</span>
                                                
                                                <small class="position-absolute top-50 start-50 translate-middle">
                                                    <i class="fa-solid fa-user-check fs-20 text-success" aria-hidden="true"></i>
                                                </small>
                                            </div>
                                            <div>
                                                <h4 class="fs-18 text-black">On Time <br> ({{ (int) ($recapDetailCharts['on_time_percent'] ?? 0) }}%)</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center mb-sm-5 mb-3">
                                            <div class="position-relative me-3">
                                                <span class="donut" data-peity='{ "fill": ["var(--bs-danger)", "var(--bs-light)"],   "innerRadius": 34, "radius": 10}'>{{ $recapDetailPeityValue($recapDetailCharts['late_percent'] ?? 0) }}</span>
                                                <small class="position-absolute top-50 start-50 translate-middle">
                                                    <i class="fa-solid fa-clock fs-20 text-danger" aria-hidden="true"></i>
                                                </small>
                                            </div>
                                            <div>
                                                <h4 class="fs-18 text-black">Late <br> ({{ (int) ($recapDetailCharts['late_percent'] ?? 0) }}%)</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center mb-sm-5 mb-3">
                                            <div class="position-relative me-3">
                                                <span class="donut" data-peity='{ "fill": ["var(--bs-secondary)", "var(--bs-light)"],   "innerRadius": 34, "radius": 10}'>{{ $recapDetailPeityValue($recapDetailCharts['monthly_hours_percent'] ?? 0) }}</span>
                                                <small class="position-absolute top-50 start-50 translate-middle">
                                                    <i class="fa-solid fa-hourglass-half fs-20 text-secondary" aria-hidden="true"></i>
                                                </small>
                                            </div>
                                            <div>
                                                <h4 class="fs-18 text-black">Monthly Hours <br> ({{ (int) ($recapDetailCharts['monthly_hours_percent'] ?? 0) }}%)</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center mb-sm-5 mb-3">
                                            <div class="position-relative me-3">
                                                <span class="donut" data-peity='{ "fill": ["var(--bs-info)", "var(--bs-light)"],   "innerRadius": 34, "radius": 10}'>{{ $recapDetailPeityValue($recapDetailCharts['overtime_percent'] ?? 0) }}</span>
                                                <small class="position-absolute top-50 start-50 translate-middle">
                                                    <i class="fa-solid fa-business-time fs-20 text-info" aria-hidden="true"></i>
                                                </small>
                                            </div>
                                            <div>
                                                <h4 class="fs-18 text-black">Overtime <br> ({{ (int) ($recapDetailCharts['overtime_percent'] ?? 0) }}%)</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    <!-- Start - Account Setup -->

</div>

<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header border-0 align-items-center gap-2 flex-wrap">
                <h4 id="recapDetailPeriodLabel" class="card-title m-0">Attendance Recap ({{ $recapDetailPeriodLabel }})</h4>
                <div class="clearfix d-flex align-items-center">
                    <form id="recapDetailPeriodFilter" method="GET" action="{{ route('director-attendance.attendance.detail-employees', ['employee' => $recapDetailEmployee['id'] ?? '']) }}" class="clearfix d-flex align-items-center">
                        <div class="clearfix me-1">
                            <select id="recapDetailMonthFilter" name="month" class="selectpicker form-select form-select-sm" aria-label="Select month">
                                @foreach (range(1, 12) as $month)
                                    <option value="{{ $month }}" @selected($recapDetailMonth === $month)>{{ \Illuminate\Support\Carbon::create($recapDetailYear, $month, 1)->format('F') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="clearfix">
                            <select id="recapDetailYearFilter" name="year" class="selectpicker form-select form-select-sm" aria-label="Select year">
                                @foreach (range((int) now('Asia/Jakarta')->year, (int) now('Asia/Jakarta')->year - 3) as $year)
                                    <option value="{{ $year }}" @selected($recapDetailYear === $year)>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button id="recapDetailPeriodFilterButton" type="button" class="btn btn-sm btn-primary light ms-2" title="Apply period" aria-label="Apply period"><i class="fa-solid fa-filter"></i></button>
                    </form>
                </div>
            </div>
            <div class="card-body table-card-body px-0 pt-0 pb-2">
                <div class="table-responsive">
                    <table class="table table-sm table-sm-responsive text-nowrap" id="recapDetailAttendanceTable">
                        <thead>
                            <tr>
                                <th class="mw-100">Date</th>
                                <th class="mw-80">Clock In</th>
                                <th class="mw-80">Clock Out</th>
                                <th class="mw-80">Note</th>
                                <th class="mw-80">Working Hours</th>
                                <th class="mw-160">Address</th>
                                <th class="mw-100">Attachment</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                        </tbody>
                    </table>
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
        $dataTablesJsPath = public_path('assets/vendor/datatables/js/jquery.dataTables.bundle.min.js');
        $dataTablesJsVersion = file_exists($dataTablesJsPath) ? filemtime($dataTablesJsPath) : time();
        $apexChartsPath = public_path('assets/vendor/apexcharts/dist/apexcharts.min.js');
        $apexChartsVersion = file_exists($apexChartsPath) ? filemtime($apexChartsPath) : time();
    @endphp
    <script src="{{ asset('assets/vendor/datatables/js/jquery.dataTables.bundle.min.js') }}?v={{ $dataTablesJsVersion }}"></script>
    <script src="{{ asset('assets/vendor/apexcharts/dist/apexcharts.min.js') }}?v={{ $apexChartsVersion }}"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
    <script>
		(function($) {
			/* "use strict" */


		var dzChartlist = function(){
			//let draw = Chart.controllers.line.__super__.draw; //draw shadow
			var screenWidth = $(window).width();
			
			var chartBar = function(){
				var optionsArea = {
				series: [{
					name: "Running",
					data: [20, 40, 20, 80, 40, 40]
				}
				],
				chart: {
				height: 400,
				type: 'area',
				group: 'social',
				toolbar: {
					show: false
				},
				zoom: {
					enabled: false
				},
				},
				dataLabels: {
				enabled: false
				},
				stroke: {
				width: [4],
				colors:['#A02CFA'],
				curve: 'smooth'
				},
				legend: {
					show:false,
				tooltipHoverFormatter: function(val, opts) {
					return val + ' - ' + opts.w.globals.series[opts.seriesIndex][opts.dataPointIndex] + ''
				},
				markers: {
					fillColors:['#A02CFA'],
					width: 19,
					height: 19,
					strokeWidth: 0,
					radius: 19
				}
				},
				markers: {
				strokeWidth: [4],
				strokeColors: ['#A02CFA'],
				border:0,
				colors:['#fff'],
				hover: {
					size: 6,
				}
				},
				xaxis: {
				categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
				labels: {
				style: {
					colors: 'var(--bs-body-color)',
					fontSize: '14px',
					fontFamily: 'Poppins',
					fontWeight: 500,
					
					},
				},
				},
				yaxis: {
					labels: {
					offsetX:-16,
				style: {
					colors: 'var(--bs-body-color)',
					fontSize: '14px',
					fontFamily: 'Poppins',
					fontWeight: 500,
					
					},
				},
				},
				fill: {
					colors:['#A02CFA'],
					type:'solid',
					opacity: 0.7
				},
				colors:['#A02CFA'],
				grid: {
				borderColor: 'var(--bs-body-bg)',
				xaxis: {
					lines: {
					show: true
					}
				}
				},
				responsive: [{
					breakpoint: 575,
					options: {
						chart: {
							height: 250,
						}
					}
				}]
				};
				var chartArea = new ApexCharts(document.querySelector("#chartBar"), optionsArea);
				chartArea.render();

			}

			var chartBar2 = function(){
				var optionsArea = {
				series: [{
					name: "Running",
					data: [40, 40, 30, 90, 10, 80]
				}
				],
				chart: {
				height: 400,
				type: 'area',
				group: 'social',
				toolbar: {
					show: false
				},
				zoom: {
					enabled: false
				},
				},
				dataLabels: {
				enabled: false
				},
				stroke: {
				width: [4],
				colors:['#FF3282'],
				curve: 'smooth'
				},
				legend: {
					show:false,
				tooltipHoverFormatter: function(val, opts) {
					return val + ' - ' + opts.w.globals.series[opts.seriesIndex][opts.dataPointIndex] + ''
				},
				markers: {
					fillColors:['#FF3282'],
					width: 19,
					height: 19,
					strokeWidth: 0,
					radius: 19
				}
				},
				markers: {
				strokeWidth: [4],
				strokeColors: ['#FF3282'],
				border:0,
				colors:['#fff'],
				hover: {
					size: 6,
				}
				},
				xaxis: {
				categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
				labels: {
				style: {
					colors: 'var(--bs-body-color)',
					fontSize: '14px',
					fontFamily: 'Poppins',
					fontWeight: 500,
					
					},
				},
				},
				yaxis: {
					labels: {
					offsetX:-16,
				style: {
					colors: 'var(--bs-body-color)',
					fontSize: '14px',
					fontFamily: 'Poppins',
					fontWeight: 500,
					
					},
				},
				},
				fill: {
					colors:['#FF3282'],
					type:'solid',
					opacity: 0.7
				},
				colors:['#FF3282'],
				grid: {
				borderColor: 'var(--bs-body-bg)',
				xaxis: {
					lines: {
					show: true
					}
				}
				},
				responsive: [{
					breakpoint: 575,
					options: {
						chart: {
							height: 250,
						}
					}
				}]
				};
				var chartArea = new ApexCharts(document.querySelector("#chartBar2"), optionsArea);
				chartArea.render();

			}

			var chartBar3 = function(){
				var optionsArea = {
				series: [{
					name: "Running",
					data: [20, 15, 50, 20, 50, 30]
				}
				],
				chart: {
				height: 400,
				type: 'area',
				group: 'social',
				toolbar: {
					show: false
				},
				zoom: {
					enabled: false
				},
				},
				dataLabels: {
				enabled: false
				},
				stroke: {
				width: [4],
				colors:['#FFBC11'],
				curve: 'smooth'
				},
				legend: {
					show:false,
				tooltipHoverFormatter: function(val, opts) {
					return val + ' - ' + opts.w.globals.series[opts.seriesIndex][opts.dataPointIndex] + ''
				},
				markers: {
					fillColors:['#FFBC11'],
					width: 19,
					height: 19,
					strokeWidth: 0,
					radius: 19
				}
				},
				markers: {
				strokeWidth: [4],
				strokeColors: ['#FFBC11'],
				border:0,
				colors:['#fff'],
				hover: {
					size: 6,
				}
				},
				xaxis: {
				categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
				labels: {
				style: {
					colors: 'var(--bs-body-color)',
					fontSize: '14px',
					fontFamily: 'Poppins',
					fontWeight: 500,
					
					},
				},
				},
				yaxis: {
					labels: {
					offsetX:-16,
				style: {
					colors: 'var(--bs-body-color)',
					fontSize: '14px',
					fontFamily: 'Poppins',
					fontWeight: 500,
					
					},
				},
				},
				fill: {
					colors:['#FFBC11'],
					type:'solid',
					opacity: 0.7
				},
				colors:['#FFBC11'],
				grid: {
				borderColor: 'var(--bs-body-bg)',
				xaxis: {
					lines: {
					show: true
					}
				}
				},
				responsive: [{
					breakpoint: 575,
					options: {
						chart: {
							height: 250,
						}
					}
				}] 
				};
				var chartArea = new ApexCharts(document.querySelector("#chartBar3"), optionsArea);
				chartArea.render();

			}
			
			var pieChart = function(){
				var options = {
				series: [2, 18, 1, 1],
				chart: {
				type: 'donut',
				height:200,
				},
				legend: {
					show:false,
				},
				fill:{
					colors:['#F94687', '#2BC155', '#A02CFA', '#1EA7C5']
				},
				stroke:{
					width:0,
				},
				colors:['#F94687', '#2BC155', '#A02CFA', '#1EA7C5'],
				dataLabels: {
				enabled: false
				}
				};

				var chart = new ApexCharts(document.querySelector("#pieChart"), options);
				chart.render();
			}
			
			var radialBar = function(){
				var options = {
				series: [{{ (int) ($recapDetailCharts['days_worked_percent'] ?? 0) }}],
				chart: {
				height: 280,
				type: 'radialBar',
				offsetY: -10
				},
				plotOptions: {
				radialBar: {
					startAngle: -135,
					endAngle: 135,
					dataLabels: {
					name: {
						fontSize: '16px',
						color: undefined,
						offsetY: 120
					},
					value: {
						offsetY: 0,
						fontSize: '34px',
						color: 'var(--bs-body-color)',
						formatter: function (val) {
						return val + "%";
						}
					}
					}
				}
				},
				fill: {
				type: 'gradient',
				colors:'#0B2A97',
				gradient: {
					shade: 'dark',
					shadeIntensity: 0.15,
					inverseColors: false,
					opacityFrom: 1,
					opacityTo: 1,
					stops: [0, 50, 65, 91]
				},
				},
				stroke: {
					lineCap: 'round',
					colors:'#0B2A97'
				},
				labels: [''],
				};

				var radialBarElement = document.querySelector("#radialBar");
				if (!radialBarElement || typeof ApexCharts === 'undefined') {
					return;
				}

				var chart = new ApexCharts(radialBarElement, options);
				chart.render();
			}
			var donutChart = function(){
				if (!$.fn.peity) {
					return;
				}

				$("span.donut").peity("donut", {
					width: "90",
					height: "90"
				});
			}	
			/* Function ============ */
				return {
					init:function(){
					},
					
					
					load:function(){
						radialBar();
						donutChart();
					},
					
					resize:function(){
						
					}
				}
			
			}();

			jQuery(document).ready(function(){
				var detailPeriodFilter = document.getElementById('recapDetailPeriodFilter');
				var detailMonthFilter = document.getElementById('recapDetailMonthFilter');
				var detailYearFilter = document.getElementById('recapDetailYearFilter');
				var periodLabel = document.getElementById('recapDetailPeriodLabel');
				var filterButton = document.getElementById('recapDetailPeriodFilterButton');

				if (!detailPeriodFilter) {
					return;
				}

				var escapeHtml = function(value) {
					return String(value || '')
						.replace(/&/g, '&amp;')
						.replace(/</g, '&lt;')
						.replace(/>/g, '&gt;')
						.replace(/"/g, '&quot;')
						.replace(/'/g, '&#039;');
				};
				var detailTable = jQuery('#recapDetailAttendanceTable').DataTable({
					ajax: {
						url: @json(route('director-attendance.attendance.detail-employees.datatable', ['employee' => $recapDetailEmployee['id'] ?? ''])),
						data: function(requestData) {
							requestData.month = detailMonthFilter ? detailMonthFilter.value : 0;
							requestData.year = detailYearFilter ? detailYearFilter.value : 0;
						},
						dataSrc: function(response) {
							if (periodLabel && response.period_label) {
								periodLabel.textContent = 'Attendance Recap (' + response.period_label + ')';
							}

							return response.data || [];
						}
					},
					autoWidth: false,
					searching: false,
					pageLength: 10,
					lengthChange: false,
					columns: [
						{
							data: 'date',
							render: function(data, type, row) {
								return type === 'sort' ? row.date_sort : escapeHtml(data);
							}
						},
						{
							data: 'clock_in',
							render: function(data, type, row) {
								if (type !== 'display') {
									return data;
								}

								return '<span class="badge badge-sm badge-' + escapeHtml(row.clock_in_badge) + ' light fw-semibold">' + escapeHtml(data) + '</span>';
							}
						},
						{
							data: 'clock_out',
							render: function(data, type, row) {
								if (type !== 'display') {
									return data;
								}

								return '<span class="badge badge-sm badge-' + escapeHtml(row.clock_out_badge) + ' light fw-semibold">' + escapeHtml(data) + '</span>';
							}
						},
						{ data: 'note', render: function(data) { return escapeHtml(data); } },
						{ data: 'working_hours', render: function(data) { return escapeHtml(data); } },
						{ data: 'location_address', render: function(data) { return escapeHtml(data); } },
						{ data: null, defaultContent: '', orderable: false, searchable: false }
					],
					order: [[0, 'asc']],
					language: {
						emptyTable: 'No attendance records available for this employee.',
						paginate: {
							next: '<i class="fa-solid fa-angle-right"></i>',
							previous: '<i class="fa-solid fa-angle-left"></i>'
						}
					}
				});
				var reloadDetailTable = function(){
					detailTable.ajax.reload();
				};

				jQuery(detailPeriodFilter).on('submit', function(event){
					event.preventDefault();
					reloadDetailTable();
				});
				if (detailMonthFilter) {
					detailMonthFilter.addEventListener('change', reloadDetailTable);
				}
				if (detailYearFilter) {
					detailYearFilter.addEventListener('change', reloadDetailTable);
				}
				if (filterButton) {
					filterButton.addEventListener('click', reloadDetailTable);
				}
			});
				
			jQuery(window).on('load',function(){
				setTimeout(function(){
					dzChartlist.load();
				}, 1000); 
				
			});

			jQuery(window).on('resize',function(){
				
				
			});     

		})(jQuery);
	</script>
@endsection
