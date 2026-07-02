<div class="row leave-balance-mobile-slider">
    <div class="col-md-2 col-sm-6 leave-balance-mobile-slide">
        <div class="card overflow-hidden avtivity-card">
            <div class="card-body">
                <div class="d-flex gap-md-4 gap-3 align-items-center">
                    <div>
                        <p class="fs-14 mb-2">Leave Balance ({{ $leaveSummaryYear }})</p>
                        <span class="title text-success fs-28 fw-semibold">{{ $leaveEligibility['available_balance_label'] ?? '0 Days' }}</span>
                    </div>
                </div>
                <div>
                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                        <div class="progress-bar rounded bg-info" style="width: 0%; height:5px;" aria-label="Progess-info" role="progressbar">
                            <span class="sr-only">100% Complete</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="effect bg-success"></div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6 leave-balance-mobile-slide">
        <div class="card overflow-hidden avtivity-card">
            <div class="card-body">
                <div class="d-flex gap-md-4 gap-3 align-items-center">
                    <div>
                        <p class="fs-14 mb-2">Joint Holiday ({{ $leaveSummaryYear }})</p>
                        <span class="title text-black fs-28 fw-semibold">{{ $leaveEligibility['joint_holiday_label'] ?? '0 / 0 Days' }}</span>
                    </div>
                </div>
                <div>
                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                        <div class="progress-bar rounded bg-info" style="width: 0%; height:5px;" aria-label="Progess-info" role="progressbar">
                            <span class="sr-only">100% Complete</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="effect bg-secondary"></div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6 leave-balance-mobile-slide">
        <div class="card overflow-hidden avtivity-card">
            <div class="card-body">
                <div class="d-flex gap-md-4 gap-3 align-items-center">
                    <div>
                        <p class="fs-14 mb-2">Annual Leave ({{ $leaveSummaryYear }})</p>
                        <span class="title text-black fs-28 fw-semibold">{{ $leaveTracker['annual_leave_taken_label'] ?? '0 Days' }}</span>
                    </div>
                </div>
                <div>
                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                        <div class="progress-bar bg-success position-absolute rounded bootom-0" style="width: 0%; height:5px;" aria-label="Progess-success" role="progressbar">
                            <span class="sr-only">95% Complete</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="effect bg-primary"></div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6 leave-balance-mobile-slide">
        <div class="card overflow-hidden avtivity-card">
            <div class="card-body">
                <div class="d-flex gap-md-4 gap-3 align-items-center">
                    <div>
                        <p class="fs-14 mb-2">Sick Leave ({{ $leaveSummaryYear }})</p>
                        <span class="title text-black fs-28 fw-semibold">{{ $leaveTracker['sick_leave_taken_label'] ?? '0 Days' }}</span>
                    </div>
                </div>
                <div>
                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                        <div class="progress-bar rounded bg-secondary" style="width: 0%; height:5px;" aria-label="Progess-secondary" role="progressbar">
                            <span class="sr-only">10%</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="effect bg-warning"></div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6 leave-balance-mobile-slide">
        <div class="card overflow-hidden avtivity-card">
            <div class="card-body">
                <div class="d-flex gap-md-4 gap-3 align-items-center">
                    <div>
                        <p class="fs-14 mb-2">Special Leave ({{ $leaveSummaryYear }})</p>
                        <span class="title text-black fs-28 fw-semibold">{{ $leaveTracker['special_leave_taken_label'] ?? '0 Days' }}</span>
                    </div>
                </div>
                <div>
                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                        <div class="progress-bar rounded bg-danger" style="width: 0%; height:5px;" aria-label="Progess-danger" role="progressbar">
                            <span class="sr-only">0% Complete</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="effect bg-info"></div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6 leave-balance-mobile-slide">
        <div class="card overflow-hidden avtivity-card">
            <div class="card-body">
                <div class="d-flex gap-md-4 gap-3 align-items-center">
                    <div>
                        <p class="fs-14 mb-2">Unpaid Leave ({{ $leaveSummaryYear }})</p>
                        <span class="title text-black fs-28 fw-semibold">{{ $leaveTracker['unpaid_leave_taken_label'] ?? '0 Days' }}</span>
                    </div>
                </div>
                <div>
                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                        <div class="progress-bar rounded bg-danger" style="width: 0%; height:5px;" aria-label="Progess-danger" role="progressbar">
                            <span class="sr-only">0% Complete</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="effect bg-danger"></div>
        </div>
    </div>
</div>
