@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <link rel="stylesheet" href="https://unpkg.com/antd@6.2.3/dist/antd.css">
    <!-- Start - All Required Plugins -->
    <style>
        .absensi-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            scrollbar-width: thin;
            -webkit-overflow-scrolling: touch;
            white-space: nowrap;
            gap: 0.25rem;
        }

        .absensi-tabs .nav-item {
            flex: 0 0 auto;
        }

        .absensi-tabs .nav-link {
            white-space: nowrap;
        }

        .absensi-tabs .absensi-tab-btn {
            border: 0;
            background: transparent;
        }

        .absensi-tabs .absensi-tab-btn:focus {
            box-shadow: none;
        }

        #myTable thead th {
            font-size: 1.1rem;
            font-weight: 600;
            padding: 1rem 0.75rem;
        }

        #myTable tbody td {
            font-size: 1rem;
            padding: 0.9rem 0.75rem;
            vertical-align: middle;
        }

        #myTable thead th:first-child,
        #myTable tbody td:first-child {
            text-align: center !important;
        }

        .badge-attendance-empty {
            background: #fff1f2;
            color: #be123c;
            border: 1px solid #fecdd3;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.35rem 0.6rem;
            border-radius: 999px;
            display: inline-block;
        }

        .applicant-table-title {
            font-size: 1rem;
            font-weight: 700;
            color: #25314c;
            margin-bottom: 0;
            text-align: left;
        }

        .applicant-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.85rem;
        }

        .applicant-filter-bar {
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.5rem;
            margin-bottom: 0;
            margin-left: 0;
            width: auto;
        }

        .employee-header-actions {
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.5rem;
            margin-left: auto;
        }

        .applicant-filter-bar label {
            margin: 0;
            color: #5f6b7a;
            font-weight: 600;
        }

        .applicant-filter-bar select {
            min-height: 38px;
            width: 190px;
            max-width: 190px;
            border-radius: 0.6rem;
            border: 1px solid #d9dce5;
            padding: 0.35rem 0.75rem;
            background: #fff;
            color: #27334a;
            font-size: 0.95rem;
            text-overflow: ellipsis;
        }

        .applicant-filter-bar select:focus {
            border-color: #cfd5df;
            box-shadow: 0 0 0 0.15rem rgba(15, 23, 42, 0.08);
            outline: 0;
        }

        .applicant-photo {
            width: 34px;
            height: 34px;
            border-radius: 0.45rem;
            background: #e5e7eb;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
        }

        .applicant-action-group {
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .applicant-action-btn {
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 0.35rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .applicant-action-btn.edit {
            background: #f8e8b7;
            color: #f2ad00;
        }

        .applicant-action-btn.view {
            background: #d9f2f4;
            color: #4aa3ad;
        }

        .applicant-action-btn.delete {
            background: #f8d6e2;
            color: #ff4f7b;
        }

        .permission-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
        }

        .permission-chip {
            display: inline-flex;
            align-items: center;
            border: 1px solid #5b6dff;
            color: #4c60ff;
            border-radius: 0.35rem;
            padding: 0.2rem 0.55rem;
            font-size: 0.9rem;
            line-height: 1.2;
            background: transparent;
            white-space: nowrap;
        }

        #myTable_wrapper .dt-length label,
        #myTable_wrapper .dt-search label,
        #myTable_wrapper .dt-info,
        #myTable_wrapper .dt-paging {
            font-size: 1rem;
        }

        #myTable_wrapper .dt-layout-row:first-child {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1rem;
            margin-bottom: 0.85rem;
            background: #fff;
            border: 1px solid #e6eaf2;
            border-radius: 0.75rem;
        }

        #myTable_wrapper .dt-length,
        #myTable_wrapper .dt-search {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
        }

        #myTable_wrapper .dt-search label,
        #myTable_wrapper .dt-length label {
            margin: 0;
            color: #5f6b7a;
            font-weight: 500;
        }

        #myTable_wrapper .dt-search input,
        #myTable_wrapper .dt-length select {
            min-height: 40px;
            font-size: 1rem;
            border-radius: 0.6rem;
            border: 1px solid #d9dce5;
            padding: 0.35rem 0.75rem;
            background: #fff;
            color: #27334a;
        }

        #myTable_wrapper .dt-search input {
            min-width: 220px;
        }

        #myTable_wrapper .dt-search input:focus,
        #myTable_wrapper .dt-length select:focus {
            border-color: #cfd5df;
            box-shadow: 0 0 0 0.15rem rgba(15, 23, 42, 0.08);
            outline: 0;
        }

        @media only screen and (max-width: 767.98px) {
            .applicant-header-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .applicant-filter-bar {
                display: flex;
                width: 100%;
                margin-left: 0;
                justify-content: space-between;
            }

            .applicant-filter-bar select {
                width: 100%;
                max-width: 100%;
            }

            .employee-header-actions {
                width: 100%;
                margin-left: 0;
                justify-content: flex-end;
            }

            #myTable_wrapper .dt-layout-row:first-child {
                flex-direction: column;
                align-items: stretch;
            }

            #myTable_wrapper .dt-search,
            #myTable_wrapper .dt-length {
                justify-content: space-between;
                width: 100%;
            }

            #myTable_wrapper .dt-search input {
                min-width: 0;
                width: 100%;
            }
        }

        #myTable_wrapper .dt-paging .dt-paging-button {
            background: #fff !important;
            border: 1px solid #d9dce5 !important;
            color: #5f6b7a !important;
        }

        #myTable_wrapper .dt-paging .dt-paging-button:hover {
            background: #f3f6ff !important;
            color: var(--bs-primary) !important;
        }

        #myTable_wrapper .dt-paging .dt-paging-button.current {
            background: var(--bs-danger) !important;
            border-color: var(--bs-danger) !important;
            color: #fff !important;
        }
    </style>

