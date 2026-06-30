@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
        $fullCalendarCssPath = public_path('assets/vendor/fullcalendar/css/main.min.css');
        $fullCalendarCssVersion = file_exists($fullCalendarCssPath) ? filemtime($fullCalendarCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fullcalendar/css/main.min.css') }}?v={{ $fullCalendarCssVersion }}">
    <link rel="stylesheet" href="https://unpkg.com/antd@6.2.3/dist/antd.css">
    <!-- Start - All Required Plugins -->
    <style>
        .app-fullcalendar .fc-toolbar {
            align-items: center;
            flex-direction: row !important;
            gap: 1rem;
        }

        .app-fullcalendar .fc-toolbar-chunk {
            align-items: center;
            display: flex;
        }

        .app-fullcalendar .fc-toolbar-title {
            color: var(--bs-heading-color);
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
            text-align: center;
        }

        .app-fullcalendar .fc-button {
            border-radius: 0.75rem;
            box-shadow: none !important;
            font-size: 0.875rem;
            font-weight: 600;
            line-height: 1.25;
            min-height: 38px;
            padding: 0.6rem 1rem;
            text-transform: capitalize;
        }

        .app-fullcalendar .fc-button-primary {
            background-color: #ffffff !important;
            border-color: var(--bs-border-color) !important;
            color: var(--bs-heading-color) !important;
        }

        .app-fullcalendar .fc-button-primary:hover,
        .app-fullcalendar .fc-button-primary:focus {
            background-color: #f4f6fb !important;
            border-color: var(--bs-primary) !important;
            color: var(--bs-primary) !important;
        }

        .app-fullcalendar .fc-button-primary:disabled {
            background-color: #f4f6fb !important;
            border-color: var(--bs-border-color) !important;
            color: #6c757d !important;
            opacity: 1;
        }

        .app-fullcalendar .fc-button-primary:not(:disabled).fc-button-active,
        .app-fullcalendar .fc-button-primary:not(:disabled):active {
            background-color: var(--bs-primary) !important;
            border-color: var(--bs-primary) !important;
            color: #ffffff !important;
        }

        .app-fullcalendar .fc-button-group {
            display: inline-flex;
        }

        .app-fullcalendar .fc-button-group>.fc-button {
            border-radius: 0;
        }

        .app-fullcalendar .fc-button-group>.fc-button:first-child {
            border-bottom-left-radius: 0.75rem;
            border-top-left-radius: 0.75rem;
        }

        .app-fullcalendar .fc-button-group>.fc-button:last-child {
            border-bottom-right-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
        }

        .app-fullcalendar .fc-prev-button,
        .app-fullcalendar .fc-next-button {
            align-items: center;
            display: inline-flex;
            height: 38px;
            justify-content: center;
            min-height: 38px;
            padding: 0 !important;
            width: 38px;
        }

        .app-fullcalendar .fc-prev-button {
            border-bottom-left-radius: 0.75rem !important;
            border-top-left-radius: 0.75rem !important;
        }

        .app-fullcalendar .fc-next-button {
            border-bottom-right-radius: 0.75rem !important;
            border-top-right-radius: 0.75rem !important;
        }

        .app-fullcalendar .fc-today-button {
            margin-left: 0.75rem !important;
        }

        .app-fullcalendar #calendar-date-filter {
            min-height: 38px;
        }

        @media (max-width: 767.98px) {
            .app-fullcalendar .fc-toolbar {
                align-items: stretch;
                flex-direction: column !important;
            }

            .app-fullcalendar .fc-toolbar-chunk {
                justify-content: center;
            }

            .app-fullcalendar .fc-toolbar-chunk:first-child,
            .app-fullcalendar .fc-toolbar-chunk:last-child {
                overflow-x: auto;
            }
        }
    </style>

@endsection

@section('navbarTitle', 'Calendar')

@section('content')
<!-- Start - Page Title & Breadcrumb -->
<div class="page-title">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li><h1>Calendar</h1></li>
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">
                    <svg width="18" height="18" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.125 6.375L8.5 1.41667L14.875 6.375V14.1667C14.875 14.5424 14.7257 14.9027 14.4601 15.1684C14.1944 15.4341 13.8341 15.5833 13.4583 15.5833H3.54167C3.16594 15.5833 2.80561 15.4341 2.53993 15.1684C2.27426 14.9027 2.125 14.5424 2.125 14.1667V6.375Z" stroke="var(--bs-body-color)" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M6.375 15.5833V8.5H10.625V15.5833" stroke="var(--bs-body-color)" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Home
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Calendar</li>
        </ol>
    </nav>
</div>
<!-- End - Page Title & Breadcrumb -->
<div class="row">
    <div class="col-xl-12 col-xxl-12">
        <div class="card h-auto">
            <div class="card-header">
                <ul class="nav nav-underline card-header-tabs" id="nav-tab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="underline-Posts-tab" data-bs-toggle="tab" data-bs-target="#underline-Posts" type="button" role="tab" aria-controls="underline-Posts" aria-selected="true">Google Calendar</button>
                    </li>
                </ul>
            </div>
            <div class="row">
                <div class="col-xxl-12 col-xl-12">
                    <div class="card-body">
                        <div id="calendar-date-filter-wrap" class="d-none">
                            <input type="date" id="calendar-date-filter" class="form-control form-control-sm" style="width: 180px;">
                        </div>
                        <div id="calendar" class="app-fullcalendar" data-event-source-url="{{ route('activity-schadule.events') }}" data-time-zone="Asia/Jakarta"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End - Content Body -->

@endsection

@section('script')
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
        $fullCalendarJsPath = public_path('assets/vendor/fullcalendar/js/main.js');
        $fullCalendarJsVersion = file_exists($fullCalendarJsPath) ? filemtime($fullCalendarJsPath) : time();
        $fullCalendarInitJsPath = public_path('assets/js/plugins-init/fullcalendar-init.js');
        $fullCalendarInitJsVersion = file_exists($fullCalendarInitJsPath) ? filemtime($fullCalendarInitJsPath) : time();
    @endphp
    <script src="{{ asset('assets/vendor/fullcalendar/js/main.js') }}?v={{ $fullCalendarJsVersion }}"></script>
    <script src="{{ asset('assets/js/plugins-init/fullcalendar-init.js') }}?v={{ $fullCalendarInitJsVersion }}"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
@endsection
