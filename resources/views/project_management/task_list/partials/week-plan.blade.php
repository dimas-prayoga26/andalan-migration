@php
    $taskListWeekPlanItems = collect($taskListWeekPlanItems ?? []);
@endphp

<h4 class="text-black mb-4">This week plan</h4>
@forelse ($taskListWeekPlanItems as $task)
    <div class="d-flex mb-4 align-items-center">
        <span class="avatar avatar-primary border-0 rounded me-3">{{ $task['date_day'] }}</span>
        <div>
            <h6 class="fs-16 text-truncate project-week-plan-title">
                <a type="button" data-bs-toggle="modal" data-bs-target="#taskDetailsModal" data-task='@json($task)' class="text-black js-task-details">{{ $task['title'] }}</a>
            </h6>
            <span class="fs-14">{{ $task['date_range_label'] }} ({{ $task['due_label'] }})</span>
        </div>
    </div>
@empty
    <p class="fs-14 mb-0">No task scheduled for this week.</p>
@endforelse
