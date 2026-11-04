@extends('layouts.main')

@section('title', 'Meeting Details')

@section('navbarTitle', 'Meeting Details')

@section('css')
<style>
    .meeting-soft-card {
        border: 0;
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }

    .meeting-icon-box {
        width: 44px;
        height: 44px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #1da1f2;
        background: #fff;
        flex: 0 0 44px;
    }

    .meeting-meta-list {
        display: grid;
        gap: 17px;
    }

    .meeting-meta-row {
        display: grid;
        grid-template-columns: 180px minmax(0, 1fr);
        gap: 16px;
        align-items: center;
        font-size: 14px;
    }

    .meeting-meta-row span:first-child {
        color: #6b7280;
    }

    .meeting-status-complete {
        color: #16a34a;
        font-weight: 700;
    }

    .meeting-staff-stack {
        display: flex;
        align-items: center;
        padding-left: 8px;
    }

    .meeting-staff-avatar {
        width: 31px;
        height: 31px;
        border-radius: 50%;
        border: 2px solid #fff;
        margin-left: -8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        color: #fff;
        overflow: hidden;
    }

    .meeting-staff-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .meeting-donut-wrap {
        min-height: 245px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 38px;
    }

    .meeting-donut {
        width: 166px;
        height: 166px;
        border-radius: 50%;
        background: conic-gradient(
            #243ec6 0deg 75deg,
            #9b2cf3 75deg 150deg,
            #21bf55 150deg 201deg,
            #f43f86 201deg 261deg,
            #ffb000 261deg 360deg
        );
        position: relative;
        flex: 0 0 166px;
    }

    .meeting-donut::after {
        content: "";
        position: absolute;
        inset: 9px;
        border-radius: 50%;
        background: #fff;
    }

    .meeting-donut-center {
        position: absolute;
        inset: 0;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #0f172a;
    }

    .meeting-donut-center strong {
        font-size: 28px;
        line-height: 1;
    }

    .meeting-legend {
        min-width: 220px;
        display: grid;
        gap: 16px;
    }

    .meeting-legend-row {
        display: grid;
        grid-template-columns: 14px minmax(0, 1fr) auto;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }

    .meeting-legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 2px;
        display: inline-block;
    }

    .meeting-action-muted {
        background: #dfe4f6;
        color: #1239c2;
        border: 0;
        border-radius: 10px;
        height: 50px;
        font-weight: 600;
    }

    @media (max-width: 1399.98px) {
        .meeting-donut-wrap {
            flex-direction: column;
            gap: 24px;
        }

        .meeting-legend {
            width: 100%;
        }
    }

    @media (max-width: 575.98px) {
        .meeting-meta-row {
            grid-template-columns: 1fr;
            gap: 4px;
        }
    }
</style>
@endsection

@section('content')
<div class="page-title">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li><h1>Meeting</h1></li>
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('zoom-meeting.index') }}">Meeting</a></li>
            <li class="breadcrumb-item active" aria-current="page">Meeting Details</li>
        </ol>
    </nav>
</div>

@if (session('status'))
    @include('partials.swal-alert', ['type' => 'success', 'message' => session('status')])
@endif

@if ($errors->any())
    @include('partials.swal-alert', ['type' => 'error', 'message' => $errors->first()])
@endif

