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

    .meeting-attendance-scroll {
        max-height: 340px;
        overflow-y: auto;
        padding-right: 4px;
    }

    .meeting-division-card {
        min-height: 360px;
    }

    .meeting-task-list {
        max-height: 245px;
        overflow-y: auto;
        padding-right: 4px;
    }

    .meeting-task-item {
        position: relative;
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding: 9px 0 9px 16px;
        border-left: 4px solid #eef2f7;
    }

    .meeting-task-item.is-done {
        border-left-color: #22c55e;
    }

    .meeting-task-item h6 {
        margin-bottom: 3px;
        font-size: 14px;
    }

    .meeting-task-action {
        width: 34px;
        height: 34px;
        border-radius: 12px;
        border: 0;
        color: #42526e;
        background: #f4f6fb;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 34px;
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

<div class="row">
    <div class="col-xl-4 col-lg-6">
        <div class="card meeting-soft-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <span class="meeting-icon-box me-3">
                        <i class="fa-solid fa-video"></i>
                    </span>
                    <div>
                        <h4 class="mb-1">Weekly Meeting</h4>
                        <p class="mb-0 text-muted">Evaluasi Mingguan</p>
                    </div>
                </div>

                <div class="meeting-meta-list">
                    <div class="meeting-meta-row">
                        <span>Date</span>
                        <strong>Friday, 04 September 2025</strong>
                    </div>
                    <div class="meeting-meta-row">
                        <span>Time</span>
                        <strong>10:00 WIB</strong>
                    </div>
                    <div class="meeting-meta-row">
                        <span>Meeting Status</span>
                        <strong class="meeting-status-complete">Completed</strong>
                    </div>
                    <div class="meeting-meta-row">
                        <span>Attachments Link</span>
                        <a href="#" class="fw-semibold">canva.com</a>
                    </div>
                    <div class="meeting-meta-row">
                        <span>Total Task</span>
                        <strong>18 Tasks</strong>
                    </div>
                    <div class="meeting-meta-row">
                        <span>Joined</span>
                        <strong>12 Staff</strong>
                    </div>
                </div>

                <div class="mt-4">
                    <strong class="d-block mb-2">Staff</strong>
                    <div class="meeting-staff-stack">
                        <span class="meeting-staff-avatar bg-primary">HR</span>
                        <span class="meeting-staff-avatar bg-danger">IT</span>
                        <span class="meeting-staff-avatar bg-warning">OP</span>
                        <span class="meeting-staff-avatar bg-info">CR</span>
                        <span class="meeting-staff-avatar bg-success">+8</span>
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
                    <p class="mb-0 text-muted">24 Overdue Tasks</p>
                </div>

                <div class="meeting-donut-wrap flex-grow-1">
                    <div class="meeting-donut">
                        <div class="meeting-donut-center">
                            <strong>120</strong>
                            <span>Total</span>
                        </div>
                    </div>

                    <div class="meeting-legend">
                        <div class="meeting-legend-row">
                            <span class="meeting-legend-dot" style="background:#243ec6"></span>
                            <span>Administration</span>
                            <span>25</span>
                        </div>
                        <div class="meeting-legend-row">
                            <span class="meeting-legend-dot" style="background:#9b2cf3"></span>
                            <span>Event (KMA)</span>
                            <span>25</span>
                        </div>
                        <div class="meeting-legend-row">
                            <span class="meeting-legend-dot" style="background:#21bf55"></span>
                            <span>Property (TRAH)</span>
                            <span>17</span>
                        </div>
                        <div class="meeting-legend-row">
                            <span class="meeting-legend-dot" style="background:#f43f86"></span>
                            <span>IT and Socmed (TMS)</span>
                            <span>20</span>
                        </div>
                        <div class="meeting-legend-row">
                            <span class="meeting-legend-dot" style="background:#ffb000"></span>
                            <span>Others</span>
                            <span>38</span>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn meeting-action-muted w-100 mt-3">Generate MoM PDF</button>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card meeting-soft-card h-100">
            <div class="card-body">
                <div class="mb-4">
                    <h4 class="mb-1">Log Attendance</h4>
                    <p class="mb-0 text-muted">Record of attendance</p>
                </div>

                <div class="meeting-attendance-scroll">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>User</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>1.</td><td>Williams</td><td>09:01 WIB</td></tr>
                                <tr><td>2</td><td>Paul</td><td>09:0 WIB</td></tr>
                                <tr><td>3.</td><td>Sarah</td><td>09:00 WIB</td></tr>
                                <tr><td>4.</td><td>Marcus</td><td>09:00 WIB</td></tr>
                                <tr><td>5.</td><td>Maria</td><td>09:00 WIB</td></tr>
                                <tr><td>6.</td><td>Robert</td><td>09:00 WIB</td></tr>
                                <tr><td>7.</td><td>Juan</td><td>09:10 WIB</td></tr>
                                <tr><td>8.</td><td>Robert</td><td>09:10 WIB</td></tr>
                                <tr><td>9.</td><td>Bob</td><td>09:12 WIB</td></tr>
                                <tr><td>10.</td><td>Alex</td><td>09:14 WIB</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $divisionCards = [
        [
            'title' => 'Administration',
            'subtitle' => 'Administrative operational support',
            'completed' => '5 / 8 Completed',
            'percent' => '62%',
            'tasks' => [
                ['title' => 'Sistem registrasi peserta', 'meta' => 'Due in 1 day (10 jun) by Syafiq', 'done' => false],
                ['title' => 'Design Stage', 'meta' => '18 May 2026 (2 days overdue) by Rexy', 'done' => false],
                ['title' => 'Develop halaman utama website promotion', 'meta' => 'Completed by Dimas', 'done' => true],
                ['title' => 'Update laporan weekly progress', 'meta' => 'Completed by Leonie', 'done' => true],
            ],
        ],
        [
            'title' => 'Event Management (KMA)',
            'subtitle' => 'Event Planning & Operations',
            'completed' => '5 / 8 Completed',
            'percent' => '62%',
            'tasks' => [
                ['title' => 'Sistem registrasi peserta', 'meta' => 'Due in 1 day (10 jun) by Syafiq', 'done' => false],
                ['title' => 'Design Stage', 'meta' => '18 May 2026 (2 days overdue) by Rexy', 'done' => false],
                ['title' => 'Checklist vendor dan rundown', 'meta' => 'Completed by Rina', 'done' => true],
                ['title' => 'Follow up kebutuhan venue', 'meta' => 'Completed by Paul', 'done' => true],
            ],
        ],
        [
            'title' => 'Property and Construction (TRAH)',
            'subtitle' => 'Property & Construction Operations',
            'completed' => '5 / 8 Completed',
            'percent' => '62%',
            'tasks' => [
                ['title' => 'Sistem registrasi peserta', 'meta' => 'Due in 1 day (10 jun) by Syafiq', 'done' => false],
                ['title' => 'Design Stage', 'meta' => '18 May 2026 (2 days overdue) by Rexy', 'done' => false],
                ['title' => 'Review progress material lapangan', 'meta' => 'Completed by Marcus', 'done' => true],
                ['title' => 'Dokumentasi progress mingguan', 'meta' => 'Completed by Sarah', 'done' => true],
            ],
        ],
        [
            'title' => 'IT and Socmed (TMS)',
            'subtitle' => 'Digital support and publication',
            'completed' => '4 / 7 Completed',
            'percent' => '57%',
            'tasks' => [
                ['title' => 'Fixing integrasi notifikasi email', 'meta' => 'Due in 2 days by Dimas', 'done' => false],
                ['title' => 'Konten recap meeting', 'meta' => 'Due today by Creative Team', 'done' => false],
                ['title' => 'Backup database production', 'meta' => 'Completed by IT Team', 'done' => true],
            ],
        ],
        [
            'title' => 'Others',
            'subtitle' => 'Cross division follow-up',
            'completed' => '6 / 9 Completed',
            'percent' => '67%',
            'tasks' => [
                ['title' => 'Finalisasi MoM weekly meeting', 'meta' => 'Due tomorrow by HR Team', 'done' => false],
                ['title' => 'Share link recording meeting', 'meta' => 'Completed by Admin', 'done' => true],
                ['title' => 'Follow up overdue task PIC', 'meta' => 'Completed by Supervisor', 'done' => true],
            ],
        ],
    ];
@endphp

<div class="row">
    @foreach ($divisionCards as $division)
        <div class="col-xl-4 col-md-6">
            <div class="card meeting-soft-card meeting-division-card">
                <div class="card-body">
                    <div class="mb-4">
                        <h4 class="mb-1">{{ $division['title'] }}</h4>
                        <p class="mb-0 text-muted">{{ $division['subtitle'] }}</p>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <strong>{{ $division['completed'] }} <span class="text-success">({{ $division['percent'] }})</span></strong>
                        <a href="#" class="text-success fw-semibold">+ Add Task</a>
                    </div>

                    <div class="meeting-task-list">
                        @foreach ($division['tasks'] as $task)
                            <div class="meeting-task-item {{ $task['done'] ? 'is-done' : '' }}">
                                <div>
                                    <h6>{{ $task['title'] }}</h6>
                                    <p class="mb-0 text-muted fs-13">{{ $task['meta'] }}</p>
                                </div>
                                <button type="button" class="meeting-task-action" aria-label="Task detail">
                                    <i class="fa-solid fa-border-all"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
