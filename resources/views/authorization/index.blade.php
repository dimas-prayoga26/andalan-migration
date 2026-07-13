@extends('layouts.main')

@section('title', 'Employee Data')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <style>
        .authorization-nav-card {
            border-radius: 8px;
        }

        .authorization-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            scrollbar-width: thin;
            white-space: nowrap;
        }

        .authorization-tabs .nav-link {
            border: 0;
            border-bottom: 3px solid transparent;
            color: #6b7280;
            background: transparent;
        }

        .authorization-tabs .nav-link.active {
            color: var(--bs-primary);
            border-bottom-color: var(--bs-primary);
            background: transparent;
        }

        .authorization-avatar {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #eef2ff;
            color: #2448c7;
            font-weight: 700;
        }

        .authorization-list-actions {
            display: grid;
            grid-template-columns: minmax(240px, 320px) auto;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }

        .authorization-employee-search {
            margin: 0;
        }

        .authorization-list-actions .btn {
            min-height: 42px;
            white-space: nowrap;
        }

        .authorization-table-card .table-card-body {
            padding: 0;
        }

        .authorization-table-card .table-responsive {
            margin: 0;
        }

        .authorization-table-card table {
            margin-bottom: 0;
        }

        .authorization-table-card table th,
        .authorization-table-card table td {
            vertical-align: middle;
        }

        .authorization-table-footer.dataTables_wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 12px 30px;
            border-top: 1px solid var(--bs-border-color);
        }

        .authorization-table-footer.dataTables_wrapper .dataTables_info,
        .authorization-table-footer.dataTables_wrapper .dataTables_paginate {
            float: none;
            padding: 0;
        }

        .authorization-table-footer .paginate_button.disabled {
            pointer-events: none;
            opacity: .35;
        }

        @media (max-width: 767.98px) {
            .authorization-table-footer.dataTables_wrapper {
                flex-direction: column;
                align-items: stretch;
                padding: 12px 16px;
            }

            .authorization-table-footer.dataTables_wrapper .dataTables_info,
            .authorization-table-footer.dataTables_wrapper .dataTables_paginate {
                text-align: center;
            }
        }

        @media (max-width: 575.98px) {
            .authorization-list-actions {
                grid-template-columns: minmax(0, 1fr);
                width: 100%;
            }

            .authorization-list-actions .btn {
                width: 100%;
            }
        }
    </style>
@endsection

@section('navbarTitle', 'Employee Data')

@section('content')
@include('layouts.breadcrumb', [
    'title' => 'Employee Data',
    'current' => 'Employee List',
    'homeRoute' => 'dashboard',
])

<div class="card authorization-nav-card">
    <div class="card-header py-0">
        <ul class="nav nav-underline authorization-tabs gap-3">
            <li class="nav-item">
                <a class="nav-link py-3 px-1 active" href="{{ route('authorization') }}">Employee List</a>
            </li>
            @if ($canManagePositionPermissions)
                <li class="nav-item">
                    <a class="nav-link py-3 px-1" href="{{ route('authorization.access-menus') }}">Assign Permission</a>
                </li>
            @endif
        </ul>
    </div>
</div>

<div class="card authorization-table-card">
    <div class="card-header border-0 flex-wrap gap-3">
        <div>
            <h4 class="card-title mb-1">Employee List</h4>
            <p class="mb-0 text-muted fs-13">Employee, deployment, identity, and PIC data.</p>
        </div>
        <div class="authorization-list-actions">
            <form method="GET" action="{{ route('authorization') }}" class="authorization-employee-search">
                <div class="input-group">
                    <button type="submit" class="input-group-text bg-white" aria-label="Search employee">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    <input
                        id="authorizationEmployeeSearch"
                        name="search"
                        type="search"
                        class="form-control"
                        value="{{ $search }}"
                        placeholder="Search employee"
                        autocomplete="off"
                        aria-label="Search employee"
                    >
                </div>
            </form>
            @if ($canManageDataEmployee)
                <a href="{{ route('authorization.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus me-1"></i>Add Employee
                </a>
            @endif
        </div>
    </div>
    @if (session('status'))
        <div class="alert alert-success mx-4 mb-3">{{ session('status') }}</div>
    @endif
    <div class="card-body table-card-body p-0">
        <div class="table-responsive">
            <table id="authorizationEmployeeTable" class="table table-sm mb-0 table-bottom-borderless table-striped align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>NIK</th>
                        <th>Employee Code</th>
                        <th>Position</th>
                        <th>Company</th>
                        <th>PIC</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="authorization-avatar">{{ $user['initials'] }}</span>
                                    <div>
                                        <h6 class="mb-0 text-black">{{ $user['name'] }}</h6>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user['nik'] }}</td>
                            <td>{{ $user['employee_code'] }}</td>
                            <td>{{ $user['position'] }}</td>
                            <td>{{ $user['company'] }}</td>
                            <td>{{ $user['pic'] }}</td>
                            <td>
                                @php
                                    $statusClass = match ($user['status']) {
                                        'Active' => 'badge-success',
                                        'Pending' => 'badge-warning',
                                        default => 'badge-danger',
                                    };
                                @endphp
                                <span class="badge badge-sm light {{ $statusClass }}">{{ $user['status'] }}</span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('authorization.show', ['employee' => $user['id']]) }}" class="btn btn-info light btn-sm">Detail</a>
                                    @if ($canManageDataEmployee)
                                        <a href="{{ route('authorization.edit', ['employee' => $user['id']]) }}" class="btn btn-primary light btn-sm">Update</a>
                                        <form action="{{ route('authorization.destroy', ['employee' => $user['id']]) }}" method="POST" onsubmit="return confirm('Delete this employee data?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger light btn-sm">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                {{ $search !== '' ? 'No matching employee found.' : 'No employee data available.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->total() > 0)
            <div class="authorization-table-footer dataTables_wrapper no-footer">
                <div class="dataTables_info">
                    Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} entries
                </div>
                <div class="dataTables_paginate paging_simple_numbers">
                    <a
                        class="paginate_button previous {{ $users->onFirstPage() ? 'disabled' : '' }}"
                        href="{{ $users->previousPageUrl() ?? '#' }}"
                        aria-label="Previous page"
                    ><i class="fa-solid fa-angle-left"></i></a>
                    <span>
                        @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                            <a
                                class="paginate_button {{ $page === $users->currentPage() ? 'current' : '' }}"
                                href="{{ $url }}"
                                @if ($page === $users->currentPage()) aria-current="page" @endif
                            >{{ $page }}</a>
                        @endforeach
                    </span>
                    <a
                        class="paginate_button next {{ $users->hasMorePages() ? '' : 'disabled' }}"
                        href="{{ $users->nextPageUrl() ?? '#' }}"
                        aria-label="Next page"
                    ><i class="fa-solid fa-angle-right"></i></a>
                </div>
            </div>
        @endif
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
