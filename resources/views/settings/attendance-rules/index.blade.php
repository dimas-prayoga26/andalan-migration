@extends('layouts.main')

@section('title', 'Setting - '.$pageTitle)
@section('navbarTitle', 'Setting')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <style>
        .settings-nav-card,
        .settings-table-card {
            border-radius: 8px;
        }

        .settings-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            scrollbar-width: thin;
            white-space: nowrap;
        }

        .settings-tabs .nav-link {
            border: 0;
            border-bottom: 3px solid transparent;
            color: #6b7280;
            background: transparent;
        }

        .settings-tabs .nav-link.active {
            color: var(--bs-primary);
            border-bottom-color: var(--bs-primary);
            background: transparent;
        }

        .settings-list-actions {
            display: grid;
            grid-template-columns: minmax(240px, 320px) auto;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }

        .settings-list-actions .btn {
            min-height: 42px;
            white-space: nowrap;
        }

        .settings-table-footer.dataTables_wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 12px 30px;
            border-top: 1px solid var(--bs-border-color);
        }

        .settings-table-footer.dataTables_wrapper .dataTables_info,
        .settings-table-footer.dataTables_wrapper .dataTables_paginate {
            float: none;
            padding: 0;
        }

        .settings-table-footer .paginate_button.disabled {
            pointer-events: none;
            opacity: .35;
        }

        @media (max-width: 767.98px) {
            .settings-table-footer.dataTables_wrapper {
                flex-direction: column;
                align-items: stretch;
                padding: 12px 16px;
            }

            .settings-table-footer.dataTables_wrapper .dataTables_info,
            .settings-table-footer.dataTables_wrapper .dataTables_paginate {
                text-align: center;
            }
        }

        @media (max-width: 575.98px) {
            .settings-list-actions {
                grid-template-columns: minmax(0, 1fr);
                width: 100%;
            }

            .settings-list-actions .btn {
                width: 100%;
            }
        }
    </style>
@endsection

@section('content')
@php
    $formatTimeLabel = static function (mixed $value): string {
        if (blank($value)) {
            return '-';
        }

        return substr((string) $value, 0, 5);
    };
@endphp

@include('layouts.breadcrumb', [
    'title' => 'Setting',
    'current' => $pageTitle,
    'homeRoute' => 'dashboard',
])

@include('settings.partials.nav')

<div class="card settings-table-card">
    <div class="card-header border-0 flex-wrap gap-3">
        <div>
            <h4 class="card-title mb-1">{{ $pageTitle }}</h4>
            <p class="mb-0 text-muted fs-13">Manage attendance time, IP, and location radius rules.</p>
        </div>
        <div class="settings-list-actions">
            <form method="GET" action="{{ route('settings.attendance-rules.index') }}" class="mb-0">
                <div class="input-group">
                    <button type="submit" class="input-group-text bg-white" aria-label="Search attendance rules">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    <input
                        name="search"
                        type="search"
                        class="form-control"
                        value="{{ $search }}"
                        placeholder="Search attendance rules"
                        autocomplete="off"
                        aria-label="Search attendance rules"
                    >
                </div>
            </form>
            <a href="{{ route('settings.attendance-rules.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i>Add Rule
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success mx-4 mb-3">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger mx-4 mb-3">{{ session('error') }}</div>
    @endif

    <div class="card-body table-card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0 table-bottom-borderless table-striped align-middle">
                <thead>
                    <tr>
                        <th>Office Location</th>
                        <th>IP Range</th>
                        <th>Radius</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendanceRules as $attendanceRule)
                        <tr>
                            <td>
                                <span class="fw-semibold text-black">{{ $attendanceRule->officeLocation?->name ?? '-' }}</span>
                                @if (filled($attendanceRule->officeLocation?->address))
                                    <br><span class="text-muted fs-12">{{ $attendanceRule->officeLocation->address }}</span>
                                @endif
                            </td>
                            <td>{{ $attendanceRule->ip_range }}</td>
                            <td>{{ number_format((int) $attendanceRule->radius) }} m</td>
                            <td>{{ $formatTimeLabel($attendanceRule->office_start_time) }}</td>
                            <td>{{ $formatTimeLabel($attendanceRule->office_end_time) }}</td>
                            <td>
                                <span class="badge badge-sm light {{ $attendanceRule->is_active ? 'badge-success' : 'badge-danger' }}">
                                    {{ $attendanceRule->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('settings.attendance-rules.edit', ['attendanceRule' => $attendanceRule]) }}" class="btn btn-primary light btn-sm">Update</a>
                                    <form action="{{ route('settings.attendance-rules.destroy', ['attendanceRule' => $attendanceRule]) }}" method="POST" onsubmit="return confirm('Delete this attendance rule?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger light btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                {{ $search !== '' ? 'No matching attendance rule found.' : 'No attendance rule data available.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('settings.partials.pagination', ['items' => $attendanceRules])
    </div>
</div>
@endsection

@section('script')
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
    @endphp
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
@endsection
