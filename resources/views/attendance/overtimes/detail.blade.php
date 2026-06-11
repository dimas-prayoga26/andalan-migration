@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
@endsection

@section('navbarTitle', 'Attendances')

@section('content')
@include('layouts.breadcrumb', [
    'title' => 'Attendances',
    'current' => 'Overtime',
    'homeRoute' => 'dashboard',
])

@include('attendance.layouts.profile-index')

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
                                <span class="text-gray fw-semibold">#OVT-2605-0089</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Full Name</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">Thomas Jefferson</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Supervisor</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">Michael Scott</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span class="fw-semibold">Overtime Log (Approved)</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Date</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray">22 May 2026</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Time</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray text-decoration-line-through">17:00 - 21:00</span>
                                <span class="text-gray">, 17:00 - 20:30</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Total Duration</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray text-decoration-line-through">4 Hours</span>
                                <span class="text-gray">, 3 Hours, 30 Minutes</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Brief and Instructions</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray">Renovasi fasad dan desain interior rumah bintaro.</span>
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
                                <span>Rate Multiplier</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">1.5x</span> <br>
                                <span class="text-gray">Standard Weekday Overtime</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Estimated Calculated Earnings</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">Rp. 100.000</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Payout Period</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">Included in May 2026 Payroll</span>
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
                                <span class="text-gray">Clock-out matched requested time</span> <br>
                                <span class="text-gray">23 May 2026, 09:00</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Approved by Supervisor</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray">Michael Scott</span> <br>
                                <span class="text-gray">23 May 2026, 09:00</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Approved by Director</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray">Steven Johansson</span> <br>
                                <span class="text-gray">23 May 2026, 12:00</span>
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
                                <span class="fs-20 text-black">01:20</span>
                            </div>
                            <div class="text-center">
                                <p class="fs-14 mb-2">Time</p>
                                <span class="fs-20 text-black">08:34:53</span>
                            </div>
                            <div class="text-center">
                                <p class="fs-14 mb-2">Start</p>
                                <span class="fs-20 text-black">08:00</span>
                            </div>
                        </div>
                    </div>
                    <a class="btn light btn-info m-3 mb-2 btn-lg" data-bs-toggle="modal" data-bs-target="#clockIn">Overtime Clock In</a>
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
                            <span class="fs-20 text-black">01:20</span>
                        </div>
                        <div class="text-center">
                            <p class="fs-14 mb-2">Time</p>
                            <span class="fs-20 text-black">08:34:53</span>
                        </div>
                        <div class="text-center">
                            <p class="fs-14 mb-2">Ended</p>
                            <span class="fs-20 text-black">08:00</span>
                        </div>
                    </div>
                </div>
                <a class="btn light btn-warning m-3 mb-2 btn-lg" data-bs-toggle="modal" data-bs-target="#clockOut">Overtime Clock Out</a>
                <div class="mb-3"></div>
            </div>
        </div>
            
        <!-- Start - Activity -->
        <div class="col-xxl-6 col-xl-12 col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Activity #OVT-2605-0001</h4>
                </div>
                <div class="card-body dz-scroll height380">
                    <div class="widget-timeline1">
                        <ul class="timeline">
                            <li>
                                <span class="timeline-status">22 May</span>
                                <div class="timeline-badge border-success"></div>
                                <div class="timeline-panel">
                                    <span>
                                        <span class="text-black fs-14 fw-semibold">Phase 1: Assignment & Request</span> <br>
                                    </span>
                                </div>
                            </li>
                            <li>
                                <span class="timeline-status">22 May</span>
                                <div class="timeline-badge border-success"></div>
                                <div class="timeline-panel">
                                    <span>
                                        1. Overtime Assignment Submitted <br>
                                        Datetime : 22 May 2026, 17:00 <br>
                                        Actor : Michael (Supervisor) <br>
                                        Status : <span class="badge badge-xs badge-success light fw-semibold">Complete</span>
                                    </span>
                                </div>
                            </li>
                            <li>
                                <span class="timeline-status">22 May</span>
                                <div class="timeline-badge border-success"></div>
                                <div class="timeline-panel">
                                    <span>
                                        <span class="text-black fs-14 fw-semibold">Phase 2: Execution (Time Tracking)</span> <br>
                                    </span>
                                </div>
                            </li>
                            <li>
                                <span class="timeline-status">22 May</span>
                                <div class="timeline-badge border-success"></div>
                                <div class="timeline-panel">
                                    <span>
                                        2. Overtime Session Started <br>
                                        Datetime : 22 May 2026, 17:00 <br>
                                        Actor : System Tracker <br>
                                        Status : <span class="badge badge-xs badge-success light fw-semibold">Clock In</span>
                                    </span>
                                </div>
                            </li>
                            <li>
                                <span class="timeline-status">22 May</span>
                                <div class="timeline-badge border-success"></div>
                                <div class="timeline-panel">
                                    <span>
                                        3. Task & Deliverables Submitted <br>
                                        Datetime : 22 May 2026, 20:00 <br>
                                        Actor : Thomas (Employee) <br>
                                        Status : <span class="badge badge-xs badge-success light fw-semibold">Completed</span>
                                    </span>
                                </div>
                            </li>
                            <li>
                                <span class="timeline-status">22 May</span>
                                <div class="timeline-badge border-success"></div>
                                <div class="timeline-panel">
                                    <span>
                                        4. Overtime Session Ended <br>
                                        Datetime : 22 May 2026, 20:00 <br>
                                        Actor : System Tracker <br>
                                        Status : <span class="badge badge-xs badge-success light fw-semibold">Clock Out</span>
                                    </span>
                                </div>
                            </li>
                            <li>
                                <span class="timeline-status">22 May</span>
                                <div class="timeline-badge border-success"></div>
                                <div class="timeline-panel">
                                    <span>
                                        <span class="text-black fs-14 fw-semibold">Phase 3: Review & Approval</span> <br>
                                    </span>
                                </div>
                            </li>
                            <li>
                                <span class="timeline-status">22 May</span>
                                <div class="timeline-badge border-success"></div>
                                <div class="timeline-panel">
                                    <span>
                                        5. Task & Hours Verification <br>
                                        Datetime : 22 May 2026, 20:00 <br>
                                        Actor : Michael (Supervisor) <br>
                                        Status : <span class="badge badge-xs badge-success light fw-semibold">Verified</span>
                                    </span>
                                </div>
                            </li>
                            <li>
                                <span class="timeline-status">22 May</span>
                                <div class="timeline-badge border-success"></div>
                                <div class="timeline-panel">
                                    <span>
                                        <span class="text-black fs-14 fw-semibold">Phase 4: Payroll & Payment</span> <br>
                                    </span>
                                </div>
                            </li>
                            <li>
                                <span class="timeline-status">22 May</span>
                                <div class="timeline-badge border-success"></div>
                                <div class="timeline-panel">
                                    <span>
                                        6. HR / Payroll Processing <br>
                                        Datetime : 22 May 2026, 20:00 <br>
                                        Actor : Ana (Finance) <br>
                                        Status : <span class="badge badge-xs badge-success light fw-semibold">Calculated & Locked</span>
                                    </span>
                                </div>
                            </li>
                            <li>
                                <span class="timeline-status">22 May</span>
                                <div class="timeline-badge border-success"></div>
                                <div class="timeline-panel">
                                    <span>
                                        7. Director Approval <br>
                                        Datetime : 22 May 2026, 20:00 <br>
                                        Actor : Josh (Director) <br>
                                        Status : <span class="badge badge-xs badge-success light fw-semibold">Approved</span>
                                    </span>
                                </div>
                            </li>
                            <li>
                                <span class="timeline-status">22 May</span>
                                <div class="timeline-badge border-dark"></div>
                                <div class="timeline-panel">
                                    <span>
                                        8. Payment Disbursement <br>
                                        Scheduled Date: 31 May 2026 <br>
                                        Actor : Ana (Finance) <br>
                                        Status : <span class="badge badge-xs badge-info light fw-semibold">Upcoming</span>
                                    </span>
                                </div>
                            </li>
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
                        <a class="text-success" data-bs-toggle="modal" data-bs-target="#task">+ Add Task</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush dz-draggable dropzoneContainer dz-scroll height400">
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
                    </div>
                </div>
            </div>
        </div>
        <!-- End - To Do -->
            
        </div>
    </div>
</div>

<!-- Modal Box Start -->
<div class="modal fade" id="filter" tabindex="-1" aria-labelledby="filterLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Filter Details</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="mb-3">
                                <label class="form-label">Filter by Status</label>
                                <select class="form-control selectpicker">
                                    <option selected="">Select All</option>
                                    <option>Assigned</option>
                                    <option>In Progress</option>
                                    <option>Completed</option>
                                    <option>Cancelled</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Filter by Leave Type</label>
                                <select class="form-control selectpicker">
                                    <option selected="">Select All</option>
                                    <option>Annual Leave</option>
                                    <option>Sick Leave</option>
                                    <option>Special Leave</option>
                                    <option>Unpaid Leave</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Filter by Timeframe</label>
                                <select class="form-control selectpicker">
                                    <option>Select All</option>
                                    <option>This Month</option>
                                    <option>Last Month</option>
                                    <option selected="">Year-to-Date</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
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
