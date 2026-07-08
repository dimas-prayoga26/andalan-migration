@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <style>
        .overtime-summary-slider {
            display: flex;
            flex-wrap: nowrap;
            gap: 24px;
            overflow-x: auto;
            overflow-y: hidden;
            scroll-snap-type: x proximity;
            -ms-overflow-style: none;
            scrollbar-width: none;
            margin-right: 0;
            margin-left: 0;
            padding-top: 2px;
            padding-bottom: 12px;
        }

        .overtime-summary-slider::-webkit-scrollbar {
            display: none;
            width: 0;
            height: 0;
        }

        .overtime-summary-slide {
            flex: 0 0 calc((100% - 72px) / 4);
            width: calc((100% - 72px) / 4);
            max-width: calc((100% - 72px) / 4);
            padding-right: 0;
            padding-left: 0;
            scroll-snap-align: start;
        }

        .overtime-summary-icon {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
        }

        .overtime-summary-icon i {
            display: block;
            font-size: 31px;
            line-height: 1;
        }

        .overtime-summary-slide .card-body {
            padding-bottom: 2rem;
        }

        @media (max-width: 767.98px) {
            .overtime-summary-slider {
                gap: 12px;
                scroll-snap-type: x mandatory;
            }

            .overtime-summary-slide {
                flex-basis: 100%;
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
@endsection

@section('navbarTitle', 'Attendances')

@section('content')
@php
    $overtimeSummary = $overtimeSummary ?? [];
    $overtimeStatusFilterValue = $overtimeStatusFilter ?? 'all';
    $overtimeTimeframeFilterValue = $overtimeTimeframeFilter ?? 'year_to_date';
    $activeOvertimeFilterCount = (int) ($overtimeStatusFilterValue !== 'all')
        + (int) ($overtimeTimeframeFilterValue !== 'year_to_date');
    $overtimeSummaryCards = [
        [
            'label' => 'Total Logged Hours ('.($overtimeSummary['current_month_label'] ?? now('Asia/Jakarta')->format('M')).')',
            'value' => $overtimeSummary['total_logged_hours_label'] ?? '0 Hours',
            'avatar_class' => 'avatar-info',
            'icon_color' => '#a02cfa',
            'progress_class' => 'bg-info',
            'effect_class' => 'bg-secondary',
            'progress' => $overtimeSummary['overtime_cap_progress'] ?? 0,
            'icon' => 'fa-solid fa-clock',
        ],
        [
            'label' => 'Overtime Cap (40 Hours)',
            'value' => $overtimeSummary['overtime_cap_label'] ?? '0 H (0%)',
            'avatar_class' => 'avatar-success',
            'icon_color' => '#27bc48',
            'progress_class' => 'bg-success',
            'effect_class' => 'bg-success',
            'progress' => $overtimeSummary['overtime_cap_progress'] ?? 0,
            'icon' => 'fa-solid fa-gauge-high',
        ],
        [
            'label' => 'Average Extra Hours',
            'value' => $overtimeSummary['average_extra_hours_label'] ?? '0 H / Week',
            'avatar_class' => 'avatar-danger',
            'icon_color' => '#ff3282',
            'progress_class' => 'bg-danger',
            'effect_class' => 'bg-danger',
            'progress' => 10,
            'icon' => 'fa-solid fa-chart-line',
        ],
        [
            'label' => 'Tasks Finalized',
            'value' => $overtimeSummary['tasks_finalized_label'] ?? '0 Tasks',
            'avatar_class' => 'avatar-secondary',
            'icon_color' => '#a02cfa',
            'progress_class' => 'bg-secondary',
            'effect_class' => 'bg-secondary',
            'progress' => 10,
            'icon' => 'fa-solid fa-list-check',
        ],
        [
            'label' => 'Pending SPV Approval',
            'value' => $overtimeSummary['pending_spv_approval_hours_label'] ?? '0 Hours',
            'avatar_class' => 'avatar-info',
            'icon_color' => '#a02cfa',
            'progress_class' => 'bg-info',
            'effect_class' => 'bg-secondary',
            'progress' => $overtimeSummary['pending_spv_approval_hours_progress'] ?? 0,
            'icon' => 'fa-solid fa-user-clock',
            'sr_label' => ($overtimeSummary['pending_spv_approval_hours_progress'] ?? 0).'% Pending SPV Approval',
        ],
        [
            'label' => 'Completed & Locked',
            'value' => $overtimeSummary['completed_locked_hours_label'] ?? '0 Hours',
            'avatar_class' => 'avatar-success',
            'icon_color' => '#27bc48',
            'progress_class' => 'bg-success',
            'effect_class' => 'bg-success',
            'progress' => $overtimeSummary['completed_locked_progress'] ?? 0,
            'icon' => 'fa-solid fa-lock',
        ],
        [
            'label' => 'Estimated Extra Earnings',
            'value' => $overtimeSummary['estimated_extra_earnings_label'] ?? 'Rp 0',
            'avatar_class' => 'avatar-danger',
            'icon_color' => '#ff3282',
            'progress_class' => 'bg-danger',
            'effect_class' => 'bg-danger',
            'progress' => 10,
            'icon' => 'fa-solid fa-coins',
        ],
        [
            'label' => 'Disputed Hours',
            'value' => $overtimeSummary['disputed_hours_label'] ?? '0 Hours',
            'avatar_class' => 'avatar-secondary',
            'icon_color' => '#a02cfa',
            'progress_class' => 'bg-secondary',
            'effect_class' => 'bg-secondary',
            'progress' => 10,
            'icon' => 'fa-solid fa-triangle-exclamation',
        ],
    ];
@endphp

@include('layouts.breadcrumb', [
    'title' => 'Attendances',
    'current' => 'Overtime',
    'homeRoute' => 'dashboard',
])

@include('staff_attendance.layouts.profile-index')

<div class="row overtime-summary-slider">
    @foreach ($overtimeSummaryCards as $summaryCard)
        <div class="overtime-summary-slide">
            <div class="card overflow-hidden avtivity-card">
                <div class="card-body">
                    <div class="d-flex gap-md-4 gap-3 align-items-center">
                        <span class="avatar avatar-lg {{ $summaryCard['avatar_class'] }} rounded-circle border-0 overtime-summary-icon">
                            <i class="{{ $summaryCard['icon'] }}" style="color: {{ $summaryCard['icon_color'] }};" aria-hidden="true"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="fs-14 mb-2 text-truncate">{{ $summaryCard['label'] }}</p>
                            <span class="title text-black fs-28 fw-semibold d-block text-truncate">{{ $summaryCard['value'] }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                            <div class="progress-bar rounded {{ $summaryCard['progress_class'] }}" style="width: {{ $summaryCard['progress'] }}%; height:5px;" aria-label="{{ $summaryCard['label'] }} progress" role="progressbar">
                                <span class="sr-only">{{ $summaryCard['sr_label'] ?? $summaryCard['progress'].'% Complete' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="effect {{ $summaryCard['effect_class'] }}"></div>
            </div>
        </div>
    @endforeach
</div>
<div class="row">

    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Overtime List</h5>
            <div class="d-flex align-items-center">
                <button type="button" class="btn rounded btn-primary mt-xxl-0 mt-xl-3 mt-lg-0 mt-3 position-relative" data-bs-toggle="modal" data-bs-target="#filter">
                    <svg class="me-2" width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3.31615 6H14.4744C14.4744 6.53043 14.6882 7.03914 15.0686 7.41421C15.4491 7.78929 15.9651 8 16.5032 8H18.532C19.07 8 19.5861 7.78929 19.9665 7.41421C20.347 7.03914 20.5607 6.53043 20.5607 6H21.5751C21.8442 6 22.1022 5.89464 22.2924 5.70711C22.4827 5.51957 22.5895 5.26522 22.5895 5C22.5895 4.73478 22.4827 4.48043 22.2924 4.29289C22.1022 4.10536 21.8442 4 21.5751 4H20.5607C20.5607 3.46957 20.347 2.96086 19.9665 2.58579C19.5861 2.21071 19.07 2 18.532 2H16.5032C15.9651 2 15.4491 2.21071 15.0686 2.58579C14.6882 2.96086 14.4744 3.46957 14.4744 4H3.31615C3.04711 4 2.7891 4.10536 2.59887 4.29289C2.40863 4.48043 2.30176 4.73478 2.30176 5C2.30176 5.26522 2.40863 5.51957 2.59887 5.70711C2.7891 5.89464 3.04711 6 3.31615 6ZM16.5032 4H18.532V5V6H16.5032V4ZM21.5751 11H12.4456C12.4456 10.4696 12.2319 9.96086 11.8514 9.58579C11.471 9.21071 10.9549 9 10.4169 9H8.38809C7.85002 9 7.334 9.21071 6.95353 9.58579C6.57306 9.96086 6.35931 10.4696 6.35931 11H3.31615C3.04711 11 2.7891 11.1054 2.59887 11.2929C2.40863 11.4804 2.30176 11.7348 2.30176 12C2.30176 12.2652 2.40863 12.5196 2.59887 12.7071C2.7891 12.8946 3.04711 13 3.31615 13H6.35931C6.35931 13.5304 6.57306 14.0391 6.95353 14.4142C7.334 14.7893 7.85002 15 8.38809 15H10.4169C10.9549 15 11.471 14.7893 11.8514 14.4142C12.2319 14.0391 12.4456 13.5304 12.4456 13H21.5751C21.8442 13 22.1022 12.8946 22.2924 12.7071C22.4827 12.5196 22.5895 12.2652 22.5895 12C22.5895 11.7348 22.4827 11.4804 22.2924 11.2929C22.1022 11.1054 21.8442 11 21.5751 11ZM8.38809 13V11H10.4169V12V13H8.38809ZM21.5751 18H18.532C18.532 17.4696 18.3182 16.9609 17.9378 16.5858C17.5573 16.2107 17.0413 16 16.5032 16H14.4744C13.9364 16 13.4203 16.2107 13.0399 16.5858C12.6594 16.9609 12.4456 17.4696 12.4456 18H3.31615C3.04711 18 2.7891 18.1054 2.59887 18.2929C2.40863 18.4804 2.30176 18.7348 2.30176 19C2.30176 19.2652 2.40863 19.5196 2.59887 19.7071C2.7891 19.8946 3.04711 20 3.31615 20H12.4456C12.4456 20.5304 12.6594 21.0391 13.0399 21.4142C13.4203 21.7893 13.9364 22 14.4744 22H16.5032C17.0413 22 17.5573 21.7893 17.9378 21.4142C18.3182 21.0391 18.532 20.5304 18.532 20H21.5751C21.8442 20 22.1022 19.8946 22.2924 19.7071C22.4827 19.5196 22.5895 19.2652 22.5895 19C22.5895 18.7348 22.4827 18.4804 22.2924 18.2929C22.1022 18.1054 21.8442 18 21.5751 18ZM14.4744 20V18H16.5032V19V20H14.4744Z" fill="#fff"></path>
                    </svg>
                    Filter
                    @if ($activeOvertimeFilterCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $activeOvertimeFilterCount }}</span>
                    @endif
                </button>
            </div>
        </div>
    </div>

    <div class="row g-3" id="overtime-list">
        @forelse (($overtimeList ?? collect()) as $overtimeItem)
            <div class="col-xxl-3 col-xl-4 col-sm-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="clearfix d-flex">
                            <div class="avatar avatar-sm rounded me-3 p-2 bg-primary text-white flex-shrink-0">
                                <span class="fw-semibold">O</span>
                            </div>
                            <div class="clearfix min-w-0">
                                <h6 class="mb-0 fw-semibold text-truncate">
                                    <a href="{{ $overtimeItem['detail_url'] ?? route('attendance.overtimes') }}" class="stretched-link">
                                        {{ $overtimeItem['reference'] ?? '#OVT' }}
                                    </a>
                                </h6>
                                <span class="small d-block text-muted">{{ $overtimeItem['overtime_date'] ?? '-' }}, {{ $overtimeItem['time_range'] ?? '-' }} ({{ $overtimeItem['duration'] ?? '-' }})</span>
                            </div>
                        </div>
                        <div class="my-3">
                            <p class="mb-0 text-muted fs-13">{{ $overtimeItem['instruction'] ?? '-' }}</p>
                        </div>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between">
                                <span>{{ $overtimeItem['progress_label'] ?? 'Complete' }}</span>
                                <span>{{ $overtimeItem['progress_percent'] ?? 0 }}%</span>
                            </div>
                            <div class="progress mt-2">
                                <div class="progress-bar bg-purple" style="width:{{ $overtimeItem['progress_percent'] ?? 0 }}%;" role="progressbar" aria-valuenow="{{ $overtimeItem['progress_percent'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between flex-wrap gap-2">
                        <p class="mb-0 fw-medium">Due <span class="text-purple">: {{ $overtimeItem['due_label'] ?? '-' }}</span></p>
                        <span class="badge badge-sm {{ $overtimeItem['footer_status_badge_class'] ?? 'badge-warning light' }}">{{ $overtimeItem['footer_status_label'] ?? 'Pending' }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <h6 class="mb-1">No overtime records found</h6>
                        <p class="mb-0 text-muted">Data overtime belum tersedia untuk filter saat ini.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

</div>

<!-- Modal Box Start -->
<div class="modal fade" id="filter" tabindex="-1" aria-labelledby="filterLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="filterLabel">Filter Details</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="GET" action="{{ route('attendance.overtimes') }}" id="overtimeFilterForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="mb-3">
                                <label class="form-label" for="overtimeStatusFilter">Filter by Status</label>
                                <select class="form-control selectpicker" id="overtimeStatusFilter" name="status">
                                    <option value="all" @selected($overtimeStatusFilterValue === 'all')>Select All</option>
                                    <option value="assigned" @selected($overtimeStatusFilterValue === 'assigned')>Assigned</option>
                                    <option value="in_progress" @selected($overtimeStatusFilterValue === 'in_progress')>In Progress</option>
                                    <option value="completed" @selected($overtimeStatusFilterValue === 'completed')>Completed</option>
                                    <option value="cancelled" @selected($overtimeStatusFilterValue === 'cancelled')>Cancelled</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="overtimeTimeframeFilter">Filter by Timeframe</label>
                                <select class="form-control selectpicker" id="overtimeTimeframeFilter" name="timeframe">
                                    <option value="all" @selected($overtimeTimeframeFilterValue === 'all')>Select All</option>
                                    <option value="this_month" @selected($overtimeTimeframeFilterValue === 'this_month')>This Month</option>
                                    <option value="last_month" @selected($overtimeTimeframeFilterValue === 'last_month')>Last Month</option>
                                    <option value="year_to_date" @selected($overtimeTimeframeFilterValue === 'year_to_date')>Year-to-Date</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('attendance.overtimes') }}" class="btn btn-danger light">Reset</a>
                    <button type="submit" class="btn btn-primary">Apply Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal-Box-End -->

@endsection

@section('script')
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
    @endphp
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
    <script>
        $(function () {
            $('.attendance-tab-btn').on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                var targetUrl = $(this).data('href');
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
            });

            $('#filter').on('shown.bs.modal', function () {
                if ($.fn.selectpicker) {
                    $(this).find('.selectpicker').selectpicker('refresh');
                }
            });
        });
    </script>
@endsection
