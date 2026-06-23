@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <style>
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
        }

        .admin-attendance-person-avatar {
            align-items: center;
            aspect-ratio: 1 / 1;
            background: #eef3ff;
            border: 1px solid #d8e0ef;
            border-radius: 50% !important;
            box-sizing: border-box;
            color: #0b2a97;
            display: inline-flex !important;
            flex: 0 0 48px !important;
            font-size: 14px;
            font-weight: 700;
            height: 48px !important;
            justify-content: center;
            max-height: 48px;
            max-width: 48px;
            min-height: 48px;
            min-width: 48px !important;
            width: 48px !important;
        }

        .admin-attendance-empty {
            align-items: center;
            color: #8b8b9a;
            display: flex;
            font-size: 13px;
            min-height: 160px;
            justify-content: center;
            text-align: center;
        }

        @media (min-width: 768px) {
            .admin-attendance-overview-column {
                display: flex;
            }

            .admin-attendance-overview-column > .row {
                flex: 1;
            }

            .admin-attendance-overview-column > .row > [class*="col-"] {
                display: flex;
            }

            .admin-attendance-overview-card {
                display: flex;
                flex-direction: column;
                min-height: 430px;
                width: 100%;
            }

            .admin-attendance-overview-card > .card-body {
                flex: 1;
            }

            .admin-attendance-summary-card > .card-body {
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
        }
    </style>
@endsection

@section('navbarTitle', 'Overview')

@section('content')
@php
    $dailyAttendanceDateLabel = (string) ($dailyAttendanceDateLabel ?? now('Asia/Jakarta')->format('d F Y'));
    $dailyTotalStaffCount = (int) ($dailyTotalStaffCount ?? 0);
    $dailyPresentStaffCount = (int) ($dailyPresentStaffCount ?? 0);
    $dailyOnTimeCount = (int) ($dailyOnTimeCount ?? 0);
    $dailyLateCount = (int) ($dailyLateCount ?? 0);
    $dailyLeaveCount = (int) ($dailyLeaveCount ?? 0);
    $dailyDeviationCount = (int) ($dailyDeviationCount ?? 0);
    $dailyOnTimePercent = (int) ($dailyOnTimePercent ?? 0);
    $dailyLatePercent = (int) ($dailyLatePercent ?? 0);
    $dailyLeavePercent = (int) ($dailyLeavePercent ?? 0);
    $dailyDeviationPercent = (int) ($dailyDeviationPercent ?? 0);
    $dailyOnTimeDonutValue = (string) ($dailyOnTimeDonutValue ?? '0/1');
    $dailyLateDonutValue = (string) ($dailyLateDonutValue ?? '0/1');
    $dailyLeaveDonutValue = (string) ($dailyLeaveDonutValue ?? '0/1');
    $dailyDeviationDonutValue = (string) ($dailyDeviationDonutValue ?? '0/1');
    $attendanceProgressPercent = (int) ($attendanceProgressPercent ?? 0);
    $attendanceOverviewSeries = array_values($attendanceOverviewSeries ?? [0, 0, 0, 0]);
    $attendanceOverviewChartSeries = array_sum($attendanceOverviewSeries) > 0 ? $attendanceOverviewSeries : [1];
    $attendanceOverviewChartColors = array_sum($attendanceOverviewSeries) > 0
        ? ['#2BC155', '#F94687', '#1EA7C5', '#A02CFA']
        : ['#F2F3F8'];
    $weeklyAttendanceRangeLabel = (string) ($weeklyAttendanceRangeLabel ?? now('Asia/Jakarta')->startOfWeek()->format('d').' - '.now('Asia/Jakarta')->startOfWeek()->addDays(4)->format('d F Y'));
    $weeklyDayLabels = array_values($weeklyDayLabels ?? []);
    $weeklyAttendanceSeries = array_values($weeklyAttendanceSeries ?? [
        ['name' => 'On Time', 'data' => [0, 0, 0, 0, 0]],
        ['name' => 'Late', 'data' => [0, 0, 0, 0, 0]],
    ]);
    $weeklyOutOfOfficeSeries = array_values($weeklyOutOfOfficeSeries ?? [
        ['name' => 'Leave', 'data' => [0, 0, 0, 0, 0]],
        ['name' => 'Business Trip', 'data' => [0, 0, 0, 0, 0]],
        ['name' => 'Deviation', 'data' => [0, 0, 0, 0, 0]],
    ]);
    $weeklyOvertimeHoursSeries = array_values($weeklyOvertimeHoursSeries ?? [0, 0, 0, 0, 0]);
    $monthlyAttendanceRangeLabel = (string) ($monthlyAttendanceRangeLabel ?? now('Asia/Jakarta')->startOfMonth()->format('d').' - '.now('Asia/Jakarta')->endOfMonth()->format('d F Y'));
    $monthlyDayLabels = array_values($monthlyDayLabels ?? []);
    $monthlyAttendanceSeries = array_values($monthlyAttendanceSeries ?? [
        ['name' => 'On Time', 'data' => []],
        ['name' => 'Late', 'data' => []],
    ]);
    $monthlyOutOfOfficeSeries = array_values($monthlyOutOfOfficeSeries ?? [
        ['name' => 'Leave', 'data' => []],
        ['name' => 'Business Trip', 'data' => []],
        ['name' => 'Deviation', 'data' => []],
    ]);
    $yearToDateYearLabel = (string) ($yearToDateYearLabel ?? now('Asia/Jakarta')->format('Y'));
    $yearToDateMonthLabels = array_values($yearToDateMonthLabels ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']);
    $yearToDateLeaveSeries = array_values($yearToDateLeaveSeries ?? [
        ['name' => 'Leave', 'data' => array_fill(0, 12, 0)],
    ]);
    $yearToDateOvertimeHoursSeries = array_values($yearToDateOvertimeHoursSeries ?? array_fill(0, 12, 0));
@endphp

@include('admin_attendance.layout.navbar')

<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Overview</h5>
    </div>
</div>

<div class="mb-4">
    <ul class="nav nav-pills menu-pill nav-pills-all gap-2" id="justify-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link fs-16 active" data-bs-toggle="pill" data-bs-target="#nav-recomended" 
            type="button" role="tab" aria-controls="nav-recomended" aria-selected="true">Daily</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fs-16" data-bs-toggle="pill" data-bs-target="#nav-salad" 
            type="button" role="tab" aria-controls="nav-salad" aria-selected="false" tabindex="-1">Weekly</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fs-16" data-bs-toggle="pill" data-bs-target="#nav-breakfast" 
            type="button" role="tab" aria-controls="nav-breakfast" aria-selected="false" tabindex="-1">Monthly</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fs-16" data-bs-toggle="pill" data-bs-target="#nav-lunch" 
            type="button" role="tab" aria-controls="nav-lunch" aria-selected="false" tabindex="-1">Year to Date</button>
        </li>
    </ul>
</div>
				<!-- Start - Attendance -->
<div class="tab-content" id="nav-tabContent">

    <!-- Start - Recommended for you -->
    <div class="tab-pane fade show active admin-attendance-daily-grid" id="nav-recomended" role="tabpanel" aria-labelledby="nav-recomended-tab" tabindex="0">
        <div class="row admin-attendance-list-grid">
            <div class="col-md-3 col-12 admin-attendance-overview-column">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card admin-attendance-overview-card admin-attendance-summary-card">
                            <div class="card-header border-0 pb-0">
                                <h4 class="card-title">Attendance Overview <br> ({{ $dailyAttendanceDateLabel }})</h4>
                            </div>
                            <div class="card-body">
                                <div class="position-relative mb-4">
                                    <div id="pieChart"></div>
                                    <div class="position-absolute top-50 start-50 translate-middle">
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
                                <ul class="d-flex flex-wrap justify-content-center">
                                    <li class="d-flex align-items-center gap-3 me-5 mb-2">
                                        <i class="fa fa-circle text-success"></i>
                                        <span class="fs-12 text-black">On Time</span> 
                                    </li>
                                    <li class="d-flex align-items-center gap-3 me-5 mb-2">
                                        <i class="fa fa-circle text-danger"></i>
                                        <span class="fs-12 text-black">Late</span>
                                    </li>
                                    <li class="d-flex align-items-center gap-3 me-5 mb-2">
                                        <i class="fa fa-circle text-info"></i>
                                        <span class="fs-12 text-black">Leave</span>
                                    </li>
                                    <li class="d-flex align-items-center gap-3 me-5 mb-2">
                                        <i class="fa fa-circle text-secondary"></i>
                                        <span class="fs-12 text-black">Deviation</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-9 col-12 admin-attendance-overview-column">
                <div class="card admin-attendance-overview-card">
                    <div class="card-header flex-wrap border-0 pb-0">
                        <h4 class="card-title">Progress ({{ $dailyAttendanceDateLabel }})</h4>
                    </div>
                    <div class="card-body pt-0 pb-3">
                        <div class="row align-items-center">
                            <div class="col-lg-4 mb-lg-0 mb-4 text-center radialBar">
                                <div id="radialBar"></div>
                                <h4 class="fs-18 text-black">Daily Staff Attendance ({{ $dailyPresentStaffCount }} / {{ $dailyTotalStaffCount }} Staff)</h4>
                                <p class="fs-14">Tracking the number of staff currently present against the total registered workforce today.</p>
                            </div>
                            <div class="col-lg-8">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center mb-sm-5 mb-3">
                                            <div class="position-relative me-3">
                                                <span class="donut" data-peity='{ "fill": ["var(--bs-success)", "var(--bs-light)"],   "innerRadius": 34, "radius": 10}'>{{ $dailyOnTimeDonutValue }}</span>
                                                
                                                <small class="position-absolute top-50 start-50 translate-middle">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <g clip-path="url(#clip3)">
                                                        <path d="M0.988957 17.0741C0.328275 17.2007 -0.104585 17.8386 0.0219821 18.4993C0.133361 19.0815 0.644693 19.4865 1.21678 19.4865C1.29272 19.4865 1.37119 19.4789 1.44713 19.4637L6.4592 18.5018C6.74524 18.4461 7.0009 18.2917 7.18316 18.0639L9.33481 15.3503L8.61593 14.9832C8.08435 14.7149 7.71474 14.2289 7.58818 13.6391L5.55804 16.1983L0.988957 17.0741Z" fill="#A02CFA"/>
                                                        <path d="M18.84 6.49306C20.3135 6.49306 21.508 5.29854 21.508 3.82502C21.508 2.3515 20.3135 1.15698 18.84 1.15698C17.3665 1.15698 16.1719 2.3515 16.1719 3.82502C16.1719 5.29854 17.3665 6.49306 18.84 6.49306Z" fill="#A02CFA"/>
                                                        <path d="M13.0179 3.15677C12.7369 2.8682 12.4762 2.75428 12.1902 2.75428C12.0864 2.75428 11.9826 2.76947 11.8712 2.79479L7.29203 3.88073C6.6592 4.03008 6.26937 4.66545 6.41872 5.29576C6.54782 5.83746 7.02877 6.20198 7.56289 6.20198C7.65404 6.20198 7.74514 6.19185 7.8363 6.16907L11.7371 5.24513C11.9902 5.52611 13.2584 6.90063 13.4888 7.14364C11.8763 8.87002 10.2639 10.5939 8.65137 12.3202C8.62605 12.3481 8.60329 12.3759 8.58049 12.4038C8.10966 13.0037 8.25397 13.9454 8.96275 14.3023L13.9064 16.826L11.3397 20.985C10.9878 21.5571 11.165 22.3064 11.7371 22.6608C11.9371 22.7848 12.1573 22.843 12.375 22.843C12.7825 22.843 13.1824 22.638 13.4128 22.2659L16.6732 16.983C16.8529 16.6919 16.901 16.34 16.8074 16.0135C16.7137 15.6844 16.4884 15.411 16.1821 15.2566L12.8331 13.553L16.3543 9.78636L19.0122 12.0393C19.2324 12.2266 19.5032 12.3177 19.7716 12.3177C20.0601 12.3177 20.3487 12.2114 20.574 12.0038L23.6243 9.16112C24.1002 8.71814 24.128 7.97392 23.685 7.49803C23.4521 7.24996 23.1383 7.12339 22.8244 7.12339C22.5383 7.12339 22.2497 7.22717 22.0245 7.43728L19.7412 9.56107C19.7386 9.56361 14.0178 4.18196 13.0179 3.15677Z" fill="#A02CFA"/>
                                                        </g>
                                                        <defs>
                                                        <clipPath id="clip3">
                                                        <rect width="24" height="24" fill="white"/>
                                                        </clipPath>
                                                        </defs>
                                                    </svg>
                                                </small>
                                            </div>
                                            <div>
                                                <h4 class="fs-18 text-black">On Time ({{ $dailyOnTimePercent }}%)</h4>
                                                <span>{{ $dailyOnTimeCount }} Staff / {{ $dailyTotalStaffCount }} Staff</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center mb-sm-5 mb-3">
                                            <div class="position-relative me-3">
                                                <span class="donut" data-peity='{ "fill": ["var(--bs-danger)", "var(--bs-light)"],   "innerRadius": 34, "radius": 10}'>{{ $dailyLateDonutValue }}</span>
                                                <small class="position-absolute top-50 start-50 translate-middle">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <g clip-path="url(#clip4)">
                                                        <path d="M11.9995 5.9999C13.6563 5.9999 14.9994 4.65677 14.9994 2.99995C14.9994 1.34312 13.6563 1.61033e-07 11.9995 1.41496e-07C10.3426 1.21959e-07 8.99953 1.34312 8.99953 2.99995C8.99953 4.65677 10.3426 5.9999 11.9995 5.9999Z" fill="#FFBC11"/>
                                                        <path d="M17.8302 21.8297L14.1358 23.2153L15.973 23.9042C16.7637 24.1978 17.6168 23.791 17.9044 23.0261C18.0574 22.618 18.0121 22.1905 17.8302 21.8297Z" fill="#FFBC11"/>
                                                        <path d="M5.0265 16.5949C4.25236 16.3078 3.38663 16.6974 3.09516 17.473C2.80439 18.2486 3.19772 19.1128 3.97327 19.4043L5.59153 20.0111L9.86385 18.4088L5.0265 16.5949Z" fill="#FFBC11"/>
                                                        <path d="M20.9043 17.473C20.6127 16.6974 19.7471 16.3078 18.9729 16.5949L6.97318 21.0948C6.19754 21.3863 5.80426 22.2505 6.09502 23.0262C6.38251 23.7908 7.23544 24.198 8.02636 23.9043L20.0261 19.4044C20.8018 19.1129 21.1951 18.2487 20.9043 17.473Z" fill="#FFBC11"/>
                                                        <path d="M22.4995 11.9998L18.9268 11.9998L16.3414 6.82899C16.0728 6.29213 15.5262 5.98627 14.9629 5.99991L11.9995 5.9999L9.03636 5.99991C8.47316 5.98627 7.9273 6.29217 7.658 6.82899L5.07262 11.9998L1.49995 11.9998C0.671624 11.9998 -1.49419e-07 12.6714 -1.59186e-07 13.4997C-1.68954e-07 14.328 0.671624 14.9997 1.49995 14.9997L5.99985 14.9997C6.56821 14.9997 7.08749 14.6789 7.3416 14.1706L8.99975 10.8543L8.99975 16.483L11.9996 17.6079L14.9996 16.4827L14.9996 10.8543L16.6578 14.1706C16.912 14.6789 17.4312 14.9997 17.9995 14.9997L22.4994 14.9997C23.3278 14.9997 23.9994 14.328 23.9994 13.4997C23.9994 12.6714 23.3278 11.9998 22.4995 11.9998Z" fill="#FFBC11"/>
                                                        </g>
                                                        <defs>
                                                        <clipPath id="clip4">
                                                        <rect width="24" height="24" fill="white" transform="translate(-0.000244141)"/>
                                                        </clipPath>
                                                        </defs>
                                                    </svg>
                                                </small>
                                            </div>
                                            <div>
                                                <h4 class="fs-18 text-black">Late ({{ $dailyLatePercent }}%)</h4>
                                                <span>{{ $dailyLateCount }} Staff / {{ $dailyTotalStaffCount }} Staff</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center mb-sm-5 mb-3">
                                            <div class="position-relative me-3">
                                                <span class="donut" data-peity='{ "fill": ["var(--bs-secondary)", "var(--bs-light)"],   "innerRadius": 34, "radius": 10}'>{{ $dailyDeviationDonutValue }}</span>
                                                <small class="position-absolute top-50 start-50 translate-middle">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <g clip-path="url(#clip5)">
                                                        <path d="M10.8586 5.22596L5.87121 10.5542C5.50758 11.0845 5.64394 11.8068 6.17172 12.1679L11.1945 15.6098L11.1945 18.9558C11.1945 19.5921 11.6995 20.125 12.3359 20.1376C12.9874 20.1477 13.5177 19.6249 13.5177 18.976L13.5177 15.0012C13.5177 14.6174 13.3283 14.2588 13.0126 14.0442L9.79041 11.8346L12.5025 8.95833L13.8914 12.1225C14.0758 12.5442 14.4949 12.8169 14.9546 12.8169L19.1844 12.8169C19.8207 12.8169 20.3536 12.3119 20.3662 11.6755C20.3763 11.024 19.8536 10.4937 19.2046 10.4937L15.7172 10.4937C15.2576 9.44821 14.7677 8.41285 14.3409 7.35225C14.1237 6.81689 14.0025 6.58457 13.6036 6.21588C13.5227 6.14013 12.9596 5.62498 12.4571 5.16538C11.995 4.74616 11.2828 4.77394 10.8586 5.22596Z" fill="#FF3282"/>
                                                        <path d="M15.6162 5.80678C17.0861 5.80678 18.2778 4.61514 18.2778 3.14517C18.2778 1.6752 17.0861 0.483551 15.6162 0.483551C14.1462 0.483551 12.9545 1.6752 12.9545 3.14517C12.9545 4.61514 14.1462 5.80678 15.6162 5.80678Z" fill="#FF3282"/>
                                                        <path d="M4.89899 23.5164C7.60463 23.5164 9.79798 21.323 9.79798 18.6174C9.79798 15.9117 7.60463 13.7184 4.89899 13.7184C2.19335 13.7184 -1.81927e-07 15.9117 -2.13831e-07 18.6174C-2.45735e-07 21.323 2.19335 23.5164 4.89899 23.5164Z" fill="#FF3282"/>
                                                        <path d="M19.101 23.5164C21.8066 23.5164 24 21.323 24 18.6174C24 15.9118 21.8066 13.7184 19.101 13.7184C16.3954 13.7184 14.202 15.9118 14.202 18.6174C14.202 21.323 16.3954 23.5164 19.101 23.5164Z" fill="#FF3282"/>
                                                        </g>
                                                        <defs>
                                                        <clipPath id="clip5">
                                                        <rect width="24" height="24" fill="white"/>
                                                        </clipPath>
                                                        </defs>
                                                    </svg>
                                                </small>
                                            </div>
                                            <div>
                                                <h4 class="fs-18 text-black">Deviation ({{ $dailyDeviationPercent }}%)</h4>
                                                <span>{{ $dailyDeviationCount }} Staff / {{ $dailyTotalStaffCount }} Staff</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center mb-sm-5 mb-3">
                                            <div class="position-relative me-3">
                                                <span class="donut" data-peity='{ "fill": ["var(--bs-info)", "var(--bs-light)"],   "innerRadius": 34, "radius": 10}'>{{ $dailyLeaveDonutValue }}</span>
                                                <small class="position-absolute top-50 start-50 translate-middle">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <g clip-path="url(#clip8)">
                                                        <path d="M22.2363 3.06982C22.0806 2.91507 21.8978 2.83724 21.6855 2.83724C21.58 2.83724 21.3576 2.92382 21.0205 3.09469C20.682 3.26601 20.3218 3.45668 19.9442 3.66945C19.5651 3.88084 19.1166 4.07243 18.5985 4.24375C18.0813 4.41461 17.6028 4.5012 17.162 4.5012C16.7544 4.5012 16.3961 4.42382 16.0862 4.26862C15.0596 3.78781 14.1662 3.42904 13.4086 3.19232C12.6505 2.95606 11.8353 2.83724 10.9626 2.83724C9.45569 2.83724 7.73923 3.32726 5.81506 4.30546C5.41807 4.5035 5.13346 4.65686 4.94924 4.76923L4.7664 3.42858C5.17951 3.06982 5.44617 2.5471 5.44617 1.95714C5.44617 0.876234 4.57021 0.000274694 3.48931 0.000274681C2.4084 0.000274669 1.53198 0.876234 1.53198 1.95714C1.53198 2.66223 1.90871 3.27522 2.46781 3.61971L5.11135 23.0041C5.1901 23.5812 5.68381 23.9998 6.25074 23.9998C6.30232 23.9998 6.35482 23.9957 6.40779 23.9901C7.03782 23.9036 7.47902 23.3237 7.3929 22.6937L6.33042 14.9031C8.25826 13.9465 9.9259 13.4644 11.3287 13.4644C11.9242 13.4644 12.505 13.5523 13.071 13.7329C13.6374 13.9129 14.109 14.1073 14.4835 14.3187C14.8574 14.531 15.3 14.7272 15.8098 14.9054C16.3197 15.085 16.823 15.1748 17.32 15.1748C18.5754 15.1748 20.0782 14.7018 21.8315 13.7563C22.0516 13.6421 22.2124 13.5297 22.3146 13.4201C22.4168 13.3101 22.4675 13.153 22.4675 12.9499L22.4675 3.62017C22.4675 3.40878 22.3906 3.22502 22.2363 3.06982Z" fill="#1EA7C5"/>
                                                        </g>
                                                        <defs>
                                                        <clipPath id="clip8">
                                                        <rect width="24" height="24" fill="white"/>
                                                        </clipPath>
                                                        </defs>
                                                    </svg>
                                                </small>
                                            </div>
                                            <div>
                                                <h4 class="fs-18 text-black">On Leave ({{ $dailyLeavePercent }}%)</h4>
                                                <span>{{ $dailyLeaveCount }} Staff / {{ $dailyTotalStaffCount }} Staff</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Start - Top On Time -->
            <div class="col-md-3 col-12">
                <div class="card">
                    <div class="card-header d-sm-flex d-block border-0 pb-4">
                        <div class="me-auto pe-3">
                            <h4 class="card-title">Today's Early Birds <br> ({{ $dailyAttendanceDateLabel }})</h4>
                            <p class="fs-13 mb-0">A big shoutout to our staff who kicked off the day early!</p>
                        </div>
                    </div>
                    <div class="card-body loadmore-content dz-scroll pb-0 pt-0 height320">
                        @forelse ($dailyEarlyBirds as $staff)
                            <div class="d-flex border-bottom py-3">
                                <span class="admin-attendance-person-avatar avatar me-3">{{ $staff['initials'] }}</span>
                                <div class="pe-3 me-auto">
                                    <h6 class="fs-16 mb-0 text-black">{{ $staff['name'] }}</h6>
                                    <span class="fs-12">{{ $staff['time'] }}</span>
                                </div>
                                <span class="fs-16 text-black">#{{ $staff['rank'] }}</span>
                            </div>
                        @empty
                            <div class="admin-attendance-empty">No clock-in data available.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            <!-- End - Trending Ingridients -->

            <!-- Start - Top On Time -->
            <div class="col-md-3 col-12">
                <div class="card">
                    <div class="card-header d-sm-flex d-block border-0 pb-4">
                        <div class="me-auto pe-3">
                            <h4 class="card-title">Today's Running Late <br> ({{ $dailyAttendanceDateLabel }})</h4>
                            <p class="fs-13 mb-0">Staff members who are kicking off their workday a bit later.</p>
                        </div>
                    </div>
                    <div class="card-body loadmore-content dz-scroll pb-0 pt-0 height320">
                        @forelse ($dailyRunningLate as $staff)
                            <div class="d-flex border-bottom py-3">
                                <span class="admin-attendance-person-avatar avatar me-3">{{ $staff['initials'] }}</span>
                                <div class="pe-3 me-auto">
                                    <h6 class="fs-16 mb-0 text-black">{{ $staff['name'] }}</h6>
                                    <span class="fs-12">{{ $staff['time'] }}</span>
                                </div>
                                <span class="fs-16 text-black">#{{ $staff['rank'] }}</span>
                            </div>
                        @empty
                            <div class="admin-attendance-empty">No late arrival data available.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            <!-- End - Trending Ingridients -->

            <!-- Start - Top On Time -->
            <div class="col-md-3 col-12">
                <div class="card">
                    <div class="card-header d-sm-flex d-block border-0 pb-4">
                        <div class="me-auto pe-3">
                            <h4 class="card-title">On Business Trip <br> ({{ $dailyAttendanceDateLabel }})</h4>
                            <p class="fs-13 mb-0">Team members who are traveling for client meetings, or external projects.</p>
                        </div>
                    </div>
                    <div class="card-body loadmore-content dz-scroll pb-0 pt-0 height320">
                        @forelse ($dailyBusinessTrips as $staff)
                            <div class="d-flex border-bottom py-3">
                                <span class="admin-attendance-person-avatar avatar me-3">{{ $staff['initials'] }}</span>
                                <div class="pe-3 me-auto">
                                    <h6 class="fs-16 mb-0 text-black">{{ $staff['name'] }}</h6>
                                    <span class="fs-12">{{ $staff['destination'] }}</span> <br>
                                    <span class="fs-12">{{ $staff['date_range'] }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="admin-attendance-empty">No business trip data available.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            <!-- End - Trending Ingridients -->

            <!-- Start - Top On Time -->
            <div class="col-md-3 col-12">
                <div class="card">
                    <div class="card-header d-sm-flex d-block border-0 pb-4">
                        <div class="me-auto pe-3">
                            <h4 class="card-title">Days on Leave (Time Off) <br> ({{ $dailyAttendanceDateLabel }})</h4>
                            <p class="fs-13 mb-0">Team members who are currently away and taking some well-deserved time off.</p>
                        </div>
                    </div>
                    <div class="card-body loadmore-content dz-scroll pb-0 pt-0 height320">
                        @forelse ($dailyLeaves as $staff)
                            <div class="d-flex border-bottom py-3">
                                <span class="admin-attendance-person-avatar avatar me-3">{{ $staff['initials'] }}</span>
                                <div class="pe-3 me-auto">
                                    <h6 class="fs-16 mb-0 text-black">{{ $staff['name'] }}</h6>
                                    <span class="fs-12">{{ $staff['leave_type'] }}</span>
                                    <div>
                                        <button
                                            type="button"
                                            class="btn btn-primary light btn-xs mt-2"
                                            data-admin-attendance-leave-details
                                            data-bs-toggle="modal"
                                            data-bs-target="#{{ $staff['modal_id'] }}"
                                            data-leave-modal-title="{{ $staff['modal_title'] }}"
                                            data-leave-type="{{ $staff['leave_type'] }}"
                                            data-leave-reason="{{ $staff['reason'] }}"
                                            data-leave-duration="{{ $staff['duration'] }}"
                                            data-leave-status="{{ $staff['status'] }}"
                                            data-leave-status-date="{{ $staff['status_date'] }}"
                                            data-leave-attachment-url="{{ $staff['attachment_url'] }}"
                                            data-leave-attachment-is-image="{{ $staff['attachment_is_image'] ? '1' : '0' }}"
                                        >
                                            Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="admin-attendance-empty">No leave data available.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            <!-- End - Trending Ingridients -->

        </div>
    </div>
    <!-- End - Recommended for you -->
    
    <!-- Start - Salad -->
    <div class="tab-pane fade" id="nav-salad" role="tabpanel" aria-labelledby="nav-salad-tab" tabindex="0">
        <div class="row">
            <div class="col-md-6 col-12">
                <div class="card">
                    <div class="card-header d-sm-flex d-block pb-0 border-0">
                        <div>
                            <h4 class="card-title">Attendance ({{ $weeklyAttendanceRangeLabel }})</h4>
                        </div>
                    </div>
                    <div class="card-body pb-0">
                        <div id="chartBarRunning"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="card">
                    <div class="card-header d-sm-flex d-block pb-0 border-0">
                        <div>
                            <h4 class="card-title">Out of Office ({{ $weeklyAttendanceRangeLabel }})</h4>
                        </div>
                    </div>
                    <div class="card-body pb-0">
                        <div id="chartLeaveBt"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header border-0 pb-0">
                                <h4 class="card-title">Overtime Overview ({{ $weeklyAttendanceRangeLabel }})</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="overtimeChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End - Salad -->

    <!-- Start - Breakfast -->
    <div class="tab-pane fade" id="nav-breakfast" role="tabpanel" aria-labelledby="nav-breakfast-tab" tabindex="0">
        <div class="row">
            <div class="col-md-12 col-12">
                <div class="card">
                    <div class="card-header d-sm-flex d-block pb-0 border-0">
                        <div>
                            <h4 class="card-title">Attendance ({{ $monthlyAttendanceRangeLabel }})</h4>
                        </div>
                    </div>
                    <div class="card-body pb-0">
                        <div id="attendanceMonthly"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-12">
                <div class="card">
                    <div class="card-header d-sm-flex d-block pb-0 border-0">
                        <div>
                            <h4 class="card-title">Out of Office ({{ $monthlyAttendanceRangeLabel }})</h4>
                        </div>
                    </div>
                    <div class="card-body pb-0">
                        <div id="oooMonthly"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End - Breakfast -->

    <!-- Start - Lunch -->
    <div class="tab-pane fade" id="nav-lunch" role="tabpanel" aria-labelledby="nav-lunch-tab" tabindex="0">
        <div class="row">
            <div class="col-md-6 col-12">
                <div class="card">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title">Leave Overview ({{ $yearToDateYearLabel }})</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="lineChart_3"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title">Overtime Overview ({{ $yearToDateYearLabel }})</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="lineChart_2"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End - Lunch -->

    <!-- Start - Stats -->
            
            <!-- End - Stats -->

</div>				

<div class="modal fade" id="annualLeave" tabindex="-1" aria-labelledby="annualLeaveLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="annualLeaveLabel">Leave Details</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xl-12">
                        <h5 class="text-muted mb-0 fw-bold">Out of Office mode: ON</h5>
                        <p class="form-label text-muted mb-3">
                            Your leave request is approved and currently active.
                        </p>
                        <div class="row py-2">
                            <div class="col-4"><span>Leave Type</span></div>
                            <div class="col-8"><span id="annualLeaveTypeText" class="text-gray fw-semibold">-</span></div>
                        </div>
                        <div class="row py-2">
                            <div class="col-4"><span>Reason</span></div>
                            <div class="col-8"><span id="annualLeaveReasonText" class="text-gray">-</span></div>
                        </div>
                        <div class="row py-2">
                            <div class="col-4"><span>Leave Duration</span></div>
                            <div class="col-8"><span id="annualLeaveDurationText" class="text-gray fw-semibold">-</span></div>
                        </div>
                        <div class="row py-2">
                            <div class="col-4"><span>Status</span></div>
                            <div class="col-8">
                                <span id="annualLeaveStatusText" class="text-success fw-semibold">-</span>
                                <span id="annualLeaveStatusDateText" class="text-gray"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="sick" tabindex="-1" aria-labelledby="sickLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="sickLabel">Attendance Sick</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xl-12">
                        <h5 class="text-muted mb-0 fw-bold">Your health comes first</h5>
                        <p class="form-label text-muted mb-3">
                            Take the time needed to rest and recover.
                        </p>
                        <div class="row py-2">
                            <div class="col-4"><span>Leave Type</span></div>
                            <div class="col-8"><span id="sickTypeText" class="text-gray fw-semibold">-</span></div>
                        </div>
                        <div class="row py-2">
                            <div class="col-4"><span>Reason</span></div>
                            <div class="col-8"><span id="sickReasonText" class="text-gray">-</span></div>
                        </div>
                        <div class="row py-2">
                            <div class="col-4"><span>Leave Duration</span></div>
                            <div class="col-8"><span id="sickDurationText" class="text-gray fw-semibold">-</span></div>
                        </div>
                        <div class="row py-2">
                            <div class="col-4"><span>Status</span></div>
                            <div class="col-8">
                                <span id="sickStatusText" class="text-success fw-semibold">-</span>
                                <span id="sickStatusDateText" class="text-gray"></span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-4"><span>Medical Notes</span></div>
                            <div class="col-8">
                                <a id="sickMedicalNotesImageLink" href="#" target="_blank" rel="noopener" class="d-none">
                                    <img id="sickMedicalNotesImage" src="" alt="Medical Notes" class="mt-2 w-100 rounded">
                                </a>
                                <a id="sickMedicalNotesFileLink" href="#" target="_blank" rel="noopener" class="text-primary d-none">View Medical Notes</a>
                                <span id="sickMedicalNotesFallback" class="text-gray">No medical note attachment.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
    @endphp
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
	<script>
        var attendanceOverviewChartSeries = @json($attendanceOverviewChartSeries);
        var attendanceOverviewChartColors = @json($attendanceOverviewChartColors);
        var attendanceProgressPercent = @json($attendanceProgressPercent);
        var weeklyDayLabels = @json($weeklyDayLabels);
        var weeklyAttendanceSeries = @json($weeklyAttendanceSeries);
        var weeklyOutOfOfficeSeries = @json($weeklyOutOfOfficeSeries);
        var weeklyOvertimeHoursSeries = @json($weeklyOvertimeHoursSeries);
        var monthlyDayLabels = @json($monthlyDayLabels);
        var monthlyAttendanceSeries = @json($monthlyAttendanceSeries);
        var monthlyOutOfOfficeSeries = @json($monthlyOutOfOfficeSeries);
        var yearToDateMonthLabels = @json($yearToDateMonthLabels);
        var yearToDateLeaveSeries = @json($yearToDateLeaveSeries);
        var yearToDateOvertimeHoursSeries = @json($yearToDateOvertimeHoursSeries);

        function collectChartSeriesValues(series) {
            if (!Array.isArray(series)) {
                return [];
            }

            return series.flatMap(function(item) {
                if (Array.isArray(item)) {
                    return item;
                }

                return Array.isArray(item.data) ? item.data : [];
            }).map(function(value) {
                return Number(value) || 0;
            });
        }

        function maxChartValue(series) {
            var values = collectChartSeriesValues(series);

            return values.length ? Math.max.apply(Math, values) : 0;
        }

        function integerAxisMax(series) {
            return Math.max(1, Math.ceil(maxChartValue(series)));
        }

        function integerTickLabel(value) {
            var numericValue = Number(value) || 0;

            return numericValue < 1 ? '' : Math.round(numericValue).toString();
        }

        function apexIntegerYAxis(series) {
            var maxValue = integerAxisMax(series);

            return {
                min: 0,
                max: maxValue,
                tickAmount: maxValue <= 10 ? maxValue : 5,
                forceNiceScale: false,
                decimalsInFloat: 0,
                labels: {
                    offsetX: -16,
                    formatter: integerTickLabel,
                    style: {
                        colors: 'var(--bs-dark)',
                        fontSize: '13px',
                        fontFamily: 'poppins',
                        fontWeight: 400,
                        cssClass: 'apexcharts-xaxis-label',
                    },
                },
            };
        }

        function chartJsIntegerYAxis(series) {
            var maxValue = integerAxisMax(series);
            var ticks = {
                beginAtZero: true,
                precision: 0,
                maxTicksLimit: maxValue <= 10 ? maxValue + 1 : 6,
                callback: integerTickLabel,
                padding: 10,
            };

            if (maxValue <= 10) {
                ticks.stepSize = 1;
            }

            return {
                beginAtZero: true,
                suggestedMin: 0,
                suggestedMax: maxValue,
                ticks: ticks,
            };
        }

		(function($) {
		/* "use strict" */

		var dzSparkLine = function(){
			//let draw = Chart.controllers.line.__super__.draw; //draw shadow
			
			var screenWidth = $(window).width();

			var barChart1 = function(){
				if(jQuery('#barChart_1').length > 0 ){
					const barChart_1 = document.getElementById("barChart_1").getContext('2d');
					
					barChart_1.height = 100;

					new Chart(barChart_1, {
						type: 'bar',
						data: {
							defaultFontFamily: 'Poppins',
							labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
							datasets: [
								{
									label: "My Business Trips",
									data: [65, 59, 80, 81, 56, 55, 40, 45, 50, 55, 60, 65],
									borderColor: 'rgba(11, 42, 151, 1)',
									borderWidth: "0",
									barPercentage: 0.5,
									backgroundColor: 'rgba(11, 42, 151, 1)'
								}
							]
						},
						options: {
							plugins:{
								legend:false,
								
							},
							scales: {
								y:{
									ticks: {
										beginAtZero: true
									}
								},
								x:{
									// Change here
									
								}
							}
						}
					});
				}
			}


			var overtimeChart = function(){
				//stalked bar chart
				if(jQuery('#overtimeChart').length > 0 ){
					const overtimeChart = document.getElementById("overtimeChart").getContext('2d');
					//generate gradient
					const overtimeChartgradientStroke = overtimeChart.createLinearGradient(50, 100, 50, 50);
					overtimeChartgradientStroke.addColorStop(0, "rgba(43, 193, 85, 1)");
					overtimeChartgradientStroke.addColorStop(1, "rgba(43, 193, 85, 1)");

					const overtimeChartgradientStroke2 = overtimeChart.createLinearGradient(50, 100, 50, 50);
					overtimeChartgradientStroke2.addColorStop(0, "rgba(249, 69, 135, 1)");
					overtimeChartgradientStroke2.addColorStop(1, "rgba(249, 69, 135, 1)");

					const overtimeChartgradientStroke3 = overtimeChart.createLinearGradient(50, 100, 50, 50);
					overtimeChartgradientStroke3.addColorStop(0, "rgba(160, 44, 250, 1)");
					overtimeChartgradientStroke3.addColorStop(1, "rgba(160, 44, 250, 1)");

					const overtimeChartgradientStroke4 = overtimeChart.createLinearGradient(50, 100, 50, 50);
					overtimeChartgradientStroke4.addColorStop(0, "rgba(30, 167, 197, 1)");
					overtimeChartgradientStroke4.addColorStop(1, "rgba(30, 167, 197, 1)");
					
					overtimeChart.height = 100;

					let barChartData = {
						defaultFontFamily: 'Poppins',
						labels: weeklyDayLabels,
						datasets: [{
							label: 'Overtime Hours',
							backgroundColor: overtimeChartgradientStroke,
							hoverBackgroundColor: overtimeChartgradientStroke, 
							data: weeklyOvertimeHoursSeries
						}]
					};

					new Chart(overtimeChart, {
						type: 'bar',
						data: barChartData,
						options: {
							plugins:{
								legend:false,
								tooltip: {
									mode: 'index',
									intersect: false
								},
								
							},
							title: {
								display: false
							},
							responsive: true,
							scales: {
								x:{
									stacked: true,
								},
								y: Object.assign({ stacked: true }, chartJsIntegerYAxis(weeklyOvertimeHoursSeries))
							}
						}
					});


				}
			}

			var lineChart2 = function(){
			//gradient line chart
			if(jQuery('#lineChart_2').length > 0 ){
				
				const lineChart_2 = document.getElementById("lineChart_2").getContext('2d');
				//generate gradient
				const lineChart_2gradientStroke = lineChart_2.createLinearGradient(500, 0, 100, 0);
				lineChart_2gradientStroke.addColorStop(0, "rgba(11, 42, 151, 1)");
				lineChart_2gradientStroke.addColorStop(1, "rgba(11, 42, 151, 0.5)");

				//Chart.controllers.line.draw = function(){ };
				
				class Custom extends Chart.LineController {
					draw() {
						// Call bubble controller method to draw all the points
						super.draw(arguments);	
						const ctx = this.chart.ctx;
						let _stroke = ctx.stroke;
						ctx.stroke = function(){
							ctx.save();
							ctx.shadowColor = 'rgba(110, 197, 30, 0.1)';
							ctx.shadowBlur = 10;
							ctx.shadowOffsetX = 0;
							ctx.shadowOffsetY = 4;
							_stroke.apply(this, arguments);
							ctx.restore();
							
						}
					}
				};
				Custom.id = 'shadowLine';
				Custom.defaults = Chart.LineController.defaults;

				// Stores the controller so that the chart initialization routine can look it up
				Chart.register(Custom);
					
					
				lineChart_2.height = 100;

				new Chart(lineChart_2, {
					type: 'line',
					data: {
						defaultFontFamily: 'Poppins',
						labels: yearToDateMonthLabels,
						datasets: [
							{
								label: "Overtime Hours",
								data: yearToDateOvertimeHoursSeries,
								borderColor: lineChart_2gradientStroke,
								borderWidth: "2",
								backgroundColor: 'transparent', 
								tension:0.5,
								pointBackgroundColor: 'rgba(11, 42, 151, 0.5)'
							}
						]
					},
					options: {
						plugins:{
							legend:false,
							
						},
						scales: {
							y: chartJsIntegerYAxis(yearToDateOvertimeHoursSeries),
							x:{ 
								ticks: {
									padding: 5
								}
							}
						}
					}
				});
			}
		}
			
			var lineChart3 = function(){
			//dual line chart
			if(jQuery('#lineChart_3').length > 0 ){
				const lineChart_3 = document.getElementById("lineChart_3").getContext('2d');
				//generate gradient
				const lineChart_3gradientStroke1 = lineChart_3.createLinearGradient(500, 0, 100, 0);
				lineChart_3gradientStroke1.addColorStop(0, "rgba(11, 42, 151, 1)");
				lineChart_3gradientStroke1.addColorStop(1, "rgba(11, 42, 151, 0.5)");

				const lineChart_3gradientStroke2 = lineChart_3.createLinearGradient(500, 0, 100, 0);
				lineChart_3gradientStroke2.addColorStop(0, "rgba(255, 188, 17, 1)");
				lineChart_3gradientStroke2.addColorStop(1, "rgba(255, 188, 17, 1)");

				const lineChart_3gradientStroke3 = lineChart_3.createLinearGradient(500, 0, 100, 0);
				lineChart_3gradientStroke3.addColorStop(0, "rgba(110, 197, 30, 1)");
				lineChart_3gradientStroke3.addColorStop(1, "rgba(110, 197, 30, 1)");

				const lineChart_3gradientStroke4 = lineChart_3.createLinearGradient(500, 0, 100, 0);
				lineChart_3gradientStroke4.addColorStop(0, "rgba(30, 167, 197, 1)");
				lineChart_3gradientStroke4.addColorStop(1, "rgba(30, 167, 197, 1)");

				class Custom extends Chart.LineController {
					draw() {
						// Call bubble controller method to draw all the points
						super.draw(arguments);	
						const ctx = this.chart.ctx;
						let _stroke = ctx.stroke;
						ctx.stroke = function(){
							ctx.save();
							ctx.shadowColor = 'rgba(110, 197, 30, 0.1)';
							ctx.shadowBlur = 10;
							ctx.shadowOffsetX = 0;
							ctx.shadowOffsetY = 4;
							_stroke.apply(this, arguments);
							ctx.restore();
							
						}
					}
				};
				Custom.id = 'shadowLine';
				Custom.defaults = Chart.LineController.defaults;

				// Stores the controller so that the chart initialization routine can look it up
				Chart.register(Custom);
					
				lineChart_3.height = 100;
				var yearToDateLeaveColors = [
					lineChart_3gradientStroke1,
					lineChart_3gradientStroke2,
					lineChart_3gradientStroke3,
					lineChart_3gradientStroke4
				];
				var yearToDateLeavePointColors = [
					'rgba(11, 42, 151, 0.5)',
					'rgba(254, 176, 25, 1)',
					'rgba(110, 197, 30, 1)',
					'rgba(30, 167, 197, 1)'
				];

				new Chart(lineChart_3, {
					type: 'line',
					data: {
						defaultFontFamily: 'Poppins',
						labels: yearToDateMonthLabels,
						datasets: yearToDateLeaveSeries.map(function(item, index) {
							return {
								label: item.name,
								data: item.data,
								borderColor: yearToDateLeaveColors[index % yearToDateLeaveColors.length],
								borderWidth: "2",
								backgroundColor: 'transparent',
								tension: 0.5,
								pointBackgroundColor: yearToDateLeavePointColors[index % yearToDateLeavePointColors.length]
							};
						})
					},
					options: {
						plugins:{
							legend:false,
							
						},
						scales: {
							y: chartJsIntegerYAxis(yearToDateLeaveSeries),
							x:{ 
								ticks: {
									padding: 5
								}
							}
						}
					}
				});
			}
		}
			/* Function ============ */
				return {
					init:function(){
					},
					
					
					load:function(){
						barChart1();
						lineChart2();	
						lineChart3();
						overtimeChart();
					},
					
					resize:function(){
						barChart1();
						lineChart2();
						lineChart3();
						overtimeChart();
					}
				}
			
			}();

			jQuery(document).ready(function(){
			});
				
			jQuery(window).on('load',function(){
				dzSparkLine.load();
			});

			jQuery(window).on('resize',function(){
				//dzSparkLine.resize();
				setTimeout(function(){ dzSparkLine.resize(); }, 1000);
			}); 
			
		})(jQuery);
	</script>

	<script>
		(function($){

		var dzProfile = function(){
			var chartProfileProgress = function(){
				var options = {
					series: [
						{
							name: 'Attendance Score',
							data: [18, 18, 18, 20, 20, 22, 13, 15, 16, 17, 18, 12],
						},
					],
					chart: {
						type: 'area',
						height: 100,
						toolbar: {
							show: false,
						},
						zoom: {
							enabled: false
						},
						sparkline: {
							enabled: true
						}
					},
					colors:[
						'var(--bs-primary)'
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
						curve:'straight',
						colors:['var(--bs-primary)'],
					},
					grid: {
						show:false,
						borderColor: '#eee',
						padding: {
							top: 0,
							right: 0,
							bottom: 0,
							left: -1
						}
					},
					states: {
						normal: {
							filter: {
								type: 'none',
								value: 0
							}
						},
						hover: {
							filter: {
								type: 'none',
								value: 0
							}
						},
						active: {
							allowMultipleDataPointsSelection: false,
							filter: {
								type: 'none',
								value: 0
							}
						}
					},
					xaxis: {
						categories: [
							'January',
							'February',
							'March',
							'April',
							'May',
							'June',
							'July',
							'August',
							'September',
							'October',
							'November',
							'December'
						],
						axisBorder: {
							show: false,
						},
						axisTicks: {
							show: false
						},
						labels: {
							show: false,
							style: {
								fontSize: '12px',
							}
						},
						crosshairs: {
							show: false,
							position: 'front',
							stroke: {
								width: 1,
								dashArray: 3
							}
						},
						tooltip: {
							enabled: false,
							formatter: undefined,
							offsetY: 0,
							style: {
								fontSize: '12px',
							}
						}
					},
					yaxis: {
						show: false,
					},
					fill: {
						opacity: 0.9,
						colors:'var(--bs-primary)',
						type: 'gradient', 
						gradient: {
							colorStops:[
								{
									offset: 0,
									color: 'var(--bs-primary)',
									opacity: .4
								},
								{
									offset: 0.6,
									color: 'var(--bs-primary)',
									opacity: .4
								},
								{
									offset: 100,
									color: 'white',
									opacity: 0
								}
							],
						}
					},
					tooltip: {
						enabled:true,
						style: {
							fontSize: '12px',
						},
						y: {
							formatter: function(val) {
								return val + " days"
							}
						}
					}
				};

				if($('#chartProfileProgress').length > 0){
					var handleProfileProgress = new ApexCharts(document.querySelector("#chartProfileProgress"), options);
					handleProfileProgress.render();
				}
			
			}
			
			/* Function ============ */
			return {
				
				load:function(){
					chartProfileProgress();
				},
				
				resize:function(){
					lineChartSecuritySummary();
				}
			}
			
		}();

		jQuery(window).on('load',function(){
			setTimeout(function(){
				dzProfile.load();
			}, 1000); 
		});

		jQuery(window).on('resize',function () {
			setTimeout(function(){
				dzProfile.resize();
			}, 1000);
			
		});

		})(jQuery);

	</script>

	<!-- Chart -->
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
				series: attendanceOverviewChartSeries,
				chart: {
				type: 'donut',
				height:200,
				},
				legend: {
					show:false,
				},
				fill:{
					colors: attendanceOverviewChartColors
				},
				stroke:{
					width:0,
				},
				colors: attendanceOverviewChartColors,
				dataLabels: {
				enabled: false
				}
				};

				var chart = new ApexCharts(document.querySelector("#pieChart"), options);
				chart.render();
			}
			
			var radialBar = function(){
				var options = {
				series: [attendanceProgressPercent],
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

				var chart = new ApexCharts(document.querySelector("#radialBar"), options);
				chart.render();
			}
			var donutChart = function(){
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
						chartBar();
						chartBar2();
						chartBar3();
						pieChart();
						radialBar();
						donutChart();
					},
					
					resize:function(){
						
					}
				}
			
			}();

			jQuery(document).ready(function(){
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

	<script>
		(function($) {
    /* "use strict" */


		var dzChartlist = function(){
			//let draw = Chart.controllers.line.__super__.draw; //draw shadow
			var screenWidth = $(window).width();
			var chartBarRunning = function(){
				
				var chartBarRunningOptions = {
						series: weeklyAttendanceSeries,
						chart: {
						type: 'bar',
						height: 370,
						
						toolbar: {
							show: false,
						},
						
					},
					plotOptions: {
					bar: {
						horizontal: false,
						endingShape:"flat",
						borderRadius: 5,
						columnWidth: '50%',
						
					},
					},
					colors:['#2BC155', '#FF3282'],
					dataLabels: {
					enabled: false,
					},
					markers: {
						shape: "circle",
					},
					legend: {
						show: false,
						fontSize: '12px',
						labels: {
							colors: '#000000',
							
							},
						markers: {
						width: 18,
						height: 18,
						strokeWidth: 0,
						strokeColor: '#fff',
						fillColors: undefined,
						radius: 12,	
						}
					},
					stroke: {
					show: true,
					width: 6,
					colors: ['transparent']
					},
					grid: {
						borderColor: 'var(--bs-light)',
					},
					xaxis: {
						
					categories: weeklyDayLabels,
					labels: {
					style: {
						colors: 'var(--bs-dark)',
						fontSize: '13px',
						fontFamily: 'poppins',
						fontWeight: 400,
						cssClass: 'apexcharts-xaxis-label',
						},
					},
						axisTicks:{
								show:false,
						},
					crosshairs: {
					show: false,
					}
					},
					yaxis: apexIntegerYAxis(weeklyAttendanceSeries),
					fill: {
					opacity: 1,
					colors:['#2BC155', '#FF3282'],
					},
					tooltip: {
					y: {
						formatter: function (val) {
						return val + " Staff"
						}
					}
					},
					responsive: [{
						breakpoint: 575,
						options: {
							plotOptions: {
							bar: {
								columnWidth: '40%',
								
							},
							},
							chart:{
								height:250,
							}
						}
					}]
					};

					var chartBarRunningObject = new ApexCharts(document.querySelector("#chartBarRunning"), chartBarRunningOptions);
					chartBarRunningObject.render();
					
			}

			var chartLeaveBt = function(){
				
				var chartLeaveBtOptions = {
						series: weeklyOutOfOfficeSeries,
						chart: {
						type: 'bar',
						height: 370,
						
						toolbar: {
							show: false,
						},
						
					},
					plotOptions: {
					bar: {
						horizontal: false,
						endingShape:"flat",
						borderRadius: 5,
						columnWidth: '50%',
						
					},
					},
					colors:['#1EA7C5', '#1E3FB4', '#A02CFA'],
					dataLabels: {
					enabled: false,
					},
					markers: {
						shape: "circle",
					},
					legend: {
						show: false,
						fontSize: '12px',
						labels: {
							colors: '#000000',
							
							},
						markers: {
						width: 18,
						height: 18,
						strokeWidth: 0,
						strokeColor: '#fff',
						fillColors: undefined,
						radius: 12,	
						}
					},
					stroke: {
					show: true,
					width: 6,
					colors: ['transparent']
					},
					grid: {
						borderColor: 'var(--bs-light)',
					},
					xaxis: {
						
					categories: weeklyDayLabels,
					labels: {
					style: {
						colors: 'var(--bs-dark)',
						fontSize: '13px',
						fontFamily: 'poppins',
						fontWeight: 400,
						cssClass: 'apexcharts-xaxis-label',
						},
					},
						axisTicks:{
								show:false,
						},
					crosshairs: {
					show: false,
					}
					},
					yaxis: apexIntegerYAxis(weeklyOutOfOfficeSeries),
					fill: {
					opacity: 1,
					colors:['#1EA7C5', '#1E3FB4', '#A02CFA'],
					},
					tooltip: {
					y: {
						formatter: function (val) {
						return val + " Staff"
						}
					}
					},
					responsive: [{
						breakpoint: 575,
						options: {
							plotOptions: {
							bar: {
								columnWidth: '40%',
								
							},
							},
							chart:{
								height:250,
							}
						}
					}]
					};

					var chartLeaveBtObject = new ApexCharts(document.querySelector("#chartLeaveBt"), chartLeaveBtOptions);
					chartLeaveBtObject.render();
					
			}


			var attendanceMonthly = function(){
				
				var attendanceMonthlyOptions = {
						series: monthlyAttendanceSeries,
						chart: {
						type: 'bar',
						height: 370,
						
						toolbar: {
							show: false,
						},
						
					},
					plotOptions: {
					bar: {
						horizontal: false,
						endingShape:"flat",
						borderRadius: 5,
						columnWidth: '50%',
						
					},
					},
					colors:['#2BC155', '#F94687'],
					dataLabels: {
					enabled: false,
					},
					markers: {
						shape: "circle",
					},
					legend: {
						show: false,
						fontSize: '12px',
						labels: {
							colors: '#000000',
							
							},
						markers: {
						width: 18,
						height: 18,
						strokeWidth: 0,
						strokeColor: '#fff',
						fillColors: undefined,
						radius: 12,	
						}
					},
					stroke: {
					show: true,
					width: 6,
					colors: ['transparent']
					},
					grid: {
						borderColor: 'var(--bs-light)',
					},
					xaxis: {
						
					categories: monthlyDayLabels,
					labels: {
					style: {
						colors: 'var(--bs-dark)',
						fontSize: '13px',
						fontFamily: 'poppins',
						fontWeight: 400,
						cssClass: 'apexcharts-xaxis-label',
						},
					},
						axisTicks:{
								show:false,
						},
					crosshairs: {
					show: false,
					}
					},
					yaxis: apexIntegerYAxis(monthlyAttendanceSeries),
					fill: {
					opacity: 1,
					colors:['#2BC155', '#F94687'],
					},
					tooltip: {
					y: {
						formatter: function (val) {
						return val + " Staff"
						}
					}
					},
					responsive: [{
						breakpoint: 575,
						options: {
							plotOptions: {
							bar: {
								columnWidth: '40%',
								
							},
							},
							chart:{
								height:250,
							}
						}
					}]
					};

					var attendanceMonthlyObject = new ApexCharts(document.querySelector("#attendanceMonthly"), attendanceMonthlyOptions);
					attendanceMonthlyObject.render();
					
			}

			var oooMonthly = function(){
				
				var oooMonthlyOptions = {
						series: monthlyOutOfOfficeSeries,
						chart: {
						type: 'bar',
						height: 370,
						
						toolbar: {
							show: false,
						},
						
					},
					plotOptions: {
					bar: {
						horizontal: false,
						endingShape:"flat",
						borderRadius: 5,
						columnWidth: '50%',
						
					},
					},
					colors:['#1EA7C5', '#1E3FB4', '#A02CFA'],
					dataLabels: {
					enabled: false,
					},
					markers: {
						shape: "circle",
					},
					legend: {
						show: false,
						fontSize: '12px',
						labels: {
							colors: '#000000',
							
							},
						markers: {
						width: 18,
						height: 18,
						strokeWidth: 0,
						strokeColor: '#fff',
						fillColors: undefined,
						radius: 12,	
						}
					},
					stroke: {
					show: true,
					width: 6,
					colors: ['transparent']
					},
					grid: {
						borderColor: 'var(--bs-light)',
					},
					xaxis: {
						
					categories: monthlyDayLabels,
					labels: {
					style: {
						colors: 'var(--bs-dark)',
						fontSize: '13px',
						fontFamily: 'poppins',
						fontWeight: 400,
						cssClass: 'apexcharts-xaxis-label',
						},
					},
						axisTicks:{
								show:false,
						},
					crosshairs: {
					show: false,
					}
					},
					yaxis: apexIntegerYAxis(monthlyOutOfOfficeSeries),
					fill: {
					opacity: 1,
					colors:['#1EA7C5', '#1E3FB4', '#A02CFA'],
					},
					tooltip: {
					y: {
						formatter: function (val) {
						return val + " Staff"
						}
					}
					},
					responsive: [{
						breakpoint: 575,
						options: {
							plotOptions: {
							bar: {
								columnWidth: '40%',
								
							},
							},
							chart:{
								height:250,
							}
						}
					}]
					};

					var oooMonthlyObject = new ApexCharts(document.querySelector("#oooMonthly"), oooMonthlyOptions);
					oooMonthlyObject.render();
					
			}

			var chartBarCycle = function(){
				
				var chartBarCycleOptions = {
						series: [
							{
							name: 'Cycling',
							data: [80, 40, 55, 20, 45, 30, 80, 90, 85, 90, 30, 85]
							}, 
							
						],
						chart: {
						type: 'bar',
						height: 370,
						
						toolbar: {
							show: false,
						},
						
					},
					plotOptions: {
					bar: {
						horizontal: false,
						endingShape:'rounded',
						borderRadius:5,
						columnWidth: '50%',
						
					},
					},
					colors:['#FF3282'],
					dataLabels: {
					enabled: false,
					},
					markers: {
						shape: "circle",
					},
					legend: {
						show: false,
						fontSize: '12px',
						labels: {
							colors: '#000000',
							
							},
						markers: {
						width: 18,
						height: 18,
						strokeWidth: 0,
						strokeColor: '#fff',
						fillColors: undefined,
						radius: 12,	
						}
					},
					stroke: {
					show: true,
					width: 6,
					colors: ['transparent']
					},
					grid: {
						borderColor: 'var(--bs-light)',
					},
					xaxis: {
						
					categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
					labels: {
					style: {
						colors: '#787878',
						fontSize: '13px',
						fontFamily: 'poppins',
						fontWeight: 100,
						cssClass: 'apexcharts-xaxis-label',
						},
					},
					axisTicks:{
						show:false,
						
					},
					crosshairs: {
					show: false,
					}
					},
					yaxis: {
						labels: {
							offsetX:-16,
						style: {
							colors: '#787878',
							fontSize: '13px',
							fontFamily: 'poppins',
							fontWeight: 100,
							cssClass: 'apexcharts-xaxis-label',
						},
					},
					},
					fill: {
					opacity: 1,
					colors:['#FF3282'],
					},
					tooltip: {
					y: {
						formatter: function (val) {
						return "$ " + val + " thousands"
						}
					}
					},
					responsive: [{
						breakpoint: 575,
						options: {
							plotOptions: {
							bar: {
								columnWidth: '40%',
								
							},
							},
							chart:{
								height:250,
							}
						}
					}]
					};

					var chartBarCycleObject = new ApexCharts(document.querySelector("#chartBarCycle"), chartBarCycleOptions);
					chartBarCycleObject.render();
			}	
			/* Function ============ */
				return {
					init:function(){
					},
					
					
					load:function(){
						chartBarRunning();
						chartLeaveBt();
						chartBarCycle();
						attendanceMonthly();
						oooMonthly();
					},
					
					resize:function(){
						
					}
				}
			
			}();

			jQuery(document).ready(function(){
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

    <script>
        (function () {
            function setLeaveModalText(elementId, value) {
                var element = document.getElementById(elementId);

                if (element) {
                    element.textContent = value && value.trim() !== '' ? value : '-';
                }
            }

            function setSickMedicalNotes(button) {
                var imageLink = document.getElementById('sickMedicalNotesImageLink');
                var image = document.getElementById('sickMedicalNotesImage');
                var fileLink = document.getElementById('sickMedicalNotesFileLink');
                var fallback = document.getElementById('sickMedicalNotesFallback');
                var attachmentUrl = button.dataset.leaveAttachmentUrl || '';
                var attachmentIsImage = button.dataset.leaveAttachmentIsImage === '1';

                if (!imageLink || !image || !fileLink || !fallback) {
                    return;
                }

                imageLink.classList.add('d-none');
                fileLink.classList.add('d-none');
                fallback.classList.remove('d-none');
                fallback.textContent = 'No medical note attachment.';
                imageLink.href = '#';
                fileLink.href = '#';
                image.src = '';

                if (attachmentUrl === '') {
                    return;
                }

                if (attachmentIsImage) {
                    imageLink.href = attachmentUrl;
                    image.src = attachmentUrl;
                    imageLink.classList.remove('d-none');
                } else {
                    fileLink.href = attachmentUrl;
                    fileLink.classList.remove('d-none');
                }

                fallback.classList.add('d-none');
            }

            document.querySelectorAll('[data-admin-attendance-leave-details]').forEach(function (button) {
                button.addEventListener('click', function () {
                    var modalId = (button.dataset.bsTarget || '').replace('#', '');
                    var isSickLeave = modalId === 'sick';
                    var prefix = isSickLeave ? 'sick' : 'annualLeave';

                    setLeaveModalText(prefix + 'Label', button.dataset.leaveModalTitle);
                    setLeaveModalText(prefix + 'TypeText', button.dataset.leaveType);
                    setLeaveModalText(prefix + 'ReasonText', button.dataset.leaveReason);
                    setLeaveModalText(prefix + 'DurationText', button.dataset.leaveDuration);
                    setLeaveModalText(prefix + 'StatusText', button.dataset.leaveStatus);

                    var statusDateElement = document.getElementById(prefix + 'StatusDateText');
                    if (statusDateElement) {
                        var statusDate = button.dataset.leaveStatusDate || '';
                        statusDateElement.textContent = statusDate !== '' ? 'on ' + statusDate : '';
                    }

                    if (isSickLeave) {
                        setSickMedicalNotes(button);
                    }
                });
            });
        })();
    </script>
@endsection
