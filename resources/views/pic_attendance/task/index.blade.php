@extends('layouts.main')

@section('title', 'PIC Task')

@section('navbarTitle', 'Task Tracker')

@section('content')
    @php
        $picTaskStaffOptions = collect($picTaskStaffOptions ?? []);
        // dd($picTaskStaffOptions);
        $picTaskRows = collect($picTaskRows ?? []);
        $picTaskSummary = $picTaskSummary ?? [
            'completion_rate' => 0,
            'total_tasks' => 0,
            'in_progress_tasks' => 0,
            'completed_tasks' => 0,
            'total_progress' => 0,
            'in_progress_progress' => 0,
            'completed_progress' => 0,
        ];
        $picTaskMonthOptions = $picTaskMonthOptions ?? [];
        $picTaskYearOptions = $picTaskYearOptions ?? [];
        $picTaskSelectedMonth = (int) ($picTaskSelectedMonth ?? now('Asia/Jakarta')->month);
        $picTaskSelectedYear = (int) ($picTaskSelectedYear ?? now('Asia/Jakarta')->year);
        $tasksByStaff = $picTaskRows->groupBy('staff_id');
    @endphp

    <div class="row">
        <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center d-block mb-4">
            <div>
                <h4 class="fw-bold text-black">Task Management</h4>
                <p class="fs-13 mb-0 text-black">A comprehensive list of all tasks performed by the team.</p>
            </div>
            <div class="d-flex gap-3">
                <a class="btn btn-lg btn-primary fw-bold rounded" data-bs-toggle="modal" data-bs-target="#picTaskPeriodFilterModal">
                    <i class="fa-regular fa-calendar-days me-2"></i>{{ $picTaskMonthLabel ?? now('Asia/Jakarta')->format('F Y') }}
                </a>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card overflow-hidden avtivity-card">
                <div class="card-body">
                    <div class="d-flex gap-md-4 gap-3 align-items-center">
                        <span class="avatar avatar-lg avatar-secondary rounded-circle border-0">
                            <i class="fa-solid fa-chart-line fs-28 text-secondary"></i>
                        </span>
                        <div>
                            <p class="fs-14 mb-2">Task Completion Rate</p>
                            <span class="title text-black fs-28 fw-semibold">{{ $picTaskSummary['completion_rate'] }}%</span>
                        </div>
                    </div>
                    <div>
                        <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                            <div class="progress-bar rounded bg-secondary" style="width: {{ $picTaskSummary['completion_rate'] }}%; height:5px;" aria-label="Progess-secondary" role="progressbar">
                                <span class="sr-only">{{ $picTaskSummary['completion_rate'] }}% Complete</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="effect bg-secondary"></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card overflow-hidden avtivity-card">
                <div class="card-body">
                    <div class="d-flex gap-md-4 gap-3 align-items-center">
                        <span class="avatar avatar-lg avatar-danger rounded-circle border-0">
                            <i class="fa-solid fa-list-check fs-28 text-danger"></i>
                        </span>
                        <div>
                            <p class="fs-14 mb-2">Total Tasks</p>
                            <span class="title text-black fs-28 fw-semibold">{{ $picTaskSummary['total_tasks'] }} Tasks</span>
                        </div>
                    </div>
                    <div>
                        <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                            <div class="progress-bar rounded bg-danger" style="width: {{ $picTaskSummary['total_progress'] }}%; height:5px;" aria-label="Progess-danger" role="progressbar">
                                <span class="sr-only">{{ $picTaskSummary['total_progress'] }}% Complete</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="effect bg-danger"></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card overflow-hidden avtivity-card">
                <div class="card-body">
                    <div class="d-flex gap-md-4 gap-3 align-items-center">
                        <span class="avatar avatar-lg avatar-info rounded-circle border-0">
                            <i class="fa-solid fa-hourglass-half fs-28 text-info"></i>
                        </span>
                        <div>
                            <p class="fs-14 mb-2">In Progress</p>
                            <span class="title text-black fs-28 fw-semibold">{{ $picTaskSummary['in_progress_tasks'] }} Tasks</span>
                        </div>
                    </div>
                    <div>
                        <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                            <div class="progress-bar rounded bg-info" style="width: {{ $picTaskSummary['in_progress_progress'] }}%; height:5px;" aria-label="Progess-info" role="progressbar">
                                <span class="sr-only">{{ $picTaskSummary['in_progress_progress'] }}% Complete</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="effect bg-info"></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card overflow-hidden avtivity-card">
                <div class="card-body">
                    <div class="d-flex gap-md-4 gap-3 align-items-center">
                        <span class="avatar avatar-lg avatar-success rounded-circle border-0">
                            <i class="fa-solid fa-circle-check fs-28 text-success"></i>
                        </span>
                        <div>
                            <p class="fs-14 mb-2">Completed</p>
                            <span class="title text-black fs-28 fw-semibold">{{ $picTaskSummary['completed_tasks'] }} Tasks</span>
                        </div>
                    </div>
                    <div>
                        <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                            <div class="progress-bar bg-success position-absolute rounded bootom-0" style="width: {{ $picTaskSummary['completed_progress'] }}%; height:5px;" aria-label="Progess-success" role="progressbar">
                                <span class="sr-only">{{ $picTaskSummary['completed_progress'] }}% Complete</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="effect bg-success"></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-block pb-0 border-0">
            <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center d-block mb-4">
                <div>
                    <h4 class="card-title">Team Task List</h4>
                    <p class="fs-13 mb-0">A comprehensive list of all tasks performed by the team.</p>
                </div>
            </div>

            <ul class="nav nav-pills menu-pill nav-pills-all gap-2" id="picTaskTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link fs-16 active" data-bs-toggle="pill" data-bs-target="#pic-task-all" type="button" role="tab" aria-controls="pic-task-all" aria-selected="true">All Tasks</button>
                </li>
                @foreach ($picTaskStaffOptions as $staffIndex => $staffOption)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fs-16" data-bs-toggle="pill" data-bs-target="#pic-task-staff-{{ $staffIndex }}" type="button" role="tab" aria-controls="pic-task-staff-{{ $staffIndex }}" aria-selected="false" tabindex="-1">{{ $staffOption['name'] }}</button>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="picTaskTabContent">
                <div class="tab-pane fade show active" id="pic-task-all" role="tabpanel" tabindex="0">
                    <div class="card-body loadmore-content dz-scroll height750" id="PicTaskAllContent">
                        @forelse ($picTaskRows as $taskRow)
                            @include('pic_attendance.task.partials.task-card', ['taskRow' => $taskRow])
                        @empty
                            <div class="text-center py-4 text-muted">No task data available.</div>
                        @endforelse
                    </div>
                    <div class="position-absolute top-100 start-50 translate-middle">
                        <a class="avatar avatar-sm border-light dz-load-more text-bg-light rounded-circle fa fa-chevron-down text-primary shadow" aria-label="Featured-icon"></a>
                    </div>
                </div>
                @foreach ($picTaskStaffOptions as $staffIndex => $staffOption)
                    <div class="tab-pane fade" id="pic-task-staff-{{ $staffIndex }}" role="tabpanel" tabindex="0">
                        <div class="card-body loadmore-content dz-scroll height750">
                            @forelse ($tasksByStaff->get($staffOption['id'], collect()) as $taskRow)
                                @include('pic_attendance.task.partials.task-card', ['taskRow' => $taskRow])
                            @empty
                                <div class="text-center py-4 text-muted">No task data available.</div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="modal fade" id="picTaskPeriodFilterModal" tabindex="-1" aria-labelledby="picTaskPeriodFilterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="GET" action="{{ route('pic-attendance.task') }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="picTaskPeriodFilterModalLabel">Filter Period</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-sm-7">
                            <label for="picTaskMonthFilter" class="form-label">Month</label>
                            <select id="picTaskMonthFilter" name="month" class="form-select">
                                @foreach ($picTaskMonthOptions as $monthValue => $monthLabel)
                                    <option value="{{ $monthValue }}" @selected((int) $monthValue === $picTaskSelectedMonth)>{{ $monthLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-5">
                            <label for="picTaskYearFilter" class="form-label">Year</label>
                            <select id="picTaskYearFilter" name="year" class="form-select">
                                @foreach ($picTaskYearOptions as $yearOption)
                                    <option value="{{ $yearOption }}" @selected((int) $yearOption === $picTaskSelectedYear)>{{ $yearOption }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Apply</button>
                </div>
            </form>
        </div>
    </div>
@endsection
