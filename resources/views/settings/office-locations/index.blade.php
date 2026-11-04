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

        #datatable_wrapper .dataTables_filter,
        #datatable_wrapper .dataTables_length {
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
            <a href="{{ route('settings.office-locations.create') }}" class="btn light btn-success btn-sm">
                <i class="fa-solid fa-plus me-1"></i>Add Location
            </a>
        </div>
    </div>

    @include('partials.swal-alert', ['type' => 'success', 'message' => session('status')])

    @include('partials.swal-alert', ['type' => 'error', 'message' => session('error')])

    <div class="card-body table-card-body p-0">
        <div class="table-responsive">
            <table id="datatable" class="table table-bordered text-nowrap w-100"></table>
        </div>
    </div>
</div>
@endsection

@section('script')
    <script src="{{ asset('assets/vendor/datatables/js/jquery.dataTables.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
        var csrfToken = @json(csrf_token());
        var deleteUrlTemplate = @json(route('settings.office-locations.destroy', ['officeLocation' => '__ID__']));

        function deleteData(id) {
            var officeLocation = $('#datatable').DataTable().rows().data().toArray().find(function (row) {
                return String(row.id) === String(id);
            });

            Swal.fire({
                title: 'Delete Work Location',
                text: 'Delete ' + (officeLocation ? officeLocation.name : 'this work location') + ' from work location data?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545',
                reverseButtons: true,
                focusCancel: true
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: deleteUrlTemplate.replace('__ID__', encodeURIComponent(id)),
                    type: 'DELETE',
                    dataType: 'json',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                }).done(function (response) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message });
                    $('#datatable').DataTable().ajax.reload(null, false);
                }).fail(function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Work location gagal dihapus.' });
                });
            });
        }

        $(function () {
            if (!window.jQuery || !jQuery.fn.DataTable) {
                return;
            }

            var editUrlTemplate = @json(route('settings.office-locations.edit', ['officeLocation' => '__ID__']));

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

                return actionMenu([
                    '<li><a class="dropdown-item" href="' + editUrl + '"><i class="bi bi-pencil-square"></i>Update</a></li>',
                    '<li><button type="button" class="dropdown-item text-danger" onclick="deleteData(\'' + escapeHtml(row.id) + '\')"><i class="bi bi-trash"></i>Delete</button></li>'
                ]);
            }

            var officeLocationsTable = jQuery('#datatable').DataTable({
                ajax: '{{ route('settings.office-locations.datatable') }}',
                autoWidth: false,
                order: [[0, 'asc']],
                pageLength: 10,
                processing: true,
                serverSide: true,
                columns: [
                    {
                        title: 'Name',
                        data: 'name',
                        name: 'name',
                        className: 'fw-semibold text-black'
                    },
                    {
                        title: 'Address',
                        data: 'address',
                        name: 'address'
                    },
                    {
                        title: 'Latitude',
                        data: 'latitude',
                        name: 'latitude'
                    },
                    {
                        title: 'Longitude',
                        data: 'longitude',
                        name: 'longitude'
                    },
                    {
                        title: 'Status',
                        data: 'is_active',
                        name: 'is_active',
                        searchable: false,
                        render: function (data) {
                            return statusBadge(data);
                        }
                    },
                    {
                        title: 'Action',
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
        });
    </script>
    @stack('scripts')
@endsection
