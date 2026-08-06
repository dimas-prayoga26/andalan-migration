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
        .settings-form-card {
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
    </style>
@endsection

@section('content')
@php
    $isEdit = $mode === 'edit';
    $formatTimeInput = static function (mixed $value): string {
        if (blank($value)) {
            return '';
        }

        return substr((string) $value, 0, 5);
    };
    $selectedPositionIds = collect(old(
        'position_ids',
        $attendanceRule?->positions?->pluck('id')->map(fn ($id) => (string) $id)->all() ?? []
    ))
        ->map(fn ($id) => (string) $id)
        ->all();
@endphp

@include('layouts.breadcrumb', [
    'title' => 'Setting',
    'current' => $pageTitle,
    'homeRoute' => 'dashboard',
])

@include('settings.partials.nav')

<form method="POST" action="{{ $isEdit ? route('settings.attendance-rules.update', ['attendanceRule' => $attendanceRule]) : route('settings.attendance-rules.store') }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="card settings-form-card">
        <div class="card-header border-0">
            <div>
                <h4 class="card-title mb-1">{{ $pageTitle }}</h4>
                <p class="mb-0 text-muted fs-13">Manage office attendance time, IP range, and radius.</p>
            </div>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>The data is not valid.</strong> Please check the required fields again.
                </div>
            @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Office Location</label>
                    <select name="office_location_id" class="default-select form-control @error('office_location_id') is-invalid @enderror" required>
                        <option value="">Select office location</option>
                        @foreach ($officeLocationOptions as $officeLocation)
                            <option value="{{ $officeLocation['id'] }}" @selected((string) old('office_location_id', $attendanceRule?->office_location_id ?? '') === $officeLocation['id'])>{{ $officeLocation['label'] }}</option>
                        @endforeach
                    </select>
                    @error('office_location_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Attendance Type</label>
                    <select name="attendance_type" class="default-select form-control @error('attendance_type') is-invalid @enderror" required>
                        <option value="fixed" @selected(old('attendance_type', $attendanceRule?->attendance_type ?? 'fixed') === 'fixed')>Fixed</option>
                        <option value="flexible" @selected(old('attendance_type', $attendanceRule?->attendance_type ?? 'fixed') === 'flexible')>Flexible</option>
                    </select>
                    @error('attendance_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Positions</label>
                    <select name="position_ids[]" class="default-select form-control @error('position_ids') is-invalid @enderror @error('position_ids.*') is-invalid @enderror" multiple>
                        @foreach ($positionOptions as $position)
                            <option value="{{ $position['id'] }}" @selected(in_array($position['id'], $selectedPositionIds, true))>{{ $position['label'] }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Leave empty as fallback rule for this office.</small>
                    @error('position_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    @error('position_ids.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">IP Range</label>
                    <input type="text" name="ip_range" class="form-control @error('ip_range') is-invalid @enderror" value="{{ old('ip_range', $attendanceRule?->ip_range) }}" placeholder="182.8" required>
                    @error('ip_range')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Radius (meters)</label>
                    <input type="number" name="radius" class="form-control @error('radius') is-invalid @enderror" value="{{ old('radius', $attendanceRule?->radius ?? 75) }}" min="1" max="100000" required>
                    @error('radius')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Office Start Time</label>
                    <input type="time" name="office_start_time" class="form-control @error('office_start_time') is-invalid @enderror" value="{{ old('office_start_time', $formatTimeInput($attendanceRule?->office_start_time ?? '08:00:00')) }}" required>
                    @error('office_start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Office End Time</label>
                    <input type="time" name="office_end_time" class="form-control @error('office_end_time') is-invalid @enderror" value="{{ old('office_end_time', $formatTimeInput($attendanceRule?->office_end_time ?? '17:00:00')) }}" required>
                    @error('office_end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label d-block">Status</label>
                    <input type="hidden" name="is_active" value="0">
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="attendanceRuleIsActive" name="is_active" value="1" @checked((bool) old('is_active', $attendanceRule?->is_active ?? true))>
                        <label class="form-check-label fw-semibold" for="attendanceRuleIsActive">Active Rule</label>
                    </div>
                    @error('is_active')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('settings.attendance-rules.index') }}" class="btn btn-light">Close</a>
            <button type="submit" class="btn btn-success">
                <i class="fa-regular fa-floppy-disk me-1"></i>{{ $isEdit ? 'Update' : 'Add' }} Rule
            </button>
        </div>
    </div>
</form>
@endsection

@section('script')
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
    @endphp
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
@endsection
