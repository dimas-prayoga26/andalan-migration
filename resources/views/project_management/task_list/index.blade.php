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
<link rel="stylesheet" href="https://unpkg.com/antd@6.2.3/dist/antd.css">
<style>
    .project-task-list-card .list-row:last-child {
        border-bottom: 0 !important;
    }

    .project-task-title {
        max-width: 680px;
        line-height: 1.45;
    }

    .project-task-date-box {
        min-width: 84px;
        min-height: 66px;
    }

    .project-task-empty {
        min-height: 170px;
    }

    .project-week-plan-title {
        max-width: 180px;
    }

    .project-kanban-page {
        width: 100%;
        overflow-x: hidden;
    }

    .project-kanban-page .kanban-bx {
        display: flex;
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        flex-wrap: nowrap;
        align-items: flex-start;
        gap: 1.25rem;
        padding-bottom: 0.5rem;
        margin-left: 0;
        margin-right: 0;
        touch-action: pan-x pan-y;
        -webkit-overflow-scrolling: touch;
    }

    .project-kanban-page .kanban-bx .col {
        width: 360px;
        min-width: 360px;
        flex-grow: 0;
        flex-shrink: 0;
        flex-basis: 360px;
        padding-left: 0;
        padding-right: 0;
    }

    .project-kanban-page .kanban-bx .col .project-kanban-card {
        display: flex;
        height: 230px;
        cursor: grab;
        will-change: transform;
    }

    .project-kanban-page .draggable.card {
        transition: none;
    }

    .project-kanban-page .kanban-bx .col .project-kanban-card .card-body {
        display: flex;
        flex-direction: column;
        min-height: 0;
    }

    .project-kanban-page .kanban-bx .col .project-kanban-card p.font-w600 {
        display: -webkit-box;
        min-height: 58px;
        overflow: hidden;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .project-kanban-page .kanban-bx .col .project-kanban-card .progress {
        flex-shrink: 0;
    }

    .project-kanban-page .kanban-bx .col .project-kanban-card .kanban-user {
        margin-top: auto;
        flex-shrink: 0;
    }

    .project-kanban-page .project-kanban-task-title {
        display: inline-flex;
        align-items: center;
        max-width: 260px;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .project-kanban-page .project-kanban-card-actions,
    .project-kanban-page .project-kanban-card-actions * {
        cursor: pointer;
    }

    .project-kanban-page .project-kanban-card-actions .dropdown-menu {
        z-index: 1080;
    }

    .project-kanban-page .kanban-bx .col .card.draggable-source--is-dragging {
        cursor: grabbing;
        opacity: 0.45;
    }

    .draggable-mirror {
        z-index: 1060 !important;
        pointer-events: none;
        cursor: grabbing;
        margin: 0 !important;
        transition: none !important;
        transform: none !important;
    }

    .project-kanban-page .kanban-user .users {
        display: flex;
        padding-left: 0;
        margin-bottom: 0;
        list-style: none;
    }

    .project-kanban-page .kanban-user .users li {
        margin-right: -10px;
    }

    .project-kanban-page .kanban-user .users li img {
        border-radius: 32px;
        height: 32px;
        width: 32px;
        border: 2px solid #fff;
        object-fit: cover;
    }

    .project-kanban-page .dropzoneContainer {
        min-height: 96px;
    }

    .project-kanban-page .kanbanPreview-bx > .sub-card {
        margin-bottom: 1.5rem;
        min-height: 32px;
    }

    .project-kanban-page .kanban-empty-state {
        min-height: 235px;
        border: 1px dashed #d8dde8;
        border-radius: 1rem;
        background: rgba(245, 248, 253, 0.5);
    }

    .project-kanban-page .kanban-bx::-webkit-scrollbar {
        background-color: #ececec;
        width: 8px;
        height: 8px;
    }

    .project-kanban-page .kanban-bx::-webkit-scrollbar-thumb {
        background-color: #7e7e7e;
        border-radius: 10px;
    }

    .project-task-calendar-widget .datepicker-days .day.today:not(.active) {
        background: transparent !important;
        color: var(--bs-heading-color) !important;
    }

    .project-task-calendar-widget .datepicker-days .day.today:not(.active)::before,
    .project-task-calendar-widget .datepicker-days .day.today:not(.active)::after {
        opacity: 0;
    }

    @media (max-width: 575.98px) {
        .project-task-date-box {
            min-width: 72px;
        }
    }

    @media (max-width: 767.98px) {
        .project-kanban-page .kanban-bx {
            gap: 0.75rem;
            padding: 0 0.75rem 0.75rem;
            max-width: 100vw;
            scroll-snap-type: x proximity;
            scroll-padding-left: 0.75rem;
            overscroll-behavior-x: contain;
            touch-action: pan-x pan-y;
        }

        .project-kanban-page .kanban-bx .col {
            width: 82vw;
            min-width: 82vw;
            max-width: 82vw;
            scroll-snap-align: start;
        }

        .project-kanban-page .kanban-bx .col .project-kanban-card {
            cursor: default;
            touch-action: pan-x pan-y;
        }
    }
</style>

@endsection

@section('content')

@include('layouts.breadcrumb', [
    'title' => 'Project Management',
    'current' => 'Task List',
    'homeRoute' => 'dashboard',
])

@include('project_management.layouts.profile-index')

@php
    $taskListItems = collect($taskListItems ?? []);
    $taskListOngoingItems = collect($taskListOngoingItems ?? []);
    $taskListDoneItems = collect($taskListDoneItems ?? []);
    $taskListWeekPlanItems = collect($taskListWeekPlanItems ?? []);
    $taskListProjectOptions = collect($taskListProjectOptions ?? []);
    $taskListAssignableStaffOptions = collect($taskListAssignableStaffOptions ?? []);
    $taskListProjectOptionsByEmployee = is_array($taskListProjectOptionsByEmployee ?? null) ? $taskListProjectOptionsByEmployee : [];
    $taskListDefaultAssigneeEmployeeId = (string) ($taskListAssignableStaffOptions->first()['id'] ?? '');
    $taskListMonthOptions = is_array($taskListMonthOptions ?? null) ? $taskListMonthOptions : [];
    $taskListYearOptions = is_array($taskListYearOptions ?? null) ? $taskListYearOptions : [];
    $taskListSelectedMonth = is_string($taskListSelectedMonth ?? null) ? $taskListSelectedMonth : now('Asia/Jakarta')->format('Y-m');
    $taskListSelectedMonthNumber = (int) ($taskListSelectedMonthNumber ?? now('Asia/Jakarta')->month);
    $taskListSelectedYear = (int) ($taskListSelectedYear ?? now('Asia/Jakarta')->year);
    $taskListSelectedMonthLabel = is_string($taskListSelectedMonthLabel ?? null) ? $taskListSelectedMonthLabel : now('Asia/Jakarta')->format('F Y');
    $taskListPastIncompleteCount = (int) ($taskListPastIncompleteCount ?? 0);
    $taskGroups = [
        [
            'id' => 'All',
            'label' => 'All',
            'items' => $taskListItems,
            'active' => false,
            'empty' => 'No task found for this month.',
        ],
        [
            'id' => 'Unfinished',
            'label' => 'Ongoing',
            'items' => $taskListOngoingItems,
            'active' => true,
            'empty' => 'No ongoing task for this month.',
        ],
        [
            'id' => 'Finished',
            'label' => 'Done',
            'items' => $taskListDoneItems,
            'active' => false,
            'empty' => 'No completed task for this month.',
        ],
    ];
@endphp

<div class="tab-content" id="tabContentMyProfileBottom">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Task List</h5>
        <div class="d-flex align-items-center">
            <ul class="nav nav-pills nav-pills-square-sm gap-2" id="myTabView" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="project-list-tab" data-bs-toggle="tab" data-bs-target="#project-list-pane" type="button" role="tab" aria-controls="project-list-pane" aria-selected="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-list">
                            <line x1="8" y1="6" x2="21" y2="6"></line>
                            <line x1="8" y1="12" x2="21" y2="12"></line>
                            <line x1="8" y1="18" x2="21" y2="18"></line>
                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                        </svg>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="project-grid-tab" data-bs-toggle="tab" data-bs-target="#project-grid-pane" type="button" role="tab" aria-controls="project-grid-pane" aria-selected="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-grid">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content" id="myTabContentView">
        <div class="tab-pane fade show active" id="project-list-pane" role="tabpanel" aria-labelledby="project-list-tab" tabindex="0">
            <div class="row">
                <div class="col-xl-8 col-xxl-9">
                    <div class="card plan-list project-task-list-card">
                        <div class="card-header align-items-center d-flex flex-wrap d-block pb-0 border-0">
                            <div>
                                <h4 class="card-title">Task List</h4>
                                <p class="fs-13 mb-0">Turn your daily goals into accomplishments.</p>
                            </div>
                            <div class="card-tabs mt-md-0 mt-3">
                                <ul class="nav nav-pills nav-pills-card gap-1" role="tablist">
                                    @foreach ($taskGroups as $taskGroup)
                                        <li class="nav-item">
                                            <a class="nav-link {{ $taskGroup['active'] ? 'active' : '' }}" data-bs-toggle="tab" href="#{{ $taskGroup['id'] }}" role="tab" aria-selected="{{ $taskGroup['active'] ? 'true' : 'false' }}">{{ $taskGroup['label'] }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <button type="button" class="btn rounded btn-primary mt-xxl-0 mt-xl-3 mt-lg-0 mt-3" data-bs-toggle="modal" data-bs-target="#taskFilterModal">
                                <i class="fa fa-sliders me-2"></i><span id="taskFilterLabel">{{ $taskListSelectedMonthLabel }}</span>
                            </button>
                        </div>

                        <div class="card-body tab-content pt-2" id="taskListItemsPanel">
                            @include('project_management.task_list.partials.task-list-items')
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-xxl-3">
                    <div class="card flex-xl-column flex-md-row flex-column">
                        <div class="card-body widget-events project-task-calendar-widget">
                            <input type="text" class="form-control d-none" id="datetimepicker1">
                        </div>
                        <div class="card-body" id="taskListWeekPlanPanel">
                            @include('project_management.task_list.partials.week-plan')
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="project-grid-pane" role="tabpanel" aria-labelledby="project-grid-tab" tabindex="0">
            <div id="taskListProjectGridPanel">
                @include('project_management.task_list.partials.project-grid')
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="taskFilterModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="taskFilterForm">
                <div class="modal-header">
                    <h5 class="modal-title">Filter Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-0">
                                <label class="form-label">Month <span class="required text-danger">*</span></label>
                                <select class="form-control default-select" name="month" id="taskFilterMonth" required>
                                    @foreach ($taskListMonthOptions as $taskListMonthOption)
                                        <option value="{{ $taskListMonthOption['value'] }}" @selected((int) $taskListMonthOption['value'] === $taskListSelectedMonthNumber)>{{ $taskListMonthOption['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-0">
                                <label class="form-label">Year <span class="required text-danger">*</span></label>
                                <input type="number" class="form-control" name="year" id="taskFilterYear" min="2020" max="2100" value="{{ $taskListSelectedYear }}" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="taskFilterSubmit">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="taskFormModal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="taskForm">
                @csrf
                <input type="hidden" name="_method" id="taskFormMethod" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="taskFormTitle">Create New Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Task Name <span class="required text-danger">*</span></label>
                                <input type="text" class="form-control" name="title" id="taskTitle" placeholder="Contoh: Rekap absensi bulanan" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Task Description</label>
                                <textarea class="form-control" rows="3" name="description" id="taskDescription" placeholder="Tambahkan detail atau konteks pekerjaan"></textarea>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Date <span class="required text-danger">*</span></label>
                                <input type="hidden" name="start_date" id="taskStartDate" required>
                                <input type="hidden" name="due_date" id="taskDueDate" required>
                                <input type="text" class="form-control js-task-date-range-input" id="taskDateRange" placeholder="Select date range" autocomplete="off" readonly required>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Priority <span class="required text-danger">*</span></label>
                                <select class="form-control default-select" name="priority" id="taskPriority" required>
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Task Status <span class="required text-danger">*</span></label>
                                <select class="form-control default-select" name="status" id="taskStatus" required>
                                    <option value="pending">To Do</option>
                                    <option value="in_progress">On Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Attachment</label>
                                <input type="text" class="form-control" name="attachment_path" id="taskAttachment" placeholder="Contoh: Link Google Drive, Figma, atau Docs">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Blockers</label>
                                <input type="text" class="form-control" name="blockers" id="taskBlockers" placeholder="Contoh: Menunggu approval dokumen">
                            </div>
                        </div>
                        @if ($taskListAssignableStaffOptions->count() > 1)
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Assign Staff <span class="required text-danger">*</span></label>
                                    <select class="form-control default-select" name="assigned_employee_id" id="taskAssigneeEmployeeId" required>
                                        @foreach ($taskListAssignableStaffOptions as $staffOption)
                                            <option value="{{ $staffOption['id'] }}">{{ $staffOption['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Task Category <span class="required text-danger">*</span></label>
                                <select class="form-control default-select" name="task_category" id="taskCategory" required>
                                    <option value="daily">Daily Task Report</option>
                                    <option value="project">Project Report</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Project Name</label>
                                <select class="form-control default-select" name="project_id" id="taskProject" disabled>
                                    <option value="">Pilih Nama Project</option>
                                    @foreach ($taskListProjectOptions as $projectOption)
                                        <option value="{{ $projectOption['id'] }}">{{ $projectOption['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" id="taskFormSubmit">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="taskCompleteModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Complete Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h5 class="text-muted fw-bold text-center">Mark this task as done?</h5>
                <p class="form-label text-muted mb-0 text-center">This will move the selected task into the completed task list.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success light" id="taskCompleteButton">Yes, Mark as Done</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="taskDetailsModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Task Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row py-2">
                    <div class="col-4"><span>Task Name</span></div>
                    <div class="col-8"><span class="text-gray fw-semibold" id="taskDetailTitle">-</span></div>
                </div>
                <div class="row py-2">
                    <div class="col-4"><span>Task Description</span></div>
                    <div class="col-8"><span class="text-gray fw-normal" id="taskDetailDescription">-</span></div>
                </div>
                <div class="row py-2">
                    <div class="col-4"><span>Date - Due Date</span></div>
                    <div class="col-8"><span class="text-gray fw-semibold" id="taskDetailDate">-</span></div>
                </div>
                <div class="row py-2">
                    <div class="col-4"><span>Attachment</span></div>
                    <div class="col-8"><span class="text-gray fw-semibold" id="taskDetailAttachment">No attachment</span></div>
                </div>
                <div class="row py-2">
                    <div class="col-4"><span>Blockers</span></div>
                    <div class="col-8"><span class="text-gray fw-normal" id="taskDetailBlockers">-</span></div>
                </div>
                <div class="row py-2">
                    <div class="col-4"><span>Task Category</span></div>
                    <div class="col-8">
                        <span class="text-gray fw-semibold" id="taskDetailCategory">-</span><br>
                        <span class="text-gray fw-normal" id="taskDetailProject">-</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-4"><span>Assigned by</span></div>
                    <div class="col-8"><span class="text-primary fw-semibold" id="taskDetailAssignedBy">-</span></div>
                </div>
                <div class="row py-2">
                    <div class="col-4"><span>Task Status</span></div>
                    <div class="col-8"><span class="fw-semibold" id="taskDetailStatus">-</span></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="taskDeleteModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Delete Task</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5 class="text-muted fw-bold text-center">Delete this task?</h5>
                <p class="form-label text-muted mb-0 text-center">This task will be removed from your task list.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark light" data-bs-dismiss="modal">Nevermind</button>
                <button type="button" class="btn btn-danger light" id="taskDeleteButton">Yes, Delete It</button>
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
<script src="{{ asset('assets-workload/vendor/draggable/draggable.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(function () {
        var taskStoreUrl = @json($taskListStoreUrl);
        var taskFilterUrl = @json(route('project_management.task_list.filter'));
        var taskProjectOptionsByEmployee = @json($taskListProjectOptionsByEmployee);
        var taskDefaultAssigneeEmployeeId = @json($taskListDefaultAssigneeEmployeeId);
        var selectedMonth = @json($taskListSelectedMonth);
        var defaultCalendarDate = @json(now('Asia/Jakarta')->format('Y-m-d'));
        var currentTaskFilters = {
            month: @json($taskListSelectedMonthNumber),
            year: @json($taskListSelectedYear),
        };
        var activeActionUrl = '';
        var isSyncingCalendar = false;
        var kanbanSortableInstance = null;

        function parseTask(button) {
            var taskJson = $(button).attr('data-task') || '{}';
            var task = {};

            try {
                task = JSON.parse(taskJson);
            } catch (error) {
                task = {};
            }

            var currentStatus = $(button).closest('.project-kanban-card').attr('data-task-status') || '';

            if (currentStatus !== '') {
                task.status = currentStatus;
            }

            return task;
        }

        function setTaskSelectValue(selector, value) {
            var selectElement = $(selector);

            if (! selectElement.length) {
                return;
            }

            if ($.fn.selectpicker && selectElement.data('selectpicker')) {
                selectElement.selectpicker('val', value);
                return;
            }

            selectElement.val(value);
        }

        function refreshDefaultSelect(selector) {
            var selectElement = $(selector);

            if ($.fn.selectpicker && selectElement.length && selectElement.data('selectpicker')) {
                selectElement.selectpicker('refresh');
            }
        }

        function refreshTaskFormSelects() {
            [
                '#taskPriority',
                '#taskStatus',
                '#taskCategory',
                '#taskAssigneeEmployeeId',
                '#taskProject',
            ].forEach(refreshDefaultSelect);
        }

        function nullableValue(value) {
            return value || '';
        }

        function taskListPageSize(tabPane) {
            return parseInt($(tabPane).attr('data-task-list-page-size'), 10) || 5;
        }

        function updateTaskListPagination(tabPane, currentPage, totalPages, totalRows) {
            var pane = $(tabPane);
            var start = totalRows === 0 ? 0 : ((currentPage - 1) * taskListPageSize(pane)) + 1;
            var end = Math.min(currentPage * taskListPageSize(pane), totalRows);

            pane.find('.js-task-list-page-summary').text('Showing ' + start + ' - ' + end + ' of ' + totalRows + ' tasks');
            pane.find('[data-task-page-item]').removeClass('active disabled');
            pane.find('[data-task-page-item="' + currentPage + '"]').addClass('active');

            if (currentPage <= 1) {
                pane.find('[data-task-page-item="previous"]').addClass('disabled');
            }

            if (currentPage >= totalPages) {
                pane.find('[data-task-page-item="next"]').addClass('disabled');
            }
        }

        function showTaskListPage(tabPane, pageNumber) {
            var pane = $(tabPane);
            var rows = pane.find('.js-task-list-row');
            var pageSize = taskListPageSize(pane);
            var totalRows = rows.length;
            var totalPages = Math.max(1, Math.ceil(totalRows / pageSize));
            var currentPage = Math.min(Math.max(parseInt(pageNumber, 10) || 1, 1), totalPages);
            var startIndex = (currentPage - 1) * pageSize;
            var endIndex = startIndex + pageSize;

            rows.each(function (index) {
                $(this).toggle(index >= startIndex && index < endIndex);
            });

            pane.attr('data-task-current-page', currentPage);
            updateTaskListPagination(pane, currentPage, totalPages, totalRows);
        }

        function initializeTaskListPagination() {
            $('#taskListItemsPanel .tab-pane').each(function () {
                showTaskListPage(this, $(this).attr('data-task-current-page') || 1);
            });
        }

        function selectedTaskAssigneeEmployeeId() {
            return $('#taskAssigneeEmployeeId').val() || taskDefaultAssigneeEmployeeId;
        }

        function renderTaskProjectOptions(selectedProjectId) {
            var employeeId = selectedTaskAssigneeEmployeeId();
            var projectOptions = taskProjectOptionsByEmployee[employeeId] || [];
            var projectSelect = $('#taskProject');

            projectSelect.empty().append(new Option('Pilih Nama Project', ''));

            projectOptions.forEach(function (projectOption) {
                projectSelect.append(new Option(projectOption.name, projectOption.id));
            });

            if (selectedProjectId) {
                setTaskSelectValue('#taskProject', selectedProjectId);
            }

            refreshDefaultSelect('#taskProject');
        }

        function setProjectFieldState() {
            var isProjectTask = $('#taskCategory').val() === 'project';
            var selectedProjectId = $('#taskProject').val();

            renderTaskProjectOptions(selectedProjectId);
            $('#taskProject').prop('disabled', ! isProjectTask);

            if (! isProjectTask) {
                setTaskSelectValue('#taskProject', '');
            }

            refreshDefaultSelect('#taskProject');
        }

        function resetTaskForm() {
            $('#taskForm')[0].reset();
            $('#taskForm').attr('action', taskStoreUrl);
            $('#taskFormMethod').val('POST');
            $('#taskFormTitle').text('Create New Task');
            $('#taskFormSubmit').removeClass('btn-warning').addClass('btn-success').text('Save changes');
            setTaskSelectValue('#taskStatus', 'pending');
            setTaskSelectValue('#taskPriority', 'medium');
            setTaskSelectValue('#taskCategory', 'daily');
            setTaskSelectValue('#taskAssigneeEmployeeId', taskDefaultAssigneeEmployeeId);
            $('#taskAssigneeEmployeeId').prop('disabled', false);
            setTaskDateRange('', '');
            setProjectFieldState();
            refreshTaskFormSelects();
        }

        function fillTaskForm(task) {
            $('#taskForm').attr('action', task.update_url || taskStoreUrl);
            $('#taskFormMethod').val('PUT');
            $('#taskFormTitle').text('Update Task');
            $('#taskFormSubmit').removeClass('btn-success').addClass('btn-warning').text('Save changes');
            $('#taskTitle').val(nullableValue(task.title));
            $('#taskDescription').val(nullableValue(task.description));
            setTaskDateRange(nullableValue(task.start_date), nullableValue(task.due_date));
            setTaskSelectValue('#taskPriority', nullableValue(task.priority) || 'medium');
            setTaskSelectValue('#taskStatus', nullableValue(task.status) || 'pending');
            $('#taskAttachment').val(nullableValue(task.attachment_path));
            $('#taskBlockers').val(nullableValue(task.blockers));
            setTaskSelectValue('#taskCategory', nullableValue(task.task_category) || 'daily');
            setTaskSelectValue('#taskAssigneeEmployeeId', nullableValue(task.employee_id) || taskDefaultAssigneeEmployeeId);
            $('#taskAssigneeEmployeeId').prop('disabled', true);
            setProjectFieldState();
            setTaskSelectValue('#taskProject', nullableValue(task.project_id));
            refreshTaskFormSelects();
        }

        function fillTaskDetails(task) {
            $('#taskDetailTitle').text(nullableValue(task.title) || '-');
            $('#taskDetailDescription').text(nullableValue(task.description) || '-');
            $('#taskDetailDate').text(nullableValue(task.date_range_label) || '-');
            $('#taskDetailBlockers').text(nullableValue(task.blockers) || '-');
            $('#taskDetailCategory').text(nullableValue(task.task_category_label) || '-');
            $('#taskDetailProject').text(nullableValue(task.project_name) || '-');
            $('#taskDetailAssignedBy').text('@' + (nullableValue(task.assigned_by) || 'self'));
            $('#taskDetailStatus')
                .removeClass('text-danger text-success text-warning')
                .addClass(task.status_class || 'text-warning')
                .text(nullableValue(task.status_label) || '-');

            if (task.attachment_path) {
                $('#taskDetailAttachment').html('<a href="' + task.attachment_path + '" class="text-primary" target="_blank" rel="noopener noreferrer">Open attachment</a>');
            } else {
                $('#taskDetailAttachment').text('No attachment');
            }
        }

        function handleAjaxError(xhr) {
            var message = xhr.responseJSON?.message || 'Gagal memproses permintaan.';

            if (xhr.responseJSON?.errors) {
                var firstError = Object.values(xhr.responseJSON.errors)[0];
                if (Array.isArray(firstError) && firstError.length > 0) {
                    message = firstError[0];
                }
            }

            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: message,
            });
        }

        function hideModal(selector) {
            var modalElement = document.querySelector(selector);

            if (window.bootstrap && modalElement) {
                var modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                modal.hide();

                return;
            }

            $(selector).modal('hide');
        }

        function calendarSelectionForMonth(monthValue) {
            if (typeof moment === 'undefined') {
                return undefined;
            }

            var selectedMoment = moment(monthValue + '-01');
            var todayMoment = moment(defaultCalendarDate);

            if (selectedMoment.isValid() && todayMoment.isValid() && selectedMoment.isSame(todayMoment, 'month')) {
                return todayMoment;
            }

            return selectedMoment;
        }

        function formatTaskDateRangeDisplay(startDateValue, dueDateValue) {
            if (! startDateValue || ! dueDateValue || typeof moment === 'undefined') {
                return '';
            }

            var startDate = moment(startDateValue, 'YYYY-MM-DD');
            var dueDate = moment(dueDateValue, 'YYYY-MM-DD');

            if (! startDate.isValid() || ! dueDate.isValid()) {
                return '';
            }

            return startDate.format('DD/MM/YYYY') + ' - ' + dueDate.format('DD/MM/YYYY');
        }

        function setTaskDateRange(startDateValue, dueDateValue) {
            var startDate = startDateValue || '';
            var dueDate = dueDateValue || startDate;
            var dateRangeInput = $('#taskDateRange');

            $('#taskStartDate').val(startDate);
            $('#taskDueDate').val(dueDate);
            dateRangeInput.val(formatTaskDateRangeDisplay(startDate, dueDate));

            if ($.fn.daterangepicker && dateRangeInput.data('daterangepicker') && typeof moment !== 'undefined' && startDate && dueDate) {
                var startMoment = moment(startDate, 'YYYY-MM-DD');
                var dueMoment = moment(dueDate, 'YYYY-MM-DD');

                if (startMoment.isValid() && dueMoment.isValid()) {
                    dateRangeInput.data('daterangepicker').setStartDate(startMoment);
                    dateRangeInput.data('daterangepicker').setEndDate(dueMoment);
                }
            }
        }

        function initializeTaskDateRangePicker() {
            var dateRangeInput = $('#taskDateRange');

            if (! dateRangeInput.length) {
                return;
            }

            dateRangeInput.val(formatTaskDateRangeDisplay($('#taskStartDate').val(), $('#taskDueDate').val()));

            if (! $.fn.daterangepicker || dateRangeInput.data('daterangepicker-initialized')) {
                return;
            }

            dateRangeInput.daterangepicker({
                autoApply: true,
                autoUpdateInput: false,
                parentEl: '#taskFormModal',
                locale: {
                    format: 'DD/MM/YYYY',
                    cancelLabel: 'Clear'
                }
            });

            dateRangeInput.on('apply.daterangepicker', function (event, picker) {
                setTaskDateRange(picker.startDate.format('YYYY-MM-DD'), picker.endDate.format('YYYY-MM-DD'));
            });

            dateRangeInput.on('cancel.daterangepicker', function () {
                setTaskDateRange('', '');
            });

            dateRangeInput.data('daterangepicker-initialized', true);
        }

        function updateCalendar(monthValue) {
            selectedMonth = monthValue || selectedMonth;

            if (! $.fn.datetimepicker || ! $('#datetimepicker1').length || typeof moment === 'undefined') {
                return;
            }

            var datePicker = $('#datetimepicker1').data('DateTimePicker');
            if (datePicker) {
                isSyncingCalendar = true;
                datePicker.date(calendarSelectionForMonth(selectedMonth));
                isSyncingCalendar = false;
            }
        }

        function syncFiltersFromCalendar(calendarMoment) {
            if (! calendarMoment || ! calendarMoment.isValid || ! calendarMoment.isValid()) {
                return;
            }

            var calendarMonth = calendarMoment.month() + 1;
            var calendarYear = calendarMoment.year();

            if (String(currentTaskFilters.month) === String(calendarMonth) && String(currentTaskFilters.year) === String(calendarYear)) {
                return;
            }

            currentTaskFilters.month = calendarMonth;
            currentTaskFilters.year = calendarYear;

            refreshTaskList();
        }

        function applyTaskListResponse(response) {
            if (! response.fragments) {
                return;
            }

            $('#taskListItemsPanel').html(response.fragments.task_list || '');
            $('#taskListWeekPlanPanel').html(response.fragments.week_plan || '');
            $('#taskListProjectGridPanel').html(response.fragments.project_grid || '');
            $('#taskFilterLabel').text(response.selected_month_label || selectedMonth);
            setTaskSelectValue('#taskFilterMonth', response.selected_month_number || currentTaskFilters.month);
            $('#taskFilterYear').val(response.selected_year || currentTaskFilters.year);
            refreshDefaultSelect('#taskFilterMonth');

            currentTaskFilters.month = response.selected_month_number || currentTaskFilters.month;
            currentTaskFilters.year = response.selected_year || currentTaskFilters.year;
            activeActionUrl = '';

            updateCalendar(response.selected_month);
            initializeTaskListPagination();
            initializeStaticKanbanBoard();
        }

        function shouldUseMobileKanbanScroll() {
            if (typeof window.matchMedia === 'function') {
                return window.matchMedia('(max-width: 767.98px), (pointer: coarse)').matches;
            }

            return window.innerWidth <= 767 || navigator.maxTouchPoints > 0;
        }

        function initializeStaticKanbanBoard() {
            var dropzones = document.querySelectorAll('#taskListProjectGridPanel .dropzoneContainer');
            var draggableCards = document.querySelectorAll('#taskListProjectGridPanel .draggable-handle');

            if (kanbanSortableInstance && typeof kanbanSortableInstance.destroy === 'function') {
                kanbanSortableInstance.destroy();
                kanbanSortableInstance = null;
            }

            if (shouldUseMobileKanbanScroll() || ! dropzones.length || ! draggableCards.length || typeof window.Sortable === 'undefined' || typeof window.Sortable.default === 'undefined') {
                return;
            }

            kanbanSortableInstance = new window.Sortable.default(dropzones, {
                draggable: '.draggable-handle',
                mirror: {
                    appendTo: document.body,
                    constrainDimensions: true,
                },
            });

            preventStaticKanbanActionDrag(kanbanSortableInstance);
            attachStaticKanbanStatusSync(kanbanSortableInstance);
            attachStaticKanbanMirrorPositioning(kanbanSortableInstance);
        }

        function eventStartedFromKanbanAction(event) {
            var candidates = [
                event && event.sensorEvent && event.sensorEvent.target,
                event && event.sensorEvent && event.sensorEvent.originalEvent && event.sensorEvent.originalEvent.target,
                event && event.data && event.data.sensorEvent && event.data.sensorEvent.target,
                event && event.originalEvent && event.originalEvent.target,
                event && event.target,
            ];

            for (var i = 0; i < candidates.length; i += 1) {
                var target = candidates[i];

                if (target && target.closest && target.closest('.project-kanban-card-actions, .project-kanban-card-actions *, .dropdown-menu')) {
                    return true;
                }
            }

            return false;
        }

        function preventStaticKanbanActionDrag(sortableInstance) {
            if (! sortableInstance || typeof sortableInstance.on !== 'function') {
                return;
            }

            sortableInstance.on('drag:start', function (event) {
                if (eventStartedFromKanbanAction(event) && event && typeof event.cancel === 'function') {
                    event.cancel();
                }
            });
        }

        function syncMovedKanbanTaskStatuses() {
            var movedCards = $('#taskListProjectGridPanel .project-kanban-card').filter(function () {
                var card = $(this);
                var targetStatus = card.closest('.dropzoneContainer').attr('data-kanban-status') || '';
                var currentStatus = card.attr('data-task-status') || '';
                var statusUpdateUrl = card.attr('data-status-update-url') || '';

                return statusUpdateUrl !== '' && targetStatus !== '' && targetStatus !== currentStatus;
            });

            if (! movedCards.length) {
                return;
            }

            var pendingRequests = movedCards.length;
            var shouldRefresh = false;

            movedCards.each(function () {
                var card = $(this);
                var targetStatus = card.closest('.dropzoneContainer').attr('data-kanban-status') || '';
                var statusUpdateUrl = card.attr('data-status-update-url') || '';

                card.attr('data-task-status', targetStatus);

                $.ajax({
                    url: statusUpdateUrl,
                    type: 'PATCH',
                    data: {
                        _token: @json(csrf_token()),
                        status: targetStatus,
                    },
                    headers: {
                        'X-CSRF-TOKEN': @json(csrf_token()),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    success: function (response) {
                        if (response.success === true || response.status === true) {
                            shouldRefresh = true;
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Gagal',
                                text: response.message,
                            });
                            shouldRefresh = true;
                        }
                    },
                    error: function (xhr) {
                        handleAjaxError(xhr);
                        shouldRefresh = true;
                    },
                    complete: function () {
                        pendingRequests -= 1;

                        if (pendingRequests <= 0 && shouldRefresh) {
                            refreshTaskList();
                        }
                    },
                });
            });
        }

        function attachStaticKanbanStatusSync(sortableInstance) {
            if (! sortableInstance || typeof sortableInstance.on !== 'function') {
                return;
            }

            sortableInstance.on('sortable:stop', function () {
                window.setTimeout(syncMovedKanbanTaskStatuses, 0);
            });

            sortableInstance.on('drag:stop', function () {
                window.setTimeout(syncMovedKanbanTaskStatuses, 0);
            });
        }

        function extractPointerPosition(value) {
            if (! value) {
                return null;
            }

            if (typeof value.clientX === 'number' && typeof value.clientY === 'number') {
                return {
                    x: value.clientX,
                    y: value.clientY,
                };
            }

            if (value.touches && value.touches.length > 0 && typeof value.touches[0].clientX === 'number' && typeof value.touches[0].clientY === 'number') {
                return {
                    x: value.touches[0].clientX,
                    y: value.touches[0].clientY,
                };
            }

            if (value.changedTouches && value.changedTouches.length > 0 && typeof value.changedTouches[0].clientX === 'number' && typeof value.changedTouches[0].clientY === 'number') {
                return {
                    x: value.changedTouches[0].clientX,
                    y: value.changedTouches[0].clientY,
                };
            }

            return null;
        }

        function getPointerPositionFromEvent(event) {
            var candidates = [
                event && event.sensorEvent,
                event && event.sensorEvent && event.sensorEvent.originalEvent,
                event && event.data && event.data.sensorEvent,
                event && event.data && event.data.sensorEvent && event.data.sensorEvent.originalEvent,
                event && event.originalEvent,
                event && event.data && event.data.originalEvent,
                event,
            ];

            for (var i = 0; i < candidates.length; i += 1) {
                var pointerPosition = extractPointerPosition(candidates[i]);

                if (pointerPosition !== null) {
                    return pointerPosition;
                }
            }

            return null;
        }

        function attachStaticKanbanMirrorPositioning(sortableInstance) {
            if (! sortableInstance || typeof sortableInstance.on !== 'function') {
                return;
            }

            var dragMirror = null;
            var dragOffset = {
                x: 0,
                y: 0,
            };

            function resetMirrorState() {
                dragMirror = null;
                dragOffset = {
                    x: 0,
                    y: 0,
                };
            }

            function positionMirror(event) {
                if (! dragMirror) {
                    dragMirror = document.querySelector('.draggable-mirror');
                }

                if (! dragMirror) {
                    return;
                }

                var pointerPosition = getPointerPositionFromEvent(event);

                if (! pointerPosition) {
                    return;
                }

                dragMirror.style.position = 'fixed';
                dragMirror.style.left = (pointerPosition.x - dragOffset.x) + 'px';
                dragMirror.style.top = (pointerPosition.y - dragOffset.y) + 'px';
                dragMirror.style.right = 'auto';
                dragMirror.style.bottom = 'auto';
                dragMirror.style.transform = 'none';
            }

            function scheduleMirrorPosition(event) {
                positionMirror(event);
                window.requestAnimationFrame(function () {
                    positionMirror(event);
                });
            }

            sortableInstance.on('drag:start', function (event) {
                var sourceRect = event && event.source
                    ? event.source.getBoundingClientRect()
                    : null;
                var pointerPosition = getPointerPositionFromEvent(event);

                if (sourceRect && pointerPosition) {
                    dragOffset = {
                        x: pointerPosition.x - sourceRect.left,
                        y: pointerPosition.y - sourceRect.top,
                    };
                } else if (sourceRect) {
                    dragOffset = {
                        x: sourceRect.width / 2,
                        y: sourceRect.height / 2,
                    };
                }
            });

            sortableInstance.on('mirror:created', function (event) {
                dragMirror = event && event.mirror
                    ? event.mirror
                    : document.querySelector('.draggable-mirror');

                if (dragMirror && event && event.source) {
                    var sourceRect = event.source.getBoundingClientRect();

                    dragMirror.style.width = sourceRect.width + 'px';
                    dragMirror.style.height = sourceRect.height + 'px';
                    dragMirror.style.position = 'fixed';
                    dragMirror.style.left = sourceRect.left + 'px';
                    dragMirror.style.top = sourceRect.top + 'px';
                    dragMirror.style.right = 'auto';
                    dragMirror.style.bottom = 'auto';
                    dragMirror.style.transform = 'none';
                }

                scheduleMirrorPosition(event);
            });

            sortableInstance.on('drag:move', scheduleMirrorPosition);
            sortableInstance.on('mirror:move', scheduleMirrorPosition);
            sortableInstance.on('drag:stop', resetMirrorState);
            sortableInstance.on('mirror:destroy', resetMirrorState);
        }

        function refreshTaskList() {
            return $.ajax({
                url: taskFilterUrl,
                type: 'GET',
                data: currentTaskFilters,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                success: function (response) {
                    if (response.success === true || response.status === true) {
                        applyTaskListResponse(response);
                    }
                },
                error: handleAjaxError,
            });
        }

        function submitAction(url, type, button, loadingText, modalSelector) {
            var originalText = button.text();

            $.ajax({
                url: url,
                type: type,
                data: {
                    _token: @json(csrf_token()),
                },
                headers: {
                    'X-CSRF-TOKEN': @json(csrf_token()),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                beforeSend: function () {
                    button.prop('disabled', true).text(loadingText);
                },
                success: function (response) {
                    if (response.success === true || response.status === true) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 1200,
                            showConfirmButton: false,
                        }).then(function () {
                            hideModal(modalSelector);
                            refreshTaskList();
                        });
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Gagal',
                            text: response.message,
                        });
                    }
                },
                error: handleAjaxError,
                complete: function () {
                    button.prop('disabled', false).text(originalText);
                },
            });
        }

        $(document).on('click', '[data-task-form-mode="create"]', resetTaskForm);
        $('#taskCategory').on('change', setProjectFieldState);
        $('#taskAssigneeEmployeeId').on('change', setProjectFieldState);

        $('#taskFormModal').on('shown.bs.modal', initializeTaskDateRangePicker);

        $(document).on('click', '.js-task-edit', function () {
            fillTaskForm(parseTask(this));
        });

        $(document).on('click', '.js-task-details', function () {
            fillTaskDetails(parseTask(this));
        });

        $('#taskListItemsPanel').on('click', '.js-task-list-page-button', function () {
            var button = $(this);
            var pageItem = button.closest('.page-item');
            var pane = button.closest('.tab-pane');
            var currentPage = parseInt(pane.attr('data-task-current-page'), 10) || 1;
            var action = button.attr('data-task-page-action') || '';
            var nextPage = parseInt(button.attr('data-task-page'), 10) || currentPage;

            if (pageItem.hasClass('disabled') || pageItem.hasClass('active')) {
                return;
            }

            if (action === 'previous') {
                nextPage = currentPage - 1;
            } else if (action === 'next') {
                nextPage = currentPage + 1;
            }

            showTaskListPage(pane, nextPage);
        });

        $(document).on('click', '.js-task-complete', function () {
            activeActionUrl = $(this).attr('data-complete-url') || '';
        });

        $(document).on('click', '.js-task-delete', function () {
            activeActionUrl = $(this).attr('data-delete-url') || '';
        });

        $('#taskFilterForm').on('submit', function (event) {
            event.preventDefault();

            var submitButton = $('#taskFilterSubmit');

            currentTaskFilters.month = $('#taskFilterMonth').val();
            currentTaskFilters.year = $('#taskFilterYear').val();

            $.ajax({
                url: taskFilterUrl,
                type: 'GET',
                data: currentTaskFilters,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                beforeSend: function () {
                    submitButton.prop('disabled', true).text('Memfilter...');
                },
                success: function (response) {
                    if (response.success === true || response.status === true) {
                        applyTaskListResponse(response);
                        hideModal('#taskFilterModal');
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Gagal',
                            text: response.message,
                        });
                    }
                },
                error: handleAjaxError,
                complete: function () {
                    submitButton.prop('disabled', false).text('Save changes');
                },
            });
        });

        $('#taskForm').on('submit', function (event) {
            event.preventDefault();

            var form = $(this);
            var formData = new FormData(this);
            var submitButton = $('#taskFormSubmit');

            if (! formData.get('start_date') || ! formData.get('due_date')) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tanggal belum lengkap',
                    text: 'Pilih date range task terlebih dahulu.',
                });

                return;
            }

            $.ajax({
                url: form.attr('action') || taskStoreUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': formData.get('_token'),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                beforeSend: function () {
                    submitButton.prop('disabled', true).html('Menyimpan...');
                },
                success: function (response) {
                    if (response.success === true || response.status === true) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 1200,
                            showConfirmButton: false,
                        }).then(function () {
                            hideModal('#taskFormModal');
                            refreshTaskList();
                        });
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Gagal',
                            text: response.message,
                        });
                    }
                },
                error: handleAjaxError,
                complete: function () {
                    submitButton.prop('disabled', false).html('Save changes');
                },
            });
        });

        $('#taskCompleteButton').on('click', function () {
            if (activeActionUrl) {
                submitAction(activeActionUrl, 'PATCH', $(this), 'Menyimpan...', '#taskCompleteModal');
            }
        });

        $('#taskDeleteButton').on('click', function () {
            if (activeActionUrl) {
                submitAction(activeActionUrl, 'DELETE', $(this), 'Menghapus...', '#taskDeleteModal');
            }
        });

        if (typeof moment !== 'undefined') {
            moment.updateLocale('en', {
                week: { dow: 1 },
            });
        }

        if ($.fn.datetimepicker && $('#datetimepicker1').length) {
            $('#datetimepicker1').datetimepicker({
                inline: true,
                format: 'L',
                date: calendarSelectionForMonth(selectedMonth),
                icons: {
                    previous: 'las la-angle-left',
                    next: 'las la-angle-right',
                },
            });

            $('#datetimepicker1').on('dp.change dp.update', function (event) {
                if (isSyncingCalendar) {
                    return;
                }

                syncFiltersFromCalendar(event.date || event.viewDate);
            });
        }

        initializeTaskDateRangePicker();
        initializeTaskListPagination();
        initializeStaticKanbanBoard();
    });
</script>

@endsection
