@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <style>
        .business-trip-summary-icon svg {
            width: 40px;
            height: 40px;
        }

        .business-trip-summary-mobile-slider .card:has(.effect) .avatar-primary,
        .business-trip-summary-mobile-slider .card:has(.effect) .avatar-info {
            transition: all 0.5s;
            -ms-transition: all 0.5s;
            -webkit-transition: all 0.5s;
        }

        .business-trip-summary-mobile-slider .card:has(.effect):hover .avatar-primary,
        .business-trip-summary-mobile-slider .card:has(.effect):hover .avatar-info {
            background-color: var(--bs-body-bg);
        }

        @media (max-width: 767.98px) {
            .business-trip-summary-mobile-slider {
                display: flex;
                flex-wrap: nowrap;
                gap: 12px;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                -ms-overflow-style: none;
                scrollbar-width: none;
            }

            .business-trip-summary-mobile-slider::-webkit-scrollbar {
                display: none;
                width: 0;
                height: 0;
            }

            .business-trip-summary-mobile-slide {
                flex: 0 0 100%;
                width: 100%;
                max-width: 100%;
                scroll-snap-align: start;
            }
        }
    </style>
@endsection

@section('navbarTitle', 'Attendances')

@section('content')
@include('layouts.breadcrumb', [
    'title' => 'Attendances',
    'current' => 'Business Trip',
    'homeRoute' => 'dashboard',
])

@include('staff_attendance.layouts.profile-index')

<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Business Trip</h5>
        <div class="d-flex align-items-center">
            <a href="{{ route('attendance.business-trips.create') }}" class="btn btn-success light btn-sm ms-2">+ Business Trip</a>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="col-12">
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    </div>
@endif

@php
    $businessTripSummaryCards = [
        [
            'label' => 'Total Trips',
            'value' => ($businessTripSummary['total_trips'] ?? 0).' Trips',
            'avatar_class' => 'avatar-secondary',
            'progress_class' => 'bg-secondary',
            'effect_class' => 'bg-secondary',
            'progress_width' => '100%',
            'icon' => 'suitcase',
        ],
        [
            'label' => 'Total Days Away',
            'value' => ($businessTripSummary['total_days_away'] ?? 0).' Days',
            'avatar_class' => 'avatar-success',
            'progress_class' => 'bg-success',
            'effect_class' => 'bg-success',
            'progress_width' => '95%',
            'icon' => 'calendar-days',
        ],
        [
            'label' => 'Pending Approvals',
            'value' => ($businessTripSummary['pending_approvals'] ?? 0).' Request',
            'avatar_class' => 'avatar-warning',
            'progress_class' => 'bg-warning',
            'effect_class' => 'bg-warning',
            'progress_width' => '35%',
            'icon' => 'approval',
        ],
        [
            'label' => 'Upcoming Scheduled',
            'value' => ($businessTripSummary['upcoming_scheduled'] ?? 0).' Trip',
            'avatar_class' => 'avatar-info',
            'progress_class' => 'bg-info',
            'effect_class' => 'bg-info',
            'progress_width' => '50%',
            'icon' => 'upcoming',
        ],
        [
            'label' => 'Active Cash Advance',
            'value' => $businessTripSummary['active_cash_advance'] ?? 'Rp 0',
            'avatar_class' => 'avatar-primary',
            'progress_class' => 'bg-primary',
            'effect_class' => 'bg-primary',
            'progress_width' => '75%',
            'icon' => 'wallet',
        ],
        [
            'label' => 'Pending Reimbursement',
            'value' => $businessTripSummary['pending_reimbursement'] ?? 'Rp 0',
            'avatar_class' => 'avatar-warning',
            'progress_class' => 'bg-warning',
            'effect_class' => 'bg-warning',
            'progress_width' => '45%',
            'icon' => 'receipt',
        ],
        [
            'label' => 'Overdue Reports',
            'value' => ($businessTripSummary['overdue_reports'] ?? 0).' Task',
            'avatar_class' => 'avatar-danger',
            'progress_class' => 'bg-danger',
            'effect_class' => 'bg-danger',
            'progress_width' => '20%',
            'icon' => 'overdue',
        ],
        [
            'label' => 'Successfully Settled',
            'value' => ($businessTripSummary['successfully_settled'] ?? 0).' Trips',
            'avatar_class' => 'avatar-success',
            'progress_class' => 'bg-success',
            'effect_class' => 'bg-success',
            'progress_width' => '100%',
            'icon' => 'settled',
        ],
    ];
