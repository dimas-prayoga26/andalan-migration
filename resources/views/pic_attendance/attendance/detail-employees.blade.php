@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <style>
        .admin-attendance-detail-avatar {
            align-items: center;
            aspect-ratio: 1 / 1;
            border: 1px solid #e2e7f0;
            border-radius: 50%;
            color: #0b2a97;
            display: inline-flex;
            font-size: 42px;
            font-weight: 700;
            height: 120px;
            justify-content: center;
            overflow: hidden;
            width: 120px;
        }

        .admin-attendance-detail-avatar img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }
    </style>
@endsection

@section('navbarTitle', 'Attendance')

@section('content')
@php
    $recapDetailEmployee = $recapDetailEmployee ?? [];
    $recapDetailMetrics = $recapDetailMetrics ?? [];
    $recapDetailCharts = $recapDetailCharts ?? [];
    $recapDetailAttendanceRows = $recapDetailAttendanceRows ?? collect();
    $recapDetailPeriodLabel = (string) ($recapDetailPeriodLabel ?? now('Asia/Jakarta')->format('F Y'));
    $recapDetailMonth = (int) ($recapDetailMonth ?? now('Asia/Jakarta')->month);
    $recapDetailYear = (int) ($recapDetailYear ?? now('Asia/Jakarta')->year);
    $recapDetailPeityValue = static fn (mixed $percent): string => max(0, min(100, (int) $percent)).'/100';
@endphp

@include('pic_attendance.layout.navbar')

<!-- Start - Attendance -->
<div class="col-lg-12">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h5 class="mb-0">Attendance Details</h5>
	</div>
</div>		

