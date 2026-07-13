@php
    $taskListKanbanGroups = collect($taskListKanbanGroups ?? []);
@endphp

<div class="project-kanban-page">
    <div class="row kanban-bx">
        @foreach ($taskListKanbanGroups as $kanbanGroup)
            @php
                $kanbanTasks = collect($kanbanGroup['items'] ?? []);
                $dotClass = (string) ($kanbanGroup['dot_class'] ?? 'text-secondary');
                $progressClass = (string) ($kanbanGroup['progress_class'] ?? 'bg-secondary');
                $progress = (int) ($kanbanGroup['progress'] ?? 0);
            @endphp

            <div class="col">
                <div class="kanbanPreview-bx">
                    <div class="sub-card align-items-center d-flex justify-content-between mb-4">
                        <div>
                            <h4 class="fs-20 mb-0 font-w600">{{ $kanbanGroup['label'] }} (<span class="totalCount">{{ $kanbanTasks->count() }}</span>)</h4>
                        </div>
                        <div class="plus-bx">
                            <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#taskFormModal" data-task-form-mode="create"><i class="fas fa-plus"></i></a>
                        </div>
                    </div>

                    <div class="draggable-zone dropzoneContainer" data-kanban-status="{{ $kanbanGroup['status'] }}">
                        @forelse ($kanbanTasks as $task)
                            <div class="card project-kanban-card draggable-handle draggable" data-task-status="{{ $task['status'] ?? '' }}" data-status-update-url="{{ $task['status_update_url'] ?? '' }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="{{ $dotClass }} project-kanban-task-title">
                                            <i class="fa fa-circle me-2 fs-10"></i>
                                            {{ $task['title'] ?? '-' }}
                                        </span>
                                        <div class="dropdown project-kanban-card-actions">
                                            <button type="button" class="btn btn-link p-0 border-0 project-kanban-dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <circle cx="3.5" cy="11.5" r="2.5" transform="rotate(-90 3.5 11.5)" fill="#717579"/>
                                                    <circle cx="11.5" cy="11.5" r="2.5" transform="rotate(-90 11.5 11.5)" fill="#717579"/>
                                                    <circle cx="19.5" cy="11.5" r="2.5" transform="rotate(-90 19.5 11.5)" fill="#717579"/>
                                                </svg>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-right">
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
                                    <p class="font-w600 fs-18">
                                        <a type="button" data-bs-toggle="modal" data-bs-target="#taskDetailsModal" data-task='@json($task)' class="text-black js-task-details">
                                            {{ ($task['description'] ?? '') !== '' ? $task['description'] : ($task['project_name'] ?? '-') }}
                                        </a>
                                    </p>
                                    <div class="progress default-progress my-4">
                                        <div class="progress-bar {{ $progressClass }} progress-animated" style="width: {{ $progress }}%; height:10px;" role="progressbar">
                                            <span class="sr-only">{{ $progress }}% Complete</span>
                                        </div>
                                    </div>
                                    <div class="row justify-content-between align-items-center kanban-user">
                                        <div class="col-6">
                                            <span class="badge light badge-sm {{ ($task['task_category'] ?? '') === 'project' ? 'badge-primary' : 'badge-secondary' }}">{{ $task['task_category_label'] ?? 'Daily Task' }}</span>
                                            @if ($task['is_overtime_task'] ?? false)
                                                <span class="badge badge-sm badge-danger light ms-1">{{ $task['overtime_label'] ?? 'Overtime' }}</span>
                                            @endif
                                        </div>
                                        <div class="col-6 d-flex justify-content-end">
                                            <span class="fs-14"><i class="{{ ($task['is_completed'] ?? false) ? 'far fa-check-circle' : 'far fa-clock' }} me-2"></i>{{ $task['due_label'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="kanban-empty-state d-flex align-items-center justify-content-center text-center px-3">
                                <p class="fs-14 text-muted mb-0">{{ $kanbanGroup['empty'] }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
