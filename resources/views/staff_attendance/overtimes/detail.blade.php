@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <style>
        .overtime-task-list {
            scrollbar-gutter: stable;
        }

        .overtime-task-item {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 0.75rem;
            align-items: start;
            padding: 0.6rem 0;
        }

        .overtime-task-content {
            display: grid;
            grid-template-columns: 10px minmax(0, 1fr);
            gap: 0.75rem;
            min-width: 0;
        }

        .overtime-task-actions {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            min-width: 4.35rem;
            justify-content: flex-end;
        }

        .overtime-task-action {
            width: 2rem;
            height: 2rem;
            min-width: 2rem;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .overtime-task-action i {
            margin: 0;
        }

        .overtime-task-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.35rem;
        }

        .overtime-task-toggle,
        .overtime-task-checkbox-label {
            cursor: pointer;
            user-select: none;
        }

        .overtime-task-checkbox {
            pointer-events: none;
        }

        .overtime-clock-in-blocked {
            cursor: not-allowed;
            opacity: 0.65;
        }

        .overtime-clock-out-blocked {
            cursor: not-allowed;
            opacity: 0.65;
        }
    </style>
@endsection

@section('navbarTitle', 'Attendances')

@section('content')
@include('layouts.breadcrumb', [
    'title' => 'Attendances',
    'current' => 'Overtime',
    'homeRoute' => 'dashboard',
])

@include('staff_attendance.layouts.profile-index')

<!-- Start - My Projects -->
<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Overtime Details</h5>
        <div class="d-flex align-items-center">

        </div>
    </div>
</div>
<!-- End - My Projects -->

