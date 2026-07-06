@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')

@php
    $dashboardCssPath = public_path('assets/css/dashboard.css');
    $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    $attendanceCssPath = public_path('assets/css/attendance.css');
    $attendanceCssVersion = file_exists($attendanceCssPath) ? filemtime($attendanceCssPath) : time();
@endphp
<link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
<link rel="stylesheet" href="{{ asset('assets/css/attendance.css') }}?v={{ $attendanceCssVersion }}">
{{-- <link rel="stylesheet" href="https://unpkg.com/antd@6.2.3/dist/antd.css"> --}}
<style>
    .project-task-overview-card .card-body {
        min-height: 235px;
        padding-top: .5rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .project-monthly-overview-row > .project-monthly-overview-column {
        display: flex;
    }

    .project-monthly-overview-row .card {
        width: 100%;
    }

    .project-progress-card .card-body {
        display: flex;
        align-items: center;
    }

    .project-progress-card .card-body > .row {
        width: 100%;
    }

    .project-progress-radial-chart {
        width: 100%;
        max-width: 320px;
        margin-inline: auto;
        overflow: hidden;
    }

    .project-task-donut-wrap {
        min-height: 170px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem !important;
    }

    .project-task-donut-chart {
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: conic-gradient(#2bc155 0 var(--project-task-completed-rate, 0%), #f94687 var(--project-task-completed-rate, 0%) 100%);
        position: relative;
    }

    .project-task-donut-chart::after {
        content: "";
        position: absolute;
        inset: 45px;
        border-radius: 50%;
        background: #fff;
    }

    .project-task-donut-icon svg {
        width: 34px;
        height: 64px;
    }

    .project-task-legend {
        gap: .5rem 1.75rem;
        padding-left: 0;
        margin-top: .75rem;
        margin-bottom: 0;
    }

    .project-task-legend li {
        min-width: 96px;
        margin: 0;
    }

    .project-overview-chart-wrapper {
        position: relative;
        width: 100%;
        height: 300px;
    }

    .project-overview-chart-wrapper canvas {
        width: 100% !important;
        height: 100% !important;
    }

    @media (max-width: 767.98px) {
        .project-summary-mobile-slider {
            display: flex;
            flex-wrap: nowrap;
            gap: 12px;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .project-summary-mobile-slider::-webkit-scrollbar {
            display: none;
            width: 0;
            height: 0;
        }

        .project-summary-mobile-slide {
            flex: 0 0 100%;
            width: 100%;
            max-width: 100%;
            scroll-snap-align: start;
        }

        .project-task-overview-card .card-body {
            min-height: 220px;
        }

        .project-task-donut-wrap {
            min-height: 160px;
        }

        .project-task-donut-chart {
            width: 170px;
            height: 170px;
        }

        .project-task-donut-chart::after {
            inset: 43px;
        }

        .project-progress-card .card-body {
            display: block;
        }

        .project-overview-chart-wrapper {
            height: 280px;
        }

        .project-overview-chart-card .card-body {
            padding-left: 12px;
            padding-right: 12px;
        }
    }
</style>

@endsection

@section('content')

@include('layouts.breadcrumb', [
    'title' => 'Project Management',
    'current' => 'Overview',
    'homeRoute' => 'dashboard',
])

@include('project_management.layouts.profile-index')

<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Overview</h5>
    </div>
</div>

@include('project_management.overview.partials.summary-cards')

@php
    $projectCurrentMonthLabel = is_string($projectCurrentMonthLabel ?? null) && trim($projectCurrentMonthLabel) !== ''
        ? trim($projectCurrentMonthLabel)
        : now('Asia/Jakarta')->format('F');
    $projectCurrentMonthYearLabel = is_string($projectCurrentMonthYearLabel ?? null) && trim($projectCurrentMonthYearLabel) !== ''
        ? trim($projectCurrentMonthYearLabel)
        : now('Asia/Jakarta')->format('F Y');
    $projectTaskCompletionRate = (int) ($projectTaskCompletionRate ?? 0);
    $projectTotalTasksCount = (int) ($projectTotalTasksCount ?? 0);
    $projectTasksCompletedCount = (int) ($projectTasksCompletedCount ?? 0);
    $projectTasksIncompleteCount = (int) ($projectTasksIncompleteCount ?? max(0, $projectTotalTasksCount - $projectTasksCompletedCount));
    $projectDailyTasksCount = (int) ($projectDailyTasksCount ?? 0);
    $projectDailyTasksCompletedCount = (int) ($projectDailyTasksCompletedCount ?? 0);
    $projectDailyTasksRate = (int) ($projectDailyTasksRate ?? 0);
    $projectProjectTasksCount = (int) ($projectProjectTasksCount ?? 0);
    $projectProjectTasksCompletedCount = (int) ($projectProjectTasksCompletedCount ?? 0);
    $projectProjectTasksRate = (int) ($projectProjectTasksRate ?? 0);
    $projectWeeklyTasksCompletedCount = (int) ($projectWeeklyTasksCompletedCount ?? 0);
    $projectWeeklyTasksIncompleteCount = (int) ($projectWeeklyTasksIncompleteCount ?? 0);
    $projectWeeklyTasksTotalCount = (int) ($projectWeeklyTasksTotalCount ?? 0);
    $projectWeeklyTasksCompletedRate = (int) ($projectWeeklyTasksCompletedRate ?? 0);
    $projectWeeklyTasksIncompleteRate = (int) ($projectWeeklyTasksIncompleteRate ?? 0);
    $projectDailyTaskDonutTotal = max(1, $projectDailyTasksCount);
    $projectProjectTaskDonutTotal = max(1, $projectProjectTasksCount);
    $projectWeeklyTaskDonutTotal = max(1, $projectWeeklyTasksTotalCount);
    $projectCurrentYearLabel = is_string($projectCurrentYearLabel ?? null) && trim($projectCurrentYearLabel) !== ''
        ? trim($projectCurrentYearLabel)
        : now('Asia/Jakarta')->format('Y');
    $projectMonthlyOverviewSelectedMonth = is_string($projectMonthlyOverviewSelectedMonth ?? null) && trim($projectMonthlyOverviewSelectedMonth) !== ''
        ? trim($projectMonthlyOverviewSelectedMonth)
        : now('Asia/Jakarta')->format('Y-m');
    $projectMonthlyOverviewFilterOptions = is_array($projectMonthlyOverviewFilterOptions ?? null) ? $projectMonthlyOverviewFilterOptions : [];
    $projectMonthlyOverviewChartsByMonth = is_array($projectMonthlyOverviewChartsByMonth ?? null) ? $projectMonthlyOverviewChartsByMonth : [];
    $projectMonthlyOverviewChartLabels = is_array($projectMonthlyOverviewChartLabels ?? null) ? $projectMonthlyOverviewChartLabels : [];
    $projectMonthlyOverviewCompletedSeries = is_array($projectMonthlyOverviewCompletedSeries ?? null) ? $projectMonthlyOverviewCompletedSeries : [];
    $projectMonthlyOverviewIncompleteSeries = is_array($projectMonthlyOverviewIncompleteSeries ?? null) ? $projectMonthlyOverviewIncompleteSeries : [];
    $projectYearlyOverviewChartLabels = is_array($projectYearlyOverviewChartLabels ?? null) ? $projectYearlyOverviewChartLabels : [];
    $projectYearlyOverviewDailySeries = is_array($projectYearlyOverviewDailySeries ?? null) ? $projectYearlyOverviewDailySeries : [];
    $projectYearlyOverviewProjectSeries = is_array($projectYearlyOverviewProjectSeries ?? null) ? $projectYearlyOverviewProjectSeries : [];
@endphp

<div class="row align-items-stretch project-monthly-overview-row mb-4">
    <div class="col-md-3 col-12 project-monthly-overview-column">
        <div class="row h-100 w-100">
            <div class="col-md-12 d-flex">
                <div class="card project-task-overview-card h-100">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title">Task Overview ({{ $projectCurrentMonthLabel }})</h4>
                    </div>
                    <div class="card-body">
                        <div class="project-task-donut-wrap position-relative mb-4">
                            <div
                                class="project-task-donut-chart"
                                style="--project-task-completed-rate: {{ $projectTaskCompletionRate }}%;"
                                aria-hidden="true"></div>
                            <div class="project-task-donut-icon position-absolute top-50 start-50 translate-middle">
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
                        <ul class="project-task-legend d-flex flex-wrap justify-content-center list-unstyled">
                            <li class="d-flex align-items-center gap-3">
                                <i class="fa fa-circle text-success"></i>
                                <span class="fs-12 text-black">Completed</span> 
                            </li>
                            <li class="d-flex align-items-center gap-3">
                                <i class="fa fa-circle text-danger"></i>
                                <span class="fs-12 text-black">Incomplete</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-9 col-12 project-monthly-overview-column">
        <div class="card project-progress-card h-100">
            <div class="card-header flex-wrap border-0 pb-0">
                <h4 class="card-title">Progress ({{ $projectCurrentMonthLabel }})</h4>
            </div>
            <div class="card-body pt-0 pb-3">
                <div class="row align-items-center">
                    <div class="col-lg-4 mb-lg-0 mb-4 text-center radialBar">
                        <div id="radialBar" class="project-progress-radial-chart" data-progress-rate="{{ $projectTaskCompletionRate }}"></div>
                        <h4 class="fs-18 text-black">Tasks Completed ({{ $projectTasksCompletedCount }}/{{ $projectTotalTasksCount }} Completed)</h4>
                        <p class="fs-14">Tracking your assigned tasks and active deliverables for the current month.</p>
                    </div>
                    <div class="col-lg-8">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center mb-sm-5 mb-3">
                                    <div class="position-relative me-3">
                                        <span class="donut" data-peity='{ "fill": ["var(--bs-info)", "var(--bs-light)"],   "innerRadius": 34, "radius": 10}'>{{ $projectDailyTasksCompletedCount }}/{{ $projectDailyTaskDonutTotal }}</span>
                                        
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
                                        <h4 class="fs-18 text-black">Daily Tasks ({{ $projectDailyTasksRate }}%)</h4>
                                        <span>{{ $projectDailyTasksCompletedCount }} Task / {{ $projectDailyTasksCount }} Daily Tasks</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center mb-sm-5 mb-3">
                                    <div class="position-relative me-3">
                                        <span class="donut" data-peity='{ "fill": ["var(--bs-secondary)", "var(--bs-light)"],   "innerRadius": 34, "radius": 10}'>{{ $projectProjectTasksCompletedCount }}/{{ $projectProjectTaskDonutTotal }}</span>
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
                                        <h4 class="fs-18 text-black">Project Tasks ({{ $projectProjectTasksRate }}%)</h4>
                                        <span>{{ $projectProjectTasksCompletedCount }} Task / {{ $projectProjectTasksCount }} Project Tasks</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center mb-sm-5 mb-3">
                                    <div class="position-relative me-3">
                                        <span class="donut" data-peity='{ "fill": ["var(--bs-success)", "var(--bs-light)"],   "innerRadius": 34, "radius": 10}'>{{ $projectWeeklyTasksCompletedCount }}/{{ $projectWeeklyTaskDonutTotal }}</span>
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
                                        <h4 class="fs-18 text-black">Completed Tasks This Week ({{ $projectWeeklyTasksCompletedRate }}%)</h4>
                                        <span>{{ $projectWeeklyTasksCompletedCount }} Task / {{ $projectWeeklyTasksTotalCount }} Tasks This Week</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center mb-sm-5 mb-3">
                                    <div class="position-relative me-3">
                                        <span class="donut" data-peity='{ "fill": ["var(--bs-danger)", "var(--bs-light)"],   "innerRadius": 34, "radius": 10}'>{{ $projectWeeklyTasksIncompleteCount }}/{{ $projectWeeklyTaskDonutTotal }}</span>
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
                                        <h4 class="fs-18 text-black">Incomplete Tasks This Week ({{ $projectWeeklyTasksIncompleteRate }}%)</h4>
                                        <span>{{ $projectWeeklyTasksIncompleteCount }} Task / {{ $projectWeeklyTasksTotalCount }} Tasks This Week</span>
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

<div class="row">
    <div class="col-md-6 col-12">
        <div class="row">
            <div class="col-md-12">
                <div class="card project-overview-chart-card">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title" id="projectMonthlyOverviewTitle">Task Overview ({{ $projectCurrentMonthYearLabel }})</h4>
                        <div class="clearfix">
                            <select class="selectpicker form-select form-select-sm" id="projectMonthlyOverviewMonthFilter">
                                @forelse ($projectMonthlyOverviewFilterOptions as $projectMonthlyOverviewFilterOption)
                                    @php
                                        $projectMonthlyOverviewFilterValue = is_array($projectMonthlyOverviewFilterOption) ? (string) ($projectMonthlyOverviewFilterOption['value'] ?? '') : '';
                                        $projectMonthlyOverviewFilterLabel = is_array($projectMonthlyOverviewFilterOption) ? (string) ($projectMonthlyOverviewFilterOption['label'] ?? '') : '';
                                    @endphp
                                    @if ($projectMonthlyOverviewFilterValue !== '' && $projectMonthlyOverviewFilterLabel !== '')
                                        <option value="{{ $projectMonthlyOverviewFilterValue }}" @selected($projectMonthlyOverviewFilterValue === $projectMonthlyOverviewSelectedMonth)>
                                            {{ $projectMonthlyOverviewFilterLabel }}
                                        </option>
                                    @endif
                                @empty
                                    <option value="{{ $projectMonthlyOverviewSelectedMonth }}">{{ $projectCurrentMonthYearLabel }}</option>
                                @endforelse
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="project-overview-chart-wrapper">
                            <canvas
                                id="barChart_3"
                                data-selected-month="{{ $projectMonthlyOverviewSelectedMonth }}"
                                data-monthly-charts='@json($projectMonthlyOverviewChartsByMonth)'
                                data-chart-labels='@json($projectMonthlyOverviewChartLabels)'
                                data-completed-series='@json($projectMonthlyOverviewCompletedSeries)'
                                data-incomplete-series='@json($projectMonthlyOverviewIncompleteSeries)'></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-12">
        <div class="row">
            <div class="col-md-12">
                <div class="card project-overview-chart-card">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title">Task Overview ({{ $projectCurrentYearLabel }})</h4>
                    </div>
                    <div class="card-body">
                        <div class="project-overview-chart-wrapper">
                            <canvas
                                id="lineChart_3"
                                data-chart-labels='@json($projectYearlyOverviewChartLabels)'
                                data-daily-series='@json($projectYearlyOverviewDailySeries)'
                                data-project-series='@json($projectYearlyOverviewProjectSeries)'></canvas>
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
        $apexChartsPath = public_path('assets/vendor/apexcharts/dist/apexcharts.min.js');
        $apexChartsVersion = file_exists($apexChartsPath) ? filemtime($apexChartsPath) : time();
    @endphp
    <script src="{{ asset('assets/vendor/chart-js/chart.bundle.min.js') }}?v={{ $chartJsVersion }}"></script>
    <script src="{{ asset('assets/vendor/apexcharts/dist/apexcharts.min.js') }}?v={{ $apexChartsVersion }}"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<script>
		(function($) {
		/* "use strict" */

		var dzSparkLine = function(){
			//let draw = Chart.controllers.line.__super__.draw; //draw shadow
			
			var screenWidth = $(window).width();
			var monthlyOverviewChart = null;
			var yearlyOverviewChart = null;
			var parseChartData = function(value, fallback){
				if(!value){
					return fallback;
				}

				try {
					var parsedValue = JSON.parse(value);

					return Array.isArray(parsedValue) ? parsedValue : fallback;
				} catch (error) {
					return fallback;
				}
			};
			var parseChartObject = function(value, fallback){
				if(!value){
					return fallback;
				}

				try {
					var parsedValue = JSON.parse(value);

					return parsedValue && typeof parsedValue === 'object' && !Array.isArray(parsedValue) ? parsedValue : fallback;
				} catch (error) {
					return fallback;
				}
			};
			var isMobileChartViewport = function(){
				return window.matchMedia('(max-width: 767.98px)').matches;
			};
			var chartAxisFont = function(){
				return {
					size: isMobileChartViewport() ? 10 : 12
				};
			};
			var chartXAxisTickOptions = function(isMonthly){
				return {
					autoSkip: true,
					maxTicksLimit: isMobileChartViewport() ? 6 : 12,
					maxRotation: isMobileChartViewport() ? 0 : 50,
					minRotation: 0,
					font: chartAxisFont(),
					callback: function(value){
						var label = String(this.getLabelForValue(value) || '');

						if(isMobileChartViewport() && isMonthly){
							return label.split('-')[0] || label;
						}

						if(isMobileChartViewport()){
							return label.substring(0, 3);
						}

						return label;
					}
				};
			};
			var chartYAxisTickOptions = function(stepSize){
				return {
					beginAtZero: true,
					stepSize: stepSize,
					precision: 0,
					padding: isMobileChartViewport() ? 4 : 10,
					font: chartAxisFont(),
					callback: function(value) {
						return Number.isInteger(value) ? value : '';
					}
				};
			};
			var hiddenChartLegendOptions = function(){
				return {
					display: false
				};
			};

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

			var barChart3 = function(){
				//stalked bar chart
				if(jQuery('#barChart_3').length > 0 ){
					const barChart_3target = document.getElementById("barChart_3");
					const barChart_3 = barChart_3target.getContext('2d');
					const monthlyChartMap = parseChartObject(barChart_3target.getAttribute('data-monthly-charts'), {});
					const selectedMonth = barChart_3target.getAttribute('data-selected-month');
					const selectedMonthlyChart = monthlyChartMap[selectedMonth] || {};
					const monthlyLabels = Array.isArray(selectedMonthlyChart.labels) ? selectedMonthlyChart.labels : parseChartData(barChart_3target.getAttribute('data-chart-labels'), []);
					const completedSeries = Array.isArray(selectedMonthlyChart.completed) ? selectedMonthlyChart.completed : parseChartData(barChart_3target.getAttribute('data-completed-series'), []);
					const incompleteSeries = Array.isArray(selectedMonthlyChart.incomplete) ? selectedMonthlyChart.incomplete : parseChartData(barChart_3target.getAttribute('data-incomplete-series'), []);
					//generate gradient
					const barChart_3gradientStroke = barChart_3.createLinearGradient(50, 100, 50, 50);
					barChart_3gradientStroke.addColorStop(0, "rgba(43, 193, 85, 1)");
					barChart_3gradientStroke.addColorStop(1, "rgba(43, 193, 85, 1)");

					const barChart_3gradientStroke2 = barChart_3.createLinearGradient(50, 100, 50, 50);
					barChart_3gradientStroke2.addColorStop(0, "rgba(249, 69, 135, 1)");
					barChart_3gradientStroke2.addColorStop(1, "rgba(249, 69, 135, 1)");
					
					barChart_3.height = 100;

					let barChartData = {
						defaultFontFamily: 'Poppins',
						labels: monthlyLabels,
						datasets: [{
							label: 'Completed',
							backgroundColor: barChart_3gradientStroke,
							hoverBackgroundColor: barChart_3gradientStroke, 
							data: completedSeries
						}, {
							label: 'Incomplete',
							backgroundColor: barChart_3gradientStroke2,
							hoverBackgroundColor: barChart_3gradientStroke2, 
							data: incompleteSeries
						}]
					};

					if(monthlyOverviewChart){
						monthlyOverviewChart.destroy();
					}

					monthlyOverviewChart = new Chart(barChart_3, {
						type: 'bar',
						data: barChartData,
						options: {
							responsive: true,
							maintainAspectRatio: false,
							plugins:{
								legend: hiddenChartLegendOptions(),
								tooltip: {
									mode: 'index',
									intersect: false
								},
								
							},
							title: {
								display: false
							},
							scales: {
								x:{
									stacked: true,
									ticks: chartXAxisTickOptions(true)
								},
								y:{
									stacked: true,
									beginAtZero: true,
									ticks: chartYAxisTickOptions(1)
								}
							}
						}
					});

					$('#projectMonthlyOverviewMonthFilter').off('change.projectMonthlyOverview').on('change.projectMonthlyOverview', function(){
						var selectedFilterMonth = this.value;
						var chartData = monthlyChartMap[selectedFilterMonth] || {
							labels: [],
							completed: [],
							incomplete: []
						};
						var selectedLabel = $(this).find('option:selected').text().trim();

						monthlyOverviewChart.data.labels = Array.isArray(chartData.labels) ? chartData.labels : [];
						monthlyOverviewChart.data.datasets[0].data = Array.isArray(chartData.completed) ? chartData.completed : [];
						monthlyOverviewChart.data.datasets[1].data = Array.isArray(chartData.incomplete) ? chartData.incomplete : [];
						monthlyOverviewChart.update();

						if(selectedLabel){
							$('#projectMonthlyOverviewTitle').text('Task Overview (' + selectedLabel + ')');
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
				if(!Chart.registry.controllers.items.shadowLine){
					Chart.register(Custom);
				}
					
					
				lineChart_2.height = 100;

				new Chart(lineChart_2, {
					type: 'line',
					data: {
						defaultFontFamily: 'Poppins',
						labels: [
							'Jan', 'Feb', 'Mar', 
							'Apr', 'May', 'Jun', 
							'Jul', 'Aug', 'Sep', 
							'Oct', 'Nov', 'Dec'
						],
						datasets: [
							{
								label: "My Overtime Hours",
								data: [25, 20, 60, 41, 66, 45, 80, 25, 20, 60, 41, 12],
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
							y:{
								max: 100, 
								min: 0, 
								ticks: {
									beginAtZero: true, 
									stepSize: 20, 
									padding: 10
								}
							},
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
				const lineChart_3target = document.getElementById("lineChart_3");
				const lineChart_3 = lineChart_3target.getContext('2d');
				const yearlyLabels = parseChartData(lineChart_3target.getAttribute('data-chart-labels'), []);
				const dailySeries = parseChartData(lineChart_3target.getAttribute('data-daily-series'), []);
				const projectSeries = parseChartData(lineChart_3target.getAttribute('data-project-series'), []);
				//generate gradient
				const lineChart_3gradientStroke1 = lineChart_3.createLinearGradient(500, 0, 100, 0);
				lineChart_3gradientStroke1.addColorStop(0, "rgba(11, 42, 151, 1)");
				lineChart_3gradientStroke1.addColorStop(1, "rgba(11, 42, 151, 0.5)");

				const lineChart_3gradientStroke2 = lineChart_3.createLinearGradient(500, 0, 100, 0);
				lineChart_3gradientStroke2.addColorStop(0, "rgba(255, 188, 17, 1)");
				lineChart_3gradientStroke2.addColorStop(1, "rgba(255, 188, 17, 1)");

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
				if(!Chart.registry.controllers.items.shadowLine){
					Chart.register(Custom);
				}
					
				lineChart_3.height = 100;

				if(yearlyOverviewChart){
					yearlyOverviewChart.destroy();
				}

				yearlyOverviewChart = new Chart(lineChart_3, {
					type: 'line',
					data: {
						defaultFontFamily: 'Poppins',
						labels: yearlyLabels,
						datasets: [
							{
								label: "Daily Task",
								data: dailySeries,
								borderColor: lineChart_3gradientStroke1,
								borderWidth: "2",
								backgroundColor: 'transparent', 
								tension: 0.5,
								pointBackgroundColor: 'rgba(11, 42, 151, 0.5)'
							}, {
								label: "Project Task",
								data: projectSeries,
								borderColor: lineChart_3gradientStroke2,
								borderWidth: "2",
								backgroundColor: 'transparent', 
								tension: 0.5,
								pointBackgroundColor: 'rgba(254, 176, 25, 1)'
							}
						]
					},
					options: {
						responsive: true,
						maintainAspectRatio: false,
						plugins:{
							legend: hiddenChartLegendOptions(),
							
						},
						scales: {
							y:{
								max: 100, 
								min: 0, 
								ticks: chartYAxisTickOptions(20)
							},
							x:{ 
								ticks: chartXAxisTickOptions(false)
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
						barChart3();
						lineChart2();	
						lineChart3();
					},
					
					resize:function(){
						barChart1();
						barChart3();	
						lineChart2();
						lineChart3();
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
				var currentYear = new Date().getFullYear();
				var defaultLabels = Array.from({ length: 12 }, function(_, index){
					return new Date(currentYear, index, 1).toLocaleString('en-US', { month: 'long' });
				});
				var defaultSeries = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
				var parseChartData = function(value, fallback){
					if(!value){
						return fallback;
					}

					try {
						var parsedValue = JSON.parse(value);

						return Array.isArray(parsedValue) ? parsedValue : fallback;
					} catch (error) {
						return fallback;
					}
				};
				var isVisibleChart = function(chartElement){
					return !!(chartElement.offsetWidth || chartElement.offsetHeight || chartElement.getClientRects().length);
				};
				var renderProfileProgress = function(chartElement){
					if(chartElement.dataset.profileProgressRendered === 'true'){
						return;
					}

					chartElement.innerHTML = '';
					chartElement.dataset.profileProgressRendered = 'true';

					var progressSeries = parseChartData(chartElement.getAttribute('data-progress-series'), defaultSeries).map(function(value){
						return Number(value) || 0;
					});
					var progressLabels = parseChartData(chartElement.getAttribute('data-progress-labels'), defaultLabels);

					if(progressSeries.length === 0){
						progressSeries = defaultSeries;
					}

					if(progressLabels.length === 0){
						progressLabels = defaultLabels;
					}

					var options = {
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
						categories: progressLabels,
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
								return val + " tasks";
							}
						}
					}
				};

					var handleProfileProgress = new ApexCharts(chartElement, options);
					handleProfileProgress.render();
				};

				var chartTargets = Array.prototype.slice.call(document.querySelectorAll('#chartProfileProgressDesktop, #chartProfileProgress'));
				var visibleChartTarget = chartTargets.find(isVisibleChart) || chartTargets[0];

				if(visibleChartTarget){
					renderProfileProgress(visibleChartTarget);
				}
			
			}
			
			/* Function ============ */
			return {
				
				load:function(){
					chartProfileProgress();
				},
				
				resize:function(){
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
				var pieChartTarget = document.querySelector("#pieChart");

				if (!pieChartTarget) {
					return;
				}

				var options = {
				series: [10, 30],
				chart: {
				type: 'donut',
				height:180,
                width:180,
				},
				plotOptions: {
					pie: {
						donut: {
							size: '54%'
						}
					}
				},
				legend: {
					show:false,
				},
				fill:{
					colors:['#F94687', '#2BC155']
				},
				stroke:{
					width:0,
				},
				colors:['#F94687', '#2BC155'],
				dataLabels: {
				enabled: false
				}
				};

				var chart = new ApexCharts(pieChartTarget, options);
				chart.render();
			}
			
			var radialBar = function(){
				var radialBarTarget = document.querySelector("#radialBar");

				if (!radialBarTarget) {
					return;
				}

				var progressRate = Number(radialBarTarget.getAttribute('data-progress-rate')) || 0;
				var options = {
				series: [progressRate],
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

				var chart = new ApexCharts(radialBarTarget, options);
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

@endsection