<div class="row">
    
    <!-- Start - Portfolio -->
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body py-sm-5">
                <div class="text-center mb-3">
                    <div class="admin-attendance-detail-avatar mx-auto">
                        @if (! empty($recapDetailEmployee['avatar_url']))
                            <img src="{{ $recapDetailEmployee['avatar_url'] }}" alt="{{ $recapDetailEmployee['name'] ?? 'Employee' }}">
                        @else
                            <span>{{ $recapDetailEmployee['initials'] ?? '-' }}</span>
                        @endif
                    </div>
                    <div class="clearfix mt-3">
                        <h6 class="mb-0">{{ $recapDetailEmployee['name'] ?? '-' }}</h6>
                        <span>{{ $recapDetailEmployee['position'] ?? '-' }}</span> <br>
                        <span class="fw-semibold">{{ $recapDetailEmployee['department'] ?? '-' }}</span>
                        <span class="badge badge-sm light badge-danger fw-bold mt-1">{{ $recapDetailEmployee['company'] ?? '-' }}</span>
                    </div>
                </div>
                <div class="row py-1">
                    <div class="col-12">
                        <span>Employee ID :</span>
                    </div>
                    <div class="col-12">
                        <span>{{ $recapDetailEmployee['employee_code'] ?? '-' }}</span> <br>
                    </div>
                </div>
                <div class="row py-1">
                    <div class="col-12">
                        <span>Phone Number :</span>
                    </div>
                    <div class="col-12">
                        <span>{{ $recapDetailEmployee['phone'] ?? '-' }}</span> <br>
                    </div>
                </div>
                <div class="row py-1">
                    <div class="col-12">
                        <span>Email :</span>
                    </div>
                    <div class="col-12">
                        <span>{{ $recapDetailEmployee['email'] ?? '-' }}</span> <br>
                    </div>
                </div>
                <div class="row py-1">
                    <div class="col-12">
                        <span>Base :</span>
                    </div>
                    <div class="col-12">
                        <span>{{ $recapDetailEmployee['base'] ?? '-' }}</span> <br>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End - Portfolio -->

    <!-- Start - Account Setup -->
    <div class="col-md-9">
        <div class="card h-100">
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-2 col-6">
                        <div class="bg-success-subtle rounded px-3 py-2 text-center">
                            <span class="fs-14 text-success fw-semibold">On Time</span>
                            <h4 class="mb-0 fw-semibold">{{ $recapDetailMetrics['on_time'] ?? '0 days' }}</h4>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="bg-danger-subtle rounded px-3 py-2 text-center">
                            <span class="fs-14 text-danger fw-semibold fw-semibold">Late</span>
                            <h4 class="mb-0 fw-semibold">{{ $recapDetailMetrics['late'] ?? '0 days' }}</h4>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="bg-info-subtle rounded px-3 py-2 text-center">
                            <span class="fs-14 text-info fw-semibold">Leave</span>
                            <h4 class="mb-0 fw-semibold">{{ $recapDetailMetrics['leave'] ?? '0 days' }}</h4>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="bg-secondary-subtle rounded px-3 py-2 text-center">
                            <span class="fs-14 text-secondary fw-semibold">Deviation</span>
                            <h4 class="mb-0 fw-semibold">{{ $recapDetailMetrics['deviation'] ?? '0 days' }}</h4>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="bg-dark-subtle rounded px-3 py-2 text-center">
                            <span class="fs-14 text-black fw-semibold">Alpha</span>
                            <h4 class="mb-0 fw-semibold">{{ $recapDetailMetrics['alpha'] ?? '0 days' }}</h4>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="bg-info-subtle rounded px-3 py-2 text-center">
                            <span class="fs-14 text-info fw-semibold">Trip</span>
                            <h4 class="mb-0 fw-semibold">{{ $recapDetailMetrics['trip'] ?? '0 days' }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="bg-primary-subtle rounded px-3 py-2 text-center">
                            <span class="fs-14 text-primary fw-semibold">Annual Leave</span>
                            <h4 class="mb-0 fw-semibold">{{ $recapDetailMetrics['annual_leave'] ?? '0 days' }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="bg-warning-subtle rounded px-3 py-2 text-center">
                            <span class="fs-14 text-warning fw-semibold">Sick Leave</span>
                            <h4 class="mb-0 fw-semibold">{{ $recapDetailMetrics['sick_leave'] ?? '0 days' }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="bg-secondary-subtle rounded px-3 py-2 text-center">
                            <span class="fs-14 text-secondary fw-semibold">Special Leave</span>
                            <h4 class="mb-0 fw-semibold">{{ $recapDetailMetrics['special_leave'] ?? '0 days' }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="bg-light-subtle rounded px-3 py-2 text-center">
                            <span class="fs-14 text-dark fw-semibold">Unpaid Leave</span>
                            <h4 class="mb-0 fw-semibold">{{ $recapDetailMetrics['unpaid_leave'] ?? '0 days' }}</h4>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mt-3">
                        <h4 class="card-title">Progress ({{ $recapDetailPeriodLabel }})</h4>
                        <div class="row align-items-center">
                            <div class="col-lg-4 mb-lg-0 mb-3 text-center radialBar">
                                <div id="radialBar"></div>
                                <h4 class="fs-16 text-black">Days Worked <br> ({{ $recapDetailCharts['days_worked_label'] ?? '0/0 days' }})</h4>
                            </div>
                            <div class="col-lg-8">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center mb-sm-5 mb-3">
                                            <div class="position-relative me-3">
                                                <span class="donut" data-peity='{ "fill": ["var(--bs-success)", "var(--bs-light)"],   "innerRadius": 34, "radius": 10}'>{{ $recapDetailPeityValue($recapDetailCharts['on_time_percent'] ?? 0) }}</span>
                                                
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
                                                <h4 class="fs-18 text-black">On Time <br> ({{ (int) ($recapDetailCharts['on_time_percent'] ?? 0) }}%)</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center mb-sm-5 mb-3">
                                            <div class="position-relative me-3">
                                                <span class="donut" data-peity='{ "fill": ["var(--bs-danger)", "var(--bs-light)"],   "innerRadius": 34, "radius": 10}'>{{ $recapDetailPeityValue($recapDetailCharts['late_percent'] ?? 0) }}</span>
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
                                                <h4 class="fs-18 text-black">Late <br> ({{ (int) ($recapDetailCharts['late_percent'] ?? 0) }}%)</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center mb-sm-5 mb-3">
                                            <div class="position-relative me-3">
                                                <span class="donut" data-peity='{ "fill": ["var(--bs-secondary)", "var(--bs-light)"],   "innerRadius": 34, "radius": 10}'>{{ $recapDetailPeityValue($recapDetailCharts['monthly_hours_percent'] ?? 0) }}</span>
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
                                                <h4 class="fs-18 text-black">Monthly Hours <br> ({{ (int) ($recapDetailCharts['monthly_hours_percent'] ?? 0) }}%)</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center mb-sm-5 mb-3">
                                            <div class="position-relative me-3">
                                                <span class="donut" data-peity='{ "fill": ["var(--bs-info)", "var(--bs-light)"],   "innerRadius": 34, "radius": 10}'>{{ $recapDetailPeityValue($recapDetailCharts['overtime_percent'] ?? 0) }}</span>
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
                                                <h4 class="fs-18 text-black">Overtime <br> ({{ (int) ($recapDetailCharts['overtime_percent'] ?? 0) }}%)</h4>
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
    </div>
    <!-- Start - Account Setup -->

</div>

<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header border-0 align-items-center gap-2 flex-wrap">
                <h4 id="recapDetailPeriodLabel" class="card-title m-0">Attendance Recap ({{ $recapDetailPeriodLabel }})</h4>
                <div class="clearfix d-flex align-items-center">
                    <form id="recapDetailPeriodFilter" method="GET" action="{{ route('pic-attendance.attendance.detail-employees', ['employee' => $recapDetailEmployee['id'] ?? '']) }}" class="clearfix d-flex align-items-center">
                        <div class="clearfix me-1">
                            <select id="recapDetailMonthFilter" name="month" class="selectpicker form-select form-select-sm" aria-label="Select month">
                                @foreach (range(1, 12) as $month)
                                    <option value="{{ $month }}" @selected($recapDetailMonth === $month)>{{ \Illuminate\Support\Carbon::create($recapDetailYear, $month, 1)->format('F') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="clearfix">
                            <select id="recapDetailYearFilter" name="year" class="selectpicker form-select form-select-sm" aria-label="Select year">
                                @foreach (range((int) now('Asia/Jakarta')->year, (int) now('Asia/Jakarta')->year - 3) as $year)
                                    <option value="{{ $year }}" @selected($recapDetailYear === $year)>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button id="recapDetailPeriodFilterButton" type="button" class="btn btn-sm btn-primary light ms-2" title="Apply period" aria-label="Apply period"><i class="fa-solid fa-filter"></i></button>
                    </form>
                </div>
            </div>
            <div class="card-body table-card-body px-0 pt-0 pb-2">
                <div class="table-responsive">
                    <table class="table table-sm table-sm-responsive text-nowrap" id="recapDetailAttendanceTable">
                        <thead>
                            <tr>
                                <th class="mw-100">Date</th>
                                <th class="mw-80">Clock In</th>
                                <th class="mw-80">Clock Out</th>
                                <th class="mw-80">Note</th>
                                <th class="mw-80">Working Hours</th>
                                <th class="mw-100">Attachment</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                        </tbody>
                    </table>
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
        $dataTablesJsPath = public_path('assets/vendor/datatables/js/jquery.dataTables.bundle.min.js');
        $dataTablesJsVersion = file_exists($dataTablesJsPath) ? filemtime($dataTablesJsPath) : time();
        $apexChartsPath = public_path('assets/vendor/apexcharts/dist/apexcharts.min.js');
        $apexChartsVersion = file_exists($apexChartsPath) ? filemtime($apexChartsPath) : time();
    @endphp
    <script src="{{ asset('assets/vendor/datatables/js/jquery.dataTables.bundle.min.js') }}?v={{ $dataTablesJsVersion }}"></script>
    <script src="{{ asset('assets/vendor/apexcharts/dist/apexcharts.min.js') }}?v={{ $apexChartsVersion }}"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
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
				series: [2, 18, 1, 1],
				chart: {
				type: 'donut',
				height:200,
				},
				legend: {
					show:false,
				},
				fill:{
					colors:['#F94687', '#2BC155', '#A02CFA', '#1EA7C5']
				},
				stroke:{
					width:0,
				},
				colors:['#F94687', '#2BC155', '#A02CFA', '#1EA7C5'],
				dataLabels: {
				enabled: false
				}
				};

				var chart = new ApexCharts(document.querySelector("#pieChart"), options);
				chart.render();
			}
			
			var radialBar = function(){
				if (typeof window.ApexCharts === 'undefined') {
					return;
				}

				var options = {
				series: [{{ (int) ($recapDetailCharts['days_worked_percent'] ?? 0) }}],
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
				if (typeof $.fn.peity !== 'function') {
					return;
				}

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
						radialBar();
						donutChart();
					},
					
					resize:function(){
						
					}
				}
			
			}();

			jQuery(document).ready(function(){
				var detailPeriodFilter = document.getElementById('recapDetailPeriodFilter');
				var detailMonthFilter = document.getElementById('recapDetailMonthFilter');
				var detailYearFilter = document.getElementById('recapDetailYearFilter');
				var periodLabel = document.getElementById('recapDetailPeriodLabel');
				var filterButton = document.getElementById('recapDetailPeriodFilterButton');

				if (!detailPeriodFilter) {
					return;
				}

				var escapeHtml = function(value) {
					return String(value || '')
						.replace(/&/g, '&amp;')
						.replace(/</g, '&lt;')
						.replace(/>/g, '&gt;')
						.replace(/"/g, '&quot;')
						.replace(/'/g, '&#039;');
				};
				var detailTable = jQuery('#recapDetailAttendanceTable').DataTable({
					ajax: {
						url: @json(route('pic-attendance.attendance.detail-employees.datatable', ['employee' => $recapDetailEmployee['id'] ?? ''])),
						data: function(requestData) {
							requestData.month = detailMonthFilter ? detailMonthFilter.value : 0;
							requestData.year = detailYearFilter ? detailYearFilter.value : 0;
						},
						dataSrc: function(response) {
							if (periodLabel && response.period_label) {
								periodLabel.textContent = 'Attendance Recap (' + response.period_label + ')';
							}

							return response.data || [];
						}
					},
					autoWidth: false,
					searching: false,
					pageLength: 10,
					lengthChange: false,
					columns: [
						{
							data: 'date',
							render: function(data, type, row) {
								return type === 'sort' ? row.date_sort : escapeHtml(data);
							}
						},
						{
							data: 'clock_in',
							render: function(data, type, row) {
								if (type !== 'display') {
									return data;
								}

								return '<span class="badge badge-sm badge-' + escapeHtml(row.clock_in_badge) + ' light fw-semibold">' + escapeHtml(data) + '</span>';
							}
						},
						{
							data: 'clock_out',
							render: function(data, type, row) {
								if (type !== 'display') {
									return data;
								}

								return '<span class="badge badge-sm badge-' + escapeHtml(row.clock_out_badge) + ' light fw-semibold">' + escapeHtml(data) + '</span>';
							}
						},
						{ data: 'note', render: function(data) { return escapeHtml(data); } },
						{ data: 'working_hours', render: function(data) { return escapeHtml(data); } },
						{ data: null, defaultContent: '', orderable: false, searchable: false }
					],
					order: [[0, 'asc']],
					language: {
						emptyTable: 'No attendance records available for this employee.',
						paginate: {
							next: '<i class="fa-solid fa-angle-right"></i>',
							previous: '<i class="fa-solid fa-angle-left"></i>'
						}
					}
				});
				var reloadDetailTable = function(){
					detailTable.ajax.reload();
				};

				jQuery(detailPeriodFilter).on('submit', function(event){
					event.preventDefault();
					reloadDetailTable();
				});
				if (detailMonthFilter) {
					detailMonthFilter.addEventListener('change', reloadDetailTable);
				}
				if (detailYearFilter) {
					detailYearFilter.addEventListener('change', reloadDetailTable);
				}
				if (filterButton) {
					filterButton.addEventListener('click', reloadDetailTable);
				}
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
