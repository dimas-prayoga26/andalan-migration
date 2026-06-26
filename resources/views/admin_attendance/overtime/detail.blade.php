@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">

@endsection

@section('navbarTitle', 'Overview')

@section('content')

@include('admin_attendance.layout.navbar')

<!-- Start - My Projects -->
<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Overtime</h5>
        <div class="d-flex align-items-center">
            <!-- <a href="javascript:void(0)" class="btn btn-primary btn-sm ms-2">+ New Project</a> -->
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
                            <h4 class="card-title">[Name] Extra Mile</h4>
                            <p class="fs-13 mb-0">Here is a breakdown overtime shift, the tasks, and reward details.</p>
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
                            <h4 class="card-title">Overtime Verification</h4>
                            <p class="fs-13 mb-0">Please review the submitted task details and for compliance.</p>
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
                                <h6 class="fs-16 text-black mb-0">Monitoring the Extra Mile</h6>
                                <span class="fs-12">
                                    Verify the session to ensure staff overtime are recorded accurately for compensation.
                                </span>
                            </div>
                        </div>
                        <div class="d-flex gap-3 justify-content-between flex-wrap p-4 pb-2">
                            <div class="text-center">
                                <p class="fs-14 mb-2">Start</p>
                                <span class="fs-20 text-black">18:00</span>
                            </div>
                            <div class="text-center">
                                <p class="fs-14 mb-2">Duration</p>
                                <span class="fs-20 text-black">2 hours</span>
                            </div>
                            <div class="text-center">
                                <p class="fs-14 mb-2">End</p>
                                <span class="fs-20 text-black">20:00</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3"></div>
                </div>
            </div>

            <div class="col-md-6">
            <div class="card">
                <div class="card-header border-0 pb-3">
                    <div>
                        <h4 class="card-title">Review Overtime Session</h4>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="row ps-4 pe-4">
                        <div class="col-md-6">
                            <div class="">
                                <label class="form-label">Scheduled Start</label>
                                <p class="fs-14 mb-0">18:00</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="">
                                <label class="form-label">Scheduled End</label>
                                <p class="fs-14 mb-0">20:00</p>
                            </div>
                        </div>
                    </div>
                    <div class="row p-4 pb-2">
                        <div class="col-md-6">
                            <div class=" mb-3">
                                <label class="form-label">Approved Start <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" value="18:00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class=" mb-3">
                                <label class="form-label">Approved End <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" value="20:00">
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn light btn-success m-3 mb-2 btn-lg" disabled>Approve Overtime Session</button>
                <div class="mb-3"></div>
            </div>
        </div>
            
        
        <!-- Start - To Do -->
        <div class="col-xxl-12 col-xl-12 col-md-12">
            <div class="card overflow-hidden">
                <div class="card-header">
                    <h4 class="card-title">[Name] Task Items</h4>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush dz-draggable dropzoneContainer dz-scroll height400">
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
                                        <div>
                                            <div class="form-check custom-checkbox checkbox-success">
                                                <input class="form-check-input" type="checkbox" id="customCheckBox1" checked disabled>
                                                <label class="form-check-label text-black" for="customCheckBox1">Desain Interior Ruang Tamu</label>
                                            </div>
                                        </div>
                                        <span>20 May 2026, 20:00 WIB</span>
                                    </div>
                                </div>
                                <!-- <div class="clearfix">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#approval">
                                        <div class="btn btn-square btn-success light btn-sm ms-1">
                                            <i class="fa fa-pen "></i>
                                        </div>
                                    </a>
                                </div> -->
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
                                            <input class="form-check-input" type="checkbox" id="customCheckBox2" disabled>
                                            <label class="form-check-label text-black" for="customCheckBox2">Compete this projects Monday</label>
                                        </div>
                                        <span>2023-12-26 07:15:00</span>
                                    </div>
                                </div>
                                <div class="clearfix">
                                    <button type="button" class="btn btn-square btn-danger light btn-sm" disabled>
                                        <i class="fa-regular fa-trash-can "></i>
                                    </button>
                                    <button type="button" class="btn btn-square btn-primary light btn-sm ms-1" disabled>
                                        <i class="fa fa-pen "></i>
                                    </button>
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
                                            <input class="form-check-input" type="checkbox" id="customCheckBox3" disabled>
                                            <label class="form-check-label text-black" for="customCheckBox3">Compete this projects Monday</label>
                                        </div>
                                        <span>2023-12-26 07:15:00</span>
                                    </div>
                                </div>
                                <div class="clearfix">
                                    <button type="button" class="btn btn-square btn-danger light btn-sm" disabled>
                                        <i class="fa-regular fa-trash-can "></i>
                                    </button>
                                    <button type="button" class="btn btn-square btn-primary light btn-sm ms-1" disabled>
                                        <i class="fa fa-pen "></i>
                                    </button>
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
                                            <input class="form-check-input" type="checkbox" id="customCheckBox4" disabled>
                                            <label class="form-check-label text-black" for="customCheckBox4">Compete this projects Monday</label>
                                        </div>
                                        <span>2023-12-26 07:15:00</span>
                                    </div>
                                </div>
                                <div class="clearfix">
                                    <button type="button" class="btn btn-square btn-danger light btn-sm" disabled>
                                        <i class="fa-regular fa-trash-can "></i>
                                    </button>
                                    <button type="button" class="btn btn-square btn-primary light btn-sm ms-1" disabled>
                                        <i class="fa fa-pen "></i>
                                    </button>
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
                                            <input class="form-check-input" type="checkbox" id="customCheckBox5" disabled>
                                            <label class="form-check-label text-black" for="customCheckBox5">Compete this projects Monday</label>
                                        </div>
                                        <span>2023-12-26 07:15:00</span>
                                    </div>
                                </div>
                                <div class="clearfix">
                                    <button type="button" class="btn btn-square btn-danger light btn-sm" disabled>
                                        <i class="fa-regular fa-trash-can "></i>
                                    </button>
                                    <button type="button" class="btn btn-square btn-primary light btn-sm ms-1" disabled>
                                        <i class="fa fa-pen "></i>
                                    </button>
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
                                            <input class="form-check-input" type="checkbox" id="customCheckBox6" disabled>
                                            <label class="form-check-label text-black" for="customCheckBox6">Compete this projects Monday</label>
                                        </div>
                                        <span>2023-12-26 07:15:00</span>
                                    </div>
                                </div>
                                <div class="clearfix">
                                    <button type="button" class="btn btn-square btn-danger light btn-sm" disabled>
                                        <i class="fa-regular fa-trash-can "></i>
                                    </button>
                                    <button type="button" class="btn btn-square btn-primary light btn-sm ms-1" disabled>
                                        <i class="fa fa-pen "></i>
                                    </button>
                                </div>
                            </div>
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
    @endphp
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
@endsection
