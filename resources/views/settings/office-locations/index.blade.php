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
    <link rel="stylesheet" href="{{ asset('assets/vendor/datatables/js/jquery.dataTables.min.css') }}">
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

        #officeLocationsTable_wrapper .dataTables_filter,
        #officeLocationsTable_wrapper .dataTables_length {
            display: none;
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
            <p class="mb-0 text-muted fs-13">Manage work location master data for attendance rules and employee deployment.</p>
        </div>
        <div class="settings-list-actions">
            <div class="input-group">
                <span class="input-group-text bg-white" aria-hidden="true">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input
                    id="officeLocationsTableSearch"
                    type="search"
                    class="form-control"
                    placeholder="Search work locations"
                    autocomplete="off"
                    aria-label="Search work locations"
                >
            </div>
            <a href="{{ route('settings.office-locations.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i>Add Location
            </a>
        </div>
    </div>

    @include('partials.swal-alert', ['type' => 'success', 'message' => session('status')])

    @include('partials.swal-alert', ['type' => 'error', 'message' => session('error')])

    <div class="card-body table-card-body p-0">
        <div class="table-responsive">
            <table id="officeLocationsTable" class="table table-sm mb-0 table-bottom-borderless table-striped align-middle w-100">
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
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

@include('settings.partials.delete-confirmation-swal')
@endsection

@section('script')
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
    @endphp
    <script src="{{ asset('assets/vendor/datatables/js/jquery.dataTables.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
    <script>
        (function () {
            if (!window.jQuery || !jQuery.fn.DataTable) {
                return;
            }

            var editUrlTemplate = @json(route('settings.office-locations.edit', ['officeLocation' => '__ID__']));
            var deleteUrlTemplate = @json(route('settings.office-locations.destroy', ['officeLocation' => '__ID__']));
            var csrfToken = @json(csrf_token());

            function escapeHtml(value) {
                return String(value === null || value === undefined ? '' : value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function routeFor(template, id) {
                return template.replace('__ID__', encodeURIComponent(id));
            }

            function isTruthy(value) {
                return value === true || value === 1 || value === '1' || value === 'true';
            }

            function statusBadge(isActive) {
                var active = isTruthy(isActive);
                var statusClass = active ? 'badge-success' : 'badge-danger';
                var statusLabel = active ? 'Active' : 'Inactive';

                return '<span class="badge badge-sm light ' + statusClass + '">' + statusLabel + '</span>';
            }

            function actionButtons(row) {
                var editUrl = routeFor(editUrlTemplate, row.id);
                var deleteUrl = routeFor(deleteUrlTemplate, row.id);
                var name = escapeHtml(row.name || 'this work location');

                return '' +
                    '<div class="d-inline-flex gap-1">' +
                        '<a href="' + editUrl + '" class="btn btn-primary light btn-sm">Update</a>' +
                        '<form action="' + deleteUrl + '" method="POST" data-settings-delete-form data-delete-title="Delete Work Location" data-delete-message="Delete ' + name + ' from work location data?">' +
                            '<input type="hidden" name="_token" value="' + csrfToken + '">' +
                            '<input type="hidden" name="_method" value="DELETE">' +
                            '<button type="submit" class="btn btn-danger light btn-sm">Delete</button>' +
                        '</form>' +
                    '</div>';
            }

            var officeLocationsTable = jQuery('#officeLocationsTable').DataTable({
                ajax: '{{ route('settings.office-locations.datatable') }}',
                autoWidth: false,
                order: [[0, 'asc']],
                pageLength: 10,
                processing: true,
                serverSide: true,
                columns: [
                    {
                        data: 'name',
                        name: 'name',
                        className: 'fw-semibold text-black'
                    },
                    {
                        data: 'address',
                        name: 'address'
                    },
                    {
                        data: 'latitude',
                        name: 'latitude'
                    },
                    {
                        data: 'longitude',
                        name: 'longitude'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        searchable: false,
                        render: function (data) {
                            return statusBadge(data);
                        }
                    },
                    {
                        data: null,
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-end',
                        render: function (data, type, row) {
                            return actionButtons(row);
                        }
                    }
                ],
                language: {
                    emptyTable: 'No work location data available.',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    infoEmpty: 'Showing 0 to 0 of 0 entries',
                    infoFiltered: '(filtered from _MAX_ total entries)',
                    processing: 'Loading...',
                    zeroRecords: 'No matching work location found.'
                }
            });

            var searchInput = document.getElementById('officeLocationsTableSearch');
            var searchTimeout = null;

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    window.clearTimeout(searchTimeout);

                    searchTimeout = window.setTimeout(function () {
                        officeLocationsTable.search(searchInput.value).draw();
                    }, 400);
                });
            }
        })();
    </script>
    @stack('scripts')
@endsection