@endphp

<div class="row business-trip-summary-mobile-slider">
    @foreach ($businessTripSummaryCards as $summaryCard)
        <div class="col-md-3 col-sm-6 business-trip-summary-mobile-slide">
            <div class="card overflow-hidden avtivity-card">
                <div class="card-body">
                    <div class="d-flex gap-md-4 gap-3 align-items-center">
                        <span class="avatar avatar-lg {{ $summaryCard['avatar_class'] }} rounded-circle border-0 business-trip-summary-icon">
                            @switch($summaryCard['icon'])
                                @case('suitcase')
                                    <svg viewBox="0 0 40 40" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M14 13V10.5C14 8.6 15.6 7 17.5 7H22.5C24.4 7 26 8.6 26 10.5V13" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                        <path d="M8 14.5H32C34 14.5 35.5 16 35.5 18V29.5C35.5 31.5 34 33 32 33H8C6 33 4.5 31.5 4.5 29.5V18C4.5 16 6 14.5 8 14.5Z" stroke="currentColor" stroke-width="3"/>
                                        <path d="M14 20V27M26 20V27" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                    </svg>
                                    @break

                                @case('calendar-days')
                                    <svg viewBox="0 0 40 40" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11 8V13M29 8V13" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                        <path d="M7 12H33C35 12 36.5 13.5 36.5 15.5V32C36.5 34 35 35.5 33 35.5H7C5 35.5 3.5 34 3.5 32V15.5C3.5 13.5 5 12 7 12Z" stroke="currentColor" stroke-width="3"/>
                                        <path d="M4 19H36M12 25H13M20 25H21M28 25H29M12 31H13M20 31H21" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                    </svg>
                                    @break

                                @case('approval')
                                    <svg viewBox="0 0 40 40" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 5.5H25L32 12.5V34.5H12C9.8 34.5 8.5 33.2 8.5 31V9C8.5 6.8 9.8 5.5 12 5.5Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/>
                                        <path d="M24.5 6V13H31.5M14.5 23L18 26.5L25.5 18.5M15 31H26" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    @break

                                @case('upcoming')
                                    <svg viewBox="0 0 40 40" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11 8V13M29 8V13" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                        <path d="M7 12H33C35 12 36.5 13.5 36.5 15.5V32C36.5 34 35 35.5 33 35.5H7C5 35.5 3.5 34 3.5 32V15.5C3.5 13.5 5 12 7 12Z" stroke="currentColor" stroke-width="3"/>
                                        <path d="M4 19H36M15 27H27M22.5 22.5L27 27L22.5 31.5" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    @break

                                @case('wallet')
                                    <svg viewBox="0 0 40 40" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M8 12.5H31C33.5 12.5 35.5 14.5 35.5 17V30C35.5 32.5 33.5 34.5 31 34.5H8C5.5 34.5 3.5 32.5 3.5 30V11.5C3.5 8.8 5.7 7.1 8.2 7.8L27 12.5" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/>
                                        <path d="M29 22.5H35.5V28.5H29C27.3 28.5 26 27.2 26 25.5C26 23.8 27.3 22.5 29 22.5Z" stroke="currentColor" stroke-width="3"/>
                                        <path d="M29.5 25.5H30" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                    </svg>
                                    @break

                                @case('receipt')
                                    <svg viewBox="0 0 40 40" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9 5.5L12 8L15 5.5L18 8L21 5.5L24 8L27 5.5L31 8.5V34.5L27 32L24 34.5L21 32L18 34.5L15 32L12 34.5L9 32V5.5Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/>
                                        <path d="M15 16H25M15 22H25M15 28H20" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                    </svg>
                                    @break

                                @case('overdue')
                                    <svg viewBox="0 0 40 40" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 5.5H25L32 12.5V34.5H12C9.8 34.5 8.5 33.2 8.5 31V9C8.5 6.8 9.8 5.5 12 5.5Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/>
                                        <path d="M24.5 6V13H31.5M20 17V25M20 30H20.2" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    @break

                                @case('settled')
                                    <svg viewBox="0 0 40 40" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M20 35.5C28.6 35.5 35.5 28.6 35.5 20C35.5 11.4 28.6 4.5 20 4.5C11.4 4.5 4.5 11.4 4.5 20C4.5 28.6 11.4 35.5 20 35.5Z" stroke="currentColor" stroke-width="3"/>
                                        <path d="M13 20.5L18 25.5L27.5 15.5" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    @break
                            @endswitch
                        </span>
                        <div>
                            <p class="fs-14 mb-2">{{ $summaryCard['label'] }}</p>
                            <span class="title text-black fs-28 fw-semibold">{{ $summaryCard['value'] }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                            <div class="progress-bar rounded {{ $summaryCard['progress_class'] }}" style="width: {{ $summaryCard['progress_width'] }}; height:5px;" aria-label="{{ $summaryCard['label'] }} progress" role="progressbar">
                                <span class="sr-only">{{ $summaryCard['progress_width'] }} Complete</span>
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
            <h5 class="mb-0">Business Trip List</h5>
            <div class="d-flex align-items-center">
                <a class="btn rounded btn-primary mt-xxl-0 mt-xl-3 mt-lg-0 mt-3" data-bs-toggle="modal" data-bs-target="#filterBt">
                    <svg class="me-2" width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3.31615 6H14.4744C14.4744 6.53043 14.6882 7.03914 15.0686 7.41421C15.4491 7.78929 15.9651 8 16.5032 8H18.532C19.07 8 19.5861 7.78929 19.9665 7.41421C20.347 7.03914 20.5607 6.53043 20.5607 6H21.5751C21.8442 6 22.1022 5.89464 22.2924 5.70711C22.4827 5.51957 22.5895 5.26522 22.5895 5C22.5895 4.73478 22.4827 4.48043 22.2924 4.29289C22.1022 4.10536 21.8442 4 21.5751 4H20.5607C20.5607 3.46957 20.347 2.96086 19.9665 2.58579C19.5861 2.21071 19.07 2 18.532 2H16.5032C15.9651 2 15.4491 2.21071 15.0686 2.58579C14.6882 2.96086 14.4744 3.46957 14.4744 4H3.31615C3.04711 4 2.7891 4.10536 2.59887 4.29289C2.40863 4.48043 2.30176 4.73478 2.30176 5C2.30176 5.26522 2.40863 5.51957 2.59887 5.70711C2.7891 5.89464 3.04711 6 3.31615 6ZM16.5032 4H18.532V5V6H16.5032V4ZM21.5751 11H12.4456C12.4456 10.4696 12.2319 9.96086 11.8514 9.58579C11.471 9.21071 10.9549 9 10.4169 9H8.38809C7.85002 9 7.334 9.21071 6.95353 9.58579C6.57306 9.96086 6.35931 10.4696 6.35931 11H3.31615C3.04711 11 2.7891 11.1054 2.59887 11.2929C2.40863 11.4804 2.30176 11.7348 2.30176 12C2.30176 12.2652 2.40863 12.5196 2.59887 12.7071C2.7891 12.8946 3.04711 13 3.31615 13H6.35931C6.35931 13.5304 6.57306 14.0391 6.95353 14.4142C7.334 14.7893 7.85002 15 8.38809 15H10.4169C10.9549 15 11.471 14.7893 11.8514 14.4142C12.2319 14.0391 12.4456 13.5304 12.4456 13H21.5751C21.8442 13 22.1022 12.8946 22.2924 12.7071C22.4827 12.5196 22.5895 12.2652 22.5895 12C22.5895 11.7348 22.4827 11.4804 22.2924 11.2929C22.1022 11.1054 21.8442 11 21.5751 11ZM8.38809 13V11H10.4169V12V13H8.38809ZM21.5751 18H18.532C18.532 17.4696 18.3182 16.9609 17.9378 16.5858C17.5573 16.2107 17.0413 16 16.5032 16H14.4744C13.9364 16 13.4203 16.2107 13.0399 16.5858C12.6594 16.9609 12.4456 17.4696 12.4456 18H3.31615C3.04711 18 2.7891 18.1054 2.59887 18.2929C2.40863 18.4804 2.30176 18.7348 2.30176 19C2.30176 19.2652 2.40863 19.5196 2.59887 19.7071C2.7891 19.8946 3.04711 20 3.31615 20H12.4456C12.4456 20.5304 12.6594 21.0391 13.0399 21.4142C13.4203 21.7893 13.9364 22 14.4744 22H16.5032C17.0413 22 17.5573 21.7893 17.9378 21.4142C18.3182 21.0391 18.532 20.5304 18.532 20H21.5751C21.8442 20 22.1022 19.8946 22.2924 19.7071C22.4827 19.5196 22.5895 19.2652 22.5895 19C22.5895 18.7348 22.4827 18.4804 22.2924 18.2929C22.1022 18.1054 21.8442 18 21.5751 18ZM14.4744 20V18H16.5032V19V20H14.4744Z" fill="#fff"></path>
                    </svg>
                    Filter
                </a>
            </div>
        </div>
    </div>

    @forelse (($businessTripCards ?? collect()) as $businessTripCard)
        <div class="col-xxl-3 col-xl-4 col-sm-6">
            <a href="{{ $businessTripCard['detail_url'] ?? '#' }}" class="text-decoration-none text-reset d-block">
                <div class="card">
                    <div class="card-body">
                        <div class="clearfix d-flex">
                            <div class="avatar avatar-sm rounded me-3 p-2">
                                <img src="{{ asset('assets/images/logo/figma.avif') }}" alt="Business Trip">
                            </div>
                            <div class="clearfix">
                                <h6 class="mb-0 fw-semibold">{{ $businessTripCard['request_number'] ?? '-' }}</h6>
                                <span class="small">{{ $businessTripCard['location'] ?? '-' }}</span>
                            </div>
                        </div>
                        <p class="my-3">{{ $businessTripCard['purpose'] ?? '-' }}</p>
                        <div class="row py-1">
                            <div class="col-12">
                                <span>Date :</span>
                            </div>
                            <div class="col-12">
                                <span>{{ $businessTripCard['date_label'] ?? '-' }}</span> <br>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span>Complete</span>
                            <span>{{ $businessTripCard['progress_percentage'] ?? 0 }}%</span>
                        </div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div
                                class="progress-bar bg-primary"
                                style="width: {{ $businessTripCard['progress_percentage'] ?? 0 }}%;"
                                aria-label="Business trip progress"
                                role="progressbar"
                                aria-valuenow="{{ $businessTripCard['progress_percentage'] ?? 0 }}"
                                aria-valuemin="0"
                                aria-valuemax="100"
                            ></div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between flex-wrap">
                        <p class="mb-0 fw-medium">Due <span class="text-purple">: {{ $businessTripCard['due_label'] ?? '-' }}</span></p>
                        <span class="badge badge-sm {{ $businessTripCard['status_badge_class'] ?? 'badge-primary light' }}">{{ $businessTripCard['status_label'] ?? 'Pending' }}</span>
                    </div>
                </div>
            </a>
        </div>
    @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <span class="text-gray">Belum ada business trip request.</span>
                </div>
            </div>
        </div>
    @endforelse

</div>

<!-- Modal Box Start -->
<div class="modal fade" id="filterBt" tabindex="-1" aria-labelledby="filterLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Filter Details</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="GET" action="{{ route('attendance.business-trips') }}">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="mb-3">
                                <label class="form-label">Filter by Status</label>
                                <select class="form-control selectpicker" name="status">
                                    <option value="all" @selected(($businessTripFilters['status'] ?? 'all') === 'all')>Select All</option>
                                    <option value="approved" @selected(($businessTripFilters['status'] ?? 'all') === 'approved')>Approved</option>
                                    <option value="pending" @selected(($businessTripFilters['status'] ?? 'all') === 'pending')>Pending Review</option>
                                    <option value="rejected" @selected(($businessTripFilters['status'] ?? 'all') === 'rejected')>Rejected</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Filter by Business Trip Type</label>
                                <select class="form-control selectpicker" name="type">
                                    <option value="all" @selected(($businessTripFilters['type'] ?? 'all') === 'all')>Select All</option>
                                    <option value="local" @selected(($businessTripFilters['type'] ?? 'all') === 'local')>Local (Dalam Kota)</option>
                                    <option value="intercity" @selected(($businessTripFilters['type'] ?? 'all') === 'intercity')>Intercity (Luar Kota)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Filter by Timeframe</label>
                                <select class="form-control selectpicker" name="timeframe">
                                    <option value="all" @selected(($businessTripFilters['timeframe'] ?? 'year_to_date') === 'all')>Select All</option>
                                    <option value="this_month" @selected(($businessTripFilters['timeframe'] ?? 'year_to_date') === 'this_month')>This Month</option>
                                    <option value="last_month" @selected(($businessTripFilters['timeframe'] ?? 'year_to_date') === 'last_month')>Last Month</option>
                                    <option value="year_to_date" @selected(($businessTripFilters['timeframe'] ?? 'year_to_date') === 'year_to_date')>Year-to-Date</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('attendance.business-trips') }}" class="btn btn-danger light">Reset</a>
                    <button type="submit" class="btn btn-primary">Save changes</button>
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
        });
    </script>
@endsection
