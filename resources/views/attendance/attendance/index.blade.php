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
        .app-fullcalendar .fc-event.fc-attendance-log-card {
            display: block;
            width: 100%;
            border-radius: 999px;
            font-size: 0.88rem;
            line-height: 1.1;
            font-weight: 700;
            padding: 0.42rem 0.7rem;
            border: 0 !important;
            cursor: pointer;
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
                var attendanceHistoryEvents = @json($attendanceHistoryEvents ?? []);

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
                                textColor: eventItem.textColor || '#ffffff'
                            };
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
                        if (!props.dayOffDate) {
                            return;
                        }

                        info.jsEvent.preventDefault();
                        openDayOffModal(
                            props.dayOffEventType || 'Hari Libur',
                            props.dayOffHolidayName || info.event.title || '-',
                            props.dayOffDate
                        );
                    },
                    events: function (fetchInfo, successCallback, failureCallback) {
                        try {
                            var holidayEventsInRange = buildHolidayEventsInRange(fetchInfo.start, fetchInfo.end);
                            var attendanceLogEvents = buildAttendanceHistoryEventsInRange(fetchInfo.start, fetchInfo.end);
                            if (!showWeekendOff) {
                                successCallback(holidayEventsInRange.concat(attendanceLogEvents));
                                return;
                            }

                            var weekendEvents = buildWeekendEventsInRange(fetchInfo.start, fetchInfo.end);
                            successCallback(holidayEventsInRange.concat(weekendEvents, attendanceLogEvents));
                        } catch (error) {
                            console.error(error);
                            failureCallback(error);
                        }
                    }
                });

                calendar.render();
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
