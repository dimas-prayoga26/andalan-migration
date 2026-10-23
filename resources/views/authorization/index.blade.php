@extends('layouts.main')

@section('title', 'Employee Data')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.css') }}">
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

        .authorization-employee-avatar {
            width: 35px;
            height: 35px;
            object-fit: cover;
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

        .authorization-status-tabs {
            gap: 6px;
        }

        .authorization-status-tabs .nav-link {
            border-radius: 6px;
            color: #6b7280;
            padding: 6px 14px;
        }

        .authorization-status-tabs .nav-link.active {
            background: var(--bs-primary);
            color: #fff;
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

@php
    $hasEventDivisionRoute = \Illuminate\Support\Facades\Route::has('authorization.event-divisions');
    $employeeStatusFilter = $employeeStatusFilter ?? 'active';
    $employeeStatusTabs = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];
@endphp

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
                @if ($hasEventDivisionRoute)
                    <li class="nav-item">
                        <a class="nav-link py-3 px-1" href="{{ route('authorization.event-divisions') }}">Assign Event Division</a>
                    </li>
                @endif
            @endif
        </ul>
    </div>
</div>

<div class="card authorization-table-card">
    <div class="card-header border-0 flex-wrap gap-3">
        <div>
            <h4 class="card-title mb-1">Employee List</h4>
            <p class="mb-0 text-muted fs-13">Employee, position, company, and contract data.</p>
            <ul class="nav nav-pills authorization-status-tabs mt-3" aria-label="Employee status filter">
                @foreach ($employeeStatusTabs as $statusValue => $statusLabel)
                    <li class="nav-item">
                        <a
                            class="nav-link {{ $employeeStatusFilter === $statusValue ? 'active' : '' }}"
                            href="{{ route('authorization', array_filter(['status' => $statusValue, 'search' => $search !== '' ? $search : null])) }}"
                            @if ($employeeStatusFilter === $statusValue) aria-current="page" @endif
                        >{{ $statusLabel }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="authorization-list-actions">
            <form method="GET" action="{{ route('authorization') }}" class="authorization-employee-search">
                <input type="hidden" name="status" value="{{ $employeeStatusFilter }}">
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
                <a href="{{ route('authorization.create') }}" class="btn light btn-success btn-sm">
                    <i class="fa-solid fa-plus me-1"></i>Add Employee
                </a>
            @endif
        </div>
    </div>
    @include('partials.swal-alert', ['type' => 'success', 'message' => session('status')])
    <div class="card-body table-card-body p-0">
        <div class="table-responsive">
            <table id="authorizationEmployeeTable" class="table table-sm table-sm-responsive text-nowrap mb-0">
                <thead>
                    <tr>
                        <th class="mw-10">No</th>
                        <th class="mw-80">Name</th>
                        <th class="mw-80">Position</th>
                        <th class="mw-80">Company</th>
                        @if ($employeeStatusFilter === 'inactive')
                            <th class="mw-80">Working Period</th>
                        @else
                            <th class="mw-80">Contract</th>
                        @endif
                        <th class="mw-100">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $users->firstItem() + $loop->index }}</td>
                            <td>
                                <a href="{{ route('authorization.show', ['employee' => $user['id']]) }}">
                                    <img src="{{ $user['avatar_url'] }}" alt="{{ $user['name'] }}" class="rounded-circle me-2 authorization-employee-avatar" width="35" height="35">
                                    <span>{{ $user['name'] }}</span>
                                </a>
                            </td>
                            <td>
                                <span>{{ $user['position'] }}</span>
                                @if ($user['department'] !== '')
                                    <br>
                                    <span>{{ $user['department'] }}</span>
                                @endif
                            </td>
                            <td>{{ $user['company'] }}</td>
                            <td>
                                <span>{{ $user['contract_start'] }}</span> <br>
                                <span>{{ $employeeStatusFilter === 'inactive' ? $user['resignation_date'] : $user['contract_end'] }}</span>
                            </td>
                            <td class="text-start">
                                <x-action-menu>
                                    <li><a class="dropdown-item" href="{{ route('authorization.show', ['employee' => $user['id']]) }}"><i class="bi bi-eye"></i>View More</a></li>
                                    @if ($canManageDataEmployee)
                                        <li><a class="dropdown-item" href="{{ route('authorization.edit', ['employee' => $user['id']]) }}"><i class="bi bi-pencil-square"></i>Edit</a></li>
                                        <li>
                                            <form
                                                action="{{ route('authorization.destroy', ['employee' => $user['id']]) }}"
                                                method="POST"
                                                data-authorization-delete-form
                                                data-employee-name="{{ $user['name'] }}"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash"></i>Delete</button>
                                            </form>
                                        </li>
                                    @endif
                                </x-action-menu>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                {{ $search !== '' ? 'No matching employee found.' : 'No '.$employeeStatusFilter.' employee data available.' }}
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
    <script src="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
        document.addEventListener('submit', function (event) {
            if (! event.target.matches('[data-authorization-delete-form]')) {
                return;
            }

            var form = event.target;

            if (form.dataset.deleteConfirmed === 'true') {
                return;
            }

            event.preventDefault();

            Swal.fire({
                title: 'Delete Employee Data?',
                text: 'Employee data for ' + (form.dataset.employeeName || 'this employee') + ' will be deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusCancel: true
            }).then(function (result) {
                if (! result.isConfirmed) {
                    return;
                }

                form.dataset.deleteConfirmed = 'true';
                form.submit();
            });
        });
    </script>
@endsection
