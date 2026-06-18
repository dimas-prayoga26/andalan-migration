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
    <link rel="stylesheet" href="https://unpkg.com/antd@6.2.3/dist/antd.css">
    <!-- Start - All Required Plugins -->
    <style>
        .attendance-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            scrollbar-width: thin;
            -webkit-overflow-scrolling: touch;
            white-space: nowrap;
            gap: 0.25rem;
        }

        .attendance-tabs .nav-item {
            flex: 0 0 auto;
        }

        .attendance-tabs .nav-link {
            white-space: nowrap;
        }

        .attendance-tabs .attendance-tab-btn {
            border: 0;
            background: transparent;
        }

        .attendance-tabs .attendance-tab-btn:focus {
            box-shadow: none;
        }

        .attendance-card-icon {
            width: 51px;
            height: 51px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.2rem;
            flex: 0 0 51px;
        }

        .attendance-card-icon--success {
            background: #2BC155;
        }

        .attendance-card-icon--danger {
            background: #F94687;
        }

        .attendance-mobile-slider {
            overflow: visible;
        }

        .attendance-mobile-slide {
            min-width: 0;
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

            .attendance-mobile-slider {
                display: flex;
                flex-wrap: nowrap;
                gap: 0;
                overflow-x: auto;
                overflow-y: hidden;
                scroll-snap-type: x mandatory;
                -webkit-overflow-scrolling: touch;
                margin-right: 0;
                margin-left: 0;
                padding: 0 0 0.25rem;
            }

            .attendance-mobile-slider::-webkit-scrollbar {
                height: 6px;
            }

            .attendance-mobile-slider::-webkit-scrollbar-thumb {
                background: #d7deef;
                border-radius: 999px;
            }

            .attendance-mobile-slide {
                flex: 0 0 100%;
                max-width: 100%;
                scroll-snap-align: start;
            }

            .attendance-mobile-slide .card {
                margin-bottom: 0;
            }
        }

        .attendance-datetime {
            font-size: 1rem;
            font-weight: 600;
            color: #25314c;
            margin-bottom: 0;
            text-align: center;
        }

        .onsite-running-time {
            font-size: 1.05rem;
            letter-spacing: 0.02em;
        }

        .onsite-map-canvas {
            width: 100%;
            height: 250px;
            border: 1px solid #e6eaf2;
            background: #f8fafc;
        }

        .app-fullcalendar .fc-event.fc-weekend-dayoff-card,
        .app-fullcalendar .fc-event.fc-national-holiday-card,
        .app-fullcalendar .fc-event.fc-joint-leave-card,
        .app-fullcalendar .fc-event.fc-calendar-label-card,
        .app-fullcalendar .fc-event.fc-attendance-log-card {
            align-items: center;
            box-sizing: border-box;
            display: flex;
            min-height: 30px;
            width: 100%;
            border-radius: 999px;
            font-size: 0.88rem;
            line-height: 1.1;
            font-weight: 700;
            padding: 0.42rem 0.7rem;
            border: 0 !important;
            cursor: pointer;
        }

        .app-fullcalendar .fc-event.fc-weekend-dayoff-card .fc-event-main,
        .app-fullcalendar .fc-event.fc-national-holiday-card .fc-event-main,
        .app-fullcalendar .fc-event.fc-joint-leave-card .fc-event-main,
        .app-fullcalendar .fc-event.fc-calendar-label-card .fc-event-main,
        .app-fullcalendar .fc-event.fc-attendance-log-card .fc-event-main {
            min-width: 0;
            width: 100%;
        }

        .app-fullcalendar .fc-event.fc-weekend-dayoff-card .fc-event-title,
        .app-fullcalendar .fc-event.fc-national-holiday-card .fc-event-title,
        .app-fullcalendar .fc-event.fc-joint-leave-card .fc-event-title,
        .app-fullcalendar .fc-event.fc-calendar-label-card .fc-event-title,
        .app-fullcalendar .fc-event.fc-attendance-log-card .fc-event-title {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

    </style>

@endsection

@section('navbarTitle', 'Attendances')

@section('content')
<!-- Start - Page Title & Breadcrumb -->
@include('layouts.breadcrumb', [
    'title' => 'Attendances',
    'current' => 'Presensi',
    'homeRoute' => 'dashboard',
])

@include('attendance.layouts.profile-index')

<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Attendance</h5>
    </div>
</div>

@include('attendance.components.card-analytics')

@include('attendance.components.attendance-cards')

<div class="col-xxl-12 col-xl-12">
    <div class="card card-body">
        <div id="calendar" class="app-fullcalendar" data-show-weekend-off="1" data-disable-default-events="1"></div>
    </div>
</div>

<!-- End - Content Body -->

<div class="modal fade" id="dayOff" tabindex="-1" aria-labelledby="dayOffLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="dayOffLabel">Day Off</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-xl-12">
                            <h5 class="text-muted mb-0 fw-bold">It’s a Day Off! Time to Unplug</h5>
                            <p class="form-label text-muted mb-3">
                                Whether it's the weekend, a public holiday, or a joint holiday, today is officially a non-working day. 
                                Take this time to fully rest and recharge. See you on the next working day!
                            </p>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Event Type</span>
                                </div>
                                <div class="col-8">
                                    <span id="dayOffEventTypeText" class="text-danger fw-semibold">Weekend / Public Holiday / Joint Holiday</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Holiday Name</span>
                                </div>
                                <div class="col-8">
                                    <span id="dayOffHolidayNameText" class="text-gray fw-semibold">Weekend</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Date</span>
                                </div>
                                <div class="col-8">
                                    <span id="dayOffDateText" class="text-gray">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Box Start -->
<div class="modal fade" id="onTime" tabindex="-1" aria-labelledby="onTimeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="onTimeLabel">On-Time Clock In</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-xl-12">
                            <h5 class="text-muted mb-0 fw-bold">Spot on! Kudos for the punctuality.</h5>
                            <p class="form-label text-muted mb-3">
                                The early bird gets the worm! Thanks for showing up on time. Grab your coffee, review your task list, and let’s crush those goals today!
                            </p>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Attendance Status</span>
                                </div>
                                <div class="col-8">
                                    <span id="onTimeStatusText" class="text-success fw-semibold">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Location</span>
                                </div>
                                <div class="col-8">
                                    <span id="onTimeLocationNameText" class="text-gray fw-semibold">-</span> <br>
                                    <span id="onTimeLocationAddressText" class="text-gray">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Clock In Time</span>
                                </div>
                                <div class="col-8">
                                    <span id="onTimeClockInText" class="text-success fw-semibold">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Clock Out Time</span>
                                </div>
                                <div class="col-8">
                                    <span id="onTimeClockOutText" class="text-gray fw-semibold">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal-Box-End -->

<!-- Modal Box Start -->
<div class="modal fade" id="late" tabindex="-1" aria-labelledby="lateLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="lateLabel">Late Arrival</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-xl-12">
                            <h5 class="text-muted mb-0 fw-bold">Let's catch up on lost time!</h5>
                            <p class="form-label text-muted mb-3">
                                You're clocking in past your scheduled time today. Please try to stick to your shift schedule moving forward so you don't miss out on important morning updates. Take a deep breath, and let's dive into work!
                            </p>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Attendance Status</span>
                                </div>
                                <div class="col-8">
                                    <span id="lateStatusText" class="text-danger fw-semibold">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Location</span>
                                </div>
                                <div class="col-8">
                                    <span id="lateLocationNameText" class="text-gray fw-semibold">-</span> <br>
                                    <span id="lateLocationAddressText" class="text-gray">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Clock In Time</span>
                                </div>
                                <div class="col-8">
                                    <span id="lateClockInText" class="text-danger fw-semibold">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Clock Out Time</span>
                                </div>
                                <div class="col-8">
                                    <span id="lateClockOutText" class="text-gray fw-semibold">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal-Box-End -->

<!-- Modal Box Start -->
<div class="modal fade" id="deviation" tabindex="-1" aria-labelledby="deviationLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="deviationLabel">Attendance Exception</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="mb-3">
                                <h2 class="fs-6 fw-bold text-gray mb-1" id="deviationIntroTitleText">-</h2>
                                <p class="form-label text-muted mb-3" id="deviationIntroPrimaryText">-</p>
                                <p class="form-label text-muted mb-3" id="deviationIntroSecondaryText">-</p>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Request Type</span>
                                </div>
                                <div class="col-8">
                                    <span id="deviationRequestTypeText" class="text-gray fw-semibold">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Reason</span>
                                </div>
                                <div class="col-8">
                                    <span id="deviationReasonText" class="text-gray">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Time Variance</span>
                                </div>
                                <div class="col-8">
                                    <span id="deviationTimeVarianceText" class="text-gray fw-semibold">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Status</span>
                                </div>
                                <div class="col-8">
                                    <span id="deviationStatusText" class="text-success fw-semibold">-</span> <span id="deviationStatusDateText" class="text-gray"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal-Box-End -->

<!-- Modal Box Start -->
<div class="modal fade" id="annualLeave" tabindex="-1" aria-labelledby="annualLeaveLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="annualLeaveLabel">Annual Leave</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-xl-12">
                            <h5 class="text-muted mb-0 fw-bold">Out of Office mode: ON</h5>
                            <p class="form-label text-muted mb-3">
                                Whether you’re traveling the globe or just relaxing on the couch, enjoy your well-deserved break. Disconnect, recharge, and have fun!
                            </p>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Leave Type</span>
                                </div>
                                <div class="col-8">
                                    <span id="annualLeaveTypeText" class="text-gray fw-semibold">Annual Leave</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Reason</span>
                                </div>
                                <div class="col-8">
                                    <span id="annualLeaveReasonText" class="text-gray">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Leave Duration</span>
                                </div>
                                <div class="col-8">
                                    <span id="annualLeaveDurationText" class="text-gray fw-semibold">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Status</span>
                                </div>
                                <div class="col-8">
                                    <span id="annualLeaveStatusText" class="text-success fw-semibold">-</span> <span id="annualLeaveStatusDateText" class="text-gray"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal-Box-End -->

<!-- Modal Box Start -->
<div class="modal fade" id="specialLeave" tabindex="-1" aria-labelledby="specialLeaveLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="specialLeaveLabel">Special Leave</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-xl-12">
                            <h5 class="text-muted mb-0 fw-bold">Take the time you need</h5>
                            <p class="form-label text-muted mb-3">
                                We understand that some personal matters require your full attention. Focus on what matters most right now, and we'll be right here when you return.
                            </p>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Leave Type</span>
                                </div>
                                <div class="col-8">
                                    <span id="specialLeaveTypeText" class="text-gray fw-semibold">Special Leave</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Reason</span>
                                </div>
                                <div class="col-8">
                                    <span id="specialLeaveReasonText" class="text-gray">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Leave Duration</span>
                                </div>
                                <div class="col-8">
                                    <span id="specialLeaveDurationText" class="text-gray fw-semibold">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Status</span>
                                </div>
                                <div class="col-8">
                                    <span id="specialLeaveStatusText" class="text-success fw-semibold">-</span> <span id="specialLeaveStatusDateText" class="text-gray"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal-Box-End -->

<!-- Modal Box Start -->
<div class="modal fade" id="unpaidLeave" tabindex="-1" aria-labelledby="unpaidLeaveLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="unpaidLeaveLabel">Unpaid Leave</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-xl-12">
                            <h5 class="text-muted mb-0 fw-bold">Time off logged</h5>
                            <p class="form-label text-muted mb-3">
                                Your extended leave has been successfully recorded. Take the space you need, and we look forward to having you back with the team when you’re ready.
                            </p>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Leave Type</span>
                                </div>
                                <div class="col-8">
                                    <span id="unpaidLeaveTypeText" class="text-gray fw-semibold">Unpaid Leave</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Reason</span>
                                </div>
                                <div class="col-8">
                                    <span id="unpaidLeaveReasonText" class="text-gray">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Leave Duration</span>
                                </div>
                                <div class="col-8">
                                    <span id="unpaidLeaveDurationText" class="text-gray fw-semibold">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Status</span>
                                </div>
                                <div class="col-8">
                                    <span id="unpaidLeaveStatusText" class="text-success fw-semibold">-</span> <span id="unpaidLeaveStatusDateText" class="text-gray"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal-Box-End -->

<!-- Modal Box Start -->
<div class="modal fade" id="sick" tabindex="-1" aria-labelledby="sickLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="sickLabel">Attendance Sick</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-xl-12">
                            <h5 class="text-muted mb-0 fw-bold">Your health comes first</h5>
                            <p class="form-label text-muted mb-3">
                                Take all the time you need to rest, hydrate, and recover. We’ve got the fort down here, so just focus on feeling better!
                            </p>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Leave Type</span>
                                </div>
                                <div class="col-8">
                                    <span id="sickTypeText" class="text-gray fw-semibold">Sick Leave</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Reason</span>
                                </div>
                                <div class="col-8">
                                    <span id="sickReasonText" class="text-gray">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Leave Duration</span>
                                </div>
                                <div class="col-8">
                                    <span id="sickDurationText" class="text-gray fw-semibold">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Status</span>
                                </div>
                                <div class="col-8">
                                    <span id="sickStatusText" class="text-success fw-semibold">-</span> <span id="sickStatusDateText" class="text-gray"></span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Medical Notes</span>
                                </div>
                                <div class="col-8">
                                    <a id="sickMedicalNotesImageLink" href="#" target="_blank" rel="noopener" class="d-none"><img id="sickMedicalNotesImage" src="" alt="Medical Notes" class="avatar avatar-lg rounded me-3" width="86"></a>
                                    <a id="sickMedicalNotesFileLink" href="#" target="_blank" rel="noopener" class="text-primary d-none">View Medical Notes</a>
                                    <span id="sickMedicalNotesFallback" class="text-gray">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal-Box-End -->

<div class="modal fade" id="trip" tabindex="-1" aria-labelledby="tripLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="tripLabel">Business Trip</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-xl-12">
                            <h5 class="text-muted mb-0 fw-bold">On the move!</h5>
                            <p class="form-label text-muted mb-3">
                                Safe travels! We wish you a smooth journey and highly successful meetings.
                                Keep crushing it out there in the field, represent us well, and don't forget to save those receipts for your expense reports!
                            </p>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Activity Type</span>
                                </div>
                                <div class="col-8">
                                    <span id="tripActivityTypeText" class="text-gray fw-semibold">Business Trip</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Purpose</span>
                                </div>
                                <div class="col-8">
                                    <span id="tripPurposeText" class="text-gray fw-semibold">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Destination</span>
                                </div>
                                <div class="col-8">
                                    <span id="tripDestinationText" class="text-gray">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Trip Duration</span>
                                </div>
                                <div class="col-8">
                                    <span id="tripDurationText" class="text-gray">-</span>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-4">
                                    <span>Status</span>
                                </div>
                                <div class="col-8">
                                    <span id="tripStatusText" class="text-success fw-semibold">-</span> <span id="tripStatusDateText" class="text-gray"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="d-flex justify-content-between mt-2">
                    <a id="tripSubmitTaskButton" class="btn light btn-info mt-2 mb-2 btn-lg me-2 w-100 disabled" href="#" aria-disabled="true" tabindex="-1">Submit Task</a>
                    <a id="tripReimbursementButton" class="btn light btn-success mt-2 mb-2 btn-lg w-100 disabled" href="#" aria-disabled="true" tabindex="-1">Reimbursement</a>
                </div>
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
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        (function () {
            var attendanceCalendarInitAttempts = 0;
            var maxAttendanceCalendarInitAttempts = 10;

            function initializeAttendanceCalendar() {
                var calendarEl = document.getElementById('calendar');
                var dayOffModalElement = document.getElementById('dayOff');
                var attendanceModalIds = ['onTime', 'late', 'deviation'];
                var calendarLabelModalIds = ['annualLeave', 'specialLeave', 'unpaidLeave', 'sick', 'trip'];
                var dayOffEventTypeTextElement = document.getElementById('dayOffEventTypeText');
                var dayOffHolidayNameTextElement = document.getElementById('dayOffHolidayNameText');
                var dayOffDateTextElement = document.getElementById('dayOffDateText');

                if (!calendarEl || calendarEl.dataset.fcInitialized === '1') {
                    return;
                }

                if (typeof FullCalendar === 'undefined') {
                    attendanceCalendarInitAttempts += 1;
                    console.warn('FullCalendar belum tersedia, retry init ke-' + attendanceCalendarInitAttempts);

                    if (attendanceCalendarInitAttempts < maxAttendanceCalendarInitAttempts) {
                        setTimeout(initializeAttendanceCalendar, 300);
                    }

                    return;
                }

                var showWeekendOff = calendarEl.dataset.showWeekendOff === '1';
                var holidayEvents = @json($holidayEvents ?? []);
                var calendarLabelEvents = @json($calendarLabelEvents ?? []);
                var attendanceHistoryEvents = @json($attendanceHistoryEvents ?? []);
                window.attendanceHistoryEvents = attendanceHistoryEvents;

                function formatDateToIso(dateObject) {
                    var year = dateObject.getFullYear();
                    var month = String(dateObject.getMonth() + 1).padStart(2, '0');
                    var day = String(dateObject.getDate()).padStart(2, '0');
                    return year + '-' + month + '-' + day;
                }

                function formatIsoDateForDisplay(isoDateValue) {
                    if (typeof isoDateValue !== 'string' || isoDateValue.trim() === '') {
                        return '-';
                    }

                    var parsedDate = new Date(isoDateValue + 'T00:00:00');
                    if (Number.isNaN(parsedDate.getTime())) {
                        return isoDateValue;
                    }

                    return new Intl.DateTimeFormat('id-ID', {
                        weekday: 'long',
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric',
                        timeZone: 'Asia/Jakarta'
                    }).format(parsedDate);
                }

                function openDayOffModal(eventTypeLabel, holidayNameLabel, isoDateValue) {
                    if (!dayOffModalElement || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
                        return;
                    }

                    if (dayOffEventTypeTextElement) {
                        dayOffEventTypeTextElement.textContent = eventTypeLabel || '-';
                    }

                    if (dayOffHolidayNameTextElement) {
                        dayOffHolidayNameTextElement.textContent = holidayNameLabel || '-';
                    }

                    if (dayOffDateTextElement) {
                        dayOffDateTextElement.textContent = formatIsoDateForDisplay(isoDateValue);
                    }

                    bootstrap.Modal.getOrCreateInstance(dayOffModalElement).show();
                }

                function setAttendanceModalText(elementId, value, fallback) {
                    var modalTextElement = document.getElementById(elementId);
                    if (!modalTextElement) {
                        return;
                    }

                    var textValue = typeof value === 'string' && value.trim() !== ''
                        ? value
                        : fallback;

                    modalTextElement.textContent = textValue || '-';
                }

                function setAttendanceModalOptionalText(elementId, value) {
                    var modalTextElement = document.getElementById(elementId);
                    if (!modalTextElement) {
                        return;
                    }

                    var textValue = typeof value === 'string' && value.trim() !== ''
                        ? value
                        : '';

                    modalTextElement.textContent = textValue;
                    modalTextElement.classList.toggle('d-none', textValue === '');
                }

                function setCalendarModalStatusText(elementId, value, statusClass) {
                    var statusTextElement = document.getElementById(elementId);
                    if (!statusTextElement) {
                        return;
                    }

                    statusTextElement.classList.remove('text-success', 'text-warning', 'text-danger', 'text-gray');
                    statusTextElement.classList.add(typeof statusClass === 'string' && statusClass.trim() !== '' ? statusClass : 'text-gray');
                    setAttendanceModalText(elementId, value, '-');
                }

                function fillAttendanceModal(modalId, props) {
                    if (modalId === 'onTime') {
                        setAttendanceModalText('onTimeLabel', props.modalTitle, 'On Time');
                        setAttendanceModalText('onTimeStatusText', props.attendanceStatusLabel, 'On-Time Arrival');
                        setAttendanceModalText('onTimeLocationNameText', props.locationName, 'Location not available');
                        setAttendanceModalText('onTimeLocationAddressText', props.locationAddress, '-');
                        setAttendanceModalText('onTimeClockInText', props.clockInSchedule, props.clockIn || '-');
                        setAttendanceModalText('onTimeClockOutText', props.clockOutSchedule, props.clockOut || '-');

                        return;
                    }

                    if (modalId === 'late') {
                        setAttendanceModalText('lateLabel', props.modalTitle, 'Late Arrival');
                        setAttendanceModalText('lateStatusText', props.attendanceStatusLabel, 'Late Arrival');
                        setAttendanceModalText('lateLocationNameText', props.locationName, 'Location not available');
                        setAttendanceModalText('lateLocationAddressText', props.locationAddress, '-');
                        setAttendanceModalText('lateClockInText', props.clockInSchedule, props.clockIn || '-');
                        setAttendanceModalText('lateClockOutText', props.clockOutSchedule, props.clockOut || '-');

                        return;
                    }

                    if (modalId === 'deviation') {
                        setAttendanceModalText('deviationLabel', props.modalTitle, 'Attendance Exception');
                        setAttendanceModalOptionalText('deviationIntroTitleText', props.deviationIntroTitle);
                        setAttendanceModalOptionalText('deviationIntroPrimaryText', props.deviationIntroPrimary);
                        setAttendanceModalOptionalText('deviationIntroSecondaryText', props.deviationIntroSecondary);
                        setAttendanceModalText('deviationRequestTypeText', props.requestTypeLabel, 'Attendance Exception');
                        setAttendanceModalText('deviationReasonText', props.reason, '-');
                        setAttendanceModalText('deviationTimeVarianceText', props.timeVarianceLabel, '-');
                        setAttendanceModalText('deviationStatusText', props.exceptionStatusLabel, '-');
                        setAttendanceModalText(
                            'deviationStatusDateText',
                            props.exceptionStatusDateLabel && props.exceptionStatusDateLabel !== '-' ? 'on ' + props.exceptionStatusDateLabel : '',
                            ''
                        );
                    }
                }

                function openAttendanceModal(modalId, props) {
                    if (attendanceModalIds.indexOf(modalId) === -1 || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
                        return;
                    }

                    var attendanceModalElement = document.getElementById(modalId);
                    if (!attendanceModalElement) {
                        return;
                    }

                    fillAttendanceModal(modalId, props || {});
                    bootstrap.Modal.getOrCreateInstance(attendanceModalElement).show();
                }

                function fillLeaveCalendarModal(modalId, props) {
                    setAttendanceModalText(modalId + 'Label', props.calendarModalTitle, props.leaveTypeLabel || 'Leave');
                    setAttendanceModalText(modalId + 'TypeText', props.leaveTypeLabel, 'Leave');
                    setAttendanceModalText(modalId + 'ReasonText', props.leaveReason, '-');
                    setAttendanceModalText(modalId + 'DurationText', props.leaveDurationLabel, '-');
                    setCalendarModalStatusText(modalId + 'StatusText', props.leaveStatusLabel, props.leaveStatusTextClass);
                    setAttendanceModalText(modalId + 'StatusDateText', props.leaveStatusDateLabel, '');
                }

                function fillSickMedicalNotes(props) {
                    var imageLinkElement = document.getElementById('sickMedicalNotesImageLink');
                    var imageElement = document.getElementById('sickMedicalNotesImage');
                    var fileLinkElement = document.getElementById('sickMedicalNotesFileLink');
                    var fallbackElement = document.getElementById('sickMedicalNotesFallback');
                    var medicalNotesUrl = typeof props.medicalNotesUrl === 'string' ? props.medicalNotesUrl.trim() : '';
                    var medicalNotesIsImage = props.medicalNotesIsImage === true;

                    if (imageLinkElement) {
                        imageLinkElement.classList.toggle('d-none', medicalNotesUrl === '' || !medicalNotesIsImage);
                        imageLinkElement.href = medicalNotesUrl || '#';
                    }

                    if (imageElement) {
                        imageElement.src = medicalNotesUrl !== '' && medicalNotesIsImage ? medicalNotesUrl : '';
                    }

                    if (fileLinkElement) {
                        fileLinkElement.classList.toggle('d-none', medicalNotesUrl === '' || medicalNotesIsImage);
                        fileLinkElement.href = medicalNotesUrl || '#';
                    }

                    if (fallbackElement) {
                        fallbackElement.classList.toggle('d-none', medicalNotesUrl !== '');
                    }
                }

                function setCalendarActionButton(elementId, url, isEnabled, disabledLabel) {
                    var buttonElement = document.getElementById(elementId);
                    if (!buttonElement) {
                        return;
                    }

                    var safeUrl = typeof url === 'string' && url.trim() !== '' ? url.trim() : '#';
                    buttonElement.href = isEnabled ? safeUrl : '#';
                    buttonElement.classList.toggle('disabled', !isEnabled);
                    buttonElement.setAttribute('aria-disabled', isEnabled ? 'false' : 'true');

                    if (isEnabled) {
                        buttonElement.removeAttribute('tabindex');
                        buttonElement.removeAttribute('title');
                        return;
                    }

                    buttonElement.setAttribute('tabindex', '-1');
                    buttonElement.setAttribute('title', disabledLabel || 'Not available yet.');
                }

                function fillTripCalendarModal(props) {
                    setAttendanceModalText('tripLabel', props.calendarModalTitle, 'Business Trip');
                    setAttendanceModalText('tripActivityTypeText', props.activityTypeLabel, 'Business Trip');
                    setAttendanceModalText('tripPurposeText', props.tripPurpose, '-');
                    setAttendanceModalText('tripDestinationText', props.tripDestination, '-');
                    setAttendanceModalText('tripDurationText', props.tripDurationLabel, '-');
                    setCalendarModalStatusText('tripStatusText', props.tripStatusLabel, props.tripStatusTextClass);
                    setAttendanceModalText('tripStatusDateText', props.tripStatusDateLabel, '');
                    setCalendarActionButton(
                        'tripSubmitTaskButton',
                        props.tripSubmitTaskUrl,
                        props.tripCanSubmitTask === true,
                        props.tripSubmitTaskDisabledLabel
                    );
                    setCalendarActionButton(
                        'tripReimbursementButton',
                        props.tripReimbursementUrl,
                        props.tripCanRequestReimbursement === true,
                        props.tripReimbursementDisabledLabel
                    );
                }

                function fillCalendarLabelModal(modalId, props) {
                    if (modalId === 'trip') {
                        fillTripCalendarModal(props);
                        return;
                    }

                    fillLeaveCalendarModal(modalId, props);
                    if (modalId === 'sick') {
                        fillSickMedicalNotes(props);
                    }
                }

                function openCalendarLabelModal(modalId, props) {
                    if (calendarLabelModalIds.indexOf(modalId) === -1 || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
                        return;
                    }

                    var calendarLabelModalElement = document.getElementById(modalId);
                    if (!calendarLabelModalElement) {
                        return;
                    }

                    fillCalendarLabelModal(modalId, props || {});
                    bootstrap.Modal.getOrCreateInstance(calendarLabelModalElement).show();
                }

                function buildWeekendEventsInRange(startDate, endDate) {
                    var weekendEvents = [];
                    var cursorDate = new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate());
                    var normalizedEndDate = new Date(endDate.getFullYear(), endDate.getMonth(), endDate.getDate());

                    while (cursorDate < normalizedEndDate) {
                        var dayNumber = cursorDate.getDay();
                        if (dayNumber === 0 || dayNumber === 6) {
                            var isoDate = formatDateToIso(cursorDate);
                            weekendEvents.push({
                                title: 'Weekend / Day Off',
                                start: isoDate,
                                allDay: true,
                                classNames: ['fc-weekend-dayoff-card'],
                                backgroundColor: '#8b0000',
                                borderColor: '#8b0000',
                                textColor: '#ffffff',
                                extendedProps: {
                                    dayOffEventType: 'Weekend / Day Off',
                                    dayOffHolidayName: 'Weekend',
                                    dayOffDate: isoDate
                                }
                            });
                        }

                        cursorDate.setDate(cursorDate.getDate() + 1);
                    }

                    return weekendEvents;
                }

                function buildHolidayEventsInRange(startDate, endDate) {
                    if (!Array.isArray(holidayEvents) || holidayEvents.length === 0) {
                        return [];
                    }

                    var normalizedStartDate = new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate());
                    var normalizedEndDate = new Date(endDate.getFullYear(), endDate.getMonth(), endDate.getDate());

                    return holidayEvents
                        .filter(function (eventItem) {
                            if (!eventItem || typeof eventItem.start !== 'string' || eventItem.start.trim() === '') {
                                return false;
                            }

                            var eventDate = new Date(eventItem.start + 'T00:00:00');
                            if (Number.isNaN(eventDate.getTime())) {
                                return false;
                            }

                            return eventDate >= normalizedStartDate && eventDate < normalizedEndDate;
                        })
                        .map(function (eventItem) {
                            return {
                                title: eventItem.title || '-',
                                start: eventItem.start,
                                allDay: eventItem.allDay !== false,
                                classNames: Array.isArray(eventItem.classNames) ? eventItem.classNames : ['fc-joint-leave-card'],
                                backgroundColor: eventItem.backgroundColor || '#cd5c5c',
                                borderColor: eventItem.borderColor || '#cd5c5c',
                                textColor: eventItem.textColor || '#ffffff',
                                extendedProps: eventItem.extendedProps || {}
                            };
                        });
                }

                function buildAttendanceHistoryEventsInRange(startDate, endDate) {
                    if (!Array.isArray(attendanceHistoryEvents) || attendanceHistoryEvents.length === 0) {
                        return [];
                    }

                    var normalizedStartDate = new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate());
                    var normalizedEndDate = new Date(endDate.getFullYear(), endDate.getMonth(), endDate.getDate());
                    return attendanceHistoryEvents
                        .filter(function (eventItem) {
                            if (!eventItem || typeof eventItem.start !== 'string' || eventItem.start.trim() === '') {
                                return false;
                            }

                            var eventDate = new Date(eventItem.start + 'T00:00:00');
                            if (Number.isNaN(eventDate.getTime())) {
                                return false;
                            }

                            return eventDate >= normalizedStartDate && eventDate < normalizedEndDate;
                        })
                        .map(function (eventItem) {
                            return {
                                title: eventItem.title,
                                start: eventItem.start,
                                allDay: eventItem.allDay !== false,
                                classNames: Array.isArray(eventItem.classNames) ? eventItem.classNames : ['fc-attendance-log-card'],
                                backgroundColor: eventItem.backgroundColor || '#20c997',
                                borderColor: eventItem.borderColor || '#1aa179',
                                textColor: eventItem.textColor || '#ffffff',
                                extendedProps: eventItem.extendedProps || {}
                            };
                        });
                }

                function buildCalendarLabelEventsInRange(startDate, endDate) {
                    if (!Array.isArray(calendarLabelEvents) || calendarLabelEvents.length === 0) {
                        return [];
                    }

                    var normalizedStartDate = new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate());
                    var normalizedEndDate = new Date(endDate.getFullYear(), endDate.getMonth(), endDate.getDate());

                    return calendarLabelEvents
                        .filter(function (eventItem) {
                            if (!eventItem || typeof eventItem.start !== 'string' || eventItem.start.trim() === '') {
                                return false;
                            }

                            var eventStartDate = new Date(eventItem.start + 'T00:00:00');
                            if (Number.isNaN(eventStartDate.getTime())) {
                                return false;
                            }

                            var eventEndDate = eventStartDate;
                            if (typeof eventItem.end === 'string' && eventItem.end.trim() !== '') {
                                eventEndDate = new Date(eventItem.end + 'T00:00:00');
                                if (Number.isNaN(eventEndDate.getTime())) {
                                    eventEndDate = eventStartDate;
                                }
                            } else {
                                eventEndDate = new Date(eventStartDate.getFullYear(), eventStartDate.getMonth(), eventStartDate.getDate() + 1);
                            }

                            return eventStartDate < normalizedEndDate && eventEndDate > normalizedStartDate;
                        })
                        .map(function (eventItem) {
                            var calendarLabelEvent = {
                                title: eventItem.title || '-',
                                start: eventItem.start,
                                allDay: eventItem.allDay !== false,
                                classNames: Array.isArray(eventItem.classNames) ? eventItem.classNames : ['fc-calendar-label-card'],
                                backgroundColor: eventItem.backgroundColor || '#198754',
                                borderColor: eventItem.borderColor || '#146c43',
                                textColor: eventItem.textColor || '#ffffff',
                                extendedProps: eventItem.extendedProps || {}
                            };

                            if (typeof eventItem.end === 'string' && eventItem.end.trim() !== '') {
                                calendarLabelEvent.end = eventItem.end;
                            }

                            return calendarLabelEvent;
                        });
                }

                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    initialDate: new Date(),
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,dayGridWeek,dayGridDay,listSchedule'
                    },
                    views: {
                        listSchedule: {
                            type: 'list',
                            duration: { months: 4 },
                            buttonText: 'Schedule'
                        }
                    },
                    listDayFormat: { weekday: 'short', month: 'short', day: 'numeric' },
                    listDaySideFormat: { year: 'numeric' },
                    scrollTime: '07:30:00',
                    slotMinTime: '06:00:00',
                    slotMaxTime: '21:00:00',
                    weekNumbers: true,
                    navLinks: true,
                    nowIndicator: true,
                    longPressDelay: 0,
                    editable: false,
                    selectable: false,
                    selectMirror: true,
                    droppable: false,
                    dateClick: function (info) {
                        if (info.view.type === 'dayGridMonth' || info.view.type === 'dayGridWeek') {
                            info.view.calendar.changeView('dayGridDay', info.date);
                        }
                    },
                    eventClick: function (info) {
                        var props = info.event.extendedProps || {};
                        info.jsEvent.preventDefault();

                        if (props.dayOffDate) {
                            openDayOffModal(
                                props.dayOffEventType || 'Hari Libur',
                                props.dayOffHolidayName || info.event.title || '-',
                                props.dayOffDate
                            );

                            return;
                        }

                        if (props.attendanceModalId) {
                            openAttendanceModal(props.attendanceModalId, props);
                            return;
                        }

                        if (props.calendarModalId) {
                            openCalendarLabelModal(props.calendarModalId, props);
                        }
                    },
                    events: function (fetchInfo, successCallback, failureCallback) {
                        try {
                            var holidayEventsInRange = buildHolidayEventsInRange(fetchInfo.start, fetchInfo.end);
                            var calendarLabelEventsInRange = buildCalendarLabelEventsInRange(fetchInfo.start, fetchInfo.end);
                            var attendanceLogEvents = buildAttendanceHistoryEventsInRange(fetchInfo.start, fetchInfo.end);
                            if (!showWeekendOff) {
                                successCallback(holidayEventsInRange.concat(calendarLabelEventsInRange, attendanceLogEvents));
                                return;
                            }

                            var weekendEvents = buildWeekendEventsInRange(fetchInfo.start, fetchInfo.end);
                            successCallback(holidayEventsInRange.concat(weekendEvents, calendarLabelEventsInRange, attendanceLogEvents));
                        } catch (error) {
                            console.error(error);
                            failureCallback(error);
                        }
                    }
                });

                calendar.render();
                window.attendanceCalendar = calendar;
                window.upsertAttendanceHistoryEvent = function (eventItem) {
                    if (!eventItem || typeof eventItem.start !== 'string' || eventItem.start.trim() === '') {
                        return;
                    }

                    attendanceHistoryEvents = attendanceHistoryEvents
                        .filter(function (existingEventItem) {
                            if (!existingEventItem || typeof existingEventItem.start !== 'string') {
                                return true;
                            }

                            return existingEventItem.start !== eventItem.start;
                        });
                    attendanceHistoryEvents.push(eventItem);
                    window.attendanceHistoryEvents = attendanceHistoryEvents;
                    calendar.refetchEvents();
                };
                calendarEl.dataset.fcInitialized = '1';
            }

            document.addEventListener('DOMContentLoaded', initializeAttendanceCalendar);

            if (typeof jQuery !== 'undefined') {
                jQuery(window).on('load', function () {
                    setTimeout(initializeAttendanceCalendar, 300);
                });
            }
        })();
    </script>

    
@endsection
