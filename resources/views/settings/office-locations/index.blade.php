@extends('layouts.main')

@section('title', 'Setting - '.$pageTitle)
@section('navbarTitle', 'Setting')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.css') }}">
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
            <p class="mb-0 text-muted fs-13">Manage office master data for attendance rules and employee deployment.</p>
        </div>
        <div class="settings-list-actions">
            <form method="GET" action="{{ route('settings.office-locations.index') }}" class="mb-0">
                <div class="input-group">
                    <button type="submit" class="input-group-text bg-white" aria-label="Search office locations">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    <input
                        name="search"
                        type="search"
                        class="form-control"
                        value="{{ $search }}"
                        placeholder="Search office locations"
                        autocomplete="off"
                        aria-label="Search office locations"
                    >
                </div>
            </form>
            <a href="{{ route('settings.office-locations.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i>Add Office
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
                        <th>Name</th>
                        <th>Address</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($officeLocations as $officeLocation)
                        <tr>
                            <td class="fw-semibold text-black">{{ $officeLocation->name }}</td>
                            <td>{{ $officeLocation->address ?: '-' }}</td>
                            <td>{{ number_format((float) $officeLocation->latitude, 7, '.', '') }}</td>
                            <td>{{ number_format((float) $officeLocation->longitude, 7, '.', '') }}</td>
                            <td>
                                <span class="badge badge-sm light {{ $officeLocation->is_active ? 'badge-success' : 'badge-danger' }}">
                                    {{ $officeLocation->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('settings.office-locations.edit', ['officeLocation' => $officeLocation]) }}" class="btn btn-primary light btn-sm">Update</a>
                                    <form
                                        action="{{ route('settings.office-locations.destroy', ['officeLocation' => $officeLocation]) }}"
                                        method="POST"
                                        data-settings-delete-form
                                        data-delete-title="Delete Office Location"
                                        data-delete-message="Delete {{ $officeLocation->name }} from office location data?"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger light btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                {{ $search !== '' ? 'No matching office location found.' : 'No office location data available.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('settings.partials.pagination', ['items' => $officeLocations])
    </div>
</div>

@include('settings.partials.delete-confirmation-swal')
@endsection

@section('script')
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
    @endphp
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
    @stack('scripts')
@endsection
