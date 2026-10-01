@php
    $canManageMeetingTasks = $showTaskActions ?? ($showAddTask ?? true);
    $showTaskDeleteActions = $showTaskDeleteActions ?? $canManageMeetingTasks;
    $allowTaskAssigneeEdit = $allowTaskAssigneeEdit ?? true;
    $useFullTaskEditTemplate = $useFullTaskEditTemplate ?? false;
@endphp

<div class="row">
    @foreach ($meetingTaskCards as $division)
        <div class="col-12 col-md-4">
            <div class="card">
                <div class="card-header pb-0 border-0">
                    <div class="clearfix">
                        <h4 class="card-title mb-0">{{ $division['title'] }}</h4>
                        <small class="d-block">{{ $division['subtitle'] }}</small>
                    </div>
                </div>
                <div class="card-body dz-scroll height380">
                    <div class="d-flex justify-content-between mb-3">
                        <div class="clearfix">
                            <span class="text-gray fw-semibold">{{ (int) $division['completed'] }} / {{ (int) $division['total'] }} Completed <span class="text-success">({{ (int) $division['percentage'] }}%)</span></span>
                        </div>
                        @if ($showAddTask ?? true)
                            <div class="clearfix">
                                <a href="javascript:void(0)" class="text-success fw-semibold" data-bs-toggle="modal" data-bs-target="#create" data-meeting-task-category="{{ $division['key'] }}">+ Add Task</a>
                            </div>
                        @endif
                    </div>

                    @forelse ($division['tasks'] as $task)
                        <div class="d-flex align-items-center py-2">
                            <div class="timeline-vr-badge {{ $task['is_completed'] ? 'bg-success' : 'bg-light' }} me-2"></div>
                            <div class="clearfix ms-2">
                                <h6 class="fs-13 mb-0 fw-semibold d-flex align-items-center flex-wrap gap-1">
                                    <span>{{ $task['title'] }}</span>
                                    @if ($task['attachment_path'] ?? false)
                                        <a href="{{ $task['attachment_path'] }}" target="_blank" rel="noopener noreferrer" class="small text-primary fw-semibold" title="{{ $task['attachment_path'] }}" aria-label="Open attachment">
                                            <i class="fa fa-paperclip me-1" aria-hidden="true"></i>Attachment
                                        </a>
                                    @endif
                                </h6>
                                <span class="small">
                                    {{ $task['is_completed'] ? 'Status: Completed' : 'Due '.$task['due_label'] }}
                                    by <span class="text-primary">{{ $task['assignee'] }}</span>
                                </span>
                            </div>
                            <div class="clearfix ms-auto">
                                @if ($canManageMeetingTasks)
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-light btn-square" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Task actions">
                                            <i class="bi bi-grid"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <button type="button" class="dropdown-item js-meeting-task-detail" data-bs-toggle="modal" data-bs-target="#meetingTaskDetailModal" data-task='@json($task)'>View More</button>
                                            @if ($task['can_update'] ?? true)
                                                <button type="button" class="dropdown-item js-meeting-task-edit" data-bs-toggle="modal" data-bs-target="#meetingTaskEditModal" data-task='@json($task)'>Update</button>
                                            @endif
                                            @if ($showTaskDeleteActions && ($task['can_delete'] ?? true))
                                                <button type="button" class="dropdown-item text-danger js-meeting-task-delete" data-bs-toggle="modal" data-bs-target="#meetingTaskDeleteModal" data-task='@json($task)'>Delete</button>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <button type="button" class="btn btn-sm btn-light btn-square" disabled>
                                        <i class="bi bi-grid"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-muted py-2">No task yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endforeach

    @if ($showTaskSummary ?? false)
        <div class="col-12 col-md-4">
            <div class="card">
                <div class="card-header pb-0 border-0">
                    <div class="clearfix">
                        <h4 class="card-title mb-0">Tasks Summary</h4>
                        <small class="d-block">{{ (int) $meetingTaskSummary['overdue'] }} Overdue Tasks</small>
                    </div>
                </div>
                <div class="card-body pb-0">
                    <div class="row align-items-center">
                        <div class="col-sm-6 mb-3">
                            <div id="chartTasksSummary" class="d-flex justify-content-center">
                                <div class="meeting-summary-donut" style="background: conic-gradient({{ $meetingTaskSummary['gradient'] }});" aria-label="Tasks summary chart">
                                    <div class="meeting-summary-donut-content">
                                        <strong>{{ (int) $meetingTaskSummary['total'] }}</strong>
                                        <span>Total</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            @foreach ($meetingTaskSummary['segments'] as $segment)
                                <div class="d-flex justify-content-between mb-3">
                                    <div class="text-black">
                                        <i class="fa-solid fa-square me-1" style="color: {{ $segment['color'] }}"></i> {{ $segment['label'] }}
                                    </div>
                                    <span>{{ (int) $segment['count'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <a href="" target="_blank" class="btn light btn-primary mt-3 m-3 mb-2 btn-lg">Generate MoM PDF</a>
                <div class="mb-3"></div>
            </div>
        </div>
    @endif
</div>

@if ($canManageMeetingTasks)
    <div class="modal fade" id="meetingTaskDetailModal" tabindex="-1" aria-labelledby="meetingTaskDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="meetingTaskDetailModalLabel">Task Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row py-2">
                        <div class="col-4"><span>Task Name</span></div>
                        <div class="col-8"><span class="text-gray fw-semibold" id="meetingTaskDetailTitle">-</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Task Description</span></div>
                        <div class="col-8"><span class="text-gray fw-normal" id="meetingTaskDetailDescription">-</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Date - Due Date</span></div>
                        <div class="col-8"><span class="text-gray fw-semibold" id="meetingTaskDetailDate">-</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Attachment</span></div>
                        <div class="col-8"><span class="text-gray fw-semibold" id="meetingTaskDetailAttachment">No attachment</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Blockers</span></div>
                        <div class="col-8"><span class="text-gray fw-normal" id="meetingTaskDetailBlockers">-</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Task Category</span></div>
                        <div class="col-8">
                            <span class="text-gray fw-semibold" id="meetingTaskDetailCategory">-</span><br>
                            <span class="text-gray fw-normal" id="meetingTaskDetailCategoryDescription">-</span>
                        </div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Assigned by</span></div>
                        <div class="col-8"><span class="text-primary fw-semibold" id="meetingTaskDetailAssignedBy">-</span></div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4"><span>Task Status</span></div>
                        <div class="col-8"><span class="fw-semibold" id="meetingTaskDetailStatus">-</span></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="meetingTaskEditModal" tabindex="-1" aria-labelledby="meetingTaskEditModalLabel" aria-hidden="true">
        <div class="modal-dialog {{ $useFullTaskEditTemplate ? 'modal-lg' : 'modal-dialog-centered' }}" role="document">
            <div class="modal-content">
                <form method="POST" id="meetingTaskEditForm" action="#">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="meetingTaskEditModalLabel">Update Task</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="category" id="meetingTaskEditCategory" value="">
                        @if ($useFullTaskEditTemplate)
                            @unless ($allowTaskAssigneeEdit)
                                <input type="hidden" name="assigned_to" id="meetingTaskEditAssigneeHidden" value="">
                            @endunless
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label" for="meetingTaskEditTitle">Task Name <span class="required text-danger">*</span></label>
                                        <input type="text" class="form-control" id="meetingTaskEditTitle" name="title" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label" for="meetingTaskEditDescription">Task Description</label>
                                        <textarea class="form-control" rows="3" id="meetingTaskEditDescription" name="description" placeholder="Tambahkan detail atau konteks pekerjaan"></textarea>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="meetingTaskEditDateRangePicker">Date <span class="required text-danger">*</span></label>
                                        <input type="hidden" id="meetingTaskEditStartDate" name="start_date" value="">
                                        <input type="hidden" id="meetingTaskEditDueDate" name="due_date" value="">
                                        <input type="text" class="form-control js-meeting-task-date-range-picker" id="meetingTaskEditDateRangePicker" data-start-date-target="#meetingTaskEditStartDate" data-due-date-target="#meetingTaskEditDueDate" placeholder="Select date range" autocomplete="off" readonly required>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="meetingTaskEditPriority">Priority <span class="required text-danger">*</span></label>
                                        <select class="form-control default-select" id="meetingTaskEditPriority" name="priority" required>
                                            <option value="low">Low</option>
                                            <option value="medium">Medium</option>
                                            <option value="high">High</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="meetingTaskEditStatus">Task Status <span class="required text-danger">*</span></label>
                                        <select class="form-control default-select" id="meetingTaskEditStatus" name="status" required>
                                            <option value="pending">To Do</option>
                                            <option value="in_progress">On Progress</option>
                                            <option value="completed">Completed</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="meetingTaskEditAttachment">Attachment</label>
                                        <input type="text" class="form-control" id="meetingTaskEditAttachment" name="attachment_path" maxlength="255" placeholder="Contoh: Link Google Drive, Figma, atau Docs">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="meetingTaskEditBlockers">Blockers</label>
                                        <input type="text" class="form-control" id="meetingTaskEditBlockers" name="blockers" placeholder="Contoh: Menunggu approval dokumen">
                                    </div>
                                </div>
                                @if ($allowTaskAssigneeEdit)
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="meetingTaskEditAssignee">Assign Staff <span class="required text-danger">*</span></label>
                                            <select class="form-control default-select" id="meetingTaskEditAssignee" name="assigned_to" required @disabled(($meetingTaskAssigneeOptions ?? collect())->isEmpty())>
                                                @forelse ($meetingTaskAssigneeOptions ?? [] as $employee)
                                                    <option value="{{ $employee['id'] }}">{{ $employee['name'] }}</option>
                                                @empty
                                                    <option value="">Belum ada staff yang joined di meeting ini</option>
                                                @endforelse
                                            </select>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="meetingTaskEditCategoryLabel">Task Category <span class="required text-danger">*</span></label>
                                        <input type="text" class="form-control" id="meetingTaskEditCategoryLabel" value="" disabled>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="meetingTaskEditProjectName">Project Name</label>
                                        <input type="text" class="form-control" id="meetingTaskEditProjectName" value="Pilih Nama Project" disabled>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="mb-3">
                                <label class="form-label" for="meetingTaskEditTitle">Judul Task</label>
                                <input type="text" class="form-control" id="meetingTaskEditTitle" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="meetingTaskEditAssignee">Assign to</label>
                                <select class="form-select" id="meetingTaskEditAssignee" name="assigned_to" required @disabled(($meetingTaskAssigneeOptions ?? collect())->isEmpty())>
                                    @forelse ($meetingTaskAssigneeOptions ?? [] as $employee)
                                        <option value="{{ $employee['id'] }}">{{ $employee['name'] }}</option>
                                    @empty
                                        <option value="">Belum ada staff yang joined di meeting ini</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="meetingTaskEditDateRangePicker">Date Range</label>
                                <input type="text" class="form-control js-meeting-task-date-range-picker" id="meetingTaskEditDateRangePicker" data-start-date-target="#meetingTaskEditStartDate" data-due-date-target="#meetingTaskEditDueDate" placeholder="dd/mm/yyyy - dd/mm/yyyy" autocomplete="off" required>
                                <input type="hidden" id="meetingTaskEditStartDate" name="start_date" value="">
                                <input type="hidden" id="meetingTaskEditDueDate" name="due_date" value="">
                            </div>
                            <input type="hidden" id="meetingTaskEditStatus" name="status" value="pending">
                            <input type="hidden" id="meetingTaskEditPriority" name="priority" value="medium">
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-warning" @disabled($allowTaskAssigneeEdit && ($meetingTaskAssigneeOptions ?? collect())->isEmpty())>{{ $useFullTaskEditTemplate ? 'Save changes' : 'Submit' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if ($showTaskDeleteActions)
        <div class="modal fade" id="meetingTaskDeleteModal" tabindex="-1" aria-labelledby="meetingTaskDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="meetingTaskDeleteForm" action="#">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title" id="meetingTaskDeleteModalLabel">Delete Task</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex align-items-start">
                            <div class="avatar avatar-sm rounded bg-danger-subtle text-danger me-3 flex-shrink-0">
                                <i class="bi bi-trash"></i>
                            </div>
                            <div class="clearfix">
                                <h6 class="mb-1 fw-semibold">Hapus task dari meeting?</h6>
                                <p class="text-muted mb-3">Task meeting dan data project task terkait akan ikut dihapus.</p>
                                <div class="rounded bg-light px-3 py-2">
                                    <span class="d-block small text-muted">Task</span>
                                    <span class="d-block text-gray fw-semibold" id="meetingTaskDeleteTitle">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
        </div>
    @endif
@endif
