@php
    $taskListItems = collect($taskListItems ?? []);
    $taskListOngoingItems = collect($taskListOngoingItems ?? []);
    $taskListDoneItems = collect($taskListDoneItems ?? []);
    $taskListPastIncompleteCount = (int) ($taskListPastIncompleteCount ?? 0);
    $taskListPageSize = 5;
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

@if ($taskListPastIncompleteCount > 0)
    <div class="alert alert-warning outline-dashed border-2 py-3 px-3 mt-3 mb-3 text-dark d-flex align-items-center">
        <div class="clearfix">
            <i class="fa fa-info-circle fs-3 text-warning"></i>
        </div>
        <div class="mx-3">
            <h6 class="mb-0 fw-semibold">Pending Tasks Reminder!</h6>
            <p class="mb-0">You have {{ $taskListPastIncompleteCount }} active task from previous months. Wrap them up to keep your records up to date.</p>
        </div>
    </div>
@endif

<div class="alert alert-success outline-dashed border-2 py-3 px-3 mt-3 mb-3 text-dark d-flex align-items-center">
    <div class="clearfix">
        <i class="fa fa-info-circle fs-3 text-success"></i>
    </div>
    <div class="mx-3">
        <h6 class="mb-0 fw-semibold">Ready for a Great Day?</h6>
        <p class="mb-0">What's on your agenda today? Add your tasks to keep your workflow organized. <a class="text-success" type="button" data-bs-toggle="modal" data-bs-target="#taskFormModal" data-task-form-mode="create">Add Task</a></p>
    </div>
</div>

<div class="d-flex justify-content-end mb-3">
    <button type="button" data-bs-toggle="modal" data-bs-target="#taskFormModal" data-task-form-mode="create" class="btn btn-success light">Add Task</button>
</div>

