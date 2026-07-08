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

@php
    $finishedTaskItems = collect($overtimeTaskItems['finished'] ?? []);
    $pendingTaskItems = collect($overtimeTaskItems['pending'] ?? []);
    $taskItems = $finishedTaskItems->merge($pendingTaskItems)->values();
    $approvedStartValue = ($overtimeDetail['approved_start_time'] ?? '-') !== '-'
        ? $overtimeDetail['approved_start_time']
        : (($overtimeDetail['actual_start_time'] ?? '-') !== '-'
            ? $overtimeDetail['actual_start_time']
            : ($overtimeDetail['planned_start_time'] ?? '18:00'));
    $approvedEndValue = ($overtimeDetail['approved_end_time'] ?? '-') !== '-'
        ? $overtimeDetail['approved_end_time']
        : (($overtimeDetail['actual_end_time'] ?? '-') !== '-'
            ? $overtimeDetail['actual_end_time']
            : ($overtimeDetail['planned_end_time'] ?? '20:00'));
@endphp

<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Overtime</h5>
    </div>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header border-0 pb-0">
                <div>
                    <h4 class="card-title"><span class="fw-bold">{{ $overtimeDetail['staff_name'] ?? '[Name]' }}</span> Extra Mile</h4>
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
                        <span class="text-gray fw-semibold">{{ $overtimeDetail['record_number'] ?? '#OVT' }}</span>
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
                        @if (($overtimeDetail['time_changed'] ?? false) === true)
                            <span class="text-gray text-decoration-line-through">{{ $overtimeDetail['planned_time_range'] ?? '-' }}</span>
                            <span class="text-gray">, {{ $overtimeDetail['actual_time_range'] ?? '-' }}</span>
                        @else
                            <span class="text-gray">{{ $overtimeDetail['planned_time_range'] ?? '-' }}</span>
                        @endif
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Total Duration</span>
                    </div>
                    <div class="col-md-6 col-12">
                        @if (($overtimeDetail['duration_changed'] ?? false) === true)
                            <span class="text-gray text-decoration-line-through">{{ $overtimeDetail['planned_duration'] ?? '-' }}</span>
                            <span class="text-gray">, {{ $overtimeDetail['actual_duration'] ?? '-' }}</span>
                        @else
                            <span class="text-gray">{{ $overtimeDetail['planned_duration'] ?? '-' }}</span>
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
                        <span class="text-gray fw-semibold">Overtime Pay</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Rate Multiplier</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray fw-semibold">1.5x</span><br>
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
                        <span class="text-gray">{{ $overtimeDetail['verified_note'] ?? '-' }}</span><br>
                        <span class="text-gray">{{ $overtimeDetail['verified_datetime'] ?? '-' }}</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Approved by Supervisor</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray">{{ $overtimeDetail['supervisor_approver'] ?? '-' }}</span><br>
                        <span class="text-gray">{{ $overtimeDetail['supervisor_datetime'] ?? '-' }}</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Approved by Director</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray">{{ $overtimeDetail['director_approver'] ?? '-' }}</span><br>
                        <span class="text-gray">{{ $overtimeDetail['director_datetime'] ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="row">
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
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-info text-white flex-shrink-0" style="width: 51px; height: 51px;">
                                <i class="fa-solid fa-person-biking"></i>
                            </div>
                            <div>
                                <h6 class="fs-16 text-black mb-0">Monitoring the Extra Mile</h6>
                                <span class="fs-12">Verify the session to ensure staff overtime are recorded accurately for compensation.</span>
                            </div>
                        </div>
                        <div class="d-flex gap-3 justify-content-between flex-wrap p-4 pb-2">
                            <div class="text-center">
                                <p class="fs-14 mb-2">Start</p>
                                <span class="fs-20 text-black">{{ $overtimeDetail['verification_start_time'] ?? '-' }}</span>
                            </div>
                            <div class="text-center">
                                <p class="fs-14 mb-2">Duration</p>
                                <span class="fs-20 text-black">{{ $overtimeDetail['verification_duration'] ?? '-' }}</span>
                            </div>
                            <div class="text-center">
                                <p class="fs-14 mb-2">End</p>
                                <span class="fs-20 text-black">{{ $overtimeDetail['verification_end_time'] ?? '-' }}</span>
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
                                <label class="form-label">Scheduled Start</label>
                                <p class="fs-14 mb-0">{{ $overtimeDetail['planned_start_time'] ?? '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Scheduled End</label>
                                <p class="fs-14 mb-0">{{ $overtimeDetail['planned_end_time'] ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="row p-4 pb-2">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Approved Start <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" value="{{ $approvedStartValue }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Approved End <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" value="{{ $approvedEndValue }}" disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn light btn-success m-3 mb-2 btn-lg" disabled>Approve Overtime Session</button>
                    <div class="mb-3"></div>
                </div>
            </div>

            <div class="col-xxl-12 col-xl-12 col-md-12">
                <div class="card overflow-hidden">
                    <div class="card-header">
                        <h4 class="card-title">{{ $overtimeDetail['staff_name'] ?? '[Name]' }} Task Items</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush dz-draggable dropzoneContainer dz-scroll height400">
                            @forelse ($taskItems as $index => $taskItem)
                                <div class="list-group-item draggable p-3">
                                    <div class="d-flex justify-content-between flex-wrap">
                                        <div class="d-flex gap-3">
                                            <div class="draggable-handle">
                                                <svg width="9" height="16" viewBox="0 0 9 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    @for ($column = 0; $column < 3; $column++)
                                                        @for ($row = 0; $row < 6; $row++)
                                                            <rect x="{{ $column * 4 }}" y="{{ $row * 3 }}" width="1" height="1" fill="var(--bs-body-color)"></rect>
                                                        @endfor
                                                    @endfor
                                                </svg>
                                            </div>
                                            <div class="clearfix">
                                                <div class="form-check {{ ($taskItem['checked'] ?? false) ? 'custom-checkbox checkbox-success' : '' }}">
                                                    <input class="form-check-input" type="checkbox" id="adminOvertimeTask{{ $index }}" @checked($taskItem['checked'] ?? false) disabled>
                                                    <label class="form-check-label text-black" for="adminOvertimeTask{{ $index }}">{{ $taskItem['title'] ?? 'Untitled Task' }}</label>
                                                </div>
                                                <span>{{ $taskItem['date_label'] ?? '-' }}</span>
                                            </div>
                                        </div>
                                        <div class="clearfix">
                                            <button type="button" class="btn btn-square btn-danger light btn-sm" disabled>
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                            <button type="button" class="btn btn-square btn-primary light btn-sm ms-1" disabled>
                                                <i class="fa fa-pen"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="list-group-item p-4 text-center text-muted">
                                    No task items available for this overtime.
                                </div>
                            @endforelse
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