<div class="row">
    <div class="col-xl-4 col-lg-6">
        <div class="card meeting-soft-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <span class="meeting-icon-box me-3">
                        <i class="fa-solid fa-video"></i>
                    </span>
                    <div>
                        <h4 class="mb-1">{{ $meetingTypeLabels[$meeting->type] ?? $meeting->type }}</h4>
                        <p class="mb-0 text-muted">{{ $meeting->title }}</p>
                    </div>
                </div>

                <div class="meeting-meta-list">
                    <div class="meeting-meta-row">
                        <span>Date</span>
                        <strong>{{ optional($meeting->meeting_date)->format('l, d F Y') }}</strong>
                    </div>
                    <div class="meeting-meta-row">
                        <span>Time</span>
                        <strong>{{ substr((string) $meeting->meeting_time, 0, 5) }} WIB</strong>
                    </div>
                    <div class="meeting-meta-row">
                        <span>Meeting Status</span>
                        <strong class="meeting-status-complete">{{ ucfirst($meeting->status) }}</strong>
                    </div>
                    <div class="meeting-meta-row">
                        <span>Attachment Link</span>
                        @if ($meeting->attachment_link)
                            <a href="{{ $meeting->attachment_link }}" target="_blank" rel="noopener noreferrer" class="fw-semibold">{{ parse_url($meeting->attachment_link, PHP_URL_HOST) ?: $meeting->attachment_link }}</a>
                        @else
                            <strong>-</strong>
                        @endif
                    </div>
                    <div class="meeting-meta-row">
                        <span>Total Tasks</span>
                        <strong>{{ (int) $meeting->tasks_count }} Tasks</strong>
                    </div>
                    <div class="meeting-meta-row">
                        <span>Joined</span>
                        <strong>{{ (int) $meeting->joined_count }} Staff</strong>
                    </div>
                </div>

                <div class="mt-4">
                    <strong class="d-block mb-2">Staff</strong>
                    <div class="meeting-staff-stack">
                        @php
                            $meetingStaffAvatars = collect($meetingStaffAvatars ?? []);
                        @endphp
                        @forelse ($meetingStaffAvatars->take(6) as $staffAvatar)
                            <span class="meeting-staff-avatar bg-primary" title="{{ $staffAvatar['name'] }}">
                                <img src="{{ $staffAvatar['avatar_url'] }}" alt="{{ $staffAvatar['name'] }}" loading="lazy" decoding="async" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                                <span style="display: none;">{{ $staffAvatar['initials'] }}</span>
                            </span>
                        @empty
                            <span class="text-muted small">No staff invited.</span>
                        @endforelse
                        @if ($meetingStaffAvatars->count() > 6)
                            <span class="meeting-staff-avatar bg-success" title="{{ $meetingStaffAvatars->count() - 6 }} more staff">+{{ $meetingStaffAvatars->count() - 6 }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-6">
        <div class="card meeting-soft-card h-100">
            <div class="card-body d-flex flex-column">
                <div class="mb-3">
                    <h4 class="mb-1">Tasks Summary</h4>
                    <p class="mb-0 text-muted">{{ (int) $meetingTaskSummary['overdue'] }} Overdue Tasks</p>
                </div>

                <div class="meeting-donut-wrap flex-grow-1">
                    <div class="meeting-donut" style="background: conic-gradient({{ $meetingTaskSummary['gradient'] }});">
                        <div class="meeting-donut-center">
                            <strong>{{ (int) $meetingTaskSummary['total'] }}</strong>
                            <span>Total</span>
                        </div>
                    </div>

                    <div class="meeting-legend">
                        @foreach ($meetingTaskSummary['segments'] as $segment)
                            <div class="meeting-legend-row">
                                <span class="meeting-legend-dot" style="background:{{ $segment['color'] }}"></span>
                                <span>{{ $segment['label'] }}</span>
                                <span>{{ (int) $segment['count'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <button type="button" class="btn meeting-action-muted w-100 mt-3">Generate MoM PDF</button>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header pb-0 border-0">
                <div class="clearfix d-flex">
                    <div class="clearfix">
                        <h4 class="mb-0 fw-semibold">Meeting Attendance</h4>
                        <span class="small">Staff joined in this meeting</span>
                    </div>
                </div>
            </div>
            <div class="card-body px-3 dz-scroll height380">
                <div class="table-responsive">
                    <table class="table table-sm table-sm-responsive table-bottom-borderless mb-0">
                        <thead class="text-nowrap">
                            <tr>
                                <th class="mw-10">No</th>
                                <th class="mw-150">User</th>
                                <th class="mw-150">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($meetingAttendanceRows as $attendanceRow)
                                <tr>
                                    <td>{{ $loop->iteration }}.</td>
                                    <td>{{ $attendanceRow['name'] }}</td>
                                    <td>{{ $attendanceRow['time'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-muted">No attendance data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    @include('meetings.admin.partials.task-cards', [
        'showTaskSummary' => false,
        'showAddTask' => false,
        'showTaskActions' => true,
        'showTaskDeleteActions' => false,
        'allowTaskAssigneeEdit' => false,
        'useFullTaskEditTemplate' => true,
    ])
</div>
@endsection

@section('script')
    @include('meetings.admin.partials.task-create-modal-script', [
        'showAddTask' => false,
        'showCreateTaskModal' => false,
    ])
@endsection
