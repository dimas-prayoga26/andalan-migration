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

<form method="POST" action="{{ $isEdit ? route($routePrefix.'.update', [$routeParameter => $item]) : route($routePrefix.'.store') }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="card settings-form-card">
        <div class="card-header border-0">
            <div>
                <h4 class="card-title mb-1">{{ $pageTitle }}</h4>
                <p class="mb-0 text-muted fs-13">Manage {{ strtolower($resourceLabel) }} name and active status.</p>
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
                    <label class="form-label">{{ $resourceLabel }} Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $item?->name) }}" required autofocus>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="default-select form-control @error('status') is-invalid @enderror">
                        @foreach (['active' => 'Active', 'inactive' => 'Inactive'] as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}" @selected(old('status', strtolower((string) ($item?->status ?? 'active'))) === $statusValue)>{{ $statusLabel }}</option>
                        @endforeach
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route($routePrefix.'.index') }}" class="btn btn-light">Close</a>
            <button type="submit" class="btn btn-success">
                <i class="fa-regular fa-floppy-disk me-1"></i>{{ $isEdit ? 'Update' : 'Add' }} {{ $resourceLabel }}
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
