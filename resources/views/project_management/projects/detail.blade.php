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
    .project-division-row {
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

    .project-division-card {
        border: 0;
        border-radius: var(--bs-border-radius-xl, 1rem);
        box-shadow: 0 5px 18px rgba(20, 24, 40, .05);
        overflow: hidden;
    }

    .project-division-card .card-header {
        align-items: flex-start;
        padding: 24px 24px 0;
    }

    .project-division-card .card-body {
        max-height: 330px;
        overflow-y: auto;
        padding: 18px 24px 24px;
    }

    .project-division-task-title {
        max-width: min(245px, 46vw);
    }

    .project-division-progress {
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 16px;
    }

    .project-division-add-task {
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

    .project-division-add-task:hover,
    .project-division-add-task:focus {
        color: #22a447;
        text-decoration: none;
    }

    .project-division-task {
        min-height: 46px;
    }

    .project-division-task .timeline-vr-badge {
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
    .project-division-view-all {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }

    .project-division-view-all {
        width: auto;
        min-width: 86px;
        padding-inline: 16px;
        color: #000;
        font-weight: 600;
    }

    .project-division-view-all:disabled {
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

    .project-division-actions {
        gap: 8px;
    }

    @media (max-width: 575.98px) {
        .project-division-task-title {
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
    $projectDivisionGroups = collect($projectDivisionGroups ?? []);
    $projectTeamMembers = collect($projectDetail['team_members'] ?? []);
    $divisionPalette = ['#2445c7', '#ff8a00', '#2bc155', '#ff5b8a', '#ffb800', '#8b5cf6'];
    $summaryChartLabels = $projectDivisionGroups
        ->map(fn ($divisionGroup): string => (string) ($divisionGroup['name'] ?? '-'))
        ->values();
    $summaryChartSeries = $projectDivisionGroups
        ->map(fn ($divisionGroup): int => (int) ($divisionGroup['total_tasks'] ?? 0))
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
                            @forelse ($projectDivisionGroups as $divisionGroup)
                                <div class="d-flex justify-content-between project-summary-legend-item">
                                    <div class="text-black">
                                        <span class="project-summary-legend-color d-inline-block me-1" style="background: {{ $divisionPalette[$loop->index % count($divisionPalette)] }};"></span>
                                        {{ $divisionGroup['name'] }}
                                    </div>
                                    <span>{{ $divisionGroup['total_tasks'] }}</span>
                                </div>
                            @empty
                                <p class="fs-14 mb-0">No division category available.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="card-footer border-0 pt-0">
                    <div class="alert alert-warning outline-dashed border-2 py-3 px-3 mb-0 text-dark">
                        <strong class="text-warning">{{ $projectSummary['overdue_tasks'] ?? 0 }} Overdue Tasks</strong> Track division tasks to keep this project moving.
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

    <div class="row project-division-row">
        @forelse ($projectDivisionGroups as $divisionGroup)
            <div class="col-xxl-4 col-lg-6">
                <div class="card project-division-card">
                    <div class="card-header pb-0 border-0">
                        <div class="clearfix">
                            <h4 class="card-title mb-0">{{ $divisionGroup['name'] }}</h4>
                            <small class="d-block">{{ $divisionGroup['sub_title'] ?: $divisionGroup['total_tasks'].' task from this project' }}</small>
                        </div>
                        <div class="clearfix text-end d-flex align-items-center justify-content-end project-division-actions">
                            @if ($divisionGroup['can_manage_drive'] ?? false)
                                @if (! empty($divisionGroup['google_drive_url']))
                                    <button type="button" class="btn btn-sm btn-light project-division-view-all js-project-division-drive" data-update-url="{{ $divisionGroup['drive_update_url'] }}" data-project-name="{{ $projectDetail['name'] ?? 'Project' }}" data-event-division-name="{{ $divisionGroup['name'] }}" data-google-drive-url="{{ $divisionGroup['google_drive_url'] }}" data-folder-id="{{ $divisionGroup['folder_id'] }}" data-drive-configured="true">Drive</button>
                                @else
                                    <button type="button" class="btn btn-sm btn-light project-division-view-all js-project-division-drive" data-update-url="{{ $divisionGroup['drive_update_url'] }}" data-project-name="{{ $projectDetail['name'] ?? 'Project' }}" data-event-division-name="{{ $divisionGroup['name'] }}" data-google-drive-url="{{ $divisionGroup['google_drive_url'] }}" data-folder-id="{{ $divisionGroup['folder_id'] }}" data-drive-configured="false">Konfigurasi Drive</button>
                                @endif
                            @elseif (! empty($divisionGroup['google_drive_url']))
                                <a href="{{ $divisionGroup['google_drive_url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-light project-division-view-all">Drive</a>
                            @else
                                <button type="button" class="btn btn-sm btn-light project-division-view-all" disabled>Konfigurasi Drive</button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="project-division-progress d-flex align-items-center justify-content-between">
                            <span class="text-gray fw-semibold">{{ $divisionGroup['completed_tasks'] }} / {{ $divisionGroup['total_tasks'] }} Completed <span class="text-success">({{ $divisionGroup['completion_rate'] }}%)</span></span>
                            @if ($divisionGroup['can_create_task'] ?? false)
                                <button type="button" class="project-division-add-task js-project-task-create" data-bs-toggle="modal" data-bs-target="#projectTaskFormModal" data-store-url="{{ $divisionGroup['store_url'] }}" data-event-division-id="{{ $divisionGroup['id'] }}" data-event-division-name="{{ $divisionGroup['name'] }}" data-assignee-options='@json($divisionGroup['task_assignee_options'] ?? [])'>+ Add Task</button>
                            @endif
                        </div>
                        @forelse ($divisionGroup['tasks'] as $task)
                            <div class="d-flex align-items-center project-division-task py-2" data-project-task-row="{{ $task['id'] }}">
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
                                    <h6 class="fs-13 mb-0 fw-semibold text-truncate project-division-task-title">{{ $task['title'] }}</h6>
                                    <span class="small"><span data-task-due-label>{{ $task['due_label'] }}</span> by <span class="text-primary">{{ $task['assignee_label'] }}</span></span>
                                </div>
                                <div class="clearfix ms-auto">
                                    @if ($task['can_toggle'])
                                        <div class="dropdown project-task-menu">
                                            <button type="button" class="btn btn-sm btn-light project-task-action" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-grid"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <button type="button" class="dropdown-item js-project-task-edit" data-bs-toggle="modal" data-bs-target="#projectTaskFormModal" data-task='@json($task)' data-event-division-name="{{ $divisionGroup['name'] }}">Update Task</button>
                                                <button type="button" class="dropdown-item text-danger js-project-task-delete" data-bs-toggle="modal" data-bs-target="#projectTaskDeleteModal" data-delete-url="{{ $task['delete_url'] }}">Delete Task</button>
                                            </div>
                                        </div>
                                    @else
                                        <button type="button" class="btn btn-sm btn-light project-task-action" disabled><i class="bi bi-grid"></i></button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="fs-14 mb-0">No project task for this division.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="mb-1">No division task found</h5>
                        <p class="mb-0 fs-14">Division cards appear after event divisions are seeded.</p>
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
                <input type="hidden" name="event_division_id" id="projectTaskEventDivisionId">
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

<div class="modal fade" id="projectDivisionDriveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="projectDivisionDriveForm" method="POST">
                @csrf
                <input type="hidden" name="_method" value="PATCH">
                <input type="hidden" name="folder_id" id="projectDivisionDriveFolderId">
                <div class="modal-header">
                    <h5 class="modal-title" id="projectDivisionDriveTitle">Google Drive Division</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="projectDivisionDriveParentName" class="form-label">Lokasi Folder Induk</label>
                        <div class="d-flex gap-2">
                            <input type="hidden" id="projectDivisionDriveParentId">
                            <input type="text" class="form-control" id="projectDivisionDriveParentName" value="Belum dipilih" readonly>
                            <button type="button" class="btn btn-light flex-shrink-0" id="projectDivisionDrivePickParent">Pilih Folder</button>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="projectDivisionDriveProjectFolderName" class="form-label">Folder Project</label>
                            <input type="text" class="form-control" id="projectDivisionDriveProjectFolderName" maxlength="255">
                        </div>
                        <div class="col-md-6">
                            <label for="projectDivisionDriveDivisionFolderName" class="form-label">Folder Divisi</label>
                            <input type="text" class="form-control" id="projectDivisionDriveDivisionFolderName" maxlength="255">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="projectDivisionDriveFolderPath" class="form-label">Struktur Folder</label>
                        <input type="text" class="form-control" id="projectDivisionDriveFolderPath" readonly>
                    </div>
                    <div class="d-flex justify-content-end mb-3">
                        <button type="button" class="btn btn-primary" id="projectDivisionDriveCreateFolder" disabled>Buat Struktur Folder</button>
                    </div>
                    <hr>
                    <label for="projectDivisionDriveUrl" class="form-label d-flex align-items-center gap-2">
                        <span>Google Drive URL</span>
                        <span class="badge bg-danger" id="projectDivisionDriveStatusBadge">Belum siap</span>
                    </label>
                    <div class="input-group">
                        <input type="url" class="form-control" id="projectDivisionDriveUrl" name="google_drive_url" maxlength="2048" placeholder="https://drive.google.com/drive/folders/...">
                        <button type="button" class="btn btn-light" id="projectDivisionDriveOpenUrl" disabled>Open</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="projectDivisionDriveSubmit">Simpan URL</button>
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
<script src="https://accounts.google.com/gsi/client"></script>
<script src="https://apis.google.com/js/api.js"></script>
<script>
    $(function () {
        var projectTaskDeleteUrl = '';
        var projectDivisionDriveAccessToken = '';
        var projectDivisionDriveActiveButton = null;
        var projectDivisionDriveApiKey = @json(config('services.google.api_key'));
        var projectDivisionDriveOauthClientId = @json(config('services.google.client_id'));
        var projectDivisionDriveOauthScope = 'https://www.googleapis.com/auth/drive.file';
        var projectDivisionDriveAccessTokenUrl = @json(route('google-drive.oauth.access-token'));
        var projectDivisionDriveExchangeCodeUrl = @json(route('google-drive.oauth.exchange-code'));
        var projectDivisionDriveFolderMimeType = 'application/vnd.google-apps.folder';
        var projectDivisionDrivePendingButton = null;
        var projectDivisionDriveCodeClient = null;

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
        var showModal = function (selector) {
            var modalElement = document.querySelector(selector);

            if (window.bootstrap && modalElement) {
                var modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                modal.show();

                return;
            }

            $(selector).modal('show');
        };
        var fillProjectDivisionDriveForm = function (button) {
            var currentDriveUrl = $(button).attr('data-google-drive-url') || '';

            projectDivisionDriveActiveButton = button;
            $('#projectDivisionDriveTitle').text('Konfigurasi Drive ' + ($(button).attr('data-event-division-name') || 'Google Drive Division'));
            $('#projectDivisionDriveForm').attr('action', $(button).attr('data-update-url') || '');
            $('#projectDivisionDriveUrl').val(currentDriveUrl);
            $('#projectDivisionDriveFolderId').val($(button).attr('data-folder-id') || '');
            refreshProjectDivisionDriveOpenButton();
            $('#projectDivisionDriveParentId').val('');
            $('#projectDivisionDriveParentName').val('Belum dipilih');
            $('#projectDivisionDriveProjectFolderName').val(projectDivisionDriveProjectName(button));
            $('#projectDivisionDriveDivisionFolderName').val(projectDivisionDriveDivisionName(button));
            $('#projectDivisionDriveCreateFolder').prop('disabled', true).text('Buat Struktur Folder');
            updateProjectDivisionDriveFolderPath(button);
        };
        var resetProjectDivisionDriveButton = function (button) {
            if (! button) {
                return;
            }

            $(button).prop('disabled', false).text('Konfigurasi Drive');
        };
        var showProjectDivisionDriveModal = function (button) {
            fillProjectDivisionDriveForm(button);
            showModal('#projectDivisionDriveModal');
        };
        var restoreProjectDivisionDriveModal = function () {
            window.setTimeout(function () {
                if (projectDivisionDriveActiveButton) {
                    showModal('#projectDivisionDriveModal');
                }
            }, 150);
        };
        var projectDivisionDriveProjectName = function (button) {
            return $(button).attr('data-project-name') || 'Project';
        };
        var projectDivisionDriveDivisionName = function (button) {
            return $(button).attr('data-event-division-name') || 'Google Drive Division';
        };
        var updateProjectDivisionDriveFolderPath = function (button) {
            var parentName = $('#projectDivisionDriveParentName').val() || 'Belum dipilih';
            var projectFolderName = $('#projectDivisionDriveProjectFolderName').val() || projectDivisionDriveProjectName(button);
            var divisionFolderName = $('#projectDivisionDriveDivisionFolderName').val() || projectDivisionDriveDivisionName(button);

            $('#projectDivisionDriveFolderPath').val(parentName + ' / ' + projectFolderName + ' / ' + divisionFolderName);
        };
        var saveProjectDivisionDriveUrl = function (button, driveUrl, folderId) {
            return $.ajax({
                url: $(button).attr('data-update-url') || '',
                type: 'POST',
                data: {
                    _method: 'PATCH',
                    google_drive_url: driveUrl,
                    folder_id: folderId || '',
                },
                headers: {
                    'X-CSRF-TOKEN': @json(csrf_token()),
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
        };
        var syncProjectDivisionDriveButton = function (button, driveUrl, folderId) {
            $(button).attr('data-google-drive-url', driveUrl);
            $(button).attr('data-folder-id', folderId || '');
            $(button).attr('data-drive-configured', driveUrl ? 'true' : 'false');
            $(button).text(driveUrl ? 'Drive' : 'Konfigurasi Drive');
            $('#projectDivisionDriveUrl').val(driveUrl);
            $('#projectDivisionDriveFolderId').val(folderId || '');
            refreshProjectDivisionDriveOpenButton();
        };
        var refreshProjectDivisionDriveStatusBadge = function () {
            var hasDriveUrl = Boolean($('#projectDivisionDriveUrl').val());

            $('#projectDivisionDriveStatusBadge')
                .toggleClass('bg-success', hasDriveUrl)
                .toggleClass('bg-danger', ! hasDriveUrl)
                .text(hasDriveUrl ? 'Folder siap' : 'Belum siap');
        };
        var refreshProjectDivisionDriveOpenButton = function () {
            $('#projectDivisionDriveOpenUrl').prop('disabled', ! $('#projectDivisionDriveUrl').val());
            refreshProjectDivisionDriveStatusBadge();
        };
        var escapeProjectDivisionDriveQueryValue = function (value) {
            return String(value || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
        };
        var findProjectDivisionDriveFolder = function (folderName, parentFolderId) {
            var query = "'" + escapeProjectDivisionDriveQueryValue(parentFolderId) + "' in parents and name = '" + escapeProjectDivisionDriveQueryValue(folderName) + "' and mimeType = '" + projectDivisionDriveFolderMimeType + "' and trashed = false";

            return $.ajax({
                url: 'https://www.googleapis.com/drive/v3/files',
                type: 'GET',
                data: {
                    q: query,
                    fields: 'files(id,name,webViewLink)',
                    pageSize: 1,
                    supportsAllDrives: true,
                    includeItemsFromAllDrives: true,
                },
                headers: {
                    Authorization: 'Bearer ' + projectDivisionDriveAccessToken,
                },
            }).then(function (response) {
                return response.files?.[0] || null;
            });
        };
        var createProjectDivisionDriveChildFolder = function (folderName, parentFolderId) {
            return $.ajax({
                url: 'https://www.googleapis.com/drive/v3/files?fields=id,name,webViewLink&supportsAllDrives=true',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    name: folderName,
                    mimeType: projectDivisionDriveFolderMimeType,
                    parents: [parentFolderId],
                }),
                headers: {
                    Authorization: 'Bearer ' + projectDivisionDriveAccessToken,
                },
            });
        };
        var findOrCreateProjectDivisionDriveFolder = function (folderName, parentFolderId) {
            return findProjectDivisionDriveFolder(folderName, parentFolderId).then(function (folder) {
                if (folder) {
                    return folder;
                }

                return createProjectDivisionDriveChildFolder(folderName, parentFolderId);
            });
        };
        var createProjectDivisionDriveFolder = function (button) {
            var parentFolderId = $('#projectDivisionDriveParentId').val();
            var projectName = $('#projectDivisionDriveProjectFolderName').val() || projectDivisionDriveProjectName(button);
            var divisionName = $('#projectDivisionDriveDivisionFolderName').val() || projectDivisionDriveDivisionName(button);
            var createButton = $('#projectDivisionDriveCreateFolder');

            if (! parentFolderId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih lokasi',
                    text: 'Pilih folder induk terlebih dahulu.',
                });

                return;
            }

            createButton.prop('disabled', true).text('Membuat struktur...');

            findOrCreateProjectDivisionDriveFolder(projectName, parentFolderId)
                .then(function (projectFolder) {
                    return findOrCreateProjectDivisionDriveFolder(divisionName, projectFolder.id);
                })
                .then(function (folder) {
                    var driveUrl = folder.webViewLink || ('https://drive.google.com/drive/folders/' + folder.id);

                    $('#projectDivisionDriveUrl').val(driveUrl);
                    $('#projectDivisionDriveFolderId').val(folder.id || '');
                    createButton.text('Menyimpan link...');

                    return saveProjectDivisionDriveUrl(button, driveUrl, folder.id || '')
                        .then(function (response) {
                            if (response.success === true || response.status === true) {
                                syncProjectDivisionDriveButton(button, response.google_drive_url || driveUrl, response.folder_id || folder.id || '');
                                hideModal('#projectDivisionDriveModal');

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Drive siap',
                                    text: 'Struktur folder Google Drive berhasil dibuat dan disimpan.',
                                    timer: 1100,
                                    showConfirmButton: false,
                                });

                                return;
                            }

                            $(button).attr('data-google-drive-url', driveUrl);
                            showProjectDivisionDriveModal(button);
                            Swal.fire({
                                icon: 'warning',
                                title: 'Folder dibuat',
                                text: response.message || 'Link folder belum tersimpan otomatis.',
                            });
                        });
                })
                .fail(function (xhr) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Drive belum tersimpan',
                        text: xhr.responseJSON?.error?.message || xhr.responseJSON?.message || 'Google Drive API gagal membuat folder atau link belum tersimpan.',
                    });
                })
                .always(function () {
                    createButton.prop('disabled', false).text('Buat Struktur Folder');
                });
        };
        var openProjectDivisionDrivePicker = function () {
            if (! projectDivisionDriveApiKey) {
                Swal.fire({
                    icon: 'error',
                    title: 'Konfigurasi belum lengkap',
                    text: 'GOOGLE_API_KEY belum diset.',
                });

                return;
            }

            if (! projectDivisionDriveAccessToken) {
                Swal.fire({
                    icon: 'warning',
                    title: 'OAuth belum siap',
                    text: 'Hubungkan akun Google terlebih dahulu.',
                });

                return;
            }

            if (! window.gapi) {
                Swal.fire({
                    icon: 'error',
                    title: 'Picker belum siap',
                    text: 'Google Picker belum selesai dimuat.',
                });

                return;
            }

            gapi.load('picker', {
                callback: function () {
                    var folderView = new google.picker.DocsView(google.picker.ViewId.FOLDERS)
                        .setIncludeFolders(true)
                        .setMimeTypes(projectDivisionDriveFolderMimeType)
                        .setSelectFolderEnabled(true);
                    var picker = new google.picker.PickerBuilder()
                        .addView(folderView)
                        .setDeveloperKey(projectDivisionDriveApiKey)
                        .setOAuthToken(projectDivisionDriveAccessToken)
                        .setOrigin(window.location.protocol + '//' + window.location.host)
                        .setCallback(function (data) {
                            var pickerAction = data[google.picker.Response.ACTION];

                            if (pickerAction !== google.picker.Action.PICKED) {
                                restoreProjectDivisionDriveModal();

                                return;
                            }

                            var folder = data[google.picker.Response.DOCUMENTS]?.[0];

                            if (! folder) {
                                restoreProjectDivisionDriveModal();

                                return;
                            }

                            $('#projectDivisionDriveParentId').val(folder[google.picker.Document.ID] || folder.id || '');
                            $('#projectDivisionDriveParentName').val(folder[google.picker.Document.NAME] || folder.name || folder.title || 'Folder Drive');
                            $('#projectDivisionDriveCreateFolder').prop('disabled', false);
                            updateProjectDivisionDriveFolderPath(projectDivisionDriveActiveButton);
                            restoreProjectDivisionDriveModal();
                        })
                        .build();

                    hideModal('#projectDivisionDriveModal');
                    picker.setVisible(true);
                },
            });
        };
        var requestProjectDivisionDriveOAuth = function (button) {
            if (! projectDivisionDriveOauthClientId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Konfigurasi belum lengkap',
                    text: 'GOOGLE_CLIENT_ID belum diset.',
                });

                return;
            }

            projectDivisionDrivePendingButton = button;
            $(button).prop('disabled', true).text('Menghubungkan...');

            $.ajax({
                url: projectDivisionDriveAccessTokenUrl,
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                success: function (response) {
                    projectDivisionDriveAccessToken = response.access_token || '';

                    if (! projectDivisionDriveAccessToken) {
                        requestProjectDivisionDriveCode();

                        return;
                    }

                    resetProjectDivisionDriveButton(button);
                    projectDivisionDrivePendingButton = null;
                    showProjectDivisionDriveModal(button);
                },
                error: function (xhr) {
                    if (xhr.responseJSON?.requires_authorization === true) {
                        requestProjectDivisionDriveCode();

                        return;
                    }

                    resetProjectDivisionDriveButton(button);
                    projectDivisionDrivePendingButton = null;
                    Swal.fire({
                        icon: 'error',
                        title: 'OAuth belum siap',
                        text: xhr.responseJSON?.message || 'Gagal membaca token Google dari server.',
                    });
                },
            });
        };
        var requestProjectDivisionDriveCode = function () {
            if (! window.google?.accounts?.oauth2) {
                resetProjectDivisionDriveButton(projectDivisionDrivePendingButton);
                projectDivisionDrivePendingButton = null;
                Swal.fire({
                    icon: 'error',
                    title: 'OAuth belum siap',
                    text: 'Google OAuth belum selesai dimuat.',
                });

                return;
            }

            if (! projectDivisionDriveCodeClient) {
                projectDivisionDriveCodeClient = google.accounts.oauth2.initCodeClient({
                    client_id: projectDivisionDriveOauthClientId,
                    scope: projectDivisionDriveOauthScope,
                    ux_mode: 'popup',
                    prompt: 'consent',
                    callback: function (response) {
                        var targetButton = projectDivisionDrivePendingButton;

                        if (! response || response.error) {
                            projectDivisionDrivePendingButton = null;
                            resetProjectDivisionDriveButton(targetButton);
                            Swal.fire({
                                icon: 'warning',
                                title: 'OAuth dibatalkan',
                                text: response?.error_description || response?.error || 'OAuth Google dibatalkan.',
                            });

                            return;
                        }

                        exchangeProjectDivisionDriveCode(response.code || '', targetButton);
                    },
                    error_callback: function () {
                        var targetButton = projectDivisionDrivePendingButton;

                        projectDivisionDrivePendingButton = null;
                        resetProjectDivisionDriveButton(targetButton);

                        Swal.fire({
                            icon: 'warning',
                            title: 'OAuth dibatalkan',
                            text: 'Popup Google OAuth ditutup atau gagal dibuka.',
                        });
                    },
                });
            }

            projectDivisionDriveCodeClient.requestCode();
        };
        var exchangeProjectDivisionDriveCode = function (code, button) {
            if (! code) {
                projectDivisionDrivePendingButton = null;
                resetProjectDivisionDriveButton(button);
                Swal.fire({
                    icon: 'warning',
                    title: 'OAuth gagal',
                    text: 'Authorization code Google tidak diterima.',
                });

                return;
            }

            $.ajax({
                url: projectDivisionDriveExchangeCodeUrl,
                type: 'POST',
                data: {
                    code: code,
                    redirect_uri: window.location.origin,
                },
                headers: {
                    'X-CSRF-TOKEN': @json(csrf_token()),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                success: function (response) {
                    projectDivisionDriveAccessToken = response.access_token || '';
                    projectDivisionDrivePendingButton = null;
                    resetProjectDivisionDriveButton(button);

                    if (! projectDivisionDriveAccessToken) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'OAuth gagal',
                            text: 'Access token Google tidak diterima dari server.',
                        });

                        return;
                    }

                    showProjectDivisionDriveModal(button);
                },
                error: function (xhr) {
                    projectDivisionDrivePendingButton = null;
                    resetProjectDivisionDriveButton(button);
                    Swal.fire({
                        icon: 'error',
                        title: 'OAuth gagal',
                        text: xhr.responseJSON?.message || 'Gagal menyimpan OAuth Google.',
                    });
                },
            });
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
            $('#projectTaskEventDivisionId').val($(button).attr('data-event-division-id') || '');
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
            $('#projectTaskEventDivisionId').val('');
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

        $(document).on('click', '.js-project-division-drive', function () {
            if ($(this).attr('data-drive-configured') === 'true') {
                showProjectDivisionDriveModal(this);

                return;
            }

            requestProjectDivisionDriveOAuth(this);
        });

        $('#projectDivisionDrivePickParent').on('click', openProjectDivisionDrivePicker);

        $('#projectDivisionDriveProjectFolderName, #projectDivisionDriveDivisionFolderName').on('input', function () {
            if (! projectDivisionDriveActiveButton) {
                return;
            }

            updateProjectDivisionDriveFolderPath(projectDivisionDriveActiveButton);
        });

        $('#projectDivisionDriveUrl').on('input', refreshProjectDivisionDriveOpenButton);

        $('#projectDivisionDriveOpenUrl').on('click', function () {
            var driveUrl = $('#projectDivisionDriveUrl').val();

            if (! driveUrl) {
                return;
            }

            window.open(driveUrl, '_blank', 'noopener,noreferrer');
        });

        $('#projectDivisionDriveCreateFolder').on('click', function () {
            if (! projectDivisionDriveActiveButton) {
                return;
            }

            createProjectDivisionDriveFolder(projectDivisionDriveActiveButton);
        });

        $('#projectDivisionDriveForm').on('submit', function (event) {
            event.preventDefault();

            var form = $(this);
            var submitButton = $('#projectDivisionDriveSubmit');

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
                        syncProjectDivisionDriveButton(projectDivisionDriveActiveButton, response.google_drive_url || $('#projectDivisionDriveUrl').val(), response.folder_id || $('#projectDivisionDriveFolderId').val());
                        hideModal('#projectDivisionDriveModal');

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 1100,
                            showConfirmButton: false,
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
                    submitButton.prop('disabled', false).text('Simpan URL');
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
                        text: xhr.responseJSON?.message || 'Gagal mengubah task division.',
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
