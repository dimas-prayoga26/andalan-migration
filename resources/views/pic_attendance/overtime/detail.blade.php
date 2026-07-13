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

@include('pic_attendance.layout.navbar')

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
                                <span class="text-gray">{{ $overtimeDetail['planned_time_range'] ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Staff submitted <span class="text-muted">(Total Duration)</span></span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray">
                                    {{ $overtimeDetail['staff_submitted_time_range'] ?? '-' }}
                                    @if (($overtimeDetail['staff_submitted_duration'] ?? '-') !== '-')
                                        ({{ $overtimeDetail['staff_submitted_duration'] }})
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Total Duration</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray">{{ $overtimeDetail['planned_duration'] ?? '-' }}</span>
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
                                <span class="text-gray">{{ $overtimeDetail['verified_datetime'] ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Approved by Supervisor</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray">{{ $overtimeDetail['supervisor_approver'] ?? '-' }}</span> <br>
                                <span class="text-gray">{{ $overtimeDetail['supervisor_datetime'] ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Approved by Director</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray">{{ $overtimeDetail['director_approver'] ?? '-' }}</span> <br>
                                <span class="text-gray">{{ $overtimeDetail['director_datetime'] ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="row sticky-top z-0">
            @php
                $verificationReady = (bool) ($overtimeDetail['verification_ready'] ?? false);
                $isTaskHoursVerified = (bool) ($overtimeDetail['is_task_hours_verified'] ?? false);
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
                    <form method="POST" action="{{ route('pic-attendance.overtime.verify-session', ['uid' => $overtime->id]) }}">
                        @csrf
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
                                        <p class="fs-14 mb-0">{{ $overtimeDetail['planned_start_time'] ?? '18:00' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="">
                                        <label class="form-label">Scheduled End</label>
                                        <p class="fs-14 mb-0">{{ $overtimeDetail['planned_end_time'] ?? '20:00' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row p-4 pb-2">
                                <div class="col-md-6">
                                    <div class=" mb-3">
                                        <label class="form-label">Approved Start <span class="text-danger">*</span></label>
                                        <input type="time" name="approved_start_time" class="form-control" value="{{ old('approved_start_time', $approvedStartValue) }}" @disabled(! $verificationReady || $isTaskHoursVerified)>
                                        @error('approved_start_time', 'picOvertimeVerify')
                                            <span class="text-danger fs-12">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class=" mb-3">
                                        <label class="form-label">Approved End <span class="text-danger">*</span></label>
                                        <input type="time" name="approved_end_time" class="form-control" value="{{ old('approved_end_time', $approvedEndValue) }}" @disabled(! $verificationReady || $isTaskHoursVerified)>
                                        @error('approved_end_time', 'picOvertimeVerify')
                                            <span class="text-danger fs-12">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="px-3 pb-3">
                            <button type="submit" class="btn light btn-success btn-lg w-100" @disabled(! $verificationReady || $isTaskHoursVerified)>
                                Approve Overtime Session
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Start - To Do -->
            @php
                $taskCollections = $overtimeTaskItems ?? ['finished' => collect(), 'pending' => collect()];
                $displayTaskItems = collect($taskCollections['finished'] ?? collect())
                    ->merge($taskCollections['pending'] ?? collect())
                    ->values();
                $taskItemPayload = $displayTaskItems->keyBy('id')->toArray();
            @endphp
            <div class="col-xxl-12 col-xl-12 col-md-12">
                <div class="card overflow-hidden">
                    <div class="card-header">
                        <h4 class="card-title"><span class="fw-bold">{{ $overtimeDetail['staff_name'] ?? '[Name]' }}</span> Task Items</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush dz-draggable dropzoneContainer dz-scroll height400">
                            @forelse ($displayTaskItems as $taskItem)
                                <div class="list-group-item draggable p-3 pic-overtime-task-detail" role="button" tabindex="0" data-bs-toggle="modal" data-bs-target="#taskDetailModal" data-task-id="{{ $taskItem['id'] ?? '' }}">
                                    <div class="d-flex justify-content-between flex-wrap">
                                        <div class="d-flex gap-3">
                                            <div class="draggable-handle">
                                                <svg width="9" height="16" viewBox="0 0 9 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    @for ($rectIndex = 0; $rectIndex < 18; $rectIndex++)
                                                        <rect x="{{ intdiv($rectIndex, 6) * 4 }}" y="{{ ($rectIndex % 6) * 3 }}" width="1" height="1" fill="var(--bs-body-color)"/>
                                                    @endfor
                                                </svg>
                                            </div>
                                            <div class="clearfix">
                                                <div class="form-check @if ($taskItem['checked'] ?? false) custom-checkbox checkbox-success @endif">
                                                    <input class="form-check-input" type="checkbox" id="picOvertimeTask{{ $taskItem['id'] ?? '' }}" @checked($taskItem['checked'] ?? false) disabled>
                                                    <label class="form-check-label text-black" for="picOvertimeTask{{ $taskItem['id'] ?? '' }}">{{ $taskItem['title'] ?? '-' }}</label>
                                                </div>
                                                <span>{{ $taskItem['date_label'] ?? '-' }}</span>
                                            </div>
                                        </div>
                                        <div class="clearfix">
                                            <button type="button" class="btn btn-square btn-danger light btn-sm" disabled aria-label="Delete task">
                                                <i class="fa-regular fa-trash-can "></i>
                                            </button>
                                            <button type="button" class="btn btn-square btn-primary light btn-sm ms-1 pic-overtime-task-edit" data-bs-toggle="modal" data-bs-target="#updateTaskModal" data-task-id="{{ $taskItem['id'] ?? '' }}" data-task-update-url="{{ $taskItem['update_url'] ?? '#' }}" aria-label="Edit task">
                                                <i class="fa fa-pen "></i>
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
            <!-- End - To Do -->
        </div>
    </div>
</div>

<div class="modal fade" id="taskDetailModal" tabindex="-1" aria-labelledby="taskDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskDetailModalLabel">Task Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row py-2">
                    <div class="col-4"><span>Task Name</span></div>
                    <div class="col-8"><span class="text-gray fw-semibold" id="picTaskDetailTitle">-</span></div>
                </div>
                <div class="row py-2">
                    <div class="col-4"><span>Task Description</span></div>
                    <div class="col-8"><span class="text-gray fw-normal" id="picTaskDetailDescription">-</span></div>
                </div>
                <div class="row py-2">
                    <div class="col-4"><span>Date - Due Date</span></div>
                    <div class="col-8"><span class="text-gray fw-semibold" id="picTaskDetailDate">-</span></div>
                </div>
                <div class="row py-2">
                    <div class="col-4"><span>Attachment</span></div>
                    <div class="col-8"><span class="text-gray fw-semibold" id="picTaskDetailAttachment">No attachment</span></div>
                </div>
                <div class="row py-2">
                    <div class="col-4"><span>Blockers</span></div>
                    <div class="col-8"><span class="text-gray fw-normal" id="picTaskDetailBlockers">-</span></div>
                </div>
                <div class="row py-2">
                    <div class="col-4"><span>Task Category</span></div>
                    <div class="col-8">
                        <span class="text-gray fw-semibold" id="picTaskDetailCategory">-</span><br>
                        <span class="text-gray fw-normal" id="picTaskDetailProject">-</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-4"><span>Assigned by</span></div>
                    <div class="col-8"><span class="text-primary fw-semibold" id="picTaskDetailAssignedBy">-</span></div>
                </div>
                <div class="row py-2">
                    <div class="col-4"><span>Task Status</span></div>
                    <div class="col-8"><span class="fw-semibold" id="picTaskDetailStatus">-</span></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="updateTaskModal" tabindex="-1" aria-labelledby="updateTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form class="comment-form" id="picUpdateTaskForm" action="" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="updateTaskModalLabel">Update a Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Task Name <span class="required text-danger">*</span></label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Task Description</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" name="start_date">
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Due Date</label>
                                <input type="date" class="form-control" name="due_date">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Volume Workload</label>
                                <select class="form-control default-select" name="priority" id="picUpdateTaskPrioritySelect">
                                    <option value="low">Light</option>
                                    <option value="medium">Moderate</option>
                                    <option value="high">Heavy</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Attachment</label>
                                <input type="text" class="form-control" name="attachment_path">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Blockers</label>
                                <input type="text" class="form-control" name="blockers">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Task Status <span class="required text-danger">*</span></label>
                                <select class="form-control default-select" name="status" id="picUpdateTaskStatusSelect" required>
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
                    <button type="submit" class="btn btn-warning" id="picUpdateTaskSubmit">Save changes</button>
                </div>
            </form>
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
    <script>
        (function ($) {
            var taskItemsById = @js($taskItemPayload ?? []);

            function setSelectValue(selectElement, value) {
                if ($.fn.selectpicker && selectElement.data('selectpicker')) {
                    selectElement.selectpicker('val', value);
                    return;
                }

                selectElement.val(value);
            }

            function showTaskAlert(iconType, titleText, messageText) {
                if (typeof Swal !== 'undefined' && Swal && typeof Swal.fire === 'function') {
                    Swal.fire({
                        icon: iconType,
                        title: titleText,
                        text: messageText,
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                window.alert(messageText);
            }

            function resolveAjaxErrorMessage(xhr, fallbackMessage) {
                if (xhr && xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        return xhr.responseJSON.message;
                    }

                    if (xhr.responseJSON.errors) {
                        var firstErrorKey = Object.keys(xhr.responseJSON.errors)[0];
                        if (firstErrorKey && xhr.responseJSON.errors[firstErrorKey][0]) {
                            return xhr.responseJSON.errors[firstErrorKey][0];
                        }
                    }
                }

                return fallbackMessage;
            }

            function nullableTaskText(value) {
                return value ? value : '-';
            }

            function nullableTaskValue(value) {
                if (value === null || typeof value === 'undefined') {
                    return '';
                }

                return String(value).trim();
            }

            function renderTaskAttachment(selector, attachmentPath) {
                var attachmentValue = nullableTaskValue(attachmentPath);
                var attachmentElement = $(selector);

                if (attachmentValue === '') {
                    attachmentElement.text('No attachment');
                    return;
                }

                attachmentElement
                    .empty()
                    .append($('<a>', {
                        href: attachmentValue,
                        class: 'text-primary',
                        target: '_blank',
                        rel: 'noopener noreferrer',
                        text: 'Open attachment'
                    }));
            }

            function taskStatusLabel(value, isChecked) {
                if (isChecked) {
                    return 'Completed';
                }

                switch ((value || '').toString().toLowerCase()) {
                    case 'in_progress':
                        return 'On Progress';
                    case 'completed':
                        return 'Completed';
                    case 'cancelled':
                        return 'Cancelled';
                    default:
                        return 'To Do';
                }
            }

            function taskPriorityLabel(value) {
                switch ((value || '').toString().toLowerCase()) {
                    case 'high':
                        return 'High';
                    case 'low':
                        return 'Low';
                    default:
                        return 'Medium';
                }
            }

            function openTaskDetailModal(event) {
                if ($(event.target).closest('button, a, input').length) {
                    return;
                }

                var trigger = $(event.currentTarget);
                var taskId = trigger.data('task-id');
                var taskItem = taskItemsById[taskId];

                if (!taskItem) {
                    return;
                }

                $('#picTaskDetailTitle').text(nullableTaskText(taskItem.title));
                $('#picTaskDetailDescription').text(nullableTaskText(taskItem.description));
                $('#picTaskDetailDate').text(nullableTaskText(taskItem.date_range_label));
                $('#picTaskDetailBlockers').text(nullableTaskText(taskItem.blockers));
                $('#picTaskDetailCategory').text(nullableTaskText(taskItem.task_category_label));
                $('#picTaskDetailProject').text(nullableTaskText(taskItem.project_name));
                $('#picTaskDetailAssignedBy').text('@' + (nullableTaskValue(taskItem.assigned_by) || 'self'));
                $('#picTaskDetailStatus')
                    .removeClass('text-danger text-success text-warning')
                    .addClass(taskItem.status_class || 'text-warning')
                    .text(nullableTaskValue(taskItem.status_label) || taskStatusLabel(taskItem.status_value, taskItem.checked === true));
                renderTaskAttachment('#picTaskDetailAttachment', taskItem.attachment_path);
            }

            function openTaskDetailModalFromKeyboard(event) {
                if (event.key !== 'Enter' && event.key !== ' ') {
                    return;
                }

                event.preventDefault();
                $(event.currentTarget).trigger('click');
            }

            function openUpdateTaskModal(event) {
                event.stopPropagation();

                var trigger = $(event.currentTarget);
                var taskId = trigger.data('task-id');
                var taskItem = taskItemsById[taskId];
                var form = $('#picUpdateTaskForm');

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
                setSelectValue($('#picUpdateTaskPrioritySelect'), taskItem.priority || 'medium');
                setSelectValue($('#picUpdateTaskStatusSelect'), taskItem.status_value || 'pending');
                $('#picUpdateTaskSubmit').prop('disabled', false);
            }

            function submitUpdateTaskForm(event) {
                event.preventDefault();

                var form = $(this);
                var submitButton = $('#picUpdateTaskSubmit');
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
                        submitButton.prop('disabled', false);
                        showTaskAlert('error', 'Gagal', resolveAjaxErrorMessage(xhr, 'Task gagal diperbarui.'));
                    }
                });
            }

            $('.pic-overtime-task-detail').on('click', openTaskDetailModal);
            $('.pic-overtime-task-detail').on('keydown', openTaskDetailModalFromKeyboard);
            $('.pic-overtime-task-edit').on('click', openUpdateTaskModal);
            $('#picUpdateTaskForm').on('submit', submitUpdateTaskForm);
            $('#picUpdateTaskForm input[name="start_date"]').on('change', function () {
                var startDate = $(this).val();
                var dueDateInput = $('#picUpdateTaskForm input[name="due_date"]');

                dueDateInput.attr('min', startDate);
                if (dueDateInput.val() && startDate && dueDateInput.val() < startDate) {
                    dueDateInput.val(startDate);
                }
            });
            $('#updateTaskModal').on('hidden.bs.modal', function () {
                var form = $('#picUpdateTaskForm')[0];
                if (form) {
                    form.reset();
                }

                $('#picUpdateTaskForm').attr('action', '');
                setSelectValue($('#picUpdateTaskPrioritySelect'), 'medium');
                setSelectValue($('#picUpdateTaskStatusSelect'), 'pending');
                $('#picUpdateTaskSubmit').prop('disabled', false);
            });
        })(jQuery);
    </script>
@endsection
