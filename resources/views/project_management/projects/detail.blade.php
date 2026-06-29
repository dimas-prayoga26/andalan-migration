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
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(20, 24, 40, .06);
        overflow: hidden;
    }

    .project-detail-card .card-header {
        padding: 24px 24px 0;
    }

    .project-detail-card .card-body {
        padding: 22px 24px 24px;
    }

    .project-detail-folder {
        width: 34px;
    }

    .project-folder-avatar {
        width: 44px;
        height: 44px;
        border: 1px solid #e8ecf4;
        background: #fff;
    }

    .project-meta-list {
        margin-top: 22px;
    }

    .project-meta-item {
        display: grid;
        grid-template-columns: 44% 1fr;
        gap: 14px;
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
        display: grid;
        grid-template-columns: 190px 1fr;
        align-items: center;
        gap: 24px;
    }

    .project-summary-ring {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        position: relative;
        background: var(--project-summary-gradient);
        margin: 0 auto;
    }

    .project-summary-ring::before {
        content: "";
        position: absolute;
        inset: 14px;
        border-radius: 50%;
        background: #fff;
    }

    .project-summary-ring-content {
        position: relative;
        text-align: center;
    }

    .project-summary-ring-content strong {
        display: block;
        color: #111827;
        font-size: 28px;
        line-height: 1;
    }

    .project-summary-legend {
        font-size: 13px;
    }

    .project-summary-legend-item {
        display: grid;
        grid-template-columns: 12px 1fr auto;
        align-items: center;
        gap: 10px;
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
        min-height: 260px;
        position: relative;
    }

    .project-tasks-over-time-chart canvas {
        width: 100% !important;
        height: 260px !important;
    }

    .project-chart-filter {
        min-width: 132px;
        border-color: #e8ecf4;
        border-radius: 12px;
        color: #6b7280;
        font-size: 13px;
    }

    .project-department-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(20, 24, 40, .06);
        overflow: hidden;
    }

    .project-department-card .card-header {
        align-items: flex-start;
        padding: 22px 24px 0;
    }

    .project-department-card .card-body {
        max-height: 330px;
        overflow-y: auto;
        padding: 18px 24px 24px;
    }

    .project-department-task-title {
        max-width: 245px;
    }

    .project-department-progress {
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 16px;
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

    .project-task-action {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }

    .project-task-toggle:disabled {
        opacity: .35;
    }

    .project-department-add-task {
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        padding: 7px 13px;
    }

    .project-department-drive {
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        padding: 7px 16px;
    }

    .project-task-menu .dropdown-menu {
        min-width: 140px;
    }

    @media (max-width: 575.98px) {
        .project-summary-content {
            grid-template-columns: 1fr;
            gap: 18px;
        }

        .project-meta-item {
            grid-template-columns: 1fr;
            gap: 3px;
        }

        .project-department-task-title {
            max-width: 170px;
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
    $summaryTotalTasks = max(1, (int) ($projectSummary['total_tasks'] ?? 0));
    $summaryGradientSegments = [];
    $summaryGradientCursor = 0;

    foreach ($projectDepartmentGroups as $departmentIndex => $departmentGroup) {
        $departmentTotal = (int) ($departmentGroup['total_tasks'] ?? 0);
        if ($departmentTotal <= 0) {
            continue;
        }

        $summaryGradientNext = min(100, $summaryGradientCursor + (($departmentTotal / $summaryTotalTasks) * 100));
        $summaryGradientSegments[] = $departmentPalette[$departmentIndex % count($departmentPalette)].' '.$summaryGradientCursor.'% '.$summaryGradientNext.'%';
        $summaryGradientCursor = $summaryGradientNext;
    }

    $summaryGradient = count($summaryGradientSegments) > 0
        ? 'conic-gradient('.implode(', ', $summaryGradientSegments).', #eef1f7 '.$summaryGradientCursor.'% 100%)'
        : 'conic-gradient(#eef1f7 0 100%)';
@endphp

<div class="tab-content" id="tabContentMyProfileBottom">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Projects</h5>
        <a href="{{ route('project_management.projects') }}" class="btn btn-sm btn-light">Back to Projects</a>
    </div>

    <div class="row project-detail-overview-row mb-4">
        <div class="col-xxl-4 col-lg-6">
            <div class="card project-detail-card h-100">
                <div class="card-header pb-0 border-0">
                    <div class="clearfix d-flex">
                        <div class="project-folder-avatar rounded me-3 d-flex align-items-center justify-content-center">
                            <img src="{{ asset('assets/images/files/folder.avif') }}" class="project-detail-folder" alt="">
                        </div>
                        <div class="clearfix">
                            <h4 class="mb-1 fw-semibold">{{ $projectDetail['name'] ?? '-' }}</h4>
                            <span class="small">{{ $projectDetail['subtitle'] ?? '-' }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="project-meta-list">
                        <div class="project-meta-item">
                            <span class="project-meta-label">Status Badge</span>
                            <span class="project-meta-value">{{ $projectDetail['status_label'] ?? '-' }}</span>
                        </div>
                        <div class="project-meta-item">
                            <span class="project-meta-label">Client Name</span>
                            <span class="project-meta-value">{{ $projectDetail['client_name'] ?? '-' }}</span>
                        </div>
                        <div class="project-meta-item">
                            <span class="project-meta-label">Live Event Dates</span>
                            <span class="project-meta-value">{{ $projectDetail['live_event_date_label'] ?? '-' }} ({{ $projectDetail['live_event_duration_label'] ?? '-' }})</span>
                        </div>
                        <div class="project-meta-item">
                            <span class="project-meta-label">Project Lifecycle</span>
                            <span class="project-meta-value">{{ $projectDetail['date_label'] ?? '-' }} ({{ $projectDetail['duration_label'] ?? '-' }})</span>
                        </div>
                        <div class="project-meta-item">
                            <span class="project-meta-label">Event Lead / PIC</span>
                            <span class="project-meta-value">{{ $projectDetail['pic_label'] ?? '-' }}</span>
                        </div>
                        <div class="project-meta-item">
                            <span class="project-meta-label">Core Team</span>
                            <span class="project-meta-value">{{ $projectDetail['team_count'] ?? 0 }} Staff</span>
                        </div>
                    </div>
                    <div class="clearfix mt-3">
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
            <div class="card project-detail-card h-100">
                <div class="card-header pb-0 border-0">
                    <div class="clearfix">
                        <h4 class="card-title mb-0">Tasks Summary</h4>
                        <small class="d-block">{{ $projectSummary['overdue_tasks'] ?? 0 }} Overdue Tasks</small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="project-summary-content">
                        <div class="project-summary-ring" style="--project-summary-gradient: {{ $summaryGradient }};">
                            <div class="project-summary-ring-content">
                                <strong>{{ $projectSummary['total_tasks'] ?? 0 }}</strong>
                                <span class="fs-13">Total</span>
                            </div>
                        </div>

                        <div class="project-summary-legend">
                            @forelse ($projectDepartmentGroups as $departmentGroup)
                                <div class="project-summary-legend-item">
                                    <span class="project-summary-legend-color" style="background: {{ $departmentPalette[$loop->index % count($departmentPalette)] }};"></span>
                                    <span>{{ $departmentGroup['name'] }}</span>
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
            <div class="card project-detail-card h-100">
                <div class="card-header pb-0 border-0 d-flex justify-content-between align-items-start">
                    <div class="clearfix">
                        <h4 class="card-title mb-0">Tasks Over Time</h4>
                        <div class="d-flex align-items-center gap-3 fs-13">
                            <span><i class="fa fa-minus text-danger me-1"></i>Incomplete</span>
                            <span><i class="fa fa-minus text-primary me-1"></i>Complete</span>
                        </div>
                    </div>
                    <select class="form-select project-chart-filter" aria-label="Tasks over time period">
                        <option selected>{{ $projectTaskTimeline['month_label'] ?? '-' }}</option>
                    </select>
                </div>
                <div class="card-body">
                    <div class="project-tasks-over-time-chart">
                        <canvas
                            id="projectTasksOverTimeChart"
                            data-chart-labels='@json($projectTaskTimeline['labels'] ?? [])'
                            data-completed-series='@json($projectTaskTimeline['completed'] ?? [])'
                            data-incomplete-series='@json($projectTaskTimeline['incomplete'] ?? [])'></canvas>
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
                        <div class="clearfix text-end">
                            @if ($departmentGroup['is_own_department'])
                                <div class="d-flex align-items-center justify-content-end gap-2 flex-wrap">
                                    <button type="button" class="btn btn-light project-department-drive">Drive</button>
                                    <button
                                        type="button"
                                        class="btn btn-success light project-department-add-task js-project-task-create"
                                        data-bs-toggle="modal"
                                        data-bs-target="#projectTaskFormModal"
                                        data-store-url="{{ $departmentGroup['store_url'] }}"
                                        data-department-name="{{ $departmentGroup['name'] }}">
                                        + Add Task
                                    </button>
                                </div>
                            @else
                                <span class="badge badge-sm badge-light">View Only</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="project-department-progress">
                            <span class="text-gray fw-semibold">{{ $departmentGroup['completed_tasks'] }} / {{ $departmentGroup['total_tasks'] }} Completed <span class="text-success">({{ $departmentGroup['completion_rate'] }}%)</span></span>
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

@endsection

@section('script')

@php
    $dashboardJsPath = public_path('assets/js/dashboard.js');
    $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
@endphp
<script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(function () {
        var projectTaskDeleteUrl = '';

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

            $('#projectTaskFormTitle').text('Create New Task');
            $('#projectTaskForm').attr('action', $(button).attr('data-store-url') || '');
            $('#projectTaskFormMethod').val('POST');
            $('#projectTaskTitle').val('');
            $('#projectTaskDescription').val('');
            $('#projectTaskBlockers').val('');
            $('#projectTaskAttachment').val('');
            $('#projectTaskPriority').val('medium');
            $('#projectTaskStatus').val('pending');
            $('#projectTaskStartDate').val(currentDate);
            $('#projectTaskDueDate').val(currentDate);
        };
        var fillProjectTaskForm = function (button) {
            var task = parseJsonAttribute($(button).attr('data-task'), {});

            $('#projectTaskFormTitle').text('Update Task');
            $('#projectTaskForm').attr('action', task.update_url || '');
            $('#projectTaskFormMethod').val('PUT');
            $('#projectTaskTitle').val(task.title || '');
            $('#projectTaskDescription').val(task.description || '');
            $('#projectTaskBlockers').val(task.blockers || '');
            $('#projectTaskAttachment').val(task.attachment_path || '');
            $('#projectTaskPriority').val(task.priority || 'medium');
            $('#projectTaskStatus').val(task.status || 'pending');
            $('#projectTaskStartDate').val(task.start_date || todayDate());
            $('#projectTaskDueDate').val(task.due_date || todayDate());
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
        var tasksOverTimeCanvas = document.getElementById('projectTasksOverTimeChart');

        if (tasksOverTimeCanvas && typeof Chart !== 'undefined') {
            var tasksOverTimeContext = tasksOverTimeCanvas.getContext('2d');
            var completedGradient = tasksOverTimeContext.createLinearGradient(0, 0, 0, 260);
            completedGradient.addColorStop(0, 'rgba(36, 69, 199, .24)');
            completedGradient.addColorStop(1, 'rgba(36, 69, 199, 0)');
            var incompleteGradient = tasksOverTimeContext.createLinearGradient(0, 0, 0, 260);
            incompleteGradient.addColorStop(0, 'rgba(255, 91, 138, .26)');
            incompleteGradient.addColorStop(1, 'rgba(255, 91, 138, 0)');

            new Chart(tasksOverTimeContext, {
                type: 'line',
                data: {
                    labels: parseProjectChartData(tasksOverTimeCanvas.getAttribute('data-chart-labels')),
                    datasets: [
                        {
                            label: 'Incomplete',
                            data: parseProjectChartData(tasksOverTimeCanvas.getAttribute('data-incomplete-series')),
                            borderColor: '#ff5b8a',
                            backgroundColor: incompleteGradient,
                            borderWidth: 3,
                            fill: true,
                            pointRadius: 0,
                            tension: .45,
                        },
                        {
                            label: 'Complete',
                            data: parseProjectChartData(tasksOverTimeCanvas.getAttribute('data-completed-series')),
                            borderColor: '#2445c7',
                            backgroundColor: completedGradient,
                            borderWidth: 3,
                            fill: true,
                            pointRadius: 0,
                            tension: .45,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                            },
                            ticks: {
                                color: '#6b7280',
                                font: {
                                    size: 11,
                                },
                            },
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(148, 163, 184, .18)',
                                borderDash: [4, 4],
                            },
                            ticks: {
                                precision: 0,
                                color: '#6b7280',
                                font: {
                                    size: 11,
                                },
                            },
                        },
                    },
                },
            });
        }

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

        $('#projectTaskForm').on('submit', function (event) {
            event.preventDefault();

            var form = $(this);
            var formData = new FormData(this);
            var submitButton = $('#projectTaskFormSubmit');

            $.ajax({
                url: form.attr('action'),
                type: formData.get('_method') || 'POST',
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
