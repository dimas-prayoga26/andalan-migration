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
@endphp

@include('layouts.breadcrumb', [
    'title' => 'Setting',
    'current' => $pageTitle,
    'homeRoute' => 'dashboard',
])

@include('settings.partials.nav')

<form method="POST" action="{{ $isEdit ? route('settings.office-locations.update', ['officeLocation' => $officeLocation]) : route('settings.office-locations.store') }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="card settings-form-card">
        <div class="card-header border-0">
            <div>
                <h4 class="card-title mb-1">{{ $pageTitle }}</h4>
                <p class="mb-0 text-muted fs-13">Manage office name, address, coordinates, and active status.</p>
            </div>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>The data is not valid.</strong> Please check the required fields again.
                </div>
            @endif

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Office Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $officeLocation?->name) }}" required autofocus>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-8">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $officeLocation?->address) }}">
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Latitude</label>
                    <input type="number" step="any" name="latitude" class="form-control @error('latitude') is-invalid @enderror" value="{{ old('latitude', $officeLocation?->latitude) }}" required>
                    @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Longitude</label>
                    <input type="number" step="any" name="longitude" class="form-control @error('longitude') is-invalid @enderror" value="{{ old('longitude', $officeLocation?->longitude) }}" required>
                    @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label d-block">Status</label>
                    <input type="hidden" name="is_active" value="0">
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="officeLocationIsActive" name="is_active" value="1" @checked((bool) old('is_active', $officeLocation?->is_active ?? true))>
                        <label class="form-check-label fw-semibold" for="officeLocationIsActive">Active Office</label>
                    </div>
                    @error('is_active')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('settings.office-locations.index') }}" class="btn btn-light">Close</a>
            <button type="submit" class="btn btn-success">
                <i class="fa-regular fa-floppy-disk me-1"></i>{{ $isEdit ? 'Update' : 'Add' }} Office
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
