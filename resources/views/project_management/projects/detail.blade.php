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
<link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css') }}">
<style>
    .project-detail-overview-row,
    .project-department-row {
        --bs-gutter-x: 1.5rem;
        --bs-gutter-y: 1.5rem;
    }

    .project-detail-card {
        border: 0;
        border-radius: var(--bs-border-radius-xl, 1rem);
        box-shadow: 0 5px 18px rgba(20, 24, 40, .05);
        overflow: hidden;
    }

    .project-detail-card .card-header {
        align-items: flex-start;
        padding: 24px 24px 0;
    }

    .project-detail-card .card-body {
        padding: 20px 16px 24px;
    }

    .project-detail-folder {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .project-folder-avatar {
        width: 44px;
        height: 44px;
        border: 1px solid #e8ecf4;
        background: #fff;
    }

    .project-card-command {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }

    .project-meta-list {
        margin-top: 24px;
    }

    .project-meta-item {
        margin-bottom: 14px;
        font-size: 13px;
    }

    .project-meta-label {
        color: #7d8490;
    }

    .project-meta-value {
        color: #111827;
        font-weight: 600;
    }

    .project-summary-content {
        align-items: center;
    }

    .project-summary-chart {
        min-height: 250px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .project-summary-chart .apexcharts-canvas {
        margin: 0 auto;
    }

    .project-summary-legend {
        font-size: 13px;
    }

    .project-summary-legend-item {
        margin-bottom: 16px;
        color: #111827;
    }

    .project-summary-legend-color {
        width: 10px;
        height: 10px;
        border-radius: 2px;
    }

    .project-team-stack {
        display: flex;
        align-items: center;
        min-height: 34px;
        padding-left: 2px;
    }

    .project-team-avatar {
        width: 34px;
        height: 34px;
        border: 2px solid #fff;
        border-radius: 50%;
        box-shadow: 0 4px 12px rgba(20, 24, 40, .12);
        margin-left: -8px;
        overflow: hidden;
        background: #eef3ff;
        color: #2445c7;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        line-height: 1;
    }

    .project-team-avatar:first-child {
        margin-left: 0;
    }

    .project-team-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .project-team-more {
        background: #2445c7;
        color: #fff;
    }

    .project-tasks-over-time-chart {
        min-height: 280px;
        position: relative;
    }

    .project-tasks-over-time-chart .apexcharts-canvas {
        margin: 0 auto;
    }

    .project-tasks-over-time-card .card-title {
        white-space: nowrap;
    }

    .project-chart-series {
        display: inline-flex;
        align-items: center;
        color: #7d8490;
        font-size: 13px;
        gap: 4px;
    }

    .project-chart-series + .project-chart-series {
        margin-left: 14px;
    }

    .project-chart-filter {
        width: 142px !important;
        min-width: 142px;
        max-width: 142px;
        flex: 0 0 142px;
        border-color: #e8ecf4;
        border-radius: 12px;
        color: #6b7280;
        font-size: 13px;
    }

    .project-department-card {
        border: 0;
        border-radius: var(--bs-border-radius-xl, 1rem);
        box-shadow: 0 5px 18px rgba(20, 24, 40, .05);
        overflow: hidden;
    }

    .project-department-card .card-header {
        align-items: flex-start;
        padding: 24px 24px 0;
    }

    .project-department-card .card-body {
        max-height: 330px;
        overflow-y: auto;
        padding: 18px 24px 24px;
    }

    .project-department-task-title {
        max-width: min(245px, 46vw);
    }

    .project-department-progress {
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 16px;
    }

    .project-department-add-task {
        appearance: none;
        background: transparent;
        border: 0;
        color: #2bc155;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.4;
        padding: 0;
        white-space: nowrap;
    }

    .project-department-add-task:hover,
    .project-department-add-task:focus {
        color: #22a447;
        text-decoration: none;
    }

    .project-department-task {
        min-height: 46px;
    }

    .project-department-task .timeline-vr-badge {
        width: 3px;
        height: 34px;
        border-radius: 8px;
        flex: 0 0 3px;
    }

    .project-task-check-space {
        width: 22px;
        flex: 0 0 22px;
    }

    .project-task-action,
    .project-department-view-all {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }

    .project-department-view-all {
        width: auto;
        min-width: 86px;
        padding-inline: 16px;
        color: #000;
        font-weight: 600;
    }

    .project-department-view-all:disabled {
        cursor: not-allowed;
        opacity: .55;
        pointer-events: none;
    }

    .project-task-toggle:disabled {
        opacity: .35;
    }

    .project-task-menu .dropdown-menu {
        min-width: 140px;
    }

    .project-department-actions {
        gap: 8px;
    }

    @media (max-width: 575.98px) {
        .project-department-task-title {
            max-width: 170px;
        }

        .project-chart-filter {
            width: 100% !important;
            max-width: 180px;
            flex-basis: 180px;
        }
    }
</style>

@endsection

@section('content')

@include('layouts.breadcrumb', [
    'title' => 'Project Management',
    'current' => 'Project Detail',
    'homeRoute' => 'dashboard',
])

@include('project_management.layouts.profile-index')

@php
    $projectDetail = $projectDetail ?? [];
    $projectSummary = $projectSummary ?? [];
    $projectTaskTimeline = $projectTaskTimeline ?? [];
    $projectDepartmentGroups = collect($projectDepartmentGroups ?? []);
    $projectTeamMembers = collect($projectDetail['team_members'] ?? []);
    $departmentPalette = ['#2445c7', '#ff8a00', '#2bc155', '#ff5b8a', '#ffb800', '#8b5cf6'];
    $summaryChartLabels = $projectDepartmentGroups
        ->map(fn ($departmentGroup): string => (string) ($departmentGroup['name'] ?? '-'))
        ->values();
    $summaryChartSeries = $projectDepartmentGroups
        ->map(fn ($departmentGroup): int => (int) ($departmentGroup['total_tasks'] ?? 0))
        ->values();
@endphp

<div class="tab-content" id="tabContentMyProfileBottom">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Projects</h5>
        <a href="{{ route('project_management.projects') }}" class="btn btn-sm btn-light">Back to Projects</a>
    </div>

    <div class="row project-detail-overview-row mb-4">
        <div class="col-xxl-4 col-lg-6">
            <div class="card project-detail-card">
                <div class="card-header pb-0 border-0">
                    <div class="clearfix d-flex">
                        <div class="avatar avatar-sm project-folder-avatar rounded me-3 p-2 d-flex align-items-center justify-content-center">
                            <img src="{{ $projectDetail['image_url'] ?? asset('assets/images/files/folder.avif') }}" class="project-detail-folder" alt="">
                        </div>
                        <div class="clearfix">
                            <h4 class="mb-0 fw-semibold">{{ $projectDetail['name'] ?? '-' }}</h4>
                            <span class="small">{{ $projectDetail['subtitle'] ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="clearfix ms-auto">
                        <button type="button" class="btn btn-sm btn-light project-card-command" aria-label="Project actions"><i class="bi bi-grid"></i></button>
                    </div>
                </div>
                <div class="card-body px-3">
                    <div class="project-meta-list">
                        <div class="row ps-3 project-meta-item">
                            <div class="col-md-6 col-12">
                                <span class="d-block project-meta-label">Status Badge</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="d-block project-meta-value text-{{ $projectDetail['status_class'] ?? 'primary' }}">{{ $projectDetail['status_label'] ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="row ps-3 project-meta-item">
                            <div class="col-md-6 col-12">
                                <span class="d-block project-meta-label">Client Name</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="d-block project-meta-value">{{ $projectDetail['client_name'] ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="row ps-3 project-meta-item">
                            <div class="col-md-6 col-12">
                                <span class="d-block project-meta-label">Live Event Dates</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="d-block project-meta-value">{{ $projectDetail['live_event_date_label'] ?? '-' }} ({{ $projectDetail['live_event_duration_label'] ?? '-' }})</span>
                            </div>
                        </div>
                        <div class="row ps-3 project-meta-item">
                            <div class="col-md-6 col-12">
                                <span class="d-block project-meta-label">Project Lifecycle</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="d-block project-meta-value">{{ $projectDetail['date_label'] ?? '-' }} ({{ $projectDetail['duration_label'] ?? '-' }})</span>
                            </div>
                        </div>
                        <div class="row ps-3 project-meta-item">
                            <div class="col-md-6 col-12">
                                <span class="d-block project-meta-label">Event Lead / PIC</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="d-block project-meta-value">{{ $projectDetail['pic_label'] ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="row ps-3 project-meta-item">
                            <div class="col-md-6 col-12">
                                <span class="d-block project-meta-label">Core Team</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="d-block project-meta-value">{{ $projectDetail['team_count'] ?? 0 }} Staff</span>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix mt-3 ms-3">
                        <h6 class="mb-1 fw-semibold">Team</h6>
                        <div class="project-team-stack">
                            @forelse ($projectTeamMembers->take(6) as $teamMember)
                                <span class="project-team-avatar" title="{{ $teamMember['name'] ?? 'Staff' }}">
                                    @if (! empty($teamMember['avatar_url']))
                                        <img src="{{ $teamMember['avatar_url'] }}" alt="{{ $teamMember['name'] ?? 'Staff' }}">
                                    @else
                                        {{ $teamMember['fallback_label'] ?? 'S' }}
                                    @endif
                                </span>
                            @empty
                                <span class="fs-14 text-muted">-</span>
                            @endforelse

                            @if ($projectTeamMembers->count() > 6)
                                <span class="project-team-avatar project-team-more">+{{ $projectTeamMembers->count() - 6 }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-xl-6">
            <div class="card project-detail-card">
                <div class="card-header pb-0 border-0">
                    <div class="clearfix">
                        <h4 class="card-title mb-0">Tasks Summary</h4>
                        <small class="d-block">{{ $projectSummary['overdue_tasks'] ?? 0 }} Overdue Tasks</small>
                    </div>
                </div>
                <div class="card-body pb-0">
                    <div class="row project-summary-content">
                        <div class="col-sm-6 mb-3">
                            <div
                                id="projectTasksSummaryChart"
                                class="project-summary-chart"
                                data-chart-labels='@json($summaryChartLabels)'
                                data-chart-series='@json($summaryChartSeries)'
                                data-chart-total="{{ (int) ($projectSummary['total_tasks'] ?? 0) }}"></div>
                        </div>

                        <div class="col-sm-6 mb-3 project-summary-legend">
                            @forelse ($projectDepartmentGroups as $departmentGroup)
                                <div class="d-flex justify-content-between project-summary-legend-item">
                                    <div class="text-black">
                                        <span class="project-summary-legend-color d-inline-block me-1" style="background: {{ $departmentPalette[$loop->index % count($departmentPalette)] }};"></span>
                                        {{ $departmentGroup['name'] }}
                                    </div>
                                    <span>{{ $departmentGroup['total_tasks'] }}</span>
                                </div>
                            @empty
                                <p class="fs-14 mb-0">No department category available.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="card-footer border-0 pt-0">
                    <div class="alert alert-warning outline-dashed border-2 py-3 px-3 mb-0 text-dark">
                        <strong class="text-warning">{{ $projectSummary['overdue_tasks'] ?? 0 }} Overdue Tasks</strong> Track departmental tasks to keep this project moving.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-xl-6">
            <div class="card project-detail-card project-tasks-over-time-card" id="user-activity">
                <div class="card-header pb-0 border-0 d-flex justify-content-between align-items-start">
                    <div class="clearfix">
                        <h4 class="card-title mb-0">Tasks</h4>
                        <div class="clearfix d-flex">
                            <span class="project-chart-series">
                                <svg width="8" height="3" viewBox="0 0 8 3" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <rect width="8" height="3" rx="1.5" fill="#ff5b8a"></rect>
                                </svg>
                                Incomplete
                            </span>
                            <span class="project-chart-series">
                                <svg width="8" height="3" viewBox="0 0 8 3" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <rect width="8" height="3" rx="1.5" fill="#2445c7"></rect>
                                </svg>
                                Complete
                            </span>
                        </div>
                    </div>
                    <select class="form-select project-chart-filter" aria-label="Tasks over time period">
                        <option selected>{{ $projectTaskTimeline['month_label'] ?? '-' }}</option>
                    </select>
                </div>
                <div class="card-body ps-0 pt-2 pe-1 pb-1">
                    <div class="project-tasks-over-time-chart">
                        <div
                            id="projectTasksOverTimeChart"
                            data-chart-labels='@json($projectTaskTimeline['labels'] ?? [])'
                            data-completed-series='@json($projectTaskTimeline['completed'] ?? [])'
                            data-incomplete-series='@json($projectTaskTimeline['incomplete'] ?? [])'></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row project-department-row">
        @forelse ($projectDepartmentGroups as $departmentGroup)
            <div class="col-xxl-4 col-lg-6">
                <div class="card project-department-card">
                    <div class="card-header pb-0 border-0">
                        <div class="clearfix">
                            <h4 class="card-title mb-0">{{ $departmentGroup['name'] }}</h4>
                            <small class="d-block">{{ $departmentGroup['total_tasks'] }} task from this project</small>
                        </div>
                        <div class="clearfix text-end d-flex align-items-center justify-content-end project-department-actions">
                            @if ($departmentGroup['can_manage_drive'] ?? false)
                                <button type="button" class="btn btn-sm btn-light project-department-view-all js-project-department-drive" data-bs-toggle="modal" data-bs-target="#projectDepartmentDriveModal" data-update-url="{{ $departmentGroup['drive_update_url'] }}" data-department-name="{{ $departmentGroup['name'] }}" data-google-drive-url="{{ $departmentGroup['google_drive_url'] }}">{{ empty($departmentGroup['google_drive_url']) ? 'Add Drive' : 'Drive' }}</button>
                            @elseif (! empty($departmentGroup['google_drive_url']))
                                <a href="{{ $departmentGroup['google_drive_url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-light project-department-view-all">Drive</a>
                            @else
                                <button type="button" class="btn btn-sm btn-light project-department-view-all" disabled>Add Drive</button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="project-department-progress d-flex align-items-center justify-content-between">
                            <span class="text-gray fw-semibold">{{ $departmentGroup['completed_tasks'] }} / {{ $departmentGroup['total_tasks'] }} Completed <span class="text-success">({{ $departmentGroup['completion_rate'] }}%)</span></span>
                            @if ($departmentGroup['can_create_task'] ?? false)
                                <button type="button" class="project-department-add-task js-project-task-create" data-bs-toggle="modal" data-bs-target="#projectTaskFormModal" data-store-url="{{ $departmentGroup['store_url'] }}" data-department-id="{{ $departmentGroup['id'] }}" data-department-name="{{ $departmentGroup['name'] }}" data-assignee-options='@json($departmentGroup['task_assignee_options'] ?? [])'>+ Add Task</button>
                            @endif
                        </div>
                        @forelse ($departmentGroup['tasks'] as $task)
                            <div class="d-flex align-items-center project-department-task py-2" data-project-task-row="{{ $task['id'] }}">
                                <div class="timeline-vr-badge {{ $task['is_completed'] ? 'bg-success' : 'bg-light' }} me-2"></div>
                                @if ($task['can_toggle'])
                                    <div class="form-check custom-checkbox">
                                        <input type="checkbox" class="form-check-input project-task-toggle" id="projectTask{{ $task['id'] }}" data-toggle-url="{{ $task['toggle_url'] }}" @checked($task['is_completed'])>
                                        <label class="form-check-label" for="projectTask{{ $task['id'] }}"></label>
                                    </div>
                                @else
                                    <div class="project-task-check-space"></div>
                                @endif
                                <div class="clearfix ms-2">
                                    <h6 class="fs-13 mb-0 fw-semibold text-truncate project-department-task-title">{{ $task['title'] }}</h6>
                                    <span class="small"><span data-task-due-label>{{ $task['due_label'] }}</span> by <span class="text-primary">{{ $task['assignee_label'] }}</span></span>
                                </div>
                                <div class="clearfix ms-auto">
                                    @if ($task['can_toggle'])
                                        <div class="dropdown project-task-menu">
                                            <button type="button" class="btn btn-sm btn-light project-task-action" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-grid"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <button type="button" class="dropdown-item js-project-task-edit" data-bs-toggle="modal" data-bs-target="#projectTaskFormModal" data-task='@json($task)' data-department-name="{{ $departmentGroup['name'] }}">Update Task</button>
                                                <button type="button" class="dropdown-item text-danger js-project-task-delete" data-bs-toggle="modal" data-bs-target="#projectTaskDeleteModal" data-delete-url="{{ $task['delete_url'] }}">Delete Task</button>
                                            </div>
                                        </div>
                                    @else
                                        <button type="button" class="btn btn-sm btn-light project-task-action" disabled><i class="bi bi-grid"></i></button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="fs-14 mb-0">No project task for this department.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="mb-1">No department task found</h5>
                        <p class="mb-0 fs-14">Department cards appear after project members and project tasks are available.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

<div class="modal fade" id="projectTaskFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="projectTaskForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="projectTaskFormMethod" value="POST">
                <input type="hidden" name="department_id" id="projectTaskDepartmentId">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0" id="projectTaskFormTitle">Create New Task</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="projectTaskTitle" class="form-label">Task Name <span class="required text-danger">*</span></label>
                            <input type="text" class="form-control" id="projectTaskTitle" name="title" maxlength="255" placeholder="Contoh: Rekap absensi bulanan" required>
                        </div>
                        <div class="col-12">
                            <label for="projectTaskDescription" class="form-label">Task Description</label>
                            <textarea class="form-control" id="projectTaskDescription" name="description" rows="3" placeholder="Tambahkan detail atau konteks pekerjaan"></textarea>
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="projectTaskStartDate" class="form-label">Date <span class="required text-danger">*</span></label>
                            <input type="text" class="form-control js-project-task-date-input" id="projectTaskStartDate" name="start_date" placeholder="yyyy-mm-dd" autocomplete="off" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="projectTaskDueDate" class="form-label">Due Date <span class="required text-danger">*</span></label>
                            <input type="text" class="form-control js-project-task-date-input" id="projectTaskDueDate" name="due_date" placeholder="yyyy-mm-dd" autocomplete="off" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="projectTaskPriority" class="form-label">Priority <span class="required text-danger">*</span></label>
                            <select class="form-control default-select" id="projectTaskPriority" name="priority" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="projectTaskStatus" class="form-label">Task Status <span class="required text-danger">*</span></label>
                            <select class="form-control default-select" id="projectTaskStatus" name="status" required>
                                <option value="pending">To Do</option>
                                <option value="in_progress">On Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="projectTaskAttachment" class="form-label">Attachment</label>
                            <input type="text" class="form-control" id="projectTaskAttachment" name="attachment_path" maxlength="255" placeholder="Contoh: Link Google Drive, Figma, atau Docs">
                        </div>
                        <div class="col-md-6">
                            <label for="projectTaskBlockers" class="form-label">Blockers</label>
                            <input type="text" class="form-control" id="projectTaskBlockers" name="blockers" placeholder="Contoh: Menunggu approval dokumen">
                        </div>
                        <div class="col-6 col-md-6">
                            <label for="projectTaskCategory" class="form-label">Task Category <span class="required text-danger">*</span></label>
                            <select class="form-control default-select" id="projectTaskCategory" disabled>
                                <option selected>Project Report</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-6">
                            <label for="projectTaskProjectName" class="form-label">Project Name</label>
                            <input type="text" class="form-control" id="projectTaskProjectName" value="{{ $projectDetail['name'] ?? '-' }}" disabled>
                        </div>
                        <div class="col-12">
                            <label for="projectTaskAssignee" class="form-label">Assignee <span class="required text-danger">*</span></label>
                            <select class="form-control default-select" id="projectTaskAssignee" name="assigned_employee_id" required></select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="projectTaskFormSubmit">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="projectTaskDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Delete this project task?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="projectTaskDeleteButton">Delete</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="projectDepartmentDriveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="projectDepartmentDriveForm" method="POST">
                @csrf
                <input type="hidden" name="_method" value="PATCH">
                <div class="modal-header">
                    <h5 class="modal-title" id="projectDepartmentDriveTitle">Google Drive Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="projectDepartmentDriveUrl" class="form-label">Google Drive URL</label>
                    <input type="url" class="form-control" id="projectDepartmentDriveUrl" name="google_drive_url" maxlength="2048" placeholder="https://drive.google.com/drive/folders/...">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="projectDepartmentDriveSubmit">Save changes</button>
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
    $apexChartsPath = public_path('assets/vendor/apexcharts/dist/apexcharts.min.js');
    $apexChartsVersion = file_exists($apexChartsPath) ? filemtime($apexChartsPath) : time();
@endphp
<script src="{{ asset('assets/vendor/apexcharts/dist/apexcharts.min.js') }}?v={{ $apexChartsVersion }}"></script>
<script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(function () {
        var projectTaskDeleteUrl = '';

        if (typeof moment !== 'undefined') {
            moment.updateLocale('en', {
                week: { dow: 1 },
            });
        }

        var parseProjectChartData = function (value) {
            try {
                var parsedValue = JSON.parse(value || '[]');

                return Array.isArray(parsedValue) ? parsedValue : [];
            } catch (error) {
                return [];
            }
        };
        var parseJsonAttribute = function (value, fallback) {
            try {
                var parsedValue = JSON.parse(value || '');

                return parsedValue || fallback;
            } catch (error) {
                return fallback;
            }
        };
        var hideModal = function (selector) {
            var modalElement = document.querySelector(selector);

            if (window.bootstrap && modalElement) {
                var modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                modal.hide();

                return;
            }

            $(selector).modal('hide');
        };
        var todayDate = function () {
            var now = new Date();
            var month = String(now.getMonth() + 1).padStart(2, '0');
            var day = String(now.getDate()).padStart(2, '0');

            return now.getFullYear() + '-' + month + '-' + day;
        };
        var initializeProjectTaskDatePickers = function () {
            if (! $.fn.datetimepicker || typeof moment === 'undefined') {
                return;
            }

            $('.js-project-task-date-input').each(function () {
                if ($(this).data('DateTimePicker')) {
                    return;
                }

                $(this).datetimepicker({
                    format: 'YYYY-MM-DD',
                    useCurrent: false,
                    widgetPositioning: {
                        horizontal: 'auto',
                        vertical: 'top',
                    },
                    icons: {
                        previous: 'las la-angle-left',
                        next: 'las la-angle-right',
                    },
                });
            });
        };
        var hideProjectTaskDatePickers = function () {
            $('.js-project-task-date-input').each(function () {
                var datePicker = $(this).data('DateTimePicker');

                if (datePicker) {
                    datePicker.hide();
                }
            });
        };
        var resetProjectTaskForm = function (button) {
            var currentDate = todayDate();
            var assigneeOptions = parseJsonAttribute($(button).attr('data-assignee-options'), []);

            $('#projectTaskFormTitle').text('Create New Task');
            $('#projectTaskForm').attr('action', $(button).attr('data-store-url') || '');
            $('#projectTaskFormMethod').val('POST');
            $('#projectTaskDepartmentId').val($(button).attr('data-department-id') || '');
            $('#projectTaskAssignee').empty();
            assigneeOptions.forEach(function (assignee) {
                $('#projectTaskAssignee').append(new Option(assignee.name || assignee.id || 'Staff', assignee.id || ''));
            });
            $('#projectTaskTitle').val('');
            $('#projectTaskDescription').val('');
            $('#projectTaskBlockers').val('');
            $('#projectTaskAttachment').val('');
            $('#projectTaskPriority').val('medium');
            $('#projectTaskStatus').val('pending');
            $('#projectTaskStartDate').val(currentDate);
            $('#projectTaskDueDate').val(currentDate);
            refreshProjectTaskFormSelects();
        };
        var fillProjectTaskForm = function (button) {
            var task = parseJsonAttribute($(button).attr('data-task'), {});

            $('#projectTaskFormTitle').text('Update Task');
            $('#projectTaskForm').attr('action', task.update_url || '');
            $('#projectTaskFormMethod').val('PUT');
            $('#projectTaskDepartmentId').val('');
            $('#projectTaskAssignee').empty().append(new Option(task.assignee_label || 'Staff', task.employee_id || ''));
            $('#projectTaskTitle').val(task.title || '');
            $('#projectTaskDescription').val(task.description || '');
            $('#projectTaskBlockers').val(task.blockers || '');
            $('#projectTaskAttachment').val(task.attachment_path || '');
            $('#projectTaskPriority').val(task.priority || 'medium');
            $('#projectTaskStatus').val(task.status || 'pending');
            $('#projectTaskStartDate').val(task.start_date || todayDate());
            $('#projectTaskDueDate').val(task.due_date || todayDate());
            refreshProjectTaskFormSelects();
        };
        var refreshProjectTaskFormSelects = function () {
            if (! $.fn.selectpicker) {
                return;
            }

            $('#projectTaskPriority, #projectTaskStatus, #projectTaskAssignee').selectpicker('refresh');
        };
        var handleProjectTaskAjaxError = function (xhr) {
            var errors = xhr.responseJSON?.errors || {};
            var firstError = Object.values(errors)[0]?.[0];

            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: firstError || xhr.responseJSON?.message || 'Gagal memproses task project.',
            });
        };
        var renderProjectTasksSummaryChart = function () {
            var summaryChartElement = document.getElementById('projectTasksSummaryChart');

            if (! summaryChartElement || typeof ApexCharts === 'undefined') {
                return;
            }

            var summarySeries = parseProjectChartData(summaryChartElement.getAttribute('data-chart-series'));
            var summaryLabels = parseProjectChartData(summaryChartElement.getAttribute('data-chart-labels'));
            var summaryTotal = Number(summaryChartElement.getAttribute('data-chart-total') || 0);

            new ApexCharts(summaryChartElement, {
                series: summarySeries,
                chart: {
                    type: 'donut',
                    width: 250,
                    toolbar: {
                        show: false,
                    },
                },
                labels: summaryLabels,
                colors: ['#2445c7', '#ff8a00', '#2bc155', '#ff5b8a', '#ffb800', '#8b5cf6'],
                dataLabels: {
                    enabled: false,
                },
                legend: {
                    show: false,
                },
                stroke: {
                    width: 3,
                    colors: ['#fff'],
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '90%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    offsetY: 20,
                                },
                                value: {
                                    show: true,
                                    color: '#111827',
                                    fontSize: '24px',
                                    fontWeight: 600,
                                    offsetY: -16,
                                    formatter: function () {
                                        return summaryTotal;
                                    },
                                },
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: 'Total',
                                    color: '#6b7280',
                                    fontSize: '13px',
                                    fontWeight: 500,
                                    formatter: function () {
                                        return summaryTotal;
                                    },
                                },
                            },
                        },
                    },
                },
            }).render();
        };
        var renderProjectTasksOverTimeChart = function () {
            var tasksOverTimeElement = document.getElementById('projectTasksOverTimeChart');

            if (! tasksOverTimeElement || typeof ApexCharts === 'undefined') {
                return;
            }

            var chartLabels = parseProjectChartData(tasksOverTimeElement.getAttribute('data-chart-labels'));
            var incompleteSeries = parseProjectChartData(tasksOverTimeElement.getAttribute('data-incomplete-series'));
            var completedSeries = parseProjectChartData(tasksOverTimeElement.getAttribute('data-completed-series'));
            var highestValue = Math.max(0, ...incompleteSeries, ...completedSeries);
            var shouldUseTemplateScale = highestValue > 0 && highestValue < 30;
            var normalizeSeriesForTemplateScale = function (series, minValue, maxValue) {
                var seriesMax = Math.max(0, ...series);

                if (! shouldUseTemplateScale || seriesMax <= 0) {
                    return series;
                }

                return series.map(function (value) {
                    return Math.round(minValue + ((value / seriesMax) * (maxValue - minValue)));
                });
            };
            var displayedIncompleteSeries = normalizeSeriesForTemplateScale(incompleteSeries, 90, 120);
            var displayedCompletedSeries = normalizeSeriesForTemplateScale(completedSeries, 50, 75);
            var yAxisMax = shouldUseTemplateScale ? 120 : Math.max(4, Math.ceil(highestValue / 4) * 4);
            var tooltipSource = {
                Incomplete: incompleteSeries,
                Complete: completedSeries,
            };

            new ApexCharts(tasksOverTimeElement, {
                series: [
                    {
                        name: 'Incomplete',
                        data: displayedIncompleteSeries,
                    },
                    {
                        name: 'Complete',
                        data: displayedCompletedSeries,
                    },
                ],
                chart: {
                    height: 280,
                    type: 'area',
                    toolbar: {
                        show: false,
                    },
                    zoom: {
                        enabled: false,
                    },
                },
                colors: ['#ff5b8a', '#2445c7'],
                dataLabels: {
                    enabled: false,
                },
                stroke: {
                    curve: 'smooth',
                    width: 3,
                },
                legend: {
                    show: false,
                },
                grid: {
                    show: true,
                    strokeDashArray: 3,
                    borderColor: 'rgba(148, 163, 184, .28)',
                },
                yaxis: {
                    min: 0,
                    max: yAxisMax,
                    tickAmount: 4,
                    labels: {
                        style: {
                            colors: '#6b7280',
                            fontSize: '12px',
                        },
                        formatter: function (value) {
                            return Math.round(value);
                        },
                    },
                },
                xaxis: {
                    categories: chartLabels,
                    labels: {
                        style: {
                            colors: '#6b7280',
                            fontSize: '12px',
                        },
                    },
                    axisTicks: {
                        show: false,
                    },
                    axisBorder: {
                        show: false,
                    },
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 0,
                        inverseColors: false,
                        colorStops: [
                            [
                                {
                                    offset: 0,
                                    color: '#ff5b8a',
                                    opacity: .24,
                                },
                                {
                                    offset: 55,
                                    color: '#ff5b8a',
                                    opacity: .14,
                                },
                                {
                                    offset: 100,
                                    color: '#ff5b8a',
                                    opacity: .04,
                                },
                            ],
                            [
                                {
                                    offset: 0,
                                    color: '#2445c7',
                                    opacity: .24,
                                },
                                {
                                    offset: 55,
                                    color: '#2445c7',
                                    opacity: .14,
                                },
                                {
                                    offset: 100,
                                    color: '#2445c7',
                                    opacity: .04,
                                },
                            ],
                        ],
                    },
                },
                markers: {
                    size: 0,
                },
                tooltip: {
                    y: {
                        formatter: function (value, options) {
                            var seriesName = options.w.config.series[options.seriesIndex].name;
                            var originalValue = tooltipSource[seriesName]?.[options.dataPointIndex] ?? value;

                            return originalValue + ' Tasks';
                        },
                    },
                },
            }).render();
        };

        renderProjectTasksSummaryChart();
        renderProjectTasksOverTimeChart();

        $(document).on('click', '.js-project-task-create', function () {
            resetProjectTaskForm(this);
        });

        $('#projectTaskFormModal').on('shown.bs.modal', initializeProjectTaskDatePickers);

        $('#projectTaskFormModal').on('mousedown', function (event) {
            if ($(event.target).closest('.bootstrap-datetimepicker-widget, .js-project-task-date-input').length) {
                return;
            }

            hideProjectTaskDatePickers();
        });

        $(document).on('focus', '#projectTaskFormModal input:not(.js-project-task-date-input), #projectTaskFormModal textarea, #projectTaskFormModal select', hideProjectTaskDatePickers);

        $(document).on('click', '.js-project-task-edit', function () {
            fillProjectTaskForm(this);
        });

        $(document).on('click', '.js-project-task-delete', function () {
            projectTaskDeleteUrl = $(this).attr('data-delete-url') || '';
        });

        $(document).on('click', '.js-project-department-drive', function () {
            var currentDriveUrl = $(this).attr('data-google-drive-url') || '';

            $('#projectDepartmentDriveTitle').text((currentDriveUrl ? 'Update ' : 'Add ') + ($(this).attr('data-department-name') || 'Google Drive Department') + ' Drive');
            $('#projectDepartmentDriveForm').attr('action', $(this).attr('data-update-url') || '');
            $('#projectDepartmentDriveUrl').val(currentDriveUrl);
        });

        $('#projectDepartmentDriveForm').on('submit', function (event) {
            event.preventDefault();

            var form = $(this);
            var submitButton = $('#projectDepartmentDriveSubmit');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                headers: {
                    'X-CSRF-TOKEN': @json(csrf_token()),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                beforeSend: function () {
                    submitButton.prop('disabled', true).text('Menyimpan...');
                },
                success: function (response) {
                    if (response.success === true || response.status === true) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 1100,
                            showConfirmButton: false,
                        }).then(function () {
                            hideModal('#projectDepartmentDriveModal');
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Gagal',
                            text: response.message,
                        });
                    }
                },
                error: handleProjectTaskAjaxError,
                complete: function () {
                    submitButton.prop('disabled', false).text('Save changes');
                },
            });
        });

        $('#projectTaskForm').on('submit', function (event) {
            event.preventDefault();

            var form = $(this);
            var formData = new FormData(this);
            var submitButton = $('#projectTaskFormSubmit');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': @json(csrf_token()),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                beforeSend: function () {
                    submitButton.prop('disabled', true).text('Menyimpan...');
                },
                success: function (response) {
                    if (response.success === true || response.status === true) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 1100,
                            showConfirmButton: false,
                        }).then(function () {
                            hideModal('#projectTaskFormModal');
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Gagal',
                            text: response.message,
                        });
                    }
                },
                error: handleProjectTaskAjaxError,
                complete: function () {
                    submitButton.prop('disabled', false).text('Save changes');
                },
            });
        });

        $('#projectTaskDeleteButton').on('click', function () {
            var button = $(this);

            if (! projectTaskDeleteUrl) {
                return;
            }

            $.ajax({
                url: projectTaskDeleteUrl,
                type: 'DELETE',
                data: {
                    _token: @json(csrf_token()),
                },
                headers: {
                    'X-CSRF-TOKEN': @json(csrf_token()),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                beforeSend: function () {
                    button.prop('disabled', true).text('Menghapus...');
                },
                success: function (response) {
                    if (response.success === true || response.status === true) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 1100,
                            showConfirmButton: false,
                        }).then(function () {
                            hideModal('#projectTaskDeleteModal');
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Gagal',
                            text: response.message,
                        });
                    }
                },
                error: handleProjectTaskAjaxError,
                complete: function () {
                    button.prop('disabled', false).text('Delete');
                },
            });
        });

        $(document).on('change', '.project-task-toggle', function () {
            var checkbox = $(this);
            var row = checkbox.closest('[data-project-task-row]');
            var isCompleted = checkbox.is(':checked');

            $.ajax({
                url: checkbox.data('toggle-url'),
                type: 'PATCH',
                data: {
                    _token: @json(csrf_token()),
                    completed: isCompleted ? 1 : 0,
                },
                headers: {
                    'X-CSRF-TOKEN': @json(csrf_token()),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                beforeSend: function () {
                    checkbox.prop('disabled', true);
                },
                success: function (response) {
                    if (response.success === true || response.status === true) {
                        row.find('.timeline-vr-badge')
                            .toggleClass('bg-success', response.task?.is_completed === true)
                            .toggleClass('bg-light', response.task?.is_completed !== true);
                        row.find('[data-task-due-label]').text(response.task?.due_label || '');
                    } else {
                        checkbox.prop('checked', ! isCompleted);
                        Swal.fire({
                            icon: 'warning',
                            title: 'Gagal',
                            text: response.message,
                        });
                    }
                },
                error: function (xhr) {
                    checkbox.prop('checked', ! isCompleted);
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: xhr.responseJSON?.message || 'Gagal mengubah task department.',
                    });
                },
                complete: function () {
                    checkbox.prop('disabled', false);
                },
            });
        });

        initializeProjectTaskDatePickers();
    });
</script>

@endsection