@endsection

@section('navbarTitle', 'Users')

@section('content')
<!-- Start - Page Title & Breadcrumb -->
<div class="page-title">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li><h1>Users</h1></li>
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">
                    <svg width="18" height="18" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.125 6.375L8.5 1.41667L14.875 6.375V14.1667C14.875 14.5424 14.7257 14.9027 14.4601 15.1684C14.1944 15.4341 13.8341 15.5833 13.4583 15.5833H3.54167C3.16594 15.5833 2.80561 15.4341 2.53993 15.1684C2.27426 14.9027 2.125 14.5424 2.125 14.1667V6.375Z" stroke="var(--bs-body-color)" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M6.375 15.5833V8.5H10.625V15.5833" stroke="var(--bs-body-color)" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Home
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Users</li>
        </ol>
    </nav>
</div>
<!-- End - Page Title & Breadcrumb -->
<div class="row">
    <div class="col-xl-12 col-xxl-12">
        <div class="card h-auto">
            <div class="card-header">
                <ul class="nav nav-underline card-header-tabs absensi-tabs" id="nav-tab" role="tablist">
                    <li class="nav-item">
                        <button type="button" data-href="{{ route('employee_data') }}" class="nav-link absensi-tab-btn {{ request()->routeIs('employee_data') ? 'active' : '' }}" aria-selected="{{ request()->routeIs('employee_data') ? 'true' : 'false' }}">List Karyawan</button>
                    </li>
                    <li class="nav-item">
                        <button type="button" data-href="{{ route('employee_data.authorization') }}" class="nav-link absensi-tab-btn {{ request()->routeIs('employee_data.authorization') ? 'active' : '' }}" aria-selected="{{ request()->routeIs('employee_data.authorization') ? 'true' : 'false' }}">Authorization</button>
                    </li>
                </ul>
            </div>
            <div class="row">
                <div class="col-xxl-12 col-xl-12">
                    <div class="card-body">
                        <div class="applicant-header-bar">
                            <div class="applicant-table-title">Data Authorization</div>
                            <div class="employee-header-actions">
                                <button type="button" class="btn btn-primary btn-sm">Create Authorization</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="myTable" class="display table">
                                <thead>
                                <tr>
                                    <th class="mw-80">No</th>
                                    <th class="mw-140">Action</th>
                                    <th class="mw-220">Menu</th>
                                    <th class="mw-500">Department</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>1.</td>
                                    <td>
                                        <div class="applicant-action-group">
                                            <button type="button" class="applicant-action-btn edit"><i class="bi bi-pencil"></i></button>
                                            <button type="button" class="applicant-action-btn delete"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                    <td>Administrations</td>
                                    <td>
                                        <div class="permission-wrap">
                                            <span class="permission-chip">Board of Directors</span>
                                            <span class="permission-chip">System Administrator</span>
                                            <span class="permission-chip">Finance and Administration Coordinator</span>
                                            <span class="permission-chip">Accounting and Taxation</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2.</td>
                                    <td>
                                        <div class="applicant-action-group">
                                            <button type="button" class="applicant-action-btn edit"><i class="bi bi-pencil"></i></button>
                                            <button type="button" class="applicant-action-btn delete"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                    <td>Applicants</td>
                                    <td>
                                        <div class="permission-wrap">
                                            <span class="permission-chip">Board of Directors</span>
                                            <span class="permission-chip">System Administrator</span>
                                            <span class="permission-chip">Finance and Administration Coordinator</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>3.</td>
                                    <td>
                                        <div class="applicant-action-group">
                                            <button type="button" class="applicant-action-btn edit"><i class="bi bi-pencil"></i></button>
                                            <button type="button" class="applicant-action-btn delete"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                    <td>Attendances</td>
                                    <td>
                                        <div class="permission-wrap">
                                            <span class="permission-chip">All Staff</span>
                                            <span class="permission-chip">Legal Officer &amp; Partnership</span>
                                            <span class="permission-chip">Finance and Administration Coordinator</span>
                                            <span class="permission-chip">Accounting and Taxation</span>
                                            <span class="permission-chip">Operations Coordinator</span>
                                            <span class="permission-chip">Interior Design</span>
                                            <span class="permission-chip">Architecture Design</span>
                                            <span class="permission-chip">Web Developer</span>
                                            <span class="permission-chip">Documentation Staff</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>4.</td>
                                    <td>
                                        <div class="applicant-action-group">
                                            <button type="button" class="applicant-action-btn edit"><i class="bi bi-pencil"></i></button>
                                            <button type="button" class="applicant-action-btn delete"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                    <td>Attendances-data</td>
                                    <td>
                                        <div class="permission-wrap">
                                            <span class="permission-chip">Board of Directors</span>
                                            <span class="permission-chip">System Administrator</span>
                                            <span class="permission-chip">Legal Officer &amp; Partnership</span>
                                            <span class="permission-chip">Finance and Administration Coordinator</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>5.</td>
                                    <td>
                                        <div class="applicant-action-group">
                                            <button type="button" class="applicant-action-btn edit"><i class="bi bi-pencil"></i></button>
                                            <button type="button" class="applicant-action-btn delete"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                    <td>Blogs</td>
                                    <td>
                                        <div class="permission-wrap">
                                            <span class="permission-chip">Select All</span>
                                            <span class="permission-chip">System Administrator</span>
                                            <span class="permission-chip">Legal Officer &amp; Partnership</span>
                                            <span class="permission-chip">Finance and Administration Coordinator</span>
                                            <span class="permission-chip">Accounting and Taxation</span>
                                            <span class="permission-chip">Operations Coordinator</span>
                                            <span class="permission-chip">Interior Design</span>
                                            <span class="permission-chip">Architecture Design</span>
                                            <span class="permission-chip">Web Developer</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>6.</td>
                                    <td>
                                        <div class="applicant-action-group">
                                            <button type="button" class="applicant-action-btn edit"><i class="bi bi-pencil"></i></button>
                                            <button type="button" class="applicant-action-btn delete"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                    <td>Calendar</td>
                                    <td>
                                        <div class="permission-wrap">
                                            <span class="permission-chip">Select All</span>
                                            <span class="permission-chip">System Administrator</span>
                                            <span class="permission-chip">Legal Officer &amp; Partnership</span>
                                            <span class="permission-chip">Finance and Administration Coordinator</span>
                                            <span class="permission-chip">Accounting and Taxation</span>
                                            <span class="permission-chip">Operations Coordinator</span>
                                            <span class="permission-chip">Interior Design</span>
                                            <span class="permission-chip">Architecture Design</span>
                                            <span class="permission-chip">Web Developer</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>7.</td>
                                    <td>
                                        <div class="applicant-action-group">
                                            <button type="button" class="applicant-action-btn edit"><i class="bi bi-pencil"></i></button>
                                            <button type="button" class="applicant-action-btn delete"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                    <td>Cms</td>
                                    <td>
                                        <div class="permission-wrap">
                                            <span class="permission-chip">Select All</span>
                                            <span class="permission-chip">System Administrator</span>
                                            <span class="permission-chip">Legal Officer &amp; Partnership</span>
                                            <span class="permission-chip">Finance and Administration Coordinator</span>
                                            <span class="permission-chip">Accounting and Taxation</span>
                                            <span class="permission-chip">Operations Coordinator</span>
                                            <span class="permission-chip">Interior Design</span>
                                            <span class="permission-chip">Architecture Design</span>
                                            <span class="permission-chip">Web Developer</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>8.</td>
                                    <td>
                                        <div class="applicant-action-group">
                                            <button type="button" class="applicant-action-btn edit"><i class="bi bi-pencil"></i></button>
                                            <button type="button" class="applicant-action-btn delete"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                    <td>Dashboard</td>
                                    <td>
                                        <div class="permission-wrap">
                                            <span class="permission-chip">Select All</span>
                                            <span class="permission-chip">System Administrator</span>
                                            <span class="permission-chip">Legal Officer &amp; Partnership</span>
                                            <span class="permission-chip">Finance and Administration Coordinator</span>
                                            <span class="permission-chip">Accounting and Taxation</span>
                                            <span class="permission-chip">Operations Coordinator</span>
                                            <span class="permission-chip">Interior Design</span>
                                            <span class="permission-chip">Architecture Design</span>
                                            <span class="permission-chip">Web Developer</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>9.</td>
                                    <td>
                                        <div class="applicant-action-group">
                                            <button type="button" class="applicant-action-btn edit"><i class="bi bi-pencil"></i></button>
                                            <button type="button" class="applicant-action-btn delete"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                    <td>Finances</td>
                                    <td>
                                        <div class="permission-wrap">
                                            <span class="permission-chip">Board of Directors</span>
                                            <span class="permission-chip">System Administrator</span>
                                            <span class="permission-chip">Finance and Administration Coordinator</span>
                                            <span class="permission-chip">Accounting and Taxation</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>10.</td>
                                    <td>
                                        <div class="applicant-action-group">
                                            <button type="button" class="applicant-action-btn edit"><i class="bi bi-pencil"></i></button>
                                            <button type="button" class="applicant-action-btn delete"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                    <td>Options</td>
                                    <td>
                                        <div class="permission-wrap">
                                            <span class="permission-chip">System Administrator</span>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End - Content Body -->

@endsection

@section('script')
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
    @endphp
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
    <script>
        $(function () {
            $('.absensi-tab-btn').on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                var targetUrl = $(this).data('href');
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
            });

            var applicantsTable = $('#myTable').DataTable({
                columnDefs: [
                    {
                        targets: [0, 1, 3],
                        orderable: false
                    }
                ]
            });

            applicantsTable.on('order.dt search.dt draw.dt', function () {
                applicantsTable
                    .column(0, { search: 'applied', order: 'applied', page: 'current' })
                    .nodes()
                    .each(function (cell, index) {
                        cell.innerHTML = (index + 1) + '.';
                    });
            }).draw();
        });
    </script>
@endsection