@foreach ($taskGroups as $taskGroup)
    @php
        $taskGroupPageCount = (int) ceil($taskGroup['items']->count() / $taskListPageSize);
    @endphp
    <div class="tab-pane {{ $taskGroup['active'] ? 'active show' : '' }} fade" id="{{ $taskGroup['id'] }}" data-task-list-page-size="{{ $taskListPageSize }}" data-task-current-page="1">
        @forelse ($taskGroup['items'] as $task)
            <div class="d-flex border-bottom flex-wrap py-3 align-items-center px-3 list-row js-task-list-row {{ $loop->iteration > $taskListPageSize ? 'is-task-list-hidden' : '' }}">
                <div class="col-xl-8 col-xxl-8 col-lg-8 col-sm-8 d-flex gap-3 align-items-center">
                    <div class="avatar avatar-lg {{ $task['is_completed'] ? 'bg-light' : 'bg-primary-subtle' }} d-grid border-0 rounded project-task-date-box">
                        <div class="d-grid">
                            <p class="fs-18 {{ $task['is_completed'] ? 'text-black' : 'text-primary' }} mb-0">{{ $task['date_day'] }}</p>
                            <span class="fs-12 {{ $task['is_completed'] ? 'text-black' : 'text-primary' }} lh-1 mt-1">{{ $task['due_label'] }}</span>
                        </div>
                    </div>
                    <div>
                        <h4 class="fs-16 project-task-title">
                            <a type="button" data-bs-toggle="modal" data-bs-target="#taskDetailsModal" data-task='@json($task)' class="text-black js-task-details">{{ $task['title'] }}</a>
                            @if ($task['is_overtime_task'] ?? false)
                                <span class="badge badge-sm badge-danger light ms-2 align-middle">{{ $task['overtime_label'] ?? 'Overtime' }}</span>
                            @endif
                        </h4>
                        <span class="fs-14 me-2">
                            <span class="{{ $task['status_class'] }} fw-semibold">{{ $task['status_label'] }}</span>
                            <span class="ps-3 pe-3 fs-14">{{ $task['project_name'] }}</span>
                            <span class="{{ $task['priority_class'] }} fw-semibold">Priority: {{ $task['priority_label'] }}</span>
                        </span>
                        @if ($task['is_assigned_by_other_user'] ?? false)
                            <p class="fs-13 text-muted mb-0 mt-1">Assign by : <span class="fw-semibold">{{ $task['assigned_by_label'] }}</span></p>
                        @endif
                    </div>
                </div>
                <div class="col-xl-12 col-xxl-4 col-lg-4 col-12 d-flex gap-3 align-items-center justify-content-xxl-end justify-content-xl-between justify-content-lg-end justify-content-between mt-xxl-0 mt-xl-3 mt-lg-0 mt-3">
                    @if (! $task['is_completed'] && ($task['can_manage_from_task_list'] ?? true))
                        <button type="button" data-bs-toggle="modal" data-bs-target="#taskCompleteModal" data-complete-url="{{ $task['complete_url'] }}" class="btn play-btn btn-lg btn-light text-primary fs-14 js-task-complete">
                            <i class="las la-caret-right text-primary fs-24"></i>Mark as Done
                        </button>
                    @endif
                    <div class="dropdown">
                        <a href="javascript:void(0)" class="btn btn-lg light btn-square" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg width="6" height="26" viewBox="0 0 6 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6 3C6 4.65685 4.65685 6 3 6C1.34315 6 0 4.65685 0 3C0 1.34315 1.34315 0 3 0C4.65685 0 6 1.34315 6 3Z" fill="var(--bs-body-color)"></path>
                                <path d="M6 13C6 14.6569 4.65685 16 3 16C1.34315 16 0 14.6569 0 13C0 11.3431 1.34315 10 3 10C4.65685 10 6 11.3431 6 13Z" fill="var(--bs-body-color)"></path>
                                <path d="M6 23C6 24.6569 4.65685 26 3 26C1.34315 26 0 24.6569 0 23C0 21.3431 1.34315 20 3 20C4.65685 20 6 21.3431 6 23Z" fill="var(--bs-body-color)"></path>
                            </svg>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item js-task-details" type="button" data-bs-toggle="modal" data-bs-target="#taskDetailsModal" data-task='@json($task)'>View More</a>
                            @if ($task['can_manage_from_task_list'] ?? true)
                                <a class="dropdown-item js-task-edit" type="button" data-bs-toggle="modal" data-bs-target="#taskFormModal" data-task='@json($task)'>Update Task</a>
                            @endif
                            @if ($task['can_delete_from_task_list'] ?? true)
                                <a class="dropdown-item js-task-delete" type="button" data-bs-toggle="modal" data-bs-target="#taskDeleteModal" data-delete-url="{{ $task['delete_url'] }}">Delete Task</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="project-task-empty d-flex align-items-center justify-content-center text-center">
                <div>
                    <h5 class="mb-1">{{ $taskGroup['empty'] }}</h5>
                    <p class="mb-0 fs-13">Use Add Task to create a new daily or project task.</p>
                </div>
            </div>
        @endforelse

        @if ($taskGroupPageCount > 1)
            <div class="project-task-list-footer dataTables_wrapper no-footer">
                <div class="dataTables_info js-task-list-page-summary">
                    Showing 1 to {{ min($taskListPageSize, $taskGroup['items']->count()) }} of {{ $taskGroup['items']->count() }} entries
                </div>
                <div class="dataTables_paginate paging_simple_numbers js-task-list-pagination">
                    <a
                        href="javascript:void(0)"
                        class="paginate_button previous disabled js-task-list-page-button"
                        data-task-page-action="previous"
                        data-task-page-item="previous"
                        aria-label="Previous page"
                    ><i class="fa-solid fa-angle-left"></i></a>
                    <span>
                        @for ($page = 1; $page <= $taskGroupPageCount; $page++)
                            <a
                                href="javascript:void(0)"
                                class="paginate_button {{ $page === 1 ? 'current' : '' }} js-task-list-page-button"
                                data-task-page="{{ $page }}"
                                data-task-page-item="{{ $page }}"
                                @if ($page === 1) aria-current="page" @endif
                            >{{ $page }}</a>
                        @endfor
                    </span>
                    <a
                        href="javascript:void(0)"
                        class="paginate_button next js-task-list-page-button"
                        data-task-page-action="next"
                        data-task-page-item="next"
                        aria-label="Next page"
                    ><i class="fa-solid fa-angle-right"></i></a>
                </div>
            </div>
        @endif
    </div>
@endforeach
