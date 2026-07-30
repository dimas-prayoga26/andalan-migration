@extends('layouts.main')

@section('title', 'Setting - '.$pageTitle)

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

        .settings-table-card .table-card-body {
            padding: 0;
        }

        .settings-table-card table {
            margin-bottom: 0;
        }

        .settings-table-card table th,
        .settings-table-card table td {
            vertical-align: middle;
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

@section('navbarTitle', 'Setting')

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
            <p class="mb-0 text-muted fs-13">Manage {{ strtolower($resourceLabel) }} master data.</p>
        </div>
        <div class="settings-list-actions">
            <form method="GET" action="{{ route($routePrefix.'.index') }}" class="mb-0">
                <div class="input-group">
                    <button type="submit" class="input-group-text bg-white" aria-label="Search {{ strtolower($resourceLabel) }}">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    <input
                        name="search"
                        type="search"
                        class="form-control"
                        value="{{ $search }}"
                        placeholder="Search {{ strtolower($resourceLabel) }}"
                        autocomplete="off"
                        aria-label="Search {{ strtolower($resourceLabel) }}"
                    >
                </div>
            </form>
            <a href="{{ route($routePrefix.'.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i>Add {{ $resourceLabel }}
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
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        @php
                            $status = strtolower((string) ($item->status ?? 'inactive'));
                            $statusClass = $status === 'active' ? 'badge-success' : 'badge-danger';
                        @endphp
                        <tr>
                            <td class="fw-semibold text-black">{{ $item->name }}</td>
                            <td>
                                <span class="badge badge-sm light {{ $statusClass }}">{{ ucfirst($status) }}</span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route($routePrefix.'.edit', [$routeParameter => $item]) }}" class="btn btn-primary light btn-sm">Update</a>
                                    <form action="{{ route($routePrefix.'.destroy', [$routeParameter => $item]) }}" method="POST" onsubmit="return confirm('Delete this {{ strtolower($resourceLabel) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger light btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                {{ $search !== '' ? 'No matching '.$resourceLabel.' found.' : 'No '.$resourceLabel.' data available.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('settings.partials.pagination', ['items' => $items])
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
