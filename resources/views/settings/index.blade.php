@extends('layouts.main')

@section('title', 'Setting - '.$pageTitle)

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

        #{{ $tableId }}_wrapper .dataTables_filter,
        #{{ $tableId }}_wrapper .dataTables_length {
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
            <div class="input-group">
                <span class="input-group-text bg-white" aria-hidden="true">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input
                    id="{{ $tableId }}Search"
                    type="search"
                    class="form-control"
                    placeholder="{{ $searchPlaceholder }}"
                    autocomplete="off"
                    aria-label="{{ $searchPlaceholder }}"
                >
            </div>
            <a href="{{ route($routePrefix.'.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i>Add {{ $resourceLabel }}
            </a>
        </div>
    </div>

    @include('partials.swal-alert', ['type' => 'success', 'message' => session('status')])

    @include('partials.swal-alert', ['type' => 'error', 'message' => session('error')])

    <div class="card-body table-card-body p-0">
        <div class="table-responsive">
            <table id="{{ $tableId }}" class="table table-sm mb-0 table-bottom-borderless table-striped align-middle w-100">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Status</th>
                        @if ($resourceLabel === 'Position')
                            <th>Type</th>
                        @endif
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

            var editUrlTemplate = @json(route($routePrefix.'.edit', [$routeParameter => '__ID__']));
            var deleteUrlTemplate = @json(route($routePrefix.'.destroy', [$routeParameter => '__ID__']));
            var csrfToken = @json(csrf_token());
            var resourceLabel = @json($resourceLabel);
            var resourceName = @json(strtolower($resourceLabel));
            var isPositionTable = @json($resourceLabel === 'Position');

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

            function statusBadge(status) {
                var normalizedStatus = String(status || 'inactive').toLowerCase();
                var statusClass = normalizedStatus === 'active' ? 'badge-success' : 'badge-danger';
                var statusLabel = normalizedStatus.charAt(0).toUpperCase() + normalizedStatus.slice(1);

                return '<span class="badge badge-sm light ' + statusClass + '">' + escapeHtml(statusLabel) + '</span>';
            }

            function typeBadge(isProtected) {
                if (isTruthy(isProtected)) {
                    return '<span class="badge badge-sm light badge-primary">Protected</span>';
                }

                return '<span class="text-muted">-</span>';
            }

            function actionButtons(row) {
                var editUrl = routeFor(editUrlTemplate, row.id);

                if (isPositionTable && isTruthy(row.is_protected)) {
                    return '' +
                        '<div class="d-inline-flex gap-1">' +
                            '<a href="' + editUrl + '" class="btn btn-primary light btn-sm">Update</a>' +
                            '<button type="button" class="btn btn-danger light btn-sm" disabled>Delete</button>' +
                        '</div>';
                }

                var deleteUrl = routeFor(deleteUrlTemplate, row.id);
                var name = escapeHtml(row.name || ('this ' + resourceName));

                return '' +
                    '<div class="d-inline-flex gap-1">' +
                        '<a href="' + editUrl + '" class="btn btn-primary light btn-sm">Update</a>' +
                        '<form action="' + deleteUrl + '" method="POST" data-settings-delete-form data-delete-title="Delete ' + escapeHtml(resourceLabel) + '" data-delete-message="Delete ' + name + ' from ' + escapeHtml(resourceName) + ' data?">' +
                            '<input type="hidden" name="_token" value="' + csrfToken + '">' +
                            '<input type="hidden" name="_method" value="DELETE">' +
                            '<button type="submit" class="btn btn-danger light btn-sm">Delete</button>' +
                        '</form>' +
                    '</div>';
            }

            var columns = [
                {
                    data: 'name',
                    name: 'name',
                    className: 'fw-semibold text-black'
                },
                {
                    data: 'status',
                    name: 'status',
                    render: function (data) {
                        return statusBadge(data);
                    }
                }
            ];

            if (isPositionTable) {
                columns.push({
                    data: 'is_protected',
                    name: 'is_protected',
                    orderable: true,
                    searchable: false,
                    render: function (data) {
                        return typeBadge(data);
                    }
                });
            }

            columns.push({
                data: null,
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'text-end',
                render: function (data, type, row) {
                    return actionButtons(row);
                }
            });

            var settingsTable = jQuery('#{{ $tableId }}').DataTable({
                ajax: '{{ route($datatableRoute) }}',
                autoWidth: false,
                order: [[0, 'asc']],
                pageLength: 10,
                processing: true,
                serverSide: true,
                columns: columns,
                language: {
                    emptyTable: 'No {{ strtolower($resourceLabel) }} data available.',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    infoEmpty: 'Showing 0 to 0 of 0 entries',
                    infoFiltered: '(filtered from _MAX_ total entries)',
                    processing: 'Loading...',
                    zeroRecords: 'No matching {{ strtolower($resourceLabel) }} found.'
                }
            });

            var searchInput = document.getElementById('{{ $tableId }}Search');
            var searchTimeout = null;

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    window.clearTimeout(searchTimeout);

                    searchTimeout = window.setTimeout(function () {
                        settingsTable.search(searchInput.value).draw();
                    }, 400);
                });
            }
        })();
    </script>
    @stack('scripts')
@endsection