<div class="row">
    <div class="col-md-5">
        <div class="row">
            <div class="col-xxl-12 col-xl-12 col-md-12">
                <div class="card">
                    <div class="card-header border-0 pb-0">
                        <div>
                            <h4 class="card-title">Your Extra Mile</h4>
                            <p class="fs-13 mb-0">Here is a breakdown of your overtime shift, the tasks you crushed, and your reward details.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span class="fw-semibold">Employee Profile</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Record ID</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $overtimeReference ?? '#OVT' }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Full Name</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $overtimeDetail['staff_name'] ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Supervisor</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $overtimeDetail['supervisor_name'] ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span class="fw-semibold">Overtime Log ({{ $overtimeDetail['log_status'] ?? '-' }})</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Date</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray">{{ $overtimeDetail['overtime_date'] ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Time</span>
                            </div>
                            <div class="col-md-6 col-12">
                                @if (($overtimeDetail['has_actual_time'] ?? false) && ($overtimeDetail['time_changed'] ?? false))
                                    <span class="text-gray text-decoration-line-through">{{ $overtimeDetail['planned_time_range'] ?? '-' }}</span>
                                    <span class="text-gray">, {{ $overtimeDetail['actual_time_range'] ?? '-' }}</span>
                                @else
                                    <span class="text-gray">{{ ($overtimeDetail['has_actual_time'] ?? false) ? ($overtimeDetail['actual_time_range'] ?? '-') : ($overtimeDetail['planned_time_range'] ?? '-') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Total Duration</span>
                            </div>
                            <div class="col-md-6 col-12">
                                @if (($overtimeDetail['has_actual_time'] ?? false) && ($overtimeDetail['duration_changed'] ?? false))
                                    <span class="text-gray text-decoration-line-through">{{ $overtimeDetail['planned_duration'] ?? '-' }}</span>
                                    <span class="text-gray">, {{ $overtimeDetail['actual_duration'] ?? '-' }}</span>
                                @else
                                    <span class="text-gray">{{ ($overtimeDetail['has_actual_time'] ?? false) ? ($overtimeDetail['actual_duration'] ?? '-') : ($overtimeDetail['planned_duration'] ?? '-') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Brief and Instructions</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray">{{ $overtimeDetail['instruction'] ?? '-' }}</span>
                            </div>
                        </div>
                        <hr>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span class="fw-semibold">Compensation & Payroll Details</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Compensation Type</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">Overtime Pay</span> <br>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Calculated Hours</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $overtimeDetail['calculated_hours'] ?? '-' }}</span> <br>
                                <span class="text-gray">Actual hours from clock-in and clock-out</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Estimated Calculated Earnings</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $overtimeDetail['estimated_earnings'] ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Payout Period</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $overtimeDetail['payout_period'] ?? '-' }}</span>
                            </div>
                        </div>
                        <hr>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span class="fw-semibold">Approval Trail</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Verified by System</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray">{{ $overtimeDetail['verified_note'] ?? '-' }}</span> <br>
                                @if (($overtimeDetail['verified_datetime'] ?? '-') === '-')
                                    <span class="badge badge-sm badge-warning light">Pending</span>
                                @else
                                    <span class="text-gray">{{ $overtimeDetail['verified_datetime'] ?? '-' }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Approved by Supervisor</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray">{{ $overtimeDetail['supervisor_approver'] ?? '-' }}</span> <br>
                                @if (($overtimeDetail['supervisor_datetime'] ?? '-') === '-')
                                    <span class="badge badge-sm badge-warning light">Pending</span>
                                @else
                                    <span class="text-gray">{{ $overtimeDetail['supervisor_datetime'] ?? '-' }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Approved by Director</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray">{{ $overtimeDetail['director_approver'] ?? '-' }}</span> <br>
                                @if (($overtimeDetail['director_datetime'] ?? '-') === '-')
                                    <span class="badge badge-sm badge-warning light">Pending</span>
                                @else
                                    <span class="text-gray">{{ $overtimeDetail['director_datetime'] ?? '-' }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="row sticky-top z-0">

            <div class="col-md-6">
                @php
                    $canClockInOvertime = (bool) ($overtimeDetail['clock_in_allowed'] ?? false);
                    $clockInUnavailableTitle = trim((string) ($overtimeDetail['clock_in_unavailable_title'] ?? ''));
                    $clockInUnavailableMessage = trim((string) ($overtimeDetail['clock_in_unavailable_message'] ?? ''));
                    $canClockOutOvertime = (bool) ($overtimeDetail['clock_out_allowed'] ?? false);
                    $clockOutUnavailableTitle = trim((string) ($overtimeDetail['clock_out_unavailable_title'] ?? ''));
                    $clockOutUnavailableMessage = trim((string) ($overtimeDetail['clock_out_unavailable_message'] ?? ''));
                @endphp
                <div class="card">
                    <div class="card-header border-0 pb-3">
                        <div>
                            <h4 class="card-title">Overtime Confirmation</h4>
                            <p class="fs-13 mb-0">Please ensure your task details are updated. Remote and flexible work locations are supported.</p>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="d-flex gap-3 align-items-center avatar-info p-4">
                            <svg width="51" height="51" viewBox="0 0 51 51" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="51" height="51" rx="25.5" fill="#1EA7C5"></rect>
                                <g clip-path="url()">
                                <path d="M23.8586 19.226L18.8712 24.5542C18.5076 25.0845 18.6439 25.8068 19.1717 26.1679L24.1945 29.6098L24.1945 32.9558C24.1945 33.5921 24.6995 34.125 25.3359 34.1376C25.9874 34.1477 26.5177 33.6249 26.5177 32.976L26.5177 29.0012C26.5177 28.6174 26.3283 28.2588 26.0126 28.0442L22.7904 25.8346L25.5025 22.9583L26.8914 26.1225C27.0758 26.5442 27.4949 26.8169 27.9546 26.8169L32.1844 26.8169C32.8207 26.8169 33.3536 26.3119 33.3662 25.6755C33.3763 25.024 32.8536 24.4937 32.2046 24.4937L28.7172 24.4937C28.2576 23.4482 27.7677 22.4129 27.3409 21.3522C27.1237 20.8169 27.0025 20.5846 26.6036 20.2159C26.5227 20.1401 25.9596 19.625 25.4571 19.1654C24.995 18.7462 24.2828 18.7739 23.8586 19.226Z" fill="white"></path>
                                <path d="M28.6162 19.8068C30.0861 19.8068 31.2778 18.6151 31.2778 17.1452C31.2778 15.6752 30.0861 14.4836 28.6162 14.4836C27.1462 14.4836 25.9545 15.6752 25.9545 17.1452C25.9545 18.6151 27.1462 19.8068 28.6162 19.8068Z" fill="white"></path>
                                <path d="M17.899 37.5164C20.6046 37.5164 22.798 35.323 22.798 32.6174C22.798 29.9117 20.6046 27.7184 17.899 27.7184C15.1934 27.7184 13 29.9117 13 32.6174C13 35.323 15.1934 37.5164 17.899 37.5164Z" fill="white"></path>
                                <path d="M32.101 37.5164C34.8066 37.5164 37 35.323 37 32.6174C37 29.9118 34.8066 27.7184 32.101 27.7184C29.3954 27.7184 27.202 29.9118 27.202 32.6174C27.202 35.323 29.3954 37.5164 32.101 37.5164Z" fill="white"></path>
                                </g>
                                <defs>
                                <clipPath id="clip8">
                                <rect width="24" height="24" fill="white" transform="translate(13 14)"></rect>
                                </clipPath>
                                </defs>
                            </svg>
                            <div>
                                <h6 class="fs-16 text-black mb-0">Ready for the Extra Mile?</h6>
                                <span class="fs-12">Don't forget to clock in to keep your overtime hours tracked and recorded accurately for compensation.</span>
                            </div>
                        </div>
                        <div class="d-flex gap-3 justify-content-between flex-wrap p-4 pb-2">
                            <div class="text-center">
                                <p class="fs-14 mb-2">Duration</p>
                                <span class="fs-20 text-black">{{ $overtimeDetail['overtime_card_duration'] ?? '--:--' }}</span>
                            </div>
                            <div class="text-center">
                                <p class="fs-14 mb-2">Time</p>
                                <span class="fs-20 text-black" data-overtime-current-time>{{ $overtimeDetail['overtime_card_current_time'] ?? '--:--:--' }}</span>
                            </div>
                            <div class="text-center">
                                <p class="fs-14 mb-2">Start</p>
                                <span class="fs-20 text-black">{{ $overtimeDetail['overtime_card_start'] ?? '--:--' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 m-3 mb-2">
                        <a class="btn light btn-info btn-lg flex-fill {{ $canClockInOvertime ? '' : 'overtime-clock-in-blocked' }}"
                            @if ($canClockInOvertime)
                                data-bs-toggle="modal" data-bs-target="#clockIn"
                            @else
                                role="button"
                                aria-disabled="true"
                                data-overtime-clock-in-blocked
                                data-overtime-clock-in-blocked-title="{{ $clockInUnavailableTitle }}"
                                data-overtime-clock-in-blocked-message="{{ $clockInUnavailableMessage }}"
                            @endif>Overtime Clock In</a>
                    </div>
                    <div class="mb-3"></div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header border-0 pb-3">
                        <div>
                            <h4 class="card-title">End of Overtime</h4>
                            <p class="fs-13 mb-0">Ensure your overtime tasks and deliverables have been documented before ending your session.</p>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="d-flex gap-3 align-items-center avatar-warning p-4">
                            <svg width="51" height="51" viewBox="0 0 51 51" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="51" height="51" rx="25.5" fill="#FFBC11"></rect>
                                <g clip-path="url()">
                                    <path d="M23.8586 19.226L18.8712 24.5542C18.5076 25.0845 18.6439 25.8068 19.1717 26.1679L24.1945 29.6098L24.1945 32.9558C24.1945 33.5921 24.6995 34.125 25.3359 34.1376C25.9874 34.1477 26.5177 33.6249 26.5177 32.976L26.5177 29.0012C26.5177 28.6174 26.3283 28.2588 26.0126 28.0442L22.7904 25.8346L25.5025 22.9583L26.8914 26.1225C27.0758 26.5442 27.4949 26.8169 27.9546 26.8169L32.1844 26.8169C32.8207 26.8169 33.3536 26.3119 33.3662 25.6755C33.3763 25.024 32.8536 24.4937 32.2046 24.4937L28.7172 24.4937C28.2576 23.4482 27.7677 22.4129 27.3409 21.3522C27.1237 20.8169 27.0025 20.5846 26.6036 20.2159C26.5227 20.1401 25.9596 19.625 25.4571 19.1654C24.995 18.7462 24.2828 18.7739 23.8586 19.226Z" fill="white"></path>
                                    <path d="M28.6162 19.8068C30.0861 19.8068 31.2778 18.6151 31.2778 17.1452C31.2778 15.6752 30.0861 14.4836 28.6162 14.4836C27.1462 14.4836 25.9545 15.6752 25.9545 17.1452C25.9545 18.6151 27.1462 19.8068 28.6162 19.8068Z" fill="white"></path>
                                    <path d="M17.899 37.5164C20.6046 37.5164 22.798 35.323 22.798 32.6174C22.798 29.9117 20.6046 27.7184 17.899 27.7184C15.1934 27.7184 13 29.9117 13 32.6174C13 35.323 15.1934 37.5164 17.899 37.5164Z" fill="white"></path>
                                    <path d="M32.101 37.5164C34.8066 37.5164 37 35.323 37 32.6174C37 29.9118 34.8066 27.7184 32.101 27.7184C29.3954 27.7184 27.202 29.9118 27.202 32.6174C27.202 35.323 29.3954 37.5164 32.101 37.5164Z" fill="white"></path>
                                </g>
                                <defs>
                                <clipPath id="clip8">
                                    <rect width="24" height="24" fill="white" transform="translate(13 14)"></rect>
                                </clipPath>
                                </defs>
                            </svg>
                            <div>
                                <h6 class="fs-16 text-black mb-0">Time to Recharge!</h6>
                                <span class="fs-12">Thanks for the hard work! End your session to save your overtime hours.</span>
                            </div>
                        </div>
                        <div class="d-flex gap-3 justify-content-between flex-wrap p-4 pb-2">
                            <div class="text-center">
                                <p class="fs-14 mb-2">Duration</p>
                                <span class="fs-20 text-black">{{ $overtimeDetail['overtime_card_duration'] ?? '--:--' }}</span>
                            </div>
                            <div class="text-center">
                                <p class="fs-14 mb-2">Time</p>
                                <span class="fs-20 text-black" data-overtime-current-time>{{ $overtimeDetail['overtime_card_current_time'] ?? '--:--:--' }}</span>
                            </div>
                            <div class="text-center">
                                <p class="fs-14 mb-2">Ended</p>
                                <span class="fs-20 text-black">{{ $overtimeDetail['overtime_card_ended'] ?? '--:--' }}</span>
                            </div>
                        </div>
                    </div>
                    <a class="btn light btn-warning m-3 mb-2 btn-lg {{ $canClockOutOvertime ? '' : 'overtime-clock-out-blocked' }}"
                        @if ($canClockOutOvertime)
                            data-bs-toggle="modal" data-bs-target="#clockOut"
                        @else
                            role="button"
                            aria-disabled="true"
                            data-overtime-clock-out-blocked
                            data-overtime-clock-out-blocked-title="{{ $clockOutUnavailableTitle }}"
                            data-overtime-clock-out-blocked-message="{{ $clockOutUnavailableMessage }}"
                        @endif>Overtime Clock Out</a>
                    <div class="mb-3"></div>
                </div>
            </div>

            <!-- Start - Activity -->
            <div class="col-xxl-6 col-xl-12 col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Activity {{ $overtimeReference ?? '#OVT' }}</h4>
                    </div>
                    <div class="card-body dz-scroll height380">
                        <div class="widget-timeline1">
                            <ul class="timeline">
                                @forelse (($overtimeLifecycleTracker ?? collect()) as $lifecyclePhase)
                                    <li>
                                        <span class="timeline-status">{{ $lifecyclePhase['date_label'] ?? '-' }}</span>
                                        <div class="timeline-badge {{ $lifecyclePhase['marker_class'] ?? 'border-secondary' }}"></div>
                                        <div class="timeline-panel">
                                            <span class="text-black fs-14 fw-semibold">{{ $lifecyclePhase['title'] ?? '-' }}</span> <br>
                                        </div>
                                    </li>
                                    @foreach (($lifecyclePhase['items'] ?? collect()) as $lifecycleItem)
                                        <li>
                                            <span class="timeline-status">{{ $lifecycleItem['date_label'] ?? '-' }}</span>
                                            <div class="timeline-badge {{ $lifecycleItem['marker_class'] ?? 'border-secondary' }}"></div>
                                            <div class="timeline-panel">
                                                <span>
                                                    {{ $lifecycleItem['step_order'] ?? '-' }}. {{ $lifecycleItem['title'] ?? '-' }} <br>
                                                    Datetime : {{ $lifecycleItem['datetime_label'] ?? '-' }} <br>
                                                    Actor : {{ $lifecycleItem['actor_label'] ?? '-' }} <br>
                                                    Status : <span class="badge badge-xs {{ $lifecycleItem['badge_class'] ?? 'badge-secondary light' }} fw-semibold">{{ $lifecycleItem['status_label'] ?? '-' }}</span>
                                                </span>
                                            </div>
                                        </li>
                                    @endforeach
                                @empty
                                    <li>
                                        <span class="timeline-status">-</span>
                                        <div class="timeline-badge border-secondary"></div>
                                        <div class="timeline-panel"><span>No lifecycle logs recorded.</span></div>
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <!-- End - Activity -->

            <!-- Start - To Do -->
            <div class="col-xxl-6 col-xl-12 col-md-6">
                <div class="card overflow-hidden">
                    <div class="card-header">
                        <h4 class="card-title">My Task Items</h4>
                        <div class="clearfix">
                            @if ((bool) ($overtimeDetail['can_create_task'] ?? false))
                            <a class="text-success" data-bs-toggle="modal" data-bs-target="#task">+ Add Task</a>
                            @else
                            <button type="button" class="btn btn-link text-muted p-0" disabled title="Lakukan Overtime Clock In terlebih dahulu">+ Add Task</button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush dz-scroll height400 overtime-task-list">
                            <div class="list-group-item p-3">
                                <span class="text-warning d-block mb-2">
                                    <svg class="me-1" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.61051 15.3276H14.3978C15.5843 15.3276 16.329 14.0451 15.7395 13.0146L10.35 3.59085C9.75676 2.5536 8.26126 2.55285 7.66726 3.5901L2.26876 13.0139C1.67926 14.0444 2.42326 15.3276 3.61051 15.3276Z" stroke="var(--bs-warning)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                        <path d="M9.00189 10.0611V7.7361" stroke="var(--bs-warning)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                        <path d="M8.99625 12.375H9.00375" stroke="var(--bs-warning)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                    Latest to do's
                                </span>
                                @forelse (($overtimeTaskItems['pending'] ?? collect()) as $taskItem)
                                    <div class="overtime-task-item">
                                        <div class="overtime-task-content">
                                            <div class="draggable-handle">
                                                <svg width="9" height="16" viewBox="0 0 9 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <rect width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect y="3" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect y="6" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect y="9" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect y="12" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect y="15" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect x="4" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect x="4" y="3" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect x="4" y="6" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect x="4" y="9" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect x="4" y="12" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect x="4" y="15" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect x="8" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect x="8" y="3" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect x="8" y="6" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect x="8" y="9" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect x="8" y="12" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect x="8" y="15" width="1" height="1" fill="var(--bs-body-color)"/>
                                                </svg>
                                            </div>
                                            <div class="clearfix min-w-0">
                                                <div class="form-check overtime-task-toggle" role="checkbox" aria-checked="false" tabindex="0" data-task-toggle-url="{{ $taskItem['update_url'] ?? '#' }}" data-task-id="{{ $taskItem['id'] ?? '' }}" data-task-title="{{ $taskItem['title'] ?? '-' }}" data-task-status="{{ $taskItem['status_value'] ?? 'pending' }}">
                                                    <input class="form-check-input overtime-task-checkbox" type="checkbox" id="projectTask{{ $taskItem['id'] ?? '' }}">
                                                    <label class="form-check-label overtime-task-checkbox-label text-black d-block text-truncate" for="projectTask{{ $taskItem['id'] ?? '' }}">{{ $taskItem['title'] ?? '-' }}</label>
                                                </div>
                                                <span class="overtime-task-meta">
                                                    <span>{{ $taskItem['date_label'] ?? '-' }}</span>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="overtime-task-actions">
                                            <a href="#" class="btn btn-square btn-danger light btn-sm overtime-task-action" data-bs-toggle="modal" data-bs-target="#delete" data-task-id="{{ $taskItem['id'] ?? '' }}" data-task-title="{{ $taskItem['title'] ?? '-' }}" data-task-delete-url="{{ $taskItem['delete_url'] ?? '#' }}" aria-label="Delete task">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </a>
                                            <a href="#" class="btn btn-square btn-primary light btn-sm overtime-task-action" data-bs-toggle="modal" data-bs-target="#update" data-task-id="{{ $taskItem['id'] ?? '' }}" data-task-update-url="{{ $taskItem['update_url'] ?? '#' }}" aria-label="Edit task">
                                                <i class="fa fa-pen"></i>
                                            </a>
                                        </div>
                                    </div>
                                @empty
                                    <p class="mb-0 text-muted fs-13">No pending task items.</p>
                                @endforelse
                            </div>
                            <div class="list-group-item p-3">
                                <span class="text-success d-block mb-2">
                                    <svg class="me-1" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15 4.5L6.75 12.75L3 9" stroke="var(--bs-success)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Latest finished to do's
                                </span>
                                @forelse (($overtimeTaskItems['finished'] ?? collect()) as $taskItem)
                                    <div class="overtime-task-item">
                                        <div class="overtime-task-content">
                                            <div class="draggable-handle">
                                                <svg width="9" height="16" viewBox="0 0 9 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <rect width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect y="3" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect y="6" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect y="9" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect y="12" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect y="15" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect x="4" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect x="4" y="3" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect x="4" y="6" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect x="4" y="9" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect x="4" y="12" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect x="4" y="15" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect x="8" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect x="8" y="3" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect x="8" y="6" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect x="8" y="9" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect x="8" y="12" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    <rect x="8" y="15" width="1" height="1" fill="var(--bs-body-color)"/>
                                                </svg>
                                            </div>
                                            <div class="clearfix min-w-0">
                                                <div class="form-check overtime-task-toggle" role="checkbox" aria-checked="true" tabindex="0" data-task-toggle-url="{{ $taskItem['update_url'] ?? '#' }}" data-task-id="{{ $taskItem['id'] ?? '' }}" data-task-title="{{ $taskItem['title'] ?? '-' }}" data-task-status="{{ $taskItem['status_value'] ?? 'completed' }}">
                                                    <input class="form-check-input overtime-task-checkbox" type="checkbox" id="projectTask{{ $taskItem['id'] ?? '' }}" checked>
                                                    <label class="form-check-label overtime-task-checkbox-label text-black d-block text-truncate" for="projectTask{{ $taskItem['id'] ?? '' }}">{{ $taskItem['title'] ?? '-' }}</label>
                                                </div>
                                                <span class="overtime-task-meta">{{ $taskItem['date_label'] ?? '-' }}</span>
                                            </div>
                                        </div>
                                        <div class="overtime-task-actions">
                                            <a href="#" class="btn btn-square btn-danger light btn-sm overtime-task-action" data-bs-toggle="modal" data-bs-target="#delete" data-task-id="{{ $taskItem['id'] ?? '' }}" data-task-title="{{ $taskItem['title'] ?? '-' }}" data-task-delete-url="{{ $taskItem['delete_url'] ?? '#' }}" aria-label="Delete task">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </a>
                                            <a href="#" class="btn btn-square btn-primary light btn-sm overtime-task-action" data-bs-toggle="modal" data-bs-target="#update" data-task-id="{{ $taskItem['id'] ?? '' }}" data-task-update-url="{{ $taskItem['update_url'] ?? '#' }}" aria-label="Edit task">
                                                <i class="fa fa-pen"></i>
                                            </a>
                                        </div>
                                    </div>
                                @empty
                                    <p class="mb-0 text-muted fs-13">No finished task items.</p>
                                @endforelse
                            </div>
                            @if (false)
                            <div class="list-group-item draggable p-3">
                                <span class="text-warning d-block mb-2">
                                    <svg class="me-1" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.61051 15.3276H14.3978C15.5843 15.3276 16.329 14.0451 15.7395 13.0146L10.35 3.59085C9.75676 2.5536 8.26126 2.55285 7.66726 3.5901L2.26876 13.0139C1.67926 14.0444 2.42326 15.3276 3.61051 15.3276Z" stroke="var(--bs-warning)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                        <path d="M9.00189 10.0611V7.7361" stroke="var(--bs-warning)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                        <path d="M8.99625 12.375H9.00375" stroke="var(--bs-warning)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                    Latest to do's
                                </span>
                                <div class="d-flex justify-content-between flex-wrap">
                                    <div class="d-flex gap-3">
                                        <div class="draggable-handle">
                                            <svg width="9" height="16" viewBox="0 0 9 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <rect width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="3" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="6" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="9" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="12" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="15" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="3" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="6" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="9" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="12" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="15" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="3" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="6" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="9" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="12" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="15" width="1" height="1" fill="var(--bs-body-color)"/>
                                            </svg>
                                        </div>
                                        <div class="clearfix">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="customCheckBox1">
                                                <label class="form-check-label text-black" for="customCheckBox1">Desain Interior Ruang Tamu</label>
                                            </div>
                                            <span>20 May 2026</span>
                                        </div>
                                    </div>
                                    <div class="clearfix">
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#delete">
                                            <div class="btn btn-square btn-danger light btn-sm">
                                                <i class="fa-regular fa-trash-can "></i>
                                            </div>
                                        </a>
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#update">
                                            <div class="btn btn-square btn-primary light btn-sm ms-1">
                                                <i class="fa fa-pen "></i>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="list-group-item draggable p-3">
                                <span class="text-success d-block mb-2">
                                    <svg class="me-1" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15 4.5L6.75 12.75L3 9" stroke="var(--bs-success)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Latest finished to do's
                                </span>
                                <div class="d-flex justify-content-between flex-wrap">
                                    <div class="d-flex gap-3">
                                        <div class="draggable-handle">
                                            <svg width="9" height="16" viewBox="0 0 9 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <rect width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="3" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="6" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="9" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="12" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="15" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="3" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="6" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="9" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="12" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="15" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="3" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="6" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="9" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="12" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="15" width="1" height="1" fill="var(--bs-body-color)"/>
                                            </svg>
                                        </div>
                                        <div class="clearfix">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="customCheckBox2">
                                                <label class="form-check-label text-black" for="customCheckBox2">Compete this projects Monday</label>
                                            </div>
                                            <span>2023-12-26 07:15:00</span>
                                        </div>
                                    </div>
                                    <div class="clearfix">
                                        <div class="btn btn-square btn-danger light btn-sm">
                                            <i class="fa-regular fa-trash-can "></i>
                                        </div>
                                        <div class="btn btn-square btn-primary light btn-sm ms-1">
                                            <i class="fa fa-pen "></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="list-group-item draggable p-3">
                                <div class="d-flex justify-content-between flex-wrap">
                                    <div class="d-flex gap-3">
                                        <div class="draggable-handle">
                                            <svg width="9" height="16" viewBox="0 0 9 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <rect width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="3" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="6" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="9" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="12" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="15" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="3" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="6" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="9" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="12" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="15" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="3" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="6" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="9" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="12" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="15" width="1" height="1" fill="var(--bs-body-color)"/>
                                            </svg>
                                        </div>
                                        <div class="clearfix">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="customCheckBox3">
                                                <label class="form-check-label text-black" for="customCheckBox3">Compete this projects Monday</label>
                                            </div>
                                            <span>2023-12-26 07:15:00</span>
                                        </div>
                                    </div>
                                    <div class="clearfix">
                                        <div class="btn btn-square btn-danger light btn-sm">
                                            <i class="fa-regular fa-trash-can "></i>
                                        </div>
                                        <div class="btn btn-square btn-primary light btn-sm ms-1">
                                            <i class="fa fa-pen "></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="list-group-item draggable p-3">
                                <div class="d-flex justify-content-between flex-wrap">
                                    <div class="d-flex gap-3">
                                        <div class="draggable-handle">
                                            <svg width="9" height="16" viewBox="0 0 9 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <rect width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="3" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="6" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="9" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="12" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="15" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="3" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="6" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="9" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="12" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="15" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="3" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="6" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="9" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="12" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="15" width="1" height="1" fill="var(--bs-body-color)"/>
                                            </svg>
                                        </div>
                                        <div class="clearfix">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="customCheckBox4">
                                                <label class="form-check-label text-black" for="customCheckBox4">Compete this projects Monday</label>
                                            </div>
                                            <span>2023-12-26 07:15:00</span>
                                        </div>
                                    </div>
                                    <div class="clearfix">
                                        <div class="btn btn-square btn-danger light btn-sm">
                                            <i class="fa-regular fa-trash-can "></i>
                                        </div>
                                        <div class="btn btn-square btn-primary light btn-sm ms-1">
                                            <i class="fa fa-pen "></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="list-group-item draggable p-3">
                                <div class="d-flex justify-content-between flex-wrap">
                                    <div class="d-flex gap-3">
                                        <div class="draggable-handle">
                                            <svg width="9" height="16" viewBox="0 0 9 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <rect width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="3" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="6" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="9" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="12" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="15" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="3" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="6" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="9" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="12" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="15" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="3" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="6" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="9" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="12" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="15" width="1" height="1" fill="var(--bs-body-color)"/>
                                            </svg>
                                        </div>
                                        <div class="clearfix">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="customCheckBox5">
                                                <label class="form-check-label text-black" for="customCheckBox5">Compete this projects Monday</label>
                                            </div>
                                            <span>2023-12-26 07:15:00</span>
                                        </div>
                                    </div>
                                    <div class="clearfix">
                                        <div class="btn btn-square btn-danger light btn-sm">
                                            <i class="fa-regular fa-trash-can "></i>
                                        </div>
                                        <div class="btn btn-square btn-primary light btn-sm ms-1">
                                            <i class="fa fa-pen "></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="list-group-item draggable p-3">
                                <div class="d-flex justify-content-between flex-wrap">
                                    <div class="d-flex gap-3">
                                        <div class="draggable-handle">
                                            <svg width="9" height="16" viewBox="0 0 9 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <rect width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="3" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="6" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="9" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="12" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect y="15" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="3" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="6" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="9" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="12" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="4" y="15" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="3" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="6" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="9" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="12" width="1" height="1" fill="var(--bs-body-color)"/>
                                                <rect x="8" y="15" width="1" height="1" fill="var(--bs-body-color)"/>
                                            </svg>
                                        </div>
                                        <div class="clearfix">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="customCheckBox6">
                                                <label class="form-check-label text-black" for="customCheckBox6">Compete this projects Monday</label>
                                            </div>
                                            <span>2023-12-26 07:15:00</span>
                                        </div>
                                    </div>
                                    <div class="clearfix">
                                        <div class="btn btn-square btn-danger light btn-sm">
                                            <i class="fa-regular fa-trash-can "></i>
                                        </div>
                                        <div class="btn btn-square btn-primary light btn-sm ms-1">
                                            <i class="fa fa-pen "></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        <!-- End - To Do -->
        </div>
    </div>
</div>

<!-- Start - Modal Complete Task -->
<div class="modal fade" id="task">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            @php
                $availableTaskProjects = $taskProjectOptions ?? collect();
                $hasTaskProjectOptions = $availableTaskProjects->isNotEmpty();
            @endphp
            <form class="comment-form" id="createTaskForm" action="{{ $taskStoreUrl ?? '' }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create a Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Task Name <span class="required text-danger">*</span></label>
                                <input type="text" class="form-control" name="title" placeholder="Contoh: Rekap absensi bulanan" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Task Description <span class="required text-danger">*</span></label>
                                <textarea class="form-control" name="description" rows="3" placeholder="Tambahkan detail atau konteks pekerjaan" required></textarea>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Date <span class="required text-danger">*</span></label>
                                <input type="date" class="form-control" name="start_date" value="{{ $taskDefaultDate ?? now('Asia/Jakarta')->toDateString() }}" required>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Due Date <span class="required text-danger">*</span></label>
                                <input type="date" class="form-control" name="due_date" value="{{ $taskDefaultDate ?? now('Asia/Jakarta')->toDateString() }}" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Volume Workload <span class="required text-danger">*</span></label>
                                <select class="form-control default-select" name="priority" required>
                                    <option value="low">Light</option>
                                    <option value="medium" selected>Moderate</option>
                                    <option value="high">Heavy</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Attachment</label>
                                <input type="text" class="form-control" name="attachment_path" placeholder="Contoh: Link Google Drive, Figma, atau Docs">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Blockers</label>
                                <input type="text" class="form-control" name="blockers" placeholder="Contoh: Menunggu persetujuan (approval) dokumen dari manajer">
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Task Category <span class="required text-danger">*</span></label>
                                <div class="form-group mb-0">
                                    <div class="form-check d-inline-block me-3">
                                        <input class="form-check-input" type="radio" name="task_category" id="taskCategoryDaily" value="daily" checked>
                                        <label class="form-check-label" for="taskCategoryDaily">Daily Task</label>
                                    </div>
                                    <div class="form-check d-inline-block mx-2">
                                        <input class="form-check-input" type="radio" name="task_category" id="taskCategoryProject" value="project" @if (! $hasTaskProjectOptions) disabled @endif>
                                        <label class="form-check-label" for="taskCategoryProject">Project Task</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Project Name <span class="required text-danger">*</span></label>
                                <select class="form-control default-select" name="project_id" id="taskProjectSelect" disabled>
                                    @if ($hasTaskProjectOptions)
                                        <option value="">Pilih Nama Project</option>
                                        @foreach ($availableTaskProjects as $projectOption)
                                            <option value="{{ $projectOption['id'] }}">{{ $projectOption['name'] }}@if (($projectOption['code'] ?? '') !== '') - {{ $projectOption['code'] }}@endif</option>
                                        @endforeach
                                    @else
                                        <option value="">Belum ada pilihan project tersedia</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Task Status <span class="required text-danger">*</span></label>
                                <select class="form-control default-select" name="status" required>
                                    <option value="pending">To Do</option>
                                    <option value="in_progress">On Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" id="createTaskSubmit" @if (empty($taskStoreUrl)) disabled @endif>Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End - Modal Send Message -->

<!-- Start - Modal Complete Task -->
<div class="modal fade" id="update">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form class="comment-form" id="updateTaskForm" action="" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Update a Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Task Name <span class="required text-danger">*</span></label>
                                <input type="text" class="form-control" name="title" placeholder="Contoh: Rekap absensi bulanan" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Task Description <span class="required text-danger">*</span></label>
                                <textarea class="form-control" name="description" rows="3" placeholder="Tambahkan detail atau konteks pekerjaan" required></textarea>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Date <span class="required text-danger">*</span></label>
                                <input type="date" class="form-control" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Due Date <span class="required text-danger">*</span></label>
                                <input type="date" class="form-control" name="due_date" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Volume Workload <span class="required text-danger">*</span></label>
                                <select class="form-control default-select" name="priority" id="updateTaskPrioritySelect" required>
                                    <option value="low">Light</option>
                                    <option value="medium">Moderate</option>
                                    <option value="high">Heavy</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Attachment</label>
                                <input type="text" class="form-control" name="attachment_path" placeholder="Contoh: Link Google Drive, Figma, atau Docs">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Blockers</label>
                                <input type="text" class="form-control" name="blockers" placeholder="Contoh: Menunggu persetujuan (approval) dokumen dari manajer">
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Task Category <span class="required text-danger">*</span></label>
                                <div class="form-group mb-0">
                                    <div class="form-check d-inline-block me-3">
                                        <input class="form-check-input" type="radio" name="task_category" id="updateTaskCategoryDaily" value="daily">
                                        <label class="form-check-label" for="updateTaskCategoryDaily">Daily Task</label>
                                    </div>
                                    <div class="form-check d-inline-block mx-2">
                                        <input class="form-check-input" type="radio" name="task_category" id="updateTaskCategoryProject" value="project" @if (! $hasTaskProjectOptions) disabled @endif>
                                        <label class="form-check-label" for="updateTaskCategoryProject">Project Task</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Project Name <span class="required text-danger">*</span></label>
                                <select class="form-control default-select" name="project_id" id="updateTaskProjectSelect" disabled>
                                    @if ($hasTaskProjectOptions)
                                        <option value="">Pilih Nama Project</option>
                                        @foreach ($availableTaskProjects as $projectOption)
                                            <option value="{{ $projectOption['id'] }}">{{ $projectOption['name'] }}@if (($projectOption['code'] ?? '') !== '') - {{ $projectOption['code'] }}@endif</option>
                                        @endforeach
                                    @else
                                        <option value="">Belum ada pilihan project tersedia</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Task Status <span class="required text-danger">*</span></label>
                                <select class="form-control default-select" name="status" id="updateTaskStatusSelect" required>
                                    <option value="pending">To Do</option>
                                    <option value="in_progress">On Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning" id="updateTaskSubmit">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End - Modal Send Message -->

<!-- Modal Box Start -->
<div class="modal fade" id="delete" tabindex="-1" aria-labelledby="deleteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteTaskForm" action="" method="POST">
                @csrf
                <input type="hidden" id="deleteTaskIdInput" name="task_id">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="deleteLabel">Delete Task</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xl-12">
                            <h5 class="text-muted mb-3 fw-bold text-center">Delete Task?</h5>
                            <p class="form-label text-muted mb-3">
                                Are you sure you want to delete <span class="fw-semibold" id="deleteTaskTitle">this task</span>? <br> This action cannot be undone and will permanently remove the record from your current timesheet.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger light" id="deleteTaskSubmit">Yes, Delete Task</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal-Box-End -->
<!-- Modal Box Start -->
<div class="modal fade" id="clockIn" tabindex="-1" aria-labelledby="clockInLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="clockInLabel">Overtime Confirmation</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-xl-12">
                            <p class="form-label mb-3 text-center"><span data-overtime-modal-date>{{ $overtimeDetail['modal_date_label'] ?? '-' }}</span> -
                                <span class="text-success fw-semibold" data-overtime-current-time>{{ $overtimeDetail['overtime_card_current_time'] ?? '--:--:--' }}</span>
                            </p>
                            <p class="form-label text-muted mb-3">
                                Grab your coffee and let's get things done. Start your session when you're ready to crush this extra hustle!
                            </p>
                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Scheduled Start Time</label>
                                        <p class="fs-13 mb-0">{{ $overtimeDetail['scheduled_start_label'] ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Scheduled End Time</label>
                                        <p class="fs-13 mb-0">{{ $overtimeDetail['scheduled_end_label'] ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Target Duration</label>
                                        <p class="fs-13 mb-0">{{ $overtimeDetail['target_duration_label'] ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <button type="button" class="btn light btn-info mb-2 btn-lg w-100" id="overtimeClockInSubmit" @if (! $canClockInOvertime) disabled @endif>Overtime Clock In</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal-Box-End -->

<!-- Modal Box Start -->
<div class="modal fade" id="clockOut" tabindex="-1" aria-labelledby="clockOutLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="clockOutLabel">Overtime Completed</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-xl-12">
                            <p class="form-label mb-3 text-center"><span data-overtime-modal-date>{{ $overtimeDetail['modal_date_label'] ?? '-' }}</span> -
                                <span class="text-success fw-semibold" data-overtime-current-time>{{ $overtimeDetail['overtime_card_current_time'] ?? '--:--:--' }}</span>
                            </p>
                            <p class="form-label text-muted mb-3">
                                Great hustle today! Make sure your tasks are updated, then clock out and get some well-deserved rest.
                            </p>
                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Scheduled Start Time</label>
                                        <p class="fs-13 mb-0">{{ $overtimeDetail['scheduled_start_label'] ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Scheduled End Time</label>
                                        <p class="fs-13 mb-0">{{ $overtimeDetail['scheduled_end_label'] ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Actual Start Time</label>
                                        <p class="fs-13 mb-0">{{ $overtimeDetail['actual_start_label'] ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Actual End Time</label>
                                        <p class="fs-13 mb-0">{{ $overtimeDetail['actual_end_label'] ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Target Duration</label>
                                        <p class="fs-13 mb-0">{{ $overtimeDetail['target_duration_label'] ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Actual Duration</label>
                                        <p class="fs-13 mb-0">{{ $overtimeDetail['actual_duration_label'] ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <button type="button" class="btn light btn-warning mb-2 btn-lg w-100" id="overtimeClockOutSubmit" @if (! $canClockOutOvertime) disabled @endif>End Overtime Session</button>
            </div>
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
    <script src="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
    <script>
        @php
            $overtimeSessionPayload = [
                'update_url' => $overtimeDetail['update_url'] ?? null,
                'employee_id' => $overtimeDetail['employee_id'] ?? null,
                'pic_user_id' => $overtimeDetail['pic_user_id'] ?? null,
                'overtime_date' => $overtimeDetail['overtime_date_input'] ?? null,
                'planned_start_time' => $overtimeDetail['planned_start_time_value'] ?? null,
                'planned_end_time' => $overtimeDetail['planned_end_time_value'] ?? null,
                'actual_start_time' => $overtimeDetail['actual_start_time_value'] ?? null,
                'actual_end_time' => $overtimeDetail['actual_end_time_value'] ?? null,
                'clock_in_allowed' => (bool) ($overtimeDetail['clock_in_allowed'] ?? false),
                'clock_in_unavailable_title' => $overtimeDetail['clock_in_unavailable_title'] ?? null,
                'clock_in_unavailable_message' => $overtimeDetail['clock_in_unavailable_message'] ?? null,
                'clock_out_allowed' => (bool) ($overtimeDetail['clock_out_allowed'] ?? false),
                'clock_out_unavailable_title' => $overtimeDetail['clock_out_unavailable_title'] ?? null,
                'clock_out_unavailable_message' => $overtimeDetail['clock_out_unavailable_message'] ?? null,
                'instruction' => $overtimeDetail['instruction'] ?? null,
            ];
            $taskItemPayload = collect($overtimeTaskItems['pending'] ?? collect())
                ->merge($overtimeTaskItems['finished'] ?? collect())
                ->keyBy('id');
        @endphp

        $(function () {
            $('.attendance-tab-btn').on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                var targetUrl = $(this).data('href');
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
            });

            updateOvertimeCurrentTime();
            window.setInterval(updateOvertimeCurrentTime, 1000);
            $('#overtimeClockInSubmit').on('click', function () {
                submitOvertimeSession('clock_in', $(this));
            });
            $('#overtimeClockOutSubmit').on('click', function () {
                submitOvertimeSession('clock_out', $(this));
            });
            $('[data-overtime-clock-in-blocked]').on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                showSwalAlert(
                    'warning',
                    $(this).data('overtime-clock-in-blocked-title') || 'Absen Lembur Belum Tersedia',
                    $(this).data('overtime-clock-in-blocked-message') || 'Clock in lembur hanya tersedia sesuai jadwal yang ditentukan.'
                );
            });
            $('[data-overtime-clock-out-blocked]').on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                showSwalAlert(
                    'warning',
                    $(this).data('overtime-clock-out-blocked-title') || 'Clock Out Belum Tersedia',
                    $(this).data('overtime-clock-out-blocked-message') || 'Silakan lengkapi task lembur sebelum mengakhiri sesi.'
                );
            });
            $('#createTaskForm input[name="task_category"]').on('change', updateCreateTaskProjectState);
            $('#createTaskForm').on('submit', submitCreateTaskForm);
            $('#updateTaskForm input[name="task_category"]').on('change', updateUpdateTaskProjectState);
            $('#updateTaskForm').on('submit', submitUpdateTaskForm);
            $('[data-task-update-url]').on('click', openUpdateTaskModal);
            $('[data-task-delete-url]').on('click', openDeleteTaskModal);
            $('.overtime-task-list').on('click', '[data-task-toggle-url]', toggleTaskStatusFromCheckbox);
            $('.overtime-task-list').on('keydown', '[data-task-toggle-url]', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    $(this).trigger('click');
                }
            });
            $('#deleteTaskForm').on('submit', submitDeleteTaskForm);
            $('#createTaskForm input[name="start_date"]').on('change', function () {
                var startDate = $(this).val();
                var dueDateInput = $('#createTaskForm input[name="due_date"]');

                dueDateInput.attr('min', startDate);
                if (dueDateInput.val() && startDate && dueDateInput.val() < startDate) {
                    dueDateInput.val(startDate);
                }
            }).trigger('change');
            $('#updateTaskForm input[name="start_date"]').on('change', function () {
                var startDate = $(this).val();
                var dueDateInput = $('#updateTaskForm input[name="due_date"]');

                dueDateInput.attr('min', startDate);
                if (dueDateInput.val() && startDate && dueDateInput.val() < startDate) {
                    dueDateInput.val(startDate);
                }
            });
            $('#task').on('hidden.bs.modal', function () {
                var form = $('#createTaskForm')[0];
                if (form) {
                    form.reset();
                }
                updateCreateTaskProjectState();
            });
            $('#update').on('hidden.bs.modal', function () {
                var form = $('#updateTaskForm')[0];
                if (form) {
                    form.reset();
                }
                $('#updateTaskForm').attr('action', '');
                setSelectValue($('#updateTaskPrioritySelect'), 'medium');
                setSelectValue($('#updateTaskStatusSelect'), 'pending');
                updateUpdateTaskProjectState();
            });
            $('#delete').on('hidden.bs.modal', function () {
                $('#deleteTaskForm').attr('action', '');
                $('#deleteTaskIdInput').val('');
                $('#deleteTaskTitle').text('this task');
                $('#deleteTaskSubmit').prop('disabled', false);
            });
            updateCreateTaskProjectState();

            function updateOvertimeCurrentTime() {
                var currentTime = new Intl.DateTimeFormat('en-GB', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false,
                    timeZone: 'Asia/Jakarta'
                }).format(new Date());

                $('[data-overtime-current-time]').text(currentTime);
            }

            function currentJakartaTime() {
                return new Intl.DateTimeFormat('en-GB', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false,
                    timeZone: 'Asia/Jakarta'
                }).format(new Date());
            }

            function updateCreateTaskProjectState() {
                var hasProjectOptions = @js(($taskProjectOptions ?? collect())->isNotEmpty());
                var isProjectTask = $('#taskCategoryProject').is(':checked');
                var projectSelect = $('#taskProjectSelect');

                projectSelect.prop('disabled', !hasProjectOptions || !isProjectTask);
                projectSelect.prop('required', hasProjectOptions && isProjectTask);

                if (!isProjectTask) {
                    setSelectValue(projectSelect, '');
                }

                refreshSelectPicker(projectSelect);
            }

            function updateUpdateTaskProjectState() {
                var hasProjectOptions = @js(($taskProjectOptions ?? collect())->isNotEmpty());
                var isProjectTask = $('#updateTaskCategoryProject').is(':checked');
                var projectSelect = $('#updateTaskProjectSelect');

                projectSelect.prop('disabled', !hasProjectOptions || !isProjectTask);
                projectSelect.prop('required', hasProjectOptions && isProjectTask);

                if (!isProjectTask) {
                    setSelectValue(projectSelect, '');
                }

                refreshSelectPicker(projectSelect);
            }

            function setSelectValue(selectElement, value) {
                if ($.fn.selectpicker && selectElement.data('selectpicker')) {
                    selectElement.selectpicker('val', value);
                    return;
                }

                selectElement.val(value);
            }

            function refreshSelectPicker(selectElement) {
                if ($.fn.selectpicker && selectElement.data('selectpicker')) {
                    selectElement.selectpicker('refresh');
                }
            }

            function showSwalAlert(iconType, titleText, messageText) {
                if (typeof Swal !== 'undefined' && Swal && typeof Swal.fire === 'function') {
                    Swal.fire({
                        icon: iconType,
                        title: titleText,
                        text: messageText,
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                if (window.console && typeof window.console.error === 'function') {
                    window.console.error(messageText);
                }
            }

            function openUpdateTaskModal(event) {
                var taskItemsById = @js($taskItemPayload);
                var trigger = $(event.currentTarget);
                var taskId = trigger.data('task-id');
                var taskItem = taskItemsById[taskId];
                var form = $('#updateTaskForm');

                if (!taskItem) {
                    return;
                }

                form.attr('action', trigger.data('task-update-url') || taskItem.update_url || '');
                form.find('[name="title"]').val(taskItem.title || '');
                form.find('[name="description"]').val(taskItem.description || '');
                form.find('[name="start_date"]').val(taskItem.start_date || '');
                form.find('[name="due_date"]').val(taskItem.due_date || '');
                form.find('[name="attachment_path"]').val(taskItem.attachment_path || '');
                form.find('[name="blockers"]').val(taskItem.blockers || '');

                setSelectValue($('#updateTaskPrioritySelect'), taskItem.priority || 'medium');
                setSelectValue($('#updateTaskStatusSelect'), taskItem.status_value || 'pending');

                if ((taskItem.task_category || 'daily') === 'project') {
                    $('#updateTaskCategoryProject').prop('checked', true);
                    $('#updateTaskCategoryDaily').prop('checked', false);
                    setSelectValue($('#updateTaskProjectSelect'), taskItem.project_id || '');
                } else {
                    $('#updateTaskCategoryDaily').prop('checked', true);
                    $('#updateTaskCategoryProject').prop('checked', false);
                    setSelectValue($('#updateTaskProjectSelect'), '');
                }

                $('#updateTaskForm input[name="start_date"]').trigger('change');
                updateUpdateTaskProjectState();
                $('#updateTaskSubmit').prop('disabled', false);
            }

            function openDeleteTaskModal(event) {
                var trigger = $(event.currentTarget);
                var taskTitle = trigger.data('task-title') || 'this task';

                $('#deleteTaskForm').attr('action', trigger.data('task-delete-url') || '');
                $('#deleteTaskIdInput').val(trigger.data('task-id') || '');
                $('#deleteTaskTitle').text('"' + taskTitle + '"');
                $('#deleteTaskSubmit').prop('disabled', false);
            }

            function submitCreateTaskForm(event) {
                event.preventDefault();

                var form = $(this);
                var submitButton = $('#createTaskSubmit');
                var actionUrl = form.attr('action');
                var csrfToken = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';

                if (!actionUrl || submitButton.prop('disabled')) {
                    return;
                }

                submitButton.prop('disabled', true);

                $.ajax({
                    url: actionUrl,
                    method: 'POST',
                    data: form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function () {
                        window.location.reload();
                    },
                    error: function (xhr) {
                        var responseMessage = resolveAjaxErrorMessage(xhr, 'Task gagal disimpan.');

                        submitButton.prop('disabled', false);
                        showSwalAlert('error', 'Gagal', responseMessage);
                    }
                });
            }

            function submitUpdateTaskForm(event) {
                event.preventDefault();

                var form = $(this);
                var submitButton = $('#updateTaskSubmit');
                var actionUrl = form.attr('action');
                var csrfToken = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';

                if (!actionUrl || submitButton.prop('disabled')) {
                    return;
                }

                submitButton.prop('disabled', true);

                $.ajax({
                    url: actionUrl,
                    method: 'PUT',
                    data: form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function () {
                        window.location.reload();
                    },
                    error: function (xhr) {
                        var responseMessage = resolveAjaxErrorMessage(xhr, 'Task gagal diperbarui.');

                        submitButton.prop('disabled', false);
                        showSwalAlert('error', 'Gagal', responseMessage);
                    }
                });
            }

            function toggleTaskStatusFromCheckbox(event) {
                event.preventDefault();
                event.stopPropagation();

                var toggleControl = $(event.currentTarget);
                var checkbox = toggleControl.find('.overtime-task-checkbox').first();
                var actionUrl = toggleControl.data('task-toggle-url');
                var taskTitle = toggleControl.data('task-title') || 'task ini';
                var willComplete = !checkbox.prop('checked');
                var nextStatus = willComplete ? 'completed' : 'pending';
                var nextCheckedState = willComplete;
                var previousCheckedState = checkbox.prop('checked');
                var csrfToken = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';

                if (checkbox.prop('disabled') || toggleControl.attr('aria-disabled') === 'true') {
                    return;
                }

                if (!actionUrl || actionUrl === '#') {
                    checkbox.prop('checked', previousCheckedState);
                    showSwalAlert('error', 'Gagal', 'URL update task tidak tersedia.');
                    return;
                }

                var updateTaskStatus = function () {
                    checkbox.prop('disabled', true);
                    toggleControl.attr('aria-disabled', 'true');
                    checkbox.prop('checked', nextCheckedState);
                    toggleControl.attr('aria-checked', nextCheckedState ? 'true' : 'false');

                    $.ajax({
                        url: actionUrl,
                        method: 'PUT',
                        data: {
                            _token: csrfToken,
                            status: nextStatus
                        },
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function () {
                            if (typeof Swal !== 'undefined' && Swal && typeof Swal.fire === 'function') {
                                Swal.fire({
                                    icon: 'success',
                                    title: willComplete ? 'Task Completed' : 'Task Dibuka Kembali',
                                    text: willComplete ? 'Task lembur berhasil ditandai completed.' : 'Status task berhasil dikembalikan menjadi pending.',
                                    confirmButtonText: 'OK'
                                }).then(function () {
                                    window.location.reload();
                                });
                                return;
                            }

                            window.location.reload();
                        },
                        error: function (xhr) {
                            var responseMessage = resolveAjaxErrorMessage(xhr, 'Task gagal diperbarui.');

                            checkbox.prop('checked', previousCheckedState);
                            checkbox.prop('disabled', false);
                            toggleControl.attr('aria-disabled', 'false');
                            toggleControl.attr('aria-checked', previousCheckedState ? 'true' : 'false');
                            showSwalAlert('error', 'Gagal', responseMessage);
                        }
                    });
                };

                if (typeof Swal !== 'undefined' && Swal && typeof Swal.fire === 'function') {
                    Swal.fire({
                        icon: 'question',
                        title: willComplete ? 'Tandai Task Completed?' : 'Buka Kembali Task?',
                        text: willComplete
                            ? 'Untuk task "' + taskTitle + '" akan ditandai completed.'
                            : 'Untuk task "' + taskTitle + '" akan dikembalikan ke status pending.',
                        showCancelButton: true,
                        confirmButtonText: willComplete ? 'Ya, Completed' : 'Ya, Pending',
                        cancelButtonText: 'Batal'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            updateTaskStatus();
                            return;
                        }

                        checkbox.prop('checked', previousCheckedState);
                        checkbox.prop('disabled', false);
                        toggleControl.attr('aria-disabled', 'false');
                        toggleControl.attr('aria-checked', previousCheckedState ? 'true' : 'false');
                    });
                    return;
                }

                updateTaskStatus();
            }

            function submitDeleteTaskForm(event) {
                event.preventDefault();

                var form = $(this);
                var submitButton = $('#deleteTaskSubmit');
                var actionUrl = form.attr('action');
                var csrfToken = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';

                if (!actionUrl || submitButton.prop('disabled')) {
                    return;
                }

                submitButton.prop('disabled', true);

                $.ajax({
                    url: actionUrl,
                    method: 'DELETE',
                    data: form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function () {
                        window.location.reload();
                    },
                    error: function (xhr) {
                        var responseMessage = resolveAjaxErrorMessage(xhr, 'Task gagal dihapus.');

                        submitButton.prop('disabled', false);
                        showSwalAlert('error', 'Gagal', responseMessage);
                    }
                });
            }

            function resolveAjaxErrorMessage(xhr, fallbackMessage) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errorKeys = Object.keys(xhr.responseJSON.errors);
                    if (errorKeys.length > 0 && xhr.responseJSON.errors[errorKeys[0]].length > 0) {
                        return xhr.responseJSON.errors[errorKeys[0]][0];
                    }
                }

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    return xhr.responseJSON.message;
                }

                return fallbackMessage;
            }

            function submitOvertimeSession(action, button) {
                var overtimePayload = @js($overtimeSessionPayload);
                var csrfToken = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';

                if (!overtimePayload.update_url || button.prop('disabled')) {
                    return;
                }

                if (action === 'clock_in' && !overtimePayload.clock_in_allowed) {
                    showSwalAlert(
                        'warning',
                        overtimePayload.clock_in_unavailable_title || 'Absen Lembur Belum Tersedia',
                        overtimePayload.clock_in_unavailable_message || 'Clock in lembur hanya tersedia sesuai jadwal yang ditentukan.'
                    );
                    return;
                }

                if (action === 'clock_out' && !overtimePayload.clock_out_allowed) {
                    showSwalAlert(
                        'warning',
                        overtimePayload.clock_out_unavailable_title || 'Clock Out Belum Tersedia',
                        overtimePayload.clock_out_unavailable_message || 'Silakan lengkapi task lembur sebelum mengakhiri sesi.'
                    );
                    return;
                }

                var currentTime = currentJakartaTime();
                var actualStartTime = overtimePayload.actual_start_time;
                var actualEndTime = overtimePayload.actual_end_time;

                if (action === 'clock_in') {
                    actualStartTime = currentTime;
                    actualEndTime = actualEndTime || null;
                }

                if (action === 'clock_out') {
                    actualStartTime = actualStartTime || currentTime;
                    actualEndTime = currentTime;
                }

                button.prop('disabled', true);

                $.ajax({
                    url: overtimePayload.update_url,
                    method: 'PUT',
                    data: {
                        _token: csrfToken,
                        employee_id: overtimePayload.employee_id,
                        pic_user_id: overtimePayload.pic_user_id,
                        overtime_date: overtimePayload.overtime_date,
                        planned_start_time: overtimePayload.planned_start_time,
                        planned_end_time: overtimePayload.planned_end_time,
                        instruction: overtimePayload.instruction,
                        actual_start_time: actualStartTime,
                        actual_end_time: actualEndTime
                    },
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function () {
                        window.location.reload();
                    },
                    error: function (xhr) {
                        var responseMessage = xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : 'Data overtime gagal disimpan.';

                        button.prop('disabled', false);
                        showSwalAlert('error', 'Gagal', responseMessage);
                    }
                });
            }
        });
    </script>
@endsection
