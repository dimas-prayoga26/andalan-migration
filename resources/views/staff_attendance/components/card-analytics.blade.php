@php
    $attendanceRatePercent = (int) ($profileAttendanceRatePercent ?? 0);
    $onTimeRatePercent = (int) ($profileOnTimeRatePercent ?? 0);
    $latenessRatePercent = (int) ($profileLatenessRatePercent ?? 0);
    $overtimeRatePercent = (int) ($profileOvertimeRatePercent ?? 0);
@endphp

<div class="row attendance-rate-mobile-slider">
    <div class="col-md-3 col-sm-6 attendance-rate-mobile-slide">
        <div class="card overflow-hidden avtivity-card">
            <div class="card-body">
                <div class="d-flex gap-md-4 gap-3 align-items-center">
                    <span class="avatar avatar-lg avatar-secondary rounded-circle border-0">
                        <i class="fa-solid fa-clipboard-check fs-24"></i>
                    </span>
                    <div>
                        <p class="fs-14 mb-2">Attendance Rate</p>
                        <span class="title text-black fs-28 fw-semibold">{{ $attendanceRatePercent }}%</span>
                    </div>
                </div>
                <div>
                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                        <div class="progress-bar rounded bg-secondary" style="width: {{ $attendanceRatePercent }}%; height:5px;" aria-label="Progress attendance rate" role="progressbar">
                            <span class="sr-only">{{ $attendanceRatePercent }}% Complete</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="effect bg-secondary"></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 attendance-rate-mobile-slide">
        <div class="card overflow-hidden avtivity-card">
            <div class="card-body">
                <div class="d-flex gap-md-4 gap-3 align-items-center">
                    <span class="avatar avatar-lg avatar-success rounded-circle border-0">
                        <i class="fa-solid fa-user-check fs-24"></i>
                    </span>
                    <div>
                        <p class="fs-14 mb-2">On Time Rate</p>
                        <span class="title text-black fs-28 fw-semibold">{{ $onTimeRatePercent }}%</span>
                    </div>
                </div>
                <div>
                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                        <div class="progress-bar bg-success position-absolute rounded bottom-0" style="width: {{ $onTimeRatePercent }}%; height:5px;" aria-label="Progress on time rate" role="progressbar">
                            <span class="sr-only">{{ $onTimeRatePercent }}% Complete</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="effect bg-success"></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 attendance-rate-mobile-slide">
        <div class="card overflow-hidden avtivity-card">
            <div class="card-body">
                <div class="d-flex gap-md-4 gap-3 align-items-center">
                    <span class="avatar avatar-lg avatar-danger rounded-circle border-0">
                        <i class="fa-solid fa-clock fs-24"></i>
                    </span>
                    <div>
                        <p class="fs-14 mb-2">Lateness Rate</p>
                        <span class="title text-black fs-28 fw-semibold">{{ $latenessRatePercent }}%</span>
                    </div>
                </div>
                <div>
                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                        <div class="progress-bar rounded bg-danger" style="width: {{ $latenessRatePercent }}%; height:5px;" aria-label="Progress lateness rate" role="progressbar">
                            <span class="sr-only">{{ $latenessRatePercent }}% Complete</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="effect bg-danger"></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 attendance-rate-mobile-slide">
        <div class="card overflow-hidden avtivity-card">
            <div class="card-body">
                <div class="d-flex gap-md-4 gap-3 align-items-center">
                    <span class="avatar avatar-lg avatar-info rounded-circle border-0">
                        <i class="fa-solid fa-business-time fs-24"></i>
                    </span>
                    <div>
                        <p class="fs-14 mb-2">Overtime Rate</p>
                        <span class="title text-black fs-28 fw-semibold">{{ $overtimeRatePercent }}%</span>
                    </div>
                </div>
                <div>
                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                        <div class="progress-bar rounded bg-info" style="width: {{ $overtimeRatePercent }}%; height:5px;" aria-label="Progress overtime rate" role="progressbar">
                            <span class="sr-only">{{ $overtimeRatePercent }}% Complete</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="effect bg-info"></div>
        </div>
    </div>
</div>
