@php
    $taskStatusIcon = ($taskRow['status_key'] ?? '') === 'completed'
        ? 'fa-solid fa-circle-check'
        : 'fa-solid fa-hourglass-half';
@endphp

<div class="d-lg-flex align-items-start border-bottom mb-3 pb-3">
    <a href="{{ $taskRow['attachment_path'] !== '' ? $taskRow['attachment_path'] : 'javascript:void(0);' }}"></a>
    <div class="flex-grow-1 col-lg-5 ps-0">
        <h6 class="fs-16">
            <a href="{{ $taskRow['attachment_path'] !== '' ? $taskRow['attachment_path'] : 'javascript:void(0);' }}" class="text-black" @if ($taskRow['attachment_path'] !== '') target="_blank" rel="noopener noreferrer" @endif>{{ $taskRow['task'] }}</a>
        </h6>
        <p class="fs-14">{{ $taskRow['description'] !== '' ? $taskRow['description'] : $taskRow['task_context'] }}</p>
        <div class="d-flex flex-wrap align-items-center">
            <div class="d-flex mb-sm-2 mb-3 pe-3 me-auto align-items-center">
                <img src="{{ $taskRow['avatar_url'] }}" alt="avatar" class="avatar avatar-xs rounded-circle me-2">
                <span class="fs-14 text-black fw-semibold">{{ $taskRow['staff'] }}</span>
            </div>
            <ul class="d-flex flex-wrap mb-sm-0 mb-2">
                <li class="mb-2 me-4 text-nowrap">
                    <i class="fa-regular fa-calendar-days me-2"></i>
                    <span class="fs-14 text-black text-nowrap fw-semibold">{{ $taskRow['short_due_date'] }}</span>
                </li>
                <li class="mb-2 me-4 text-nowrap">
                    <i class="{{ $taskStatusIcon }} me-2"></i>
                    <span class="text-nowrap fs-14 fw-bold text-{{ $taskRow['status_class'] }}">{{ $taskRow['status'] }}</span>
                </li>
            </ul>
        </div>
    </div>
    @if ($taskRow['attachment_path'] !== '')
        <a href="{{ $taskRow['attachment_path'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary light btn-sm ms-auto"><i class="fa-solid fa-link me-1"></i>Attachment</a>
    @else
        <span class="btn btn-primary light btn-sm ms-auto disabled"><i class="fa-solid fa-link me-1"></i>Attachment</span>
    @endif
</div>
