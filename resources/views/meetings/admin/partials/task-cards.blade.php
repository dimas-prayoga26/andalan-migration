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
                                <h6 class="fs-13 mb-0 fw-semibold">{{ $task['title'] }}</h6>
                                <span class="small">
                                    {{ $task['is_completed'] ? 'Status: Completed' : 'Due '.$task['due_label'] }}
                                    by <span class="text-primary">{{ $task['assignee'] }}</span>
                                </span>
                            </div>
                            <div class="clearfix ms-auto">
                                <button type="button" class="btn btn-sm btn-light btn-square" disabled>
                                    <i class="bi bi-grid"></i>
                                </button>
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
