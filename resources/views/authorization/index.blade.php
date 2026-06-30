@extends('layouts.main')

@section('title', 'Data Employee')

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
    </style>
@endsection

@section('navbarTitle', 'Data Employee')

@section('content')
@include('layouts.breadcrumb', [
    'title' => 'Data Employee',
    'current' => 'List Employee',
    'homeRoute' => 'dashboard',
])

<div class="card authorization-nav-card">
    <div class="card-header py-0">
        <ul class="nav nav-underline authorization-tabs gap-3">
            <li class="nav-item">
                <a class="nav-link py-3 px-1 active" href="{{ route('authorization') }}">List Employee</a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3 px-1" href="{{ route('authorization.access-menus') }}">Assign Permission</a>
            </li>
        </ul>
    </div>
</div>

<div class="card">
    <div class="card-header border-0 flex-wrap gap-3">
        <div>
            <h4 class="card-title mb-1">List Employee</h4>
            <p class="mb-0 text-muted fs-13">Data karyawan, deployment, identitas, dan PIC.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <div class="input-group" style="max-width: 320px;">
                <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" class="form-control" placeholder="Search user">
            </div>
            @if ($canManageDataEmployee)
                <a href="{{ route('authorization.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus me-1"></i>Create Users
                </a>
            @endif
        </div>
    </div>
    @if (session('status'))
        <div class="alert alert-success mx-4 mb-3">{{ session('status') }}</div>
    @endif
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table align-middle">
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
                                        <form action="{{ route('authorization.destroy', ['employee' => $user['id']]) }}" method="POST" onsubmit="return confirm('Hapus data employee ini?')">
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
                            <td colspan="8" class="text-center text-muted py-4">No employee data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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
